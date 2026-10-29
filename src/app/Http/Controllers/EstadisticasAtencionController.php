<?php

namespace App\Http\Controllers;

use App\Exports\EstadisticasAtencionExport;
use App\Support\EstadisticasAtencion;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * /estadisticas/atencion: resultados del formulario de atención del trámite
 * de conciliación (App\Support\EstadisticasAtencion), su descarga en Excel y
 * el seguimiento de las alertas.
 */
class EstadisticasAtencionController extends Controller
{
    public function index(Request $request)
    {
        return view('estadisticas.atencion.index', EstadisticasAtencion::armar(EstadisticasAtencion::filtros($request), $request->user()->name));
    }

    public function descargar(Request $request)
    {
        $datos = EstadisticasAtencion::armar(EstadisticasAtencion::filtros($request), $request->user()->name);

        return Excel::download(new EstadisticasAtencionExport($datos), $datos['archivo'].'.xlsx');
    }

    /** Marca una alerta (presión al firmar o calificación mala con comentario) como atendida. */
    public function atender(Request $request, int $id)
    {
        $datos = $request->validate(['nota' => ['nullable', 'string', 'max:1000']]);
        $ok = EstadisticasAtencion::atender($id, $datos['nota'] ?? null, $request->user()->id);

        return redirect()->to(url()->previous().'#alertas')
            ->with($ok ? 'success' : 'error', $ok ? 'Se marcó la alerta como atendida.' : 'No se encontró la alerta.');
    }
}
