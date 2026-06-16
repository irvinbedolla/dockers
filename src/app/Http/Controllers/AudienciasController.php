<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pagos;
use App\Models\Turnos;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CitasExport;
use App\Http\Controllers\SeerController;
use App\Models\Audiencias;
use App\Models\SeerCitados;
use Illuminate\Support\Facades\Auth;

class AudienciasController extends Controller
{

    public function audiencias(Request $request) {
        $sedeFiltro = $request->input('sede');
        $conciliadorFiltro = $request->input('conciliador');
        $user = auth()->user();
        $userID = Auth::user()->id;
        $userRole = Auth::user()->roles->pluck('name')->all();

        // 1. Mapeo de Sedes y Oficinas de Apoyo (Consistente con tus otros módulos)
        $mapaSedes = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];

        // Determinamos las sedes a consultar según la delegación del usuario
        $sedesAconsultar = $mapaSedes[$user->delegacion] ?? [$user->delegacion];

        // Iniciamos la consulta base
        $query = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
        ->join('users','users.id','audiencias.id_conciliador')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
        ->select('audiencias.*','seer_general.NUE','seer_solicitante.nombre','users.name');

        if($userRole[0] != "Super Usuario"){
            if ($sedeFiltro != "Todos") {
                $query->where('audiencias.delegacion', $sedeFiltro);
            }
            else{
                $query->whereIn('audiencias.delegacion', $sedesAconsultar);
            }
        }
        else{
            if ($sedeFiltro != "Todos") {
                $query->where('audiencias.delegacion', $sedeFiltro);
            }
        }
        
        if (!empty($conciliadorFiltro)) {
            $query->where('audiencias.id_conciliador', $conciliadorFiltro);
        }

        // RESTRICCIONES POR ROL (Seguridad)
        if ($userRole[0] == "Delegado" || $userRole[0] == "Enlace") {
            $sedeUsuario = Auth::user()->delegacion;
            $delegacionesPermitidas = [];
            
            if($sedeUsuario == "Morelia") $delegacionesPermitidas = ['Morelia', 'Zitácuaro'];
            elseif($sedeUsuario == "Uruapan") $delegacionesPermitidas = ['Uruapan', 'Lázaro Cárdenas'];
            elseif($sedeUsuario == "Zamora") $delegacionesPermitidas = ['Zamora', 'Sahuayo'];
            elseif($sedeUsuario == "Zitácuaro") $delegacionesPermitidas = ['Zitácuaro'];
            
            $query->whereIn('audiencias.delegacion', $delegacionesPermitidas);
        } 
        else if ($userRole[0] == "Conciliador") {
            $query->where('audiencias.id_conciliador', $userID);
        }

        $audiencias = $query->get();

