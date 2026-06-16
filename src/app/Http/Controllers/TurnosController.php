<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
//use App\Http\Controllers\PDFController; 
use App\Models\User;
use App\Models\Turnos;
use App\Models\TurnoDisponible;
use App\Models\Poder; 
use App\Models\Pagos; 
use App\Models\Concepto;
use App\Models\DiasInhabiles;
use App\Models\Municipios;
use App\Models\Estados;
use App\Models\Deducciones;
use App\Models\DocumentosSolicitud;
use App\Models\HistorialAbogado;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords; // para convertir números(cantidades) a letras
use DateTime;
use Illuminate\Support\Facades\Log;

class TurnosController extends Controller 
{

    private function inicialesDeSolicitud($solicitud): string
    {
        if (!$solicitud) {
            return '';
        }

        $userId = null;
        if (!empty($solicitud->conclucion_id)) {
            $userId = $solicitud->conclucion_id;
        } elseif (!empty($solicitud->user_id)) {
            $userId = $solicitud->user_id;
        }

        if (empty($userId)) {
            return '';
        }

        $usuario = User::select('id', 'name')->find($userId);
        return $usuario ? $this->obtenerInicialesNombre($usuario->name) : '';
    }

    private function etiquetaIniciales(?string $delegacionUsuario, string $iniciales): string
    {
        $delegacionUsuario = trim((string) $delegacionUsuario);
        $iniciales = trim($iniciales);

        if ($delegacionUsuario === '' || $iniciales === '') {
            return '';
        }

        $map = [
            'Morelia' => 'DRM',
            'Uruapan' => 'DRU',
            'Zamora' => 'DRZ',

            'Zitácuaro' => 'DRM - OAZ',
            'Sahuayo' => 'DRZ - OAS',
            'Lázaro Cárdenas' => 'DRU - OAL',
        ];

    $codigo = $map[$delegacionUsuario] ?? '';

    return $codigo !== '' ? "* {$codigo}" : '*';
    }

