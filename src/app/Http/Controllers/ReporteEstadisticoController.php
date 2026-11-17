<?php

namespace App\Http\Controllers;

use App\Exports\ReporteEstadisticoExport;
use App\Support\DatosReferencia;
use App\Support\ReporteEstadistico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Panel estadístico → Reporte estadístico (App\Support\ReporteEstadistico) y
 * Datos de referencia (App\Support\DatosReferencia).
 */
class ReporteEstadisticoController extends Controller
{
    /** /estadisticas/reporte: las láminas en pantalla, con el periodo elegido. */
    public function index(Request $request)
    {
        $f = ReporteEstadistico::filtros($request);

        return view('estadisticas.reporte.index', ReporteEstadistico::armar($f['desde'], $f['hasta']) + ['filtros' => $f]);
    }

    /** Lo mismo en PDF, Excel o imagen (PNG, una por lámina). */
    public function descargar(Request $request)
    {
        $formato = $request->validate(['formato' => ['required', Rule::in(ReporteEstadistico::FORMATOS)]])['formato'];
        $f = ReporteEstadistico::filtros($request);
        $datos = ReporteEstadistico::armar($f['desde'], $f['hasta']) + ['filtros' => $f];

        if ($formato === 'xlsx') {
            return Excel::download(new ReporteEstadisticoExport($datos), $datos['archivo'].'.xlsx');
        }

        if ($formato === 'png') {
            return view('estadisticas.reporte.imagen', $datos + ['modo' => 'web']);
        }

        // dompdf guarda aquí las métricas de las fuentes del reporte.
        File::ensureDirectoryExists(storage_path('fonts'));

        return Pdf::loadView('estadisticas.reporte.pdf', $datos + ['modo' => 'pdf'])
            ->setPaper([0, 0, 720, 405]) // 16:9, igual que la presentación
            ->setOption('dpi', 96)
            ->download($datos['archivo'].'.pdf');
    }

    /** /estadisticas/datos-referencia: lo que no sale del sistema, editable. */
    public function referencia()
    {
        return view('estadisticas.datos_referencia', ['grupos' => DatosReferencia::paraEditar()]);
    }

    public function guardarReferencia(Request $request, string $clave)
    {
        abort_unless(array_key_exists($clave, DatosReferencia::catalogo()), 404);

        try {
            DatosReferencia::guardar($clave, $request->only(['campos', 'filas']), $request->user()->id);
        } catch (ValidationException $e) {
            // El aviso sale en la tarjeta del dato y la página vuelve a ella.
            throw $e->errorBag($clave)->redirectTo(route('datos_referencia.index').'#'.$clave);
        }

        return redirect()->to(route('datos_referencia.index').'#'.$clave)
            ->with('guardado', DatosReferencia::catalogo()[$clave]['titulo']);
    }
}
