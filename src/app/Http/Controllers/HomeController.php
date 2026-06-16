<?php

namespace App\Http\Controllers;

use App\Models\Turnos;
use App\Models\Municipio;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth, Hash;
use App\Models\Recepcion;
use App\Models\CitaDireccion;
use App\Models\Pagos;
use App\Models\Audiencias;
use App\Imports\PagoSolicitudImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ConceptoPagoImport;
use App\Imports\TurnosImport;

class HomeController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function publico(){
        return view('welcome');
    }

    public function home()
    {
        //return redirect('home');
        return view('home');
    }

    public function pantallaMorelia()
    {
        $fecha_actual = date('y-m-d');

        $cumplimientos = Pagos::
        where('pago_solicitud.fecha',$fecha_actual)
        ->join('seer_general','seer_general.id','pago_solicitud.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','pago_solicitud.id_solicitud')
        ->where('pago_solicitud.estatus','Pendiente')
        ->where('pago_solicitud.delegacion','Morelia')
        ->select('seer_general.NUE','seer_solicitante.nombre',DB::raw("'Cumplimiento' as tramite"))
        ->orderBy('pago_solicitud.hora')
        ->limit(7)
        ->get();

        $audienencias = Audiencias::
        where('audiencias.fecha',$fecha_actual)
        ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
        ->join('seer_general','seer_general.id','audiencias.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','audiencias.id_solicitud')
        ->where('audiencias.estatus','Pendiente')
        ->where('audiencias.delegacion','Morelia')
        ->select('users.name as NUE','seer_solicitante.nombre',DB::raw("'Audiencias' as tramite"))
        ->limit(7)
        ->get();

        $turnos = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Ratificación')
        ->where('recepcion.delegacion','Morelia')
        ->select('recepcion.solicitante as NUE',DB::raw("'Ratificación' as tramite"))
        ->limit(7)
        ->get();

        $solicitudes = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Solicitud')
        ->where('recepcion.delegacion','Morelia')
        ->select('recepcion.solicitante as NUE',DB::raw("'Solicitudes' as tramite"))
        ->limit(7)
        ->get();

        return view('pantalla', compact('cumplimientos','turnos','audienencias','solicitudes'));
    }

    public function pantallaUruapan()
    {
        $fecha_actual = date('y-m-d');
       
        $cumplimientos = Pagos::where('pago_solicitud.fecha',$fecha_actual)
        ->join('seer_general','seer_general.id','pago_solicitud.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','pago_solicitud.id_solicitud')
        ->where('pago_solicitud.estatus','Pendiente')
        ->where('seer_general.delegacion','Uruapan')
        ->select('seer_general.NUE','seer_solicitante.nombre',DB::raw("'Cumplimiento' as tramite"))
        ->orderBy('pago_solicitud.hora')
        ->limit(7)
        ->get();

        $audienencias = Audiencias::where('audiencias.fecha',$fecha_actual)
        ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
        ->join('seer_general','seer_general.id','audiencias.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','audiencias.id_solicitud')
        ->where('audiencias.estatus','Pendiente')
        ->where('audiencias.delegacion','Uruapan')
        ->select('seer_solicitante.nombre as NUE',DB::raw("'Audiencias' as tramite"))
        ->limit(7)
        ->get();

        $turnos = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Ratificación')
        ->where('recepcion.delegacion','Uruapan')
        ->select('recepcion.solicitante as NUE',DB::raw("'Ratificación' as tramite"))
        ->limit(7)
        ->get();

        $solicitudes = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Solicitud')
        ->where('recepcion.delegacion','Uruapan')
        ->select('recepcion.solicitante as NUE',DB::raw("'Solicitudes' as tramite"))
        ->limit(7)
        ->get();


        return view('pantalla', compact('cumplimientos','turnos','audienencias','solicitudes'));
    }

    public function pantallaZamora()
    {
        $fecha_actual = date('y-m-d');

        $cumplimientos = Pagos::where('pago_solicitud.fecha',$fecha_actual)
        ->join('seer_general','seer_general.id','pago_solicitud.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','pago_solicitud.id_solicitud')
        ->where('pago_solicitud.estatus','Pendiente')
        ->where('pago_solicitud.delegacion','Zamora')
        ->select('seer_general.NUE','seer_solicitante.nombre',DB::raw("'Cumplimiento' as tramite"))
        ->orderBy('pago_solicitud.hora')
        ->limit(7)
        ->get();

        $audienencias = Audiencias::where('audiencias.fecha',$fecha_actual)
        ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
        ->join('seer_general','seer_general.id','audiencias.id_solicitud')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','audiencias.id_solicitud')
        ->where('audiencias.estatus','Pendiente')
        ->where('audiencias.delegacion','Zamora')
        ->select('users.name as NUE','seer_solicitante.nombre',DB::raw("'Audiencias' as tramite"))
        ->limit(7)
        ->get();

        $turnos = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Ratificación')
        ->where('recepcion.delegacion','Zamora')
        ->select('recepcion.solicitante as NUE',DB::raw("'Ratificación' as tramite"))
        ->limit(7)
        ->get();

        $solicitudes = Recepcion::
        where('recepcion.fecha',$fecha_actual)
        ->leftjoin('users', 'users.id', '=', 'recepcion.auxiliar')
        ->where('recepcion.tipo','Solicitud')
        ->where('recepcion.delegacion','Zamora')
        ->select('recepcion.solicitante as NUE',DB::raw("'Solicitudes' as tramite"))
        ->limit(7)
        ->get();

        return view('pantalla', compact('cumplimientos','turnos','audienencias','solicitudes'));

    }

    public function citas(){
        return view('turnos');
    }

    public function turnos_publico(Request $request){
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

        $data_insertar= array(
            'consecutivo'   => $numero_consecutivo,
            'solicitante'   => $data["nombre"],
            'auxiliar'      => 0,
            'lugar_auxiliar'=> "Recepción",
            'tipo'          => $data["tipo"],
            'fecha'         => $fecha_actual,
            'hora'          => $hora_actual,
            'hora_fin'      => $hora_actual,
            'delegacion'    => $data["delegacion"],
            'estatus'       => "no atendido",
            'exepcion'      => "No",
            'edad'          => $data["edad"],
            'sexo'          => $data["sexo"],
            'vulnerables'   => $data["vulnerables"],
        );    
        Recepcion::create($data_insertar);
        
        return back()->with('success', 'Turno registrado correctamente favor de pasar a ventanilla.'); 
    }

    public function password_cambiar(){
        return view('/cambio_contraseña/reset-password');
    }

    public function contraseña_update(Request $request){
        $request->validate([
            'password'  => 'required',
            'password1' => 'required'
        ]);
        $data = $request->all();
        //dd($data);
        
        if ($data["password"] !== $data["password1"]){
            return back()->withErrors('¡La contraseña no coincide!');
        }
        else{
            $id = auth()->user()->id;
            $user = User::find($id);
    
            $user->password = Hash::make($data["password"]);
            $user->save();

            return back()->with('success', 'Contraseña Actualizada correctamente.');
        }
    }
    
    public function create_publico(){
        return view('citas');
    }

    public function store_publico(Request $request)
    {
        $data = $request->all();
        //dd($data);
        if(isset($data["folio"])){
            request()->validate([
                'folio'             => 'required',
                'primero_trabajador'=> 'required',
                'segundo_trabajador'=> 'required',
                'trabajador'        => 'required',
                'trabajador_edad'   => 'required',
                'trabajador_sexo'   => 'required',
                'trabajador_curp'   => 'required',
                'documentoCurp'     => 'required',
                'tipo_identificacion'=> 'required',
                'documentoidentificacion'=> 'required',
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
                'JLCA'              => 'required',
                'motivo'            => 'required',
                'salario'           => 'required'
            ], $data);
        }
        else{
            request()->validate([
                'empresa'           => 'required',
                'primero_empresa'   => 'required',
                'segundo_empresa'   => 'required',
                'nombre_empresa'    => 'required',
                'curp'              => 'required',
                'email'             => 'required',
                'telefono'          => 'required',
                'documentoIne'      => 'required',
                'documentoPoder'    => 'required',
                'primero_trabajador'=> 'required',
                'segundo_trabajador'=> 'required',
                'trabajador'        => 'required',
                'trabajador_edad'   => 'required',
                'trabajador_sexo'   => 'required',
                'trabajador_curp'   => 'required',
                'documentoCurp'     => 'required',
                'tipo_identificacion'=> 'required',
                'documentoidentificacion'=> 'required',
                'fecha_inicio'      => 'required',
                'fecha_termino'     => 'required',
                'categoria'         => 'required',
                'monto'             => 'required',
                'frecuencia'        => 'required',
                'tipo_pago'         => 'required',
                'sede'              => 'required',
                'dias'              => 'required',
                'hora'              => 'required',
                'JLCA'              => 'required',
                'motivo'            => 'required',
                'salario'           => 'required'
            ], $data);
        }


        //Vamos a buscar la proxima fecha disponible de la sede
        $numero_consecutivo = 0;
        $consecutivo  = Turnos::latest('id')
        ->where('fecha', $data["fecha"])
        ->first();
        
        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        if(isset($data["folio"])){
            $representante  = Poder::find($data["folio"]);
            if(!isset($representante)){
                return back()->with('error', 'El representante legal no existe');
            }
           
            $data_insertar= array(
                'consecutivo'       => $numero_consecutivo,    
                'empresa'           => $representante["empresa"],
                'primero_empresa'   => $representante["primer_apellido"],
                'segundo_empresa'   => $representante["segundo_apellido"],
                'nombre_empresa'    => $representante["nombres"],
                'primero_trabajador'=> $data["primero_trabajador"],
                'segundo_trabajador'=> $data["segundo_trabajador"],
                'trabajador'        => $data["trabajador"],
                'edad'              => $data["trabajador_edad"],
                'sexo'              => $data["trabajador_sexo"],
                'trabajador_curp'   => $data["trabajador_curp"],
                'documentoCurp'     => $data["documentoCurp"],
                'tipo_identificacion'=> $data["tipo_identificacion"],
                'documentoidentificacion'=> $data["documentoidentificacion"],
                'fecha_inicio'      => $data["fecha_inicio"],
                'fecha_termino'     => $data["fecha_termino"],
                'categoria'         => $data["categoria"],
                'tipo_pago'         => $data["tipo_pago"],
                'monto'             => $data["monto"],
                'frecuencia'        => $data["frecuencia"],
                'dias'              => $data["dias"],
                'fecha'             => $data["fecha"],
                'hora'              => $data["hora"],
                'hora_fin'          => $data["hora"],
                'auxiliar'          => 0,
                'lugar_auxiliar'    => "Recepción",
                'delegacion'        => $data["sede"],
                'estatus'           => 'Pendiente',
                'exepcion'          => 'No',
                'ine'               => $representante["ine"],
                'representacion'    => $representante["representacion"],
                'email'             => $representante["email"],
                'telefono'          => $representante["telefono"],
                'JLCA'              => $data["JLCA"],
                'motivo'            => $data["motivo"],
                'curp_solicitante'  => $representante["curp"],
                'salario'           => $data["salario"]
            ); 
            $nombre = $data["trabajador"];
            $email  = $representante["email"];
            $curp   = $representante["curp"];
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
                'segundo_trabajador'        => $data["segundo_trabajador"],
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
                'salario'                   => $data["salario"]
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
        //dd($data_insertar);

        //Documentos si cargaron el folio
        if(isset($data["folio"])){
            $nombre_ine             = $representante["nombres"]."".$representante["primer_apellido"]."".$representante["segundo_apellido"]."-".$representante["empresa"]."_IDENTIFICACION.pdf";
            $nombre_representación  = $representante["nombres"]."".$representante["primer_apellido"]."".$representante["segundo_apellido"]."-".$representante["empresa"]."_REPRESENTACION.pdf";
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
        
        $trabajador_curp = $data["trabajador_curp"].".pdf";
        $path = Storage::putFileAs(
            'documentos_ratificacion', $request->file('documentoCurp'), $trabajador_curp
        );

        $trabajador_identificacion  = $data["trabajador_curp"]."_IDENTIFICACION.pdf";
        $path = Storage::putFileAs(
            'documentos_ratificacion', $request->file('documentoidentificacion'), $trabajador_identificacion
        );

        $data_insertar["ine"]                       = $nombre_ine;
        $data_insertar["representacion"]            = $nombre_representación;   
        $data_insertar["documentoCurp"]             = $trabajador_curp;
        $data_insertar["documentoidentificacion"]   = $trabajador_identificacion;  


        if(isset($data["cuantificacion"])){
            $cuantificacion  = $data["trabajador_curp"]."_CUANTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_ratificacion', $request->file('cuantificacion'), $cuantificacion
            );
            $data_insertar["documentoCuanti"] = $cuantificacion;
        }

        //Se van insetar todos los datos
        Turnos::create($data_insertar);

       
        //Revisar si ya existe el correo
        $usuario = User::where('email',$email)->first();
        if(!isset($usuario)){
            $data_insertar_user= array(
                'name'              => $nombre,
                'email'             => $email,
                'delegacion'        => $data["sede"],
                'type'              => "Seer",
                'remember_token'    => $curp,
                'profile_photo_path'=> $curp
            ); 
            
            //Hacemos un hash del campo que tiene el password
            $data_insertar_user['password'] = Hash::make("CCLMICHOACAN");
            $usuario = User::create($data_insertar_user);
            $usuario->assignRole(('Solicitante'));
            $mensaje = " el correo:".$usuario["email"]." y la contraseña:CCLMICHOACAN para continuar tú trámite.";
        }
        else{
            $mensaje = " el correo:".$usuario["email"]." para continuar tú trámite.";
        }
        
        

        return back()->with('success', 'Debes ingresar a '. 
        ' http://siconcilio.cclmichoacan.gob.mx/ en el apartado de buzón electrónico con'.$mensaje  ); 
    }

    public function indexSubida(){
        return view('cargaExcel/index');
    }

    public function importPago(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        Excel::import(new PagoSolicitudImport, $request->file('file'));
        
        return back()->with('success', '¡Registros migrados correctamente!');
    }

    public function importConcepto(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        Excel::import(new ConceptoPagoImport, $request->file('file'));
        
        return back()->with('success', '¡Registros migrados correctamente!');
    }

    public function importTurnos(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        Excel::import(new TurnosImport, $request->file('file'));
        
        return back()->with('success', '¡Registros migrados correctamente!');
    }
    
}