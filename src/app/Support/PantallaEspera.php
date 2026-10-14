<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Datos de la pantalla de sala de espera (/pantalla-{sede}).
 *
 * Reemplaza poco a poco a /pantallaMorelia y compañía. Diferencias a
 * propósito con las viejas:
 *
 *   - Es de sólo lectura. La vieja marcaba citas como "expirada" en cada
 *     recarga de la tele; aquí una cita pendiente que ya rebasó la
 *     tolerancia simplemente deja de mostrarse (ValidadorCita sigue siendo
 *     quien cambia estatus).
 *   - Lee las citas nuevas: folio con codigo (MOR-S26-0142), módulo por
 *     modulo_id y las tres citas en línea (Solicitud, Ratificación, Asesoría)
 *     junto con las de ventanilla.
 *   - No muestra nombres completos: sólo el primero y las iniciales. Es una
 *     pantalla pública.
 *
 * Qué se considera "vigente" (puede aparecer):
 *   - confirmada (la persona ya llegó y escaneó su QR) y no atendida, de las
 *     últimas HORAS_CONFIRMADA horas;
 *   - pendiente cuya hora no ha rebasado la tolerancia de llegada.
 *
 * El turno "ahora" de cada módulo es su cita vigente más temprana cuya hora
 * ya llegó (con ADELANTO minutos de margen). Así la pantalla "llama" el folio
 * en cuanto empieza su horario, como un tablero de turnos.
 */
class PantallaEspera
{
    /** slug de la URL => nombre de la sede como se guarda en la base. */
    public const SEDES = [
        'morelia'         => 'Morelia',
        'uruapan'         => 'Uruapan',
        'zamora'          => 'Zamora',
        'zitacuaro'       => 'Zitácuaro',
        'sahuayo'         => 'Sahuayo',
        'lazaro-cardenas' => 'Lázaro Cárdenas',
    ];

    /** Minutos antes de su hora en que una cita ya se muestra como "ahora". */
    private const ADELANTO = 5;

    /** Una confirmada que nadie terminó deja de mostrarse después de esto. */
    private const HORAS_CONFIRMADA = 3;

    /** Audiencias y cumplimientos que empezaron hace más de esto ya no salen. */
    private const MINUTOS_ATRAS = 60;

    /** Varias teles por sede: una sola consulta cada tantos segundos. */
    private const SEGUNDOS_CACHE = 10;

