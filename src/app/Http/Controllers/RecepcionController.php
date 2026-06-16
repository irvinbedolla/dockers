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

        $data = $request->all();
        $sede = $data["delegacion"];
        $tipoTramite = $data["tipo"]; 
        $hora_actual = date("H:i:s");
        $id_auxiliar = auth()->user()->id;

        // 1. Llamar a la función enviándole la sede y el tipo de trámite seleccionado
        $fechaAsignada = $this->obtenerProximaFechaTurno($sede, $tipoTramite);

        if (!$fechaAsignada) {
            return redirect()->back()->with('error', 'No se encontraron fechas disponibles para realizar una ' . $tipoTramite . ' en la sede ' . $sede . '.');
        }

        $fecha_asignada_str = $fechaAsignada->format('Y-m-d');

        // 2. Calcular el consecutivo dinámico de acuerdo a la FECHA ASIGNADA y SEDE
        $consecutivo = Recepcion::where('fecha', $fecha_asignada_str)
            ->where('delegacion', $sede)
            ->orderBy('consecutivo', 'desc')
            ->first();

        if (empty($consecutivo)) {
            $numero_consecutivo = 1;
        } else {
            $numero_consecutivo = $consecutivo["consecutivo"] + 1;
        }

        // 3. SOLUCIÓN AL ERROR: Validar si los campos múltiples vienen como array o como string
        $tipo_caso    = isset($data["tipo_caso"])    ? (is_array($data["tipo_caso"])    ? implode(',', $data["tipo_caso"])    : $data["tipo_caso"])    : null;
        $prestacionSS = isset($data["prestacionSS"]) ? (is_array($data["prestacionSS"]) ? implode(',', $data["prestacionSS"]) : $data["prestacionSS"]) : null;
        $vulnerables  = isset($data["vulnerables"])  ? (is_array($data["vulnerables"])  ? implode(',', $data["vulnerables"])  : $data["vulnerables"])  : 'Ninguno';

        // 4. Preparar el guardado mapeado con la estructura e inputs del Blade
        $data_insertar = array(
            'consecutivo'     => $numero_consecutivo,
            'fecha'           => $fecha_asignada_str,
            'hora'            => $hora_actual,
            'auxiliar'        => 0,
            'tipo'            => $tipoTramite, 
            'lugar_auxiliar'  => $data["lugar_auxiliar"] ?? 'Mesa 1', 
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
        );

        // 5. Ejecutar el Insert a través de Eloquent
        Recepcion::create($data_insertar);

        // Traducir fecha legible (Ej: "Martes 9 de Junio")
        $fechaFormateada = ucfirst($fechaAsignada->isoFormat('dddd D [de] MMMM'));

        return redirect()->back()->with('success', 'Turno #' . $numero_consecutivo . ' (' . $tipoTramite . ') generado exitosamente para la sede ' . $sede . ' el día ' . $fechaFormateada);
    }

    public function index_turnos()
    {
        $fecha_actual = date('Y-m-d');
        $relacionEloquent = 'roles';
        $id = auth()->user()->id;
        $user = User::find($id);
        $last_solicitudes = SeerPerGeneral::where('delegacion', $user["delegacion"])->latest()->value('consecutivo');
        $last_turnos = Turnos::where('delegacion', $user["delegacion"])->latest()->value('consecutivo');

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

        return view('turnos.index',compact('auxiliares_morelia','total', 'last_solicitudes', 'last_turnos'));
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

        $turnos = DB::table('recepcion')
        ->where('recepcion.fecha', $fecha_actual)
        ->where('recepcion.delegacion', $user["delegacion"])
        //->where('recepcion.estatus','no atendido')
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->select('users.name','recepcion.id','recepcion.solicitante','recepcion.fecha','recepcion.hora','recepcion.estatus','recepcion.tipo','recepcion.exepcion')
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
        ->where('delegacion', $user["delegacion"])
        ->get();

        foreach($usuariosauxiliares as $token ){
            array_push($listado_auxiliares, $token["id"]);
        }
        
        //validar si hay disponibles
        $random = array_rand($listado_auxiliares);
        $nombre_usuario = User::find($listado_auxiliares[$random]);
        $lugar_auxiliar = $nombre_usuario["name"];

        $turno_update= array(
            'hora_fin'      =>  $hora_actual,
            'auxiliar'      =>  $listado_auxiliares[$random],
            'lugar_auxiliar'=>  $lugar_auxiliar
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

    public function misturnos(){
        $id = auth()->user()->id;

        /////Validar si es auxiliar o exepcion /////
        $misturnos = Recepcion::where('auxiliar', $id)
        ->where('estatus', 'no atendido')
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
        return view('turnos/crear');
    }
    
    private function obtenerProximaFechaTurno($sede, $tipo){
        // 1. Determinar el límite diario en base al tipo de trámite y la sede
        if ($tipo === 'Ratificación') {
            $limiteSolicitudes = 4; // Límite estricto para ratificaciones en cualquier sede
        } else {
            // Si es 'Solicitud' u otro tipo por defecto
            $limiteSolicitudes = ($sede === 'Morelia') ? 20 : 10;
        }

        $fecha_evaluar = Carbon::now();
        $fecha_encontrada = null;

        // Bucle de seguridad para evaluar los próximos 60 días
        for ($i = 0; $i < 60; $i++) {
            $fecha_str = $fecha_evaluar->format('Y-m-d');

            // Regla A: Omitir fines de semana
            if ($fecha_evaluar->isWeekend()) {
                $fecha_evaluar->addDay();
                continue;
            }

            // Regla B: Verificar si es día inhábil general del Centro/Sede
            $esInhabil = DiasInhabiles::where('centro', $sede)
                ->whereNull('user_id')
                ->whereIn('tipo', ['Todos', 'Audiencias'])
                ->where('descripcion', 'Inhabil')
                ->where('fecha_inicio', '<=', $fecha_str)
                ->where('fecha_final', '>=', $fecha_str)
                ->exists();

            if ($esInhabil) {
                $fecha_evaluar->addDay();
                continue;
            }

            // Regla C: Contar cuántos trámites DEL MISMO TIPO ya se agendaron en esa fecha y sede
            $totalTurnosDelDia = Recepcion::where('fecha', $fecha_str)
                ->where('delegacion', $sede)
                ->where('tipo', $tipo) // Filtramos específicamente por el tipo de trámite evaluado
                ->count();

            // Si hay cupo libre para ese tipo de trámite, se selecciona el día
            if ($totalTurnosDelDia < $limiteSolicitudes) {
                $fecha_encontrada = $fecha_evaluar->copy();
                break;
            }

            $fecha_evaluar->addDay();
        }

        return $fecha_encontrada;
    }
}