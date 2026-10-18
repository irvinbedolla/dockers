<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cifras de /estadisticas/notificaciones: cuántas se le asignaron a cada
 * notificador (o sede) en un periodo y cómo las concluyó. El historial de
 * notificaciones sólo usa filtrarLista() para acotar su lista por fechas.
 *
 *   asignadas   asignado_at en el periodo (antes de existir la columna se
 *               toma la fecha de registro del citado, created_at);
 *   exitosas    estatus de notificación lograda y fecha de diligencia
 *               (seer_citados.fecha) en el periodo;
 *   no exitosas estatus de diligencia sin éxito, misma fecha;
 *   otras       notificadas en audiencia o mandadas a exhorto (no las
 *               concluyó el notificador en campo);
 *   pendientes  las que tiene hoy sin concluir (no dependen del periodo).
 *
 * Recibe la consulta ya filtrada (sede, notificador, tipo, búsqueda) con
 * seer_citados como "c".
 */
class DesempenoNotificadores
{
    public const EXITOSAS    = ['Finalizado exitosamente', 'Exitosa por Instructivo', 'Notificada', 'Recibe pero no firma'];
    public const NO_EXITOSAS = ['No exitosa se constituye', 'No exitosa no se constituye', 'No notificada'];
    public const OTRAS       = ['Notificada en Audiencia', 'Exhorto'];

    /**
     * Citados con su expediente y su notificador ("c", "g" y "n"), sin los
     * expedientes que siguen en captura. Es la misma base del historial.
     */
    public static function base(): Builder
    {
        return DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('users as n', 'n.id', '=', 'c.id_notificador')
            ->where('g.estatus', '!=', 'Pendiente');
    }

    public static function conAsignacion(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('seer_citados', 'asignado_at');
    }

    /** Expresión SQL de la fecha de asignación. */
    public static function fechaAsignacion(): string
    {
        return self::conAsignacion() ? 'COALESCE(c.asignado_at, c.created_at)' : 'c.created_at';
    }

    /** [desde 00:00:00, hasta 23:59:59] o null si no hay periodo. */
    public static function rango(?string $desde, ?string $hasta): ?array
    {
        if (! $desde && ! $hasta) {
            return null;
        }

        return [
            $desde ? Carbon::parse($desde)->startOfDay()->toDateTimeString() : '1970-01-01 00:00:00',
            $hasta ? Carbon::parse($hasta)->endOfDay()->toDateTimeString() : '2999-12-31 23:59:59',
        ];
    }

    public static function resumen(Builder $base, ?array $rango): array
    {
        $fila = (clone $base)->selectRaw(...self::columnas($rango))->first();

        return self::armar($fila);
    }

    /**
     * Las mismas cifras por notificador (sólo quien tuvo movimiento).
     *
     * @return Collection<int, array>
     */
    public static function porNotificador(Builder $base, ?array $rango): Collection
    {
        [$sql, $bindings] = self::columnas($rango);

        return (clone $base)
            ->where('c.id_notificador', '>', 0)
            ->selectRaw('c.id_notificador, MAX(n.name) AS nombre, MAX(n.email) AS correo, MAX(n.delegacion) AS sede, '.$sql, $bindings)
            ->groupBy('c.id_notificador')
            ->get()
            ->map(fn ($f) => ['id' => (int) $f->id_notificador, 'nombre' => $f->nombre ?: 'Usuario #'.$f->id_notificador, 'correo' => $f->correo, 'sede' => $f->sede] + self::armar($f))
            ->filter(fn ($f) => $f['asignadas'] + $f['concluidas'] + $f['otras'] + $f['pendientes'] > 0)
            ->sortByDesc(fn ($f) => [$f['exitosas'], $f['asignadas']])
            ->values();
    }

    /**
     * Las mismas cifras por sede del expediente.
     *
     * @return Collection<int, array>
     */
    public static function porSede(Builder $base, ?array $rango): Collection
    {
        [$sql, $bindings] = self::columnas($rango);

        return (clone $base)
            ->selectRaw('g.delegacion AS sede, '.$sql, $bindings)
            ->groupBy('g.delegacion')
            ->get()
            ->map(fn ($f) => ['sede' => $f->sede ?: 'Sin sede'] + self::armar($f))
            ->filter(fn ($f) => $f['asignadas'] + $f['concluidas'] + $f['otras'] + $f['pendientes'] > 0)
            ->sortByDesc(fn ($f) => [$f['asignadas'], $f['exitosas']])
            ->values();
    }

