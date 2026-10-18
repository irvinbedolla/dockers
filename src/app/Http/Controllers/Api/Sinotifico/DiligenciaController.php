<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Services\Sinotifico\VisitasDelNotificador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lista de trabajo del notificador. Por ahora de solo lectura sobre
 * `seer_citados`; la captura de diligencias llega en el Módulo 2.
 */
class DiligenciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $servicio = new VisitasDelNotificador($request->user());
        $visitas = $servicio->pendientes();

        return response()->json([
            'data' => $visitas,
            'resumen' => [
                'visitas' => $visitas->count(),
                'citados' => $visitas->sum(fn ($v) => count($v['citados'])),
                'fuera_de_plazo' => $visitas->where('plazo.estado', 'fuera_de_plazo')->count(),
                'ultimo_dia' => $visitas->where('plazo.estado', 'ultimo_dia')->count(),
                'finalizadas_hoy' => $servicio->finalizadasHoy(),
            ],
            'generado_en' => now()->toIso8601String(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $visita = (new VisitasDelNotificador($request->user()))
            ->pendientes()
            ->first(fn ($v) => collect($v['citados'])->contains('id', $id));

        if (! $visita) {
            return response()->json(['message' => 'Esta diligencia no está asignada a tu cuenta o ya se cerró.'], 404);
        }

        return response()->json(['data' => $visita]);
    }
}
