<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Routing\Controller as BaseController;
/*
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SeerChatP; 
use App\Models\SeerChatR; 
use App\Models\SeerChatRP;
*/

//use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
//use App\Http\Controllers\PDFController;
use Spatie\Permission\Models\Role; 
use App\Models\User;
use App\Models\Turnos;
use App\Models\TurnoDisponible;
use App\Models\DiasInhabiles;
use App\Models\HorasInhabiles;
use App\Models\Sedes;
use App\Models\Pagos;
use App\Models\Concepto; 
use App\Models\Deducciones;
use App\Models\SeerPerGeneral;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use NumberToWords\NumberToWords; // para convertir números(cantidades) a letras
use DateTime;
use Carbon\Carbon;

class AdministracionController extends Controller{
    public function configuracion()
    {   
        $id = auth()->user()->id;
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
       
        return view('administracion.index_admin', compact('userRole'));
    }

    public function configuracion_sedes()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $sede = $user->delegacion;

        if (!empty($userRole) && ($userRole[0] === "Super Usuario" || $userRole[0] === "Administrador")) {
            $sedes = Sedes::all();
            $conciliadores = User::role('Conciliador')
            ->orderBy('delegacion')
            ->get();
            $bloqueos = DiasInhabiles::orderBy('fecha_inicio','desc')->get();
        } 
        else {
            if($sede == "Morelia"){
                $sedes = Sedes::whereIn('nombre',['Morelia', 'Zitácuaro'])->get();
                $conciliadores = User::role('Conciliador')
                ->whereIn('delegacion', ['Morelia', 'Zitácuaro'])
                ->get();
                $bloqueos = DiasInhabiles::whereIn('centro', ['Morelia', 'Zitácuaro'])
                ->orWhere('user_id', $user->id)
                ->orderBy('fecha_inicio','desc')
                ->get();
            }
            else if($sede == "Uruapan"){
                $sedes = Sedes::whereIn('nombre',['Uruapan', 'Lázaro Cárdenas'])->get();
                $conciliadores = User::role('Conciliador')
                ->whereIn('delegacion', ['Uruapan', 'Lázaro Cárdenas'])
                ->get();
                $bloqueos = DiasInhabiles::whereIn('centro', ['Uruapan', 'Lázaro Cárdenas'])
                ->orWhere('user_id', $user->id)
                ->orderBy('fecha_inicio','desc')
                ->get();
            }
            else if($sede == "Zamora"){
                $sedes = Sedes::whereIn('nombre',['Zamora', 'Sahuayo'])->get();
                $conciliadores = User::role('Conciliador')
                ->whereIn('delegacion', ['Zamora', 'Sahuayo'])
                ->get();
                $bloqueos = DiasInhabiles::whereIn('centro', ['Zamora', 'Sahuayo'])
                ->orWhere('user_id', $user->id)
                ->orderBy('fecha_inicio','desc')
                ->get();
            }
        }

        $dias_inhabiles = 'dias_inhabiles';
        $col_descripcion = 'descripcion';
        $col_tipo = 'tipo';

        $typeDesc = DB::select("SHOW COLUMNS FROM {$dias_inhabiles} WHERE Field = '{$col_descripcion}'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeDesc, $matchesDesc);
        $opciones_descripcion = array_map(function($value) {
            return trim($value, "'");
        }, explode(',', $matchesDesc[1]));

        $typeTipo = DB::select("SHOW COLUMNS FROM {$dias_inhabiles} WHERE Field = '{$col_tipo}'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeTipo, $matchesTipo);
        $opciones_tipo = array_map(function($value) {
            return trim($value, "'");
        }, explode(',', $matchesTipo[1]));
 
        return view('administracion.index_sedes', compact('sedes','conciliadores','bloqueos','opciones_descripcion','opciones_tipo'));
    } 

    public function genera_retroceso()
    {
        return view('administracion.index_retroceso');
    }

    public function consultar_retroceso(Request $request){
        $data = $request->all();
        if($data["tipo"] == "Cumplimiento"){
            $folios = Pagos::where("id_solicitud",$data["folio"])
            ->whereYear("fecha",$data["año"])
            ->select('id','NUE','fecha','descripcion','estatus')
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha->format('Y-m-d H:i:s'),
                    'descripcion' => $folio->descripcion,
                    'estatus' => $folio->estatus,
                ];
            })
            ->toArray();

