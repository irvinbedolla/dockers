<?php

namespace App\Http\Controllers;

use App\Models\User;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\Municipios;
use App\Models\Estados;
use App\Models\SeerPerGeneral;
use App\Models\SeerPerAuxiliar;
use App\Models\SeerPerConciliador;
use App\Models\SeerColectivas;
use App\Models\SeerConvenios;
use App\Models\SeerCitados;
use App\Models\SeerAsesoria;
use App\Models\SeerMotivo;
use App\Models\SolicitudMotivo;
use App\Models\SolicitudRama;
use App\Models\SolicitudEconomica;
use App\Models\SeerMotivoSolicitud;
use App\Models\SeerSolicitante;
use App\Models\PreRegistro;
use App\Models\Poder;
use App\Models\Audiencias;
use App\Models\Pagos; 
use App\Models\Concepto; 
use App\Models\PersonaFisica;
use App\Models\Turnos;
use App\Models\DiasInhabiles;
use NumberToWords\NumberToWords; // para convertir números(cantidades) a letras
use App\Models\DocumentosSolicitud;
use App\Models\SeerPerGeneral_old;
use App\Models\SeerCitados_old;
use App\Models\SeerPerConciliador_old;
use App\Models\Asistencia;
use App\Models\SeerCasosExcepcion;

//Para sacar el Id del usuario
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\HistorialAbogado;
use Illuminate\Support\Str; //Se utiliza en la imágenes que se suben en los citados
use App\Models\Sedes;
use App\Models\Usuarios;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use App\Exports\ProductsFromViewExport;
use App\Exports\RatificacionesFromViewExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotificacionesExport;
use App\Models\Deducciones;
use App\Models\TercerEncuentro;
use App\Mail\WelcomeMail;
use App\Mail\SolicitudMail;
use App\Models\PermisosConciliador;
use App\Mail\CorreoAcuseConfirmacion;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailAceptacion;
use App\Mail\MailRechazo;
use App\Exports\ReporteMexicoRati;
use App\Exports\EmpresaSinSeguro;
use App\Exports\SolicitudesExport;
use App\Exports\Convenios;
use App\Mail\ForoMail;
use App\Models\ForoNacional;
use App\Exports\Motivos;
use App\Exports\AudienciasExport;
use App\Exports\AudienciasConciliadorExport;
use App\Exports\AudienciasPORConciliadorExport;
use App\Exports\CumplimientosProgramadosExport;

class SeerController extends Controller
{   

    public function ver_identificacion_solicitante($idSolicitud)
    {   
        $tipo = SeerPerGeneral::where('id', $idSolicitud)->value('tipo_solicitud');

        $fileName = null;
        $baseDir  = null;

        //Si es solicitud patronal visualizamos el documentos desde el Storage de abogados
        if ($tipo == 2) {
            $solicitante = SeerSolicitante::where('id_solicitud', $idSolicitud)->first();

            if ($solicitante && !empty($solicitante->poder_id)) {
                $poder = Poder::where('idAbogado', $solicitante->poder_id)->first();

                if ($poder && !empty($poder->ineDocumento) && $poder->ineDocumento !== 'Sin documento') {
                    $fileName = $poder->ineDocumento;
                    $baseDir  = 'documentos_abogados/' . $poder->idAbogado . '/';
                }
            }
        }

        if (!$fileName) {
            $solicitante = isset($solicitante)
                ? $solicitante
                : SeerSolicitante::where('id_solicitud', $idSolicitud)->firstOrFail();

            $fileName = $solicitante->documentoIdentificacion;
            $baseDir  = 'documentosSolicitud/';
        }

        if (!$fileName || $fileName === 'Sin documento') {
            abort(404, 'Documento no encontrado.');
        }

        $path = $baseDir . $fileName;

        if (!Storage::exists($path)) {
            abort(404, 'Documento no encontrado en almacenamiento.');
        }

        return response()->file(Storage::path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Identificacion.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function seleccionar_representante_patronal(Request $request)
    {
        $data = $request->validate([
            'audiencia_id' => ['required', 'integer'],
            'abogado'      => ['required', 'integer'],
        ]);

        $audiencia = Audiencias::find($data['audiencia_id']);
        if (!$audiencia) {
            return redirect()->route('inicioAudiencia', [
                'id' => $request->input('solicitud'),
                'audiencia_id' => $data['audiencia_id'],
            ])->with('error', 'No se encontró la audiencia para asignar el representante.');
        }

        $poder = Poder::where('idAbogado', $data['abogado'])->first();
        if (!$poder) {
            return redirect()->route('inicioAudiencia', [
                'id' => $request->input('solicitud') ?? $audiencia->id_solicitud,
                'audiencia_id' => $data['audiencia_id'],
            ])->with('error', 'No se encontró el representante seleccionado.');
        }

        $audiencia->poder_id = $poder->idAbogado;
        $audiencia->save();

        session()->flash('preserve_edit_session', true);

        return redirect()->route('inicioAudiencia', [
            'id' => $request->input('solicitud') ?? $audiencia->id_solicitud,
            'audiencia_id' => $data['audiencia_id'],
        ])->with('success', 'Representante legal asignado correctamente.');
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

    public function index()
    {
        // 1. Carga del usuario con sus roles de una sola vez
        $user = auth()->user()->load('roles');
        $id = $user->id;
        $userRole = $user->roles->pluck('name')->first(); // Tomamos el primer rol principal
        $fecha_actual = now()->format('Y-m-d'); // Carbon es más limpio que date()
        
        // Inicialización de variables
        $estadisticas = null;
        $personas = null;
        $asesorias = null;

        // 2. Mapa de delegaciones para rol "Enlace"
        $mapaSedes = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];

        // 3. Switch Case para manejo de Roles (más limpio que múltiples if/else)
        switch ($userRole) {
            case 'Notificador':
                $estadisticas = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
                    ->join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
                    ->join('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
                    ->where('seer_citados.id_notificador', $id)
                    ->where('seer_citados.estatus', 'Pendiente')
                    ->select(
                        'seer_citados.id','seer_general.NUE','seer_solicitante.nombre as nombre_solicitado',
                        'seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
                        'municipios.nombre as municipio_citado','seer_citados.colonia','seer_citados.calle',
                        'seer_citados.n_ext','seer_citados.estatus','seer_citados.tipo_notificacion'
                    )->get();
                break;

            case 'Auxiliar':
                $personas = SeerPerGeneral_old::where('fecha', $fecha_actual)
                    ->where('user_id', $id)
                    ->join('seer_auxiliares','seer_auxiliares.id_solicitud',"=",'seer_general_old.id')
                    ->select("seer_general_old.id","seer_general_old.fecha","seer_general_old.NUE",
                            "seer_general_old.solicitante","seer_auxiliares.tipo_solicitud",
                            "seer_general_old.validado_conciliador")
                    ->get();

                $asesorias = SeerAsesoria::where('fecha', $fecha_actual)
                    ->where('id_usuario', $id)
                    ->count(); // count() es más directo que selectRaw + first()
                
                // Retorno temprano para evitar procesar el resto de la función
                return view('estadisticas.index', compact('estadisticas','userRole','personas','asesorias'));
                
            case 'Excepcion':
                $personas = SeerPerGeneral_old::where('fecha', $fecha_actual)
                    ->where('user_id', $id)
                    ->join('seer_auxiliares','seer_auxiliares.id_solicitud',"=",'seer_general_old.id')
                    ->select("seer_general_old.id","seer_general_old.fecha","seer_general_old.NUE",
                            "seer_general_old.solicitante","seer_auxiliares.tipo_solicitud",
                            "seer_general_old.validado_conciliador")
                    ->get();

                $asesorias = SeerAsesoria::where('fecha', $fecha_actual)
                    ->where('id_usuario', $id)
                    ->count(); // count() es más directo que selectRaw + first()
                
                // Retorno temprano para evitar procesar el resto de la función
                return view('estadisticas.index', compact('estadisticas','userRole','personas','asesorias'));

            case 'Conciliador':
                $personas = SeerPerGeneral_old::where('conciliador_id', $id)
                    ->join('seer_auxiliares','seer_auxiliares.id_solicitud',"=",'seer_general_old.id')
                    ->where('seer_auxiliares.tipo_solicitud','Solicitud')
                    ->where('seer_general_old.validado_conciliador','Pendiente')
                    ->get();
                break;

            case 'Enlace':
                $delegaciones = $mapaSedes[$user->delegacion] ?? [$user->delegacion];

                // Optimizamos la búsqueda de notificadores con Eloquent
                $personas = User::role('Notificador')
                    ->whereIn('delegacion', $delegaciones)
                    ->get();

                $estadisticas = SeerPerGeneral_old::join('seer_citados_old', 'seer_citados_old.id_solicitud', '=', 'seer_general_old.id')
                    ->join('seer_auxiliares', 'seer_auxiliares.id_solicitud', '=', 'seer_general_old.id')
                    ->leftJoin('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general_old.id')
                    ->leftJoin('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
                    ->where('seer_general_old.delegacion', $user->delegacion)
                    ->where('seer_citados_old.id_notificador', 0)
                    ->where('seer_auxiliares.notificacion', '!=', 'Trabajador')
                    ->select(
                        'seer_citados_old.id as id_citado_old', 'seer_general_old.NUE',
                        'seer_general_old.solicitante', 'seer_citados_old.nombre',
                        'seer_citados_old.direccion', 'seer_citados_old.estatus',
                        'municipios.nombre as municipio_nombre'
                    )->get();
                break;
        }

        return view('estadisticas.index', compact('estadisticas','userRole','personas', 'asesorias'));
    }

    public function create()
    {
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        $suma_solicitudes = SeerPerGeneral::
        join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
        ->where("seer_auxiliares.tipo_solicitud","Solicitud")
        ->where('fecha',"=", $fecha_actual)
        ->where('user_id',"=", $id)
        ->selectRaw('count(seer_general.id) as total')
        ->first();

        $suma_ratificaciones = SeerPerGeneral::
        join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
        ->where("seer_auxiliares.tipo_solicitud","Ratificación")
        ->where('fecha',"=", $fecha_actual)
        ->where('user_id',"=", $id)
        ->selectRaw('count(seer_general.id) as total')
        ->first();

        $total = SeerPerGeneral::
            join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
            ->where("seer_auxiliares.tipo_solicitud","Ratificación")
            ->where('fecha',"=", $fecha_actual)
            ->where('user_id',"=", $id)
            ->selectRaw('SUM(seer_auxiliares.monto) as monto')
            ->first();

        return view('estadisticas.crearConsentradoAux', compact('user','userRole','suma_solicitudes','suma_ratificaciones','total'));
    }
    
    public function ver_consentrado_aux(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        $estadisticas  = null;

        return view('estadisticas.crearConsentradoVer', compact('estadisticas','userRole'));
    }

    public function create_notificadores()
    {
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();

        return view('estadisticas.crearNotificador', compact('user','userRole'));
    }

    public function store_notificador(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        //Validar documentacion
        request()->validate([
            'citatorios'                    => 'required|numeric',
            'asesorias_notificador'         => 'required|numeric',
            'solicitudes_levantadas'        => 'required|numeric',
            'ratificaciones_notificador'    => 'required|numeric',
            'multas_notificador'            => 'required|numeric',
            'informe_diario'                => 'required|numeric',
            'informe_foraneo'               => 'required|numeric',
            'integrar_expediente'           => 'required|numeric',
            'escaneo_documentos'            => 'required|numeric',
        ], $data);

        $data['user_id'] = $user["id"];
        $data['fecha'] = date('Y-m-d');
        $data['delegacion'] = $user["delegacion"];

        SeerNotificadores::create($data);  
        return redirect()->route('seer'); 
    }

    public function store_auxiliares(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        //Validar documentacion
        request()->validate([
            'solicitudes'           => 'required|numeric',
            'ratificaciones'        => 'required|numeric',
            'asesorias'             => 'required|numeric',
            'expediente_consulta'   => 'required|numeric',
            'expediente_escaneo'    => 'required|numeric',
            'expediente_foliar'     => 'required|numeric',
            'cuantificacion'        => 'required|numeric',
            'exhortos'              => 'required|numeric',
            'audiencias_celebradas' => 'required|numeric',
            'cumplimientos'         => 'required|numeric',
        ], $data);

        $data['user_id'] = $user["id"];
        $data['fecha'] = date('Y-m-d');
        $data['delegacion'] = $user["delegacion"];

        SeerAuxiliares::create($data);  
        return redirect()->route('seer'); 
    
    }

    public function store_conciliadores(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        //Validar documentacion
        request()->validate([
            'citatorios'                    => 'required|numeric',
            'asesorias_notificador'         => 'required|numeric',
            'solicitudes_levantadas'        => 'required|numeric',
            'ratificaciones_notificador'    => 'required|numeric',
            'razon_registrada'              => 'required|numeric',
            'multas_notificador'            => 'required|numeric',
            'informe_diario'                => 'required|numeric',
            'informe_foraneo'               => 'required|numeric',
            'integrar_expediente'           => 'required|numeric',
            'escaneo_documentos'            => 'required|numeric',
        ], $data);

        $data['user_id'] = $user["id"];
        $data['fecha'] = date('Y-m-d');
        $data['delegacion'] = $user["delegacion"];

        //SeerConciliadores::create($data);  
        return redirect()->route('seer'); 
    
    }

    public function store_delegado(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        //Validar documentacion
        request()->validate([
            'personas_atendidas'                    => 'required|numeric',
            'asesorias'                             => 'required|numeric',
            'solicitudes_inicio'                    => 'required|numeric',
            'audiencias_programadas'                => 'required|numeric',
            'audiencias_celebradas'                 => 'required|numeric',
            'solicitudes_incopetencia'              => 'required|numeric',
            'convenio_audiencia'                    => 'required|numeric',
            'ratificacion_convenios'                => 'required|numeric',
            'monto_convenios'                       => 'required|numeric',
            'notificaciones'                        => 'required|numeric',
            'contancia_no_conciliacion'             => 'required|numeric',
            'contancia_no_conciliacion_patron'      => 'required|numeric',
            'contancia_no_conciliacion_notificacion'=> 'required|numeric',
            'solicitudes_archivadas'                => 'required|numeric',
            'colectivas'                            => 'required|numeric',
            'mujeres'                               => 'required|numeric',
            'hombres'                               => 'required|numeric',
            'despido_injitificado'                  => 'required|numeric',
            'finiquito'                             => 'required|numeric',
            'derecho_preferencia'                   => 'required|numeric',
            'pago_prestaciones'                     => 'required|numeric',
            'terminacion_volintaria'                => 'required|numeric',
            'supuesto_excepciones'                  => 'required|numeric',
            'otros'                                 => 'required|numeric',
            'multas'                                => 'required|numeric',
            'cincuenta_umas'                        => 'required|numeric',
            'cien_umas'                             => 'required|numeric',
            'otro_monto'                            => 'required|numeric',
        ], $data);

        $data['user_id'] = $user["id"];
        $data['fecha'] = date('Y-m-d');
        $data['delegacion'] = $user["delegacion"];

        SeerDelegados::create($data);  
        return redirect()->route('seer'); 
    
    }

    public function estadistica() {
        $user = auth()->user()->load('roles');
        $userRole = $user->roles->pluck('name')->first();
        $delegacionUser = $user->delegacion;

        $usuariosconciliadores = collect();
        $usuariosauxiliares = collect();
        $usuariosnotificadores = collect();
        $estadisticas = collect();

        if (in_array($userRole, ["Super Usuario", "Administrador", "Estadistica"])) {
            $todosUsuarios = User::with('roles')->get();
            
            $usuariosconciliadores = $todosUsuarios->filter(fn($u) => $u->hasRole('Conciliador'));
            $usuariosauxiliares = $todosUsuarios->filter(fn($u) => $u->hasRole('Auxiliar'));
            $usuariosnotificadores = $todosUsuarios->filter(fn($u) => $u->hasRole('Notificador'));
            
            $estadisticas = Sedes::all();

        } elseif (in_array($userRole, ["Enlace", "Delegado"])) {
            // 1. Buscamos la sede principal y sus oficinas de apoyo
            $sedePrincipal = Sedes::where('nombre', $delegacionUser)->first();
            
            if ($sedePrincipal) {
                $estadisticas = Sedes::where('nombre', $delegacionUser)
                    ->orWhere('oficina_apoyo', $sedePrincipal->id)
                    ->get();

                // 2. Extraemos todos los nombres de las sedes (Ej: ['Morelia', 'Zitácuaro'])
                $nombresSedesPermitidas = $estadisticas->pluck('nombre')->toArray();

                // 3. Filtramos la base de usuarios por este array de sedes
                $usuariosBase = User::whereIn('delegacion', $nombresSedesPermitidas)
                    ->with('roles')
                    ->get();

                // 4. Ahora los filtros por rol ya contemplan todas las sedes de la jurisdicción
                $usuariosconciliadores = $usuariosBase->filter(fn($u) => $u->hasRole('Conciliador'));
                $usuariosauxiliares = $usuariosBase->filter(fn($u) => $u->hasRole('Auxiliar'));
                $usuariosnotificadores = $usuariosBase->filter(fn($u) => $u->hasRole('Notificador'));
            }
        }

        $estados = Estados::all();
        $municipios = Municipios::all();

        return view('estadisticas.estadistica', compact(
            'user', 'userRole', 'estadisticas', 'usuariosauxiliares', 
            'usuariosnotificadores', 'estados', 'municipios', 'usuariosconciliadores'
        ));
    }
    
    public function mostrar_reporte(Request $request){
        $data = $request->all();
        //Primero vamos a validar si el reporte sera cuanticativo o detallado
        //Validar documentacion
        /*
        request()->validate([
            //General CumplimientosGrafica
            'tipo_reporte'  => 'required|in:CumplimientosGrafica,Cumplimientos,CumplimientosResumen,Ratificaciones,RatificacionesResumen,CCIRSJL,Concentrado,RatificacionesUsuario,Notificaciones,EstadisticaMexico,RatificacionesGraficas,Graficas,Solicitudes,SolicitudesResumen,SolicitudesGraficas'
        ], $data);
        */
        if(isset($data["sede"]))
            $sede = $data["sede"];
        else
            $sede = "";
        if(isset($data["auxiliar"]))
            $auxiliar = $data["auxiliar"];
        else
            $auxiliar = "";
        if(isset($data["notificador"]))
            $notificador = $data["notificador"];
        else
            $notificador = "";
    
        $fecha_inicial = $data["fecha_inicial"];
        $fecha_final   = $data["fecha_final"];
        $relacionEloquent = "roles";
    
        //Primeramente reporte detallado
        if($data["tipo_reporte"] == "Cumplimientos"){
            // 1. Manejo de Excel
            if ($data["tipo"] == "2") {
                return Excel::download(new ProductsFromViewExport($fecha_inicial, $fecha_final, $sede), 'Cumplimientos.xlsx');
            }
            
            // Consulta Base para Pagos
            $queryBase = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
            ->when($sede !== "Todos", function ($q) use ($sede) {
                // Si es el caso especial de Delegado, filtramos por el array de sedes
                if ($sede === "TodosDelegado") {
                    $id = auth()->user()->id;
                    $user = User::find($id);
                    $sedeUsuario = $user->delegacion;
    
                    if($sedeUsuario == "Morelia"){
                        $delegaciones = ['Morelia', 'Zitácuaro'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Uruapan"){
                        $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Zamora"){
                        $delegaciones = ['Zamora', 'Sahuayo'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                }
                // De lo contrario, filtramos por la sede individual seleccionada
                return $q->where('pago_solicitud.delegacion', $sede);
            });
            
            // --- Pagos de Ratificación ---
            $pagosRatificacion = (clone $queryBase)
                ->where('pago_solicitud.tipo_pago', "Ratificacion")
                ->join('turnos', 'turnos.id', 'pago_solicitud.id_solicitud')
                ->leftJoin('users', 'users.id', 'turnos.id_conciliador')
                ->select(
                    'pago_solicitud.*', // O selecciona campos específicos si prefieres
                    'turnos.NUE', 'turnos.empresa', 'turnos.primero_empresa', 'turnos.segundo_empresa',
                    'turnos.trabajador', 'turnos.primero_trabajador', 'turnos.segundo_trabajador', 'turnos.delegacion as turno_delegacion',
                    'users.name as conciliador_name'
                )
                ->orderBy('users.name')
                ->get();
    
            // --- Pagos de Audiencias ---
            $pagosAudiencias = (clone $queryBase)
                ->whereIn('pago_solicitud.tipo_pago', ["Audiencia","Conciliador"])
                ->join('users', 'users.id', 'pago_solicitud.id_conciliador')
                ->select('pago_solicitud.*', 'users.name as conciliador_name')
                ->get();
                    
            // 3. Generación del PDF
            return \PDF::loadView('PDF/Estadisticas/reporte-Cumplimientos', compact(
                    'fecha_inicial', 'fecha_final', 'pagosRatificacion', 'pagosAudiencias'
                ))
                ->setPaper('a4', 'landscape')
                ->stream('CumplimientosDetalles.pdf');
            
        }
        else if ($data["tipo_reporte"] == "CumplimientosGrafica"){
            $id_usuario = auth()->user()->id;
            $userActual = User::find($id_usuario);
            // 1. Consulta Unificada para Ratificaciones
            $ratificacionesData = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
            ->join('turnos', 'turnos.id', 'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.tipo_pago', 'Ratificacion')
            ->when($sede !== "Todos", function ($q) use ($sede) {
                // Si es el caso especial de Delegado, filtramos por el array de sedes
                if ($sede === "TodosDelegado") {
                    $id = auth()->user()->id;
                    $user = User::find($id);
                    $sedeUsuario = $user->delegacion;
    
                    if($sedeUsuario == "Morelia"){
                        $delegaciones = ['Morelia', 'Zitácuaro'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Uruapan"){
                        $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Zamora"){
                        $delegaciones = ['Zamora', 'Sahuayo'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                }
                return $q->where('pago_solicitud.delegacion', $sede);
            })
            ->selectRaw("
                COUNT(pago_solicitud.id) as total_count,
                SUM(pago_solicitud.monto) as total_monto,
                COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN 1 END) as pagado_count,
                SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as pagado_monto,
                COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 END) as pendiente_count,
                SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as pendiente_monto
            ")
            ->first();
    
            // Reasignar a tus variables originales para no romper la vista Blade
            $pagosRatificacion               = (object)['ratificaciones' => $ratificacionesData->total_count];
            $pagosRatificacionMonto          = (object)['ratificacionesMonto' => $ratificacionesData->total_monto];
            $pagosRatificacionPagado         = (object)['ratificaciones' => $ratificacionesData->pagado_count];
            $pagosRatificacionMontoPagado    = (object)['ratificacionesMonto' => $ratificacionesData->pagado_monto];
            $pagosRatificacionPendiente      = (object)['ratificaciones' => $ratificacionesData->pendiente_count];
            $pagosRatificacionMontoPendiente = (object)['ratificacionesMonto' => $ratificacionesData->pendiente_monto];
    
            // 2. Consulta Unificada para Audiencias
            $audienciasData = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
            ->whereIn('pago_solicitud.tipo_pago', ["Audiencia","Conciliador"])
            ->when($sede !== "Todos", function ($q) use ($sede) {
                // Si es el caso especial de Delegado, filtramos por el array de sedes
                if ($sede === "TodosDelegado") {
                    $id = auth()->user()->id;
                    $user = User::find($id);
                    $sedeUsuario = $user->delegacion;
    
                    if($sedeUsuario == "Morelia"){
                        $delegaciones = ['Morelia', 'Zitácuaro'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Uruapan"){
                        $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Zamora"){
                        $delegaciones = ['Zamora', 'Sahuayo'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                }
                return $q->where('pago_solicitud.delegacion', $sede);
            })
            ->selectRaw("
                COUNT(pago_solicitud.id) as total_count,
                SUM(pago_solicitud.monto) as total_monto,
                COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN 1 END) as pagado_count,
                SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as pagado_monto,
                COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 END) as pendiente_count,
                SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as pendiente_monto
            ")
            ->first();
    
            // Reasignar a tus variables originales
            $pagosAudiencias              = (object)['audiencias' => $audienciasData->total_count];
            $pagosAudienciasMonto         = (object)['audienciasMonto' => $audienciasData->total_monto];
            $pagosAudienciaPagado         = (object)['audiencias' => $audienciasData->pagado_count];
            $pagosAudienciaMontoPagado    = (object)['audienciasMonto' => $audienciasData->pagado_monto];
            $pagosAudienciaPendiente      = (object)['audiencias' => $audienciasData->pendiente_count];
            $pagosAudienciaMontoPendiente = (object)['audienciasMonto' => $audienciasData->pendiente_monto];
    
    

            $usuarios = Turnos::whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                ->join('users', 'users.id', 'turnos.user_id')
                // Aplicamos el filtro de sede solo si no es "Todos"
                ->when($sede !== "Todos", function ($query) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
    
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                    }
                    return $query->where('turnos.delegacion', $sede);
                })
                ->select(
                    'users.name', 
                    DB::raw('COUNT(turnos.id) as ratificacion'), 
                    DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                )
                ->groupBy('users.id', 'users.name')
                ->get();
    
                // Extraemos los nombres para las etiquetas del eje X
                $nombres_rati = $usuarios->pluck('name')->toArray();
                // Extraemos las cantidades para las barras
                $totales_rati = $usuarios->pluck('ratificacion')->toArray();
    
            
            // 1. Obtenemos los datos detallados (usando la consulta anterior)
            $detalleSolicitantes = DB::table('seer_general')
                ->join('users', 'users.id', '=', 'seer_general.user_id')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                    }
                    return $q->where("seer_general.delegacion", $sede);
                })
                ->select('users.name as auxiliar', 'seer_general.id')
                ->get();
        
                // 2. Procesar los datos para la gráfica usando colecciones de Laravel
                // Contamos cuántas veces aparece cada nombre de auxiliar
                $conteoPorUsuario = $detalleSolicitantes->countBy('auxiliar');
        
                $nombres = $conteoPorUsuario->keys();  
                $totales = $conteoPorUsuario->values(); 
    
                $conciliadores = User::role('Conciliador')->select('id','name')->get();
                $i = 0;
                foreach ($conciliadores as $conciliador) {
                    $conciliacion  = SeerPerGeneral::whereBetween('audiencias.fecha',[$fecha_inicial,$fecha_final])
                    ->join("audiencias","audiencias.id_solicitud","=","seer_general.id")
                    ->select(DB::raw('count(audiencias.id) as Conciliacion'))
                    ->where('seer_general.conciliador_id',$conciliador->id)
                    ->whereIn('seer_general.estatus',['Conciliacion','Concluida'])
                    ->first();
        
                    $noconciliacion  = SeerPerGeneral::whereBetween('audiencias.fecha',[$fecha_inicial,$fecha_final])
                    ->join("audiencias","audiencias.id_solicitud","=","seer_general.id")
                    ->select(DB::raw('count(audiencias.id) as NoConciliacion'))
                    ->where('seer_general.conciliador_id',$conciliador->id)
                    ->where('seer_general.estatus','No conciliacion')
                    ->first();
        
                    $conciliadores[$i]->conciliador     = $conciliacion->Conciliacion;
                    $conciliadores[$i]->noconciliador   = $noconciliacion->NoConciliacion;
                    
                    if($conciliacion->Conciliacion == 0){
                        $conciliadores[$i]->total = 0;
                    }else{
                        $resultado = ($conciliacion->Conciliacion / ($conciliacion->Conciliacion + $noconciliacion->NoConciliacion)) * 100;
                        $conciliadores[$i]->total = round($resultado, 2);
                        //$conciliadores[$i]->total           = ($conciliacion->Conciliacion / ($conciliacion->Conciliacion + $noconciliacion->NoConciliacion)) *100;
                    }
                    
                    $i++;
                }
                // Ahora, extraemos las etiquetas (meses) y los datos (counts)
                $labels = $conciliadores->pluck('name')->toArray();;
                $data   = $conciliadores->pluck('total')->toArray();;
    
           
            $solicitudes = DB::table('seer_general')
                ->join('users', 'users.id', '=', 'seer_general.user_id')
                ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
                ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')

                ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->where(function($query) {
                    $query->where('seer_general.incidencia', 0)
                        ->orWhereNull('seer_general.incidencia');
                })
                ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                    // Lógica de sedes vinculadas para Delegados
                    if ($sede === "TodosDelegado") {
                        $mapaSedes = [
                            'Morelia' => ['Morelia', 'Zitácuaro'],
                            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                            'Zamora'  => ['Zamora', 'Sahuayo'],
                        ];
                            
                        $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                        return $q->whereIn('seer_general.delegacion', $delegaciones);
                    }
                    // Filtro manual de una sola sede
                        return $q->where("seer_general.delegacion", $sede);
                })
                ->select(
                    'seer_general.delegacion as sede_nombre', // Agrupamos por este campo
                    DB::raw('COUNT(DISTINCT seer_general.id) as numeroSolicitudes'),
                    DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                )
                ->groupBy('seer_general.delegacion')
                ->get();
                // Extraer etiquetas (Nombres de las sedes)
                $sedes_labels = $solicitudes->pluck('sede_nombre')->toArray();
                // Extraer valores (Número de solicitudes por cada sede)
                $sedes_valores = $solicitudes->pluck('numeroSolicitudes')->toArray();
                
                
            $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->where(function($query) {
                        $query->where('turnos.incidencia', 0)
                            ->orWhereNull('turnos.incidencia');
                    })
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        // Lógica para Delegados (Ver sede propia y oficina de apoyo)
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('turnos.delegacion', $delegaciones);
                        }
                        // Filtro manual por una sede específica
                        return $q->where('turnos.delegacion', $sede);
                    })
                    ->whereIn('turnos.estatus',["Concluida","Concluida Pagos"])
                    ->select(
                        'turnos.delegacion as sede_nombre', // Agrupamos por nombre de sede
                        DB::raw('COUNT(DISTINCT turnos.id) as ratificaciones'),
                    )
                    ->groupBy('turnos.delegacion')
                    ->get();
                // Extraer etiquetas (Nombres de las sedes)
                $sedes_rati_labels = $dataTurnos->pluck('sede_nombre')->toArray();
                // Extraer valores (Número de solicitudes por cada sede)
                $sedes_rati_valores = $dataTurnos->pluck('ratificaciones')->toArray();

            $audiencias = DB::table('seer_general')
                    ->join('users', 'users.id', '=', 'seer_general.user_id')
                    ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
                    ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
                    ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                    ->where(function($query) {
                        $query->where('seer_general.incidencia', 0)
                            ->orWhereNull('seer_general.incidencia');
                    })

                    ->whereBetween('audiencias.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'seer_general.delegacion as sede_nombre', // Agrupamos por sede
                        DB::raw('COUNT(DISTINCT audiencias.id) as total_audiencias')
                    )
                    ->groupBy('seer_general.delegacion')
                    ->get();
                // Extraer etiquetas (Nombres de las sedes)
                $sedes_audiencias_labels = $audiencias->pluck('sede_nombre')->toArray();
                // Extraer valores (Número de solicitudes por cada sede)
                $sedes_audiencias_valores = $audiencias->pluck('total_audiencias')->toArray();

            $notificaciones = DB::table('seer_general')
                ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->where('seer_citados.estatus', '!=', 'Sin asignar')
                ->where('seer_citados.notificacion', 'Centro')
                ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                    if ($sede === "TodosDelegado") {
                        $mapaSedes = [
                            'Morelia' => ['Morelia', 'Zitácuaro'],
                            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                            'Zamora'  => ['Zamora', 'Sahuayo'],
                        ];
                        $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                        return $q->whereIn('seer_general.delegacion', $delegaciones);
                    }
                    return $q->where("seer_general.delegacion", $sede);
                })
                ->select(
                    'seer_general.delegacion as sede_nombre', // Agrupamos por este campo
                    DB::raw("COUNT(DISTINCT CASE WHEN seer_citados.estatus IN ('Notificada','Finalizado exitosamente','Exitosa por Instructivo','No exitosa se constituye') THEN seer_citados.id END) as notificada"),
                )
                ->groupBy('seer_general.delegacion')
                ->get();
                // Extraer etiquetas (Nombres de las sedes)
                $sedes_notificaciones_labels = $notificaciones->pluck('sede_nombre')->toArray();
                // Extraer valores (Número de solicitudes por cada sede)
                $sedes_notificaciones_valores = $notificaciones->pluck('notificada')->toArray();

            $resumenGeneral = [];
            // 1. Sumar Solicitudes
            foreach ($solicitudes as $item) {
                $resumenGeneral[$item->sede_nombre] = ($resumenGeneral[$item->sede_nombre] ?? 0) + $item->numeroSolicitudes;
            }
            // 2. Sumar Ratificaciones
            foreach ($dataTurnos as $item) {
                $resumenGeneral[$item->sede_nombre] = ($resumenGeneral[$item->sede_nombre] ?? 0) + $item->ratificaciones;
            }
            // 3. Sumar Audiencias
            foreach ($audiencias as $item) {
                $resumenGeneral[$item->sede_nombre] = ($resumenGeneral[$item->sede_nombre] ?? 0) + $item->total_audiencias;
            }
            // 4. Sumar Notificaciones
            foreach ($notificaciones as $item) {
                $resumenGeneral[$item->sede_nombre] = ($resumenGeneral[$item->sede_nombre] ?? 0) + $item->notificada;
            }

            // Preparamos los datos finales para la gráfica
            $labels_resumen = array_keys($resumenGeneral);
            $valores_resumen = array_values($resumenGeneral);

            //Grafica    
            return view('PDF/Estadisticas/Graficas',compact('labels','data','ratificacionesData', 'audienciasData','nombres_rati', 'totales_rati', 'detalleSolicitantes','nombres', 'totales', 'sedes_labels', 'sedes_valores', 'solicitudes',
            'dataTurnos', 'sedes_rati_labels', 'sedes_rati_valores','audiencias', 'sedes_audiencias_labels', 'sedes_audiencias_valores','notificaciones', 'sedes_notificaciones_labels', 'sedes_notificaciones_valores',
            'labels_resumen', 
            'valores_resumen'));

            //return view('PDF.Estadisticas.graficaSolicitudes', compact('nombres', 'totales', 'detalleSolicitantes'));

            //return view('PDF.Estadisticas.graficaRatificaciones', compact('nombres', 'totales'));

            //return view('PDF.Estadisticas.graficaCumplimientos', compact('ratificacionesData', 'audienciasData'));
        }
        else if($data["tipo_reporte"] == "Ratificaciones"){
            if ($data["tipo"] == "2") {
                return Excel::download(new RatificacionesFromViewExport($fecha_inicial, $fecha_final, $sede), 'Ratificaciones.xlsx');
            }
            
            // 1. Construir la consulta base
            $query = Turnos::whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                ->join('users', 'users.id', 'turnos.id_conciliador')
                ->join('users as user_usuario', 'user_usuario.id', 'turnos.user_id')
                // Aplicar filtro de sede solo si no es "Todos"
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
    
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('turnos.delegacion', $delegaciones);
                        }
                    }
                    return $q->where('turnos.delegacion', $sede);
                })
                ->select(
                    'turnos.*', 
                    'users.name as conciliador_name', 
                    'user_usuario.name as auxiliar_name'
                )
                ->orderBy('user_usuario.name');
            
            // 2. Ejecutar la consulta
            $Ratificacion = $query->get();

             // 1. Consulta Unificada para Ratificaciones
            $ratificacionePagadas = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
            ->join('turnos', 'turnos.id', 'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.tipo_pago', 'Ratificacion')
            ->when($sede !== "Todos", function ($q) use ($sede) {
                // Si es el caso especial de Delegado, filtramos por el array de sedes
                if ($sede === "TodosDelegado") {
                    $id = auth()->user()->id;
                    $user = User::find($id);
                    $sedeUsuario = $user->delegacion;
    
                    if($sedeUsuario == "Morelia"){
                        $delegaciones = ['Morelia', 'Zitácuaro'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Uruapan"){
                        $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Zamora"){
                        $delegaciones = ['Zamora', 'Sahuayo'];
                        return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                    }
                }
                return $q->where('pago_solicitud.delegacion', $sede);
            })
            ->selectRaw("
                SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as pagado_monto
            ")
            ->first();

            // 3. Generar y retornar PDF
            return \PDF::loadView('PDF/Estadisticas/Ratificaciones', compact('fecha_inicial', 'fecha_final', 'Ratificacion','ratificacionePagadas'))
                ->setPaper('a4', 'landscape')
                ->stream('ratificaciones.pdf');
        }
        else if($data["tipo_reporte"] == "Solicitudes"){

            return Excel::download(new SolicitudesExport($fecha_inicial, $fecha_final, $sede), 'Solicitudes.xlsx');
            /*
                // Obtenemos los usuarios con sus solicitudes y pagos filtrados por fecha y sede
                $detalleSolicitantes = DB::table('seer_general')
                ->join('users', 'users.id', '=', 'seer_general.user_id')
                ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
                ->join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
                ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                    }
                    return $q->where("seer_general.delegacion", $sede);
                })
                ->select(
                    'users.name as auxiliar',
                    'seer_general.consecutivo as folio',
                    'seer_general.fecha',
                    'seer_general.estatus',
                    'seer_general.delegacion',
                    'seer_general.actividad',
                    'seer_solicitante.nombre',
                    'seer_general.tipo_solicitud',
                    DB::raw('GROUP_CONCAT(catalogo_motivos.motivo SEPARATOR ", ") as motivos')
                )
                ->groupBy(
                    'users.name', 
                    'seer_general.id', // Agrupar por el ID de la solicitud es clave
                    'seer_general.consecutivo', 
                    'seer_general.fecha', 
                    'seer_general.estatus', 
                    'seer_general.delegacion', 
                    'seer_general.actividad', 
                    'seer_solicitante.nombre',
                    'seer_general.tipo_solicitud'
                )
                ->orderBy('seer_general.consecutivo', 'desc')
                ->get();
    
                $pdf = \PDF::loadView('PDF/Estadisticas/SolicitudesDetallado',compact('fecha_inicial','fecha_final','detalleSolicitantes'));
                $pdf->setPaper('a4', 'landscape');
                return $pdf->stream('solicitudes.pdf');
            */
        }
        else if($data["tipo_reporte"] == "Notificaciones"){
            //Notificaciones
            return Excel::download(new NotificacionesExport($fecha_inicial, $fecha_final, $sede, $auxiliar , $notificador), 'notificaciones.xlsx');
        }
        else if($data["tipo_reporte"] == "Concentrado"){
            $id_usuario = auth()->user()->id;
            $userActual = User::find($id_usuario);
            $total_cumplimiento = 0;
            //Auxiliares
               // 1. Consulta Principal (Solicitudes y Pagos de Audiencias/Ratificaciones)
                $solicitudes = DB::table('users')
                    ->join('seer_general', 'users.id', '=', 'seer_general.user_id')
                    ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        if ($sede === "TodosDelegado") {
                            $id = auth()->user()->id;
                            $user = User::find($id);
                            $sedeUsuario = $user->delegacion;
            
                            if($sedeUsuario == "Morelia"){
                                $delegaciones = ['Morelia', 'Zitácuaro'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Uruapan"){
                                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Zamora"){
                                $delegaciones = ['Zamora', 'Sahuayo'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'users.id as user_id', 
                        'users.name',
                        DB::raw('COUNT(DISTINCT seer_general.id) as solicitudes'),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as incompetencia"),
                        
                        // Totales de Audiencia (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.id END) as cumplimientoAudiencia"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMonto"),
                        
                        // Totales de Audiencia (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoAudienciaPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMontPagado"),

                        // Totales de Ratificación vía Pago (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.id END) as cumplimientoRatificacion"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMonto"),

                        // Totales de Ratificación vía Pago (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoRatificacionPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMontoPagado")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get()
                    ->keyBy('user_id');

                // 2. Consulta de Turnos (La parte de Ratificaciones que viene de otra tabla)
                $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        return $q->where('turnos.delegacion', $sede);
                    })
                    ->select(
                        'turnos.user_id',
                        DB::raw('COUNT(turnos.id) as ratificaciones'),
                        DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                    )
                    ->groupBy('turnos.user_id')
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $turno = $dataTurnos->get($id);
                    $solicitud->ratificaciones = $turno ? $turno->ratificaciones : 0;
                    $solicitud->ratificacionesMonto = $turno ? $turno->ratificacionesMonto : 0;
                }
                
                $cumplimientos = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    // Unimos ambas tablas con Left Join
                    ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->leftJoin('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    
                    // Unimos la tabla users a través de ambas posibilidades
                    ->leftJoin('users as u_general', 'u_general.id', '=', 'seer_general.user_id')
                    ->leftJoin('users as u_turnos', 'u_turnos.id', '=', 'turnos.user_id')
                    
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        // ... (Tu lógica de sedes para delegados se mantiene igual)
                        return $q->where("pago_solicitud.delegacion", $sede);
                    })
                    ->select(
                        // Usamos COALESCE para tomar el primer ID de usuario que no sea nulo
                        DB::raw('COALESCE(u_general.id, u_turnos.id) as user_id'),
                        DB::raw('COALESCE(u_general.name, u_turnos.name) as user_name'),
                        'pago_solicitud.delegacion',
                        DB::raw('COUNT(pago_solicitud.id) as cumplimientos')
                    )
                    // Agrupamos por los campos calculados
                    ->groupBy('user_id', 'user_name', 'pago_solicitud.delegacion')
                    // Filtramos para asegurar que el pago pertenezca a una de las dos tablas
                    ->where(function($q) {
                        $q->whereNotNull('seer_general.id')
                        ->orWhereNotNull('turnos.id');
                    })
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $cumplimiento = $cumplimientos->get($solicitud->user_id);
                    $solicitud->cumplimientos = $cumplimiento ? $cumplimiento->cumplimientos : 0;
                    $total_cumplimiento++;
                }

            //Audiencias
                $audiencias = DB::table('users')
                    ->join('seer_general', 'users.id', '=', 'seer_general.conciliador_id')
                    ->join('audiencias', 'seer_general.id', '=', 'audiencias.id_solicitud')
                    // Left Joins para traer datos de otras tablas sin perder registros de la principal
                    ->leftJoin('pago_solicitud', function($join) {
                        $join->on('seer_general.id', '=', 'pago_solicitud.id_solicitud')
                            ->whereIN('pago_solicitud.tipo_pago', ['Conciliador','Audiencia']);
                    })
                    ->leftJoin('seer_citados', function($join) {
                        $join->on('seer_general.id', '=', 'seer_citados.id_solicitud')
                            ->where('seer_citados.tipo_notificacion', '=', 'Multa');
                    })
                    ->whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        if ($sede === "TodosDelegado") {
                            $id = auth()->user()->id;
                            $user = User::find($id);
                            $sedeUsuario = $user->delegacion;
            
                            if($sedeUsuario == "Morelia"){
                                $delegaciones = ['Morelia', 'Zitácuaro'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Uruapan"){
                                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Zamora"){
                                $delegaciones = ['Zamora', 'Sahuayo'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'users.id as user_id',
                        'users.name',
                        // Conteo base de audiencias
                        // Conteo base de audiencias
                        DB::raw('COUNT(DISTINCT audiencias.id) as total_audiencias'),
                        DB::raw('COUNT(DISTINCT audiencias.id) as audienencias_programadas'),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion','No conciliacion','Reagendada','Archivada','No conciliacion reagendada','Reinstalacion','Desistimiento',
                        'Archivada en Audiencia') THEN audiencias.id END) as audienencias_celebradas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion','Reinstalacion') THEN audiencias.id END) as convenios"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Archivada','Archivada en Audiencia') THEN audiencias.id END) as achivada"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Incompetencia') THEN audiencias.id END) as incompetencia"),

                        // Cumplimientos y Montos (Pagos)
                        DB::raw('COUNT(DISTINCT pago_solicitud.id) as cumplimientoAudiencia'),
                        DB::raw('SUM(pago_solicitud.monto) as cumplimientoAudienciaMonto'),
                        
                        // Estatus específicos (Convenio, Falta de Interés, Incompetencia)
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus IN ('Concluida', 'Conciliacion') THEN seer_general.id END) as cumplimientoAudienciaConvenio"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Archivada' THEN seer_general.id END) as cumplimientoAudienciaFalta"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as cumplimientoAudienciaIncompetencia"),
                        
                        // Multas y Virtuales
                        DB::raw("COUNT(DISTINCT seer_citados.id) as multas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.tipo = 'Virtual' THEN seer_general.id END) as audiencias_virtuales"),

                        // Dentro del select de la consulta anterior:
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 1 THEN seer_general.id END) as una_audiencia"),
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 2 THEN seer_general.id END) as dos_audiencias"),
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) >= 3 THEN seer_general.id END) as tres_audiencias")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get();
               
            //Notificadores
                $notificaciones = SeerPerGeneral::whereBetween('seer_citados.fecha', [$fecha_inicial, $fecha_final])
                    ->join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
                    ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
                    ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
                    //->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
                    ->join('municipios','municipios.id','seer_citados.municipio_citado')
                    ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
                    ->where(function($query) {
                        $query->where('seer_general.incidencia', 0)
                        ->orWhereNull('seer_general.incidencia');
                    })
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        if ($sede === "TodosDelegado") {
                            $id = auth()->user()->id;
                            $user = User::find($id);
                            $sedeUsuario = $user->delegacion;
            
                            if($sedeUsuario == "Morelia"){
                                $delegaciones = ['Morelia', 'Zitácuaro'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Uruapan"){
                                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Zamora"){
                                $delegaciones = ['Zamora', 'Sahuayo'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    //->when($this->auxiliar !== "Todos", function ($q) { return $q->where('seer_general.user_id', $this->auxiliar); })
                    //->when($this->notificador !== "Todos", function ($q) { return $q->where('seer_citados.id_notificador', $this->notificador); })
                    ->select(
                        'notificador.id as user_id', 
                        'notificador.name',
                        // Total base
                        DB::raw('COUNT(seer_citados.id) as Todas_notificaciones'),
                        
                        // Conteos condicionales por estatus
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Notificada' THEN 1 ELSE 0 END) as notificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No notificada' THEN 1 ELSE 0 END) as notificacion_Nonotificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Pendiente' THEN 1 ELSE 0 END) as notificacion_pendientes"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Exhorto' THEN 1 ELSE 0 END) as notificacion_exhortos"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa se constituye' THEN 1 ELSE 0 END) as notificacion_NESC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa no se constituye' THEN 1 ELSE 0 END) as notificacion_NENSC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Finalizado exitosamente' THEN 1 ELSE 0 END) as exitosamente"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Recibe pero no firma' THEN 1 ELSE 0 END) as firma"),
                    )
                    ->groupBy('notificador.id', 'notificador.name')
                    ->get();
                
                
            
            // Cálculo de efectividad global para el encabezado del reporte
            $total_gral_solicitudes = $solicitudes->sum('solicitudes');
            $total_gral_confirmadas = $solicitudes->sum('confirmadas');
            $porcentaje_confirmacion = ($total_gral_solicitudes > 0) 
                ? ($total_gral_confirmadas / $total_gral_solicitudes) * 100 
                : 0;

            $pdf = \PDF::loadView('PDF/Estadisticas/reporte_cuantitativo', compact(
                'fecha_inicial',
                'fecha_final',
                'solicitudes',
                'audiencias',
                'notificaciones',
                'porcentaje_confirmacion',
                'total_cumplimiento'
            ));
            $pdf->setPaper('legal', 'landscape');
            return $pdf->stream('Reporte_General.pdf');
            
            //return Excel::download(new ReporteGeneral($fecha_inicial, $fecha_final,$sede), 'reporte.xlsx');
        }
        else if($data["tipo_reporte"] == "GeneralSede"){
            //Auxiliares
                $id_usuario = auth()->user()->id;
                $userActual = User::find($id_usuario);
                $total_cumplimiento = 0;
                
                $solicitudes = DB::table('seer_general')
                    ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        // Lógica de sedes vinculadas para Delegados
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        // Filtro manual de una sola sede
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'seer_general.delegacion as sede_nombre', // Agrupamos por este campo
                        DB::raw('COUNT(DISTINCT seer_general.id) as numeroSolicitudes'),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as incompetencia"),
                        
                        // Totales de Audiencia
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.id END) as cumplimientoAudiencia"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMonto"),
                        
                        // Totales de Audiencia (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoAudienciaPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMontPagado"),

                        // Totales de Ratificación
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.id END) as cumplimientoRatificacion"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMonto"),

                        // Totales de Ratificación (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoRatificacionPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMontoPagado")
                    )
                    ->groupBy('seer_general.delegacion')
                    ->get()
                    ->keyBy('sede_nombre'); // Ahora indexamos por el nombre de la sede


                $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        // Lógica para Delegados (Ver sede propia y oficina de apoyo)
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('turnos.delegacion', $delegaciones);
                        }
                        // Filtro manual por una sede específica
                        return $q->where('turnos.delegacion', $sede);
                    })
                    ->select(
                        'turnos.delegacion as sede_nombre', // Agrupamos por nombre de sede
                        DB::raw('COUNT(DISTINCT turnos.id) as ratificaciones'),
                        DB::raw('SUM(pago_solicitud.monto) as ratificacionesMonto') // Monto real del pago
                    )
                    ->groupBy('turnos.delegacion')
                    ->get()
                    ->keyBy('sede_nombre');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $turno = $dataTurnos->get($solicitud->sede_nombre);
                    $solicitud->ratificaciones = $turno ? $turno->ratificaciones : 0;
                    $solicitud->ratificacionesMonto = $turno ? $turno->ratificacionesMonto : 0;
                }

                $cumplimientos = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    // Unimos ambas tablas con Left Join
                    ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->leftJoin('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    
                    // Unimos la tabla users a través de ambas posibilidades
                    ->leftJoin('users as u_general', 'u_general.id', '=', 'seer_general.user_id')
                    ->leftJoin('users as u_turnos', 'u_turnos.id', '=', 'turnos.user_id')
                    
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        // ... (Tu lógica de sedes para delegados se mantiene igual)
                        return $q->where("pago_solicitud.delegacion", $sede);
                    })
                    ->select(
                        // Usamos COALESCE para tomar el primer ID de usuario que no sea nulo
                        //DB::raw('COALESCE(u_general.id, u_turnos.id) as user_id'),
                        //DB::raw('COALESCE(u_general.name, u_turnos.name) as user_name'),
                        'pago_solicitud.delegacion as sede_nombre',
                        DB::raw('COUNT(pago_solicitud.id) as cumplimientos'),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as audienciasMonto")
                    )
                    // Agrupamos por los campos calculados
                    ->groupBy('pago_solicitud.delegacion')
                    // Filtramos para asegurar que el pago pertenezca a una de las dos tablas
                    ->where(function($q) {
                        $q->whereNotNull('seer_general.id')
                        ->orWhereNotNull('turnos.id');
                    })
                    ->get()
                    ->keyBy('sede_nombre');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $cumplimiento = $cumplimientos->get($solicitud->sede_nombre);
                    $solicitud->cumplimientos = $cumplimiento ? $cumplimiento->cumplimientos : 0;
                    $solicitud->audienciasMonto = $cumplimiento ? $cumplimiento->audienciasMonto : 0;
                    $total_cumplimiento++;
                }


            //Audiencias
                $audiencias = DB::table('seer_general')
                    ->join('audiencias', 'seer_general.id', '=', 'audiencias.id_solicitud')
                    ->leftJoin('pago_solicitud', function($join) {
                        $join->on('seer_general.id', '=', 'pago_solicitud.id_solicitud')
                            ->whereIn('pago_solicitud.tipo_pago', ['Audiencia','Conciliador']);
                    })
                    ->leftJoin('seer_citados', function($join) {
                        $join->on('seer_general.id', '=', 'seer_citados.id_solicitud')
                            ->where('seer_citados.tipo_notificacion', '=', 'Multa');
                    })
                    ->whereBetween('audiencias.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'seer_general.delegacion as sede_nombre', // Agrupamos por sede
                        DB::raw('COUNT(DISTINCT audiencias.id) as total_audiencias'),
                        
                        // Cumplimientos y Montos
                        DB::raw('COUNT(DISTINCT pago_solicitud.id) as cumplimientoAudiencia'),
                        DB::raw('SUM(pago_solicitud.monto) as cumplimientoAudienciaMonto'),
                        
                        // Estatus específicos
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Concluida', 'Conciliacion') THEN seer_general.id END) as cumplimientoAudienciaConvenio"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus = 'Archivada' THEN seer_general.id END) as cumplimientoAudienciaFalta"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus = 'Incompetencia' THEN seer_general.id END) as cumplimientoAudienciaIncompetencia"),
                        //Audienicas Programadas
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Pendiente','Conciliacion','No conciliacion','Reagendada','Archivada','No conciliacion reagendada','Incompetencia','Reinstalacion','Desistimiento','Archivada en Audiencia') THEN seer_general.id END) as audienencias_programadas"),
                        //Audienicas Celebradas
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion','Reinstalacion','No conciliacion reagendada') OR (audiencias.estatus = 'No conciliacion' AND (SELECT resolicion_primera FROM seer_conciliadores WHERE id_solicitud = seer_general.id ORDER BY id DESC LIMIT 1) IS NOT NULL) THEN seer_general.id END) as audienencias_celebradas"),
                        //Audienicas Celebradas
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion') THEN seer_general.id END) as convenios"),
                        //No conciliacion
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('No conciliacion') THEN seer_general.id END) as no_conciliacion"),
                        //Falta de interes
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Archivada') THEN seer_general.id END) as achivada"),
                        //Incompetencia
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus IN ('Incompetencia') THEN seer_general.id END) as incompetencia"),
                        // Conteos por número de audiencias (Subconsultas optimizadas por sede)
                        // 1 Audiencia finalizada
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 1 
                            AND (
                                (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) IN ('Conciliacion','Reinstalacion','No conciliacion reagendada') 
                                OR (
                                    (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) = 'No conciliacion' 
                                    AND (SELECT resolicion_primera FROM seer_conciliadores WHERE id_solicitud = seer_general.id ORDER BY id DESC LIMIT 1) IS NOT NULL
                                )
                            ) 
                        THEN seer_general.id END) as una_audiencia"),

                        // 2 Audiencias finalizadas
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 2 
                            AND (
                                (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) IN ('Conciliacion','Reinstalacion','No conciliacion reagendada') 
                                OR (
                                    (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) = 'No conciliacion' 
                                    AND (SELECT resolicion_primera FROM seer_conciliadores WHERE id_solicitud = seer_general.id ORDER BY id DESC LIMIT 1) IS NOT NULL
                                )
                            ) 
                        THEN seer_general.id END) as dos_audiencias"),

                        // 3+ Audiencias finalizadas
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) >= 3 
                            AND (
                                (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) IN ('Conciliacion','Reinstalacion','No conciliacion reagendada') 
                                OR (
                                    (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) = 'No conciliacion' 
                                    AND (SELECT resolicion_primera FROM seer_conciliadores WHERE id_solicitud = seer_general.id ORDER BY id DESC LIMIT 1) IS NOT NULL
                                )
                            ) 
                        THEN seer_general.id END) as tres_audiencias")
                    )
                    ->groupBy('seer_general.delegacion')
                    ->get();

                $ratificacionesTotal= $dataTurnos->sum('ratificaciones');

                // Notificadores - Centro
                $notificaciones = SeerPerGeneral::whereBetween('seer_citados.fecha', [$fecha_inicial, $fecha_final])
                    ->join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
                    ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
                    ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
                    ->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
                    ->join('municipios','municipios.id','seer_citados.municipio_citado')
                    ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
                    ->where(function($query) {
                        $query->where('seer_general.incidencia', 0)
                        ->orWhereNull('seer_general.incidencia');
                    })
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        if ($sede === "TodosDelegado") {
                            $id = auth()->user()->id;
                            $user = User::find($id);
                            $sedeUsuario = $user->delegacion;
            
                            if($sedeUsuario == "Morelia"){
                                $delegaciones = ['Morelia', 'Zitácuaro'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Uruapan"){
                                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Zamora"){
                                $delegaciones = ['Zamora', 'Sahuayo'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    //->when($this->auxiliar !== "Todos", function ($q) { return $q->where('seer_general.user_id', $this->auxiliar); })
                    //->when($this->notificador !== "Todos", function ($q) { return $q->where('seer_citados.id_notificador', $this->notificador); })
                    ->select(
                        'seer_general.delegacion as sede_nombre', // Agrupamos por este campo
                        // Total base
                        DB::raw('COUNT(seer_citados.id) as Todas_notificaciones'),
                        
                        // Conteos condicionales por estatus
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Notificada' THEN 1 ELSE 0 END) as notificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No notificada' THEN 1 ELSE 0 END) as notificacion_Nonotificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Pendiente' THEN 1 ELSE 0 END) as notificacion_pendientes"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Exhorto' THEN 1 ELSE 0 END) as notificacion_exhortos"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa se constituye' THEN 1 ELSE 0 END) as notificacion_NESC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa no se constituye' THEN 1 ELSE 0 END) as notificacion_NENSC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Finalizado exitosamente' THEN 1 ELSE 0 END) as exitosamente"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Recibe pero no firma' THEN 1 ELSE 0 END) as firma"),
                        DB::raw("SUM(CASE WHEN seer_citados.notificacion = 'Centro' THEN 1 ELSE 0 END) as notificadas_centro"),
                        DB::raw("SUM(CASE WHEN seer_citados.notificacion = 'Trabajador' THEN 1 ELSE 0 END) as notificadas_trabajador")
                    )
                    ->groupBy('seer_general.delegacion')
                    ->get();

                // Notificadores - Solicitante
                $notificacionesSol = DB::table('seer_general')
                    ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
                    ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                    ->where('seer_citados.estatus', '!=', 'Sin asignar')
                    ->where('seer_citados.notificacion', 'Trabajador')
                    ->when($sede !== "Todos", function ($q) use ($sede, $userActual) {
                        if ($sede === "TodosDelegado") {
                            $mapaSedes = [
                                'Morelia' => ['Morelia', 'Zitácuaro'],
                                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                                'Zamora'  => ['Zamora', 'Sahuayo'],
                            ];
                            $delegaciones = $mapaSedes[$userActual->delegacion] ?? [$userActual->delegacion];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'seer_general.delegacion as sede_nombre',
                        DB::raw('COUNT(seer_citados.id) as Todas_notificaciones'),
                        DB::raw("SUM(CASE WHEN seer_citados.notificacion = 'Trabajador' THEN 1 ELSE 0 END) as total_solicitante"),
                    )
                    ->groupBy('seer_general.delegacion')
                    ->get();

                $notificacionesSolIndexed = $notificacionesSol->keyBy('sede_nombre');
                foreach ($notificaciones as $notificacion) {
                    $sede = $notificacion->sede_nombre;
                    $notificacion->total_solicitante = isset($notificacionesSolIndexed[$sede]) ? $notificacionesSolIndexed[$sede]->total_solicitante : 0;
                }

            //Totales
                // 2. Cálculo del Gran Total usando la colección de Laravel
                $total_notificaciones = [
                    'sede_nombre'               => 'TOTAL GENERAL',
                    'Todas_notificaciones'      => $notificaciones->sum('Todas_notificaciones'),
                    'notificada'                => $notificaciones->sum('notificada'),
                    'notificacion_Nonotificada' => $notificaciones->sum('notificacion_Nonotificada'),
                    'notificacion_pendientes'   => $notificaciones->sum('notificacion_pendientes'),
                    'notificacion_exhortos'     => $notificaciones->sum('notificacion_exhortos'),
                    'notificacion_NESC'         => $notificaciones->sum('notificacion_NESC'),
                    'notificacion_NENSC'        => $notificaciones->sum('notificacion_NENSC'),
                    'exitosamente'              => $notificaciones->sum('exitosamente'),
                    'firma'                     => $notificaciones->sum('firma'),
                    'total_centro'              => $notificaciones->sum('total_centro'),
                ];


                $granTotal = [
                    'sede_nombre' => 'TOTAL',
                    'numeroSolicitudes' => $solicitudes->sum('numeroSolicitudes'),
                    'confirmadas' => $solicitudes->sum('confirmadas'),
                    'incompetencia' => $solicitudes->sum('incompetencia'),
                    
                    'cumplimientoAudiencia' => $solicitudes->sum('cumplimientoAudiencia'),
                    'cumplimientoAudienciaMonto' => $solicitudes->sum('cumplimientoAudienciaMonto'),
                    
                    'cumplimientoAudienciaPagado' => $solicitudes->sum('cumplimientoAudienciaPagado'),
                    'cumplimientoAudienciaMontPagado' => $solicitudes->sum('cumplimientoAudienciaMontPagado'),

                    'cumplimientoRatificacion' => $solicitudes->sum('cumplimientoRatificacion'),
                    'cumplimientoRatificacionMonto' => $solicitudes->sum('cumplimientoRatificacionMonto'),

                    'cumplimientoRatificacionPagado' => $solicitudes->sum('cumplimientoRatificacionPagado'),
                    'cumplimientoRatificacionMontoPagado' => $solicitudes->sum('cumplimientoRatificacionMontoPagado'),
                ];


                $total_audiencias = [
                    'sede_nombre' => 'TOTAL',
                    'total_audiencias' => $audiencias->sum('total_audiencias'),
                    'cumplimientoAudiencia' => $audiencias->sum('cumplimientoAudiencia'),
                    'cumplimientoAudienciaMonto' => $audiencias->sum('cumplimientoAudienciaMonto'),
                    'cumplimientoAudienciaConvenio' => $audiencias->sum('cumplimientoAudienciaConvenio'),
                    'cumplimientoAudienciaFalta' => $audiencias->sum('cumplimientoAudienciaFalta'),
                    'cumplimientoAudienciaIncompetencia' => $audiencias->sum('cumplimientoAudienciaIncompetencia'),
                    'multas' => $audiencias->sum('multas'),
                    'audiencias_virtuales' => $audiencias->sum('audiencias_virtuales'),
                    'una_audiencia' => $audiencias->sum('una_audiencia'),
                    'dos_audiencias' => $audiencias->sum('dos_audiencias'),
                    'tres_audiencias' => $audiencias->sum('tres_audiencias'),
                ];

                //$audienciasConTotal = $audiencias->push((object)$total_audiencias);
                //$solicitudesConTotal = $solicitudes->push((object)$granTotal);   
                //$notificacionesConTotal = $notificaciones->push((object)$total_notificaciones);
               
            

            $pdf = \PDF::loadView('PDF/Estadisticas/reporte_cuantitativo_sede', compact('fecha_inicial','fecha_final','solicitudes','audiencias','notificaciones', 'notificacionesSol', 'ratificacionesTotal'));
            $pdf->setPaper('legal', 'landscape');
            return $pdf->stream('archivo.pdf');
        }
        else if($data["tipo_reporte"] == "EstadisticaMexico"){
            return Excel::download(new ReporteMexicoRati($fecha_inicial, $fecha_final,$sede), 'INEGI.xlsx');
        }
        else if($data["tipo_reporte"] == "CCIRSJL"){
            //2 CONCILIACION EN MATERIA LABORAL
            $total_asesoria =  SeerAsesoria::whereBetween('fecha', [$fecha_inicial,$fecha_final])
            ->selectRaw('count(seer_asesorias.id) as total_asesorias')
            ->where('delegacion',$sede)
            ->first();
            //DEPIDO
                $solicitud_despido_H  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_despido_H = $solicitud_despido_H->where("seer_general.delegacion", $sede);
                }
                $solicitud_despido_H = $solicitud_despido_H->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->where('seer_motivos.id_motivo',1)
                ->where('seer_solicitante.sexo','H')
                ->first();
                $solicitud_despido_M  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_despido_M = $solicitud_despido_M->where("seer_general.delegacion", $sede);
                }
                $solicitud_despido_M = $solicitud_despido_M->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->where('seer_motivos.id_motivo',1)
                ->where('seer_solicitante.sexo','M')
                ->first();
            
            //FINIQUIETO
                $solicitud_finiquito_H  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_finiquito_H = $solicitud_finiquito_H->where("seer_general.delegacion", $sede);
                }
                $solicitud_finiquito_H = $solicitud_finiquito_H->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->where('seer_motivos.id_motivo',1)
                ->where('seer_solicitante.sexo','H')
                ->first();
    
                $solicitud_finiquito_M  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_finiquito_M = $solicitud_finiquito_M->where("seer_general.delegacion", $sede);
                }
                $solicitud_finiquito_M = $solicitud_finiquito_M->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->where('seer_motivos.id_motivo',1)
                ->where('seer_solicitante.sexo','M')
                ->first();
            //DERECHO DE PREFERERNCIA ATIGUEDAD Y ASENSO
                $solicitud_finiquito_H  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_finiquito_H = $solicitud_finiquito_H->where("seer_general.delegacion", $sede);
                }
                $solicitud_finiquito_H = $solicitud_finiquito_H->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->whereIn('seer_motivos.id_motivo',[4,5,6])
                ->where('seer_solicitante.sexo','H')
                ->first();
    
                $solicitud_finiquito_H  = SeerPerGeneral::whereBetween('seer_general.fecha',[$fecha_inicial,$fecha_final]);
                if($sede !== "Todos"){
                    $solicitud_finiquito_H = $solicitud_finiquito_H->where("seer_general.delegacion", $sede);
                }
                $solicitud_finiquito_H = $solicitud_finiquito_H->select(DB::raw('count(seer_general.id) as solicitudes'))
                ->join('seer_motivos','seer_motivos.id_solicitud','seer_general.id')
                ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
                ->whereIn('seer_motivos.id_motivo',[4,5,6])
                ->where('seer_solicitante.sexo','M')
                ->first();
        }
        else if($data["tipo_reporte"] == "SeguroSocial"){
            return Excel::download(new EmpresaSinSeguro($fecha_inicial, $fecha_final, $sede), 'empresas.xlsx');
        }
        else if($data["tipo_reporte"] == "Productividad"){
            // 1. Consulta Unificada para Ratificaciones
            /*$ratificacionesData = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                ->join('turnos', 'turnos.id', 'pago_solicitud.id_solicitud')
                ->where('pago_solicitud.tipo_pago', 'Ratificacion')*/
            $ratificacionesData = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                ->where('pago_solicitud.tipo_pago', 'Ratificacion')
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                    }
                    return $q->where('pago_solicitud.delegacion', $sede);
                })
                ->selectRaw("
                    COUNT(*) as total_count, SUM(pago_solicitud.monto) as total_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN 1 END) as pagado_sin_pena_count,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado con pena convencional' THEN 1 END) as pagado_con_pena_count,
                    COUNT(CASE WHEN pago_solicitud.estatus IN ('Pagado', 'Pagado con pena convencional') THEN 1 END) as pagado_count,
                    SUM(CASE WHEN pago_solicitud.estatus IN ('Pagado', 'Pagado con pena convencional')THEN pago_solicitud.monto ELSE 0 END) as pagado_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 END) as pendiente_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as pendiente_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'No pagado' THEN 1 END) as noPagado_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'No pagado' THEN pago_solicitud.monto ELSE 0 END) as noPagado_monto
                ")
                   /* COUNT(pago_solicitud.id) as total_count,
                    SUM(pago_solicitud.monto) as total_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN 1 END) as pagado_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as pagado_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 END) as pendiente_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as pendiente_monto
                ")*/
            ->first();
            $promediosRatificaciones = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                ->join('turnos', 'turnos.id', 'pago_solicitud.id_solicitud')
                ->where('pago_solicitud.tipo_pago', 'Ratificacion')
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $user = User::find(auth()->id());
                        $sedeUsuario = $user->delegacion;
                        
                        $mapping = [
                            'Morelia' => ['Morelia', 'Zitácuaro'],
                            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                            'Zamora'  => ['Zamora', 'Sahuayo']
                        ];

                        if (isset($mapping[$sedeUsuario])) {
                            return $q->whereIn('pago_solicitud.delegacion', $mapping[$sedeUsuario]);
                        }
                    }
                    return $q->where('pago_solicitud.delegacion', $sede);
                })
                ->selectRaw("
                    pago_solicitud.delegacion as sede,
                    COUNT(pago_solicitud.id) as total_pagos,
                    COUNT(DISTINCT DATE(pago_solicitud.fecha)) as dias_con_actividad
                ")
                ->groupBy('pago_solicitud.delegacion')
                ->get()
                ->map(function ($item) {
                    // Calculamos el promedio evitando división por cero
                    $promedio = $item->dias_con_actividad > 0 
                        ? $item->total_pagos / $item->dias_con_actividad 
                        : 0;

                    return [
                        'sede'               => $item->sede,
                        'total_pagos'        => $item->total_pagos,
                        'dias_con_actividad' => $item->dias_con_actividad,
                        'promedio_diario'    => $promedio
                    ];
            });

            // Reasignar a tus variables originales para no romper la vista Blade
            $pagosRatificacion               = (object)['ratificaciones' => $ratificacionesData->total_count];
            $pagosRatificacionMonto          = (object)['ratificacionesMonto' => $ratificacionesData->total_monto];
            $pagosRatificacionPagado         = (object)['ratificaciones' => $ratificacionesData->pagado_count];
            $pagosRatificacionMontoPagado    = (object)['ratificacionesMonto' => $ratificacionesData->pagado_monto];
           $pagosRatificacionPendiente       = (object)['ratificaciones' => $ratificacionesData->pendiente_count];
            $pagosRatificacionMontoPendiente = (object)['ratificacionesMonto' => $ratificacionesData->pendiente_monto];
            $pagosRatificacionNoPagado       = (object)['ratificaciones' => $ratificacionesData->noPagado_count];
            $pagosRatificacionMontoNoPagado  = (object)['ratificacionesMonto' => $ratificacionesData->noPagado_monto];
    
            // 2. Consulta Unificada para Audiencias
            $audienciasData = Audiencias::whereBetween('audiencias.fecha', [$fecha_inicial, $fecha_final])
                ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
                ->join('pago_solicitud','pago_solicitud.id_solicitud','=','audiencias.id_solicitud')
                //->whereIn('pago_solicitud.tipo_pago', ["Audiencia","Conciliador"])
                //->join('audiencias', 'audiencias.id_solicitud', '=', 'pago_solicitud.id_solicitud')
                //->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                        }
                    }
                    return $q->where('pago_solicitud.delegacion', $sede);
                })
                ->selectRaw("
                    COUNT(DISTINCT audiencias.id) as total_count,
                    SUM(pago_solicitud.monto) as total_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha <= '{$fecha_final}' THEN pago_solicitud.id END) as pagado_audiencias_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha <= '{$fecha_final}' THEN pago_solicitud.monto ELSE 0 END) as pagado_audiencias_monto,
                    COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 END) as pendiente_audiencias_count,
                    SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' OR (pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha > '{$fecha_final}') THEN pago_solicitud.monto ELSE 0 END) as pendiente_audiencias_monto
                ")
            ->first();
            // Reasignar a tus variables originales
            $pagosAudiencias      = (object)['audiencias' => $audienciasData->total_count];
            $pagosAudienciasMonto = (object)['audienciasMonto' => $audienciasData->total_monto];
            $pagosAudienciasPagado         = (object)['audiencias' => $audienciasData->pagado_audiencias_count];
            $pagosAudienciasMontoPagado    = (object)['audienciasMonto' => $audienciasData->pagado_audiencias_monto];
            $pagosAudienciaPendiente      = (object)['audiencias' => $audienciasData->pendiente_audiencias_count];
            $pagosAudienciaMontoPendiente = (object)['audienciasMonto' => $audienciasData->pendiente_audiencias_monto];
            // 3. Consulta para Promedios por Sede
            $promediosPagos = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $user = User::find(auth()->id());
                        $sedeUsuario = $user->delegacion;
                        
                        $mapping = [
                            'Morelia' => ['Morelia', 'Zitácuaro'],
                            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                            'Zamora'  => ['Zamora', 'Sahuayo']
                        ];

                        if (isset($mapping[$sedeUsuario])) {
                            return $q->whereIn('pago_solicitud.delegacion', $mapping[$sedeUsuario]);
                        }
                    }
                    return $q->where('pago_solicitud.delegacion', $sede);
                })
                ->selectRaw("
                    pago_solicitud.delegacion as sede,
                    COUNT(pago_solicitud.id) as total_pagos,
                    COUNT(DISTINCT DATE(pago_solicitud.fecha)) as dias_con_actividad
                ")
                ->groupBy('pago_solicitud.delegacion')
                ->get()
                ->map(function ($item) {
                    // Calculamos el promedio evitando división por cero
                    $promedio = $item->dias_con_actividad > 0 
                        ? $item->total_pagos / $item->dias_con_actividad 
                        : 0;

                    return [
                        'sede'               => $item->sede,
                        'total_pagos'        => $item->total_pagos,
                        'dias_con_actividad' => $item->dias_con_actividad,
                        'promedio_diario'    => $promedio
                    ];
            });
            $usuariosTotal = Turnos::whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                ->join('users', 'users.id', 'turnos.user_id')
                // Aplicamos el filtro de sede solo si no es "Todos"
                ->when($sede !== "Todos", function ($query) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
    
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                    }
                    return $query->where('turnos.delegacion', $sede);
                })
                ->select(
                    'users.name', 
                    DB::raw('COUNT(turnos.id) as ratificacion'), 
                    DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                )
                ->groupBy('users.id', 'users.name')
                ->get();
    
            $usuariosDias = Turnos::whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                ->join('users', 'users.id', 'turnos.user_id')
                // Filtro condicional de sede
                ->when($sede !== "Todos", function ($query) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
    
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $query->whereIn('turnos.delegacion', $delegaciones);
                        }
                    }
                    return $query->where('turnos.delegacion', $sede);
                })
                ->select(
                    'users.name',
                    'turnos.fecha', 
                    DB::raw('COUNT(turnos.id) as numero')
                )
                // Agrupamos por fecha y por usuario para obtener el conteo diario por persona
                ->groupBy('turnos.fecha', 'users.id', 'users.name')
                ->orderBy('turnos.fecha', 'asc') // Opcional: ordena por fecha para facilitar la lectura
                ->get();
    
            // 1. Obtenemos los totales y el conteo de días con actividad por sede
            $promedios = Turnos::whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                ->when($sede !== "Todos", function ($query) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $sedeUsuario = auth()->user()->delegacion;
                        $mapping = [
                            'Morelia' => ['Morelia', 'Zitácuaro'],
                            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                            'Zamora'  => ['Zamora', 'Sahuayo'],
                        ];
                        return $query->whereIn('turnos.delegacion', $mapping[$sedeUsuario] ?? [$sedeUsuario]);
                    }
                    return $query->where('turnos.delegacion', $sede);
                })
                ->select(
                    'turnos.delegacion as sede',
                    DB::raw('COUNT(turnos.id) as total'),
                    // Contamos fechas únicas para saber cuántos días realmente se trabajó
                    DB::raw('COUNT(DISTINCT turnos.fecha) as dias_activos')
                )
                ->groupBy('turnos.delegacion')
                ->get()
                ->map(function ($item) {
                    // Calculamos el promedio: Total / Días Activos
                    $item->promedio = $item->dias_activos > 0 ? ($item->total / $item->dias_activos) : 0;
                    return $item;
                });


            $solicitudes = SeerPerGeneral::join("users", "users.id", "=", "seer_general.user_id")
                ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                ->leftJoin('turnos', 'users.id', '=', 'turnos.user_id') // Join para ratificaciones
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                    }
                    return $q->where("seer_general.delegacion", $sede);
                })
                ->select(
                    'users.id as user_id', 
                    'users.name',
                    // Conteo total de solicitudes
                    DB::raw('COUNT(DISTINCT seer_general.id) as solicitudes'),
                    // Solicitudes Confirmadas (No Pendiente, Prevencion, Rechazado)
                    DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                )
                ->groupBy('users.id', 'users.name')
            ->get(); 
            





            $pdf = \PDF::loadView('PDF/Estadisticas/SolicitudesCantidad',compact('fecha_inicial','fecha_final','solicitudes','usuariosTotal','usuariosDias','promedios'
                ,'pagosRatificacion','pagosRatificacionMonto','pagosRatificacionNoPagado','pagosRatificacionMontoNoPagado'
                ,'pagosAudiencias','pagosAudienciasMonto','pagosRatificacionPagado','pagosRatificacionMontoPagado'
                ,'pagosRatificacionPendiente','pagosRatificacionMontoPendiente', 'pagosAudienciasPagado', 'pagosAudienciasMontoPagado', 'pagosAudienciaPendiente', 'pagosAudienciaMontoPendiente','promediosPagos','promediosRatificaciones'));
            return $pdf->stream('Productividad.pdf');


            /*
            $pdf = \PDF::loadView('PDF/Estadisticas/RatificacionUsuario',compact('fecha_inicial','fecha_final','usuariosTotal','usuariosDias','promedios'));
                //$pdf->setPaper('a4', 'landscape');
                return $pdf->stream('archivo.pdf');

            $pdf = \PDF::loadView('PDF/Estadisticas/reporte-CumplimientosMonto', 
                compact('fecha_inicial','fecha_final','pagosRatificacion','pagosRatificacionMonto',
                'pagosAudiencias','pagosAudienciasMonto','pagosRatificacionPagado','pagosRatificacionMontoPagado',
                'pagosRatificacionPendiente','pagosRatificacionMontoPendiente','promediosPagos'));
                return $pdf->stream('archivo.pdf');
            */
        }
        else if($data["tipo_reporte"] == "Convenios"){
            return Excel::download(new Convenios($fecha_inicial, $fecha_final, $sede), 'Convenios.xlsx');
        }
        else if($data["tipo_reporte"] == "Motivos"){
            return Excel::download(new Motivos($fecha_inicial, $fecha_final, $sede), 'Reporte_motivos.xlsx');
        }
        else if ($data["tipo_reporte"] == "Audiencias"){
            $conciliador = $data['conciliador'] ?? 'Todos';
            return Excel::download(new AudienciasExport($fecha_inicial, $fecha_final, $sede, $conciliador), 'audiencias.xlsx');
        }
        else if($data["tipo_reporte"] == "AudienciaConciliador"){
            return Excel::download(new AudienciasConciliadorExport($fecha_inicial, $fecha_final, $sede), 'audienciasConciliador.xlsx');
        }
        else if($data["tipo_reporte"] == "CumplimientosProgramados"){
            return Excel::download(new CumplimientosProgramadosExport($fecha_inicial, $fecha_final, $sede), 'CumplimientosProgramados.xlsx');
        }
        else if($data["tipo_reporte"] == "ReporteMunicipio"){
            $solicutudes_minicipio = DB::table('seer_general')
                ->join('seer_solicitante','seer_solicitante.id_solicitud', "=", 'seer_general.id')
                ->join('municipios', 'seer_solicitante.municipio_domicilio', '=', 'municipios.id')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->where('municipios.estado', "=", 16)
                ->when($sede !== "Todos", function ($q) use ($sede) {
                    if ($sede === "TodosDelegado") {
                        $id = auth()->user()->id;
                        $user = User::find($id);
                        $sedeUsuario = $user->delegacion;
        
                        if($sedeUsuario == "Morelia"){
                            $delegaciones = ['Morelia', 'Zitácuaro'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Uruapan"){
                            $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                        else if($sedeUsuario == "Zamora"){
                            $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                    }
                    return $q->where("seer_general.delegacion", $sede);
                })
                ->select(
                    'seer_general.delegacion', // Campo para agrupar por sede
                    'municipios.nombre as municipio', 
                    DB::raw('COUNT(seer_general.id) as total_solicitudes')
                )
                ->groupBy('seer_general.delegacion', 'municipios.id', 'municipios.nombre')
                ->orderByRaw("FIELD(seer_general.delegacion, 'Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo') ASC")
                ->orderBy('municipios.nombre', 'asc')
                ->get();

            $agrupados = $solicutudes_minicipio->groupBy('delegacion');

            $pdf = \PDF::loadView('PDF/Estadisticas/reporteMunicipios', compact('agrupados'));
            return $pdf->stream('Reporte_municipio.pdf');
        }
        else if($data["tipo_reporte"] == "ReporteActividad"){
            $solicutudes_actividad = DB::table('seer_general')
                ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                ->select(
                    'seer_general.actividad',
                    DB::raw('COUNT(seer_general.id) as total_solicitudes')
                )
                ->groupBy('seer_general.actividad')
                ->orderBy('seer_general.actividad', 'asc')
                ->get();

            $pdf = \PDF::loadView('PDF/Estadisticas/reporteActividad', compact('solicutudes_actividad'));
            return $pdf->stream('Reporte_actividad.pdf');
        }
    }

    public function create_persona_s(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $estados = Estados::all();
        $municipios = Municipios::all();
        $relacionEloquent = 'roles';

        $conciliadores = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        return view('estadisticas.crearPersonaAux', compact('user','userRole','municipios','estados','conciliadores'));
    }

    public function create_persona_r(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $estados = Estados::all();
        $municipios = Municipios::all();
        $relacionEloquent = 'roles';

        $conciliadores = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        return view('estadisticas.crearPersonaAuxR', compact('user','userRole','municipios','estados','conciliadores'));
    }

    public function obtenerMunicipio($id){
        try {
            $municipios = Municipios::where('estado', $id)->get(['id','nombre']);
            //Log::info('obtenerMunicipio called', ['estado_id' => $id, 'count' => count($municipios)]);
            return response()->json($municipios);
        } catch (\Exception $e) {
            //Log::error('obtenerMunicipio error', ['estado_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([], 500);
        }
    }

    public function auxiliar_persona(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');
        $cont = count($data["citado"]);

        //Validar el Numero de expediente
        $nue = SeerPerGeneral_old::where("NUE",$data["NUE"])->first();
        if($nue){
            return back()->withErrors('El Numero de expediente '.$nue->NUE.' ya existe.');
        }

        //Validar documentacion
        request()->validate([
            //General
            'NUE'                   => 'required|min:18|max:18',
            'solicitante'           => 'required',
            'estado_solicitante'    => 'required|numeric',
            'mun_solicitante'       => 'required|numeric',
            'actividad_economica'   => 'required',
            'conciliador_id'        => 'required|numeric',

            //Auxiliares
            'sexo'                  => 'required|in:H,M',
            'motivo'                => 'required|in:Despido,Pago de prestaciones,Recision de la relación laboral,Derecho de preferencia,Derecho de antiguedad,Derecho de ascesnso,Terminación voluntaria de relación laboral,Supuestos de Excepción 685-Ter LFT,Otros',
            'notificacion'          => 'required|in:Trabajador,Centro,Ambos',

        ], $data);

        $data_general = [
            'fecha'                 => $fecha_actual,
            'fecha_confimacion'     => $data["fecha_confirmacion"],
            'NUE'                   => $data["NUE"],
            'solicitante'           => $data["solicitante"],
            'estado_solicitante'    => $data["estado_solicitante"],
            'mun_solicitante'       => $data["mun_solicitante"],
            'user_id'               => $id,
            'conciliador_id'        => $data["conciliador_id"],
            'delegacion'            => $user["delegacion"],
        ];

        SeerPerGeneral_old::create($data_general);  
        $id_general  = SeerPerGeneral_old::latest('id')->first();

        $data_auxiliar = [
            'id_solicitud'              => $id_general["id"],
            'sexo'                      => $data["sexo"],
            'actividad_economica'       => $data["actividad_economica"],
            'motivo'                    => $data["motivo"],
            'notificacion'              => $data["notificacion"],
            'tipo_solicitud'            => "Solicitud",
            'monto'                     => "0"
        ];
        
        SeerPerAuxiliar::create($data_auxiliar);  

        for($i = 0; $i < $cont; $i++) {
            $data_citado = [
                'id_solicitud'  => $id_general["id"],
                'fecha'         => $fecha_actual,
                'nombre'        => $data["citado"][$i],
                'direccion'     => $data["direccion"][$i], 
                'id_municipio'  => 0, 
                'id_estado'     => 0,
                'observaciones' => ''
            ];
            SeerCitados_old::create($data_citado);
        }

        return redirect()->route('seer');
    }

    public function auxiliar_personar(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');
        
        //Validar el Numero de expediente
        $nue = SeerPerGeneral_old::where("NUE",$data["NUE"])->first();
        if($nue){
            return back()->withErrors('El Numero de expediente '.$nue->NUE.' ya existe.');
        }
        //Validar documentacion
        request()->validate([
            //General
            'NUE'                   => 'required|min:18|max:18',
            'solicitante'           => 'required',
            'estado_solicitante'    => 'required|numeric',
            'mun_solicitante'       => 'required|numeric',
            'actividad_economica'   => 'required',

            //Auxiliares
            'sexo'                  => 'required|in:H,M',
            'motivo'                => 'required|in:Despido,Pago de prestaciones,Recision de la relación laboral,Derecho de preferencia,Derecho de antiguedad,Derecho de ascesnso,Terminación voluntaria de relación laboral',
            'monto'                 => 'required|numeric',
            'estatus'               => 'required|in:Pendiente,Parcial,Cumplido',
        ], $data);

        $data_general = [
            'fecha'                 => $fecha_actual,
            'NUE'                   => $data["NUE"],
            'solicitante'           => $data["solicitante"],
            'estado_solicitante'    => $data["estado_solicitante"],
            'mun_solicitante'       => $data["mun_solicitante"],
            'user_id'               => $id,
            'delegacion'            => $user["delegacion"],
        ];

        SeerPerGeneral_old::create($data_general);  
        $id_general  = SeerPerGeneral_old::latest('id')->first();

        $data_auxiliar = [
            'id_solicitud'              => $id_general["id"],
            'sexo'                      => $data["sexo"],
            'actividad_economica'       => $data["actividad_economica"],
            'motivo'                    => $data["motivo"],
            'monto'                     => $data["monto"],
            'estatus'                   => $data["estatus"],
            'tipo_solicitud'            => "Ratificación",
        ];
        SeerPerAuxiliar::create($data_auxiliar);  

        if(isset($data["citado"])) {
            $cont = count($data["citado"]);
            for($i = 0; $i < $cont; $i++) {
                $data_citado = [
                    'id_solicitud'  => $id_general["id"],
                    'fecha'         => $fecha_actual,
                    'nombre'        => $data["citado"][$i], 
                    'direccion'     => $data["direccion"][$i], 
                    //'id_municipio'  => $data["estado_citado"][$i], 
                    //'id_estado'     => $data["municipio_citado"][$i]
                ];
                SeerCitados_old::create($data_citado);
            }
        }

        return redirect()->route('seer');
    }

    public function ver_auxiliar($id){
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $userRole = $user->roles->pluck('name')->all();

        $general  = SeerPerGeneral_old::find($id);
        $auxiliar = SeerPerAuxiliar::where("id_solicitud",$id)->first();
        
        $estado_citado = Estados::find($general["estado_solicitante"]);
        $mun_citado    = Municipios::find($general["mun_solicitante"]);

        $estado_solicitante = Estados::find($general["estado_citado"]);
        $mun_solicitante    = Municipios::find($general["mun_citado"]);
        $conciliador        = User::find($general["conciliador_id"]);

        $citados           = SeerCitados_old::where("id_solicitud",$id)->get();
        $notificadores     = SeerCitados_old::where("id_solicitud",$id)
        ->join("users","users.id","=","seer_citados_old.id_notificador")
        ->select("users.name as notificador", "seer_citados_old.created_at", "seer_citados_old.nombre as citado","seer_citados_old.direccion","seer_citados_old.estatus")
        ->get();
        $audiencia          = SeerPerConciliador_old::where("id_solicitud",$id)->get();
        $registro  = User::find($general["user_id"]);

        return view('estadisticas.verPersonaAux', compact('userRole','general','auxiliar','estado_citado','mun_citado','estado_solicitante','mun_solicitante','conciliador','citados','audiencia','notificadores','registro'));
    }

    public function conciliador_persona(Request $request){
        $data = $request->all();
        
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');
        $cont = count($data["citado"]);

        //Validar documentacion
        request()->validate([
            'id'                    => 'required|numeric',
            'citado'                => 'required',
            'actividad_economica'   => 'required',
            'numero_audiencias'     => 'required',
            'estatus'               => 'required|in:Conciliacion,No conciliacion,Regenerada,Archivada',
            'monto'                 => 'required|numeric',
            'multa'                 => 'required|in:Si,No',
            'solicitud'             => 'required|in:Presencial,Linea',
        ], $data);


        if($data["estatus"] == "Conciliacion" || $data["estatus"] == "No conciliacion" || $data["estatus"] == "Archivada"){
            SeerPerGeneral_old::where('id', $data["id"])
            ->update(['NUE' => $data["NUE"], 'solicitante' => $data["solicitante"], 'estado_solicitante'  => $data["estado_solicitante"],
            'mun_solicitante' => $data["mun_solicitante"], 'validado_conciliador' => "Guardado"]);
        }

        SeerPerAuxiliar::where('id_solicitud', $data["id"])
        ->update(['actividad_economica' => $data["actividad_economica"], 'motivo' => $data["motivo"], 'notificacion' => $data["notificacion"]]);
        
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $data["numero_audiencia"],
            'numero_audiencias'     => $data["numero_audiencias"],
            'estatus_conciliacion'  => $data["estatus"],
            'monto'                 => $data["monto"],
            'rfc'                   => $data["rfc"],
            'NSS'                   => $data["NSS"],
            'multa'                 => $data["multa"],
            'tipo'                  => $data["solicitud"],
            'validado'              => 'Validado',
        ];
        if($data["multa"] != "Si"){
            $data_conciliador["monto_multa"] = $data["monto_multa"];
        }
        if($data["motivo_archivo"] != null || $data["motivo_archivo"] != ''){
            $data_conciliador["motivo_archivo"] = $data["motivo_archivo"];
        }
        if($data["fecha_reprogracion"] != null || $data["fecha_reprogracion"] != ''){
            $data_conciliador["fecha_reprogracion"] = $data["fecha_reprogracion"];
        }
        if($data["estatus"] == "Conciliacion" || $data["estatus"] == "No conciliacion"){
            $data_conciliador["fecha_conclucion"] = $fecha_actual;
        }
        
        SeerCitados_old::where('id_solicitud',$data["id"])->delete();

        for($i = 0; $i < $cont; $i++) {
            $data_citado = [
                'id_solicitud'  => $data["id"],
                'fecha'         => $fecha_actual,
                'nombre'        => $data["citado"][$i],
                'direccion'     => $data["direccion"][$i], 
                'id_municipio'  => 0, 
                'id_estado'     => 0,
                'observaciones' => ''
            ];
            SeerCitados_old::create($data_citado);
        }

        SeerPerConciliador_old::create($data_conciliador);  

        return redirect()->route('seer');
    }
  
    public function crear_audiencia($id){
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $userRole = $user->roles->pluck('name')->all();

        $general  = SeerPerGeneral_old::find($id);
        $auxiliar = SeerPerAuxiliar::where("id_solicitud",$id)->first();
        $audiencia = SeerPerConciliador_old::where("id_solicitud",$id)->get();

        $citados = SeerCitados_old::
        where("seer_citados_old.id_solicitud",$id)
        //->join("seer_general","seer_citados.id_solicitud", "=" , "seer_general.id")
        ->select('seer_citados_old.nombre as citado', 'seer_citados_old.direccion')
        //->groupBy("seer_citados.id")
        ->get();

        //Voy a mandar todos las variables
        $estados            = Estados::all();
        $municipios         = Municipios::all();
        $estado_solicitante = Estados::find($general["estado_solicitante"]);
        $mun_solicitante    = Municipios::find($general["mun_solicitante"]);
        $conciliador        = User::find($general["conciliador_id"]);
        

        return view('estadisticas.crearPersonaCon', compact('userRole','general','auxiliar','citados','mun_solicitante','estado_solicitante','conciliador','audiencia','estados','municipios'));
    }

    public function ver_conciliador($id){
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $userRole = $user->roles->pluck('name')->all();

        $general  = SeerPerGeneral::find($id);
        $auxiliar = SeerPerAuxiliar::where("id_solicitud",$id)->first();
        $audiencia = SeerPerConciliador::where("id_solicitud",$id)->get();

        $estado_citado = Estados::find($general["estado_solicitante"]);
        $mun_citado    = Municipios::find($general["mun_solicitante"]);

        $estado_solicitante = Estados::find($general["estado_citado"]);
        $mun_solicitante    = Municipios::find($general["mun_citado"]);

        $conciliador = SeerPerConciliador::where("id_solicitud",$id)->first();

        return view('estadisticas.verPersonaCon', compact('userRole','general','auxiliar','estado_citado','mun_citado','estado_solicitante','mun_solicitante','conciliador','audiencia'));
    }

    public function index_convenios(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        //solo le van aparecer solicitudes
        $convenios = SeerConvenios::where('fecha', $fecha_actual)->where('user_id', $id)->get();

        return view('estadisticas.index_convenios',compact('convenios','userRole'));
    }
    
    public function crear_convenio(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');
        
       
        //solo le van aparecer solicitudes
        $convenios = SeerConvenios::where('fecha', $fecha_actual)->where('user_id', $id)->get();

        return view('estadisticas.crear_convenio',compact('convenios','userRole'));
    }

    public function store_convenio(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');

        //Validar documentacion
        request()->validate([
            'fecha'         => 'required|date',
            'NUE'           => 'required|min:18|max:18',
            'monto'         => 'required|numeric',
            'tipo_pago'     => 'required',
        ], $data);
        $data['user_id'] = $id;

        SeerConvenios::create($data);  

        return redirect()->route('index_convenios');
    }

    public function index_colectivas(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');
        
       
        //solo le van aparecer solicitudes
        $convenios = SeerColectivas::where('fecha', $fecha_actual)->where('conciliador', $id)->get();

        return view('estadisticas.index_colectivas',compact('convenios','userRole'));
    }

    public function crear_colectiva(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');
        
       
        //solo le van aparecer solicitudes
        //$convenios = SeerConvenios::where('fecha', $fecha_actual)->where('conciliador', $id)->get();

        return view('estadisticas.crear_colectiva',compact('userRole'));
    }

    public function store_colectiva(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');

        //Validar documentacion
        request()->validate([
            'fecha'         => 'required|date',
            'NUE'           => 'required|min:18|max:18',
            'solicitante'   => 'required',
            'citado'        => 'required',
            'juzgado'       => 'required',
            'estado'        => 'required',
        ], $data);
        $data['conciliador'] = $id;

        SeerColectivas::create($data);  

        return redirect()->route('index_colectivas');
    }

    public function create_conciliador(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        $suma_solicitudes = SeerPerGeneral::
        join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
        ->where("seer_auxiliares.tipo_solicitud","Solicitud")
        ->where('fecha',"=", $fecha_actual)
        ->where('user_id',"=", $id)
        ->selectRaw('count(seer_general.id) as total')
        ->first();

        $suma_ratificaciones = SeerPerGeneral::
        join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
        ->where("seer_auxiliares.tipo_solicitud","Ratificación")
        ->where('fecha',"=", $fecha_actual)
        ->where('user_id',"=", $id)
        ->selectRaw('count(seer_general.id) as total')
        ->first();

        $total = SeerPerGeneral::
            join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
            ->where("seer_auxiliares.tipo_solicitud","Ratificación")
            ->where('fecha',"=", $fecha_actual)
            ->where('user_id',"=", $id)
            ->selectRaw('SUM(seer_auxiliares.monto) as monto')
            ->first();

        $suma_solicitudes_conciliador = SeerPerGeneral::
            join("seer_auxiliares","seer_auxiliares.id_solicitud", "=" , "seer_general.id")
            ->where('fecha',"=", $fecha_actual)
            ->where('conciliador_id',"=", $id)
            ->selectRaw('count(seer_general.id) as total')
            ->first();

        $total_audiencia = SeerPerGeneral::
            join("seer_conciliadores","seer_conciliadores.id_solicitud", "=" , "seer_general.id")
            ->where('fecha',"=", $fecha_actual)
            ->where('conciliador_id',"=", $id)
            ->selectRaw('SUM(seer_conciliadores.monto) as monto')
            ->first();

        return view('estadisticas.crearConsentradoCon', compact('user','userRole','suma_solicitudes','suma_ratificaciones','total','suma_solicitudes_conciliador','total_audiencia'));
    }

    public function ver_consentrado_con(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        $estadisticas  = null;

        return view('estadisticas.crearConcentradoConVer', compact('estadisticas','userRole'));
    }

    public function obtenerCitados($id){
        return SeerCitados::where('id_solicitud', $id)->get();
    }


    public function seer_estatus($id){
        $citado  = SeerCitados::find($id);
        $solicitud = SeerPerGeneral::where('id', $citado->id_solicitud)->first();
        $estado = Estados::where('id', $citado->estado_citado)->first();
        $municipio = Municipios::where('id', $citado->municipio_citado)->first();
        $nombre_estado = $estado->nombre;
        $nombre_municipio = $municipio->nombre;
        $NUE = $solicitud->NUE;
        $id = $citado->id;
        $municipios = Municipios::all();
        $estados = Estados::all();
        $fecha_formateada = \Carbon\Carbon::parse($citado->fecha)->format('Y-m-d');
        $hora_formateada  = \Carbon\Carbon::parse($citado->fecha)->format('H:i');
        $tipo_llenado = null;
        $flag = true;
        $estatusSeleccionado = null;

        return view('notificaciones.actualizarCitado', compact('id','municipios','estados', 'citado', 'NUE', 'nombre_estado', 'nombre_municipio', 'fecha_formateada', 'hora_formateada'));
    }

    /*public function update_notificador(Request $request){
        $data = $request->all();
        $data['problema_diligencia'] = $request->input('problema_diligencia');
        $fechaEspecifica = Carbon::parse($request->fecha_notificacion . ' ' . $request->hora_notificacion);

        $id_notificador = auth()->user()->id;

        // 1. Buscamos el registro ACTUAL en la base de datos primero
        $seercitado = SeerCitados::find($data["id"]);

        // 2. Si ya existen documentos en la BD, los conservamos como valor por defecto. Si no, ponemos "Sin documento"
        $documento = $seercitado->documento ?? "Sin documento";
        $documento1 = $seercitado->documento1 ?? "Sin documento";
        $documento2 = $seercitado->documento2 ?? "Sin documento";
        //Obtine cual fue el tipo de problema para despues guardarlo en bdd
        /*$data['problema_diligencia'] = null;
        if(isset($data['problema_diligencia_1'])){
            $data['problema_diligencia']= $data['problema_diligencia_1'];
        }
        elseif(isset($data['problema_diligencia_2'])){
            $data['problema_diligencia'] =$data['problema_diligencia_2'];
        }*/
        // 3. SOLO si el usuario subió un archivo NUEVO en este request, lo guardamos y actualizamos la variable
    /*    if ($request->hasFile('foto')) {
            $documento = $data["id"] . "-foto1.jpg";
            Storage::putFileAs('documentos_notificacion', $request->file('foto'), $documento);
        }
        
        if ($request->hasFile('foto1')) {
            $documento1 = $data["id"] . "-foto2.jpg";
            Storage::putFileAs('documentos_notificacion', $request->file('foto1'), $documento1);
        }
        
        if ($request->hasFile('foto2')) {
            $documento2 = $data["id"] . "-foto3.jpg";
            Storage::putFileAs('documentos_notificacion', $request->file('foto2'), $documento2);
        }

        // Validación de campos (todos opcionales)
        $request->validate([
            'quien_atiende'               => 'nullable',
            'medio'                       => 'nullable',
            'vialidad_notificacion'       => 'nullable',
            'abundar_area'                => 'nullable',
            'abundar_inmueble'            => 'nullable',
            'nombre_notificacion'         => 'nullable',
            'relacion_notificacion'       => 'nullable',
            'puesto'                      => 'nullable',
            'identificacion_notificacion' => 'nullable',
            'motivo_identificacion'       => 'nullable',
            'firma'                       => 'nullable',
            'problema_diligencia'         => 'nullable',
            'genero'                      => 'nullable',
            'tez'                         => 'nullable',
            'edad_filiacion'              => 'nullable',
            'altura'                      => 'nullable',
            'complexion'                  => 'nullable',
            'cabello'                     => 'nullable',
            'ojos'                        => 'nullable',
            'particulares'                => 'nullable',
            'especificar'                 => 'nullable',
            'num_identificacion'          => 'nullable',
        ]);

        $seercitado = SeerCitados::find($data["id"]);
        $estatusOriginal = $seercitado ? $seercitado->estatus : null;
        $solicitud = SeerPerGeneral::where('id', $seercitado->id_solicitud)->first();
        $municipio = Municipios::where('id', $seercitado->municipio_citado)->first();
        $estado = Estados::where('id', $seercitado->estado_citado)->first();
        $nombre_estado = $estado->nombre;
        $nombre_municipio = $municipio->nombre;
        $NUE = $solicitud->NUE;
        $municipios = Municipios::all();
        $estados = Estados::all();
        $notificacion = SeerCitados::where('id_solicitud', $seercitado->id_solicitud)->pluck('notificacion')->first();

        $hora_notificacion = $data["hora_notificacion"];
        $tipo_llenado = $data["tipo_llenado"];
        if(!isset($data["observaciones"])){
            $data["observaciones"] = null;
        }
        $esVistaPrevia = isset($data['vista_previa']) && (string) $data['vista_previa'] === '1';

        if(!$esVistaPrevia && $data["quien_atiende"] == 'EXHORTO'){
            $data["estatus"] = 'Exhorto';
            $notificacion = 'Exhorto';
            $data["medio"]= null;
            if($nombre_municipio == 'Morelia' || $nombre_municipio == 'Zitacuaro'){
                $id_notificador = 57;
            }
            else if($nombre_municipio == 'Uruapan' || $nombre_municipio == 'Lázaro Cárdenas'){
                $id_notificador = 58;

            }
            else if($nombre_municipio == 'Zamora' || $nombre_municipio == 'Sahuayo'){
                $id_notificador = 59;
            }
        }

        $estatusSeleccionado = $data['estatus'];

        if ($esVistaPrevia && $estatusOriginal !== null) {
            $data['estatus'] = $estatusOriginal;
        }

        // Tipo de llenado 1: actualizar un solo registro
        if ($data["tipo_llenado"] == 1) {
            SeerCitados::find($data["id"])
                ->update([
                    'estatus'                    => $data["estatus"],
                    'observaciones'              => $data["observaciones"],
                    'documento'                  => $documento,
                    'documento1'                 => $documento1,
                    'documento2'                 => $documento2,
                    'fecha'                      => $fechaEspecifica,
                    'quien_atiende'              => $data["quien_atiende"],
                    'medio'                      => $data["medio"],
                    'vialidad_notificacion'      => $data["vialidad_notificacion"],
                    'abundar_area'               => $data["abundar_area"],
                    'abundar_inmueble'           => $data["abundar_inmueble"],
                    'nombre_notificacion'        => $data["nombre_notificacion"],
                    'relacion_notificacion'      => $data["relacion_notificacion"],
                    'puesto'                     => $data["puesto"],
                    'identificacion_notificacion'=> $data["identificacion_notificacion"],
                    'motivo_identificacion'      => $data["motivo_identificacion"],
                    'firma'                       => $data["firma"],
                    'problema_diligencia'         => $data["problema_diligencia"] ?? null,
                    'genero'                      => $data["genero"],
                    'tez'                         => $data["tez"],
                    'edad_filiacion'              => $data["edad_filiacion"],
                    'altura'                      => $data["altura"],
                    'complexion'                  => $data["complexion"],
                    'cabello'                     => $data["cabello"],
                    'ojos'                        => $data["ojos"],
                    'particulares'                => $data["particulares"],
                    'especificar'                 => $data["especificar"] ?? null,
                    'giro_comercial'              => $data["giro_comercial"],
                    'id_notificador'              => $id_notificador,
                    'notificacion'                => $notificacion,
                    'num_identificacion'          => $data["num_identificacion"],
                ]);

        } else {
            // Tipo de llenado 2: actualizar varios registros de la misma solicitud
            $solicitud = SeerCitados::find($data["id"]);
            //$citados = SeerCitados::where('id_solicitud', $solicitud["id_solicitud"])->get();
            
            $citados = SeerCitados::where('id_solicitud', $solicitud["id_solicitud"])->where('id_notificador', $id_notificador)->where('estatus', 'pendiente')->get();

            foreach ($citados as $citado) {
                SeerCitados::find($citado["id"])
                    ->update([
                        'estatus'                    => $data["estatus"],
                        'observaciones'              => $data["observaciones"],
                        'documento'                  => $documento,
                        'documento1'                 => $documento1,
                        'documento2'                 => $documento2,
                        'fecha'                      => $fechaEspecifica,
                        'quien_atiende'              => $data["quien_atiende"],
                        'medio'                      => $data["medio"],
                        'vialidad_notificacion'      => $data["vialidad_notificacion"],
                        'abundar_area'               => $data["abundar_area"],
                        'abundar_inmueble'           => $data["abundar_inmueble"],
                        'nombre_notificacion'        => $data["nombre_notificacion"],
                        'relacion_notificacion'      => $data["relacion_notificacion"],
                        'puesto'                     => $data["puesto"],
                        'identificacion_notificacion'=> $data["identificacion_notificacion"],
                        'motivo_identificacion'      => $data["motivo_identificacion"],
                        'firma'                       => $data["firma"],
                        'problema_diligencia'         => $data["problema_diligencia"] ?? null,
                        'genero'                      => $data["genero"],
                        'tez'                         => $data["tez"],
                        'edad_filiacion'              => $data["edad_filiacion"],
                        'altura'                      => $data["altura"],
                        'complexion'                  => $data["complexion"],
                        'cabello'                     => $data["cabello"],
                        'ojos'                        => $data["ojos"],
                        'particulares'                => $data["particulares"],
                        'especificar'                 => $data["especificar"] ?? null,
                        'giro_comercial'              => $data["giro_comercial"],
                        'id_notificador'              => $id_notificador,
                        'notificacion'                => $notificacion,
                        //'updated_at'                  => DB::raw("'" . $fechaEspecifica->format('Y-m-d H:i:s') . "'") //da un objeto "expression"
                        'num_identificacion'          => $data["num_identificacion"],
                    ]);
                    
            }
        }
        $registro = SeerCitados::find($data["id"]);
        
        $fecha_formateada = \Carbon\Carbon::parse($registro->fecha)->format('Y-m-d');
        $hora_formateada  = \Carbon\Carbon::parse($registro->fecha)->format('H:i');

        if($data["vista_previa"] == '0'){
            // Redirigir al listado
            return redirect()->route('seer');
        }
        else{
            $url_pdf = null;
            $estatus = $estatusSeleccionado;
            // Evaluamos el estatus y el tipo de notificación exactamente igual que en tu index
            //$estatus = $registro->estatus;
            $tipo = $registro->tipo_notificacion; // Suponiendo que este campo está en tu tabla

            if ($estatus === "Finalizado exitosamente") {
                if ($tipo === "Citatorio") {
                    $url_pdf = route('PDFRazonNoticacion', [$registro->id, $registro->id_solicitud]);
                } elseif ($tipo === "Multa") {
                    $url_pdf = route('PDFmulta', [$registro->id, $registro->id_solicitud]);
                }
            } 
            elseif ($estatus === "No notificada") {
                if ($tipo === "Citatorio") {
                    $url_pdf = route('PDFInstructivo', [$registro->id, $registro->id_solicitud]);
                } elseif ($tipo === "Multa") {
                    $url_pdf = route('PDFmulta', [$registro->id, $registro->id_solicitud]);
                }
            } 
            elseif ($estatus === "No exitosa se constituye") {
                if ($tipo === "Citatorio") {
                    $url_pdf = route('VerPDFNoExitConstituye', [$registro->id, $registro->id_solicitud]);
                } elseif ($tipo === "Multa") {
                    $url_pdf = route('PDFmulta', [$registro->id, $registro->id_solicitud]);
                }
            } 
            elseif ($estatus === "No exitosa no se constituye") {
                $url_pdf = route('PDFNoExitosaInt', [$registro->id, $registro->id_solicitud]);
            }

            // Retornamos la vista pasando la variable url_pdf
            return view('notificaciones.vista_previa', compact('registro','municipios','estados', 'NUE', 'nombre_estado', 'nombre_municipio', 'url_pdf', 'hora_formateada','fecha_formateada', 'tipo_llenado', 'estatusSeleccionado'));
            
        }
          
    }*/
    public function update_notificador(Request $request)
    {
        $data = $request->all();
        $id_notificador_actual = auth()->id();
        $fechaEspecifica = Carbon::parse($request->fecha_notificacion . ' ' . $request->hora_notificacion);
        
        // 1. Carga inicial optimizada
    // $seercitado = SeerCitados::with(['solicitud', 'municipio', 'estado'])->findOrFail($data["id"]);
        $seercitado = SeerCitados::find($data["id"]);
        // 2. Manejo de archivos (se quedan los actuales o se suben nuevos)
        $documentos = [
            'doc'  => $request->hasFile('foto') ? $data["id"] . "-foto1.jpg" : ($seercitado->documento ?? "Sin documento"),
            'doc1' => $request->hasFile('foto1') ? $data["id"] . "-foto2.jpg" : ($seercitado->documento1 ?? "Sin documento"),
            'doc2' => $request->hasFile('foto2') ? $data["id"] . "-foto3.jpg" : ($seercitado->documento2 ?? "Sin documento")
        ];

        if ($request->hasFile('foto')) Storage::putFileAs('documentos_notificacion', $request->file('foto'), $documentos['doc']);
        if ($request->hasFile('foto1')) Storage::putFileAs('documentos_notificacion', $request->file('foto1'), $documentos['doc1']);
        if ($request->hasFile('foto2')) Storage::putFileAs('documentos_notificacion', $request->file('foto2'), $documentos['doc2']);

        // 3. Lógica de Estatus y Exhorto
        $esVistaPrevia = isset($data['vista_previa']) && (string)$data['vista_previa'] === '1';
        $nuevo_estatus = $data['estatus'];
        $notificacion_valor = $seercitado->notificacion;
        $id_notificador_final = $id_notificador_actual;

        // Si NO es vista previa y es EXHORTO, aplicamos reglas de reasignación
        if (!$esVistaPrevia && $data["quien_atiende"] === 'EXHORTO') {
            $nuevo_estatus = 'Exhorto';
            $notificacion_valor = 'Exhorto';
            $nombre_mun = $seercitado->municipio->nombre ?? '';

            $id_notificador_final = match ($nombre_mun) {
                'Morelia', 'Zitacuaro' => 57,
                'Uruapan', 'Lázaro Cárdenas' => 58,
                'Zamora', 'Sahuayo' => 59,
                default => $id_notificador_actual,
            };
        }

        // 4. Array de actualización
        $updatePayload = [
            'estatus'                     => $esVistaPrevia ? ($seercitado->estatus ?? $nuevo_estatus) : $nuevo_estatus,
            'observaciones'               => $data["observaciones"] ?? null,
            'documento'                   => $documentos['doc'],
            'documento1'                  => $documentos['doc1'],
            'documento2'                  => $documentos['doc2'],
            'fecha'                       => $fechaEspecifica,
            'quien_atiende'               => $data["quien_atiende"],
            'medio'                       => ($data["quien_atiende"] === 'EXHORTO') ? null : ($data["medio"] ?? null),
            'vialidad_notificacion'       => $data["vialidad_notificacion"] ?? null,
            'abundar_area'                => $data["abundar_area"] ?? null,
            'abundar_inmueble'            => $data["abundar_inmueble"] ?? null,
            'nombre_notificacion'         => $data["nombre_notificacion"] ?? null,
            'relacion_notificacion'       => $data["relacion_notificacion"] ?? null,
            'puesto'                      => $data["puesto"] ?? null,
            'identificacion_notificacion' => $data["identificacion_notificacion"] ?? null,
            'motivo_identificacion'       => $data["motivo_identificacion"] ?? null,
            'firma'                       => $data["firma"] ?? null,
            'problema_diligencia'         => $data["problema_diligencia"] ?? null,
            'genero'                      => $data["genero"] ?? null,
            'tez'                         => $data["tez"] ?? null,
            'edad_filiacion'              => $data["edad_filiacion"] ?? null,
            'altura'                      => $data["altura"] ?? null,
            'complexion'                  => $data["complexion"] ?? null,
            'cabello'                     => $data["cabello"] ?? null,
            'ojos'                        => $data["ojos"] ?? null,
            'particulares'                => $data["particulares"] ?? null,
            'especificar'                 => $data["especificar"] ?? null,
            'giro_comercial'              => $data["giro_comercial"] ?? null,
            'id_notificador'              => $id_notificador_final,
            'notificacion'                => $notificacion_valor,
            'num_identificacion'          => $data["num_identificacion"] ?? null,
        ];

        // 5. Ejecutar actualización masiva o individual
        if ($data["tipo_llenado"] == 1) {
            $seercitado->update($updatePayload);
        } else {
            SeerCitados::where('id_solicitud', $seercitado->id_solicitud)
                ->where('id_notificador', $id_notificador_actual)
                ->where('estatus', 'pendiente')
                ->update($updatePayload);
        }

        // 6. CONTROL DE FLUJO (Vista Previa vs Finalizar)
        if (!$esVistaPrevia) {
            // Caso vista_previa == '0': Redirigir al listado
            return redirect()->route('seer')->with('success', 'Registro actualizado correctamente.');
        }

        // Caso vista_previa == '1': Preparar datos para la vista
        // Volvemos a refrescar el modelo para tener los datos guardados
        $seercitado->refresh(); 

        return view('notificaciones.vista_previa', [
            'registro'           => $seercitado,
            'municipios'         => Municipios::all(), 
            'estados'            => Estados::all(),    
            'NUE'                => $seercitado->solicitud->NUE ?? '',
            'nombre_estado'      => $seercitado->estado->nombre ?? '',
            'nombre_municipio'   => $seercitado->municipio->nombre ?? '',
            'url_pdf'            => $this->obtenerRutaPdf($seercitado, $nuevo_estatus),
            'hora_formateada'    => $fechaEspecifica->format('H:i'),
            'fecha_formateada'   => $fechaEspecifica->format('Y-m-d'),
            'tipo_llenado'       => $data["tipo_llenado"],
            'estatusSeleccionado'=> $nuevo_estatus
        ]);
    }
    private function obtenerRutaPdf($registro, $estatus)
    {
        $tipo = $registro->tipo_notificacion;
        
        return match ($estatus) {
            "Finalizado exitosamente" => match ($tipo) {
                "Citatorio" => route('PDFRazonNoticacion', [$registro->id, $registro->id_solicitud]),
                "Multa"     => route('PDFmultaNotificacion', [$registro->id, $registro->id_solicitud]),
                default     => null
            },
            "No notificada" => match ($tipo) {
                "Citatorio" => route('PDFInstructivo', [$registro->id, $registro->id_solicitud]),
                "Multa"     => route('VerPDFMultaInstructivo', [$registro->id, $registro->id_solicitud]),
                default     => null
            },
            "No exitosa se constituye" => match ($tipo) {
                "Citatorio" => route('VerPDFNoExitConstituye', [$registro->id, $registro->id_solicitud]),
                "Multa"     => route('VerPDFMultaNoExitConstituye', [$registro->id, $registro->id_solicitud]),
                default     => null
            },
            "No exitosa no se constituye" => route('PDFnotificadoNoexitosaNS', [$registro->id, $registro->id_solicitud]),
            default => null
        };
    }
    public function store_enlace(Request $request, $id_citado){
        $data = $request->all();
        $registrosActualizados = SeerCitados::where('id', $id_citado)->where('id_solicitud', $data["id"])
        ->update(['id_notificador' => $data["notificador"], 'estatus' => "Pendiente"]);

        return redirect()->route('notificaciones');
    }

    public function create_asesoria(){
        return view('estadisticas.crearAsesorias');
    }

    public function store_asesorias(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        //Validar documentacion
        request()->validate([
            'nombre' => 'required',
            'sexo'   => 'required',
        ], $data);

        $data['id_usuario'] = $user["id"];
        $data['fecha'] = date('Y-m-d');
        $data['delegacion'] = $user["delegacion"];

        SeerAsesoria::create($data);  
        return redirect()->route('seer');
    }
    
    public function destroy($id)
    {
        //Borrar de la tabla Seer Auxiliares
        SeerPerAuxiliar::where('id_solicitud',$id)->delete();
        //Borrar de la tabla Seer Auxiliares
        SeerCitados_old::where('id_solicitud',$id)->delete();
        //Borrar de la tabla Seer General
        SeerPerGeneral_old::find($id)->delete();
       
        return redirect()->route('seer');
    }

    public function editar_persona($id){
        $id_usurario = auth()->user()->id;
        $user = User::find($id_usurario);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $relacionEloquent = "roles";
        
        $general    = SeerPerGeneral_old::find($id);
        $auxiliar   = SeerPerAuxiliar::where("id_solicitud",$id)->first();
        $estados    = Estados::all();
        $municipios = Municipios::all();
        $citados    = SeerCitados_old::where("id_solicitud",$id)->get();
        $conciliador= User::find($general["conciliador_id"]);
        $conciliadores = User::whereHas($relacionEloquent, function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();
        
        return view('estadisticas.editar_auxiliar', compact('userRole','general','auxiliar','municipios','conciliador','estados','conciliadores','citados'));  
    }

    public function update_auxiliar(Request $request){
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $fecha_actual = date('y-m-d');
        $cont = count($data["citado"]);

        //Validar documentacion
        request()->validate([
            //General
            'NUE'                   => 'required|min:18|max:18',
            'solicitante'           => 'required',
            'estado_solicitante'    => 'required|numeric',
            'mun_solicitante'       => 'required|numeric',
            'actividad_economica'   => 'required',
            'conciliador_id'        => 'required|numeric',

            //Auxiliares
            'sexo'                  => 'required|in:H,M',
            'motivo'                => 'required|in:Despido,Pago de prestaciones,Recision de la relación laboral,Derecho de preferencia,Derecho de antiguedad,Derecho de ascesnso,Terminación voluntaria de relación laboral',
            'notificacion'          => 'required|in:Trabajador,Centro,Ambos',

        ], $data);

        

        SeerPerGeneral::where('id', $data["id"])
        ->update(['fecha_confirmacion'   => $data["fecha_confirmacion"], 'NUE' => $data["NUE"], 'solicitante' => $data["solicitante"], 'estado_solicitante'  => $data["estado_solicitante"],
        'mun_solicitante' => $data["mun_solicitante"], 'user_id' => $id_usuario, 'conciliador_id' => $data["conciliador_id"]]);
        
        SeerPerAuxiliar::where('id_solicitud', $data["id"])
        ->update(['sexo' => $data["sexo"], 'actividad_economica' => $data["actividad_economica"], 'motivo' => $data["motivo"], 'notificacion' => $data["notificacion"]]);

        SeerCitados::where('id_solicitud',$data["id"])->delete();

        for($i = 0; $i < $cont; $i++) {
            $data_citado = [
                'id_solicitud'  => $data["id"],
                'fecha'         => $fecha_actual,
                'nombre'        => $data["citado"][$i],
                'direccion'     => $data["direccion"][$i], 
                'id_municipio'  => 0, 
                'id_estado'     => 0,
                'observaciones' => '',
            ];
            SeerCitados::create($data_citado);
        }
        return redirect()->route('seer'); 
    }

    public function ver_historial(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();

        return view('estadisticas.generaHistorial',compact('userRole'));
    }

    public function historial(Request $request){
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date|after_or_equal:fecha_inicio',
        ]);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_final');
        $personas = SeerPerGeneral::join('seer_auxiliares', 'seer_auxiliares.id_solicitud', '=', 'seer_general.id')
            ->join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
            
            ->leftjoin('seer_conciliadores', 'seer_conciliadores.id_solicitud', '=', 'seer_general.id')
                
            ->select(
                'seer_auxiliares.motivo',
                'seer_auxiliares.estatus',
                'seer_auxiliares.actividad_economica',
                'seer_general.fecha',
                'seer_general.NUE',
                'seer_general.solicitante',
                'seer_citados.nombre as citado',
                'seer_citados.id_solicitud',
                'seer_citados.direccion',
                'seer_conciliadores.id',
            )
            ->whereBetween('seer_general.fecha', [$fechaInicio, $fechaFin])
            ->get();
        return view('estadisticas.verHistorial', compact('personas'));    

    } 

    public function solicitudesLinea(){
        $ahora = Carbon::now();
        $hora_inicial = '09:00:00';
        $hora_final = '16:00:00';
        /*
        if($ahora){
            return view('solicitudes.solicitud_no_disponible', [
                'motivo' => 'Fuera de Servicio.',
            ]);
        }
        */
        //Cerrar si es fin de semana
        if ($ahora->isWeekend()) {
            return view('solicitudes.solicitud_no_disponible', [
                'motivo' => 'En mantenimiento.',
            ]);
        }

        //Cerrar si hoy cae dentro de un rango de días inhábiles
        $fechaHoy = $ahora->format('Y-m-d');
        $esInhabil = DiasInhabiles::whereNull('user_id')
            ->where('horario_inicio', $hora_inicial)
            ->where('horario_final', $hora_final)
            ->whereDate('fecha_inicio', '<=', $fechaHoy)
            ->whereDate('fecha_final', '>=', $fechaHoy)
            ->exists();

        if ($esInhabil) {
            return view('solicitudes.solicitud_no_disponible', [
                'motivo' => 'Día inhábil.',
            ]);
        }
        
        return view('solicitud');
    }

    public function Industrias($tipo_solicitud){
        return view('solicitudes.tipoIndustria', compact('tipo_solicitud'));
    } 
    
    public function solicitudesLineaCentro(){
        return view('solicitudCentro');
    }

    public function IndustriasCentro($tipo_solicitud){
        return view('solicitudes.tipoIndustriaCentro', compact('tipo_solicitud'));
    } 

    //Pre registro para solicitudes
    public function RTemportal(){
        return view('solicitudes.solicitud_trabajador');
    }

    public function GuardarRTemportal(Request $request){
        $data = $request->all();
        $request->validate([
            'nombre'      => 'required',
            'rfc'         => 'required', 
            'telefono'    => 'required'
        ]);
        
        $data_insert=array(
            'nombre'         =>  $data["nombre"],
            'rfc'            =>  $data["rfc"],
            'telefono'       =>  $data["telefono"]
        );
       
        PreRegistro::create($data_insert); 
        
        //return redirect()->away('https://michoacan.cencolab.mx/solicitudes/create?solicitud=2');
    }
    //Fin registro para solicitudes
    
    //Solicitud en línea trabajador
    public function trabajador($tipo_solicitud){  
        if ($tipo_solicitud == "1") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1') ->get();
        }
        elseif ($tipo_solicitud == "2") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '2') ->get();
        }
        elseif ($tipo_solicitud == "3") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '3') ->get();
        }
        elseif ($tipo_solicitud == "4") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '4') ->get();
        }
        $ramas = SolicitudRama::all();
       // $actividad=SolicitudEconomica::all();
        $del=Sedes::all();
        $municipios=Municipios::where('estado',16)->get();
       /* if($tipo_solicitud[0] == "1"){
            //$personas = null;
            $motivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1')
            ->select('catalogo_motivos.motivo','seer_general.NUE','seer_general.solicitante','seer_citados.nombre','seer_citados.direccion','seer_citados.estatus')
            ->get();
        }*/
        return view('solicitudes.solicitud_trabajador', compact('ramas','del','municipios','tipo_solicitud','mostrarMotivos'));
    }

    //Solicitud en línea para los Centros de Conciliación
    public function trabajadorCentro($tipo_solicitud){
          
        if ($tipo_solicitud == "1") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1') ->get();
        }
        elseif ($tipo_solicitud == "2") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '2') ->get();
        }
        elseif ($tipo_solicitud == "3") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '3') ->get();
        }
        elseif ($tipo_solicitud == "4") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '4') ->get();
        }
        $ramas = SolicitudRama::all();
       // $actividad=SolicitudEconomica::all();
        $del=Sedes::all();
        $municipios=Municipios::where('estado',16)->get();
       /* if($tipo_solicitud[0] == "1"){
            //$personas = null;
            $motivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1')
            ->select('catalogo_motivos.motivo','seer_general.NUE','seer_general.solicitante','seer_citados.nombre','seer_citados.direccion','seer_citados.estatus')
            ->get();
        }*/
        return view('solicitudes.solicitud_trabajadorCentro', compact('ramas','del','municipios','tipo_solicitud','mostrarMotivos'));
    }
   
    /* public function obtenerActEconomica($id){
        return SolicitudEconomica::where('id_rama', $id)->get();
    }*/

    public function solicitud_parte1(Request $request){
        $data = $request->all();
        /*
        if($data["delegacion"] == "Lázaro Cárdenas"){
            $data["delegacion"] = "Uruapan";
        }
        if($data["delegacion"] == "Zitácuaro"){
            $data["delegacion"] = "Morelia";
        }
        if($data["delegacion"] == "Sahuayo"){
            $data["delegacion"] = "Zamora";
        }
        */
        //validando información
        
        $request->validate([
            'ramaIndustrial'      => 'required',
            'actividad_economica' => 'required',
            'motivo_solicitud'    => 'required',

        ]);
        
        $año_actual = date('Y');
        $numero_consecutivo = 0;
        $consecutivo  = SeerPerGeneral::latest('consecutivo')
        ->where('delegacion',$data["delegacion"])
        ->where('año',$año_actual)->
        first();

        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        $data_insert=array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => 0,
            'consecutivo'    => $numero_consecutivo,    
            'año'            => $año_actual,
        );
       
        /*SeerPerGeneral::create($data_insert); 
        $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $id_general["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }*/

        // Guardar en sesión
        $solicitud_data = array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => 0,
            'consecutivo'     => $numero_consecutivo,
            'año'             => $año_actual,
            'motivo_solicitud' => $data["motivo_solicitud"] ?? []
        );
       
        session(['solicitud_data' => $solicitud_data]);
        
        // Limpiar sesiones anteriores
        session()->forget('solicitante_data');
        session()->forget('citados_data');

        $id = 'session';

        $estados = Estados::all();
        $municipios = Municipios::all();

        /*if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }*/
        return view('solicitudes.solicitante', compact('estados','municipios','id'));
        //return redirect()->route('parte2.ver', ['id' => $id]);
    }

    public function solicitud_parte1Centro(Request $request){
        $data = $request->all();
        /*
        if($data["delegacion"] == "Lázaro Cárdenas"){
            $data["delegacion"] = "Uruapan";
        }
        if($data["delegacion"] == "Zitácuaro"){
            $data["delegacion"] = "Morelia";
        }
        if($data["delegacion"] == "Sahuayo"){
            $data["delegacion"] = "Zamora";
        }
        */
        //validando información
        
        $request->validate([
            'ramaIndustrial'      => 'required',
            'actividad_economica' => 'required',
            'motivo_solicitud'    => 'required',

        ]);
        
        $año_actual = date('Y');
        $numero_consecutivo = 0;
        $consecutivo  = SeerPerGeneral::latest('consecutivo')
        ->where('delegacion',$data["delegacion"])
        ->where('año',$año_actual)->
        first();

        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        $data_insert=array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() :0,
            'consecutivo'    => $numero_consecutivo,    
            'año'            => $año_actual,
        );
       
        /*SeerPerGeneral::create($data_insert); 
        $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $id_general["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }*/

        // Guardar en sesión
        $solicitud_data = array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() : 0,
            'consecutivo'     => $numero_consecutivo,
            'año'             => $año_actual,
            'motivo_solicitud' => $data["motivo_solicitud"] ?? []
        );
       
        session(['solicitud_trabajador_data' => $solicitud_data]);
        
        // Limpiar sesiones anteriores
        session()->forget('solicitante_trabajador_data');
        session()->forget('citados_trabajador_data');

        $id = 'session';

        $estados = Estados::all();
        $municipios = Municipios::all();

        /*if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }*/
        return view('solicitudes.solicitanteCentro', compact('estados','municipios','id'));
        //return redirect()->route('parte2.ver', ['id' => $id]);
    }

    public function vista_parte2Centro(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return redirect()->route('publico');
        }

        if ($id == 'session') {
            $solicitud_data = session('solicitud_trabajador_data');
            if (!$solicitud_data) {
                return redirect()->route('publico')->with('error', 'Sesión expirada.');
            }
            $tipo_generacion = $solicitud_data['tipo_generacion'];
        } else {
            $solicitud = SeerPerGeneral::find($id);

            if (!$solicitud) {
                return redirect()->route('publico')->with('error', 'La solicitud no existe.');
            }
            $tipo_generacion = $solicitud->tipo_generacion;
        }

        $estados = Estados::all();
        $municipios = Municipios::all();

        if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }
        
        return view('solicitudes.solicitanteCentro', compact('estados','municipios','id'));
    }

    public function vista_parte2(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return redirect()->route('publico');
        }

        if ($id == 'session') {
            $solicitud_data = session('solicitud_trabajador_data');
            if (!$solicitud_data) {
                return redirect()->route('publico')->with('error', 'Sesión expirada.');
            }
            $tipo_generacion = $solicitud_data['tipo_generacion'];
        } else {
            $solicitud = SeerPerGeneral::find($id);

            if (!$solicitud) {
                return redirect()->route('publico')->with('error', 'La solicitud no existe.');
            }
            $tipo_generacion = $solicitud->tipo_generacion;
        }

        $estados = Estados::all();
        $municipios = Municipios::all();

        if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }
        
        return view('solicitudes.solicitante', compact('estados','municipios','id'));
    }

    public function solicitud_parte2Centro(Request $request){
        $data = $request->all();
        $id = $data['id'];

        //validando información
       /*$request->validate([
            //'tipo'                      => 'required|in:Fisica,Moral',
            'curp'                      => 'required|min:18|max:18',
            'nombre'                    => 'required',
            'fecha_nacimiento'          => 'required|date',
            'edad'                      => 'required|numeric',
            'genero'                    => 'required|in:H,M,NC',
            'nacionalidad'              => 'required|in:Mexicana,Otra',
            'estado_nacimiento'         => 'required',
            'telefono1'                 => 'required|min:10|max:10',
            'correo'                    => 'required',
            'estado_solicitante'        => 'required',
            'vialidad'                  => 'required',
            'vialidad_calle'            => 'required',
            'numExt'                    => 'required',
            'colonia_solicitante'       => 'required',
            'municipio_solicitante'     => 'required',
            'cp'                        => 'required|numeric',
            //'referencias'               => 'required|string|max:300',
            //'calle1'                    => 'required',
            //'calle2'                    => 'required',
            'puesto'                    => 'required', 
            'periodo_pago'              => 'required',
            'pago'                      => 'required',
            'horas'                     => 'required',
            'fecha_ingreso'             => 'required',
            'jornada'                   => 'required',
            'identificacion'            => 'required',
            //'documentoCurp'             => 'required',
            'documentoIdentificacion'   => 'required',
            'num_identificacion'        => 'required',
            'descripcionSolicitud'      => 'required',
            'excepcion'                 => 'required',
            'frecuencia_hechos' => 'required_if:excepcion,Si',
            'cambios_situacionL' => 'required_if:excepcion,Si',
            'comunico_hechos' => 'required_if:excepcion,Si',
            'descripcion_conducta' => 'required_if:excepcion,Si',
            'responsable_cargo' => 'required_if:excepcion,Si',
            'actos_cometidos' => 'required_if:excepcion,Si',
            'momento_hechos' => 'required_if:excepcion,Si',
            'lugar_hechos' => 'required_if:excepcion,Si',
            'constancia_hechos' => 'required_if:excepcion,Si',
            'solicito_apoyo' => 'required_if:excepcion,Si',
            'continuacion_solicto_apoyo' => 'required_if:excepcion,Si',
            'incidencia_directa' => 'required_if:solicito_apoyo,Si',
            'recibio_atencion' => 'required_if:excepcion,Si',
        ]);*/
        
        $data_insert=array(
            'id_solicitud'         => $data["id"],
            /*'tipo_persona'         => $data["tipo"],*/
            'curp'                 => $data["curp"],
            'nombre'               => $data["nombre"],
            'fecha_nacimiento'     => $data["fecha_nacimiento"],
            'sexo'                 => $data["genero"],
            'nacionalidad'         => $data["nacionalidad"],
            'estado'               => $data["estado_nacimiento"],
            'edad'                 => $data["edad"],
            'telefono1'            => $data["telefono1"],
            'email'                => $data["correo"],
            'estado_domicilio'     => $data["estado_solicitante"],
            'tipo_vialidad'        => $data["vialidad"],
            'calle'                => $data["vialidad_calle"],
            'num_ext'              => $data["numExt"],
            'colonia'              => $data["colonia_solicitante"],
            'municipio_domicilio'  => $data["municipio_solicitante"],
            'codigo_postal'        => $data["cp"],
            /*'referencia'           => $data["referencias"],
            'calle2'               => $data["calle1"],
            'calle3'               => $data["calle2"],*/
            'puesto'               => $data["puesto"],
            'pago'                 => $data["pago"],
            'periodo_pago'         => $data["periodo_pago"],
            'horas_semana'         => $data["horas"],
            'fecha_ingreso'        => $data["fecha_ingreso"],
            'jornada'              => $data["jornada"],
            /*'identificacion'       => $data["identificacion"],
            'num_identificacion'   => $data["num_identificacion"],*/
            'descripcionSolicitud' => $data["descripcionSolicitud"],
        ); 

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                if (is_array($data["lenguaje"])) {
                    $data_insert["lenguaje"] = $data["lenguaje"][0] ?? null;
                } else {
                    $data_insert["lenguaje"] = $data["lenguaje"] ?? null;
                }
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["numInt"])){
            $data_insert["num_int"] =  $data["numInt"];
        }
        if(isset($data["discapacidad"])){
            $data_insert["discapacidad"] =  "Si";
            $data_insert["tipo_discapacidad"] =  $data["tipo_discapacidad"];
        }
        if(isset($data["labora"])){
            $data_insert["labora"] =  "Si";
            //$data_insert["fecha_salida"]  =  $data["fecha_salida"];
        }
        if(isset($data["telefono2"])){
            $data_insert["telefono2"] =  $data["telefono2"];
        }
        if(isset($data["seguro"])){
            $data_insert["nss"] =  $data["seguro"];
        }
        if(isset($data["fecha_salida"])){
            $data_insert["fecha_salida"] =  $data["fecha_salida"];
        }
        if(isset($data["referencias"])){
            $data_insert["referencia"] =  $data["referencias"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle2"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle3"] =  $data["calle2"];
        } 
        //CURP
        $documento = $data["curp"]."_CURP.pdf";
        /*$path = Storage::putFileAs(
            'documentosSolicitud', $request->file('documentoCurp'), $documento
        );*/
        //Acta de nacimiento
        /*if(isset($data["documentoIdentificacion"])){
            $documentoidentificacion = $data["curp"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion
        );
        }
        else{
            $documentoidentificacion = $data["curp"]."_Acta.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoActa'), $documentoidentificacion
            );
        }*/

        //$data_insert["documentoCurp"] = $documento;
        //$data_insert["documentoIdentificacion"] = $documentoidentificacion;
       
        /*SeerSolicitante::create($data_insert);
        SeerPerGeneral::where('id', $id)
        ->update([
            'caso_excepcion' => $data["excepcion"]
        ]);
        
        if ($data["excepcion"] === "Si") {
            SeerCasosExcepcion::create([
                'id_solicitud' => $id,
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ]);
        }*/

        // Datos de excepción
        $excepcion_data = [];
        if ($data["excepcion"] === "Si") {
             $excepcion_data = [
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ];
        }
        
        // Guardar en sesión
        $solicitante_data = [
            'solicitante' => $data_insert,
            'excepcion' => $data["excepcion"],
            'excepcion_data' => $excepcion_data
        ];
        
        session(['solicitante_trabajador_data' => $solicitante_data]);

       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){
            return redirect()->route('agrega_citadoAux', ['id' => $id] );
        }
        //$estados=Estados::all();*/
        return redirect()->route('agregar_citadoCentro', ['id' => $id] ); 
    }
    
    public function solicitud_parte2(Request $request){
        $data = $request->all();
        $id = $data['id'];

        //validando información
       /*$request->validate([
            //'tipo'                      => 'required|in:Fisica,Moral',
            'curp'                      => 'required|min:18|max:18',
            'nombre'                    => 'required',
            'fecha_nacimiento'          => 'required|date',
            'edad'                      => 'required|numeric',
            'genero'                    => 'required|in:H,M,NC',
            'nacionalidad'              => 'required|in:Mexicana,Otra',
            'estado_nacimiento'         => 'required',
            'telefono1'                 => 'required|min:10|max:10',
            'correo'                    => 'required',
            'estado_solicitante'        => 'required',
            'vialidad'                  => 'required',
            'vialidad_calle'            => 'required',
            'numExt'                    => 'required',
            'colonia_solicitante'       => 'required',
            'municipio_solicitante'     => 'required',
            'cp'                        => 'required|numeric',
            //'referencias'               => 'required|string|max:300',
            //'calle1'                    => 'required',
            //'calle2'                    => 'required',
            'puesto'                    => 'required', 
            'periodo_pago'              => 'required',
            'pago'                      => 'required',
            'horas'                     => 'required',
            'fecha_ingreso'             => 'required',
            'jornada'                   => 'required',
            'identificacion'            => 'required',
            //'documentoCurp'             => 'required',
            'documentoIdentificacion'   => 'required',
            'num_identificacion'        => 'required',
            'descripcionSolicitud'      => 'required',
            'excepcion'                 => 'required',
            'frecuencia_hechos' => 'required_if:excepcion,Si',
            'cambios_situacionL' => 'required_if:excepcion,Si',
            'comunico_hechos' => 'required_if:excepcion,Si',
            'descripcion_conducta' => 'required_if:excepcion,Si',
            'responsable_cargo' => 'required_if:excepcion,Si',
            'actos_cometidos' => 'required_if:excepcion,Si',
            'momento_hechos' => 'required_if:excepcion,Si',
            'lugar_hechos' => 'required_if:excepcion,Si',
            'constancia_hechos' => 'required_if:excepcion,Si',
            'solicito_apoyo' => 'required_if:excepcion,Si',
            'continuacion_solicto_apoyo' => 'required_if:excepcion,Si',
            'incidencia_directa' => 'required_if:solicito_apoyo,Si',
            'recibio_atencion' => 'required_if:excepcion,Si',
        ]);*/
        
        $data_insert=array(
            'id_solicitud'         => $data["id"],
            /*'tipo_persona'         => $data["tipo"],*/
            'curp'                 => $data["curp"],
            'nombre'               => $data["nombre"],
            'fecha_nacimiento'     => $data["fecha_nacimiento"],
            'sexo'                 => $data["genero"],
            'nacionalidad'         => $data["nacionalidad"],
            'estado'               => $data["estado_nacimiento"],
            'edad'                 => $data["edad"],
            'telefono1'            => $data["telefono1"],
            'email'                => $data["correo"],
            'estado_domicilio'     => $data["estado_solicitante"],
            'tipo_vialidad'        => $data["vialidad"],
            'calle'                => $data["vialidad_calle"],
            'num_ext'              => $data["numExt"],
            'colonia'              => $data["colonia_solicitante"],
            'municipio_domicilio'  => $data["municipio_solicitante"],
            'codigo_postal'        => $data["cp"],
            /*'referencia'           => $data["referencias"],
            'calle2'               => $data["calle1"],
            'calle3'               => $data["calle2"],*/
            'puesto'               => $data["puesto"],
            'pago'                 => $data["pago"],
            'periodo_pago'         => $data["periodo_pago"],
            'horas_semana'         => $data["horas"],
            'fecha_ingreso'        => $data["fecha_ingreso"],
            'jornada'              => $data["jornada"],
            'identificacion'       => $data["identificacion"],
            'num_identificacion'   => $data["num_identificacion"],
            'descripcionSolicitud' => $data["descripcionSolicitud"],
        ); 

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                if (is_array($data["lenguaje"])) {
                    $data_insert["lenguaje"] = $data["lenguaje"][0] ?? null;
                } else {
                    $data_insert["lenguaje"] = $data["lenguaje"] ?? null;
                }
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["numInt"])){
            $data_insert["num_int"] =  $data["numInt"];
        }
        if(isset($data["discapacidad"])){
            $data_insert["discapacidad"] =  "Si";
            $data_insert["tipo_discapacidad"] =  $data["tipo_discapacidad"];
        }
        if(isset($data["labora"])){
            $data_insert["labora"] =  "Si";
            //$data_insert["fecha_salida"]  =  $data["fecha_salida"];
        }
        if(isset($data["telefono2"])){
            $data_insert["telefono2"] =  $data["telefono2"];
        }
        if(isset($data["seguro"])){
            $data_insert["nss"] =  $data["seguro"];
        }
        if(isset($data["fecha_salida"])){
            $data_insert["fecha_salida"] =  $data["fecha_salida"];
        }
        if(isset($data["referencias"])){
            $data_insert["referencia"] =  $data["referencias"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle2"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle3"] =  $data["calle2"];
        } 
        //CURP
        $documento = $data["curp"]."_CURP.pdf";
        /*$path = Storage::putFileAs(
            'documentosSolicitud', $request->file('documentoCurp'), $documento
        );*/
        //Acta de nacimiento
        if(isset($data["documentoIdentificacion"])){
            $documentoidentificacion = $data["curp"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion
        );
        }
        else{
            $documentoidentificacion = $data["curp"]."_Acta.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoActa'), $documentoidentificacion
            );
        }

        //$data_insert["documentoCurp"] = $documento;
        $data_insert["documentoIdentificacion"] = $documentoidentificacion;
       
        /*SeerSolicitante::create($data_insert);
        SeerPerGeneral::where('id', $id)
        ->update([
            'caso_excepcion' => $data["excepcion"]
        ]);
        
        if ($data["excepcion"] === "Si") {
            SeerCasosExcepcion::create([
                'id_solicitud' => $id,
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ]);
        }*/

        // Guardar en sesión
        session(['solicitante_data' => $data_insert]);
        
        // Actualizar datos de solicitud en sesión con caso_excepcion
        $solicitudData = session('solicitud_data', []);
        $solicitudData['caso_excepcion'] = $data["excepcion"];
        session(['solicitud_data' => $solicitudData]);

        // Guardar datos de excepción si aplica
        if ($data["excepcion"] === "Si") {
             $excepcionData = [
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ];
            session(['excepcion_data' => $excepcionData]);
        }

       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){
            return redirect()->route('agrega_citadoAux', ['id' => $id] );
        }
        //$estados=Estados::all();*/
        return redirect()->route('agregar_citado', ['id' => $id] ); 
    }

    public function guardar_citadoCentro(Request $request){
        $data = $request->all();
        //$imagen_domicilio1 = "Sin documento";
        //$imagen_domicilio2 = "Sin documento";

        // Usar ID temporal si es sesión
        $tempId = ($data['id'] == 'session') ? uniqid('session_') : $data['id'];

        /*if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;*/
        //validando información
        $request->validate([
            'id'                => 'required',
            'colonia'           => 'required',
            'vialidad'          => 'required',
            'cp'                => 'required|numeric',
            'calle'             => 'required',
            'exterior'          => 'required',
            'referencia'        => 'required',
            'municipio_citado'  => 'required',
            'estado_citado'     => 'required',
            'vialidad'          => 'required'
        ]);
        
        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            /*'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2,*/
            'estado_citado'     => $data["estado_citado"],
        );
        $data_insert["notificacion"] =  $data["notificacion"];

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $data_insert["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        
            if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                $data_insert["nombre"] = $data["razon"];
            }
        
            if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                $data_insert["nombre"] = $data["nombre"];
            }
        }

        //Se van a generar el citatorio
        $data_insert['resulte_responsable'] = 'No';
        //SeerCitados::create($data_insert); 
        // Si es persona física, elimina los apellidos para este citado
        if (isset($data["tipo"]) && $data["tipo"] === "Fisica") {
            unset($data_insert["primer_apellido"], $data_insert["segundo_apellido"]);
        }

        $municipio = Municipios::find($data["municipio_citado"]); 
        $estado = Estados::find($data["estado_citado"]);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        //Validar si existe quien resulta responsable con la misma direccion

        $data_insert["nombre"] = "QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN " .
        $data["vialidad"] . " " . $data["calle"] . ", NÚMERO " . $data["exterior"];
        if (!empty($data["interior"])) {
            $data_insert["nombre"] .= " INT. " . $data["interior"];
        }
        $data_insert["nombre"] .= " COLONIA " . $data["colonia"] . ", " . $municipioNombre . ", " . $estadoNombre . ", C.P. " . $data["cp"] . ".";

        // Marcar este nuevo registro como el "quien resulte" y crear solo si no existe ya uno igual
        $data_insert['resulte_responsable'] = 'Si';
        $direccionNombre = $data_insert["nombre"];
        
        if ($data['id'] == 'session') {
            $citados_list = session('citados_trabajador_data', []);
            
            $citado_original = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                /*'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2,*/
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
            
            if(isset($data["rfc"])) $citado_original["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $citado_original["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $citado_original["traductor"] = $requires ? 1 : 0;
                $citado_original["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $citado_original["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $citado_original["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $citado_original["calle2"] = $data["calle2"];
            
            if (isset($data["tipo"])) {
                $citado_original["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original["nombre"] = $data["nombre"];
                    if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                    if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
                }
            } else {
                 if(isset($data["nombre"])) $citado_original["nombre"] = $data["nombre"];
                 if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                 if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
            }

            $citados_list[] = $citado_original;

            // Verificar si existe "quien resulte" en la sesión
            $existe = false;
            foreach ($citados_list as $c) {
                if (isset($c['resulte_responsable']) && $c['resulte_responsable'] == 'Si' && $c['nombre'] == $direccionNombre) {
                    $existe = true;
                    break;
                }
            }
            
            if (!$existe) {
                $citados_list[] = $data_insert; // Este ya tiene el nombre modificado y resulte_responsable='Si'
            }
            
            session(['citados_trabajador_data' => $citados_list]);
            
        } else {
            // Lógica original BD
            // Reconstruir data_insert original para guardar el primero
             $citado_original_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                /*'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2,*/
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
             
             if (isset($data["tipo"])) {
                $citado_original_bd["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original_bd["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original_bd["nombre"] = $data["nombre"];
                }
            }
            
             $data_insert_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                /*'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2,*/
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );

            if(isset($data["rfc"])) $data_insert_bd["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $data_insert_bd["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $data_insert_bd["traductor"] = $requires ? 1 : 0;
                $data_insert_bd["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $data_insert_bd["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $data_insert_bd["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $data_insert_bd["calle2"] = $data["calle2"];
            if(isset($data["nombre"])) $data_insert_bd["nombre"] = $data["nombre"];
            if(isset($data["primer_apellido"])) $data_insert_bd["primer_apellido"] = $data["primer_apellido"];
            if(isset($data["segundo_apellido"])) $data_insert_bd["segundo_apellido"] = $data["segundo_apellido"];
            
             if (isset($data["tipo"])) {
                $data_insert_bd["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $data_insert_bd["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $data_insert_bd["nombre"] = $data["nombre"];
                }
            }
            
            SeerCitados::create($data_insert_bd);
            
            $existe = SeerCitados::where('id_solicitud', $data['id'])
                    ->where('nombre', $direccionNombre)
                    ->where('resulte_responsable', 'Si')
                    ->exists();
            if (!$existe) {
                SeerCitados::create($data_insert); // Este usa el $data_insert modificado con el nombre largo
            }
        }
        /*$existe = SeerCitados::where('id_solicitud', $data['id'])
                    ->where('nombre', $direccionNombre)
                    ->where('resulte_responsable', 'Si')
                    ->exists();
        if (!$existe) {
            SeerCitados::create($data_insert);
        }*/
        return back()->with('success', 'Citado agregado correctamente, puedes agregar otro o continuar.');
    }

    public function guardar_citado(Request $request){
        $data = $request->all();
        $imagen_domicilio1 = "Sin documento";
        $imagen_domicilio2 = "Sin documento";

        // Usar ID temporal si es sesión
        $tempId = ($data['id'] == 'session') ? uniqid('session_') : $data['id'];

        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;
        //validando información
        $request->validate([
            'id'                => 'required',
            'colonia'           => 'required',
            'vialidad'          => 'required',
            'cp'                => 'required|numeric',
            'calle'             => 'required',
            'exterior'          => 'required',
            'referencia'        => 'required',
            'municipio_citado'  => 'required',
            'estado_citado'     => 'required',
            'vialidad'          => 'required'
        ]);
        
        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2, 
            'estado_citado'     => $data["estado_citado"],
        );
        
        $data_insert["notificacion"] = session('citados_data.0.notificacion', $data['notificacion'] ?? null);

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $data_insert["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        
            if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                $data_insert["nombre"] = $data["razon"];
            }
        
            if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                $data_insert["nombre"] = $data["nombre"];
            }
        }

        $data_insert['resulte_responsable'] = 'No';
        
        if ($data["id"] == 'session') {
            $citados = session('citados_data', []);
            $citados[] = $data_insert;
            session(['citados_data' => $citados]);
        } else {
            SeerCitados::create($data_insert); 
        }
        /*$checando = session()->get('citados_data');
        */
        
        // Si es persona física, elimina los apellidos para este citado
        if (isset($data["tipo"]) && $data["tipo"] === "Fisica") {
            unset($data_insert["primer_apellido"], $data_insert["segundo_apellido"]);
        }

        $municipio = Municipios::find($data["municipio_citado"]); 
        $estado = Estados::find($data["estado_citado"]);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        //Validar si existe quien resulta responsable con la misma direccion

        if($data["resulte_responsable"] == "Si"){
            $data_insert["nombre"] = "QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN " .
            $data["vialidad"] . " " . $data["calle"] . ", NÚMERO " . $data["exterior"];
            if (!empty($data["interior"])) {
                $data_insert["nombre"] .= " INT. " . $data["interior"];
            }
            $data_insert["nombre"] .= " COLONIA " . $data["colonia"] . ", " . $municipioNombre . ", " . $estadoNombre . ", C.P. " . $data["cp"] . ".";

            // Marcar este nuevo registro como el "quien resulte" y crear solo si no existe ya uno igual
            $data_insert['resulte_responsable'] = 'Si';
            $direccionNombre = $data_insert["nombre"];
            
            if ($data["id"] == 'session') {
                $citados = session('citados_data', []);
                $existe = false;
                foreach ($citados as $citado) {
                    if ($citado['nombre'] == $direccionNombre && $citado['resulte_responsable'] == 'Si') {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $citados[] = $data_insert;
                    session(['citados_data' => $citados]);
                }
            } else {
                $existe = SeerCitados::where('id_solicitud', $data['id'])
                            ->where('nombre', $direccionNombre)
                            ->where('resulte_responsable', 'Si')
                            ->exists();
                if (!$existe) {
                    SeerCitados::create($data_insert);
                }
            }
        }
        
        /*if ($data['id'] == 'session') {
            $citados_list = session('citados_trabajador_data', []);
            
            $citado_original = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
            
            if(isset($data["rfc"])) $citado_original["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $citado_original["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $citado_original["traductor"] = $requires ? 1 : 0;
                $citado_original["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $citado_original["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $citado_original["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $citado_original["calle2"] = $data["calle2"];
            
            if (isset($data["tipo"])) {
                $citado_original["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original["nombre"] = $data["nombre"];
                    if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                    if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
                }
            } else {
                 if(isset($data["nombre"])) $citado_original["nombre"] = $data["nombre"];
                 if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                 if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
            }

            $citados_list[] = $citado_original;

            // Verificar si existe "quien resulte" en la sesión
            $existe = false;
            foreach ($citados_list as $c) {
                if (isset($c['resulte_responsable']) && $c['resulte_responsable'] == 'Si' && $c['nombre'] == $direccionNombre) {
                    $existe = true;
                    break;
                }
            }
            
            if (!$existe) {
                $citados_list[] = $data_insert; // Este ya tiene el nombre modificado y resulte_responsable='Si'
            }
            
            session(['citados_trabajador_data' => $citados_list]);
            
        } else {
            // Lógica original BD
            // Reconstruir data_insert original para guardar el primero
             $citado_original_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
             
             if (isset($data["tipo"])) {
                $citado_original_bd["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original_bd["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original_bd["nombre"] = $data["nombre"];
                }
            }
            
             $data_insert_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );

            if(isset($data["rfc"])) $data_insert_bd["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $data_insert_bd["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $data_insert_bd["traductor"] = $requires ? 1 : 0;
                $data_insert_bd["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $data_insert_bd["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $data_insert_bd["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $data_insert_bd["calle2"] = $data["calle2"];
            if(isset($data["nombre"])) $data_insert_bd["nombre"] = $data["nombre"];
            if(isset($data["primer_apellido"])) $data_insert_bd["primer_apellido"] = $data["primer_apellido"];
            if(isset($data["segundo_apellido"])) $data_insert_bd["segundo_apellido"] = $data["segundo_apellido"];
            
             if (isset($data["tipo"])) {
                $data_insert_bd["tipo_persona"] = $data["tipo"];
                if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $data_insert_bd["nombre"] = $data["razon"];
                }
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $data_insert_bd["nombre"] = $data["nombre"];
                }
            }
            
            SeerCitados::create($data_insert_bd);
            
            $existe = SeerCitados::where('id_solicitud', $data['id'])
                    ->where('nombre', $direccionNombre)
                    ->where('resulte_responsable', 'Si')
                    ->exists();
            if (!$existe) {
                SeerCitados::create($data_insert); // Este usa el $data_insert modificado con el nombre largo
            }
        }
        $existe = SeerCitados::where('id_solicitud', $data['id'])
                    ->where('nombre', $direccionNombre)
                    ->where('resulte_responsable', 'Si')
                    ->exists();
        if (!$existe) {
            SeerCitados::create($data_insert);
        }*/

        return back()->with('success', 'Citado agregado correctamente, puedes agregar otro o continuar.');
    }

    public function vista_citadoCentro($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        
        if ($id == 'session') {
            $citados_data = session('citados_trabajador_data', []);
            $citados = count($citados_data);
        } else {
            $citados = SeerCitados::where('id_solicitud', $id)->count(); //LLeva el conteo de los citados agregados
        }
       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        /*if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.citadosAux',compact('estados','id','citados','municipios'));
        }*/
        return view('solicitudes.citadosCentro',compact('estados','id','citados','municipios'));
    }

    public function vista_citado($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        $session_notificacion = session('citados_data.0.notificacion');
        
        if ($id == 'session') {
            $citados_data = session('citados_data', []);
            $citados = count($citados_data);
        } else {
            $citados = SeerCitados::where('id_solicitud', $id)->count(); //LLeva el conteo de los citados agregados
        }
       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        /*if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.citadosAux',compact('estados','id','citados','municipios'));
        }*/
        return view('solicitudes.citados',compact('estados','id','citados','municipios', 'session_notificacion'));
    }

    /*public function vista_solicitante($id){
        $estados = Estados::all();
        $municipios = Municipios::all();

        return view('solicitudes.solicitante_nuevo', compact('estados','municipios','id'))->with('success', 'Solicitante agregado correctamente, puedes agregar otro o continuar.');
    }*/

    /*public function vista_documentos($id){
        return view('solicitudes.documentos',compact('id'));
    }*/

    public function guardar_solicitudCentro($id){
        if ($id == 'session') {
             // Recuperar datos de sesión
             $solicitud_data = session('solicitud_trabajador_data');
             $solicitante_data = session('solicitante_trabajador_data');
             $citados_data = session('citados_trabajador_data', []);
             
             if (!$solicitud_data || !$solicitante_data) {
                 return redirect()->route('solicitudEnLineaCentro')->with('error', 'Sesión expirada o datos incompletos.');
             }

             DB::beginTransaction();
             try {
                 // 1. Crear SeerPerGeneral
                 $general_insert = [
                    'id_rama'         =>  $solicitud_data["id_rama"],
                    'actividad'       =>  $solicitud_data["actividad"],
                    'delegacion'      =>  $solicitud_data["delegacion"],
                    'tipo_solicitud'  =>  $solicitud_data["tipo_solicitud"],
                    'tipo_generacion' =>  1000,
                    'consecutivo'     =>  $solicitud_data["consecutivo"],
                    'año'             =>  $solicitud_data["año"],
                    'caso_excepcion'  =>  $solicitante_data['excepcion']
                 ];
                 
                 SeerPerGeneral::create($general_insert);
                 $general_record = SeerPerGeneral::latest('id')->first();
                 $new_id = $general_record->id;

                 
                 // 2. Crear SeerMotivo
                 if (!empty($solicitud_data["motivo_solicitud"])) {
                    foreach ($solicitud_data["motivo_solicitud"] as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $new_id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                 }

                 // 3. Crear SeerSolicitante
                 $solicitante_insert = $solicitante_data['solicitante'];
                 $solicitante_insert['id_solicitud'] = $new_id;
                 SeerSolicitante::create($solicitante_insert);

                 // 4. Crear SeerCasosExcepcion
                 if ($solicitante_data['excepcion'] === "Si") {
                     $excepcion_insert = $solicitante_data['excepcion_data'];
                     $excepcion_insert['id_solicitud'] = $new_id;
                     SeerCasosExcepcion::create($excepcion_insert);
                 }
                 
                 // 5. Crear SeerCitados
                 foreach ($citados_data as $citado) {
                     $citado['id_solicitud'] = $new_id;
                     SeerCitados::create($citado);
                 }
                 
                 DB::commit();
                 
                 // Limpiar sesión
                 session()->forget('solicitud_trabajador_data');
                 session()->forget('solicitante_trabajador_data');
                 session()->forget('citados_trabajador_data');
                 
                 $id = $new_id; // Actualizar ID para el resto del flujo
                 
             } catch (\Exception $e) {
                DB::rollBack();

                    $solicitanteSess = session('solicitante_trabajador_data', []);
                    if (!empty($solicitanteSess) && is_array($solicitanteSess)) {
                        $solicitanteArr = [];
                        if (isset($solicitanteSess['solicitante']) && is_array($solicitanteSess['solicitante'])) {
                            $solicitanteArr = $solicitanteSess['solicitante'];
                        } else {
                            $solicitanteArr = $solicitanteSess;
                        }
                        $fileKeys = ['documentoIdentificacion', 'documentoCurp', 'documentoCurpPath', 'documentoIdentificacionPath'];
                        foreach ($fileKeys as $key) {
                            if (!empty($solicitanteArr[$key]) && is_string($solicitanteArr[$key])) {
                                $filename = basename($solicitanteArr[$key]);
                                $path = 'documentosSolicitud/' . $filename;
                                    if (\Storage::exists($path)) {
                                        \Storage::delete($path);
                                    }
                                
                            }
                        }
                    }

                    $citados = session('citados_trabajador_data', []);
                    if (!empty($citados) && is_array($citados)) {
                        foreach ($citados as $citado) {
                            if (is_array($citado)) {
                                foreach ($citado as $k => $v) {
                                    if (is_string($v) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $v)) {
                                        $filename = basename($v);
                                        $path = 'documentosSolicitud/' . $filename;
                                            if (\Storage::exists($path)) {
                                                \Storage::delete($path);
                                            }
                                    }
                                }
                            }
                        }
                    }

                    session()->forget(['solicitud_trabajador_data', 'solicitante_trabajador_data', 'citados_trabajador_data']);

                // En el flujo Centro, regresamos al inicio del flujo Centro.
                return redirect()->route('solicitudEnLineaCentro')->with('error', 'Ocurrió un error al guardar la solicitud. Se descartaron los datos de captura.');
            }
        }

        //Revisar si ya existe el correo
        $solicitante = SeerSolicitante::where('id_solicitud',$id)->first();
        $nombre = $solicitante["nombre"]." ".$solicitante["primer_apellido"]." ".$solicitante["segundo_apellido"];
        $folio = $solicitante["id_solicitud"];
        $delegacion = SeerPerGeneral::find($id);
        $nombreDelegacion = $delegacion->delegacion;
        
        $mapaSedes = [
            'Morelia'           => ['Morelia'],
            'Uruapan'           => ['Uruapan'],
            'Zamora'            => ['Zamora'],
            'Zitácuaro'         => ['Morelia'],
            'Lázaro Cárdenas'   => ['Uruapan'],
            'Sahuayo'           => ['Zamora']
        ];

        $sedesFiltradas = $mapaSedes[$nombreDelegacion] ?? [$nombreDelegacion];

        $delegado = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Delegado');
        })
        ->whereIn('delegacion', $sedesFiltradas)
        ->first();
       
        SeerPerGeneral::find($id)->update(['delegado_id' => $delegado->id]);

        $usuario = User::
        where('profile_photo_path',$solicitante['curp'])
        ->orWhere('email',$solicitante["email"])
         ->first();

        if(!isset($usuario)){
            $data_insertar_user= array(
                'name'              => $nombre,
                'email'             => $solicitante["email"],
                'delegacion'        => $delegacion["delegacion"],
                'type'              => "Seer",
                'remember_token'    => $solicitante["curp"],
                'profile_photo_path'=> $solicitante["curp"]
            ); 
            //Genrar un random del uno al 100 y agregarlo a la contraseña
            $numero_aleatorio = mt_rand(1, 1000);

            //Hacemos un hash del campo que tiene el password
            $data_insertar_user['password'] = Hash::make("CCLMICHOACAN".$numero_aleatorio);
            $usuario = User::create($data_insertar_user);
            $usuario->assignRole(('Solicitante'));
            $mensaje = " el correo:".$usuario["email"]." y la contraseña:CCLMICHOACAN".$numero_aleatorio." para continuar tú trámite.";

            $solicitud = SeerPerGeneral::find($id);
            $citados = SeerCitados::where('id_solicitud', $id)->get();

            $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
            $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
            $pdfContent = $pdf->output();

            $variables = [
                'Nombre'           => $nombre,
                'Contraseña'       => "CCLMICHOACAN".$numero_aleatorio,
                'email'            => $solicitante["email"],
                'NumFolio'         => $folio,
            ];
            //Mail::to($usuario['email'])->send(new SolicitudMail($pdfContent, $variables));
        }
        else{
            $mensaje = " el correo:".$usuario["email"]." ya esta registrado en Si Concilio su solicitud sera asignado al usuario existente.";
            $solicitud = SeerPerGeneral::find($id);
            $citados = SeerCitados::where('id_solicitud', $id)->get();
            $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
            $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
            $pdfContent = $pdf->output();

            $variables = [
                'Nombre'           => $nombre,
                'Contraseña'       => "Ya esta registrada",
                'email'            => $solicitante["email"],
                'NumFolio'         => $folio,
            ];
            //Mail::to($usuario['email'])->send(new SolicitudMail($pdfContent, $variables));
        }

        return view('solicitudes.avisoCentro',compact('id','mensaje','delegacion'));
    }


    public function guardar_solicitudCentro_post(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            $id = 'session';
        }

        return $this->guardar_solicitudCentro($id);
    }

    public function guardar_citado_patronal(Request $request, $id)
    {
        $data = $request->all();
        
        // Validar campos requeridos
        $request->validate([
            'colonia'           => 'required',
            'vialidad'          => 'required',
            'cp'                => 'required|numeric',
            'calle'             => 'required',
            'exterior'          => 'required',
            'referencia'        => 'required',
            'municipio_citado'  => 'required',
            'estado_citado'     => 'required',
            'notificacion'      => 'required',
        ]);

        // Preparar datos del citado para guardar en sesión
        $citado_data = [
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            'estado_citado'     => $data["estado_citado"],
            'notificacion'      => $data["notificacion"],
            'tipo_persona'      => 'Fisica', // Siempre física según tu vista
        ];

        // Agregar campos opcionales
        if(isset($data["rfc"])) $citado_data["rfc"] = $data["rfc"];
        if(isset($data["curp"])) $citado_data["curp"] = $data["curp"];
        if(isset($data["traductor"])) {
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $citado_data["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $citado_data["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            }
        }
        if(isset($data["interior"])) $citado_data["n_int"] = $data["interior"];
        if(isset($data["calle1"])) $citado_data["calle1"] = $data["calle1"];
        if(isset($data["calle2"])) $citado_data["calle2"] = $data["calle2"];
        if(isset($data["nombre"])) $citado_data["nombre"] = $data["nombre"];
        if(isset($data["primer_apellido"])) $citado_data["primer_apellido"] = $data["primer_apellido"];
        if(isset($data["segundo_apellido"])) $citado_data["segundo_apellido"] = $data["segundo_apellido"];

        // Manejar archivos si existen
        if ($request->hasFile('foto1')) {
            $tempId = ($id == 'session') ? uniqid('session_') : $id;
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
            $citado_data['imagen_domicilio1'] = $imagen_domicilio1;
        }

        if ($request->hasFile('foto2')) {
            $tempId = ($id == 'session') ? uniqid('session_') : $id;
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
            $citado_data['imagen_domicilio2'] = $imagen_domicilio2;
        }

        // Guardar en sesión
        session(['citados_trabajador_data' => $citado_data]);

        // Redirigir a guardar_solicitud
        return $this->guardar_solicitud($id);
    }

    public function guardar_solicitud($id){
        if ($id == 'session') {
            // Recuperar datos de sesión
            $solicitud_data = session('solicitud_data');
            $solicitante_data = session('solicitante_data');
            $citadosData = session('citados_data', []);
            $excepcionData = session('excepcion_data');         

             
            if (!$solicitud_data || !$solicitante_data) {
                return redirect()->route('solicitudEnLinea')->with('error', 'Sesión expirada o datos incompletos.');
            }

            //Si ya existen 5 solicitudes en seer_general para la delegación en la fecha de hoy no se guarda.
            if (
                isset($solicitud_data['delegacion'], $solicitud_data['tipo_generacion'])
                && (string) $solicitud_data['tipo_generacion'] === '0'
            ) {
                $delegacionLimite = (string) $solicitud_data['delegacion'];
                $hoy = now()->toDateString();
                $limiteDiario = 5;

                $conteoHoy = SeerPerGeneral::query()
                    ->where('delegacion', $delegacionLimite)
                    ->where('tipo_generacion', 0)
                    ->whereDate('fecha', $hoy)
                    ->count();

                if ($conteoHoy >= $limiteDiario) {
                    if ($id == 'session' || session()->has('solicitud_data')) {
                    // Limpiar sesión
                    session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);
                    }
                    return redirect()->route('solicitudEnLinea')
                        ->with('error', "Página en mantenimiento.");
                }
            }

            DB::beginTransaction();
            try {
                // 1. Crear SeerPerGeneral
                $general_insert = [
                    'id_rama'         =>  $solicitud_data["id_rama"],
                    'actividad'       =>  $solicitud_data["actividad"],
                    'delegacion'      =>  $solicitud_data["delegacion"],
                    'tipo_solicitud'  =>  $solicitud_data["tipo_solicitud"],
                    'tipo_generacion' =>  $solicitud_data["tipo_generacion"],
                    'consecutivo'     =>  $solicitud_data["consecutivo"],
                    'año'             =>  $solicitud_data["año"],
                    'caso_excepcion'  =>  $solicitante_data['excepcion'] ?? 'No' // En caso de que no venga el campo, se asume "No"
                ];

                 // 2. Guardar Motivos
                if (!empty($solicitudMotivos)) {
                    foreach ($solicitudMotivos as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                }

                SeerPerGeneral::create($general_insert);
                $general_record = SeerPerGeneral::latest('id')->first();
                $new_id = $general_record->id;
                 
                // 2. Crear SeerMotivo
                if (!empty($solicitud_data["motivo_solicitud"])) {
                    foreach ($solicitud_data["motivo_solicitud"] as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $new_id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                }
                 
                // 3. Crear SeerSolicitante
                $solicitante_data['id_solicitud'] = $new_id;
                SeerSolicitante::create($solicitante_data);
                 
                // 4. Guardar Caso Excepción (si existe)
                if ($excepcionData) {
                    $excepcionData['id_solicitud'] = $new_id;
                    SeerCasosExcepcion::create($excepcionData);
                }

                // 5. Guardar Citados
                foreach ($citadosData as $citado) {
                    $citado['id_solicitud'] = $new_id;
                    SeerCitados::create($citado);
                }
                 
                DB::commit();
                 
                if ($id == 'session' || session()->has('solicitud_data')) {
                    // Limpiar sesión
                    session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);
                }
                 
                $id = $new_id; // Actualizar ID para el resto del flujo
                 
             } catch (\Exception $e) {
                DB::rollBack();
                    $solicitanteSess = session('solicitante_data', []);
                    if (!empty($solicitanteSess) && is_array($solicitanteSess)) {
                        $solicitanteArr = [];
                        if (isset($solicitanteSess['solicitante']) && is_array($solicitanteSess['solicitante'])) {
                            $solicitanteArr = $solicitanteSess['solicitante'];
                        } else {
                            $solicitanteArr = $solicitanteSess;
                        }
                        $fileKeys = ['documentoIdentificacion', 'documentoCurp', 'documentoCurpPath', 'documentoIdentificacionPath'];
                        foreach ($fileKeys as $key) {
                            if (!empty($solicitanteArr[$key]) && is_string($solicitanteArr[$key])) {
                                $filename = basename($solicitanteArr[$key]);
                                $path = 'documentosSolicitud/' . $filename;
                                    if (\Storage::exists($path)) {
                                        \Storage::delete($path);
                                    }
                                
                            }
                        }
                    }

                    $citados = session('citados_data', []);
                    if (!empty($citados) && is_array($citados)) {
                        foreach ($citados as $citado) {
                            if (is_array($citado)) {
                                foreach ($citado as $k => $v) {
                                    if (is_string($v) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $v)) {
                                        $filename = basename($v);
                                        $path = 'documentosSolicitud/' . $filename;
                                            if (\Storage::exists($path)) {
                                                \Storage::delete($path);
                                            }
                                    }
                                }
                            }
                        }
                    }

                    session()->forget(['solicitud_trabajador_data', 'solicitante_trabajador_data', 'citados_trabajador_data']);

                return redirect()->route('solicitudEnLinea')->with('error', 'Ocurrió un error al guardar la solicitud. Se descartaron los datos de captura.');
            }
        }

        //Revisar si ya existe el correo
        $solicitante = SeerSolicitante::where('id_solicitud',$id)->first();
        $nombre = $solicitante["nombre"]." ".$solicitante["primer_apellido"]." ".$solicitante["segundo_apellido"];
        $folio = $solicitante["id_solicitud"];
        $delegacion = SeerPerGeneral::find($id);
        $nombreDelegacion = $delegacion->delegacion;
        
        $mapaSedes = [
            'Morelia'           => ['Morelia'],
            'Uruapan'           => ['Uruapan'],
            'Zamora'            => ['Zamora'],
            'Zitácuaro'         => ['Morelia'],
            'Lázaro Cárdenas'   => ['Uruapan'],
            'Sahuayo'           => ['Zamora']
        ];

        $sedesFiltradas = $mapaSedes[$nombreDelegacion] ?? [$nombreDelegacion];

        $delegado = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Delegado');
        })
        ->whereIn('delegacion', $sedesFiltradas)
        ->first();
       
        SeerPerGeneral::find($id)->update(['delegado_id' => $delegado->id]);

        /*
            $usuario = User::
            where('profile_photo_path',$solicitante['curp'])
            ->orWhere('email',$solicitante["email"])
            ->first();

            if(!isset($usuario)){
                $data_insertar_user= array(
                    'name'              => $nombre,
                    'email'             => $solicitante["email"],
                    'delegacion'        => $delegacion["delegacion"],
                    'type'              => "Seer",
                    'remember_token'    => $solicitante["curp"],
                    'profile_photo_path'=> $solicitante["curp"]
                ); 
                //Genrar un random del uno al 100 y agregarlo a la contraseña
                $numero_aleatorio = mt_rand(1, 1000);

                //Hacemos un hash del campo que tiene el password
                $data_insertar_user['password'] = Hash::make("CCLMICHOACAN".$numero_aleatorio);
                $usuario = User::create($data_insertar_user);
                $usuario->assignRole(('Solicitante'));
                $mensaje = " el correo:".$usuario["email"]." y la contraseña:CCLMICHOACAN".$numero_aleatorio." para continuar tú trámite.";

                $solicitud = SeerPerGeneral::find($id);
                $citados = SeerCitados::where('id_solicitud', $id)->get();

                $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
                $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
                $pdfContent = $pdf->output();

                $variables = [
                    'Nombre'           => $nombre,
                    'Contraseña'       => "CCLMICHOACAN".$numero_aleatorio,
                    'email'            => $solicitante["email"],
                    'NumFolio'         => $folio,
                ];
                Mail::to($usuario['email'])->send(new SolicitudMail($pdfContent, $variables));
            }
            else{
                $mensaje = " el correo:".$usuario["email"]." ya esta registrado en Si Concilio su solicitud sera asignado al usuario existente.";
                $solicitud = SeerPerGeneral::find($id);
                $citados = SeerCitados::where('id_solicitud', $id)->get();
                $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
                $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
                $pdfContent = $pdf->output();

                $variables = [
                    'Nombre'           => $nombre,
                    'Contraseña'       => "Ya esta registrada",
                    'email'            => $solicitante["email"],
                    'NumFolio'         => $folio,
                ];
            }
        */ 
            $solicitud = SeerPerGeneral::find($id);
            $solicitante  = SeerSolicitante::where("id_solicitud", "=", $solicitud["id"])->first();
            $nombre = $solicitante["nombre"];
            $citados = SeerCitados::where('id_solicitud', $id)->get();

            $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
                $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
                $pdfContent = $pdf->output();

            $variables = [
                'Nombre'           => $nombre,
                'Contraseña'       => "Ya esta registrada",
                'email'            => $solicitante["email"],
                'NumFolio'         => $folio,
            ];
            Mail::to($variables['email'])->send(new SolicitudMail($pdfContent, $variables));

            $mensaje = '';
        
        return view('solicitudes.aviso',compact('id','mensaje','delegacion'));
    }

    public function aviso(Request $request){
        $data = $request->all();
        $id = $data["id"];
        $mensaje = $data["mensaje"];
        $delegacion = $data["delegacion"];

        return view('solicitudes.aviso',compact('id','mensaje','delegacion'));
    }

    public function solicitudes_pendientes() {
        $user = auth()->user();
        $rol = $user->roles->first()->name ?? '';
        $id_usuario = $user->id;
        $delegacion_usuario = $user->delegacion;

        // 1. Mapa centralizado de sedes y sus oficinas de apoyo
        $mapaSedes = [
            'Morelia'         => ['Morelia', 'Zitácuaro'],
            'Zitácuaro'       => ['Morelia', 'Zitácuaro'],
            'Uruapan'         => ['Uruapan', 'Lázaro Cárdenas'],
            'Lázaro Cárdenas' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'          => ['Zamora', 'Sahuayo'],
            'Sahuayo'         => ['Zamora', 'Sahuayo'],
        ];

        // --- NUEVO: Obtener la fecha exacta de hace 7 días ---
        // subDays(7) resta una semana a la fecha de hoy.
        // toDateString() lo deja en formato 'YYYY-MM-DD' listo para SQL.
        $haceUnaSemana = Carbon::now()->subDays(7)->toDateString();

        // 2. Iniciamos la consulta base
        $query = SeerPerGeneral::join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->leftjoin('users','users.id','seer_general.user_id')
            ->select(
                'seer_general.id',
                'seer_general.consecutivo',
                'seer_general.fecha',
                'seer_solicitante.nombre',
                'seer_general.delegacion',
                'seer_general.actividad',
                'catalogo_rama.rama_industrial',
                'seer_general.tipo_solicitud',
                'seer_general.estatus',
                'seer_general.tipo_generacion',
                'users.name'
            )
            ->where('validado_conciliador', 'Pendiente')
            ->whereIn('seer_general.estatus', ['Pendiente', 'Prevencion'])
            
            // --- NUEVA CONDICIÓN DE TIEMPO ---
            // Filtra para que solo traiga fechas desde hace 7 días hasta hoy (menor a una semana de antigüedad)
            ->where('seer_general.fecha', '>=', $haceUnaSemana)
            
            ->orderBy('seer_general.fecha');

        // 3. Aplicamos lógica de filtros por Rol (Sin repetir la consulta)
        if (!in_array($rol, ['Super Usuario', 'Administrador'])) {
            
            $delegacionesPermitidas = [$delegacion_usuario];

            if (in_array($rol, ['Conciliador', 'Delegado', 'Enlace', 'Auxiliar', 'Excepcion'])) {
                
                $accesoViculado = true;

                if ($rol == 'Conciliador') {
                    $accesoViculado = PermisosConciliador::where('id_conciliador', $id_usuario)
                        ->where('tipo', 'Ambos')
                        ->exists();
                }

                if ($accesoViculado && isset($mapaSedes[$delegacion_usuario])) {
                    $delegacionesPermitidas = $mapaSedes[$delegacion_usuario];
                }
            }

            $query->whereIn('seer_general.delegacion', $delegacionesPermitidas);
        }
        
        // 4. CORRECCIÓN CRÍTICA: Agrupamiento del OR para que no rompa el filtro de fecha ni de delegación
        $query->where(function($q) {
            $q->whereNull('seer_general.incidencia')
            ->orWhere('seer_general.incidencia', 0);
        });

        // 5. Ejecución final
        $solicitudes = $query->get();
        
        return view('solicitudes.solicitudes_pendientes', compact('solicitudes'));
    }

    public function solicitudes_pendientes_revisar($id){
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $id             = $id;
        $general        = SeerPerGeneral::find($id);
        $ramas          = SolicitudRama::all();
        $solicitantes   = SeerSolicitante::where("id_solicitud",$id)->get();
        $citados        = SeerCitados::where("id_solicitud",$id)->get();
        $estados        = Estados::all();
        $municipios     = Municipios::all();
        $conciliadores  = User::find($general["conciliador_id"]);
        $audiencia      = SeerPerConciliador::where("id_solicitud",$id)->get();
        //Catalogo de motivos
        //$mostrarMotivos = SolicitudMotivo::all();
        $mostrarMotivos = SolicitudMotivo::where('tipo_solicitud', $general->tipo_solicitud)->get();
        //Motivos capturados
        $motivos        = SeerMotivo::join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo','seer_motivos.id')->get();

        return view('solicitudes.revisar_solicitud', compact('id','general','solicitantes','citados','ramas','estados','municipios','mostrarMotivos','motivos','conciliadores','audiencia'));
    }
    
    public function eliminar_motivo($id,$id_motivo){
        // SeerMotivo::find($id_motivo)->delete();
        $deleted = session('motivos_edicion_delete', []);
        $deleted[] = (string)$id_motivo;
        session(['motivos_edicion_delete' => $deleted]);

        session()->flash('preserve_edit_session', true);
        return redirect()->route('solicitud_audiencia', ['id' => $id] ); 
    }

    public function eliminar_motivo_solicitud($id, $id_motivo){
        // SeerMotivo::find($id_motivo)->delete();
        $deleted = session('motivos_edicion_delete', []);
        $deleted[] = (string)$id_motivo;
        session(['motivos_edicion_delete' => $deleted]);

        session()->flash('preserve_edit_session', true);
        return redirect()->route('solicitud_editar', ['id' => $id] );
    }

    public function eliminar_motivo_buzon($id, $id_motivo){
        // SeerMotivo::find($id_motivo)->delete();
        $deleted = session('motivos_edicion_delete', []);
        $deleted[] = (string)$id_motivo;
        session(['motivos_edicion_delete' => $deleted]);

        session()->flash('preserve_edit_session', true);
        return redirect()->route('consulta_solicitante', ['id' => $id] );
    }

    public function regresa_eliminar($id){
        $general        = SeerPerGeneral::find($id);
        $ramas          = SolicitudRama::all();
        $solicitantes   = SeerSolicitante::where("id_solicitud",$id)->get();
        $citados        = SeerCitados::where("id_solicitud",$id)->get();
        $estados        = Estados::all();
        $municipios     = Municipios::all();
        
        //Catalogo de motivos
        //$mostrarMotivos = SolicitudMotivo::all();
        $mostrarMotivos = SolicitudMotivo::where('tipo_solicitud', $general->tipo_solicitud)->get();
        //Motivos capturados
        $motivos        = SeerMotivo::join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo','seer_motivos.id')->get();

        return view('solicitudes.revisar_solicitud', compact('id','general','solicitantes','citados','ramas','estados','municipios','mostrarMotivos','motivos'));
    }

    public function audiencia_confirmar(Request $request){
        DB::beginTransaction();
        try {
        $data = $request->all();
        //Se va asignar el conciliador y la sala
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $fecha_actual = date('Y-m-d');

        $isAudiencia = '';
        

        $motivosDelete = session('motivos_edicion_delete', []);
        if (!empty($motivosDelete)) {
            SeerMotivo::whereIn('id', $motivosDelete)->delete();
        }
        session()->forget('motivos_edicion_delete');

        SeerPerGeneral::where('id', $data["id"])
        ->update(['actividad' => $data["actividad_economica"],'id_rama' => $data["ramaIndustrial"]]);

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $data["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }

        //Actualizar SEER SOLICTUD
        SeerSolicitante::where('id_solicitud', $data["id"])
        ->update([/*'tipo_persona' => $data["tipo_persona_solicitante"],*/ 
            'curp'                  => $data["curp_solicitante"],
            //'rfc'                   => $data["rfc_solicitante"],
            'nombre'                => $data["nombre_solicitante"],
            'sexo'                  => $data["sexo_solicitante"],
            'nacionalidad'          => $data["nacionalidad_solicitante"],
            //'estado'                => $data["estado_solicitante"],
            'email'                 => $data["email_solicitante"],
            'fecha_nacimiento'      => $data["fecha_nacimiento_solicitante"],
            'edad'                  => $data["edad_solicitante"],
            'telefono1'             => $data["telefono1_solicitante"],
            'traductor'             => $data["traductor_solicitante"],
            'lenguaje'              => $data["lenguaje_solicitante"],
            'discapacidad'          => $data["discapacidad_solicitante"],
            'tipo_discapacidad'     => $data["disc_solicitante"],
            'tipo_vialidad'         => $data["tipo_vialidad"],
            'calle'                 => $data["calle_solicitante"],
            'num_ext'               => $data["num_ext_solicitante"],
            'num_int'               => $data["num_int_solicitante"],
            'codigo_postal'         => $data["codigo_postal_solicitante"],
            'referencia'            => $data["referencia_solicitante"],
            'colonia'               => $data["colonia_solicitante"],
            'calle2'                => $data["calle2_solicitante"],
            'calle3'                => $data["calle3_solicitante"],
            'municipio_domicilio'   => $data["municipio_solicitante"],
            'puesto'                => $data["puesto"],
            'pago'                  => $data["pago"],
            'periodo_pago'          => $data["periodo_pago"],
            'fecha_ingreso'         => $data["fecha_ingreso"],
            'fecha_salida'          => $data["fecha_salida"],
            'jornada'               => $data["jornada"],
            'estado_domicilio'      => $data["estado_solicitante"],
            'horas_semana'          => $data["horas_semana"],
            'descripcionSolicitud'  => $data["descripcionSolicitud"],
        ]);

        //Opcionales
        if(isset($data["telefono2"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['telefono2' => $data["telefono2_solicitante"] ]);
        }
        if(isset($data["num_int"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['num_int' => $data["num_int_solicitante"] ]);
        }
        if(isset($data["nss"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['nss' => $data["nss"] ]);
        }
        if(isset($data["rfc"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['rfc' => $data["rfc_solicitante"] ]);
        }
        if(isset($data["referencia"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['referencia' => $data["referencia_solicitante"] ]);
        }
        if(isset($data["calle2"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle2' => $data["calle2_solicitante"] ]);
        }
        if(isset($data["calle3"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle3' => $data["calle3_solicitante"] ]);
        }

        //Citados

        $audienciaId = $data['audiencia_id'] ?? null;
        if (is_null($audienciaId) || $audienciaId === '') {
            $audienciaId = Audiencias::where('id_solicitud', $data['id'])->latest('id')->value('id');
        }

        $citadosDelete = session('citados_edicion_delete', []);
        if (!empty($citadosDelete)) {
            SeerCitados::where('id_solicitud', $data["id"])->whereIn('id', $citadosDelete)->delete();
        }

        $cont = is_array($data["colonia_citado"] ?? null) ? count($data["colonia_citado"]) : 0;
        for($i = 0; $i < $cont; $i++) {

            $citadoId = null;
            if (isset($data['id_citado']) && is_array($data['id_citado'])) {
                $citadoId = $data['id_citado'][$i] ?? null;
            }

            $citado = null;
            if (!empty($citadoId)) {
                $citado = SeerCitados::where('id_solicitud', $data["id"])->where('id', $citadoId)->first();
            }
            if (!$citado) {
                $citado = new SeerCitados();
                $citado->id_solicitud = $data["id"];
                $citado->audiencia_id = $audienciaId;
            }

            $foto1 = $citado->imagen_domicilio1 ?? ($data["imagen_domicilio1"][$i] ?? 'Sin documento');
            $foto2 = $citado->imagen_domicilio2 ?? ($data["imagen_domicilio2"][$i] ?? 'Sin documento');

            if ($request->hasFile("foto1.$i")) {
                $file = $request->file("foto1")[$i];
                $foto1 = $data["id"] . "-citado_foto1_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                Storage::putFileAs('documentosSolicitud', $file, $foto1);
            }

            if ($request->hasFile("foto2.$i")) {
                $file = $request->file("foto2")[$i];
                $foto2 = $data["id"] . "-citado_foto2_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                Storage::putFileAs('documentosSolicitud', $file, $foto2);
            }

            $data_update = array(
                'colonia'           => $data["colonia_citado"][$i],
                'cp'                => $data["cp_citado"][$i],
                'n_ext'             => $data["n_ext_citado"][$i],
                'n_int'             => $data["n_int_citado"][$i],
                'tipo_vialidad'     => $data["vialidad_citado"][$i],
                'referencia'        => $data["referencia_citado"][$i],
                'municipio_citado'  => $data["municipio_citado"][$i],
                'tipo_persona'      => $data["tipo_persona_citado"][$i],
                'nombre'            => $data["nombre_citado"][$i],
                'notificacion'      => $data["notificacion"][$i],
                'primer_apellido'   => $data["primer_apellido"][$i] ?? null,
                'segundo_apellido'  => $data["segundo_apellido"][$i] ?? null,
                'calle'             => $data["calle_citado"][$i],
                'calle1'            => $data["calle1_citado"][$i],
                'calle2'            => $data["calle2_citado"][$i],
                'curp'              => $data["curp_citado"][$i] ?? null,
                'rfc'               => $data["rfc_citado"][$i],
                'estado_citado'     => $data["estado_citado"][$i],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2,
                'resulte_responsable' => $data['resulte_responsable'][$i] ?? 'No',
            );

            if(isset($data["traductor"])){
                $val = $data["traductor"][ $i ] ?? null;
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $data_update["traductor"] = $requires ? 1 : 0;
                $data_update["lenguaje"]  = $data["lenguaje"][ $i ] ?? null;
            }

            if(isset($data["calle1"])){
                SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle1' => $data["calle1_citado"] ]);
            }
            if(isset($data["calle2"])){
                SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle2' => $data["calle2_citado"] ]);
            }

            $citado->fill($data_update);
            $citado->save();
        }

        $citados = SeerCitados::where('id_solicitud', $data["id"])->get();
        foreach ($citados as $citado) {
            if (!$citado->audiencia_id) {
                $citado->audiencia_id = $audienciaId;
                $citado->save();
            }
        }

        $curpBase = $data['curp_solicitante'];

        // CURP (PDF)
        /*if ($request->hasFile('curp_solicitante')) {
            $documento = $curpBase . '_CURP.pdf';
            Storage::putFileAs('documentosSolicitud', $request->file('curpsolicitante'), $documento);
            SeerSolicitante::where('id_solicitud', $data['id'])->update(['documentoCurp' => $documento]);
        }*/

        if ($request->hasFile('indetificacion')) {
            $documentoidentificacion = $curpBase . '_Identificacion.pdf';
            Storage::putFileAs('documentosSolicitud', $request->file('indetificacion'), $documentoidentificacion);
            SeerSolicitante::where('id_solicitud', $data['id'])->update(['documentoIdentificacion' => $documentoidentificacion]);
        }
            DB::commit();
            session()->forget(['citados_edicion_new', 'citados_edicion_delete']);
            if ($request->input('toquen') === 'iniciar_audiencia') {
                $audiencia = Audiencias::where('id_solicitud', $data["id"])
                        ->orderBy('fecha', 'desc')
                        ->first();

                $audienciaId = $data['audiencia_id'] ?? ($audiencia->id ?? null);

                $url = route('inicioAudiencia', ['id' => $audiencia->id_solicitud, 'estatus' => 'Confirmado']);
                if (!is_null($audienciaId)) {
                    $url .= '?audiencia_id=' . urlencode((string)$audienciaId);
                }
                return redirect($url);
            }

            if($data["esAudiencia"] == 'No'){
                return redirect()->route('todas_solicitudes');
            } else {
                return redirect()->route('todas_audiencias'); 
            }

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('preserve_edit_session', true);
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function solicitante_edicion(Request $request){
        $data = $request->all();

        //Se va asignar el conciliador y la sala
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $fecha_actual = date('Y-m-d');

        $isAudiencia = '';

        $motivosDelete = session('motivos_edicion_delete', []);
        if (!empty($motivosDelete)) {
            SeerMotivo::whereIn('id', $motivosDelete)->delete();
        }
        session()->forget('motivos_edicion_delete');

        SeerPerGeneral::where('id', $data["id"])
        ->update(['actividad' => $data["actividad_economica"],'id_rama' => $data["ramaIndustrial"], 'fecha_confirmacion' => $fecha_actual,]);

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $data["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }

        //Actualizar SEER SOLICTUD
        SeerSolicitante::where('id_solicitud', $data["id"])
        ->update([/*'tipo_persona' => $data["tipo_persona_solicitante"],*/ 
            'curp'                  => $data["curp_solicitante"],
            //'rfc'                   => $data["rfc_solicitante"],
            'nombre'                => $data["nombre_solicitante"],
            'sexo'                  => $data["sexo_solicitante"],
            'nacionalidad'          => $data["nacionalidad_solicitante"],
            //'estado'                => $data["estado_solicitante"],
            'email'                 => $data["email_solicitante"],
            'fecha_nacimiento'      => $data["fecha_nacimiento_solicitante"],
            'edad'                  => $data["edad_solicitante"],
            'telefono1'             => $data["telefono1_solicitante"],
            'traductor'             => $data["traductor_solicitante"],
            'lenguaje'              => $data["lenguaje_solicitante"],
            'discapacidad'          => $data["discapacidad_solicitante"],
            'tipo_discapacidad'     => $data["disc_solicitante"],
            'tipo_vialidad'         => $data["tipo_vialidad"],
            'calle'                 => $data["calle_solicitante"],
            'num_ext'               => $data["num_ext_solicitante"],
            'num_int'               => $data["num_int_solicitante"],
            'codigo_postal'         => $data["codigo_postal_solicitante"],
            'referencia'            => $data["referencia_solicitante"],
            'colonia'               => $data["colonia_solicitante"],
            'calle2'                => $data["calle2_solicitante"],
            'calle3'                => $data["calle3_solicitante"],
            'municipio_domicilio'   => $data["municipio_solicitante"],
            'puesto'                => $data["puesto"],
            'pago'                  => $data["pago"],
            'periodo_pago'          => $data["periodo_pago"],
            'fecha_ingreso'         => $data["fecha_ingreso"],
            'fecha_salida'          => $data["fecha_salida"],
            'jornada'               => $data["jornada"],
            'estado_domicilio'      => $data["estado_solicitante"],
            'horas_semana'          => $data["horas_semana"],
            'descripcionSolicitud'  => $data["descripcionSolicitud"],
        ]);

        //Opcionales
        if(isset($data["telefono2"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['telefono2' => $data["telefono2_solicitante"] ]);
        }
        if(isset($data["num_int"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['num_int' => $data["num_int_solicitante"] ]);
        }
        if(isset($data["nss"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['nss' => $data["nss"] ]);
        }
        if(isset($data["rfc"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['rfc' => $data["rfc_solicitante"] ]);
        }
        if(isset($data["referencia"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['referencia' => $data["referencia_solicitante"] ]);
        }
        if(isset($data["calle2"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle2' => $data["calle2_solicitante"] ]);
        }
        if(isset($data["calle3"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle3' => $data["calle3_solicitante"] ]);
        }

        //Citados
        SeerCitados::where('id_solicitud',$data["id"])->delete();
        $cont = count($data["colonia_citado"]);
        for($i = 0; $i < $cont; $i++) {

            $foto1 = $data["imagen_domicilio1"][$i] ?? 'Sin documento';
            $foto2 = $data["imagen_domicilio2"][$i] ?? 'Sin documento';
        
            if ($request->hasFile("foto1.$i")) {
                $file = $request->file("foto1")[$i];
                $foto1 = $data["id"] . "-citado_foto1_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                Storage::putFileAs('documentosSolicitud', $file, $foto1);
            }
        
            if ($request->hasFile("foto2.$i")) {
                $file = $request->file("foto2")[$i];
                $foto2 = $data["id"] . "-citado_foto2_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                Storage::putFileAs('documentosSolicitud', $file, $foto2);
            }
            $data_insert=array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia_citado"][$i],
                'cp'                => $data["cp_citado"][$i],
                'n_ext'             => $data["n_ext_citado"][$i],
                'n_int'             => $data["n_int_citado"][$i],
                'calle'             => $data["n_int_citado"][$i],
                'tipo_vialidad'     => $data["vialidad_citado"][$i],
                'referencia'        => $data["referencia_citado"][$i],
                'municipio_citado'  => $data["municipio_citado"][$i],
                'tipo_persona'      => $data["tipo_persona_citado"][$i],
                'nombre'            => $data["nombre_citado"][$i],
                'notificacion'      => $data["notificacion"][$i],
                'primer_apellido'   => $data["primer_apellido"][$i] ?? null,
                'segundo_apellido'  => $data["segundo_apellido"][$i] ?? null,
                'calle'             => $data["calle_citado"][$i],
                'calle1'            => $data["calle1_citado"][$i],
                'calle2'            => $data["calle2_citado"][$i],
                'curp'              => $data["curp_citado"][$i] ?? null,
                'rfc'               => $data["rfc_citado"][$i],
                'estado_citado'     => $data["estado_citado"][$i],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2,
                'resulte_responsable' => $data['resulte_responsable'][$i] ?? 'No',
            );
            
            if(isset($data["traductor"])){
                $val = is_array($data["traductor"]) ? ($data["traductor"][$i] ?? null) : $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $data_insert["traductor"] = $requires ? 1 : 0;
                $data_insert["lenguaje"]  = is_array($data["lenguaje"]) ? ($data["lenguaje"][$i] ?? null) : ($data["lenguaje"] ?? null);
            }
            if(isset($data["calle1"])){
                SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle1' => $data["calle1_citado"] ]);
            }
            if(isset($data["calle2"])){
                SeerSolicitante::where('id_solicitud', $data["id"])->update(['calle2' => $data["calle2_citado"] ]);
            }
            SeerCitados::create($data_insert);
        }

        $solActual = SeerSolicitante::where('id_solicitud', $data['id'])->first();
        $curpBase = $data['curp_solicitante'] ?? ($data['curp'] ?? ($solActual->curp ?? ('solicitud_' . $data['id'])));

        if ($request->hasFile('documentoCurp')) {
            $prev = $solActual->documentoCurp ?? null;
            $documento = $curpBase . '_CURP_' . time() . '.pdf';
            Storage::putFileAs('documentosSolicitud', $request->file('documentoCurp'), $documento);
            SeerSolicitante::where('id_solicitud', $data['id'])->update(['documentoCurp' => $documento]);

            if ($prev && $prev !== 'Sin documento') {
                Storage::delete('documentosSolicitud/' . $prev);
            }
        }

        if ($request->hasFile('documentoIdentificacion')) {
            $prev = $solActual->documentoIdentificacion ?? null;
            $documentoidentificacion = $curpBase . '_Identificacion_' . time() . '.pdf';
            Storage::putFileAs('documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion);
            SeerSolicitante::where('id_solicitud', $data['id'])->update(['documentoIdentificacion' => $documentoidentificacion]);

            if ($prev && $prev !== 'Sin documento') {
                Storage::delete('documentosSolicitud/' . $prev);
            }
        }
        
        SeerPerGeneral::find($data["id"])->update(['estatus' => 'Pendiente']);

        return redirect()->route('mis_solicitudes'); 
    }

    public function solicitud_confirmar(Request $request) {
        // 1. Validación rápida inicial usando exists() indexado
        $id_solicitud = $request->input('id');
        if (Audiencias::where('id_solicitud', $id_solicitud)->exists()) {
            return back()->withErrors('Esta solicitud ya ha sido confirmada o se está procesando actualmente.');
        }

        $id_user = auth()->id();
        $fecha_actual = Carbon::now()->toDateString();

        DB::beginTransaction();
        try {
            // 2. Carga del modelo principal (Lanza 404 si no existe)
            $delegacion = SeerPerGeneral::findOrFail($id_solicitud);
            $consecutivo = $delegacion->consecutivo;
            $delegacionUser = $delegacion->delegacion;

            // Generar NUE sin bucles directos en BD
            $NUE = $this->GeneraExpediente($consecutivo, $delegacionUser);
            
            if (SeerPerGeneral::where('NUE', $NUE)->exists()) {
                $ultimoConsecutivo = SeerPerGeneral::where('delegacion', $delegacionUser)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->max('consecutivo');
                    
                $consecutivo = $ultimoConsecutivo ? $ultimoConsecutivo + 1 : $consecutivo + 1;
                $NUE = $this->GeneraExpediente($consecutivo, $delegacionUser);
            }

            // 3. Eliminación de motivos en bloque (Bulk Delete)
            $motivosDelete = session('motivos_edicion_delete', []);
            if (!empty($motivosDelete)) {
                SeerMotivo::whereIn('id', $motivosDelete)->delete();
            }
            session()->forget('motivos_edicion_delete');

            // 4. Actualización optimizada del modelo principal
            $userToSet = $delegacion->user_id ?: $id_user;
            $delegacion->update([
                'user_id'            => $userToSet,
                'consecutivo'        => $consecutivo,
                'NUE'                => $NUE,
                'actividad'          => $request->input('actividad_economica'),
                'id_rama'            => $request->input('ramaIndustrial'),
                'fecha_confirmacion' => $fecha_actual,
                'pendiente_firma'    => 'Si',
            ]);

            // 5. Bulk Insert de Motivos
            $motivosSolicitud = $request->input('motivo_solicitud', []);
            if (!empty($motivosSolicitud)) {
                $timestamp = Carbon::now();
                $motivosInsert = array_map(function($motivoId) use ($id_solicitud, $timestamp) {
                    return [
                        'id_solicitud' => $id_solicitud,
                        'id_motivo'    => $motivoId,
                        'created_at'   => $timestamp,
                        'updated_at'   => $timestamp
                    ];
                }, $motivosSolicitud);
                
                SeerMotivo::insert($motivosInsert);
            }

            // 6. Preparación de datos del Solicitante en una sola operación
            $solActual = SeerSolicitante::where('id_solicitud', $id_solicitud)->first();
            
            $datosSolicitante = [
                'curp'                 => $request->input('curp_solicitante'),
                'nombre'               => $request->input('nombre_solicitante'),
                'sexo'                 => $request->input('sexo_solicitante'),
                'nacionalidad'         => $request->input('nacionalidad_solicitante'),
                'email'                => $request->input('email_solicitante'),
                'fecha_nacimiento'     => $request->input('fecha_nacimiento_solicitante'),
                'edad'                 => $request->input('edad_solicitante'),
                'telefono1'            => $request->input('telefono1_solicitante'),
                'traductor'            => $request->input('traductor_solicitante'),
                'lenguaje'             => $request->input('lenguaje_solicitante'),
                'discapacidad'         => $request->input('discapacidad_solicitante'),
                'tipo_discapacidad'    => $request->input('disc_solicitante'),
                'tipo_vialidad'        => $request->input('tipo_vialidad'),
                'calle'                => $request->input('calle_solicitante'),
                'num_ext'              => $request->input('num_ext_solicitante'),
                'num_int'              => $request->input('num_int_solicitante'),
                'codigo_postal'        => $request->input('codigo_postal_solicitante'),
                'referencia'           => $request->input('referencia_solicitante'),
                'colonia'              => $request->input('colonia_solicitante'),
                'calle2'               => $request->input('calle2_solicitante'),
                'calle3'               => $request->input('calle3_solicitante'),
                'municipio_domicilio'  => $request->input('municipio_solicitante'),
                'puesto'               => $request->input('puesto'),
                'pago'                 => $request->input('pago'),
                'periodo_pago'         => $request->input('periodo_pago'),
                'fecha_ingreso'        => $request->input('fecha_ingreso'),
                'fecha_salida'         => $request->input('fecha_salida'),
                'jornada'              => $request->input('jornada'),
                'identificacion'       => $request->input('tipoIdentificacion'),
                'num_identificacion'   => $request->input('numeroIdentificacion'),
                'estado_domicilio'     => $request->input('estado_solicitante'),
                'horas_semana'         => $request->input('horas_semana'),
                'descripcionSolicitud' => $request->input('descripcionSolicitud'),
            ];

            // Combinación de campos opcionales usando operadores null coalescing de PHP corto
            if ($request->has('telefono2_solicitante')) $datosSolicitante['telefono2'] = $request->input('telefono2_solicitante');
            if ($request->has('nss'))                   $datosSolicitante['nss'] = $request->input('nss');
            if ($request->has('rfc_solicitante'))       $datosSolicitante['rfc'] = $request->input('rfc_solicitante');

            // 7. Almacenamiento eficiente de archivos de identificación
            $curpBase = $request->input('curp_solicitante') ?: ($solActual->curp ?? 'solicitud_' . $id_solicitud);
            $time = time();

            if ($request->hasFile('documentoCurp')) {
                $documento = "{$curpBase}_CURP_{$time}.pdf";
                Storage::putFileAs('documentosSolicitud', $request->file('documentoCurp'), $documento);
                $datosSolicitante['documentoCurp'] = $documento;
                if ($solActual && $solActual->documentoCurp && $solActual->documentoCurp !== 'Sin documento') {
                    Storage::delete("documentosSolicitud/{$solActual->documentoCurp}");
                }
            }

            if ($request->hasFile('documentoIdentificacion')) {
                $documentoidentificacion = "{$curpBase}_Identificacion_{$time}.pdf";
                Storage::putFileAs('documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion);
                $datosSolicitante['documentoIdentificacion'] = $documentoidentificacion;
                if ($solActual && $solActual->documentoIdentificacion && $solActual->documentoIdentificacion !== 'Sin documento') {
                    Storage::delete("documentosSolicitud/{$solActual->documentoIdentificacion}");
                }
            }

            SeerSolicitante::where('id_solicitud', $id_solicitud)->update($datosSolicitante);

            // 8. Determinación de la Audiencia
            $notificaciones = $request->input('notificacion', []);
            $tipo_notificacion = $notificaciones[0] ?? 'Trabajador';
            $numero_audiencia = $this->GeneraAudiencia($id_solicitud);
            
            // Evitamos que falle si no encuentra el registro del conciliador
            $numero_audiencias = SeerPerConciliador::find($id_solicitud);
            $num_audi = ($numero_audiencias->numero_audiencias ?? 0) + 1;

            $Audiencia = $this->ObtenerAudiencia($delegacion->delegacion, $tipo_notificacion);

            if ($Audiencia instanceof \Illuminate\Http\JsonResponse) {
                DB::rollBack();
                return back()->withErrors('No hay conciliadores disponibles en la delegación para asignar audiencia.');
            }

            // Diccionario de mapeo de salas directo
            $salasMapeo = [
                45 => "Sala 2", 39 => "Sala 3", 14 => "Sala 4", 42 => "Sala 5",
                38 => "Sala 6", 54 => "Sala 7", 36 => "Sala 8", 33 => "Sala 8",
                35 => "Sala 9", 41 => "Sala 10", 2437 => "Sala 11", 2438 => "Sala 12"
            ];
            $sala = $salasMapeo[(int)$Audiencia[3]] ?? "Pendiente";

            // Cambiado a un formato limpio usando Carbon para evitar discrepancias de zona horaria
            $fecha_audiencia = Carbon::parse($Audiencia[0])->addDays(7)->toDateString();

            $audiencia_insert = [
                'id_solicitud'     => $id_solicitud,
                'numero_audiencia' => $num_audi,
                'folio_audiencia'  => $numero_audiencia[0],
                'fecha'            => $Audiencia[0],
                'proxima_audiencia'=> $fecha_audiencia,
                'hora'             => $Audiencia[1],
                'id_conciliador'   => $Audiencia[3],
                'sala'             => $sala,
                'delegacion'       => $delegacion->delegacion,
                'estatus'          => 'Pendiente'
            ];

            if ($delegacion->tipo_solicitud == 2 && $solActual) {
                $audiencia_insert["poder_id"] = $solActual->poder_id;
            }

            $audienciaCreated = Audiencias::create($audiencia_insert);

            // Actualización final de estatus de la delegación
            $estatusGeneral = ['conciliador_id' => $Audiencia[3], 'estatus' => 'Confirmado'];
            if ($tipo_notificacion === 'Trabajador') {
                $estatusGeneral['pendiente_firma'] = 'Si';
            }
            $delegacion->update($estatusGeneral);

            // 9. Procesamiento y Limpieza de Citados con Bulk Insert optimizado
            SeerCitados::where('id_solicitud', $id_solicitud)->delete();
            
            $citadosInsert = [];
            $coloniasCitados = $request->input('colonia_citado', []);
            $cont = count($coloniasCitados);

            // Cacheamos los archivos del request fuera del loop para optimizar memoria
            $fotos1_files = $request->file('foto1', []);
            $fotos2_files = $request->file('foto2', []);

            for ($i = 0; $i < $cont; $i++) {
                $foto1 = $request->input("imagen_domicilio1.{$i}", 'Sin documento');
                $foto2 = $request->input("imagen_domicilio2.{$i}", 'Sin documento');

                if (isset($fotos1_files[$i])) {
                    $file = $fotos1_files[$i];
                    $foto1 = "{$id_solicitud}-citado_foto1_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                    Storage::putFileAs('documentosSolicitud', $file, $foto1);
                }

                if (isset($fotos2_files[$i])) {
                    $file = $fotos2_files[$i];
                    $foto2 = "{$id_solicitud}-citado_foto2_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                    Storage::putFileAs('documentosSolicitud', $file, $foto2);
                }

                // Sanitización del booleano del Traductor
                $traductorVal = 0;
                if ($request->has('traductor')) {
                    $traductorInput = $request->input('traductor');
                    $val = is_array($traductorInput) ? ($traductorInput[$i] ?? null) : $traductorInput;
                    $traductorVal = in_array($val, ['Si', '1', 1, 'on', true], true) ? 1 : 0;
                }

                $lenguajeInput = $request->input('lenguaje');
                $lenguajeVal = is_array($lenguajeInput) ? ($lenguajeInput[$i] ?? null) : $lenguajeInput;

                $citadosInsert[] = [
                    'id_solicitud'        => $id_solicitud,
                    'colonia'             => $coloniasCitados[$i],
                    'cp'                  => $request->input("cp_citado.{$i}"),
                    'n_ext'               => $request->input("n_ext_citado.{$i}"),
                    'n_int'               => $request->input("n_int_citado.{$i}"),
                    'calle'               => $request->input("calle_citado.{$i}"),
                    'tipo_vialidad'       => $request->input("vialidad_citado.{$i}"),
                    'referencia'          => $request->input("referencia_citado.{$i}"),
                    'municipio_citado'    => $request->input("municipio_citado.{$i}"),
                    'tipo_persona'        => $request->input("tipo_persona_citado.{$i}"),
                    'nombre'              => $request->input("nombre_citado.{$i}"),
                    'notificacion'        => $notificaciones[$i] ?? null,
                    'primer_apellido'     => $request->input("primer_apellido.{$i}"),
                    'segundo_apellido'    => $request->input("segundo_apellido.{$i}"),
                    'calle1'              => $request->input("calle1_citado.{$i}"),
                    'calle2'              => $request->input("calle2_citado.{$i}"),
                    'curp'                => $request->input("curp_citado.{$i}"),
                    'rfc'                 => $request->input("rfc_citado.{$i}"),
                    'estado_citado'       => $request->input("estado_citado.{$i}"),
                    'imagen_domicilio1'   => $foto1,
                    'imagen_domicilio2'   => $foto2,
                    'resulte_responsable' => $request->input("resulte_responsable.{$i}", 'No'),
                    'audiencia_id'        => $audienciaCreated->id,
                    'traductor'           => $traductorVal,
                    'lenguaje'            => $lenguajeVal,
                    'created_at'          => Carbon::now(),
                    'updated_at'          => Carbon::now()
                ];
            }

            SeerCitados::insert($citadosInsert);

            // 10. GENERACIÓN ÚNICA DEL PDF (Eliminamos el doble renderizado)
            $solicitud   = $delegacion;
            $solicitante = $solActual;
            $citados = collect($citadosInsert)->map(function($item) {
                return (object) $item;
            });

            $pdf = \PDF::loadView('PDF/Solicitudes/acuseConfirmacion', compact('id_solicitud','solicitud','solicitante','citados'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

            // Obtenemos el output una sola vez para el Mail
            $pdfContent = $pdf->output();

            // 11. Envío de Notificación por Correo
            $userMailData = [
                'nombre'  => (string) $request->input('nombre_solicitante'),
                'fecha'   => Carbon::now()->format('d-m-Y'),
                'email'   => 'irvinsbm@gmail.com', // Mantengo tu valor estático de pruebas
                'id'      => $id_solicitud,
                'mensaje' => "Tu solicitud ha sido confirmada exitosamente.",
            ];

            //Mail::to($userMailData['email'])->send(new MailAceptacion($pdfContent, $userMailData));

            DB::commit();
            session()->forget(['citados_edicion_new', 'citados_edicion_delete']);
            
            return redirect()->route('solicitudes_pendientes');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('preserve_edit_session', true);
            return back()->withErrors('Error al confirmar solicitud: ' . $e->getMessage());
        }
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
        else if($delegacion == "Sahuayo"){
            $del = "SAH";
        }
        else if($delegacion == "Lázaro Cárdenas"){
            $del = "LAZ";
        }
        //contar el numero de ceros
        $numeroConCeros = str_pad($id, 5, "0", STR_PAD_LEFT);
        $folio = $del."/SOL"."/".$año_actual."/".$numeroConCeros;
    
        return $folio;
    }

    public function cancelar_edicion(Request $request){
        session()->forget(['citados_edicion_new', 'citados_edicion_delete', 'motivos_edicion_delete']);
        
        if ($request->has('id')) {
            $id = $request->input('id');
            session()->forget("audiencia_data_{$id}");
        }
        
        $redirectTo = $request->input('redirect_to');
        
        if ($redirectTo) {
            return redirect()->route($redirectTo);
        }
        
        return back();
    }

    public function agregar_citado_edicion(Request $request){
        $data = $request->all();
        $imagen_domicilio1 = "Sin documento";
        $imagen_domicilio2 = "Sin documento";

        $municipio_input = $data['municipio_citado'] ?? null;
        $estado_input = $data['estado_citado'] ?? null;
        $municipioId = is_array($municipio_input) ? ($municipio_input[0] ?? null) : $municipio_input;
        $estadoId = is_array($estado_input) ? ($estado_input[0] ?? null) : $estado_input;

        $traductor_input = $data['traductor'] ?? 'No';
        $lenguaje_input = $data['lenguaje'] ?? null;
        $traductorVal = is_array($traductor_input) ? ($traductor_input[0] ?? 'No') : $traductor_input;
        $lenguajeVal = is_array($lenguaje_input) ? ($lenguaje_input[0] ?? null) : $lenguaje_input;

        if ((trim($traductorVal) === 'Si' || trim($traductorVal) === 'sí') && empty($lenguajeVal)) {
            return back()->withErrors(['citados' => 'Debe especificar el lenguaje cuando el citado requiere traductor.'])->withInput();
        }

        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $data["id"] . "-domicilio_Citado1_" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $data["id"] . "-domicilio_Citado2_" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;

        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $municipioId,
            'estado_citado'     => $estadoId,
            'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2,
        );
        $data_insert["notificacion"] =  $data["notificacion"];

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data['traductor'])){
            $requires = trim($traductorVal) === 'Si' || trim($traductorVal) === 'sí';
            $data_insert["traductor"] =  $requires ? 1 : 0;
            $data_insert["lenguaje"]  =  $requires ? ($lenguajeVal ?? null) : null;
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        /*if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }*/
        /*if(isset($data["tipo"])){
            $data_insert["tipo_persona"] =  $data["tipo"];
        }*/
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        /*if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["estado_solicitante"])){
            $data_insert["estado_solicitante"] =  $data["estado_solicitante"];
        }*/
        if(isset($data["municipio_citado"])){
            $data_insert["municipio_citado"] =  $data["municipio_citado"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        
            if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                $data_insert["nombre"] = $data["razon"];
            }
        
            if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                $data_insert["nombre"] = $data["nombre"];
            }
        }
        
    $municipio = Municipios::find($municipioId);
    $estado = Estados::find($estadoId);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        // Detectar robustamente si se indicó "Quien resulte responsable"
        $isResulte = false;
        if (isset($data["responsable"])) {
            $val = trim($data["responsable"]);
            $valLower = mb_strtolower($val, 'UTF-8');
            if ($valLower === 'si' || $valLower === 'sí') {
                $isResulte = true;
            }
        }

        $normalCitado = $data_insert;
        $normalCitado['resulte_responsable'] = 'No';
        // SeerCitados::create($normalCitado);
        
        // Guardar en sesión
        $normalCitado['id'] = 'new_' . uniqid();
        $normalCitado['is_new'] = true;
        $citadosNew = session('citados_edicion_new', []);
        $citadosNew[] = $normalCitado;

        if ($isResulte) {
            $special = $data_insert;
            $special["nombre"] =  "REPRESENTANTE LEGAL DE: QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN " .
             $data["calle"] . ", NÚMERO " . $data["exterior"];
            if (!empty($data["interior"])) {
                $special["nombre"] .= " INT. " . $data["interior"];
            }
            $special["nombre"] .= " COLONIA " . $data["colonia"] . ", " . $municipioNombre . ", " . $estadoNombre . ", C.P. " . $data["cp"] . ".";
            $special['resulte_responsable'] = 'Si';
 
            $special['primer_apellido'] = null;
            $special['segundo_apellido'] = null;

            $direccionNombre = $special["nombre"];
            $existe = SeerCitados::where('id_solicitud', $data['id'])
                        ->where('nombre', $direccionNombre)
                        ->where('resulte_responsable', 'Si')
                        ->exists();
            if (!$existe) {
                // SeerCitados::create($special);
                $special['id'] = 'new_' . uniqid();
                $special['is_new'] = true;
                $citadosNew[] = $special;
            }
        }
        
        session(['citados_edicion_new' => $citadosNew]);
        session()->flash('preserve_edit_session', true);

        return back()->with('success', 'Citado agregado. Guarde los cambios para confirmar.');
    }

    public function borrar_citado_edicion(Request $request){
        $data = $request->all();
        $id = $data["borrar"];

        if (strpos($id, 'new_') === 0) {
             $citadosNew = session('citados_edicion_new', []);
             $citadosNew = array_filter($citadosNew, function($c) use ($id) {
                 return $c['id'] !== $id;
             });
             session(['citados_edicion_new' => $citadosNew]);
        } else {
             // SeerCitados::find($data["borrar"])->delete();
             $deleted = session('citados_edicion_delete', []);
             $deleted[] = $id;
             session(['citados_edicion_delete' => $deleted]);
        }
        session()->flash('preserve_edit_session', true);

        return back()->with('success', 'Citado borrado correctamente. Guarde los cambios para confirmar.');
    }

    public function notificaciones(){
        $user = auth()->user(); // Obtenemos el usuario directamente sin buscarlo por ID
        $userRole = $user->roles->pluck('name')->all();

        // 1. Mapeo de Sedes y Oficinas de Apoyo (Consistente con tus otros módulos)
        $mapaSedes = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];

        // Determinamos las sedes a consultar según la delegación del usuario
        $sedesAconsultar = $mapaSedes[$user->delegacion] ?? [$user->delegacion];

        // 2. Construcción de la consulta optimizada
        $mis_notificaciones = SeerPerGeneral::join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
            ->leftjoin('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
            ->leftjoin('estados', 'seer_citados.estado_citado', '=', 'estados.id')
            ->select(
                'seer_general.id as id_solicitud',
                'seer_citados.id as id_citado',
                'seer_general.NUE',
                'seer_citados.nombre',
                'seer_citados.primer_apellido',
                'seer_citados.segundo_apellido',
                'seer_citados.colonia',
                'seer_citados.tipo_vialidad',
                'seer_citados.calle',
                'seer_citados.n_ext',
                'seer_citados.n_int',
                'seer_citados.municipio_citado',
                'seer_citados.estado_citado',
                'seer_citados.estatus',
                'seer_citados.tipo_notificacion',
                'municipios.nombre as municipio_nombre',
                'estados.nombre as estado_nombre',
                'seer_citados.id_notificador'
            )
            // Usamos whereIn con las sedes mapeadas para incluir oficinas de apoyo automáticamente
            ->whereIn('seer_general.delegacion', $sedesAconsultar)
            ->where('seer_citados.id_notificador', 0)
            ->where('seer_citados.notificacion', '!=', 'Trabajador')
            ->whereNotIn('seer_general.estatus', ['Pendiente', 'Prevencion'])
            ->get();
       
        if($user["delegacion"] == "Morelia"){
            $personas = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Notificador');
            })
            ->whereIn('delegacion', ["Morelia", "Zitácuaro" , "Zitácuaro"])
            ->get();
        }else if ($user["delegacion"] == "Uruapan"){
            $personas = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Notificador');
            })
            ->whereIn('delegacion', ["Uruapan", "Lázaro Cárdenas"])
            ->get();
        }else if ($user["delegacion"] == "Zamora"){
            $personas = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Notificador');
            })
            ->whereIn('delegacion', ["Zamora", "Sahuayo"])
            ->get();
        }
        else if ($user["delegacion"] == "Zitácuaro"){
            $personas = User::whereHas('roles', function ($query) {
                return $query->where('name', '=', 'Notificador');
            })
            ->where('delegacion', ["Zitácuaro"])
            ->get();
        }

        return view('notificaciones.index',compact('personas','mis_notificaciones','userRole'));
    }


    //Conciliadores en solicitudes audiencias
    public function indexA(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $fecha_actual = date('y-m-d');

        $audiencias = Audiencias::where('id_conciliador', $user->id)->where('fecha', $fecha_actual)->get();
        foreach ($audiencias as $audiencia) {
            $solicitante = SeerSolicitante::where('id_solicitud', $audiencia->id_solicitud)->first();
            $audiencia->nombre = $solicitante ? $solicitante->nombre : 'Sin solicitante';
            $expediente = SeerPerGeneral::find($audiencia->id_solicitud);
            $audiencia["NUE"] = $expediente ? $expediente->NUE : 'Sin Expediente';
            $audiencia["estatus"] = $expediente ? $expediente->estatus : 'Algo';
            $audiencia["fecha"] = date('Y-m-d', strtotime($audiencia["fecha"]));
            $audiencia["hora"] = date('H:i:s', strtotime($audiencia["hora"]));
        }

        return view('/solicitudes/indexConciliador',compact('audiencias'));
    }
    /*
    public function iniciar_audiencia($id){
        if (!session('preserve_edit_session')) {
            session()->forget("audiencia_data_{$id}");
        }

        $audiencia_id = request()->query('audiencia_id');
        if (!is_null($audiencia_id) && $audiencia_id !== '') {
            session(["audiencia_id_{$id}" => $audiencia_id]);
        }

        $audiencia = Audiencias::where('id', $audiencia_id)->first();

        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);

        $solicitudes = SeerPerGeneral::where('conciliador_id', $user->id)
            ->where(function ($query) {
                $query->where('estatus', 'Conciliacion')
                    ->orWhere('estatus', 'No conciliacion')
                    ->orWhere('estatus', 'Archivado por incomparecencia')
                    ->orWhere('estatus', 'Reagendada')
                    ->orWhere('estatus', 'Incompetencia')
                    ->orWhere('estatus', 'Confirmado');
            })
            ->get();
        
        $solicitud = SeerPerGeneral::find($id); //obtiene todo de seergeneral
        $conciliador = User::select('id','name')->where('id', $solicitud->conciliador_id)->first();

        $NUE = $solicitud->NUE;
        if($NUE === NULL){
            $NUE = 'Sin NUE';
        }

        $tipo_solicitud = $solicitud->tipo_solicitud; //obtiene el tipo de solicitud
        $audienciaId = Audiencias::where('id_solicitud', $solicitud->id)->first()->id;
        
        $fechaConfirmacion = SeerPerGeneral::where('id', $id)->value('fecha_confirmacion');
        if(is_null($fechaConfirmacion)) {
            $fechaConfirmacion = now();
            $fechaConfirmacion = $fechaConfirmacion->format('Y-m-d');
        }

        $allCentro = 1;
        $hasAudienciaID = 1;
        $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
        foreach ($citadosCentro as $citado){
            if($citado->notificacion == 'Centro'){
                $allCentro = 0;
                break;
            }
        }

        foreach ($citadosCentro as $citado){
            if($citado->audiencia_id){
                $hasAudienciaID = 0;
                break;
            }
        }


        $sessionKey = "audiencia_data_{$id}";
        if (!session()->has($sessionKey)) {
            $solicitanteDB = SeerSolicitante::where('id_solicitud', $id)->first();
            if($allCentro == 0){
                if($hasAudienciaID == 0){
                    $citadosDB = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->where('audiencia_id', $audiencia_id)->get();
                }
                else {
                    $citadosDB = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->get();
                }
            } else {
                $citadosDB = SeerCitados::where('id_solicitud', $id)->get();
            }
            
            session([$sessionKey => [
                'solicitante' => $solicitanteDB,
                'citados' => $citadosDB
            ]]);
        }

        $sessionData = session($sessionKey);
        $solicitante = $sessionData['solicitante'];
        
        // Reconstruir $representantes desde la sesión

        if ($solicitud->tipo_solicitud == 1){
            $representantes = collect();
            foreach ($sessionData['citados'] as $citado) {
                $rep = new \stdClass();
                $rep->id = $citado->id;
                $rep->nombre = $citado->nombre;
                $rep->primer_apellido = $citado->primer_apellido;
                $rep->segundo_apellido = $citado->segundo_apellido;
                $rep->rfc = $citado->rfc;
                $rep->id_abogado = $citado->id_abogado;
                $rep->id_fisica = $citado->id_fisica;
                $rep->notificacion = $citado->notificacion;
                $rep->estatus = $citado->estatus;
                
                $rep->nombre_abogado = null;
                $rep->primero_abogado = null;
                $rep->segundo_abogado = null;
                if ($citado->id_abogado) {
                    $abogado = Poder::find($citado->id_abogado);
                    if ($abogado) {
                        $rep->nombre_abogado = $abogado->nombres_patronal;
                        $rep->primero_abogado = $abogado->primer_apellido_patronal;
                        $rep->segundo_abogado = $abogado->segundo_apellido_patronal;
                    }
                }
                
                $rep->nombre_fisica = null;
                $rep->primer_fisica = null;
                $rep->segundo_fisica = null;
                if ($citado->id_fisica) {
                    $fisica = PersonaFisica::find($citado->id_fisica);
                    if ($fisica) {
                        $rep->nombre_fisica = $fisica->nombre;
                        $rep->primer_fisica = $fisica->primer_apellido;
                        $rep->segundo_fisica = $fisica->segundo_apellido;
                    }
                }
                
                $representantes->push($rep);
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $citados = $sessionData['citados'];
        }
        
        $abogados = Poder::all();
        //SeerPerGeneral::find($id)->update(['conciliador' => $user->id, 'estatus' => 'Confirmado']);
        $estados        = Estados::all();
        $municipios     = Municipios::all();
        if($tipo_solicitud == "1"){
            return view('/audiencias/audiencias',compact('id','audiencia_id','solicitudes','representantes','solicitante','conciliador','solicitud','abogados','estados','municipios', 'fechaConfirmacion', 'allCentro', 'NUE'));
        }
        else{
            return view('/audiencias/audienciasPatronal',compact('id','audiencia_id','solicitudes', 'citados','solicitante','conciliador','solicitud','abogados','estados','municipios', 'fechaConfirmacion', 'allCentro', 'NUE', 'audiencia'));
        }
    }
    */
    public function iniciar_audiencia($id) {
        if (!session('preserve_edit_session')) {
            session()->forget("audiencia_data_{$id}");
        }

        $audiencia_id = request()->query('audiencia_id');
        if (!is_null($audiencia_id) && $audiencia_id !== '') {
            session(["audiencia_id_{$id}" => $audiencia_id]);
        }

        $audiencia = Audiencias::where('id', $audiencia_id)->first();
        $id_usuario = auth()->id();

        // 1. Optimizamos estados trayendo solo lo necesario
        $solicitudes = SeerPerGeneral::where('conciliador_id', $id_usuario)
            ->whereIn('estatus', ['Conciliacion', 'No conciliacion', 'Archivado por incomparecencia', 'Reagendada', 'Incompetencia', 'Confirmado'])
            ->get();
        
        $solicitud = SeerPerGeneral::findOrFail($id); 
        $conciliador = User::select('id','name')->where('id', $solicitud->conciliador_id)->first();

        $NUE = $solicitud->NUE ?? 'Sin NUE';
        $tipo_solicitud = $solicitud->tipo_solicitud;
        
        // Evitamos crash si no hay audiencias previas
        $audiencia_prev = Audiencias::where('id_solicitud', $solicitud->id)->first();
        $audienciaId = $audiencia_prev ? $audiencia_prev->id : null;
        
        $fechaConfirmacion = $solicitud->fecha_confirmacion ?? now()->toDateString();

        // 2. Optimización de Flags con métodos nativos de Colecciones (Cero bucles for pesados)
        $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
        
        $allCentro = $citadosCentro->contains('notificacion', 'Centro') ? 0 : 1;
        $hasAudienciaID = $citadosCentro->contains(function($value) { return !is_null($value->audiencia_id); }) ? 0 : 1;

        $sessionKey = "audiencia_data_{$id}";
        if (!session()->has($sessionKey)) {
            $solicitanteDB = SeerSolicitante::where('id_solicitud', $id)->first();
            
            if ($allCentro == 0) {
                $queryCitados = SeerCitados::where('id_solicitud', $id)
                    ->where('notificacion', 'Centro')
                    ->where('tipo_notificacion', '!=', 'Multa');
                    
                if ($hasAudienciaID == 0) {
                    $queryCitados->where('audiencia_id', $audiencia_id);
                }
                $citadosDB = $queryCitados->get();
            } else {
                $citadosDB = SeerCitados::where('id_solicitud', $id)->get();
            }
            
            session([$sessionKey => [
                'solicitante' => $solicitanteDB,
                'citados' => $citadosDB
            ]]);
        }

        $sessionData = session($sessionKey);
        $solicitante = $sessionData['solicitante'];
        
        // 3. SOLUCIÓN AL PROBLEMA N+1: Traer abogados y físicas vinculados en bloque con Eager Loading manual
        if ($solicitud->tipo_solicitud == 1) {
            $representantes = collect();
            
            // Extraemos todos los IDs únicos para buscarlos en una sola consulta SQL en lugar de usar un bucle
            $abogadosIds = collect($sessionData['citados'])->pluck('id_abogado')->filter()->unique();
            $fisicasIds = collect($sessionData['citados'])->pluck('id_fisica')->filter()->unique();

            $abogadosCargados = $abogadosIds->isNotEmpty() ? Poder::whereIn('idAbogado', $abogadosIds)->get()->keyBy('idAbogado') : collect();
            $fisicasCargadas = $fisicasIds->isNotEmpty() ? PersonaFisica::whereIn('id', $fisicasIds)->get()->keyBy('id') : collect();

            foreach ($sessionData['citados'] as $citado) {
                $rep = new \stdClass();
                $rep->id = $citado->id;
                $rep->nombre = $citado->nombre;
                $rep->primer_apellido = $citado->primer_apellido;
                $rep->segundo_apellido = $citado->segundo_apellido;
                $rep->rfc = $citado->rfc;
                $rep->id_abogado = $citado->id_abogado;
                $rep->id_fisica = $citado->id_fisica;
                $rep->notificacion = $citado->notificacion;
                $rep->estatus = $citado->estatus;
                
                // Asignación instantánea desde memoria externa
                $abogado = $abogadosCargados->get($citado->id_abogado);
                $rep->nombre_abogado = $abogado ? $abogado->nombres_patronal : null;
                $rep->primero_abogado = $abogado ? $abogado->primer_apellido_patronal : null;
                $rep->segundo_abogado = $abogado ? $abogado->segundo_apellido_patronal : null;
                
                $fisica = $fisicasCargadas->get($citado->id_fisica);
                $rep->nombre_fisica = $fisica ? $fisica->nombre : null;
                $rep->primer_fisica = $fisica ? $fisica->primer_apellido : null;
                $rep->segundo_fisica = $fisica ? $fisica->segundo_apellido : null;
                
                $representantes->push($rep);
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $citados = $sessionData['citados'];
        }
        
        // 4. CRÍTICO: Eliminamos Poder::all() y Municipios::all()
        $estados = Estados::select('id', 'nombre')->get();
        
        // Solo cargamos los municipios de Michoacán (estado 16) por defecto para que la vista renderice rápido
        $municipios = Municipios::where('estado', 16)->select('id', 'nombre')->get();

        $viewName = ($tipo_solicitud == "1") ? 'audiencias.audiencias' : 'audiencias.audienciasPatronal';

        return view($viewName, compact(
            'id', 'audiencia_id', 'solicitudes', 'solicitante', 'conciliador', 
            'solicitud', 'estados', 'municipios', 'fechaConfirmacion', 'allCentro', 'NUE', 'audiencia'
        ) + ($tipo_solicitud == "1" ? compact('representantes') : compact('citados')));
    }

    public function buscar_abogados_audiencia_ajax(Request $request) {
        try {
            $buscar = $request->input('search.value');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            
            // Obtenemos la fecha de hoy en formato Y-m-d para comparar la vigencia
            $hoy = \Carbon\Carbon::now()->toDateString();

            // 1. Consulta base optimizada
            $query = Poder::select('*'); 

            $totalRegistros = $query->count();

            // CORRECCIÓN: Se cambió $q por $query y se agrupó el contenido en un closure para evitar romper los filtros
            if (!empty($buscar)) {
                $query->where(function($q) use ($buscar) {
                    $q->where('idAbogado', 'LIKE', "%{$buscar}%")
                    
                    // 1. Columnas individuales de la Parte Patronal
                    ->orWhere('nombres_patronal', 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_apellido_patronal', 'LIKE', "%{$buscar}%")
                    ->orWhere('segundo_apellido_patronal', 'LIKE', "%{$buscar}%")
                    ->orWhere('rfc_patronal', 'LIKE', "%{$buscar}%")
                    
                    // 2. Concatenación de la Parte Patronal con conversión explícita de Collation
                    ->orWhere(\DB::raw("CONVERT(CONCAT_WS(' ', nombres_patronal, primer_apellido_patronal, segundo_apellido_patronal) USING utf8mb4)"), 'LIKE', "%{$buscar}%")
                    
                    // 3. Columnas individuales del Representante Legal
                    ->orWhere('nombre_representante', 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_apellido_representante', 'LIKE', "%{$buscar}%")
                    ->orWhere('segundo_apellido_representante', 'LIKE', "%{$buscar}%")
                    
                    // 4. Concatenación del Representante Legal con conversión explícita de Collation
                    ->orWhere(\DB::raw("CONVERT(CONCAT_WS(' ', nombre_representante, primer_apellido_representante, segundo_apellido_representante) USING utf8mb4)"), 'LIKE', "%{$buscar}%");
                });
            }

            $registrosFiltrados = $query->count();
            $abogados = $query->offset(intval($start))->limit(intval($length))->get();

            $data = [];
            foreach ($abogados as $abogado) {
                $idActual = $abogado->idAbogado ?? $abogado->id;

                // Limpieza y unión de cadenas de nombres
                $nombrePatronal = trim(($abogado->nombres_patronal ?? '') . ' ' . ($abogado->primer_apellido_patronal ?? '') . ' ' . ($abogado->segundo_apellido_patronal ?? ''));
                $rfcPatronal = trim(($abogado->rfc_patronal ?? ''));
                $nombreRepresentante = trim(($abogado->nombre_representante ?? '') . ' ' . ($abogado->primer_apellido_representante ?? '') . ' ' . ($abogado->segundo_apellido_representante ?? ''));
                
                // 2. RÉPLICA EXACTA DE TUS CONDICIONES DE NEGOCIO
                $isVencido = (!is_null($abogado->fechaVigencia) && $abogado->fechaVigencia < $hoy);
                $requiereValidacion = ($abogado->estatus !== 'Validado');

                if ($isVencido) {
                    $accionHtml = '<button class="btn btn-info" onclick="editar_rol();" type="submit" name="abogado" value="' . $idActual . '" disabled>Seleccionar</button>' .
                                '<span class="ms-2 text-danger fw-semibold">Sin vigencia</span>';
                } elseif ($requiereValidacion) {
                    $accionHtml = '<button class="btn btn-info" onclick="editar_rol();" type="submit" name="abogado" value="' . $idActual . '" disabled>Seleccionar</button>' .
                                '<span class="ms-2 text-danger fw-semibold">Requiere validación</span>';
                } else {
                    $accionHtml = '<button class="btn btn-info" onclick="editar_rol();" type="submit" name="abogado" value="' . $idActual . '">Seleccionar</button>' .
                                '<span class="ms-2 text-success fw-semibold">Elegible</span>';
                }

                $data[] = [
                    $idActual,
                    $nombrePatronal ?: 'Sin nombre patronal',
                    $rfcPatronal ?: 'Sin RFC',
                    $nombreRepresentante ?: 'Sin representante',
                    $accionHtml 
                ];
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalRegistros),
                "recordsFiltered" => intval($registrosFiltrados),
                "data" => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "data" => [],
                "recordsTotal" => 0,
                "recordsFiltered" => 0
            ], 500);
        }
    }

    public function guardar_audiencia_archivo(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'estatus_conciliacion'  => 'Archivado por incomparecencia',
            'numero_audiencias'     => $num_audi,
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'conclucion'            => "Archivada"
        ];
        
        SeerPerConciliador::create($data_conciliador);  

        SeerPerGeneral::find($data["id"])
        ->update([
            'fecha_terminacion' => $fecha_actual, 
            'estatus'           => 'Archivada',
            'observaciones'     => 'Archivada por falta de interés', 
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
        ->latest()
        ->first()
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'Archivada',
        ]);
   
        try {
            session()->forget(["audiencia_conclucion_data_{$data['id']}", "convenio_citados_{$data['id']}", "acta_citados_{$data['id']}", "audiencia_data_{$data['id']}", 'preserve_edit_session']);
        } catch (\Exception $e) {
        }

        return redirect()->route('todas_audiencias');
    }

    public function guardar_audiencia_archivo_parte3(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');
        $audiencia_id = $data['audiencia_id'] ?? $request->query('audiencia_id');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'estatus_conciliacion'  => 'Archivada en Audiencia',
            'numero_audiencias'     => $num_audi,
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'resolicion_primera'        => $data['primera'] ?? null,
            'resolicion_justificacion'  => $data['justificacion'] ?? null,
            'resolicion_segunda'        => $data['segunda'] ?? null,
            'conclucion'            => 'Archivada',
            'audiencia_id' => $audiencia_id
        ];
        
        SeerPerConciliador::create($data_conciliador);  

        SeerPerGeneral::find($data["id"])
        ->update([
            'fecha_terminacion' => $fecha_actual, 
            'estatus'           => 'Archivada',
            'observaciones'     => $data["observaciones"], 
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
        ->latest()
        ->first()
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'Archivada en Audiencia',
        ]);
   
        try {
            session()->forget(["audiencia_conclucion_data_{$data['id']}", "convenio_citados_{$data['id']}", "acta_citados_{$data['id']}", "audiencia_data_{$data['id']}", 'preserve_edit_session']);
        } catch (\Exception $e) {
        }

        return redirect()->route('todas_audiencias');
    }

    public function emitir_multas(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');

        $idSolicitud = $data["id"] ?? null;
        if ($idSolicitud) {
            $tipoSolicitud = SeerPerGeneral::where('id', $idSolicitud)->value('tipo_solicitud');

            $sessionKey = "audiencia_data_{$idSolicitud}";
            if (session()->has($sessionKey)) {
                $sessionData = session($sessionKey);

                if (isset($sessionData['citados'])) {
                    foreach ($sessionData['citados'] as $citado) {

                        if ((int)$tipoSolicitud === 1) {
                            SeerCitados::where('id', $citado->id)->update([
                                'id_abogado' => $citado->id_abogado,
                                'id_historial' => $citado->id_historial,
                            ]);
                        }

                        if ((int)$tipoSolicitud === 2) {
                            SeerCitados::where('id', $citado->id)->update([
                                'comparecencia' => $citado->comparecencia,
                            ]);

                            if (SeerCitados::where('id', $citado->id)->value('comparecencia') == NULL) {
                                SeerCitados::where('id', $citado->id)->update([
                                    'comparecencia' => 'No'
                                ]);
                            }
                        }
                    }
                }
            }
        }

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'estatus_conciliacion'  => 'No conciliacion',
            'numero_audiencias'     => $num_audi,
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'conclucion'            => "No conciliacion"
        ];
        
        SeerPerConciliador::create($data_conciliador);

        SeerPerGeneral::find($data["id"])
        ->update([
            'fecha_terminacion' => $fecha_actual, 
            'estatus'           => 'No conciliacion',
            'observaciones'     => 'Incomparencia de los citados',
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
        ->orderBy('id_solicitud','desc')
        ->latest()
        ->first()
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'No conciliacion',
        ]);

        //SeerCitados::where('id_solicitud', $data["id"])->where('notificacion', 'Centro')->whereIn('estatus', ['Notificada', 'Finalizado exitosamente'])->update(['tipo_notificacion' => 'Multa']);
        $solicitud = SeerPerGeneral::where('id', $data["id"])->first();

        if ($solicitud->tipo_solicitud == 1){
            $citados = SeerCitados::where('id_solicitud', $data["id"])->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->whereIn('estatus', ['Notificada', 'Finalizado exitosamente', 'Exitosa por Instructivo', 'No notificada', 'Notificada en Audiencia'])->get();
            foreach($citados as $citado){
                $tieneNotificadaEnAudiencia = $citados->where('nombre', $citado->nombre)
                                                    ->where('estatus', 'Notificada en Audiencia')
                                                    ->count() > 0;

                if ($tieneNotificadaEnAudiencia && $citado->estatus !== 'Notificada en Audiencia') {
                    continue;
                }

                $existeMulta = SeerCitados::where('id_solicitud', $data["id"])
                    ->where('tipo_notificacion', 'Multa')
                    ->where('nombre', $citado->nombre)
                    ->exists();

                if (!$existeMulta) {
                    $nuevo_citado = $citado->replicate();
                    $nuevo_citado->fecha = NULL;
                    $nuevo_citado->tipo_notificacion = 'Multa';
                    $nuevo_citado->estatus = 'Sin asignar';
                    $nuevo_citado->audiencia_id = $data['audiencia_id'] ?? request()->query('audiencia_id') ?? $citado->audiencia_id;
                    $nuevo_citado->id_notificador = 0;
                    $nuevo_citado->save();
                }
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $citados = SeerCitados::where('id_solicitud', $data["id"])->update([
                'comparecencia' => 'No',
            ]);
        }

        return redirect()->route('todas_audiencias');
    }

    public function editar_solicitud_con(Request $request) {
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();

        $solicitante = SeerSolicitante::where('id_solicitud', $data['id'])->first();
    
        $sessionKey = "audiencia_data_{$data['id']}";
        
        if (session()->has($sessionKey)) {
            $sessionData = session($sessionKey);
            $solicitante = $sessionData['solicitante'];
            
            $solicitante->curp = $data["curp"];
            $solicitante->rfc = $data["rfc"];
            $solicitante->nombre = $data["nombre"];
            $solicitante->puesto = $data["puesto"];
            $solicitante->pago = $data["pago"];
            $solicitante->periodo_pago = $data["periodo_pago"];
            $solicitante->fecha_ingreso = $data["fecha_ingreso"];
            $solicitante->fecha_salida = $data["fecha_salida"];
            $solicitante->jornada = $data["jornada"];
            $solicitante->horas_semana = $data["horas"];
            
            if(isset($data["seguro"])){
                $solicitante->nss = $data["seguro"];
            }

            $sessionData['solicitante'] = $solicitante;
            session([$sessionKey => $sessionData]);
        } else {
            //Actualizar Solicitante
            SeerSolicitante::where('id_solicitud', $data["id"])
            ->update([
                'curp'                  => $data["curp"],
                'rfc'                   => $data["rfc"],
                'nombre'                => $data["nombre"],
                'puesto'                => $data["puesto"],
                'pago'                  => $data["pago"],
                'periodo_pago'          => $data["periodo_pago"],
                'fecha_ingreso'         => $data["fecha_ingreso"],
                'fecha_salida'          => $data["fecha_salida"],
                'jornada'               => $data["jornada"],
                'horas_semana'          => $data["horas"],
            ]);

            if(isset($data["seguro"])){
                SeerSolicitante::where('id_solicitud', $data["id"])->update(['nss' => $data["seguro"] ]);
            }
        }
            
        session()->flash('preserve_edit_session', true);
        return redirect()->route('inicioAudiencia', ['id' => $data['id']]);
      
    }

    public function insertar_citados_con(Request $request) {
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
                $data = $request->all();

        if($data["tipoPersona"] == "Fisica"){
            if($data["representate"] == "No"){
                request()->validate([
                    'nombre_pF'     => 'required',
                    'primero_PF'    => 'required',
                    'segundo_Pf'    => 'required',
                    'curp_PF'       => 'required',
                    'RFC_pF'        => 'required',
                    'sexo_pf'       => 'required',
                    'giro_pF'       => 'required',
                    'correo_pF'     => 'required',
                    'telefono_PF'   => 'required',
                    'estado_pF'     => 'required',
                    'municipio_pF'  => 'required',
                    'vialidad_pF'   => 'required',
                    'vialidad_calle_pF'   => 'required',
                    'colonia_pF'   => 'required',
                    'num_ext_pF'   => 'required',
                    'cp_pF'         => 'required',
                    'documentoIne_pFSR' => 'required'
                ], $data);
            }
            else if($data["representate"] == "Si"){
                request()->validate([
                    'nombre_pF'                 => 'required',
                    'primero_PF'                => 'required',
                    'segundo_Pf'                => 'required',
                    'curp_PF'                   => 'required',
                    'RFC_pF'                    => 'required',
                    'sexo_pf'                   => 'required',
                    'giro_pF'                   => 'required',
                    'correo_pF'                 => 'required',
                    'telefono_PF'               => 'required',
                    'estado_pF'                 => 'required',
                    'municipio_pF'              => 'required',
                    'vialidad_pF'               => 'required',
                    'vialidad_calle_pF'         => 'required',
                    'colonia_pF'                => 'required',
                    'num_ext_pF'                => 'required',
                    'cp_pF'                     => 'required',
                    "nombre_representante_pF"   => 'required',
                    "primer_representante_pF"   => 'required',
                    "segundo_representante_pF"  => 'required',
                    "curp_representante_pF"     => 'required',
                    "sexo_representante_pF"     => 'required',
                    "correo_representante_pF"   => 'required',
                    "telefono_representante_pF" => 'required',
                    "tipo_documento_pF"         => 'required',
                    "fecha_expedicion_pF"       => 'required',
                    //"fecha_vigencia_pF"         => 'required',
                    "descripcion_pF"            => 'required',
                    "documentoIne_pF"           => 'required',
                    'documentoRepresentacion_pF'=> 'required',
                    'documentoPoder_pF'         => 'required'
                ], $data);
            }
        }   
        else {
            request()->validate([
                "razon"                         => 'required',
                "rfc_moral"                     => 'required',
                "giro_moral"                    => 'required',
                "estado_moral"                  => 'required',
                "municipio_moral"               => 'required',
                "vialidad_Moral"                => 'required',
                "vialidad_calleMoral"           => 'required',
                "colonia_moral"                 => 'required',
                "num_ext_moral"                 => 'required',
                "cp_moral"                      => 'required',
                "nombre_representante_Moral"    => 'required',
                "primer_Moral"                  => 'required',
                "segundo_Moral"                 => 'required',
                "curp_moral"                    => 'required',
                "sexo_Moral"                    => 'required',
                "correo_Moral"                  => 'required',
                "telefono_Moral"                => 'required',
                "tipo_Moral"                    => 'required',
                "fecha_expedicicion_Moral"      => 'required',
                //"fecha_vigencia_Moral"          => 'required',
                "descripcion_Moral"             => 'required',
                "documentoIne_Moral"            => 'required',
                "documentoRepresentacion_Moral" => 'required',
                "documentoPoder"                => 'required'
            ], $data);
        }

        //Vamos insetar los datos para la persona fisica con representante legal
        if($data["tipoPersona"] == "Fisica"){
            if($data["representate"] == "No"){
                $data_insertar = array(
                        'tipo'                      => $data["tipoPersona"],
                        'nombres_patronal'          => $data["nombre_pF"],
                        'primer_apellido_patronal'  => $data["primero_PF"],
                        'segundo_apellido_patronal' => $data["segundo_Pf"],
                        'curp_patronal'             => $data["curp_PF"],
                        'rfc_patronal'              => $data["RFC_pF"],
                        'sexo_patronal'             => $data["sexo_pf"],
                        'giroComercial'             => $data["giro_pF"],
                        'email_patronal'            => $data["correo_pF"],
                        'telefono_patronal'         => $data["telefono_PF"],
                        'estado_patronal'           => $data["estado_pF"],
                        'municipio_patronal'        => $data["municipio_pF"],
                        'tipo_vialidad_patronal'    => $data["vialidad_pF"],
                        'vialidad_patronal'         => $data["vialidad_calle_pF"],
                        'colonia_patronal'          => $data["colonia_pF"],
                        'num_ext_patronal'          => $data["num_ext_pF"],
                        'cp_patronal'               => $data["cp_pF"],
                        'estatus'                   => "Pendiente",
                        'reprecentante'             => "No",
                        'idUsuario'                 => $id_usuario,
                        'tipo_identificacion'       => $data["tipo_identificacion_pF"],
                        'num_identificacion'        => $data["num_identificacion_pF"],
						'ineDocumento'               => 'PENDIENTE',
						'anexo_documeto'             => 'Sin anexo'
                );

                // Crear primero el registro para obtener idAbogado y guardar documentos en su carpeta.
                $nuevoAbogado = Poder::create($data_insertar);
                $idAbogado = $nuevoAbogado->idAbogado;
                $carpetaAbogado = 'documentos_abogados/' . $idAbogado;

                $nombre_ine_original = $data["nombre_pF"]." ".$data["primero_PF"]." ".$data["segundo_Pf"]."-FISICA"."_IDENTIFICACION.pdf";
                $nombre_ine = $idAbogado . '_' . $nombre_ine_original;
                Storage::putFileAs(
                    $carpetaAbogado, $request->file('documentoIne_pFSR'), $nombre_ine
                );
                if(!isset($data["documentoAnexo_pFSR"])){
                    $nombre_anexo = "Sin anexo";
                }
                else{
                    $nombre_anexo_original = $data["nombre_pF"]." ".$data["primero_PF"]." ".$data["segundo_Pf"]."-FISICA"."_ANEXO.pdf";
                    $nombre_anexo = $idAbogado . '_' . $nombre_anexo_original;
                    Storage::putFileAs(
                        $carpetaAbogado, $request->file('documentoAnexo_pFSR'), $nombre_anexo
                    );
                }

                if(isset($data["num_int_pF"])){
                    $nuevoAbogado->mun_int_patronal = $data["num_int_pF"];
                }

                $nuevoAbogado->ineDocumento = $nombre_ine;
                $nuevoAbogado->anexo_documeto = $nombre_anexo;
                $nuevoAbogado->save();

                     // $nuevoAbogado ya fue creado arriba para poder nombrar carpeta/archivos.

                     $id_user_historial = Auth::id() ?? 0;
                     $historialPayload = $nuevoAbogado->toArray();
                     unset($historialPayload['idAbogado'], $historialPayload['created_at'], $historialPayload['updated_at']);
                     $historialPayload['id_abogado'] = $nuevoAbogado->idAbogado;
                     $historialPayload['id_user'] = $id_user_historial;
                     HistorialAbogado::create($historialPayload);

                     SeerCitados::find($data['id_citado_2'])->update(['id_abogado' => $nuevoAbogado->idAbogado]);
                     return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
            }
            else if($data["representate"] == "Si"){
                $data_insertar = array(
                        'nombres_patronal'          => $data["nombre_pF"],
                        'primer_apellido_patronal'  => $data["primero_PF"],
                        'segundo_apellido_patronal' => $data["segundo_Pf"],
                        'curp_patronal'             => $data["curp_PF"],
                        'rfc_patronal'              => $data["RFC_pF"],
                        'sexo_patronal'             => $data["sexo_pf"],
                        'giroComercial'             => $data["giro_pF"],
                        'email_patronal'            => $data["correo_pF"],
                        'telefono_patronal'         => $data["telefono_PF"],
                        'estado_patronal'           => $data["estado_pF"],
                        'municipio_patronal'        => $data["municipio_pF"],
                        'tipo_vialidad_patronal'    => $data["vialidad_pF"],
                        'vialidad_patronal'         => $data["vialidad_calle_pF"],
                        'colonia_patronal'          => $data["colonia_pF"],
                        'num_ext_patronal'          => $data["num_ext_pF"],
                        'cp_patronal'               => $data["cp_pF"],
                        'nombre_representante'          => $data["nombre_representante_pF"],
                        'primer_apellido_representante' => $data["primer_representante_pF"],
                        'segundo_apellido_representante'=> $data["segundo_representante_pF"],
                        'curp_representante'            => $data["curp_representante_pF"],
                        'sexo_representante'            => $data["sexo_representante_pF"],
                        'correo_representante'          => $data["correo_representante_pF"],
                        'numero_representante'          => $data["telefono_representante_pF"],
                        'tipo_documento_representante'  => $data["tipo_documento_pF"],
                        'fechaRegistro'                 => $data["fecha_expedicion_pF"],
                        //'fechaVigencia'                 => $data["fecha_vigencia_pF"],
                        'descipcion_poder'              => $data["descripcion_pF"],
                        'representacionDocumento'       => $data['documentoRepresentacion_pF'],
                        'ineDocumento'                  => $data['documentoIne_pF'],
                        'documentoPoder_pF'             => $data["documentoPoder_pF"],
                        'tipo'                          => $data["tipoPersona"],
                        'estatus'                       => "Pendiente",
                        'reprecentante'                 => "Si",
                        'idUsuario'                     => $id_usuario,
                        'tipo_identificacion'       => $data["tipo_identificacion_pFCR"],
                        'num_identificacion'        => $data["num_identificacion_pFCR"],
                );

                // Crear primero el registro para obtener idAbogado y guardar documentos en su carpeta.
                $nuevoAbogado = Poder::create($data_insertar);
                $idAbogado = $nuevoAbogado->idAbogado;
                $carpetaAbogado = 'documentos_abogados/' . $idAbogado;

                $nombre_ine_original = $data["nombre_pF"]." ".$data["primero_PF"]." ".$data["segundo_Pf"]."-FISICA"."_IDENTIFICACION.pdf";
                $nombre_ine = $idAbogado . '_' . $nombre_ine_original;
                Storage::putFileAs(
                    $carpetaAbogado, $request->file('documentoIne_pF'), $nombre_ine
                );
                $nombre_reprecentacion_original = $data["nombre_representante_pF"]." ".$data["primer_representante_pF"]." ".$data["segundo_representante_pF"]."-FISICA"."_REPRESENTACION.pdf";
                $nombre_reprecentacion = $idAbogado . '_' . $nombre_reprecentacion_original;
                Storage::putFileAs(
                    $carpetaAbogado, $request->file('documentoRepresentacion_pF'), $nombre_reprecentacion
                );
                $nombre_poder_original = $data["nombre_pF"]." ".$data["primero_PF"]." ".$data["segundo_Pf"]."-FISICA"."_PODER.pdf";
                $nombre_poder = $idAbogado . '_' . $nombre_poder_original;
                Storage::putFileAs(
                    $carpetaAbogado, $request->file('documentoPoder_pF'), $nombre_poder
                );
                if(!isset($data["documentoAnexo_pF"])){
                    $nombre_anexo = "Sin anexo";
                }
                else{
                    $nombre_anexo_original = $data["nombre_pF"]." ".$data["primero_PF"]." ".$data["segundo_Pf"]."-FISICA"."_ANEXO.pdf";
                    $nombre_anexo = $idAbogado . '_' . $nombre_anexo_original;
                    Storage::putFileAs(
                        $carpetaAbogado, $request->file('documentoAnexo_pF'), $nombre_anexo
                    );
                }

                $nuevoAbogado->ineDocumento = $nombre_ine;
                $nuevoAbogado->representacionDocumento = $nombre_reprecentacion;
                $nuevoAbogado->cedulaDocumento = $nombre_poder;
                $nuevoAbogado->anexo_documeto = $nombre_anexo;
                $nuevoAbogado->save();
                if(isset($data["num_int_pF"])){
                   $data_insertar["mun_int_patronal"] = $data["num_int_pF"];
                }
                if(isset($data["fecha_vigencia_pF"])){
                    $data_insertar["fechaVigencia"] = $data["fecha_vigencia_pF"];
                }

                // $nuevoAbogado ya fue creado arriba para poder nombrar carpeta/archivos.

                $id_user_historial = Auth::id() ?? 0;
                $historialPayload = $nuevoAbogado->toArray();
                unset($historialPayload['idAbogado'], $historialPayload['created_at'], $historialPayload['updated_at']);
                $historialPayload['id_abogado'] = $nuevoAbogado->idAbogado;
                $historialPayload['id_user'] = $id_user_historial;
                $historialReciente = HistorialAbogado::create($historialPayload);

                SeerCitados::find($data['id_citado_2'])->update(['id_abogado' => $nuevoAbogado->idAbogado, 'id_historial' => $historialReciente->id]);
                return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
            }   
        }
        else if($data["tipoPersona"] == "Moral"){
            $data_insertar = array(
                    'nombres_patronal'          => $data["razon"],
                    'primer_apellido_patronal'  => "",
                    'segundo_apellido_patronal' => "",
                    'rfc_patronal'              => $data["rfc_moral"],
                    'giroComercial'             => $data["giro_moral"],
                    'estado_patronal'           => $data["estado_moral"],
                    'municipio_patronal'        => $data["municipio_moral"],
                    'tipo_vialidad_patronal'    => $data["vialidad_Moral"],
                    'vialidad_patronal'         => $data["vialidad_calleMoral"],
                    'colonia_patronal'          => $data["colonia_moral"],
                    'num_ext_patronal'          => $data["num_ext_moral"],
                    'cp_patronal'               => $data["cp_moral"],
                    'nombre_representante'          => $data["nombre_representante_Moral"],
                    'primer_apellido_representante' => $data["primer_Moral"],
                    'segundo_apellido_representante'=> $data["segundo_Moral"],
                    'curp_representante'            => $data["curp_moral"],
                    'sexo_representante'            => $data["sexo_Moral"],
                    'correo_representante'          => $data["correo_Moral"],
                    'numero_representante'          => $data["telefono_Moral"],
                    'tipo_documento_representante'  => $data["tipo_Moral"],
                    'fechaRegistro'                 => $data["fecha_expedicicion_Moral"],
                    //'fechaVigencia'                 => $data["fecha_vigencia_Moral"],
                    'descipcion_poder'              => $data["descripcion_Moral"],
                    'representacionDocumento'       => $data['documentoRepresentacion_Moral'],
                    'ineDocumento'                  => $data['documentoIne_Moral'],
                    'cedulaDocumento'               => $data["documentoPoder"],
                    'tipo'                          => $data["tipoPersona"],
                    'estatus'                       => "Pendiente",
                    'reprecentante'                 => "Si",
                    'idUsuario'                     => $id_usuario,
                    'tipo_identificacion'           => $data["tipo_identificacion_Moral"],
                    'num_identificacion'            => $data["num_identificacion_Moral"]
            );

            // Crear primero el registro para obtener idAbogado y guardar documentos en su carpeta.
            $nuevoAbogado = Poder::create($data_insertar);
            $idAbogado = $nuevoAbogado->idAbogado;
            $carpetaAbogado = 'documentos_abogados/' . $idAbogado;

            $nombre_ine_original = $data["razon"]."-MORAL"."_IDENTIFICACION.pdf";
            $nombre_ine = $idAbogado . '_' . $nombre_ine_original;
            Storage::putFileAs(
                $carpetaAbogado, $request->file('documentoIne_Moral'), $nombre_ine
            );
            $nombre_reprecentacion_original = $data["razon"]."-MORAL"."_REPRESENTACION.pdf";
            $nombre_reprecentacion = $idAbogado . '_' . $nombre_reprecentacion_original;
            Storage::putFileAs(
                $carpetaAbogado, $request->file('documentoRepresentacion_Moral'), $nombre_reprecentacion
            );
            $nombre_poder_original = $data["razon"]."-MORAL"."_PODER.pdf";
            $nombre_poder = $idAbogado . '_' . $nombre_poder_original;
            Storage::putFileAs(
                $carpetaAbogado, $request->file('documentoPoder'), $nombre_poder
            );
            if(!isset($data["documentoAnexo"])){
                $nombre_anexo = "Sin anexo";
            }
            else{
                $nombre_anexo_original = $data["razon"]."-MORAL"."_ANEXO.pdf";
                $nombre_anexo = $idAbogado . '_' . $nombre_anexo_original;
                Storage::putFileAs(
                    $carpetaAbogado, $request->file('documentoAnexo'), $nombre_anexo
                );
            }

            $nuevoAbogado->ineDocumento = $nombre_ine;
            $nuevoAbogado->representacionDocumento = $nombre_reprecentacion;
            $nuevoAbogado->cedulaDocumento = $nombre_poder;
            $nuevoAbogado->anexo_documeto = $nombre_anexo;
            $nuevoAbogado->save();
            if(isset($data["num_int"])){
                $data_insertar["mun_int_patronal"] = $data["num_int"];
            }
            if(isset($data["fecha_vigencia_Moral"])){
                $data_insertar["fechaVigencia"] = $data["fecha_vigencia_Moral"];
            }

            // $nuevoAbogado ya fue creado arriba para poder nombrar carpeta/archivos.

            $id_user_historial = Auth::id() ?? 0;
            $historialPayload = $nuevoAbogado->toArray();
            unset($historialPayload['idAbogado'], $historialPayload['created_at'], $historialPayload['updated_at']);
            $historialPayload['id_abogado'] = $nuevoAbogado->idAbogado;
            $historialPayload['id_user'] = $id_user_historial;
            $historialReciente = HistorialAbogado::create($historialPayload);

            //SeerCitados::find($data['id_citado_2'])->update(['id_abogado' => $nuevoAbogado->idAbogado, 'id_historial' => $historialReciente->id]);
            return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
        }

        return back()->with('error', 'Tipo de persona inválido.');
    }

    public function editar_citados(Request $request){
        $data = $request->all();
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $folio = SeerCitados::find($data["id"]);
        
        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $data["id"] . "-domicilio_Citado1.jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
            $foto1 = $imagen_domicilio1;
        } else {
            $foto1 = $folio->imagen_domicilio1;
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $data["id"] . "-domicilio_Citado2.jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
            $foto2 = $imagen_domicilio2;
        } else {
            $foto2 = $folio->imagen_domicilio2;
        }
        $data_update = SeerCitados::find($data["id"])
        ->update([
            //'tipo_persona'             => $data["tipo"],
            'curp'                     => $data["curp"] ?? null,
            'rfc'                      => $data["rfc"],
            'nombre'                   => $data["nombre"],
            'primer_apellido'          => $data["primer_apellido"] ?? null,
            'segundo_apellido'         => $data["segundo_apellido"] ?? null,
            'colonia'                  => $data["colonia"],
            'cp'                       => $data["cp"],
            'calle1'                   => $data["calle1"],
            'calle2'                   => $data["calle2"],
            'n_ext'                    => $data["exterior"],
            'n_int'                    => $data["interior"],
            'tipo_vialidad'            => $data["vialidad"],
            'calle'                    => $data["calle"],
            'municipio_citado'         => $data["municipio_citado"],
            'referencia'               => $data["referencia"],
            'imagen_domicilio1'        => $foto1,
            'imagen_domicilio2'        => $foto2,
            'estado_citado'            => $data["estado_citado"],
        ]);

       
       if ($request->has('origen') && $request->origen === "previa") {
            return redirect()->route('vista_previa', ['id_solicitud' => $id]);
        }
        return redirect()->route('notificaciones');
    }

    public function seleccionar_abogado(Request $request){
        $data = $request->all();
        $id = $data["solicitud"];
        $audienciaId = $request->input('audiencia_id');

        $sessionKey = "audiencia_data_{$id}";

        $ultimoRegistro = HistorialAbogado::where('id_abogado', $data["abogado"] ?? null)->latest()->first();
        $solicitud = SeerPerGeneral::find($id);

        if ($solicitud->tipo_solicitud == 1) {
            if (session()->has($sessionKey)) {
                $sessionData = session($sessionKey);
                $citados = $sessionData['citados'];
                
                $citados = $citados->map(function ($citado) use ($data, $ultimoRegistro) {
                    if ((int)$citado->id == (int)$data["citado"]) {
                        $citado->id_abogado = $data["abogado"];
                        $citado->id_historial = $ultimoRegistro?->id;
                    }
                    return $citado;
                });

                $sessionData['citados'] = $citados;
                session([$sessionKey => $sessionData]);
            } else {
                SeerCitados::find($data["citado"])
                ->update([
                    'id_abogado'  => $data["abogado"],
                    'id_historial'=> $ultimoRegistro?->id
                ]);
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $citadoId = $data["id"] ?? null;

            if (session()->has($sessionKey)) {
                $sessionData = session($sessionKey);
                $citados = $sessionData['citados'];
                
                $citados = $citados->map(function ($citado) use ($citadoId) {
                    if ($citadoId !== null && (int)$citado->id == (int)$citadoId) {
                        $citado->comparecencia = 'Si';
                    }
                    return $citado;
                });

                $sessionData['citados'] = $citados;
                session([$sessionKey => $sessionData]);
            } else {
                if ($citadoId !== null) {
                    SeerCitados::find($citadoId)
                        ->update([
                            'comparecencia' => 'Si'
                        ]);
                }
            }
        }

        session()->flash('preserve_edit_session', true);
        return redirect()->route('inicioAudiencia', ['id' => $id, 'audiencia_id' => $audienciaId]);
        
    }

    public function guardar_comparecencia_citado(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'solicitud' => 'required',
            'audiencia_id' => 'nullable',
            'tipo_identificacion_comparecencia' => 'required|string',
            'num_identificacion_comparecencia' => 'required|string',
            'identificacion_comparecencia' => 'nullable|file|mimes:pdf|max:5120'
        ]);

        $citadoId = $request->input('id');
        $idSolicitud = $request->input('solicitud');
        $audienciaId = $request->input('audiencia_id');
        $tipoIdentificacion = $request->input('tipo_identificacion_comparecencia');
        $numIdentificacion = $request->input('num_identificacion_comparecencia');

        $docPath = null;
        if ($request->hasFile('identificacion_comparecencia')) {
            $archivo = $request->file('identificacion_comparecencia');
            $fileName = $citadoId . "_identificacion_comparecencia_" . time() . ".pdf";
            \Storage::putFileAs('documentosSolicitud', $archivo, $fileName);
            $docPath = $fileName;
        }

        $sessionKey = "audiencia_data_{$idSolicitud}";

        if (session()->has($sessionKey)) {
            $sessionData = session($sessionKey);
            
            // Verificamos si en la sesión existen los citados
            if (isset($sessionData['citados'])) {
                $citados = $sessionData['citados']->map(function ($c) use ($citadoId, $tipoIdentificacion, $numIdentificacion, $docPath) {
                    if ((int)$c->id == (int)$citadoId) {
                        $c->comparecencia = 'Si';
                        $c->tipo_identificacion_comparecencia = $tipoIdentificacion;
                        $c->num_identificacion_comparecencia = $numIdentificacion;
                        if ($docPath) {
                            $c->identificacion_comparecencia = $docPath;
                        }
                    }
                    return $c;
                });
                $sessionData['citados'] = $citados;
            }
            
            // Caso de que sea patronal/citados por otro lado
            if (isset($sessionData['audienciaPatronal'])) {
                $patronales = $sessionData['audienciaPatronal']->map(function ($c) use ($citadoId, $tipoIdentificacion, $numIdentificacion, $docPath) {
                    if ((int)$c->id == (int)$citadoId) {
                        $c->comparecencia = 'Si';
                        $c->tipo_identificacion_comparecencia = $tipoIdentificacion;
                        $c->num_identificacion_comparecencia = $numIdentificacion;
                        if ($docPath) {
                            $c->identificacion_comparecencia = $docPath;
                        }
                    }
                    return $c;
                });
                $sessionData['audienciaPatronal'] = $patronales;
            }

            session([$sessionKey => $sessionData]);
        } else {
            // Actualizamos en BD solo si NO hay sesión activa
            $citadoDb = \App\Models\SeerCitados::find($citadoId);
            if ($citadoDb) {
                $updateData = [
                    'comparecencia' => 'Si',
                    'tipo_identificacion_comparecencia' => $tipoIdentificacion,
                    'num_identificacion_comparecencia' => $numIdentificacion,
                ];
                if ($docPath) {
                    $updateData['identificacion_comparecencia'] = $docPath;
                }
                $citadoDb->update($updateData);
            }
        }

        session()->flash('preserve_edit_session', true);
        return redirect()->route('inicioAudiencia', ['id' => $idSolicitud, 'audiencia_id' => $audienciaId])
            ->with('success', 'Comparecencia registrada correctamente.');
    }

    public function mostrar_citadoC($id){
        $folio = SeerCitados::find($id);
        
        return view('/notificaciones/mostrar_citado',compact('folio'));
    }

    //PDF Acta por falta de interés
    public function VerPDFInteres($id){
        $solicitud = SeerPerGeneral::find($id);
        $solicitante = SeerSolicitante::where('id_solicitud',$solicitud["id"])->first();
       
        $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name')
            ->first();
        $citados = SeerCitados::where("id_solicitud",$id)
        ->where('tipo_notificacion', '!=', 'Multa')
        ->select('nombre','primer_apellido','segundo_apellido')
        ->get();
        $motivos = SeerMotivo::join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo')->get();
        $audiencia = Audiencias::where("id_solicitud",$solicitud["id"])->latest()->first();

        $html = view('PDF/Solicitudes/ActaFaltaInteres', compact('id', 'solicitud','conciliador','solicitante','citados','motivos','audiencia'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'falta_de_interes_' . $solicitante->nombre .'.pdf';
        return $pdf->stream($nombreArchivo);  
    }

    public function incopentencia_audiencia(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'numero_audiencias'     => $num_audi,
            'validado'              => 'Validado',
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'estatus_conciliacion'  => 'Incompetencia'
        ];
        
        SeerPerConciliador::create($data_conciliador);  

        SeerPerGeneral::find($data["id"])
        ->update([
            'fecha_terminacion'     => $fecha_actual, 
            'observaciones'         => $data["observaciones"], 
            'conciliador_id'        => $user->id,
            'estatus'               => 'Incompetencia'
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        $prueba = Audiencias::where('id_solicitud',$data["id"])
        //->orderBy('id_solicitud','desc')
        ->latest()
        ->first()
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'Incompetencia',
        ]);

        return redirect()->route('todas_audiencias');
    }

    public function desistimiento_audiencia(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'numero_audiencias'     => $num_audi,
            'validado'              => 'Validado',
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'estatus_conciliacion'  => 'Desistimiento'
        ];
        
        SeerPerConciliador::create($data_conciliador);  

        SeerPerGeneral::find($data["id"])
        ->update([
            'fecha_terminacion'     => $fecha_actual, 
            'observaciones'         => $data["observaciones"], 
            'conciliador_id'        => $user->id,
            'estatus'               => 'Desistimiento'
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
        ->latest()
        ->first()
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'Desistimiento',
        ]);

        return redirect()->route('todas_audiencias');
    }
    
    public function GeneraAudiencia($id){
        $año_actual = date('Y');
        $id_adiencia = SeerPerConciliador::select('consecutivo')->orderBy('consecutivo', 'desc')->first();
        if(!isset($id_adiencia)){
            $num_adiencia = 0;
        }
        else{
            $num_adiencia = $id_adiencia["consecutivo"];
        }

        $num_adiencia = $num_adiencia + 1;
        $numeroConCeros = str_pad($num_adiencia, 4, "0", STR_PAD_LEFT);
        $folio = $numeroConCeros."/".$año_actual;
    
        return array($folio,$num_adiencia);
    }

    public function reagendar_audiencia(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        //Se va insertar un registro en audiencias 
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia[0],
            'numero_audiencias'     => $num_audi,
            'validado'              => 'Validado',
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'estatus_conciliacion'  => 'Regenerada'
        ];        
        SeerPerConciliador::create($data_conciliador);
        
        \DB::transaction(function() use ($user, $data, $request) {
            //Obtener la audiencia mas reciente
            $audienciaOld = Audiencias::where('id_solicitud', $data["id"])->orderBy('id', 'desc')->first();
            $audiencia_id = $data['audiencia_id'] ?? $request->query('audiencia_id');

            if ($audienciaOld) {
                // Marcar la audiencia existente como reagendada
                $audienciaOld->update([
                    'estatus' => 'Reagendada'
                ]);

                $old_num = is_numeric($audienciaOld->numero_audiencia) ? intval($audienciaOld->numero_audiencia) : 0;
                $new_num = $old_num + 1;

                // Incrementar folio (formato: 0000/2025)
                $old_folio = $audienciaOld->folio_audiencia ?? '';
                if (strpos($old_folio, '/') !== false) {
                    list($prefix, $year) = explode('/', $old_folio, 2);
                    $prefixNum = intval(preg_replace('/[^0-9]/', '', $prefix));
                    $width = strlen(preg_replace('/[^0-9]/', '', $prefix));
                    $newPrefix = str_pad(strval($prefixNum + 1), max(4, $width), '0', STR_PAD_LEFT);
                    $new_folio = $newPrefix . '/' . $year;
                } else {
                    $year = date('Y');
                    $new_folio = str_pad(strval($new_num), 4, '0', STR_PAD_LEFT) . '/' . $year;
                }

                // Crear nueva audiencia con estatus Pendiente y datos copiados
                $audiencia = Audiencias::create([
                    'id_conciliador'   => $audienciaOld->id_conciliador,
                    'id_solicitud'     => $audienciaOld->id_solicitud,
                    'numero_audiencia' => $new_num,
                    'folio_audiencia'  => $new_folio,
                    'fecha'            => $data["fecha"],
                    'hora'             => $data["hora"],
                    'sala'             => $audienciaOld->sala ?? null,
                    'delegacion'       => $audienciaOld->delegacion ?? null,
                    'estatus'          => 'Pendiente'
                ]);
            } else {
                // Si no existe audiencia previa, crear una nueva simple
                $new_folio = str_pad('1', 4, '0', STR_PAD_LEFT) . '/' . date('Y');
                $audiencia = Audiencias::create([
                    'id_conciliador'   => $user->id,
                    'id_solicitud'     => $data["id"],
                    'numero_audiencia' => 1,
                    'folio_audiencia'  => $new_folio,
                    'fecha'            => $data["fecha"],
                    'hora'             => $data["hora"],
                    'sala'             => null,
                    'delegacion'       => null,
                    'estatus'          => 'Pendiente'
                ]);
            }

            $citados = SeerCitados::where('id_solicitud', $data["id"])->get();
            foreach($citados as $citado){
                if (!$citado->audiencia_id) {
                    $citado->audiencia_id = $audiencia_id;
                    $citado->save();
                }
                $nuevo_citado = $citado->replicate();
                $nuevo_citado->fecha = NULL;
                $nuevo_citado->notificacion = 'Centro';
                $nuevo_citado->id_abogado = NULL;
                $nuevo_citado->audiencia_id = $audiencia->id;
                $nuevo_citado->save();
            }
        });
    
        return redirect()->route('todas_audiencias');
    }

    public function reagendar_audiencia_parte3(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $fecha_actual = date('y-m-d');
        $audiencia_id = $data['audiencia_id'] ?? $request->query('audiencia_id');
        $solicitudTipo = SeerPerGeneral::where('id', $data["id"])->value('tipo_solicitud');

        //Guardar registro en SeerConciliador
        $numero_audiencias = SeerPerConciliador::find($data["id"]);
        if(!isset($numero_audiencias)){
            $num_audi = 0;
        }
        else{
            $num_audi = $numero_audiencias->numero_audiencias;
        }
        $num_audi = $num_audi+1;

        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        //Se va insertar un registro en audiencias 
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'audiencia_id'          => $audiencia_id,
            'numero_audiencia'      => $numero_audiencia[0],
            'numero_audiencias'     => $num_audi,
            'validado'              => 'Validado',
            'fecha_conclucion'      =>  $fecha_actual,
            'consecutivo'           =>  $numero_audiencia[1],
            'estatus_conciliacion'  => 'No conciliacion se reagenda',
            'resolicion_primera'        => $data['primera'] ?? null,
            'resolicion_justificacion'  => $data['justificacion'] ?? null,
            'resolicion_segunda'        => $data['segunda'] ?? null,
        ];        
        SeerPerConciliador::create($data_conciliador);  
        
        \DB::transaction(function() use ($solicitudTipo, $user, $data) {
            //Obtener la audiencia mas reciente
            $audienciaOld = Audiencias::where('id_solicitud', $data["id"])->orderBy('id', 'desc')->first();

            if ($audienciaOld) {
                // Marcar la audiencia existente como reagendada
                $audienciaOld->update([
                    'estatus' => 'No conciliacion reagendada'
                ]);

                $old_num = is_numeric($audienciaOld->numero_audiencia) ? intval($audienciaOld->numero_audiencia) : 0;
                $new_num = $old_num + 1;

                // Incrementar folio (formato: 0000/2025)
                $old_folio = $audienciaOld->folio_audiencia ?? '';
                if (strpos($old_folio, '/') !== false) {
                    list($prefix, $year) = explode('/', $old_folio, 2);
                    $prefixNum = intval(preg_replace('/[^0-9]/', '', $prefix));
                    $width = strlen(preg_replace('/[^0-9]/', '', $prefix));
                    $newPrefix = str_pad(strval($prefixNum + 1), max(4, $width), '0', STR_PAD_LEFT);
                    $new_folio = $newPrefix . '/' . $year;
                } else {
                    $year = date('Y');
                    $new_folio = str_pad(strval($new_num), 4, '0', STR_PAD_LEFT) . '/' . $year;
                }

                // Crear nueva audiencia con estatus Pendiente y datos copiados
                $audiencia = Audiencias::create([
                    'id_conciliador'   => $audienciaOld->id_conciliador,
                    'id_solicitud'     => $audienciaOld->id_solicitud,
                    'numero_audiencia' => $new_num,
                    'folio_audiencia'  => $new_folio,
                    'fecha'            => $data["fecha"],
                    'hora'             => $data["hora"],
                    'sala'             => $audienciaOld->sala ?? null,
                    'delegacion'       => $audienciaOld->delegacion ?? null,
                    'estatus'          => 'Pendiente',
                    'poder_id'         => $audienciaOld->poder_id ?? null
                ]);

            } else {
                // Si no existe audiencia previa, crear una nueva simple
                $new_folio = str_pad('1', 4, '0', STR_PAD_LEFT) . '/' . date('Y');
                $audiencia = Audiencias::create([
                    'id_conciliador'   => $user->id,
                    'id_solicitud'     => $data["id"],
                    'numero_audiencia' => 1,
                    'folio_audiencia'  => $new_folio,
                    'fecha'            => $data["fecha"],
                    'hora'             => $data["hora"],
                    'sala'             => null,
                    'delegacion'       => null,
                    'estatus'          => 'Pendiente',
                    'poder_id'         => $audienciaOld->poder_id ?? null
                ]);
            }

            $hayCentro = SeerCitados::where('id_solicitud', $data["id"])
                //->where('audiencia_id', $audienciaOld->id ?? null)
                ->where('notificacion', 'Centro')
                ->exists();

            if($solicitudTipo == 1){
                if ($hayCentro) {
                $citados = SeerCitados::where('id_solicitud', $data["id"])
                    //->where('audiencia_id', $audienciaOld->id ?? null)
                    ->where('tipo_notificacion', '!=', 'Multa')
                    ->where('notificacion', 'Centro')
                    ->whereNotNull('id_abogado')
                    ->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $data["id"])
                        //->where('audiencia_id', $audienciaOld->id ?? null)
                        ->where('tipo_notificacion', '!=', 'Multa')
                        ->where('notificacion', 'Trabajador')
                        ->get();
                }
            } else if ($solicitudTipo == 2){
                if ($hayCentro) {
                    $citados = SeerCitados::where('id_solicitud', $data["id"])
                        //->where('audiencia_id', $audienciaOld->id ?? null)
                        ->where('tipo_notificacion', '!=', 'Multa')
                        ->where('notificacion', 'Centro')
                        ->where('comparecencia', 'Si')
                        ->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $data["id"])
                        //->where('audiencia_id', $audienciaOld->id ?? null)
                        ->where('tipo_notificacion', '!=', 'Multa')
                        ->where('notificacion', 'Trabajador')
                        ->get();
                }
            }
            

            foreach ($citados as $citado) {
                $nuevo_citado = $citado->replicate();
                $formattedDate = now()->toDateTimeString();
                $nuevo_citado->fecha = $formattedDate;
                $nuevo_citado->notificacion = 'Centro';
                $nuevo_citado->tipo_notificacion = 'Citatorio';
                $nuevo_citado->id_abogado = NULL;
                $nuevo_citado->audiencia_id = $audiencia->id; // nueva audiencia

                if($solicitudTipo == 1) {
                    if (!$citado->id_abogado) {
                        $nuevo_citado->estatus = 'Sin asignar';
                        $nuevo_citado->id_notificador = 0;
                    } else {
                        $nuevo_citado->estatus = 'Notificada en Audiencia';
                    }
                } else if ($solicitudTipo == 2) {
                    if ($citado->comparecencia == NULL || $citado->comparecencia == 'No') {
                        $nuevo_citado->estatus = 'Sin asignar';
                        $nuevo_citado->id_notificador = 0;
                    } else {
                        $nuevo_citado->estatus = 'Notificada en Audiencia';
                    }
                }

                $nuevo_citado->save();
            }
        });

        try {
            session()->forget(["audiencia_conclucion_data_{$data['id']}", "convenio_citados_{$data['id']}", "acta_citados_{$data['id']}", "audiencia_data_{$data['id']}", 'preserve_edit_session']);
        } catch (\Exception $e) {
        }
    
        return redirect()->route('todas_audiencias');
    }


    //PDF Acta por falta de interés
    public function VerPDFIncompetencia($id){
        $solicitud = SeerPerGeneral::find($id);
        $solicitante = SeerSolicitante::where('id_solicitud',$solicitud["id"])->first();
        $conciliador  = User::join("seer_general","seer_general.conciliador_id","=","users.id");
        $conciliador = $conciliador->where("seer_general.id", "=", $id)
        ->select('users.name')
        ->first();
       /* $delegadosEspeciales = [
            'Zitácuaro'        => 11,
            'Lázaro Cárdenas'  => 43,
            'Sahuayo'          => 26,
        ];*/
        $delegacion = $solicitud->delegacion;
        $delegado = null;
        if (!empty($solicitud->delegado_id)) {
            $delegado = User::select('id', 'name', 'delegacion')->find($solicitud->delegado_id);
        }

        if (!$delegado) {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();
        }
        /*if (array_key_exists($delegacion, $delegadosEspeciales)) {
            $delegado = User::select('id', 'name', 'delegacion')
            ->find($delegadosEspeciales[$delegacion]);
        } else {
            $delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();
        }*/
        $citados = SeerCitados::where("id_solicitud",$id)
        ->where('tipo_notificacion', '!=', 'Multa')
        ->select('nombre','primer_apellido','segundo_apellido')
        ->get();
        $motivos = SeerMotivo::join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo')->get();
        $audiencia = SeerPerConciliador::where("id_solicitud",$solicitud["id"])->first();

        $html = view('PDF/Solicitudes/incompetencia', compact('id', 'solicitud','conciliador','solicitante','citados','motivos','audiencia','delegado', 'delegacion'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'incompetencia_' . $solicitante->nombre .'.pdf';
        return $pdf->stream($nombreArchivo);  
    }


    public function audiencia_parte2(Request $request){
        $data = $request->all();
        $id = $data["id"];
        $audienciaId = $request->input('audiencia_id');

        $sessionKey = "audiencia_data_{$id}";
        
        if (session()->has($sessionKey)) {
            $sessionData = session($sessionKey);
            
            $solicitanteData = $sessionData['solicitante'];
            SeerSolicitante::where('id', $solicitanteData->id)->update([
                'curp' => $solicitanteData->curp,
                'rfc' => $solicitanteData->rfc,
                'nombre' => $solicitanteData->nombre,
                'puesto' => $solicitanteData->puesto,
                'pago' => $solicitanteData->pago,
                'periodo_pago' => $solicitanteData->periodo_pago,
                'fecha_ingreso' => $solicitanteData->fecha_ingreso,
                'fecha_salida' => $solicitanteData->fecha_salida,
                'jornada' => $solicitanteData->jornada,
                'horas_semana' => $solicitanteData->horas_semana,
                'nss' => $solicitanteData->nss
            ]);
            
            foreach ($sessionData['citados'] as $citado) {
                SeerCitados::where('id', $citado->id)->update([
                    'id_abogado' => $citado->id_abogado,
                    'id_fisica' => $citado->id_fisica,
                    'nombre' => $citado->nombre,
                    'primer_apellido' => $citado->primer_apellido,
                    'segundo_apellido' => $citado->segundo_apellido,
                    'id_historial' => $citado->id_historial,
                    'comparecencia' => $citado->comparecencia,
                    'tipo_identificacion_comparecencia' => $citado->tipo_identificacion_comparecencia ?? null,
                    'num_identificacion_comparecencia' => $citado->num_identificacion_comparecencia ?? null,
                    'identificacion_comparecencia' => $citado->identificacion_comparecencia ?? null,
                ]);
                if (SeerCitados::where('id', $citado->id)->value('comparecencia') == NULL) {
                    SeerCitados::where('id', $citado->id)->update([
                        'comparecencia' => 'No'
                    ]);
                }
            }
            
            session()->forget($sessionKey);
        }

        //Revisar si los citattorios son por el centro o por le trabajador
        $citados = SeerCitados::where('id_solicitud',$data["id"])->select('notificacion')->orderBy('id', 'desc')->first();
        $user = auth()->user();

        //Se usa para marcar que citados si tienen un representante o una persona fisica, para el tema de la no conciliación y se genere un documento por citado, indicando si asistió o no
        /*$citados_apareceConvenio = SeerCitados::where('id_solicitud', $id)->get();
        foreach ($citados_apareceConvenio as $citado) {

            $tiene_representante = 
                (!empty($citado->id_abogado) && $citado->id_abogado > 0) ||
                (!empty($citado->id_fisica) && $citado->id_fisica > 0);

            $citado->aparece_convenio = $tiene_representante ? 1 : 0;
            $citado->save();
        }*/
 
        //Si la bandera es 0 selecciono a todos los representantes puede avanzar
        if($data["bandera"] == 0){
            return redirect()->route('audiencias.parte3', ['id' => $id, 'audiencia_id' => $audienciaId]);
        }
        //Si la bandera es 1 le fanto un representante por lo tanto va a generar nueva audiencia o va multar
        else{
            if($citados->notificacion == "Trabajador"){
                return redirect()->route('audiencias.parte3', ['id' => $id, 'audiencia_id' => $audienciaId]);
            }
            else if($citados->notificacion == "Centro" || $citados->notificacion == "Exhorto"){
                //Si va por el centro se van a generar las multas a los que no tiene reprecentante legal
                /*$total_citados = SeerCitados::where("id_solicitud",$data["id"])
                ->where('notificacion',"Centro")
                ->get();
                $citados = SeerCitados::where("id_solicitud",$data["id"])
                ->where('notificacion',"Centro")
                ->whereNull("id_abogado")
                ->get();
                $cont = count($citados);
                $cont_total = count($total_citados);*/
                
                $tipoSolicitud = SeerPerGeneral::where('id', $data['id'])->value('tipo_solicitud');

                if ($tipoSolicitud == 1){
                    $citados = SeerCitados::where('id_solicitud', $data["id"])->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->whereNULL("id_abogado")->whereIn('estatus', ['Notificada', 'Finalizado exitosamente', 'Exitosa por Instructivo', 'No notificada', 'Notificada en Audiencia'])->get();
                    foreach($citados as $citado){
                        $tieneNotificadaEnAudiencia = $citados->where('nombre', $citado->nombre)
                                                            ->where('estatus', 'Notificada en Audiencia')
                                                            ->count() > 0;

                        if ($tieneNotificadaEnAudiencia && $citado->estatus !== 'Notificada en Audiencia') {
                            continue;
                        }

                        $existeMulta = SeerCitados::where('id_solicitud', $data["id"])
                            ->where('tipo_notificacion', 'Multa')
                            ->where('nombre', $citado->nombre)
                            ->exists();

                        if (!$existeMulta) {
                            $nuevo_citado = $citado->replicate();
                            $nuevo_citado->fecha = NULL;
                            $nuevo_citado->tipo_notificacion = 'Multa';
                            $nuevo_citado->estatus = 'Sin asignar';
                            $nuevo_citado->audiencia_id = $data['audiencia_id'] ?? request()->query('audiencia_id') ?? $citado->audiencia_id;
                            $nuevo_citado->id_notificador = 0;
                            $nuevo_citado->save();
                        }
                    }
                }

                /*if($cont == $cont_total){
                    $update = SeerPerGeneral::find($id)->update(['estatus' => 'No conciliacion']);
                }
                else{*/
                    return redirect()->route('audiencias.parte3', ['id' => $id, 'audiencia_id' => $audienciaId]);
                //}                
            }
            return redirect()->route('todas_audiencias');
        }
         
    }

    public function mis_solicitudes(){
        $id = auth()->user()->id;
        $user = User::find($id);

        $solicitudes = SeerPerGeneral::join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
        ->join('users','seer_solicitante.curp','=','users.profile_photo_path')
        ->where('seer_solicitante.curp',$user["profile_photo_path"])
        ->select('seer_general.id','seer_general.fecha','seer_solicitante.nombre','seer_general.estatus')
        ->get();
        
        return view('/solicitudes/missolicitudes',compact('solicitudes'));
    }

    public function mostrar_citados($id){
        $municipios = Municipios::all();
        $estados = Estados::all();
        $folio = SeerCitados::find($id);
        return view('/notificaciones/ver_citado',compact('folio','municipios','estados'));
    }

    public function audienciaParte3($id){
        $audiencia_id = request()->query('audiencia_id');
        if (is_null($audiencia_id) || $audiencia_id === '') {
            $audiencia_id = Audiencias::where('id_solicitud', $id)->latest('id')->value('id');
            if (!is_null($audiencia_id) && $audiencia_id !== '') {
                return redirect()->route('audiencias.parte3', ['id' => $id, 'audiencia_id' => $audiencia_id]);
            }
        }

        $solicitud = SeerPerGeneral::find($id);
        $conciliadorId = $solicitud->conciliador_id;
        $sede = $solicitud["delegacion"];
        
        $raw_fecha = Audiencias::where('id_solicitud', $id)->latest('id')->value('fecha');
        $raw_hora  = Audiencias::where('id_solicitud', $id)->latest('id')->value('hora');

        $audiencia_fecha = Carbon::parse($raw_fecha)->format('Y-m-d');
        $audiencia_hora = Carbon::parse($raw_hora)->format('H:i:s');

        $NUE = $solicitud->NUE;
        if($NUE === NULL){
            $NUE = 'Sin NUE';
        }

        $fechaConfirmacion = SeerPerGeneral::where('id', $id)->value('fecha_confirmacion');
        if(is_null($fechaConfirmacion)) {
            $fechaConfirmacion = now();
            $fechaConfirmacion = $fechaConfirmacion->format('Y-m-d');
        }
        
        $representantes = SeerCitados::
        leftjoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado')
        ->leftJoin('persona_fisica', 'persona_fisica.id', '=', 'seer_citados.id_fisica')
        ->where('seer_citados.id_solicitud', $id)
        ->select('seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido','seer_citados.rfc',
        'abogados.nombres_patronal as nombre_abogado','abogados.primer_apellido_patronal as primero_abogado','abogados.segundo_apellido_patronal as segundo_abogado',
        'persona_fisica.nombre as nombre_fisica','persona_fisica.primer_apellido as primer_fisica','persona_fisica.segundo_apellido as segundo_fisica',
        'seer_citados.id_abogado','seer_citados.id_fisica','seer_citados.id','seer_citados.notificacion','seer_citados.estatus','seer_citados.aparece_convenio', 'seer_citados.id_historial')
        ->get();

        $dato_pena =  null;
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        
        if($solicitante){
            if($solicitante->periodo_pago === 'Semanal'){
                $dato_pena =  $solicitante->pago / 7;
            }
            elseif($solicitante->periodo_pago === 'Mensual'){
                $dato_pena =  $solicitante->pago / 30;
            }
            elseif($solicitante->periodo_pago === 'Quincenal'){
                $dato_pena =  $solicitante->pago / 15;
            }
            elseif($solicitante->periodo_pago === 'Diario'){
                $dato_pena =  $solicitante->pago;
            }
        }
         
        $direccion_c = 'NO DEFINIDA';

        $citado_historial = $representantes->where('id_historial')->first();
        $citado_referencia = $representantes->where('id_abogado')->first();
        
        //Primero busca en la tabla de Historial Abogados 

        if($citado_historial){
            $id_h = $citado_historial->id_historial;
            $historial = HistorialAbogado::where('id', $id_h)->first();
            if($historial){
                $municipio = Municipios::find($historial->municipio_patronal);
                $municipioAbogado = $municipio ? $municipio->nombre : '';
                $estado = Estados::find($historial->estado_patronal);
                $estadoAbogado = $estado ? $estado->nombre : '';
                $direccion_c = $historial->tipo_vialidad_patronal.' '. $historial->vialidad_patronal.' '. $historial->num_ext_patronal.' '. $historial->mun_int_patronal . ' COLONIA '. mb_strtoupper($historial->colonia_patronal) . ', CP ' . $historial->cp_patronal .', '. mb_strtoupper($municipioAbogado) .' '. mb_strtoupper($estadoAbogado);
            }
        }
        elseif($citado_referencia){ //Si no encuentra nada con id_historial anterior busca con referencia al id_abogados
            $id_a = $citado_referencia->id_abogado;
            $abogado = Poder::where('idAbogado', $id_a)->first();
            if($abogado){
                $municipio = Municipios::find($abogado->municipio_patronal);
                $municipioAbogado = $municipio ? $municipio->nombre : '';
                $estado = Estados::find($abogado->estado_patronal);
                $estadoAbogado = $estado ? $estado->nombre : '';
                $direccion_c = $abogado->tipo_vialidad_patronal.' '. $abogado->vialidad_patronal.' '. $abogado->num_ext_patronal.' '. $abogado->mun_int_patronal . ', COLONIA '. mb_strtoupper($abogado->colonia_patronal) . ', CP ' . $abogado->cp_patronal .', '. mb_strtoupper($municipioAbogado) .' '. mb_strtoupper($estadoAbogado);
            }
        }

        $montoPena = is_numeric($dato_pena) ? $dato_pena : 0;

        $sessionKey = 'audiencia_conclucion_data_' . $id;
        $previewData = session($sessionKey, null);

        $bandera = request()->query('bandera', null);

        return view('/audiencias/parte3',compact('id', 'sede', 'fechaConfirmacion', 'NUE', 'previewData', 'audiencia_fecha', 'audiencia_hora', 'bandera', 'conciliadorId', 'direccion_c', 'montoPena', 'audiencia_id'));
    }

    public function historial_notificador(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        $notificaciones = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
        ->select('seer_general.id as id_solicitud','seer_citados.id as id_citado','seer_general.NUE',
            'seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
            'seer_citados.colonia','seer_citados.calle','seer_citados.n_ext','seer_citados.n_int','seer_citados.estatus'
            ,'seer_citados.fecha','seer_solicitante.nombre as nombre_solicitante')
        ->where('seer_citados.id_notificador', $id)
        ->where("seer_citados.fecha",">=",$data["fecha_inicio"])
        ->where("seer_citados.fecha",">=",$data["fecha_final"])
        ->get();
                
        return view('/historial/notificaciones',compact('notificaciones'));
    }

    public function historial_auxiliar(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        $notificaciones = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
        ->select('seer_general.id as id_solicitud','seer_citados.id as id_citado','seer_general.NUE',
            'seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
            'seer_citados.colonia','seer_citados.calle','seer_citados.n_ext','seer_citados.n_int','seer_citados.estatus'
            ,'seer_citados.fecha','seer_solicitante.nombre as nombre_solicitante')
        ->where('seer_citados.id_notificador', $id)
        ->where("seer_citados.fecha",">=",$data["fecha_inicio"])
        ->where("seer_citados.fecha",">=",$data["fecha_final"])
        ->get();
                
        return view('/historial/auxiliares',compact('notificaciones'));
    }
    
    public function solicitudes_todas(){
        $solicitudes = SeerPerGeneral::join('catalogo_rama','catalogo_rama.id','seer_general.id_rama')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
        ->select('seer_general.id','seer_general.fecha','seer_solicitante.nombre','seer_general.delegacion','seer_general.actividad',
        'catalogo_rama.rama_industrial','seer_general.tipo_solicitud')
        //->where('seer_general.estatus','Pendiente')
        ->orderBy('seer_general.consecutivo', 'asc') // Primer criterio
        ->orderBy('seer_general.fecha', 'desc')       // Segundo criterio (puedes usar 'asc' o 'desc')
        ->get();

        return view('solicitudes.solicitudes', compact('solicitudes'));
    }
    
    public function ObtenerAudiencia($delegacion, $notificion) {
        $id = auth()->user()->id;
        $user = User::find($id);
        
        // El punto de partida real para los 45 días siempre es HOY
        $hoy = \Carbon\Carbon::now();

        $horarios_disponibles = ["09:00:00", "10:15:00", "11:30:00", "12:45:00", "14:00:00"];
        $mapa_sedes = ["Zitácuaro" => "Morelia", "Lázaro Cárdenas" => "Uruapan", "Sahuayo" => "Zamora"];
        $oficina = $mapa_sedes[$delegacion] ?? $delegacion;
        $permisos_requeridos = array_key_exists($delegacion, $mapa_sedes) ? ["Ambos", "Virtual"] : ["Ambos", "Precencial"];

        // 1. Calcular días inhábiles dentro de la ventana base de 45 días en la sede destino
        $fechaLimiteBase = $hoy->copy()->addDays(45);
        $periodosVacacionalesSede = DiasInhabiles::where('centro', $oficina)
            ->whereNull('user_id')
            ->where('descripcion', 'Inhabil')
            ->whereIn('tipo', ['Todos', 'Audiencias'])
            ->where('fecha_inicio', '<=', $fechaLimiteBase->format('Y-m-d'))
            ->where('fecha_final', '>=', $hoy->format('Y-m-d'))
            ->get(['fecha_inicio', 'fecha_final']);

        $diasVacacionesSede = 0;
        $inicioVentana = $hoy->copy()->startOfDay();
        $finVentana = $fechaLimiteBase->copy()->startOfDay();
        foreach ($periodosVacacionalesSede as $periodo) {
            $inicio = \Carbon\Carbon::parse($periodo->fecha_inicio)->max($inicioVentana);
            $fin = \Carbon\Carbon::parse($periodo->fecha_final)->min($finVentana);
            if ($inicio->lte($fin)) {
                $diasVacacionesSede += $inicio->diffInDays($fin) + 1;
            }
        }

        // 2. El plazo de 45 días naturales parte de 'hoy' y se extiende con las vacaciones de la sede
        $diasTotalesPlazo = 45 + $diasVacacionesSede;
        $fecha_limite_natural = $hoy->copy()->addDays($diasTotalesPlazo);

        // Margen de notificación reglamentario (también partiendo de hoy)
        $diasMargen = ($notificion == "Trabajador") ? 7 : 15;
        $fecha_inicio_busqueda = $hoy->copy()->addDays($diasMargen);

        if ($fecha_inicio_busqueda->gt($fecha_limite_natural)) {
            $fecha_inicio_busqueda = $fecha_limite_natural->copy();
        }

        // 3. Bucle para extender el límite si existen días inhábiles generales en la sede de destino
        $revisar_limite = $fecha_inicio_busqueda->copy();
        $maxIteracionesLimite = 200;
        while ($revisar_limite->lte($fecha_limite_natural) && $maxIteracionesLimite-- > 0) {
            $fecha_str = $revisar_limite->format('Y-m-d');

            $dia_inhabil_centro = DiasInhabiles::where('centro', $oficina)
                ->whereNull('user_id')
                ->whereIn('tipo', ['Todos', 'Audiencias'])
                ->where('descripcion', 'Inhabil')
                ->where('fecha_inicio', '<=', $fecha_str)
                ->where('fecha_final', '>=', $fecha_str)
                ->exists();

            if ($dia_inhabil_centro) {
                $fecha_limite_natural->addDay(); 
            }

            $revisar_limite->addDay();
        }

        // 4. Obtener conciliadores aptos
        $conciliadores = User::whereHas('roles', function ($q) { $q->where('name', 'Conciliador'); })
            ->where('delegacion', $oficina)
            ->whereIn('id', function ($q) use ($permisos_requeridos) {
                $q->select('id_conciliador')->from('permisos_conciliador')->whereIn('tipo', $permisos_requeridos);
            })->get();

        if ($conciliadores->isEmpty()) {
            return response()->json(['error' => 'Sin conciliadores configurados.'], 404);
        }

        $permisosConciliadores = PermisosConciliador::whereIn('id_conciliador', $conciliadores->pluck('id'))
            ->whereIn('tipo', $permisos_requeridos)
            ->get()
            ->groupBy('id_conciliador');

        $dia_semana_map = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes'];

        $fecha_revisar = $fecha_inicio_busqueda->copy();

        // 5. Bucle principal de búsqueda de espacios vacíos
        while ($fecha_revisar->lte($fecha_limite_natural)) {
            $fecha_str = $fecha_revisar->format('Y-m-d');

            $dia_inhabil_centro = DiasInhabiles::where('centro', $oficina)
                ->whereNull('user_id')
                ->whereIn('tipo', ['Todos', 'Audiencias'])
                ->where('descripcion', 'Inhabil')
                ->where('fecha_inicio', '<=', $fecha_str)
                ->where('fecha_final', '>=', $fecha_str)
                ->exists();

            if ($fecha_revisar->isWeekend() || $dia_inhabil_centro) {
                $fecha_revisar->addDay();
                continue;
            }

            foreach ($horarios_disponibles as $h) {

                // VALIDACIÓN: Bloqueo de Sede por Horas ("No inhabil")
                $sede_bloqueada_en_hora = DiasInhabiles::where('centro', $oficina)
                    ->whereNull('user_id')
                    ->whereIn('tipo', ['Todos', 'Audiencias'])
                    ->where('descripcion', 'No inhabil')
                    ->where('fecha_inicio', '<=', $fecha_str)
                    ->where('fecha_final', '>=', $fecha_str)
                    ->where('horario_inicio', '<=', $h)
                    ->where('horario_final', '>=', $h)
                    ->exists();

                if ($sede_bloqueada_en_hora) {
                    continue; 
                }

                $posibles_conciliadores = [];
                $dia_campo = $dia_semana_map[$fecha_revisar->dayOfWeek] ?? null;
                foreach ($conciliadores as $c) {
                    // Validar disponibilidad del conciliador según permisos_conciliador (día y horario)
                    $permisosDelConciliador = $permisosConciliadores->get($c->id, collect());
                    $disponible = $dia_campo && $permisosDelConciliador->contains(function ($permiso) use ($dia_campo, $h) {
                        return $permiso->{$dia_campo} === 'Si'
                            && $permiso->{$dia_campo . '_inicio'} <= $h
                            && $permiso->{$dia_campo . '_final'} >= $h;
                    });
                    if (!$disponible) continue;

                    $bloqueo_conciliador = DiasInhabiles::where('user_id', $c->id)
                        ->whereIn('tipo', ['Todos', 'Audiencias', 'Bloqueo por permiso'])
                        ->where('fecha_inicio', '<=', $fecha_str)
                        ->where('fecha_final', '>=', $fecha_str)
                        ->where(function ($query) use ($h) {
                            $query->where('descripcion', 'Inhabil')
                                ->orWhere(function ($qSub) use ($h) {
                                    $qSub->where('descripcion', 'No inhabil')
                                        ->where('horario_inicio', '<=', $h)
                                        ->where('horario_final', '>=', $h);
                                });
                        })
                        ->exists();

                    if (!$bloqueo_conciliador) {
                        // Validar que el conciliador individual tampoco tenga otra audiencia asignada a esa hora
                        $ocupado = Audiencias::where('fecha', $fecha_str)->where('hora', $h)->where('id_conciliador', $c->id)->exists();
                        if (!$ocupado) {
                            $posibles_conciliadores[] = $c->id;
                        }
                    }
                }

                if (!empty($posibles_conciliadores)) {
                    return [
                        $fecha_str,
                        $h,
                        ucfirst($fecha_revisar->isoFormat('dddd D [de] MMMM')),
                        $posibles_conciliadores[array_rand($posibles_conciliadores)],
                        "Fecha encontrada correctamente."
                    ];
                }
            }
            $fecha_revisar->addDay();
        }
        return response()->json(['error' => "No hay disponibilidad en el rango legal extendido por días inhábiles"], 404);
    }

    public function concluir_audiencia_conciliador(Request $request){    
        $data = $request->all();

        $hayCentro = false;

        $id_solicitud = $data["id"];
        $audiencia_id = $data['audiencia_id'] ?? $request->query('audiencia_id');
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        $solicitudOriginal = SeerPerGeneral::find($data["id"]);
        $sede_a_guardar = $solicitudOriginal->delegacion ?? $user->delegacion;
        
        if($data["conclucion"] == "Conciliacion" || $data["conclucion"] == "Reinstalacion"){
             // --- BLOQUE VISTA PREVIA ---
            if(!isset($data["dias_pagos"])) return back()->withErrors('Debes agregar por lo menos una fecha de pago.');
            if(!isset($data["tipo_pago"])) return back()->withErrors('Debes agregar por lo menos un concepto de pago.');

            if(isset($data["valor"]) && $data["valor"] == 1){
                $sessionKey = 'audiencia_conclucion_data_' . $id_solicitud;
                session([$sessionKey => $data]);
                session()->put('preserve_edit_session', true);
                if ($solicitudOriginal->tipo_solicitud == 1) {
                    return redirect()->route('vista_previa', ['id_solicitud' => $id_solicitud, 'audiencia_id' => ($data['audiencia_id'] ?? null)]);
                } else if ($solicitudOriginal->tipo_solicitud == 2) {
                    return redirect()->route('vista_previa_patronal', ['id_solicitud' => $id_solicitud, 'audiencia_id' => ($data['audiencia_id'] ?? null)]);
                }
            }
            // ---------------------------

            //Revisar si existe
            if(isset($data["dias_pagos"])){
                $conteo = count($data["dias_pagos"]);
                for($i = 0; $i < $conteo; $i++) {
                    //Solo para el primer caso voy a seleccionar el tipo de pago
                    if($i == 0){
                        $data_pagos = [
                            'id_solicitud'  => $data["id"],
                            'fecha'         => $data["dias_pagos"][$i],
                            'hora'          => $data["hora_pagos"][$i], 
                            'monto'         => $data["monto_pagos"][$i], 
                            'descripcion'   => $data["descripcion_pagos"][$i],
                            'estatus'       => "Pendiente",
                            'delegacion'    => $sede_a_guardar,
                            'tipo_pago'     => $data["tipo_pagoAgenda"][$i],
                        ];
                        $monto = $monto + $data["monto_pagos"][$i];
                        Pagos::create($data_pagos);
                    }else{
                        $data_pagos = [
                            'id_solicitud'  => $data["id"],
                            'fecha'         => $data["dias_pagos"][$i],
                            'hora'          => $data["hora_pagos"][$i], 
                            'monto'         => $data["monto_pagos"][$i], 
                            'descripcion'   => $data["descripcion_pagos"][$i],
                            'estatus'       => "Pendiente",
                            'delegacion'    => $sede_a_guardar,
                            'tipo_pago'     => "Audiencia",
                        ];
                        $monto = $monto + $data["monto_pagos"][$i];
                        Pagos::create($data_pagos);
                    }
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
                        'tipo_pago'     => "Audiencia"
                    ];
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
                        'tipo_pago'     => "Audiencia"
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
            
            $solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();
            $numero_audiencia = $this->GeneraAudiencia($data["id"]);
            //Actualizar Audiencia
            $data_conciliador = [
                'id_solicitud'          => $data["id"],
                'numero_audiencia'      => $numero_audiencia["0"],
                'numero_audiencias'     => $numero_audiencia["1"],
                'estatus_conciliacion'  => $data["conclucion"],
                'monto'                 => $monto,
                'rfc'                   => $solicitante["rfc"],
                'NSS'                   => $solicitante["nss"],
                'multa'                 => 'No',
                'tipo'                  => $data["tipo_audiencia"],
                'validado'              => 'Validado',
                'consecutivo'           =>  $numero_audiencia[1],
                'resolicion_primera'    =>  $data["primera"],
                'resolicion_justificacion'=>  $data["justificacion"],
                'resolicion_segunda'    =>  $data["segunda"],
                'conclucion'            =>  $data["conclucion"],
                'vacaciones'            =>  $data["vacaciones"],
                'aguinaldo'             =>  $data["aguinaldo"],
                'otros'                 =>  $data["otros"],
                'horario'               =>  $data["horario"],
                'comida'                =>  $data["comida"],
                'tipo_audiencia'        =>  $data["tipo_audiencia"],
            ];
            SeerPerConciliador::create($data_conciliador);

            $solicitud = SeerPerGeneral::find($data["id"])
            ->update([
                'tipo'                  => $data["tipo_audiencia"],
                'fecha_terminacion'     => $fecha_actual, 
                //'conciliador_id'        => $user->id,
                'estatus'               => $data["conclucion"]
            ]);
            
            $pena_convencional =  $data['pena_convencional'] ?? null;
            $direccion_convenio = $data['direccion_convenio'] ?? null;
            $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
            Audiencias::where('id_solicitud',$data["id"])
            ->orderBy('id_solicitud','desc')
            ->update([
                'numero_audiencia'  =>  $numAudiencia+1,
                'folio_audiencia'   =>  $numero_audiencia[0],
                'pena_convencional'  =>  $data['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $data['direccion_convenio'] ?? null,
            ]);

            //Actualiza el campo aparece_convenio de la tabla citados a los citados que responderán o los que pagarán los cumplimientos
            $apareceConvenio = isset($data['aparece_convenio']) && is_array($data['aparece_convenio'])
            ? array_keys($data['aparece_convenio'])
            : [];

            $representantes = SeerCitados::where('id_solicitud', $request->id)->pluck('id');
            SeerCitados::whereIn('id', $representantes)->update(['aparece_convenio' => 0]);
            
            if (!empty($apareceConvenio)) {
                SeerCitados::whereIn('id', $apareceConvenio)->update(['aparece_convenio' => 1]);
            }
        }
        else{
            $solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();
            $numero_audiencia = $this->GeneraAudiencia($data["id"]);
            //Actualizar Audiencia
            $data_conciliador = [
                'id_solicitud'          => $data["id"],
                'audiencia_id'          => $audiencia_id,
                'numero_audiencia'      => $numero_audiencia["0"],
                'numero_audiencias'     => $numero_audiencia["1"],
                'estatus_conciliacion'  => $data["conclucion"],
                'monto'                 => 0,
                'rfc'                   => $solicitante["rfc"],
                'NSS'                   => $solicitante["nss"],
                'multa'                 => 'No',
                'tipo'                  => "Presencial",
                'validado'              => 'Validado',
                'consecutivo'           =>  $numero_audiencia[1],
                'resolicion_primera'    => $data["primera"],
                'resolicion_justificacion'  => $data["justificacion"],
                'resolicion_segunda'    => $data["segunda"],
                'conclucion'            => $data["conclucion"],
                /*
                'vacaciones'            => $data[""],
                'aguinaldo'             => $data[""],
                'otros'                 => $data[""],
                'horario'               => $data[""],
                'comida'                => $data[""],
                'tipo_audiencia'        => $data[""]
                */
            ];
            SeerPerConciliador::create($data_conciliador);

            $solicitud = SeerPerGeneral::find($data["id"])
            ->update([
                'tipo'                  => "Presencial",
                'fecha_terminacion'     => $fecha_actual, 
                //'conciliador_id'        => $user->id,
                'observaciones'         => $data["observaciones"], 
                'estatus'               => $data["conclucion"]
            ]);

            $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
            Audiencias::where('id_solicitud',$data["id"])
            ->orderBy('id_solicitud','desc')
            ->latest()
            ->first()
            ->update([
                'numero_audiencia'  =>  $numAudiencia+1,
                'folio_audiencia'   =>  $numero_audiencia[0],
                'pena_convencional'  =>  $data['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $data['direccion_convenio'] ?? null,
                'estatus'           => 'No conciliacion',
            ]);

            $citadosPorCentro = SeerCitados::where('id_solicitud', $id)->get();
            foreach($citadosPorCentro as $citado){
                if($citado->notificacion == 'Centro'){
                    $hayCentro = true;
                    break;
                }
            }

            if($hayCentro){
                $citados = SeerCitados::where('id_solicitud', $data["id"])
                        ->where('notificacion', 'Centro')
                        ->where('id_abogado', '!=' , NULL)
                        ->where('resulte_responsable', 'No')
                        ->update(['aparece_convenio' => 1]);
            } else {
                SeerCitados::where('id_solicitud', $data["id"])
                            ->where('id_abogado', '!=' , NULL)
                            ->where('resulte_responsable', 'No')
                            ->update(['aparece_convenio' => 1]);
            }

            try {
                $keys = [
                    "audiencia_conclucion_data_{$data['id']}",
                    "convenio_citados_{$data['id']}",
                    "acta_citados_{$data['id']}",
                    "audiencia_data_{$data['id']}",
                    'preserve_edit_session'
                ];
                session()->forget($keys);
            } catch (\Exception $e) {
            }

            return redirect()->route('todas_audiencias');
        }

        if($data["valor"] == 1){
            if ($solicitud->tipo_solicitud == 1) {
                return redirect()->route('vista_previa',compact('id_solicitud'));
            } else if ($solicitud->tipo_solicitud == 2) {
                return redirect()->route('vista_previa_patronal', compact('id_solicitud'));
            }
        }
        if($data["valor"] == 2){
            return redirect()->route('audiencias.conciliador');
        }
    }

    public function concluir_audiencia_no_conciliacion(Request $request){
        $data = $request->all();

        $id_solicitud = $data["id"];
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        $solicitudOriginal = SeerPerGeneral::find($data["id"]);
        $sede_a_guardar = $solicitudOriginal->delegacion ?? $user->delegacion;

        $solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();
        $numero_audiencia = $this->GeneraAudiencia($data["id"]);
        //Actualizar Audiencia
        $data_conciliador = [
            'id_solicitud'          => $data["id"],
            'numero_audiencia'      => $numero_audiencia["0"],
            'numero_audiencias'     => $numero_audiencia["1"],
            'estatus_conciliacion'  => $data["conclucion"],
            'monto'                 => 0,
            'rfc'                   => $solicitante["rfc"],
            'NSS'                   => $solicitante["nss"],
            'multa'                 => 'No',
            'tipo'                  => "Presencial",
            'validado'              => 'Validado',
            'consecutivo'           => $numero_audiencia[1],
            'resolicion_primera'    => $data["primera"],
            'resolicion_justificacion'  => $data["justificacion"],
            'resolicion_segunda'    => $data["segunda"],
            'conclucion'            => $data["conclucion"],
            /*
            'vacaciones'            => $data[""],
            'aguinaldo'             => $data[""],
            'otros'                 => $data[""],
            'horario'               => $data[""],
            'comida'                => $data[""],
            'tipo_audiencia'        => $data[""]
            */
        ];
        SeerPerConciliador::create($data_conciliador);

        SeerPerGeneral::find($data["id"])
        ->update([
            'tipo'                  => "Presencial",
            'fecha_terminacion'     => $fecha_actual, 
            'conciliador_id'        => $user->id,
            'observaciones'         => $data["observaciones"], 
            'estatus'               => $data["conclucion"]
        ]);

        $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
        ->orderBy('id_solicitud','desc')
        ->update([
            'numero_audiencia'  =>  $numAudiencia+1,
            'folio_audiencia'   =>  $numero_audiencia[0],
            'estatus'           => 'No conciliacion',
        ]);

        return redirect()->route('audiencia_index');
    }
    
    // PDF Convenio para solicitudes
    public function VerPDFConvenioSol($id, Request $request){
        $solicitud = SeerPerGeneral::find($id); 
        // Priorizar datos temporales de la vista previa guardados en sesión
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        $sessionData = session()->get($sessionKey);
        if ($sessionData && is_array($sessionData)) {
            $datosAudiencia = (object) [
                'resolicion_primera' => $sessionData['primera'] ?? '',
                'resolucion_primera' => $sessionData['primera'] ?? '',
                'resolicion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolucion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolicion_segunda' => $sessionData['segunda'] ?? '',
                'resolucion_segunda' => $sessionData['segunda'] ?? '',
                'vacaciones' => $sessionData['vacaciones'] ?? null,
                'aguinaldo' => $sessionData['aguinaldo'] ?? null,
                'otros' => $sessionData['otros'] ?? null,
                'horario' => $sessionData['horario'] ?? null,
                'comida' => $sessionData['comida'] ?? null,
                'tipo_audiencia' => $sessionData['tipo_audiencia'] ?? null,
                'conclucion' => $sessionData['conclucion'] ?? null,
                'pena_convencional' =>  $sessionData['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $sessionData['direccion_convenio'] ?? null
            ];
        } else {
            $datosAudiencia = SeerPerConciliador::where('id_solicitud', $id)
                ->orderBy('numero_audiencias', 'DESC')
                ->first();
            $datosExtraAudiencia = Audiencias::where('id_solicitud', $id)->orderBy('numero_audiencia', 'DESC')->first();
            if($datosAudiencia){
                $datosAudiencia->pena_convencional = $datosExtraAudiencia ? $datosExtraAudiencia->pena_convencional : '';
                $datosAudiencia->direccion_convenio = $datosExtraAudiencia ? $datosExtraAudiencia->direccion_convenio : '';
            }
            elseif(!$datosAudiencia && $datosExtraAudiencia){
                $datosAudiencia = (object)[
                    'pena_convencional' =>  $datosExtraAudiencia->pena_convencional,
                    'direccion_convenio'    =>  $datosExtraAudiencia->direccion_convenio,
                ];

            }
        }
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();
        $municipio = Municipios::find($solicitud->municipio_rat);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($solicitud->estado_rat);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';
        $abogado = null;
        $abogadosConvenio = collect();
        $descripcionIdentificacionPMap = [];

        $conceptosTexto = [];
        $deduccionesTexto = [];

        if ($sessionData && is_array($sessionData)) {
            $prestaciones = collect();
            $tipos = $sessionData['tipo_pago'] ?? [];
            $montos_p = $sessionData['monto_pago'] ?? [];
            $otras = $sessionData['otra_prestacion'] ?? [];
            $countPrest = max(count($tipos), count($montos_p));
            for ($i = 0; $i < $countPrest; $i++) {
                $descripcion = $tipos[$i] ?? '';
                if (($descripcion === 'Otras' || $descripcion === 'Otras') && isset($otras[$i]) && trim($otras[$i]) !== '') {
                    $descripcion = trim($otras[$i]);
                }
                $montoVal = isset($montos_p[$i]) ? floatval($montos_p[$i]) : 0;
                $obj = (object) [
                    'id' => 's_p_'.$i,
                    'descripcion' => $descripcion,
                    'monto' => $montoVal,
                ];
                $prestaciones->push($obj);
            }

            $deducciones = collect();
            $descDed = $sessionData['descripcion_deduccion'] ?? [];
            $montosDed = $sessionData['monto_deduccion'] ?? [];
            $countDed = max(count($descDed), count($montosDed));
            for ($i = 0; $i < $countDed; $i++) {
                $montoVal = isset($montosDed[$i]) ? floatval($montosDed[$i]) : 0;
                $obj = (object) [
                    'id' => 's_d_'.$i,
                    'descripcion' => $descDed[$i] ?? '',
                    'monto' => $montoVal,
                ];
                $deducciones->push($obj);
            }

            // pagos desde sesión
            $pagos = collect();
            $dias = $sessionData['dias_pagos'] ?? [];
            $horas = $sessionData['hora_pagos'] ?? [];
            $montosPag = $sessionData['monto_pagos'] ?? [];
            $descPag = $sessionData['descripcion_pagos'] ?? [];
            $countPag = max(count($dias), count($montosPag));
            for ($i = 0; $i < $countPag; $i++) {
                $obj = (object) [
                    'id_solicitud' => $id,
                    'fecha' => $dias[$i] ?? null,
                    'hora' => $horas[$i] ?? null,
                    'monto' => isset($montosPag[$i]) ? floatval($montosPag[$i]) : 0,
                    'descripcion' => $descPag[$i] ?? '',
                ];
                $pagos->push($obj);
            }

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }
            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = collect($prestaciones)->sum('monto');
            $totalDeducciones = collect($deducciones)->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        } else {
            $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();
            $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }

            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = $prestaciones->sum('monto');
            $totalDeducciones = $deducciones->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        }

        // Asegurar que $datosAudiencia tenga la propiedad monto (si se uso sesión puede no existir)
        if (isset($datosAudiencia) && is_object($datosAudiencia) && !property_exists($datosAudiencia, 'monto')) {
            // Tomar monto de la sesión si existe, si no usar el cálculo de prestaciones menos deducciones
            $datosAudiencia->monto = isset($sessionData) && is_array($sessionData) && isset($sessionData['monto']) ? $sessionData['monto'] : $pagoTotal;
        }
        
        
        $pagosCount = $pagos instanceof \Illuminate\Support\Collection ? $pagos->count() : (is_countable($pagos) ? count($pagos) : 0);
        $pagosDif = (object) [
            'C_pagos' => max(1, (int) $pagosCount),
        ];

        $conciliador = User::join("audiencias", "audiencias.id_conciliador", "=", "users.id")
        ->where("audiencias.id_solicitud", "=", $id)
        ->latest('audiencias.created_at')
        ->select("users.name")
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
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        $audienciaId = $request->query->get('audiencia_id');
        if (is_array($audienciaId)) {
            $audienciaId = $audienciaId[0] ?? null;
        }

        if ($solicitud->tipo_solicitud == 2) {
            $audienciaPoder = Audiencias::where('id', $audienciaId)->first();
            $solicitantePoder = SeerSolicitante::where('id_solicitud', $solicitud["id"])->first();
            $descripcionIdentificacionPoder = $this->descripcionIdentificacion($audienciaPoder->poder->tipo_identificacion);
        }

        // $dias_descanso = $solicitud->dias !== null ? 7 - $solicitud->dias : null;
        $salario_diario = $this->calcularSalarioDiario($solicitante->pago, $solicitante->periodo_pago);
        $salario_mensual = $salario_diario * 30;
        $diarioTexto = $this->convertirNumerosALetras($salario_diario);
        $mensualTexto = $this->convertirNumerosALetras($salario_mensual);
        $montoTexto = $this->convertirNumerosALetras($datosAudiencia->monto);
        $montoPena = is_numeric($datosAudiencia->pena_convencional) ? $datosAudiencia->pena_convencional : 0;
        $penaTexto = $this->convertirNumerosALetras($montoPena);

        $idsSession = session()->get('convenio_citados_' . $id);

        if($solicitud->tipo_solicitud == 1){
            if ($idsSession !== null) {
            // Si existen en sesión, filtramos por esos IDs específicos
            $citados = SeerCitados::whereIn('id', $idsSession)
                        ->where('id_solicitud', $id)
                        ->get();
            } /*else {
                $allCentro = 1;
                $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
                foreach ($citadosCentro as $citado){
                    if($citado->notificacion == 'Centro'){
                        $allCentro = 0;
                        break;
                    }
                }
                // Si no hay sesión (ej. el usuario recargó o entró directo), usamos la lógica de BD
                if($allCentro == 0) {
                    $citados = SeerCitados::where('id_solicitud', $id)
                            ->where('aparece_convenio', 1)
                            ->where('tipo_notificacion', '!=', 'Multa')
                            ->where('notificacion', 'Centro')
                            ->get();
                }
                else {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('aparece_convenio', 1)->get();
                }
            }*/
            else {
                $audienciaId = request()->query('audiencia_id');
                $citados = SeerCitados::where('audiencia_id', $audienciaId)
                        ->where('tipo_notificacion', '!=', 'Multa')
                        ->where('aparece_convenio', 1)
                        ->get();
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $audienciaId = request()->query('audiencia_id');
            if (is_array($audienciaId)) {
                $audienciaId = $audienciaId[0] ?? null;
            }

            if (!empty($audienciaId)) {
                $citados = SeerCitados::where('id_solicitud', $id)
                            ->where('audiencia_id', $audienciaId)
                            ->get();
            } else {
                $hayNotificadosenAudiencia = SeerCitados::where('id_solicitud', $id)->where('estatus', 'Notificado en Audiencia')->exists();
                $hayCentro = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->exists();

                if ($hayNotificadosenAudiencia) {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('estatus', 'Notificado en Audiencia')->get();
                } else if ($hayCentro) {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $id)->get();
                }
            }

            foreach ($citados as $citado) {
                $citadoID = $citado->id ?? null;
                if ($citadoID !== null) {
                    $descripcionIdentificacionCitado[$citadoID] = $this->descripcionIdentificacion($citado->tipo_identificacion_comparecencia);
                }
            }
        }
        

        // Obtener TODOS los representantes/abogados distintos que correspondan a los citados incluidos en el convenio
        $citadoIdsParaConvenio = $citados instanceof \Illuminate\Support\Collection ? $citados->pluck('id')->filter()->values()->all() : [];

        if($solicitud->tipo_solicitud == 1){
            if (!empty($citadoIdsParaConvenio)) {
                $citadosConHist = $citados->filter(fn($c) => !empty($c->id_historial));
                $citadosSinHist = $citados->filter(fn($c) => empty($c->id_historial));

                $abogadosConvenio = collect();

                if ($citadosConHist->isNotEmpty()) {
                    $idsConHist = $citadosConHist->pluck('id')->filter()->values()->all();

                    $abogadosHist = \App\Models\HistorialAbogado::join('seer_citados as sc', 'sc.id_historial', '=', 'historial_abogados.id')
                        ->where('sc.id_solicitud', $id)
                        ->whereIn('sc.id', $idsConHist)
                        ->select(
                            'historial_abogados.id',
                            'historial_abogados.nombres_patronal',
                            'historial_abogados.primer_apellido_patronal',
                            'historial_abogados.segundo_apellido_patronal',
                            'historial_abogados.descipcion_poder',
                            'historial_abogados.tipo_identificacion',
                            'historial_abogados.num_identificacion',
                            'historial_abogados.nombre_representante',
                            'historial_abogados.primer_apellido_representante',
                            'historial_abogados.segundo_apellido_representante',
                            'historial_abogados.estado_patronal',
                            'historial_abogados.municipio_patronal',
                            'historial_abogados.tipo_vialidad_patronal',
                            'historial_abogados.vialidad_patronal',
                            'historial_abogados.num_ext_patronal',
                            'historial_abogados.mun_int_patronal',
                            'historial_abogados.colonia_patronal',
                            'historial_abogados.cp_patronal',
                            'historial_abogados.id_abogado as idAbogado'
                        )
                        ->distinct()
                        ->get();

                    $abogadosConvenio = $abogadosConvenio->merge($abogadosHist);
                }

                if ($citadosSinHist->isNotEmpty()) {
                    $idsSinHist = $citadosSinHist->pluck('id')->filter()->values()->all();

                    $abogadosPoder = Poder::join('seer_citados as sc', 'sc.id_abogado', '=', 'abogados.idAbogado')
                        ->where('sc.id_solicitud', $id)
                        ->whereIn('sc.id', $idsSinHist)
                        ->select(
                            'abogados.idAbogado',
                            'abogados.nombres_patronal',
                            'abogados.primer_apellido_patronal',
                            'abogados.segundo_apellido_patronal',
                            'abogados.descipcion_poder',
                            'abogados.tipo_identificacion',
                            'abogados.num_identificacion',
                            'abogados.nombre_representante',
                            'abogados.primer_apellido_representante',
                            'abogados.segundo_apellido_representante',
                            'abogados.estado_patronal',
                            'abogados.municipio_patronal',
                            'abogados.tipo_vialidad_patronal',
                            'abogados.vialidad_patronal',
                            'abogados.num_ext_patronal',
                            'abogados.mun_int_patronal',
                            'abogados.colonia_patronal',
                            'abogados.cp_patronal'
                        )
                        ->distinct()
                        ->get();

                    $abogadosConvenio = $abogadosConvenio->merge($abogadosPoder);
                }

                $abogadosConvenio = $abogadosConvenio->unique('idAbogado')->values();

                $abogado = $abogadosConvenio->first();
                foreach ($abogadosConvenio as $rep) {
                    $repId = $rep->idAbogado ?? null;
                    if ($repId !== null) {
                        $descripcionIdentificacionPMap[$repId] = $this->descripcionIdentificacion($rep->tipo_identificacion);
                    }
                }
            }
        }
        
        //$citados = SeerCitados::where('id_solicitud', $id)->get();
        /*$citados = SeerCitados::where('id_solicitud', $id)
        ->where('aparece_convenio', 1)
        ->get();*/

        /*$audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id");
        $audiencia = $audiencia->where("audiencias.id_solicitud", "=", $solicitud["id"])
        ->first();*/
        $audiencia = SeerPerGeneral::join("audiencias", "audiencias.id_solicitud", "=", "seer_general.id")
            ->where("audiencias.id_solicitud", "=", $solicitud->id)
            ->latest('audiencias.created_at')
            ->first();

        // Descripción del tipo de identificación para los solicitantes y poderes
        $identificacionSolicitante = $solicitante->identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);
        $identificacionPoder = $abogado ? $abogado->tipo_identificacion : null;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);
        $ultimoCitatorio = SeerCitados::where('id_solicitud', $id)->latest()->first();

        if ($solicitud->tipo_solicitud == 1) {
            $html = view('PDF/Solicitudes/convenioSolicitud',
            compact('id', 'solicitud', /*'dias_descanso',*/ 'salario_diario','salario_mensual','pagos','diarioTexto','penaTexto','mensualTexto','montoTexto',/*'vacacionesTexto',
            'primaTexto','aguinaldoTexto','DSueldoTexto','antiguedadTexto','gratificacionATexto','gratificacionBTexto','gratificacionCTexto','gratificacionDTexto',
            'gratificacionETexto','gratificacionFTexto','otrasTexto',*/'pagosDif','conciliador','prestaciones','solicitante','citados','audiencia','pagoTotal','abogado',
            'conceptosTexto', 'deduccionesTexto','municipioEmpresa', 'estadoEmpresa','descripcionIdentificacionS', 'descripcionIdentificacionP','prestaciones','deducciones','datosAudiencia','delegado',
            'abogadosConvenio', 'descripcionIdentificacionPMap','ultimoCitatorio'))
            ->render();
        } else if ($solicitud->tipo_solicitud == 2) {
            $html = view('PDF/Solicitudes/convenioSolicitud',
            compact('id', 'solicitud', /*'dias_descanso',*/ 'salario_diario','salario_mensual','pagos','diarioTexto','penaTexto','mensualTexto','montoTexto',/*'vacacionesTexto',
            'primaTexto','aguinaldoTexto','DSueldoTexto','antiguedadTexto','gratificacionATexto','gratificacionBTexto','gratificacionCTexto','gratificacionDTexto',
            'gratificacionETexto','gratificacionFTexto','otrasTexto',*/'pagosDif','conciliador','prestaciones','solicitante','citados','audiencia','pagoTotal','abogado',
            'conceptosTexto', 'deduccionesTexto','municipioEmpresa', 'estadoEmpresa', 'citados','descripcionIdentificacionS','prestaciones','deducciones','datosAudiencia','delegado',
            'abogadosConvenio', 'descripcionIdentificacionPMap','ultimoCitatorio', 'solicitantePoder', 'descripcionIdentificacionPoder', 'descripcionIdentificacionCitado', 'audienciaPoder'))
            ->render();
        }

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('Convenio_solicitud.pdf');          
    }

    public function VerPDFConvenioRei($id){
        $solicitud = SeerPerGeneral::find($id); 
        $motivos = SeerMotivo::join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
            ->where('id_solicitud', $id)
            ->select('catalogo_motivos.motivo')
            ->get()
            ->values();
            
        // Priorizar datos temporales de la vista previa guardados en sesión
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        $sessionData = session()->get($sessionKey);
        if ($sessionData && is_array($sessionData)) {
            $datosAudiencia = (object) [
                'resolicion_primera' => $sessionData['primera'] ?? '',
                'resolucion_primera' => $sessionData['primera'] ?? '',
                'resolicion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolucion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolicion_segunda' => $sessionData['segunda'] ?? '',
                'resolucion_segunda' => $sessionData['segunda'] ?? '',
                'vacaciones' => $sessionData['vacaciones'] ?? null,
                'aguinaldo' => $sessionData['aguinaldo'] ?? null,
                'otros' => $sessionData['otros'] ?? null,
                'horario' => $sessionData['horario'] ?? null,
                'comida' => $sessionData['comida'] ?? null,
                'tipo_audiencia' => $sessionData['tipo_audiencia'] ?? null,
                'conclucion' => $sessionData['conclucion'] ?? null,
                'pena_convencional' =>  $sessionData['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $sessionData['direccion_convenio'] ?? null
            ];
        } else {
            $datosAudiencia = SeerPerConciliador::where('id_solicitud', $id)
                ->orderBy('numero_audiencias', 'DESC')
                ->first();
            $datosExtraAudiencia = Audiencias::where('id_solicitud', $id)->orderBy('numero_audiencia', 'DESC')->first();
            if($datosAudiencia){
                $datosAudiencia->pena_convencional = $datosExtraAudiencia ? $datosExtraAudiencia->pena_convencional : '';
                $datosAudiencia->direccion_convenio = $datosExtraAudiencia ? $datosExtraAudiencia->direccion_convenio : '';
            }
            elseif(!$datosAudiencia && $datosExtraAudiencia){
                $datosAudiencia = (object)[
                    'pena_convencional' =>  $datosExtraAudiencia->pena_convencional,
                    'direccion_convenio'    =>  $datosExtraAudiencia->direccion_convenio,
                ];

            }
        }
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();
        $municipio = Municipios::find($solicitud->municipio_rat);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($solicitud->estado_rat);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';
        $abogado = null;
        $abogadosConvenio = collect();
        $descripcionIdentificacionPMap = [];

        $conceptosTexto = [];
        $deduccionesTexto = [];

        if ($sessionData && is_array($sessionData)) {
            $prestaciones = collect();
            $tipos = $sessionData['tipo_pago'] ?? [];
            $montos_p = $sessionData['monto_pago'] ?? [];
            $otras = $sessionData['otra_prestacion'] ?? [];
            $countPrest = max(count($tipos), count($montos_p));
            for ($i = 0; $i < $countPrest; $i++) {
                $descripcion = $tipos[$i] ?? '';
                if (($descripcion === 'Otras' || $descripcion === 'Otras') && isset($otras[$i]) && trim($otras[$i]) !== '') {
                    $descripcion = trim($otras[$i]);
                }
                $montoVal = isset($montos_p[$i]) ? floatval($montos_p[$i]) : 0;
                $obj = (object) [
                    'id' => 's_p_'.$i,
                    'descripcion' => $descripcion,
                    'monto' => $montoVal,
                ];
                $prestaciones->push($obj);
            }

            $deducciones = collect();
            $descDed = $sessionData['descripcion_deduccion'] ?? [];
            $montosDed = $sessionData['monto_deduccion'] ?? [];
            $countDed = max(count($descDed), count($montosDed));
            for ($i = 0; $i < $countDed; $i++) {
                $montoVal = isset($montosDed[$i]) ? floatval($montosDed[$i]) : 0;
                $obj = (object) [
                    'id' => 's_d_'.$i,
                    'descripcion' => $descDed[$i] ?? '',
                    'monto' => $montoVal,
                ];
                $deducciones->push($obj);
            }

            // pagos desde sesión
            $pagos = collect();
            $dias = $sessionData['dias_pagos'] ?? [];
            $horas = $sessionData['hora_pagos'] ?? [];
            $montosPag = $sessionData['monto_pagos'] ?? [];
            $descPag = $sessionData['descripcion_pagos'] ?? [];
            $countPag = max(count($dias), count($montosPag));
            for ($i = 0; $i < $countPag; $i++) {
                $obj = (object) [
                    'id_solicitud' => $id,
                    'fecha' => $dias[$i] ?? null,
                    'hora' => $horas[$i] ?? null,
                    'monto' => isset($montosPag[$i]) ? floatval($montosPag[$i]) : 0,
                    'descripcion' => $descPag[$i] ?? '',
                ];
                $pagos->push($obj);
            }

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }
            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = collect($prestaciones)->sum('monto');
            $totalDeducciones = collect($deducciones)->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        } else {
            $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();
            $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }

            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = $prestaciones->sum('monto');
            $totalDeducciones = $deducciones->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        }

        $fecha_reinstalacion = null;
        try {
            if ($pagos instanceof \Illuminate\Support\Collection) {
                $pagoReinst = $pagos->first(function ($p) {
                    return isset($p->monto) && is_numeric($p->monto) && (float) $p->monto == 0.0 && !empty($p->fecha);
                });
                if ($pagoReinst && !empty($pagoReinst->fecha)) {
                    $fecha_reinstalacion = $pagoReinst->fecha;
                }
            }

            if (empty($fecha_reinstalacion)) {
                $pagoReinstDb = Pagos::where('id_solicitud', $id)
                    ->where('tipo_pago', 'Audiencia')
                    ->where('monto', 0)
                    ->orderBy('fecha', 'asc')
                    ->first();
                if ($pagoReinstDb && !empty($pagoReinstDb->fecha)) {
                    $fecha_reinstalacion = $pagoReinstDb->fecha;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo determinar fecha_reinstalacion en VerPDFConvenioRei', [
                'id_solicitud' => $id,
                'error' => $e->getMessage(),
            ]);
        }

        if (empty($fecha_reinstalacion)) {
            $fecha_reinstalacion = $datosAudiencia->fecha_audiencia
                ?? ($audiencia->fecha_audiencia ?? null);
        }

        // Asegurar que $datosAudiencia tenga la propiedad monto (si se uso sesión puede no existir)
        if (isset($datosAudiencia) && is_object($datosAudiencia) && !property_exists($datosAudiencia, 'monto')) {
            // Tomar monto de la sesión si existe, si no usar el cálculo de prestaciones menos deducciones
            $datosAudiencia->monto = isset($sessionData) && is_array($sessionData) && isset($sessionData['monto']) ? $sessionData['monto'] : $pagoTotal;
        }
        
        
        $pagosCount = $pagos instanceof \Illuminate\Support\Collection ? $pagos->count() : (is_countable($pagos) ? count($pagos) : 0);
        $pagosDif = (object) [
            'C_pagos' => max(1, (int) $pagosCount),
        ];

        $conciliador = User::join("seer_general", "seer_general.conciliador_id", "=", "users.id")
        ->where("seer_general.id", "=", $id)
        ->select("users.name")
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
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        // $dias_descanso = $solicitud->dias !== null ? 7 - $solicitud->dias : null;
        $salario_diario = $this->calcularSalarioDiario($solicitante->pago, $solicitante->periodo_pago);
        $salario_mensual = $salario_diario * 30;
        $diarioTexto = $this->convertirNumerosALetras($salario_diario);
        $mensualTexto = $this->convertirNumerosALetras($salario_mensual);
        $montoTexto = $this->convertirNumerosALetras($datosAudiencia->monto);
        $montoPena = is_numeric($datosAudiencia->pena_convencional) ? $datosAudiencia->pena_convencional : 0;
        $penaTexto = $this->convertirNumerosALetras($montoPena);

        $idsSession = session()->get('convenio_citados_' . $id);

        if($solicitud->tipo_solicitud == 1){
            if ($idsSession !== null) {
            // Si existen en sesión, filtramos por esos IDs específicos
            $citados = SeerCitados::whereIn('id', $idsSession)
                        ->where('id_solicitud', $id)
                        ->get();
            } else {
                $allCentro = 1;
                $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
                foreach ($citadosCentro as $citado){
                    if($citado->notificacion == 'Centro'){
                        $allCentro = 0;
                        break;
                    }
                }
                // Si no hay sesión (ej. el usuario recargó o entró directo), usamos la lógica de BD
                if($allCentro == 0) {
                    $citados = SeerCitados::where('id_solicitud', $id)
                            ->where('aparece_convenio', 1)
                            ->where('tipo_notificacion', '!=', 'Multa')
                            ->where('notificacion', 'Centro')
                            ->get();
                }
                else {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('aparece_convenio', 1)->get();
                }
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            $audienciaId = request()->query('audiencia_id');
            if (is_array($audienciaId)) {
                $audienciaId = $audienciaId[0] ?? null;
            }

            if (!empty($audienciaId)) {
                $citados = SeerCitados::where('id_solicitud', $id)
                            ->where('audiencia_id', $audienciaId)
                            ->get();
            } else {
                $hayNotificadosenAudiencia = SeerCitados::where('id_solicitud', $id)->where('estatus', 'Notificado en Audiencia')->exists();
                $hayCentro = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->exists();

                if ($hayNotificadosenAudiencia) {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('estatus', 'Notificado en Audiencia')->get();
                } else if ($hayCentro) {
                    $citados = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $id)->get();
                }
            }
        }

        // Obtener TODOS los representantes/abogados distintos que correspondan a los citados incluidos en el convenio
        $citadoIdsParaConvenio = $citados instanceof \Illuminate\Support\Collection ? $citados->pluck('id')->filter()->values()->all() : [];

        if (!empty($citadoIdsParaConvenio)) {
            $citadosConHist = $citados->filter(fn($c) => !empty($c->id_historial));
            $citadosSinHist = $citados->filter(fn($c) => empty($c->id_historial));

            $abogadosConvenio = collect();

            if ($citadosConHist->isNotEmpty()) {
                $idsConHist = $citadosConHist->pluck('id')->filter()->values()->all();

                $abogadosHist = \App\Models\HistorialAbogado::join('seer_citados as sc', 'sc.id_historial', '=', 'historial_abogados.id')
                    ->where('sc.id_solicitud', $id)
                    ->whereIn('sc.id', $idsConHist)
                    ->select(
                        'historial_abogados.id',
                        'historial_abogados.nombres_patronal',
                        'historial_abogados.primer_apellido_patronal',
                        'historial_abogados.segundo_apellido_patronal',
                        'historial_abogados.descipcion_poder',
                        'historial_abogados.tipo_identificacion',
                        'historial_abogados.num_identificacion',
                        'historial_abogados.nombre_representante',
                        'historial_abogados.primer_apellido_representante',
                        'historial_abogados.segundo_apellido_representante',
                        'historial_abogados.estado_patronal',
                        'historial_abogados.municipio_patronal',
                        'historial_abogados.tipo_vialidad_patronal',
                        'historial_abogados.vialidad_patronal',
                        'historial_abogados.num_ext_patronal',
                        'historial_abogados.mun_int_patronal',
                        'historial_abogados.colonia_patronal',
                        'historial_abogados.cp_patronal',
                        'historial_abogados.id_abogado as idAbogado',
                        'historial_abogados.tipo_documento_representante'
                    )
                    ->distinct()
                    ->get();

                $abogadosConvenio = $abogadosConvenio->merge($abogadosHist);
            }

            if ($citadosSinHist->isNotEmpty()) {
                $idsSinHist = $citadosSinHist->pluck('id')->filter()->values()->all();

                $abogadosPoder = Poder::join('seer_citados as sc', 'sc.id_abogado', '=', 'abogados.idAbogado')
                    ->where('sc.id_solicitud', $id)
                    ->whereIn('sc.id', $idsSinHist)
                    ->select(
                        'abogados.idAbogado',
                        'abogados.nombres_patronal',
                        'abogados.primer_apellido_patronal',
                        'abogados.segundo_apellido_patronal',
                        'abogados.descipcion_poder',
                        'abogados.tipo_identificacion',
                        'abogados.num_identificacion',
                        'abogados.nombre_representante',
                        'abogados.primer_apellido_representante',
                        'abogados.segundo_apellido_representante',
                        'abogados.estado_patronal',
                        'abogados.municipio_patronal',
                        'abogados.tipo_vialidad_patronal',
                        'abogados.vialidad_patronal',
                        'abogados.num_ext_patronal',
                        'abogados.mun_int_patronal',
                        'abogados.colonia_patronal',
                        'abogados.cp_patronal',
                        'abogados.tipo_documento_representante'
                    )
                    ->distinct()
                    ->get();

                $abogadosConvenio = $abogadosConvenio->merge($abogadosPoder);
            }

            $abogadosConvenio = $abogadosConvenio->unique('idAbogado')->values();

            $abogado = $abogadosConvenio->first();
            foreach ($abogadosConvenio as $rep) {
                $repId = $rep->idAbogado ?? null;
                if ($repId !== null) {
                    $descripcionIdentificacionPMap[$repId] = $this->descripcionIdentificacion($rep->tipo_identificacion);
                }
            }
        }

        //$citados = SeerCitados::where('id_solicitud', $id)->get();
        /*$citados = SeerCitados::where('id_solicitud', $id)
        ->where('aparece_convenio', 1)
        ->get();*/

        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id");
        $audiencia = $audiencia->where("audiencias.id_solicitud", "=", $solicitud["id"])
        ->first();

        // Descripción del tipo de identificación para los solicitantes y poderes
        $identificacionSolicitante = $solicitante->identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);
        $identificacionPoder = $abogado ? $abogado->tipo_identificacion : null;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);

        $html = view('PDF/Solicitudes/convenioReinstalacion', 
        compact('id', 'solicitud', /*'dias_descanso',*/ 'salario_diario','salario_mensual','pagos','diarioTexto','penaTexto','mensualTexto','montoTexto',/*'vacacionesTexto',
        'primaTexto','aguinaldoTexto','DSueldoTexto','antiguedadTexto','gratificacionATexto','gratificacionBTexto','gratificacionCTexto','gratificacionDTexto',
        'gratificacionETexto','gratificacionFTexto','otrasTexto',*/'pagosDif','conciliador','prestaciones','solicitante','citados','audiencia','pagoTotal','abogado',
        'conceptosTexto', 'deduccionesTexto','municipioEmpresa', 'estadoEmpresa','descripcionIdentificacionS', 'descripcionIdentificacionP','prestaciones','deducciones','datosAudiencia','delegado',
        'abogadosConvenio', 'descripcionIdentificacionPMap', 'motivos', 'fecha_reinstalacion'))
        ->render();
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('Convenio_reinstalacion.pdf');          
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
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es'); 

        $parteEntera = floor($valor);
        $letras = strtoupper($numberTransformer->toWords($parteEntera)); 

        $parteDecimal = round(($valor - $parteEntera) * 100);
        $centavos = str_pad($parteDecimal, 2, '0', STR_PAD_LEFT); 
        return "{$letras} PESOS {$centavos}/100";
    }

    //PDF Acuse de solicitud
    public function PDFacuseSolicitud($id){
        $solicitud = SeerPerGeneral::find($id);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        $citados = SeerCitados::where('id_solicitud', $id)->get();
       
        $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true);

        $nombreArchivo = 'acuse_solicitud_' . $solicitud->nombre .'.pdf';
        return $pdf->stream($nombreArchivo);               
    }

    //PDF Notificación de solicitud
    public function PDFnotificacionSolicitante($id){
        $solicitud = SeerPerGeneral::find($id);

        $inicialesConcluye = $this->inicialesDeSeerGeneral($solicitud);
        $etiquetaIniciales = $this->etiquetaDelegacionSeer($solicitud->delegacion ?? null);

        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $audiencia = Audiencias::where('id_solicitud', $solicitud["id"])->first();
        $conciliador  = User::where('id', $audiencia->id_conciliador)->select('users.name')->first();

        $citados = SeerCitados::whereIn('id', function ($query) use ($id) {
            $query->selectRaw('MAX(id)')
                ->from('seer_citados')
                ->where('id_solicitud', $id)
                //->where('resulte_responsable', 'No')
                ->groupBy('nombre', 'primer_apellido', 'segundo_apellido');
        })->get();
       
        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id");
        $audiencia = $audiencia->where("audiencias.id_solicitud", "=", $solicitud["id"])
        ->latest('audiencias.created_at')
        ->first();
        $pdf = \PDF::loadView('PDF/Solicitudes/notificacionSolicitante', compact('id','solicitud','solicitante','citados','conciliador','audiencia','inicialesConcluye','etiquetaIniciales'))
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true);

        $nombreArchivo = 'notificación_solicitante_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);               
    }
    
    //PDF Multa de solicitud
    public function VerPDFMulta($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $conciliador = User::join("seer_general", "seer_general.conciliador_id", "=", "users.id")
        ->where("seer_general.id", "=", $id_solicitud)
        ->select('users.name')
        ->first();
        //$citado = SeerCitados::find($id);
        $citado = SeerCitados::find($id);
        if (!$citado) {
            return redirect()->back()->with('error', 'No se encontró el registro del citado.');
        }
        // Buscamos el registro de notificación (tipo Citatorio y Notificación Centro) 
        // que coincida con los datos personales y de domicilio del citado
        $citadoOriginal = SeerCitados::where('id_solicitud', $id_solicitud)
            ->where('nombre', $citado->nombre)
            ->where('primer_apellido', $citado->primer_apellido)
            ->where('segundo_apellido', $citado->segundo_apellido)
            ->where('calle', $citado->calle)
            ->where('n_ext', $citado->n_ext)
            ->where('colonia', $citado->colonia)
            ->where('tipo_notificacion', 'Citatorio')
            ->where('notificacion', 'Centro')
            ->whereIn('estatus', [
                'Finalizado exitosamente', 
                'Exitosa por Instructivo', 
                'No notificada',
                'Notificada en Audiencia'
            ])
            ->first(); 
        
        if($citado->audiencia_id){
            $audiencia = Audiencias::where('id', $citado->audiencia_id)->first();
        } else {
            $audiencia = Audiencias::where('id_solicitud', $id_solicitud)
            //->orderBy('fecha', 'desc')
            ->first();
        }
        
        $municipio = Municipios::find($citado->municipio_citado);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($citado->estado_citado);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';

        $pdf = \PDF::loadView('PDF/Solicitudes/ActaMulta', compact('id','solicitud','citado','conciliador','audiencia','municipioEmpresa','estadoEmpresa','citadoOriginal'))
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true);

        $nombreArchivo = 'multa_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);               
    }

    //PDF Citatorio
    public function pdfCitatorio($id) {
        try {
            $citado = SeerCitados::findOrFail($id);
            $solicitud = SeerPerGeneral::findOrFail($citado->id_solicitud);
            $solicitante = SeerSolicitante::where('id_solicitud', $citado->id_solicitud)->first();
            $motivoIds = SeerMotivo::where('id_solicitud', $citado->id_solicitud)->pluck('id_motivo');
            $motivos = SolicitudMotivo::whereIn('id', $motivoIds)->get();

            $audiencia = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id")
                ->where("audiencias.id_solicitud", "=", $citado->id_solicitud)
                ->first();

            $conciliador = User::join("seer_general","seer_general.conciliador_id","=","users.id")
                ->where("seer_general.conciliador_id", "=", $solicitud->conciliador_id)
                ->select('users.name')
                ->first();
            $municipio = Municipios::find($citado->municipio_citado);
            $estado = Estados::find($citado->estado_citado);
            $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
            $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';
            $fechaEmision = $solicitud->fecha_confirmacion;
            $nombreArchivo = 'citatorio_' . $citado->nombre . '_' . $citado->primer_apellido . '.pdf';
            $nombreArchivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nombreArchivo); //Elimina los caracteres especiales no permitidos en archivos

            $pdf = \PDF::loadView('PDF/Solicitudes/citatorio', compact(
                'solicitud',
                'solicitante',
                'citado',
                'motivos',
                'audiencia',
                'conciliador','municipioNombre','estadoNombre','fechaEmision'
            ))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

            return $pdf->stream($nombreArchivo); //Visualiza los citatorios

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }  
    }
    //Consultar solicitudes(Solicitante) conciliadores
    public function consultar_solicitudes($id){
        $solicitudes = SeerPerGeneral::find($id);
        $solicitud  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitud = $solicitud->where("seer_solicitante.id_solicitud", "=", $id) ->first();
        
        //Validar si existe el abogado
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        return view('/solicitudes/verSolicitud',compact('solicitud','userRole'));
    }

    public function audiencia_fecha(){
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

        return view('audiencias/busqueda',compact('auxiliares','conciliadores'));
    }

    //Conciliadores en solicitudes audiencias
    public function historial_conciliador(Request $request){
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

        $solicitudes  = SeerPerGeneral::select("seer_general.id","seer_general.fecha","seer_general.fecha","seer_general.NUE","seer_general.tipo_solicitud","seer_general.estatus","seer_general.actividad","seer_solicitante.nombre");
        $solicitudes = $solicitudes->join("seer_solicitante","seer_solicitante.id_solicitud","seer_general.id");
        if($bandera_fechas == 1){
            $solicitudes = $solicitudes->where("seer_general.fecha",">=",$data["inicio"]);
            $solicitudes = $solicitudes->where("seer_general.fecha","<=",$data["final"]);
        }
        if($bandera_nue == 1){
            $solicitudes = $solicitudes->where("seer_general.NUE",$data["nue"]);
        }
        if($bandera_curp == 1){
            $solicitudes = $solicitudes->where("seer_solicitante.curp",$data["curp"]);
        }
        if($bandera_solicitante == 1){
            $solicitudes = $solicitudes->where("seer_solicitante.nombre",'like',$data["solicitante"]);
        }
        if($bandera_citado == 1){
            $solicitudes = $solicitudes->join("seer_citados","seer_citados.id_solicitud","seer_general.id");
            $solicitudes = $solicitudes->where("seer_citados.nombre",'like',$data["citado"]);
        }
        if($bandera_folio == 1){
            $solicitudes = $solicitudes->where("seer_general.id","=",$data["folio"]);
        }
        if($bandera_estatus == 1){
            $solicitudes = $solicitudes->where("seer_general.estatus","=",$data["estatus"]);
        }
        if($bandera_tipo == 1){
            $solicitudes = $solicitudes->where("seer_general.tipo_solicitud","=",$data["tipo"]);
        }
        if($bandera_auxiliar == 1){
            $solicitudes = $solicitudes->where("seer_general.user_id","=",$data["auxiliar"]);
        }
        if($bandera_conciliador == 1){
            $solicitudes = $solicitudes->where("seer_general.conciliador_id","=",$data["conciliador"]);
        }
        $solicitudes = $solicitudes->get();

        //return view('/solicitudes/busqueda',compact('solicitudes'));
        return view('/historial/conciliadores',compact('solicitudes'));
    }

    //Citado persona física
    public function citado_personaF(Request $request){
        $data = $request->all();
        //$citados = SeerCitados::find($data["id_citado_pf"]);
       
        $sessionKey = "audiencia_data_{$data['id']}";

        if (session()->has($sessionKey)) {
            $sessionData = session($sessionKey);
            $citados = $sessionData['citados'];

            $data_insertar= array(
                'id_solicitud'              => $data["id"],
                'id_citado'                 => $data["id_citado_pf"],
                'nombre'                    => $data["nombre"],
                'primer_apellido'           => $data["primer_apellido"], 
                'segundo_apellido'          => $data["segundo_apellido"],
                'identificacion'            => $data["identificacionAlta"],
            );
            
            $documento = $data["nombre"]."-".$data["primer_apellido"]."-".$data["segundo_apellido"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documento
            );
            $data_insertar["documentoIdentificacion"] = $documento;

            PersonaFisica::create($data_insertar);   
            $id_adiencia = PersonaFisica::select('id')->orderBy('id', 'desc')->first();
            
            $citados = $citados->map(function ($citado) use ($data, $id_adiencia) {
                if ((int)$citado->id == (int)$data['id_citado_pf']) {
                    $citado->id_fisica = $id_adiencia["id"];
                    $citado->nombre = $data["nombre"];
                    $citado->primer_apellido = $data["primer_apellido"];
                    $citado->segundo_apellido = $data["segundo_apellido"];
                }
                return $citado;
            });

            $sessionData['citados'] = $citados;
            session([$sessionKey => $sessionData]);

        } else {
            $data_insertar= array(
                'id_solicitud'              => $data["id"],
                'id_citado'                 => $data["id_citado_pf"],
                'nombre'                    => $data["nombre"],
                'primer_apellido'           => $data["primer_apellido"], 
                'segundo_apellido'          => $data["segundo_apellido"],
                'identificacion'            => $data["identificacionAlta"],
            );
            
            $documento = $data["nombre"]."-".$data["primer_apellido"]."-".$data["segundo_apellido"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documento
            );
            $data_insertar["documentoIdentificacion"] = $documento;

            PersonaFisica::create($data_insertar);   
            $id_adiencia = PersonaFisica::select('id')->orderBy('id', 'desc')->first();
            SeerCitados::find($data['id_citado_pf'])->update([
                'id_fisica'         => $id_adiencia["id"],
                'nombre'            => $data["nombre"],
                'primer_apellido'   => $data["primer_apellido"], 
                'segundo_apellido'  => $data["segundo_apellido"]
            ]);
        }

        session()->flash('preserve_edit_session', true);
        return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
    }

    //PDF Acta No conciliación
    public function VerPDFNoConciliacion($id){
        $solicitud = SeerPerGeneral::find($id);
        
        $solicitante = SeerPerGeneral::join("seer_solicitante", "seer_solicitante.id_solicitud", "=", "seer_general.id")
            ->where("seer_solicitante.id_solicitud", "=", $solicitud->id)
            ->first();

        $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name')
            ->first();

        /*$audiencia = SeerPerGeneral::join("audiencias", "audiencias.id_solicitud", "=", "seer_general.id")
            ->where("audiencias.id_solicitud", "=", $solicitud->id)
            ->first();*/
        $audiencia = SeerPerGeneral::join("audiencias", "audiencias.id_solicitud", "=", "seer_general.id")
            ->where("audiencias.id_solicitud", "=", $solicitud->id)
            ->latest('audiencias.created_at')
            ->first();
        $citados = SeerCitados::where("id_solicitud", $solicitud->id)->where('tipo_notificacion', '!=', 'Multa')->get();
        $html = '<html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                    .page-break { page-break-after: always; }
                </style>
            </head>
            <body>';

        foreach ($citados as $index => $citado) {
            $municipio = Municipios::find($citado->municipio_citado);
            $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
            $estado = Estados::find($citado->estado_citado);
            $estadoEmpresa = $estado ? $estado->nombre : 'No definido';
            $html .= view('PDF/Solicitudes/NoConciliacion', compact(
                'id', 'solicitud', 'conciliador', 'citado', 'audiencia', 'solicitante', 'municipioEmpresa', 'estadoEmpresa'
            ))->render();

            if ($index < count($citados) - 1) {
                $html .= '<div class="page-break"></div>';
            }
        }

        //$html .= '</body></html>';
        $html .= '
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $width = $fontMetrics->get_text_width($text, $font, $size);
                    $x = ($pdf->get_width() / 2) - 50;
                    $y = $pdf->get_height() - 44;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>';
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isPhpEnabled', true);
            /*->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);*/

        $nombreArchivo = 'No_Conciliacion_' . $solicitante->nombre . '.pdf';
        return $pdf->stream($nombreArchivo);                   
    }

    public function audiencias_cumplimiento(){
        // 1. Obtenemos directamente el objeto del usuario autenticado (evitamos User::find)
        $user = auth()->user();


        $cumplimientos = Pagos::where('pago_solicitud.delegacion', $user->delegacion)
            ->whereIn('pago_solicitud.tipo_pago', ["Ratificacion", "Audiencia", "Conciliador"])
            
            ->leftJoin('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('users', 'users.id', '=', 'pago_solicitud.id_conciliador')
            ->select(
            // Agrupador principal (NUE unificado)
            DB::raw("CASE 
                WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.id_solicitud != 0 THEN turnos.NUE 
                WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.NUE 
                ELSE pago_solicitud.NUE 
            END as NUE_FINAL"),
            
            DB::raw('MAX(pago_solicitud.id) as id'),
            'pago_solicitud.id_solicitud',
            DB::raw('MAX(pago_solicitud.descripcion) as descripcion'),
            'pago_solicitud.tipo_pago',
            'users.name as conciliador_name',
            
            // Usamos funciones de agregación para campos que pueden variar
            DB::raw("DATE_FORMAT(MAX(pago_solicitud.fecha), '%d/%m/%Y') as fecha_formateada"),
            DB::raw("DATE_FORMAT(MAX(pago_solicitud.hora), '%h:%i %p') as hora_formateada"),
            DB::raw("MAX(pago_solicitud.descripcion) as descripcion_pago"),

            // Agregaciones de montos y cantidades
            DB::raw("COUNT(pago_solicitud.id) as total_pagos"),
            DB::raw("SUM(pago_solicitud.monto) as monto_total"),
            DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN 1 ELSE 0 END) as pagos_realizados"),
            DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN 1 ELSE 0 END) as pagos_pendientes")
        )
        ->groupBy(
            // 1. La lógica del CASE completa
            DB::raw("CASE 
                WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.id_solicitud != 0 THEN turnos.NUE 
                WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.NUE 
                ELSE pago_solicitud.NUE 
            END"),
            // 2. Las columnas físicas que SQL detecta en la consulta
            'pago_solicitud.id_solicitud',
            'pago_solicitud.tipo_pago',
            'pago_solicitud.NUE',   // El campo de la tabla pagos
            'turnos.NUE',           // El campo de la tabla turnos (aquí estaba el error)
            'seer_general.NUE',     // El campo de la tabla seer_general
            'users.name'            // El nombre del conciliador
        )
        ->orderBy(DB::raw("MAX(pago_solicitud.fecha)"), 'asc')
        ->take(1500)
        ->get();

        return view('/cumplimientos/index',compact('cumplimientos'));
    }

    public function solicitud_audiencia_revisar($id, Request $request) {
        if (!session('preserve_edit_session')) {
            session()->forget(['citados_edicion_new', 'citados_edicion_delete', 'motivos_edicion_delete']);
        }

        $user = auth()->user(); // Ya trae el objeto, no necesitas buscarlo por ID de nuevo
        $isAudiencia = $request->query('isAudiencia', null);
        $audiencia_id = $request->query('audiencia_id', null);

        $audienciaCurrent = Audiencias::where('id', $audiencia_id)->first();
        
        // Usamos with() si existen relaciones definidas en el modelo para evitar el problema N+1
        $general = SeerPerGeneral::findOrFail($id);
        $ramas = SolicitudRama::all();
        $solicitantes = SeerSolicitante::where("id_solicitud", $id)->get();
        
        // Citados
        $dbCitados = SeerCitados::where("id_solicitud", $id)
            ->when($audiencia_id, function($query) use ($audiencia_id, $id) {
                //Obtenemos la audiencia más antigua en el tiempo
                $primera_audiencia = Audiencias::where('id_solicitud', $id)->orderBy('id', 'asc')->first();
                $query->where(function($q) use ($audiencia_id, $primera_audiencia) {
                    $q->where('audiencia_id', $audiencia_id);
                    if ($primera_audiencia && $primera_audiencia->id == (int)$audiencia_id) {
                        $q->orWhereNull('audiencia_id');
                    }
                });
            })
            ->get();
        $citadosDelete = session('citados_edicion_delete', []);
        $citadosNew = session('citados_edicion_new', []);

        // Filtrar y empujar nuevos citados en una sola colección
        $citados = $dbCitados->reject(fn($c) => in_array($c->id, $citadosDelete));
        foreach ($citadosNew as $cData) {
            $citados->push(new SeerCitados($cData + ['id' => $cData['id']]));
        }

        $citadosConMulta = $dbCitados->where('tipo_notificacion', 'Multa');
        $notificaciones = $dbCitados->keyBy('id');

        // Catálogos
        $estados = Estados::orderBy('nombre', 'asc')->get();
        $municipios = Municipios::orderBy('nombre', 'asc')->get();
        
        // Conciliadores con carga de relación optimizada
        $conciliadores = User::role('Conciliador')
            ->where('delegacion', $user->delegacion)
            ->get();

        // Motivos
        $mostrarMotivos = SolicitudMotivo::where('tipo_solicitud', $general->tipo_solicitud)->get();
        $motivosDelete = session('motivos_edicion_delete', []);
        $motivos = SeerMotivo::join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
            ->where('id_solicitud', $id)
            ->select('catalogo_motivos.motivo', 'seer_motivos.id')
            ->get()
            ->reject(fn($m) => in_array((string)$m->id, array_map('strval', $motivosDelete)))
            ->values();

        $historial_audiencias = Audiencias::where('id_solicitud', $id)->orderBy('fecha', 'desc')->get();
        $ultimaEstatus = $historial_audiencias->first()->estatus ?? null;

        // Validación de datos incompletos
        $idsAbogados = $dbCitados->whereNotNull('id_abogado')->pluck('id_abogado');
        $solIncompleto = SeerSolicitante::where('id_solicitud', $id)
            ->where(fn($q) => $q->whereNull('identificacion')->orWhere('identificacion', ''))
            ->exists();
        
        $abogIncompleto = Poder::whereIn('idAbogado', $idsAbogados)
            ->where(fn($q) => $q->whereNull('tipo_identificacion')->orWhere('tipo_identificacion', ''))
            ->exists();

        $datosIncompletos = $solIncompleto || $abogIncompleto;
        
        // PASAMOS LA FECHA ACTUAL DESDE EL CONTROLADOR
        $fecha_actual = now(); 

        return view('audiencias.revisar_audiencia', compact(
            'id','general','solicitantes','citados','ramas','estados','municipios',
            'mostrarMotivos','motivos','conciliadores', 'isAudiencia','notificaciones',
            'historial_audiencias','datosIncompletos','citadosConMulta', 'ultimaEstatus', 'fecha_actual', 'audienciaCurrent'
        )); 
    }

    public function pdfCitatorioAudiencia($id) {
        $citado = SeerCitados::find($id);
        $solicitud = SeerPerGeneral::where('id',$citado["id_solicitud"])->first();   

        $inicialesConcluye = $this->inicialesDeSeerGeneral($solicitud);
        $etiquetaIniciales = $this->etiquetaDelegacionSeer($solicitud->delegacion ?? null);

        $solicitante = SeerSolicitante::where('id_solicitud', $citado["id_solicitud"])->first();
        $motivoIds = SeerMotivo::where('id_solicitud', $citado["id_solicitud"])->pluck('id_motivo');
        $motivos = SolicitudMotivo::whereIn('id', $motivoIds)->get();
        if (!empty($citado->audiencia_id)) {
            $audiencia = Audiencias::where('id', $citado->audiencia_id)
                ->where('id_solicitud', $solicitud["id"])
                ->first();
        } else {
            $audiencia = Audiencias::where('id_solicitud', $solicitud["id"])
                ->orderBy('id', 'asc')
                ->first();
        }
        $conciliador  = User::where('id', $audiencia["id_conciliador"])->first();
        $municipio = Municipios::find($citado->municipio_citado);
        $estado = Estados::find($citado->estado_citado);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';
        $fechaEmision = $audiencia ? $audiencia->created_at : now();
        $html = view('PDF/Solicitudes/citatorio', compact('solicitud','solicitante','citado','motivos','audiencia','conciliador','municipioNombre','estadoNombre','fechaEmision','inicialesConcluye','etiquetaIniciales'))->render();
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 
        $nombreArchivo = 'citatorio_' . $citado->nombre . '_' . $citado->primer_apellido . '.pdf';
        $nombreArchivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nombreArchivo); //Elimina los caracteres especiales no permitidos en archivos

        return $pdf->stream($nombreArchivo);
    }

    public function solicitudes(){
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

        return view('solicitudes/index',compact('auxiliares','conciliadores'));
    }

    public function solicitudes_busqueda(Request $request){
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

        $solicitudes  = SeerPerGeneral::select("seer_general.id","seer_general.fecha","seer_general.fecha","seer_general.NUE","seer_general.tipo_solicitud","seer_general.estatus","seer_general.actividad","seer_solicitante.nombre");
        $solicitudes = $solicitudes->join("seer_solicitante","seer_solicitante.id_solicitud","seer_general.id");
        if($bandera_fechas == 1){
            $solicitudes = $solicitudes->where("seer_general.fecha",">=",$data["inicio"]);
            $solicitudes = $solicitudes->where("seer_general.fecha","<=",$data["final"]);
        }
        if($bandera_nue == 1){
            $solicitudes = $solicitudes->where("seer_general.NUE",$data["nue"]);
        }
        if($bandera_curp == 1){
            $solicitudes = $solicitudes->where("seer_solicitante.curp",$data["curp"]);
        }
        if($bandera_solicitante == 1){
            $solicitudes = $solicitudes->where("seer_solicitante.nombre",'like',$data["solicitante"]);
        }
        if($bandera_citado == 1){
            $solicitudes = $solicitudes->join("seer_citados","seer_citados.id_solicitud","seer_general.id");
            $solicitudes = $solicitudes->where("seer_citados.nombre",'like',$data["citado"]);
        }
        if($bandera_folio == 1){
            $solicitudes = $solicitudes->where("seer_general.id","=",$data["folio"]);
        }
        if($bandera_estatus == 1){
            $solicitudes = $solicitudes->where("seer_general.estatus","=",$data["estatus"]);
        }
        if($bandera_tipo == 1){
            $solicitudes = $solicitudes->where("seer_general.tipo_solicitud","=",$data["tipo"]);
        }
        if($bandera_auxiliar == 1){
            $solicitudes = $solicitudes->where("seer_general.user_id","=",$data["auxiliar"]);
        }
        if($bandera_conciliador == 1){
            $solicitudes = $solicitudes->where("seer_general.conciliador_id","=",$data["conciliador"]);
        }
        $solicitudes = $solicitudes->get();


        return view('solicitudes/busqueda',compact('solicitudes'));

    }

    public function solicitudes_pendientes_editar($id){
        if (!session('preserve_edit_session')) {
            session()->forget(['citados_edicion_new', 'citados_edicion_delete', 'motivos_edicion_delete']);
        }

        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $id             = $id;
        $general        = SeerPerGeneral::find($id);
        $ramas          = SolicitudRama::all();
        $solicitantes   = SeerSolicitante::where("id_solicitud",$id)->get();
        $citados        = SeerCitados::where("id_solicitud",$id)->get();

        $citadosNew = session('citados_edicion_new', []);
        $citadosDelete = session('citados_edicion_delete', []);
        
        $citados = $citados->filter(function($c) use ($citadosDelete) {
            return !in_array($c->id, $citadosDelete);
        });
        
        foreach ($citadosNew as $cData) {
            $cModel = new SeerCitados($cData);
            $cModel->id = $cData['id'];
            $citados->push($cModel);
        }

        $estados        = Estados::orderBy('nombre', 'asc')->get();
        $municipios     = Municipios::orderBy('nombre', 'asc')->get();
        $conciliadores = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();
        //Catalogo de motivos
        //$mostrarMotivos = SolicitudMotivo::all();
        $mostrarMotivos = SolicitudMotivo::where('tipo_solicitud', $general->tipo_solicitud)->get();
        //Motivos capturados
        $motivos        = SeerMotivo::join('seer_general','seer_general.id','seer_motivos.id_solicitud')
        ->join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo','seer_motivos.id')->get();

        // --- MERGE SESSION MOTIVOS ---
        $motivosDelete = session('motivos_edicion_delete', []);
        $motivos = $motivos->filter(function($m) use ($motivosDelete) {
            return !in_array((string)$m->id, array_map('strval', $motivosDelete));
        })->values();
        // -----------------------------

        return view('solicitudes.editar_solicitud', compact('id','general','solicitantes','citados','ramas','estados','municipios','mostrarMotivos','motivos','conciliadores'));
    }

    public function cumplimiento_actual(){
        $fecha_actual = date('Y-m-d');
        $mi_delegacion = auth()->user()->delegacion;
        $cumplimientos_ratificacion = Pagos::where("pago_solicitud.fecha", $fecha_actual)
            ->where("pago_solicitud.tipo_pago", "Ratificacion")
            ->join("turnos", "turnos.id", "pago_solicitud.id_solicitud")
            ->where("turnos.delegacion", $mi_delegacion)
            ->select(
                "pago_solicitud.id", "pago_solicitud.fecha", "pago_solicitud.hora", "pago_solicitud.monto", 
                "pago_solicitud.descripcion", "pago_solicitud.observaciones", "pago_solicitud.estatus", 
                "turnos.NUE", "turnos.id as id_solicitud",
                DB::raw('CONCAT(turnos.nombre_empresa, " ", turnos.primero_empresa, " ", turnos.segundo_empresa) AS empresa'),
                DB::raw('CONCAT(turnos.trabajador, " ", turnos.primero_trabajador, " ", turnos.segundo_trabajador) AS trabajador')
            )
            ->get();

        $cumplimientos_audiencias = Pagos::where("pago_solicitud.fecha", $fecha_actual)
            ->where("pago_solicitud.tipo_pago", "Audiencia")
            ->join("seer_general", "seer_general.id", "pago_solicitud.id_solicitud")
            ->join("seer_solicitante", "seer_general.id", "seer_solicitante.id_solicitud")
            ->where("seer_general.delegacion", $mi_delegacion)
            ->select(
                "pago_solicitud.id", "pago_solicitud.fecha", "pago_solicitud.hora", "pago_solicitud.monto", 
                "pago_solicitud.descripcion", "pago_solicitud.observaciones", "pago_solicitud.estatus", 
                "seer_general.NUE", "seer_general.id as id_solicitud",
                DB::raw('seer_solicitante.nombre AS trabajador')
            )
            ->get();

        return view('cumplimientos/actuales', compact('cumplimientos_ratificacion', 'cumplimientos_audiencias'));
    }

    //PDF NOTIFICADORES Razón de notificación Cuando atiende el citado
    public function VerPDFRNotificacion($id, $id_solicitud){
        $solicitud = SeerPerGeneral::findOrFail($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        /*$audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id")
        ->where("audiencias.id_solicitud", "=", $solicitud["id"])->latest('audiencias.created_at')->first();
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();*/
        $citado = SeerCitados::findOrFail($id);
        $audiencias = \DB::table('audiencias')
        ->where('id_solicitud', $id_solicitud)
        ->orderBy('created_at', 'asc')
        ->get();

        $totalAudiencias = $audiencias->count();
        $fechaCitatorio = null;

        if ($totalAudiencias == 1 && $citado->notificacion == "Centro") {
            //if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $audiencias->first()->created_at;
            //} 
        } elseif ($totalAudiencias == 2) {
            $primeraAudiencia = $audiencias->first();
            $segundaAudiencia = $audiencias->get(1);
            if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $segundaAudiencia->created_at;
            } 
        }
        elseif ($totalAudiencias > 2) {
            $fechaCitatorio = $audiencias->last()->created_at;
        }
        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        /*$imagenes = [];

        for ($i = 1; $i <= 3; $i++) {
            $path = storage_path("app/documentos_notificacion/{$citado->id}-foto{$i}.jpg");

            if (file_exists($path)) {
                $imagenes[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }*/
        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
        $html = view('PDF/Solicitudes/razonNotificacion', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','fechaCitatorio'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Razón_Notificación' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                
    }

    public function consulta_cumplimiento($id,$tipo){
        $pago = Pagos::find($id);
        if($tipo == 1){
            $solicitudes = Pagos::join('turnos','turnos.id',"=",'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.id',$id)
            ->select('pago_solicitud.id','turnos.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
            ->get();

            return view('/cumplimientos/pagar_ratificacion',compact('solicitudes'));

        }
        else if($tipo == 2){
            $solicitudes = Pagos::join('seer_general','seer_general.id',"=",'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.id',$id)
            ->select('pago_solicitud.id','seer_general.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
            ->get();
            return view('/cumplimientos/pagarAuciencia',compact('solicitudes'));
        }
        else if($tipo == 3){
            $solicitudes = Pagos::join('turnos','turnos.id',"=",'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.id',$id)
            ->select('pago_solicitud.id','turnos.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
            ->get();
            return view('/cumplimientos/pagar_busqueda',compact('solicitudes'));
        }
        else if($tipo == 4){
            $solicitudes = Pagos::join('seer_general','seer_general.id',"=",'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.id',$id)
            ->select('pago_solicitud.id','seer_general.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
            ->get();
            return view('/cumplimientos/pagar_busqueda',compact('solicitudes'));
        }
        //Audiencias
        else if($tipo == 5){
            
            $audiencia = Audiencias::where('id', $id)
                        ->orderBy('fecha', 'desc')
                        ->first();
            
            return redirect()->route('inicioAudiencia', ['id' => $audiencia->id_solicitud, 'estatus' => 'Confirmado']);
            //$url = route('solicitud_audiencia', $id) . '?isAudiencia=Si';
            //return redirect()->to($url);
        }
        //Cumplimientos , Ratificacion, Audiencia y generales
        else if($tipo == 6){
            //$solicitudes = Pagos::where('id',$id)->get();
            //return view('/cumplimientos/pagar_busqueda',compact('solicitudes'));
            $pago = Pagos::where('id', $id)->first();
            $id_pago= $pago->id;
            return redirect()->route('pago_cumplimiento', $id_pago);
        }
        //Ratificaciones
        else if($tipo == 7){
            $url = route('consultar_ratificacion', $id); 
            return redirect()->to($url);
        }
    }

    public function consulta_cumplimiento_ratificacion($id){
        $solicitudes = Pagos::join('turnos','turnos.id',"=",'pago_solicitud.id_solicitud')
            ->where('pago_solicitud.id',$id)
            ->select('pago_solicitud.id','turnos.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
            ->get();
            return view('/cumplimientos/pagarratificacion',compact('solicitudes'));
    }
    public function cumplimiento_pagar_rati(Request $request){
        $request->validate([
            'id'              => 'required|exists:pago_solicitud,id',
            'observaciones'   => 'nullable|string',
            'forma_pago'      => 'required|string',
            'fecha_audiencia' => 'required|date',
            'hora_audiencia'  => 'required',
        ]);
        $data = $request->all();

        Pagos::find($data["id"])->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"],
                                        'forma_pago'      => $data["forma_pago"],
                                        'fecha_audiencia' => $data["fecha_audiencia"],
                                        'hora_audiencia'  => $data["hora_audiencia"]]);

        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();

        if(count($faltantes) == 0){
            Turnos::find($id_solicitud)->update(['estatus' => "Concluida"]);
        }

        return redirect()->route('cumplimiento_actual');
    }

    public function cumplimiento_rechazar_rati($id){
        $pagos = Pagos::find($id);
        
        $id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)->update(['estatus'  => "No pagado", 'fecha_conclucion' => \Carbon\Carbon::now()->format('Y-m-d')]);
        Turnos::find($id_solicitud)->update(['estatus' => "Incumplimiento"]);

        return redirect()->route('cumplimiento_actual');
    }

    public function cumplimiento_pagar_audiencia(Request $request){
        $data = $request->all();

        Pagos::find($data["id"])->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"]]);
        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();

        if(count($faltantes) == 0){
            SeerPerGeneral::find($id_solicitud)->update(['estatus' => "Concluida"]);
        }

        return redirect()->route('cumplimiento_actual');
    }

    public function cumplimiento_pagar_con_pena_audiencia(Request $request)
    { 
        $user_id = auth()->user()->id;
        $data = $request->validate([
            'id' => 'required|integer|exists:pago_solicitud,id',
            'observaciones' => 'nullable|string',
            'monto_pc' => 'required|numeric|min:0',
        ]);

        Pagos::find($data['id'])->update([
            'estatus' => 'Pagado con pena convencional',
            'observaciones' => $data['observaciones'] ?? null,
            'monto_pc' => $data['monto_pc'],
            'user_id' => $user_id,
        ]);

        $pago = Pagos::find($data['id']);
        $id_solicitud = $pago['id_solicitud'];
        $faltantes = Pagos::where('id_solicitud', $id_solicitud)
            ->where('estatus', 'Pendiente')
            ->get();

        if (count($faltantes) == 0) {
            SeerPerGeneral::find($id_solicitud)->update(['estatus' => 'Concluida']);
        }

        return redirect()->route('cumplimiento_actual');
    }

    public function cumplimiento_rechazar_audiencia($id){
        $user_id = auth()->user()->id;
        $pagos = Pagos::find($id);
        $id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)->update(['estatus'  => "No pagado", 'user_id' => $user_id, 'fecha_conclucion' => \Carbon\Carbon::now()->format('Y-m-d')]);

        SeerPerGeneral::find($id_solicitud)->update(['estatus' => "Incumplimiento"]);

        return redirect()->route('cumplimiento_actual');
    }

    public function cumplimientos_busqueda(Request $request){
        $data = $request->all();
        $bandera_fechas = 0;
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

        $solicitudes = Pagos::whereBetween("pago_solicitud.fecha",[$data["inicio"],$data["final"]])
        ->where("pago_solicitud.tipo_pago","Ratificacion")
        ->join("turnos","turnos.id","pago_solicitud.id_solicitud")
        ->select("pago_solicitud.id","pago_solicitud.fecha","pago_solicitud.hora","pago_solicitud.monto","pago_solicitud.descripcion",
        "pago_solicitud.observaciones","pago_solicitud.estatus","turnos.NUE","turnos.id as id_solicitud",
        DB::raw('CONCAT(turnos.nombre_empresa, " ", turnos.primero_empresa, " ", turnos.segundo_empresa) AS empresa'),
        DB::raw('CONCAT(turnos.trabajador, " ", turnos.primero_trabajador, " ", turnos.segundo_trabajador) AS trabajador'))
        ->get();

        return view('cumplimientos/busqueda_resultado',compact('solicitudes'));
    }

    public function PDFincumplimientoAudiencia($id){
        $pagos = Pagos::find($id);
    
        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $solicitud->trabajador = $solicitud->nombre_trabajador;
            $solicitud->empresa = $solicitud->empresa_representante;
            $salario_diario = 0;
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $id)
            ->select('users.name')
            ->first();
            $html = view('PDF/Cumplimientos/Incumplimiento', compact('id', 'solicitud','conciliador','salario_diario','pagos'))->render();
        }
        else{
            $solicitante = SeerSolicitante::where('id_solicitud',$pagos["id_solicitud"])->first();
            $solicitud = SeerPerGeneral::where('id', $pagos["id_solicitud"])->first();
            $pagos      = Pagos::find($id);
            $general    = SeerPerGeneral::find($pagos["id_solicitud"]);
            $citados = SeerCitados::where('id_solicitud',$pagos["id_solicitud"])->where('aparece_convenio', 1)->get();
            $antefirma = $this->antefirmaDesdePagoSolicitud($pagos->user_id ?? null, $solicitud->delegacion ?? null);
            $inicialesConcluye = $antefirma['inicialesConcluye'];
            $etiquetaIniciales = $antefirma['etiquetaIniciales'];

            $salario_diario = $this->calcularSalarioDiario($solicitante->pago, $solicitante->periodo_pago);
    
            $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name','audiencias.fecha','audiencias.hora')
            ->first();
            $html = view('PDF/IncumplimientoAudiencia', compact('id', 'solicitud','conciliador','salario_diario','pagos','general', 'citados', 'solicitante', 'inicialesConcluye', 'etiquetaIniciales'))->render();
        }
       
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 
    
        $nombreArchivo = 'constancia_de_incumplimiento_'  .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }
    
    public function cumplimiento_pagar_busqueda_rati(Request $request){
        $request->validate([
            'forma_pago'      => 'required',
            'fecha_audiencia' => 'required|date',
            'hora_audiencia'  => 'required',
        ]);
        $data = $request->all();

        Pagos::find($data["id"])->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"], 
                                            'forma_pago'      => $data["forma_pago"],
                                            'fecha_audiencia' => $data["fecha_audiencia"],
                                            'hora_audiencia'  => $data["hora_audiencia"]]);

        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();
        if($pagos["id_solicitud"] != 0){
            if(count($faltantes) == 0){
                Turnos::find($id_solicitud)->update(['estatus' => "Concluida"]);
            }
        }

        return redirect()->route('agenda')->with('pagos', $pagos);
    }

    public function cumplimiento_rechazar_busqueda_rati(Request $request, $id){
        $request->validate([
            'fecha_audiencia' => 'required|date',
            'hora_audiencia'  => 'required',
        ]);
        $pago = Pagos::find($id);
        $pago->update([
            'estatus'         => "No pagado",
            'fecha_audiencia' => $request->fecha_audiencia,
            'hora_audiencia'  => $request->hora_audiencia,
        ]);

        $id_solicitud = $pago->id_solicitud;
        Turnos::find($id_solicitud)?->update(['estatus' => "Incumplimiento"]);
    
        return redirect()->route('agenda');                         
      /*Así estab antes de los cambios en los cumplimientos*/ 
        /* 
       $pagos = Pagos::find($id);
        
        //$id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)->update(['estatus'  => "No pagado"]);
        //Turnos::find($id_solicitud)->update(['estatus' => "Incumplimiento"]);

        return redirect()->route('agenda');*/
    }

    public function cumplimiento_pagar_busqueda_audiencia(Request $request){
        $request->validate([
            'forma_pago'      => 'required',
            'fecha_audiencia' => 'required|date',
            'hora_audiencia'  => 'required',
        ]);
        $data = $request->all();

        Pagos::find($data["id"])->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"], 
                                            'forma_pago'      => $data["forma_pago"],
                                            'fecha_audiencia' => $data["fecha_audiencia"],
                                            'hora_audiencia'  => $data["hora_audiencia"]]);

        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();

        if(count($faltantes) == 0){
            SeerPerGeneral::find($id_solicitud)->update(['estatus' => "Concluida"]);
        }

        return redirect()->route('agenda');
    }

    public function VerPDFAudiencia($id, Request $request){
        $solicitud = SeerPerGeneral::find($id);
        $pagos = Pagos::where('id_solicitud',$id)->get();

        $audienciaId = $request->query->get('audiencia_id');
        if (is_array($audienciaId)) {
            $audienciaId = $audienciaId[0] ?? null;
        }

        $audienciaPoder = Audiencias::where('id', $audienciaId)->first();

        $hayCentro = false;
        $hayNULL = false;
        // Primero revisamos si hay datos temporales en sesión (vista previa)
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        $sessionData = session()->get($sessionKey);
        if ($sessionData && is_array($sessionData)) {
            // Construimos un objeto mínimo compatible con lo que espera la vista
            $datosAudiencia = (object) [
                // Compatibilidad con ortografías en vistas
                'resolicion_primera' => $sessionData['primera'] ?? '',
                'resolucion_primera' => $sessionData['primera'] ?? '',
                'resolicion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolucion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolicion_segunda' => $sessionData['segunda'] ?? '',
                'resolucion_segunda' => $sessionData['segunda'] ?? '',
                'vacaciones' => $sessionData['vacaciones'] ?? null,
                'aguinaldo' => $sessionData['aguinaldo'] ?? null,
                'otros' => $sessionData['otros'] ?? null,
                'horario' => $sessionData['horario'] ?? null,
                'comida' => $sessionData['comida'] ?? null,
                'tipo_audiencia' => $sessionData['tipo_audiencia'] ?? null,
                'conclucion' => $sessionData['conclucion'] ?? null,
            ];
        } else {
            // Si no hay sesión, traemos el registro de la BD (el más reciente si hay varios)
            $datosAudiencia = SeerPerConciliador::where('audiencia_id', $audienciaId)->first();
            if(!$datosAudiencia){
                $datosAudiencia = SeerPerConciliador::where('id_solicitud', $id)->latest()->first();
            }
            //->orderBy('numero_audiencias', 'DESC')->first();
        }
        $solicitante = SeerSolicitante::where('id_solicitud',$solicitud["id"])->first();
        $pagos = Pagos::where('id_solicitud', $id)->where('id_solicitud', 'Audiencia')->get();
        $conciliador = User::join('audiencias', 'audiencias.id_conciliador', '=', 'users.id')
            ->where('audiencias.id_solicitud', $solicitud['id'])
            ->latest('audiencias.created_at')
            ->select('users.name')
            ->orderByDesc('audiencias.id')
            ->first();
        /*$audiencia = Audiencias::where('id_solicitud', $id)
        ->orderByDesc('id')
        ->first();*/
        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id");
        $audiencia = $audiencia->where("audiencias.id", "=", $audienciaId)
        ->first();
        /*$audiencia = SeerPerGeneral::join("audiencias", "audiencias.id_solicitud", "=", "seer_general.id")
            ->where("audiencias.id_solicitud", "=", $solicitud->id)
            ->latest('audiencias.created_at')
            ->first();*/

        $prestaciones = Concepto::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();

            // Soporte para selección específica de citados (guardada en sesión desde la vista)
            $idsSession = session()->get('acta_citados_' . $id);

            // Si la solicitud es patronal (tipo_solicitud == 2) -> incluir TODOS los citados de la audiencia (ignorando sesión y filtros)
            if (isset($solicitud) && $solicitud->tipo_solicitud == 2) {
                if (!empty($audienciaId)) {
                    $citados = SeerCitados::where('id_solicitud', $id)
                                ->where('audiencia_id', $audienciaId)
                                ->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $id)->get();
                }
            } else {
                
                if($audienciaId != null){
                    $citadosPorCentro = SeerCitados::where('id_solicitud', $id)->where('audiencia_id', $audienciaId)->get();
                } else {
                    $citadosPorCentro = SeerCitados::where('id_solicitud', $id)->get();
                }
                
                foreach($citadosPorCentro as $citado){
                    if($citado->notificacion == 'Centro'){
                        $hayCentro = true;
                        break;
                    }
                }

                if($hayCentro){
                    $citados = SeerCitados::where('id_solicitud', $id)
                                ->where('notificacion', 'Centro')
                                ->where('tipo_notificacion', '!=', 'Multa')
                                //->where('resulte_responsable', 'No')
                                ->where('audiencia_id', $audienciaId)
                                ->whereNotNull('id_abogado')
                                ->get();
                } else {
                    $citados = SeerCitados::where('id_solicitud', $id)
                                //->where('resulte_responsable', 'No')
                                ->where('audiencia_id', $audienciaId)
                                ->whereNotNull('id_abogado')
                                ->get();
                }
            }

        $abogados = collect();
        $idsHistorial = $citados->pluck('id_historial')->filter()->unique()->values()->all();
        $idsAbogadoPoder = $citados->pluck('id_abogado')->filter()->unique()->values()->all();

        if (!empty($idsHistorial)) {
            $abogadosHist = \App\Models\HistorialAbogado::whereIn('id', $idsHistorial)->get();
            $abogados = $abogados->merge($abogadosHist);
        }

        if (!empty($idsAbogadoPoder)) {
            $abogadosPoder = Poder::whereIn('idAbogado', $idsAbogadoPoder)->get();
            $abogados = $abogados->merge($abogadosPoder);
        }

        $abogados = $abogados->unique(function ($a) {
            $clase = is_object($a) ? get_class($a) : '';
            $id = $a->id ?? ($a->idAbogado ?? null);
            return $clase . ':' . (string) $id;
        })->values();

        $historialMap = !empty($idsHistorial)
            ? $abogadosHist->keyBy('id')
            : collect();
        $poderMap = !empty($idsAbogadoPoder)
            ? $abogadosPoder->keyBy('idAbogado')
            : collect();
        $descripcionIdentificacionP = '';
        foreach ($citados as $c) {
            if (!empty($c->id_historial) && $historialMap->has($c->id_historial)) {
                $c->abogado = $historialMap->get($c->id_historial);
                $c->abogado_fuente = 'historial';
            } elseif (!empty($c->id_abogado) && $poderMap->has($c->id_abogado)) {
                $c->abogado = $poderMap->get($c->id_abogado);
                $c->abogado_fuente = 'poder';
            } else {
                $c->abogado = null;
                $c->abogado_fuente = null;
            }
            if ($c->abogado && isset($c->abogado->tipo_identificacion)) {
                $descripcionIdentificacionP = $this->descripcionIdentificacion($c->abogado->tipo_identificacion);
                $c->abogado->$descripcionIdentificacionP = $descripcionIdentificacionP;
            }
        }

        // Si hubo datos de preview en sesión, construir prestaciones/deducciones/pagos desde sesión
        if ($sessionData && is_array($sessionData)) {
            // Construir prestaciones (tipo_pago / monto_pago / otra_prestacion)
            $prestaciones = collect();
            $tipos = $sessionData['tipo_pago'] ?? [];
            $montos_p = $sessionData['monto_pago'] ?? [];
            $otras = $sessionData['otra_prestacion'] ?? [];
            $countPrest = max(count($tipos), count($montos_p));
            for ($i = 0; $i < $countPrest; $i++) {
                $descripcion = $tipos[$i] ?? '';
                if (($descripcion === 'Otras' || $descripcion === 'Otras') && isset($otras[$i]) && trim($otras[$i]) !== '') {
                    $descripcion = trim($otras[$i]);
                }
                $montoVal = isset($montos_p[$i]) ? floatval($montos_p[$i]) : 0;
                $obj = (object) [
                    'id' => 's_p_'.$i,
                    'descripcion' => $descripcion,
                    'monto' => $montoVal,
                ];
                $prestaciones->push($obj);
            }

            // Construir deducciones
            $deducciones = collect();
            $descDed = $sessionData['descripcion_deduccion'] ?? [];
            $montosDed = $sessionData['monto_deduccion'] ?? [];
            $countDed = max(count($descDed), count($montosDed));
            for ($i = 0; $i < $countDed; $i++) {
                $montoVal = isset($montosDed[$i]) ? floatval($montosDed[$i]) : 0;
                $obj = (object) [
                    'id' => 's_d_'.$i,
                    'descripcion' => $descDed[$i] ?? '',
                    'monto' => $montoVal,
                ];
                $deducciones->push($obj);
            }

            // Construir pagos diferidos (monto_pagos, dias_pagos, hora_pagos, descripcion_pagos)
            $pagos = collect();
            $dias = $sessionData['dias_pagos'] ?? [];
            $horas = $sessionData['hora_pagos'] ?? [];
            $montosPag = $sessionData['monto_pagos'] ?? [];
            $descPag = $sessionData['descripcion_pagos'] ?? [];
            $countPag = max(count($dias), count($montosPag));
            for ($i = 0; $i < $countPag; $i++) {
                $obj = (object) [
                    'id_solicitud' => $id,
                    'fecha' => $dias[$i] ?? null,
                    'hora' => $horas[$i] ?? null,
                    'monto' => isset($montosPag[$i]) ? floatval($montosPag[$i]) : 0,
                    'descripcion' => $descPag[$i] ?? '',
                ];
                $pagos->push($obj);
            }
        }

        // construir textos y totales desde las colecciones actuales
        $conceptosTexto = [];
        foreach ($prestaciones as $concepto) {
            $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
        }

        $deduccionesTexto = [];
        foreach ($deducciones as $deduccion) {
            $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
        }

        $totalPrestaciones = collect($prestaciones)->sum('monto');
        $totalDeducciones = collect($deducciones)->sum('monto');
        //Total a pagar
        $pagoTotal= $totalPrestaciones-$totalDeducciones;

        // Asegurar que $datosAudiencia tenga la propiedad monto 
        if (isset($datosAudiencia) && is_object($datosAudiencia) && !property_exists($datosAudiencia, 'monto')) {
            $datosAudiencia->monto = $pagoTotal;
        }

        //Descripción del tipo de identificación para los solicitantes
        $identificacionSolicitante = $solicitante->identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);

        //$identificacionPoder = $abogado->tipo_identificacion ?? null;
        //$descripcionIdentificacionP = $identificacionPoder ? $this->descripcionIdentificacion($identificacionPoder) : '';

        /*if (!$abogado) {
            $abogado = (object) [
                'nombres_patronal' => null,
                'primer_apellido_patronal' => null,
                'segundo_apellido_patronal' => null,
                'nombre_representante' => null,
                'primer_apellido_representante' => null,
                'segundo_apellido_representante' => null,
                'descipcion_poder' => null,
                'tipo_identificacion' => null,
                'num_identificacion' => null,
            ];
        }*/
       
        $html = view('PDF/Solicitudes/ActaAudiencia', compact('id','solicitud','conciliador','prestaciones','deducciones','deduccionesTexto','pagoTotal','descripcionIdentificacionS',
        'descripcionIdentificacionP','conceptosTexto','solicitante','audiencia','datosAudiencia','citados', 'audienciaPoder'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'acta_de_audiencia_' . $solicitante->nombre .'.pdf';
        return $pdf->stream($nombreArchivo);            
    }

    public function audiencia_index(){
        $id = auth()->user()->id;
        $user = User::find($id);
        //$roles = Role::pluck('name','name')->all();
        //$userRole = $user->roles->pluck('name')->all();

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
        
        return view('/audiencias/index',compact('auxiliares','conciliadores'));
    }
    
    //Rechazo de solicitud
    public function guardar_rechazo(Request $request){
        $data = $request->all();
        SeerPerGeneral::find($data["id"])->update(['estatus' => 'Prevencion','observaciones' => $data["observaciones"]]);
        $solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();

        //Mandar un correo
        $user = [
            'nombre'    => $solicitante["nombre"],
            'fecha'     => date('d-m-Y'),
            'email'     => $solicitante["email"],
            'id'        => $data["id"],
            'mensaje'   => $data["observaciones"] ,
        ];

        // El método Mail::to() toma el email del destinatario
        //Mail::to($user['email'])->send(new MailAceptacionRechazo($user));


        return redirect()->route('solicitudes_pendientes');
    }

    //Consultar solicitud por parte del solicitante
    public function solicitud_consultarSolicitante($id){
        if (!session('preserve_edit_session')) {
            session()->forget(['citados_edicion_new', 'citados_edicion_delete', 'motivos_edicion_delete']);
        }

        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $id             = $id;
        $general        = SeerPerGeneral::find($id);
        $ramas          = SolicitudRama::all();
        $solicitantes   = SeerSolicitante::where("id_solicitud",$id)->get();
        $citados        = SeerCitados::where("id_solicitud",$id)->get();

        $citadosNew = session('citados_edicion_new', []);
        $citadosDelete = session('citados_edicion_delete', []);
        
        $citados = $citados->filter(function($c) use ($citadosDelete) {
            return !in_array($c->id, $citadosDelete);
        });
        
        foreach ($citadosNew as $cData) {
            $cModel = new SeerCitados($cData);
            $cModel->id = $cData['id'];
            $citados->push($cModel);
        }

        $estados        = Estados::all();
        $municipios     = Municipios::all();
        $conciliadores = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Conciliador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();
        //Catalogo de motivos
        //$mostrarMotivos = SolicitudMotivo::all();
        $mostrarMotivos = SolicitudMotivo::where('tipo_solicitud', $general->tipo_solicitud)->get();
        //Motivos capturados
        $motivos        = SeerMotivo::join('catalogo_motivos','catalogo_motivos.id','seer_motivos.id_motivo')
        ->where('id_solicitud',$id)
        ->select('catalogo_motivos.motivo','seer_motivos.id')->get();

        $motivosDelete = session('motivos_edicion_delete', []);
        $motivos = $motivos->filter(function($m) use ($motivosDelete) {
            return !in_array((string)$m->id, array_map('strval', $motivosDelete));
        })->values();

        return view('solicitudes.correccion_solicitantes', compact('id','general','solicitantes','citados','ramas','estados','municipios','mostrarMotivos','motivos','conciliadores'));
    }

    //Guardar cambios realizados por el solicitante en su solicitud una vez que fue rechazada
    public function correccion_solicitante(Request $request){
        DB::beginTransaction();
        try {
        $data = $request->all();

        //Se va asignar el conciliador y la sala
        $id_user = auth()->user()->id;
        $user = User::find($id_user);
        $listado_auxiliares = array();
        $relacionEloquent = 'roles';
        $fecha_actual = date('y-m-d');
            
        $motivosDelete = session('motivos_edicion_delete', []);
        if (!empty($motivosDelete)) {
            SeerMotivo::whereIn('id', $motivosDelete)->delete();
        }
        session()->forget('motivos_edicion_delete');

        SeerPerGeneral::where('id', $data["id"])
        ->update(['actividad' => $data["actividad_economica"],'id_rama' => $data["ramaIndustrial"] ]);

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $data["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }

            //Actualizar SEER SOLICTUD
        SeerSolicitante::where('id_solicitud', $data["id"])
            ->update(['tipo_persona' => $data["tipo_persona_solicitante"], 
                'curp'                  => $data["curp_solicitante"],
                'rfc'                   => $data["rfc_solicitante"],
                'nombre'                => $data["nombre_solicitante"],
                'sexo'                  => $data["sexo_solicitante"],
                'nacionalidad'          => $data["nacionalidad_solicitante"],
                //'estado'                => $data["estado_solicitante"],
                'email'                 => $data["email_solicitante"],
                'fecha_nacimiento'      => $data["fecha_nacimiento_solicitante"],
                'edad'                  => $data["edad_solicitante"],
                'telefono1'             => $data["telefono1_solicitante"],
                'traductor'             => $data["traductor_solicitante"],
                'lenguaje'              => $data["lenguaje_solicitante"],
                'discapacidad'          => $data["discapacidad_solicitante"],
                'tipo_discapacidad'     => $data["disc_solicitante"],
                'tipo_vialidad'         => $data["tipo_vialidad"],
                'calle'                 => $data["calle_solicitante"],
                'num_ext'               => $data["num_ext_solicitante"],
                'codigo_postal'         => $data["codigo_postal_solicitante"],
                'referencia'            => $data["referencia_solicitante"],
                'colonia'               => $data["colonia_solicitante"],
                'calle2'                => $data["calle2_solicitante"],
                'calle3'                => $data["calle3_solicitante"],
                'municipio_domicilio'   => $data["municipio_solicitante"],
                'puesto'                => $data["puesto"],
                'pago'                  => $data["pago"],
                'periodo_pago'          => $data["periodo_pago"],
                'fecha_ingreso'         => $data["fecha_ingreso"],
                'fecha_salida'          => $data["fecha_salida"],
                'jornada'               => $data["jornada"],
                'estado_domicilio'      => $data["estado_solicitante"],
                'horas_semana'          => $data["horas_semana"],
                'descripcionSolicitud'  => $data["descripcionSolicitud"],
        ]);

        //Opcionales
        if(isset($data["telefono2"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['telefono2' => $data["telefono2_solicitante"] ]);
        }
        if(isset($data["num_int"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['num_int' => $data["num_int_solicitante"] ]);
        }
        if(isset($data["nss"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['nss' => $data["nss"] ]);
        }


        //Citados
        SeerCitados::where('id_solicitud',$data["id"])->delete();
        $cont = count($data["colonia_citado"]);
        for($i = 0; $i < $cont; $i++) {
                $foto1 = $data["imagen_domicilio1"][$i] ?? 'Sin documento';
                $foto2 = $data["imagen_domicilio2"][$i] ?? 'Sin documento';
            
                if ($request->hasFile("foto1.$i")) {
                    $file = $request->file("foto1")[$i];
                    $foto1 = $data["id"] . "-citado_foto1_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                    Storage::putFileAs('documentosSolicitud', $file, $foto1);
                }
            
                if ($request->hasFile("foto2.$i")) {
                    $file = $request->file("foto2")[$i];
                    $foto2 = $data["id"] . "-citado_foto2_" . Str::random(8) . "." . $file->getClientOriginalExtension();
                    Storage::putFileAs('documentosSolicitud', $file, $foto2);
                }
                
            $data_insert=array(
                    'id_solicitud'      => $data["id"],
                    'colonia'           => $data["colonia_citado"][$i],
                    'cp'                => $data["cp_citado"][$i],
                    'n_ext'             => $data["n_ext_citado"][$i],
                    'calle'             => $data["calle_citado"][$i],
                    'tipo_vialidad'     => $data["vialidad_citado"][$i],
                    'referencia'        => $data["referencia_citado"][$i],
                    'municipio_citado'  => $data["municipio_citado"][$i],
                    'tipo_persona'      => $data["tipo_persona_citado"][$i],
                    'nombre'            => $data["nombre_citado"][$i],
                    'primer_apellido'   => $data["primer_apellido"][$i] ?? null,
                    'segundo_apellido'  => $data["segundo_apellido"][$i] ?? null,
                    'curp'              => $data["curp_citado"][$i] ?? null,
                    'rfc'               => $data["rfc_citado"][$i],
                    'estado_citado'     => $data["estado_citado"][$i],
                    'imagen_domicilio1' => $foto1,
                    'imagen_domicilio2' => $foto2,
            );
                
            if(isset($data["rfc"])){
                $data_insert["rfc"] =  $data["rfc_citado"][$i];
            }
            if(isset($data["interior"])){
                $data_insert["n_int"] =  $data["n_int_citado"][$i];
            }
            if(isset($data["calle1"])){
                $data_insert["calle1"] =  $data["calle1_citado"][$i];
            }
            if(isset($data["calle2"])){
                $data_insert["calle2"] =  $data["calle2_citado"][$i];
            }
            if(isset($data["tipo"])){
                $data_insert["tipo_persona"] =  $data["tipo_persona_citado"][$i];
            }
            if(isset($data["curp"][$i])){
                $data_insert["curp"] =  $data["curp_citado"][$i];
            }
            if(isset($data["nombre"])){
                $data_insert["nombre"] =  $data["nombre_citado"][$i];
            }
            if(isset($data["primer_apellido"][$i])){
                $data_insert["primer_apellido"] =  $data["primer_apellido"][$i];
            }
            if(isset($data["segundo_apellido"][$i])){
                $data_insert["segundo_apellido"] =  $data["segundo_apellido"][$i];
            }
            if(isset($data["rfc"])){
                $data_insert["rfc"] =  $data["rfc"][$i];
            }
            SeerCitados::create($data_insert);
        }
            
        //Documentos
        if(isset($data["curp"])){
            $documento = $data["curp"]."_CURP.pdf";
            $path = Storage::putFileAs('documentosSolicitud', $request->file('documentoCurp'), $documento);
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['documentoCurp' => $documento ]);
        }
            
        //Acta de nacimiento
        if(isset($data["indetificacion"])){
            $documentoidentificacion = $data["curp"]."_Identificacion.pdf";
            $path = Storage::putFileAs('documentosSolicitud', $request->file('indetificacion'), $documentoidentificacion);
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['documentoIdentificacion' => $documentoidentificacion ]);
        }

        //Actualizar el estatus
        SeerPerGeneral::find($data["id"])->update(['estatus' => "Pendiente" ]);

            DB::commit();
            session()->forget(['citados_edicion_new', 'citados_edicion_delete']);
            return redirect()->route('mis_solicitudes'); 
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('preserve_edit_session', true);
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    //PDF Notificación Por instructivo
    public function PDFnotificadoInstructivo($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();
        if (!empty($citado->medio)) {
            $citado->medio = json_decode($citado->medio);
        }
        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $audiencias = \DB::table('audiencias')
        ->where('id_solicitud', $id_solicitud)
        ->orderBy('created_at', 'asc')
        ->get();

        $totalAudiencias = $audiencias->count();
        $fechaCitatorio = null;

        if ($totalAudiencias == 1 && $citado->notificacion == "Centro") {
            //if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $audiencias->first()->created_at;
            //} 
        } elseif ($totalAudiencias == 2) {
            $primeraAudiencia = $audiencias->first();
            $segundaAudiencia = $audiencias->get(1);
            if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $segundaAudiencia->created_at;
            } 
        }
        elseif ($totalAudiencias > 2) {
            $fechaCitatorio = $audiencias->last()->created_at;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
            
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
            
            $path = storage_path("app/documentos_notificacion/{$img}");
            
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
            
        $html = view('PDF/Solicitudes/razonPorInstructivo', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','fechaCitatorio'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Razón_NotificaciónIns' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                
    }

    //PDF Notificación No exitosa SE CONSTITUYE, CERRADO
    public function VerPDFNoExitConstituye($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();
        if (!empty($citado->medio)) {
            $citado->medio = json_decode($citado->medio);
        }
        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $audiencias = \DB::table('audiencias')
        ->where('id_solicitud', $id_solicitud)
        ->orderBy('created_at', 'asc')
        ->get();

        $totalAudiencias = $audiencias->count();
        $fechaCitatorio = null;

        if ($totalAudiencias == 1 && $citado->notificacion == "Centro") {
            //if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $audiencias->first()->created_at;
            //} 
        } elseif ($totalAudiencias == 2) {
            $primeraAudiencia = $audiencias->first();
            $segundaAudiencia = $audiencias->get(1);
            if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $segundaAudiencia->created_at;
            } 
        }
        elseif ($totalAudiencias > 2) {
            $fechaCitatorio = $audiencias->last()->created_at;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        /*$imagenes = [];

        for ($i = 1; $i <= 3; $i++) {
            $path = storage_path("app/documentos_notificacion/{$citado->id}-foto{$i}.jpg");

            if (file_exists($path)) {
                $imagenes[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }*/
        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
            
        $html = view('PDF/Solicitudes/razonNoExitosa', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','fechaCitatorio'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Razón_NotificaciónN' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                   
    }

    //PDF Notificación No exitosa NO SE LOCALIZA INTERIOR
    public function PDFnotificadoNoexitosaInt($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
    
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();
        if (!empty($citado->medio)) {
            $citado->medio = json_decode($citado->medio);
        }
        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $audiencias = \DB::table('audiencias')
        ->where('id_solicitud', $id_solicitud)
        ->orderBy('created_at', 'asc')
        ->get();

        $totalAudiencias = $audiencias->count();
        $fechaCitatorio = null;

        if ($totalAudiencias == 1 && $citado->notificacion == "Centro") {
            //if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $audiencias->first()->created_at;
            //} 
        } elseif ($totalAudiencias == 2) {
            $primeraAudiencia = $audiencias->first();
            $segundaAudiencia = $audiencias->get(1);
            if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $segundaAudiencia->created_at;
            } 
        }
        elseif ($totalAudiencias > 2) {
            $fechaCitatorio = $audiencias->last()->created_at;
        }
        $id_notificador = $citado->id_notificador;
 
        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();
 
        /*$imagenes = [];
 
        for ($i = 1; $i <= 3; $i++) {
            $path = storage_path("app/documentos_notificacion/{$citado->id}-foto{$i}.jpg");
 
            if (file_exists($path)) {
                $imagenes[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }*/
        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
             
        $html = view('PDF/Solicitudes/razonNumInt', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','fechaCitatorio'))->render();
 
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 
 
        $nombreArchivo = 'Razón_NotificaciónNInt' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                      
    }

    public function PDFnotificadoNoexitosaNS($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
    
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();
        if (!empty($citado->medio)) {
            $citado->medio = json_decode($citado->medio);
        }

        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $audiencias = \DB::table('audiencias')
        ->where('id_solicitud', $id_solicitud)
        ->orderBy('created_at', 'asc')
        ->get();

        $totalAudiencias = $audiencias->count();
        $fechaCitatorio = null;

        if ($totalAudiencias == 1 && $citado->notificacion == "Centro") {
            //if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $audiencias->first()->created_at;
            //} 
        } elseif ($totalAudiencias == 2) {
            $primeraAudiencia = $audiencias->first();
            $segundaAudiencia = $audiencias->get(1);
            if ($citado->notificacion == "Centro") {
                $fechaCitatorio = $segundaAudiencia->created_at;
            } 
        }
        elseif ($totalAudiencias > 2) {
            $fechaCitatorio = $audiencias->last()->created_at;
        }
        $id_notificador = $citado->id_notificador;
 
        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();
 
        /*$imagenes = [];
 
        for ($i = 1; $i <= 3; $i++) {
            $path = storage_path("app/documentos_notificacion/{$citado->id}-foto{$i}.jpg");
 
            if (file_exists($path)) {
                $imagenes[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }*/
        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
             
        $html = view('PDF/Solicitudes/razonNoExitosaNS', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','fechaCitatorio'))->render();
 
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 
 
        $nombreArchivo = 'Razón_NotificaciónNInt' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo);                      
    }

    //Guarda el expediente
    public function guardar_expediente(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        $audienciaId = $data['audiencia_id']; 
        $solicitud = SeerPerGeneral::find($audienciaId);

        if ($request->hasFile('documentoExpediente')) {
            $file = $request->file('documentoExpediente');
            if ($file->isValid()) {
                //Creamos primero el registro para obtener un ID y así generar un nombre único
                $doc = DocumentosSolicitud::create([
                    'id_solicitud'     => $data['audiencia_id'],
                    'nombre_documento' => '',
                    'tipo_documentos'  => $file->getClientOriginalName(),
                    'tramite'          => 'Audiencia',
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
        
    public function actualiza_citados(Request $request){
        $data = $request->all();

        $citado = SeerCitados::find($data['id_citado_pf']);
        $id_solicitud = $citado->id_solicitud;
        $sessionKey = "audiencia_data_{$id_solicitud}";

        if (session()->has($sessionKey)) {
            $sessionData = session($sessionKey);
            $citados = $sessionData['citados'];
            
            $citados = $citados->map(function ($c) use ($data) {
                if ($c->id == $data['id_citado_pf']) {
                    $c->nombre = $data["nombre"];
                    $c->primer_apellido = $data["primer_apellido"];
                    $c->segundo_apellido = $data["segundo_apellido"];
                }
                return $c;
            });

            $sessionData['citados'] = $citados;
            session([$sessionKey => $sessionData]);
        } else {
            SeerCitados::find($data['id_citado_pf'])->update([
                'nombre'            => $data["nombre"],
                'primer_apellido'   => $data["primer_apellido"], 
                'segundo_apellido'  => $data["segundo_apellido"]
            ]);
        }

        return back()->with('success', 'Nombre del Citado Actualizado Correctamente.');
    }

    public function Ver_INE_Solicitante(){

    }

    public function Ver_Documentos_Solicitante($id){

    }

    // PDF PTU
    public function VerPDFConvenioPTU($id){
       $solicitud = SeerPerGeneral::find($id); 
        // Priorizar datos temporales de la vista previa guardados en sesión
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        $sessionData = session()->get($sessionKey);
        if ($sessionData && is_array($sessionData)) {
            $datosAudiencia = (object) [
                'resolicion_primera' => $sessionData['primera'] ?? '',
                'resolucion_primera' => $sessionData['primera'] ?? '',
                'resolicion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolucion_justificacion' => $sessionData['justificacion'] ?? '',
                'resolicion_segunda' => $sessionData['segunda'] ?? '',
                'resolucion_segunda' => $sessionData['segunda'] ?? '',
                'vacaciones' => $sessionData['vacaciones'] ?? null,
                'aguinaldo' => $sessionData['aguinaldo'] ?? null,
                'otros' => $sessionData['otros'] ?? null,
                'horario' => $sessionData['horario'] ?? null,
                'comida' => $sessionData['comida'] ?? null,
                'tipo_audiencia' => $sessionData['tipo_audiencia'] ?? null,
                'conclucion' => $sessionData['conclucion'] ?? null,
                'pena_convencional' =>  $sessionData['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $sessionData['direccion_convenio'] ?? null
            ];
        } else {
            $datosAudiencia = SeerPerConciliador::where('id_solicitud', $id)
                ->orderBy('numero_audiencias', 'DESC')
                ->first();
            $datosExtraAudiencia = Audiencias::where('id_solicitud', $id)->orderBy('numero_audiencia', 'DESC')->first();
            if($datosAudiencia){
                $datosAudiencia->pena_convencional = $datosExtraAudiencia ? $datosExtraAudiencia->pena_convencional : '';
                $datosAudiencia->direccion_convenio = $datosExtraAudiencia ? $datosExtraAudiencia->direccion_convenio : '';
            }
            elseif(!$datosAudiencia && $datosExtraAudiencia){
                $datosAudiencia = (object)[
                    'pena_convencional' =>  $datosExtraAudiencia->pena_convencional,
                    'direccion_convenio'    =>  $datosExtraAudiencia->direccion_convenio,
                ];

            }
        }
        $pagos = Pagos::where('id_solicitud', $id)->get();
        $municipio = Municipios::find($solicitud->municipio_rat);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($solicitud->estado_rat);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';
        $abogado = Poder::join('seer_citados','seer_citados.id_abogado','abogados.idAbogado')
        ->where('id_solicitud',$id)
        ->select('abogados.nombre_representante','abogados.nombre_representante','abogados.segundo_apellido_representante','abogados.descipcion_poder','abogados.tipo_identificacion',
        'abogados.num_identificacion','estado_patronal','municipio_patronal','tipo_vialidad_patronal','vialidad_patronal','num_ext_patronal','mun_int_patronal','colonia_patronal','cp_patronal')
        ->first();
        $delegacion = $solicitud->delegacion;
        $delegado = User::where('delegacion', $delegacion)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();   
        $conceptosTexto = [];
        $deduccionesTexto = [];

        if ($sessionData && is_array($sessionData)) {
            $prestaciones = collect();
            $tipos = $sessionData['tipo_pago'] ?? [];
            $montos_p = $sessionData['monto_pago'] ?? [];
            $otras = $sessionData['otra_prestacion'] ?? [];
            $countPrest = max(count($tipos), count($montos_p));
            for ($i = 0; $i < $countPrest; $i++) {
                $descripcion = $tipos[$i] ?? '';
                if (($descripcion === 'Otras' || $descripcion === 'Otras') && isset($otras[$i]) && trim($otras[$i]) !== '') {
                    $descripcion = trim($otras[$i]);
                }
                $montoVal = isset($montos_p[$i]) ? floatval($montos_p[$i]) : 0;
                $obj = (object) [
                    'id' => 's_p_'.$i,
                    'descripcion' => $descripcion,
                    'monto' => $montoVal,
                ];
                $prestaciones->push($obj);
            }

            $deducciones = collect();
            $descDed = $sessionData['descripcion_deduccion'] ?? [];
            $montosDed = $sessionData['monto_deduccion'] ?? [];
            $countDed = max(count($descDed), count($montosDed));
            for ($i = 0; $i < $countDed; $i++) {
                $montoVal = isset($montosDed[$i]) ? floatval($montosDed[$i]) : 0;
                $obj = (object) [
                    'id' => 's_d_'.$i,
                    'descripcion' => $descDed[$i] ?? '',
                    'monto' => $montoVal,
                ];
                $deducciones->push($obj);
            }

            // pagos desde sesión
            $pagos = collect();
            $dias = $sessionData['dias_pagos'] ?? [];
            $horas = $sessionData['hora_pagos'] ?? [];
            $montosPag = $sessionData['monto_pagos'] ?? [];
            $descPag = $sessionData['descripcion_pagos'] ?? [];
            $countPag = max(count($dias), count($montosPag));
            for ($i = 0; $i < $countPag; $i++) {
                $obj = (object) [
                    'id_solicitud' => $id,
                    'fecha' => $dias[$i] ?? null,
                    'hora' => $horas[$i] ?? null,
                    'monto' => isset($montosPag[$i]) ? floatval($montosPag[$i]) : 0,
                    'descripcion' => $descPag[$i] ?? '',
                ];
                $pagos->push($obj);
            }

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }
            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = collect($prestaciones)->sum('monto');
            $totalDeducciones = collect($deducciones)->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        } else {
            $prestaciones = Concepto::where('id_solicitud', $id)->get();
            $deducciones = Deducciones::where('id_solicitud', $id)->get();

            foreach ($prestaciones as $concepto) {
                $conceptosTexto[$concepto->id] = $this->convertirNumerosALetras($concepto->monto);
            }

            foreach ($deducciones as $deduccion) {
                $deduccionesTexto[$deduccion->id] = $this->convertirNumerosALetras($deduccion->monto);
            }

            $totalPrestaciones = $prestaciones->sum('monto');
            $totalDeducciones = $deducciones->sum('monto');
            $pagoTotal = $totalPrestaciones - $totalDeducciones;
        }

        // Asegurar que $datosAudiencia tenga la propiedad monto (si se uso sesión puede no existir)
        if (isset($datosAudiencia) && is_object($datosAudiencia) && !property_exists($datosAudiencia, 'monto')) {
            // Tomar monto de la sesión si existe, si no usar el cálculo de prestaciones menos deducciones
            $datosAudiencia->monto = isset($sessionData) && is_array($sessionData) && isset($sessionData['monto']) ? $sessionData['monto'] : $pagoTotal;
        }
        
        $pagosDif  = Pagos::join("turnos","turnos.id","=","pago_solicitud.id_solicitud");
        $pagosDif = $pagosDif->where("pago_solicitud.id_solicitud", "=", $id)
        ->select(DB::raw('count(pago_solicitud.id_solicitud) as C_pagos'))
        ->first();

        $conciliador = User::join("seer_general", "seer_general.conciliador_id", "=", "users.id")
        ->where("seer_general.id", "=", $id)
        ->select("users.name")
        ->first();

        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        // $dias_descanso = $solicitud->dias !== null ? 7 - $solicitud->dias : null;
        $salario_diario = $this->calcularSalarioDiario($solicitante->pago, $solicitante->periodo_pago);
        $salario_mensual = $salario_diario * 30;
        $diarioTexto = $this->convertirNumerosALetras($salario_diario);
        $mensualTexto = $this->convertirNumerosALetras($salario_mensual);
        $montoTexto = $this->convertirNumerosALetras($solicitante->monto);
        $montoPena = is_numeric($datosAudiencia->pena_convencional) ? $datosAudiencia->pena_convencional : 0;
        $penaTexto = $this->convertirNumerosALetras($montoPena);

        $idsSession = session()->get('convenio_citados_' . $id);

        if ($idsSession !== null) {
        // Si existen en sesión, filtramos por esos IDs específicos
        $citados = SeerCitados::whereIn('id', $idsSession)
                    ->where('id_solicitud', $id)
                    ->get();
        } else {
            // Si no hay sesión (ej. el usuario recargó o entró directo), usamos la lógica de BD
            $citados = SeerCitados::where('id_solicitud', $id)
                        ->where('aparece_convenio', 1)
                        ->get();
        }

        // Descripción del tipo de identificación para los solicitantes y poderes
        $identificacionSolicitante = $solicitante->identificacion;
        $descripcionIdentificacionS = $this->descripcionIdentificacion($identificacionSolicitante);
        $identificacionPoder = $abogado->tipo_identificacion;
        $descripcionIdentificacionP = $this->descripcionIdentificacion($identificacionPoder);
        
        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id");
        $audiencia = $audiencia->where("audiencias.id_solicitud", "=", $solicitud["id"])
        ->first();

        $html = view('PDF/Solicitudes/convenioPTUNoLabora', 
        compact('id', 'solicitud', /*'dias_descanso',*/ 'salario_diario','salario_mensual','pagos','diarioTexto','mensualTexto','montoTexto','penaTexto','prestaciones','pagoTotal','delegado',
        'pagosDif','conciliador','solicitante','citados','abogado','datosAudiencia','descripcionIdentificacionS','descripcionIdentificacionP','audiencia','conceptosTexto','municipioEmpresa','estadoEmpresa'))
        ->render();
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('Convenio_PTU.pdf');           
    }

    public function Historial_Solicitante(){ //ANA
        return view('solicitudes.solicitud_revision');
    }

    public function VerDocumentosAudiencia($id){
        $documento_general = SeerPerGeneral::find($id); 
        $documento_solicitante = SeerSolicitante::where('id_solicitud',$id)
        //->select('documentoCurp','documentoIdentificacion')
        ->first(); 
        
        //Documento de comparecencia para citados patronales
        $documentos_comparecencia = null;
        if ($documento_general->tipo_solicitud == 2) {
            $documentos_comparecencia = \App\Models\SeerCitados::where('id_solicitud', $id)
                ->whereNotNull('identificacion_comparecencia')
                ->where('identificacion_comparecencia', '!=', '')
                ->get();
        }

        //Documentos del abogado y citados
        $documento_abogado = Poder::find($documento_general["idAbogado"]);
        //Documentos perdona fisica
        $documento_fisica = PersonaFisica::
        join('seer_citados','seer_citados.id_fisica','persona_fisica.id')
        ->where('seer_citados.id_solicitud',$id)
        ->select('persona_fisica.documentoIdentificacion')
        ->get();
        
         //Documentos subidos
        $documento_subidos = DocumentosSolicitud::where('id_solicitud',$documento_general->id)->where('tramite','Audiencia')->get();

        return view('solicitudes.verDocumentos',compact('documento_general','documento_solicitante','documento_abogado','documento_fisica','documento_subidos','documentos_comparecencia'));
     }

   //PDF Constancia de cumplimiento
    public function VerPDFCumplimiento($id){
        $pagos = Pagos::find($id);
        /*$delegadosEspeciales = [
            'Zitácuaro'        => 11,
            'Lázaro Cárdenas'  => 43,
            'Sahuayo'          => 26,
        ];*/
        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $pagos["id"])
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
            /*if (array_key_exists($delegacion, $delegadosEspeciales)) {
                $delegado = User::select('id', 'name', 'delegacion')
                    ->find($delegadosEspeciales[$delegacion]);
            } else {
                $delegado = User::where('delegacion', $delegacion)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Delegado');
                    })
                    ->select('users.id', 'users.name', 'users.delegacion')
                    ->first();
            }*/
            $delegado = null;
            if (!empty($solicitud->delegado_id)) {
                $delegado = User::select('id', 'name', 'delegacion')->find($solicitud->delegado_id);
            }
            if (!$delegado) {
                $delegado = User::where('delegacion', $delegacion)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Delegado');
                    })
                    ->select('users.id', 'users.name', 'users.delegacion')
                    ->first();
            }       
            $html = view('PDF/Cumplimientos/pagosParciales', compact('id', 'solicitud','conciliador','pagos','delegado','delegacion'))->render();
        }else{
            $solicitud = Turnos::find($pagos["id_solicitud"]);
            $delegacion = $solicitud->delegacion;
            /*$delegado = User::where('delegacion', $delegacion)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Delegado');
                })
                ->select('users.id', 'users.name', 'users.delegacion')
                ->first();*/
            $delegado = null;
            if (!empty($solicitud->delegado_id)) {
                $delegado = User::select('id', 'name', 'delegacion')->find($solicitud->delegado_id);
            }
            if (!$delegado) {
                $delegado = User::where('delegacion', $delegacion)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Delegado');
                    })
                    ->select('users.id', 'users.name', 'users.delegacion')
                    ->first();
            }
            $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("turnos.id", "=", $solicitud["id"])
            ->select('users.name')
            ->first();
            // Obtener el número de pagos
            $pagosDif = Pagos::where("id_solicitud", $pagos->id_solicitud)->count();
            $html = view('PDF/pagosParciales', compact('id', 'solicitud','conciliador','pagos','pagosDif','delegado','delegacion'))->render();
        }

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_cumplimiento_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    public function notificaciones_consultar(){
       //return view('/notificaciones/consultar');
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $delegacion = $user->delegacion;
        if($userRole[0] == "Enlace" || $userRole[0] == "Estadistica"){
            if($delegacion == "Morelia"){
                $delegaciones = ["Morelia", "Zitácuaro"];
            }
            else if($delegacion == "Uruapan"){
                $delegaciones = ["Uruapan", "Lázaro Cárdenas"];
            }
            else if($delegacion == "Zamora"){
                $delegaciones = ["Zamora", "Sahuayo"];
            }
            else if($delegacion == "Lázaro Cárdenas"){
                $delegaciones = ["Lázaro Cárdenas"];
            }
            else if($delegacion == "Zitácuaro"){
                $delegaciones = ["Zitácuaro"];
            }
            else if($delegacion == "Sahuayo"){
                $delegaciones = ["Sahuayo"];
            }
            $notificaciones = SeerCitados::join('seer_general','seer_general.id','seer_citados.id_solicitud')
            ->leftjoin('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
            ->leftjoin('estados', 'seer_citados.estado_citado', '=', 'estados.id')
            ->whereIn('seer_general.delegacion', $delegaciones)
            ->select('seer_citados.*','seer_general.NUE','municipios.nombre as municipio_citado','estados.nombre as estado_citado')
            ->orderBy('created_at', 'desc')->limit(3000)->get();
        }
        else{
            $notificaciones = SeerCitados::join('seer_general','seer_general.id','seer_citados.id_solicitud')
            ->leftjoin('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
            ->leftjoin('estados', 'seer_citados.estado_citado', '=', 'estados.id')
            ->select('seer_citados.*','seer_general.NUE','municipios.nombre as municipio_citado','estados.nombre as estado_citado')
            ->orderBy('created_at', 'desc')->limit(3000)->get();
        }
       
        return view('/notificaciones.index_busqueda',compact('notificaciones'));
    }

    public function notificaciones_busqueda(Request $request){
        
        $request->validate([
            'fecha_inicio' => 'required',
            'fecha_final' => 'required',
        ]);

        $data = $request->all();
        $fecha_inicio = $data["fecha_inicio"];
        $fecha_fin = $data["fecha_final"];
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        //$fecha_actual = date('y-m-d');
        $personas = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Notificador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        $notificaciones = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->leftJoin('users', 'seer_citados.id_notificador', '=', 'users.id')
        ->select('seer_general.id as id_solicitud','seer_citados.id as id_citado','seer_general.NUE',
            'seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
            'seer_citados.colonia','seer_citados.calle','seer_citados.n_ext','seer_citados.n_int','seer_citados.estatus','seer_citados.tipo_notificacion','users.name as notificador_nombre')
        ->where('seer_general.delegacion', $user["delegacion"])
        //->where('seer_citados.id_notificador', '!=', 0)
        ->where('seer_citados.notificacion',"!=", "Trabajador")
        ->whereBetween('seer_general.fecha', [$data["fecha_inicio"], $data["fecha_final"]])
        ->get();

        return view('notificaciones.index_busqueda',compact('notificaciones','personas','userRole','fecha_inicio','fecha_fin'));
    }
    
    //PDF COMPARECE REPRESENTANTE LEGAL SIN PODER
    public function VerPDFCompareceSinPoder($id){
        $solicitud = SeerPerGeneral::find($id);
        $conciliador  = User::join("seer_general","seer_general.conciliador_id","=","users.id");
        $conciliador = $conciliador->where("seer_general.id", "=", $id)
        ->select('users.name')
        ->first();
       
        $solicitante = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $citado = SeerCitados::where('id_solicitud', $id)->first();
        $abogado = Poder::join('seer_citados','seer_citados.id_abogado','abogados.idAbogado')
        ->where('id_solicitud',$id)
        ->select('abogados.nombres_patronal','abogados.primer_apellido_patronal','abogados.segundo_apellido_patronal','abogados.descipcion_poder')
        ->first();
        $html = view('PDF/Solicitudes/compareceSinPoder', 
            compact('id', 'solicitud', 'conciliador','solicitante','citado','abogado'))
            ->render();
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('compareceSinPoder.pdf');                   
    }
    
    //Cumplimiento cuando no comparece el trabajador
    public function cumplimiento_incompa_rati($id){
        $pagos = Pagos::find($id);
        
        $id_solicitud = $pagos["id_solicitud"];
        Pagos::find($id)->update(['estatus'  => "Incomparecencia trabajador"]);
        Turnos::find($id_solicitud)->update(['estatus' => "Incumplimiento"]); //Revisar si se va a archivar, o q procede ANA

        return redirect()->route('cumplimiento_actual');
        //return redirect()->route('ratificacion_atender'); 
    }

    //Muestra la delegación que le corresponde según el municipío seleccionado
    public function DelegacionPorMunicipio($municipioId)
    {
        $municipio = Municipios::find($municipioId);

        if (!$municipio) {
            return response()->json([
                'success' => false,
                'message' => 'Municipio no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'delegacion_id' => $municipio->delegacion_id,
        ]);
    }

    public function check_limite_diario(Request $request)
    {
        $data = $request->validate([
            'delegacion' => ['required', 'string'],
        ]);

        $delegacion = $data['delegacion'];
        $hoy = now()->toDateString();
        $limite = 5;

        $conteo = SeerPerGeneral::query()
            ->where('delegacion', $delegacion)
            ->where('tipo_generacion', 0)
            ->whereDate('fecha', $hoy)
            ->count();

        return response()->json([
            'success'    => true,
            'delegacion' => $delegacion,
            'hoy'        => $hoy,
            'limite'     => $limite,
            'conteo'     => $conteo,
            'reached'    => $conteo >= $limite,
        ]);
    }

    public function guardarSeleccionConvenioSession(Request $request)
    {
        $idSolicitud = $request->input('id_solicitud');
        // Esto será un array con los IDs de los citados seleccionados (ej: [5, 8, 12])
        $idsSeleccionados = $request->input('ids_seleccionados', []); 

        // Guardamos en sesión con una llave única para esta solicitud
        session()->put('convenio_citados_' . $idSolicitud, $idsSeleccionados);

        return response()->json(['status' => 'success']);
    }

    // Guardar temporalmente la selección de citados para el ACTA de audiencia (sesión)
    public function guardarSeleccionActaSession(Request $request)
    {
        $idSolicitud = $request->input('id_solicitud');
        $idsSeleccionados = $request->input('ids_seleccionados', []);

        session()->put('acta_citados_' . $idSolicitud, $idsSeleccionados);

        return response()->json(['status' => 'success']);
    }

    public function vista_previa($id){
        $audiencia_id = request()->query('audiencia_id');
        if (is_null($audiencia_id) || $audiencia_id === '') {
            $audiencia_id = Audiencias::where('id_solicitud', $id)->latest('id')->value('id');
            if (!is_null($audiencia_id) && $audiencia_id !== '') {
                return redirect()->route('vista_previa', ['id_solicitud' => $id, 'audiencia_id' => $audiencia_id]);
            }
        }

        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);           
        $conciliadores  = SeerPerConciliador::when(!empty($audiencia_id), function ($q) use ($audiencia_id) {
                return $q->where('audiencia_id', $audiencia_id);
            })
            ->where('id_solicitud',$id)
            ->orderBy('id', 'desc')
            ->first();
        $solicitud      = SeerPerGeneral::find($id);
        $conciliador    = User::select('name')->where('id', $solicitud->conciliador_id)->first();
        $tipo_solicitud = $solicitud->tipo_solicitud;

        $allCentro = 1;
        $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
        foreach ($citadosCentro as $citado){
            if($citado->notificacion == 'Centro'){
                $allCentro = 0;
                break;
            }
        }

        if ( $allCentro == 0 ){
            $baseQuery = SeerCitados::
            leftjoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado')
            ->leftJoin('persona_fisica', 'persona_fisica.id', '=', 'seer_citados.id_fisica')
            ->where('seer_citados.id_solicitud', $id)
            ->where('seer_citados.notificacion', 'Centro')
            ->where('seer_citados.tipo_notificacion', '!=', 'Multa')
            ->whereNotNull('seer_citados.id_abogado')
            ->select('seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido','seer_citados.rfc',
            'abogados.nombres_patronal as nombre_abogado','abogados.primer_apellido_patronal as primero_abogado','abogados.segundo_apellido_patronal as segundo_abogado',
            'persona_fisica.nombre as nombre_fisica','persona_fisica.primer_apellido as primer_fisica','persona_fisica.segundo_apellido as segundo_fisica',
            'seer_citados.id_abogado','seer_citados.id_fisica','seer_citados.id','seer_citados.notificacion','seer_citados.estatus','abogados.tipo_identificacion');

            /*$hayNotificadaAudiencia = (clone $baseQuery)
                ->where('seer_citados.estatus', 'Notificada en Audiencia')
                ->exists();

            if ($hayNotificadaAudiencia) {
                $baseQuery->where('seer_citados.estatus', 'Notificada en Audiencia');
            }*/

            $representantes = $baseQuery->get();
        }else {
            $representantes = SeerCitados::
            leftjoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado')
            ->leftJoin('persona_fisica', 'persona_fisica.id', '=', 'seer_citados.id_fisica')
            ->where('seer_citados.id_solicitud', $id)
            ->whereNotNull('seer_citados.id_abogado')
            ->select('seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido','seer_citados.rfc',
            'abogados.nombres_patronal as nombre_abogado','abogados.primer_apellido_patronal as primero_abogado','abogados.segundo_apellido_patronal as segundo_abogado',
            'persona_fisica.nombre as nombre_fisica','persona_fisica.primer_apellido as primer_fisica','persona_fisica.segundo_apellido as segundo_fisica',
            'seer_citados.id_abogado','seer_citados.id_fisica','seer_citados.id','seer_citados.notificacion','seer_citados.estatus','abogados.tipo_identificacion')
            ->get();
        }
        
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        $abogados = Poder::all();
        SeerPerGeneral::find($id)->update(['conciliador' => $user->id, 'estatus' => 'Confirmado']);
        $estados        = Estados::all();
        $municipios     = Municipios::all();
        $conceptos      = Concepto::where('id_solicitud',$id)->where('tipo_pago','Audiencia')->get();
        $pagos          = Pagos::where('id_solicitud',$id)->whereIN('tipo_pago',['Audiencia','Conciliador'])->get();
        $deducciones    = Deducciones::where('id_solicitud',$id)->where('tipo_pago','Audiencia')->get();

        // --- VISTA PREVIA LOGIC ---
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        
        // Si no existe registro en BD, iniciamos uno vacío para no romper la vista
        if(!$conciliadores) {
            $conciliadores = new SeerPerConciliador();
            // Inicializar valores por defecto para evitar warnings en la vista
            $conciliadores->resolicion_primera = '';
            $conciliadores->resolicion_justificacion = '';
            $conciliadores->resolicion_segunda = '';
            $conciliadores->conclucion = '';
            $conciliadores->vacaciones = '';
            $conciliadores->aguinaldo = '';
            $conciliadores->otros = '';
            $conciliadores->horario = '';
            $conciliadores->comida = '';
            $conciliadores->tipo_audiencia = '';
        }

        if(session()->has($sessionKey)){
            $sData = session($sessionKey);

            $conceptos = collect();
            if(isset($sData['tipo_pago'])){
                foreach($sData['tipo_pago'] as $k => $desc){
                     $obj = new \stdClass();
                     $obj->id = null;
                     if($desc == "Otras" && isset($sData['otra_prestacion'][$k])) $desc = $sData['otra_prestacion'][$k];
                     $obj->descripcion = $desc;
                     $obj->monto = $sData['monto_pago'][$k] ?? 0;
                     // Importante: session_index para eliminar
                     $obj->session_index = $k;
                     $conceptos->push($obj);
                }
            }

            $deducciones = collect();
            if(isset($sData['descripcion_deduccion'])){
                foreach($sData['descripcion_deduccion'] as $k => $desc){
                    $obj = new \stdClass();
                    $obj->id = null;
                    $obj->descripcion = $desc;
                    $obj->monto = $sData['monto_deduccion'][$k] ?? 0;
                    $obj->session_index = $k;
                    $deducciones->push($obj);
                }
            }

            $pagos = collect();
            if(isset($sData['dias_pagos'])){
                foreach($sData['dias_pagos'] as $k => $fecha){
                    $obj = new \stdClass();
                    $obj->id = null;
                    $obj->fecha = $fecha;
                    $obj->hora = $sData['hora_pagos'][$k] ?? '';
                    $obj->monto = $sData['monto_pagos'][$k] ?? 0;
                    $obj->descripcion = $sData['descripcion_pagos'][$k] ?? '';
                    $obj->tipo_pago = $sData['tipo_pagoAgenda'][$k] ?? 'Audiencia'; 
                    $obj->session_index = $k;
                    $pagos->push($obj);
                }
            }
            
            // Hydrate Conciliadores fields from Session
            $conciliadores->resolicion_primera = $sData['primera'] ?? $conciliadores->resolicion_primera;
            $conciliadores->resolicion_justificacion = $sData['justificacion'] ?? $conciliadores->resolicion_justificacion;
            $conciliadores->resolicion_segunda = $sData['segunda'] ?? $conciliadores->resolicion_segunda;
            $conciliadores->conclucion = $sData['conclucion'] ?? $conciliadores->conclucion;
            $conciliadores->vacaciones = $sData['vacaciones'] ?? $conciliadores->vacaciones;
            $conciliadores->aguinaldo = $sData['aguinaldo'] ?? $conciliadores->aguinaldo;
            $conciliadores->otros = $sData['otros'] ?? $conciliadores->otros;
            $conciliadores->horario = $sData['horario'] ?? $conciliadores->horario;
            $conciliadores->comida = $sData['comida'] ?? $conciliadores->comida;
            $conciliadores->tipo_audiencia = $sData['tipo_audiencia'] ?? $conciliadores->tipo_audiencia;
            // Ensure compatibility if view uses 'tipo'
            $conciliadores->tipo = $conciliadores->tipo_audiencia;
            $conciliadores->pena_convencional = $sData['pena_convencional'] ?? null;
            $conciliadores->direccion_convenio = $sData['direccion_convenio'] ?? null;
        }
        // --------------------------

        return view('/audiencias/audiencia_revisar',compact('id','conciliadores','representantes','solicitante','conciliador','solicitud','abogados','estados','municipios','conceptos','pagos','deducciones', 'tipo_solicitud'));
    }

    public function vista_previa_patronal($id){
        $audiencia_id = request()->query('audiencia_id');
        if (is_null($audiencia_id) || $audiencia_id === '') {
            $audiencia_id = Audiencias::where('id_solicitud', $id)->latest('id')->value('id');
            if (!is_null($audiencia_id) && $audiencia_id !== '') {
                return redirect()->route('vista_previa', ['id_solicitud' => $id, 'audiencia_id' => $audiencia_id]);
            }
        }

        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);           
        $conciliadores  = SeerPerConciliador::when(!empty($audiencia_id), function ($q) use ($audiencia_id) {
                return $q->where('audiencia_id', $audiencia_id);
            })
            ->where('id_solicitud',$id)
            ->orderBy('id', 'desc')
            ->first();
        $solicitud      = SeerPerGeneral::find($id);
        $conciliador    = User::select('name')->where('id', $solicitud->conciliador_id)->first();
        $tipo_solicitud = $solicitud->tipo_solicitud;

        // Patronal: show all citados (representantes in this view)
        $representantes = SeerCitados::where('id_solicitud', $id)
            ->where('audiencia_id', $audiencia_id)
            ->select('id', 'nombre', 'primer_apellido', 'segundo_apellido', 'notificacion', 'estatus')
            ->get();
        
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        $abogados = Poder::all();
        SeerPerGeneral::find($id)->update(['conciliador' => $user->id, 'estatus' => 'Confirmado']);
        $estados        = Estados::all();
        $municipios     = Municipios::all();
        $conceptos      = Concepto::where('id_solicitud',$id)->where('tipo_pago','Audiencia')->get();
        $pagos          = Pagos::where('id_solicitud',$id)->whereIN('tipo_pago',['Audiencia','Conciliador'])->get();
        $deducciones    = Deducciones::where('id_solicitud',$id)->where('tipo_pago','Audiencia')->get();

        // --- VISTA PREVIA LOGIC ---
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        
        // Si no existe registro en BD, iniciamos uno vacío para no romper la vista
        if(!$conciliadores) {
            $conciliadores = new SeerPerConciliador();
            // Inicializar valores por defecto para evitar warnings en la vista
            $conciliadores->resolicion_primera = '';
            $conciliadores->resolicion_justificacion = '';
            $conciliadores->resolicion_segunda = '';
            $conciliadores->conclucion = '';
            $conciliadores->vacaciones = '';
            $conciliadores->aguinaldo = '';
            $conciliadores->otros = '';
            $conciliadores->horario = '';
            $conciliadores->comida = '';
            $conciliadores->tipo_audiencia = '';
        }

        if(session()->has($sessionKey)){
            $sData = session($sessionKey);

            $conceptos = collect();
            if(isset($sData['tipo_pago'])){
                foreach($sData['tipo_pago'] as $k => $desc){
                     $obj = new \stdClass();
                     $obj->id = null;
                     if($desc == "Otras" && isset($sData['otra_prestacion'][$k])) $desc = $sData['otra_prestacion'][$k];
                     $obj->descripcion = $desc;
                     $obj->monto = $sData['monto_pago'][$k] ?? 0;
                     // Importante: session_index para eliminar
                     $obj->session_index = $k;
                     $conceptos->push($obj);
                }
            }

            $deducciones = collect();
            if(isset($sData['descripcion_deduccion'])){
                foreach($sData['descripcion_deduccion'] as $k => $desc){
                    $obj = new \stdClass();
                    $obj->id = null;
                    $obj->descripcion = $desc;
                    $obj->monto = $sData['monto_deduccion'][$k] ?? 0;
                    $obj->session_index = $k;
                    $deducciones->push($obj);
                }
            }

            $pagos = collect();
            if(isset($sData['dias_pagos'])){
                foreach($sData['dias_pagos'] as $k => $fecha){
                    $obj = new \stdClass();
                    $obj->id = null;
                    $obj->fecha = $fecha;
                    $obj->hora = $sData['hora_pagos'][$k] ?? '';
                    $obj->monto = $sData['monto_pagos'][$k] ?? 0;
                    $obj->descripcion = $sData['descripcion_pagos'][$k] ?? '';
                    $obj->tipo_pago = $sData['tipo_pagoAgenda'][$k] ?? 'Audiencia'; 
                    $obj->session_index = $k;
                    $pagos->push($obj);
                }
            }
            
            // Hydrate Conciliadores fields from Session
            $conciliadores->resolicion_primera = $sData['primera'] ?? $conciliadores->resolicion_primera;
            $conciliadores->resolicion_justificacion = $sData['justificacion'] ?? $conciliadores->resolicion_justificacion;
            $conciliadores->resolicion_segunda = $sData['segunda'] ?? $conciliadores->resolicion_segunda;
            $conciliadores->conclucion = $sData['conclucion'] ?? $conciliadores->conclucion;
            $conciliadores->vacaciones = $sData['vacaciones'] ?? $conciliadores->vacaciones;
            $conciliadores->aguinaldo = $sData['aguinaldo'] ?? $conciliadores->aguinaldo;
            $conciliadores->otros = $sData['otros'] ?? $conciliadores->otros;
            $conciliadores->horario = $sData['horario'] ?? $conciliadores->horario;
            $conciliadores->comida = $sData['comida'] ?? $conciliadores->comida;
            $conciliadores->tipo_audiencia = $sData['tipo_audiencia'] ?? $conciliadores->tipo_audiencia;
            // Ensure compatibility if view uses 'tipo'
            $conciliadores->tipo = $conciliadores->tipo_audiencia;
            $conciliadores->pena_convencional = $sData['pena_convencional'] ?? null;
            $conciliadores->direccion_convenio = $sData['direccion_convenio'] ?? null;
        }
        // --------------------------

        return view('/audiencias/audiencia_revisar_patronal',compact('id','conciliadores','representantes','solicitante','conciliador','solicitud','abogados','estados','municipios','conceptos','pagos','deducciones', 'tipo_solicitud'));
    }

    public function editar_solicitud_audiencia(Request $request) {
        $data = $request->all();
        $id_solicitud = $data["id"];
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();

        $solicitante = SeerSolicitante::where('id_solicitud', $data['id'])->first();
    
        //Actualizar Solicitante
        SeerSolicitante::where('id_solicitud', $data["id"])
        ->update([
            'curp'                  => $data["curp"],
            'rfc'                   => $data["rfc"],
            'nombre'                => $data["nombre"],
            'puesto'                => $data["puesto"],
            'pago'                  => $data["pago"],
            'periodo_pago'          => $data["periodo_pago"],
            'fecha_ingreso'         => $data["fecha_ingreso"],
            'fecha_salida'          => $data["fecha_salida"],
            'jornada'               => $data["jornada"],
            'horas_semana'          => $data["horas"],
        ]);

        if(isset($data["seguro"])){
            SeerSolicitante::where('id_solicitud', $data["id"])->update(['nss' => $data["seguro"] ]);
        }
            
        return redirect()->route('vista_previa', compact('id_solicitud'));
      
    }

    public function seleccionar_abogado_audiencia(Request $request){
        $data = $request->all();
        $id_solicitud = $data["solicitud"];

        SeerCitados::find($data["citado"])
        ->update([
            'id_abogado'  => $data["abogado"],
        ]);

        return redirect()->route('vista_previa',compact('id_solicitud'));
    }

    public function insertar_citados_audiencia(Request $request) {
        $data = $request->all();
        //$id_solicitud = $data["id"];
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();
        
        if(!isset($data['moreliaSucursal'])){
            $regionmorelia = "No";
        }
        else{
            $regionmorelia = $data['moreliaSucursal'];
        }
        if(!isset($data['uruapanSucursal'])){
            $regionuruapan = "No";
        }
        else{
            $regionuruapan = $data['uruapanSucursal'];
        }
        if(!isset($data['zamoraSucursal'])){
            $regionzamora = "No";
        }
        else{
            $regionzamora = $data['zamoraSucursal'];
        }

        //Validar documentacion
        request()->validate([
            'nombresAbogadoAlta'        => 'required',
            'primer_apellido'           => 'required',
            'segundo_apellido'          => 'required',
            'correoAbogadoAlta'         => 'required',
            'empresaAbogadoAlta'        => 'required',
            'curpAbogadoAlta'           => 'required',
            'domicilioAbogadoAlta'      => 'required',
            'fechaVigenciaAlta'         => 'required',
            'industriaAlta'             => 'required',
            'descripcionpoderAlta'      => 'required',
            'documentoIne'              => 'required',
            'documentoRepresentacion'   => 'required',
            'documentoPoder'            => 'nullable',
            'documentoAnexo'            => 'nullable',
        ], $data);

        //Validar las regiones
        if($regionmorelia == "No" && $regionuruapan == "No" && $regionzamora == "No"){
            return back()->withErrors('Debes seleccionar al menos una Región.');
        }

        //Validar que no exista el abogado
        $abogado = Poder::where(['nombres' => $data["nombresAbogadoAlta"], 'primer_apellido' => $data["primer_apellido"], 
        'segundo_apellido' => $data["segundo_apellido"], 'empresa' => $data["empresaAbogadoAlta"]])->first();
        if(!$abogado){
            $data_insertar= array(
                'nombres'           => $data["nombresAbogadoAlta"],
                'primer_apellido'   => $data["primer_apellido"], 
                'segundo_apellido'  => $data["segundo_apellido"], 
                'telefono'          => $data["telefonoAbogadoAlta"], 
                'email'             => $data["correoAbogadoAlta"],
                'fechaRegistro'     => date('y-m-d'),
                'fechaVigencia'     => $data["fechaVigenciaAlta"],
                'empresa'           => $data["empresaAbogadoAlta"],
                'eliminado'         => 0,
                'curp'              => $data["curpAbogadoAlta"],
                'domicilio'         => $data["domicilioAbogadoAlta"],
                'rfc'               => $data["RFCAbogadoAlta"],
                'industria'         => $data["industriaAlta"],
                'poder'             => $data["descripcionpoderAlta"],
                'regionMorelia'     => $regionmorelia,
                'regionUruapan'     => $regionuruapan,
                'regionZamora'      => $regionzamora,
            );
            $nombre_ine = $data["nombresAbogadoAlta"]."".$data["primer_apellido"]."".$data["segundo_apellido"]."-".$data["empresaAbogadoAlta"]."_IDENTIFICACION.pdf";
            $path = Storage::putFileAs(
                'documentos_abogados', $request->file('documentoIne'), $nombre_ine
            );
            $nombre_representación = $data["nombresAbogadoAlta"]."".$data["primer_apellido"]."".$data["segundo_apellido"]."-".$data["empresaAbogadoAlta"]."_REPRESENTACION.pdf";
            $path = Storage::putFileAs(
                'documentos_abogados', $request->file('documentoRepresentacion'), $nombre_representación
            );
            //Si no existe
            if(!isset($data["documentoAnexo"])){
                $nombre_anexo = "Sin anexo";
            }
            else{
                $nombre_anexo = $data["nombresAbogadoAlta"]."".$data["primer_apellido"]."".$data["segundo_apellido"]."-".$data["empresaAbogadoAlta"]."_ANEXO.pdf";
               $path = Storage::putFileAs(
                    'documentos_abogados', $request->file('documentoAnexo'), $nombre_anexo
                );
            }

            if(!isset($data["documentoPoder"])){
                $nombre_poder = "Sin carta poder";
            }
            else{
                $nombre_poder = $data["nombresAbogadoAlta"]."".$data["primer_apellido"]."".$data["segundo_apellido"]."-".$data["empresaAbogadoAlta"]."_PODER.pdf";
                $path = Storage::putFileAs(
                    'documentos_abogados', $request->file('documentoPoder'), $nombre_poder
                );
            }

            $data_insertar["ine"] = $nombre_ine;
            $data_insertar["cedula"] = $nombre_poder;
            $data_insertar["anexo"] = $nombre_anexo;
            $data_insertar["representacion"] = $nombre_representación;

            $nuevoAbogado = Poder::create($data_insertar);
        }    
         
        $A_citado=SeerCitados::find($data['id_citado_2'])->update(['id_abogado' => $nuevoAbogado->idAbogado]);
        return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
    }

    public function insertar_citado_audiencia(Request $request){
        $data = $request->all();
        //$citados = SeerCitados::find($data["id_citado_pf"]);
       
        $data_insertar= array(
            'id_solicitud'              => $data["id"],
            'id_citado'                 => $data["id_citado_pf"],
            'nombre'                    => $data["nombre"],
            'primer_apellido'           => $data["primer_apellido"], 
            'segundo_apellido'          => $data["segundo_apellido"],
            'identificacion'            => $data["identificacionAlta"],
        );
        
        $documento = $data["nombre"]."-".$data["primer_apellido"]."-".$data["segundo_apellido"]."_Identificacion.pdf";
        $path = Storage::putFileAs(
            'documentosSolicitud', $request->file('documentoIdentificacion'), $documento
        );
        $data_insertar["documentoIdentificacion"] = $documento;

        PersonaFisica::create($data_insertar);   
        $id_adiencia = PersonaFisica::select('id')->orderBy('id', 'desc')->first();
        SeerCitados::find($data['id_citado_pf'])->update([
            'id_fisica'         => $id_adiencia["id"],
            'nombre'            => $data["nombre"],
            'primer_apellido'   => $data["primer_apellido"], 
            'segundo_apellido'  => $data["segundo_apellido"]
        ]);

        return back()->with('success', 'Representante legal registrado y asignado correctamente al citado.');
    }

    public function actualiza_citados_audiencia(Request $request){
        $data = $request->all();

        SeerCitados::find($data['id_citado_pf'])->update([
            'nombre'            => $data["nombre"],
            'primer_apellido'   => $data["primer_apellido"], 
            'segundo_apellido'  => $data["segundo_apellido"]
        ]);

        return back()->with('success', 'Nombre del Citado Actualizado Correctamente.');
    }

    public function concepto_eliminar_pago($id_solicitud){
        Concepto::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Borrado Correctamente.');
    }

    public function pago_eliminar_pago($id_solicitud){
        Pagos::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Borrado Correctamente.');
    }
    
    public function eliminar_item_sesion(Request $request, $id){
        $sessionKey = 'audiencia_conclucion_data_' . $id;
        if(session()->has($sessionKey)){
            $data = session($sessionKey);
            $type = $request->type; // 'pago', 'concepto', 'deduccion'
            $index = $request->index;
            
            $arrays = [];
            if($type == 'concepto'){
                 $arrays = ['tipo_pago', 'monto_pago', 'otra_prestacion']; 
            } elseif($type == 'deduccion'){
                 $arrays = ['descripcion_deduccion', 'monto_deduccion'];
            } elseif($type == 'pago'){ // Cumplimientos
                 $arrays = ['dias_pagos', 'hora_pagos', 'monto_pagos', 'descripcion_pagos', 'tipo_pagoAgenda'];
            }
            
            foreach($arrays as $arr){
                if(isset($data[$arr]) && array_key_exists($index, $data[$arr])){
                    unset($data[$arr][$index]);
                    // Reindexar array
                    $data[$arr] = array_values($data[$arr]);
                }
            }
            session([$sessionKey => $data]);
        }
        return back();
    }

    public function terminar_audiencia(Request $request){
        $data = $request->all();
        $id_solicitud = $data["id"];
        $audiencia_id = $data['audiencia_id'] ?? $request->query('audiencia_id');
        // --- MERGE SESSION (Vista Previa) ---
        $sessionKey = "audiencia_conclucion_data_{$data['id']}";
        if(session()->has($sessionKey)){
            $sessionData = session($sessionKey);
            $arraysToMerge = [
                'dias_pagos', 'hora_pagos', 'monto_pagos', 'descripcion_pagos', 'tipo_pagoAgenda', 
                'tipo_pago', 'monto_pago', 'otra_prestacion', 
                'descripcion_deduccion', 'monto_deduccion'
            ];
            foreach($arraysToMerge as $key){
                if(isset($sessionData[$key])){
                     if(isset($data[$key])) {
                         $data[$key] = array_merge($sessionData[$key], $data[$key]);
                     } else {
                         $data[$key] = $sessionData[$key];
                     }
                }
            }
            $data = array_merge($sessionData, $data); 
        }
        // ------------------------------------

        $apareceConvenioIds = (isset($data['aparece_convenio']) && is_array($data['aparece_convenio']))
            ? array_keys($data['aparece_convenio'])
            : [];

        SeerCitados::where('id_solicitud', $id_solicitud)->update(['aparece_convenio' => 0]);
        if (!empty($apareceConvenioIds)) {
            SeerCitados::whereIn('id', $apareceConvenioIds)->update(['aparece_convenio' => 1]);
        }
        
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        $solicitudOriginal = SeerPerGeneral::find($data["id"]);
        $sede = $solicitudOriginal->delegacion ?? $user->delegacion;

        if($data["conclucion"] == "Conciliacion" || $data["conclucion"] == "Reinstalacion"){
            //Revisar si existe
            if(isset($data["dias_pagos"])){
                $conteo = count($data["dias_pagos"]);
                for($i = 0; $i < $conteo; $i++) {
                    $data_pagos = [
                        'id_solicitud'  => $data["id"],
                        'fecha'         => $data["dias_pagos"][$i],
                        'hora'          => $data["hora_pagos"][$i], 
                        'monto'         => $data["monto_pagos"][$i], 
                        'descripcion'   => $data["descripcion_pagos"][$i],
                        'id_conciliador' => $solicitudOriginal->conciliador_id,
                        'estatus'       => "Pendiente", 
                        'tipo_pago'     => "Audiencia",
                        'delegacion'    => $sede,
                    ];
                    $monto = $monto + $data["monto_pagos"][$i];
                    Pagos::create($data_pagos);
                }
            }
            //Validar si existe un pago extra
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
                        'tipo_pago'     => "Audiencia"
                    ];
                    Concepto::create($data_citado);
                }
            }
            //Validar si existe una deducción extra
            if(isset($data["descripcion_deduccion"])){
                $cont = count($data["descripcion_deduccion"]);
                for($i = 0; $i < $cont; $i++) {
                    $data_deduccion = [
                        'id_solicitud'  => $data["id"], 
                        'monto'         => $data["monto_deduccion"][$i], 
                        'descripcion'   => $data["descripcion_deduccion"][$i],
                        'tipo_pago'     => "Audiencia"
                    ];
                    Deducciones::create($data_deduccion);
                }
            }
            $conciliadorRecord = SeerPerConciliador::when(!empty($audiencia_id), function ($q) use ($audiencia_id) {
                    return $q->where('audiencia_id', $audiencia_id);
                })
                ->where('id_solicitud', $data["id"])
                ->orderBy('id', 'desc')
                ->first();
            
            $data_conciliador = [
                'id_solicitud'          => $data["id"],
                'audiencia_id'          => $audiencia_id,
                'estatus_conciliacion'  => $data["conclucion"],
                'monto'                 => $monto,
                'tipo'                  => $data["tipo_audiencia"],
                'resolicion_primera'    =>  $data["primera"] ?? ($conciliadorRecord->resolicion_primera ?? ''),
                'resolicion_justificacion'=>  $data["justificacion"] ?? ($conciliadorRecord->resolicion_justificacion ?? ''),
                'resolicion_segunda'    =>  $data["segunda"] ?? ($conciliadorRecord->resolicion_segunda ?? ''),
                'conclucion'            =>  $data["conclucion"],
                'vacaciones'            =>  (isset($data["vacaciones"]) && $data["vacaciones"] !== '') ? $data["vacaciones"] : ($conciliadorRecord->vacaciones ?? 0),
                'aguinaldo'             =>  (isset($data["aguinaldo"]) && $data["aguinaldo"] !== '') ? $data["aguinaldo"] : ($conciliadorRecord->aguinaldo ?? 0),
                'otros'                 =>  (isset($data["otros"]) && $data["otros"] !== '') ? $data["otros"] : ($conciliadorRecord->otros ?? 0),
                'horario'               =>  $data["horario"] ?? ($conciliadorRecord->horario ?? ''),
                'comida'                =>  $data["comida"] ?? ($conciliadorRecord->comida ?? ''),
                'tipo_audiencia'        =>  $data["tipo_audiencia"],
            ];
            
            if($conciliadorRecord && (!empty($audiencia_id) ? ((int)$conciliadorRecord->audiencia_id === (int)$audiencia_id) : true)){
                $conciliadorRecord->update($data_conciliador);
            } else {
                 $solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();
                 $numero_audiencia = $this->GeneraAudiencia($data["id"]);
                 
                 $data_conciliador['numero_audiencia'] = $numero_audiencia["0"];
                 $data_conciliador['numero_audiencias'] = $numero_audiencia["1"];
                 $data_conciliador['consecutivo'] = $numero_audiencia[1];
                 $data_conciliador['rfc'] = $solicitante["rfc"];
                 $data_conciliador['NSS'] = $solicitante["nss"];
                 $data_conciliador['multa'] = 'No';
                 $data_conciliador['validado'] = 'Validado';
                 
                 SeerPerConciliador::create($data_conciliador);
                 
                 // Actualizar tabla Audiencias con el folio generado (solo si es nuevo)
                 $numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
                 Audiencias::where('id_solicitud',$data["id"])
                    ->orderBy('id_solicitud','desc')
                    ->update([
                        'numero_audiencia'  =>  $numAudiencia+1,
                        'folio_audiencia'   =>  $numero_audiencia[0],
                        'pena_convencional'  =>  $data['pena_convencional'],
                        'direccion_convenio'    =>  $data['direccion_convenio'],
                    ]);
            }
            if(session()->has("audiencia_conclucion_data_{$data['id']}")) {
                session()->forget("audiencia_conclucion_data_{$data['id']}");
            }

            SeerPerGeneral::find($data["id"])
            ->update([
                'tipo'                  => $data["tipo_audiencia"],
                'fecha_terminacion'     => $fecha_actual, 
                'estatus'               => $data["conclucion"]
            ]);
            
            //Se actualiza el estatus
            Audiencias::where('id_solicitud', $data["id"])
            ->orderBy('id', 'desc')
            ->first()
            ->update([
                'estatus' => $data["conclucion"],
                'pena_convencional'  =>  $data['pena_convencional'],
                'direccion_convenio'    =>  $data['direccion_convenio'],
            ]);

            $multas = SeerCitados::where('id_solicitud', $data["id"])->where('audiencia_id', $audiencia_id)->where('tipo_notificacion', 'Multa')->get();

            foreach($multas as $multa){
                $multa->delete();
            }

            //Validar la bandera para mostrar documento o 
            if(isset($data["bandera"]) && $data["bandera"] == 1){
                // Limpiar sesiones relacionadas cuando se pulsa "Terminar"
                session()->forget(["audiencia_conclucion_data_{$data['id']}", "convenio_citados_{$data['id']}", "acta_citados_{$data['id']}", 'preserve_edit_session']);
                return redirect()->route('todas_audiencias');
            }
            else if($data["bandera"] == 2){
                return redirect()->route('vista_previa',compact('id_solicitud'));
            }
            else if($data["bandera"] == 3 || $data["bandera"] == 4){
                
                //$this->VerPDFAudiencia($data["id"]);
                //$this->VerPDFConvenioSol($data["id"]);
                //return redirect()->route('vista_previa',compact('id_solicitud'));
            }
        }
        else{
            //$solicitante = SeerSolicitante::where('id_solicitud',$data["id"])->first();
            //Actualizar Audiecia
            SeerPerConciliador::where('id_solicitud',$data["id"])
            ->orderBy('id', 'desc')
            ->first()
            ->update([
                'tipo'                      =>  $data["tipo_audiencia"],
                'resolicion_primera'        =>  $data["primera"],
                'resolicion_justificacion'  =>  $data["justificacion"],
                'resolicion_segunda'        =>  $data["segunda"],
                'conclucion'                =>  $data["conclucion"],
                'vacaciones'                =>  $data["vacaciones"],
                'aguinaldo'                 =>  $data["aguinaldo"],
                'otros'                     =>  $data["otros"],
                'horario'                   =>  $data["horario"],
                'comida'                    =>  $data["comida"],
                'tipo_audiencia'            =>  $data["tipo_audiencia"],
            ]);

            SeerPerGeneral::find($data["id"])
            ->update([
                'tipo'                  => "Presencial",
                'fecha_terminacion'     => $fecha_actual, 
                'estatus'               => $data["conclucion"]
            ]);

            Audiencias::where('id_solicitud', $data["id"])
            ->orderBy('id', 'desc')
            ->first()
            ->update([
                'estatus' => $data["conclucion"],
            ]);
        }

        return redirect()->route('todas_audiencias');
        
    }

    //Crear un cumplimiento desde la agenda
    public function crear_cumplimiento(){
        return view('cumplimientos/crearEnAgenda');
    }


    public function guardar_cumplimiento(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $sede = $user->delegacion;

        $request->validate([
            'NUE'           => 'required',
            'empresa'       => 'required',
            'trabajador'    => 'required',
            'monto'         => 'required|numeric',
            'forma_pago'    => 'required',
            'sede'          => 'required',
            'fecha'         => 'required',
            'hora'          => 'required',
            'descripcion'   => 'required'
        ]);
            
        $data_insert=array(
            'id_solicitud'          => 0,
            'fecha'                 => $data["fecha"],
            'hora'                  => $data["hora"],
            'monto'                 => $data["monto"],
            'descripcion'           => $data["descripcion"],
            'estatus'               => "Pendiente",
            'tipo_pago'             => "Audiencia",
            'delegacion'            => $data["sede"],
            'id_conciliador'        => $id,
            'NUE'                   => $data["NUE"],
            'empresa_representante' => $data["empresa"],
            'nombre_trabajador'     => $data["trabajador"],
            'forma_pago'            => $data["forma_pago"],
            'delegacion'            => $sede,
        );

        Pagos::create($data_insert);

        return back()->with('success', 'Poder registrado correctamente.'); 
        //return view('cumplimientos/index')->with('success', 'Poder registrado correctamente.'); 
    }


    public function obtenerCumplimientos(Request $request)
    {
        $fecha_inicio_str = $request->input('start', now()->format('Y-m-d'));
        $fecha_fin_str = $request->input('end', now()->addDays(800)->format('Y-m-d'));

        $fecha_inicio_dt = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fecha_fin_dt = (new \DateTime($fecha_fin_str))->setTime(23, 59, 59);

        $sede = $request->input('sede');

        $inhabiles = DiasInhabiles::where('centro', $sede)
            ->where(function($query) use ($fecha_inicio_dt, $fecha_fin_dt) {
                $query->where('fecha_inicio', '<=', $fecha_fin_dt)
                    ->where('fecha_final', '>=', $fecha_inicio_dt);
            })
            ->get();

        $ocupados = Pagos::whereBetween('fecha', [$fecha_inicio_dt, $fecha_fin_dt])
            ->where('delegacion', $sede)
            ->get();
        /*    
        $ocupadosMap = [];
        foreach ($ocupados as $cumplimiento) {
            $slotKey = $cumplimiento->fecha->format('Y-m-d') . 'T' . $cumplimiento->hora->format('H:i:s');
            $ocupadosMap[$slotKey] = true;
        }
        */
        $ocupadosMap = [];
        foreach ($ocupados as $cumplimiento) {
            // Asume que $reserva tiene una propiedad 'start' con el formato 'Y-m-d\TH:i:s'
            $slotKey = $cumplimiento->fecha->format('Y-m-d') . 'T' . $cumplimiento->hora->format('H:i:s');
            
            if (isset($ocupadosMap[$slotKey])) {
                $ocupadosMap[$slotKey]++;
            } else {
                $ocupadosMap[$slotKey] = 1;
            }
        }
        $pagosPorDia = Pagos::where('tipo_pago', 'Audiencia')
            ->where('delegacion', $sede)
            ->whereBetween('fecha', [$fecha_inicio_dt, $fecha_fin_dt])
            ->select('fecha', DB::raw('COUNT(*) as total'))
            ->groupBy('fecha')
            ->get();

        $pagosPorDiaMap = [];
        foreach ($pagosPorDia as $dia) {
            $pagosPorDiaMap[$dia->fecha->format('Y-m-d')] = $dia->total;
        }

        $ahora = new \DateTime();

        $todosLosEventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0,0,0);
        $fin = (new \DateTime($fecha_fin_str))->setTime(0,0,0);
        
        while ($fecha <= $fin) {
            if ($fecha->format('N') < 6) { // Saltar fines de semana
                
                $inicioJornada = (clone $fecha)->setTime(9, 0, 0);
                $finJornada    = (clone $fecha)->setTime(15, 0, 0);
        
                $fecha_str = $fecha->format('Y-m-d');
                $conteoDiario = $pagosPorDiaMap[$fecha_str] ?? 0; 
                $diaEstaLleno = ($conteoDiario > 16); 

                $slot = clone $inicioJornada;
                while ($slot < $finJornada) {
                    $slotStart = $slot->format('Y-m-d\TH:i:s');

                    //$ocupado = isset($ocupadosMap[$slotStart]);
                    //CAMBIO CLAVE 1: Obtener el conteo de ocupación para el slot**
                    // Usamos el operador de coalescencia nula (??) para que sea 0 si no existe.
                    $conteoOcupados = $ocupadosMap[$slotStart] ?? 0; 
                    
                    // **CAMBIO CLAVE 2: Definir la condición de ocupación**
                    // El slot está OCUPADO solo si el conteo actual es >= 2.
                    $ocupado = ($conteoOcupados >= 1);

                    $esInhabil = false;
                    foreach($inhabiles as $dia){
                        $fechaInhabilInicio = $dia->fecha_inicio . 'T' . $dia->horario_inicio;
                        $fechaInhabilFinal = $dia->fecha_final . 'T' . $dia->horario_final;
                        if($slotStart >= $fechaInhabilInicio && $slotStart <= $fechaInhabilFinal){
                            $esInhabil = true;
                            break;
                        }
                    }

                    $estado = '';
                    if ($diaEstaLleno) { 
                        $estado = 'ocupado';
                    } elseif ($ocupado) { // Usa la nueva variable $ocupado
                        $estado = 'ocupado';
                    } elseif ($esInhabil) {
                        $estado = 'inhabil';
                    } elseif ($ahora > $slot) {
                        $estado = 'expirado';
                    } else {
                        $estado = 'disponible';
                    }

                    switch ($estado) {
                        case 'ocupado':
                            $todosLosEventos[] = [
                                'title' => 'Ocupado', 'start' => $slotStart,
                                'color' => '#DA0909', 'extendedProps' => ['estado' => 'ocupado']
                            ];
                            break;
                        case 'inhabil':
                            $todosLosEventos[] = [
                                'title' => 'Inhábil', 'start' => $slotStart,
                                'color' => '#3B78DB', 'extendedProps' => ['estado' => 'inhabil']
                            ];
                            break;
                        case 'expirado':
                            $todosLosEventos[] = [
                                'title' => 'Expirado', 'start' => $slotStart,
                                'color' => '#F59727', 'extendedProps' => ['estado' => 'expirado']
                            ];
                            break;
                        case 'disponible':
                        default:
                            $todosLosEventos[] = [
                                'title' => 'Disponible', 'start' => $slotStart,
                                'color' => '#00CE1C', 'extendedProps' => ['estado' => 'disponible']
                            ];
                            break;
                    }

                    $slot->modify('+30 minutes');
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
    }

    public function obtenerCumplimientosFiltrado(Request $request)
    {
        $fecha_inicio_str = $request->input('start', now()->format('Y-m-d'));
        $fecha_fin_str = $request->input('end', now()->addDays(800)->format('Y-m-d'));

        $fecha_inicio_dt = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fecha_fin_dt = (new \DateTime($fecha_fin_str))->setTime(23, 59, 59);

        $sede = $request->input('sede');
        $conciliador_id = $request->input('conciliador_id');

        $centrosConciliador = [$sede];
        if (in_array($sede, ['Morelia', 'Zitácuaro', 'Zitácuaro'], true)) {
            $centrosConciliador = ['Morelia', 'Zitácuaro', 'Zitácuaro'];
        } elseif (in_array($sede, ['Uruapan', 'Lázaro Cárdenas'], true)) {
            $centrosConciliador = ['Uruapan', 'Lázaro Cárdenas'];
        } elseif (in_array($sede, ['Zamora', 'Sahuayo'], true)) {
            $centrosConciliador = ['Zamora', 'Sahuayo'];
        }

        $inhabiles = DiasInhabiles::where(function ($q) use ($sede, $centrosConciliador, $conciliador_id) {
                $q->where(function ($q2) use ($sede) {
                    $q2->where('centro', $sede)
                        ->whereNull('user_id');
                });

                if (!empty($conciliador_id)) {
                    $q->orWhere(function ($q3) use ($centrosConciliador, $conciliador_id) {
                        $q3->whereIn('centro', $centrosConciliador)
                            ->where('user_id', $conciliador_id);
                    });
                }
            })
            ->whereIn('descripcion', ['Inhabil', 'No inhabil'])
            ->whereIn('tipo', ['Cumplimientos', 'Todos'])
            ->where(function($query) use ($fecha_inicio_dt, $fecha_fin_dt) {
                $query->where('fecha_inicio', '<=', $fecha_fin_dt)
                    ->where('fecha_final', '>=', $fecha_inicio_dt);
            })
            ->get();

        $ocupados = Pagos::whereBetween('fecha', [$fecha_inicio_dt, $fecha_fin_dt])
            ->where('delegacion', $sede)
            ->get();

        //Capacidad por horario: 2 pagos por slot (cada 30 min)
        $ocupadosMap = [];
        foreach ($ocupados as $cumplimiento) {
            //Normalizar a 30 minutos (00 o 30)
            $horaDt = $cumplimiento->hora instanceof \DateTimeInterface
                ? \DateTime::createFromInterface($cumplimiento->hora)
                : new \DateTime((string) $cumplimiento->hora);

            $horaNormMin = ((int) $horaDt->format('i') >= 30) ? 30 : 0;
            $horaDt->setTime((int) $horaDt->format('H'), $horaNormMin, 0);

            $slotKey = $cumplimiento->fecha->format('Y-m-d') . 'T' . $horaDt->format('H:i:s');
            $ocupadosMap[$slotKey] = ($ocupadosMap[$slotKey] ?? 0) + 1;
        }

        $pagosPorDia = Pagos::where('tipo_pago', 'Audiencia')
            ->where('delegacion', $sede)
            ->whereBetween('fecha', [$fecha_inicio_dt, $fecha_fin_dt])
            ->select('fecha', DB::raw('COUNT(*) as total'))
            ->groupBy('fecha')
            ->get();

        $pagosPorDiaMap = [];
        foreach ($pagosPorDia as $dia) {
            $pagosPorDiaMap[$dia->fecha->format('Y-m-d')] = $dia->total;
        }

        $ahora = new \DateTime();
        $todosLosEventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0,0,0);
        $fin = (new \DateTime($fecha_fin_str))->setTime(0,0,0);

        while ($fecha <= $fin) {
            if ($fecha->format('N') < 6) {
                $inicioJornada = (clone $fecha)->setTime(9, 0, 0);
                $finJornada    = (clone $fecha)->setTime(15, 0, 0);

                $fecha_str = $fecha->format('Y-m-d');
                $conteoDiario = $pagosPorDiaMap[$fecha_str] ?? 0;
                $diaEstaLleno = ($conteoDiario > 16);

                $slot = clone $inicioJornada;
                while ($slot < $finJornada) {
                    $slotStart = $slot->format('Y-m-d\\TH:i:s');

                    $conteoOcupados = $ocupadosMap[$slotStart] ?? 0;
                    $ocupado = ($conteoOcupados >= 2);

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

                    if ($diaEstaLleno) {
                        $estado = 'ocupado';
                    } elseif ($ocupado) {
                        $estado = 'ocupado';
                    } elseif ($esInhabil) {
                        $estado = 'inhabil';
                    } elseif ($esNoInhabil || $ahora > $slot) {
                        $estado = 'expirado';
                    } else {
                        $estado = 'disponible';
                    }

                    switch ($estado) {
                        case 'ocupado':
                            $todosLosEventos[] = [
                                'title' => 'Ocupado', 'start' => $slotStart,
                                'color' => '#DA0909', 'extendedProps' => ['estado' => 'ocupado', 'cupos' => $conteoOcupados]
                            ];
                            break;
                        case 'inhabil':
                            $todosLosEventos[] = [
                                'title' => 'Inhábil', 'start' => $slotStart,
                                'color' => '#3B78DB', 'extendedProps' => ['estado' => 'inhabil']
                            ];
                            break;
                        case 'expirado':
                            $titulo = $esNoInhabil ? 'No disponible' : 'Expirado';
                            $todosLosEventos[] = [
                                'title' => $titulo, 'start' => $slotStart,
                                'color' => '#F59727', 'extendedProps' => ['estado' => 'expirado']
                            ];
                            break;
                        case 'disponible':
                        default:
                            $titulo = 'Disponible';
                            if ($conteoOcupados === 1) {
                                $titulo = 'Cumplimiento(1)';
                            }
                            $todosLosEventos[] = [
                                'title' => $titulo, 'start' => $slotStart,
                                'color' => '#00CE1C', 'extendedProps' => ['estado' => 'disponible', 'cupos' => $conteoOcupados]
                            ];
                            break;
                    }

                    //Muestra los horarios para agendar cumplimientos en bloques de 30 minutos
                    $slot->modify('+30 minutes');
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
    }

    //Calcula la fecha mínima a partir de la cual se puede reagendar,
    private function calcularFechaMinimaHabil(string $sede, int $diasHabiles = 16): \DateTime
    {
        $fecha = (new \DateTime())->setTime(0, 0, 0); 
        $contador = 0;
        $rangosInhabiles = DiasInhabiles::where('centro', $sede)->get(['fecha_inicio', 'fecha_final']);

        while ($contador < $diasHabiles) {
            $fecha->modify('+1 day');

            if ((int)$fecha->format('N') >= 6) {
                continue;
            }

            $fechaStr = $fecha->format('Y-m-d');
            $esInhabil = false;
            foreach ($rangosInhabiles as $r) {
                if ($r->fecha_inicio <= $fechaStr && $r->fecha_final >= $fechaStr) {
                    $esInhabil = true; break;
                }
            }

            if ($esInhabil) {
                continue;
            }

            $contador++;
        }

        return $fecha;
    }

    public function obtenerAudienciasParte3(Request $request)
    {
        $request->validate([
            'sede' => 'required|string',
            'conciliador' => 'required|integer',
        ]);

        $fecha_inicio_str = $request->input('start', now()->format('Y-m-d'));
        $fecha_fin_str = $request->input('end', now()->addDays(370)->format('Y-m-d'));
        
        $fecha_inicio = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fecha_fin = (new \DateTime($fecha_fin_str))->setTime(23, 59, 59);

    $sede = $request->input('sede');
    $id_conciliador = (int) $request->input('conciliador');
        $tipoConciliador = PermisosConciliador::where('id_conciliador', $id_conciliador)->value('tipo');

        $soloSedePrincipal = $request->boolean('solo_sede_principal', false);

        // Calcular fecha mínima para reagendar: permitir desde el siguiente día natural
        $fechaMinima = (new \DateTime())->setTime(0,0,0)->modify('+1 day');
        $minDateStr = $fechaMinima->format('Y-m-d');

        if ($soloSedePrincipal) {
            // Solo inhábiles generales de la sede principal (sin subsedes y sin user_id del conciliador)
            $inhabiles = DiasInhabiles::where('centro', $sede)
                ->whereNull('user_id')
                ->whereIn('descripcion', ['Inhabil', 'No inhabil'])
                ->whereIn('tipo', ['Audiencias', 'Todos'])
                ->where(function ($query) use ($fecha_inicio, $fecha_fin) {
                    $query->where('fecha_inicio', '<=', $fecha_fin)
                        ->where('fecha_final', '>=', $fecha_inicio);
                })
                ->get();
        } else {
            $centrosNull = [$sede];
            if ($sede === 'Zitácuaro' || $sede === 'Zitácuaro') {
                // Para generales acepto ambas variantes si existe mezcla en BD.
                $centrosNull = ['Zitácuaro', 'Zitácuaro'];
            }

            $centrosConciliador = [$sede];
            if ($tipoConciliador === 'Ambos') {
                if (in_array($sede, ['Morelia', 'Zitácuaro', 'Zitácuaro'], true)) {
                    $centrosConciliador = ['Morelia', 'Zitácuaro', 'Zitácuaro'];
                } elseif (in_array($sede, ['Uruapan', 'Lázaro Cárdenas'], true)) {
                    $centrosConciliador = ['Uruapan', 'Lázaro Cárdenas'];
                } elseif (in_array($sede, ['Zamora', 'Sahuayo'], true)) {
                    $centrosConciliador = ['Zamora', 'Sahuayo'];
                }
            }

            $inhabiles = DiasInhabiles::where(function ($q) use ($centrosNull, $centrosConciliador, $id_conciliador) {
                    $q->where(function ($q2) use ($centrosNull) {
                        $q2->whereIn('centro', $centrosNull)
                            ->whereNull('user_id');
                    });

                    $q->orWhere(function ($q3) use ($centrosConciliador, $id_conciliador) {
                        $q3->whereIn('centro', $centrosConciliador)
                            ->where('user_id', $id_conciliador);
                    });
                })
                ->whereIn('descripcion', ['Inhabil', 'No inhabil'])
                ->whereIn('tipo', ['Audiencias', 'Todos'])
                ->where(function ($query) use ($fecha_inicio, $fecha_fin) {
                    $query->where('fecha_inicio', '<=', $fecha_fin)
                        ->where('fecha_final', '>=', $fecha_inicio);
                })
                ->get();
        }

        $audienciasPorSlot = Audiencias::whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->where('id_conciliador', $id_conciliador)
            ->selectRaw("CONCAT(DATE(fecha), 'T', TIME(hora)) as slot_key, COUNT(*) as total")
            ->groupBy('slot_key')
            ->pluck('total', 'slot_key')
            ->toArray();

        $ahora = new \DateTime();

        $todosLosEventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0,0,0);
        $fin_loop = (new \DateTime($fecha_fin_str))->setTime(0,0,0);

        while ($fecha <= $fin_loop) {
            if ($fecha->format('N') < 6) { // Saltar fines de semana
                
                $inicioJornada = (clone $fecha)->setTime(9, 0, 0);
                $finJornada    = (clone $fecha)->setTime(15, 15, 0);
                

                $slot = clone $inicioJornada;
                while ($slot < $finJornada) {
                    $slotStart = $slot->format('Y-m-d\TH:i:s');

                    $audienciasEnSlot = (int)($audienciasPorSlot[$slotStart] ?? 0);
                    $ocupado = $audienciasEnSlot >= 2;
                    
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

                    // Bloquear slots anteriores a la fecha mínima (aunque estén en el futuro)
                    if ($slot->format('Y-m-d') < $minDateStr) {
                        $estado = 'expirado';
                    } elseif ($ocupado) {
                        $estado = 'ocupado';
                    } elseif ($esInhabil) {
                        $estado = 'inhabil';
                    } elseif ($esNoInhabil) {
                        $estado = 'expirado';
                    } elseif ($ahora > $slot) {
                        $estado = 'expirado';
                    } else {
                        $estado = 'disponible';
                    }

                    $colores = [
                        'ocupado' => '#DA0909', 'inhabil' => '#3B78DB',
                        'expirado' => '#F59727', 'disponible' => '#00CE1C'
                    ];
                    $titulos = [
                        'ocupado' => 'Ocupado', 'inhabil' => 'Inhábil',
                        'expirado' => 'No disponible', 'disponible' => 'Disponible'
                    ];

                    $titulo = $titulos[$estado];
                    if ($estado === 'disponible' && $audienciasEnSlot === 1) {
                        $titulo = 'Audiencia (1)';
                    }

                    $todosLosEventos[] = [
                        'title' => $titulo,
                        'start' => $slotStart,
                        'color' => $colores[$estado],
                        'extendedProps' => [
                            'estado' => $estado,
                            'audiencias_en_slot' => $audienciasEnSlot,
                        ]
                    ];

                    $slot->modify('+75 minutes');
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
    }

    public function obtenerAudiencias(Request $request)
    {
        
        $fecha_inicio_str = $request->input('start', now()->format('Y-m-d'));
        $fecha_fin_str = $request->input('end', now()->addDays(300)->format('Y-m-d'));
        
        $fecha_inicio = (new \DateTime($fecha_inicio_str))->setTime(0, 0, 0);
        $fecha_fin = (new \DateTime($fecha_fin_str))->setTime(23, 59, 59);

        $sede = $request->input('sede'); 

        $id_conciliador = $request->input('conciliador') ?? auth()->id();
        $tipoConciliador = PermisosConciliador::where('id_conciliador', $id_conciliador)->value('tipo');

        // Calcular fecha mínima para reagendar: permitir desde el siguiente día natural
        $fechaMinima = (new \DateTime())->setTime(0,0,0)->modify('+1 day');
        $minDateStr = $fechaMinima->format('Y-m-d');

        $centros = [$sede];

        if ($sede === 'Zitácuaro' || $sede =='Zitácuaro') {
            $centros = ['Zitácuaro', 'Zitácuaro'];
        }

        if ($tipoConciliador == 'Ambos'){
            if ($sede === 'Morelia' || $sede === 'Zitácuaro' || $sede === 'Zitácuaro') {
                $centros = ['Morelia', 'Zitácuaro', 'Zitácuaro'];
            } elseif ($sede === 'Uruapan' || $sede === 'Lázaro Cárdenas') {
                $centros = ['Uruapan', 'Lázaro Cárdenas'];
            } elseif ($sede === 'Zamora' || $sede === 'Sahuayo') {
                $centros = ['Zamora', 'Sahuayo'];
            }
        }

        $inhabiles = DiasInhabiles::whereIn('centro', $centros)
            ->where(function($query) use ($id_conciliador) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $id_conciliador);
            })
            ->where(function($query) use ($fecha_inicio, $fecha_fin) {
                $query->where('fecha_inicio', '<=', $fecha_fin)
                    ->where('fecha_final', '>=', $fecha_inicio);
            })
            ->get();

        $ocupados = Audiencias::whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->where('id_conciliador', $id_conciliador)
            ->get();
            
        $ocupadosMap = [];
        foreach ($ocupados as $cumplimiento) {
            $slotKey = $cumplimiento->fecha->format('Y-m-d') . 'T' . $cumplimiento->hora->format('H:i:s');
            $ocupadosMap[$slotKey] = true;
        }

        $ahora = new \DateTime();

        $todosLosEventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0,0,0);
        $fin_loop = (new \DateTime($fecha_fin_str))->setTime(0,0,0);

        while ($fecha <= $fin_loop) {
            if ($fecha->format('N') < 6) { // Saltar fines de semana
                
                $inicioJornada = (clone $fecha)->setTime(9, 0, 0);
                $finJornada    = (clone $fecha)->setTime(15, 15, 0);
                

                $slot = clone $inicioJornada;
                while ($slot < $finJornada) {
                    $slotStart = $slot->format('Y-m-d\TH:i:s');
                    
                    $ocupado = isset($ocupadosMap[$slotStart]);
                    
                    $esInhabil = false;
                    foreach($inhabiles as $dia){
                        $fechaInhabilInicio = $dia->fecha_inicio . 'T' . $dia->horario_inicio;
                        $fechaInhabilFinal = $dia->fecha_final . 'T' . $dia->horario_final;
                        if($slotStart >= $fechaInhabilInicio && $slotStart <= $fechaInhabilFinal){
                            $esInhabil = true;
                            break;
                        }
                    }

                    // Bloquear slots anteriores a la fecha mínima (aunque estén en el futuro)
                    if ($slot->format('Y-m-d') < $minDateStr) {
                        $estado = 'expirado';
                    } elseif ($ocupado) {
                        $estado = 'ocupado';
                    } elseif ($esInhabil) {
                        $estado = 'inhabil';
                    } elseif ($ahora > $slot) {
                        $estado = 'expirado';
                    } else {
                        $estado = 'disponible';
                    }

                    $colores = [
                        'ocupado' => '#DA0909', 'inhabil' => '#3B78DB',
                        'expirado' => '#F59727', 'disponible' => '#00CE1C'
                    ];
                    $titulos = [
                        'ocupado' => 'Ocupado', 'inhabil' => 'Inhábil',
                        'expirado' => 'No disponible', 'disponible' => 'Disponible'
                    ];

                    $todosLosEventos[] = [
                        'title' => $titulos[$estado],
                        'start' => $slotStart,
                        'color' => $colores[$estado],
                        'extendedProps' => ['estado' => $estado]
                    ];

                    $slot->modify('+75 minutes');
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
    }

    public function audienciasPorSolicitud(Request $request, $id_solicitud)
    {
        // Seguridad: solo números
        $id_solicitud = (int) $id_solicitud;

        $audiencias = Audiencias::where('id_solicitud', $id_solicitud)
            ->orderBy('id', 'asc')
            ->get(['id', 'fecha', 'hora', 'estatus']);

        $payload = $audiencias->map(function ($a) {
            return [
                'id' => $a->id,
                'fecha' => optional($a->fecha)->format('Y-m-d'),
                'hora' => optional($a->hora)->format('H:i:s'),
                'estatus' => $a->estatus,
            ];
        });

        return response()->json($payload);
    }

    public function diasInhabilesCentro(Request $request)
    {
        $centro = $request->query('centro');
        if (!$centro) {
            return response()->json([], 200);
        }

        $rangos = DiasInhabiles::where('centro', $centro)
            ->get(['fecha_inicio', 'fecha_final', 'horario_inicio', 'horario_final', 'user_id', 'centro']);

        return response()->json($rangos);
    }

    public function cumplimiento_incomparecencia(Request $request, $id){
        $user_id = auth()->user()->id;
        $request->validate([
            'fecha_audiencia' => 'required|date',
            'hora_audiencia'  => 'required',
        ]);
        $pago = Pagos::find($id);
        $pago->update([
            'estatus'         => "Incomparecencia trabajador",
            'fecha_audiencia' => $request->fecha_audiencia,
            'hora_audiencia'  => $request->hora_audiencia,
            'fecha_conclucion' => \Carbon\Carbon::now()->format('Y-m-d')
        ]);
    
        $id_solicitud = $pago->id_solicitud;
        Pagos::find($id_solicitud)?->update(['estatus' => "Incomparecencia trabajador", 'user_id' => $user_id]);
        
        return redirect()->route('agenda');  
        /*Así estab antes de los cambios en los cumplimientos*/ 
        /*Pagos::find($id)->update(['estatus'  => "Incomparecencia trabajador"]);

        return redirect()->route('agenda');*/
    }

    public function PDFIncomparecenciaCumplimiento($id){
        $pagos = Pagos::find($id);

        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $solicitud->trabajador = $solicitud->nombre_trabajador;
            $solicitud->empresa = $solicitud->empresa_representante;
            //$salario_diario = 0;
            $representante = ['empresa_representante' => $pagos->empresa_representante ];
            
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $id)
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
            $delegado = User::where('delegacion', $delegacion)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();
            $html = view('PDF/cumplimientos/incomparecenciaTrabajador', compact('id', 'solicitud','conciliador',/*'salario_diario',*/'pagos','delegado','representante'))->render();
        }
        else{
            $pagos = Pagos::find($id);
            $solicitante = SeerSolicitante::where('id_solicitud',$pagos->id_solicitud)->first();
            $solicitud = SeerPerGeneral::where('id', $pagos->id_solicitud)->first();
            $antefirma = $this->antefirmaDesdePagoSolicitud($pagos->user_id ?? null, $solicitud->delegacion ?? null);
            $inicialesConcluye = $antefirma['inicialesConcluye'];
            $etiquetaIniciales = $antefirma['etiquetaIniciales'];
            $delegacion = $solicitud->delegacion;
            $delegado = User::where('delegacion', $delegacion)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first(); 
            //$salario_diario = $this->calcularSalarioDiario($solicitud->pago, $solicitud->periodo_pago);

            $citados = SeerCitados::where('id_solicitud', $solicitud->id)->where('aparece_convenio', 1)->get();

            $historialIds = $citados->pluck('id_historial')->filter()->unique()->values();
            $abogadoIds   = $citados->whereNull('id_historial')->pluck('id_abogado')->filter()->unique()->values();

            $representantesHistorial = $historialIds->isNotEmpty()
                ? HistorialAbogado::whereIn('id', $historialIds)->get()
                : collect();

            $representantesPoder = $abogadoIds->isNotEmpty()
                ? Poder::whereIn('idAbogado', $abogadoIds)->get()
                : collect();

            // Colección final con ambos tipos (puedes iterar en la vista)
            $representantes = $representantesHistorial
                ->concat($representantesPoder)
                ->unique(function ($rep) {
                    // Evita duplicados entre tablas: HistorialAbogado usa id, Poder usa idAbogado
                    return $rep instanceof \App\Models\HistorialAbogado ? 'H:' . $rep->id : 'P:' . $rep->idAbogado;
                })
                ->values();

            $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name','audiencias.fecha','audiencias.hora')
            ->first();

            $complimientos = Pagos::find($id);

            $html = view('PDF/Cumplimientos/incomparecenciaTrabajadorAudiencia', compact('id','solicitud','conciliador','complimientos','pagos','delegado','citados','representantes', 'solicitante', 'inicialesConcluye', 'etiquetaIniciales'))->render();
        }
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incomparecencia_'  .'.pdf';
        return $pdf->stream($nombreArchivo);      
    }

    public function PDFIncomparecenciaCumplimientoRati($id){
        $pagos = Pagos::find($id);

        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            //$salario_diario = 0;
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $id)
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
            $delegado = User::where('delegacion', $delegacion)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first();
            $html = view('PDF/cumplimientos/incomparecenciaTrabajador', compact('id', 'solicitud','conciliador',/*'salario_diario',*/'pagos','delegado'))->render();
        }
        else{
            $pagos = Pagos::find($id);
            //$salario_diario = $this->calcularSalarioDiario($solicitud->pago, $solicitud->periodo_pago);
            $solicitud = Turnos::where('id', $pagos->id_solicitud)->first();

            if($solicitud->id_historial){
                $representante = HistorialAbogado::where('id', $solicitud->id_historial)->first();
            } else {
                $representante = Poder::where('idAbogado', $solicitud->idAbogado)->first();
            }

            $conciliador  = User::join("turnos","turnos.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("turnos.id", "=", $pagos->id_solicitud)
            ->select('users.name')
            ->first();

            $delegacion = $solicitud->delegacion;
            $delegado = User::where('delegacion', $delegacion)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Delegado');
            })
            ->select('users.id', 'users.name', 'users.delegacion')
            ->first(); 
            $html = view('PDF/Cumplimientos/incomparecenciaTrabajador', compact('id','solicitud','conciliador',/*'salario_diario',*/'pagos','delegado', 'representante'))->render();
        }
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_incomparecencia_'  .'.pdf';
        return $pdf->stream($nombreArchivo);      
    }

    public function reporte_diario(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_actual = date('y-m-d');

        $usuario = $user["name"];
        //SOLICITUDES
        $solicitudes  = SeerPerGeneral_old::join("seer_auxiliares","seer_auxiliares.id_solicitud","=","seer_general_old.id");
        $solicitudes = $solicitudes->where("fecha","=",$fecha_actual);
        $solicitudes = $solicitudes->where("seer_general_old.user_id", $id);
        $solicitudes = $solicitudes->where("seer_auxiliares.tipo_solicitud", "Solicitud")
        ->select('seer_general_old.fecha','seer_general_old.solicitante','seer_auxiliares.motivo','seer_auxiliares.actividad_economica','seer_auxiliares.notificacion')
        ->get();

        //RATIFICACIONES
        $ratificaciones  = SeerPerGeneral_old::join("seer_auxiliares","seer_auxiliares.id_solicitud","=","seer_general_old.id");
        $ratificaciones  = $ratificaciones->where("fecha","=",$fecha_actual);
        $ratificaciones  = $ratificaciones->where("seer_general_old.user_id", $id);
        $ratificaciones  = $ratificaciones->where("seer_auxiliares.tipo_solicitud", "Ratificación");
        $ratificaciones   = $ratificaciones->select('seer_general_old.fecha','seer_general_old.solicitante','seer_auxiliares.motivo','seer_auxiliares.actividad_economica',
        'seer_auxiliares.monto','seer_auxiliares.notificacion')
        ->get();

        //CONVENIOS
        $convenios = SeerConvenios::join("seer_general_old","seer_convenios.NUE","=","seer_general_old.NUE");
        $convenios = $convenios->where("seer_convenios.fecha","=",$fecha_actual);
        $convenios = $convenios->where("seer_convenios.user_id", $id);
        $convenios = $convenios->select('seer_convenios.fecha','seer_convenios.NUE','seer_convenios.tipo_pago','seer_convenios.monto')
        ->get();

        //ASESORIAS
        $asesorias = SeerAsesoria::where("fecha","=",$fecha_actual);
        $asesorias = $asesorias->where("seer_asesorias.id_usuario", $id);
        $asesorias = $asesorias->select('seer_asesorias.nombre', 'seer_asesorias.sexo')
        ->get();

        $pdf = \PDF::loadView('PDF/reporte-diario', compact('solicitudes','ratificaciones','convenios','asesorias','usuario'));
            
        return $pdf->stream('archivo.pdf');
    }

    public function misestadisticas(){
        return view('estadisticas.mis_estadisticas');
    }

    public function estadisticasPDF(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $fecha_inicial = $data["fecha_inicial"];
        $fecha_final = $data["fecha_final"];
        $sede = $user->delegacion;
       
        if($userRole[0] == "Auxiliar"){
            $Ratificacion = Turnos::whereBetween('fecha',[$fecha_inicial,$fecha_final])
            ->join('users','users.id','turnos.id_conciliador')
            ->join('users as user_usuario','user_usuario.id','turnos.user_id')
            ->where('turnos.delegacion',$user["delegacion"])
            ->where('turnos.user_id',$user["id"])
            ->select('turnos.*','users.name','user_usuario.name as auxiliar')
            ->get();
            
            $pdf = \PDF::loadView('PDF/Estadisticas/Ratificaciones',compact('fecha_inicial','fecha_final','Ratificacion'));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->stream('archivo.pdf');
        }
        else if($userRole[0] == "Cumplimientos"){
            $pagosAudiencias = Pagos::whereBetween('pago_solicitud.fecha',[$fecha_inicial,$fecha_final])
            //->leftjoin('seer_general','seer_general.id','pago_solicitud.id_solicitud')
            ->leftjoin('users','users.id','pago_solicitud.id_conciliador')
            ->where('pago_solicitud.tipo_pago',"Audiencia")
            ->where('pago_solicitud.delegacion',$sede)
            ->select('pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.nombre_trabajador','pago_solicitud.empresa_representante','pago_solicitud.descripcion','pago_solicitud.monto'
            ,'users.name','pago_solicitud.estatus','pago_solicitud.NUE')
            //->selectRaw('count(pago_solicitud.id) as audiencias')
            ->get();
            
            $pdf = \PDF::loadView('PDF/Estadisticas/reporte-miscumplimientos', compact('fecha_inicial','fecha_final','pagosAudiencias'));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->stream('archivo.pdf');
        }
    }

    public function exportarExcel(){
        return Excel::download(new CitasExport, 'pagos.xlsx');
    }
    
    public function todas_audiencias(Request $request) {
        $user = auth()->user();
        $userRole = $user->roles->first()->name ?? null; 
        $isAudiencia = 'Si';

        // 1. Query base optimizado con Eager Loading selectivo
        $query = Audiencias::with([
            'solicitante:id_solicitud,nombre', 
            'expediente:id,NUE,estatus,incidencia', 
            'conciliador:id,name', 
            'pagos' => function($q) {
                $q->select('id', 'id_solicitud', 'estatus', 'tipo_pago')
                ->where('estatus', 'Pendiente')
                ->where('tipo_pago', 'Audiencia');
            }
        ])
        ->whereHas('expediente', function ($q) {
            $q->where(function($sub) {
                $sub->whereNull('incidencia')->orWhere('incidencia', 0);
            });
        })
        ->select('id', 'id_solicitud', 'fecha', 'hora', 'id_conciliador', 'estatus', 'delegacion', 'created_at');

        // Filtro de búsqueda global en el Servidor
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            
            $query->where(function($q) use ($buscar) {
                // Buscar por coincidencia en el NUE del expediente
                $q->whereHas('expediente', function($sub) use ($buscar) {
                    $sub->where('NUE', 'LIKE', "%{$buscar}%");
                })
                // O buscar por coincidencia en el nombre del solicitante
                ->orWhereHas('solicitante', function($sub) use ($buscar) {
                    $sub->where('nombre', 'LIKE', "%{$buscar}%");
                });
            });
        }

        // 2. Mapeo de delegaciones regionales (Gobierno de Michoacán)
        $mapaDelegaciones = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Sahuayo', 'Zamora'],
        ];

        // 3. Filtros de Rol de Usuario (Incluyendo Auxiliar)
        if ($userRole === "Conciliador") {
            $query->where('id_conciliador', $user->id);
            $permisos = PermisosConciliador::where('id_conciliador', $user->id)->select('tipo')->first();
            
            if ($permisos && $permisos->tipo === "Ambos") {
                $delegaciones = $mapaDelegaciones[$user->delegacion] ?? [$user->delegacion];
                $query->whereIn('delegacion', $delegaciones);
            } else {
                $query->where('delegacion', $user->delegacion);
            }
        } 
        elseif ($userRole === "Delegado") {
            $delegaciones = $mapaDelegaciones[$user->delegacion] ?? [$user->delegacion];
            $query->whereIn('delegacion', $delegaciones);
        }
        // NUEVO: Filtro restrictivo para personal Auxiliar
        elseif ($userRole === "Auxiliar") {
            $query->where('delegacion', $user->delegacion);
        }

        // 4. Paginación e inyección del parámetro de búsqueda
        $audiencias = $query->orderBy('created_at', 'desc')
                            ->paginate(500)
                            ->appends(['buscar' => $request->input('buscar')]);
        
        $audiencias->through(function ($audiencia) {
            $audiencia->estatus_modelo = $audiencia->estatus;
            $audiencia->nombre = $audiencia->solicitante->nombre ?? 'Sin solicitante';
            $audiencia->NUE = $audiencia->expediente->NUE ?? 'Sin Expediente';
            $audiencia->estatus = $audiencia->expediente->estatus ?? 'Sin estatus';
            $audiencia->conciliador_nombre = $audiencia->conciliador->name ?? 'Sin Conciliador';
            $audiencia->constancia = $audiencia->pagos->count() > 0 ? 1 : 0;
            return $audiencia;
        });
        
        return view('audiencias.todas_audiencias', compact('audiencias', 'isAudiencia'));
    }

    public function todos_complimientos(Request $request) {
        $user = auth()->user();
        $userRole = $user->roles->first()?->name; 
        $delegacionUsuario = $user->delegacion;

        // 1. Mapeo de delegaciones regionales (Gobierno de Michoacán)
        $mapaDelegaciones = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];

        // 2. Construcción del Query Base para Ratificaciones (Tabla: turnos | Siglas: RAT)
        $queryRatificacion = Pagos::where("pago_solicitud.tipo_pago", "Ratificacion")
            ->join("turnos", "turnos.id", "pago_solicitud.id_solicitud")
            ->select(
                "pago_solicitud.id", "pago_solicitud.fecha", "pago_solicitud.hora", 
                "pago_solicitud.monto", "pago_solicitud.descripcion", "pago_solicitud.observaciones", 
                "pago_solicitud.estatus", "turnos.NUE", "turnos.id as id_solicitud",
                DB::raw('CONCAT(turnos.nombre_empresa, " ", turnos.primero_empresa, " ", turnos.segundo_empresa) AS empresa'),
                DB::raw('CONCAT(turnos.trabajador, " ", turnos.primero_trabajador, " ", turnos.segundo_trabajador) AS trabajador')
            );

        // 3. Construcción del Query Base para Audiencias (Tabla: seer_general / seer_solicitante | Siglas: SOL)
        $queryAudiencia = Pagos::where("pago_solicitud.tipo_pago", "Audiencia")
            ->join("seer_general", "seer_general.id", "pago_solicitud.id_solicitud")
            ->join("seer_solicitante", "seer_general.id", "seer_solicitante.id_solicitud")
            ->select(
                "pago_solicitud.id", "pago_solicitud.fecha", "pago_solicitud.hora", 
                "pago_solicitud.monto", "pago_solicitud.descripcion", "pago_solicitud.observaciones", 
                "pago_solicitud.estatus", "seer_general.NUE", "seer_general.id as id_solicitud",
                DB::raw('seer_solicitante.nombre AS trabajador')
            );

        // ========================================================
        // LOGICA AVANZADA: ENRUTAMIENTO INTELIGENTE DE BÚSQUEDA
        // ========================================================
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            
            // Convertimos a mayúsculas para evitar problemas de escritura (ej: mor/rat -> MOR/RAT)
            $buscarUpper = mb_strtoupper($buscar, 'UTF-8');

            if (str_contains($buscarUpper, '/RAT/')) {
                // Caso RAT: El registro vive estrictamente en turnos, vaciamos el query de audiencias para que vaya rápido
                $queryAudiencia->whereRaw('1 = 0'); 
                $queryRatificacion->where('turnos.NUE', 'LIKE', "%{$buscar}%");
                
            } elseif (str_contains($buscarUpper, '/SOL/')) {
                // Caso SOL: El registro vive estrictamente en seer_general, vaciamos ratificaciones
                $queryRatificacion->whereRaw('1 = 0');
                $queryAudiencia->where('seer_general.NUE', 'LIKE', "%{$buscar}%");
                
            } elseif (str_contains($buscarUpper, '/CI/')) {
                // Caso CI: Es un identificador directo de la tabla de pagos (pago_solicitud)
                // Como ambos tipos de pago (Audiencia/Ratificación) pueden tener folios de recibo internos, filtramos ambos por ID
                // Extraemos solo los números del string (ej: MOR/CI/2025/004166 -> 4166) o buscamos la coincidencia exacta si tienes una columna 'folio_ci'
                $queryRatificacion->where('pago_solicitud.NUE', 'LIKE', "%{$buscar}%");
                $queryAudiencia->where('pago_solicitud.NUE', 'LIKE', "%{$buscar}%");
                
            } else {
                // Búsqueda común por Nombre de trabajador o empresa (si no escribieron un NUE completo)
                $queryRatificacion->where(function($q) use ($buscar) {
                    $q->where('turnos.trabajador', 'LIKE', "%{$buscar}%")
                    ->orWhere('turnos.nombre_empresa', 'LIKE', "%{$buscar}%")
                    ->orWhere('turnos.NUE', 'LIKE', "%{$buscar}%");
                });

                $queryAudiencia->where(function($q) use ($buscar) {
                    $q->where('seer_solicitante.nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('seer_general.NUE', 'LIKE', "%{$buscar}%");
                });
            }
        }

        // 4. Aplicación de Filtros de Seguridad por Rol de Usuario
        if (in_array($userRole, ["Auxiliar", "Excepcion"])) {
            $queryRatificacion->where('turnos.delegacion', $delegacionUsuario);
            $queryAudiencia->where('seer_general.delegacion', $delegacionUsuario);
        } 
        elseif ($userRole === "Conciliador") {
            $permisos = PermisosConciliador::where('id_conciliador', $user->id)->select('tipo')->first();
            
            if ($permisos && $permisos->tipo === "Ambos" && isset($mapaDelegaciones[$delegacionUsuario])) {
                $sedes = $mapaDelegaciones[$delegacionUsuario];
                $queryRatificacion->whereIn('turnos.delegacion', $sedes);
                $queryAudiencia->whereIn('seer_general.delegacion', $sedes);
            } else {
                $queryRatificacion->where('turnos.delegacion', $delegacionUsuario);
                $queryAudiencia->where('seer_general.delegacion', $delegacionUsuario);
            }
        } 
        elseif (in_array($userRole, ["Delegado", "Enlace"])) {
            $sedes = $mapaDelegaciones[$delegacionUsuario] ?? [$delegacionUsuario];
            $queryRatificacion->whereIn('turnos.delegacion', $sedes);
            $queryAudiencia->whereIn('seer_general.delegacion', $sedes);
        }

        // Ordenamiento final por fecha de creación del trámite
        $queryRatificacion->orderBy('turnos.created_at', 'desc');
        $queryAudiencia->orderBy('seer_general.created_at', 'desc');

        // 5. Paginación asíncrona independiente preservando parámetros de filtrado
        $complimientos_ratificacion = $queryRatificacion->paginate(50, ['*'], 'pag_rat')
                                                        ->appends(['buscar' => $request->input('buscar')]);
                                                        
        $complimientos_audiencias   = $queryAudiencia->paginate(50, ['*'], 'pag_aud')
                                                    ->appends(['buscar' => $request->input('buscar')]);

        return view('cumplimientos/actuales', compact('complimientos_ratificacion', 'complimientos_audiencias'));
    } 

    public function todas_ratificaciones(Request $request) {
        $user = auth()->user();
        // Obtenemos el nombre del rol principal directamente
        $userRole = $user->roles->first()?->name; 

        // 1. Iniciamos la consulta base seleccionando columnas específicas si es posible
        $query = Turnos::where('tipo', 'Ratificación')
            ->where(function($q) {
                // Agrupamos esto para que el OR no interfiera con los otros WHERE
                $q->whereNull('incidencia')->orWhere('incidencia', 0);
            })
            ->withExists(['pagos as tiene_pendientes' => function($q) {
                $q->where('estatus', 'Pendiente')
                ->where('tipo_pago', 'Ratificacion');
            }]);

        // ========================================================
        // NUEVO: FILTRO DE BÚSQUEDA GLOBAL DESDE EL SERVIDOR
        // ========================================================
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            
            $query->where(function($q) use ($buscar) {
                // Buscar por coincidencia en el NUE del expediente de ratificación
                $q->where('NUE', 'LIKE', "%{$buscar}%")
                // O buscar por coincidencia en el nombre del solicitante (si está en la misma tabla o relación)
                // Si 'nombre' está en una relación (ej. solicitante), cámbialo por un orWhereHas
                ->orWhere('nombre_empresa', 'LIKE', "%{$buscar}%");
            });
        }

        // 2. Mapeo de delegaciones regionales (Gobierno de Michoacán)
        $mapaDelegaciones = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Sahuayo', 'Zamora'],
        ];

        // 3. Aplicamos filtros de seguridad según Rol de manera estricta y unificada
        if (in_array($userRole, ["Auxiliar", "Excepcion"])) {
            $query->where('delegacion', $user->delegacion);
        } 
        elseif ($userRole === "Conciliador") {
            // Optimizado trayendo solo la columna 'tipo'
            $permisos = PermisosConciliador::where('id_conciliador', $user->id)->select('tipo')->first();
            
            if ($permisos && $permisos->tipo === "Ambos" && isset($mapaDelegaciones[$user->delegacion])) {
                $query->whereIn('delegacion', $mapaDelegaciones[$user->delegacion]);
            } else {
                $query->where('delegacion', $user->delegacion);
            }
        } 
        elseif (in_array($userRole, ["Delegado", "Enlace"])) {
            $delegaciones = $mapaDelegaciones[$user->delegacion] ?? [$user->delegacion];
            $query->whereIn('delegacion', $delegaciones);
        }
        // "Super Usuario" y "Administrador" pasan sin filtros adicionales (ven todo)

        // 4. LA SOLUCIÓN CLAVE: Paginación fluida inyectando el término buscado
        $solicitudes = $query->orderBy('created_at', 'desc')
                            ->paginate(500)
                            ->appends(['buscar' => $request->input('buscar')]);

        // 5. Transformamos de manera eficiente ÚNICAMENTE los registros de la página actual
        $solicitudes->through(function ($audiencia) {
            $audiencia->constancia = $audiencia->tiene_pendientes ? 1 : 0;
            return $audiencia;
        });

        return view('ratificaciones.ratificaciones_todas', compact('solicitudes', 'userRole'));
    }

    public function todas_solicitudes(Request $request) {
        $user = auth()->user();
        $userRole = $user->roles->first()?->name; 
        $isAudiencia = 'No';

        // 1. Iniciamos el Query base optimizado con Eager Loading selectivo
        $query = SeerPerGeneral::with('solicitante:id,id_solicitud,nombre')
            ->where('estatus', '!=', 'Pendiente')
            ->where(function ($q) {
                $q->whereNull('incidencia')
                ->orWhere('incidencia', 0);
            })
            ->select('id', 'consecutivo','fecha_confirmacion as fecha', 'NUE', 'actividad', 'tipo_solicitud', 'estatus', 'delegacion', 'created_at');

        // ========================================================
        // NUEVO: FILTRO DE BÚSQUEDA GLOBAL DESDE EL SERVIDOR
        // ========================================================
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            
            $query->where(function($q) use ($buscar) {
                // Buscar por el campo NUE de la solicitud
                $q->where('NUE', 'LIKE', "%{$buscar}%")
                // O buscar por el nombre del solicitante en la tabla vinculada
                ->orWhereHas('solicitante', function($sub) use ($buscar) {
                    $sub->where('nombre', 'LIKE', "%{$buscar}%");
                });
            });
        }

        // 2. Definimos el mapa de delegaciones regionales (Gobierno de Michoacán)
        $mapaDelegaciones = [
            "Morelia" => ["Morelia", "Zitácuaro"],
            "Uruapan" => ["Uruapan", "Lázaro Cárdenas"],
            "Zamora"  => ["Zamora", "Sahuayo"],
            "Sahuayo" => ["Sahuayo", "Zamora"],
        ];

        // 3. Aplicamos los filtros de seguridad territorial según el Rol
        if (in_array($userRole, ["Auxiliar", "Excepcion"])) {
            $query->where('delegacion', $user->delegacion);
        } 
        elseif ($userRole === "Conciliador") {
            $permisos = PermisosConciliador::where('id_conciliador', $user->id)->select('tipo')->first();
            
            if ($permisos && $permisos->tipo === "Ambos" && isset($mapaDelegaciones[$user->delegacion])) {
                $query->whereIn('delegacion', $mapaDelegaciones[$user->delegacion]);
            } else {
                $query->where('delegacion', $user->delegacion);
            }
        } 
        elseif (in_array($userRole, ["Delegado", "Enlace"])) {
            if (isset($mapaDelegaciones[$user->delegacion])) {
                $query->whereIn('delegacion', $mapaDelegaciones[$user->delegacion]);
            } else {
                $query->where('delegacion', $user->delegacion);
            }
        }

        // 4. LA SOLUCIÓN CLAVE: Paginación fluida inyectando el término buscado
        // Se cambia de 2500 registros simultáneos a bloques manejables de 100
        $solicitudes = $query->orderBy('created_at', 'desc')
                            ->paginate(100)
                            ->appends(['buscar' => $request->input('buscar')]);

        // 5. Mapear de forma optimizada los citados únicamente para las filas de la página actual
        $idsSolicitudes = $solicitudes->pluck('id');
        
        $citadosSolicitud = DB::table('seer_citados')
            ->whereIn('id_solicitud', $idsSolicitudes)
            ->where('resulte_responsable', 'No')
            ->select('id_solicitud', 'nombre', 'primer_apellido', 'segundo_apellido')
            ->get()
            ->groupBy('id_solicitud');

        // 6. Transformación limpia usando through() (Preserva la estructura de paginación)
        $solicitudes->through(function ($solicitud) use ($citadosSolicitud) { 
            // Nombre del solicitante
            $solicitud->nombre = $solicitud->solicitante->nombre ?? 'Sin solicitante';
            
            // Listado consolidado de citados
            if (isset($citadosSolicitud[$solicitud->id])) {
                $solicitud->lista_citados = $citadosSolicitud[$solicitud->id]
                    ->map(function($citado) {
                        return trim("{$citado->nombre} {$citado->primer_apellido} {$citado->segundo_apellido}");
                    })
                    ->filter()
                    ->implode(', ');
            } else {
                $solicitud->lista_citados = 'Sin citados';
            }

            return $solicitud;
        });

        return view('solicitudes.solicitudes_todas', compact('solicitudes', 'isAudiencia', 'userRole'));
    }

    public function mostrar_citado(Request $request){
        $data = $request->all();
        $id = $data["id"];

        $municipios = Municipios::all();
        $estados = Estados::all();
        $folio = SeerCitados::find($id);
        return view('/notificaciones/ver_citado_historial',compact('folio','estados','municipios'));
    }


    public function editar_citados_historial(Request $request){
        $data = $request->all();
        
        $id_usuario = auth()->user()->id;
        $user = User::find($id_usuario);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $folio = SeerCitados::find($data["id"]);
        
        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $data["id"] . "-domicilio_Citado1.jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
            $foto1 = $imagen_domicilio1;
        } else {
            $foto1 = $folio->imagen_domicilio1;
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $data["id"] . "-domicilio_Citado2.jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
            $foto2 = $imagen_domicilio2;
        } else {
            $foto2 = $folio->imagen_domicilio2;
        }
        
        //$fecha_actualizar = Carbon::parse($data["fecha"] . ' ' . $data["hora"]);
        $fecha_actualizar =\Carbon\Carbon::parse($data["fecha"] . ' ' . $data["hora"]);
        DB::table('seer_citados')->where('id', $data["id"])
        ->update([
            //'tipo_persona'             => $data["tipo"],
            'curp'                     => $data["curp"] ?? null,
            'rfc'                      => $data["rfc"],
            'nombre'                   => $data["nombre"],
            'primer_apellido'          => $data["primer_apellido"] ?? null,
            'segundo_apellido'         => $data["segundo_apellido"] ?? null,
            'colonia'                  => $data["colonia"],
            'cp'                       => $data["cp"],
            'calle1'                   => $data["calle1"],
            'calle2'                   => $data["calle2"],
            'n_ext'                    => $data["exterior"],
            'n_int'                    => $data["interior"],
            'tipo_vialidad'            => $data["vialidad"],
            'calle'                    => $data["calle"],
            'municipio_citado'         => $data["municipio_citado"],
            'referencia'               => $data["referencia"],
            'imagen_domicilio1'        => $foto1,
            'imagen_domicilio2'        => $foto2,
            'estado_citado'            => $data["estado_citado"],
            'fecha'                    => $fecha_actualizar
            //'updated_at'               => $fecha_actualizar,
        ]);

        if($data["estatus"] == "Sin asignar"){
            $data_update = SeerCitados::find($data["id"])
            ->update(['estatus' => $data["estatus"], 'id_notificador' => 0]);
        }
        else{
            $data_update = SeerCitados::find($data["id"])
            ->update(['estatus' => $data["estatus"]]);
        }
        /*
        $fecha_inicio = $data["fecha_inicio"];
        $fecha_fin = $data["fecha_final"];
        $id = auth()->user()->id;
        //$user = User::find($id);
        //$roles = Role::pluck('name','name')->all();
        //$userRole = $user->roles->pluck('name')->all();
        //$fecha_actual = date('y-m-d');
        $personas = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Notificador');
        })
        ->where('delegacion', $user["delegacion"])
        ->get();

        $notificaciones = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->leftJoin('users', 'seer_citados.id_notificador', '=', 'users.id')
        ->select('seer_general.id as id_solicitud','seer_citados.id as id_citado','seer_general.NUE',
            'seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
            'seer_citados.colonia','seer_citados.calle','seer_citados.n_ext','seer_citados.n_int','seer_citados.estatus','seer_citados.tipo_notificacion','users.name as notificador_nombre')
        ->where('seer_general.delegacion', $user["delegacion"])
        //->where('seer_citados.id_notificador', '!=', 0)
        ->where('seer_citados.notificacion',"!=", "Trabajador")
        ->whereBetween('seer_general.fecha', [$data["fecha_inicio"], $data["fecha_final"]])
        ->get();
        */
        return redirect()->route('notificaciones_consultar');   
        //return view('notificaciones.index_busqueda',compact('notificaciones','personas','userRole','fecha_inicio','fecha_fin'));
    }

    public function hitorialnotificacador(){
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();

        $mis_notificaciones  = SeerPerGeneral::where('seer_citados.id_notificador', $id)
        ->join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
        ->join('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
        ->join('estados', 'seer_citados.estado_citado', '=', 'estados.id')
        ->join('users', 'users.id', '=', 'seer_citados.id_notificador')
        ->where('seer_citados.estatus', "!=", 'Pendiente')
        ->select('seer_citados.id as id_citado','seer_general.NUE','seer_solicitante.nombre as nombre_solicitado','seer_citados.nombre','seer_citados.primer_apellido',
        'seer_citados.segundo_apellido','municipios.nombre as municipio_citado','seer_citados.colonia','seer_citados.calle','seer_citados.tipo_vialidad','estados.nombre as estado_citado',
        'seer_citados.n_ext','seer_citados.estatus','seer_citados.tipo_notificacion','seer_citados.id_solicitud as id_solicitud','users.name as notificador_nombre')
        ->orderBy('seer_citados.created_at', 'desc')
        ->limit(500)
        ->get();

        return view('notificaciones.indexHitorial',compact('mis_notificaciones'));
    }

    public function todas_notificaciones(){
        // 1. Obtención eficiente del usuario y sus datos
        $user = auth()->user();
        $delegacionUsuario = $user->delegacion;

        // 2. Definición de grupos de delegaciones (Lógica centralizada)
        $grupos = [
            "Morelia" => ["Morelia", "Zitácuaro"],
            "Uruapan" => ["Uruapan", "Lázaro Cárdenas"],
            "Zamora"  => ["Zamora", "Sahuayo"]
        ];

        // Determinamos qué sedes consultar: el grupo correspondiente o solo la suya
        $delegacionesFiltrar = $grupos[$delegacionUsuario] ?? [$delegacionUsuario];

        // 3. Consulta optimizada
        $mis_notificaciones = SeerPerGeneral::join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
        ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
        ->join('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
        ->leftJoin('users', 'seer_citados.id_notificador', '=', 'users.id')
        
        // Filtros
        ->where('seer_citados.estatus', "!=", 'Sin asignar')
        ->whereIn('seer_general.delegacion', $delegacionesFiltrar) // Corregido: ahora usa el array de grupos
        
        // Selección de campos específica
        ->select(
            'seer_citados.id as id_citado',
            'seer_general.NUE',
            'seer_solicitante.nombre as nombre_solicitado',
            'seer_citados.nombre',
            'seer_citados.primer_apellido',
            'seer_citados.segundo_apellido',
            'municipios.nombre as municipio_citado',
            'seer_citados.colonia',
            'seer_citados.calle',
            'seer_citados.n_ext',
            'seer_citados.estatus',
            'seer_citados.tipo_notificacion',
            'seer_citados.id_solicitud as id_solicitud',
            'seer_general.id as id',
            'users.name as notificador_nombre'
        )
        ->orderBy('seer_citados.created_at', 'desc')
        ->limit(1000)
        ->get();
        /*
        $id = auth()->user()->id;
        $user = User::find($id);
        $delegacion = $user->delegacion;
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();


        if($delegacion == "Morelia"){
            $delegaciones = ["Morelia", "Zitácuaro"];
        }
        else if($delegacion == "Uruapan"){
            $delegaciones = ["Uruapan", "Lázaro Cárdenas"];
        }
        else if($delegacion == "Zamora"){
            $delegaciones = ["Zamora", "Sahuayo"];
        }


        $mis_notificaciones  = SeerPerGeneral::join('seer_citados','seer_citados.id_solicitud','=','seer_general.id')
        ->leftJoin('users', 'seer_citados.id_notificador', '=', 'users.id')
        ->join('seer_solicitante','seer_solicitante.id_solicitud','=','seer_general.id')
        ->join('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
        ->where('seer_citados.estatus', "!=", 'Pendiente')
        ->whereIn('seer_general.delegacion', $delegacion)
        ->select('seer_citados.id as id_citado','seer_general.NUE','seer_solicitante.nombre as nombre_solicitado','seer_citados.nombre',
        'seer_citados.primer_apellido','seer_citados.segundo_apellido','municipios.nombre as municipio_citado','seer_citados.colonia',
        'seer_citados.calle','seer_citados.n_ext','seer_citados.estatus','seer_citados.tipo_notificacion','seer_citados.id_solicitud as id_solicitud',
        'seer_general.id as id','users.name as notificador_nombre')
        ->orderBy('seer_citados.created_at', 'desc')
        ->limit(500)
        ->get();
        */
        return view('notificaciones.indexHitorial',compact('mis_notificaciones'));
    }

    public function genera_cumplimiento(){
         return view('cumplimientos.crear');
    }

    public function guardar_cumplimiento_cumplimientos(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $sede = $user->delegacion;

        $request->validate([
            'NUE'           => 'required',
            'empresa'       => 'required',
            'trabajador'    => 'required',
            'monto'         => 'required|numeric',
            'forma_pago'    => 'required',
            'sede'          => 'required',
            'fecha'         => 'required',
            'hora'          => 'required',
            'descripcion'   => 'required'
        ]);
            
        $data_insert=array(
            'id_solicitud'          => 0,
            'fecha'                 => $data["fecha"],
            'hora'                  => $data["hora"],
            'monto'                 => $data["monto"],
            'descripcion'           => $data["descripcion"],
            'estatus'               => "Pendiente",
            'tipo_pago'             => "Audiencia",
            'delegacion'            => $data["sede"],
            'id_conciliador'        => $id,
            'NUE'                   => $data["NUE"],
            'empresa_representante' => $data["empresa"],
            'nombre_trabajador'     => $data["trabajador"],
            'forma_pago'            => $data["forma_pago"],
            'delegacion'            => $sede,
        );

        Pagos::create($data_insert);

        return back()->with('success', 'Poder registrado correctamente.'); 
        //return view('cumplimientos/index')->with('success', 'Poder registrado correctamente.'); 
    }

    public function cumplimientos_conciliadores(){
         return view('cumplimientos.crearConciliador');
    }

    public function guardar_cumplimiento_conciliadores(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);
        $sede = $user->delegacion;
        
        $request->validate([
            'NUE'           => 'required',
            'empresa'       => 'required',
            'trabajador'    => 'required',
            'monto'         => 'required|numeric',
            'forma_pago'    => 'required',
            'sede'          => 'required',
            'fecha'         => 'required',
            'hora'          => 'required',
            'descripcion'   => 'required'
        ]);
            
        $data_insert=array(
            'id_solicitud'          => 0,
            'fecha'                 => $data["fecha"],
            'hora'                  => $data["hora"],
            'monto'                 => $data["monto"],
            'descripcion'           => $data["descripcion"],
            'estatus'               => "Pendiente",
            'tipo_pago'             => "Conciliador",
            'delegacion'            => $data["sede"],
            'id_conciliador'        => $id,
            'NUE'                   => $data["NUE"],
            'empresa_representante' => $data["empresa"],
            'nombre_trabajador'     => $data["trabajador"],
            'forma_pago'            => $data["forma_pago"],
            'delegacion'            => $sede,
        );

        Pagos::create($data_insert);

        return back()->with('success', 'Poder registrado correctamente.'); 
        //return view('cumplimientos/index')->with('success', 'Poder registrado correctamente.'); 
    }

    //PDF Constancia de cumplimiento
    public function PDFcumplimientoParcial($id){
        $pagos = Pagos::find($id);
        $delegadosEspeciales = [
            'Zitácuaro'        => 11,
            'Lázaro Cárdenas'  => 43,
            'Sahuayo'          => 26,
        ];
        if($pagos["id_solicitud"] == 0){
            $solicitud = Pagos::find($id);
            $conciliador  = User::join("pago_solicitud","pago_solicitud.id_conciliador","=","users.id");
            $conciliador = $conciliador->where("pago_solicitud.id", "=", $pagos["id"])
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
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
            $html = view('PDF/Cumplimientos/pagosParciales', compact('id', 'solicitud','conciliador','pagos','delegado'))->render();
        }else if($pagos->tipo_pago == 'Ratificacion'){
            $solicitud = Turnos::where('id', $pagos->id_solicitud)->first();
            $conciliador = User::where('id', $solicitud->id_conciliador)->select('name')->first();
            $pagosDif = Pagos::where('id_solicitud', $solicitud->id)->where('tipo_pago', 'Ratificacion')->count();
            $html = view('PDF/pagosParciales', compact('id', 'solicitud','conciliador','pagos', 'pagosDif'))->render();
        }else{
            $solicitud = SeerPerGeneral::find($pagos["id_solicitud"]);
            $antefirma = $this->antefirmaDesdePagoSolicitud($pagos->user_id ?? null, $solicitud->delegacion ?? null);
            $inicialesConcluye = $antefirma['inicialesConcluye'];
            $etiquetaIniciales = $antefirma['etiquetaIniciales'];
            $audiencia = Audiencias::where('id_solicitud', $pagos["id_solicitud"])
                ->latest()
                ->first();
            $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name')
            ->first();
            $delegacion = $solicitud->delegacion;
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
            
            $solicitanteNombre = SeerSolicitante::where('id_solicitud', $pagos["id_solicitud"])->value('nombre');
            $citados = SeerCitados::where('id_solicitud', $pagos["id_solicitud"])->where('aparece_convenio', 1)->where('resulte_responsable', 'No')->get();

            $html = view('PDF/Solicitudes/pagosParciales', compact('id', 'solicitud','conciliador','pagos', 'delegado', 'delegacion', 'solicitanteNombre', 'citados', 'audiencia', 'inicialesConcluye', 'etiquetaIniciales'))->render();
        }

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_cumplimiento_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    public function ver_pagos_audiencia($id){
        $cumplimientos = Pagos::join('seer_general','seer_general.id',"=",'pago_solicitud.id_solicitud')
        ->where('pago_solicitud.id_solicitud',$id)
        ->whereIn('tipo_pago',['Audiencia','Conciliador'])
        ->select('pago_solicitud.id','pago_solicitud.id_solicitud','seer_general.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
        ->get();

        return view('/cumplimientos/pagar_audiencia',compact('cumplimientos'));
    }

    public function ver_pago_cumplimiento($id_pago){
        
        $pago = Pagos::where('id', $id_pago)->first();
        $idSolicitud = $pago->id_solicitud;
        $id_pago = $pago->id;
        $tipo = $pago->tipo_pago;

        if ($idSolicitud == 0) {
            $cumplimientos = Pagos::where('NUE', $pago->NUE)
                ->select('id', 'id_solicitud', 'NUE', 'fecha', 'hora', 'monto', 'descripcion', 'estatus', 'forma_pago')
                ->get();
        } else {
            if ($tipo == 'Ratificacion') {
                $cumplimientos = Pagos::join('turnos','turnos.id',"=",'pago_solicitud.id_solicitud')
                ->where('pago_solicitud.id_solicitud',$idSolicitud)
                ->select('pago_solicitud.id','pago_solicitud.id_solicitud','turnos.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
                ->get();

            } else {
                $cumplimientos = Pagos::join('seer_general','seer_general.id',"=",'pago_solicitud.id_solicitud')
                ->where('pago_solicitud.id_solicitud',$idSolicitud)
                ->select('pago_solicitud.id','pago_solicitud.id_solicitud','seer_general.NUE','pago_solicitud.fecha','pago_solicitud.hora','pago_solicitud.monto','pago_solicitud.descripcion','pago_solicitud.estatus','pago_solicitud.forma_pago')
                ->get();
            }
        }

        return view('/cumplimientos/pagar_audiencia',compact('cumplimientos'));
    }

    public function seer_detalles($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        $folio = SeerCitados::find($id);
        
        return view('notificaciones.detalles',compact('folio','estados','municipios'));
    }
    //VISTA PDF Citatorio entregado por el trabajador
    public function descargarCitatorios(Request $request, $id) {
        try {
        // Obtener la solicitud
        $solicitud = SeerPerGeneral::select('id', 'NUE')
            ->where('id', $id)
            ->first();

        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        // Obtener el nombre del solicitante
        $solicitud->nombre_solicitante = SeerSolicitante::where('id_solicitud', $id)
            ->value('nombre');

        // Obtener los citados
        $citados = SeerCitados::where('id_solicitud', $id)->get();
        if ($citados->isEmpty()) {
            return redirect()->back()->with('error', 'No hay citados para esta solicitud.');
        }

        $isAudiencia = $request->query('isAud', null);

        return view('solicitudes.descargaCitatorios', compact('solicitud', 'citados', 'isAudiencia'));

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
        ], 500);
    }
        /*try {
            $citados = SeerCitados::where('id_solicitud', $id)->get();
            $isAudiencia = $request->query('isAud', null);

            if ($citados->isEmpty()) {
                return redirect()->back()->with('error', 'No hay citados para esta solicitud.');
            }

            return view('solicitudes.descargaCitatorios', compact('citados', 'isAudiencia'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }¨*/
    }

    //Guarda los citatorios para notificar el trabajador ya firmados digitalmente
    public function guardar_citatoriosT(Request $request){
        $data = $request->all();
        $id = auth()->user()->id;
        $user = User::find($id);

        $solicitudId = $data['citatorioT_id'];
        $solicitud = SeerPerGeneral::findOrFail($solicitudId);
        if ($request->hasFile('documentoCitatoriosT')) {
            $file = $request->file('documentoCitatoriosT');
            if ($file->isValid()) {
                $nombreInput = $data["nombreCitatoriosT"];
                $filename = \Illuminate\Support\Str::slug($nombreInput);
                $documentoCitatoriosT = $filename . '_Citatorio.' . $file->getClientOriginalExtension();
        
                $path = Storage::putFileAs(
                    'documentosSolicitud', $file, $documentoCitatoriosT
                );

                $data_insertar= array(
                    'id_solicitud'      => $solicitudId,
                    'nombre_documento'  => $documentoCitatoriosT,
                    'tipo_documentos'   => $file->getClientOriginalName(),
                    'tramite'           => "Audiencia", 
                );
                DocumentosSolicitud::create($data_insertar);

                $totalCitados = SeerCitados::where('id_solicitud', $solicitudId)->count();
                $totalCitatoriosSubidos = DocumentosSolicitud::where('id_solicitud', $solicitudId)
                ->where('tramite','Audiencia')
                ->where('nombre_documento', 'like', '%Citatorio%')
                ->count();
                if ($totalCitatoriosSubidos >= $totalCitados && $totalCitados > 0) {
                    SeerPerGeneral::where('id', $solicitudId)
                    ->update(['pendiente_firma' => 'No']);
                }

            return back()->with('success', 'Citatorio cargado correctamente.');
            
            } else {
                return back()->withErrors(['documentoCitatoriosT' => 'Archivo no válido.']);
            }
        }
        return back()->with('success', 'Citatorio cargado correctamente.');
    }

    public function registro_tercer_encuentro(){
        return view('tercer_encuentro');
    }

    public function tercer_encuentro_registro(Request $request){
        $data = $request->all();
        
        $data_insert=array(
            'primer_apellido'   => $data["primero_trabajador"],
            'segundo_apellido'  => $data["segundo_trabajador"],
            'nombre'            => $data["trabajador"],
            'correo'            => $data["email"],
            'telefono'          => $data["telefono"],
            'lugar'             => $data["trabajador_edad"],
            'sexo'              => $data["trabajador_sexo"],
            'estatus'           => "Pendiente",
        );

        if( $data["convesatorio1"] == "on"){
            $data_insert["convesatorio1"] = 'Conferencia Inaugural: “Implementación del Mecanismo Laboral de Respuesta Rápida (MLRR) del T- MEC”';
        }
        if( $data["convesatorio2"] == "on"){
            $data_insert["convesatorio2"] = 'Conversatorio 1: “La Conciliación Laboral como Mecanismo de la Solución Pacífica de los Conflictos Laborales”';
        }
        if( $data["convesatorio3"] == "on"){
            $data_insert["convesatorio3"] = 'Conversatorio 2: “Implicación y Aplicación de la Ley Silla, Regulación del Trabajo en Plataformas Digitales y Reducción de las Jornadas Laborales”';
        }
        if( $data["convesatorio4"] == "on"){
            $data_insert["convesatorio4"] = 'Conversatorio 3: “La Seguridad Social como Derecho Humano y su Impacto en las Resoluciones Judiciales”';
        }
        if( $data["convesatorio5"] == "on"){
            $data_insert["convesatorio5"] = 'Presentación del Libro “Conciliación y Justicia Laboral” Coordinadores: Andrés Medina Guzmán y Sergio Carmelo Domínguez Mota';
        }
        if( $data["convesatorio6"] == "on"){
            $data_insert["convesatorio6"] = 'Conversatorio 4: “Criterios Relevantes en la Ejecución de las Sentencias en Materia Laboral';
        }
        if( $data["convesatorio7"] == "on"){
            $data_insert["convesatorio7"] = 'Conversatorio 5: ILTRAS “Modelo de la Conciliación Laboral Comparada Internacionalmente”';
        }
        if( $data["convesatorio8"] == "on"){
            $data_insert["convesatorio8"] = 'Presentación del Libro ILTRAS “El Despido en Latinoamérica: Una Visión de Derecho Comparado”';
        }
        if( $data["convesatorio9"] == "on"){
            $data_insert["convesatorio9"] = 'Conferencia Magistral de Clausura';
        }
        TercerEncuentro::create($data_insert);

        $user = [
            'primer_apellido'   => $data["primero_trabajador"],
            'segundo_apellido'  => $data["segundo_trabajador"],
            'nombre'            => $data["trabajador"],
            'email'             => $data["email"],
            'convesatorio1'    => 'Conferencia Inaugural: “Implementación del Mecanismo Laboral de Respuesta Rápida (MLRR) del T- MEC”',
            'convesatorio2'    => 'Conversatorio 1: “La Conciliación Laboral como Mecanismo de la Solución Pacífica de los Conflictos Laborales”',
            'convesatorio3'    => 'Conversatorio 2: “Implicación y Aplicación de la Ley Silla, Regulación del Trabajo en Plataformas Digitales y Reducción de las Jornadas Laborales”',
            'convesatorio4'    => 'Conversatorio 3: “La Seguridad Social como Derecho Humano y su Impacto en las Resoluciones Judiciales”',
            'convesatorio5'    => 'Presentación del Libro “Conciliación y Justicia Laboral” Coordinadores: Andrés Medina Guzmán y Sergio Carmelo Domínguez Mota',
            'convesatorio6'    => 'Conversatorio 4: “Criterios Relevantes en la Ejecución de las Sentencias en Materia Laboral',
            'convesatorio7'    => 'Conversatorio 5: ILTRAS “Modelo de la Conciliación Laboral Comparada Internacionalmente”',
            'convesatorio8'    => 'Presentación del Libro ILTRAS “El Despido en Latinoamérica: Una Visión de Derecho Comparado”',
            'convesatorio9'    => 'Conferencia Magistral de Clausura',
        ];

        // 2. Envío del correo
        // El método Mail::to() toma el email del destinatario
        Mail::to($user['email'])->send(new WelcomeMail($user));

        return back()->with('success', 'Revisa tu bandeja de entrada para verificar tu folio de registro a las actividades del Tercer Encuentro Nacional de la Conciliación y Justicia Laboral.'); 
    }

    public function index_tercer_encuentro(){
        $personas = TercerEncuentro::all();
        return view('tercer.index',compact('personas'));
    }

    public function registro_asistencia_te($id){
        $persona = TercerEncuentro::findOrFail($id);
        return view('tercer.registro_asistencia', compact('persona'));
    }

    public function guardar_asistencia_te(Request $request, $id)
    {
        $persona = TercerEncuentro::findOrFail($id);

        //Se va 1 por 1 para verificar los valores Si o No
        for ($i = 1; $i <= 10; $i++) {
            $key = 'convesatorio' . $i;
            $valor = $request->boolean($key) ? 'Si' : 'No';
            
            //Si no encuentra Si o No (nombre de la conferencia) lo reemplaza con No
            if (!in_array($valor, ['Si', 'No'], true)) {
                $valor = 'No';
            }
            $persona->{$key} = $valor;
        }

        $persona->save();

        return redirect()
            ->route('registro_asistencia_te', $persona->id)
            ->with('success', 'Asistencia guardada correctamente.');
    }

    public function editar_datos_te($id){
        $persona = TercerEncuentro::findOrFail($id);
        return view('tercer.editar_datos', compact('persona'));
    }

    public function guardar_datos_te(Request $request, $id){
        $persona = TercerEncuentro::findOrFail($id);

        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido'=> 'nullable|string|max:255',
            'sexo'            => 'required|string|max:50',
            'lugar'           => 'required|string|max:255',
            'correo'          => 'required|email|max:255',
            'telefono'        => 'required|string|max:50',
        ]);

        $persona->update($validated);

        return redirect()
            ->route('editar_datos_te', $persona->id)
            ->with('success', 'Datos actualizados correctamente.');
    }

    public function pdf_tercer_encuentro(){
        $personas_conferencia1 = TercerEncuentro::where('convesatorio1','Conferencia Inaugural: “Implementación del Mecanismo Laboral de Respuesta Rápida (MLRR) del T- MEC”')->orderby('primer_apellido')->get();
        $personas_conferencia2 = TercerEncuentro::where('convesatorio2','Conversatorio 1: “La Conciliación Laboral como Mecanismo de la Solución Pacífica de los Conflictos Laborales”')->orderby('primer_apellido')->get();
        $personas_conferencia3 = TercerEncuentro::where('convesatorio3','Conversatorio 2: “Implicación y Aplicación de la Ley Silla, Regulación del Trabajo en Plataformas Digitales y Reducción de las Jornadas Laborales”')->orderby('primer_apellido')->get();
        $personas_conferencia4 = TercerEncuentro::where('convesatorio4','Conversatorio 3: “La Seguridad Social como Derecho Humano y su Impacto en las Resoluciones Judiciales”')->orderby('primer_apellido')->get();
        $personas_conferencia5 = TercerEncuentro::where('convesatorio5','Presentación del Libro “Conciliación y Justicia Laboral” Coordinadores: Andrés Medina Guzmán y Sergio Carmelo Domínguez Mota')->orderby('primer_apellido')->get();
        $personas_conferencia6 = TercerEncuentro::where('convesatorio6','Conversatorio 4: “Criterios Relevantes en la Ejecución de las Sentencias en Materia Laboral')->orderby('primer_apellido')->get();
        $personas_conferencia7 = TercerEncuentro::where('convesatorio7','Conversatorio 5: ILTRAS “Modelo de la Conciliación Laboral Comparada Internacionalmente”')->orderby('primer_apellido')->get();
        $personas_conferencia8 = TercerEncuentro::where('convesatorio8','Presentación del Libro ILTRAS “El Despido en Latinoamérica: Una Visión de Derecho Comparado”')->orderby('primer_apellido')->get();
        $personas_conferencia9 = TercerEncuentro::where('convesatorio9','Conferencia Magistral de Clausura')->orderby('primer_apellido')->get();
        $personas_conferencia10 = TercerEncuentro::where('convesatorio10','Ceremonia de Clausura')->orderby('primer_apellido')->get();
        $pdf = \PDF::loadView('PDF/TercerEncuentro/reporte', compact('personas_conferencia1','personas_conferencia2','personas_conferencia3','personas_conferencia4','personas_conferencia5','personas_conferencia6'
        ,'personas_conferencia7','personas_conferencia8','personas_conferencia9','personas_conferencia10'));
        //$pdf->setPaper('a4', 'landscape');
        return $pdf->stream('archivo.pdf');
    }
    
    //PDF Acuse de solicitud confirmada
    public function PDFacuseConfirmada($id){
        $solicitud = SeerPerGeneral::find($id);

        $inicialesConcluye = $this->inicialesDeSeerGeneral($solicitud);
        $etiquetaIniciales = $this->etiquetaDelegacionSeer($solicitud->delegacion ?? null);

        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();

        $citados = SeerCitados::where('id_solicitud', $id)->get();
       
        $pdf = \PDF::loadView('PDF/Solicitudes/acuseConfirmacion', compact('id','solicitud','solicitante','citados','inicialesConcluye','etiquetaIniciales'))
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isPhpEnabled', true);

        $nombreArchivo = 'acuse_confirmacion_' . $solicitante->nombre .'.pdf';
        return $pdf->stream($nombreArchivo);               
    }

    public function enviarAcuse(){
        $correos = TercerEncuentro::all();
        foreach($correos as $correo){
            $id = $correo["id"]; 
            $nombre = $correo["nombre"];
            // 1. Generar el PDF y obtener el contenido binario
            $pdf = \PDF::loadView('PDF/vista-prueba', compact('id', 'nombre'));
            //return $pdf->stream('archivo.pdf');
            $pdfContent = $pdf->output();

            // 2. Definir los datos para el cuerpo del mensaje (opcional)
            $datosMensaje = [
                'nombre_solicitante' => $nombre,
                'fecha_envio' => now()->format('d/m/Y'),
            ];
            $destinatario = $correo->correo;
            //$destinatario = 'sam_8929@hotmail.com';

            // 3. Enviar el Mailable, pasando el contenido del PDF y los datos del mensaje
            Mail::to($destinatario)->send(new CorreoAcuseConfirmacion($pdfContent, $datosMensaje));
        }

        return "Correo enviado con mensaje y PDF adjunto.";  
    }
    // Vista para mostrar y generar constancia usando el folio (ID) ya obtenido
    public function genera_constancia(Request $request){
        $id = $request->input('folio');
        $constancia = null;
        $asistencias = [];
        session(['ultimo_folio' => $id]);

        if ($id) {
            $constancia = ForoNacional::find($id);
            if (!$constancia) {
                return back()->with('error', 'Folio no encontrado.');
            }
        }

        return view('genera_constancia', compact('constancia', 'asistencias','id'));
    }

    //PDF Constancia Tercer Encuentro
    public function VerPDFConstancia($id){
        $constancia = TercerEncuentro::find($id);
        $html = view('PDF/TercerEncuentro/constancia', compact('id', 'constancia'))->render();

        $pdf = \PDF::loadHTML($html)
            //->setPaper('a4', 'landscape') //Horientación horizontal
            ->setPaper('a4', 'portrait') //Horientación vertical
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_' . $constancia->nombre .'.pdf';
        return $pdf->stream($nombreArchivo); 
    }

    public function RegistroPrimeraConferencia(){
        return view('tercer.primeraconferencia');
    }

    public function guardar_asistencia_post(Request $request){
        $data = $request->all();
        $fecha_actual = date('Y-m-d');
        $hora_actual  = date("H:i:s");

        if($fecha_actual == "2025-10-30"){
            if($hora_actual < "11:15:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio1' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "12:45:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio2' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "14:05:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio3' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "15:15:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio4' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "18:55:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio5' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual > "18:56:00"){
                return back()->withErrors('El registro de asistencia concluyo.'); 
            }
        }
        else if($fecha_actual == "2025-10-31"){
            if($hora_actual < "10:45:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio6' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "12:15:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio7' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual < "13:45:00"){
                TercerEncuentro::find($data["folio"])->update(['convesatorio8' => "Si"]);
                return back()->with('success', 'Asistencia registrada correctamente.'); 
            }
            else if($hora_actual > "14:00:00"){
                return back()->withErrors('El registro de asistencia concluyo.'); 
            }
        }
        else{
            $errors = "Registro de asistencia concluido.";
            return back()->withErrors($errors);
        }
    }
    
    //Genera las constacias de cada una de las conferencias asistidas
    public function crear_constancia(Request $request){
        $data = $request->all();
        $participante = ForoNacional::find($data["folio"]); // Objeto participante
        $nombre = $participante->nombre . " " . $participante->primer_apellido . " " . $participante->segundo_apellido;
       
        $html = view('PDF/TercerEncuentro/constancia', [
            'participante' => $participante,
        ])->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_' . Str::slug($nombre, '_') . '.pdf';
        return $pdf->stream($nombreArchivo);
    }

    //Envio de constancia final a todos los que cumplieron cn el 80% de asistencia
    public function enviarConstanciaFinal(){
        $participantes = TercerEncuentro::all();
        $conferencias = [
            'convesatorio1' => 'Conferencia Magistral titulada “Representatividad Sindical en México”',
            'convesatorio2' => 'Conversatorio titulado “La Conciliación Laboral como Mecanismo de la Solución Pacífica de los Conflictos Laborales”',
            'convesatorio3' => 'Conversatorio titulado “Implicación y Aplicación de la Ley Silla, Regulación del Trabajo en Plataformas Digitales y Reducción de las Jornadas Laborales”',
            'convesatorio4' => 'Conversatorio titulado “La Seguridad Social como Derecho Humano y su Impacto en las Resoluciones Judiciales”',
            'convesatorio5' => 'Presentación del Libro “Conciliación y Justicia Laboral” Coordinadores: Andrés Medina Guzmán y Sergio Carmelo Domínguez Mota',
            'convesatorio6' => 'Conversatorio titulado “Criterios Relevantes en la Ejecución de las Sentencias en Materia Laboral”',
            'convesatorio7' => 'Conversatorio titulado “Modelo de la Conciliación Laboral Comparada Internacionalmente”',
            'convesatorio8' => 'Presentación del Libro “El Despido en Latinoamérica: Una Visión de Derecho Comparado”',
            'convesatorio9' => 'Conferencia Magistral de Clausura',
        ];

        foreach ($participantes as $participante) {
            $asistencias = [];
            foreach ($conferencias as $campo => $nombre) {
                if ($participante->$campo === 'Si') {
                    $asistencias[$campo] = $nombre;
                }
            }
            $totalAsistencias = count($asistencias);

            if ($totalAsistencias >= 6) {
                $id = $participante->id;
                $nombre = "{$participante->nombre} {$participante->primer_apellido} {$participante->segundo_apellido}";
                $correo = $participante->correo;

                // Generar PDF
                $pdf = \PDF::loadView('PDF/TercerEncuentro/constanciaFinal', compact('nombre'));
                $pdfContent = $pdf->output();

                // Datos del correo
                $datosMensaje = [
                    'nombre_solicitante' => $nombre,
                    'fecha_envio' => now()->format('d/m/Y'),
                ];
                $destinatario = $correo;
                
                // 3. Enviar el Mailable, pasando el contenido del PDF y los datos del mensaje
                Mail::to($destinatario)->send(new CorreoAcuseConfirmacion($pdfContent, $datosMensaje));
                // Mail::to($correo)->send(new CorreoAcuseConfirmacion($pdfContent, $datosMensaje));
            } 
        }
        return "Correos enviados a todos los participantes con 6 o más asistencias.";
    }
    public function firmaCitatorios_index(){
        $user = auth()->user();
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();

        if($userRole[0] == "Auxiliar"){
            $solicitudes = SeerPerGeneral::select(
            'seer_general.*',
            'seer_solicitante.nombre as nombre_solicitante'
            )
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where('seer_general.user_id', $user->id)
            ->where('seer_general.pendiente_firma', 'Si')
            ->where('seer_general.estatus', 'Confirmado')
            ->orderByDesc('seer_general.id')
            ->get();
        } 
        else if($userRole[0] == "Conciliador"){
            $solicitudes = SeerPerGeneral::select(
            'seer_general.*',
            'seer_solicitante.nombre as nombre_solicitante'
            )
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where('seer_general.conciliador_id', $user->id)
            ->where('seer_general.pendiente_firma', 'Si')
            ->where('seer_general.estatus', 'Confirmado')
            ->orderByDesc('seer_general.id')
            ->get();
        }
        else if($userRole[0] == "Super Usuario" || $userRole[0] == "Administrador"){
            $solicitudes = SeerPerGeneral::select(
            'seer_general.*',
            'seer_solicitante.nombre as nombre_solicitante'
            )
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where('seer_general.conciliador_id', $user->id)
            ->where('seer_general.pendiente_firma', 'Si')
            ->where('seer_general.estatus', 'Confirmado')
            ->orderByDesc('seer_general.id')
            ->get();
        }

        return view('conciliadores.firmaCitatorios', compact('solicitudes', 'user'));
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
            ''                        => 'Sin identificación',
        ];
        return $descripciones[$tipo];
    }

    public function pagoA_audiencia(Request $request){
        $user_id = auth()->user()->id;
        $data = $request->all();
        Pagos::find($data["id"])
        ->update(['estatus'  => "Pagado", 'observaciones' => $data["observaciones"], 'user_id' => $user_id, 'fecha_conclucion' => \Carbon\Carbon::now()->format('Y-m-d')]);

        $pagos = Pagos::find($data["id"]);
        $id_solicitud = $pagos["id_solicitud"];
        $faltantes =  Pagos::where('id_solicitud',$id_solicitud)->where('estatus',"Pendiente")->get();

        if(count($faltantes) == 0){
            SeerPerGeneral::find($id_solicitud)
            ->update(['estatus' => "Concluida"]);
        }

        return redirect()->route('todas_audiencias'); 
    }

    // Eliminar/Quitar representante legal asiganado al de iniciar la audiencia
    public function quitarRepresentante(Request $request)
    {
        $data = $request->all();
        $id = $request->id;

        $citadoDB = SeerCitados::find($id);
        if (!$citadoDB) return back();

        $solicitudId = $citadoDB->id_solicitud;
        $solicitud = SeerPerGeneral::find($solicitudId);
        $sessionKey = "audiencia_data_{$solicitudId}";        
        if($solicitud->tipo_solicitud == 1) {
            if (session()->has($sessionKey)) {
                $sessionData = session($sessionKey);
                $sessionData['citados'] = $sessionData['citados']->map(function($citado) use ($id) {
                    if ($citado->id == $id) {
                        $citado->id_abogado = null;
                        $citado->id_fisica = null;
                        $citado->id_historial = null;
                    }
                    return $citado;
                });
                session([$sessionKey => $sessionData]);
            } else {
                $citado = SeerCitados::findOrFail($id);
                $citado->id_abogado = null;
                $citado->id_fisica = null;
                $citado->id_historial = null;
                $citado->save();
            }
        } else if ($solicitud->tipo_solicitud == 2) {
            if (session()->has($sessionKey)) {
                $sessionData = session($sessionKey);
                $sessionData['citados'] = $sessionData['citados']->map(function($citado) use ($id) {
                    if ($citado->id == $id) {
                        $citado->comparecencia = 'No';
                    }
                    return $citado;
                });
                session([$sessionKey => $sessionData]);
            } else {
                $citado = SeerCitados::findOrFail($id);
                $citado->comparecencia = 'No';
                $citado->save();
            }
        }
        
        session()->flash('preserve_edit_session', true);
        return back()->with('success', 'Representante eliminado correctamente.');
    }

    public function eliminar_deduccion_audiencia($id_solicitud){
        Deducciones::find($id_solicitud)->delete();
        return back()->with('success', 'Pago Deducción Correctamente.');
    }

    public function mostrar_citatorios($id) {
        
        // Obtener los citados
        $citadoCentro = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->exists();

        if($citadoCentro){
            $citados = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->get();
        } else{
            $citados = SeerCitados::select('id','nombre','primer_apellido','segundo_apellido')->where('id_solicitud', $id)->get();
        }

        if ($citados->isEmpty()) {
            return redirect()->back()->with('error', 'No hay citados para esta solicitud.');
        }
        return response()->json($citados);
    }
    public function solicitudesAuxiliares(){
        return view('solicitudes.auxiliares.solicitudAuxiliares');
    }

    public function IndustriasAux($tipo_solicitud){
        return view('solicitudes.auxiliares.tipoIndustriaAux', compact('tipo_solicitud'));
    }
    public function IndustriasAuxP($tipo_solicitud){
        return view('solicitudes.auxiliares.tipoIndustriaAuxP', compact('tipo_solicitud'));
    }
    public function inicioSolicitud_auxiliar($tipo_solicitud){  
        if ($tipo_solicitud == "1") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1') ->get();
        }
        elseif ($tipo_solicitud == "2") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '2') ->get();
        }
        elseif ($tipo_solicitud == "3") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '3') ->get();
        }
        elseif ($tipo_solicitud == "4") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '4') ->get();
        }
        $ramas = SolicitudRama::all();
       // $actividad=SolicitudEconomica::all();
        $del=Sedes::all();
        $municipios=Municipios::where('estado',16)->get();
        return view('solicitudes.auxiliares.inicioSolicitud', compact('ramas','del','municipios','tipo_solicitud','mostrarMotivos'));
    }
    public function inicioSolicitud_auxiliarP($tipo_solicitud){  
        if ($tipo_solicitud == "1") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1') ->get();
        }
        elseif ($tipo_solicitud == "2") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '2') ->get();
        }
        elseif ($tipo_solicitud == "3") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '3') ->get();
        }
        elseif ($tipo_solicitud == "4") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '4') ->get();
        }
        $ramas = SolicitudRama::all();
       // $actividad=SolicitudEconomica::all();
        $del=Sedes::all();
        $municipios=Municipios::where('estado',16)->get();
        return view('solicitudes.auxiliares.inicioSolicitudP', compact('ramas','del','municipios','tipo_solicitud','mostrarMotivos'));
    }

    public function guardar_solicitudAux($id){
        $id_usuario = auth()->user()->id;
        DB::beginTransaction();
        //try {
            if ($id == 'session') {
                // Recuperar datos de la sesión
                $solicitudData = session('solicitud_data');
                $solicitudMotivos = session('solicitud_motivos', []);
                $solicitanteData = session('solicitante_data');
                $citadosData = session('citados_data', []);
                $excepcionData = session('excepcion_data');

                if (!$solicitudData || !$solicitanteData) {
                    return redirect()->back()->with('error', 'No hay datos de solicitud en la sesión.');
                }

               // 1. Guardar SeerPerGeneral inicial
                $solicitudData = session('solicitud_data');
                $general = SeerPerGeneral::create($solicitudData);
                $id = $general->id;

                $consecutivo = $general->consecutivo;
                $delegacion = $general->delegacion;
                
                // 2. Guardar Motivos
                if (!empty($solicitudMotivos)) {
                    foreach ($solicitudMotivos as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                }

                // 3. Guardar Solicitante
                $solicitanteData['id_solicitud'] = $id;
                SeerSolicitante::create($solicitanteData);

                // 4. Guardar Caso Excepción (si existe)
                if ($excepcionData) {
                    $excepcionData['id_solicitud'] = $id;
                    SeerCasosExcepcion::create($excepcionData);
                }

                // 5. Guardar Citados
                foreach ($citadosData as $citado) {
                    $citado['id_solicitud'] = $id;
                    SeerCitados::create($citado);
                }
            }
            
            DB::commit();

            if ($id == 'session' || session()->has('solicitud_data')) {
                // Limpiar sesión
                session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);
            }
        /*} 
        catch (\Exception $e) {
            DB::rollBack();
                $solicitante = session('solicitante_data', []);
                if (!empty($solicitante) && is_array($solicitante)) {
                    $fileKeys = ['documentoIdentificacion', 'documentoCurp'];
                    foreach ($fileKeys as $key) {
                        if (!empty($solicitante[$key]) && is_string($solicitante[$key])) {
                            $filename = basename($solicitante[$key]);
                            $path = 'documentosSolicitud/' . $filename;
                            if (\Storage::exists($path)) {
                                \Storage::delete($path);
                            }
                        }
                    }
                }

                $citados = session('citados_data', []);
                if (!empty($citados) && is_array($citados)) {
                    foreach ($citados as $citado) {
                        if (is_array($citado)) {
                            foreach ($citado as $k => $v) {
                                if (is_string($v) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $v)) {
                                    $filename = basename($v);
                                    $path = 'documentosSolicitud/' . $filename;
                                    if (\Storage::exists($path)) {
                                        \Storage::delete($path);
                                    }
                                    
                                }
                            }
                        }
                    }
                }
                session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);

            return redirect()->route('solicitudes_index')->with('error', 'Ocurrió un error al finalizar la solicitud. Se descartaron los datos de captura.');
        }*/

        $delegacion = SeerPerGeneral::find($id);
        $nombreDelegacion = $delegacion->delegacion;
        
        $mapaSedes = [
            'Morelia'           => ['Morelia'],
            'Uruapan'           => ['Uruapan'],
            'Zamora'            => ['Zamora'],
            'Zitácuaro'         => ['Morelia'],
            'Lázaro Cárdenas'   => ['Uruapan'],
            'Sahuayo'           => ['Zamora']
        ];

        $sedesFiltradas = $mapaSedes[$nombreDelegacion] ?? [$nombreDelegacion];

        $delegado = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Delegado');
        })
        ->whereIn('delegacion', $sedesFiltradas)
        ->first();
  
        SeerPerGeneral::find($id)->update(['delegado_id' => $delegado->id]);
        // 1. Carga de relaciones necesarias (Eager Loading para evitar múltiples consultas)
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        $solicitud = SeerPerGeneral::find($id);
        $citados = SeerCitados::where('id_solicitud', $id)->get();
        $nombreCompleto = "{$solicitante->nombre} {$solicitante->primer_apellido} {$solicitante->segundo_apellido}";

        // 2. Buscar usuario existente por CURP o Email
        $usuario = User::where('profile_photo_path', $solicitante->curp)
        ->orWhere('email', $solicitante->email)
        ->first();

        // Inicializamos variables para el flujo de correo
        $passwordPlana = "Ya está registrada";
        if (!$usuario) {
            // Generar contraseña temporal
            $numero_aleatorio = mt_rand(1, 1000);
            $passwordPlana = "CCLMICHOACAN" . $numero_aleatorio;
        
            $usuario = User::create([
                'name'               => $nombreCompleto,
                'email'              => $solicitante->email,
                'delegacion'         => $solicitud->delegacion,
                'type'               => "Seer",
                'remember_token'     => $solicitante->curp,
                'profile_photo_path' => $solicitante->curp,
                'password'           => Hash::make($passwordPlana),
            ]);
            
            $usuario->assignRole('Solicitante');
            $mensaje = " el correo: {$usuario->email} y la contraseña: {$passwordPlana} para continuar tú trámite.";
        } else {
            $mensaje = " el correo: {$usuario->email} ya está registrado en Si Concilio. Su solicitud será asignada al usuario existente.";
        }

        // 3. Generación de PDF (Se hace una sola vez, fuera del IF)
        $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id', 'solicitud', 'solicitante', 'citados'))
        ->setPaper('a4', 'portrait')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $pdfContent = $pdf->output();

        // 4. Envío de Correo (Se hace una sola vez)
        $variables = [
        'Nombre'     => $nombreCompleto,
        'Contraseña' => $passwordPlana,
        'email'      => $solicitante->email,
        'NumFolio'   => $id,
        ];

        //Mail::to($solicitante->email)->send(new SolicitudMail($pdfContent, $variables));

        return view('solicitudes.auxiliares.avisoAux',compact('id','mensaje','delegacion'));
    }

    public function guardar_solicitudAuxP($id){
        $id_usuario = auth()->user()->id;
        if ($id == 'session') {
            // Recuperar datos de la sesión
            $solicitudData = session('solicitud_data');
            $solicitudMotivos = session('solicitud_motivos', []);
            $solicitanteData = session('solicitante_data');
            $citadosData = session('citados_data'/* , [] */); // Ya solo es un citado
            $excepcionData = session('excepcion_data');

            if (!$solicitudData || !$solicitanteData || !$citadosData) {
                return redirect()->back()->with('error', 'No hay datos de solicitud en la sesión.');
            }

            DB::beginTransaction();
            //try {
                // 1. Guardar SeerPerGeneral inicial
                $general = SeerPerGeneral::create($solicitudData);
                $id = $general->id;

                $consecutivo = $general->consecutivo;
                $delegacion = $general->delegacion;
                
                // 2. Guardar Motivos
                if (!empty($solicitudMotivos)) {
                    foreach ($solicitudMotivos as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                }

                // 3. Guardar Solicitante
                $solicitanteData['id_solicitud'] = $id;

                $solicitanteData['edad'] = $citadosData['edad'] ?? 0;
                $solicitanteData['fecha_nacimiento'] = $citadosData['fecha_nacimiento'] ?? '2000-01-01';
                $solicitanteData['nacionalidad'] = $citadosData['nacionalidad'] ?? 'Mexicana';

                SeerSolicitante::create($solicitanteData);

                // 4. Guardar Caso Excepción (si existe)
                if ($excepcionData) {
                    $excepcionData['id_solicitud'] = $id;
                    SeerCasosExcepcion::create($excepcionData);
                }

                // 5. Guardar Citados
                /* foreach ($citadosData as $citado) { */
                    $citadosData['id_solicitud'] = $id;
                    SeerCitados::create($citadosData);
                /* } */

                DB::commit();

                if ($id == 'session' || session()->has('solicitud_data')) {
                    // Limpiar sesión
                    session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);
                }
            /*} catch (\Exception $e) {
                DB::rollBack();
                    $solicitante = session('solicitante_data', []);
                    if (!empty($solicitante) && is_array($solicitante)) {
                        $fileKeys = ['documentoIdentificacion', 'documentoCurp'];
                        foreach ($fileKeys as $key) {
                            if (!empty($solicitante[$key]) && is_string($solicitante[$key])) {
                                $filename = basename($solicitante[$key]);
                                $path = 'documentosSolicitud/' . $filename;
                                if (\Storage::exists($path)) {
                                    \Storage::delete($path);
                                }
                            }
                        }
                    }

                    $citados = session('citados_data');
                    if (!empty($citados) && is_array($citados)) {
                        foreach ($citados as $citado) { 
                            if (is_array($citado)) {
                                foreach ($citado as $k => $v) {
                                    if (is_string($v) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $v)) {
                                        $filename = basename($v);
                                        $path = 'documentosSolicitud/' . $filename;
                                        if (\Storage::exists($path)) {
                                            \Storage::delete($path);
                                        }
                                        
                                    }
                                }
                            }
                        } 
                    }
                    session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);

                return redirect()->route('solicitudes_index')->with('error', 'Ocurrió un error al finalizar la solicitud. Se descartaron los datos de captura.');
            }*/
        }

        /* DB::beginTransaction();
        try {
            if ($id == 'session') {
                // Recuperar datos de la sesión
                $solicitudData = session('solicitud_data');
                $solicitudMotivos = session('solicitud_motivos', []);
                $solicitanteData = session('solicitante_data');
                $citadosData = session('citados_data', []);
                $excepcionData = session('excepcion_data');

                if (!$solicitudData || !$solicitanteData) {
                    return redirect()->back()->with('error', 'No hay datos de solicitud en la sesión.');
                }

               // 1. Guardar SeerPerGeneral inicial
                $solicitudData = session('solicitud_data');
                $general = SeerPerGeneral::create($solicitudData);
                $id = $general->id;

                $consecutivo = $general->consecutivo;
                $delegacion = $general->delegacion;
                
                // 2. Guardar Motivos
                if (!empty($solicitudMotivos)) {
                    foreach ($solicitudMotivos as $motivoId) {
                        SeerMotivo::create([
                            'id_solicitud'    => $id,
                            'id_motivo'       => $motivoId,
                        ]);
                    }
                }

                // 3. Guardar Solicitante
                $solicitanteData['id_solicitud'] = $id;
                SeerSolicitante::create($solicitanteData);

                // 4. Guardar Caso Excepción (si existe)
                if ($excepcionData) {
                    $excepcionData['id_solicitud'] = $id;
                    SeerCasosExcepcion::create($excepcionData);
                }

                // 5. Guardar Citados
                foreach ($citadosData as $citado) {
                    $citado['id_solicitud'] = $id;
                    SeerCitados::create($citado);
                }
            }
            
            DB::commit();

            if ($id == 'session' || session()->has('solicitud_data')) {
                // Limpiar sesión
                session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
                $solicitante = session('solicitante_data', []);
                if (!empty($solicitante) && is_array($solicitante)) {
                    $fileKeys = ['documentoIdentificacion', 'documentoCurp'];
                    foreach ($fileKeys as $key) {
                        if (!empty($solicitante[$key]) && is_string($solicitante[$key])) {
                            $filename = basename($solicitante[$key]);
                            $path = 'documentosSolicitud/' . $filename;
                            if (\Storage::exists($path)) {
                                \Storage::delete($path);
                            }
                        }
                    }
                }

                $citados = session('citados_data', []);
                if (!empty($citados) && is_array($citados)) {
                    foreach ($citados as $citado) {
                        if (is_array($citado)) {
                            foreach ($citado as $k => $v) {
                                if (is_string($v) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $v)) {
                                    $filename = basename($v);
                                    $path = 'documentosSolicitud/' . $filename;
                                    if (\Storage::exists($path)) {
                                        \Storage::delete($path);
                                    }
                                    
                                }
                            }
                        }
                    }
                }
                session()->forget(['solicitud_data', 'solicitud_motivos', 'solicitante_data', 'citados_data', 'excepcion_data']);

            return redirect()->route('solicitudes_index')->with('error', 'Ocurrió un error al finalizar la solicitud. Se descartaron los datos de captura.');
        } */

        $delegacion = SeerPerGeneral::find($id);
        $nombreDelegacion = $delegacion->delegacion;
        
        $mapaSedes = [
            'Morelia'           => ['Morelia'],
            'Uruapan'           => ['Uruapan'],
            'Zamora'            => ['Zamora'],
            'Zitácuaro'         => ['Morelia'],
            'Lázaro Cárdenas'   => ['Uruapan'],
            'Sahuayo'           => ['Zamora']
        ];

        $sedesFiltradas = $mapaSedes[$nombreDelegacion] ?? [$nombreDelegacion];

        $delegado = User::whereHas('roles', function ($query) {
            return $query->where('name', '=', 'Delegado');
        })
        ->whereIn('delegacion', $sedesFiltradas)
        ->first();
       
        SeerPerGeneral::find($id)->update(['delegado_id' => $delegado->id]);

        // 1. Carga de relaciones necesarias (Eager Loading para evitar múltiples consultas)
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        $solicitud = SeerPerGeneral::find($id);
        $citados = SeerCitados::where('id_solicitud', $id)->get();
        $nombreCompleto = "{$solicitante->nombre} {$solicitante->primer_apellido} {$solicitante->segundo_apellido}";

        // 2. Buscar usuario existente por CURP o Email
        $usuario = User::where('profile_photo_path', $solicitante->curp)
        ->orWhere('email', $solicitante->email)
        ->first();

        // Inicializamos variables para el flujo de correo
        $passwordPlana = "Ya está registrada";
        if (!$usuario) {
            // Generar contraseña temporal
            $numero_aleatorio = mt_rand(1, 1000);
            $passwordPlana = "CCLMICHOACAN" . $numero_aleatorio;
        
            $usuario = User::create([
                'name'               => $nombreCompleto,
                'email'              => $solicitante->email,
                'delegacion'         => $solicitud->delegacion,
                'type'               => "Seer",
                'remember_token'     => $solicitante->curp,
                'profile_photo_path' => $solicitante->curp,
                'password'           => Hash::make($passwordPlana),
            ]);
            
            $usuario->assignRole('Solicitante');
            $mensaje = " el correo: {$usuario->email} y la contraseña: {$passwordPlana} para continuar tú trámite.";
        } else {
            $mensaje = " el correo: {$usuario->email} ya está registrado en Si Concilio. Su solicitud será asignada al usuario existente.";
        }

        // 3. Generación de PDF (Se hace una sola vez, fuera del IF)
        $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id', 'solicitud', 'solicitante', 'citados'))
        ->setPaper('a4', 'portrait')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

        $pdfContent = $pdf->output();

        // 4. Envío de Correo (Se hace una sola vez)
        $variables = [
        'Nombre'     => $nombreCompleto,
        'Contraseña' => $passwordPlana,
        'email'      => $solicitante->email,
        'NumFolio'   => $id,
        ];

        //Mail::to($solicitante->email)->send(new SolicitudMail($pdfContent, $variables));


        /*
        //Revisar si ya existe el correo
        $solicitante = SeerSolicitante::where('id_solicitud',$id)->first();
        $nombre = $solicitante["nombre"]." ".$solicitante["primer_apellido"]." ".$solicitante["segundo_apellido"];
        $folio = $solicitante["id_solicitud"];
        $delegacion = SeerPerGeneral::find($id);
        $usuario = User::
        where('profile_photo_path',$solicitante['curp'])
        ->orWhere('email',$solicitante["email"])
         ->first();

        if(!isset($usuario)){
            $data_insertar_user= array(
                'name'              => $nombre,
                'email'             => $solicitante["email"],
                'delegacion'        => $delegacion["delegacion"],
                'type'              => "Seer",
                'remember_token'    => $solicitante["curp"],
                'profile_photo_path'=> $solicitante["curp"]
            ); 
            //Genrar un random del uno al 100 y agregarlo a la contraseña
            $numero_aleatorio = mt_rand(1, 1000);

            //Hacemos un hash del campo que tiene el password
            $data_insertar_user['password'] = Hash::make("CCLMICHOACAN".$numero_aleatorio);
            $usuario = User::create($data_insertar_user);
            $usuario->assignRole(('Solicitante'));
            $mensaje = " el correo:".$usuario["email"]." y la contraseña:CCLMICHOACAN".$numero_aleatorio." para continuar tú trámite.";

            $solicitud = SeerPerGeneral::find($id);
            $citados = SeerCitados::where('id_solicitud', $id)->get();

            $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
            $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
            $pdfContent = $pdf->output();

            $variables = [
                'Nombre'           => $nombre,
                'Contraseña'       => "CCLMICHOACAN".$numero_aleatorio,
                'email'            => $usuario["email"],
                'NumFolio'         => $folio,
            ];
            Mail::to($usuario['email'])->send(new SolicitudMail($pdfContent, $variables));
        }
        else{
            $mensaje = " el correo:".$usuario["email"]." ya esta registrado en Si Concilio su solicitud sera asignado al usuario existente.";
            $solicitud = SeerPerGeneral::find($id);
            $citados = SeerCitados::where('id_solicitud', $id)->get();
            $pdf = \PDF::loadView('PDF/Solicitudes/acuseSolicitud', compact('id','solicitud','solicitante','citados'))->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)->setOption('isPhpEnabled', true);
            $nombreArchivo = 'acuse_solicitud_' . $nombre .'.pdf';
            $pdfContent = $pdf->output();

            $variables = [
                'Nombre'           => $nombre,
                'Contraseña'       => "Ya esta registrada",
                'email'             => $solicitante["email"],
                'NumFolio'         => $folio,
            ];
            Mail::to($usuario['email'])->send(new SolicitudMail($pdfContent, $variables));
        }
        */
        return view('solicitudes.auxiliares.avisoAux',compact('id','mensaje','delegacion'));
    }

    public function solicitud_parte1Aux(Request $request){
        $data = $request->all();
        /*
        if($data["delegacion"] == "Lázaro Cárdenas"){
            $data["delegacion"] = "Uruapan";
        }
        if($data["delegacion"] == "Zitácuaro"){
            $data["delegacion"] = "Morelia";
        }
        if($data["delegacion"] == "Sahuayo"){
            $data["delegacion"] = "Zamora";
        }
        */
        //validando información
        
        /*$request->validate([
            'ramaIndustrial'      => 'required',
            'actividad_economica' => 'required',
            'motivo_solicitud'    => 'required',

        ]);*/

        $año_actual = date('Y');
        $numero_consecutivo = 0;
        $consecutivo  = SeerPerGeneral::latest('consecutivo')
        ->where('delegacion',$data["delegacion"])
        ->where('año',$año_actual)->
        first();
        
        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        $data_insert=array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() :0,
            'consecutivo'    => $numero_consecutivo,    
            'año'            => $año_actual,
        );
       
        // SeerPerGeneral::create($data_insert); 
        // $id_general  = SeerPerGeneral::latest('id')->first();
        // $id=$id_general["id"];
        // $tipo_generacion=$id_general->tipo_generacion;

        // Guardar en sesión en lugar de BD
        session(['solicitud_data' => $data_insert]);
        session(['solicitud_motivos' => $data["motivo_solicitud"] ?? []]);
        
        $id = 'session'; // ID temporal para indicar que estamos usando sesión

        /*
        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $id_general["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }
        */
        $estados = Estados::all();
        $municipios = Municipios::all();

       /* if($tipo_generacion != 0){*/
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
       /* }
        return view('solicitudes.solicitante', compact('estados','municipios','id'));*/
        //return redirect()->route('parte2.ver', ['id' => $id]);
    }

    public function solicitud_parte1AuxP(Request $request){
        $data = $request->all();
        /*
        if($data["delegacion"] == "Lázaro Cárdenas"){
            $data["delegacion"] = "Uruapan";
        }
        if($data["delegacion"] == "Zitácuaro"){
            $data["delegacion"] = "Morelia";
        }
        if($data["delegacion"] == "Sahuayo"){
            $data["delegacion"] = "Zamora";
        }
        */
        //validando información
        
        /*$request->validate([
            'ramaIndustrial'      => 'required',
            'actividad_economica' => 'required',
            'motivo_solicitud'    => 'required',

        ]);*/

        $año_actual = date('Y');
        $numero_consecutivo = 0;
        $consecutivo  = SeerPerGeneral::latest('consecutivo')
        ->where('delegacion',$data["delegacion"])
        ->where('año',$año_actual)->
        first();
        
        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        $data_insert=array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() :0,
            'consecutivo'    => $numero_consecutivo,    
            'año'            => $año_actual,
        );
       
        // SeerPerGeneral::create($data_insert); 
        // $id_general  = SeerPerGeneral::latest('id')->first();
        // $id=$id_general["id"];
        // $tipo_generacion=$id_general->tipo_generacion;

        // Guardar en sesión en lugar de BD
        session(['solicitud_data' => $data_insert]);
        session(['solicitud_motivos' => $data["motivo_solicitud"] ?? []]);
        
        $id = 'session'; // ID temporal para indicar que estamos usando sesión

        /*
        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $id_general["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }
        */
        $estados = Estados::all();
        $municipios = Municipios::all();

       /* if($tipo_generacion != 0){*/
            return view('solicitudes.auxiliares.solicitanteAuxP', compact('estados','municipios','id'));
       /* }
        return view('solicitudes.solicitante', compact('estados','municipios','id'));*/
        //return redirect()->route('parte2.ver', ['id' => $id]);
    }

    public function solicitud_parte2Aux(Request $request){
        $data = $request->all();
        $id = $data['id'];

        //validando información
       /*$request->validate([
            /*'tipo'                      => 'required|in:Fisica,Moral',*/
           /* 'curp'                      => 'required|min:18|max:18',
            'nombre'                    => 'required',
            'fecha_nacimiento'          => 'required|date',
            'edad'                      => 'required|numeric',
            'genero'                    => 'required|in:H,M,NC',
            'nacionalidad'              => 'required|in:Mexicana,Otra',
            'estado_nacimiento'         => 'required',
            'telefono1'                 => 'required|min:10|max:10',
            'correo'                    => 'required',
            'estado_solicitante'        => 'required',
            'vialidad'                  => 'required',
            'vialidad_calle'            => 'required',
            'numExt'                    => 'required',
            'colonia_solicitante'       => 'required',
            'municipio_solicitante'     => 'required',
            'cp'                        => 'required|numeric',
            /*'referencias'               => 'required|string|max:300',
            'calle1'                    => 'required',
            'calle2'                    => 'required',*/
           /* 'puesto'                    => 'required', 
            'periodo_pago'              => 'required',
            'pago'                      => 'required',
            'horas'                     => 'required',
            'fecha_ingreso'             => 'required',
            'jornada'                   => 'required',
            'identificacion'            => 'required',
            //'documentoCurp'             => 'required',
            'documentoIdentificacion'   => 'required',
            'num_identificacion'        => 'required',
            'descripcionSolicitud'      => 'required',
            'excepcion'                 => 'required',
            'frecuencia_hechos' => 'required_if:excepcion,Si',
            'cambios_situacionL' => 'required_if:excepcion,Si',
            'comunico_hechos' => 'required_if:excepcion,Si',
            'descripcion_conducta' => 'required_if:excepcion,Si',
            'responsable_cargo' => 'required_if:excepcion,Si',
            'actos_cometidos' => 'required_if:excepcion,Si',
            'momento_hechos' => 'required_if:excepcion,Si',
            'lugar_hechos' => 'required_if:excepcion,Si',
            'constancia_hechos' => 'required_if:excepcion,Si',
            'solicito_apoyo' => 'required_if:excepcion,Si',
            'continuacion_solicto_apoyo' => 'required_if:excepcion,Si',
            'incidencia_directa' => 'required_if:solicito_apoyo,Si',
            'recibio_atencion' => 'required_if:excepcion,Si',
        ]);*/
        
        $data_insert=array(
            'id_solicitud'         => $data["id"],
            /*'tipo_persona'         => $data["tipo"],*/
            'curp'                 => $data["curp"],
            'nombre'               => $data["nombre"],
            'fecha_nacimiento'     => $data["fecha_nacimiento"],
            'sexo'                 => $data["genero"],
            'nacionalidad'         => $data["nacionalidad"],
            'estado'               => $data["estado_nacimiento"],
            'edad'                 => $data["edad"],
            'telefono1'            => $data["telefono1"],
            'email'                => $data["correo"],
            'estado_domicilio'     => $data["estado_solicitante"],
            'tipo_vialidad'        => $data["vialidad"],
            'calle'                => $data["vialidad_calle"],
            'num_ext'              => $data["numExt"],
            'colonia'              => $data["colonia_solicitante"],
            'municipio_domicilio'  => $data["municipio_solicitante"],
            'codigo_postal'        => $data["cp"],
            /*'referencia'           => $data["referencias"],
            'calle2'               => $data["calle1"],
            'calle3'               => $data["calle2"],*/
            'puesto'               => $data["puesto"],
            'pago'                 => $data["pago"],
            'periodo_pago'         => $data["periodo_pago"],
            'horas_semana'         => $data["horas"],
            'fecha_ingreso'        => $data["fecha_ingreso"],
            'jornada'              => $data["jornada"],
            'identificacion'       => $data["identificacion"],
            'num_identificacion'   => $data["num_identificacion"],
            'descripcionSolicitud' => $data["descripcionSolicitud"],
        ); 

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                if (is_array($data["lenguaje"])) {
                    $data_insert["lenguaje"] = $data["lenguaje"][0] ?? null;
                } else {
                    $data_insert["lenguaje"] = $data["lenguaje"] ?? null;
                }
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["numInt"])){
            $data_insert["num_int"] =  $data["numInt"];
        }
        if(isset($data["discapacidad"])){
            $data_insert["discapacidad"] =  "Si";
            $data_insert["tipo_discapacidad"] =  $data["tipo_discapacidad"];
        }
        if(isset($data["labora"])){
            $data_insert["labora"] =  "Si";
            //$data_insert["fecha_salida"]  =  $data["fecha_salida"];
        }
        if(isset($data["telefono2"])){
            $data_insert["telefono2"] =  $data["telefono2"];
        }
        if(isset($data["seguro"])){
            $data_insert["nss"] =  $data["seguro"];
        }
        if(isset($data["fecha_salida"])){
            $data_insert["fecha_salida"] =  $data["fecha_salida"];
        }
        if(isset($data["referencias"])){
            $data_insert["referencia"] =  $data["referencias"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle2"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle3"] =  $data["calle2"];
        } 
        //CURP
        $documento = $data["curp"]."_CURP.pdf";
        /*$path = Storage::putFileAs(
            'documentosSolicitud', $request->file('documentoCurp'), $documento
        );*/
        //Acta de nacimiento
        if(isset($data["documentoIdentificacion"])){
            $documentoidentificacion = $data["curp"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion
        );
        }
        else{
            $documentoidentificacion = $data["curp"]."_Acta.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoActa'), $documentoidentificacion
            );
        }

        //$data_insert["documentoCurp"] = $documento;
        $data_insert["documentoIdentificacion"] = $documentoidentificacion;
       
        // SeerSolicitante::create($data_insert);
        // SeerPerGeneral::where('id', $id)
        // ->update([
        //     'caso_excepcion' => $data["excepcion"]
        // ]);
        
        // Guardar en sesión
        session(['solicitante_data' => $data_insert]);
        
        // Actualizar datos de solicitud en sesión con caso_excepcion
        $solicitudData = session('solicitud_data', []);
        $solicitudData['caso_excepcion'] = $data["excepcion"];
        session(['solicitud_data' => $solicitudData]);

        // Guardar datos de excepción si aplica
        if ($data["excepcion"] === "Si") {
             $excepcionData = [
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ];
            session(['excepcion_data' => $excepcionData]);
        }

        /*$id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){*/
            return redirect()->route('agrega_citadoAux', ['id' => $id] );
        //}
        //$estados=Estados::all();
        //return redirect()->route('agregar_citado', ['id' => $id] ); 
    }
    public function solicitud_parte2AuxP(Request $request){
        $data = $request->all();
        $id_solicitud = $data['id'];

        //validando información
       /*$request->validate([
            /*'tipo'                      => 'required|in:Fisica,Moral',*/
           /* 'curp'                      => 'required|min:18|max:18',
            'nombre'                    => 'required',
            'fecha_nacimiento'          => 'required|date',
            'edad'                      => 'required|numeric',
            'genero'                    => 'required|in:H,M,NC',
            'nacionalidad'              => 'required|in:Mexicana,Otra',
            'estado_nacimiento'         => 'required',
            'telefono1'                 => 'required|min:10|max:10',
            'correo'                    => 'required',
            'estado_solicitante'        => 'required',
            'vialidad'                  => 'required',
            'vialidad_calle'            => 'required',
            'numExt'                    => 'required',
            'colonia_solicitante'       => 'required',
            'municipio_solicitante'     => 'required',
            'cp'                        => 'required|numeric',
            /*'referencias'               => 'required|string|max:300',
            'calle1'                    => 'required',
            'calle2'                    => 'required',*/
           /* 'puesto'                    => 'required', 
            'periodo_pago'              => 'required',
            'pago'                      => 'required',
            'horas'                     => 'required',
            'fecha_ingreso'             => 'required',
            'jornada'                   => 'required',
            'identificacion'            => 'required',
            //'documentoCurp'             => 'required',
            'documentoIdentificacion'   => 'required',
            'num_identificacion'        => 'required',
            'descripcionSolicitud'      => 'required',
            'excepcion'                 => 'required',
            'frecuencia_hechos' => 'required_if:excepcion,Si',
            'cambios_situacionL' => 'required_if:excepcion,Si',
            'comunico_hechos' => 'required_if:excepcion,Si',
            'descripcion_conducta' => 'required_if:excepcion,Si',
            'responsable_cargo' => 'required_if:excepcion,Si',
            'actos_cometidos' => 'required_if:excepcion,Si',
            'momento_hechos' => 'required_if:excepcion,Si',
            'lugar_hechos' => 'required_if:excepcion,Si',
            'constancia_hechos' => 'required_if:excepcion,Si',
            'solicito_apoyo' => 'required_if:excepcion,Si',
            'continuacion_solicto_apoyo' => 'required_if:excepcion,Si',
            'incidencia_directa' => 'required_if:solicito_apoyo,Si',
            'recibio_atencion' => 'required_if:excepcion,Si',
        ]);*/

        if (empty($data['folio'])) {
            return back()->withErrors(['folio' => 'El folio es obligatorio'])->withInput();
        }

        $poder = Poder::find($data['folio']);

        if (!$poder) {
            return back()->withErrors(['folio' => 'El folio ingresado no existe'])->withInput();
        }

        $nombreCompleto = trim(
            ($poder->nombres_patronal ?? '') . ' ' .
            ($poder->primer_apellido_patronal ?? '') . ' ' .
            ($poder->segundo_apellido_patronal ?? '')
        );
        $data['nombre'] = preg_replace('/\s+/', ' ', $nombreCompleto);

         // Conversión de sexo
        $sexoMap = [
            'Femenino' => 'M',
            'Masculino' => 'H',
            'Prefiero no responder' => 'NC'
        ];
        $sexoMapeado = $sexoMap[$poder['sexo_representante']] ?? 'NC';

        // Conversión de identificación
        $idenMap = [
            'Credencial de elector' => 'Credencial de elector',
            'Pasaporte' => 'Pasaporte',
            'Cédula profesional' => 'Cédula profesional',
            'Licencia de conducir' => 'Licencia de conducir',
            'Otro' => 'Otro',
            'Credencial de inapam' => 'Credencial de inapam',
            'Cartilla militar' => 'Cartilla militar',
            'Documento migratorio' => 'Documento migratorio',
            'Constancia de identidad' => 'Constancia de identidad'
        ];
        $idenMapeado = $idenMap[$poder['tipo_identificacion']] ?? 'Otro';
        
        /* $data_insert=array(
            'id_solicitud'         => $data["id"],
            // 'tipo_persona'         => $data["tipo"],
            'curp'                 => $data["curp"],
            'nombre'               => $data["nombre"],
            'fecha_nacimiento'     => $data["fecha_nacimiento"],
            'sexo'                 => $data["genero"],
            'nacionalidad'         => $data["nacionalidad"],
            'estado'               => $data["estado_nacimiento"],
            'edad'                 => $data["edad"],
            'telefono1'            => $data["telefono1"],
            'email'                => $data["correo"],
            'estado_domicilio'     => $data["estado_solicitante"],
            'tipo_vialidad'        => $data["vialidad"],
            'calle'                => $data["vialidad_calle"],
            'num_ext'              => $data["numExt"],
            'colonia'              => $data["colonia_solicitante"],
            'municipio_domicilio'  => $data["municipio_solicitante"],
            'codigo_postal'        => $data["cp"],
            // 'referencia'           => $data["referencias"],
            // 'calle2'               => $data["calle1"],
            // 'calle3'               => $data["calle2"],
            'puesto'               => $data["puesto"],
            'pago'                 => $data["pago"],
            'periodo_pago'         => $data["periodo_pago"],
            'horas_semana'         => $data["horas"],
            'fecha_ingreso'        => $data["fecha_ingreso"],
            'jornada'              => $data["jornada"],
            'identificacion'       => $data["identificacion"],
            'num_identificacion'   => $data["num_identificacion"],
            'descripcionSolicitud' => $data["descripcionSolicitud"],
        ); */ 

        if($poder['reprecentante'] == 'Si'){
            $data_insert = [
                // --- DATOS OBTENIDOS DEL REGISTRO DEL PODER ($poder) ---
                'curp'                => $poder['curp_representante'],
                'nombre'              => $nombreCompleto,
                'sexo'                => $sexoMapeado,
                'email'               => $poder['correo_representante'],
                'telefono1'           => $poder['numero_representante'],
                'identificacion'      => $idenMapeado,
                'num_identificacion'  => $poder['num_identificacion'],
                'estado_domicilio'    => $poder['estado_patronal'],
                'estado'              => $poder['estado_patronal'],
                'municipio_domicilio' => $poder['municipio_patronal'],
                'tipo_vialidad'       => $poder['tipo_vialidad_patronal'],
                'calle'               => $poder['vialidad_patronal'],
                'num_ext'             => $poder['num_ext_patronal'],
                'num_int'             => $poder['mun_int_patronal'], // Puede ser NULL
                'colonia'             => $poder['colonia_patronal'],
                'codigo_postal'       => $poder['cp_patronal'],
                'rfc'                 => $poder['rfc_patronal'],

                // --- DATOS OBTENIDOS DEL FORMULARIO ($data) ---
                
                'id_solicitud'        => $id_solicitud,
                'puesto'              => $data['puesto'] ?? null,
                'pago'                => $data['pago'] ?? null,
                'horas_semana'        => $data['horas'] ?? null,
                'fecha_ingreso'       => $data['fecha_ingreso'] ?? null,
                'descripcionSolicitud'=> $data['descripcionSolicitud'] ?? null,
                'jornada'             => $data['jornada'] ?? null,
                'traductor'           => $data['traductor'] ?? 'No', //Usar 'No' si no viene
                'discapacidad'        => $data['discapacidad'] ?? 'No', // Usar 'No' si no viene
                'labora'              => $data['labora'] ?? 'No', // Usar 'No' si no viene

                // Campos Opcionales (usar el operador de fusión de null ?? para seguridad)
                'edad'                => $data['edad'] ?? null,
                'fecha_nacimiento'    => $data['fecha_nacimiento'] ?? null,
                'nacionalidad'        => $data['nacionalidad'] ?? null,
                'tipo_persona'        => $data['tipo_persona'] ?? null,
                'lenguaje'            => $data['lenguaje'] ?? null,
                'tipo_discapacidad'   => $data['tipo_discapacidad'] ?? null,
                'telefono2'           => $data['telefono2'] ?? null,
                'referencia'          => $data['referencia'] ?? null,
                'calle2'              => $data['calle2'] ?? null,
                'calle3'              => $data['calle3'] ?? null,
                'nss'                 => $data['nss'] ?? null,
                'periodo_pago'        => $data['periodo_pago'] ?? null,
                'fecha_salida'        => $data['fecha_salida'] ?? null,
                
                // Campos de documentos (se llenarán si se suben archivos)
                'documentoCurp'           => $data['documentoCurp'] ?? null,
                'documentoIdentificacion' => $data['documentoIdentificacion'] ?? null,

                'poder_id' => $poder['idAbogado']
            ];
        } else {
            $data_insert = [
            // --- DATOS OBTENIDOS DEL REGISTRO DEL PODER ($poder) ---
                'curp'                => $poder['curp_patronal'],
                'nombre'              => $nombreCompleto,
                'sexo'                => $sexoMapeado,
                'email'               => $poder['email_patronal'],
                'telefono1'           => $poder['telefono_patronal'],
                'identificacion'      => $poder['tipo_identificacion'],
                'num_identificacion'  => $poder['num_identificacion'],
                'estado_domicilio'    => $poder['estado_patronal'],
                'estado'              => $poder['estado_patronal'],
                'municipio_domicilio' => $poder['municipio_patronal'],
                'tipo_vialidad'       => $poder['tipo_vialidad_patronal'],
                'calle'               => $poder['vialidad_patronal'],
                'num_ext'             => $poder['num_ext_patronal'],
                'num_int'             => $poder['mun_int_patronal'], // Puede ser NULL
                'colonia'             => $poder['colonia_patronal'],
                'codigo_postal'       => $poder['cp_patronal'],
                'rfc'                 => $poder['rfc_patronal'],

                // --- DATOS OBTENIDOS DEL FORMULARIO ($data) ---
                'id_solicitud'        => $id_solicitud,
                'puesto'              => $data['puesto'] ?? null,
                'pago'                => $data['pago'] ?? null,
                'horas_semana'        => $data['horas'] ?? null,
                'fecha_ingreso'       => $data['fecha_ingreso'] ?? null,
                'descripcionSolicitud'=> $data['descripcionSolicitud'] ?? null,
                'jornada'             => $data['jornada'] ?? null,
                'traductor'           => $data['traductor'] ?? 'No', //Usar 'No' si no viene
                'discapacidad'        => $data['discapacidad'] ?? 'No', // Usar 'No' si no viene
                'labora'              => $data['labora'] ?? 'No', // Usar 'No' si no viene

                // Campos Opcionales (usar el operador de fusión de null ?? para seguridad)
                'edad'                => $data['edad'] ?? null,
                'fecha_nacimiento'    => $data['fecha_nacimiento'] ?? null,
                'nacionalidad'        => $data['nacionalidad'] ?? null,
                'tipo_persona'        => $data['tipo_persona'] ?? null,
                'lenguaje'            => $data['lenguaje'] ?? null,
                'tipo_discapacidad'   => $data['tipo_discapacidad'] ?? null,
                'telefono2'           => $data['telefono2'] ?? null,
                'referencia'          => $data['referencia'] ?? null,
                'calle2'              => $data['calle2'] ?? null,
                'calle3'              => $data['calle3'] ?? null,
                'nss'                 => $data['nss'] ?? null,
                'periodo_pago'        => $data['periodo_pago'] ?? null,
                'fecha_salida'        => $data['fecha_salida'] ?? null,
                
                // Campos de documentos (se llenarán si se suben archivos)
                'documentoCurp'           => $data['documentoCurp'] ?? null,
                'documentoIdentificacion' => $data['documentoIdentificacion'] ?? null,

                'poder_id' => $poder['idAbogado']
            ];
        }

        

        /* if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        } */
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                if (is_array($data["lenguaje"])) {
                    $data_insert["lenguaje"] = $data["lenguaje"][0] ?? null;
                } else {
                    $data_insert["lenguaje"] = $data["lenguaje"] ?? null;
                }
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        /* if(isset($data["numInt"])){
            $data_insert["num_int"] =  $data["numInt"];
        } */
        if(isset($data["discapacidad"])){
            $data_insert["discapacidad"] =  "Si";
            $data_insert["tipo_discapacidad"] =  $data["tipo_discapacidad"];
        }
        if(isset($data["labora"])){
            $data_insert["labora"] =  "Si";
            //$data_insert["fecha_salida"]  =  $data["fecha_salida"];
        }
        /* if(isset($data["telefono2"])){
            $data_insert["telefono2"] =  $data["telefono2"];
        } */
        if(isset($data["seguro"])){
            $data_insert["nss"] =  $data["seguro"];
        }
        if(isset($data["fecha_salida"])){
            $data_insert["fecha_salida"] =  $data["fecha_salida"];
        }
        /* if(isset($data["referencias"])){
            $data_insert["referencia"] =  $data["referencias"];
        } */
        /* if(isset($data["calle1"])){
            $data_insert["calle2"] =  $data["calle1"];
        } */
        /* if(isset($data["calle2"])){
            $data_insert["calle3"] =  $data["calle2"];
        } */ 
        //CURP
        /* $documento = $data["curp"]."_CURP.pdf"; */
        /*$path = Storage::putFileAs(
            'documentosSolicitud', $request->file('documentoCurp'), $documento
        );*/
        //Acta de nacimiento
        if(isset($data["documentoIdentificacion"])){
            $documentoidentificacion = $data_insert["curp"]."_Identificacion.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoIdentificacion'), $documentoidentificacion
            );
            $data_insert["documentoIdentificacion"] = $documentoidentificacion; // Guardar solo el nombre
        }
        else{
            /* $documentoidentificacion = $data["curp"]."_Acta.pdf";
            $path = Storage::putFileAs(
                'documentosSolicitud', $request->file('documentoActa'), $documentoidentificacion
            ); */
            $data_insert["documentoIdentificacion"] = null;
        }

        //$data_insert["documentoCurp"] = $documento;
        /* $data_insert["documentoIdentificacion"] = $documentoidentificacion; */
       
        // SeerSolicitante::create($data_insert);
        // SeerPerGeneral::where('id', $id)
        // ->update([
        //     'caso_excepcion' => $data["excepcion"]
        // ]);
        
        // Guardar en sesión
        session(['solicitante_data' => $data_insert]);
        
        // Actualizar datos de solicitud en sesión con caso_excepcion
        $solicitudData = session('solicitud_data', []);
        $solicitudData['caso_excepcion'] = $data["excepcion"];
        session(['solicitud_data' => $solicitudData]);

        // Guardar datos de excepción si aplica
        if ($data["excepcion"] === "Si") {
             $excepcionData = [
                'frecuencia_hechos' => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL' => $data["cambios_situacionL"] ?? null,
                'comunico_hechos' => $data["comunico_hechos"] ?? null,
                'descripcion_conducta' => $data["descripcion_conducta"] ?? null,
                'responsable_cargo' => $data["responsable_cargo"] ?? null,
                'actos_cometidos' => $data["actos_cometidos"] ?? null,
                'momento_hechos' => $data["momento_hechos"] ?? null,
                'lugar_hechos' => $data["lugar_hechos"] ?? null,
                'constancia_hechos' => $data["constancia_hechos"] ?? null,
                'solicito_apoyo' => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa' => $data["incidencia_directa"] ?? null,
                'recibio_atencion' => $data["recibio_atencion"] ?? null,
            ];
            session(['excepcion_data' => $excepcionData]);
        }

        /*$id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){*/
        return redirect()->route('agrega_citadoAuxP', ['id' => $id_solicitud] );
        //}
        //$estados=Estados::all();
        //return redirect()->route('agregar_citado', ['id' => $id] ); 
    }
    public function vista_citadoAux($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        $session_notificacion = session('citados_data.0.notificacion');
        
        if ($id == 'session') {
            $citados = count(session('citados_data', []));
        } else {
            $citados = SeerCitados::where('id_solicitud', $id)->count(); //LLeva el conteo de los citados agregados
        }

       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){*/
        return view('solicitudes.auxiliares.citadosAux',compact('estados','id','citados','municipios', 'session_notificacion'));

        /*}
        return view('solicitudes.citados',compact('estados','id','citados','municipios'));*/
    }
    public function vista_citadoAuxP($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        $session_notificacion = session('citados_data.0.notificacion');
        
        if ($id == 'session') {
            $citados = count(session('citados_data', []));
        } else {
            $citados = SeerCitados::where('id_solicitud', $id)->count(); //LLeva el conteo de los citados agregados
        }

       /* $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;
        
        //return view('solicitudes.aviso',compact('folio'));
        if($tipo_generacion != 0){*/
        return view('solicitudes.auxiliares.citadosAuxP',compact('estados','id','citados','municipios', 'session_notificacion'));

        /*}
        return view('solicitudes.citados',compact('estados','id','citados','municipios'));*/
    }

    public function guardar_citadoAux(Request $request){
        $data = $request->all();
        $imagen_domicilio1 = "Sin documento";
        $imagen_domicilio2 = "Sin documento";

        $tempId = $data["id"] == 'session' ? uniqid('session_') : $data["id"];

        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;
        
        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2, 
            'estado_citado'     => $data["estado_citado"],
        );
        
        $data_insert["notificacion"] = session('citados_data.0.notificacion', $data['notificacion'] ?? null);
        

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $data_insert["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        
            if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                $data_insert["nombre"] = $data["razon"];
            }
        
            if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                $data_insert["nombre"] = $data["nombre"];
            }
        }

        //Se van a generar el citatorio
        $data_insert['resulte_responsable'] = 'No';
        
        if ($data["id"] == 'session') {
            $citados = session('citados_data', []);
            $citados[] = $data_insert;
            session(['citados_data' => $citados]);
        } else {
            SeerCitados::create($data_insert); 
        }
        
        // Si es persona física, elimina los apellidos para este citado
        if (isset($data["tipo"]) && $data["tipo"] === "Fisica") {
            unset($data_insert["primer_apellido"], $data_insert["segundo_apellido"]);
        }

        $municipio = Municipios::find($data["municipio_citado"]); 
        $estado = Estados::find($data["estado_citado"]);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        //Validar si existe quien resulta responsable con la misma direccion

        if($data["resulte_responsable"] == "Si"){
            $data_insert["nombre"] = "QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN " .
            $data["vialidad"] . " " . $data["calle"] . ", NÚMERO " . $data["exterior"];
            if (!empty($data["interior"])) {
                $data_insert["nombre"] .= " INT. " . $data["interior"];
            }
            $data_insert["nombre"] .= " COLONIA " . $data["colonia"] . ", " . $municipioNombre . ", " . $estadoNombre . ", C.P. " . $data["cp"] . ".";

            // Marcar este nuevo registro como el "quien resulte" y crear solo si no existe ya uno igual
            $data_insert['resulte_responsable'] = 'Si';
            $direccionNombre = $data_insert["nombre"];
            
            if ($data["id"] == 'session') {
                $citados = session('citados_data', []);
                $existe = false;
                foreach ($citados as $citado) {
                    if ($citado['nombre'] == $direccionNombre && $citado['resulte_responsable'] == 'Si') {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $citados[] = $data_insert;
                    session(['citados_data' => $citados]);
                }
            } else {
                $existe = SeerCitados::where('id_solicitud', $data['id'])
                            ->where('nombre', $direccionNombre)
                            ->where('resulte_responsable', 'Si')
                            ->exists();
                if (!$existe) {
                    SeerCitados::create($data_insert);
                }
            }
        }
        
        return back()->with('success', 'Citado agregado correctamente, puedes agregar otro o continuar.');
    }

    public function guardar_citadoAuxP(Request $request, $id){
        $data = $request->all();
        $imagen_domicilio1 = "Sin documento";
        $imagen_domicilio2 = "Sin documento";

        $data['notificacion'] = 'Centro';

        $tempId = $data["id"] == 'session' ? uniqid('session_') : $data["id"];

        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;
        //validando información
        /*$request->validate([
            'id'                => 'required',
            'colonia'           => 'required',
            'vialidad'          => 'required',
            'cp'                => 'required|numeric',
            'calle'             => 'required',
            'exterior'          => 'required',
            'referencia'        => 'required',
            'municipio_citado'  => 'required',
            'estado_citado'     => 'required',
            'vialidad'          => 'required'
        ]);*/
        
        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2, 
            'estado_citado'     => $data["estado_citado"],
            'edad'              => $data["edad"],
            'fecha_nacimiento'  => $data["fecha_nacimiento"],
            'nacionalidad'      => $data["nacionalidad"],
        );
        // Regla 1: siempre Centro
        $data_insert["notificacion"] = $data['notificacion'];
        

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $data_insert["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        }

        //Se van a generar el citatorio
        $data_insert['resulte_responsable'] = 'No';
        
        /* if ($data["id"] == 'session') {
            $citados = session('citados_data', []);
            $citados[] = $data_insert;
            session(['citados_data' => $citados]);
        } else {
            SeerCitados::create($data_insert); 
        } */
        /*$checando = session()->get('citados_data');
        
        // Si es persona física, elimina los apellidos para este citado
        /* if (isset($data["tipo"]) && $data["tipo"] === "Fisica") {
            unset($data_insert["primer_apellido"], $data_insert["segundo_apellido"]);
        } */

        $municipio = Municipios::find($data["municipio_citado"]); 
        $estado = Estados::find($data["estado_citado"]);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        $data_insert['resulte_responsable'] = 'No';
        
        /* if ($data["id"] == 'session') {
            $citados = session('citados_data', []);
            $existe = false;
            foreach ($citados as $citado) {
                if ($citado['nombre'] == $direccionNombre && $citado['resulte_responsable'] == 'Si') {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $citados[] = $data_insert;
                session(['citados_data' => $citados]);
            }
        } else {
            $existe = SeerCitados::where('id_solicitud', $data['id'])
                        ->where('nombre', $direccionNombre)
                        ->where('resulte_responsable', 'Si')
                        ->exists();
            if (!$existe) {
                SeerCitados::create($data_insert);
            }
        } */
        /* return back()->with('success', 'Citado agregado correctamente, puedes agregar otro o continuar.') */;

        // Actualizar en sesión los datos del solicitante con los Datos Laborales
        // capturados en esta vista (AuxP), para que se usen al crear SeerSolicitante.
        $solicitanteSession = session('solicitante_data', []);
        if (!empty($solicitanteSession) && is_array($solicitanteSession)) {
            $solicitanteSession['nss']                 = $data['seguro'] ?? ($solicitanteSession['nss'] ?? null);
            $solicitanteSession['puesto']              = $data['puesto'] ?? ($solicitanteSession['puesto'] ?? null);
            $solicitanteSession['periodo_pago']        = $data['periodo_pago'] ?? ($solicitanteSession['periodo_pago'] ?? null);
            $solicitanteSession['pago']                = $data['pago'] ?? ($solicitanteSession['pago'] ?? null);
            $solicitanteSession['horas_semana']        = $data['horas'] ?? ($solicitanteSession['horas_semana'] ?? null);
            $solicitanteSession['jornada']             = $data['jornada'] ?? ($solicitanteSession['jornada'] ?? null);
            $solicitanteSession['fecha_ingreso']       = $data['fecha_ingreso'] ?? ($solicitanteSession['fecha_ingreso'] ?? null);
            $solicitanteSession['fecha_salida']        = $data['fecha_salida'] ?? ($solicitanteSession['fecha_salida'] ?? null);
            $solicitanteSession['descripcionSolicitud']= $data['descripcionSolicitud'] ?? ($solicitanteSession['descripcionSolicitud'] ?? null);

            // Checkbox de "¿Laboras actualmente?" => Si/No
            if (isset($data['labora'])) {
                $solicitanteSession['labora'] = 'Si';
            } else {
                if (!array_key_exists('labora', $solicitanteSession)) {
                    $solicitanteSession['labora'] = 'No';
                }
            }

            session(['solicitante_data' => $solicitanteSession]);
        }

        // Guardar en sesión el citado
        session(['citados_data' => $data_insert]);

        return $this->guardar_solicitudAuxP($id);
    }
    
    //PDF Notificación de multa cuando es exitosa
    public function VerPDFMultaNotificacion($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $audiencia = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id")
        ->where("audiencias.id_solicitud", "=", $solicitud["id"])->latest('audiencias.created_at')->first();
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();

        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        /*$imagenes = [];

        for ($i = 1; $i <= 3; $i++) {
            $path = storage_path("app/documentos_notificacion/{$citado->id}-foto{$i}.jpg");

            if (file_exists($path)) {
                $imagenes[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }*/
        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
        $html = view('PDF/Solicitudes/multaNotificaciones', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','audiencia'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'multaNotificada_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo); 
    }


    //PDF Constancia de cumplimiento
    public function VerPDFCumplimientoTotal($id){
        $solicitud = SeerPerGeneral::find($id);
        $solicitud->solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        $audienciaFecha = Audiencias::where('id_solicitud', $id)->latest()->value('updated_at');

        $allCentro = 1;
        $citadosCentro = SeerCitados::where('id_solicitud', $id)->latest()->get();
        foreach ($citadosCentro as $citado){
            if($citado->notificacion == 'Centro'){
                $allCentro = 0;
                break;
            }
        }

        /*if ($allCentro == 0){
            $solicitud->citados = SeerCitados::where('id_solicitud', $id)->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->get();
        }
        else {
            $solicitud->citados = SeerCitados::where('id_solicitud', $id)->get();
        }*/
        if ($allCentro == 0){
            $solicitud->citados = SeerCitados::where('id_solicitud', $id)->where('aparece_convenio', 1)->where('resulte_responsable', 'No')->where('notificacion', 'Centro')->where('tipo_notificacion', '!=', 'Multa')->get();
        }
        else {
            $solicitud->citados = SeerCitados::where('id_solicitud', $id)->where('aparece_convenio', 1)->where('resulte_responsable', 'No')->get();
            //$solicitud->citados = SeerCitados::where('id_solicitud', $id)->get();
        }
        
        $pagos = Pagos::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->get();
        $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id");
        $conciliador = $conciliador->where("audiencias.id_solicitud", "=", $id)
        ->latest('audiencias.created_at')
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

        $ultimoPago = Pagos::where('id_solicitud', $id)->where('tipo_pago','Audiencia')->latest()->first();
        $antefirma = $this->antefirmaDesdePagoSolicitud($ultimoPago->user_id ?? null, $solicitud->delegacion ?? null);
        $inicialesConcluye = $antefirma['inicialesConcluye'];
        $etiquetaIniciales = $antefirma['etiquetaIniciales'];

        $html = view('PDF/Solicitudes/ConstanciaCumplimiento', compact('id', 'solicitud','conciliador','pagos','delegado', 'audienciaFecha', 'inicialesConcluye', 'etiquetaIniciales'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'constancia_de_cumplimiento_' . $solicitud->trabajador .'.pdf';
        return $pdf->stream($nombreArchivo);                  
    }

    public function VerPDFCaratula($id, $tipo){
        $bandera = ($tipo == 'ratificacion') ? 'Ratificación' : 'Solicitud';

        if ($tipo == 'ratificacion') {
            $ratificacion = Turnos::where('turnos.id', $id)
            ->leftJoin('municipios', 'turnos.municipio_rat', '=', 'municipios.id')
            ->leftJoin('estados', 'turnos.estado_rat', '=', 'estados.id')
            ->select(
                'turnos.*', 
                'municipios.nombre as municipio_domicilio', 
                'estados.nombre as estado_domicilio'
            )
            ->first();

            if($ratificacion->id_historial){
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
            
            $html = view('PDF/Caratula', compact('id','ratificacion','abogado','bandera'))->render();
        } else {
            $solicitud = SeerPerGeneral::find($id);
            $solicitante = SeerSolicitante::where('id_solicitud', $solicitud["id"])
            ->leftJoin('municipios', 'seer_solicitante.municipio_domicilio', '=', 'municipios.id')
            ->leftJoin('estados', 'seer_solicitante.estado_domicilio', '=', 'estados.id')
            ->select(
                'seer_solicitante.*', 
                'municipios.nombre as nombre_municipio_sol', 
                'estados.nombre as nombre_estado_sol'
            )
            ->first();
            $citados = SeerCitados::where("id_solicitud", $id)
            ->leftJoin('municipios', 'seer_citados.municipio_citado', '=', 'municipios.id')
            ->leftJoin('estados', 'seer_citados.estado_citado', '=', 'estados.id')
            ->select('seer_citados.*', 'municipios.nombre as nombre_municipio', 'estados.nombre as nombre_estado')
            ->get();
            $notifica = \DB::table('seer_citados')
            ->where('id_solicitud', $id)
            ->pluck('notificacion');

            $motivos = \DB::table('seer_motivos')
            ->join('catalogo_motivos', 'seer_motivos.id_motivo', '=', 'catalogo_motivos.id')
            ->where('seer_motivos.id_solicitud', $id)
            ->select('catalogo_motivos.motivo')
            ->get();

            $html = view('PDF/Caratula', compact('id','solicitud','solicitante','citados','motivos','notifica','bandera'))->render();
        }
        
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'Captura_caratula' .'.pdf';
        return $pdf->stream($nombreArchivo);  
    }

//Solicitud en línea patronal
    public function Industrias_p($tipo_solicitud){
        return view('solicitudes.patronal.tipoIndustria_p', compact('tipo_solicitud'));
    }
    public function patron($tipo_solicitud){  
        if ($tipo_solicitud == "1") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1') ->get();
        }
        elseif ($tipo_solicitud == "2") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '2') ->get();
        }
        elseif ($tipo_solicitud == "3") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '3') ->get();
        }
        elseif ($tipo_solicitud == "4") {
            $mostrarMotivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '4') ->get();
        }
        $ramas = SolicitudRama::all();
       // $actividad=SolicitudEconomica::all();
        $del=Sedes::all();
        $municipios=Municipios::where('estado',16)->get();
       /* if($tipo_solicitud[0] == "1"){
            //$personas = null;
            $motivos = SolicitudMotivo::where('catalogo_motivos.tipo_solicitud', '1')
            ->select('catalogo_motivos.motivo','seer_general.NUE','seer_general.solicitante','seer_citados.nombre','seer_citados.direccion','seer_citados.estatus')
            ->get();
        }*/
        return view('solicitudes.patronal.solicitud_p', compact('ramas','del','municipios','tipo_solicitud','mostrarMotivos'));
    }
    public function solicitud_patronal(Request $request){
        $data = $request->all();
        /*
        if($data["delegacion"] == "Lázaro Cárdenas"){
            $data["delegacion"] = "Uruapan";
        }
        if($data["delegacion"] == "Zitácuaro"){
            $data["delegacion"] = "Morelia";
        }
        if($data["delegacion"] == "Sahuayo"){
            $data["delegacion"] = "Zamora";
        }
        */
        //validando información
        
        $request->validate([
            'ramaIndustrial'      => 'required',
            'actividad_economica' => 'required',
            'motivo_solicitud'    => 'required',

        ]);
        
        $año_actual = date('Y');
        $numero_consecutivo = 0;
        $consecutivo  = SeerPerGeneral::latest('consecutivo')
        ->where('delegacion',$data["delegacion"])
        ->where('año',$año_actual)->
        first();

        if(empty($consecutivo)){
            $numero_consecutivo = 1;
        }
        else{
            $numero_consecutivo = $consecutivo["consecutivo"];
            $numero_consecutivo++;
        }

        $data_insert=array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() :0,
            'consecutivo'    => $numero_consecutivo,    
            'año'            => $año_actual,
        );
       
        /*SeerPerGeneral::create($data_insert); 
        $id_general  = SeerPerGeneral::latest('id')->first();
        $id=$id_general["id"];
        $tipo_generacion=$id_general->tipo_generacion;

        if (!empty($data["motivo_solicitud"])) {
            foreach ($data["motivo_solicitud"] as $motivoId) {
                SeerMotivo::create([
                    'id_solicitud'    => $id_general["id"],
                    'id_motivo'       => $motivoId,
                    
                ]);
            }
        }*/

        // Guardar en sesión
        $solicitud_data = array(
            'id_rama'         =>  $data["ramaIndustrial"],
            'actividad'       =>  $data["actividad_economica"],
            'delegacion'      =>  $data["delegacion"],
            'tipo_solicitud'  =>  $data["tipo_solicitud"],
            'tipo_generacion' => auth()->check() ? auth()->id() : 0,
            'consecutivo'     => $numero_consecutivo,
            'año'             => $año_actual,
            'motivo_solicitud' => $data["motivo_solicitud"] ?? []
        );

        // Limpiar sesiones anteriores
        session()->forget('solicitante_trabajador_data');
        session()->forget('citados_trabajador_data');
       
        session(['solicitud_trabajador_data' => $solicitud_data]);

        $id = 'session';

        $estados = Estados::all();
        $municipios = Municipios::all();

        /*if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }*/
        return view('solicitudes.patronal.solicitante_p', compact('estados','municipios','id'));
        //return redirect()->route('parte2.ver', ['id' => $id]);
    }

    public function vista_solicitanteP(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return redirect()->route('publico');
        }

        if ($id == 'session') {
            $solicitud_data = session('solicitud_trabajador_data');
            if (!$solicitud_data) {
                return redirect()->route('publico')->with('error', 'Sesión expirada.');
            }
            $tipo_generacion = $solicitud_data['tipo_generacion'];
        } else {
            $solicitud = SeerPerGeneral::find($id);

            if (!$solicitud) {
                return redirect()->route('publico')->with('error', 'La solicitud no existe.');
            }
            $tipo_generacion = $solicitud->tipo_generacion;
        }

        $estados = Estados::all();
        $municipios = Municipios::all();

        if($tipo_generacion != 0){
            return view('solicitudes.auxiliares.solicitanteAux', compact('estados','municipios','id'));
        }
        
        return view('solicitudes.patronal.solicitante_p', compact('estados','municipios','id'));
    }
    
    public function inserta_solicitanteP(Request $request){
        $data = $request->all();
        $id_solicitud = $data['id'];

        if (empty($data['folio'])) {
            return back()->withErrors(['folio' => 'El folio es obligatorio'])->withInput();
        }

        $poder = Poder::find($data['folio']);

        if (!$poder) {
            return back()->withErrors(['folio' => 'El folio ingresado no existe'])->withInput();
        }

        $nombreCompleto = trim(
            ($poder->nombres_patronal ?? '') . ' ' .
            ($poder->primer_apellido_patronal ?? '') . ' ' .
            ($poder->segundo_apellido_patronal ?? '')
        );
        $data['nombre'] = preg_replace('/\s+/', ' ', $nombreCompleto);

        // Conversión de sexo
        $sexoMap = [
            'Femenino' => 'M',
            'Masculino' => 'H',
            'Prefiero no responder' => 'NC'
        ];
        $sexoMapeado = $sexoMap[$poder['sexo_representante']] ?? 'NC';

        // Conversión de identificación
        $idenMap = [
            'Credencial de Elector' => 'Credencial de elector',
            'Pasaporte' => 'Pasaporte',
            'Cédula Profesional' => 'Cédula profesional',
            'Licencia de Conducir' => 'Licencia de conducir',
            'Otros' => 'Otros',
            'Credencial de INAPAM' => 'Credencial de inapam',
            'Cartilla Militar' => 'Cartilla militar',
            'Documento Migratorio' => 'Documento migratorio',
            'Constancia de Identidad' => 'Constancia de identidad'
        ];
        $idenMapeado = $idenMap[$poder['tipo_identificacion']] ?? 'Otros';

        $data_solicitante = [
            // --- DATOS OBTENIDOS DEL REGISTRO DEL PODER ($poder) ---
            'curp'                => $poder['curp_representante'],
            'nombre'              => $nombreCompleto,
            'sexo'                => $sexoMapeado,
            'email'               => $poder['correo_representante'],
            'telefono1'           => $poder['numero_representante'],
            'identificacion'      => $idenMapeado,
            'num_identificacion'  => $poder['num_identificacion'],
            'estado_domicilio'    => $poder['estado_patronal'],
            'estado'              => $poder['estado_patronal'],
            'municipio_domicilio' => $poder['municipio_patronal'],
            'tipo_vialidad'       => $poder['tipo_vialidad_patronal'],
            'calle'               => $poder['vialidad_patronal'],
            'num_ext'             => $poder['num_ext_patronal'],
            'num_int'             => $poder['mun_int_patronal'], // Puede ser NULL
            'colonia'             => $poder['colonia_patronal'],
            'codigo_postal'       => $poder['cp_patronal'],

            // --- DATOS OBTENIDOS DEL FORMULARIO ($data) ---
            // Campos Obligatorios
            'id_solicitud'        => $id_solicitud,
            'nacionalidad'        => $data['nacionalidad'],
            'fecha_nacimiento'    => $data['fecha_nacimiento'],
            'edad'                => $data['edad'],
            'puesto'              => $data['puesto'],
            'pago'                => $data['pago'],
            'horas_semana'        => $data['horas'],
            'fecha_ingreso'       => $data['fecha_ingreso'],
            'descripcionSolicitud'=> $data['descripcionSolicitud'],
            'traductor'           => $data['traductor'] ?? 'No', // Usar 'No' si no viene
            'discapacidad'        => $data['discapacidad'] ?? 'No', // Usar 'No' si no viene
            'labora'              => $data['labora'] ?? 'No', // Usar 'No' si no viene

            // Campos Opcionales (usar el operador de fusión de null ?? para seguridad)
            'tipo_persona'        => $data['tipo_persona'] ?? null,
            'rfc'                 => $data['rfc'] ?? null,
            'lenguaje'            => $data['lenguaje'] ?? null,
            'tipo_discapacidad'   => $data['tipo_discapacidad'] ?? null,
            'telefono2'           => $data['telefono2'] ?? null,
            'referencia'          => $data['referencia'] ?? null,
            'calle2'              => $data['calle2'] ?? null,
            'calle3'              => $data['calle3'] ?? null,
            'nss'                 => $data['nss'] ?? null,
            'periodo_pago'        => $data['periodo_pago'] ?? null,
            'fecha_salida'        => $data['fecha_salida'] ?? null,
            'jornada'             => $data['jornada'] ?? null,
            
            // Campos de documentos (se llenarán si se suben archivos)
            'documentoCurp'           => $data['documentoCurp'] ?? null,
            'documentoIdentificacion' => $data['documentoIdentificacion'] ?? null,
        ];

        // Manejo de traductor
        if (isset($data["traductor"])) {
            $val = $data["traductor"];
            $data_solicitante["traductor"] = ($val === 'Si' || $val === '1' || $val === 'on') ? 'Si' : 'No';
            $data_solicitante["lenguaje"] = $data["lenguaje"] ?? null;
        }

        /*$solicitante_data = [
            'solicitante' => $data_solicitante,
            'excepcion' => $data["excepcion"],
            'excepcion_data' => $excepcion_data
        ];*/
        
        session(['solicitante_trabajador_data' => $data_solicitante]);
       
        $data_citado = [
            'id_solicitud'      => $id_solicitud,
            'tipo_persona'      => $poder->tipo,
            'estatus'           => 'Pendiente',
            'id_abogado'        => $poder->idAbogado,
            //'notificacion'      => 'Centro',
            'tipo_notificacion' => 'Citatorio'
        ];

        //SeerCitados::create($data_citado);

        //SeerPerGeneral::where('id', $id_solicitud)->update([
        //    'caso_excepcion' => $data["excepcion"]
        //]);

        if ($data["excepcion"] === "Si") {
            SeerCasosExcepcion::create([
                'id_solicitud'               => $id_solicitud,
                'frecuencia_hechos'          => $data["frecuencia_hechos"] ?? null,
                'cambios_situacionL'         => $data["cambios_situacionL"] ?? null,
                'comunico_hechos'            => $data["comunico_hechos"] ?? null,
                'descripcion_conducta'       => $data["descripcion_conducta"] ?? null,
                'responsable_cargo'          => $data["responsable_cargo"] ?? null,
                'actos_cometidos'            => $data["actos_cometidos"] ?? null,
                'momento_hechos'             => $data["momento_hechos"] ?? null,
                'lugar_hechos'               => $data["lugar_hechos"] ?? null,
                'constancia_hechos'          => $data["constancia_hechos"] ?? null,
                'solicito_apoyo'             => $data["solicito_apoyo"] ?? null,
                'continuacion_solicto_apoyo' => $data["continuacion_solicto_apoyo"] ?? null,
                'incidencia_directa'         => $data["incidencia_directa"] ?? null,
                'recibio_atencion'           => $data["recibio_atencion"] ?? null,
            ]);
        }
        
        return redirect()->route('agregar_citadoPatronal', ['id' => $id_solicitud]);
    }
    public function vista_citadoPatronal($id){
        $estados = Estados::all();
        $municipios = Municipios::all();
        if ($id == 'session') {
            $citados_data = session('citados_trabajador_data', []);
            $citados = count($citados_data);
        } else {
            $citados = SeerCitados::where('id_solicitud', $id)->count(); //LLeva el conteo de los citados agregados
        }
        return view('solicitudes.patronal.citados_p',compact('estados','id','citados','municipios'));
    }
    public function guardar_citadoPatronal(Request $request){
        $data = $request->all();
        $imagen_domicilio1 = "Sin documento";
        $imagen_domicilio2 = "Sin documento";

        // Usar ID temporal si es sesión
        $tempId = ($data['id'] == 'session') ? uniqid('session_') : $data['id'];

        if ($request->hasFile('foto1')) {
            $imagen_domicilio1 = $tempId . "-domicilio_Citado1.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto1'), $imagen_domicilio1);
        }
        
        if ($request->hasFile('foto2')) {
            $imagen_domicilio2 = $tempId . "-domicilio_Citado2.jpg" . Str::random(8) . ".jpg";
            Storage::putFileAs('documentosSolicitud', $request->file('foto2'), $imagen_domicilio2);
        }
        $foto1 = $imagen_domicilio1;
        $foto2 = $imagen_domicilio2;
        //validando información
        $request->validate([
            'id'                => 'required',
            'colonia'           => 'required',
            'vialidad'          => 'required',
            'cp'                => 'required|numeric',
            'calle'             => 'required',
            'exterior'          => 'required',
            'referencia'        => 'required',
            'municipio_citado'  => 'required',
            'estado_citado'     => 'required',
            'vialidad'          => 'required'
        ]);
        
        $data_insert=array(
            'id_solicitud'      => $data["id"],
            'colonia'           => $data["colonia"],
            'cp'                => $data["cp"],
            'n_ext'             => $data["exterior"],
            'calle'             => $data["calle"],
            'tipo_vialidad'     => $data["vialidad"],
            'referencia'        => $data["referencia"],
            'municipio_citado'  => $data["municipio_citado"],
            'imagen_domicilio1' => $foto1,
            'imagen_domicilio2' => $foto2, 
            'estado_citado'     => $data["estado_citado"],
        );
        $data_insert["notificacion"] =  $data["notificacion"];

        if(isset($data["rfc"])){
            $data_insert["rfc"] =  $data["rfc"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["traductor"])){
            $val = $data["traductor"];
            $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
            $data_insert["traductor"] = $requires ? 1 : 0;
            if (isset($data["lenguaje"])) {
                $data_insert["lenguaje"] = is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : ($data["lenguaje"] ?? null);
            } else {
                $data_insert["lenguaje"] = null;
            }
        }
        if(isset($data["interior"])){
            $data_insert["n_int"] =  $data["interior"];
        }
        if(isset($data["calle1"])){
            $data_insert["calle1"] =  $data["calle1"];
        }
        if(isset($data["calle2"])){
            $data_insert["calle2"] =  $data["calle2"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["curp"])){
            $data_insert["curp"] =  $data["curp"];
        }
        if(isset($data["nombre"])){
            $data_insert["nombre"] =  $data["nombre"];
        }
        if(isset($data["primer_apellido"])){
            $data_insert["primer_apellido"] =  $data["primer_apellido"];
        }
        if(isset($data["segundo_apellido"])){
            $data_insert["segundo_apellido"] =  $data["segundo_apellido"];
        }
        if (isset($data["tipo"])) {
            $data_insert["tipo_persona"] = $data["tipo"];
        
            /*if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                $data_insert["nombre"] = $data["razon"];
            }*/
        
            if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                $data_insert["nombre"] = $data["nombre"];
            }
        }

        //Se van a generar el citatorio
        $data_insert['resulte_responsable'] = 'No';
        //SeerCitados::create($data_insert); 
        // Si es persona física, elimina los apellidos para este citado
        if (isset($data["tipo"]) && $data["tipo"] === "Fisica") {
            unset($data_insert["primer_apellido"], $data_insert["segundo_apellido"]);
        }

        $municipio = Municipios::find($data["municipio_citado"]); 
        $estado = Estados::find($data["estado_citado"]);
        $municipioNombre = $municipio ? mb_strtoupper($municipio->nombre, 'UTF-8') : '';
        $estadoNombre = $estado ? mb_strtoupper($estado->nombre, 'UTF-8') : '';

        //Validar si existe quien resulta responsable con la misma direccion

        /*$data_insert["nombre"] = "QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN " .
        $data["vialidad"] . " " . $data["calle"] . ", NÚMERO " . $data["exterior"];
        if (!empty($data["interior"])) {
            $data_insert["nombre"] .= " INT. " . $data["interior"];
        }
        $data_insert["nombre"] .= " COLONIA " . $data["colonia"] . ", " . $municipioNombre . ", " . $estadoNombre . ", C.P. " . $data["cp"] . ".";

        // Marcar este nuevo registro como el "quien resulte" y crear solo si no existe ya uno igual
        $data_insert['resulte_responsable'] = 'Si';
        $direccionNombre = $data_insert["nombre"];
        */
        if ($data['id'] == 'session') {
            $citados_list = session('citados_trabajador_data', []);
            
            $citado_original = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
            
            if(isset($data["rfc"])) $citado_original["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $citado_original["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $citado_original["traductor"] = $requires ? 1 : 0;
                $citado_original["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $citado_original["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $citado_original["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $citado_original["calle2"] = $data["calle2"];
            
            if (isset($data["tipo"])) {
                $citado_original["tipo_persona"] = $data["tipo"];
                /*if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original["nombre"] = $data["razon"];
                }*/
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original["nombre"] = $data["nombre"];
                    if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                    if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
                }
            } else {
                 if(isset($data["nombre"])) $citado_original["nombre"] = $data["nombre"];
                 if(isset($data["primer_apellido"])) $citado_original["primer_apellido"] = $data["primer_apellido"];
                 if(isset($data["segundo_apellido"])) $citado_original["segundo_apellido"] = $data["segundo_apellido"];
            }

            $citados_list[] = $citado_original;

            // Verificar si existe "quien resulte" en la sesión
            //$existe = false;
            /*foreach ($citados_list as $c) {
                if (isset($c['resulte_responsable']) && $c['resulte_responsable'] == 'Si' && $c['nombre'] == $direccionNombre) {
                    $existe = true;
                    break;
                }
            }
            
            if (!$existe) {
                $citados_list[] = $data_insert; // Este ya tiene el nombre modificado y resulte_responsable='Si'
            }*/
            
            session(['citados_trabajador_data' => $citados_list]);
            
        } else {
            // Lógica original BD
            // Reconstruir data_insert original para guardar el primero
             $citado_original_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );
             
             if (isset($data["tipo"])) {
                $citado_original_bd["tipo_persona"] = $data["tipo"];
                /*if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $citado_original_bd["nombre"] = $data["razon"];
                }*/
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $citado_original_bd["nombre"] = $data["nombre"];
                }
            }
            
             $data_insert_bd = array(
                'id_solicitud'      => $data["id"],
                'colonia'           => $data["colonia"],
                'cp'                => $data["cp"],
                'n_ext'             => $data["exterior"],
                'calle'             => $data["calle"],
                'tipo_vialidad'     => $data["vialidad"],
                'referencia'        => $data["referencia"],
                'municipio_citado'  => $data["municipio_citado"],
                'imagen_domicilio1' => $foto1,
                'imagen_domicilio2' => $foto2, 
                'estado_citado'     => $data["estado_citado"],
                'notificacion'      => $data["notificacion"],
                'resulte_responsable' => 'No'
            );

            if(isset($data["rfc"])) $data_insert_bd["rfc"] = $data["rfc"];
            if(isset($data["curp"])) $data_insert_bd["curp"] = $data["curp"];
            if(isset($data["traductor"])) {
                $val = $data["traductor"];
                $requires = ($val === 'Si' || $val === '1' || $val === 1 || $val === 'on' || $val === true);
                $data_insert_bd["traductor"] = $requires ? 1 : 0;
                $data_insert_bd["lenguaje"] = isset($data["lenguaje"]) ? (is_array($data["lenguaje"]) ? ($data["lenguaje"][0] ?? null) : $data["lenguaje"]) : null;
            }
            if(isset($data["interior"])) $data_insert_bd["n_int"] = $data["interior"];
            if(isset($data["calle1"])) $data_insert_bd["calle1"] = $data["calle1"];
            if(isset($data["calle2"])) $data_insert_bd["calle2"] = $data["calle2"];
            if(isset($data["nombre"])) $data_insert_bd["nombre"] = $data["nombre"];
            if(isset($data["primer_apellido"])) $data_insert_bd["primer_apellido"] = $data["primer_apellido"];
            if(isset($data["segundo_apellido"])) $data_insert_bd["segundo_apellido"] = $data["segundo_apellido"];
            
             if (isset($data["tipo"])) {
                $data_insert_bd["tipo_persona"] = $data["tipo"];
                /*if ($data["tipo"] == "Moral" && isset($data["razon"])) {
                    $data_insert_bd["nombre"] = $data["razon"];
                }*/
                if ($data["tipo"] == "Fisica" && isset($data["nombre"])) {
                    $data_insert_bd["nombre"] = $data["nombre"];
                }
            }
            
            SeerCitados::create($data_insert_bd);
            
           /* $existe = SeerCitados::where('id_solicitud', $data['id'])
                    ->where('nombre', $direccionNombre)
                    ->where('resulte_responsable', 'Si')
                    ->exists();*/
            /*if (!$existe) {
                SeerCitados::create($data_insert); // Este usa el $data_insert modificado con el nombre largo
            }*/
        }
        return back()->with('success', 'Citado agregado correctamente, puedes agregar otro o continuar.');
    }
    public function mostrar_noConciliacion($id) {
        $citados = SeerCitados::select('id','nombre','primer_apellido','segundo_apellido')->where('tipo_notificacion', '!=', 'Multa')->where('id_solicitud', $id)->get();

        if ($citados->isEmpty()) {
            return redirect()->back()->with('error', 'No hay constancias para esta solicitud.');
        }
        return response()->json($citados);
    }
    // Genera de manera individual las constancias de no conciliación para cada citado
    public function VerPDFNoConciliacionIndividual($id_citado) {
        $citado = SeerCitados::find($id_citado);
        $solicitud = SeerPerGeneral::find($citado->id_solicitud);
        $solicitante = SeerPerGeneral::join("seer_solicitante", "seer_solicitante.id_solicitud", "=", "seer_general.id")
            ->where("seer_solicitante.id_solicitud", "=", $solicitud->id)
            ->first();
        $conciliador  = User::join("audiencias","audiencias.id_conciliador","=","users.id")
            ->where("audiencias.id_solicitud", "=", $solicitud["id"])
            ->latest('audiencias.created_at')
            ->select('users.name')
            ->first();
        $audiencia = SeerPerGeneral::join("audiencias", "audiencias.id_solicitud", "=", "seer_general.id")
            ->where("audiencias.id_solicitud", "=", $solicitud->id)
            ->latest('audiencias.created_at')
            ->first();
        $municipio = Municipios::find($citado->municipio_citado);
        $municipioEmpresa = $municipio ? $municipio->nombre : 'No definido';
        $estado = Estados::find($citado->estado_citado);
        $estadoEmpresa = $estado ? $estado->nombre : 'No definido';

         //$html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans; font-size:12px;}</style></head><body>';
        $html = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                @page {
                    margin: 100px 50px 80px 50px;
                }
                body {
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>';

        $html .= view('PDF/Solicitudes/NoConciliacion', compact(
            'solicitud', 'conciliador', 'citado', 'audiencia', 'solicitante', 'municipioEmpresa', 'estadoEmpresa'
        ))->render();

        $html .= '
        <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->get_font("Arial", "normal");
                $size = 10;
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                $width = $fontMetrics->get_text_width($text, $font, $size);
                $x = ($pdf->get_width() / 2) - 50;
                $y = $pdf->get_height() - 44;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>';
        $html .= '</body></html>';

        $pdf = \PDF::loadHTML($html)
        ->setPaper('a4', 'portrait')
        ->setOption('isPhpEnabled', true);
            $nombreArchivo = 'No_Conciliacion_'.$citado->nombre.'.pdf';
            $nombreArchivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nombreArchivo); //Elimina los caracteres especiales no permitidos en archivos

        return $pdf->stream($nombreArchivo);
    }

    public function registro_foro_nacional(){
        return view('foroNacional');
    }

    public function foroNacionalregistro(Request $request){
        $data = $request->all();

        $data_insert=array(
            'primer_apellido'   => $data["primero_trabajador"],
            'segundo_apellido'  => $data["segundo_trabajador"],
            'nombre'            => $data["trabajador"],
            'correo'            => $data["email"],
            'telefono'          => $data["telefono"],
            'convesatorio1'     => $data["ocupacion"],
            'lugar'             => $data["lugar"],
            'sexo'              => $data["trabajador_sexo"],
            'estatus'           => "Pendiente",
        );
        ForoNacional::create($data_insert);

        $ultimoRegistro = ForoNacional::latest('id')->first();
        $ultimoId = $ultimoRegistro->id;

        // 2. Envío del correo
        Mail::to($data['email'])->send(new ForoMail($data_insert,$ultimoId));

        return back()->with('success', 'Revisa tu bandeja de entrada para verificar tu folio de registro del Foro Nacional por la Consolidación de la Justicia Laboral en México.'); 
    }
    
    //PDF Notificación de multa cuando es por instructivo
    public function VerPDFMultaInstructivo($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id")
        ->where("audiencias.id_solicitud", "=", $solicitud["id"])->latest('audiencias.created_at')->first();
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();

        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
        $html = view('PDF/Solicitudes/multaNotificacionInstructivo', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','audiencia'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'multaNotificada_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo); 
    }

    //PDF Notificación de multa cuando es No exitosa, se constituye
    public function VerPDFMultaNoExitConstituye($id, $id_solicitud){
        $solicitud = SeerPerGeneral::find($id_solicitud);
        $solicitante  = SeerPerGeneral::join("seer_solicitante","seer_solicitante.id_solicitud","=","seer_general.id");
        $solicitante = $solicitante->where("seer_solicitante.id_solicitud", "=", $solicitud["id"])
        ->first();
        $audiencia  = SeerPerGeneral::join("audiencias","audiencias.id_solicitud","=","seer_general.id")
        ->where("audiencias.id_solicitud", "=", $solicitud["id"])->latest('audiencias.created_at')->first();
        $citado = SeerPerGeneral::join("seer_citados", "seer_citados.id_solicitud", "=", "seer_general.id")
        ->where("seer_citados.id", $id)
        ->first();

        $municipioCitado = null;
        if ($citado && $citado->municipio_citado) {
            $municipio = \App\Models\Municipios::find($citado->municipio_citado);
            $municipioCitado = $municipio ? $municipio->nombre : null;
        }
        $estadoCitado = null;
        if ($citado && $citado->estado_citado) {
            $estado = \App\Models\Estados::find($citado->estado_citado);
            $estadoCitado = $estado ? $estado->nombre : null;
        }
        $id_notificador = $citado->id_notificador;

        $notificador = User::where('id', $id_notificador)
            ->select('name')
            ->first();

        $imagenes = [];
    
        $camposImagen = [
            $citado->documento ?? null,
            $citado->documento1 ?? null,
            $citado->documento2 ?? null,
        ];
        
        foreach ($camposImagen as $img) {    
            if (!$img || $img === 'Sin documento') {
                $imagenes[] = null;
                continue;
            }
        
            $path = storage_path("app/documentos_notificacion/{$img}");
        
            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $imagenes[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            } else {
                $imagenes[] = null;
            }
        }
        $html = view('PDF/Solicitudes/multaNotificacionNExitosaSeConstituye', compact('id', 'solicitud','citado','solicitante','notificador','imagenes','municipioCitado','estadoCitado','audiencia'))->render();

        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true); 

        $nombreArchivo = 'multaNotificada_' . $solicitud->empresa .'.pdf';
        return $pdf->stream($nombreArchivo); 
    }

    public function indexCAN(){
        //cargar la vista  y mandar la
        $user = auth()->user()->load('roles');
        $id = $user->id;
        $userRole = $user->roles->pluck('name')->first(); // Tomamos el primer rol principal

        return view('estadisticas.estadisticaUsuario',compact('userRole'));
    }

    public function generaReporteUsuario(Request $request){
        $data = $request->all();
        $user = auth()->user()->load('roles');
        $id = $user->id;

        $fecha_inicial = $data["fecha_inicial"];
        $fecha_final   = $data["fecha_final"];
        $tipo_reporte  = $data["tipo_reporte"];

        if($tipo_reporte == "AudienciaConciliador"){
            return Excel::download(new AudienciasPORConciliadorExport($fecha_inicial, $fecha_final, $id), 'audienciasConciliador.xlsx');
        }else if($tipo_reporte == "ProductividadConciliador"){
            $total_cumplimiento = 0;
            //Auxiliares
                $solicitudes = DB::table('seer_general')
                    ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->join('users','users.id', "=", 'seer_general.user_id')
                    ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                    ->where('seer_general.user_id', "=" , $id)
                    ->select(
                        'users.id as user_id', 
                        'users.name',
                        DB::raw('COUNT(DISTINCT seer_general.id) as solicitudes'),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as incompetencia"),
                        
                        // Totales de Audiencia (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.id END) as cumplimientoAudiencia"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMonto"),
                        
                        // Totales de Audiencia (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoAudienciaPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMontPagado"),

                        // Totales de Ratificación vía Pago (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.id END) as cumplimientoRatificacion"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMonto"),

                        // Totales de Ratificación vía Pago (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoRatificacionPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMontoPagado")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get()
                    ->keyBy('user_id');

                // 2. Consulta de Turnos (La parte de Ratificaciones que viene de otra tabla)
                $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->where('turnos.user_id','=',$id)
                    ->select(
                        'turnos.user_id',
                        DB::raw('COUNT(turnos.id) as ratificaciones'),
                        DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                    )
                    ->groupBy('turnos.user_id')
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $turno = $dataTurnos->get($id);
                    $solicitud->ratificaciones = $turno ? $turno->ratificaciones : 0;
                    $solicitud->ratificacionesMonto = $turno ? $turno->ratificacionesMonto : 0;
                }
                
                $cumplimientos = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    // Unimos ambas tablas con Left Join
                    ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->leftJoin('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    
                    // Unimos la tabla users a través de ambas posibilidades
                    ->leftJoin('users as u_general', 'u_general.id', '=', 'seer_general.user_id')
                    ->leftJoin('users as u_turnos', 'u_turnos.id', '=', 'turnos.user_id')
                    ->select(
                        // Usamos COALESCE para tomar el primer ID de usuario que no sea nulo
                        DB::raw('COALESCE(u_general.id, u_turnos.id) as user_id'),
                        DB::raw('COALESCE(u_general.name, u_turnos.name) as user_name'),
                        'pago_solicitud.delegacion',
                        DB::raw('COUNT(pago_solicitud.id) as cumplimientos')
                    )
                    // Agrupamos por los campos calculados
                    ->groupBy('user_id', 'user_name', 'pago_solicitud.delegacion')
                    // Filtramos para asegurar que el pago pertenezca a una de las dos tablas
                    ->where(function($q) {
                        $q->whereNotNull('seer_general.id')
                        ->orWhereNotNull('turnos.id');
                    })
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $cumplimiento = $cumplimientos->get($solicitud->user_id);
                    $solicitud->cumplimientos = $cumplimiento ? $cumplimiento->cumplimientos : 0;
                    $total_cumplimiento++;
                }

            //Audiencias
                $audiencias = DB::table('seer_general')
                    ->join('users','users.id', "=", 'seer_general.user_id')
                    ->join('audiencias', 'seer_general.id', '=', 'audiencias.id_solicitud')
                    // Left Joins para traer datos de otras tablas sin perder registros de la principal
                    ->leftJoin('pago_solicitud', function($join) {
                        $join->on('seer_general.id', '=', 'pago_solicitud.id_solicitud')
                            ->whereIN('pago_solicitud.tipo_pago', ['Conciliador','Audiencia']);
                    })
                    ->leftJoin('seer_citados', function($join) {
                        $join->on('seer_general.id', '=', 'seer_citados.id_solicitud')
                            ->where('seer_citados.tipo_notificacion', '=', 'Multa');
                    })
                    ->whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    ->where('seer_general.user_id', "=" , $id)
                    ->select(
                        'users.id as user_id',
                        'users.name',
                        // Conteo base de audiencias
                        DB::raw('COUNT(DISTINCT audiencias.id) as audienencias_programadas'),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion','No conciliacion','Reagendada','Archivada','No conciliacion reagendada','Reinstalacion','Desistimiento',
                        'Archivada en Audiencia') THEN audiencias.id END) as audienencias_celebradas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Conciliacion','Reinstalacion') THEN audiencias.id END) as convenios"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Archivada','Archivada en Audiencia') THEN audiencias.id END) as achivada"),
                        DB::raw("COUNT(DISTINCT CASE WHEN audiencias.estatus IN ('Incompetencia') THEN audiencias.id END) as incompetencia"),

                        // Cumplimientos y Montos (Pagos)
                        DB::raw('COUNT(DISTINCT pago_solicitud.id) as cumplimientoAudiencia'),
                        DB::raw('SUM(pago_solicitud.monto) as cumplimientoAudienciaMonto'),
                        
                        // Estatus específicos (Convenio, Falta de Interés, Incompetencia)
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus IN ('Concluida', 'Conciliacion') THEN seer_general.id END) as cumplimientoAudienciaConvenio"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Archivada' THEN seer_general.id END) as cumplimientoAudienciaFalta"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as cumplimientoAudienciaIncompetencia"),
                        
                        // Multas y Virtuales
                        DB::raw("COUNT(DISTINCT seer_citados.id) as multas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.tipo = 'Virtual' THEN seer_general.id END) as audiencias_virtuales"),

                        // Dentro del select de la consulta anterior:
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 1 THEN seer_general.id END) as una_audiencia"),
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) = 2 THEN seer_general.id END) as dos_audiencias"),
                        DB::raw("COUNT(DISTINCT CASE WHEN (SELECT COUNT(*) FROM audiencias a WHERE a.id_solicitud = seer_general.id) >= 3 THEN seer_general.id END) as tres_audiencias")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get();
            
            
            $notificaciones = array();
                
                
            
                // Cálculo de efectividad global para el encabezado del reporte
                $total_gral_solicitudes = $solicitudes->sum('solicitudes');
                $total_gral_confirmadas = $solicitudes->sum('confirmadas');
                $porcentaje_confirmacion = ($total_gral_solicitudes > 0) 
                    ? ($total_gral_confirmadas / $total_gral_solicitudes) * 100 
                    : 0;

            $pdf = \PDF::loadView('PDF/Estadisticas/reporteGeneralConciliador', compact(
                    'fecha_inicial',
                    'fecha_final',
                    'solicitudes',
                    'audiencias',
                    'notificaciones',
                    'porcentaje_confirmacion',
                    'total_cumplimiento'
                ));
            $pdf->setPaper('legal', 'landscape');
            return $pdf->stream('Reporte_General.pdf');
        }
        else if($tipo_reporte == "ProductividadAuxiliar"){
            $total_cumplimiento = 0;
            $solicitudes = DB::table('seer_general')
                    ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->join('users','users.id', "=", 'seer_general.user_id')
                    ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
                    ->where('seer_general.user_id', "=" , $id)
                    ->select(
                        'users.id as user_id', 
                        'users.name',
                        DB::raw('COUNT(DISTINCT seer_general.id) as solicitudes'),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as incompetencia"),
                        
                        // Totales de Audiencia (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.id END) as cumplimientoAudiencia"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMonto"),
                        
                        // Totales de Audiencia (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoAudienciaPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMontPagado"),

                        // Totales de Ratificación vía Pago (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.id END) as cumplimientoRatificacion"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMonto"),

                        // Totales de Ratificación vía Pago (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoRatificacionPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMontoPagado")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get()
                    ->keyBy('user_id');

            $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->where('turnos.user_id','=',$id)
                    ->select(
                        'turnos.user_id',
                        DB::raw('COUNT(turnos.id) as ratificaciones'),
                        DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                    )
                    ->groupBy('turnos.user_id')
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $turno = $dataTurnos->get($id);
                    $solicitud->ratificaciones = $turno ? $turno->ratificaciones : 0;
                    $solicitud->ratificacionesMonto = $turno ? $turno->ratificacionesMonto : 0;
                }
                
            $cumplimientos = Pagos::whereBetween('pago_solicitud.fecha', [$fecha_inicial, $fecha_final])
                    // Unimos ambas tablas con Left Join
                    ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
                    ->leftJoin('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    
                    // Unimos la tabla users a través de ambas posibilidades
                    ->leftJoin('users as u_general', 'u_general.id', '=', 'seer_general.user_id')
                    ->leftJoin('users as u_turnos', 'u_turnos.id', '=', 'turnos.user_id')
                    ->select(
                        // Usamos COALESCE para tomar el primer ID de usuario que no sea nulo
                        DB::raw('COALESCE(u_general.id, u_turnos.id) as user_id'),
                        DB::raw('COALESCE(u_general.name, u_turnos.name) as user_name'),
                        'pago_solicitud.delegacion',
                        DB::raw('COUNT(pago_solicitud.id) as cumplimientos')
                    )
                    // Agrupamos por los campos calculados
                    ->groupBy('user_id', 'user_name', 'pago_solicitud.delegacion')
                    // Filtramos para asegurar que el pago pertenezca a una de las dos tablas
                    ->where(function($q) {
                        $q->whereNotNull('seer_general.id')
                        ->orWhereNotNull('turnos.id');
                    })
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $cumplimiento = $cumplimientos->get($solicitud->user_id);
                    $solicitud->cumplimientos = $cumplimiento ? $cumplimiento->cumplimientos : 0;
                    $total_cumplimiento++;
                }
            // Cálculo de efectividad global para el encabezado del reporte
                $total_gral_solicitudes = $solicitudes->sum('solicitudes');
                $total_gral_confirmadas = $solicitudes->sum('confirmadas');
                $porcentaje_confirmacion = ($total_gral_solicitudes > 0) 
                    ? ($total_gral_confirmadas / $total_gral_solicitudes) * 100 
                    : 0;

            $pdf = \PDF::loadView('PDF/Estadisticas/reporteGeneralAuxiliar', compact(
                    'fecha_inicial',
                    'fecha_final',
                    'solicitudes',
                    'porcentaje_confirmacion',
                    'total_cumplimiento'
                ));
            $pdf->setPaper('legal', 'landscape');
            return $pdf->stream('Reporte_General.pdf');
        }
        else if($tipo_reporte == "ProductividadNotificador"){
                $notificaciones = SeerPerGeneral::whereBetween('seer_citados.fecha', [$fecha_inicial, $fecha_final])
                    ->join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
                    ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
                    ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
                    //->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
                    ->join('municipios','municipios.id','seer_citados.municipio_citado')
                    ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
                    ->where(function($query) {
                        $query->where('seer_general.incidencia', 0)
                        ->orWhereNull('seer_general.incidencia');
                    })
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        if ($sede === "TodosDelegado") {
                            $id = auth()->user()->id;
                            $user = User::find($id);
                            $sedeUsuario = $user->delegacion;
            
                            if($sedeUsuario == "Morelia"){
                                $delegaciones = ['Morelia', 'Zitácuaro'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Uruapan"){
                                $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                            else if($sedeUsuario == "Zamora"){
                                $delegaciones = ['Zamora', 'Sahuayo'];
                                return $q->whereIn('seer_general.delegacion', $delegaciones);
                            }
                        }
                        return $q->where("seer_general.delegacion", $sede);
                    })
                    //->when($this->auxiliar !== "Todos", function ($q) { return $q->where('seer_general.user_id', $this->auxiliar); })
                    //->when($this->notificador !== "Todos", function ($q) { return $q->where('seer_citados.id_notificador', $this->notificador); })
                    ->select(
                        'notificador.id as user_id', 
                        'notificador.name',
                        // Total base
                        DB::raw('COUNT(seer_citados.id) as Todas_notificaciones'),
                        
                        // Conteos condicionales por estatus
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Notificada' THEN 1 ELSE 0 END) as notificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No notificada' THEN 1 ELSE 0 END) as notificacion_Nonotificada"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Pendiente' THEN 1 ELSE 0 END) as notificacion_pendientes"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Exhorto' THEN 1 ELSE 0 END) as notificacion_exhortos"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa se constituye' THEN 1 ELSE 0 END) as notificacion_NESC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'No exitosa no se constituye' THEN 1 ELSE 0 END) as notificacion_NENSC"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Finalizado exitosamente' THEN 1 ELSE 0 END) as exitosamente"),
                        DB::raw("SUM(CASE WHEN seer_citados.estatus = 'Recibe pero no firma' THEN 1 ELSE 0 END) as firma"),
                    )
                    ->groupBy('notificador.id', 'notificador.name')
                    ->get();
                
                
            
            // Cálculo de efectividad global para el encabezado del reporte
            $total_gral_solicitudes = $solicitudes->sum('solicitudes');
            $total_gral_confirmadas = $solicitudes->sum('confirmadas');
            $porcentaje_confirmacion = ($total_gral_solicitudes > 0) 
                ? ($total_gral_confirmadas / $total_gral_solicitudes) * 100 
                : 0;

            $pdf = \PDF::loadView('PDF/Estadisticas/reporteGeneralNotificador', compact(
                'fecha_inicial',
                'fecha_final',
                'notificaciones',
                'porcentaje_confirmacion',
                'total_cumplimiento'
            ));
            $pdf->setPaper('legal', 'landscape');
            return $pdf->stream('Reporte_General.pdf');
        }
    }
    public function edicion_audienciaConcluida($id, $audiencia_id){ 
        $solicitudOriginal = SeerPerGeneral::findOrFail($id);
        //$audienciaAEditar = Audiencias::findOrFail($audiencia_id);
        
        /*$datosConciliador = SeerPerConciliador::where('id_solicitud', $id)
                            ->where('audiencia_id', $audiencia_id)
                            ->first();  */
        $authId = auth()->user()->id; 
        $user = User::find($authId);
        $sede = $user->delegacion;
        $conciliadorId = $authId;

        $audienciaAEditar = Audiencias::where('id_solicitud', $id)
        ->whereIn('estatus', [
            'Conciliacion',
            'No conciliacion',
            'Reinstalacion'
        ])
        ->orderByDesc('fecha')
        ->orderByDesc('hora')
        ->first();
        $manifestaciones = null;

        if ($audienciaAEditar) {
            $manifestaciones = SeerPerConciliador::where('id_solicitud', $id)
                ->latest('id')
                ->first();
        }

        $conceptos   = Concepto::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();
        $pagos       = Pagos::where('id_solicitud', $id)->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();

        $montoPena = $audienciaAEditar->pena_convencional ?? 0;
        $direccion_convenio = $audienciaAEditar->direccion_convenio ?? '';
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        
        $totalPrestaciones = $conceptos->sum('monto');
        $totalDeducciones = $deducciones->sum('monto');
        $pagoTotal = $totalPrestaciones - $totalDeducciones;
        $raw_fecha = Audiencias::where('id_solicitud', $id)->latest('id')->value('fecha');
        $raw_hora  = Audiencias::where('id_solicitud', $id)->latest('id')->value('hora');

        $audiencia_fecha = Carbon::parse($raw_fecha)->format('Y-m-d');
        $audiencia_hora = Carbon::parse($raw_hora)->format('H:i:s');
        return view('/audiencias/edita_audienciaConcluida', compact('solicitudOriginal','audienciaAEditar','conciliadorId','audiencia_fecha','audiencia_hora',
            'conceptos','pagos','deducciones','sede','pagoTotal','montoPena','direccion_convenio','solicitante','id','audiencia_id','manifestaciones'
        ));
    }
    public function Guarda_edicion_audienciaConcluida(Request $request){
        $data = $request->all();
        $id_solicitud = $data["id"];
        $audiencia_id = $data["audiencia_id"]; 
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        //$sede = $user->delegacion;
        $conteo = 0;
        $montoTotal = 0;
        $solicitudOriginal = SeerPerGeneral::find($id_solicitud);
        $sede_a_guardar = $solicitudOriginal->delegacion ?? $user->delegacion;
       //Deducciones
        if (isset($data["monto_deduccion"])) {
            foreach ($data["monto_deduccion"] as $i => $monto) {
                Deducciones::create([
                    'id_solicitud' => $id_solicitud,
                    'monto'        => $monto,
                    'descripcion'  => $data["descripcion_deduccion"][$i],
                    'tipo_pago'    => "Audiencia"
                ]);
            }
        }
        //Prestaciones
        if(isset($data["tipo_pago"])){
            $tiposPago = $data["tipo_pago"];
            $cont = count($data["monto_pago"]);
            $otrasPrestaciones = $data["otra_prestacion"] ?? [];
            for($i = 0; $i < $cont; $i++) {
                $descripcion = $tiposPago[$i];
                if ($descripcion === "Otras"
                    && isset($otrasPrestaciones[$i])
                    && !empty(trim($otrasPrestaciones[$i]))) {
                    $descripcion = trim($otrasPrestaciones[$i]);
                }
                Concepto::firstOrCreate([
                        'id_solicitud' => $data["id"],
                        'descripcion'  => $descripcion,
                        'tipo_pago'    => 'Audiencia',],
                        ['monto' => $data["monto_pago"][$i],]
                );
            }
        }
        $existenConceptos = Concepto::where('id_solicitud', $id_solicitud)
        ->where('tipo_pago', 'Audiencia')
        ->exists();
        if(!isset($data["tipo_pago"]) && !$existenConceptos && $solicitudOriginal->estatus != "No conciliacion"){
            return back()->withErrors('Debes agregar por lo menos un concepto de pago.');
        }
        
        //Pagos/Cumplimientos
        if(isset($data["dias_pagos"])){
            $conteo = count($data["dias_pagos"]);
            for($i = 0; $i < $conteo; $i++) {
                //Solo para el primer caso voy a seleccionar el tipo de pago
                if($i == 0){
                    $data_pagos = [
                        'id_solicitud'  => $data["id"],
                        'fecha'         => $data["dias_pagos"][$i],
                        'hora'          => $data["hora_pagos"][$i], 
                        'monto'         => $data["monto_pagos"][$i], 
                        'descripcion'   => $data["descripcion_pagos"][$i],
                        'estatus'       => "Pendiente",
                        'delegacion'    => $sede_a_guardar,
                        'tipo_pago'     => $data["tipo_pagoAgenda"][$i],
                    ];
                    $monto = $monto + $data["monto_pagos"][$i];
                    Pagos::create($data_pagos);
                }else{
                    $data_pagos = [
                        'id_solicitud'  => $data["id"],
                        'fecha'         => $data["dias_pagos"][$i],
                        'hora'          => $data["hora_pagos"][$i], 
                        'monto'         => $data["monto_pagos"][$i], 
                        'descripcion'   => $data["descripcion_pagos"][$i],
                        'estatus'       => "Pendiente",
                        'delegacion'    => $sede_a_guardar,
                        'tipo_pago'     => "Audiencia",
                    ];
                    $monto = $monto + $data["monto_pagos"][$i];
                    Pagos::create($data_pagos);
                }
            }
        }
       
        $existenPagos = Pagos::where('id_solicitud', $id_solicitud)
        ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
        ->exists();
        if(!isset($data["dias_pagos"]) && !$existenPagos && $solicitudOriginal->estatus != "No conciliacion"){
            return back()->withErrors('Debes agregar por lo menos una fecha de pago.');
        }
       
        $pena_convencional =  $data['pena_convencional'] ?? null;
        $direccion_convenio = $data['direccion_convenio'] ?? null;
        //$numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
            ->orderBy('id_solicitud','desc')
            ->update([
                /*'numero_audiencia'  =>  $numAudiencia+1,
                'folio_audiencia'   =>  $numero_audiencia[0],*/
                'pena_convencional'  =>  $data['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $data['direccion_convenio'] ?? null,
            ]);
        SeerPerConciliador::where('id_solicitud', $id_solicitud)
            //->where('audiencia_id', $audiencia_id)
            ->update([
                'resolicion_primera'       => $data["primera"],
                'resolicion_justificacion' => $data["justificacion"],
                'resolicion_segunda'       => $data["segunda"],
                'vacaciones'               => $data["vacaciones"] ?? 0,
                'aguinaldo'                => $data["aguinaldo"] ?? 0,
                'otros'                    => $data["otros"] ?? 0,
                'horario'                  => $data["horario"],
                'comida'                   => $data["comida"],
                'monto'                    => $montoTotal,
            ]);
        SeerPerGeneral::where('id', $id_solicitud)
            ->update([
                'observaciones'       => $data["observaciones"],
            ]);

        // renombrar pagos automaticamente
        $pagosActuales = Pagos::where('id_solicitud', $id_solicitud)
            ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
            ->orderBy('id', 'asc')
            ->get();
        $totalPagos = $pagosActuales->count();
        if ($totalPagos == 1) {
            $pagosActuales[0]->update([
                'descripcion' => 'Cumplimiento total de convenio'
            ]);
        } elseif ($totalPagos > 1) {
            foreach ($pagosActuales as $index => $pago) {
                $pago->update([
                    'descripcion' => 'Parcialidad ' . ($index + 1)
                ]);
            }
        }
        return redirect()->route('todas_audiencias')->with('success', 'Cambios guardados correctamente.');
    }
    /*public function edicion_solConcluida($id, $audiencia_id){ 
        $solicitudOriginal = SeerPerGeneral::findOrFail($id);
        $audienciaAEditar = Audiencias::findOrFail($audiencia_id);
        
        $datosConciliador = SeerPerConciliador::where('id_solicitud', $id)
                            ->where('audiencia_id', $audiencia_id)
                            ->first();  
        $authId = auth()->user()->id; 
        $user = User::find($authId);
        $sede = $user->delegacion;
        $conciliadorId = $authId;
    
        $conceptos   = Concepto::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();
        $pagos       = Pagos::where('id_solicitud', $id)->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])->get();
        $deducciones = Deducciones::where('id_solicitud', $id)->where('tipo_pago', 'Audiencia')->get();

        $montoPena = $audienciaAEditar->pena_convencional ?? 0;
        $direccion_convenio = $audienciaAEditar->direccion_convenio ?? '';
        $solicitante = SeerSolicitante::where('id_solicitud', $id)->first();
        
        $totalPrestaciones = $conceptos->sum('monto');
        $totalDeducciones = $deducciones->sum('monto');
        $pagoTotal = $totalPrestaciones - $totalDeducciones;
        $raw_fecha = Audiencias::where('id_solicitud', $id)->latest('id')->value('fecha');
        $raw_hora  = Audiencias::where('id_solicitud', $id)->latest('id')->value('hora');

        $audiencia_fecha = Carbon::parse($raw_fecha)->format('Y-m-d');
        $audiencia_hora = Carbon::parse($raw_hora)->format('H:i:s');
        return view('/solicitudes/edita_solConcluida', compact('solicitudOriginal','audienciaAEditar','conciliadorId','audiencia_fecha','audiencia_hora',
            'datosConciliador','conceptos','pagos','deducciones','sede','pagoTotal','montoPena','direccion_convenio','solicitante','id','audiencia_id'
        ));
    }
    public function Guarda_edicion_solConcluida(Request $request){
        $data = $request->all();
        $id_solicitud = $data["id"];
        $audiencia_id = $data["audiencia_id"]; 
        $monto = 0;
        $fecha_actual = date('y-m-d');
        $id = auth()->user()->id;
        $user = User::find($id);
        //$sede = $user->delegacion;
        $conteo = 0;
        $montoTotal = 0;
        $solicitudOriginal = SeerPerGeneral::find($id_solicitud);
        $sede_a_guardar = $solicitudOriginal->delegacion ?? $user->delegacion;
       //Deducciones
        if (isset($data["monto_deduccion"])) {
            foreach ($data["monto_deduccion"] as $i => $monto) {
                Deducciones::create([
                    'id_solicitud' => $id_solicitud,
                    'monto'        => $monto,
                    'descripcion'  => $data["descripcion_deduccion"][$i],
                    'tipo_pago'    => "Audiencia"
                ]);
            }
        }
        //Prestaciones
        if(isset($data["tipo_pago"])){
            $tiposPago = $data["tipo_pago"];
            $cont = count($data["monto_pago"]);
            $otrasPrestaciones = $data["otra_prestacion"] ?? [];
            for($i = 0; $i < $cont; $i++) {
                $descripcion = $tiposPago[$i];
                if ($descripcion === "Otras"
                    && isset($otrasPrestaciones[$i])
                    && !empty(trim($otrasPrestaciones[$i]))) {
                    $descripcion = trim($otrasPrestaciones[$i]);
                }
                Concepto::firstOrCreate([
                        'id_solicitud' => $data["id"],
                        'descripcion'  => $descripcion,
                        'tipo_pago'    => 'Audiencia',],
                        ['monto' => $data["monto_pago"][$i],]
                );
            }
        }
        $existenConceptos = Concepto::where('id_solicitud', $id_solicitud)
        ->where('tipo_pago', 'Audiencia')
        ->exists();
        if(!isset($data["tipo_pago"]) && !$existenConceptos && $solicitudOriginal->estatus != "No conciliacion"){
            return back()->withErrors('Debes agregar por lo menos un concepto de pago.');
        }
        
        //Pagos/Cumplimientos
        if(isset($data["dias_pagos"])){
            $conteo = count($data["dias_pagos"]);
            for($i = 0; $i < $conteo; $i++) {
                //Solo para el primer caso voy a seleccionar el tipo de pago
                if($i == 0){
                    $data_pagos = [
                        'id_solicitud'  => $data["id"],
                        'fecha'         => $data["dias_pagos"][$i],
                        'hora'          => $data["hora_pagos"][$i], 
                        'monto'         => $data["monto_pagos"][$i], 
                        'descripcion'   => $data["descripcion_pagos"][$i],
                        'estatus'       => "Pendiente",
                        'delegacion'    => $sede_a_guardar,
                        'tipo_pago'     => $data["tipo_pagoAgenda"][$i],
                    ];
                    $monto = $monto + $data["monto_pagos"][$i];
                    Pagos::create($data_pagos);
                }else{
                    $data_pagos = [
                        'id_solicitud'  => $data["id"],
                        'fecha'         => $data["dias_pagos"][$i],
                        'hora'          => $data["hora_pagos"][$i], 
                        'monto'         => $data["monto_pagos"][$i], 
                        'descripcion'   => $data["descripcion_pagos"][$i],
                        'estatus'       => "Pendiente",
                        'delegacion'    => $sede_a_guardar,
                        'tipo_pago'     => "Audiencia",
                    ];
                    $monto = $monto + $data["monto_pagos"][$i];
                    Pagos::create($data_pagos);
                }
            }
        }
       
        $existenPagos = Pagos::where('id_solicitud', $id_solicitud)
        ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
        ->exists();
        if(!isset($data["dias_pagos"]) && !$existenPagos && $solicitudOriginal->estatus != "No conciliacion"){
            return back()->withErrors('Debes agregar por lo menos una fecha de pago.');
        }
       
        $pena_convencional =  $data['pena_convencional'] ?? null;
        $direccion_convenio = $data['direccion_convenio'] ?? null;
        //$numAudiencia = Audiencias::where('id_solicitud',$data["id"])->count();
        Audiencias::where('id_solicitud',$data["id"])
            ->orderBy('id_solicitud','desc')
            ->update([
                /*'numero_audiencia'  =>  $numAudiencia+1,
                'folio_audiencia'   =>  $numero_audiencia[0],*/
        /*        'pena_convencional'  =>  $data['pena_convencional'] ?? null,
                'direccion_convenio'    =>  $data['direccion_convenio'] ?? null,
            ]);
        SeerPerConciliador::where('id_solicitud', $id_solicitud)
            ->where('audiencia_id', $audiencia_id)
            ->update([
                'resolicion_primera'       => $data["primera"],
                'resolicion_justificacion' => $data["justificacion"],
                'resolicion_segunda'       => $data["segunda"],
                'vacaciones'               => $data["vacaciones"] ?? 0,
                'aguinaldo'                => $data["aguinaldo"] ?? 0,
                'otros'                    => $data["otros"] ?? 0,
                'horario'                  => $data["horario"],
                'comida'                   => $data["comida"],
                'monto'                    => $montoTotal,
            ]);
        SeerPerGeneral::where('id', $id_solicitud)
            ->update([
                'observaciones'       => $data["observaciones"],
            ]);
        
        // renombrar pagos automaticamente
        $pagosActuales = Pagos::where('id_solicitud', $id_solicitud)
            ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
            ->orderBy('id', 'asc')
            ->get();
        $totalPagos = $pagosActuales->count();
        if ($totalPagos == 1) {
            $pagosActuales[0]->update([
                'descripcion' => 'Cumplimiento total de convenio'
            ]);
        } elseif ($totalPagos > 1) {
            foreach ($pagosActuales as $index => $pago) {
                $pago->update([
                    'descripcion' => 'Parcialidad ' . ($index + 1)
                ]);
            }
        }
        return redirect()->route('todas_solicitudes')->with('success', 'Cambios guardados correctamente.');
    }*/

    public function plantillas_index(){
        return view('plantillas.index');
    }

    public function plantillas_ratificaciones(){
        return view('plantillas.plantillas_solicitudes');
    }

    private function inicialesDeSeerGeneral(?SeerPerGeneral $solicitud): string
    {
        if (!$solicitud || empty($solicitud->user_id)) {
            return '';
        }

        $usuario = User::select('id', 'name')->find($solicitud->user_id);
        if (!$usuario || trim((string) $usuario->name) === '') {
            return '';
        }

        $partes = preg_split('/\s+/u', trim((string) $usuario->name), -1, PREG_SPLIT_NO_EMPTY);
        if (!$partes) {
            return '';
        }

        $iniciales = '';
        foreach ($partes as $parte) {
            $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
        }

        return mb_strtolower($iniciales, 'UTF-8');
    }

    private function etiquetaDelegacionSeer(?string $delegacion): string
    {
        $delegacion = trim((string) $delegacion);
        if ($delegacion === '') {
            return '';
        }

        $map = [
            'Morelia' => 'DRM',
            'Uruapan' => 'DRU',
            'Zamora' => 'DRZ',

            // Oficinas alternas
            'Zitácuaro' => 'DRM - OAZ',
            'Sahuayo' => 'DRZ - OAS',
            'Lázaro Cárdenas' => 'DRU - OAL',
        ];

        $codigo = $map[$delegacion] ?? '';
        return $codigo !== '' ? "* {$codigo}" : '';
    }

    private function antefirmaDesdePagoSolicitud(?int $userId, ?string $delegacion): array
    {
        if (empty($userId)) {
            return [
                'inicialesConcluye' => '',
                'etiquetaIniciales' => '',
            ];
        }

        $usuario = User::select('id', 'name')->find($userId);
        if (!$usuario || trim((string) $usuario->name) === '') {
            return [
                'inicialesConcluye' => '',
                'etiquetaIniciales' => '',
            ];
        }

        $partes = preg_split('/\s+/u', trim((string) $usuario->name), -1, PREG_SPLIT_NO_EMPTY);
        if (!$partes) {
            return [
                'inicialesConcluye' => '',
                'etiquetaIniciales' => '',
            ];
        }

        $iniciales = '';
        foreach ($partes as $parte) {
            $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
        }

        return [
            'inicialesConcluye' => mb_strtolower($iniciales, 'UTF-8'),
            'etiquetaIniciales' => $this->etiquetaDelegacionSeer($delegacion),
        ];
    }
}