<?php

namespace App\Support;

use App\Http\Controllers\RecepcionController;
use App\Models\Modulo;
use App\Models\Poder;
use App\Models\RatificacionLote;
use App\Models\RatificacionLoteTrabajador;
use App\Models\Recepcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Agenda de una ratificación múltiple: una cita de 15 min por trabajador en
 * los horarios de ratificación de la sede (Administración → Horarios), a lo
 * más RatificacionMultiple::MAXIMO_DIAS días hábiles desde la fecha que
 * propuso la empresa, para no saturar a los auxiliares.
 *
 *  - Hasta POR_HORARIO trabajadores del lote a la misma hora, cada uno con
 *    un auxiliar distinto, sin pasar del cupo del horario.
 *  - Usa los mismos módulos que las citas en línea (AsignadorModulos): que
 *    reciban ratificaciones, sin pausa, sin día bloqueado, fuera de comida y
 *    sin otra cita a esa hora.
 *  - Si caben en menos días, se reparten parejo en los que hagan falta.
 *
 * proponer() sólo calcula; crear() lo vuelve a calcular con candado (para
 * que dos empresas no tomen el mismo lugar) y guarda el lote y las citas.
 */
class AgendaRatificacionMultiple
{
    public const POR_HORARIO = 3;

    /** Hasta cuántos días naturales se buscan los días hábiles. */
    private const BUSCAR_DIAS = 21;

    private const TRAMITE = 'Ratificación';

    /**
     * @return array{citas: array<int, array{fecha: string, hora: string, modulo: Modulo}>, dias: string[], lugares: int}
     *         'lugares' es cuántos cabían en los días encontrados (para el aviso si no alcanzan).
     */
    public static function proponer(string $sede, string $desde, int $n): array
    {
        $config = HorarioCitas::config($sede, self::TRAMITE);
        $horas = HorarioCitas::horarios($config);
        $inicio = Carbon::parse($desde)->max(Carbon::tomorrow());
        $recepcion = new RecepcionController();

        // Lugares libres de cada día hábil con cupo, hasta juntar MAXIMO_DIAS días.
        $dias = [];
        for ($d = 0; $d < self::BUSCAR_DIAS && count($dias) < RatificacionMultiple::MAXIMO_DIAS; $d++) {
            $fecha = $inicio->copy()->addDays($d);
            if ($fecha->isWeekend()) {
                continue;
            }
            $f = $fecha->toDateString();
            $modulos = self::modulos($sede, $f);
            $lugares = [];
            $carga = []; // citas del lote por persona ese día: se reparten entre los auxiliares
            foreach ($horas as $hora) {
                if ($modulos->isEmpty() || ! $recepcion->turnoSlotDisponible($sede, self::TRAMITE, $f, $hora, 'No', false)) {
                    continue;
                }
                $ocupadas = HorarioCitas::citasQueOcupan($sede, self::TRAMITE, false)->where('fecha', $f)->where('hora', $hora)->count();
                $libres = $modulos->filter(fn (Modulo $m) => ! $m->enComida($hora) && AsignadorModulos::choque($m, $f, $hora) === null)
                    ->unique(fn (Modulo $m) => $m->user_id)
                    ->sortBy(fn (Modulo $m) => [$carga[$m->user_id] ?? 0, $m->recibe(self::TRAMITE) ? 0 : 1])->values();
                $cupo = min(self::POR_HORARIO, max(0, HorarioCitas::capacidad($config, $hora) - $ocupadas), $libres->count());
                foreach ($libres->take($cupo) as $m) {
                    $lugares[] = ['fecha' => $f, 'hora' => $hora, 'modulo' => $m];
                    $carga[$m->user_id] = ($carga[$m->user_id] ?? 0) + 1;
                }
            }
            if ($lugares) {
                $dias[$f] = $lugares;
            }
        }

        $capacidades = array_map('count', $dias);
        // Los menos días que alcanzan; si ni con todos alcanza, se usan todos.
        $usar = count($dias);
        for ($k = 1, $suma = 0; $k <= count($dias); $k++) {
            $suma += array_values($capacidades)[$k - 1];
            if ($suma >= $n) {
                $usar = $k;
                break;
            }
        }

        $citas = [];
        $restantes = $n;
        foreach (array_slice($dias, 0, $usar, true) as $i => $lugares) {
            $quedanDias = $usar - count(array_unique(array_column($citas, 'fecha')));
            $cuota = min(count($lugares), (int) ceil($restantes / max(1, $quedanDias)));
            foreach (array_slice($lugares, 0, $cuota) as $l) {
                $citas[] = $l;
            }
            $restantes -= $cuota;
        }

        return [
            'citas'   => array_slice($citas, 0, $n),
            'dias'    => array_keys(array_slice($dias, 0, $usar, true)),
            'lugares' => array_sum(array_slice($capacidades, 0, $usar)),
        ];
    }

