<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pagos;
use App\Models\Recepcion;
use App\Models\Turnos;
use App\Models\User;
use App\Models\PermisosConciliador;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CitasExport;
use App\Models\SeerCitados;
use App\Models\SeerPerGeneral;
use App\Models\SeerSolicitante;

class CitaController extends Controller
{
    public function create()
    {
        return view('/calendar.crear_cita', [
            //'usuario' => User::all(),
            'estados' => Cita::ESTADOS,
            'tipos' => Cita::TIPOS
        ]);
    }

    //Agrenda de pagos en Ratificacion
    public function citas(Request $request) {
        $user = auth()->user();
        $rol = $user->roles->first()->name ?? '';
        $id_usuario = $user->id;
        $sede_usuario = $user->delegacion;

        // 1. Iniciamos la consulta base con el Join y los campos necesarios
        $query = Pagos::join('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.tipo_pago', 'Ratificacion')
            ->select(
                'turnos.NUE',
                'pago_solicitud.descripcion',
                'pago_solicitud.hora',
                'pago_solicitud.fecha',
                'turnos.empresa',
                'pago_solicitud.nombre_trabajador',
                'pago_solicitud.estatus',
                'pago_solicitud.monto',
                'pago_solicitud.observaciones',
                'pago_solicitud.id',
                'pago_solicitud.id_solicitud',
                'turnos.id_conciliador'
            )
            ->selectRaw("CONCAT(turnos.trabajador, ' ', turnos.primero_trabajador, ' ', turnos.segundo_trabajador) as nombre_completo");

        // 2. Aplicamos filtros de seguridad por ROL
        if (!in_array($rol, ['Super Usuario', 'Administrador'])) {
            
            $delegacionesPermitidas = [$sede_usuario];

            if (in_array($rol, ['Conciliador', 'Delegado', 'Enlace', 'Auxiliar'])) {
                $mapaSedes = [
                    'Morelia' => ['Morelia', 'Zitácuaro'],
                    'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                    'Zamora'  => ['Zamora', 'Sahuayo'],
                ];

                // Verificamos permiso especial "Ambos"
                $permisoAmbos = PermisosConciliador::where('id_conciliador', $id_usuario)
                    ->where('tipo', 'Ambos')
                    ->exists();

                if ($permisoAmbos && isset($mapaSedes[$sede_usuario])) {
                    $delegacionesPermitidas = $mapaSedes[$sede_usuario];
                }
            }

            // Aplicamos el filtro de delegación (ya sea una o varias)
            // Nota: Especificamos la tabla para evitar ambigüedad en el join
            $query->whereIn('pago_solicitud.delegacion', $delegacionesPermitidas);
        }

        // 3. Filtros opcionales desde el Request (Selects de la interfaz)
        $query->when($request->sede, function ($q) use ($request) {
            return $q->where('pago_solicitud.delegacion', $request->sede);
        });

        $query->when($request->conciliador, function ($q) use ($request) {
            return $q->where('turnos.id_conciliador', $request->conciliador);
        });

        // 4. Ejecución final
        $recepciones = $query->get();

        $tipo = 8;

        $eventos = [];
        foreach ($recepciones as $pago) {
            $turno = $pago->turno;
   
            $empresa_turno = $pago ? $pago->empresa : "S/E";
            $nombre_trabajador = $pago ? $pago->nombre_completo : "S/N";
            $tipo = 6;
            $conciliadorName = User::where('id', $pago->id_conciliador)->value('name') ?: '';

            if ($pago->estatus === 'Pendiente') {
                $color = '#EAE300';
            } elseif ($pago->estatus === 'Pagado') {
                $color = '#00CE1C';
            } elseif ($pago->estatus === 'Incomparecencia trabajador') {
                $color = '#FF2C2C';
            } else {
                $color = '#CCCCCC';
            }

            $eventos[] = [
                'id' => $pago->id,
                'title' => $pago->NUE,
                'start' => $pago->fecha->format('Y-m-d') . 'T' . $pago->hora->format('H:i:s'),
                'extendedProps' => [
                    'solicitante' => $nombre_trabajador,
                    'citado' => $empresa_turno,
                    'nue' => $pago->NUE,
                    'descripcion' => $pago->descripcion,
                    'hora' => $pago->hora->format('h:i A'),
                    'color' => $color,
                    'fecha' => $pago->fecha->format('d/m/Y'),
                    'empresa' => $empresa_turno,
                    'trabajador' => $nombre_trabajador,
                    'conciliador' => $conciliadorName,
                    'estatus' => $pago->estatus,
                    'monto' => $pago->monto,
                    'observaciones' => $pago->observaciones,
                    'tipo' => $tipo
                ]
            ];
        }

        return response()->json($eventos);

    }

