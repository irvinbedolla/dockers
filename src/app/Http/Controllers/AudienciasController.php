<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
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
use App\Support\SemaforoAgenda;
use Illuminate\Support\Facades\DB;

class AudienciasController extends Controller
{

    public function audiencias(Request $request) {
        $sedeFiltro = $request->input('sede');
        $fecha_inicio = Carbon::parse($request->input('start'))->format('Y-m-d');
        $fecha_final = Carbon::parse($request->input('end'))->format('Y-m-d');
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
        $query = Audiencias::with('citado')->whereBetween('audiencias.fecha', [$fecha_inicio, $fecha_final])
            ->join('seer_general','seer_general.id','audiencias.id_solicitud')
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

                $color = SemaforoAgenda::audiencia($audiencia->estatus);

                if ($audiencia->citado) {
                    $citadoNombre = trim($audiencia->citado->nombre . " " . $audiencia->citado->primer_apellido . " " . $audiencia->citado->segundo_apellido);
                } else {
                    $citadoNombre = 'S/N';
                }

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

                $color = SemaforoAgenda::audiencia($audiencia->estatus);

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

                $color = SemaforoAgenda::audiencia($audiencia->estatus);

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

                $color = SemaforoAgenda::audiencia($audiencia->estatus);

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

    /**
     * Fuente de eventos de la pastilla "Solicitudes".
     *
     * Dibuja seer_general en su fecha de solicitud. No lleva hora —la tabla
     * no la guarda—, así que son eventos de día completo; el semáforo sale
     * del estatus de la solicitud, no del de sus audiencias.
     *
     * El alcance por rol y sede es el mismo que en audiencias(): la sede
     * acota a todos, y al Conciliador se le fija su propio id.
     */
    public function solicitudes(Request $request)
    {
        $sedeFiltro   = $request->input('sede');
        $fecha_inicio = Carbon::parse($request->input('start'))->format('Y-m-d');
        $fecha_final  = Carbon::parse($request->input('end'))->format('Y-m-d');
        $conciliador  = $request->input('conciliador');

        $user     = auth()->user();
        $userID   = $user->id;
        $userRole = $user->roles->pluck('name')->all();

        $mapaSedes = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];
        $sedesAconsultar = $mapaSedes[$user->delegacion] ?? [$user->delegacion];

        $primerCitado = "(SELECT SUBSTRING_INDEX(GROUP_CONCAT("
            . "TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) "
            . "ORDER BY c.id ASC SEPARATOR '|'), '|', 1) "
            . "FROM seer_citados c WHERE c.id_solicitud = seer_general.id) as citado";

        // El solicitante va por subconsulta y no por join: hay solicitudes
        // colectivas con mas de un solicitante, y con el join esa solicitud se
        // dibujaba dos veces en el calendario. La agenda es una fila por
        // solicitud, asi que se toma el primero.
        $primerSolicitante = "(SELECT s.nombre FROM seer_solicitante s "
            . "WHERE s.id_solicitud = seer_general.id ORDER BY s.id ASC LIMIT 1) as solicitante";

        $query = DB::table('seer_general')
            ->leftJoin('users', 'users.id', '=', 'seer_general.conciliador_id')
            ->whereBetween('seer_general.fecha', [$fecha_inicio, $fecha_final])
            ->whereNotNull('seer_general.fecha')
            ->select([
                'seer_general.id',
                'seer_general.NUE',
                'seer_general.fecha',
                'seer_general.estatus',
                'seer_general.delegacion',
                'users.name as conciliador',
                DB::raw($primerSolicitante),
                DB::raw($primerCitado),
            ]);

        if (($userRole[0] ?? '') !== 'Super Usuario') {
            $sedeFiltro !== 'Todos' && $sedeFiltro !== null
                ? $query->where('seer_general.delegacion', $sedeFiltro)
                : $query->whereIn('seer_general.delegacion', $sedesAconsultar);
        } elseif ($sedeFiltro !== 'Todos' && $sedeFiltro !== null) {
            $query->where('seer_general.delegacion', $sedeFiltro);
        }

        if (!empty($conciliador)) {
            $query->where('seer_general.conciliador_id', $conciliador);
        }

        if (in_array($userRole[0] ?? '', ['Delegado', 'Enlace'], true)) {
            $query->whereIn('seer_general.delegacion', $sedesAconsultar);
        } elseif (($userRole[0] ?? '') === 'Conciliador') {
            $query->where('seer_general.conciliador_id', $userID);
        }

        $eventos = [];

        foreach ($query->get() as $solicitud) {
            $eventos[] = [
                'id'    => $solicitud->id,
                'title' => $solicitud->NUE,
                'start' => $solicitud->fecha,
                'allDay' => true,
                'extendedProps' => [
                    'solicitante'  => $solicitud->solicitante ?: 'S/N',
                    'citado'       => $solicitud->citado ?: 'S/N',
                    'conciliador'  => $solicitud->conciliador ?: 'No asignado',
                    'nue'          => $solicitud->NUE,
                    'id_solicitud' => $solicitud->id,
                    'hora'         => 'Todo el día',
                    'fecha'        => Carbon::parse($solicitud->fecha)->format('d/m/Y'),
                    'estatus'      => $solicitud->estatus,
                    'delegacion'   => $solicitud->delegacion,
                    'color'        => SemaforoAgenda::solicitud($solicitud->estatus),
                    'tipo'         => 7,
                ],
            ];
        }

        return response()->json($eventos);
    }

    public function ratificaciones(Request $request) {
        $sedeFiltro = $request->input('sede');
        $fecha_inicio = Carbon::parse($request->input('start'))->format('Y-m-d');
        $fecha_final = Carbon::parse($request->input('end'))->format('Y-m-d');
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
        $query = Turnos::whereBetween('turnos.fecha', [$fecha_inicio, $fecha_final])->leftjoin('users','users.id','turnos.id_conciliador')
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

                $color = SemaforoAgenda::ratificacion($rati->estatus);

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