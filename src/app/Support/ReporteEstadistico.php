<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Reporte estadístico (/estadisticas/reporte): la presentación "Estadística"
 * que antes se armaba a mano en Canva, ahora calculada con lo que registra el
 * SICONCILIO para el mes o el rango de fechas que se elija. Lo que no sale
 * del sistema viene de App\Support\DatosReferencia.
 *
 * Cómo se cuenta cada columna (por fecha dentro del periodo):
 *   Asesorías ............... seer_asesorias (y seer_asesorias_old) por fecha.
 *   Solicitudes confirmadas . seer_general por fecha, sin Pendiente, Prevención
 *                             ni Rechazado (igual que "General por sede").
 *   Audiencias programadas .. audiencias con fecha en el periodo.
 *   Convenios ............... solicitudes con audiencia en el periodo que
 *                             terminó en "Conciliacion" (acuerdo firmado).
 *   Constancias ............. ídem con "No conciliacion".
 *   Archivadas .............. ídem con "Archivada" / "Archivada en Audiencia".
 *   Incompetencias .......... ídem con "Incompetencia".
 *   Ratificaciones .......... turnos (ratificaciones) del periodo ya
 *                             ratificados: Concluida, Concluida Pagos o
 *                             Incumplimiento.
 *   Personas atendidas ...... asesorías + solicitudes confirmadas + audiencias
 *                             programadas + ratificaciones + archivadas (como
 *                             lo reportaban las sedes).
 *   Efectividad ............. (convenios + ratificaciones) ÷ (convenios +
 *                             ratificaciones + constancias).
 *   Montos convenidos ....... conceptos del convenio (concepto_pago) de esos
 *                             convenios y ratificaciones.
 *
 * Cada oficina de apoyo pertenece a una delegación (SEDES): en la tabla por
 * delegación se suman; en las de sedes y oficinas van por separado.
 */
class ReporteEstadistico
{
    /** Delegación => su oficina de apoyo. */
    public const SEDES = ['Morelia' => 'Zitácuaro', 'Uruapan' => 'Lázaro Cárdenas', 'Zamora' => 'Sahuayo'];

    public const RATIFICADAS = ['Concluida', 'Concluida Pagos', 'Incumplimiento'];

    public const NO_CONFIRMADAS = ['Pendiente', 'Prevencion', 'Rechazado'];

    public const ARCHIVADAS = ['Archivada', 'Archivada en Audiencia'];

    /** Tiempo de resolución: etiqueta => [desde, hasta] días (null = sin tope). */
    public const RANGOS_DIAS = [
        'Menos de 5 días'  => [0, 5],
        'De 6 a 15 días'   => [6, 15],
        'De 16 a 25 días'  => [16, 25],
        'De 26 a 35 días'  => [26, 35],
        'De 36 a 45 días'  => [36, 45],
        'Más de 46 días'   => [46, null],
    ];

    public const FORMATOS = ['pdf', 'xlsx', 'png'];

    /** Diseños de las láminas (no cambian los datos ni el Excel). */
    public const DISENOS = ['tradicional' => 'Tradicional', 'nuevo' => 'Nuevo'];

    private const METRICAS = ['asesorias', 'solicitudes', 'audiencias', 'convenios', 'constancias', 'archivadas', 'incompetencias', 'ratificaciones', 'montos'];

    private const MESES = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    // ------------------------------------------------------------------ filtros

