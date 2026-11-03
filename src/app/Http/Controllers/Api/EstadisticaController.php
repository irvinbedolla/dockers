<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeerPerGeneral;
use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\SeerAsesoria;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EstadisticaController extends Controller
{
    public function inicio(): JsonResponse
    {

        $fechaActual = Carbon::now()->toDateString();
        $fechaInicio = '2026-09-01';

        $solicitudQuery = SeerPerGeneral::whereBetween('fecha', [$fechaInicio, $fechaActual]);
        $ratificacionQuery = Turnos::whereBetween('fecha', [$fechaInicio, $fechaActual]);

        $baseConvenios = 26704; //Conevnios celebrados desde octubre 2022 - agosto 2026 (incluyendo ratificaciones)
        $conveniosSolicitud = (clone $solicitudQuery)->where('estatus', 'Conciliacion')->count();
        $conveniosRatificacion = (clone $ratificacionQuery)->where('estatus', 'Concluida')->count();
        $conveniosTotales = $baseConvenios + $conveniosSolicitud + $conveniosRatificacion;

        $baseNoConciliacion = 10247; //No conciliacion  celebrados desde octubre 2022 - agosto 2026 
        $noConciliacionSolicitud = (clone $solicitudQuery)->where('estatus', 'No conciliacion')->count();
        $noConciliacionTotal = $baseNoConciliacion + $noConciliacionSolicitud;
        
        $basePersonas = 117094; //Personas atendidas desde octubre 2022 - agosto 2026 
        $totalPersonas = $basePersonas; 

        $efectividad = $conveniosTotales / ($conveniosTotales + $noConciliacionTotal) * 100;

        $baseMonto = 883626669.85; //montos totales desde octubre 2022 - agosto 2026 
        $montos = Pagos::whereBetween('fecha', [$fechaInicio, $fechaActual])->where('estatus', 'Pagado')->sum('monto');
        $montos += $baseMonto;

        return response()->json([
            'fecha' => '2022 - ' . Carbon::now()->year,
            'personas' => $totalPersonas,
            'audiencias' => $conveniosTotales,
            'efectividad' => $efectividad,
            'monto' => $montos,
        ]);

    }
}