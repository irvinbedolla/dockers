<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Models\Sinotifico\Ruta;
use App\Services\Sinotifico\PlanificadorRutas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Ruta del día del notificador. Sólo se entrega aprobada por su supervisor. */
class RutaController extends Controller
{
    public function hoy(Request $request, PlanificadorRutas $planificador): JsonResponse
    {
        $fecha = $request->validate(['fecha' => ['nullable', 'date_format:Y-m-d']])['fecha'] ?? now()->toDateString();
        $ruta = $planificador->ruta($request->user(), $fecha);

        return response()->json([
            'data' => $ruta?->estatus === Ruta::APROBADA ? $planificador->presentar($ruta) : null,
            // Para avisarle que su supervisor la está armando o ajustando.
            'en_preparacion' => $ruta !== null && $ruta->estatus !== Ruta::APROBADA,
            'fecha' => $fecha,
        ]);
    }
}