    /**
     * Periodo pedido: ?mes=2026-09 (un mes) o ?desde=…&hasta=… (rango).
     * Sin nada: de enero al último mes completo. ?diseno=nuevo cambia las láminas.
     */
    public static function filtros(Request $request): array
    {
        $datos = $request->validate([
            'mes'   => ['nullable', 'date_format:Y-m'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'diseno' => ['nullable', 'in:'.implode(',', array_keys(self::DISENOS))],
        ]);
        $diseno = ['diseno' => $datos['diseno'] ?? 'tradicional'];

        if (! empty($datos['mes'])) {
            $m = Carbon::createFromFormat('Y-m-d', $datos['mes'].'-01');

            return ['modo' => 'mes', 'mes' => $datos['mes'], 'desde' => $m->toDateString(), 'hasta' => $m->copy()->endOfMonth()->toDateString()] + $diseno;
        }

        if (! empty($datos['desde']) || ! empty($datos['hasta'])) {
            $desde = $datos['desde'] ?? $datos['hasta'];
            $hasta = $datos['hasta'] ?? $datos['desde'];
            if ($desde > $hasta) {
                throw ValidationException::withMessages(['hasta' => 'La fecha final no puede ser antes de la inicial.']);
            }

            return ['modo' => 'rango', 'mes' => null, 'desde' => $desde, 'hasta' => $hasta] + $diseno;
        }

        $fin = Carbon::today()->subMonthNoOverflow()->endOfMonth();

        return ['modo' => 'rango', 'mes' => null, 'desde' => $fin->copy()->startOfYear()->toDateString(), 'hasta' => $fin->toDateString()] + $diseno;
    }

    // ------------------------------------------------------------------ armado

    public static function armar(string $desde, string $hasta): array
    {
        $ref = fn (string $c) => DatosReferencia::valor($c);
        $corte = Carbon::parse($ref('corte')['fecha_corte'])->startOfDay();
        $finPeriodo = Carbon::parse($hasta);

        // --- Periodo elegido: por delegación, sede y oficina.
        $m = self::metricas($desde, $hasta);
        $delegaciones = [];
        $sedes = [];
        $oficinas = [];
        foreach (self::SEDES as $sede => $oficina) {
            $delegaciones[$sede] = self::fila(self::sumar([$m[$sede], $m[$oficina]]));
            $sedes[$sede] = self::fila($m[$sede]);
            $oficinas[$oficina] = self::fila($m[$oficina]);
        }

        // --- Histórico: lo capturado hasta el corte + lo del sistema desde el corte.
        $conSistema = $finPeriodo->gte($corte);
        $sis = $conSistema ? self::metricas($corte->toDateString(), $hasta) : self::metricas(null, null);
        $finHist = $conSistema ? $finPeriodo : $corte->copy()->subDay();
        $base = $ref('historico_totales');
        $sisTotal = self::sumar(array_values($sis));
        $historica = [
            'personas'    => $base['personas'] + self::fila($sisTotal)['personas'],
            'asesorias'   => $base['asesorias'] + $sisTotal['asesorias'],
            'solicitudes' => $base['solicitudes'] + $sisTotal['solicitudes'],
            'audiencias'  => $base['audiencias'] + $sisTotal['audiencias'],
            'constancias' => $base['constancias'] + $sisTotal['constancias'],
            'convenios'   => $base['convenios'] + $sisTotal['convenios'] + $sisTotal['ratificaciones'],
            'archivadas'  => $base['archivadas'] + $sisTotal['archivadas'],
            'montos'      => (float) $base['montos'] + $sisTotal['montos'],
        ];
        $historica['efectividad'] = self::porcentaje($historica['convenios'], $historica['convenios'] + $historica['constancias']);

        $histDeleg = [];
        foreach ($ref('historico_delegaciones') as $b) {
            $sede = $b['delegacion'];
            $s = isset(self::SEDES[$sede]) ? self::sumar([$sis[$sede], $sis[self::SEDES[$sede]]]) : self::cero();
            $r = [
                'solicitudes'    => (int) $b['solicitudes'] + $s['solicitudes'],
                'incompetencias' => (int) $b['incompetencias'] + $s['incompetencias'],
                'convenios'      => (int) $b['convenios'] + $s['convenios'],
                'ratificaciones' => (int) $b['ratificaciones'] + $s['ratificaciones'],
                'constancias'    => (int) $b['constancias'] + $s['constancias'],
                'archivadas'     => (int) $b['archivadas'] + $s['archivadas'],
            ];
            $histDeleg[$sede] = $r + ['efectividad' => self::porcentaje($r['convenios'] + $r['ratificaciones'], $r['convenios'] + $r['ratificaciones'] + $r['constancias'])];
        }
        $histDelegTotal = [];
        foreach (['solicitudes', 'incompetencias', 'convenios', 'ratificaciones', 'constancias', 'archivadas'] as $k) {
            $histDelegTotal[$k] = array_sum(array_column($histDeleg, $k));
        }
        $histDelegTotal['efectividad'] = self::porcentaje($histDelegTotal['convenios'] + $histDelegTotal['ratificaciones'], $histDelegTotal['convenios'] + $histDelegTotal['ratificaciones'] + $histDelegTotal['constancias']);

        // --- Audiencias por conciliador (por año).
        $conciliadores = max(1, (int) ($ref('conciliadores_ccl')['total'] ?? 12));
        $porAnio = [];
        foreach ($ref('audiencias_anio') as $a) {
            if ($a['anio']) {
                $porAnio[(int) $a['anio']] = ['etiqueta' => $a['etiqueta'] ?: (string) $a['anio'], 'total' => (int) $a['audiencias']];
            }
        }
        if ($conSistema) {
            foreach (self::audienciasPorAnio($corte->toDateString(), $hasta) as $anio => $n) {
                $porAnio[$anio] ??= ['etiqueta' => (string) $anio, 'total' => 0];
                $porAnio[$anio]['total'] += $n;
            }
        }
        ksort($porAnio);
        foreach ($porAnio as &$a) {
            $a['promedio'] = (int) round($a['total'] / $conciliadores);
        }
        unset($a);

        $tiempos = self::tiempos($desde, $hasta);
        $periodo = self::textoPeriodo($desde, $hasta);
        $historicoTexto = trim(($ref('corte')['inicio_texto'] ?? '').'-'.self::MESES[$finHist->month].' '.$finHist->year, '-');

        return [
            'desde'          => $desde,
            'hasta'          => $hasta,
            'periodo'        => $periodo,
            'historicoTexto' => $historicoTexto,
            'corte'          => $corte->toDateString(),
            'delegaciones'   => $delegaciones,
            'delegacionesTotal' => self::fila(self::sumar(array_map(fn ($s, $o) => self::sumar([$m[$s], $m[$o]]), array_keys(self::SEDES), self::SEDES))),
            'sedes'          => $sedes,
            'sedesTotal'     => self::fila(self::sumar(array_map(fn ($s) => $m[$s], array_keys(self::SEDES)))),
            'oficinas'       => $oficinas,
            'oficinasTotal'  => self::fila(self::sumar(array_map(fn ($o) => $m[$o], array_values(self::SEDES)))),
            'historica'      => $historica,
            'historicoDelegaciones' => $histDeleg,
            'historicoDelegacionesTotal' => $histDelegTotal,
            'conciliadores'  => $conciliadores,
            'audienciasAnio' => $porAnio,
            'audienciasHistoricas' => $historica['audiencias'],
            'audienciasPromedio'   => (int) round($historica['audiencias'] / $conciliadores),
            'tiempos'        => $tiempos,
            'externos'       => self::externos(),
            'archivo'        => 'Reporte estadístico '.$periodo,
        ];
    }

    // ------------------------------------------------------------------ consultas

    /** Cifras del periodo por sede/oficina (las 6), en ceros si no hay. */
    private static function metricas(?string $desde, ?string $hasta): array
    {
        $r = [];
        foreach (self::SEDES as $s => $o) {
            $r[$s] = self::cero();
            $r[$o] = self::cero();
        }
        if ($desde === null) {
            return $r;
        }

        $sumar = function ($filas, array $campos) use (&$r) {
            foreach ($filas as $f) {
                $clave = self::clave($f->del);
                if ($clave === null) {
                    continue;
                }
                foreach ($campos as $c) {
                    $r[$clave][$c] += $c === 'montos' ? (float) $f->$c : (int) $f->$c;
                }
            }
        };

        // Asesorías (la tabla vieja tiene las de antes de septiembre 2025).
        foreach (['seer_asesorias', 'seer_asesorias_old'] as $tabla) {
            if (Schema::hasTable($tabla)) {
                $sumar(DB::table($tabla)->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])
                    ->groupBy('delegacion')->selectRaw('delegacion as del, COUNT(*) as asesorias')->get(), ['asesorias']);
            }
        }

