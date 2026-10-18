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
use App\Support\HorarioCitas;
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
        // Cada recepción agenda sólo sus trámites (App\Support\Recepcion::CREA).
        $tramitesPermitidos = \App\Support\Recepcion::tramitesQuePuedeCrear(auth()->user());
        if (! in_array($request->input('tipo'), $tramitesPermitidos, true)) {
            return redirect()->back()->withInput()->with('error', 'Tu cuenta sólo puede agendar: '.implode(', ', $tramitesPermitidos).'.');
        }
        if (!$request->filled('fecha_turno') || !$request->filled('hora_turno')) {
            return redirect()->back()->with('error', 'Es necesario seleccionar la fecha y el horario del turno en el calendario.');
        }

        // CURP y domicilio del flujo nuevo (sólo si el formulario los pidió).
        [$camposFlujo, $errorFlujo] = \App\Support\FlujoCita::camposDeCita($request);
        if ($errorFlujo) {
            return back()->withInput()->with('error', $errorFlujo);
        }

        $data = $request->all();
        $sede = $data["delegacion"];
        $tipoTramite = $data["tipo"];
        $fecha_asignada_str = $data["fecha_turno"];
        $hora_turno = $data["hora_turno"];
        $id_auxiliar = auth()->user()->id;
        $hora_fin =$hora_turno;
        $esExcepcion = $request->input('excepcion') === 'Si';

        $todas_direcciones = [
            'Morelia' => 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260, MORELIA, MICHOACÁN',
            'Zitácuaro' => '5 DE MAYO NTE. 3, CENTRO, C.P. 61500, ZITÁCUARO, MICHOACÁN.',
            'Zamora' => 'JUSTO SIERRA NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670, ZAMORA DE HIDALGO, MICHOACÁN.',
            'Sahuayo' => 'AV. UNIVERSIDAD SUR NO. 3000, SEGUNDO PISO, EDIFICIO CENTRAL, COL. LOMAS DE UNIVERSIDAD, C.P. 59103, SAHUAYO DE MORELOS, MICHOACÁN.',
            'Uruapan' => 'NUEVO PARICUTÍN NO. 308, COL. SAN RAFAEL, C.P. 60136, URUAPAN, MICHOACÁN.',
            'Lázaro Cárdenas' => 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950, LÁZARO CÁRDENAS, MICHOACÁN.',
        ];

        $hora_fin = HorarioCitas::horaFin($sede, $tipoTramite, $esExcepcion, $hora_turno);

        // El horario seleccionado en el calendario ya no debe estar ocupado ni caer en un día/horario inhábil
        if (!$this->turnoSlotDisponible($sede, $tipoTramite, $fecha_asignada_str, $hora_turno, $data["excepcion"] ?? null)) {
            return redirect()->back()->with('error', 'El horario seleccionado ya no está disponible. Por favor selecciona otro.');
        }

        // 3. SOLUCIÓN AL ERROR: Validar si los campos múltiples vienen como array o como string
        $tipo_caso    = isset($data["tipo_caso"])    ? (is_array($data["tipo_caso"])    ? implode(',', $data["tipo_caso"])    : $data["tipo_caso"])    : null;
        $prestacionSS = isset($data["prestacionSS"]) ? (is_array($data["prestacionSS"]) ? implode(',', $data["prestacionSS"]) : $data["prestacionSS"]) : null;
        $vulnerables  = isset($data["vulnerables"])  ? (is_array($data["vulnerables"])  ? implode(',', $data["vulnerables"])  : $data["vulnerables"])  : 'Ninguno';
        
        $modulo_id = null;
        if($data["excepcion"] == "Si"){
            $modulo = "Departamento de genero e igualdad";
            $id_aux = 13;
        }
        else{
            // Catálogo de módulos: Administración → Módulos.
            $elegido = \App\Support\AsignadorModulos::elegir($sede, $tipoTramite, $fecha_asignada_str, $hora_turno);
            if (!$elegido) {
                return redirect()->back()->with('error', 'No se ha podido completar tu cita. No se ha encontrado auxiliar disponible.');
            }
            $id_aux    = $elegido->user_id;
            $modulo    = $elegido->nombre;
            $modulo_id = $elegido->id;
        }
        $direccion = $todas_direcciones[$sede];
        
        try {

            $numero_consecutivo = 0;
            $cita = null;

            DB::transaction(function () use (
                $sede, $fecha_asignada_str, $hora_turno, $hora_fin, $id_aux, 
                $tipoTramite, $modulo, $modulo_id, $data, $tipo_caso, $prestacionSS, 
                $vulnerables, $camposFlujo, &$numero_consecutivo, &$cita
            ) {
                $ultimoTurno = Recepcion::where('delegacion', $sede) 
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $numero_consecutivo = $ultimoTurno ? ($ultimoTurno->consecutivo + 1) : 1;

                $cita = Recepcion::create([
                    'consecutivo'     => $numero_consecutivo,
                    'fecha'           => $fecha_asignada_str,
                    'hora'            => $hora_turno,
                    'hora_fin'        => $hora_fin,
                    'auxiliar'        => $id_aux,
                    'tipo'            => $tipoTramite,
                    'lugar_auxiliar'  => $modulo,
                    'modulo_id'       => $modulo_id,
                    'origen'          => 'ventanilla',
                    'exepcion'        => $data["excepcion"] ?? 'No',
                    'edad'            => $data["edad"] ?? null,
                    'sexo'            => $data["sexo"] ?? null,
                    'tipo_caso'       => $tipo_caso,
                    // Motivo exacto del caso de excepción (CasosExcepcion::MOTIVOS).
                    ...(\App\Support\CasosExcepcion::conMotivo()
                        ? ['motivo_excepcion' => \App\Support\CasosExcepcion::motivoValido($data['motivo_excepcion'] ?? null)]
                        : []),
                    'prestacionSS'    => $prestacionSS,
                    'vulnerables'     => $vulnerables,
                    'conflicto'       => $data["conflicto"] ?? null,
                    'solicitante'     => $data["nombre"] ?? null,
                    'estatus'         => 'pendiente',
                    'orientacion'     => $data["orientacion"] ?? 'No',
                    'delegacion'      => $sede,
                    'folio'           => $data["folio"] ?? null,
                    'INS'             => $data["INS"] ?? null,
                    'resultado'       => null,
                    'telefono'        => $data["telefono"],
                    'correo'          => $data["correo"],
                    'municipio'       => $data["municipio_solicitante"],
                ] + $camposFlujo);
            }); 

            $fechaFormateada = ucfirst(Carbon::parse($fecha_asignada_str)->isoFormat('dddd D [de] MMMM'));

            return redirect()->back()->with('cita_id', $cita->id)->with('success', 'Turno de ' . $tipoTramite .' con folio ' . $cita->folio_cita . ' generado exitosamente para la sede ' . $sede . ' localizada en '. $direccion .' el día ' . $fechaFormateada . ' a las ' . substr($hora_turno, 0, 5) . ' horas.');
        
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

        // Resumen de hoy en las sedes que esta persona puede ver (la
        // recepción regional ve cinco; los demás, la suya).
        $sedesVisibles = \App\Support\Recepcion::sedesVisibles($user);
        $hoy = DB::table('recepcion')
            ->whereIn('delegacion', $sedesVisibles ?: ['__ninguna__'])
            ->where('fecha', $fecha_actual)
            // Morelia 02 sólo ve ratificaciones y cumplimientos.
            ->when(\App\Support\Recepcion::tramitesVisibles($user), fn ($q, $tramites) => $q->whereIn('tipo', $tramites));

        $hoyPorEstatus = (clone $hoy)
            ->selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');
        $hoyEnLinea = (clone $hoy)->where('origen', 'linea')->count();
        $proximos = (clone $hoy)
            ->where('estatus', 'pendiente')
            ->where('hora', '>=', date('H:i:s', strtotime('-30 minutes')))
            ->orderBy('hora')
            ->limit(6)
            ->get(['id', 'consecutivo', 'solicitante', 'tipo', 'hora', 'lugar_auxiliar', 'origen', 'delegacion']);

        return view('turnos.index',compact('auxiliares_morelia','total', 'last_hora_solicitud', 'last_hora_ratificacion', 'last_sede_solicitud', 'last_sede_ratificacion',
            'sedesVisibles', 'hoyPorEstatus', 'hoyEnLinea', 'proximos'));
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
                    'estatus'       => "pendiente",
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
                    'estatus'       => "pendiente",
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
                    'estatus'       => "pendiente",
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
                'origen'        => 'ventanilla',
                'exepcion'      => $data["excepcion"],
                'edad'          => $data["edad"],
                'sexo'          => $data["sexo"],
                'tipo_caso'     => $data["tipo_caso"],
                'vulnerables'   => $data["vulnerables"],
                'orientacion'   => $data["orientacion"],
                'conflicto'     => $data["conflicto"],
                'solicitante'   => $data["nombre"],
                'estatus'       => "pendiente",
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
        ->select('users.name','recepcion.consecutivo','recepcion.id','recepcion.solicitante','recepcion.fecha','recepcion.hora','recepcion.estatus','recepcion.tipo','recepcion.exepcion', 'recepcion.lugar_auxiliar')
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

        // Pasa el turno al primer módulo libre del catálogo a su hora; si no
        // hay ninguno, se queda donde estaba.
        $elegido = \App\Support\AsignadorModulos::elegir(
            $turno->delegacion, $turno->tipo, $turno->fecha->format('Y-m-d'), $turno->getRawOriginal('hora')
        );
        $modulo    = $elegido ? $elegido->nombre  : $turno->lugar_auxiliar;
        $id_aux    = $elegido ? $elegido->user_id : $turno->auxiliar;
        $modulo_id = $elegido ? $elegido->id      : $turno->modulo_id;


        $turno_update= array(
            'hora_fin'      =>  $hora_actual,
            'auxiliar'      =>  $id_aux,
            'lugar_auxiliar'=>  $modulo,
            'modulo_id'     =>  $modulo_id,
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
        $fecha_actual = date('Y-m-d');
        $user = auth()->user();
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();

        if($userRole[0] == "Super Usuario"){
            $misturnos = Recepcion::where('fecha', $fecha_actual)->orderBy('hora')
            ->get();
        }
        else{
            $misturnos = Recepcion::where('auxiliar', $id)
            ->where('fecha', $fecha_actual)->orderBy('hora')
            ->get();
        }
        

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

    /**
     * Horarios del calendario de citas (formulario público y ventanilla).
     * Intervalo, cierre, cupo por horario y límite diario salen de la tabla
     * horarios_citas: Administración → Horarios de citas.
     */
    public function obtenerTurnosDisponibles(Request $request){
        $request->validate([
            'sede' => 'required|string',
            'tipo' => 'required|string',
        ]);

        $sede = $request->input('sede');
        $tipo = $request->input('tipo');
        $esExcepcion = $request->input('excepcion') === 'Si';
        $publico = $request->input('externo', false) == '1';

        $fecha_inicio_str = substr($request->input('start', now()->format('Y-m-d')), 0, 10);
        $fecha_fin_str = substr($request->input('end', now()->addDays(60)->format('Y-m-d')), 0, 10);

        $config   = HorarioCitas::config($sede, $tipo, $esExcepcion);
        $horarios = HorarioCitas::horarios($config);

        $inhabiles = DiasInhabiles::where('centro', $sede)
            ->whereNull('user_id')
            ->where(function ($query) use ($fecha_inicio_str, $fecha_fin_str) {
                $query->where('fecha_inicio', '<=', $fecha_fin_str)
                    ->where('fecha_final', '>=', $fecha_inicio_str);
            })
            ->get();

        $ocupados = HorarioCitas::citasQueOcupan($sede, $tipo, $esExcepcion)
            ->whereBetween('fecha', [$fecha_inicio_str, $fecha_fin_str])
            ->get(['fecha', 'hora']);

        $ocupadosCount = [];
        $porDia = [];
        foreach ($ocupados as $turno) {
            $fechaTurno = Carbon::parse($turno->fecha)->format('Y-m-d');
            $key = $fechaTurno . 'T' . Carbon::parse($turno->hora)->format('H:i:s');
            $ocupadosCount[$key] = ($ocupadosCount[$key] ?? 0) + 1;
            $porDia[$fechaTurno] = ($porDia[$fechaTurno] ?? 0) + 1;
        }

        // El límite diario sólo aplica al formulario público.
        $limite = ($publico && ! $esExcepcion) ? $config->limite_diario : null;

        $ahora = now();
        $eventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fin = (new \DateTime($fecha_fin_str))->setTime(0, 0, 0);

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
                $dia = $fecha->format('Y-m-d');
                $lleno = $limite !== null && ($porDia[$dia] ?? 0) >= $limite;

                foreach ($horarios as $hora) {
                    $slotStart = $dia . 'T' . $hora;

                    $esInhabil = false;
                    foreach ($inhabiles as $diaInhabil) {
                        $inicioInhabil = $diaInhabil->fecha_inicio . 'T' . ($diaInhabil->horario_inicio ?? '00:00:00');
                        $finInhabil = $diaInhabil->fecha_final . 'T' . ($diaInhabil->horario_final ?? '23:59:59');
                        if ($slotStart >= $inicioInhabil && $slotStart <= $finInhabil) {
                            $esInhabil = true; // "No inhabil" también bloquea, como antes
                            break;
                        }
                    }

                    $cantidadOcupados = $ocupadosCount[$slotStart] ?? 0;
                    $tolerancia = Carbon::parse($slotStart)->addMinutes(HorarioCitas::TOLERANCIA_RESERVA);

                    if ($lleno || $esInhabil || $ahora > $tolerancia) {
                        $estado = 'expirado';
                    } elseif ($cantidadOcupados >= HorarioCitas::capacidad($config, $hora)) {
                        $estado = 'ocupado';
                    } elseif ($cantidadOcupados > 0) {
                        $estado = 'turnos';
                    } else {
                        $estado = 'disponible';
                    }

                    $eventos[] = [
                        'title' => $estado === 'turnos' ? 'Disponible' : $titulos[$estado],
                        'start' => $slotStart,
                        'color' => $colores[$estado],
                        'extendedProps' => ['estado' => $estado],
                    ];
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($eventos);
    }

    /**
     * Revisión final antes de guardar: que la hora sea uno de los horarios
     * configurados, que quede cupo, que no sea inhábil y, en el formulario
     * público, que el día no haya llegado a su límite.
     */
    public function turnoSlotDisponible($sede, $tipo, $fecha, $hora, $excepcion, bool $publico = false){
        $esExcepcion = $excepcion === 'Si';
        $config = HorarioCitas::config($sede, $tipo, $esExcepcion);
        $hora = HorarioCitas::normalizarHora((string) $hora);

        if (! in_array($hora, HorarioCitas::horarios($config), true)) {
            return false;
        }

        $delDia = HorarioCitas::citasQueOcupan($sede, $tipo, $esExcepcion)->where('fecha', $fecha);

        if ((clone $delDia)->where('hora', $hora)->count() >= HorarioCitas::capacidad($config, $hora)) {
            return false;
        }

        if ($publico && ! $esExcepcion && $config->limite_diario !== null
            && (clone $delDia)->count() >= $config->limite_diario) {
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

    // Bandeja, formato y canalización de casos de excepción: ahora en
    // CasosExcepcionController (App\Support\CasosExcepcion / FormatoExcepcion).

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
        $qrCode = QrCode::size(200)->color(94, 119, 122)->generate($urlConfirmacion);
        $html = view('recepcion.acuse_cita', compact('cita', 'qrCode', 'direccion','fecha','hora'))->render();
        
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Confirmacion_cita' .'.pdf';
        return $pdf->stream($nombreArchivo, ["Attachment" => false]);

    }
    public function confirmarAsistencia($id)
    {
        // La regla vive en App\Support\ValidadorCita, compartida con el
        // escáner de recepción. Aquí sólo se traduce a la "bandera" que la
        // vista recepcion.confirmacion ya sabía pintar.
        try {
            $cita = Recepcion::findOrFail($id);
            $fecha_hora = $cita->fecha->format('Y-m-d').' '.$cita->hora->format('H:i:s');

            $bandera = match (\App\Support\ValidadorCita::validar($cita)['resultado']) {
                'otro_dia'                => '0',
                'confirmada', 'atendida'  => '1',
                'ya_confirmada', 'pasada' => '2',
                default                   => '3', // tarde, expirada
            };

            return view('recepcion.confirmacion', compact('cita', 'bandera', 'fecha_hora'));
        }
        catch (\Throwable $e) {
            $bandera = '4';
            $cita = null;
            $fecha_hora = null;

            return view('recepcion.confirmacion', compact('cita', 'bandera', 'fecha_hora'));
        }
    }
}