<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * /estadisticas/turnos: cifras de las citas y turnos de recepción (tabla
 * recepcion), en línea y de ventanilla, por sede y por día, semana o mes.
 *
 * Qué cuenta cada cifra (lo mismo en la pantalla, el PDF, el Excel y la
 * imagen):
 *
 *   citas           todas las del periodo (por la fecha de la cita);
 *   atendidas       estatus "atendido" (el módulo terminó la atención);
 *   noAcudieron     estatus "expirada": no validó su QR a tiempo o el módulo
 *                   la marcó "No se presentó" (llamadas: de esas, cuántas sí
 *                   se llamaron en pantalla);
 *   sinAtencion     ya pasó el día y quedó en "confirmada": llegó (recepción
 *                   validó su cita) pero nadie registró que se atendió;
 *   sinRegistro     ya pasó el día y quedó en "pendiente": nadie registró ni
 *                   su llegada ni su atención;
 *   porVenir        hoy o después, todavía pendiente o en espera;
 *   largas          atendidas cuya atención (de "Atender" a "Terminar" en Mi
 *                   módulo) duró el umbral o más;
 *   tarde           se atendieron aunque llegaron tarde.
 *
 * Los casos de excepción son las citas marcadas como tal (recepcion.exepcion);
 * el seguimiento de su formato está en /estadisticas/excepciones.
 *
 * Las citas de prueba no se cuentan. Se agrupa en PHP (no con funciones de
 * fecha de MySQL) para que día, semana y mes salgan iguales en cualquier base.
 */
class EstadisticasTurnos
{
    public const AGRUPAR = [
        'dia'    => 'Por día',
        'semana' => 'Por semana',
        'mes'    => 'Por mes',
    ];

    /** Más periodos que esto no caben en una tabla legible: se agrupa más grueso. */
    private const MAXIMO = ['dia' => 62, 'semana' => 60];

    /** Minutos de atención a partir de los que cuenta como "atención larga". */
    public const UMBRALES = [20, 30, 45, 60, 90];

    public const UMBRAL = 45;

    /** Duraciones fuera de esto se toman como captura errónea (no se promedian). */
    private const MINUTOS_MAXIMOS = 480;

    public const TIPOS = ['Solicitud', 'Ratificación', 'Asesoría', 'Cumplimiento'];

    public const ORIGENES = ['linea' => 'En línea', 'ventanilla' => 'Ventanilla'];

    public const FORMATOS = ['xlsx', 'pdf', 'png'];

    /** Cuántas atenciones largas se listan. */
    public const LISTA_LARGAS = 20;

    /**
     * Columnas de las tablas (pantalla, PDF, Excel e imagen). "largas" lleva
     * el umbral en el encabezado (encabezados()).
     */
    public const COLUMNAS = [
        'citas'       => 'Citas',
        'linea'       => 'En línea',
        'ventanilla'  => 'Ventanilla',
        'atendidas'   => 'Atendidas',
        'noAcudieron' => 'No acudieron',
        'sinAtencion' => 'Llegaron sin atención',
        'sinRegistro' => 'Sin registro',
        'porVenir'    => 'Por atender',
        'largas'      => 'Atención larga',
        'promedio'    => 'Promedio (min)',
        'tarde'       => 'Llegaron tarde',
    ];

    /** Encabezados con el umbral de atención larga. */
    public static function encabezados(int $umbral): array
    {
        return array_merge(self::COLUMNAS, ['largas' => 'Atención larga (≥ '.$umbral.' min)']);
    }

    /** Qué significa cada cifra, en palabras de todos los días. */
    public const GLOSARIO = [
        'Citas'                      => 'Todas las citas y turnos agendados para esas fechas, en línea y de ventanilla.',
        'Atendidas'                  => 'El módulo terminó su atención.',
        'No acudieron'               => 'No se presentaron: no validaron su cita a tiempo o el módulo las marcó «No se presentó».',
        'Llegaron sin atención'      => 'Ya pasó el día: recepción registró que llegaron, pero nadie registró que se atendieron.',
        'Sin registro'               => 'Ya pasó el día y nadie registró ni su llegada ni su atención (siguen «pendientes»).',
        'Por atender'                => 'Son de hoy o de días que aún no llegan.',
        'Atención larga'             => 'Atendidas cuya atención (de «Atender» a «Terminar» en Mi módulo) duró el tiempo elegido o más.',
        'Tiempo promedio'            => 'Minutos promedio de atención, sólo de las que tienen hora de inicio y de fin.',
        'Llegaron tarde'             => 'Se atendieron aunque su cita ya había expirado.',
        'Casos de excepción'         => 'Citas marcadas como caso de excepción (art. 685 Ter LFT).',
        'Conexión (IP)'              => 'Dirección de internet desde la que se agendó la cita en línea. Varias personas pueden compartirla (una empresa, una escuela, un mismo proveedor de celular).',
        'IP, correo o teléfono frecuente' => 'El que registró el número de citas en línea elegido o más en el periodo; «personas distintas» cuenta CURP diferentes.',
        'Intentos detenidos'         => 'Citas en línea que no se registraron porque la conexión, el correo o el teléfono ya tenía el máximo (Administración → Módulos).',
    ];

