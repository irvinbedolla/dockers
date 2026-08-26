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
use App\Models\Audiencias;
use App\Models\SeerSolicitante;
use App\Models\SeerCitados;
use App\Models\PermisosConciliador;
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
        $request->validate([
            'tipo'  => 'required|string|in:Cumplimiento,Ratificación,Solicitudes',
            'folio' => 'required|integer|min:1',
            'año'   => 'required|integer|min:' . (date('Y') - 3) . '|max:' . date('Y'),
        ], [
            'tipo.required'  => 'Debe seleccionar un tipo de retroceso.',
            'tipo.in'        => 'El tipo de retroceso seleccionado no es válido.',
            'folio.required' => 'El folio es obligatorio.',
            'folio.integer'  => 'El folio debe ser un número entero.',
            'folio.min'      => 'El folio debe ser un número positivo mayor a 0.',
            'año.required'   => 'Debe seleccionar un año.',
            'año.integer'    => 'El año debe ser un número válido.',
            'año.min'        => 'El año seleccionado está fuera del rango permitido.',
            'año.max'        => 'El año seleccionado está fuera del rango permitido.',
        ]);

        $data = $request->all();
        if($data["tipo"] == "Cumplimiento"){
            $folios = Pagos::where("id_solicitud",$data["folio"])
            ->whereYear("fecha",$data["año"])
            ->select('id','NUE','fecha','descripcion','estatus','delegacion','nombre_trabajador')
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha->format('Y-m-d H:i:s'),
                    'descripcion' => $folio->descripcion,
                    'solicitante' => $folio->nombre_trabajador,
                    'delegacion' => $folio->delegacion,
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
            ->select('id','NUE','fecha','estatus','delegacion')
            ->selectRaw("CONCAT(empresa,' ',primero_empresa,' ',segundo_empresa) as empresa")
            ->selectRaw("CONCAT(trabajador,' ',primero_trabajador,' ',segundo_trabajador) as trabajador")
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha,
                    'empresa' => $folio->empresa,
                    'solicitante' => $folio->trabajador,
                    'delegacion' => $folio->delegacion,
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
            ->select('seer_general.id','seer_general.NUE','seer_general.fecha','seer_general.estatus','seer_solicitante.nombre','seer_general.delegacion')
            //->selectRaw("CONCAT(empresa,' ',primero_empresa,' ',segundo_empresa) as empresa")
            //->selectRaw("CONCAT(trabajador,' ',primero_trabajador,' ',segundo_trabajador) as trabajador")
            ->get()
            ->map(function ($folio) {
                return [
                    'id' => $folio->id,
                    'NUE' => $folio->NUE,
                    'fecha' => $folio->fecha,
                    'empresa' => "Citados",
                    'solicitante' => $folio->nombre,
                    'delegacion' => $folio->delegacion,
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

    public function hacer_retroceso_cumplimiento($id){
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
    private function prefijosDelegacion(): array
    {
        return [
            'MOR' => 'Morelia',
            'URU' => 'Uruapan',
            'ZAM' => 'Zamora',
            'ZIT' => 'Zitácuaro',
            'LZC' => 'Lázaro Cárdenas',
            'SAH' => 'Sahuayo',
        ];
    }
    public function cambio_audiencia(){
        $delegaciones = $this->prefijosDelegacion();

        return view('administracion.index_audiencia', compact('delegaciones'));
    }
    public function fecha_audiencia_buscar(Request $request)
    {
        $prefijos = array_keys($this->prefijosDelegacion());

        $request->validate([
            'delegacion'  => 'required|string|in:' . implode(',', $prefijos),
            'anio'        => 'required|integer|min:' . (date('Y') - 5) . '|max:' . date('Y'),
            'consecutivo' => 'required|integer|min:1',
        ], [
            'delegacion.required'  => 'Debe seleccionar la delegación del NUE.',
            'delegacion.in'        => 'La delegación seleccionada no es válida.',
            'anio.required'        => 'Debe seleccionar el año del NUE.',
            'anio.min'             => 'El año seleccionado está fuera del rango permitido.',
            'anio.max'             => 'El año seleccionado está fuera del rango permitido.',
            'consecutivo.required' => 'El consecutivo es obligatorio.',
            'consecutivo.integer'  => 'El consecutivo debe ser un número entero.',
            'consecutivo.min'      => 'El consecutivo debe ser mayor a 0.',
        ]);

        // El NUE se arma igual que en GeneraExpediente(): MOR/RAT/2026/00576
        $nue = $request->delegacion . '/SOL/' . $request->anio . '/'
             . str_pad($request->consecutivo, 5, '0', STR_PAD_LEFT);

        $solicitud = SeerPerGeneral::where('NUE', $nue)->first();
        $audiencia = Audiencias::where('id_solicitud', $solicitud->id)->orderByDesc('id')->first();
        $solicitante = SeerSolicitante::where('id_solicitud', $solicitud->id)->pluck('nombre')->first();
        $citado = SeerCitados::where('id_solicitud', $solicitud->id)->first();
        $conciliador = User::where('id', $audiencia->id_conciliador)->pluck('name')->first();
        if (!$audiencia) {
            return back()->withErrors("No se encontró ninguna audiencia con el NUE {$nue}.");
        }
        $resultado =[];

        $resultado= [
            'id'          => $audiencia->id,
            'NUE'         => $solicitud->NUE,
            'fecha'       => $audiencia->fecha,
            'hora'        => $audiencia->hora,
            'delegacion'  => $solicitud->delegacion,
            'estatus'     => $audiencia->estatus,
            'solicitante'  => $solicitante,
            'citados'     => trim($citado->nombre . ' ' . $citado->primer_apellido . ' ' . $citado->segundo_apellido),
            'conciliador'   => $conciliador,
            'id_conciliador' => $audiencia->id_conciliador,
        ];

        return back()
            ->with('message', "Audiencia localizada: {$nue}")
            ->with('folio', $resultado);
    }
    public function cambiar_fecha(Request $request){
        $data = $request->all();
        //$data['audiencia_id'], $data["fecha"],$data["hora"],
        $audienciaOld = Audiencias::where('id', $data["id_audiencia"])->first();
        $audienciaOld->update([
                    'fecha' => $data["fecha"],
                    'hora'  => $data["hora"],
                ]);
        return redirect()->route('cambio_fecha_audiencia');
    }
    public function obtenerAudienciasConciliador(Request $request)
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
        $audiencia_id = (int) $request->input('audiencia');
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
            if ($sede === 'Zitácuaro') {
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

        $duracionSlotMinutos = 75;

        $horasBase = [[9, 0], [10, 15], [12, 0], [14, 15], [15, 30]];

        $audienciasExistentes = Audiencias::whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->where('id_conciliador', $id_conciliador)
            ->selectRaw('DATE(fecha) as fecha_dia, TIME(hora) as hora_inicio')
            ->get();

        $audiencia = Audiencias::where('id', $audiencia_id)->first();
        $audienciaActualStart = null;
        if ($audiencia && $audiencia->fecha && $audiencia->hora) {
            $fechaAud = (new \DateTime($audiencia->fecha))->format('Y-m-d');
            $horaAud = (new \DateTime($audiencia->hora))->format('H:i:s');
            $audienciaActualStart = $fechaAud . 'T' . $horaAud;
        }
        
        $audienciasPorFecha = [];
        foreach ($audienciasExistentes as $audienciaExistente) {
            $audienciasPorFecha[$audienciaExistente->fecha_dia][] = $audienciaExistente->hora_inicio;
        }

        $ahora = new \DateTime();

        $todosLosEventos = [];
        $fecha = (new \DateTime($fecha_inicio_str))->setTime(0,0,0);
        $fin_loop = (new \DateTime($fecha_fin_str))->setTime(0,0,0);

        while ($fecha <= $fin_loop) {
            if ($fecha->format('N') < 6) { // Saltar fines de semana

                $fechaDia = $fecha->format('Y-m-d');

                $horarios = array_map(
                    fn ($h) => (clone $fecha)->setTime($h[0], $h[1], 0),
                    $horasBase
                );

                foreach ($horarios as $horario) {
                    $slot = $horario;
                    $slotStart = $slot->format('Y-m-d\TH:i:s');
                    $slotFin = (clone $slot)->modify("+{$duracionSlotMinutos} minutes");
                    $slotEnd = $slotFin->format('Y-m-d\TH:i:s');

                    $audienciasEnSlot = 0;
                    foreach ($audienciasPorFecha[$fechaDia] ?? [] as $horaExistente) {
                        $existenteInicio = new \DateTime($fechaDia . ' ' . $horaExistente);
                        $existenteFin = (clone $existenteInicio)->modify("+{$duracionSlotMinutos} minutes");
                        // Traslape de intervalos semiabiertos [inicio, fin)
                        if ($existenteInicio < $slotFin && $slot < $existenteFin) {
                            $audienciasEnSlot++;
                        }
                    }
                    $ocupado = $audienciasEnSlot >= 1;

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
                    if ($audienciaActualStart && $slotStart === $audienciaActualStart) {
                        $estado = 'actual';
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
                        'ocupado' => '#eca130', 'inhabil' => '#3B78DB',
                        'expirado' => '#969696', 'disponible' => '#00CE1C',
                        'actual'  => '#8163a8',
                    ];
                    $titulos = [
                        'ocupado' => 'Ocupado', 'inhabil' => 'Inhábil',
                        'expirado' => 'No disponible', 'disponible' => 'Disponible',
                        'actual' => 'Actual'
                    ];

                    $titulo = $titulos[$estado];
                    

                    $todosLosEventos[] = [
                        'title' => $titulo,
                        'start' => $slotStart,
                        'end' => $slotEnd,
                        'color' => $colores[$estado],
                        'extendedProps' => [
                            'estado' => $estado,
                            'audiencias_en_slot' => $audienciasEnSlot,
                        ]
                    ];
                }
            }
            $fecha->modify('+1 day');
        }

        return response()->json($todosLosEventos);
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
        ]);

        // Permisos, incapacidades y vacaciones son de días enteros: antes había que
        // teclear el horario a mano en todos los casos.
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

        $conciliador = User::find($request->conciliador_id);

        if (!$conciliador) {
            return back()->withErrors('El conciliador no existe.');
        }

        $existe = DiasInhabiles::where('user_id', $request->conciliador_id)
        ->whereDate('fecha_inicio', '<=', $request->fecha_final)
        ->whereDate('fecha_final', '>=', $request->fecha_inicio)
        ->where('horario_inicio', '<', $horaFinal)
        ->where('horario_final', '>', $horaInicio)
        ->exists();
        if ($existe) {
            return back()->withErrors("El conciliador ya está bloqueado en ese horario.");
        }
        DiasInhabiles::create([
            'fecha_inicio'   => $request->fecha_inicio,
            'fecha_final'    => $request->fecha_final,
            'horario_inicio' => $horaInicio,
            'horario_final'  => $horaFinal,
            // Antes se guardaba Auth::user()->delegacion, es decir la sede de quien
            // capturaba: los bloqueos de una persona quedaban repartidos entre varios
            // centros. Los registros nuevos usan la delegación del bloqueado.
            'centro'         => $conciliador->delegacion ?: Auth::user()->delegacion,
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

    /**
     * Sedes que ve cada delegación. Antes este mapa estaba duplicado dentro de
     * obtenerBloqueosCalendario; vive aquí para que el calendario por sede y el
     * endpoint de eventos usen exactamente el mismo criterio.
     */
    private function mapaSedes(): array
    {
        return [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo'],
        ];
    }

    /**
     * Un bloqueo cubre la jornada completa cuando abarca de las 8 a las 15 o más.
     * El alta guarda 16:00 cuando se marca "bloquear todo el día", pero la lógica
     * vieja comparaba contra 15:00 exacto y esos bloqueos se pintaban como si
     * fueran de horario parcial.
     */
    private function esJornadaCompleta($horaInicio, $horaFinal): bool
    {
        if (empty($horaInicio) || empty($horaFinal)) {
            return true;
        }

        return $horaInicio <= '08:00:00' && $horaFinal >= '15:00:00';
    }

    /**
     * Calendario de bloqueos de una sede concreta.
     */
    public function calendarioSede($id)
    {
        $sede = Sedes::findOrFail($id);
        $user = auth()->user();
        $roles = $user->getRoleNames()->all();

        // Fuera de Super Usuario / Administrador solo se ve la región propia.
        if (!in_array('Super Usuario', $roles) && !in_array('Administrador', $roles)) {
            $permitidas = $this->mapaSedes()[$user->delegacion] ?? [$user->delegacion];

            if (!in_array($sede->nombre, $permitidas)) {
                abort(403, 'No tienes acceso al calendario de esta sede.');
            }
        }

        // Las oficinas de apoyo (Zitácuaro, Lázaro Cárdenas, Sahuayo) no tienen
        // conciliadores propios: se listan los de su sede madre.
        $delegacionesConciliadores = [$sede->nombre];

        if (!empty($sede->oficina_apoyo)) {
            $madre = Sedes::find($sede->oficina_apoyo);

            if ($madre) {
                $delegacionesConciliadores[] = $madre->nombre;
            }
        }

        $conciliadores = User::role('Conciliador')
            ->whereIn('delegacion', $delegacionesConciliadores)
            ->orderBy('name')
            ->get();

        // Jornada semanal de cada conciliador, para sombrear en el calendario las
        // horas en las que de entrada no atiende. Se manda completa a la vista
        // porque el selector cambia de persona sin recargar la página.
        $horarios = PermisosConciliador::whereIn('id_conciliador', $conciliadores->pluck('id'))
            ->get()
            ->keyBy('id_conciliador');

        $jornadas = [];

        foreach ($conciliadores as $con) {
            $jornadas[$con->id] = $this->jornadaSemanal($horarios->get($con->id));
        }

        $bloqueos = DiasInhabiles::where('centro', $sede->nombre)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('administracion.calendario_sede', compact('sede', 'conciliadores', 'bloqueos', 'jornadas'));
    }

    /**
     * Traduce una fila de permisos_conciliador al formato businessHours de
     * FullCalendar. Devuelve null cuando no hay horario capturado, para no
     * sombrear un calendario del que no sabemos nada.
     */
    private function jornadaSemanal($permiso)
    {
        if (!$permiso) {
            return null;
        }

        $dias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
        ];

        $jornada = [];

        foreach ($dias as $numero => $nombre) {
            if (strtolower((string) $permiso->{$nombre}) !== 'si') {
                continue;
            }

            $jornada[] = [
                'daysOfWeek' => [$numero],
                'startTime'  => substr((string) $permiso->{$nombre . '_inicio'}, 0, 5) ?: '09:00',
                'endTime'    => substr((string) $permiso->{$nombre . '_final'}, 0, 5) ?: '15:30',
            ];
        }

        return empty($jornada) ? [] : $jornada;
    }

    /**
     * Edición de un bloqueo existente. Antes solo existían alta y baja: para
     * corregir una fecha había que borrar y volver a capturar.
     */
    public function actualizarBloqueo(Request $request, $id)
    {
        $bloqueo = DiasInhabiles::find($id);

        if (!$bloqueo) {
            return back()->withErrors('El bloqueo no existe.');
        }

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_final'  => 'required|date|after_or_equal:fecha_inicio',
            'tipo'         => 'required|string',
            'descripcion'  => 'required|string',
        ]);

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

        // Misma validación de solapamiento que el alta, excluyendo el propio registro
        // y comparando contra bloqueos del mismo ámbito (sede completa o conciliador).
        $existe = DiasInhabiles::where('centro', $bloqueo->centro)
            ->where('id', '<>', $bloqueo->id)
            ->when(is_null($bloqueo->user_id),
                function ($q) { return $q->whereNull('user_id'); },
                function ($q) use ($bloqueo) { return $q->where('user_id', $bloqueo->user_id); }
            )
            ->whereDate('fecha_inicio', '<=', $request->fecha_final)
            ->whereDate('fecha_final', '>=', $request->fecha_inicio)
            ->where(function ($q) use ($horaInicio, $horaFinal) {
                $q->where('horario_inicio', '<', $horaFinal)
                  ->where('horario_final', '>', $horaInicio);
            })
            ->exists();

        if ($existe) {
            return back()->withErrors('Los cambios colisionan con otro bloqueo ya registrado en esta sede.');
        }

        $bloqueo->update([
            'fecha_inicio'   => $request->fecha_inicio,
            'fecha_final'    => $request->fecha_final,
            'horario_inicio' => $horaInicio,
            'horario_final'  => $horaFinal,
            'tipo'           => $request->tipo,
            'descripcion'    => $request->descripcion,
        ]);

        return back()->with('success', 'El bloqueo se actualizó correctamente.');
    }

    public function obtenerBloqueosCalendario(Request $request)
    {
        try {
            $sedeFiltro = $request->input('sede');
            $conciliadorFiltro = $request->input('conciliador');
            // sede_exacta=1 consulta una sola sede en lugar del grupo regional.
            $sedeExacta = $request->boolean('sede_exacta');

            $mapaSedes = $this->mapaSedes();

            // FullCalendar manda el rango visible en cada navegación. Antes esto
            // estaba fijo en hoy..+4 meses, así que al retroceder de mes el
            // calendario se veía vacío aunque hubiera bloqueos.
            $inicioRango = $request->input('start')
                ? Carbon::parse($request->input('start'))->toDateString()
                : Carbon::now()->toDateString();

            $finRango = $request->input('end')
                ? Carbon::parse($request->input('end'))->toDateString()
                : Carbon::now()->addMonths(4)->toDateString();

            $query = DiasInhabiles::query();

            $query->where(function($q) use ($inicioRango, $finRango) {
                $q->whereBetween('fecha_inicio', [$inicioRango, $finRango])
                ->orWhereBetween('fecha_final', [$inicioRango, $finRango])
                ->orWhere(function($sub) use ($inicioRango, $finRango) {
                    $sub->where('fecha_inicio', '<=', $inicioRango)
                        ->where('fecha_final', '>=', $finRango);
                });
            });

            $sedesAsociadas = !empty($sedeFiltro)
                ? ($sedeExacta ? [$sedeFiltro] : ($mapaSedes[$sedeFiltro] ?? [$sedeFiltro]))
                : null;

            if (!empty($conciliadorFiltro)) {
                // Los bloqueos de conciliador se guardan con el 'centro' de quien los
                // captura, no con la delegación del bloqueado, así que filtrar por
                // centro esconde parte de su agenda. Sus bloqueos se buscan por
                // user_id y aparte se suman los de la sede consultada.
                $query->where(function($q) use ($conciliadorFiltro, $sedesAsociadas) {
                    $q->where('user_id', $conciliadorFiltro)
                      ->orWhere(function($sub) use ($sedesAsociadas) {
                          $sub->whereNull('user_id');
                          if ($sedesAsociadas) {
                              $sub->whereIn('centro', $sedesAsociadas);
                          }
                      });
                });
            } elseif ($sedesAsociadas) {
                $query->whereIn('centro', $sedesAsociadas);
            }

            $bloqueos = $query->get();

            // Nombres de los conciliadores bloqueados, para etiquetar el evento.
            $nombresConciliador = User::whereIn('id', $bloqueos->pluck('user_id')->filter()->unique())
                ->pluck('name', 'id');

            $eventos = [];

            foreach ($bloqueos as $b) {
                $esInhabilCompleto = ($b->descripcion === 'Inhabil');
                $esJornadaCompleta = ($esInhabilCompleto || $this->esJornadaCompleta($b->horario_inicio, $b->horario_final));
                $esDeSede = is_null($b->user_id);

                if ($esDeSede) {
                    $titulo = $esInhabilCompleto ? 'Día Inhábil' : 'Horario bloqueado';
                    $color  = $esInhabilCompleto ? '#6A0F49' : '#B5824A';
                    $clase  = $esInhabilCompleto ? 'evt-inhabil' : 'evt-horario';
                } else {
                    $nombre = $nombresConciliador[$b->user_id] ?? 'Conciliador';
                    $titulo = $esInhabilCompleto ? $nombre . ' — inactivo' : $nombre . ' — bloqueado';
                    $color  = '#496163';
                    $clase  = 'evt-conciliador';
                }

                $eventos[] = [
                    // El id lleva prefijo por compatibilidad con lo que ya existía;
                    // el id real del registro va en extendedProps para poder editar.
                    'id'              => ($esDeSede ? 'bloqueo_sede_' : 'bloqueo_') . $b->id,
                    'title'           => $titulo,
                    // FullCalendar v6 trata 'end' como exclusivo en eventos de día
                    // completo: sin el +1 día el último día no se pinta.
                    'start'           => $esJornadaCompleta ? $b->fecha_inicio : $b->fecha_inicio . 'T' . $b->horario_inicio,
                    'end'             => $esJornadaCompleta
                                            ? Carbon::parse($b->fecha_final)->addDay()->toDateString()
                                            : $b->fecha_final . 'T' . $b->horario_final,
                    'allDay'          => $esJornadaCompleta,
                    // backgroundColor/borderColor los usa el dashboard; classNames deja
                    // que el calendario por sede pinte sus propias píldoras con CSS.
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'classNames'      => (!empty($conciliadorFiltro) && $esDeSede)
                                            ? [$clase, 'evt-contexto']
                                            : [$clase],
                    'extendedProps'   => [
                        'tipo'           => 'BloqueoAgenda',
                        'bloqueo_id'     => $b->id,
                        'ambito'         => $esDeSede ? 'sede' : 'conciliador',
                        'conciliador'    => $esDeSede ? null : ($nombresConciliador[$b->user_id] ?? null),
                        'centro'         => $b->centro,
                        'regimen'        => $b->descripcion,
                        'modulo'         => $b->tipo,
                        'fecha_inicio'   => $b->fecha_inicio,
                        'fecha_final'    => $b->fecha_final,
                        'horario_inicio' => $b->horario_inicio,
                        'horario_final'  => $b->horario_final,
                        'jornada'        => $esJornadaCompleta,
                        // Cuando se consulta a un conciliador, sus bloqueos son los
                        // editables y los de la sede solo dan contexto.
                        'contexto'       => (!empty($conciliadorFiltro) && $esDeSede),
                    ]
                ];
            }

            return response()->json($eventos);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}