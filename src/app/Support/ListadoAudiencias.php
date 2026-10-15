<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tabla de audiencias de Administración → Cambio de fecha en audiencia.
 *
 * Antes la pantalla arrancaba vacía y sólo se podía buscar un NUE exacto.
 * Ahora lista todas las audiencias con filtros (texto, sede, conciliador,
 * fechas) y tres vistas:
 *
 *   proximas  pendientes de hoy en adelante (la de inicio: lo que se mueve);
 *   vencidas  pendientes cuya fecha ya pasó y nadie concluyó;
 *   todas     cualquier estatus, la más reciente primero.
 *
 * $sedes limita a las sedes que puede operar quien consulta (Delegado y
 * Enlace); null = todas.
 */
class ListadoAudiencias
{
    public const VISTAS = [
        'proximas' => 'Próximas',
        'vencidas' => 'Pendientes vencidas',
        'todas'    => 'Todas',
    ];

    public const POR_PAGINA = 25;

    /** Lo que llega por GET, limpio. */
    public static function filtros(array $entrada): array
    {
        $fecha = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;

        return [
            'vista'       => array_key_exists($entrada['vista'] ?? '', self::VISTAS) ? $entrada['vista'] : 'proximas',
            'q'           => Str::limit(trim((string) ($entrada['q'] ?? '')), 80, ''),
            'sede'        => trim((string) ($entrada['sede'] ?? '')),
            'conciliador' => (int) ($entrada['conciliador'] ?? 0) ?: null,
            'desde'       => $fecha($entrada['desde'] ?? null),
            'hasta'       => $fecha($entrada['hasta'] ?? null),
        ];
    }

    /**
     * La página de la vista elegida y cuántas hay en cada vista.
     * Los conteos salen en una sola pasada y el total de la página se toma
     * de ahí (la búsqueda por texto es la parte cara).
     *
     * @return array{0: LengthAwarePaginator, 1: array<string, int>}
     */
    public static function listar(array $filtros, ?array $sedes): array
    {
        $hoy = Carbon::today()->toDateString();

        $fila = self::base($filtros, $sedes)
            ->selectRaw(
                "SUM(a.estatus = 'Pendiente' AND a.fecha >= ?) AS proximas, "
                ."SUM(a.estatus = 'Pendiente' AND a.fecha < ?) AS vencidas, COUNT(*) AS todas",
                [$hoy, $hoy]
            )
            ->first();

        $conteos = [];
        foreach (array_keys(self::VISTAS) as $vista) {
            $conteos[$vista] = (int) ($fila->{$vista} ?? 0);
        }

        $total  = $conteos[$filtros['vista']];
        $pagina = max(1, min(Paginator::resolveCurrentPage(), (int) ceil(max($total, 1) / self::POR_PAGINA)));

        $consulta = self::base($filtros, $sedes);
        self::vista($consulta, $filtros['vista']);

        $filas = $total === 0 ? collect() : $consulta
            ->select([
                'a.id', 'a.id_solicitud', 'a.fecha', 'a.hora', 'a.sala', 'a.estatus', 'a.delegacion',
                'a.id_conciliador', 'a.numero_audiencia', 'g.NUE', 'g.delegacion as sede_solicitud',
                'u.name as conciliador',
                DB::raw('(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = a.id_solicitud ORDER BY s.id LIMIT 1) AS solicitante'),
                DB::raw("(SELECT TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) FROM seer_citados c WHERE c.id_solicitud = a.id_solicitud ORDER BY c.id LIMIT 1) AS citado"),
                DB::raw("(SELECT COUNT(DISTINCT TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido))) FROM seer_citados c WHERE c.id_solicitud = a.id_solicitud) AS citados_total"),
            ])
            ->forPage($pagina, self::POR_PAGINA)
            ->get();

        $paginador = new LengthAwarePaginator($filas, $total, self::POR_PAGINA, $pagina, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

        return [$paginador->withQueryString(), $conteos];
    }

    /** Conciliadores con audiencias en las sedes que se pueden ver. */
    public static function conciliadores(?array $sedes): Collection
    {
        return DB::table('audiencias as a')
            ->join('users as u', 'u.id', '=', 'a.id_conciliador')
            ->when($sedes !== null, fn ($q) => $q->whereIn('a.delegacion', self::variantes($sedes)))
            ->distinct()
            ->orderBy('u.name')
            ->get(['u.id', 'u.name']);
    }

    /** La base lleva todos los filtros menos la vista. */
    private static function base(array $f, ?array $sedes): Builder
    {
        $consulta = DB::table('audiencias as a')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador');

        if ($sedes !== null) {
            $consulta->whereIn('a.delegacion', self::variantes($sedes));
        }
        if ($f['sede'] !== '') {
            $consulta->whereIn('a.delegacion', self::variantes([$f['sede']]));
        }
        if ($f['conciliador']) {
            $consulta->where('a.id_conciliador', $f['conciliador']);
        }
        if ($f['desde']) {
            $consulta->where('a.fecha', '>=', $f['desde']);
        }
        if ($f['hasta']) {
            $consulta->where('a.fecha', '<=', $f['hasta']);
        }
        if ($f['q'] !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $f['q']).'%';
            $consulta->where(function ($w) use ($like) {
                $w->where('g.NUE', 'like', $like)
                  ->orWhere('a.folio_audiencia', 'like', $like)
                  ->orWhere('u.name', 'like', $like)
                  ->orWhereExists(fn ($s) => $s->from('seer_solicitante as s')
                      ->whereColumn('s.id_solicitud', 'a.id_solicitud')
                      ->where('s.nombre', 'like', $like))
                  ->orWhereExists(fn ($c) => $c->from('seer_citados as c')
                      ->whereColumn('c.id_solicitud', 'a.id_solicitud')
                      ->whereRaw("CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido) LIKE ?", [$like]));
            });
        }

        return $consulta;
    }

    private static function vista(Builder $consulta, string $vista, bool $ordenar = true): void
    {
        $hoy = Carbon::today()->toDateString();

        match ($vista) {
            'proximas' => $consulta->where('a.estatus', 'Pendiente')->where('a.fecha', '>=', $hoy),
            'vencidas' => $consulta->where('a.estatus', 'Pendiente')->where('a.fecha', '<', $hoy),
            default    => null,
        };

        if (! $ordenar) {
            return;
        }

        if ($vista === 'proximas') {
            $consulta->orderBy('a.fecha')->orderBy('a.hora')->orderBy('a.id');
        } else {
            $consulta->orderByDesc('a.fecha')->orderByDesc('a.hora')->orderByDesc('a.id');
        }
    }

    /** "Zitácuaro" se guardó también sin acento. */
    private static function variantes(array $sedes): array
    {
        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }
}
