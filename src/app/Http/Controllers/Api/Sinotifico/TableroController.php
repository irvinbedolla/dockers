<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Services\Sinotifico\TableroSupervision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Tablero de supervisión en la app (Módulo 5). */
class TableroController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'periodo' => ['nullable', 'in:'.implode(',', TableroSupervision::PERIODOS)],
            'sede' => ['nullable', 'string', 'max:40'], // fuera de su alcance se ignora
        ]);

        return response()->json(['data' => (new TableroSupervision($request->user()))->datos($datos['periodo'] ?? 'hoy', $datos['sede'] ?? null)]);
    }
}
