<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth, Hash;
use App\Models\Recepcion;
use App\Models\SeerPerGeneral;
use App\Models\Turnos;
use App\Models\TurnoDisponible;
use Carbon\Carbon;
use App\Models\DiasInhabiles;
use App\Models\SeerCasosExcepcion;
use App\Models\SeerChatR;
use App\Models\Estados;
use App\Models\Municipios;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RecepcionController extends Controller
{   
    public function citas(){
        return view('turnos');
    }

    public function turnos_guardar(Request $request){
        // Validamos que los campos esenciales existan en la petición
        if (!$request->has('delegacion') || empty($request->input('delegacion'))) {
            return redirect()->back()->with('error', 'Es necesario seleccionar una sede (delegación).');
        }
        if (!$request->has('tipo') || empty($request->input('tipo'))) {
            return redirect()->back()->with('error', 'Es necesario seleccionar el Tipo de Trámite.');
        }
        if (!$request->filled('fecha_turno') || !$request->filled('hora_turno')) {
            return redirect()->back()->with('error', 'Es necesario seleccionar la fecha y el horario del turno en el calendario.');
        }

        $data = $request->all();
        $sede = $data["delegacion"];
        $tipoTramite = $data["tipo"];
        $fecha_asignada_str = $data["fecha_turno"];
        $hora_turno = $data["hora_turno"];
        $id_auxiliar = auth()->user()->id;
        $hora_fin =$hora_turno;
        $lista_solicitudes = [5,3919,65,2817,2664,2814,70,61];
        $lista_ratificaciones = [4,6,9,32,28,2663,74,731,154,44,47];
        $todas_direcciones = [
            'Morelia' => 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260, MORELIA, MICHOACÁN',
            'Zitácuaro' => '5 DE MAYO NTE. 3, CENTRO, C.P. 61500, ZITÁCUARO, MICHOACÁN.',
            'Zamora' => 'JUSTO SIERRA NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670, ZAMORA DE HIDALGO, MICHOACÁN.',
            'Sahuayo' => 'AV. UNIVERSIDAD SUR NO. 3000, SEGUNDO PISO, EDIFICIO CENTRAL, COL. LOMAS DE UNIVERSIDAD, C.P. 59103, SAHUAYO DE MORELOS, MICHOACÁN.',
            'Uruapan' => 'NUEVO PARICUTÍN NO. 308, COL. SAN RAFAEL, C.P. 60136, URUAPAN, MICHOACÁN.',
            'Lázaro Cárdenas' => 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950, LÁZARO CÁRDENAS, MICHOACÁN.',
        ];

        if($data["excepcion"]== 'Si'){
             $hora_fin = date("H:i:s", strtotime($hora_turno . " +75 minutes"));
        }
        else{
            if($data["tipo"]=='Solicitud'){
                 $hora_fin = date("H:i:s", strtotime($hora_turno . " +40 minutes"));
            }
            else{
                 $hora_fin = date("H:i:s", strtotime($hora_turno . " +60 minutes"));
            }
        }
        // El horario seleccionado en el calendario ya no debe estar ocupado ni caer en un día/horario inhábil
        if (!$this->turnoSlotDisponible($sede, $tipoTramite, $fecha_asignada_str, $hora_turno, $data["excepcion"] ?? null)) {
            return redirect()->back()->with('error', 'El horario seleccionado ya no está disponible. Por favor selecciona otro.');
        }

        // 2. Calcular el consecutivo dinámico de acuerdo a la FECHA ASIGNADA y SEDE
        $consecutivo  = Recepcion::latest('id')->where('delegacion', $sede)->first();

        if (empty($consecutivo)) {
            $numero_consecutivo = 1;
        } else {
            $numero_consecutivo = $consecutivo["consecutivo"] + 1;
        }

        // 3. SOLUCIÓN AL ERROR: Validar si los campos múltiples vienen como array o como string
        $tipo_caso    = isset($data["tipo_caso"])    ? (is_array($data["tipo_caso"])    ? implode(',', $data["tipo_caso"])    : $data["tipo_caso"])    : null;
        $prestacionSS = isset($data["prestacionSS"]) ? (is_array($data["prestacionSS"]) ? implode(',', $data["prestacionSS"]) : $data["prestacionSS"]) : null;
        $vulnerables  = isset($data["vulnerables"])  ? (is_array($data["vulnerables"])  ? implode(',', $data["vulnerables"])  : $data["vulnerables"])  : 'Ninguno';

        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $sede)
        ->get();
        
        
        if($data["excepcion"] == "Si"){
            $modulo = "Departamento de genero e igualdad";
            $id_aux = 13;
            $direccion = $todas_direcciones[$sede];
        }
        
        else{
            $listado_auxiliares = $usuariosauxiliares->pluck('id')->toArray();
            if($tipoTramite == "Ratificación"){
                $auxiliares = array_intersect($listado_auxiliares,$lista_ratificaciones);
            }
            else{
                $auxiliares = array_intersect($listado_auxiliares,$lista_solicitudes);
            }
            if(!empty($auxiliares)){
                $random = array_rand($auxiliares);
            }
            else{
                $sedesEspeciales = ['Zamora', 'Sahuayo', 'Zitácuaro'];

                if ($tipoTramite !== "Ratificación" && in_array($sede, $sedesEspeciales)) {
                    
                    $ratificadoresSede = array_intersect($listado_auxiliares, $lista_ratificaciones);
                   
                    $auxiliaresOcupados = Recepcion::where('hora', $hora_turno)
                        ->where('fecha', $fecha_asignada_str)
                        ->whereIn('auxiliar', $listado_auxiliares)
                        ->pluck('auxiliar')
                        ->toArray();

                    
                    $auxiliares = array_diff($ratificadoresSede, $auxiliaresOcupados);

                    if (!empty($auxiliares)) {
                        $random = array_rand($auxiliares);
                    }
                    else {
                        return redirect()->route('citas')->with('error', 'No se ha podido completar tu cita. No se ha encontrado auxiliar disponible.'); 
                    }
                } else {
                    return redirect()->route('citas')->with('error', 'No se ha podido completar tu cita. No se ha encontrado auxiliar disponible.'); 
                }
                
            }
            
            //validar si hay disponibles
            
            if($sede == 'Morelia'){
                $auxiliaresOcupados = Recepcion::where('hora', $hora_turno)->where('fecha', $fecha_asignada_str)->where('delegacion', $sede)->pluck('auxiliar')->toArray();
                $disponibles = array_diff($auxiliares, $auxiliaresOcupados);
                if($hora_turno === '13:00:00') $disponibles = array_diff($disponibles, [5]);
                elseif($hora_turno === '13:30:00') $disponibles = array_diff($disponibles, [209]);
                if(!empty($disponibles)){
                    $random = array_rand($disponibles);
                }
                else{
                    return  redirect()->back()->with('error', 'No se ha podido completar tu cita. No se ha encontrado auxiliar disponible.'); 
                }
                
                $modulo = $this->asignarModulo($disponibles[$random]);
                $id_aux=$disponibles[$random];
            }
            else{
                $modulo = $this->asignarModulo($auxiliares[$random]);
                $id_aux=$auxiliares[$random];
            }
            $direccion = $todas_direcciones[$sede];
        }

        // 4. Preparar el guardado mapeado con la estructura e inputs del Blade
        $data_insertar = array(
            'consecutivo'     => $numero_consecutivo,
            'fecha'           => $fecha_asignada_str,
            'hora'            => $hora_turno,
            'hora_fin'        => $hora_fin,
            'auxiliar'        => $id_aux,
            'tipo'            => $tipoTramite,
            'lugar_auxiliar'  => $modulo,
            'exepcion'        => $data["excepcion"] ?? 'No',
            'edad'            => $data["edad"] ?? null,
            'sexo'            => $data["sexo"] ?? null,
            'tipo_caso'       => $tipo_caso,
            'prestacionSS'    => $prestacionSS,
            'vulnerables'     => $vulnerables,
            'conflicto'       => $data["conflicto"] ?? null,
            'solicitante'     => $data["nombre"] ?? null,
            'estatus'         => 'no atendido',
            'orientacion'     => $data["orientacion"] ?? 'No',
            'delegacion'      => $sede,
            'folio'           => $data["folio"] ?? null,
            'INS'             => $data["INS"] ?? null,
            'resultado'       => null,
            'telefono'        => $data["telefono"],
            'correo'          => $data["correo"],
            'municipio'       => $data["municipio_solicitante"],
        );
        try{
            // 5. Ejecutar el Insert a través de Eloquent
            Recepcion::create($data_insertar);
            // Traducir fecha legible (Ej: "Martes 9 de Junio")
            $fechaFormateada = ucfirst(Carbon::parse($fecha_asignada_str)->isoFormat('dddd D [de] MMMM'));

            return redirect()->back()->with('success', 'Turno de ' . $tipoTramite .' con folio ' . $numero_consecutivo. ' generado exitosamente para la sede ' . $sede . 'Localizada en '. $direccion .' el día ' . $fechaFormateada . ' a las ' . substr($hora_turno, 0, 5) . ' horas.');
        }
        catch (\Throwable $e) { 
            return redirect()->back()->with('error', 'No se ha podrido completar tu cita.'); 
        }
    }

    public function index_turnos()
    {
        $fecha_actual = date('Y-m-d');
        $relacionEloquent = 'roles';
        $id = auth()->user()->id;
        $user = User::find($id);
        $delegacion_user = $user["delegacion"];
        $last_solicitudes = SeerPerGeneral::where('delegacion', $delegacion_user)->latest()->value('consecutivo');
        $last_turnos = Turnos::where('delegacion', $delegacion_user)->latest()->value('consecutivo');
        $last_sede_solicitud = Recepcion::where('delegacion', $delegacion_user)->where('tipo', "Solicitud")->count();
        $last_sede_ratificacion = Recepcion::where('delegacion', $delegacion_user)->where('tipo', "Ratificación")->count();
        $last_hora_solicitud = Recepcion::where('delegacion', $delegacion_user)->where('fecha', $fecha_actual)->where('tipo', "Solicitud")->count();
        $last_hora_ratificacion = Recepcion::where('delegacion', $delegacion_user)->where('fecha', $fecha_actual)->where('tipo', "Ratificación")->count();

        $auxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        $auxiliares_morelia = array();
        foreach($auxiliares as $auxiliar){
            $estatus = "Disponible";
            $ocupados = TurnoDisponible::where('fecha', $fecha_actual)
            ->where('id_auxiliar', $auxiliar["id"])
            ->select('turno_disponible.estatus')
            ->orderBy('id', 'DESC')
            ->get();

            if(!count($ocupados) == 0){
                $estatus = $ocupados[0]["estatus"];
            }
            $data_insertar = [
                'id'        => $auxiliar["id"],
                'name'      => $auxiliar["name"],
                'delegacion'=> $auxiliar["delegacion"],
                'estatus'   => $estatus,
            ];
            array_push($auxiliares_morelia, $data_insertar);
        }
        $total = count($auxiliares_morelia);

        return view('turnos.index',compact('auxiliares_morelia','total', 'last_hora_solicitud', 'last_hora_ratificacion', 'last_sede_solicitud', 'last_sede_ratificacion'));
    }

    public function create()
    {
        //Vamos a traer un usuario para asignarle los roles
        $id_usuario = Auth::id();
        return view('recepcion.crear', compact('id_usuario'));
    }

    public function store_turnos(Request $request)
    {
        $data = $request->all();
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");
        $numero_consecutivo = 0;
        $consecutivo  = Recepcion::latest('id')->where('fecha', $fecha_actual)->first();

        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        if($data["orientacion"] == "Si" && $data["excepcion"] == "Si"){
            if($data["delegacion"] == "Morelia" || $data["delegacion"] == "Zitácuaro"){
                $data_insertar= array(
                    'consecutivo'   => $numero_consecutivo,
                    'fecha'         => $fecha_actual,
                    'hora'          => $hora_actual,
                    'hora_fin'      => $hora_actual,
                    'auxiliar'      => 13,
                    'tipo'          => $data["tipo"],
                    'lugar_auxiliar'=> "Departamento de Igualdad de Género",
                    'exepcion'      => $data["excepcion"],
                    'edad'          => $data["edad"],
                    'sexo'          => $data["sexo"],
                    'tipo_caso'     => $data["tipo_caso"],
                    'vulnerables'   => $data["vulnerables"],
                    'orientacion'   => $data["orientacion"],
                    'conflicto'     => $data["conflicto"],
                    'solicitante'   => $data["nombre"],
                    'estatus'       => "no atendido",
                    'delegacion'    => $data["delegacion"],
                );   
            }
            if($data["delegacion"] == "Uruapan" || $data["delegacion"] == "Lázaro Cárdenas"){
                $data_insertar= array(
                    'consecutivo'   => $numero_consecutivo,
                    'fecha'         => $fecha_actual,
                    'hora'          => $hora_actual,
                    'hora_fin'      => $hora_actual,
                    'auxiliar'      => 43,
                    'tipo'          => $data["tipo"],
                    'lugar_auxiliar'=> "Delegada Regional",
                    'exepcion'      => $data["excepcion"],
                    'edad'          => $data["edad"],
                    'sexo'          => $data["sexo"],
                    'tipo_caso'     => $data["tipo_caso"],
                    'vulnerables'   => $data["vulnerables"],
                    'orientacion'   => $data["orientacion"],
                    'conflicto'     => $data["conflicto"],
                    'solicitante'   => $data["nombre"],
                    'estatus'       => "no atendido",
                    'delegacion'    => $data["delegacion"],
                );
            }
            if($data["delegacion"] == "Zamora" || $data["delegacion"] == "Sahuayo"){
                $data_insertar= array(
                    'consecutivo'   => $numero_consecutivo,
                    'fecha'         => $fecha_actual,
                    'hora'          => $hora_actual,
                    'hora_fin'      => $hora_actual,
                    'auxiliar'      => 26,
                    'tipo'          => $data["tipo"],
                    'lugar_auxiliar'=> "Delegada Regional",
                    'exepcion'      => $data["excepcion"],
                    'edad'          => $data["edad"],
                    'sexo'          => $data["sexo"],
                    'tipo_caso'     => $data["tipo_caso"],
                    'vulnerables'   => $data["vulnerables"],
                    'orientacion'   => $data["orientacion"],
                    'conflicto'     => $data["conflicto"],
                    'solicitante'   => $data["nombre"],
                    'estatus'       => "no atendido",
                    'delegacion'    => $data["delegacion"],
                );
            }
        }
        else{
            $data_insertar= array(
                'consecutivo'   => $numero_consecutivo,
                'fecha'         => $fecha_actual,
                'hora'          => $hora_actual,
                'hora_fin'      => $hora_actual,
                'auxiliar'      => 0,
                'tipo'          => $data["tipo"],
                'lugar_auxiliar'=> "Recepción",
                'exepcion'      => $data["excepcion"],
                'edad'          => $data["edad"],
                'sexo'          => $data["sexo"],
                'tipo_caso'     => $data["tipo_caso"],
                'vulnerables'   => $data["vulnerables"],
                'orientacion'   => $data["orientacion"],
                'conflicto'     => $data["conflicto"],
                'solicitante'   => $data["nombre"],
                'estatus'       => "no atendido",
                'delegacion'    => $data["delegacion"],
            );    
        }

        Recepcion::create($data_insertar);
        
        return redirect()->route('turnos');
    }

    public function turnos(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('Y-m-d');
        if($id === 13){
            $turnos = DB::table('recepcion')->where('auxiliar', $id)->get();
        }
        $turnos = DB::table('recepcion')
        ->where('recepcion.fecha', $fecha_actual)
        ->where('recepcion.delegacion', $user["delegacion"])
        //->where('recepcion.estatus','no atendido')
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->select('users.name','recepcion.id','recepcion.solicitante','recepcion.fecha','recepcion.hora','recepcion.estatus','recepcion.tipo','recepcion.exepcion', 'recepcion.lugar_auxiliar')
        ->get();

        return view('recepcion.turnos',compact('turnos'));
    }

    public function activo($id)
    {
        $fecha_actual = date('Y-m-d');

        $ocupados = TurnoDisponible::where('fecha', $fecha_actual)
        ->where('id_auxiliar', $id)
        ->get();
        /*
        //Si existe voy actualizar
        if(!count($ocupados) == 0){
            $data_update = DB::table('turno_disponible')
            ->where('id_auxiliar', $id)
            ->update(['estatus' => 'Disponible']);
            if($id == 3 || $id == 5 || $id ==7 ){
                $ocupados = Turnos::where('fecha', $fecha_actual)
                ->where('auxiliar', 0)
                ->where('tipo', 'Solicitud')
                ->orderBy('id', 'asc')
                ->first();
                //Si hay fila se va asiganar el primero de la fila al axulilar librre
                if(!empty($ocupados)){
                    $id_turno = $ocupados["id"];

                    //Relacion auxiliar con usuario
                    switch($IDauxiliar){
                        case 6: 
                            //Erandi
                            $lugar_auxiliar = "Auxiliar 1";
                            break;
                        case 10: 
                            //Rosario
                            $lugar_auxiliar = "Auxiliar 2";
                            break;
                        case 8: 
                            //Mayra
                            $lugar_auxiliar = "Auxiliar 3";
                            break;
                        case 9: 
                            //Luis
                            $lugar_auxiliar = "Auxiliar 4";
                            break;
                        case 3: 
                            //Yessiu
                            $lugar_auxiliar = "Auxiliar 5";
                            break;
                        case 7: 
                            //Clever
                            $lugar_auxiliar = "Auxiliar 6";
                            break;
                        case 5: 
                            //Sandra
                            $lugar_auxiliar = "Auxiliar 7";
                            break;
                        default:
                            $lugar_auxiliar = "Pendiente";
                            break;
                    }
                    
                    $turno_update= array(
                        'auxiliar'       => $IDauxiliar,
                        'lugar_auxiliar' => $lugar_auxiliar
                    );
                    $disponible_update= array(
                        'estatus'       => 'Ocupado'
                    );

                    $turno = Turnos::find($id_turno);
                    $turno->update($turno_update);

                    $persona = DB::table('turno_disponible')
                    ->where('id_auxiliar', $IDauxiliar)
                    ->where('fecha', $fecha_actual)
                    ->update(['estatus' => 'Ocupado']);
                }
            }
            else{
                $ocupados = Turnos::where('fecha', $fecha_actual)
                ->where('auxiliar', 0)
                ->orderBy('id', 'asc')
                ->first();
                //Si hay fila se va asiganar el primero de la fila al axulilar librre
                if(!empty($ocupados)){
                    $id_turno = $ocupados["id"];

                    //Relacion auxiliar con usuario
                    switch($IDauxiliar){
                        case 6: 
                            //Erandi
                            $lugar_auxiliar = "Auxiliar 5";
                            break;
                        case 10: 
                            //Rosario
                            $lugar_auxiliar = "Auxiliar 2";
                            break;
                        case 8: 
                            //Mayra
                            $lugar_auxiliar = "Auxiliar 3";
                            break;
                        case 9: 
                            //Luis
                            $lugar_auxiliar = "Auxiliar 4";
                            break;
                        case 3: 
                            //Yessiu
                            $lugar_auxiliar = "Auxiliar 5";
                            break;
                        case 7: 
                            //Clever
                            $lugar_auxiliar = "Auxiliar 6";
                            break;
                        case 5: 
                            //Sandra
                            $lugar_auxiliar = "Auxiliar 7";
                            break;
                        default:
                            $lugar_auxiliar = "Pendiente";
                            break;
                    }
                    
                    $turno_update= array(
                        'auxiliar'       => $IDauxiliar,
                        'lugar_auxiliar' => $lugar_auxiliar
                    );
                    $disponible_update= array(
                        'estatus'       => 'Ocupado'
                    );

                    $turno = Turnos::find($id_turno);
                    $turno->update($turno_update);

                    $persona = DB::table('turno_disponible')
                    ->where('id_auxiliar', $IDauxiliar)
                    ->where('fecha', $fecha_actual)
                    ->update(['estatus' => 'Ocupado']);
                }
            }
        }
        */       
        $data_update = DB::table('turno_disponible')
        ->where('id_auxiliar', $id)
        ->update(['estatus' => 'Disponible']);
        return redirect()->route('turnos');
    }

    public function noactivo($id)
    {
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");
        
        $ocupados = TurnoDisponible::where('fecha', $fecha_actual)
        ->where('id_auxiliar', $id)
        ->get();

        if(count($ocupados) == 0){
            $data_insertar_disponible= array(
                'id_auxiliar'   => $id,
                'fecha'         => $fecha_actual,
                'hora'          => $hora_actual,
                'estatus'       => 'Ocupado'
            );
            TurnoDisponible::create($data_insertar_disponible);
        }else{
            $data_update = DB::table('turno_disponible')
            ->where('id_auxiliar', $id)
            ->update(['estatus' => 'Ocupado']);
        }
        
        $data_update = DB::table('turno_disponible')
        ->where('id_auxiliar', $id)
        ->update(['estatus' => 'Ocupado']);

        return redirect()->route('turnos');
    }

    public function cambiar($id)
    {
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $lista_solicitudes = [5,3919,65,2817,2664,2814,70,61];
        $lista_ratificaciones = [4,6,9,32,28,2663,74,731,154,44,47];

        //Se actualizan los estatus
        $turno              = Recepcion::find($id);
        $IDauxiliar         = $turno["auxiliar"];
        
        $disponibles     = TurnoDisponible::where('fecha', $fecha_actual)->where('estatus', 'Disponible')->get();
        $listado_ocupados   = array();
        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $turno->delegacion)
        ->get();

        $listado_auxiliares = $usuariosauxiliares->pluck('id')->toArray();
        if($turno->tipo == "Ratificación"){
            $auxiliaresSede = array_intersect($listado_auxiliares,$lista_ratificaciones);
        }
        else{
            $auxiliaresSede = array_intersect($listado_auxiliares,$lista_solicitudes);
        }
        $auxiliaresOcupados = Recepcion::where('delegacion',  $turno->delegacion)
                ->where('fecha',  $turno->fecha)
                ->where('hora', $turno->hora)
                ->whereIn('auxiliar', $listado_auxiliares)
                ->pluck('auxiliar')
                ->toArray();

        $auxiliares = array_diff($auxiliaresSede, $auxiliaresOcupados);
        
        //validar si hay disponibles
        if(!empty($auxiliares)){
            $random = array_rand($auxiliares);
            if($turno->delegacion == 'Morelia'){
                $auxiliaresOcupados = Recepcion::where('delegacion',$turno->delegacion)->where('fecha', $turno->fecha)->where('hora', $turno->hora)->pluck('auxiliar')->toArray();
                $disponibles = array_diff($auxiliares, $auxiliaresOcupados);
                if($turno->hora === '13:00:00') $disponibles = array_diff($disponibles, [5]);
                elseif($turno->hora=== '13:30:00') $disponibles = array_diff($disponibles, [209]);
                if($disponibles){
                    $random = array_rand($disponibles);
                    $modulo = $this->asignarModulo($disponibles[$random]);
                    $id_aux = $disponibles[$random];
                }
                else{
                    $modulo = $turno->lugar_auxiliar;
                    $id_aux = $turno->auxiliar;
                }
            }
            else{
                $modulo = $this->asignarModulo($auxiliares[$random]);
                $id_aux = $auxiliares[$random];
            }
        }
        else{
            $modulo = $turno->lugar_auxiliar;
            $id_aux = $turno->auxiliar;
        }
        

        $turno_update= array(
            'hora_fin'      =>  $hora_actual,
            'auxiliar'      =>  $id_aux,
            'lugar_auxiliar'=>  $modulo
        );
        $disponible_update= array(
            'estatus'       => 'Disponible'
        );

        $turno->update($turno_update);
        $turno_disponible   = TurnoDisponible::where('id_auxiliar', $IDauxiliar)->where('fecha', $fecha_actual)->first();
        if($turno_disponible != null){
            $turno_disponible->update($disponible_update);
        }
        
        return redirect()->route('turnos.listado');
    }

    private function asignarModulo(int $aux){
        switch($aux){
            //Modulos de Morelia
            case '5':       return 'Modulo 1'; //Sandra Rocio Varela Cortés (Solicitudes y asesorias)
            case '3919':    return 'Modulo 2'; //Mónica Alejandra Pérez López (Solicitudes y asesorias)
            case '65':      return 'Modulo 3'; // Lorena Lachino Barboza (Solicitudes y asesorias)
            case '4':       return 'Modulo 4'; // Ana Luisa Soriano Virueta (Ratificaciones)
            case '6':       return 'Modulo 5'; // Erandi Martinez barajas (Ratificaciones)
            //case '3':       return 'Modulo 6'; // Yesenia Arteaga Vences (Cumplimientos)
            case '9':       return 'Modulo 7'; // Luis Rico Tinoco (Ratificaciones)

            //Modulos de Uruapan
            case '2817':    return 'Modulo 1'; //Andrea Cristina Lagunas Toledo (Solicitudes y asesorias)
            case '32':      return 'Modulo 2'; //Maria Guadalupe Mata Ponce  (Ratificaciones)
            case '28':      return 'Modulo 3'; //Reyna Erendira Tejeda Diaz  (Ratificaciones)

            //Modulos de Zamora
            case '2664':    return 'Modulo 1'; //Yaritza Bravo Cortes (Solicitudes y asesorias)
            case '2663':    return 'Modulo 2'; //Juan Jose Abundes Garcia (Solicitudes, Asesorias y Ratificaciones)
            case '74':      return 'Modulo 3'; //Francisco Hernández Molina (Solicitudes, Asesorias y Ratificaciones)

            //modulos de Lázaro Cárdenas
            case '2814':    return 'Modulo 1'; //Alizon Yanine García Rosas (Solicitudes y asesorias)
            case '731':     return 'Modulo 2'; //Judith Adriana De la Peña Carrillo (Ratificaciones) ->enlace
            case '154':     return 'Modulo 3'; //Bertha Marisol Barriga Garcia (Solicitudes y asesorias)

            //modulos de Sahuayo
            case '70':      return 'Modulo 1'; //María Guadalupe Villanueva Macías (Solicitudes y asesorias)
            case '44':      return 'Modulo 2'; //Ignacio de Jesus Degollado Nuñez (Solicitudes, Asesorias y Ratificaciones) ->enlace

            //Modulois de Zitácuaro
            case '61':      return 'Modulo 1'; //Mariela Zavala Blancas (Solicitudes y asesorias)
            case '47':      return 'Modulo 2'; //Epifanio Gonzalez Vanegas (Solicitudes, Asesorias y Ratificaciones) ->enlace
            default: break;

        }
        
        return 'Modulo 0';
    }


    public function misturnos(){
        $id = auth()->user()->id;
        $fecha_actual = date('Y-m-d');

        /////Validar si es auxiliar o exepcion /////
        $misturnos = Recepcion::where('auxiliar', $id)
        ->where('fecha', $fecha_actual)
        ->get();

        return view('turnos.misturnos',compact('misturnos'));
    }

    public function terminado_confirmar($id){
        $turno = Recepcion::find($id);
        return view('turnos.confirmar', compact('turno'));
    }

    public function cambio($id){
        $id_user = auth()->user()->id;
        $user = User::find($id_user);

        $relacionEloquent = 'roles';
        $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Excepcion');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        $turno_update= array(
            'auxiliar'      =>  $usuariosauxiliares[0]["id"],
            'lugar_auxiliar'=> "Departamento de Igualdad de Género"
        );

        $turno = Recepcion::find($id);
        $turno->update($turno_update);

        return redirect()->route('misturnos');
    }

    public function terminado($id)
    {
        // $id es la variable de la tabla de turnos
        //Obtenemos el id de del auxiliar que esta terminado el turno 
        $turnos = Recepcion::where('id', $id)->first();
        $IDauxiliar = $turnos["auxiliar"];
       
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");

        $turno_update= array(
            'hora_fin'      =>  $hora_actual,
            'estatus'       => 'atendido'
        );
        $disponible_update= array(
            'estatus'       => 'Disponible'
        );

        //Se actualizan los estatus
        $turno = Recepcion::find($id);
        $turno->update($turno_update);

        $persona = DB::table('turno_disponible')
        ->where('id_auxiliar', $IDauxiliar)
        ->where('fecha', $fecha_actual)
        ->update(['estatus' => 'Disponible']);

        //Se va buscar en fila si existe algun otro y se va asiganar
        if($turnos["exepcion"] == "Si"){
            $user = User::find($IDauxiliar);

            $relacionEloquent = 'roles';
            $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
                return $query->where('name', '=', 'Excepcion');
            })
            ->where('delegacion', $user["delegacion"])
            ->get();
            
            $turno_update= array(
                'auxiliar'       => $usuariosauxiliares[0]["id"],
                'lugar_auxiliar' => "Departamento de casos de Excepción"
            );
            $disponible_update= array(
                'estatus'       => 'Ocupado'
            );

            $turno = Recepcion::find($id);
            $turno->update($turno_update);

            $persona = DB::table('turno_disponible')
            ->where('id_auxiliar', $usuariosauxiliares[0]["id"])
            ->where('fecha', $fecha_actual)
            ->update(['estatus' => 'Ocupado']);
        }
        else{
            $ocupados = Recepcion::where('fecha', $fecha_actual)
            ->where('auxiliar', 0)
            ->orderBy('id', 'asc')->first();
            //Si hay fila se va asiganar el primero de la fila al axulilar libre
            if(!empty($ocupados)){
                $id_turno = $ocupados["id"];

                $lugar_auxiliar = "Pendiente";
                
                $turno_update= array(
                    'auxiliar'       => 0,
                    'lugar_auxiliar' => $lugar_auxiliar
                );
                $disponible_update= array(
                    'estatus'       => 'Ocupado'
                );

                $turno = Recepcion::find($id_turno);
                $turno->update($turno_update);
            }
        }

        return redirect()->route('misturnos');
    }

    public function edit(Request $request){
        $data = $request->all();
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $fecha_actual = date('Y-m-d');

        

        if($data["resultado"] == "Solicitud"){
            $turno_update= array(
                'solicitante'   => $data["nombre"],
                'motivo'        => $data["motivo"],
                'excepcion'     => $data["excepcion"],
                'tipo_caso'     => $data["tipo_caso"],
                'vulnerables'   => $data["vulnerables"],
                'folio'         => $data["folio"],
                //'tarjeta'       => $data["tarjeta"],
                'auxiliar'      => 0,
                'resultado'     => $data["resultado"]
            );
        }else if($data["resultado"] == "Canaliza"){
            $turno_update= array(
                'solicitante'   => $data["nombre"],
                'motivo'        => $data["motivo"],
                'excepcion'     => $data["excepcion"],
                'tipo_caso'     => $data["tipo_caso"],
                'vulnerables'   => $data["vulnerables"],
                'INS'           => $data["INS"],
                'estatus'       => "atendido",
                'resultado'     => $data["resultado"]
            );
        }else{
            $turno_update= array(
                'solicitante'   => $data["nombre"],
                //'tarjeta'       => $data["tarjeta"],
                'estatus'       => "atendido",
                'resultado'     => $data["resultado"]
            );
        }

        $turno = Recepcion::find($data["id"])->update($turno_update);

        return redirect()->route('misturnos');
    }

    public function index_tarjeta(){
        $id = auth()->user()->id;

        $misturnos = Recepcion::where('auxiliar', $id)
        ->where('estatus', 'atendido')
        ->where('exepcion','Si')
        ->where('tarjeta',NULL)
        ->get();

        return view('recepcion/index',compact('misturnos'));
    }

    public function tarjeta_crear($id){
        $tarjeta = Recepcion::find($id);

        return view('recepcion/tarjeta',compact('tarjeta'));
    }

    public function guardar(Request $request){
        $data = $request->all();
        $turno_update= array(
            'tarjeta'       => $data["tarjeta"],
        );
        $turno = Recepcion::find($data["id"])->update($turno_update); 
        
        return redirect()->route('tarjeta_informativa');
    }

    public function reporte_excepcion(){
        return view('/turnos/reporte');
    }

    public function reportePDF(Request $request){
        $data = $request->all();

        $turnos = Recepcion::whereBetween("fecha",[$data["fecha_inicial"],$data["fecha_final"]])
        ->where("exepcion","Si")
        ->where("orientacion","Si");
        if($data["delegacion"] != "Todas"){
            $turnos = $turnos->where("delegacion",$data["delegacion"]);
        }
        $turnos = $turnos->get();

        $pdf = \PDF::loadView('PDF/pdf-casos', compact('turnos'));
        $pdf->setPaper('A4', 'landscape');
    
        return $pdf->stream('archivo.pdf');
    }

    public function nueva_cita(){
        $estados = Estados::all();
        $municipios=Municipios::where('estado',16)->get();
        return view('turnos.crear', compact('estados','municipios'));
    }

    // Duración del slot (minutos) y hora de cierre de jornada según el tipo de trámite y si es caso de excepción.
    private function configuracionHorarioTurno($tipo, $excepcion, $sede){
        if ($excepcion === 'Si') {
            return ['intervalo' => 75, 'fin' => ['hour' => 17, 'minute' => 00],'comida' => ['hour' => 14, 'minute' => 0]];
        }
        
        if ($tipo === 'Ratificación') {
            return ['intervalo' => 50, 'fin' => ['hour' => 15, 'minute' => 30],'comida' => ['hour' => 16, 'minute' => 30]];
        }

        return ['intervalo' => 50, 'fin' => ['hour' => 15, 'minute' => 30],'comida' => ['hour' => 16, 'minute' => 30]]; //cambio fin a las 16:50
        
    }

    public function obtenerTurnosDisponibles(Request $request){
        $request->validate([
            'sede' => 'required|string',
            'tipo' => 'required|string',
        ]);

        $sede = $request->input('sede');
        $tipo = $request->input('tipo');
        $excepcion = $request->input('excepcion');
        $bandera = $request->input('externo', false);
        

        $fecha_inicio_str = substr($request->input('start', now()->format('Y-m-d')), 0, 10);
        $fecha_fin_str = substr($request->input('end', now()->addDays(60)->format('Y-m-d')), 0, 10);

        $config = $this->configuracionHorarioTurno($tipo, $excepcion, $sede);
        
        
        $inhabiles = DiasInhabiles::where('centro', $sede)
            ->whereNull('user_id')
            ->where(function ($query) use ($fecha_inicio_str, $fecha_fin_str) {
                $query->where('fecha_inicio', '<=', $fecha_fin_str)
                    ->where('fecha_final', '>=', $fecha_inicio_str);
            })
            ->get();

        $esExcepcion = $excepcion === 'Si';
        $maxEmpalme = $esExcepcion ? 1 : ($sede === 'Morelia' ? ($tipo === 'Ratificación' ? 2 : 3) : 1);

        $ocupadosQuery = Recepcion::whereBetween('fecha', [$fecha_inicio_str, $fecha_fin_str]);
        if ($esExcepcion) {
            $ocupadosQuery->where('exepcion', 'Si'); 
        } else {
            if($tipo == "Ratificación"){
                $ocupadosQuery->where('tipo', $tipo)->where('delegacion', $sede);
            } else {
                $ocupadosQuery->whereIn('tipo', ['Solicitud', 'Asesoría'])->where('delegacion', $sede);
            }
        }
        $ocupados = $ocupadosQuery->get(['fecha', 'hora']);

        $ocupadosCount = [];
        foreach ($ocupados as $turno) {
            $fechaTurno = \Carbon\Carbon::parse($turno->fecha)->format('Y-m-d');
            $horaTurno = \Carbon\Carbon::parse($turno->hora)->format('H:i:s');
            $key = $fechaTurno . 'T' . $horaTurno;
            $ocupadosCount[$key] = ($ocupadosCount[$key] ?? 0) + 1;
        }

        $ahora = new \DateTime();
        $eventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fin = (new \DateTime($fecha_fin_str))->setTime(0, 0, 0);
        $horas_ocupadas = ['13:00:00', '13:30:00', '14:00:00'];

        $colores = [
            'ocupado' => '#DA0909', 'inhabil' => '#3B78DB',
            'expirado' => '#8a959e', 'disponible' => '#26c03a',
            'turnos' => '#26c03a',
        ];
        $titulos = [
            'ocupado' => 'Ocupado', 'inhabil' => 'Inhábil',
            'expirado' => 'No disponible', 'disponible' => 'Disponible',
        ];

        while ($fecha <= $fin) {
            if ((int) $fecha->format('N') < 6) { // Saltar fines de semana
                $lleno = false;
                if($bandera == '1'){
                    $ocupados_dia = Recepcion::where('fecha', $fecha)->where('tipo', $tipo)->where('delegacion', $sede)->count();
                    if(($ocupados_dia >= 30 && $tipo === 'Solicitud' && $sede ==='Morelia') || ($ocupados_dia >= 10 && $tipo === 'Solicitud') || ($ocupados_dia >= 15 && $tipo === 'Ratificación')){
                        $lleno= true;
                    }
                }
                $slot = (clone $fecha)->setTime(8, 30, 0);
                
                $finJornada = (clone $fecha)->setTime($config['fin']['hour'], $config['fin']['minute'], 0);
                $hora_comida = (clone $fecha)->setTime($config['comida']['hour'], $config['comida']['minute'], 0);
                while ($slot < $finJornada) {
                    $slotStart = $slot->format('Y-m-d\TH:i:s');

                    $esInhabil = false;
                    $esNoInhabil = false;
                    foreach ($inhabiles as $dia) {
                        $inicioInhabil = $dia->fecha_inicio . 'T' . ($dia->horario_inicio ?? '00:00:00');
                        $finInhabil = $dia->fecha_final . 'T' . ($dia->horario_final ?? '23:59:59');
                        if ($slotStart >= $inicioInhabil && $slotStart <= $finInhabil) {
                            if ($dia->descripcion === 'No inhabil') {
                                $esNoInhabil = true;
                            } else {
                                $esInhabil = true;
                            }
                            break;
                        }
                    }
                    $hora_actual = $slot->format('H:i:s');
                    $cantidadOcupados = $ocupadosCount[$slotStart] ?? 0;

                    if($lleno && !$esExcepcion){
                        $estado = 'expirado';
                    }
                    elseif ($esInhabil || $esNoInhabil || $ahora > $slot || $slot == $hora_comida) {
                        $estado = 'expirado';
                    }
                    elseif ($cantidadOcupados >= $maxEmpalme) {
                        // Bloquea inmediatamente si se alcanzó el límite (1 para excepciones)
                        $estado = 'ocupado';
                    }
                    elseif ($cantidadOcupados > 0 && $cantidadOcupados < $maxEmpalme) {
                        if(!$esExcepcion && in_array($hora_actual, $horas_ocupadas)){
                            $estado = 'ocupado';
                        } else {
                            $estado = 'turnos'; 
                        }
                    }
                    
                    else {
                        $estado = 'disponible';
                    }

                    $titulo = $estado === 'turnos' ? "Disponible" : $titulos[$estado];

                    $eventos[] = [
                        'title' => $titulo,
                        'start' => $slotStart,
                        'color' => $colores[$estado],
                        'extendedProps' => ['estado' => $estado],
                    ];
                    if($slot == $hora_comida){
                        $slot->modify("+30 minutes");
                    }
                    else{
                        $slot->modify("+{$config['intervalo']} minutes");
                    }
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($eventos);
    }

    public function turnoSlotDisponible($sede, $tipo, $fecha, $hora, $excepcion){
        // El calendario de excepción no admite empalmes. Fuera de excepción, solo la sede Morelia admite empalmar varias citas en un mismo slot.
        $esExcepcion = $excepcion === 'Si';
        $maxEmpalme = $esExcepcion ? 1 : ($sede === 'Morelia' ? ($tipo === 'Ratificación' ? 2 : 3) : 1);
                            
        $cantidadQuery = Recepcion::where('fecha', $fecha)->where('hora', $hora);
        if ($esExcepcion) {
            //Si es excepción bloquea el slot sin importar el tipo de trámite o la sede
            $cantidadQuery->where('exepcion', 'Si');
        } else {
            $cantidadQuery->where('tipo', $tipo)->where('delegacion', $sede);
        }

        if ($cantidadQuery->count() >= $maxEmpalme) {
            return false;
        }

        $slotStart = $fecha . 'T' . $hora;

        $inhabil = DiasInhabiles::where('centro', $sede)
            ->whereNull('user_id')
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_final', '>=', $fecha)
            ->get()
            ->contains(function ($dia) use ($slotStart) {
                $inicio = $dia->fecha_inicio . 'T' . ($dia->horario_inicio ?? '00:00:00');
                $fin = $dia->fecha_final . 'T' . ($dia->horario_final ?? '23:59:59');
                return $slotStart >= $inicio && $slotStart <= $fin;
            });

        return !$inhabil;
    }

    public function index_excepciones()
    {
        $fecha_actual = date('Y-m-d');
        $recepciones = Recepcion::where('exepcion', 'Si')->where('fecha',$fecha_actual)->where('estatus','no atendido')->get();
        $recepciones = Recepcion::where('exepcion', 'Si')->get();

        return view('excepciones.index',compact('recepciones'));
    }

    public function atender_excepcion($id){
        
        $recepcion = Recepcion::find($id);
        return view('excepciones.atender', compact('recepcion'));
    }

    public function guardar_excepcion(Request $request){

        $data = $request->all();
        $id_user = auth()->user()->id;
        $fecha_actual = date('Y-m-d');
        $hora_actual= date('H:i');
        $data_insertar = array(
            'id_turno'          => $data['id'],
            'id_user'           => $id_user,
            'observaciones'     => $data['observaciones'],
            'dependencia'       => $data['dependencia'],
            'expediente'        => $data['expediente'], 
            'situacion_laboral' => $data['situacion_laboral'],
            'frecuencia'        => $data['frecuencia'],
            'descripcion_conductas' => $data['descripcion_conductas'],
            'tipo_caso'         => $data['tipo_caso'],
            'vulnerables'       => $data['vulnerables'],
            'jefe_inmediato'    => $data['jefe_inmediato'],
            'empresa'           => $data['empresa'],
            'ubicacion'         =>$data['ubicacion'],
            'puesto'            => $data['puesto'],
            'area_adscripcion'   => $data['area_adscripcion'],
            'fecha'             => $fecha_actual, 
            'hora'              => $hora_actual ,
        );

        if(!isset($data['descripcion_persona'])){
            $data_insertar['descripcion_persona'] =null;
        }
        else{
            $data_insertar['descripcion_persona'] =  $data['descripcion_persona'];
        }
        if(isset($data['motivo'])){
            $data_insertar['motivos'] =  $data['motivo'];
        }
        else{
            $data_insertar['motivos'] = null;
        }
        
        
        SeerCasosExcepcion::create($data_insertar);
        
        $data_update = DB::table('recepcion')
        ->where('id', $data["id"])
        ->update(['estatus' => 'atendido']);

        return redirect()->route('excepcion');

    }
    public function Verpdfcasosprevistos($id){
        $recepcion = Recepcion::find($id);
        $caso = SeerCasosExcepcion::where('id_turno', $id)->first();
        $auxiliar = User::where('id', $recepcion->auxiliar)->first();

        $html = view('PDF.Recepcion.CasosPrevistos', compact('recepcion','caso', 'auxiliar'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'casos_previstos'.'pdf';
        return $pdf->stream($nombreArchivo);       
    }
    public function Verpdfcanalizacion($id){
        $recepcion = Recepcion::find($id);
        $caso = SeerCasosExcepcion::where('id_turno', $id)->first();
        $auxiliar = User::where('id', $recepcion->auxiliar)->first();
        $html = view('PDF.Recepcion.Canalizacion', compact('recepcion','caso','auxiliar'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'canalizacion'.'pdf';
        return $pdf->stream($nombreArchivo);       
    }

    public function verDocumentoCita($id)
    {
        $cita = Recepcion::findOrFail($id);
        $fecha = $cita->fecha->format('Y-m-d');
        $hora = $cita->hora->format('H:i');
        $todas_direcciones = [
            'Morelia' => 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260, MORELIA, MICHOACÁN',
            'Zitácuaro' => '5 DE MAYO NTE. 3, CENTRO, C.P. 61500, ZITÁCUARO, MICHOACÁN.',
            'Zamora' => 'JUSTO SIERRA NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670, ZAMORA DE HIDALGO, MICHOACÁN.',
            'Sahuayo' => 'AV. UNIVERSIDAD SUR NO. 3000, SEGUNDO PISO, EDIFICIO CENTRAL, COL. LOMAS DE UNIVERSIDAD, C.P. 59103, SAHUAYO DE MORELOS, MICHOACÁN.',
            'Uruapan' => 'NUEVO PARICUTÍN NO. 308, COL. SAN RAFAEL, C.P. 60136, URUAPAN, MICHOACÁN.',
            'Lázaro Cárdenas' => 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950, LÁZARO CÁRDENAS, MICHOACÁN.',
        ];
        $direccion = $todas_direcciones[$cita->delegacion];
        // Se genera la URL absoluta que abrirá el teléfono al escanear
        $urlConfirmacion = route('citas.confirmar', $cita->id);

        // Se crea el QR apuntando a esa URL específica
        $qrCode = QrCode::size(200)->generate($urlConfirmacion);
        $html = view('recepcion.acuse_cita', compact('cita', 'qrCode', 'direccion','fecha','hora'))->render();
        
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Confirmacion_cita' .'.pdf';
        return $pdf->stream($nombreArchivo); 

    }
    public function confirmarAsistencia($id)
    {
        try{
            $cita = Recepcion::findOrFail($id);
            $bandera = '0';
            
            $horaLimite = $cita->hora->copy()->addMinutes(5)->format('H:i:s');
            $fecha_hora = $cita->fecha->format('Y-m-d'). ' ' . $cita->hora->format('H:i:s');
            $bandera = '0';

            if($cita->estatus === 'expirada'){
                $bandera = '3';
            }
            else{
                if (now()->isSameDay($cita->fecha)) {
                    $bandera = '1'; 

                    if ($cita->estatus === 'confirmada') {
                        $bandera = '2';
                    }
                    elseif (now()->format('H:i:s') > $horaLimite) {
                        $cita->update(['estatus' => 'expirada']); 
                        $bandera = '3';
                        
                    } elseif ($cita->estatus === 'no atendido') {
                        $cita->update(['estatus' => 'confirmada']);
                        
                    } 
                }
                elseif (now()->startOfDay() > $cita->fecha->startOfDay()){
                    $cita->update(['estatus' => 'expirada']); 
                    $bandera = '3';
                }

            }
            
            return view('recepcion.confirmacion', compact('cita', 'bandera', 'fecha_hora'));
        }
        catch(\Throwable $e) {
            $bandera = '4';
            $cita =null;
            $horaLimite = null;
            $fecha_hora = null;
            return view('recepcion.confirmacion', compact('cita', 'bandera', 'fecha_hora'));
        }
        
    }
}