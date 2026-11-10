<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Models\Sinotifico\Diligencia;
use App\Services\Sinotifico\Razones;
use App\Services\Sinotifico\RevisionDiligencia;
use App\Services\Sinotifico\VisitasDelNotificador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Revisión de diligencias por Enlaces elegidos, Delegados, Directivos y Super Usuarios. */
class RevisionController extends Controller
{
    public function __construct(private readonly RevisionDiligencia $revision) {}

    public function index(Request $request): JsonResponse
    {
        $estatus = $request->validate([
            'estatus' => ['nullable', Rule::in([Diligencia::POR_REVISAR, Diligencia::VALIDADA, Diligencia::DEVUELTA])],
        ])['estatus'] ?? Diligencia::POR_REVISAR;

        $diligencias = $this->revision->visibles($request->user())
            ->where('estatus', $estatus)
            ->when($estatus === Diligencia::POR_REVISAR, fn ($q) => $q->oldest('capturada_at'), fn ($q) => $q->latest('revisada_at'))
            ->limit(200)
            ->get();

        $base = $this->revision->visibles($request->user());

        return response()->json([
            'data' => $diligencias->map(fn ($d) => $this->tarjeta($d))->values(),
            'conteo' => [
                'por_revisar' => (clone $base)->where('estatus', Diligencia::POR_REVISAR)->count(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $d = Diligencia::with('evidencias')->findOrFail($id);
        abort_unless($this->revision->puedeVer($request->user(), $d), 404);

        return response()->json(['data' => $this->detalle($d)]);
    }

    public function validar(Request $request, int $id): JsonResponse
    {
        $d = $this->propia($request, $id);

        return response()->json(['data' => $this->detalle($this->revision->validar($d, $request->user())->load('evidencias'))]);
    }

    public function devolver(Request $request, int $id): JsonResponse
    {
        $comentario = $request->validate(
            ['comentario' => ['required', 'string', 'min:5', 'max:2000']],
            ['comentario.required' => 'Escribe qué debe corregir el notificador.', 'comentario.min' => 'Explica un poco más qué debe corregir.'],
        )['comentario'];

        $d = $this->propia($request, $id);

        return response()->json(['data' => $this->detalle($this->revision->devolver($d, $request->user(), $comentario)->load('evidencias'))]);
    }

    /** Sólo diligencias de su alcance; quien la capturó no puede revisarla. */
    private function propia(Request $request, int $id): Diligencia
    {
        $d = $this->revision->visibles($request->user())->findOrFail($id);
        abort_if((int) $d->notificador_id === (int) $request->user()->id, 403, 'No puedes revisar una diligencia que tú capturaste.');

        return $d;
    }

    private function tarjeta(Diligencia $d): array
    {
        $citados = $this->citados($d);

        return [
            'id' => $d->id,
            'estatus' => $d->estatus,
            'resultado' => $d->resultado ?? 'Exhorto',
            'quien_atiende' => $d->quien_atiende,
            'sede' => $d->sede,
            'notificador' => DB::table('users')->where('id', $d->notificador_id)->value('name'),
            'expediente' => DB::table('seer_general')->where('id', $d->solicitud_id)->value('NUE'),
            'citado_principal' => $citados->first()['nombre'] ?? null,
            'citados' => $citados->count(),
            'domicilio' => $citados->first()['domicilio'] ?? null,
            'llegada_at' => $d->llegada_at?->toIso8601String(),
            'capturada_at' => $d->capturada_at?->toIso8601String(),
            'revisada_at' => $d->revisada_at?->toIso8601String(),
        ];
    }

    private function detalle(Diligencia $d): array
    {
        return $this->tarjeta($d) + [
            'datos' => $d->datos,
            'ubicacion' => $d->lat !== null ? ['lat' => $d->lat, 'lng' => $d->lng, 'precision' => $d->precision_m] : null,
            'lista_citados' => $this->citados($d)->values(),
            'evidencias' => $d->evidencias->map(fn ($e) => [
                'id' => $e->id,
                'tipo' => $e->tipo,
                'orden' => $e->orden,
                'tomada_at' => $e->tomada_at?->toIso8601String(),
                'ubicacion' => $e->lat !== null ? ['lat' => $e->lat, 'lng' => $e->lng] : null,
            ])->values(),
            'revisor' => $d->revisor_id ? DB::table('users')->where('id', $d->revisor_id)->value('name') : null,
            'comentario_revision' => $d->comentario_revision,
            'audiencia' => DB::table('audiencias')->where('id', DB::table('seer_citados')->whereIn('id', $d->citadoIds())->value('audiencia_id'))->first(['fecha', 'hora', 'folio_audiencia']),
        ];
    }

    private function citados(Diligencia $d)
    {
        $razones = DB::table('sinotifico_razones')->where('diligencia_id', $d->id)->get()->keyBy('citado_id');

        return DB::table('seer_citados as c')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->whereIn('c.id', $d->citadoIds())
            ->orderBy('c.id')
            ->get(['c.*', 'm.nombre as municipio', 'e.nombre as estado'])
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'nombre' => trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->primer_apellido, $c->segundo_apellido])))),
                'estatus' => $c->estatus,
                'domicilio' => VisitasDelNotificador::textoDomicilio($c),
                'razon' => $this->razon($d, $c, $razones->get($c->id)),
            ]);
    }

    /** Qué razón le toca (o le tocará al validar) y si ya se firmó. */
    private function razon(Diligencia $d, object $c, ?object $r): ?array
    {
        $estatus = $d->estatus === Diligencia::POR_REVISAR ? $d->resultado : $c->estatus;
        $doc = $d->quien_atiende === 'EXHORTO' ? null : Razones::documento((object) ['estatus' => $estatus, 'tipo_notificacion' => $c->tipo_notificacion]);
        if (! $doc) {
            return null;
        }

        return [
            'documento' => $doc[1],
            'id' => $r ? (int) $r->id : null,
            'estatus' => $r->estatus ?? null,
            'modo' => $r->modo ?? null,
            'firmada_at' => $r && $r->firmada_at ? \Carbon\Carbon::parse($r->firmada_at)->toIso8601String() : null,
        ];
    }
}
