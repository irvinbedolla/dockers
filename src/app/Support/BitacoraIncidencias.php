<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Incidencias: todo lo que se regresó, corrigió o borró a mano en un
 * expediente, con quién lo hizo, cuándo y por qué. Junta en una sola
 * bitácora lo que el sistema ya guardaba por separado:
 *
 *   retrocesos                retrocesos de ratificación, solicitud, audiencia
 *                             y cumplimiento (App\Services\RetrocesoRecorder);
 *   historial_administracion  cambios de fecha de audiencia y de cumplimiento,
 *                             cumplimientos borrados y expedientes marcados como
 *                             incidencia (App\Services\BitacoraAdministracionRecorder);
 *   historial_ratificaciones  ediciones de ratificaciones con su motivo
 *                             (App\Services\HistorialRatificacionRecorder).
 *
 * Sólo lectura. Lo que se registre después en esas tablas aparece solo.
 */
class BitacoraIncidencias
{
    public const AREAS = [
        'ratificacion' => 'Ratificaciones',
        'solicitud'    => 'Solicitudes',
        'audiencia'    => 'Audiencias',
        'cumplimiento' => 'Cumplimientos',
    ];

    public const COLORES = [
        'ratificacion' => '#496163',
        'solicitud'    => '#CEA845',
        'audiencia'    => '#5B7DB1',
        'cumplimiento' => '#B4532A',
    ];

    public const ACCIONES = [
        'retroceso'                 => 'Retroceso',
        'edicion_ratificacion'      => 'Edición de ratificación',
        'cambio_fecha_audiencia'    => 'Cambio de fecha de audiencia',
        'cambio_fecha_cumplimiento' => 'Cambio de fecha de cumplimiento',
        'borrar_cumplimiento'       => 'Cumplimiento borrado',
        'marcar_incidencia'         => 'Marcado como incidencia',
        'desmarcar_incidencia'      => 'Se quitó la incidencia',
    ];

    public const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    /** Todas las incidencias en una sola consulta (subconsulta "i"). */
    public static function base(): Builder
    {
        $retrocesos = DB::table('retrocesos')->selectRaw(
            "'retroceso' as origen, id as origen_id, tipo as area, 'retroceso' as accion, NUE, delegacion,
             estatus_previo, estatus_nuevo, motivo, user_id, user_nombre, created_at,
             entidad_tipo, entidad_id, NULL as datos_antes, NULL as datos_despues"
        );

        $bitacora = DB::table('historial_administracion')
            ->whereIn('tipo', ['cambio_fecha_audiencia', 'cambio_fecha_cumplimiento', 'borrar_cumplimiento', 'marcar_incidencia', 'desmarcar_incidencia'])
            ->selectRaw(
                "'bitacora', id,
                 CASE WHEN tipo = 'cambio_fecha_audiencia' THEN 'audiencia'
                      WHEN tipo IN ('cambio_fecha_cumplimiento', 'borrar_cumplimiento') THEN 'cumplimiento'
                      WHEN tabla = 'turnos' THEN 'ratificacion'
                      ELSE 'solicitud' END,
                 tipo, NUE, delegacion, NULL, NULL, motivo, user_id, user_nombre, created_at,
                 tabla, registro_id, datos_antes, datos_despues"
            );

        $ediciones = DB::table('historial_ratificaciones')->selectRaw(
            "'edicion', id, 'ratificacion', 'edicion_ratificacion', NUE, delegacion, estatus, NULL, motivo,
             user_id, user_nombre, created_at, 'turnos', ratificacion_id, NULL, NULL"
        );

        return DB::query()->fromSub($retrocesos->unionAll($bitacora)->unionAll($ediciones), 'i');
    }