    /** Módulos que pueden recibir una ratificación en línea ese día. */
    private static function modulos(string $sede, string $fecha): Collection
    {
        return AsignadorModulos::deSede($sede, true)
            ->filter(fn (Modulo $m) => AsignadorModulos::atendible($m)
                && $m->recibePor('linea')
                && ! $m->bloqueadoEl($fecha)
                && ($m->recibe(self::TRAMITE) || $m->recibeDeRespaldo(self::TRAMITE)))
            ->sortBy(fn (Modulo $m) => $m->recibe(self::TRAMITE) ? 0 : 1) // primero quien lo recibe de primera mano
            ->values();
    }

    /**
     * Guarda el lote, sus trabajadores y una cita por trabajador.
     *
     * @param  array  $solicitud  datos del formulario (delegacion, motivo, fecha_propuesta, solicitante, correo, telefono, observaciones, trabajadores_declarados)
     * @param  array  $leido      RatificacionMultiple::leer()
     * @return RatificacionLote|string el lote, o el aviso si ya no alcanzaron los lugares
     */
    public static function crear(array $solicitud, array $leido, Poder $poder, array $archivos, ?string $ip, ?string $agente)
    {
        $sede = $solicitud['delegacion'];
        $n = count($leido['trabajadores']);

        $candado = Cache::lock('ratificacion-multiple-'.$sede, 30);

        return $candado->block(20, function () use ($solicitud, $leido, $poder, $archivos, $ip, $agente, $sede, $n) {
            $agenda = self::proponer($sede, $solicitud['fecha_propuesta'], $n);
            if (count($agenda['citas']) < $n) {
                return 'Mientras revisabas se ocuparon algunos horarios y ya no caben los '.$n.' trabajadores en '.RatificacionMultiple::MAXIMO_DIAS.' días. Elige otra fecha.';
            }

            return DB::transaction(function () use ($solicitud, $leido, $poder, $archivos, $ip, $agente, $sede, $agenda) {
                $empresa = trim(implode(' ', array_filter([$poder->nombres_patronal, $poder->primer_apellido_patronal, $poder->segundo_apellido_patronal])));
                $lote = RatificacionLote::create([
                    'folio'                   => self::folioLote($sede),
                    'delegacion'              => $sede,
                    'poder_folio'             => (string) $poder->idAbogado,
                    'empresa'                 => mb_substr($empresa ?: $leido['encabezado']['empresa'], 0, 255),
                    'solicitante'             => mb_substr($solicitud['solicitante'], 0, 150),
                    'motivo'                  => $solicitud['motivo'],
                    'fecha_propuesta'         => $solicitud['fecha_propuesta'],
                    'domicilio'               => $leido['encabezado']['domicilio'] ?: null,
                    'archivo'                 => $archivos['excel'],
                    'archivo_nombre'          => mb_substr($archivos['excel_nombre'], 0, 255),
                    'constancia_fiscal'       => $archivos['constancia'] ?? null,
                    'declaracion_at'          => now(),
                    'correo'                  => $solicitud['correo'],
                    'telefono'                => $solicitud['telefono'],
                    'trabajadores'            => count($leido['trabajadores']),
                    'trabajadores_declarados' => $solicitud['trabajadores_declarados'],
                    'observaciones'           => $solicitud['observaciones'] ?: null,
                    'monto_total'             => array_sum(array_column($leido['trabajadores'], 'neto')),
                    'estatus'                 => 'agendado',
                    'ip'                      => $ip,
                ]);

                $curpFolio = Curp::normalizar($poder->reprecentante === 'Si' ? $poder->curp_representante : $poder->curp_patronal);
                $consecutivo = (int) (Recepcion::where('delegacion', $sede)->max('consecutivo') ?? 0);

                foreach ($leido['trabajadores'] as $i => $t) {
                    $lugar = $agenda['citas'][$i];
                    $partes = $t['nombre_partes'];

                    $cita = Recepcion::create(array_filter([
                        'consecutivo'    => ++$consecutivo,
                        'solicitante'    => mb_substr(mb_strtoupper($solicitud['solicitante']), 0, 100),
                        'auxiliar'       => $lugar['modulo']->user_id,
                        'lugar_auxiliar' => mb_substr($lugar['modulo']->nombre, 0, 40),
                        'modulo_id'      => $lugar['modulo']->id,
                        'origen'         => 'linea',
                        'tipo'           => 'Ratificación',
                        'tipo_caso'      => 'Ninguno',
                        'fecha'          => $lugar['fecha'],
                        'hora'           => $lugar['hora'],
                        'hora_fin'       => Carbon::parse($lugar['hora'])->addMinutes(RatificacionMultiple::MINUTOS_CITA)->format('H:i:s'),
                        'delegacion'     => $sede,
                        'estatus'        => 'pendiente',
                        'exepcion'       => 'No',
                        'orientacion'    => 'No',
                        'vulnerables'    => 'No aplica',
                        'correo'         => $solicitud['correo'],
                        'telefono'       => $solicitud['telefono'],
                        'observaciones'  => 'Ratificación múltiple '.$lote->folio.' · trabajador '.$t['numero'].' de '.count($leido['trabajadores']),
                        'calidad'        => 'empleador',
                        'poder_folio'    => $poder->idAbogado,
                        'curp'           => strlen($curpFolio) === 18 ? $curpFolio : null,
                        'citado'         => json_encode($partes, JSON_UNESCAPED_UNICODE),
                        'ip'             => LimiteCitas::migrado() ? $ip : null,
                        'user_agent'     => LimiteCitas::migrado() ? LimiteCitas::agente($agente) : null,
                    ], fn ($v) => $v !== null));

                    RatificacionLoteTrabajador::create([
                        'lote_id'             => $lote->id,
                        'numero'              => $t['numero'],
                        'nombre_completo'     => $t['nombre_completo'],
                        'nombre'              => $partes['nombre'] ?? null,
                        'primer_apellido'     => $partes['primer_apellido'] ?? null,
                        'segundo_apellido'    => $partes['segundo_apellido'] ?? null,
                        'puesto'              => $t['puesto'],
                        'salario_diario'      => $t['salario_diario'],
                        'fecha_ingreso'       => $t['fecha_ingreso'],
                        'fecha_termino'       => $t['fecha_termino'],
                        'aguinaldo_dias'      => $t['aguinaldo_dias'],
                        'otras'               => $t['otras'] ?: null,
                        'total'               => $t['total'],
                        'impuesto'            => $t['impuesto'],
                        'neto'                => $t['neto'],
                        'minimos'             => $t['minimos'],
                        'observaciones'       => $t['observaciones'] ?: null,
                        'recepcion_id'        => $cita->id,
                    ] + $t['conceptos']);
                }

                return $lote;
            });
        });
    }

