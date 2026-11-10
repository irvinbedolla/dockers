<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Models\Sinotifico\Evidencia;
use App\Services\Sinotifico\RevisionDiligencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Fotos y firmas: sólo para quien capturó la diligencia o la supervisa. */
class EvidenciaController extends Controller
{
    public function show(Request $request, int $id, RevisionDiligencia $revision)
    {
        $evidencia = Evidencia::with('diligencia')->findOrFail($id);
        abort_unless($revision->puedeVer($request->user(), $evidencia->diligencia), 404);
        abort_unless(Storage::disk('local')->exists($evidencia->ruta), 404);

        return Storage::disk('local')->response($evidencia->ruta, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