    public function ver_documento_subido($id)
    {
        $doc = DocumentosSolicitud::findOrFail($id);

        $path = 'documentosSolicitud/' . $doc->nombre_documento;

        if (!Storage::exists($path)) {
            abort(404, 'Documento no encontrado en almacenamiento.');
        }

        $downloadName = $doc->tipo_documentos ?: $doc->nombre_documento;
        return response()->file(Storage::path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }
    public function destroy($id)
    {
        $user = User::find($id)->delete();
        return redirect()->route('usuarios');
    }

    public function misturnos(){
        $id = auth()->user()->id;
        //$fecha_actual = date('Y-m-d');

        /////Validar si es auxiliar o exepcion /////
        $misturnos = Turnos::where('auxiliar', $id)
        ->where('estatus', 'no atendido')
        ->get();

        return view('turnos.misturnos',compact('misturnos'));
    }

    public function terminado($id)
    {
        // $id es la variable de la tabla de turnos
        //Obtenemos el id de del auxiliar que esta terminado el turno 
        $turnos = Turnos::where('id', $id)->first();
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
        $turno = Turnos::find($id);
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

            $turno = Turnos::find($id);
            $turno->update($turno_update);

            $persona = DB::table('turno_disponible')
            ->where('id_auxiliar', $usuariosauxiliares[0]["id"])
            ->where('fecha', $fecha_actual)
            ->update(['estatus' => 'Ocupado']);
        }
        else if($id == 3 || $id == 5 || $id ==7 ){
            $ocupados = Turnos::where('fecha', $fecha_actual)
            ->where('auxiliar', 0)
            ->where('tipo', 'Solicitud')
            ->orderBy('id', 'asc')->first();
            //Si hay fila se va asiganar el primero de la fila al axulilar libre
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
        else{
            $ocupados = Turnos::where('fecha', $fecha_actual)
            ->where('auxiliar', 0)
            ->orderBy('id', 'asc')->first();
            //Si hay fila se va asiganar el primero de la fila al axulilar libre
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

        return redirect()->route('misturnos');
    }

    public function turnos(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('Y-m-d');

        $turnos = DB::table('turnos')
        ->where('turnos.fecha', $fecha_actual)
        ->where('turnos.delegacion', $user["delegacion"])
        ->where('turnos.estatus','no atendido')
        ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
        ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.exepcion')
        ->get();

        
        return view('turnos.turnos',compact('turnos'));
    }

    public function estadistica(){
        $id = auth()->user()->id;
        $user = User::find($id);

        $auxiliares = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        return view('turnos.estadistica',compact('auxiliares'));
    }

    public function mostrar(Request $request){
        //Voy a recibir todos los parametros en voy a realizar la consulta y mostrar los datos
        $data = $request->all();

        request()->validate([
            'fecha_inicial' => 'required|date',
            'fecha_final'   => 'required|date',
        ], $data);

        $id = auth()->user()->id;
        $user = User::find($id);


        if($data["auxiliares"] == "" && $data["tipo"] == ""){
            $suma_turnos = DB::table('turnos')
            ->where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->selectRaw('count(id) as total')
            ->first();

            $turnos = Turnos::where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
            ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.hora_fin','turnos.updated_at')
            ->get();

            
        }
        //Solo se agrego el auxiliar
        else if($data["auxiliares"] != "" && $data["tipo"] == ""){
            $suma_turnos = DB::table('turnos')
            ->where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.auxiliar',$data["auxiliares"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->selectRaw('count(id) as total')
            ->first();


            $turnos = Turnos::
            where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.auxiliar',$data["auxiliares"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
            ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.hora_fin','turnos.updated_at')
            ->get();
        }
        else if($data["auxiliares"] == "" && $data["tipo"] != ""){
            if($data["tipo"] == "exepcion"){
                $suma_turnos = DB::table('turnos')
                ->where("turnos.fecha",">=",$data["fecha_inicial"])
                ->where('turnos.fecha',"<=", $data["fecha_final"])
                ->where('turnos.exepcion',"Si")
                ->where('turnos.delegacion', $user["delegacion"])
                ->selectRaw('count(id) as total')
                ->first();

                $turnos = Turnos::
                where("turnos.fecha",">=",$data["fecha_inicial"])
                ->where('turnos.fecha',"<=", $data["fecha_final"])
                ->where('turnos.exepcion',"Si")
                ->where('turnos.delegacion', $user["delegacion"])
                ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
                ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.hora_fin','turnos.updated_at')
                ->get();
            }
            else{
                $suma_turnos = DB::table('turnos')
                ->where("turnos.fecha",">=",$data["fecha_inicial"])
                ->where('turnos.fecha',"<=", $data["fecha_final"])
                ->where('turnos.tipo',$data["tipo"])
                ->where('turnos.delegacion', $user["delegacion"])
                ->selectRaw('count(id) as total')
                ->first();


                $turnos = Turnos::
                where("turnos.fecha",">=",$data["fecha_inicial"])
                ->where('turnos.fecha',"<=", $data["fecha_final"])
                ->where('turnos.tipo',$data["tipo"])
                ->where('turnos.delegacion', $user["delegacion"])
                ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
                ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.hora_fin','turnos.updated_at')
                ->get();
            }
        }
        else{
            $suma_turnos = DB::table('turnos')
            ->where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.tipo',$data["tipo"])
            ->where('turnos.auxiliar',$data["auxiliares"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->selectRaw('count(id) as total')
            ->first();


            $turnos = Turnos::
            where("turnos.fecha",">=",$data["fecha_inicial"])
            ->where('turnos.fecha',"<=", $data["fecha_final"])
            ->where('turnos.tipo',$data["tipo"])
            ->where('turnos.auxiliar',$data["auxiliares"])
            ->where('turnos.delegacion', $user["delegacion"])
            ->leftjoin('users', 'users.id', '=', 'turnos.auxiliar')
            ->select('users.name','turnos.id','turnos.solicitante','turnos.fecha','turnos.hora','turnos.estatus','turnos.tipo','turnos.hora_fin','turnos.updated_at')
            ->get();
        }

        return view('turnos.mostrar',compact('turnos','suma_turnos'));        
    }

    public function cambiar($id)
    {
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");
        $id_user = auth()->user()->id;
        $user = User::find($id_user);

        //Se actualizan los estatus
        $turno              = Turnos::find($id);
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
            //Validar que solo sea morelia
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

    public function terminado_confirmar($id){
        $turno = Turnos::find($id);
        return view('turnos.confirmar', compact('turno'));
    }

    public function edit(Request $request)
    {
        $data = $request->all();
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $fecha_actual = date('Y-m-d');

        $relacionEloquent = 'roles';
        $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Excepcion');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        $turno_update= array(
            'solicitante'   => $data["nombre"],
            'tipo'          => $data["tipo"],
            'edad'          => $data["edad"],
            'sexo'          => $data["sexo"],
            'conflicto'     => $data["conflicto"],
            'vulnerables'   => $data["vulnerables"],
            'estatus'       => "atendido"
        );

        $turno = Turnos::find($data["id"]);
        $turno->update($turno_update);

        
        $persona = DB::table('turno_disponible')
        ->where('id_auxiliar', $usuariosauxiliares[0]["id"])
        ->where('fecha', $fecha_actual)
        ->update(['estatus' => 'Ocupado']);



        return redirect()->route('misturnos');
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

        $turno = Turnos::find($id);
        $turno->update($turno_update);

        return redirect()->route('misturnos');
    }

    public function create_publico(){
        $estados = Estados::all();
        $municipios = Municipios::all();
        return view('citas', compact('estados','municipios'));
    }

    public function store_publico(Request $request)
    {
        $data = $request->all();
        $año_actual = date('Y');
        $fecha_actual = date('Y-m-d');
        $hora_actual =  date("H:i:s");
        $user = auth()->user()->id;
        if(isset($data["folio"])){
            request()->validate([
                'folio'             => 'required',
                'primero_trabajador'=> 'required',
                'trabajador'        => 'required',
                'trabajador_edad'   => 'required',
                'trabajador_sexo'   => 'required',
                'trabajador_curp'   => 'required',
                'tipo_identificacion'=> 'required',
                'documentoidentificacion'=> 'required',
                'fecha_inicio'      => 'required',
                /*'fecha_termino'     => 'required',*/
                'categoria'         => 'required',
                'monto'             => 'required',
                'frecuencia'        => 'required',
                'tipo_pago'         => 'required',
                'sede'              => 'required',
                'dias'              => 'required',
                'fecha'             => 'required',
                'hora'              => 'required',
                'JLCA'              => 'required',
                'motivo'            => 'required',
                'salario'           => 'required',
                'municipio_rat'     => 'required',
                'tipo_vialidad'     => 'required',
                'vialidad_calle'    => 'required',
                'colonia'           => 'required',
                'N_Ext'             => 'required',
                'cp'                => 'required',
                'num_identificacion'=> 'required',
                'estado_rat'        => 'required',
            ], $data);
        }
        /*
        // Validar que no se excedan las 2 citas por hora
        $citasExistentes = Turnos::where('fecha', $data["fecha"])
            ->where('hora', $data["hora"])
            ->where('delegacion', $data["sede"])
            ->count();

        if ($citasExistentes >= 2) {
            return back()
                ->with('error', 'Ya se han agendado las 2 citas máximas para este horario. Por favor selecciona otro.')
                ->withInput();
        }
        */
        //Buscar la proxima fecha disponible de la sede
        $numero_consecutivo = 0;
        $consecutivo  = Turnos::latest('consecutivo')
        ->where('delegacion',$data["sede"])
        ->where('año',$año_actual)->
        first();
        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        if(isset($data["folio"])){
            $representante  = Poder::find($data["folio"]);
            $ultimoRegistro = HistorialAbogado::where('id_abogado', $data["folio"])->latest()->first();

            if(!isset($representante)){
                return back()->with('error', 'El representante legal no existe');
            }
            if($representante["tipo"] == "Fisica"){
                $email  = $representante["email_patronal"];
                $telefono = $representante["telefono_patronal"];
                $curp   = $representante["curp_patronal"];
            }
            else{
                $email  = $representante["correo_representante"];
                $telefono = $representante["numero_representante"];
                $curp   = $representante["curp_representante"];
            }
            $data_insertar= array(
                'consecutivo'       => $numero_consecutivo,    
                'empresa'           => $representante["nombres_patronal"],
                'primero_empresa'   => $representante["primer_apellido_patronal"],
                'segundo_empresa'   => $representante["segundo_apellido_patronal"],
                'nombre_empresa'    => $representante["nombres_patronal"],
                'primero_trabajador'=> $data["primero_trabajador"],
                'segundo_trabajador'=> $data["segundo_trabajador"] ?? null,
                'trabajador'        => $data["trabajador"],
                'edad'              => $data["trabajador_edad"],
                'sexo'              => $data["trabajador_sexo"],
                'trabajador_curp'   => $data["trabajador_curp"],
                //'documentoCurp'     => $data["documentoCurp"],
                'tipo_identificacion'=> $data["tipo_identificacion"],
                'documentoidentificacion'=> $data["documentoidentificacion"],
                'fecha_inicio'      => $data["fecha_inicio"],
                'fecha_termino'     => $data["fecha_termino"] ?? NULL,
                'categoria'         => $data["categoria"],
                'tipo_pago'         => $data["tipo_pago"],
                'monto'             => $data["monto"],
                'frecuencia'        => $data["frecuencia"],
                'dias'              => $data["dias"],
                'auxiliar'          => 0,
                'lugar_auxiliar'    => "Recepción",
                'delegacion'        => $data["sede"],
                'estatus'           => 'Confirmado',
                'exepcion'          => 'No',
                'ine'               => $representante["ineDocumento	"],
                'representacion'    => $representante["representacionDocumento"],
                'email'             => $email,
                'telefono'          => $telefono,
                'JLCA'              => $data["JLCA"],
                'motivo'            => $data["motivo"],
                'curp_solicitante'  => $curp,
                'salario'           => $data["salario"],
                'municipio_rat'     => $data["municipio_rat"],
                'tipo_vialidad'     => $data["tipo_vialidad"],
                'calle'             => $data["vialidad_calle"],
                'colonia'           => $data["colonia"],
                'num_ext'           => $data["N_Ext"],
                'codigo_postal'     => $data["cp"],
                'idAbogado'         => $data["folio"],
                'user_id'           => $user,
                'fecha'             => $data["fecha"],
                'hora'              => $data["hora"],
                'hora_fin'          => $data["hora"],
                'num_identificacion'=> $data["num_identificacion"],
                'estado_rat'        => $data["estado_rat"],
                'año'               => $año_actual,
                'id_historial'      => $ultimoRegistro->id ?? NULL,
                'nacionalidad'      => $data["nacionalidad"],
            ); 
            $nombre = $data["trabajador"];
            
        }
        else{
            $data_insertar= array(
                'consecutivo'               => $numero_consecutivo,    
                'empresa'                   => $data["empresa"],
                'primero_empresa'           => $data["primero_empresa"],
                'segundo_empresa'           => $data["segundo_empresa"],
                'nombre_empresa'            => $data["nombre_empresa"],
                'email'                     => $data["email"],
                'telefono'                  => $data["telefono"],
                'documentoIne'              => $data["documentoIne"],
                'documentoPoder'            => $data["documentoPoder"],
                'primero_trabajador'        => $data["primero_trabajador"],
                'segundo_trabajador'        => $data["segundo_trabajador"] ?? null,
                'trabajador'                => $data["trabajador"],
                'edad'                      => $data["trabajador_edad"],
                'sexo'                      => $data["trabajador_sexo"],
                'trabajador_curp'           => $data["trabajador_curp"],
                'documentoCurp'             => $data["documentoCurp"],
                'tipo_identificacion'       => $data["tipo_identificacion"],
                'documentoidentificacion'   => $data["documentoidentificacion"],
                'fecha_inicio'              => $data["fecha_inicio"],
                'fecha_termino'             => $data["fecha_termino"],
                'categoria'                 => $data["categoria"],
                'tipo_pago'                 => $data["tipo_pago"],
                'monto'                     => $data["monto"],
                'frecuencia'                => $data["frecuencia"],
                'dias'                      => $data["dias"],
                'fecha'                     => $data["fecha"],
                'hora'                      => $data["hora"],
                'hora_fin'                  => $data["hora"],
                'auxiliar'                  => 0,
                'lugar_auxiliar'            => "Recepción",
                'delegacion'                => $data["sede"],
                'estatus'                   => 'Pendiente',
                'exepcion'                  => 'No',
                'ine'                       => $data["documentoIne"],
                'representacion'            => $data["documentoPoder"],
                'email'                     => $data["email"],
                'telefono'                  => $data["telefono"],
                'JLCA'                      => $data["JLCA"],
                'motivo'                    => $data["motivo"],
                'curp_solicitante'          => $data["curp"],
                'salario'                   => $data["salario"],
                'municipio_rat'             => $data["municipio_rat"],
                'tipo_vialidad'             => $data["tipo_vialidad"],
                'calle'                     => $data["vialidad_calle"],
                'colonia'                   => $data["colonia"],
                'num_ext'                   => $data["N_Ext"],
                'codigo_postal'             => $data["cp"],
                'estado_rat'                => $data["estado_rat"],
                'año'                       => $año_actual,
                'nacionalidad'              => $data["nacionalidad"],
            ); 
            $nombre = $data["trabajador"];
            $email  = $data["email"];
            $curp   = $data["curp"];
        }

        //Variables opcionales
        if(isset($data["Aguinaldo"])){
            $data_insertar["Aguinaldo"] =  1;
        }
        if(isset($data["Vacaciones"])){
            $data_insertar["Vacaciones"] =  1;
        }
        if(isset($data["PrimaVacacional"])){
            $data_insertar["PrimaVacacional"] = 1;
        }
        if(isset($data["PagoPTU"])){
            $data_insertar["PagoPTU"] =  1;
        }
        if(isset($data["Gratificación"])){
            $data_insertar["Gratificación"] =  1;
        }
        if(isset($data["PrimaAntigüedad"])){
            $data_insertar["PrimaAntigüedad"] =  1;
        }
        if(isset($data["Otras"])){
            $data_insertar["Otras"] =  1;
        }
        if(isset($data["Especifique"])){
            $data_insertar["Especifique"] =  $data["Especifique"];
        }
        if(isset($data["cuantificacion"])){
            $data_insertar["cuantificacion"] =  $data["cuantificacion"];
        }
        if(isset($data["tipo_otros"])){
            $data_insertar["tipo_otros"] =  $data["tipo_otros"];
        }

        if(isset($data["N_Int"])){
            $data_insert["num_int"] =  $data["N_Int"];
        }

        //Documentos si cargaron el folio
        if(isset($data["folio"])){
            $representante  = Poder::find($data["folio"]);
            if($representante["tipo"] == "Fisica"){
                $nombre_ine             = $representante["nombre_patronal"]."".$representante["primer_apellido_patronal"]."".$representante["segundo_apellido_patronal"]."-".$representante["empresa"]."_IDENTIFICACION.pdf";
            }
            else{
               $nombre_ine             = $representante["nombres_patronal"]."_IDENTIFICACION.pdf";
            }
        }
        else{
            //Se carga el INE del abogado
            $nombre_ine = $data["nombre_empresa"]."".$data["primero_empresa"]."".$data["segundo_empresa"]."-".$data["empresa"]."_IDENTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoIne'), $nombre_ine
            );
            
            //Se carga el Poder del abogado
            $nombre_representación = $data["nombre_empresa"]."".$data["primero_empresa"]."".$data["segundo_empresa"]."-".$data["empresa"]."_PODER.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoPoder'), $nombre_representación
            );
        }
        
        /*
        $trabajador_curp = $data["trabajador_curp"].".pdf";
        $path = Storage::putFileAs(
            'documentos_ratificacion', $request->file('documentoCurp'), $trabajador_curp
        );
        */
        $trabajador_identificacion  = $data["trabajador_curp"]."_IDENTIFICACION.pdf";
        $path = Storage::putFileAs(
            'documentos_ratificacion', $request->file('documentoidentificacion'), $trabajador_identificacion
        );

        $data_insertar["ine"]                       = $nombre_ine;
        $data_insertar["representacion"]            = "";   
        //$data_insertar["documentoCurp"]             = $trabajador_curp;
        $data_insertar["documentoidentificacion"]   = $trabajador_identificacion;  


        if(isset($data["cuantificacion"])){
            $cuantificacion  = $data["trabajador_curp"]."_CUANTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('cuantificacion'), $cuantificacion
            );
            $data_insertar["documentoCuanti"] = $cuantificacion;
        }
        if(isset($data["N_Int"])){
            $data_insert["num_int"] =  $data["N_Int"];
        }
        //Se van insetar todos los datos
        Turnos::create($data_insertar);
       /*
        //Revisar si ya existe el correo
        $usuario = User::where('email',$email)->first();
        if(!isset($usuario)){
            $data_insertar_user= array(
                'name'              => $nombre,
                'email'             => $email,
                //'delegacion'        => $data["sede"],
                'delegacion'        => "Morelia",
                'type'              => "Seer",
                'remember_token'    => $curp,
                'profile_photo_path'=> $curp
            ); 
            //Genrar un random del uno al 100 y agregarlo a la contraseña
            $numero_aleatorio = mt_rand(1, 1000);

            //Hacemos un hash del campo que tiene el password
            $data_insertar_user['password'] = Hash::make("CCLMICHOACAN".$numero_aleatorio);
            $usuario = User::create($data_insertar_user);
            $usuario->assignRole(('Solicitante'));
            $mensaje = " el correo:".$usuario["email"]." y la contraseña:CCLMICHOACAN".$numero_aleatorio." para continuar tú trámite.";
        }
        else{
            $mensaje = " el correo:".$usuario["email"]." para continuar tú trámite.";
        }
        
        */
        return back()->with('success', 'Debes ingresar a '. 
        ' http://siconcilio.cclmichoacan.gob.mx/ en el apartado de buzón electrónico.'  ); 
    }

    public function pagoA_ratificacion(Request $request){
        $data = $request->all();
        Pagos::find($data["id"])
        ->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"], 'fecha_conclucion' => \Carbon\Carbon::now()->format('Y-m-d')]);

        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();

        if(count($faltantes) == 0){
            Turnos::find($id_solicitud)
            ->update(['estatus' => "Concluida"]);
        }

        return redirect()->route('todas_ratificaciones');
    }

    public function obtenerHorario($fecha_revisar,$sede){
        $array_final = array();
        $array_horarios = array();
        $array_horarios[] = array('hora' => "09:00:00");
        $array_horarios[] = array('hora' => "10:00:00");
        $array_horarios[] = array('hora' => "11:00:00");
        $array_horarios[] = array('hora' => "12:00:00");
        $array_horarios[] = array('hora' => "13:00:00");
        $array_horarios[] = array('hora' => "14:00:00");
        $array_horarios[] = array('hora' => "15:00:00");

        //Turnos no disponibles
        $turnos = Turnos::where('fecha', $fecha_revisar)
        ->where('delegacion',$sede)
        ->select('hora')
        ->get();
        
        $contador=0;
        foreach($array_horarios as $horario){
            

            foreach($turnos as $turno){
                

                if($turno["hora"] != $horario["hora"]){
                    $contador ++;
                    array_push($array_final, $turno);
                    break;
                }


            }

        }

        return $array_horarios;
        //->where('hora', $hora_solicitud)
        //->where('delegacion', $data["sede"])->get();
        //return Municipios::where('estado', $id)->get();
    }

    public function obtenerEventos(Request $request)
    {
        $fecha_inicio = now()->subDays(20)->format('Y-m-d');
        $fecha_fin = now()->addDays(20)->format('Y-m-d');
        $sede = $request->input('sede'); // Obtener sede de la solicitud

        $inhabiles = DiasInhabiles::whereNull('user_id')
            ->where('centro', $sede)
            ->whereIn('descripcion', ['Inhabil', 'No inhabil'])
            ->whereIn('tipo', ['Ratificaciones', 'Todos'])
            ->get(); //Obtenemos días inhabiles

        /* Obtener turnos ocupados filtrando por sede
        $ocupados = Turnos::whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->where('delegacion', $sede) // FILTRO POR SEDE
            ->get()
            ->map(function ($turno) {
                return [
                    'title' => 'Ocupado',
                    'start' => $turno->fecha . 'T' . $turno->hora,
                    'color' => '#DA0909',
                    'extendedProps' => ['estado' => 'ocupado']
                ];
            });*/

        $todosLosEventos = [];
        $fecha = new \DateTime($fecha_inicio);
        $fin = new \DateTime($fecha_fin);

        while ($fecha <= $fin) {
            if ($fecha->format('N') < 6) { 
                $slotDt = new \DateTime($fecha->format('Y-m-d') . ' 08:30:00');
                $slotEndDt = new \DateTime($fecha->format('Y-m-d') . ' 16:00:00');

                while ($slotDt <= $slotEndDt) {
                        $hora_str = $slotDt->format('H:i:s');
                        $slotStart = $fecha->format('Y-m-d') . 'T' . $slotDt->format('H:i');
                        $ahora = new DateTime();
                        $currentCita = new DateTime($slotStart);

                        // Verifica si el slot está ocupado
                        $citasExistentes = Turnos::where('fecha', $fecha->format('Y-m-d'))
                            ->where('hora', $hora_str)
                            ->where('delegacion', $sede)
                            ->count();

                        $esInhabil = false;
                        $esNoInhabil = false;
                        foreach($inhabiles as $dia){
                            $fechaInhabilInicio = $dia->fecha_inicio . 'T' . $dia->horario_inicio;
                            $fechaInhabilFinal = $dia->fecha_final . 'T' . $dia->horario_final;
                            if($slotStart >= $fechaInhabilInicio && $slotStart <= $fechaInhabilFinal){
                                if ($dia->descripcion === 'No inhabil') {
                                    $esNoInhabil = true;
                                } else {
                                    $esInhabil = true;
                                }
                                break;
                            }
                        }

                        //Comparación de fechas de días inhábiles

                        /*$fechaTurno = $fecha->format('Y-m-d');
                        $sedeTurno = $sede;
                        $esInhabil = false;
                        foreach ($inhabiles as $dia){
                            if ($fechaTurno >= $dia->fecha_inicio && $fechaTurno <= $dia->fecha_final && ($dia->centro == $sedeTurno || $dia->centro == $sedeTurno) ){
                                $esInhabil = true;
                                break;
                            }
                        }*/

                        $disponibles = 1 - $citasExistentes;
                        $ocupado = $disponibles <= 0;
                        
                        if ($ocupado) {
                            $todosLosEventos[] = [
                                'title' => 'Ocupado',
                                'start' => $slotStart,
                                'color' => '#DA0909',
                                'extendedProps' => ['estado' => 'ocupado', 'espacios_disponibles' => 0]
                            ];
                        } else if ($esInhabil){
                            $todosLosEventos[] = [
                                'title' => 'Inhábil',
                                'start' => $slotStart,
                                'color' => '#3B78DB',
                                'extendedProps' => ['estado' => 'inhabil', 'espacios_disponibles' => 0]
                            ];
                        } else if ($esNoInhabil || $ahora > $currentCita){
                            $titulo = $esNoInhabil ? 'No disponible' : 'Expirado';
                            $todosLosEventos[] = [
                                'title' => $titulo,
                                'start' => $slotStart,
                                'color' => '#F59727',
                                'extendedProps' => ['estado' => 'expirado', 'espacios_disponibles' => 0]
                            ];
                        } else {
                            $todosLosEventos[] = [
                                'title' => "Disponible",
                                'start' => $slotStart,
                                'color' => '#00CE1C',
                                'extendedProps' => ['estado' => 'disponible', 'espacios_disponibles' => $disponibles]
                            ];
                        }

                        /*foreach ($todosLosEventos as &$evento) {
                            foreach ($inhabiles as $dia) {
                                $fechaInhabilInicio = $dia->fecha_inicio . 'T' . $dia->horario_inicio;
                                $fechaInhabilFinal = $dia->fecha_final . 'T' . $dia->horario_final;
                                if ($evento['start'] >= $fechaInhabilInicio && $evento['start'] <= $fechaInhabilFinal) {
                                    $evento['title'] = 'Inhábil';
                                    $evento['color'] = '#970EE3';
                                    $evento['extendedProps']['estado'] = 'inhabil';
                                    break;
                                }
                            }
                        }
                        unset($evento);*/

                        $slotDt->modify('+30 minutes');
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
    }

    //PDF Acuse de Ratificación
    public function VerPDF($id){
        $solicitud = Turnos::find($id);
        $montoTexto = $this->convertirNumerosALetras($solicitud->monto);
        $pdf = \PDF::loadView('PDF/ratificacion', compact('id','solicitud','montoTexto'))
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true);

        $nombreArchivo = 'ratificaion_' . $solicitud->empresa .'.pdf';

        return $pdf->stream($nombreArchivo);               
    }

    public function VerPDFConvenio($id){
        $solicitud = Turnos::find($id);

        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $delegacion = $solicitud->delegacion;
        $delegadosEspeciales = [
                'Zitácuaro'        => 11,
                'Lázaro Cárdenas'  => 43,
                'Sahuayo'          => 26,
            ];

        if (array_key_exists($delegacion, $delegadosEspeciales)) {
            $delegado = User::select('id', 'name', 'delegacion')
                ->find($delegadosEspeciales[$delegacion]);
        } else {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');   
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();
        }
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();
        /*$abogado  = Poder::join("turnos","turnos.idAbogado","=","abogados.idAbogado");
        $abogado = $abogado->where("turnos.id", "=", $id)
        ->first();*/

        if($solicitud->id_historial){
            $abogado = HistorialAbogado::join("turnos", "turnos.id_historial", "=", "historial_abogados.id")
            ->where("turnos.id", "=", $id)
            ->select(
                "historial_abogados.*",
                "turnos.tipo_identificacion as tipo_identificacion_turno",
                "turnos.num_identificacion as num_identificacion_turno"
            )
            ->first();
        } else {
            $abogado = Poder::join("turnos", "turnos.idAbogado", "=", "abogados.idAbogado")
            ->where("turnos.id", "=", $id)
            ->select(
                "abogados.*",
                "turnos.tipo_identificacion as tipo_identificacion_turno",
                "turnos.num_identificacion as num_identificacion_turno"
            )
            ->first();
        }

        // $abogado = Poder::where('idAbogado', $id)->get();
        //dd($abogado);
        //$prestacionesLab = Concepto::where('id_solicitud', $id)->first();
        //dd($prestaciones);
        $municipio = Municipios::find($solicitud->municipio_rat);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($solicitud->estado_rat);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';

        // Obtener prestaciones y deducciones
        $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();

        $conceptosTexto = [];
        $deduccionesTexto = [];

        foreach ($prestaciones as $concepto) {
            $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
        }

        foreach ($deducciones as $deduccion) {
            $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
        }

        $totalPrestaciones = $prestaciones->sum('monto');
        $totalDeducciones = $deducciones->sum('monto');
        $pagoTotal = $totalPrestaciones - $totalDeducciones;

        $dias_descanso = $solicitud->dias !== null ? 7 - $solicitud->dias : null;
        $salario_diario = $this->calcularSalarioDiario($solicitud->salario, $solicitud->frecuencia);
        $salario_mensual = $salario_diario * 30;

        $diarioTexto = $this->convertirNumerosALetras($salario_diario);
        $mensualTexto = $this->convertirNumerosALetras($salario_mensual);
        $montoTexto = $this->convertirNumerosALetras($solicitud->monto);

        // Obtener el número de pagos
        $pagosDif = Pagos::join("turnos", "turnos.id", "=", "pago_solicitud.id_solicitud")
            ->where("pago_solicitud.id_solicitud", "=", $id)
            ->where("pago_solicitud.tipo_pago", "=", "Ratificacion")
            ->select(DB::raw('count(pago_solicitud.id_solicitud) as C_pagos'))
            ->first();
        $conciliador = User::join("turnos", "turnos.id_conciliador", "=", "users.id")
            ->where("turnos.id", "=", $id)
            ->select('users.name')
            ->first();

        // Descripción del tipo de identificación para los solicitantes y poderes
        $identificacionSolicitante = $solicitud->tipo_identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);
        $identificacionPoder = $abogado->tipo_identificacion;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);

        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);

        $html = view('PDF/convenioRatificacion', 
            compact(
                'id', 'solicitud', 'dias_descanso', 'salario_diario', 'salario_mensual', 'pagos', 
                'diarioTexto', 'mensualTexto', 'montoTexto', 'conceptosTexto', 'deduccionesTexto', 
                'pagosDif', 'conciliador', 'abogado', 'municipioEmpresa', 'estadoEmpresa', 'pagoTotal', 
                'descripcionIdentificacionS', 'descripcionIdentificacionP','prestaciones','deducciones','delegado',
                'inicialesConcluye', 'etiquetaIniciales'
            )
        )->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('Convenio_terminacion_'. $solicitud->trabajador .'.pdf');      
    }