    //Esta funcion se carga por defecto en el calendario
    public function pagos(Request $request) {
        $user = auth()->user();
        $rol = $user->roles->first()->name ?? '';
        $tipo = 6;

        $query = Pagos::with(['pagoturnos', 'conciliadorUser'])
            ->where('tipo_pago', 'Audiencia');

        if (!in_array($rol, ['Super Usuario', 'Administrador'])) {
            $delegacionesPermitidas = [$user->delegacion];

            if (in_array($rol, ['Conciliador', 'Delegado', 'Enlace'])) {
                $mapaSedes = [
                    'Morelia' => ['Morelia', 'Zitácuaro'],
                    'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                    'Zamora'  => ['Zamora', 'Sahuayo'],
                ];

                $permisoAmbos = PermisosConciliador::where('id_conciliador', $user->id)
                    ->where('tipo', 'Ambos')
                    ->exists();

                if ($permisoAmbos && isset($mapaSedes[$user->delegacion])) {
                    $delegacionesPermitidas = $mapaSedes[$user->delegacion];
                }
            }
            $query->whereIn('delegacion', $delegacionesPermitidas);
        }

        $query->when($request->sede, function ($q) use ($request) {
            return $q->where('delegacion', $request->sede);
        });

        $query->when($request->filled('conciliador'), function ($q) use ($request) {
            // Especificamos la tabla 'pago_solicitud' para ir a lo seguro
            return $q->where('pago_solicitud.id_conciliador', $request->conciliador);
        });

        $pagos = $query->get();
        $id_solicitudes = $pagos->pluck('id_solicitud')->toArray();
        

        $mapaColores = [
            'Pendiente'                  => '#EAE300',
            'Pagado'                     => '#00CE1C',
            'Incomparecencia trabajador' => '#FF2C2C',
        ];

        
        $eventos = $pagos->map(function ($pago) use ($mapaColores) {
            $color = $mapaColores[$pago->estatus] ?? '#CCCCCC';
            $solicitante = SeerSolicitante::where('id_solicitud', $pago->id_solicitud)->value('nombre');
            $citado = SeerCitados::where('id_solicitud', $pago->id_solicitud)->first();
            $citado_nombre = $citado
            ? $citado->nombre . " " . ($citado->primer_apellido ?? "") . " " . ($citado->segundo_apellido ?? "")
            : $pago->empresa_representante;
            return [
                'id' => $pago->id,
                    'title' => $pago->NUE,
                    'start' => $pago->fecha->format('Y-m-d') . 'T' . $pago->hora->format('H:i:s'),
                    'extendedProps' => [
                        'solicitante' => $solicitante ?? $pago->nombre_trabajador ??  'S/N',
                        'citado' => $citado_nombre ?? 'S/N',
                        'nue' => $pago->NUE,
                        'descripcion' => $pago->descripcion,
                        'hora' => $pago->hora->format('h:i A'),
                        'color' => $color,
                        'fecha' => $pago->fecha->format('d/m/Y'),
                        'empresa' => $pago->empresa_representante,
                        'trabajador' => $pago->nombre_trabajador,
                        'conciliador'  => $pago->conciliadorUser->name ?? 'No asignado',
                        'estatus' => $pago->estatus,
                        'monto' => $pago->monto,
                        'observaciones' => $pago->observaciones,
                        'tipo' => 6,
                    ]
            ];
        });

        return response()->json($eventos);
    }

    public function conciliadores(Request $request) {
        $user = auth()->user();
        $rol = $user->roles->first()->name ?? '';
        $tipo = 6;

        $query = Pagos::with(['pagoturnos', 'conciliadorUser'])
            ->where('tipo_pago', 'Conciliador');

        if (!in_array($rol, ['Super Usuario', 'Administrador'])) {
            $delegacionesPermitidas = [$user->delegacion];

            if (in_array($rol, ['Conciliador', 'Delegado', 'Enlace'])) {
                $mapaSedes = [
                    'Morelia' => ['Morelia', 'Zitácuaro'],
                    'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                    'Zamora'  => ['Zamora', 'Sahuayo'],
                ];

                $permisoAmbos = PermisosConciliador::where('id_conciliador', $user->id)
                    ->where('tipo', 'Ambos')
                    ->exists();

                if ($permisoAmbos && isset($mapaSedes[$user->delegacion])) {
                    $delegacionesPermitidas = $mapaSedes[$user->delegacion];
                }
            }
            $query->whereIn('delegacion', $delegacionesPermitidas);
        }

        $query->when($request->sede, function ($q) use ($request) {
            return $q->where('delegacion', $request->sede);
        });

        $query->when($request->filled('conciliador'), function ($q) use ($request) {
            // Especificamos la tabla 'pago_solicitud' para ir a lo seguro
            return $q->where('pago_solicitud.id_conciliador', $request->conciliador);
        });

        $pagos = $query->get();

        $mapaColores = [
            'Pendiente'                  => '#EAE300',
            'Pagado'                     => '#00CE1C',
            'Incomparecencia trabajador' => '#FF2C2C',
        ];

        
        $eventos = $pagos->map(function ($pago) use ($mapaColores) {
            $color = $mapaColores[$pago->estatus] ?? '#CCCCCC';

            return [
                'id' => $pago->id,
                    'title' => $pago->NUE,
                    'solicitante' => $pago->nombre_trabajador,
                    'start' => $pago->fecha->format('Y-m-d') . 'T' . $pago->hora->format('H:i:s'),
                    'extendedProps' => [
                        'solicitante' => $pago->nombre_trabajador,
                        'citado' => $pago->empresa_representante,
                        'nue' => $pago->NUE,
                        'descripcion' => $pago->descripcion,
                        'hora' => $pago->hora->format('h:i A'),
                        'color' => $color,
                        'fecha' => $pago->fecha->format('d/m/Y'),
                        'empresa' => $pago->empresa_representante,
                        'trabajador' => $pago->nombre_trabajador,
                        'conciliador'  => $pago->conciliadorUser->name ?? 'No asignado',
                        'estatus' => $pago->estatus,
                        'monto' => $pago->monto,
                        'observaciones' => $pago->observaciones,
                        'tipo' => 6,
                    ]
            ];
        });

        return response()->json($eventos);
    }

    public function exportarExcel()
    {
        //return Excel::download(new CitasExport, 'citas.xlsx');
        return Excel::download(new CitasExport, 'pagos.xlsx');
    }

}
