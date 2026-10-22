<?php

namespace App\Support;

use App\Models\User;
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
 *   sede   una de las sedes que ve (sedes());
 *   caso   'excepcion' (marcada o nacida de una cita de excepción, igual que
 *          CasosExcepcion::solicitudEsCaso) u 'otros';
 *   desde / hasta  fecha de la solicitud (seer_general.fecha).
 *
 * "Ver" abre la solicitud completa, sólo consulta (excepciones.expediente).
 *
 * Super Usuario, Directivo y Conciliación, Género e Igualdad ven todas las
 * sedes; quien más tenga la pestaña (Delegado, Excepcion), sólo su sede y su
 * sede de apoyo (AgendaContexto::sedesDeRegion).
 */
class BuscadorExpedientes
{
    public const POR_PAGINA = 20;

    public const CASOS = [
        ''          => 'Todos',
        'excepcion' => 'Sólo casos de excepción',
        'otros'     => 'Sin caso de excepción',
    ];

    /** Ven los expedientes de todas las sedes. */
    public const VEN_TODAS = ['Super Usuario', 'Directivo', CasosExcepcion::ROL];

    /** Es caso de excepción: la solicitud marcada o su cita de origen. */
    private const ES_CASO = "(g.caso_excepcion = 'Si' OR r.exepcion = 'Si')";

    /** Sedes cuyos expedientes puede buscar y ver. */
    public static function sedes(?User $usuario): array
    {
        if ($usuario === null) {
            return [];
        }
        if ($usuario->hasAnyRole(self::VEN_TODAS)) {
            return CasosExcepcion::SEDES;
        }
        $region = array_map(fn ($s) => Str::lower(Str::ascii($s)), AgendaContexto::sedesDeRegion($usuario->delegacion));

        return array_values(array_filter(CasosExcepcion::SEDES, fn ($s) => in_array(Str::lower(Str::ascii($s)), $region, true)));
    }

    /** ¿Ese registro (seer_general.delegacion) es de esas sedes? Se guarda a veces sin acento. */
    public static function esDeSedes(?string $delegacion, array $sedes): bool
    {
        return in_array(Str::lower(Str::ascii((string) $delegacion)), array_map(fn ($s) => Str::lower(Str::ascii($s)), $sedes), true);
    }

    /** $sedes: las que ve quien busca (sedes()); el filtro de sede se acota a ellas. */
    public static function buscar(array $f, array $sedes, int $pagina = 1): LengthAwarePaginator
    {
        $texto = trim(preg_replace('/\s+/', ' ', (string) ($f['q'] ?? '')));
        $like  = '%'.str_replace(['%', '_'], ['\%', '\_'], $texto).'%';
        $folio = $texto !== '' ? FolioCita::normalizar($texto) : null;
        $curpN = Curp::normalizar($texto);
        $curp  = strlen($curpN) === 18 ? $curpN : null;
        $sede  = (string) ($f['sede'] ?? '');
        $caso  = (string) ($f['caso'] ?? '');

        $sedes = $sede !== '' ? array_values(array_filter($sedes, fn ($s) => $s === $sede)) : $sedes;
        $sedes = $sedes ? array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes)))) : ['__ninguna__'];

        $consulta = DB::table('seer_general as g')
            ->leftJoin('recepcion as r', 'r.id', '=', 'g.recepcion_id')
            ->whereIn('g.delegacion', $sedes)
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