    /**
     * Serie para la gráfica: asignadas, exitosas y no exitosas por día (o por
     * mes si el rango pasa de dos meses). Los huecos van en cero.
     *
     * @param  array{0: string, 1: string}  $rango
     * @return array{unidad: string, claves: array, etiquetas: array, asignadas: array, exitosas: array, noExitosas: array}
     */
    public static function tendencia(Builder $base, array $rango): array
    {
        $inicio = Carbon::parse($rango[0])->startOfDay();
        $fin    = Carbon::parse($rango[1])->startOfDay();
        $porMes = abs($inicio->diffInDays($fin)) > 62;
        $formato = $porMes ? '%Y-%m' : '%Y-%m-%d';

        $diligencias = (clone $base)
            ->whereBetween('c.fecha', $rango)
            ->whereIn('c.estatus', array_merge(self::EXITOSAS, self::NO_EXITOSAS))
            ->selectRaw("DATE_FORMAT(c.fecha, '{$formato}') AS clave, "
                .'SUM(CASE WHEN c.estatus IN ('.self::lista(self::EXITOSAS).') THEN 1 ELSE 0 END) AS exitosas, COUNT(*) AS total')
            ->groupBy('clave')
            ->get()
            ->keyBy('clave');

        $asignadas = (clone $base)
            ->where('c.id_notificador', '>', 0)
            ->whereRaw(self::fechaAsignacion().' BETWEEN ? AND ?', $rango)
            ->selectRaw('DATE_FORMAT('.self::fechaAsignacion().", '{$formato}') AS clave, COUNT(*) AS total")
            ->groupBy('clave')
            ->pluck('total', 'clave');

        $serie = ['unidad' => $porMes ? 'mes' : 'dia', 'claves' => [], 'etiquetas' => [], 'asignadas' => [], 'exitosas' => [], 'noExitosas' => []];
        $cursor = $porMes ? $inicio->copy()->startOfMonth() : $inicio->copy();

        while ($cursor <= $fin) {
            $clave = $cursor->format($porMes ? 'Y-m' : 'Y-m-d');
            $fila  = $diligencias[$clave] ?? null;
            $exitosas = (int) ($fila->exitosas ?? 0);

            $serie['claves'][]     = $clave;
            $serie['etiquetas'][]  = $cursor->locale('es')->isoFormat($porMes ? 'MMM YY' : 'D MMM');
            $serie['asignadas'][]  = (int) ($asignadas[$clave] ?? 0);
            $serie['exitosas'][]   = $exitosas;
            $serie['noExitosas'][] = (int) ($fila->total ?? 0) - $exitosas;

            $porMes ? $cursor->addMonthNoOverflow() : $cursor->addDay();
        }

        return $serie;
    }

    /** Filtra la lista por periodo según la fecha elegida. */
    public static function filtrarLista(Builder $consulta, ?array $rango, string $fechaDe): void
    {
        if (! $rango) {
            return;
        }

        $fechaDe === 'asignacion'
            ? $consulta->whereRaw(self::fechaAsignacion().' BETWEEN ? AND ?', $rango)
            : $consulta->whereBetween('c.fecha', $rango);
    }

    /** @return array{0: string, 1: array} */
    private static function columnas(?array $rango): array
    {
        $lista = fn (array $valores) => self::lista($valores);
        $enRango = fn (string $col) => $rango ? " AND {$col} BETWEEN ? AND ?" : '';
        $bindings = [];
        $suma = function (string $condicion, string $col) use ($enRango, $rango, &$bindings) {
            if ($rango) {
                array_push($bindings, ...$rango);
            }

            return "SUM(CASE WHEN {$condicion}".$enRango($col).' THEN 1 ELSE 0 END)';
        };

        $sql = implode(', ', [
            $suma('c.id_notificador > 0', self::fechaAsignacion()).' AS asignadas',
            $suma('c.estatus IN ('.$lista(self::EXITOSAS).')', 'c.fecha').' AS exitosas',
            $suma('c.estatus IN ('.$lista(self::NO_EXITOSAS).')', 'c.fecha').' AS no_exitosas',
            $suma('c.estatus IN ('.$lista(self::OTRAS).')', 'c.fecha').' AS otras',
            "SUM(CASE WHEN c.estatus = 'Pendiente' AND c.id_notificador > 0 THEN 1 ELSE 0 END) AS pendientes",
        ]);

        return [$sql, $bindings];
    }

    /** Lista SQL entre comillas de valores fijos (las constantes de arriba). */
    private static function lista(array $valores): string
    {
        return "'".implode("','", array_map(fn ($v) => str_replace("'", "''", $v), $valores))."'";
    }

    private static function armar(?object $f): array
    {
        $exitosas   = (int) ($f->exitosas ?? 0);
        $noExitosas = (int) ($f->no_exitosas ?? 0);
        $concluidas = $exitosas + $noExitosas;

        return [
            'asignadas'   => (int) ($f->asignadas ?? 0),
            'exitosas'    => $exitosas,
            'noExitosas'  => $noExitosas,
            'concluidas'  => $concluidas,
            'otras'       => (int) ($f->otras ?? 0),
            'pendientes'  => (int) ($f->pendientes ?? 0),
            'efectividad' => $concluidas ? round($exitosas * 100 / $concluidas, 1) : null,
        ];
    }
}