    /** Lee y acomoda los filtros (fechas al revés se voltean; por omisión, este mes por día). */
    public static function filtros(Request $request): array
    {
        $fecha = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;

        $desde = $fecha($request->query('desde'));
        $hasta = $fecha($request->query('hasta'));
        $desde ??= $hasta ?? now()->startOfMonth()->toDateString();
        $hasta ??= max($desde, now()->endOfMonth()->toDateString());
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $sede     = (string) $request->query('sede', '');
        $tipo     = (string) $request->query('tipo', '');
        $origen   = (string) $request->query('origen', '');
        $agrupar  = (string) $request->query('agrupar', 'dia');
        $minutos  = (int) $request->query('minutos', self::UMBRAL);

        return [
            'desde'   => $desde,
            'hasta'   => $hasta,
            'agrupar' => array_key_exists($agrupar, self::AGRUPAR) ? $agrupar : 'dia',
            'sede'    => in_array($sede, Recepcion::TODAS_LAS_SEDES, true) ? $sede : '',
            'tipo'    => in_array($tipo, self::TIPOS, true) ? $tipo : '',
            'origen'  => array_key_exists($origen, self::ORIGENES) ? $origen : '',
            'minutos' => in_array($minutos, self::UMBRALES, true) ? $minutos : self::UMBRAL,
            // ¿Quién agenda las citas en línea? (AgendadoresCitas): desde cuántas citas es "frecuente".
            'frecuente' => AgendadoresCitas::frecuente($request->query('frecuente', AgendadoresCitas::FRECUENTE_DEF)),
        ];
    }

