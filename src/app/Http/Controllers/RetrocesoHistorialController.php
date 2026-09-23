<?php

namespace App\Http\Controllers;

use App\Models\Retroceso;
use Illuminate\Http\Request;

class RetrocesoHistorialController extends Controller
{
    public const TIPOS = [
        'ratificacion' => 'Ratificación',
        'audiencia'    => 'Audiencia',
        'solicitud'    => 'Solicitud',
        'cumplimiento' => 'Cumplimiento',
    ];

    // Nombre legible de cada tabla; si falta alguna se muestra el nombre real.
    public const TABLAS = [
        'pago_solicitud'     => 'Pagos',
        'concepto_pago'      => 'Conceptos de pago',
        'deducciones'        => 'Deducciones',
        'seer_citados'       => 'Citados / Multas',
        'seer_conciliadores' => 'Registro del conciliador',
        'audiencias'         => 'Audiencias',
        'seer_general'       => 'Solicitud',
        'turnos'             => 'Ratificación',
    ];

    // Columnas que se muestran en la tabla resumen de registros eliminados.
    // El resto se ve en "Ver todo". Tablas no listadas muestran las primeras columnas.
    public const COLUMNAS_RESUMEN = [
        'pago_solicitud'     => ['fecha', 'monto', 'estatus', 'forma_pago', 'descripcion'],
        'concepto_pago'      => ['descripcion', 'monto'],
        'deducciones'        => ['descripcion', 'monto'],
        'seer_citados'       => ['nombre', 'primer_apellido', 'tipo_notificacion', 'estatus'],
        'seer_conciliadores' => ['numero_audiencia', 'estatus_conciliacion', 'monto', 'fecha'],
        'audiencias'         => ['numero_audiencia', 'folio_audiencia', 'fecha', 'hora', 'estatus'],
    ];

    public function index(Request $request)
    {
        $request->validate([
            'tipo'  => 'nullable|in:' . implode(',', array_keys(self::TIPOS)),
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'hasta.after_or_equal' => 'La fecha final no debe ser menor a la fecha de inicio.',
        ]);

        $query = Retroceso::withCount('detalles')->orderByDesc('id');

        if ($request->filled('nue')) {
            $query->where('NUE', 'like', '%' . trim($request->nue) . '%');
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
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

        $retrocesos = $query->paginate(20)->withQueryString();

        $usuarios = Retroceso::whereNotNull('user_id')
            ->select('user_id', 'user_nombre')
            ->distinct()
            ->orderBy('user_nombre')
            ->get();

        $tipos = self::TIPOS;

        return view('retrocesos.historial', compact('retrocesos', 'usuarios', 'tipos'));
    }

    public function show($id)
    {
        $retroceso = Retroceso::with('detalles')->findOrFail($id);

        $eliminados   = $retroceso->detalles->where('accion', 'deleted')->groupBy('tabla');
        $modificados  = $retroceso->detalles->where('accion', 'updated');
        $tipos        = self::TIPOS;
        $tablas       = self::TABLAS;
        $columnas     = self::COLUMNAS_RESUMEN;

        return view('retrocesos.detalle', compact('retroceso', 'eliminados', 'modificados', 'tipos', 'tablas', 'columnas'));
    }
}