            if(count($folios) != 0){
                return redirect()->back()
                ->with('message', 'Cumplimientos Encontrados.') // Mensaje general
                ->with('folios_generados', $folios)
                ->with('tipo', $data["tipo"]); // La variable específica
            }
            else{
                return back()->withErrors('No existe el folio y/o año ingresado.');
            }
        }
        else if($data["tipo"] == "Ratificación"){
            $folios = Turnos::where('consecutivo',$data["folio"])
            ->whereYear("fecha",$data["año"])
            ->select('id','NUE','fecha','estatus')
            ->selectRaw("CONCAT(empresa,' ',primero_empresa,' ',segundo_empresa) as empresa")
            ->selectRaw("CONCAT(trabajador,' ',primero_trabajador,' ',segundo_trabajador) as trabajador")
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha,
                    'empresa' => $folio->empresa,
                    'trabajador' => $folio->trabajador,
                    'estatus' => $folio->estatus,
                ];
            })
            ->toArray();

            if(count($folios) != 0){
                return redirect()->back()
                ->with('message', 'Ratificación Encontrada. Al realizar el retroceso se borran las Prestaiones,deduciones y dias de cumplimientos.') // Mensaje general
                ->with('folios_generados', $folios)
                ->with('tipo', $data["tipo"]); // La variable específica
            }
            else{
                return back()->withErrors('El foilio ingresado no existe.');
            }
        }
        else if($data["tipo"] == "Solicitudes"){
            $folios = SeerPerGeneral::where('seer_general.id',$data["folio"])
            ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
            ->whereYear("fecha",$data["año"])
            ->select('seer_general.id','seer_general.NUE','seer_general.fecha','seer_general.estatus','seer_general.estatus','seer_solicitante.nombre')
            //->selectRaw("CONCAT(empresa,' ',primero_empresa,' ',segundo_empresa) as empresa")
            //->selectRaw("CONCAT(trabajador,' ',primero_trabajador,' ',segundo_trabajador) as trabajador")
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha,
                    'empresa' => "Citados",
                    'trabajador' => $folio->nombre,
                    'estatus' => $folio->estatus,
                ];
            })
            ->toArray();

            if(count($folios) != 0){
                return redirect()->back()
                ->with('message', 'Ratificación Encontrada. Al realizar el retroceso se borran las Prestaiones,deduciones y dias de cumplimientos.') // Mensaje general
                ->with('folios_generados', $folios)
                ->with('tipo', $data["tipo"]); // La variable específica
            }
            else{
                return back()->withErrors('Debes seleccionar al menos una Región.');
            }
        }
    }

    public function hacer_retroceso($id){
        Pagos::find($id)->update(['estatus'  => "Concluir"]);
        return redirect()->back()->with('success', 'Puedes realizar tu cumplimiento nuevamente.');
    }

    public function hacer_retroceso_ratificacion($id){
        Turnos::find($id)->update(['estatus'  => "Pendiente"]);
        Pagos::      where("id_solicitud",$id)->delete();
        Concepto::   where('id_solicitud',$id)->delete();
        Deducciones::where('id_solicitud',$id)->where('tipo_pago','Ratificacion')->delete();

        return redirect()->back()->with('success', 'Puedes realizar tu ratificación nuevamente.');
    }
    
    public function bloqueoSede(Request $request)
    {
        // 1. Validación exhaustiva de los datos del formulario unificado
        $request->validate([
            'sede_id'      => 'required|string',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_final'  => 'required|date|after_or_equal:fecha_inicio',
            'tipo'         => 'required|string', // Módulo: Todos, Audiencias, Ratificaciones, etc.
            'descripcion'  => 'required|string',  // Régimen: Inhabil o No inhabil
        ]);

        // 2. Determinar las horas operativas según el switch "Bloquear todo el día"
        // Si viene marcado "todo el día" usamos la jornada completa del Centro de Conciliación
        if ($request->has('bloquear_todo_el_dia')) {
            $horaInicio = '08:00:00';
            $horaFinal  = '16:00:00';
        } else {
            $request->validate([
                'hora_inicio' => 'required',
                'hora_final'  => 'required|after:hora_inicio',
            ]);
            $horaInicio = $request->input('hora_inicio');
            $horaFinal  = $request->input('hora_final');
        }

        $centro = $request->input('sede_id');

        // ====================================================================
        // ESCENARIO 1: PROCESAR BLOQUEOS RECURRENTES (Día por día específico)
        // ====================================================================
        if ($request->has('es_recurrente') && $request->has('dias_semana')) {
            $diasSeleccionados = $request->input('dias_semana'); // Arreglo ej: [1, 4] (Lunes=1, Jueves=4)
            
            $periodo = CarbonPeriod::create($request->fecha_inicio, $request->fecha_final);
            $contadorInsertados = 0;

            foreach ($periodo as $fecha) {
                // Verificar si el día de la semana actual coincide con los seleccionados
                if (in_array($fecha->dayOfWeek, $diasSeleccionados)) {
                    $fechaString = $fecha->toDateString();

                    // Validación de colisión/solapamiento de horarios para este día en particular
                    $existeBloqueo = DiasInhabiles::where('centro', $centro)
                        ->whereNull('user_id')
                        ->where(function($query) use ($fechaString) {
                            $query->whereDate('fecha_inicio', '<=', $fechaString)
                                ->whereDate('fecha_final', '>=', $fechaString);
                        })
                        ->where(function($query) use ($horaInicio, $horaFinal) {
                            $query->where('horario_inicio', '<', $horaFinal)
                                ->where('horario_final', '>', $horaInicio);
                        })
                        ->exists();

                    if (!$existeBloqueo) {
                        DiasInhabiles::create([
                            'fecha_inicio'   => $fechaString,
                            'fecha_final'    => $fechaString, // Al ser recurrente, inicio y fin coinciden en la misma fecha
                            'horario_inicio' => $horaInicio,
                            'horario_final'  => $horaFinal,
                            'centro'         => $centro,
                            'user_id'        => null,
                            'tipo'           => $request->tipo,
                            'descripcion'    => $request->descripcion,
                        ]);
                        $contadorInsertados++;
                    }
                }
            }

            if ($contadorInsertados === 0) {
                return back()->withErrors('No se pudieron crear los bloqueos. Es posible que los días seleccionados ya se encuentren bloqueados o no coincidan con el rango de fechas.');
            }

            return back()->with('success', "Se han generado correctamente {$contadorInsertados} bloqueos recurrentes en el historial.");
        }

        // ====================================================================
        // ESCENARIO 2: BLOQUEO TRADICIONAL (Rango de fechas corrido continuo)
        // ====================================================================
        else {
            // Validación matemática de colisión de Horas y Fechas continuas (Corregido sin whereDate en horas)
            $existeBloqueo = DiasInhabiles::where('centro', $centro)
                ->whereNull('user_id')
                ->whereDate('fecha_inicio', '<=', $request->fecha_final)
                ->whereDate('fecha_final', '>=', $request->request_inicio ?? $request->fecha_inicio)
                ->where(function($query) use ($horaInicio, $horaFinal) {
                    // Regula que las horas no se empalmen
                    $query->where('horario_inicio', '<', $horaFinal)
                        ->where('horario_final', '>', $horaInicio);
                })
                ->exists();

            if ($existeBloqueo) {
                return back()->withErrors('Ya existe una restricción o día inhábil registrado para esta sede que colisiona con las fechas y horarios seleccionados.');
            }

            // Registro del Rango continuo tradicional
            DiasInhabiles::create([
                'fecha_inicio'   => $request->fecha_inicio,
                'fecha_final'    => $request->fecha_final,
                'horario_inicio' => $horaInicio,
                'horario_final'  => $horaFinal,
                'centro'         => $centro,
                'user_id'        => null,
                'tipo'           => $request->tipo,
                'descripcion'    => $request->descripcion,
            ]);

            return back()->with('success', 'La restricción de agenda para la sede se aplicó correctamente.');
        }
    }

    public function bloqueoConciliador(Request $request)
    {
        $request->validate([
            'conciliador_id' => 'required|integer',
            'fecha_inicio'   => 'required|date',
            'fecha_final'    => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio'    => 'required',
            'hora_final'     => 'required|after:hora_inicio',
        ]);
        $existe = DiasInhabiles::where('user_id', $request->conciliador_id)
        ->whereDate('fecha_inicio', '<=', $request->fecha_final)
        ->whereDate('fecha_final', '>=', $request->fecha_inicio)
        ->where('horario_inicio', '<=', $request->hora_final)
        ->where('horario_final', '>=', $request->hora_inicio)
        ->exists();
        if ($existe) {
            return back()->withErrors("El conciliador ya está bloqueado en ese horario.");
        }
        DiasInhabiles::create([
            'fecha_inicio'   => $request->fecha_inicio,
            'fecha_final'    => $request->fecha_final,
            'horario_inicio' => $request->hora_inicio,
            'horario_final'  => $request->hora_final,
            'centro'         => Auth::user()->delegacion,
            'user_id'        => $request->conciliador_id,
            'descripcion'    => $request->descripcion,
            'tipo'           => $request->tipo,
        ]);

        return back()->with('success', 'El conciliador fue bloqueado correctamente.');
    }

    public function eliminarBloqueo($id)
    {
        $bloqueo = DiasInhabiles::find($id);
        if(!$bloqueo){
            return back()->withErrors('El bloqueo no existe.');
        }

        $bloqueo->delete();
        return back()->with('success', 'Bloqueo eliminado correctamente.');
    }

    public function configuracion_usuarios(){
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name')->all();
        $sede = $user->delegacion;

        if($sede == "Morelia"){
            $usuarios = User::role(['Notificador', 'Conciliador','Auxiliar','Excepcion','Delegado'])->whereIn('delegacion', ['Morelia', 'Zitácuaro'])->get();
        }
        else if($sede == "Uruapan"){
            $usuarios = User::role(['Notificador', 'Conciliador','Auxiliar','Excepcion','Delegado'])->whereIn('delegacion', ['Uruapan', 'Lázaro Cárdenas'])->get();
        }
        else if($sede == "Zamora"){
            $usuarios = User::role(['Notificador', 'Conciliador','Auxiliar','Excepcion','Delegado'])->whereIn('delegacion', ['Zamora', 'Sahuayo'])->get();
        }
            
        return view('administracion.index_usuario', compact('usuarios'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->first();
        
        return view('administracion.editar_usuario', compact('user','roles','userRole'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }else {
            $input = Arr::except($input, array('password'));
        }
        
        $user = User::find($id);
        $user->update($input);

        return redirect()->route('configuracion_usuarios');
    }

    public function destroy($id)
    {
        $user = User::find($id)->delete();
        return redirect()->route('configuracion_usuarios');
    }

    public function consular_cumplimientos(){
        return view('administracion.index_cumplimientos');
    }

    public function borrar_cumplimeinto(Request $request){
        $data = $request->all();
        if($data["tipo"] == "Audiencia"){
            $folios = Pagos::where("id_solicitud",$data["folio"])
            ->whereYear("fecha",$data["año"])
            ->where('tipo_pago','Audiencia')
            ->select('id','NUE','fecha','descripcion','estatus')
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha->format('Y-m-d H:i:s'),
                    'descripcion' => $folio->descripcion,
                    'estatus' => $folio->estatus,
                ];
            })
            ->toArray();

            if(count($folios) != 0){
                return redirect()->back()
                ->with('message', 'Cumplimientos Encontrados.') // Mensaje general
                ->with('folios_generados', $folios)
                ->with('tipo', $data["tipo"]); // La variable específica
            }
            else{
                return back()->withErrors('No existe el folio y/o año ingresado.');
            }
        }
        else if($data["tipo"] == "Ratificación"){
            $folios = Pagos::where("id_solicitud",$data["folio"])
            ->whereYear("fecha",$data["año"])
            ->where('tipo_pago','Ratificacion')
            ->select('id','NUE','fecha','descripcion','estatus')
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha->format('Y-m-d H:i:s'),
                    'descripcion' => $folio->descripcion,
                    'estatus' => $folio->estatus,
                ];
            })
            ->toArray();

            if(count($folios) != 0){
                return redirect()->back()
                ->with('message', 'Cumplimientos Encontrados.') // Mensaje general
                ->with('folios_generados', $folios)
                ->with('tipo', $data["tipo"]); // La variable específica
            }
            else{
                return back()->withErrors('No existe el folio y/o año ingresado.');
            }
        }
        else{
            return back()->withErrors('Debes seleccionar un tipo de cumplimiento.');
        }
    }

    public function destroy_cumplimientoA($id){
        Pagos::find($id)->update(['tipo_pago'  => "Borrado"]);
        return back()->with('success', 'Cumplimeinto borrado correctamente.');
    }

    public function obtenerBloqueosCalendario(Request $request)
    {
        try {
            $sedeFiltro = $request->input('sede');
            $conciliadorFiltro = $request->input('conciliador');

            $mapaSedes = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo'],
            ];

            // Acotamos la consulta a los 4 meses posteriores para máxima optimización
            $fechaHoy = Carbon::now()->toDateString();
            $fechaLimiteCuatroMeses = Carbon::now()->addMonths(4)->toDateString();

            $query = DiasInhabiles::query();

            $query->where(function($q) use ($fechaHoy, $fechaLimiteCuatroMeses) {
                $q->whereBetween('fecha_inicio', [$fechaHoy, $fechaLimiteCuatroMeses])
                ->orWhereBetween('fecha_final', [$fechaHoy, $fechaLimiteCuatroMeses])
                ->orWhere(function($sub) use ($fechaHoy, $fechaLimiteCuatroMeses) {
                    $sub->where('fecha_inicio', '<=', $fechaHoy)
                        ->where('fecha_final', '>=', $fechaLimiteCuatroMeses);
                });
            });

            if (!empty($sedeFiltro)) {
                $sedesAsociadas = $mapaSedes[$sedeFiltro] ?? [$sedeFiltro];
                $query->whereIn('centro', $sedesAsociadas);
            }

            if (!empty($conciliadorFiltro)) {
                $query->where(function($q) use ($conciliadorFiltro) {
                    $q->where('user_id', $conciliadorFiltro)
                    ->orWhereNull('user_id');
                });
            }

            $bloqueos = $query->get();
            $eventos = [];

            foreach ($bloqueos as $b) {
                $esInhabilCompleto = ($b->descripcion === 'Inhabil');
                
                // Determinamos si es jornada completa (Día Inhábil o bloqueo de 8 a 15)
                $esJornadaCompleta = ($esInhabilCompleto || ($b->horario_inicio === '08:00:00' && $b->horario_final === '15:00:00'));

                if (is_null($b->user_id) && $esInhabilCompleto) {
                    // ====================================================================
                    // CASO 1: DÍA INHÁBIL EN LA SEDE -> SE ENVÍA A LA SECCIÓN ALL-DAY
                    // ====================================================================
                    $eventos[] = [
                        'id'            => 'bloqueo_sede_' . $b->id,
                        'title'         => 'Día Inhábil', // Leyenda corta solicitada
                        'start'         => $b->fecha_inicio,
                        // CORRECCIÓN CRÍTICA: Se le suma 1 día a la fecha final para que FullCalendar v6 marque todos los días seguidos
                        'end'           => Carbon::parse($b->fecha_final)->addDay()->toDateString(),
                        'allDay'        => true, // Fuerza a que se pinte arriba en la sección all-day
                        'extendedProps' => [
                            'tipo'    => 'BloqueoAgenda',
                            'regimen' => 'Inhabil',
                            'motivo'  => $b->motivo ?? 'Suspensión oficial de labores',
                            'modulo'  => $b->tipo
                        ]
                    ];
                } else {
                    // ====================================================================
                    // CASO 2: BLOQUEOS PARCIALES O POR CONCILIADOR
                    // ====================================================================
                    $titulo = $esInhabilCompleto ? "Conciliador Inactivo" : "Hora Bloqueada";

                    $eventos[] = [
                        'id'            => 'bloqueo_' . $b->id,
                        'title'         => $titulo,
                        // Si es jornada completa va al all-day, si no, se concatena su hora correspondiente
                        'start'         => $esJornadaCompleta ? $b->fecha_inicio : $b->fecha_inicio . 'T' . $b->horario_inicio,
                        'end'           => $esJornadaCompleta ? Carbon::parse($b->fecha_final)->addDay()->toDateString() : $b->fecha_final . 'T' . $b->horario_final,
                        'allDay'        => $esJornadaCompleta,
                        'extendedProps' => [
                            'tipo'    => 'BloqueoAgenda',
                            'regimen' => $b->descripcion,
                            'motivo'  => $b->motivo ?? 'Restricción programada',
                            'modulo'  => $b->tipo
                        ]
                    ];
                }
            }

            return response()->json($eventos);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}