    /**
     * Todo lo que necesitan la pantalla y las tres descargas.
     *
     * @param array{desde: string, hasta: string, agrupar: string, sede: string, tipo: string, origen: string, minutos: int} $f
     */
    public static function armar(array $f, ?string $por = null): array
    {
        $desde = Carbon::parse($f['desde'])->startOfDay();
        $hasta = Carbon::parse($f['hasta'])->startOfDay();
        $hoy   = now()->toDateString();

        [$agrupar, $ajustado] = self::agrupacion($f['agrupar'], $desde, $hasta);
        $periodos = self::periodos($agrupar, $desde, $hasta);

        $sedes = $f['sede'] !== '' ? [$f['sede']] : Recepcion::TODAS_LAS_SEDES;

        $acum = [
            'todas'     => self::vacio(),
            'excepcion' => self::vacio(),
            'sede'      => [],
            'sedeExc'   => [],
            'tipo'      => [],
            'periodo'   => [],
            'periodoExc'=> [],
        ];
        $largas = [];

        foreach (self::consulta($f)->lazyById(2000, 'r.id', 'id') as $r) {
            $c        = self::clasificar($r, $hoy, $f['minutos']);
            $sede     = self::sede((string) $r->delegacion);
            $clave    = self::clavePeriodo($agrupar, Carbon::parse($r->fecha));
            $tipo     = $r->tipo ?: 'Sin trámite';
            $esExc    = $r->exepcion === 'Si';

            self::sumar($acum['todas'], $c);
            self::sumar($acum['sede'][$sede], $c);
            self::sumar($acum['tipo'][$tipo], $c);
            self::sumar($acum['periodo'][$clave], $c);
            if ($esExc) {
                self::sumar($acum['excepcion'], $c);
                self::sumar($acum['sedeExc'][$sede], $c);
                self::sumar($acum['periodoExc'][$clave], $c);
            }

            if ($c['larga']) {
                $largas[] = [
                    'minutos'   => $c['minutos'],
                    'fecha'     => Carbon::parse($r->fecha)->format('d/m/Y'),
                    'hora'      => substr((string) $r->hora, 0, 5),
                    'folio'     => FolioCita::mostrar($r),
                    'sede'      => $sede,
                    'tipo'      => $tipo,
                    'origen'    => self::ORIGENES[$r->origen] ?? 'Ventanilla',
                    'excepcion' => $esExc,
                    'inicio'    => Carbon::parse($r->atencion_inicio_at)->format('H:i'),
                    'fin'       => Carbon::parse($r->atencion_fin_at)->format('H:i'),
                    'atendio'   => $r->atendido_por_nombre ? Str::title(mb_strtolower($r->atendido_por_nombre)) : '',
                ];
            }
        }

        usort($largas, fn ($a, $b) => $b['minutos'] <=> $a['minutos']);

        // Por sede: todas las del filtro en el orden oficial (aunque estén en
        // cero) y, al final, las citas sin sede si las hay.
        $nombresSede = array_merge($sedes, array_values(array_diff(array_keys($acum['sede']), $sedes)));
        $porSede = $porSedeExc = [];
        foreach ($nombresSede as $s) {
            $porSede[]    = ['etiqueta' => $s] + self::cerrar($acum['sede'][$s] ?? self::vacio());
            $porSedeExc[] = ['etiqueta' => $s] + self::cerrar($acum['sedeExc'][$s] ?? self::vacio());
        }

        $porTipo = [];
        foreach (array_merge(self::TIPOS, array_diff(array_keys($acum['tipo']), self::TIPOS)) as $t) {
            if (isset($acum['tipo'][$t])) {
                $porTipo[] = ['etiqueta' => $t] + self::cerrar($acum['tipo'][$t]);
            }
        }

        $serie = [];
        foreach ($periodos as $clave => $p) {
            $datos = $acum['periodo'][$clave] ?? null;
            // En "por día" se saltan los fines de semana vacíos.
            if ($datos === null && $agrupar === 'dia' && $p['finDeSemana']) {
                continue;
            }
            $serie[] = $p + self::cerrar($datos ?? self::vacio()) + [
                'excepcion' => self::cerrar($acum['periodoExc'][$clave] ?? self::vacio()),
            ];
        }

        $texto = self::textoPeriodo($desde, $hasta);
        $filtrosTexto = implode(' · ', array_filter([
            $f['sede'] ?: 'Todas las sedes',
            $f['tipo'] ?: 'Todos los trámites',
            $f['origen'] ? self::ORIGENES[$f['origen']] : 'En línea y ventanilla',
        ]));

        return [
            'titulo'       => 'Estadísticas de turnos y citas',
            'periodo'      => $texto,
            'filtrosTexto' => $filtrosTexto,
            'agrupar'      => $agrupar,
            'agruparTexto' => self::AGRUPAR[$agrupar],
            'ajustado'     => $ajustado,
            'unidad'       => ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'][$agrupar],
            'umbral'       => $f['minutos'],
            'columnas'     => self::encabezados($f['minutos']),
            'filtros'      => $f,
            'general'      => self::cerrar($acum['todas']),
            'excepcion'    => self::cerrar($acum['excepcion']),
            'porSede'      => self::conTotal($porSede, count($nombresSede) > 1, $acum['todas']),
            'porSedeExc'   => self::conTotal($porSedeExc, count($nombresSede) > 1, $acum['excepcion']),
            'porTipo'      => self::conTotal($porTipo, count($porTipo) > 1, $acum['todas']),
            'serie'        => $serie,
            'largas'       => array_slice($largas, 0, self::LISTA_LARGAS),
            'glosario'     => self::GLOSARIO,
            'generado'     => now(),
            'por'          => $por,
            'archivo'      => 'estadisticas_turnos_'.$desde->format('Y-m-d').($f['desde'] !== $f['hasta'] ? '_al_'.$hasta->format('Y-m-d') : '')
                              .($f['sede'] ? '_'.Str::slug($f['sede'], '_') : ''),
        ];
    }

    /** Cifras rápidas (tarjeta del panel): citas, atendidas y no acudieron de un rango. */
    public static function resumen(string $desde, string $hasta): array
    {
        $f = ['desde' => $desde, 'hasta' => $hasta, 'agrupar' => 'mes', 'sede' => '', 'tipo' => '', 'origen' => '', 'minutos' => self::UMBRAL];
        $c = self::vacio();
        $hoy = now()->toDateString();
        foreach (self::consulta($f)->lazyById(2000, 'r.id', 'id') as $r) {
            self::sumar($c, self::clasificar($r, $hoy, self::UMBRAL));
        }

        return self::cerrar($c);
    }

    // ------------------------------------------------------------------