    private const PARTICULAS = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'da', 'van', 'von'];

    public static function sede(string $slug): ?string
    {
        return self::SEDES[$slug] ?? null;
    }

    public static function slug(string $sede): ?string
    {
        $slug = array_search($sede, self::SEDES, true);

        return $slug === false ? null : $slug;
    }

    /**
     * $conPruebas: incluye las citas de prueba del flujo nuevo (FlujoCita).
     * La pantalla real nunca las muestra; /pantalla-morelia?pruebas=1 sí.
     */
    public static function datos(string $sede, bool $conPruebas = false): array
    {
        return Cache::remember(
            'pantalla_espera.'.self::slug($sede).($conPruebas ? '.pruebas' : ''),
            self::SEGUNDOS_CACHE,
            fn () => self::calcular($sede, now(), $conPruebas)
        );
    }

    public static function calcular(string $sede, Carbon $ahora, bool $conPruebas = false): array
    {
        $sinPruebas = ! $conPruebas && FlujoCita::migrado();

        $hoy        = $ahora->toDateString();
        $horaAhora  = $ahora->format('H:i:s');
        $tolerancia = $ahora->copy()->subMinutes(ValidadorCita::TOLERANCIA_MINUTOS)->format('H:i:s');
        $limiteConf = $ahora->copy()->subHours(self::HORAS_CONFIRMADA)->format('H:i:s');
        $adelanto   = $ahora->copy()->addMinutes(self::ADELANTO)->format('H:i:s');

        // audiencias/pago_solicitud guardan la sede a veces sin acento.
        $variantes = array_values(array_unique([$sede, Str::ascii($sede)]));

        $citas = DB::table('recepcion as r')
            ->leftJoin('modulos as m', 'm.id', '=', 'r.modulo_id')
            ->where('r.fecha', $hoy)
            ->where('r.delegacion', $sede)
            ->when($sinPruebas, fn ($q) => $q->where('r.es_prueba', 0))
            ->where(function ($q) use ($tolerancia, $limiteConf) {
                $q->where(fn ($p) => $p->where('r.estatus', 'pendiente')->where('r.hora', '>=', $tolerancia))
                  ->orWhere(fn ($c) => $c->where('r.estatus', 'confirmada')->where('r.hora', '>=', $limiteConf));
            })
            ->orderBy('r.hora')
            ->orderBy('r.id')
            ->get([
                'r.id', 'r.codigo', 'r.consecutivo', 'r.tipo', 'r.hora', 'r.estatus',
                'r.origen', 'r.modulo_id', 'r.lugar_auxiliar', 'm.nombre as modulo_nombre',
            ]);

        $modulos = DB::table('modulos')
            ->where('delegacion', $sede)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'tramites']);

        $enModulo = [];
        $tablero  = [];

        foreach ($modulos as $modulo) {
            $suyas = $citas->where('modulo_id', $modulo->id)->values();
            $actual = $suyas->first(fn ($c) => $c->hora <= $adelanto);
            $siguiente = $suyas->first(fn ($c) => $actual === null || ($c->id !== $actual->id && $c->hora >= $actual->hora));

            if ($actual) {
                $enModulo[] = $actual->id;
            }

            $tablero[] = [
                'id'        => $modulo->id,
                'numero'    => self::numeroModulo($modulo->nombre),
                'nombre'    => self::nombreModulo($modulo->nombre),
                'tramites'  => self::tramitesModulo($modulo->tramites),
                'actual'    => $actual ? self::cita($actual) : null,
                'siguiente' => $siguiente ? self::cita($siguiente) : null,
            ];
        }

        $proximas = $citas
            ->reject(fn ($c) => in_array($c->id, $enModulo, true))
            ->where('hora', '>=', $tolerancia)
            ->take(20)
            ->map(fn ($c) => self::cita($c))
            ->values()
            ->all();

        $desde = $ahora->copy()->subMinutes(self::MINUTOS_ATRAS)->format('H:i:s');

        $audiencias = DB::table('audiencias as a')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador')
            ->where('a.fecha', $hoy)
            ->whereIn('a.delegacion', $variantes)
            ->where('a.estatus', 'Pendiente')
            ->where('a.hora', '>=', $desde)
            ->orderBy('a.hora')
            ->limit(60)
            ->get([
                'a.hora', 'a.sala', 'g.NUE', 'u.name as conciliador',
                DB::raw('(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = a.id_solicitud ORDER BY s.id LIMIT 1) AS solicitante'),
            ])
            ->map(fn ($a) => [
                'hora'        => substr((string) $a->hora, 0, 5),
                'expediente'  => (string) $a->NUE,
                'solicitante' => self::nombreCorto($a->solicitante),
                'sala'        => (string) $a->sala,
                'conciliador' => self::nombreCorto($a->conciliador, false),
            ])
            ->all();

        $cumplimientos = DB::table('pago_solicitud as p')
            ->join('seer_general as g', 'g.id', '=', 'p.id_solicitud')
            ->where('p.fecha', $hoy)
            ->whereIn('p.delegacion', $variantes)
            ->where('p.estatus', 'Pendiente')
            ->where('p.hora', '>=', $desde)
            ->orderBy('p.hora')
            ->limit(60)
            ->get([
                'p.hora', 'g.NUE', 'p.tipo_pago',
                DB::raw("COALESCE(NULLIF(p.nombre_trabajador, ''), (SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = p.id_solicitud ORDER BY s.id LIMIT 1)) AS nombre"),
            ])
            ->map(fn ($p) => [
                'hora'       => substr((string) $p->hora, 0, 5),
                'expediente' => (string) $p->NUE,
                'nombre'     => self::nombreCorto($p->nombre),
                'origen'     => $p->tipo_pago === 'Ratificacion' ? 'Ratificación' : 'Audiencia',
            ])
            ->all();

        $atendidos = DB::table('recepcion')
            ->where('fecha', $hoy)
            ->where('delegacion', $sede)
            ->when($sinPruebas, fn ($q) => $q->where('es_prueba', 0))
            ->where('estatus', 'atendido')
            ->count();

        return [
            'sede'          => $sede,
            'pruebas'       => $conPruebas,
            'generado'      => $ahora->toIso8601String(),
            'modulos'       => $tablero,
            'proximas'      => $proximas,
            'audiencias'    => $audiencias,
            'cumplimientos' => $cumplimientos,
            'cifras'        => [
                'atendidos' => $atendidos,
                'enEspera'  => $citas->where('estatus', 'confirmada')->count(),
                'porLlegar' => $citas->where('estatus', 'pendiente')->count(),
            ],
        ];
    }

    /** Lo que la pantalla necesita de una cita, sin datos personales. */
    private static function cita(object $c): array
    {
        $folio = FolioCita::mostrar($c);
        // MOR-S26-0142 → "MOR-S26-" + "0142": el consecutivo es lo que se lee de lejos.
        $corte = strrpos($folio, '-');

        return [
            'id'      => $c->id,
            'folio'   => $folio,
            'prefijo' => $corte === false ? '' : substr($folio, 0, $corte + 1),
            'numero'  => $corte === false ? $folio : substr($folio, $corte + 1),
            'tipo'    => (string) $c->tipo,
            'hora'    => substr((string) $c->hora, 0, 5),
            'estatus' => (string) $c->estatus,
            'enLinea' => $c->origen === 'linea',
            'modulo'  => $c->modulo_nombre ? self::nombreModulo($c->modulo_nombre) : trim((string) $c->lugar_auxiliar),
        ];
    }

    private static function numeroModulo(?string $nombre): string
    {
        return preg_match('/(\d+)\s*$/', (string) $nombre, $m) ? $m[1] : '';
    }

    /** "Modulo 3" se guardó sin acento. */
    private static function nombreModulo(?string $nombre): string
    {
        return preg_replace('/^m[oó]dulo\b/i', 'Módulo', trim((string) $nombre));
    }

    private static function tramitesModulo($tramites): string
    {
        $lista = is_array($tramites) ? $tramites : (json_decode((string) $tramites, true) ?: []);

        $plural = [
            'Solicitud'    => 'Solicitudes',
            'Asesoría'     => 'asesorías',
            'Ratificación' => 'Ratificaciones',
            'Cumplimiento' => 'Cumplimientos',
        ];

        $nombres = array_values(array_filter(array_map(fn ($t) => $plural[$t] ?? null, $lista)));

        if (count($nombres) > 1) {
            $ultimo = array_pop($nombres);

            return Str::ucfirst(mb_strtolower(implode(', ', $nombres).' y '.$ultimo));
        }

        return $nombres[0] ?? '';
    }

    /**
     * "MARÍA DEL ROSARIO VALLE GARCÍA" → "María V. G." (pantalla pública).
     * Con $iniciales = false sólo se acomodan mayúsculas (servidores públicos).
     */
    public static function nombreCorto(?string $nombre, bool $iniciales = true): string
    {
        $limpio = trim(preg_replace('/\s+/', ' ', (string) $nombre));

        if ($limpio === '') {
            return '';
        }

        $palabras = explode(' ', Str::title(mb_strtolower($limpio)));

        if (! $iniciales) {
            return implode(' ', array_map(
                fn ($p) => in_array(mb_strtolower($p), self::PARTICULAS, true) ? mb_strtolower($p) : $p,
                $palabras
            ));
        }

        $primero = array_shift($palabras);
        $resto   = array_values(array_filter($palabras, fn ($p) => ! in_array(mb_strtolower($p), self::PARTICULAS, true)));
        $resto   = array_slice($resto, -2);

        return trim($primero.' '.implode(' ', array_map(fn ($p) => mb_substr($p, 0, 1).'.', $resto)));
    }
}
