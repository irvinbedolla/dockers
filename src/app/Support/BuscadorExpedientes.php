<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Buscador de expedientes de Casos de excepción ("Buscar expedientes"): todas
 * las solicitudes ya registradas (seer_general), no sólo las de excepción.
 *
 * Filtros:
 *   q      expediente (NUE), nombre o CURP del solicitante, nombre del
 *          citado o folio de la cita;
 *   sede   una de CasosExcepcion::SEDES;
 *   caso   'excepcion' (marcada o nacida de una cita de excepción, igual que
 *          CasosExcepcion::solicitudEsCaso) u 'otros';
 *   desde / hasta  fecha de la solicitud (seer_general.fecha).
 *
 * "Ver" abre la solicitud completa, sólo consulta (excepciones.expediente).
 */
class BuscadorExpedientes
{
    public const POR_PAGINA = 20;

    public const CASOS = [
        ''          => 'Todos',
        'excepcion' => 'Sólo casos de excepción',
        'otros'     => 'Sin caso de excepción',
    ];

    /** Es caso de excepción: la solicitud marcada o su cita de origen. */
    private const ES_CASO = "(g.caso_excepcion = 'Si' OR r.exepcion = 'Si')";

    public static function buscar(array $f, int $pagina = 1): LengthAwarePaginator
    {
        $texto = trim(preg_replace('/\s+/', ' ', (string) ($f['q'] ?? '')));
        $like  = '%'.str_replace(['%', '_'], ['\%', '\_'], $texto).'%';
        $folio = $texto !== '' ? FolioCita::normalizar($texto) : null;
        $curpN = Curp::normalizar($texto);
        $curp  = strlen($curpN) === 18 ? $curpN : null;
        $sede  = (string) ($f['sede'] ?? '');
        $caso  = (string) ($f['caso'] ?? '');

        $consulta = DB::table('seer_general as g')
            ->leftJoin('recepcion as r', 'r.id', '=', 'g.recepcion_id')
            ->when($sede !== '', fn ($q) => $q->whereIn('g.delegacion', array_unique([$sede, Str::ascii($sede)])))
            ->when(! empty($f['desde']), fn ($q) => $q->where('g.fecha', '>=', $f['desde']))
            ->when(! empty($f['hasta']), fn ($q) => $q->where('g.fecha', '<=', $f['hasta']))
            ->when($caso === 'excepcion', fn ($q) => $q->whereRaw(self::ES_CASO))
            ->when($caso === 'otros', fn ($q) => $q->where(fn ($w) => $w->whereNull('g.caso_excepcion')->orWhere('g.caso_excepcion', '!=', 'Si'))
                ->where(fn ($w) => $w->whereNull('r.exepcion')->orWhere('r.exepcion', '!=', 'Si')))
            ->when($texto !== '', fn ($q) => $q->where(function ($w) use ($like, $folio, $curp) {
                $w->where('g.NUE', 'like', $like)
                  ->orWhereExists(fn ($s) => $s->selectRaw(1)->from('seer_solicitante as s')->whereColumn('s.id_solicitud', 'g.id')
                      ->where(fn ($n) => $n->where('s.nombre', 'like', $like)->when($curp, fn ($c) => $c->orWhere('s.curp', $curp))))
                  ->orWhereExists(fn ($s) => $s->selectRaw(1)->from('seer_citados as c')->whereColumn('c.id_solicitud', 'g.id')
                      ->whereRaw("TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) LIKE ?", [$like]));
                if ($curp) {
                    $w->orWhere('g.curp', $curp);
                }
                if ($folio) {
                    $w->orWhere('r.codigo', $folio);
                }
            }));

        $resultado = $consulta
            ->orderByDesc('g.fecha')->orderByDesc('g.id')
            ->select(['g.id', 'g.NUE as nue', 'g.fecha', 'g.delegacion', 'g.estatus', 'r.codigo', 'r.consecutivo', 'g.recepcion_id'])
            ->selectRaw('CASE WHEN '.self::ES_CASO.' THEN 1 ELSE 0 END AS es_caso')
            // Primer solicitante y primer citado, sólo para la página.
            ->selectRaw('(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = g.id ORDER BY s.id LIMIT 1) AS solicitante')
            ->selectRaw('(SELECT COUNT(*) FROM seer_solicitante s WHERE s.id_solicitud = g.id) AS solicitantes')
            ->selectRaw("(SELECT TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) FROM seer_citados c WHERE c.id_solicitud = g.id ORDER BY c.id LIMIT 1) AS citado")
            ->selectRaw('(SELECT COUNT(*) FROM seer_citados c WHERE c.id_solicitud = g.id) AS citados')
            ->paginate(self::POR_PAGINA, ['*'], 'page', max(1, $pagina));

        $resultado->setCollection($resultado->getCollection()->map(fn ($g) => [
            'id'           => (int) $g->id,
            'nue'          => $g->nue ?: 'Sin expediente',
            'fecha'        => $g->fecha ? Carbon::parse($g->fecha)->format('d/m/Y') : '—',
            'sede'         => (string) $g->delegacion,
            'estatus'      => SeguimientoCaso::ESTATUS[$g->estatus] ?? (string) $g->estatus,
            'solicitante'  => Str::title(mb_strtolower(trim((string) $g->solicitante))) ?: 'Sin nombre',
            'solicitantes' => (int) $g->solicitantes,
            'citado'       => Str::title(mb_strtolower(trim((string) $g->citado))) ?: '—',
            'citados'      => (int) $g->citados,
            'folio'        => ($g->codigo || $g->consecutivo) ? FolioCita::mostrar($g) : null,
            'caso'         => (bool) $g->es_caso,
            'vista'        => route('excepciones.expediente', $g->id),
        ]));

        return $resultado;
    }
}