        // Solicitudes confirmadas.
        $sumar(DB::table('seer_general')->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])
            ->whereNotIn('estatus', self::NO_CONFIRMADAS)
            ->groupBy('delegacion')->selectRaw('delegacion as del, COUNT(*) as solicitudes')->get(), ['solicitudes']);

        // Audiencias y su resultado.
        $arch = "'".implode("','", self::ARCHIVADAS)."'";
        $sumar(DB::table('audiencias as a')->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->whereBetween('a.fecha', [$desde, $hasta.' 23:59:59'])
            ->whereNotNull('a.estatus')->where('a.estatus', '<>', '')
            ->groupBy('g.delegacion')
            ->selectRaw("g.delegacion as del,
                COUNT(DISTINCT a.id) as audiencias,
                COUNT(DISTINCT CASE WHEN a.estatus = 'Conciliacion' THEN g.id END) as convenios,
                COUNT(DISTINCT CASE WHEN a.estatus = 'No conciliacion' THEN g.id END) as constancias,
                COUNT(DISTINCT CASE WHEN a.estatus IN ($arch) THEN g.id END) as archivadas,
                COUNT(DISTINCT CASE WHEN a.estatus = 'Incompetencia' OR g.estatus = 'Incompetencia' THEN g.id END) as incompetencias")
            ->get(), ['audiencias', 'convenios', 'constancias', 'archivadas', 'incompetencias']);

        // Montos de los convenios del periodo.
        $conConvenio = DB::table('audiencias')->select('id_solicitud')
            ->where('estatus', 'Conciliacion')->whereBetween('fecha', [$desde, $hasta.' 23:59:59']);
        $sumar(DB::table('concepto_pago as c')->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->where('c.tipo_pago', 'Audiencia')->whereIn('c.id_solicitud', $conConvenio)
            ->groupBy('g.delegacion')->selectRaw('g.delegacion as del, SUM(c.monto) as montos')->get(), ['montos']);

        // Ratificaciones y sus montos.
        $sumar(DB::table('turnos')->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])->whereIn('estatus', self::RATIFICADAS)
            ->groupBy('delegacion')->selectRaw('delegacion as del, COUNT(*) as ratificaciones')->get(), ['ratificaciones']);
        $ratificadas = DB::table('turnos')->select('id')->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])->whereIn('estatus', self::RATIFICADAS);
        $sumar(DB::table('concepto_pago as c')->join('turnos as t', 't.id', '=', 'c.id_solicitud')
            ->where('c.tipo_pago', 'Ratificacion')->whereIn('c.id_solicitud', $ratificadas)
            ->groupBy('t.delegacion')->selectRaw('t.delegacion as del, SUM(c.monto) as montos')->get(), ['montos']);

        return $r;
    }

    /** Audiencias por año (para la lámina de conciliadores). */
    private static function audienciasPorAnio(string $desde, string $hasta): array
    {
        return DB::table('audiencias')->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])
            ->whereNotNull('estatus')->where('estatus', '<>', '')
            ->selectRaw('SUBSTR(fecha, 1, 4) as anio, COUNT(*) as n')->groupByRaw('SUBSTR(fecha, 1, 4)')
            ->pluck('n', 'anio')->mapWithKeys(fn ($n, $a) => [(int) $a => (int) $n])->all();
    }

    /** Días que tardan las ratificaciones y los casos de conciliación. */
    private static function tiempos(string $desde, string $hasta): array
    {
        $vacio = array_fill_keys(array_keys(self::RANGOS_DIAS), 0);
        $rat = $vacio;
        $der = $vacio;

        // Ratificación: del registro a que se ratifica.
        DB::table('turnos')->whereBetween('fecha', [$desde, $hasta.' 23:59:59'])->whereIn('estatus', self::RATIFICADAS)
            ->select(['created_at', 'fecha', 'fecha_conclucion'])->orderBy('id')
            ->chunk(2000, function ($filas) use (&$rat) {
                foreach ($filas as $f) {
                    if ($f->created_at) {
                        $rat[self::rango(self::dias(substr($f->created_at, 0, 10), $f->fecha_conclucion ?: $f->fecha))]++;
                    }
                }
            });

        // Derivados de conciliación: de la confirmación a que se concluye.
        DB::table('seer_general')->whereBetween('fecha_terminacion', [$desde, $hasta.' 23:59:59'])
            ->whereNotNull('fecha_confirmacion')->whereNotIn('estatus', self::NO_CONFIRMADAS)
            ->select(['fecha_confirmacion', 'fecha_terminacion'])->orderBy('id')
            ->chunk(2000, function ($filas) use (&$der) {
                foreach ($filas as $f) {
                    $der[self::rango(self::dias($f->fecha_confirmacion, $f->fecha_terminacion))]++;
                }
            });

        $pct = fn (array $a) => array_map(fn ($n) => array_sum($a) ? round($n * 100 / array_sum($a), 1) : 0.0, $a);
        $hasta35 = fn (array $a) => array_sum(array_slice($a, 0, 4));

        $grafica = fn (array $conteo, array $porcentajes) => GraficaSvg::barras(
            array_keys(self::RANGOS_DIAS),
            [['nombre' => 'Casos', 'color' => '#4A6163', 'valores' => array_values($conteo)]],
            ['ancho' => 565, 'alto' => 300, 'valor' => fn ($v, $i) => $v > 0 ? number_format(array_values($porcentajes)[$i], 1).'%' : '', 'etiquetas_chicas' => true]
        );

        return [
            'graficaRatificaciones' => $grafica($rat, $pct($rat)),
            'graficaDerivados'      => $grafica($der, $pct($der)),
            'ratificaciones'    => $rat,
            'ratificacionesPct' => $pct($rat),
            'derivados'         => $der,
            'derivadosPct'      => $pct($der),
            'derivados35'       => array_sum($der) ? (int) round($hasta35($der) * 100 / array_sum($der)) : null,
            'ratificaciones5'   => array_sum($rat) ? (int) round(reset($rat) * 100 / array_sum($rat)) : null,
        ];
    }

    // ------------------------------------------------------------------ datos externos

    private static function externos(): array
    {
        $ref = fn (string $c) => DatosReferencia::valor($c);

        $jlca = collect($ref('jlca_ccl'))->sortBy('anio')->values();
        $pres = collect($ref('presupuesto'))->sortBy('anio')->values();
        $ultimo = $pres->last();
        $previo = $pres->count() > 1 ? $pres[$pres->count() - 2] : null;

        $estados = collect($ref('estados'))->map(fn ($e) => $e + [
            'pea_conciliador' => ($e['actuales'] ?? 0) > 0 && $e['pea'] ? (int) round($e['pea'] / $e['actuales']) : null,
        ]);
        $tercera = $estados->where('tercera', true)->values();

        $poblacion = collect($ref('poblacion_sedes'))->keyBy('sede');
        $textos = $ref('textos');

        $gris = '#829A9C';
        $verde = '#4A6163';
        $azul = '#7FB1B3';

        return [
            'jlca' => $jlca,
            'graficaJlca' => GraficaSvg::lineas(
                $jlca->pluck('anio')->map(fn ($a) => (string) $a)->all(),
                [
                    ['nombre' => 'JLCA', 'color' => '#8A9E9F', 'valores' => $jlca->pluck('jlca')->all()],
                    ['nombre' => 'CCLMICH', 'color' => '#4A4E6E', 'valores' => $jlca->pluck('ccl')->all()],
                ],
                ['ancho' => 560, 'alto' => 400, 'eje' => fn ($v) => (string) $v]
            ),
            'presupuesto' => $pres,
            'presupuestoDiferencia' => $ultimo && $ultimo['ccl'] ? (int) round(($ultimo['jlca'] / $ultimo['ccl'] - 1) * 100) : null,
            'presupuestoUltimo' => $ultimo,
            'presupuestoAumento' => $ultimo && $previo && $previo['ccl'] ? round(($ultimo['ccl'] / $previo['ccl'] - 1) * 100, 1) : null,
            'graficaPresupuesto' => GraficaSvg::barras(
                ['Junta Local (JLCA)', 'Centro (CCL)'],
                $pres->values()->map(fn ($p, $i) => [
                    'nombre' => (string) $p['anio'],
                    'color' => [$azul, $verde, '#A6A6A6', $gris, '#CEA845'][$i % 5],
                    'valores' => [$p['jlca'], $p['ccl']],
                ])->all(),
                ['ancho' => 750, 'alto' => 400, 'eje' => fn ($v) => '$'.number_format($v / 1e6).' M', 'valor' => fn ($v) => '$'.number_format($v), 'leyenda' => true, 'grueso_max' => 96, 'tam_valor' => 12]
            ),
            'tercera' => $tercera,
            'estados' => $estados->sortByDesc('pea_conciliador')->values(),
            'graficaCrecimiento' => GraficaSvg::lineas(
                $tercera->pluck('estado')->all(),
                [
                    ['nombre' => 'Inicio', 'color' => $azul, 'valores' => $tercera->pluck('inicio')->all()],
                    ['nombre' => 'Actual', 'color' => $verde, 'valores' => $tercera->pluck('actuales')->all()],
                ],
                ['ancho' => 700, 'alto' => 464, 'rotar' => true, 'destacar' => 'Michoacán', 'valor' => null, 'punto' => 3.5, 'grosor' => 2.5]
            ),
            'graficaPea' => GraficaSvg::barras(
                ($pea = $tercera->sortByDesc('pea')->values())->pluck('estado')->all(),
                [['nombre' => 'PEA', 'color' => $verde, 'valores' => $pea->pluck('pea')->all()]],
                ['ancho' => 700, 'alto' => 412, 'rotar' => true, 'destacar' => 'Michoacán', 'valor' => null]
            ),
            'graficaPeaConciliador' => GraficaSvg::barras(
                ($pc = $tercera->sortByDesc('pea_conciliador')->values())->pluck('estado')->all(),
                [['nombre' => 'PEA por conciliador (miles)', 'color' => $verde, 'valores' => $pc->map(fn ($e) => $e['pea_conciliador'] !== null ? round($e['pea_conciliador'] / 1000) : null)->all()]],
                ['ancho' => 700, 'alto' => 412, 'rotar' => true, 'destacar' => 'Michoacán', 'valor' => null]
            ),
            'poblacion' => $poblacion,
            'textos' => $textos,
        ];
    }

    // ------------------------------------------------------------------ apoyo

    private static function cero(): array
    {
        return array_fill_keys(self::METRICAS, 0);
    }

    private static function sumar(array $filas): array
    {
        $t = self::cero();
        foreach ($filas as $f) {
            foreach (self::METRICAS as $k) {
                $t[$k] += $f[$k] ?? 0;
            }
        }

        return $t;
    }

    /** Agrega las columnas calculadas a una fila de métricas. */
    private static function fila(array $m): array
    {
        $convRat = $m['convenios'] + $m['ratificaciones'];

        return $m + [
            'personas'    => $m['asesorias'] + $m['solicitudes'] + $m['audiencias'] + $m['ratificaciones'] + $m['archivadas'],
            'conv_rat'    => $convRat,
            'efectividad' => self::porcentaje($convRat, $convRat + $m['constancias']),
        ];
    }

    private static function porcentaje(float $parte, float $total): ?float
    {
        return $total > 0 ? round($parte * 100 / $total, 4) : null;
    }

    /** Nombre de sede tal como va en el reporte (las capturas varían en acentos). */
    public static function clave(?string $delegacion): ?string
    {
        $s = Str::of((string) $delegacion)->ascii()->lower()->toString();

        return match (true) {
            str_contains($s, 'morelia')  => 'Morelia',
            str_contains($s, 'zitac')    => 'Zitácuaro',
            str_contains($s, 'uruapan')  => 'Uruapan',
            str_contains($s, 'lazaro')   => 'Lázaro Cárdenas',
            str_contains($s, 'zamora')   => 'Zamora',
            str_contains($s, 'sahuayo')  => 'Sahuayo',
            default => null,
        };
    }

    private static function dias(?string $de, ?string $a): int
    {
        if (! $de || ! $a) {
            return 0;
        }

        return max(0, (int) Carbon::parse(substr($de, 0, 10))->diffInDays(Carbon::parse(substr($a, 0, 10)), false));
    }

    private static function rango(int $dias): string
    {
        foreach (self::RANGOS_DIAS as $et => [$min, $max]) {
            if ($dias >= $min && ($max === null || $dias <= $max)) {
                return $et;
            }
        }

        return array_key_last(self::RANGOS_DIAS);
    }

    /** "Enero-Septiembre 2026", "Octubre 2026" o "3 de octubre al 15 de noviembre de 2026". */
    public static function textoPeriodo(string $desde, string $hasta): string
    {
        $d = Carbon::parse($desde);
        $h = Carbon::parse($hasta);
        $mesesCompletos = $d->day === 1 && $h->isSameDay($h->copy()->endOfMonth());

        if ($mesesCompletos) {
            if ($d->isSameMonth($h)) {
                return self::MESES[$d->month].' '.$d->year;
            }

            return $d->year === $h->year
                ? self::MESES[$d->month].'-'.self::MESES[$h->month].' '.$h->year
                : self::MESES[$d->month].' '.$d->year.'-'.self::MESES[$h->month].' '.$h->year;
        }

        $mes = fn (Carbon $c) => mb_strtolower(self::MESES[$c->month]);
        if ($d->isSameDay($h)) {
            return $d->day.' de '.$mes($d).' de '.$d->year;
        }

        return $d->year === $h->year
            ? $d->day.' de '.$mes($d).' al '.$h->day.' de '.$mes($h).' de '.$h->year
            : $d->day.' de '.$mes($d).' de '.$d->year.' al '.$h->day.' de '.$mes($h).' de '.$h->year;
    }
}