    public function calcularSalarioDiario($salario, $frecuencia) {
        switch ($frecuencia) {
            case 'Diario':
                return $salario;
            case 'Semanal':
                return $salario / 7; 
            case 'Quincenal':
                return $salario / 15; 
            case 'Mensual':
                return $salario / 30;
            default:
                return 0;
        }
    }

    private function convertirNumerosALetras($valor) {
        $valor = (float) $valor;
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');
        $parteEntera = floor($valor);
        $parteDecimal = round(($valor - $parteEntera) * 100);
        $letras = $parteEntera > 0
            ? strtoupper($numberTransformer->toWords($parteEntera))
            : 'CERO';
        $centavos = str_pad($parteDecimal, 2, '0', STR_PAD_LEFT);
        if ($parteEntera == 0 && $parteDecimal > 0) {
            return "CERO PESOS {$centavos}/100 M.N.";
        }
        return "{$letras} PESOS {$centavos}/100 M.N.";
        /*$numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es'); 

        $parteEntera = floor($valor);
        $letras = strtoupper($numberTransformer->toWords($parteEntera)); 

        $parteDecimal = round(($valor - $parteEntera) * 100);
        $centavos = str_pad($parteDecimal, 2, '0', STR_PAD_LEFT); 
        return "{$letras} PESOS {$centavos}/100";*/
    }

    private function obtenerInicialesNombre(?string $nombre): string
    {
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            return '';
        }

        $partes = preg_split('/\s+/u', $nombre, -1, PREG_SPLIT_NO_EMPTY);
        if (!$partes) {
            return '';
        }

        $iniciales = '';
        foreach ($partes as $parte) {
            $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
        }