    private static function consulta(array $f)
    {
        $columnas = ['r.id', 'r.fecha', 'r.hora', 'r.delegacion', 'r.tipo', 'r.estatus', 'r.exepcion', 'r.origen', 'r.consecutivo', 'r.codigo'];
        foreach ([
            'llamado_at'          => FlujoCita::migrado(),
            'atencion_inicio_at'  => FlujoCita::migrado(),
            'atencion_fin_at'     => FlujoCita::conFinAtencion(),
            'atendido_por_nombre' => FlujoCita::conResponsables(),
            'llego_tarde'         => FlujoCita::conTardias(),
        ] as $col => $hay) {
            $columnas[] = $hay ? 'r.'.$col : DB::raw('NULL as '.$col);
        }

        $sedes = $f['sede'] !== '' ? [$f['sede'], Str::ascii($f['sede'])] : null;

        return DB::table('recepcion as r')
            ->whereBetween('r.fecha', [$f['desde'], $f['hasta']])
            ->when($sedes, fn ($q) => $q->whereIn('r.delegacion', array_values(array_unique($sedes))))
            ->when($f['tipo'] !== '', fn ($q) => $q->where('r.tipo', $f['tipo']))
            ->when($f['origen'] === 'linea', fn ($q) => $q->where('r.origen', 'linea'))
            ->when($f['origen'] === 'ventanilla', fn ($q) => $q->where(fn ($w) => $w->where('r.origen', 'ventanilla')->orWhereNull('r.origen')))
            ->when(FlujoCita::migrado(), fn ($q) => $q->where(fn ($w) => $w->where('r.es_prueba', 0)->orWhereNull('r.es_prueba')))
            ->select($columnas);
    }

    /** En qué casilla cae una cita. */
    private static function clasificar(object $r, string $hoy, int $umbral): array
    {
        $estatus = strtolower((string) $r->estatus);
        $fecha   = Carbon::parse($r->fecha)->toDateString();
        $paso    = $fecha < $hoy;

        $minutos = null;
        if ($estatus === 'atendido' && $r->atencion_inicio_at && $r->atencion_fin_at) {
            $seg = Carbon::parse($r->atencion_fin_at)->getTimestamp() - Carbon::parse($r->atencion_inicio_at)->getTimestamp();
            $min = (int) round($seg / 60);
            $minutos = $seg >= 0 && $min <= self::MINUTOS_MAXIMOS ? $min : null;
        }

        return [
            'linea'       => $r->origen === 'linea',
            'atendidas'   => $estatus === 'atendido',
            'noAcudieron' => $estatus === 'expirada',
            'llamadas'    => $estatus === 'expirada' && ! empty($r->llamado_at),
            'sinAtencion' => $paso && $estatus === 'confirmada',
            'sinRegistro' => $paso && ($estatus === 'pendiente' || $estatus === ''),
            'porVenir'    => ! $paso && in_array($estatus, ['pendiente', 'confirmada', ''], true),
            'tarde'       => $estatus === 'atendido' && ! empty($r->llego_tarde),
            'minutos'     => $minutos,
            'larga'       => $minutos !== null && $minutos >= $umbral,
        ];
    }

    private static function vacio(): array
    {
        return [
            'citas' => 0, 'linea' => 0, 'ventanilla' => 0, 'atendidas' => 0, 'noAcudieron' => 0, 'llamadas' => 0,
            'sinAtencion' => 0, 'sinRegistro' => 0, 'porVenir' => 0, 'tarde' => 0, 'largas' => 0,
            'conTiempo' => 0, 'sumaMinutos' => 0, 'maxMinutos' => 0,
        ];
    }

    private static function sumar(?array &$a, array $c): void
    {
        $a ??= self::vacio();
        $a['citas']++;
        $a[$c['linea'] ? 'linea' : 'ventanilla']++;
        foreach (['atendidas', 'noAcudieron', 'llamadas', 'sinAtencion', 'sinRegistro', 'porVenir', 'tarde'] as $k) {
            $a[$k] += $c[$k] ? 1 : 0;
        }
        if ($c['minutos'] !== null) {
            $a['conTiempo']++;
            $a['sumaMinutos'] += $c['minutos'];
            $a['maxMinutos'] = max($a['maxMinutos'], $c['minutos']);
            $a['largas'] += $c['larga'] ? 1 : 0;
        }
    }

    /** Agrega porcentajes y promedios. Los porcentajes son sobre las citas que ya pasaron. */
    private static function cerrar(array $a): array
    {
        $pasadas = $a['citas'] - $a['porVenir'];
        $pct = fn ($v) => $pasadas > 0 ? round($v * 100 / $pasadas, 1) : null;

        return $a + [
            'pasadas'        => $pasadas,
            'sinCerrar'      => $a['sinAtencion'] + $a['sinRegistro'],
            'pctAtendidas'   => $pct($a['atendidas']),
            'pctNoAcudieron' => $pct($a['noAcudieron']),
            'pctSinCerrar'   => $pct($a['sinAtencion'] + $a['sinRegistro']),
            'promedio'       => $a['conTiempo'] > 0 ? (int) round($a['sumaMinutos'] / $a['conTiempo']) : null,
        ];
    }

