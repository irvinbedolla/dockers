<?php

namespace App\Http\Controllers;

use App\Models\HistorialEdicion;
use Illuminate\Http\Request;

class HistorialEdicionController extends Controller
{
    // Nombre legible de cada tabla; si falta alguna se muestra el nombre real.
    public const TABLAS = [
        'seer_general'     => 'Solicitud',
        'seer_solicitante' => 'Solicitante',
        'seer_citados'     => 'Citado',
        'seer_motivos'     => 'Motivo',
    ];

    // Columnas que identifican a un registro creado o eliminado en el resumen.
    public const COLUMNAS_RESUMEN = [
        'seer_citados' => ['nombre', 'primer_apellido', 'segundo_apellido', 'tipo_persona', 'curp', 'rfc'],
        'seer_motivos' => ['id_motivo'],
    ];

    public function index(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'hasta.after_or_equal' => 'La fecha final no debe ser menor a la fecha de inicio.',
        ]);

        $query = HistorialEdicion::withCount('detalles')->orderByDesc('id');

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

        $ediciones = $query->paginate(20)->withQueryString();

        $usuarios = HistorialEdicion::whereNotNull('user_id')
            ->select('user_id', 'user_nombre')
            ->distinct()
            ->orderBy('user_nombre')
            ->get();

        return view('administracion.historial_ediciones', compact('ediciones', 'usuarios'));
    }

    public function show($id)
    {
        $edicion = HistorialEdicion::with('detalles')->findOrFail($id);

        $modificados = $edicion->detalles->where('accion', 'updated');
        $creados     = $edicion->detalles->where('accion', 'created')->groupBy('tabla');
        $eliminados  = $edicion->detalles->where('accion', 'deleted')->groupBy('tabla');
        $tablas      = self::TABLAS;
        $columnas    = self::COLUMNAS_RESUMEN;

        return view('administracion.historial_edicion_detalle', compact('edicion', 'modificados', 'creados', 'eliminados', 'tablas', 'columnas'));
    }
}
