<?php

namespace App\Http\Controllers;

use App\Models\HistorialAdministracion;
use Illuminate\Http\Request;

class BitacoraAdministracionController extends Controller
{
    // Cada opción del menú de Administración tiene su propio historial.
    public const TIPOS = [
        'borrar_cumplimiento' => [
            'titulo'   => 'Historial de cumplimientos borrados',
            'regresar' => 'configuracion_borrar_cumpli',
        ],
        'cambio_fecha_audiencia' => [
            'titulo'   => 'Historial de cambios de fecha de audiencia',
            'regresar' => 'cambio_fecha_audiencia',
        ],
        'cambio_fecha_cumplimiento' => [
            'titulo'   => 'Historial de cambios de fecha de cumplimiento',
            'regresar' => 'cambio_fecha_cumplimiento',
        ],
    ];

    public function index(Request $request, string $tipo)
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'hasta.after_or_equal' => 'La fecha final no debe ser menor a la fecha de inicio.',
        ]);

        $query = HistorialAdministracion::where('tipo', $tipo)->orderByDesc('id');

        if ($request->filled('nue')) {
            $query->where('NUE', 'like', '%' . trim($request->nue) . '%');
        }
        if ($request->filled('usuario')) {
            $query->where('user_id', $request->usuario);
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $registros = $query->paginate(20)->withQueryString();

        $usuarios = HistorialAdministracion::where('tipo', $tipo)
            ->whereNotNull('user_id')
            ->select('user_id', 'user_nombre')
            ->distinct()
            ->orderBy('user_nombre')
            ->get();

        $config = self::TIPOS[$tipo];

        return view('administracion.historial', compact('registros', 'usuarios', 'tipo', 'config'));
    }
}