        $eventos = [];
            foreach ($audiencias as $audiencia) {

                $tipo = 5;

                if ($audiencia->estatus === 'Archivada') {
                    $color = '#DA0909';
                } elseif ($audiencia->estatus === 'Pendiente') {
                    $color = '#EAE300';
                } elseif ($audiencia->estatus === 'Conciliacion') {
                    $color = '#00CE1C';
                } elseif ($audiencia->estatus === 'No conciliacion') {
                    $color = '#3D71FF';
                } elseif ($audiencia->estatus === 'Reagendada' || $audiencia->estatus === 'No conciliacion reagendada'){
                    $color = '#FFA93D';
                } else {
                    $color = '#CCCCCC';
                }

                    $citado = SeerCitados::where('id_solicitud', $audiencia->id_solicitud)->first();
                    $citadoNombre = $citado
                        ? trim($citado->nombre . " " . ($citado->primer_apellido ?? "") . " " . ($citado->segundo_apellido ?? ""))
                        : 'S/N';

                    $eventos[] = [
                        
                    'id' => $audiencia->id,
                    'id_solicitud' => $audiencia->id_solicitud,
                    'title' => $audiencia->NUE,
                    'solicitante' => $audiencia->nombre,
                    'start' => $audiencia->fecha->format('Y-m-d') . 'T' . $audiencia->hora->format('H:i:s'),
                    'extendedProps' => [
                        'solicitante' => $audiencia->nombre,
                        'citado' => $citadoNombre,
                        'audiencia_id' => $audiencia->id,
                        'id_solicitud' => $audiencia->id_solicitud,
                        'hora' => $audiencia->hora->format('h:i A'),
                        'color' => $color,
                        'numero_audiencia' => $audiencia->numero_audiencia,
                        'folio_audiencia' => $audiencia->folio_audiencia,
                        'fecha' => $audiencia->fecha->format('d/m/Y'),
                        'estatus' => $audiencia->estatus,
                        'tipo' => $audiencia->tipo,
                        'delegacion' => $audiencia->delegacion,
                        'sala' => $audiencia->sala,
                        'usuario' => $userID,
                        'tipo' => $tipo,
                        'conciliador' => $audiencia->name,
                    ]
                ];
            }
        return response()->json($eventos);
    /*    
        if ($userRole[0] == "Super Usuario" || $userRole[0] == "Administrador") {
            $audiencias = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
            ->join('users','users.id','audiencias.id_conciliador')
            ->select('audiencias.*','seer_general.NUE','seer_general.estatus','users.name')->get();

            $eventos = [];
            foreach ($audiencias as $audiencia) {

                $tipo = 5;

                if ($audiencia->estatus === 'Incompetencia') {
                    $color = '#DA0909';
                } elseif ($audiencia->estatus === 'Archivada') {
                    $color = '#EAE300';
                } elseif ($audiencia->estatus === 'Conciliación') {
                    $color = '#00CE1C';
                } elseif ($audiencia->estatus === 'No Conciliación') {
                    $color = '#00CE1C';
                }
                 else {
                    $color = '#CCCCCC';
                }

                $eventos[] = [
                    'id' => $audiencia->id,
                    'id_solicitud' => $audiencia->id_solicitud,
                    'title' => $audiencia->NUE,
                    'start' => $audiencia->fecha->format('Y-m-d') . 'T' . $audiencia->hora->format('H:i:s'),
                    'extendedProps' => [
                        'hora' => $audiencia->hora->format('h:i A'),
                        'color' => $color,
                        'numero_audiencia' => $audiencia->numero_audiencia,
                        'folio_audiencia' => $audiencia->folio_audiencia,
                        'fecha' => $audiencia->fecha->format('d/m/Y'),
                        'estatus' => $audiencia->estatus,
                        'tipo' => $audiencia->tipo,
                        'delegacion' => $audiencia->delegacion,
                        'sala' => $audiencia->sala,
                        'usuario' => $userID,
                        'tipo' => $tipo,
                        'conciliador' => $audiencia->name,
                    ]
                ];
            }

            return response()->json($eventos);
        }
        else if ($userRole[0] == "Delegado" || $userRole[0] == "Enlace") {
           
            if($sede == "Morelia"){
                $delegaciones = ['Morelia', 'Zitácuaro'];
                $audiencias = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
                ->join('users','users.id','audiencias.id_conciliador')
                ->select('audiencias.*','seer_general.NUE','seer_general.estatus','users.name')
                ->whereIn('audiencias.delegacion', $delegaciones)->get();
            }
            else if($sede == "Uruapan"){
                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                $audiencias = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
                ->join('users','users.id','audiencias.id_conciliador')
                ->select('audiencias.*','seer_general.NUE','seer_general.estatus','users.name')
                ->whereIn('audiencias.delegacion', $delegaciones)->get();
            }
            else if($sede == "Zamora"){
                $delegaciones = ['Zamora', 'Sahuayo'];
                $audiencias = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
                ->join('users','users.id','audiencias.id_conciliador')
                ->select('audiencias.*','seer_general.NUE','seer_general.estatus','users.name')
                ->whereIn('audiencias.delegacion', $delegaciones)->get();
            }

            $eventos = [];
            foreach ($audiencias as $audiencia) {

                $tipo = 5;

                if ($audiencia->estatus === 'Incompetencia') {
                    $color = '#DA0909';
                } elseif ($audiencia->estatus === 'Archivada') {
                    $color = '#EAE300';
                } elseif ($audiencia->estatus === 'Conciliación') {
                    $color = '#00CE1C';
                } elseif ($audiencia->estatus === 'No Conciliación') {
                    $color = '#00CE1C';
                }
                 else {
                    $color = '#CCCCCC';
                }

                $eventos[] = [
                    'id' => $audiencia->id,
                    'id_solicitud' => $audiencia->id_solicitud,
                    'title' => $audiencia->NUE,
                    'start' => $audiencia->fecha->format('Y-m-d') . 'T' . $audiencia->hora->format('H:i:s'),
                    'extendedProps' => [
                        'hora' => $audiencia->hora->format('h:i A'),
                        'color' => $color,
                        'numero_audiencia' => $audiencia->numero_audiencia,
                        'folio_audiencia' => $audiencia->folio_audiencia,
                        'fecha' => $audiencia->fecha->format('d/m/Y'),
                        'estatus' => $audiencia->estatus,
                        'tipo' => $audiencia->tipo,
                        'delegacion' => $audiencia->delegacion,
                        'sala' => $audiencia->sala,
                        'usuario' => $userID,
                        'tipo' => $tipo,
                        'conciliador' => $audiencia->name,
                    ]
                ];
            }

            return response()->json($eventos);
        }
        else if ($userRole[0] == "Conciliador") {
            $audiencias = Audiencias::join('seer_general','seer_general.id','audiencias.id_solicitud')
            ->join('users','users.id','audiencias.id_conciliador')
            ->select('audiencias.*','seer_general.NUE','seer_general.estatus','users.name')
            ->where('audiencias.id_conciliador',$userID)
            ->get();
            
            $eventos = [];
            foreach ($audiencias as $audiencia) {

                $tipo = 5;

                if ($audiencia->estatus === 'Incompetencia') {
                    $color = '#DA0909';
                } elseif ($audiencia->estatus === 'Archivada') {
                    $color = '#EAE300';
                } elseif ($audiencia->estatus === 'Conciliación') {
                    $color = '#00CE1C';
                } elseif ($audiencia->estatus === 'No Conciliación') {
                    $color = '#00CE1C';
                }
                 else {
                    $color = '#CCCCCC';
                }

                $eventos[] = [
                    'id' => $audiencia->id,
                    'id_solicitud' => $audiencia->id_solicitud,
                    'title' => $audiencia->NUE,
                    'start' => $audiencia->fecha->format('Y-m-d') . 'T' . $audiencia->hora->format('H:i:s'),
                    'extendedProps' => [
                        'hora' => $audiencia->hora->format('h:i A'),
                        'color' => $color,
                        'numero_audiencia' => $audiencia->numero_audiencia,
                        'folio_audiencia' => $audiencia->folio_audiencia,
                        'fecha' => $audiencia->fecha->format('d/m/Y'),
                        'estatus' => $audiencia->estatus,
                        'tipo' => $audiencia->tipo,
                        'delegacion' => $audiencia->delegacion,
                        'sala' => $audiencia->sala,
                        'usuario' => $userID,
                        'tipo' => $tipo,
                        'conciliador' => $audiencia->name,
                    ]
                ];
            }

            return response()->json($eventos);
        }
    */    
    }

    public function ratificaciones(Request $request) {
        $sedeFiltro = $request->input('sede');
        $conciliadorFiltro = $request->input('conciliador');
        $userID = Auth::user()->id;
        $userRole = Auth::user()->roles->pluck('name')->all();
        $user = auth()->user();
       
        // 1. Mapeo de Sedes y Oficinas de Apoyo (Consistente con tus otros módulos)
        $mapaSedes = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];

        // Determinamos las sedes a consultar según la delegación del usuario
        $sedesAconsultar = $mapaSedes[$user->delegacion] ?? [$user->delegacion];

        // Iniciamos la consulta base
        $query = Turnos::leftjoin('users','users.id','turnos.id_conciliador')
        ->select('turnos.*','users.name');

        if($userRole[0] != "Super Usuario"){
            if ($sedeFiltro != "Todos") {
                $query->where('turnos.delegacion', $sedeFiltro);
            }
            else{
                $query->whereIn('turnos.delegacion', $sedesAconsultar);
            }
        }
        if (!empty($conciliadorFiltro)) {
            $query->where('turnos.id_conciliador', $conciliadorFiltro);
        }
        
        // RESTRICCIONES POR ROL (Seguridad)
        if ($userRole[0] == "Delegado" || $userRole[0] == "Enlace") {
            $sedeUsuario = Auth::user()->delegacion;
            $delegacionesPermitidas = [];
            
            if($sedeUsuario == "Morelia") $delegacionesPermitidas = ['Morelia', 'Zitácuaro'];
            elseif($sedeUsuario == "Uruapan") $delegacionesPermitidas = ['Uruapan', 'Lázaro Cárdenas'];
            elseif($sedeUsuario == "Zamora") $delegacionesPermitidas = ['Zamora', 'Sahuayo'];

            $query->whereIn('turnos.delegacion', $delegacionesPermitidas);
        } 
        else if ($userRole[0] == "Conciliador") {
            $query->where('turnos.id_conciliador', $userID);
        }

        $ratificaciones = $query->get();

        $eventos = [];
        foreach ($ratificaciones as $rati) {

                $tipo = 3;

                if ($rati->estatus === 'Incumplimiento') {
                    $color = '#DA0909';
                } elseif ($rati->estatus === 'Archivada') {
                    $color = '#EAE300';
                } elseif ($rati->estatus === 'Concluida') {
                    $color = '#00CE1C';
                } elseif ($rati->estatus === 'Concluida Pagos') {
                    $color = '#00CE1C';
                } elseif ($rati->estatus === 'Confirmado') {
                    $color = '#0EB6F0';
                } else {
                    $color = '#CCCCCC';
                }

                $trabajador = $rati->trabajador." ".$rati->primero_trabajador." ".$rati->segundo_trabajador;
                $eventos[] = [
                    'id' => $rati->id,
                    'title' => $rati->empresa,
                    'solicitante' => $trabajador,
                    'start' => $rati->fecha . 'T' . $rati->hora,
                    'extendedProps' => [
                        'hora' => $rati->hora,
                        'color' => $color,
                        'folio_audiencia' => $rati->id,
                        'fecha' => $rati->fecha,
                        'estatus' => $rati->estatus,
                        'delegacion' => $rati->delegacion,
                        'usuario' => $userID,
                        'tipo' => $tipo,
                        'conciliador' => $rati->name,
                        'solicitante' => $trabajador,
                        'citado' => $rati->empresa,
                    ]
                ];
        }

        return response()->json($eventos);
    }

    public function exportarExcel()
    {
        //return Excel::download(new CitasExport, 'citas.xlsx');
        return Excel::download(new CitasExport, 'pagos.xlsx');
    }

}