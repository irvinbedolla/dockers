<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Models\SeerCitados;
use App\Models\Sinotifico\Diligencia;
use App\Services\Sinotifico\Razones;
use App\Services\Sinotifico\RevisionDiligencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Módulo 3: razones de notificación del notificador, su firma y los PDF. */
class RazonController extends Controller
{
    public function __construct(private readonly Razones $razones, private readonly RevisionDiligencia $revision) {}

    /** Por firmar, firmadas y observaciones (diligencias devueltas sin volver a capturar). */
    public function index(Request $request): JsonResponse
    {
        $yo = $request->user();
        $filas = DB::table('sinotifico_razones as r')
            ->join('seer_citados as c', 'c.id', '=', 'r.citado_id')
            ->leftJoin('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('sinotifico_diligencias as d', 'd.id', '=', 'r.diligencia_id')
            ->where('r.notificador_id', $yo->id)
            ->orderByRaw("case when r.estatus = 'por_firmar' then 0 else 1 end")
            ->orderByDesc('d.revisada_at')
            ->limit(300)
            ->get(['r.*', 'c.nombre', 'c.primer_apellido', 'c.segundo_apellido', 'c.estatus as estatus_citado', 'c.tipo_notificacion',
                'g.NUE as expediente', 'd.llegada_at', 'd.revisada_at']);

        return response()->json([
            'data' => [
                'razones' => $filas->map(fn ($r) => $this->razon($r))->values(),
                'observaciones' => $this->observaciones($yo->id),
            ],
            'permite_papel' => Razones::permitePapel(),
            'tiene_firma' => DB::table('sinotifico_firmas')->where('user_id', $yo->id)->exists(),
        ]);
    }

    public function pdf(Request $request, int $id)
    {
        $r = $this->buscar($id);
        $d = Diligencia::findOrFail($r->diligencia_id);
        abort_unless($this->revision->puedeVer($request->user(), $d), 404);

        return $this->enviarPdf($this->razones->pdf(SeerCitados::findOrFail($r->citado_id)), "razon-{$r->citado_id}.pdf");
    }

    /** Supervisor: la razón tal como quedaría (por revisar) o como quedó (ya revisada). */
    public function vistaPrevia(Request $request, int $id, int $citado)
    {
        $d = $this->revision->visibles($request->user())->findOrFail($id);

        return $this->enviarPdf($this->razones->vistaPrevia($d, $citado), "vista-previa-{$citado}.pdf");
    }

    public function firmar(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate([
            'modo' => ['required', Rule::in(['app', 'papel'])],
            'foto' => ['nullable', 'image', 'max:8192'],
        ]);
        $r = $this->buscar($id);
        $this->razones->firmar($r, $request->user(), $datos['modo'], $request->file('foto'));

        return response()->json(['data' => $this->razon($this->fila($id))]);
    }

    /** Foto de la razón firmada en papel. */
    public function papel(Request $request, int $id)
    {
        $r = $this->buscar($id);
        abort_unless($r->papel_ruta && $this->revision->puedeVer($request->user(), Diligencia::findOrFail($r->diligencia_id)), 404);

        return Storage::disk('local')->response($r->papel_ruta, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function verFirma(Request $request)
    {
        $ruta = DB::table('sinotifico_firmas')->where('user_id', $request->user()->id)->value('ruta');
        abort_unless($ruta && Storage::disk('local')->exists($ruta), 404);

        return Storage::disk('local')->response($ruta, null, ['Cache-Control' => 'private, no-cache']);
    }

    public function guardarFirma(Request $request): JsonResponse
    {
        $request->validate(['firma' => ['required', 'file', 'mimetypes:image/png', 'max:1024']], ['firma.required' => 'Dibuja tu firma.']);
        $this->razones->guardarFirma($request->user(), $request->file('firma'));

        return response()->json(['data' => ['tiene_firma' => true]]);
    }

    private function buscar(int $id): object
    {
        return DB::table('sinotifico_razones')->where('id', $id)->first() ?? abort(404);
    }

    private function enviarPdf(string $pdf, string $nombre)
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nombre.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function fila(int $id): object
    {
        return DB::table('sinotifico_razones as r')
            ->join('seer_citados as c', 'c.id', '=', 'r.citado_id')
            ->leftJoin('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('sinotifico_diligencias as d', 'd.id', '=', 'r.diligencia_id')
            ->where('r.id', $id)
            ->first(['r.*', 'c.nombre', 'c.primer_apellido', 'c.segundo_apellido', 'c.estatus as estatus_citado', 'c.tipo_notificacion',
                'g.NUE as expediente', 'd.llegada_at', 'd.revisada_at']);
    }

    private function razon(object $r): array
    {
        $doc = Razones::documento((object) ['estatus' => $r->estatus_citado, 'tipo_notificacion' => $r->tipo_notificacion]);

        return [
            'id' => (int) $r->id,
            'citado_id' => (int) $r->citado_id,
            'diligencia_id' => (int) $r->diligencia_id,
            'citado' => $this->nombre($r),
            'expediente' => $r->expediente,
            'resultado' => $r->estatus_citado,
            'documento' => $doc[1] ?? 'Razón de notificación',
            'estatus' => $r->estatus,
            'modo' => $r->modo,
            'firmada_at' => $r->firmada_at ? \Carbon\Carbon::parse($r->firmada_at)->toIso8601String() : null,
            'con_foto' => (bool) $r->papel_ruta,
            'llegada_at' => $r->llegada_at ? \Carbon\Carbon::parse($r->llegada_at)->toIso8601String() : null,
            'validada_at' => $r->revisada_at ? \Carbon\Carbon::parse($r->revisada_at)->toIso8601String() : null,
        ];
    }

    /** Devueltas cuyo domicilio no se ha vuelto a capturar: lo que el supervisor pidió corregir. */
    private function observaciones(int $notificadorId): array
    {
        $devueltas = Diligencia::where('notificador_id', $notificadorId)->where('estatus', Diligencia::DEVUELTA)
            ->latest('revisada_at')->limit(50)->get();

        return $devueltas->filter(function (Diligencia $d) {
            if ($d->citadoIds() === []) {
                return false;
            }
            // Ya se volvió a capturar alguno de sus citados después de devolverla.
            return ! DB::table('sinotifico_diligencia_citado as dc')
                ->join('sinotifico_diligencias as n', 'n.id', '=', 'dc.diligencia_id')
                ->whereIn('dc.citado_id', $d->citadoIds())
                ->where('n.id', '>', $d->id)
                ->exists();
        })->map(function (Diligencia $d) {
            $ids = $d->citadoIds();
            $c = DB::table('seer_citados')->whereIn('id', $ids)->orderBy('id')->first();

            return [
                'diligencia_id' => $d->id,
                'citado_id' => $ids[0] ?? null,
                'citado' => $c ? $this->nombre($c) : null,
                'citados' => count($ids),
                'expediente' => DB::table('seer_general')->where('id', $d->solicitud_id)->value('NUE'),
                'resultado' => $d->resultado ?? 'Exhorto',
                'comentario' => $d->comentario_revision,
                'revisor' => DB::table('users')->where('id', $d->revisor_id)->value('name'),
                'revisada_at' => $d->revisada_at?->toIso8601String(),
                // Si sigue pendiente se puede volver a capturar desde su visita.
                'pendiente' => $c && in_array($c->estatus, config('sinotifico.estatus_pendientes'), true)
                    && (int) $c->id_notificador === (int) $d->notificador_id,
            ];
        })->values()->all();
    }

    private function nombre(object $c): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->primer_apellido, $c->segundo_apellido]))));
    }
}