    private static function conTotal(array $filas, bool $conTotal, array $total): array
    {
        if ($conTotal) {
            $filas[] = ['etiqueta' => 'Total', 'esTotal' => true] + self::cerrar($total);
        }

        return $filas;
    }

    /** Nombre oficial de la sede (sin importar acentos); "Sin sede" si no trae. */
    private static function sede(string $delegacion): string
    {
        static $mapa = null;
        $mapa ??= collect(Recepcion::TODAS_LAS_SEDES)->mapWithKeys(fn ($s) => [Str::lower(Str::ascii($s)) => $s])->all();
        $clave = Str::lower(Str::ascii(trim($delegacion)));

        return $clave === '' ? 'Sin sede' : ($mapa[$clave] ?? trim($delegacion));
    }

    /** Si el rango es muy largo para la agrupación pedida, se usa una más gruesa. */
    private static function agrupacion(string $pedida, Carbon $desde, Carbon $hasta): array
    {
        $dias = $desde->diffInDays($hasta) + 1;
        if ($pedida === 'dia' && $dias > self::MAXIMO['dia']) {
            return $dias / 7 > self::MAXIMO['semana'] ? ['mes', true] : ['semana', true];
        }
        if ($pedida === 'semana' && $dias / 7 > self::MAXIMO['semana']) {
            return ['mes', true];
        }

        return [$pedida, false];
    }

    private static function clavePeriodo(string $agrupar, Carbon $d): string
    {
        return match ($agrupar) {
            'semana' => $d->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
            'mes'    => $d->format('Y-m'),
            default  => $d->toDateString(),
        };
    }

    /** Todos los periodos del rango, aunque no tengan citas. */
    private static function periodos(string $agrupar, Carbon $desde, Carbon $hasta): array
    {
        $lista = [];
        $d = $desde->copy();
        while ($d->lte($hasta)) {
            $clave = self::clavePeriodo($agrupar, $d);
            if (! isset($lista[$clave])) {
                [$ini, $fin] = match ($agrupar) {
                    'semana' => [$d->copy()->startOfWeek(Carbon::MONDAY), $d->copy()->endOfWeek(Carbon::SUNDAY)],
                    'mes'    => [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()->startOfDay()],
                    default  => [$d->copy(), $d->copy()],
                };
                // Recortados al rango elegido.
                $ini = $ini->max($desde);
                $fin = $fin->min($hasta);
                $lista[$clave] = [
                    'clave'       => $clave,
                    'etiqueta'    => self::etiqueta($agrupar, $ini, $fin),
                    'corta'       => self::corta($agrupar, $ini),
                    'desde'       => $ini->toDateString(),
                    'hasta'       => $fin->toDateString(),
                    'finDeSemana' => $agrupar === 'dia' && $d->isWeekend(),
                ];
            }
            $d->addDay();
        }

        return $lista;
    }

    private static function etiqueta(string $agrupar, Carbon $ini, Carbon $fin): string
    {
        $es = fn (Carbon $c, string $f) => $c->copy()->locale('es')->isoFormat($f);

        return match ($agrupar) {
            'semana' => $ini->month === $fin->month
                ? $es($ini, 'D').' al '.$es($fin, 'D [de] MMM YYYY')
                : $es($ini, 'D [de] MMM').' al '.$es($fin, 'D [de] MMM YYYY'),
            'mes'    => Str::ucfirst($es($ini, 'MMMM YYYY')),
            default  => Str::ucfirst($es($ini, 'ddd D [de] MMM YYYY')),
        };
    }

    /** Etiqueta corta para el eje de la gráfica. */
    private static function corta(string $agrupar, Carbon $ini): string
    {
        $es = fn (string $f) => $ini->copy()->locale('es')->isoFormat($f);

        return match ($agrupar) {
            'semana' => 'Sem. '.$es('D MMM'),
            'mes'    => Str::ucfirst($es('MMM YYYY')),
            default  => $es('ddd D MMM'),
        };
    }

    public static function textoPeriodo(Carbon $desde, Carbon $hasta): string
    {
        $f = fn (Carbon $c) => $c->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        return $desde->equalTo($hasta) ? Str::ucfirst($desde->copy()->locale('es')->isoFormat('dddd')).' '.$f($desde) : 'Del '.$f($desde).' al '.$f($hasta);
    }
}