        return mb_strtolower($iniciales, 'UTF-8');
    }

    //PDF Acta de multa
    public function VerPDFMulta($id){
        $solicitud = Turnos::find($id);

        $html = view('PDF/ActaMulta', compact('id', 'solicitud'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'multa_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Acta por falta de interés
    public function VerPDFInteres($id){
        $solicitud = Turnos::find($id);
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id", "=", $id)
        ->select('users.name')
        ->first();

        $html = view('PDF/ActaFaltaInteres', compact('id', 'solicitud','conciliador'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'falta_de_interes_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Constancia de cumplimiento
    public function VerPDFCumplimiento($id){
        $solicitud = Turnos::find($id);
        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id", "=", $id)
        ->select('users.name')
        ->first();
        $delegacion = $solicitud->delegacion;
        $delegadosEspeciales = [
                'Zitácuaro'        => 11,
                'Lázaro Cárdenas'  => 43,
                'Sahuayo'          => 26,
            ];

        if (array_key_exists($delegacion, $delegadosEspeciales)) {
            $delegado = User::select('id', 'name', 'delegacion')
                ->find($delegadosEspeciales[$delegacion]);
        } else {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();
        }
    $html = view('PDF/ConstanciaCumplimiento', compact('id', 'solicitud','conciliador','pagos','delegado','inicialesConcluye','etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_cumplimiento_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Acta de Audiencia
    public function VerPDFAudiencia($id){
        $solicitud = Turnos::find($id);
        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();

        if($solicitud->id_historial){
            $abogado = HistorialAbogado::join("turnos", "turnos.id_historial", "=", "historial_abogados.id")
            ->where("turnos.id", "=", $id)
            ->select(
                "historial_abogados.*",
                "turnos.tipo_identificacion as tipo_identificacion_turno",
                "turnos.num_identificacion as num_identificacion_turno"
            )
            ->first();
        } else {
            $abogado = Poder::join("turnos", "turnos.idAbogado", "=", "abogados.idAbogado")
            ->where("turnos.id", "=", $id)
            ->select(
                "abogados.*",
                "turnos.tipo_identificacion as tipo_identificacion_turno",
                "turnos.num_identificacion as num_identificacion_turno"
            )
            ->first();
        }

        //$prestacionesLab = Concepto::where('id_solicitud', $id)->first();
        $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago','Ratificacion')->get();

        $conceptosTexto = [];
        $deduccionesTexto = [];

        foreach ($prestaciones as $concepto) {
            $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
        }

        foreach ($deducciones as $deduccion) {
            $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
        }

        $totalPrestaciones = $prestaciones->sum('monto');
        $totalDeducciones = $deducciones->sum('monto');
        //Total a pagar
        $pagoTotal= $totalPrestaciones-$totalDeducciones;
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id", "=", $id)
        ->select('users.name')
        ->first();
        
        //Descripción del tipo de identificación para los solicitantes
        $identificacionSolicitante = $solicitud->tipo_identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);

        //Descripción del tipo de identificación para los poderes
        $identificacionPoder = $abogado->tipo_identificacion;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);

    $html = view('PDF/ActaAudiencia', compact('id','solicitud','conciliador','prestaciones','deducciones','deduccionesTexto','pagoTotal','descripcionIdentificacionS',
    'descripcionIdentificacionP','abogado','conceptosTexto','inicialesConcluye','etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'acta_de_audiencia_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Constancia de Incumplimiento
    public function VerPDFIncumplimiento($id){
        $solicitud = Turnos::find($id);
        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);
        $pagos = Pagos::find($id);
       
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id", "=", $id)
        ->select('users.name')
        ->first();
        $salario_diario = $this->calcularSalarioDiario($solicitud->salario, $solicitud->frecuencia);

    $html = view('PDF/Incumplimiento', compact('id', 'solicitud','conciliador','salario_diario','pagos','inicialesConcluye','etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incumplimiento_'  .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Constancia de Incumplimiento Parcial
    public function VerPDFInParcial($id){
        $pagos = Pagos::find($id);
        $solicitud = Turnos::find($pagos["id_solicitud"]);
        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);
        $salario_diario = $this->calcularSalarioDiario($solicitud->salario, $solicitud->frecuencia);

        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id_conciliador", "=", $solicitud["id_conciliador"])
        ->select('users.name')
        ->first();
        
    $html = view('PDF/incumplimientoParcial', compact('id', 'solicitud','conciliador','pagos','salario_diario','inicialesConcluye','etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incumplimiento_parcial'  .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    //PDF Constancia de Pago Parcial
    public function VerPDFPagos($id){
        $pagos = Pagos::find($id);
        $solicitud = Turnos::find($pagos["id_solicitud"]);
        $inicialesConcluye = $this->inicialesDeSolicitud($solicitud);
        $etiquetaIniciales = $this->etiquetaIniciales($solicitud->delegacion ?? null, $inicialesConcluye);
        $pagosDif = Pagos::where('id_solicitud', $pagos->id_solicitud)->count();
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id_conciliador", "=", $solicitud["id_conciliador"])
        ->select('users.name')
        ->first();
        $delegacion = $solicitud->delegacion;
        $delegadosEspeciales = [
                'Zitácuaro'        => 11,
                'Lázaro Cárdenas'  => 43,
                'Sahuayo'          => 26,
            ];

        if (array_key_exists($delegacion, $delegadosEspeciales)) {
            $delegado = User::select('id', 'name', 'delegacion')
                ->find($delegadosEspeciales[$delegacion]);
        } else {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();
        }
    $html = view('PDF/pagosParciales', compact('id','solicitud','conciliador','pagos','pagosDif','delegado','inicialesConcluye','etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_pago_'  .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }
    
    public function index_empresa(){
        $id = auth()->user()->id;
        $user = User::find($id);

        $solicitudes = Turnos::join('users','turnos.curp_solicitante','=','users.profile_photo_path')
        ->where('turnos.tipo','Ratificación')
        ->where('turnos.curp_solicitante',$user["profile_photo_path"])
        ->select('turnos.id','turnos.fecha','turnos.empresa','turnos.trabajador','turnos.telefono','turnos.email','turnos.estatus')
        ->get();
        return view('/solicitudes/misratificaciones',compact('solicitudes'));
    }

    public function indexr(){
        $solicitudes = Turnos::where('tipo','Ratificación')
        ->whereIn('estatus', ['Pendiente', 'Prevencion'])
        ->get();
        return view('/ratificaciones/indexr',compact('solicitudes'));
    }

    public function aceptacion($id){
        $turno = Turnos::find($id);

        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $usuariosauxiliares = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $turno["delegacion"])
        ->get();
        
        foreach($usuariosauxiliares as $token ){
            //Validar que solo sea morelia
            array_push($listado_auxiliares, $token["id"]);
        }
        //validar si hay disponibles
        $random = array_rand($listado_auxiliares);
        $conciliador = $listado_auxiliares[$random];        
        $user = User::find($conciliador);
        $expediente = $this->GeneraExpediente($turno["consecutivo"],$turno["delegacion"]);

        Turnos::find($id)->update(['auxiliar' => $user["id"],'lugar_auxiliar' => $user["name"],'estatus' => 'Confirmado','NUE' => $expediente, 'id_conciliador' => $user["id"]]);
        return redirect()->route('Ratificacion');
    }

    public function guardar_rechazo(Request $request){
        $data = $request->all();
        Turnos::find($data["id"])->update(['estatus' => 'Prevencion','observaciones' => $data["observaciones"]]);

        return redirect()->route('Ratificacion');
    }

    public function revisar_ratificaciones_hoy(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('Y-m-d');

        $solicitudes = Turnos::where('tipo','Ratificación')
        /*->where('auxiliar',$user["id"])
        ->where('estatus','Confirmado')
        ->orwhere('estatus','Concluida')
        ->orwhere('estatus','Concluida Pagos')
        ->orwhere('estatus','Incumplimiento')
        ->orwhere('estatus','Archivada')
        */
        ->where('fecha',$fecha_actual)
        ->get();
        
        return view('/solicitudes/indexauxiliar',compact('solicitudes'));
    }

    public function concluir_ratificaciones($id){
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $sede = $user->delegacion;

        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $relacionEloquent = 'roles';

        if($sede == "Morelia" || $sede =='Zitácuaro'){
            $delegaciones = ['Morelia', 'Zitácuaro'];
        }else if($sede == "Uruapan" || $sede == "Lázaro Cárdenas"){
            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
        }else if($sede == "Zamora" || $sede =='Sahuayo'){
            $delegaciones = ['Zamora', 'Sahuayo'];
        }

        $conciliadores = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->whereIn('delegacion', $delegaciones)
        ->get();
        $turno = Turnos::find($id);
        $motivo = $turno ? $turno->motivo : null;
        return view('/ratificaciones/concluir',compact('id','conciliadores', 'turno', 'motivo'));
    }

    public function consultar_ratificaciones($id){
        $folio = Turnos::find($id);
        //Validar si existe el abogado
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
        $representante = Poder::find($folio["idAbogado"]);
        if(isset($representante)){
            $ruta_abogado = 'documentos_abogados';
        }
        else{
            $ruta_abogado = 'documentos_ratificacion';
        }
        //dd($ruta_abogado);
        return view('/ratificaciones/verratificacion',compact('folio','ruta_abogado','userRole','representante'));
    }

    public function editar_ratificaciones(Request $request){
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
        //Validar si existe el documnento nuevo
        if(isset($data["documentoIne"])){
            $nombre_ine = $data["nombres"]."".$data["primer_apellido"]."".$data["segundo_apellido"]."-".$data["empresa"]."_IDENTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoIne'), $nombre_ine
            );
        }
        if(isset($data["documentoRepresentacion"])){
            $nombre_representación = $data["nombre_empresa"]."".$data["primero_empresa"]."".$data["segundo_empresa"]."-".$data["empresa"]."_PODER.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoRepresentacion'), $nombre_representación
            );
        }
        if(isset($data["documentoCurp"])){
            $trabajador_curp = $data["trabajador_curp"].".pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoCurp'), $trabajador_curp
            );
        }
        if(isset($data["documentoidentificacion"])){
            $trabajador_identificacion = $data["trabajador_curp"]."_IDENTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('documentoidentificacion'), $trabajador_identificacion
            );
        }
        //Variables opcionales
        if(isset($data["Aguinaldo"]) && $data["motivo"] == "Pago de prestaciones"){
            $Aguinaldo =  1;
        }
        else{
            $Aguinaldo =  0;
        }
        if(isset($data["Vacaciones"]) && $data["motivo"] == "Pago de prestaciones"){
            $Vacaciones =  1;
        }
        else{
            $Vacaciones =  0;
        }
        if(isset($data["PrimaVacacional"]) && $data["motivo"] == "Pago de prestaciones"){
            $PrimaVacacional = 1;
        }
        else{
            $PrimaVacacional = 0;
        }
        if(isset($data["PagoPTU"]) && $data["motivo"] == "Pago de prestaciones"){
            $PagoPTU =  1;
        }
        else{
            $PagoPTU = 0;
        }
        if(isset($data["Gratificación"]) && $data["motivo"] == "Pago de prestaciones"){
            $Gratificación =  1;
        }
        else{
            $Gratificación = 0;
        }
        if(isset($data["PrimaAntigüedad"]) && $data["motivo"] == "Pago de prestaciones"){
            $PrimaAntigüedad =  1;
        }
        else{
            $PrimaAntigüedad = 0;
        }
        if(isset($data["Otras"]) && $data["motivo"] == "Pago de prestaciones"){
            $Otras =  1;
        }
        else{
            $Otras =  0;
        }
        if(isset($data["Especifique"]) && $data["motivo"] == "Pago de prestaciones"){
            $Especifique =  $data["Especifique"];
        }
        else{
            $Especifique = 0;
        }
        //Agregar todos los campos de la tabla turnos
        if($userRole[0] == "Solicitante"){
            $data_update = Turnos::find($data["id"])
            ->update([
                //'empresa'                       => $data["empresa"],
                //'primero_empresa'               => $data["primero_empresa"],
                //'segundo_empresa'               => $data["segundo_empresa"],
                //'nombre_empresa'                => $data["nombre_empresa"],
                //'curp_solicitante'              => $data["curp_solicitante"],
                //'telefono'                      => $data["telefono"],
                'trabajador'                    => $data["nombre_trabajador"],
                'primero_trabajador'            => $data["primer_apellidot"],
                'segundo_trabajador'            => $data["segundo_apellidot"],
                'edad'                          => $data["edad"],
                'sexo'                          => $data["sexo"],
                'trabajador_curp'               => $data["trabajador_curp"],
                'email'                         => $data["email"],
                'telefono'                      => $data["telefono"],
                'tipo_identificacion'           => $data["tipo_identificacion"],
                'fecha_inicio'                  => $data["fecha_inicio"],
                'fecha_termino'                 => $data["fecha_termino"],
                'categoria'                     => $data["categoria"],
                'frecuencia'                    => $data["frecuencia"],
                'salario'                       => $data["salario"],
                'dias'                          => $data["dias"],
                'motivo'                        => $data["motivo"],
                'Aguinaldo'                     => $Aguinaldo,
                'Vacaciones'                    => $Vacaciones,
                'PrimaVacacional'               => $PrimaVacacional,
                'PagoPTU'                       => $PagoPTU,
                'Gratificación'                 => $Gratificación,
                'PrimaAntigüedad'               => $PrimaAntigüedad,
                'Otras'                         => $Otras,
                'Especifique'                   => $Especifique,
                'monto'                         => $data["monto"],
                'tipo_pago'                     => $data["tipo_pago"],
                'estatus'                       => 'Pendiente',
            ]);
        }
        else{
            $data_update = Turnos::find($data["id"])
            ->update([
                //'empresa'                       => $data["empresa"],
                //'primero_empresa'               => $data["primero_empresa"],
                //'segundo_empresa'               => $data["segundo_empresa"],
                //'nombre_empresa'                => $data["nombre_empresa"],
                //'curp_solicitante'              => $data["curp_solicitante"],
                //'telefono'                      => $data["telefono"],
                'trabajador'                    => $data["nombre_trabajador"],
                'primero_trabajador'            => $data["primer_apellidot"],
                'segundo_trabajador'            => $data["segundo_apellidot"],
                'edad'                          => $data["edad"],
                'sexo'                          => $data["sexo"],
                'trabajador_curp'               => $data["trabajador_curp"],
                'email'                         => $data["email_trabajador"],
                'tipo_identificacion'           => $data["tipo_identificacion"],
                'fecha_inicio'                  => $data["fecha_inicio"],
                'fecha_termino'                 => $data["fecha_termino"],
                'categoria'                     => $data["categoria"],
                'frecuencia'                    => $data["frecuencia"],
                'salario'                       => $data["salario"],
                'dias'                          => $data["dias"],
                'motivo'                        => $data["motivo"],
                'Aguinaldo'                     => $Aguinaldo,
                'Vacaciones'                    => $Vacaciones,
                'PrimaVacacional'               => $PrimaVacacional,
                'PagoPTU'                       => $PagoPTU,
                'Gratificación'                 => $Gratificación,
                'PrimaAntigüedad'               => $PrimaAntigüedad,
                'Otras'                         => $Otras,
                'Especifique'                   => $Especifique,
                'monto'                         => $data["monto"],
                'tipo_pago'                     => $data["tipo_pago"],
                'observaciones'                 => $data["observaciones"],
            ]);
        }

        if($userRole[0] == "Auxiliar")
            return redirect()->route('ratificacion_atender');
        else if($userRole[0] == "Solicitante")
            return redirect()->route('ratificacion');
        else if($userRole[0] == "Administrador Solicitante")
            return redirect()->route('Ratificacion');
    }
    
    public function guardar_manifestacion(Request $request){
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $sede = $user->delegacion;

        //Revisar si existe
        if(isset($data["dias_pagos"])){
            $conteo = count($data["dias_pagos"]);
            for($i = 0; $i < $conteo; $i++) {
                $data_citado = [
                    'id_solicitud'  => $data["id"],
                    'fecha'         => $data["dias_pagos"][$i],
                    'hora'          => $data["hora_pagos"][$i], 
                    'monto'         => $data["monto_pagos"][$i], 
                    'descripcion'   => $data["descripcion_pagos"][$i],
                    'estatus'       => "Pendiente", 
                    'tipo_pago'     => "Ratificacion",
                    'delegacion'    => $sede,              
                ];
                Pagos::create($data_citado);
            }
        }
        //Regresar error
        else{
            return back()->withErrors('Debes agregar por lo menos una fecha de pago.');
        }
        if(isset($data["tipo_pago"])){
            $tiposPago = $data["tipo_pago"];
            $cont = count($data["monto_pago"]);
            $otrasPrestaciones = $data["otra_prestacion"] ?? [];
        
            for($i = 0; $i < $cont; $i++) {
                $descripcion = $tiposPago[$i];
                if ($descripcion === "Otras" && isset($otrasPrestaciones[$i]) && !empty(trim($otrasPrestaciones[$i]))) {
                    $descripcion = trim($otrasPrestaciones[$i]);
                }
                
                $data_citado = [
                    'id_solicitud'  => $data["id"], 
                    'monto'         => $data["monto_pago"][$i], 
                    'descripcion'   => $descripcion,
                    'tipo_pago'     => "Ratificacion"
                ];
                //dd($data_citado);
                Concepto::create($data_citado);
            }
        }
        //Regresar error
        else{
            return back()->withErrors('Debes agregar por lo menos un concepto de pago.');
        }
        if(isset($data["descripcion_deduccion"])){
            $cont = count($data["descripcion_deduccion"]);
            for($i = 0; $i < $cont; $i++) {
                $data_deduccion = [
                    'id_solicitud'  => $data["id"], 
                    'monto'         => $data["monto_deduccion"][$i], 
                    'descripcion'   => $data["descripcion_deduccion"][$i],
                    'tipo_pago'     => "Ratificacion"
                ];
                Deducciones::create($data_deduccion);
            }
        }

        if($conteo >= 2){
            $estatus = "Concluida Pagos";
        }
        else{
            $estatus = "Concluida";
        }

        //Generar numero de expediente
        $delegacion = Turnos::find($data["id"]);
        $expediente = $this->GeneraExpediente($delegacion["consecutivo"],$delegacion["delegacion"]);

        $updateData = [
            'resolucion_primera'        => $data["primera"],
            //'resolucion_trabajadores' => $data["trabajadores"],
            'resolucion_justificacion'  => $data["justificacion"],
            'resolucion_segunda'        => $data["segunda"],
            'vacaciones_dias'           => $data["vacaciones"],
            'aguinaldo_dias'            => $data["aguinaldo"],
            'otros_dias'                => $data["otros"],
            'horario'                   => $data["horario"],
            'comida'                    => $data["comida"],
            /*'domicilio'               => $data["domicilio"],*/
            'NUE'                       => $expediente,
            'id_conciliador'            => $data["conciliador_id"],
            'user_id'                   => $id_usuario,
            'estatus'                   => $estatus,
            'conclucion_id'             => $id_usuario,
            'fecha_conclucion'          => \Carbon\Carbon::now()->format('Y-m-d'),
        ];

        if (!empty($data["year_ptu"])) {
            $updateData['year_ptu'] = (int) $data["year_ptu"][array_key_first($data["year_ptu"])];
        }

        $rechazar = Turnos::find($data["id"])->update($updateData);
        
        $id_solicitud =  $data["id"];
        if($data["valor"] == 1){
            //session()->flash('show_modal', true);
            return redirect()->route('vista_previa_ratificacion',compact('id_solicitud'));
        }
        else if($data["valor"] == 2){
            return redirect()->route('todas_ratificaciones');
            //return redirect()->route('audiencias.conciliador');
        }
    }

    public function pagar_ratificacion($id){
        //Revisar todos los pagos
        $pagos = Pagos::where('id_solicitud',$id)->get();

        return view('/ratificaciones/pagos',compact('id','pagos'));
    }

    public function pagoR_ratificacion($id){
        $pagos = Pagos::find($id);
        
        $id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)
        ->update(['estatus'  => "No pagado"]);

        Turnos::find($id_solicitud)
        ->update(['estatus' => "Incumplimiento"]);

        return redirect()->route('ratificacion_atender');
    }

    public function GeneraExpediente($id,$delegacion){
        $año_actual = date('Y');
    
        if($delegacion == "Morelia"){
            $del = "MOR";
        }
        else if($delegacion == "Uruapan"){
            $del = "URU";
        }
        else if($delegacion == "Zamora"){
            $del = "ZAM";
        }
        else if($delegacion == "Zitácuaro"){
            $del = "ZIT";
        }
        else if($delegacion == "Lázaro Cárdenas"){
            $del = "LZC";
        }
        else if($delegacion == "Sahuayo"){
            $del = "SAH";
        }
        //contar el numero de ceros
        $numeroConCeros = str_pad($id, 5, "0", STR_PAD_LEFT);
        $folio = $del."/RAT"."/".$año_actual."/".$numeroConCeros;
    
        return $folio;
    }

    public function archivar_ratificacion(Request $request){
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);

        $turno = Turnos::find($data["id"]);
        $expediente = $this->GeneraExpediente($turno["consecutivo"],$turno["delegacion"]);
        Turnos::find($turno["id"])->update(['auxiliar' => $user["id"],'lugar_auxiliar' => $user["name"],'estatus' => 'Archivada','NUE' => $expediente, 'id_conciliador' => $user["id"], 'observaciones' => $data["observaciones"]]);

        return redirect()->route('ratificacion_atender');
    
    }

     public function index_ratificacion(){
        $id = auth()->user()->id;
        $user = User::find($id);

        $auxiliares = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Auxiliar');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();
        $notificadores = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Notificador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();
        $conciliadores = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        return view('/ratificaciones/index',compact('auxiliares','conciliadores'));
    }

    public function busqueda_ratificaciones(Request $request){
        $data = $request->all();
        $bandera_fechas         = 0;
        $bandera_nue            = 0;
        $bandera_curp           = 0;
        $bandera_solicitante    = 0;
        $bandera_citado         = 0;
        $bandera_folio          = 0;
        $bandera_año            = 0;
        $bandera_estatus        = 0;
        $bandera_tipo           = 0;
        $bandera_auxiliar       = 0;
        $bandera_conciliador    = 0;

        //Si existe la fecha de inicio
        if(isset($data["inicio"]) ){
            if(isset($data["final"]) ){
                if($data["inicio"] > $data["final"]){
                    return back()->withErrors('Si seleccionas una fecha de inicio, no debe ser mayor a la fecha final.');
                }
                //Agregar fecha inicio y final
                $bandera_fechas = 1;
            }
            else{
                return back()->withErrors('Si selecciones una fecha de inicio, debes seleccionar fecha final.');
            }
        }else if(isset($data["final"])){
            if(isset($data["inicio"]) ){
                if($data["inicio"] > $data["final"]){
                    return back()->withErrors('Si seleccionas una fecha de inicio, no debe ser mayor a la fecha final.');
                }
                //Agregar fecha inicio y final
                $bandera_fechas = 1;
            }
            else{
                return back()->withErrors('Si selecciones una fecha final, debes seleccionar fecha de inicio.');
            }
        }
        else if(isset($data["nue"])){
            //se va agregar el nue a la busqueda
            $bandera_nue = 1;
        }
        else if(isset($data["curp"])){
            //se va agregar el nue a la busqueda
            $bandera_curp = 1;
        }
        else if(isset($data["solicitante"])){
            //se va agregar el nue a la busqueda
            $bandera_solicitante = 1;
        }
        else if(isset($data["citado"])){
            //se va agregar el nue a la busqueda
            $bandera_citado = 1;
        }
        else if(isset($data["folio"])){
            //se va agregar el nue a la busqueda
            $bandera_folio = 1;
        }
        else if(isset($data["estatus"])){
            //se va agregar el nue a la busqueda
            $bandera_estatus = 1;
        }
        else if(isset($data["tipo"])){
            //se va agregar el nue a la busqueda
            $bandera_tipo = 1;
        }
        else if(isset($data["auxiliar"])){
            //se va agregar el nue a la busqueda
            $bandera_auxiliar = 1;
        }
        else if(isset($data["conciliador"])){
            //se va agregar el nue a la busqueda
            $bandera_conciliador = 1;
        }

        $solicitudes  = Turnos::select("turnos.id","turnos.fecha","turnos.trabajador","turnos.primero_trabajador","turnos.segundo_trabajador",
        "turnos.empresa","turnos.NUE","turnos.estatus");
        if($bandera_fechas == 1){
            $solicitudes = $solicitudes->where("turnos.fecha",">=",$data["inicio"]);
            $solicitudes = $solicitudes->where("turnos.fecha","<=",$data["final"]);
        }
        if($bandera_nue == 1){
            $solicitudes = $solicitudes->where("turnos.NUE",$data["nue"]);
        }
        if($bandera_curp == 1){
            $solicitudes = $solicitudes->where("turnos.trabajador_curp",$data["curp"]);
        }
        if($bandera_solicitante == 1){
            //$solicitudes = $solicitudes->where("turnos.trabajador",'like',$data["solicitante"]);
            $solicitudes = $solicitudes->where(function ($query) use ($data) {
                $query->where('turnos.trabajador', 'like', '%' . $data["solicitante"] . '%')
                      ->orWhere('turnos.primero_trabajador', 'like', '%' . $data["solicitante"] . '%')
                      ->orWhere('turnos.segundo_trabajador', 'like', '%' . $data["solicitante"] . '%');
            });
        }
        if($bandera_citado == 1){
            $solicitudes = $solicitudes->where("turnos.empresa",'like',$data["citado"]);
        }
        if($bandera_folio == 1){
            $solicitudes = $solicitudes->where("turnos.id","=",$data["folio"]);
        }
        if($bandera_estatus == 1){
            $solicitudes = $solicitudes->where("turnos.estatus","=",$data["estatus"]);
        }
        if($bandera_auxiliar == 1){
            $solicitudes = $solicitudes->where("turnos.user_id","=",$data["auxiliar"]);
        }
        if($bandera_conciliador == 1){
            $solicitudes = $solicitudes->where("turnos.id_conciliador","=",$data["conciliador"]);
        }
        $solicitudes = $solicitudes->get();

        return view('/ratificaciones/busqueda',compact('solicitudes'));
    }

    //PDF INCOMPARECENCIA POR PARTE DEL TRABAJADOR
    public function VerPDFIncomTrabajador($id){
        $pagos = Pagos::find($id);

        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $id)
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
            $delegadosEspeciales = [
                    'Zitácuaro'        => 11,
                    'Lázaro Cárdenas'  => 43,
                    'Sahuayo'          => 26,
                ];

            if (array_key_exists($delegacion, $delegadosEspeciales)) {
                $delegado = User::select('id', 'name', 'delegacion')
                    ->find($delegadosEspeciales[$delegacion]);
            } else {
                $delegado = User::where('delegacion', $delegacion)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Delegado');   
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();
            }
            $html = view('PDF/Cumplimientos/incomparecenciaTrabajador', compact('id', 'solicitud','conciliador','pagos','delegado'))->render();
        }
        else{
            
            $solicitud = Turnos::find($pagos->id_solicitud);
            $pagos = Pagos::find($id);
            
            $conciliador = User::join('turnos', 'turnos.id_conciliador', '=', 'users.id')
            ->where('turnos.id', $solicitud->id)
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
            $delegadosEspeciales = [
                    'Zitácuaro'        => 11,
                    'Lázaro Cárdenas'  => 43,
                    'Sahuayo'          => 26,
                ];

            if (array_key_exists($delegacion, $delegadosEspeciales)) {
                $delegado = User::select('id', 'name', 'delegacion')
                    ->find($delegadosEspeciales[$delegacion]);
            } else {
                $delegado = User::where('delegacion', $delegacion)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Delegado');   
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();
            }
            $html = view('PDF/incomparecenciaTrabajador', compact('id','solicitud','conciliador','pagos','delegado'))->render();
        }
       
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incomparecencia_'  .'.pdf';
        return $pdf->stream($nombreArchivo);                     
    }

    //Valida si existe el abogado en base al folio y muestra el nombre en Ratificaciones
    public function validarFolio($folio)
    {
        try {
            Log::info("Buscando folio: " . $folio);
            $representante = Poder::where('idAbogado', (int)$folio)->first();
    
            if ($representante) {
                $nombre = trim("{$representante->nombres_patronal} {$representante->primer_apellido_patronal} {$representante->segundo_apellido_patronal}");
                Log::info("Folio encontrado: " . $nombre);

                $hoy = \Carbon\Carbon::now()->format('Y-m-d');
                $fechaVigencia = $representante->fechaVigencia;
                $sinVigencia = (!is_null($fechaVigencia) && $fechaVigencia < $hoy);
                $requiereValidacion = ($representante->estatus !== 'Validado');

                $status = 'elegible';
                $message = 'Elegible';
                if ($sinVigencia) {
                    $status = 'sin_vigencia';
                    $message = 'Sin Vigencia';
                } elseif ($requiereValidacion) {
                    $status = 'requiere_validacion';
                    $message = 'Requiere validación';
                }
    
                return response()->json([
                    'success' => true,
                    'nombre'  => $nombre,
                    'nombre_representante' => $representante->nombre_representante ?? '',
                    'primer_apellido_representante' => $representante->primer_apellido_representante ?? '',
                    'segundo_apellido_representante' => $representante->segundo_apellido_representante ?? '',
                    'estatus' => $representante->estatus,
                    'fechaVigencia' => $fechaVigencia,
                    'status' => $status,
                    'message' => $message,
                ]);
            }
            Log::warning("Folio no encontrado: " . $folio);
            return response()->json([
                'success' => false,
                'message' => 'El folio no existe',
            ], 404);
    
        } catch (\Throwable $e) {
            Log::error('Error en validarFolio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    public function VerDocumentosRatificacion($id){
        $documento_general = Turnos::find($id); 
        //Documentos del abogado y citados
        $documento_abogado = Poder::find($documento_general["idAbogado"]);
        $documento_subidos = DocumentosSolicitud::where('id_solicitud',$id)->where('tramite','Ratificacion')->get();

       return view('ratificaciones/verDocumentos',compact('documento_general','documento_abogado','documento_subidos'));
    }

    public function ratificacion_confirmadas(){
        $solicitudes = Turnos::where('tipo','Ratificación')
        ->whereIn('estatus', ['Confirmado'])
        ->get();
        return view('/solicitudes/indexauxiliar',compact('solicitudes'));
    }
    
    //Cumplimiento cuando no comparece el trabajador
    public function incomparecencia_rati($id){
        $pagos = Pagos::find($id);
        
        $id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)->update(['estatus'  => "Incomparecencia trabajador"]);
        Turnos::find($id_solicitud)->update(['estatus' => "Incumplimiento"]); //Revisar si se va a archivar, o q procede ANA

        return redirect()->route('cumplimiento_actual');
        //return redirect()->route('ratificacion_atender'); 
    }

    //Guarda el expediente
    public function guardar_expediente(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        $audienciaId = $data['audiencia_id']; 
        $solicitud = Turnos::find($audienciaId);

        if ($request->hasFile('documentoExpediente')) {
            $file = $request->file('documentoExpediente');
            //dd($file->getClientOriginalName());
            if ($file->isValid()) {
                //Creamos primero el registro para obtener un ID y así generar un nombre único
                $doc = DocumentosSolicitud::create([
                    'id_solicitud'     => $data['audiencia_id'],
                    'nombre_documento' => '',
                    'tipo_documentos'  => $file->getClientOriginalName(),
                    'tramite'          => 'Ratificacion',
                ]);

                $nombreInput = $data['nombreExpediente'] ?? 'expediente';
                $slugBase = \Illuminate\Support\Str::slug($nombreInput);
                $ext = strtolower($file->getClientOriginalExtension());

                //Nombre único por documento (id de solicitud y id de documento)
                $documentoExpediente = $slugBase . '_Expediente_' . $data['audiencia_id'] . '_' . $doc->id . '.' . $ext;

                Storage::putFileAs('documentosSolicitud', $file, $documentoExpediente);

                $doc->update(['nombre_documento' => $documentoExpediente]);

            } else {
                return back()->withErrors(['documentoExpediente' => 'Archivo no válido.']);
            }
        }
        return back()->with('success', 'Expediente cargado correctamente.');
    }

    public function ver_pagos_rati($id){
        $solicitudes = Pagos::join('turnos','turnos.id',"=",'pago_solicitud.id_solicitud')
        ->where('pago_solicitud.id_solicitud',$id)
        ->where('pago_solicitud.tipo_pago','Ratificacion')
        ->select('pago_solicitud.id','pago_solicitud.id_solicitud','turnos.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
        ->get(); 
        $total = $solicitudes->count();

        return view('/cumplimientos/pagar_ratificacion',compact('solicitudes','total'));
    }

    public function vista_previa_ratificacion($id) {
        $idSolicitud = $id;
        $solicitud = Turnos::findOrFail($id);
        $conciliador = User::where('id', $solicitud->id_conciliador)->select('name')->first();
        $representantes = Poder::find($solicitud->idAbogado);

        // OPTIMIZACIÓN: Ya no traemos los 3,000 registros aquí. El modal iniciará vacío o cargará vía AJAX.
        $estados = Estados::select('id', 'nombre')->get();
        $municipios = Municipios::where('estado', 16)->select('id', 'nombre')->get();

        $conceptos   = Concepto::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();
        $pagos       = Pagos::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();

        $pagoTotal = $conceptos->sum('monto') - $deducciones->sum('monto');

        return view('ratificaciones.vista_previa', compact(
            'idSolicitud', 'representantes', 'conciliador', 'solicitud', 
            'estados', 'municipios', 'conceptos', 'pagos', 'deducciones', 'pagoTotal'
        ));
    }

    // NUEVO MÉTODO: Responde exclusivamente a las búsquedas del modal en tiempo real
    public function buscar_abogados_ajax(Request $request) {
        $buscar = $request->input('search.value'); // Captura lo que el usuario escribe en DataTables
        $start = $request->input('start', 0);
        $length = $request->input('length', 10); // Límite de 10 registros solicitados

        // Consulta base optimizada seleccionando solo columnas necesarias
        $query = Poder::select(
            'idAbogado', 
            'nombres_patronal', 'primer_apellido_patronal', 'segundo_apellido_patronal', 
            'rfc_patronal', 
            'nombre_representante', 'primer_apellido_representante', 'segundo_apellido_representante'
        );

        // Total de registros sin filtrar
        $totalRegistros = $query->count();

        // Aplicar filtros dinámicos si el usuario escribe en la barra de búsqueda
        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombres_patronal', 'LIKE', "%{$buscar}%")
                ->orWhere('primer_apellido_patronal', 'LIKE', "%{$buscar}%")
                ->orWhere('rfc_patronal', 'LIKE', "%{$buscar}%")
                ->orWhere('nombre_representante', 'LIKE', "%{$buscar}%")
                ->orWhere('primer_apellido_representante', 'LIKE', "%{$buscar}%");
            });
        }

        // Total de registros que coinciden con la búsqueda
        $registrosFiltrados = $query->count();

        // Paginación estricta desde la Base de Datos (Únicamente trae 10 resultados)
        $abogados = $query->offset($start)->limit($length)->get();

        // Mapear los datos al formato JSON estructurado que requiere DataTables
        $data = [];
        foreach ($abogados as $abogado) {
            $nombrePatronal = "{$abogado->nombres_patronal} {$abogado->primer_apellido_patronal} {$abogado->segundo_apellido_patronal}";
            $nombreRepresentante = "{$abogado->nombre_representante} {$abogado->primer_apellido_representante} {$abogado->segundo_apellido_representante}";
            
            $data[] = [
                $abogado->idAbogado,
                trim($nombrePatronal),
                $abogado->rfc_patronal ?? 'N/A',
                trim($nombreRepresentante),
                '<button class="btn btn-info" onclick="editar_rol();" type="submit" name="abogado" value="'.$abogado->idAbogado.'">Seleccionar</button>'
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRegistros,
            "recordsFiltered" => $registrosFiltrados,
            "data" => $data
        ]);
    }

    public function editar_ratificacion_revisar(Request $request) {
        $data = $request->all();
        $id_solicitud = $data["id"];
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();

        $solicitante = Turnos::find($data['id'])->update([
            'trabajador'            => $data["nombre"],
            'primero_trabajador'    => $data["primero"],
            'segundo_trabajador'    => $data["segundo"],
            'trabajador_curp'       => $data["curp"],
        ]);
    
        return redirect()->route('vista_previa_ratificacion', compact('id_solicitud'));
    }

    public function seleccionar_abogado_ratificacion(Request $request){
        $data = $request->all();
        $id_solicitud = $data["solicitud"];

        $abogado = Poder::find($data["abogado"]);
        Turnos::find($data["solicitud"])
        ->update([
            'primero_empresa'   => $abogado["primer_apellido_patronal"],
            'segundo_empresa'   => $abogado["segundo_apellido_patronal"],
            'nombre_empresa'    => $abogado["nombres_patronal"],
            'idAbogado'         => $data["abogado"],
        ]);

        return redirect()->route('vista_previa_ratificacion',compact('id_solicitud'));
    }

    public function concepto_eliminar_pago_ratificacion($id_solicitud){
        Concepto::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Borrado Correctamente.');
    }

    public function concepto_eliminar_deduccion_ratificacion($id_solicitud){
        Deducciones::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Deducción Correctamente.');
    }
    
    public function pago_eliminar_pago_ratificacion($id_solicitud){
        Pagos::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Borrado Correctamente.');
    }
    
    public function terminar_ratificacion(Request $request){
        $data = $request->all();
        $id_solicitud = $data["id"];
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        $sede = $user->delegacion;

        //Revisar si existe
        if(isset($data["dias_pagos"])){
            $conteo = count($data["dias_pagos"]);
           for($i = 0; $i < $conteo; $i++) {
                $data_pagos = [
                    'id_solicitud'  => $data["id"],
                    'fecha'         => $data["dias_pagos"][$i],
                    'hora'          => $data["hora_pagos"][$i], 
                    'monto'         => $data["monto_pagos"][$i], 
                    'descripcion'   => "Pago Parcial ".$i,
                    'estatus'       => "Pendiente", 
                    'tipo_pago'     => "Ratificacion",
                    'delegacion'    => $sede,
                ];
                $monto = $monto + $data["monto_pagos"][$i];
                Pagos::create($data_pagos);
            }
        }
        //Validar si existe un pago extra
       /* if(isset($data["tipo_pago"])){
            $cont = count($data["monto_pago"]);
            for($i = 0; $i < $cont; $i++) {
                $data_citado = [
                    'id_solicitud'  => $data["id"], 
                    'monto'         => $data["monto_pago"][$i], 
                    'descripcion'   => $data["tipo_pago"][$i],
                    'tipo_pago'     => "Audiencia"
                ];
                Concepto::create($data_citado);
            }
        }*/
        if(isset($data["tipo_pago"])){
            $tiposPago = $data["tipo_pago"];
            $cont = count($data["monto_pago"]);
            $otrasPrestaciones = $data["otra_prestacion"] ?? [];
        
            for($i = 0; $i < $cont; $i++) {
                $descripcion = $tiposPago[$i];
                if ($descripcion === "Otras" && isset($otrasPrestaciones[$i]) && !empty(trim($otrasPrestaciones[$i]))) {
                    $descripcion = trim($otrasPrestaciones[$i]);
                }
                
                $data_citado = [
                    'id_solicitud'  => $data["id"], 
                    'monto'         => $data["monto_pago"][$i], 
                    'descripcion'   => $descripcion,
                    'tipo_pago'     => "Ratificacion",
                ];
                Concepto::create($data_citado);
            }
        }
        //Validar si existe un pago extra
        if(isset($data["descripcion_deduccion"])){
            $cont = count($data["descripcion_deduccion"]);
            for($i = 0; $i < $cont; $i++) {
                $data_deduccion = [
                    'id_solicitud'  => $data["id"], 
                    'monto'         => $data["monto_deduccion"][$i], 
                    'descripcion'   => $data["descripcion_deduccion"][$i],
                    'tipo_pago'     => "Ratificacion"
                ];
                Deducciones::create($data_deduccion);
            }
        }
            
        //Actualizar Audiecia
        $updateTerminar = [
            'resolucion_primera'        =>  $data["primera"],
            'resolucion_justificacion'  =>  $data["justificacion"],
            'resolucion_segunda'        =>  $data["segunda"],
            'vacaciones_dias'           =>  $data["vacaciones"],
            'aguinaldo_dias'            =>  $data["aguinaldo"],
            'otros_dias'                =>  $data["otros"],
            'horario'                   =>  $data["horario"],
            'comida'                    =>  $data["comida"],
        ];

        if (!empty($data["year_ptu"])) {
            $updateTerminar['year_ptu'] = (int) $data["year_ptu"][array_key_first($data["year_ptu"])];
        } elseif (!empty($data["year_ptu_actual"])) {
            $updateTerminar['year_ptu'] = (int) $data["year_ptu_actual"];
        }

        Turnos::find($data["id"])->update($updateTerminar);

        return redirect()->route('todas_ratificaciones');
    }

    //Muestra quien emite el tipo de identificación seleccionado para usar en PDF convenio y acta de audiencia
    private function descripcionIdentificacion($tipo) {
        $descripciones = [
            'Credencial de elector'   => 'Instituto Nacional Electoral',
            'Pasaporte'               => 'Secretaria de Relaciones Exteriores',
            'Cédula profesional'      => 'Autoridad Correspondiente',
            'Licencia de conducir'    => 'Autoridad Correspondiente',
            'Credencial de inapam'    => 'Instituto Nacional de las Personas Adultas Mayores',
            'Cartilla militar'        => 'Secretaria de la Defensa Nacional',
            'Documento migratorio'    => 'Instituto Nacional de Migración',
            'Constancia de identidad' => 'Autoridad Correspondiente',
            'Otro'                    => 'Autoridad Correspondiente',
        ];
    
        return $descripciones[$tipo];
    }

    public function PDFincumplimientoRatificacion($id){
        $pagos = Pagos::find($id);

        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $salario_diario = 0;
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $id)
            ->select('users.name')
            ->first();
            $html = view('PDF/Cumplimientos/Incumplimiento', compact('id', 'solicitud','conciliador','salario_diario','pagos'))->render();
        }
        else{
            $solicitud  = Turnos::where('id',$pagos["id_solicitud"])->first();
            $pagos      = Pagos::find($id);
            //$general    = SeerPerGeneral::find($pagos["id_solicitud"]);
            $salario_diario = $this->calcularSalarioDiario($solicitud->salario, $solicitud->frecuencia);

            $conciliador = User::where('id', $solicitud->id_conciliador)->first();
            $html = view('PDF/Incumplimiento', compact('id', 'solicitud','conciliador','salario_diario','pagos'))->render();
        }
       
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incumplimiento_'  .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }


    public function actualizar_folio(){
        $tableName = 'turnos'; // Reemplaza con el nombre real de tu tabla
        $idColumn = 'id'; // Columna para ordenar la secuencia (usualmente 'id')
        $delegacionColumn = 'delegacion'; // Columna de agrupación
        $consecutivoColumn = 'consecutivo'; // Columna a actualizar

        $sql = "
            UPDATE {$tableName} AS r
            JOIN (
                SELECT 
                    {$idColumn},
                    {$delegacionColumn},
                    @consecutivo_delegacion := IF(
                        @delegacion_actual = {$delegacionColumn}, 
                        @consecutivo_delegacion + 1, 
                        1
                    ) AS nuevo_consecutivo,                    
                    @delegacion_actual := {$delegacionColumn} AS dummy_var 
                FROM 
                    {$tableName}
                ORDER BY 
                    {$delegacionColumn} ASC, 
                    {$idColumn} ASC 
            ) AS subquery 
            ON r.{$idColumn} = subquery.{$idColumn}
            SET 
                r.{$consecutivoColumn} = subquery.nuevo_consecutivo;";

        DB::statement($sql);
    }
    //Conveio de PTU cuando el trabajador NO SIGUE laborando
    public function VerPDFConvenioPTU_rat($id){
        $ratificacion = Turnos::find($id);
        $inicialesConcluye = $this->inicialesDeSolicitud($ratificacion);
        $etiquetaIniciales = $this->etiquetaIniciales($ratificacion->delegacion ?? null, $inicialesConcluye);
        $delegacion = $ratificacion->delegacion;
        $delegadosEspeciales = [
                'Zitácuaro'        => 11,
                'Lázaro Cárdenas'  => 43,
                'Sahuayo'          => 26,
            ];

        if (array_key_exists($delegacion, $delegadosEspeciales)) {
            $delegado = User::select('id', 'name', 'delegacion')
                ->find($delegadosEspeciales[$delegacion]);
        } else {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');   
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();
        }

        $abogado = Poder::join("turnos", "turnos.idAbogado", "=", "abogados.idAbogado")
           ->where("turnos.id", "=", $id)
           ->select(
           "abogados.*",
           "turnos.tipo_identificacion as tipo_identificacion_turno",
           "turnos.num_identificacion as num_identificacion_turno"
        )
        ->first();
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();
        $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago', 'Ratificacion')->get();

        $conceptosTexto = [];
        $deduccionesTexto = [];

        foreach ($prestaciones as $concepto) {
            $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
        }

        foreach ($deducciones as $deduccion) {
            $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
        }

        $totalPrestaciones = $prestaciones->sum('monto');
        $totalDeducciones = $deducciones->sum('monto');
        //Total a pagar
        $pagoTotal= $totalPrestaciones-$totalDeducciones;
        $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("turnos.id", "=", $id)
        ->select('users.name')
        ->first();
        
        //Descripción del tipo de identificación para los solicitantes
        $identificacionSolicitante = $ratificacion->tipo_identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);

        //Descripción del tipo de identificación para los poderes
        $identificacionPoder = $abogado->tipo_identificacion;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);

        $salario_diario = $this->calcularSalarioDiario($ratificacion->salario, $ratificacion->frecuencia);
        $salario_mensual = $salario_diario * 30;
        $diarioTexto = $this->convertirNumerosALetras($salario_diario);
        $mensualTexto = $this->convertirNumerosALetras($salario_mensual);
        $montoTexto = $this->convertirNumerosALetras($ratificacion->monto);
        
        $pagosDif  = Pagos::join("turnos","turnos.id","=","pago_solicitud.id_solicitud");
        $pagosDif = $pagosDif->where("pago_solicitud.id_solicitud", "=", $id)
        ->where("pago_solicitud.tipo_pago", "=", "Ratificacion")
        ->select(DB::raw('count(pago_solicitud.id_solicitud) as C_pagos'))
        ->first();
        
        $municipio = Municipios::find($ratificacion->municipio_rat);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($ratificacion->estado_rat);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';

        if($ratificacion->year_ptu == NULL){
            $ratificacion->year_ptu = 2025;
        }

        if($ratificacion->fecha_termino){
            $html = view('PDF/convenioPTUNoLaboraRati', compact('id','ratificacion','conciliador','prestaciones','deducciones','deduccionesTexto','pagoTotal','descripcionIdentificacionS','salario_mensual','mensualTexto',
            'descripcionIdentificacionP','abogado','conceptosTexto','municipioEmpresa','estadoEmpresa','montoTexto','pagosDif','pagos','delegado', 'inicialesConcluye','etiquetaIniciales'))->render();
        } else {
            $html = view('PDF/convenioPTULaboraRati', compact('id','ratificacion','conciliador','prestaciones','deducciones','deduccionesTexto','pagoTotal','descripcionIdentificacionS','salario_mensual','mensualTexto',
            'descripcionIdentificacionP','abogado','conceptosTexto','municipioEmpresa','estadoEmpresa','montoTexto','pagosDif','pagos','delegado', 'inicialesConcluye','etiquetaIniciales'))->render();
        }

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Convenio_PTU_NRati' . $ratificacion->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);            
    }

    public function vista_previa_citas($id){
        $idSolicitud    = $id;
        $id_usuario     = auth()->user()->id;
        $user           = User::find($id_usuario);           
        $solicitud      = Turnos::find($id);

        $representantes = Poder::find($solicitud["idAbogado"]);
        $abogados       = Poder::all();
        $estados        = Estados::all();
        $municipios     = Municipios::where('estado',16)->get();

        return view('/ratificaciones/edicionVistaCitas',compact('idSolicitud','representantes','solicitud','abogados','estados','municipios'));
    }

    public function guardarEdicion_citas(Request $request){
        $data = $request->all();
        $id_solicitud = $data["id"];
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();

        $solicitud = Turnos::find($data['id']);
        //Variables opcionales
        if(isset($data["Aguinaldo"]) && $data["motivo"] == "Pago de prestaciones"){
            $Aguinaldo =  1;
        }
        else{
            $Aguinaldo =  0;
        }
        if(isset($data["Vacaciones"]) && $data["motivo"] == "Pago de prestaciones"){
            $Vacaciones =  1;
        }
        else{
            $Vacaciones =  0;
        }
        if(isset($data["PrimaVacacional"]) && $data["motivo"] == "Pago de prestaciones"){
            $PrimaVacacional = 1;
        }
        else{
            $PrimaVacacional = 0;
        }
        if(isset($data["PagoPTU"]) && $data["motivo"] == "Pago de prestaciones"){
            $PagoPTU =  1;
        }
        else{
            $PagoPTU = 0;
        }
        if(isset($data["Gratificación"]) && $data["motivo"] == "Pago de prestaciones"){
            $Gratificación =  1;
        }
        else{
            $Gratificación = 0;
        }
        if(isset($data["PrimaAntigüedad"]) && $data["motivo"] == "Pago de prestaciones"){
            $PrimaAntigüedad =  1;
        }
        else{
            $PrimaAntigüedad = 0;
        }
        if(isset($data["Otras"]) && $data["motivo"] == "Pago de prestaciones"){
            $Otras =  1;
        }
        else{
            $Otras =  0;
        }
        if(isset($data["Especifique"]) && $data["motivo"] == "Pago de prestaciones"){
            $Especifique =  $data["Especifique"];
        }
        else{
            $Especifique = 0;
        }
        //Obtener el abogado actual y obtener el historial mas reciente 
        $ultimoRegistro = HistorialAbogado::where('id_abogado', $data["folio"])->latest()->first();
        $updateData = [
            'idAbogado'           =>  $data["folio"],
            'primero_trabajador'  =>  $data["primero"],
            'segundo_trabajador'  =>  $data["segundo"],
            'trabajador'          =>  $data["nombre"],
            'edad'                =>  $data["edad"],
            'sexo'                =>  $data["trabajador_sexo"],
            'trabajador_curp'     =>  $data["curp"],
            'tipo_identificacion' =>  $data["tipo_identificacion"],
            'num_identificacion'  =>  $data["num_identificacion"],
            'fecha_inicio'        =>  $data["fecha_inicio"],
            'fecha_termino'       =>  $data["fecha_termino"],
            'categoria'           =>  $data["categoria"],
            'frecuencia'          =>  $data["frecuencia"],
            'salario'             =>  $data["salario"],
            'dias'                =>  $data["dias"],
            'motivo'              =>  $data["motivo"],
            'monto'               =>  $data["monto"],
            'tipo_pago'           =>  $data["tipo_pago"],
            'delegacion'          =>  $data["sede"],
            'fecha'               =>  $data["fecha"],
            'hora'                =>  $data["hora"],
            'hora_fin'            =>  $data["hora"],
            'Aguinaldo'           => $Aguinaldo,
            'Vacaciones'          => $Vacaciones,
            'PrimaVacacional'     => $PrimaVacacional,
            'PagoPTU'             => $PagoPTU,
            'Gratificación'       => $Gratificación,
            'PrimaAntigüedad'     => $PrimaAntigüedad,
            'Otras'               => $Otras,
            'Especifique'         => $Especifique,
            'estado_rat'          => $data["estado_rat"],
            'municipio_rat'       => $data["municipio_rat"],
            'tipo_vialidad'       => $data["tipo_vialidad"],
            'calle'               => $data["vialidad_calle"],
            'colonia'             => $data["colonia"],
            'num_ext'             => $data["N_Ext"],
            'num_int'             => $data["N_Int"],
            'codigo_postal'       => $data["cp"],
            'id_historial'        => $ultimoRegistro->id ?? NULL,
        ];

        if ($request->hasFile('documentoidentificacion')) {
            $nombre_ine = $request->curp . "_IDENTIFICACION_" . time() . ".pdf";
            $request->file('documentoidentificacion')->storeAs('documentos_ratificacion', $nombre_ine);
            $updateData["documentoidentificacion"] = $nombre_ine; 
        }
        if ($request->hasFile('documentoCurp')) {
            $nombre_curp = $request->curp . "_CURP_" . time() . ".pdf";
            $request->file('documentoCurp')->storeAs('documentos_ratificacion', $nombre_curp);
            $updateData["documentoCurp"] = $nombre_curp;
        }

        if ($request->hasFile('cuantificacion')) {
            $nombre_cuantificacion = $request->curp . "_CUANTIFICACION_" . time() . ".pdf";
            $request->file('cuantificacion')->storeAs('documentos_ratificacion', $nombre_cuantificacion);
            $updateData["documentoCuanti"] = $nombre_cuantificacion; 
        }
        $solicitud->update($updateData);

        return redirect()->route('todas_ratificaciones')->with('success', 'Actualizado correctamente');

    }

    public function create_ratiMultiple(){
        $estados = Estados::all();
        $municipios = Municipios::all();
        return view('citasRatificacion', compact('estados','municipios'));
    }

    public function guardarRatificacion(Request $request)
    {
        $data = $request->all();
        $año_actual = date('Y');
        $fecha_actual = date('Y-m-d');
        $hora_actual =  date("H:i:s");
        $user = 0;
     
        if(isset($data["folio"])){
            request()->validate([
                'folio'             => 'required',
                'primero_trabajador'=> 'required',
                'trabajador'        => 'required',
                'trabajador_edad'   => 'required',
                'trabajador_sexo'   => 'required',
                'trabajador_curp'   => 'required',
                'tipo_identificacion'=> 'required',
                //'documentoidentificacion'=> 'required',
                'fecha_inicio'      => 'required',
                'fecha_termino'     => 'required',
                'categoria'         => 'required',
                'monto'             => 'required',
                'frecuencia'        => 'required',
                'tipo_pago'         => 'required',
                'sede'              => 'required',
                'dias'              => 'required',
                'fecha'             => 'required',
                'hora'              => 'required',
                'motivo'            => 'required',
                'salario'           => 'required',
                'num_identificacion'=> 'required',
            ], $data);
        }
        
        //Buscar la proxima fecha disponible de la sede
        $numero_consecutivo = 0;
        $consecutivo  = Turnos::latest('consecutivo')
        ->where('delegacion',$data["sede"])
        ->where('año',$año_actual)->
        first();
        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }


        $representante  = Poder::find($data["folio"]);
        $ultimoRegistro = HistorialAbogado::where('id_abogado', $data["folio"])->latest()->first();

        if(!isset($representante)){
            return back()->with('error', 'El representante legal no existe');
        }
        if($representante["tipo"] == "Fisica"){
            $email  = $representante["email_patronal"];
            $telefono = $representante["telefono_patronal"];
            $curp   = $representante["curp_patronal"];
        }
        else{
            $email  = $representante["correo_representante"];
            $telefono = $representante["numero_representante"];
            $curp   = $representante["curp_representante"];
        }
        $data_insertar= array(
            'consecutivo'       => $numero_consecutivo,    
            'empresa'           => $representante["nombres_patronal"],
            'primero_empresa'   => $representante["primer_apellido_patronal"],
            'segundo_empresa'   => $representante["segundo_apellido_patronal"],
            'nombre_empresa'    => $representante["nombres_patronal"],
            'primero_trabajador'=> $data["primero_trabajador"],
            'segundo_trabajador'=> $data["segundo_trabajador"] ?? null,
            'trabajador'        => $data["trabajador"],
            'edad'              => $data["trabajador_edad"],
            'sexo'              => $data["trabajador_sexo"],
            'trabajador_curp'   => $data["trabajador_curp"],
                //'documentoCurp'     => $data["documentoCurp"],
            'tipo_identificacion'=> $data["tipo_identificacion"],
            'documentoidentificacion'=> "",
            'fecha_inicio'      => $data["fecha_inicio"],
            'fecha_termino'     => $data["fecha_termino"],
            'categoria'         => $data["categoria"],
            'tipo_pago'         => $data["tipo_pago"],
            'monto'             => $data["monto"],
            'frecuencia'        => $data["frecuencia"],
            'dias'              => $data["dias"],
            'auxiliar'          => 0,
            'lugar_auxiliar'    => "Recepción",
            'delegacion'        => $data["sede"],
            'estatus'           => 'Confirmado',
            'exepcion'          => 'No',
            'ine'               => $representante["ineDocumento	"],
            'representacion'    => $representante["representacionDocumento"],
            'email'             => $email,
            'telefono'          => $telefono,
            'JLCA'              => "No",
            'motivo'            => $data["motivo"],
            'curp_solicitante'  => $curp,
            'salario'           => $data["salario"],
            //Municipio de ururpan
            'municipio_rat'     => 16102,
            //Datos de representante legal
            'tipo_vialidad'     => $representante["tipo_vialidad_patronal"],
            'calle'             => $representante["vialidad_patronal"],
            'colonia'           => $representante["colonia_patronal"],
            'num_ext'           => $representante["num_ext_patronal"],
            'codigo_postal'     => $representante["cp_patronal"],

            'idAbogado'         => $data["folio"],
            'user_id'           => $user,
            'fecha'             => $data["fecha"],
            'hora'              => $data["hora"],
            'hora_fin'          => $data["hora"],
            'num_identificacion'=> $data["num_identificacion"],
            'estado_rat'        => 16,
            'año'               => $año_actual,
            'id_historial'      => $ultimoRegistro->id ?? NULL,
            'nacionalidad'      => "MEXICANA",
        ); 
        $nombre = $data["trabajador"];
    

        //Variables opcionales
        if(isset($data["Aguinaldo"])){
            $data_insertar["Aguinaldo"] =  1;
        }
        if(isset($data["Vacaciones"])){
            $data_insertar["Vacaciones"] =  1;
        }
        if(isset($data["PrimaVacacional"])){
            $data_insertar["PrimaVacacional"] = 1;
        }
        if(isset($data["PagoPTU"])){
            $data_insertar["PagoPTU"] =  1;
        }
        if(isset($data["Gratificación"])){
            $data_insertar["Gratificación"] =  1;
        }
        if(isset($data["PrimaAntigüedad"])){
            $data_insertar["PrimaAntigüedad"] =  1;
        }
        if(isset($data["Otras"])){
            $data_insertar["Otras"] =  1;
        }
        if(isset($data["Especifique"])){
            $data_insertar["Especifique"] =  $data["Especifique"];
        }
        if(isset($data["cuantificacion"])){
            $data_insertar["cuantificacion"] =  $data["cuantificacion"];
        }
        if(isset($data["tipo_otros"])){
            $data_insertar["tipo_otros"] =  $data["tipo_otros"];
        }

        if(isset($data["N_Int"])){
            $data_insert["num_int"] =  $data["N_Int"];
        }

        /*
        //Documentos si cargaron el folio
        if(isset($data["folio"])){
            if($representante["tipo"] == "Fisica"){
                $nombre_ine             = $representante["nombre_patronal"]."".$representante["primer_apellido_patronal"]."".$representante["segundo_apellido_patronal"]."-".$representante["empresa"]."_IDENTIFICACION.pdf";
            }
            else{
               $nombre_ine             = $representante["nombres_patronal"]."_IDENTIFICACION.pdf";
            }
        }
        
        $trabajador_identificacion  = $data["trabajador_curp"]."_IDENTIFICACION.pdf";
        $path = Storage::putFileAs(
            'documentos_ratificacion', $request->file('documentoidentificacion'), $trabajador_identificacion
        );
        */
        $data_insertar["ine"]                       = "";
        $data_insertar["representacion"]            = "";   
        $data_insertar["documentoidentificacion"]   = "";  

        if(isset($data["N_Int"])){
            $data_insert["num_int"] =  $data["N_Int"];
        }
        //Se van insetar todos los datos
        Turnos::create($data_insertar);
       
        return back()->with('success', 'Solicitud Capturada Correctamente.'  ); 
    }
}