    /** Filtros que no son de fecha: área, acción, usuario, sede y búsqueda. */
    public static function filtrar(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['area'] ?? null, fn ($q, $v) => $q->where('area', $v))
            ->when($f['accion'] ?? null, fn ($q, $v) => $q->where('accion', $v))
            ->when($f['usuario'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($f['sede'] ?? null, fn ($q, $v) => $q->whereIn('delegacion', array_values(array_unique([$v, Str::ascii($v)]))))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('NUE', 'like', "%{$v}%")->orWhere('motivo', 'like', "%{$v}%")));
    }

    /**
     * El periodo de la tabla y los cortes: el día, el mes o el año elegidos.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public static function periodo(array $f): array
    {
        if (! empty($f['dia'])) {
            $d = Carbon::parse($f['dia']);

            return [$d->copy()->startOfDay(), $d->copy()->endOfDay(), Str::ucfirst($d->locale('es')->isoFormat('dddd D [de] MMMM YYYY'))];
        }
        if (! empty($f['mes'])) {
            $d = Carbon::create($f['anio'], $f['mes'], 1);

            return [$d->copy()->startOfMonth(), $d->copy()->endOfMonth(), Str::ucfirst($d->locale('es')->isoFormat('MMMM YYYY'))];
        }
        $d = Carbon::create($f['anio'], 1, 1);

        return [$d->copy()->startOfYear(), $d->copy()->endOfYear(), 'Año '.$f['anio']];
    }

    /** Hoy, este mes y este año (con los filtros que no son de fecha). */
    public static function cifras(array $f = []): array
    {
        $ahora = now();
        $r = self::filtrar(self::base(), $f)
            ->where('created_at', '>=', $ahora->copy()->startOfYear())
            ->selectRaw('COUNT(*) as anio, COALESCE(SUM(created_at >= ?), 0) as mes, COALESCE(SUM(created_at >= ?), 0) as hoy, COUNT(DISTINCT user_id) as usuarios', [
                $ahora->copy()->startOfMonth(), $ahora->copy()->startOfDay(),
            ])
            ->first();

        return [
            'hoy'      => (int) ($r->hoy ?? 0),
            'mes'      => (int) ($r->mes ?? 0),
            'anio'     => (int) ($r->anio ?? 0),
            'usuarios' => (int) ($r->usuarios ?? 0),
        ];
    }

    /** Columnas por mes del año, apiladas por área. */
    public static function porMes(int $anio, array $f): array
    {
        $filas = self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [Carbon::create($anio, 1, 1)->startOfDay(), Carbon::create($anio, 12, 31)->endOfDay()])
            ->selectRaw('MONTH(created_at) as n, area, COUNT(*) as total')
            ->groupByRaw('MONTH(created_at), area')
            ->get();

        $columnas = [];
        foreach (range(1, 12) as $m) {
            $columnas[$m] = ['etiqueta' => self::MESES[$m - 1], 'total' => 0, 'areas' => []];
        }

        return self::apilar($columnas, $filas);
    }

    /** Columnas por día del mes, apiladas por área. */
    public static function porDia(int $anio, int $mes, array $f): array
    {
        $inicio = Carbon::create($anio, $mes, 1);
        $filas  = self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [$inicio->copy()->startOfDay(), $inicio->copy()->endOfMonth()])
            ->selectRaw('DAY(created_at) as n, area, COUNT(*) as total')
            ->groupByRaw('DAY(created_at), area')
            ->get();

        $columnas = [];
        foreach (range(1, $inicio->daysInMonth) as $d) {
            $columnas[$d] = ['etiqueta' => (string) $d, 'total' => 0, 'areas' => []];
        }

        return self::apilar($columnas, $filas);
    }

    /** Totales por área en el periodo. */
    public static function porArea(Carbon $desde, Carbon $hasta, array $f): array
    {
        $conteo = self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [$desde, $hasta])
            ->selectRaw('area, COUNT(*) as total')
            ->groupBy('area')
            ->pluck('total', 'area');

        return collect(self::AREAS)->map(fn ($nombre, $clave) => ['clave' => $clave, 'nombre' => $nombre, 'total' => (int) ($conteo[$clave] ?? 0)])->values()->all();
    }

    /** Totales por tipo de movimiento en el periodo, de más a menos. */
    public static function porAccion(Carbon $desde, Carbon $hasta, array $f): array
    {
        return self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [$desde, $hasta])
            ->selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['clave' => $r->accion, 'nombre' => self::ACCIONES[$r->accion] ?? $r->accion, 'total' => (int) $r->total])
            ->all();
    }

    /** Quién las hace: una fila por usuario con su desglose por área. */
    public static function porUsuario(Carbon $desde, Carbon $hasta, array $f): array
    {
        $filas = self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [$desde, $hasta])
            ->selectRaw('user_id, MAX(user_nombre) as nombre, area, COUNT(*) as total, MAX(created_at) as ultima')
            ->groupBy('user_id', 'area')
            ->get();

        return $filas->groupBy(fn ($r) => (string) $r->user_id)
            ->map(fn ($rs) => [
                'id'     => $rs->first()->user_id,
                'nombre' => $rs->first()->nombre ?: 'Sin usuario',
                'total'  => (int) $rs->sum('total'),
                'areas'  => $rs->pluck('total', 'area')->map(fn ($n) => (int) $n)->all(),
                'ultima' => Carbon::parse($rs->max('ultima')),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /** Por sede en el periodo (sin acentos se juntan con su nombre correcto). */
    public static function porSede(Carbon $desde, Carbon $hasta, array $f): array
    {
        $sedes = DB::table('sedes')->orderBy('id')->pluck('nombre');
        $conteo = [];
        foreach (self::filtrar(self::base(), $f)->whereBetween('created_at', [$desde, $hasta])->selectRaw('delegacion, COUNT(*) as total')->groupBy('delegacion')->get() as $r) {
            $nombre = $sedes->first(fn ($s) => Str::lower(Str::ascii($s)) === Str::lower(Str::ascii((string) $r->delegacion))) ?? ($r->delegacion ?: 'Sin sede');
            $conteo[$nombre] = ($conteo[$nombre] ?? 0) + (int) $r->total;
        }
        arsort($conteo);

        return $conteo;
    }

    /** La bitácora del periodo, de la más reciente a la más antigua. */
    public static function registro(Carbon $desde, Carbon $hasta, array $f, int $porPagina = 25): LengthAwarePaginator
    {
        return self::consultaRegistro($desde, $hasta, $f)->paginate($porPagina)->withQueryString();
    }

    public static function consultaRegistro(Carbon $desde, Carbon $hasta, array $f): Builder
    {
        return self::filtrar(self::base(), $f)
            ->whereBetween('created_at', [$desde, $hasta])
            ->select('*')
            ->orderByDesc('created_at')
            ->orderByDesc('origen_id');
    }

    /** Usuarios que alguna vez registraron una incidencia (para el filtro). */
    public static function usuarios(): array
    {
        return self::base()
            ->whereNotNull('user_id')
            ->selectRaw('user_id, MAX(user_nombre) as nombre')
            ->groupBy('user_id')
            ->orderBy('nombre')
            ->pluck('nombre', 'user_id')
            ->all();
    }

    /** Expedientes que hoy siguen marcados como incidencia (fuera de reportes). */
    public static function marcadas(): array
    {
        return [
            'solicitudes'     => DB::table('seer_general')->where('incidencia', 1)->count(),
            'ratificaciones'  => DB::table('turnos')->where('incidencia', 1)->count(),
        ];
    }

    /** Qué cambió, para los movimientos de la bitácora de administración. */
    public static function cambios(object $fila): array
    {
        $antes   = is_string($fila->datos_antes ?? null) ? json_decode($fila->datos_antes, true) : null;
        $despues = is_string($fila->datos_despues ?? null) ? json_decode($fila->datos_despues, true) : null;
        if (! is_array($despues)) {
            return [];
        }

        $cambios = [];
        foreach ($despues as $columna => $valor) {
            if (in_array($columna, ['updated_at', 'created_at', 'incidencia', 'motivo_incidencia'], true) || ($antes[$columna] ?? null) == $valor) {
                continue;
            }
            $cambios[] = Str::of($columna)->replace('_', ' ')->ucfirst().': '.self::valor($antes[$columna] ?? null).' → '.self::valor($valor);
        }

        return $cambios;
    }

    /** Ruta a la línea de tiempo del caso, si es una solicitud. */
    public static function urlCaso(object $fila): ?string
    {
        return $fila->entidad_tipo === 'seer_general' && \Illuminate\Support\Facades\Route::has('seguimiento.ver')
            ? route('seguimiento.ver', 's'.$fila->entidad_id)
            : null;
    }

    private static function valor($v): string
    {
        if ($v === null || $v === '') {
            return '—';
        }
        if (is_bool($v)) {
            return $v ? 'sí' : 'no';
        }

        return Str::limit((string) $v, 40);
    }

    private static function apilar(array $columnas, $filas): array
    {
        foreach ($filas as $r) {
            if (isset($columnas[$r->n])) {
                $columnas[$r->n]['areas'][$r->area] = (int) $r->total;
                $columnas[$r->n]['total'] += (int) $r->total;
            }
        }

        return ['columnas' => $columnas, 'maximo' => max(1, ...array_column($columnas, 'total'))];
    }
}