    /** Folio maestro: MOR-RM26-0001 (sede, ratificación múltiple, año, número). */
    public static function folioLote(string $sede): string
    {
        $prefijo = (FolioCita::SEDES[$sede] ?? 'CCL').'-RM'.now()->format('y');

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'INSERT INTO folios_citas (prefijo, ultimo) VALUES (?, LAST_INSERT_ID(1))
                 ON DUPLICATE KEY UPDATE ultimo = LAST_INSERT_ID(ultimo + 1)',
                [$prefijo]
            );
            $numero = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS n')->n;
        } else {
            $numero = RatificacionLote::where('folio', 'like', $prefijo.'-%')->count() + 1;
        }

        return $prefijo.'-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    /** Duplicados contra lo que ya está en el sistema con el mismo folio: aviso por trabajador. */
    public static function yaRegistrados(Poder $poder, array $trabajadores): array
    {
        $claves = [];
        // Ratificaciones de este patrón que siguen abiertas.
        foreach (DB::table('turnos')->where('idAbogado', $poder->idAbogado)->where('estatus', 'Confirmado')
            ->get(['trabajador', 'primero_trabajador', 'segundo_trabajador']) as $t) {
            $claves[RatificacionMultiple::clave(trim("$t->trabajador $t->primero_trabajador $t->segundo_trabajador"))] = 'ya tiene una ratificación abierta con este patrón';
        }
        // Citas de ratificación de este folio que todavía no llegan.
        foreach (Recepcion::where('poder_folio', $poder->idAbogado)->where('tipo', 'Ratificación')
            ->whereIn('estatus', ['pendiente', 'confirmada'])->where('fecha', '>=', now()->toDateString())->get(['citado']) as $c) {
            $nombre = CitaPatronal::nombreCitado($c);
            if ($nombre) {
                $claves[RatificacionMultiple::clave($nombre)] = 'ya tiene una cita de ratificación agendada con este folio';
            }
        }

        $avisos = [];
        foreach ($trabajadores as $i => $t) {
            if ($motivo = $claves[RatificacionMultiple::clave($t['nombre_completo'])] ?? null) {
                $avisos[$i] = 'Posible duplicado: '.$motivo.'.';
            }
        }

        return $avisos;
    }
}
