<?php

namespace App\Http\Controllers;

use App\Models\HistorialRatificacion;
use App\Models\User;
use Illuminate\Http\Request;

class HistorialEdicionRatificacionController extends Controller
{
    // Nombre legible de cada tabla; si falta alguna se muestra el nombre real.
    public const TABLAS = [
        'turnos' => 'Ratificación',
    ];

    public const ACCIONES = [
        'created' => 'Creado',
        'updated' => 'Modificado',
        'deleted' => 'Eliminado',
    ];

    // Nombre legible de las columnas de turnos; las que falten se muestran como "Nombre columna".
    public const CAMPOS = [
        'idAbogado'               => 'Representante legal (folio)',
        'id_conciliador'          => 'Conciliador',
        'primero_trabajador'      => 'Primer apellido del trabajador',
        'segundo_trabajador'      => 'Segundo apellido del trabajador',
        'trabajador'              => 'Nombre del trabajador',
        'trabajador_curp'         => 'CURP del trabajador',
        'documentoCurp'           => 'Documento CURP',
        'tipo_identificacion'     => 'Tipo de identificación',
        'num_identificacion'      => 'Número de identificación',
        'documentoidentificacion' => 'Documento de identificación',
        'documentoCuanti'         => 'Documento de cuantificación',
        'fecha_inicio'            => 'Fecha de inicio de la relación laboral',
        'fecha_termino'           => 'Fecha de término de la relación laboral',
        'tipo_pago'               => 'Tipo de pago',
        'delegacion'              => 'Sede',
        'hora_fin'                => 'Hora fin',
        'PrimaVacacional'         => 'Prima vacacional',
        'PagoPTU'                 => 'Pago PTU',
        'PrimaAntigüedad'         => 'Prima de antigüedad',
        'estado_rat'              => 'Estado',
        'municipio_rat'           => 'Municipio',
        'tipo_vialidad'           => 'Tipo de vialidad',
        'num_ext'                 => 'Núm. exterior',
        'num_int'                 => 'Núm. interior',
        'codigo_postal'           => 'Código postal',
        'id_historial'            => 'Historial del representante',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'hasta.after_or_equal' => 'La fecha final no debe ser menor a la fecha de inicio.',
        ]);

        $query = HistorialRatificacion::with('detalles')->orderByDesc('id');

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

        $usuarios = HistorialRatificacion::whereNotNull('user_id')
            ->select('user_id', 'user_nombre')
            ->distinct()
            ->orderBy('user_nombre')
            ->get();

        return view('ratificaciones.historial_edicion', compact('ediciones', 'usuarios'));
    }

    public function show($id)
    {
        $edicion = HistorialRatificacion::with('detalles')->findOrFail($id);

        // El conciliador se guarda por id; se muestra su nombre.
        $idsConciliador = $edicion->detalles
            ->flatMap(fn ($d) => [$d->datos_antes['id_conciliador'] ?? null, $d->datos_despues['id_conciliador'] ?? null])
            ->filter()
            ->unique();
        $conciliadores = User::whereIn('id', $idsConciliador)->pluck('name', 'id');

        $tablas   = self::TABLAS;
        $acciones = self::ACCIONES;
        $campos   = self::CAMPOS;

        return view('ratificaciones.historial_edicion_detalles', compact('edicion', 'conciliadores', 'tablas', 'acciones', 'campos'));
    }
}
