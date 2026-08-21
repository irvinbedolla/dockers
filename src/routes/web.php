<?php

use App\Http\Controllers\Apps\PermissionManagementController;
use App\Http\Controllers\Apps\RoleManagementController;
use App\Http\Controllers\Apps\UserManagementController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PDFController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PoderController;
use App\Http\Controllers\CapacitacionController;
use App\Http\Controllers\MiscapacitacionController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\SeerController;
use App\Http\Controllers\TurnosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\CitaDireccionController;
use App\Http\Controllers\CorreosController;
use App\Http\Controllers\ConciliadoresController;
use App\Http\Controllers\AdministracionController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SeerPerGeneral;
use App\Http\Controllers\IncidenciasController;
use App\Http\Controllers\IncidenciasBusquedaController;
use App\Http\Controllers\AsistenciaController;


/*
|--------------------------------------------------------------------------
| Web Públicas
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return redirect('/');
});
Route::get('/',                         [AuthenticatedSessionController::class, 'create'])->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/',                         [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login',                    [AuthenticatedSessionController::class, 'store'])->name('login.post'); 
});

Route::get('/debug-sesion', function () {
    return response()->json([
        '¿Usuario Autenticado?' => Auth::check() ? 'SÍ' : 'NO',
        'ID de Usuario'         => Auth::id() ?? 'Ninguno',
        'Datos del Usuario'     => Auth::user() ?? 'Ninguno',
        'ID de la Sesión actual'=> Session::getId(),
        'Todos los datos en la sesión' => Session::all(),
    ]);
});


// Solicitudes en Línea e Incio de Flujo Público
Route::get('inicioSolicitud',                       [SeerController::class, 'solicitudesLinea'])->name('solicitudEnLinea');
Route::get('tipoIndustria/{tipo_solicitud}',        [SeerController::class, 'Industrias'])->name('solicitud.industria');
Route::get('registro',                              [SeerController::class, 'RTemportal'])->name('PreRegistro');
Route::post('registro_solicitud',                   [SeerController::class, 'GuardarRTemportal'])->name('guardar_registro_solicitud');
Route::get('/registro_tercer_encuentro',            [SeerController::class, 'registro_tercer_encuentro'])->name('registro_tercer_encuentro');
Route::post('/registro_tercer_encuentro/guardar',   [SeerController::class, 'tercer_encuentro_registro'])->name('tercer_encuentro_registro');
Route::get('GeneraConstancia',                      [SeerController::class, 'genera_constancia']);
Route::post('/avisos',                              [SeerController::class, 'aviso'])->name('aviso');
Route::get('/solicitud-completada',                 [SeerController::class, 'solicitud_completada'])->name('solicitud.completada');

Route::get('/RegistroForo',                         [SeerController::class, 'registro_foro_nacional'])->name('registro_foro_nacional');
Route::post('/ForoNacional/guardar',                [SeerController::class, 'foroNacionalregistro'])->name('foroNacionalregistro');
Route::post('/chat/crear',                          [Controller::class, 'store_chat'])->name('RespuestasChat.store');
Route::get('chat',                                  [Controller::class, 'chats'])->name('chat');
Route::post('/chat/crearUno/',                      [Controller::class, 'storeUno'])->name('RespuestasChat.storeUno');

// Pantallas de Delegación (Acceso General Informativo)
Route::get('/pantallaMorelia',                      [HomeController::class, 'pantallaMorelia']);
Route::get('/pantallaUruapan',                      [HomeController::class, 'pantallaUruapan']);
Route::get('/pantallaZamora',                       [HomeController::class, 'pantallaZamora']);

// Creación de Poderes y Citas por el Público
Route::get('/poder-crear',                          [PoderController::class, 'registro'])->name('poder-crear');
Route::get('/poder-guardar',                        [PoderController::class, 'show'])->name('poder');
Route::post('/poderes/publico',                     [PoderController::class, 'publico'])->name('poderes.publico');
Route::get('/generarCita',                          [HomeController::class, 'citas'])->name('citas');
Route::get('/generarCitaExito',                     [HomeController::class, 'citas_exito'])->name('citas_exito');
Route::post('/turnos_guardar',                      [HomeController::class, 'turnos_publico'])->name('turnos_publico'); 
Route::get('citas',                                 [TurnosController::class, 'create_publico'])->name('create_cita');
Route::post('/citas/store_publico',                 [TurnosController::class, 'store_publico'])->name('turnos.publico');
Route::get('/validar_folio_abogado/{folio}',        [TurnosController::class, 'validarFolio'])->name('validar_folio_abogado');
Route::get('AgendaRatificacion',                    [TurnosController::class, 'create_ratiMultiple'])->name('create_cita-12');
Route::post('/citas/storeRatificacion',             [TurnosController::class, 'guardarRatificacion'])->name('guardarRatificacion');

// Flujo dinámico de Solicitudes (Trabajador / Patronal)
Route::get('Patronal/{tipo_solicitud}',             [SeerController::class, 'patron'])->name('solicitud_patron');
Route::post('guardar_patronal',                     [SeerController::class, 'solicitud_patronal'])->name('parte1Patronal');
Route::get('solicitud_continuar',                   [SeerController::class, 'vista_solicitanteP'])->name('solicitantePatronal.ver');
Route::post('solicitante_patronal',                 [SeerController::class, 'inserta_solicitanteP'])->name('solicitantePatronal');
Route::get('/agrega_citadoP/{id}',                  [SeerController::class, 'vista_citadoPatronal'])->name('agregar_citadoPatronal'); 
Route::post('/agrega_citadoP',                      [SeerController::class, 'guardar_citadoPatronal'])->name('seer.citadosPatronal');
Route::get('tipoIndustriaP/{tipo_solicitud}',       [SeerController::class, 'Industrias_p'])->name('solicitud.industria_p');
Route::get('Trabajador/{tipo_solicitud}',           [SeerController::class, 'trabajador'])->name('solicitud_trabajador');
Route::post('Agregar_solictante',                   [SeerController::class, 'solicitud_parte1'])->name('parte1');
Route::get('solicitud_continuar',                   [SeerController::class, 'vista_parte2'])->name('parte2.ver');
Route::post('solicitud_solicitante',                [SeerController::class, 'solicitud_parte2'])->name('parte2');
Route::get('vista_solicitante/{id}',                [SeerController::class, 'vista_solicitante'])->name('solicitante');
Route::post('/delegacion/{municipioId}',            [SeerController::class, 'DelegacionPorMunicipio']);
Route::get('/solicitudes/limite-diario',            [SeerController::class, 'check_limite_diario'])->name('solicitudes.check_limite_diario');
Route::get('/munSolicitante/{id}',                  [SeerController::class, 'obtenerMunicipio']);
Route::get('/munCitado/{id}',                       [SeerController::class, 'obtenerMunicipio']);
Route::get('/agrega_citado/{id}',                   [SeerController::class, 'vista_citado'])->name('agregar_citado');
Route::post('/agrega_citado',                       [SeerController::class, 'guardar_citado'])->name('seer.citados');
Route::get('/agrega_documento/{id}',                [SeerController::class, 'vista_documentos'])->name('agregar_documentos');

//Constancias
Route::post('GeneraConstancia',         [SeerController::class, 'genera_constancia'])->name('generaConstancia');
Route::post('crear_constancia/',        [SeerController::class, 'crear_constancia'])->name('ValidarConstancia');
Route::get('Asistencia',                [SeerController::class, 'RegistroPrimeraConferencia']);    
Route::post('guardar_asitencia',        [SeerController::class, 'guardar_asistencia_post'])->name('guardar_asistencia');
Route::get('constancia/final',          [SeerController::class, 'enviarConstanciaFinal']); //Genera el envio de la constancia final
Route::get('generaPDFmasivo',           [SeerController::class, 'generaPDFS']);
Route::get('constancia_individual',     [SeerController::class, 'constancia_individual']);
//Rutas Asitencias
Route::get('asistencia/{id}',           [AsistenciaController::class, 'AsistenciaCrear']);
Route::get('QRAsistencia/{id}',         [AsistenciaController::class, 'generarQrUsuario']);
Route::get('asistencia/{id}',           [AsistenciaController::class, 'seer.estadistica_consultar']);
Route::get('asistencia/{id}',           [AsistenciaController::class, 'create_persona_con']);




Route::get('/limpiar-cache', function() {
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    return "¡Caché de Laravel completamente limpia y actualizada!";
});


/*
|--------------------------------------------------------------------------
| Web Login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('home',                                  [HomeController::class, 'home'])->name('home');
    Route::get('/home',                                 [DashboardController::class, 'index'])->name('dashboard');
    Route::get('publico',                               [HomeController::class, 'publico'])->name('publico');
    Route::post('/logout',                              [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    // Panel Común de entrada
    Route::get('/agenda',                               [DashboardController::class, 'index'])->name('agenda');
    Route::get('/cambio_contraseña/index',              [HomeController::class, 'password_cambiar'])->name('password_cambiar');
    Route::post('/notificaciones/editar',               [HomeController::class, 'contraseña_update'])->name('contraseña_update'); 

    // Calendario Compartido
    Route::get('/calendario',                   [App\Http\Controllers\CalendarController::class, 'index'])->name('calendario.index');
    Route::get('/citas/eventos',                [App\Http\Controllers\CitaController::class, 'citas'])->name('citas.eventos');
    Route::get('/pagos/eventos',                [App\Http\Controllers\CitaController::class, 'pagos'])->name('pagos.eventos');
    Route::get('/pagos/conciliadores',          [App\Http\Controllers\CitaController::class, 'conciliadores'])->name('conciliador.eventos');
    Route::get('/audiencias/eventos',           [App\Http\Controllers\AudienciasController::class, 'audiencias'])->name('audiencias.eventos');
    Route::get('/ratificaciones/eventos',       [App\Http\Controllers\AudienciasController::class, 'ratificaciones'])->name('ratificaciones.eventos');
    Route::get('citas/exportar-excel',          [CitaController::class, 'exportarExcel']);
    Route::get('/obtenerBloqueosCalendario',    [AdministracionController::class, 'obtenerBloqueosCalendario'])->name('calendario.bloqueos');

    /*
     |-- SUB-GRUPO DE CONTROL DE ACCESO: SUPER USUARIO / ADMINISTRADORES
     |-- (Gestión de Roles, Usuarios y Configuraciones Globales de SiConcilio)
     |*/
    Route::middleware(['role:Super Usuario|Administrador'])->group(function () {
        // Gestión de Estructura RBAC y Usuarios
        Route::name('user-management.')->group(function () {
            Route::resource('/user-management/users', UserManagementController::class);
            Route::resource('/user-management/roles', RoleManagementController::class);
            Route::resource('/user-management/permissions', PermissionManagementController::class);
        });

        Route::get('/usuarios/index',                   [UsuarioController::class, 'index'])->name('usuarios');
        Route::get('/usuarios/create',                  [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios/store',                  [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/edit/{id}',               [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::patch('/usuarios/update/{post}',         [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/destroy/{id}',         [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/roles/index',                      [RolController::class, 'index'])->name('roles');
        Route::get('/roles/create',                     [RolController::class, 'create'])->name('roles.create');
        Route::post('/roles/guardar',                   [RolController::class, 'store_rol'])->name('roles.store');
        Route::get('/roles/edit/{id}',                  [RolController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/update/{post}',            [RolController::class, 'update'])->name('roles.update');
        Route::delete('/roles/destroy/{id}',            [RolController::class, 'destroy'])->name('roles.destroy');

        // Módulo de Control de Incidencias y Subidas Masivas Administrativas
        Route::get('/incidencias/index_search',         [IncidenciasBusquedaController::class, 'index'])->name('incidencias.busqueda.index');
        Route::post('/incidencias/marcar',              [IncidenciasBusquedaController::class, 'marcar'])->name('incidencias.busqueda.marcar');
        Route::post('/incidencias/desmarcar',           [IncidenciasBusquedaController::class, 'desmarcar'])->name('incidencias.busqueda.desmarcar');
        Route::get('/subida_doc/index',                 [HomeController::class, 'indexSubida'])->name('subir_doc_masivo');
        Route::post('pagos/import',                     [HomeController::class, 'importPago'])->name('pagos.import');
        Route::post('concepto/import',                  [HomeController::class, 'importConcepto'])->name('concecto.import');
        Route::post('turnos/import',                    [HomeController::class, 'importTurnos'])->name('turnos.import');

        //Reportes conciliciador, auxiliares y notificaciones
        Route::get('/indexConciliadores/Reportes',          [SeerController::class, 'indexCAN'])->name('reportes_conciliador');
        Route::post('indexConciliadores/generar',           [SeerController::class, 'generaReporteUsuario'])->name('generaReporteUsuario');

        // Configuración Avanzada de Sedes y Retrocesos de Estatus
        Route::get('administracion/configuracion',          [AdministracionController::class, 'configuracion'])->name('configuracion');
        Route::get('administracion/sedes',                  [AdministracionController::class, 'configuracion_sedes'])->name('configuracion_sedes');
        Route::get('administracion/usuarios',               [AdministracionController::class, 'configuracion_usuarios'])->name('configuracion_usuarios');
        Route::get('administracion/retrocesos',             [AdministracionController::class, 'genera_retroceso'])->name('genera_retroceso');       
        Route::post('/generar-retroceso',                   [AdministracionController::class, 'consultar_retroceso'])->name('generar_retroceso'); 
        Route::get('administracion/RC/{id}',                [AdministracionController::class, 'hacer_retroceso_cumplimiento'])->name('accion_retrocesoC');    
        Route::get('administracion/RR/{id}',                [AdministracionController::class, 'hacer_retroceso_ratificacion'])->name('accion_retrocesoR'); 
        Route::post('/bloquear_sede',                       [AdministracionController::class, 'bloqueoSede'])->name('bloqueoSede');  //Bloquear días inhabiles para toda la sede
        Route::post('/bloquear_conciliador',                [AdministracionController::class, 'bloqueoConciliador'])->name('bloqueoConciliador'); //bloquear por días u horas a conciliadores
        Route::delete('/bloqueo/{id}',                      [AdministracionController::class, 'eliminarBloqueo'])->name('eliminarBloqueo'); //eliminar fechas bloqueadas(inhabiles)
        Route::get('/administracion/edit/{id}',             [AdministracionController::class, 'edit'])->name('administrador_usuarios_edit');
        Route::patch('/administracion/update/{post}',       [AdministracionController::class, 'update'])->name('usuarios_update');
        Route::delete('/administracion/destroy/{id}',       [AdministracionController::class, 'destroy'])->name('usuarios_destroy');
        Route::get('/administracion/borrarCumplimientos',   [AdministracionController::class, 'consular_cumplimientos'])->name('configuracion_borrar_cumpli');
        Route::post('/administracion/borrarCumplimiento',   [AdministracionController::class, 'borrar_cumplimeinto'])->name('borrar_cumplimeinto');
        Route::delete('/administracion/borrar/{id}',        [AdministracionController::class, 'destroy_cumplimientoA'])->name('borrar_cumplimeintoA');
        Route::get('/administracion/cambiarFecha',          [AdministracionController::class, 'cambio_audiencia'])->name('cambio_fecha_audiencia');
        Route::post('/administracion/cambiarFecha/buscar',  [AdministracionController::class, 'fecha_audiencia_buscar'])->name('fecha_audiencia_buscar');
        Route::post('/administracion/cambiarFecha/cambio',   [AdministracionController::class, 'cambiar_fecha'])->name('cambiar_fecha');
        Route::post('/administracion/cambiarFecha/cambioFecha',[AdministracionController::class, 'cambio_fecha'])->name('cambio_fecha');
        

        //Direccion General
        Route::get('/DireccionGeneral/index',           [CitaDireccionController::class, 'index'])->name('indexDireccionGeneral');
        Route::get('/DireccionGeneral/create',          [CitaDireccionController::class, 'create'])->name('cita_direccion_crear');
        Route::post('/DireccionGeneral/guardar',        [CitaDireccionController::class, 'cita_direccion_guardar'])->name('cita_direccion_guardar');
        Route::get('/DireccionGeneral/crearQR/{id}',    [CitaDireccionController::class, 'generarQr'])->name('generarQR_cita');
        Route::get('/turnos/estadistica',               [TurnosController::class, 'estadistica'])->name('turno_estadistica');

        //Incidencias
        Route::get('/indidencias/index',                [IncidenciasController::class, 'index_usuario'])->name('crear_inidencia');
        Route::get('/indidencias/crear',                [IncidenciasController::class, 'crear_incidencia'])->name('incidencias_crear');
        Route::post('/indidencias/guardar',             [IncidenciasController::class, 'incidencias_store'])->name('incidencias_store');
        Route::get('/indidencias/atender/{id}',         [IncidenciasController::class, 'incidencia_atender'])->name('incidencia_atender');
        Route::post('/indidencias/update',              [IncidenciasController::class, 'incidencias_update'])->name('incidencias_update');

        //Conciliadores
        Route::get('/conciliador/index',                [ConciliadoresController::class, 'index'])->name('index_conciliadores');
        Route::post('/conciliador/update_perimsos/',    [ConciliadoresController::class, 'update'])->name('conciliadores_permisos');
        Route::get('/conciliador/firmaCitatorios',      [SeerController::class, 'firmaCitatorios_index'])->name('firma_citatorio'); //Citatorios a firmar por los conciliadores
        Route::get('/conciliador/prueba',               [TurnosController::class, 'actualizar_folio']);
        Route::get('/ObtenerCitatorios/{id}',           [SeerController::class, 'mostrar_citatorios']);
         Route::get('/ObtenerConstancias/{id}',         [SeerController::class, 'mostrar_noConciliacion']); //Constancias de no conciliación para visualizar en un modal
    });

    /*
     |-- SUB-GRUPO DE CONTROL DE ACCESO: CONCILIADORES
     |-- (Audiencias, Convenios, Firmas Jurídicas y Cierres de Expedientes)
     |*/
    Route::middleware(['role:Super Usuario|Conciliador'])->group(function () {
        Route::get('/solicitud/indexA',                 [SeerController::class, 'indexA'])->name('audiencias.conciliador');
        Route::get('/solicitud/iniciar/{id}',           [SeerController::class, 'iniciar_audiencia'])->name('inicioAudiencia');
        Route::post('/audiencia/guardar',               [SeerController::class, 'concluir_audiencia_conciliador'])->name('concluir_audiencia_conciliador');
        Route::post('/audiencia/guardarNC',             [SeerController::class, 'concluir_audiencia_noconciliacion'])->name('concluir_audiencia_noconcialiacion');
        Route::get('/solicitud/indexB/{id}',            [SeerController::class, 'audienciaParte3'])->name('audiencias.parte3');
        Route::post('/solicitud/guardar',               [SeerController::class, 'concluir_audiencia'])->name('concluir_audiencia');
        Route::post('/reagendar_audiencia',             [SeerController::class, 'reagendar_audiencia'])->name('reagendar_audiencia');
        Route::post('/reagendar_audiencia_parte3',      [SeerController::class, 'reagendar_audiencia_parte3'])->name('reagendar_audiencia_parte3');
        Route::get('/conciliador/index',                [ConciliadoresController::class, 'index'])->name('index_conciliadores');
        Route::get('/conciliador/firmaCitatorios',      [SeerController::class, 'firmaCitatorios_index'])->name('firma_citatorio');
        Route::post('/solicitud/archivar_audiencia',    [SeerController::class, 'guardar_audiencia_archivo'])->name('archivar_audiencia');
        Route::post('/solicitud/archivar_audienciaParte3', [SeerController::class, 'guardar_audiencia_archivo_parte3'])->name('archivar_audiencia_parte3');
        Route::get('/seer/convenios',                   [SeerController::class, 'index_convenios'])->name('index_convenios');
        Route::get('/seer/colectivas',                  [SeerController::class, 'index_colectivas'])->name('index_colectivas');

        //Audiencias
            Route::get('/audiencias/index',                                     [SeerController::class, 'audiencia_index'])->name('audiencia_index');
            Route::get('/audiencias_Revisar/{id}/{isAudiencia?}',               [SeerController::class, 'solicitud_audiencia_revisar'])->name('solicitud_audiencia');
            Route::get('/citatorio/{id}',                                       [SeerController::class, 'pdfCitatorioAudiencia'])->name('pdfCitatorioAudiencia');
            Route::get('/solicitud/indexA',                                     [SeerController::class, 'indexA'])->name('audiencias.conciliador'); 
            Route::get('/solicitud/iniciar/{id}',                               [SeerController::class, 'iniciar_audiencia'])->name('inicioAudiencia');
            Route::post('/reagendar_audiencia',                                 [SeerController::class, 'reagendar_audiencia'])->name('reagendar_audiencia');
            Route::post('/reagendar_audiencia_parte3',                          [SeerController::class, 'reagendar_audiencia_parte3'])->name('reagendar_audiencia_parte3');          
            Route::post('/auciencia/concluir/',                                 [SeerController::class, 'audiencia_parte2'])->name('audiencia_parte2');
            Route::get('/solicitud/indexB/{id}',                                [SeerController::class, 'audienciaParte3'])->name('audiencias.parte3'); 
            Route::post('/solicitud/guardar',                                   [SeerController::class, 'concluir_audiencia'])->name('concluir_audiencia');
            Route::post('/seleccionar_abogado',                                 [SeerController::class, 'seleccionar_abogado'])->name('seleccionar_abogado');
            Route::post('/seleccionar_representante_patronal',                  [SeerController::class, 'seleccionar_representante_patronal'])->name('seleccionar_representante_patronal');
            Route::post('/guardar_comparecencia_citado',                        [SeerController::class, 'guardar_comparecencia_citado'])->name('guardar_comparecencia_citado');
            Route::post('/incompentencia_audiencia',                            [SeerController::class, 'incopentencia_audiencia'])->name('incopentencia_audiencia');
            Route::post('/desistimiento_audiencia',                             [SeerController::class, 'desistimiento_audiencia'])->name('desistimiento_audiencia');
            Route::get('/audieniecias/complimientos',                           [SeerController::class, 'audiencias_cumplimiento'])->name('audiencias.cumplimiento');
            Route::post('/audiencia/consulta/solictud',                         [SeerController::class, 'solicitudes_busqueda'])->name('solicitudes_busqueda');
            Route::post('/solicitud/guardarExpediente',                         [SeerController::class, 'guardar_expediente'])->name('subir_expediente'); //Subir expediente 
            Route::post('/solicitud/guardarExpedienteR',                        [TurnosController::class, 'guardar_expediente'])->name('subir_expediente_ratificacion'); //Subir expediente ratificacion
            Route::get('/audiencias/vista_previa/{id_solicitud}',               [SeerController::class, 'vista_previa'])->name('vista_previa');
            Route::get('/audiencias/vista_previa_patronal/{id_solicitud}',      [SeerController::class, 'vista_previa_patronal'])->name('vista_previa_patronal');
            Route::post('/audiencia/guardar-seleccion-convenio',                [SeerController::class, 'guardarSeleccionConvenioSession'])->name('guardar_seleccion_convenio');
            Route::post('/audiencia/guardar-seleccion-acta',                    [SeerController::class, 'guardarSeleccionActaSession'])->name('guardar_seleccion_acta');
            Route::post('/solicitud/editar_audiencia',                          [SeerController::class, 'editar_solicitud_audiencia'])->name('editar_solicitud_audiencia');
            Route::post('/seleccionar_abogado_audiencia',                       [SeerController::class, 'seleccionar_abogado_audiencia'])->name('seleccionar_abogado_audiencia');
            Route::post('/audieencia/guardar_citadoC',                          [SeerController::class, 'insertar_citados_audiencia'])->name('insertar_citados_audiencia');
            Route::post('/audieencia/crear/PF',                                 [SeerController::class, 'insertar_citado_audiencia'])->name('insertar_citado_audiencia');
            Route::post('/solicitudes/actualiza_audiencia',                     [SeerController::class, 'actualiza_citados_audiencia'])->name('actualiza_citados_audiencia');
            Route::delete('/audieniecias/concepto_eliminar_pago/{id_solicitud}',[SeerController::class, 'concepto_eliminar_pago'])->name('concepto_eliminar_pago');
            Route::delete('/audieniecias/pago_eliminar_pago/{id_solicitud}',    [SeerController::class, 'pago_eliminar_pago'])->name('pago_eliminar_pago');
            Route::post('/solicitudes/terminar_audiencia',                      [SeerController::class, 'terminar_audiencia'])->name('terminar_audiencia');
            Route::post('/audiencias/eliminar_item_sesion/{id}',                [SeerController::class, 'eliminar_item_sesion'])->name('eliminar_item_sesion');
            Route::get('/audienicas/cumplimietos/{id}',                         [SeerController::class, 'ver_pagos_audiencia'])->name('audiencia_cumplimientos');
            Route::get('/cumplimientos/detalle/{id}',                           [SeerController::class, 'ver_pago_cumplimiento'])->name('pago_cumplimiento');
            Route::post('/guardar_edicion_audiencia',                           [SeerController::class, 'audiencia_confirmar'])->name('audiencia_confirmar');
            Route::post('/audiencias/pagoA',                                    [SeerController::class, 'pagoA_audiencia'])->name('pagoA_audiencia'); // cumplimiento en audiencias
            Route::post('/representante/quitar',                                [SeerController::class, 'quitarRepresentante'])->name('representante.quitar'); //Eliminar/Quitar representante legal asiganado al iniciar la audiencia
            Route::delete('/audieniecias/deduccion_eliminar_pago/{id_solicitud}',[SeerController::class, 'eliminar_deduccion_audiencia'])->name('eliminar_deduccion_audiencia');
            Route::get('/audiencias/buscar-abogados-ajax',                      [SeerController::class, 'buscar_abogados_audiencia_ajax'])->name('buscar_abogados_audiencia_ajax');
            Route::get('/VerDcocumentos/{id}',                                  [SeerController::class, 'VerDocumentosAudiencia'])->name('VerDocumentosAudiencia');
            Route::get('/VerpdfactaAudiencia/{id}',                             [SeerController::class, 'VerPDFAudiencia'])->name('VerPDFAudiencia');
            Route::get('/Verpdfcr/{id}',                                        [SeerController::class, 'VerPDFConvenioRei'])->name('PDFconvenioreinstalacion');
            Route::get('/Verpdfincompetencias/{id}',                            [SeerController::class, 'VerPDFIncompetencia'])->name('PDFincompetencia');
            Route::get('/VerpdfcumplimientoTotal/{id}',                         [SeerController::class, 'VerPDFCumplimientoTotal'])->name('PDFcumplimientoTotal');
            Route::get('/Verpdfnoconciliacion/{id}',                            [SeerController::class, 'VerPDFNoConciliacion'])->name('PDFno_conciliacion');
            Route::get('/VerPDFNoConciliacionIndividual/{id}',                  [SeerController::class, 'VerPDFNoConciliacionIndividual'])->name('PDFnoConciliacionIndividual');
        //Fin de Audiencias

    });

    /*
     |-- SUB-GRUPO DE CONTROL DE ACCESO: AUXILIARES DE CONCILIACIÓN / RECEPCIÓN
     |-- (Pre-registro presencial, validación inicial y asignación de turnos rápidos)
     |*/
    Route::middleware(['role:Super Usuario|Auxiliar|Recepcion|Turnos'])->group(function () {
        Route::get('/turnos/index',                     [RecepcionController::class, 'index_turnos'])->name('turnos');
        Route::get('/turnos/misturnos',                 [RecepcionController::class, 'misturnos'])->name('misturnos');
        
        Route::get('/solicitudes/pedientes',            [SeerController::class, 'solicitudes_pendientes'])->name('solicitudes_pendientes');
        Route::get('/solicitudes_revisar/{id}',         [SeerController::class, 'solicitudes_pendientes_revisar'])->name('solicitud_revisar');
        Route::post('/confirmar_solicitudes',           [SeerController::class, 'solicitud_confirmar'])->name('confirmar_solicitud');
        Route::post('/solicitudes/guardar',             [SeerController::class, 'guardar_rechazo'])->name('rechazar_solicitud');

        //Recepcion
        Route::get('/turnos/create',             [RecepcionController::class, 'create'])->name('turnos.create');
        Route::post('/turnos/store',             [RecepcionController::class, 'store_turnos'])->name('turnos.store');
        Route::get('/turnos/turnos',             [RecepcionController::class, 'turnos'])->name('turnos.listado');
        Route::get('/turnos/activo/{id}',        [RecepcionController::class, 'activo'])->name('turnos.activo');
        Route::get('/turnos/noactivo/{id}',      [RecepcionController::class, 'noactivo'])->name('turnos.noactivo');
        Route::get('/turnos/cambiar/{id}',       [RecepcionController::class, 'cambiar'])->name('cambiar');
        Route::get('/turnos/terminadoR/{id}',    [RecepcionController::class, 'terminado_confirmar'])->name('turnos.terminado_revisar');
        Route::get('/turnos/cambio/{id}',        [RecepcionController::class, 'cambio'])->name('turnos.cambioexcepcion');
        Route::get('/turnos/terminado/{id}',     [RecepcionController::class, 'terminado'])->name('turnos.terminado');
        Route::post('/turnos/edit',              [RecepcionController::class, 'edit'])->name('turnos.edit');
        Route::get('/turnos/tarjeta',            [RecepcionController::class, 'index_tarjeta'])->name('tarjeta_informativa');
        Route::get('/tarjeta/llenar/{id}',       [RecepcionController::class, 'tarjeta_crear'])->name('llenar_tarjeta');
        Route::post('/tarjeta/guardar',          [RecepcionController::class, 'guardar'])->name('agregar_tarjeta');
        Route::get('/tarjetas/index',            [RecepcionController::class, 'reporte_excepcion'])->name('reporte_excepcion');
        Route::post('reportes/excepcion',        [RecepcionController::class, 'reportePDF'])->name('turnos_excepcion');
        Route::get('/turnos/nuevo',              [RecepcionController::class, 'nueva_cita'])->name('nueva_cita');
        Route::post('/tuenos/guardar',           [RecepcionController::class, 'turnos_guardar'])->name('turnos_guardar_nuevo'); 
        Route::get('/excepciones/index',         [RecepcionController::class, 'index_excepciones'])->name('excepcion');
        Route::get('/excepciones/atender/{id}',  [RecepcionController::class, 'atender_excepcion'])->name('atender_excepcion');
        Route::post('/excepciones/guardar',       [RecepcionController::class, 'guardar_excepcion'])->name('guardar_excepcion');
    });

    /*
     |-- SUB-GRUPO DE CONTROL DE ACCESO: NOTIFICADORES / ENLACES JURÍDICOS
     |-- (Estatus de entrega de citatorios, instructivos y levantamiento de razones de notificación)
     |*/
    Route::middleware(['role:Super Usuario|Notificador|Enlace|Estadistica'])->group(function () {
        Route::get('/notificaciones/index',                 [SeerController::class, 'notificaciones'])->name('notificaciones');
        Route::get('/notificaciones/busqueda',              [SeerController::class, 'notificaciones_consultar'])->name('notificaciones_consultar');
        Route::post('/notificaciones/resultado',            [SeerController::class, 'notificaciones_busqueda'])->name('notificaciones_busqueda');
        Route::get('/notificaciones/detalles/{id}',         [SeerController::class, 'seer_detalles'])->name('seer_detalles');
        Route::get('/seer/estatus/{id}',                    [SeerController::class, 'seer_estatus'])->name('seer.notificador');
        Route::post('/seer/updateNotificador',              [SeerController::class, 'update_notificador'])->name('seer.cambioEstatus');
        Route::get('/notificador/mihistorial',              [SeerController::class, 'hitorialnotificacador'])->name('Historial_Notificacador');
        Route::get('/notificador/historial',                [SeerController::class, 'todas_notificaciones'])->name('todas_notificaciones');

        //Enlace
        Route::get('/notificaciones/consultar/{id}',        [SeerController::class, 'mostrar_citados']);
        Route::post('/notificaciones/editar',               [SeerController::class, 'editar_citados'])->name('editar_citado_enlace');   
        Route::get('/notificaciones/consultar_citado/{id}', [SeerController::class, 'mostrar_citadoC'])->name('consultar_citado');
        Route::get('/notificaciones/historial',             [SeerController::class, 'notificaciones_consultar'])->name('notificaciones_consultar');
        Route::post('notificaciones/actualizar',            [SeerController::class, 'editar_citados'])->name('actualizar_enlace'); 
        Route::post('/seer/store_enlace/{id}',              [SeerController::class, 'store_enlace'])->name('seer.store_enlace');
        Route::post('/seer/mostrar',                        [SeerController::class, 'mostrar_reporte'])->name('seer.mostar');
        Route::post('/notificacion/editar',                 [SeerController::class, 'mostrar_citado'])->name('editar_citado_historial');
        Route::post('notificaciones/actualizarH',           [SeerController::class, 'editar_citados_historial'])->name('actualizar_enlace_hitorial');  
    });

    /*
     |-- RETROCESOS (acciones destructivas: solo mandos)
     |*/
    Route::middleware(['role:Super Usuario|Administrador|Delegado'])->group(function () {
        Route::get('/ratificaciones/retroceso',             [TurnosController::class, 'retroceso_ratificacion_index'])->name('retroceso_ratificacion');
        Route::post('/ratificaciones/retroceso/buscar',     [TurnosController::class, 'buscar_retroceso_ratificacion'])->name('retroceso_ratificacion_buscar');
        Route::post('/ratificaciones/retroceso/{id}',       [TurnosController::class, 'aplicar_retroceso_ratificacion'])->name('retroceso_ratificacion_aplicar');

        Route::get('/solicitudes/retroceso',                [SeerController::class, 'retroceso_solicitud_index'])->name('retroceso_solicitud');
        Route::post('/solicitudes/retroceso/buscar',        [SeerController::class, 'buscar_retroceso_solicitud'])->name('retroceso_solicitud_buscar');
        Route::post('/solicitudes/retroceso/{id}',          [SeerController::class, 'aplicar_retroceso_solicitud'])->name('retroceso_solicitud_aplicar');
    });

    /*
     |-- ACCESO GENERAL PARA TODO EL PERSONAL (Lectura y Consultas)
     |*/
    Route::get('/seer/index',                           [SeerController::class, 'index'])->name('seer.index');
    Route::get('/poderes/index',                        [PoderController::class, 'index'])->name('poderes');
    Route::get('/expedientes/index',                    [ExpedienteController::class, 'index'])->name('expedientes.index');
    Route::get('/ratificaciones/index',                 [TurnosController::class, 'index_ratificacion'])->name('index_ratificacion');
    Route::get('/cumplimientos/index',                  [SeerController::class, 'audiencias_cumplimiento'])->name('audiencias.cumplimiento');
    Route::get('/seer/asseria',                         [SeerController::class, 'create_asesoria'])->name('create_asesoria');
    Route::get('/revisar/indexAudiencia',               [SeerController::class, 'todas_audiencias'])->name('todas_audiencias');
    Route::get('/revisar/indexSolictudes',              [SeerController::class, 'todas_solicitudes'])->name('todas_solicitudes');
    Route::get('/revisar/indexRatificaciones',          [SeerController::class, 'todas_ratificaciones'])->name('todas_ratificaciones');
    Route::get('/revisar/indexCumplimientos',           [SeerController::class, 'todos_complimientos'])->name('todos_complimientos');
    Route::get('/seer/estadistica',                     [SeerController::class, 'estadistica'])->name('seer.estadistica');
    Route::get('seer/historial',                        [SeerController::class, 'ver_historial'])->name('persona.historial');
    Route::get('/solicitudes/home',                     [SeerController::class, 'solicitudes'])->name('solicitudes_index');
    Route::post('/seer/aserorias',                      [SeerController::class, 'store_asesorias'])->name('seer.store_asesoria');
    Route::get('/seer/index',                           [SeerController::class, 'index'])->name('seer');

    //Solicitudes y casos de exepcion
        Route::get('/solicitudes/pedientes',                        [SeerController::class, 'solicitudes_pendientes'])->name('solicitudes_pendientes');
        Route::get('/solicitud/index',                              [SeerController::class, 'mis_solicitudes'])->name('mis_solicitudes');
        Route::get('/solicitudes_revisar/{id}',                     [SeerController::class, 'solicitudes_pendientes_revisar'])->name('solicitud_revisar'); 
        Route::get('/solicitudes_editar/{id}',                      [SeerController::class, 'solicitudes_pendientes_editar'])->name('solicitud_editar'); 
        Route::post('/confirmar_solicitudes',                       [SeerController::class, 'solicitud_confirmar'])->name('confirmar_solicitud');
        Route::get('/eliminar_motivo/{id}/{id_motivo}',             [SeerController::class, 'eliminar_motivo'])->name('eliminar_motivo');
        Route::get('/eliminar_motivo_solicitud/{id}/{id_motivo}',   [SeerController::class, 'eliminar_motivo_solicitud'])->name('eliminar_motivo_solicitud');
        Route::get('/eliminar_motivo_buzon/{id}/{id_motivo}',       [SeerController::class, 'eliminar_motivo_buzon'])->name('eliminar_motivo_buzon');
        Route::get('/solicitude/{id}',                              [SeerController::class, 'regresa_eliminar'])->name('regresa_eliminar');
        Route::post('/solicitud/archivar_audiencia',                [SeerController::class, 'guardar_audiencia_archivo'])->name('archivar_audiencia');
        Route::post('/solicitud/archivar_audienciaParte3',          [SeerController::class, 'guardar_audiencia_archivo_parte3'])->name('archivar_audiencia_parte3');
        Route::post('/solicitud/emitir_multas',                     [SeerController::class, 'emitir_multas'])->name('emitir_multas');
        Route::post('/solicitud/editar',                            [SeerController::class, 'editar_solicitud_con'])->name('editar_solicitud');
        Route::post('/historial/auxiliar',                          [SeerController::class, 'historial_auxiliar'])->name('historial_auxiliar');
        Route::get('/solicitudes/solicitudes',                      [SeerController::class, 'solicitudes_todas'])->name('solicitudes_todas');
        Route::post('/audiencia/guardar',                           [SeerController::class, 'concluir_audiencia_conciliador'])->name('concluir_audiencia_conciliador');
        Route::post('/audiencia/guardarNC',                         [SeerController::class, 'concluir_audiencia_noconciliacion'])->name('concluir_audiencia_noconcialiacion');
        Route::post('/solicitudes/crear/PF',                        [SeerController::class, 'citado_personaF'])->name('insertar_citado_PF');
        Route::post('/solicitudes/guardar',                         [SeerController::class, 'guardar_rechazo'])->name('rechazar_solicitud');
        Route::get('/correcion_solicitudes/{id}',                   [SeerController::class, 'solicitud_consultarSolicitante'])->name('consulta_solicitante');
        Route::post('/correcion_solicitudes',                       [SeerController::class, 'solicitante_edicion'])->name('solicitante_edicion');
        Route::post('/solicitudes/actualiza',                       [SeerController::class, 'actualiza_citados'])->name('actualiza_citados');
        Route::get('/solicitudes/historialSolicitante',             [SeerController::class, 'Historial_Solicitante'])->name('historial_solicitante');
        Route::post('/solicitud/guardarCitatoriosT',                [SeerController::class, 'guardar_citatoriosT'])->name('subir_citatoriosT'); //Subir los citatorios entregados por el trabajador ya firmados
        Route::get('solicitudes',                                   [SeerController::class, 'solicitudesAuxiliares'])->name('solicitud');
        Route::get('tipoIndustriaA/{tipo_solicitud}',               [SeerController::class, 'IndustriasAux'])->name('solicitud.industriaAuxiliar');
        Route::get('tipoIndustriaAP/{tipo_solicitud}',              [SeerController::class, 'IndustriasAuxP'])->name('solicitud.industriaAuxiliarP');
        Route::get('TrabajadorA/{tipo_solicitud}',                  [SeerController::class, 'inicioSolicitud_auxiliar'])->name('trabajadorAuxiliar');
        Route::get('TrabajadorAP/{tipo_solicitud}',                 [SeerController::class, 'inicioSolicitud_auxiliarP'])->name('trabajadorAuxiliarP');
        Route::post('/agregar_solicitanteA',                        [SeerController::class, 'solicitud_parte1Aux'])->name('agregaSolicitanteA');
        Route::post('/agregar_solicitanteAP',                       [SeerController::class, 'solicitud_parte1AuxP'])->name('agregaSolicitanteAP');
        Route::post('guardar_solicitanteA',                         [SeerController::class, 'solicitud_parte2Aux'])->name('guardaSolicitanteA');
        Route::post('guardar_solicitanteAP',                        [SeerController::class, 'solicitud_parte2AuxP'])->name('guardaSolicitanteAP');
        Route::get('/agrega_citadoA/{id}',                          [SeerController::class, 'vista_citadoAux'])->name('agrega_citadoAux');
        Route::get('/agrega_citadoAP/{id}',                         [SeerController::class, 'vista_citadoAuxP'])->name('agrega_citadoAuxP');
        Route::post('/guardar_citadoA',                             [SeerController::class, 'guardar_citadoAux'])->name('seer.citadosAux');
        Route::post('/guardar_citadoAP/{id}',                       [SeerController::class, 'guardar_citadoAuxP'])->name('seer.citadosAuxP');
        Route::get('/finalizaAux/{id}',                             [SeerController::class, 'guardar_solicitudAux'])->name('seer.finalizaAux');
        Route::post('/finalizaAuxP/{id}',                           [SeerController::class, 'guardar_solicitudAuxP'])->name('seer.finalizaAuxP');
        Route::post('/solicitudes/abandonar',                       [SeerController::class, 'abandonarSolicitudAux'])->name('solicitud.abandonar');
        Route::get('/solicitudes/check-lock',                       [SeerController::class, 'checkSolicitudAuxLock'])->name('solicitud.checkLock');
        Route::get('/VerpdfcumplimientoTotal/{id}',                 [SeerController::class, 'VerPDFCumplimientoTotal'])->name('PDFcumplimientoTotal');
        Route::get('/audiencias/edicion/{id}/{audiencia_id}',       [SeerController::class, 'edicion_audienciaConcluida'])->name('edicion_audienciaConcluida'); //Vista de edición cuando la audiencia ya finalizó
        Route::post('/audiencias/Guardar_edicionA',                 [SeerController::class, 'Guarda_edicion_audienciaConcluida'])->name('Guarda_edicion_audienciaConcluida');
        Route::get('/excepciones/index',                            [RecepcionController::class, 'index_excepciones'])->name('excepcion');
        Route::get('/excepciones/atender/{id}',                     [RecepcionController::class, 'atender_excepcion'])->name('atender_excepcion');
        Route::post('/excepciones/guardar',                         [RecepcionController::class, 'guardar_excepcion'])->name('guardar_excepcion');
        Route::get('/cumplimiento/rechazar/rati/{id}',              [SeerController::class, 'cumplimiento_rechazar_rati'])->name('cumplimiento_rechazar');
        Route::post('/cumplimiento/no_comparece/{id}',              [SeerController::class, 'cumplimiento_incomparecencia'])->name('cumplimiento_incomparecencia');
        Route::get('/cumplimiento/PDFIncumplimiento/{id}',          [SeerController::class, 'PDFincumplimientoAudiencia'])->name('PDFincumplimientoAudiencia');
        Route::get('/cumplimiento/incomparecencia/{id}',            [SeerController::class, 'PDFIncomparecenciaCumplimiento'])->name('PDFIncomparecenciaCumplimiento');
        Route::post('/cumplimiento/pagar/pena/audiencia',           [SeerController::class, 'cumplimiento_pagar_con_pena_audiencia'])->name('cumplimiento_pagar_pena_audiencia');
        Route::get('/notificaciones/consultar/{id}',                [SeerController::class, 'mostrar_citados'])->name('editar_citado');

        Route::get('/documentos/solicitante/identificacion/{id}',       [SeerController::class, 'ver_identificacion_solicitante'])->name('documento_identificacion_solicitante_ver');
        Route::get('/ObtenerCitatorios/{id}',                           [SeerController::class, 'mostrar_citatorios']);
        Route::get('/Verpdfcasosprevistos/{id}', [RecepcionController::class, 'VerPDFCasosPrevistos'])->name('VerPDFCasosPrevistos');
        Route::get('/Verpdfcanalizacion/{id}',   [RecepcionController::class, 'VerPDFCanalizacion'])->name('VerPDFCanalizacion');
    //Poderes
        
        Route::get('/poder-guardar',                        [PoderController::class, 'show'])->name('poder');
        Route::get('/PDF/acuseRegistro/{idAbogado}',        [PoderController::class, 'VerPDFregistroAbogado'])->name('PDFregistroAbogado');
        Route::get('/poderes/index',                        [PoderController::class, 'index'])->name('poderes.index');
        Route::get('/poderes/index',                        [PoderController::class, 'index'])->name('poderes');
        Route::get('/poderes/create',                       [PoderController::class, 'create'])->name('poderes.create');
        Route::get('/poderes/edit/{id}',                    [PoderController::class, 'edit'])->name('poderes.edit');
        Route::get('/poderes/history/{id}',                 [PoderController::class, 'history'])->name('poderes.history');
        Route::get('/poderes/history/detail/{id}',          [PoderController::class, 'historyDetail'])->name('poderes.historyDetail');
        Route::post('/poderes/store',                       [PoderController::class, 'store'])->name('poderes.store');
        Route::patch('/poderes/update/{post}',              [PoderController::class, 'update'])->name('poderes.update');
        Route::delete('/poderes/destroy/{id}',              [PoderController::class, 'destroy'])->name('poderes.destroy');
        Route::post('/poderes/agregar_representante',       [PoderController::class, 'agregarRepresentante'])->name('poderes.agregar_representante');
        Route::get('/poderes/data-ajax',                    [PoderController::class, 'buscar_poderes_ajax'])->name('poderes.index.ajax');
    //PDF Solicitudes    
        Route::get('/Verpdfincompetencias/{id}',                        [SeerController::class, 'VerPDFIncompetencia'])->name('PDFincompetencia');
        Route::get('/Verpdfcs/{id}',                                    [SeerController::class, 'VerPDFConvenioSol'])->name('PDFconveniosolicitud');
        Route::get('/Verpdfcr/{id}',                                    [SeerController::class, 'VerPDFConvenioRei'])->name('PDFconvenioreinstalacion');
        Route::get('/Verpdfacuse/{id}',                                 [SeerController::class, 'PDFacuseSolicitud'])->name('PDFacuse_solicitud');
        Route::get('/Verpdfnotificacion/{id}',                          [SeerController::class, 'PDFnotificacionSolicitante'])->name('PDFnotificacion_solicitante'); 
        Route::get('/Verpdfmulta/{id}/{id_solicitud}',                  [SeerController::class, 'VerPDFMulta'])->name('PDFmulta');       
        Route::get('/solicitud/pdfs/{id}',                              [SeerController::class, 'pdfCitatorio'])->name('PDFSolicitud');
        Route::get('solicitud/consultar/{id}',                          [SeerController::class, 'consultar_solicitudes'])->name('consultar_solicitud');
        Route::get('/audiencias/busqueda/buscar',                       [SeerController::class, 'audiencia_fecha'])->name('audiencia_fecha');
        Route::post('/historial/conciliador/busqueda',                  [SeerController::class, 'historial_conciliador'])->name('historial_conciliador');
        Route::get('/PDF/faltaInteres/{id}',                            [SeerController::class, 'VerPDFInteres'])->name('PDFfalltaInteres');
        Route::get('/Verpdfnoconciliacion/{id}',                        [SeerController::class, 'VerPDFNoConciliacion'])->name('PDFno_conciliacion');
        Route::get('/pdf/estadistica',                                  [PDFController::class, 'pdfEstadistica'])->name('PDFestaditica');
        Route::get('/VerpdfRnotificacion/{id}/{id_solicitud}',          [SeerController::class, 'VerPDFRNotificacion'])->name('PDFRazonNoticacion'); // Notificación exitosa, ATIENDE OTRA PERSONA, CITADO O NADIE
        Route::get('/VerpdfNotificacion/{id}/{id_solicitud}',           [SeerController::class, 'PDFnotificadoInstructivo'])->name('PDFInstructivo'); //Notificación por instructivo
        //Route::get('/VerpdfNotificacionNoExitosa/{id}/{id_solicitud}',  [SeerController::class, 'PDFnotificadoNoexitosa'])->name('PDFNoExitosa'); //Notificación No exitosa SE CONSTITUYE, CERRADO
        Route::get('/VerpdfNotificacionNoInt/{id}/{id_solicitud}',      [SeerController::class, 'PDFnotificadoNoexitosaInt'])->name('PDFNoExitosaInt'); //Notificación No exitosa NO SE LOCALIZA INTERIOR
        Route::get('/VerpdfNotificacionNoENS/{id}/{id_solicitud}',      [SeerController::class, 'PDFnotificadoNoexitosaNS'])->name('PDFnotificadoNoexitosaNS'); //Notificación No exitosa NO SE LOCALIZA INTERIOR
        Route::get('/VerpdfcPTULabora/{id}',                            [SeerController::class, 'VerPDFConvenioPTULabora'])->name('PDFconvenioPTU_SI_S'); //Convenio PTU SIGUE laborando el trabajador
        Route::get('/VerpdfcPTUNLabora/{id}',                           [SeerController::class, 'VerPDFConvenioPTUNoLabora'])->name('PDFconvenioPTU_NO_S'); //Convenio PTU ya NO labora el trabajador
        Route::get('/pdfsinPoder/{id}',                                 [SeerController::class, 'VerPDFCompareceSinPoder'])->name('PDFcompareceSP'); //Comparece representante legal sin poder
        Route::get('/Verpdfcumpumplimiento/{id}',                       [SeerController::class, 'VerPDFCumplimiento'])->name('PDFcumplimiento');
        Route::get('/VerpdfcumpumplimientoP/{id}',                      [SeerController::class, 'PDFcumplimientoParcial'])->name('PDFcumplimientoParcial');
        Route::get('/solicitudes/descargarCitatorios/{id}',             [SeerController::class, 'descargarCitatorios'])->name('descargarCitatorios'); //Vista para descargar y subircitatorios entregados por el trabajador
        Route::get('/VerpdfacuseConfirmacion/{id}',                     [SeerController::class, 'PDFacuseConfirmada'])->name('PDFacuseConfirmada'); //Acuse de solicitud confirmada
        Route::get('/VerpdfactaAudiencia/{id}',                         [SeerController::class, 'VerPDFAudiencia'])->name('VerPDFAudiencia');
        Route::get('/VerpdfmultaNot/{id}/{id_solicitud}',               [SeerController::class, 'VerPDFMultaNotificacion'])->name('PDFmultaNotificacion'); // Notificación de multa cuando finaliza exitosamente
        Route::get('/PDF/captura/{id}/{tipo}',                          [SeerController::class, 'VerPDFCaratula'])->name('PDFCaratulaInfo'); //Formato llenado por los solicitantes
        Route::get('/PDF/capturaConcilio/{id}/{tipo}',                  [SeerController::class, 'VerPDFCaratulaConcilio'])->name('PDFCaratulaInfoConcilio'); //Formato llenado por los solicitantes
        Route::get('/PDF/capturaConcilioR/{id}',                        [SeerController::class, 'VerPDFCaratulaConcilioR'])->name('PDFCaratulaInfoConcilioR'); //Formato llenado por los solicitantes
        Route::get('/VerPDFNoConciliacionIndividual/{id}',              [SeerController::class, 'VerPDFNoConciliacionIndividual'])->name('PDFnoConciliacionIndividual'); //Genera las constancias de no conciliación para mostrar de manera invividual por achivo
        Route::get('/VerpdfmultaInst/{id}/{id_solicitud}',              [SeerController::class, 'VerPDFMultaInstructivo'])->name('VerPDFMultaInstructivo'); // Notificación de multa por
        Route::get('/VerpdfmultaNConst/{id}/{id_solicitud}',            [SeerController::class, 'VerPDFMultaNoExitConstituye'])->name('VerPDFMultaNoExitConstituye'); // Notificación de multa NO Exitosa Se Constituye
        Route::get('/VerpdfNExitConst/{id}/{id_solicitud}',             [SeerController::class, 'VerPDFNoExitConstituye'])->name('VerPDFNoExitConstituye'); // Notificación NO Exitosa Se Constituye
    

    //Plantillas
        Route::get('/plantillas/index',                             [SeerController::class, 'plantillas_index'])->name('plantillas_index');
        Route::get('/plantillas/ratificaciones',                    [SeerController::class, 'plantillas_ratificaciones'])->name('plantillas_ratificaciones');
        Route::get('reporte',                                       [SeerController::class, 'reporte_diario'])->name('reporte_diario');
    //Ruta de agregar citados
        Route::get('/agrega_documento/{id}',                        [SeerController::class, 'vista_documentos'])->name('agregar_documentos');
        Route::post('/solicitudes/patronal/guardar-citado/{id}',    [SeerController::class, 'guardar_citado_patronal'])->name('guardar.citado.patronal');
        Route::get('/cancelar_edicion',                             [SeerController::class, 'cancelar_edicion'])->name('cancelar_edicion');
    //Citados
        Route::post('/solicitud/guardar_citadoC',           [SeerController::class, 'insertar_citados_con'])->name('insertar_citado');
        Route::get('/solicitud/consultarC',                 [SeerController::class, 'consultar_citados_con'])->name('consultar_citados');
        Route::post('/agregar_citado_edicion',              [SeerController::class, 'agregar_citado_edicion'])->name('agregar_citado_edicion');
        Route::post('/audiencia/agregar_citado',            [SeerController::class, 'agregar_citado_audiencia_directo'])->name('agregar_citado_audiencia_directo');
        Route::delete('/borrar_citado_edicion',             [SeerController::class, 'borrar_citado_edicion'])->name('borrar_citado_edicion');
        Route::post('/historial/notificador',               [SeerController::class, 'historial_notificador'])->name('historial_notificador');
    //Ratificaciones
        Route::get('/ratificaciones/atender',               [TurnosController::class, 'revisar_ratificaciones_hoy'])->name('ratificacion_atender');
        Route::post('/ratificaciones/buscar',               [TurnosController::class, 'busqueda_ratificaciones'])->name('ratificacion_buscar');
        Route::get('/ratificaciones/concluir/{id}',         [TurnosController::class, 'concluir_ratificaciones'])->name('ratificacion_concluir');
        Route::post('/ratificacion/busqueda',               [TurnosController::class, 'busqueda_ratificaciones'])->name('ratificaciones_busqueda');
        Route::post('/guardar_manifestaciones',             [TurnosController::class, 'guardar_manifestacion'])->name('solicitudes.manidestaciones');
        Route::get('/ratificaciones/pagos/{id}',            [TurnosController::class, 'pagar_ratificacion'])->name('ratificacion_pagar');
        Route::get('/ratificaciones/cumplimietos/{id}',     [TurnosController::class, 'ver_pagos_rati'])->name('ratificacion_cumplimientos');
        Route::post('/ratificaciones/pagoA',                [TurnosController::class, 'pagoA_ratificacion'])->name('ratificacion_pagoA');
        Route::get('/ratificaciones/pagoR/{id}',            [TurnosController::class, 'pagoR_ratificacion'])->name('ratificacion_pagoR');
        Route::get('ratificaciones/consultar/{id}',         [TurnosController::class, 'consultar_ratificaciones'])->name('consultar_ratificacion');
        Route::post('ratificaciones/editar',                [TurnosController::class, 'editar_ratificaciones'])->name('editar_ratificacion');
        Route::get('/PDF/falta_interes/{id}',               [TurnosController::class, 'VerPDFInteres'])->name('PDFfallta_interes');
        Route::get('/ratificaciones/pendientes',            [TurnosController::class, 'ratificacion_confirmadas'])->name('ratificacion_confirmadas'); 
        Route::get('/ratificaciones/pagoIncom/{id}',        [TurnosController::class, 'incomparecencia_rati'])->name('ratificacion_pagoIncom'); //No comparece el trabajador al pago
        Route::get('/ratificaciones/vista_previa/{id_solicitud}',       [TurnosController::class, 'vista_previa_ratificacion'])->name('vista_previa_ratificacion');
        Route::post('/ratificaciones/editarR',                          [TurnosController::class, 'editar_ratificacion_revisar'])->name('editar_ratificacion_revisar');
        Route::post('/seleccionar_abogado_ratificacion',                [TurnosController::class, 'seleccionar_abogado_ratificacion'])->name('seleccionar_abogado_ratificacion');
        Route::delete('/ratificaciones/concepto_eliminar_pago/{id_solicitud}',      [TurnosController::class, 'concepto_eliminar_pago_ratificacion'])->name('concepto_eliminar_pago_ratificacion');
        Route::delete('/ratificaciones/deduccion_eliminar_pago/{id_solicitud}',     [TurnosController::class, 'concepto_eliminar_deduccion_ratificacion'])->name('concepto_eliminar_deduccion_ratificacion');
        Route::delete('/ratificaciones/pago_eliminar_pago/{id_solicitud}',          [TurnosController::class, 'pago_eliminar_pago_ratificacion'])->name('pago_eliminar_pago_ratificacion');
        Route::post('/ratificaciones/terminar_ratificacion',            [TurnosController::class, 'terminar_ratificacion'])->name('terminar_ratificacion');
        
    //Oficialia de Partes
        Route::get('/oficialia/index_oficialia',                                    [SeerController::class, 'index_oficialia'])->name('index_oficialia');
        Route::post('/oficialia/generar_oficialia',                                 [SeerController::class, 'generar_oficialia'])->name('generar_oficialia');
        Route::post('/oficialia/concluir_oficialia',                                [SeerController::class, 'concluir_oficialia'])->name('concluir_oficialia');
        Route::post('/oficialia/turnar_oficialia',                                  [SeerController::class, 'turnar_oficialia'])->name('turnar_oficialia');

    //PDF Ratificaciones
        Route::get('/cumplimiento/PDFIncumplimientoR/{id}',             [TurnosController::class, 'PDFincumplimientoRatificacion'])->name('PDFincumplimientoRatificacion');
        Route::get('/VerpdfcPTUNLaboraRat/{id}',                        [TurnosController::class, 'VerPDFConvenioPTU_rat'])->name('PDFconvenioPTU_NO_R');
        Route::get('/Verpdf/{id}',                                      [TurnosController::class, 'VerPDF'])->name('PDFratifi');
        Route::get('/Verpdfaudiencia/{id}',                             [TurnosController::class, 'VerPDFAudiencia'])->name('PDFaudiencia');
        Route::get('/Verpdfc/{id}',                                     [TurnosController::class, 'VerPDFConvenio'])->name('PDFconvenioratificacion');
        Route::get('/VerpdfIncump/{id}',                                [TurnosController::class, 'VerPDFIncumplimiento'])->name('PDFincumplimiento');
        Route::get('/Verpdfinteres/{id}',                               [TurnosController::class, 'VerPDFInteres'])->name('PDFinteres');
        Route::get('/pdfincomTrabajador/{id}',                          [TurnosController::class, 'VerPDFIncomTrabajador'])->name('PDFincomparecenciaT');
        Route::post('/turnos/guardar',                                  [TurnosController::class, 'guardar_rechazo'])->name('rechazar_turnos');
        Route::get('/Verpdfcump/{id}',                                  [TurnosController::class, 'VerPDFCumplimiento'])->name('PDFcumplimientoR');
        
        Route::get('/ratificaciones/vista_previaCitas/{id_solicitud}',  [TurnosController::class, 'vista_previa_citas'])->name('vista_previa_citas'); //Vista previa de la vista citas(primera parte del llenado de la ratificación)
        Route::post('/ratificaciones/guardarEdicion_citas',             [TurnosController::class, 'guardarEdicion_citas'])->name('guardarEdicion_citas');
        Route::get('/ratificaciones/buscar-abogados-ajax',              [TurnosController::class, 'buscar_abogados_ajax'])->name('buscar_abogados_ajax');
        Route::get('/cumplimiento/rechazara/{id}',                      [SeerController::class, 'cumplimiento_rechazar_audiencia'])->name('cumplimiento_rechazar_audiencia');
        Route::post('/turnos/mostrar',                                  [TurnosController::class, 'mostrar'])->name('turnos_mostrar');
        Route::post('/turnos/archivar',          [TurnosController::class, 'archivar_ratificacion'])->name('archivar_ratificacion');

    //Documentos
            Route::get('/INE_Solicitante/{id}',                         [SeerController::class, 'Ver_INE_Solicitante'])->name('PDF_INE_solicitante');
            Route::get('/documentos/solicitante/identificacion/{id}',   [SeerController::class, 'ver_identificacion_solicitante'])->name('documento_identificacion_solicitante_ver');
            Route::get('/VerDcocumentosRatificacion/{id}',              [TurnosController::class, 'VerDocumentosRatificacion'])->name('VerDocumentosRatificacion');
            Route::get('/documentos/ratificacion/{id}',                 [TurnosController::class, 'ver_documento_subido'])->name('documento_ratificacion_ver');
            Route::get('/documentos/solicitud/{id}',                    [SeerController::class, 'ver_documento_subido'])->name('documento_solicitud_ver');
            Route::get('/VerDcocumentos/{id}',                          [SeerController::class, 'VerDocumentosAudiencia'])->name('VerDocumentosAudiencia');
            Route::get('/documentos/solicitud/{id_solicitud}/{filename}', [SeerController::class, 'verImagenDocumento'])->name('documentos.ver_imagen');
            Route::get('/documento/identificacion-solicitante/{id}',    [SeerController::class, 'documento_identificacion_solicitante_ver'])->name('documento_identificacion_solicitante_ver');
});