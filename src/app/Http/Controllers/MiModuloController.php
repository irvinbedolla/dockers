<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\User;
use App\Support\FlujoCita;
use App\Support\FolioCita;
use App\Support\HorarioCitas;
use App\Support\PantallaEspera;
use App\Support\SigueEnModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "Mi módulo" (flujo cita → solicitud): lo que tiene hoy el módulo de quien
 * entra. Turno actual, el que sigue con cuándo empieza y la lista del día
 * (fase 1), el prellenado de la solicitud (fase 2), el aviso de quién sigue
 * en todas las pantallas (fase 3) y los botones Llamar / Iniciar atención /
 * Terminar / No se presentó que mueven la pantalla de espera (fase 4).
 *
 * El turno actual y el siguiente salen de la misma regla que la pantalla de
 * espera (PantallaEspera::turnoDeModulo), para que el auxiliar y la sala vean
 * lo mismo; los tiempos, de la duración en Horarios de citas (SigueEnModulo).
 *
 * Lo ve quien tiene módulo asignado (orientadores y auxiliares) y el Super
 * Usuario.
 */
class MiModuloController extends Controller
{
    /** Acciones de la fase 4 (ruta mi_modulo.accion). */
    public const ACCIONES = ['llamar', 'atender', 'terminar', 'ausente'];

    private const VIGENTES = ['pendiente', 'confirmada'];

    /** Estatus de turnos que "Terminar" no debe regresar a Confirmado. */
    private const RATIFICACION_RESUELTA = ['Concluida', 'Concluida Pagos', 'Archivada', 'Incumplimiento'];

    public static function puedeVer(?User $usuario): bool
    {
        if ($usuario === null || ! FlujoCita::migrado() || ! FlujoCita::habilitadoPara($usuario)) {
            return false;
        }

        return $usuario->hasRole('Super Usuario') || self::propios($usuario)->isNotEmpty();
    }

    public function index(Request $request)
    {
        abort_unless(self::puedeVer($request->user()), 403);

        [$modulo, $opciones] = $this->elegir($request);

        return view('mi_modulo.index', [
            'modulo'   => $modulo,
            'opciones' => $opciones,
            'datos'    => $modulo ? $this->armar($modulo) : null,
            'urlDatos' => $modulo ? route('mi_modulo.datos', ['modulo' => $modulo->id]) : null,
        ]);
    }

    public function datos(Request $request)
    {
        abort_unless(self::puedeVer($request->user()), 403);

        [$modulo] = $this->elegir($request);
        abort_unless($modulo, 404);

        return response()->json($this->armar($modulo))->header('Cache-Control', 'no-store');
    }

    /**
     * Fase 2: "Iniciar solicitud" con los datos de la cita. Guarda el
     * prellenado en la sesión y manda a la captura de siempre.
     */
    public function prellenar(Request $request, int $id)
    {
        abort_unless(self::puedeVer($request->user()), 403);

        $cita = \App\Models\Recepcion::findOrFail($id);

        abort_unless($this->esDeSusModulos($request->user(), $cita), 403);
        abort_unless(FlujoCita::aplicaA($cita), 403, 'El flujo nuevo no aplica a esta cita.');
        // Una asesoría en atención puede seguir como solicitud ("Continuar como
        // Solicitud") si al final la persona decide levantarla.
        $continuaAsesoria = self::esAsesoria($cita);
        abort_unless($cita->tipo === 'Solicitud' || $continuaAsesoria, 422, 'Sólo las citas de solicitud o asesoría se prellenan.');
        abort_if($continuaAsesoria && ! (in_array($cita->estatus, self::VIGENTES, true) && $cita->atencion_inicio_at), 422,
            'Primero inicia la atención de la asesoría en Mi módulo.');

        if (FlujoCita::aplicaA($cita) && ! $cita->llamado_at && ($abierta = $this->abiertaEnModulo($cita))) {
            return back()->with('error', 'Primero termina o marca como "no se presentó" el folio '.FolioCita::mostrar($abierta).'.');
        }

        \App\Support\PrellenadoCita::preparar($cita);

        // Empieza la atención si aún no lo estaba; si nadie la había llamado,
        // queda llamada para que la pantalla muestre el folio en su módulo.
        $cambios = array_filter([
            'atencion_inicio_at' => $cita->atencion_inicio_at ? null : now(),
            'llamado_at'         => $cita->llamado_at ? null : now(),
        ]);
        if ($cambios) {
            $cita->forceFill($cambios)->save();
            PantallaEspera::olvidar((string) $cita->delegacion);
        }

        return redirect()->route('solicitud');
    }

    /**
     * Fase 4: lo que hace el auxiliar con una cita del flujo nuevo.
     *
     *   llamar   → la pantalla muestra el folio en su módulo y lo anuncia
     *              (otra vez, si ya estaba llamada);
     *   atender  → empezó la atención (si no la había llamado, la llama);
     *   terminar → estatus "atendido";
     *   ausente  → no se presentó: estatus "expirada", como cuando no llega
     *              a escanear su QR a tiempo (ValidadorCita).
     *
     * Un módulo atiende de una en una: no se llama otra mientras haya una
     * llamada sin terminar.
     */
    public function accion(Request $request, int $id, string $accion)
    {
        $usuario = $request->user();
        abort_unless(self::puedeVer($usuario), 403);
        abort_unless(in_array($accion, self::ACCIONES, true), 404);

        $cita = DB::transaction(function () use ($usuario, $id, $accion) {
            $cita = \App\Models\Recepcion::lockForUpdate()->findOrFail($id);

            abort_unless($this->esDeSusModulos($usuario, $cita), 403, 'La cita no es de tu módulo.');
            abort_unless(FlujoCita::aplicaA($cita), 422, 'El flujo nuevo no aplica a esta cita.');
            abort_unless($cita->modulo_id, 422, 'La cita no tiene módulo asignado.');
            abort_unless($cita->fecha && $cita->fecha->isToday(), 422, 'Sólo se atienden citas de hoy.');
            abort_unless(in_array($cita->estatus, self::VIGENTES, true), 422, 'Esta cita ya no está en espera (estatus: '.$cita->estatus.').');

            $ahora = now();

            if (in_array($accion, ['llamar', 'atender'], true) && ! $cita->llamado_at && ($abierta = $this->abiertaEnModulo($cita, true))) {
                abort(409, 'Primero termina o marca como "no se presentó" el folio '.FolioCita::mostrar($abierta).'.');
            }

            switch ($accion) {
                case 'llamar':
                    abort_if($cita->atencion_inicio_at, 422, 'Ya está en atención.');
                    $cita->llamado_at = $ahora;
                    break;

                case 'atender':
                    $cita->llamado_at ??= $ahora;
                    $cita->atencion_inicio_at ??= $ahora;
                    break;

                case 'terminar':
                    $cita->llamado_at ??= $ahora;
                    $cita->atencion_inicio_at ??= $ahora;
                    $cita->estatus = 'atendido';
                    if (FlujoCita::conFinAtencion()) {
                        $cita->atencion_fin_at = $ahora;
                    }
                    break;

                case 'ausente':
                    abort_if($cita->atencion_inicio_at, 422, 'Ya está en atención; usa "Terminar".');
                    $cita->estatus = 'expirada';
                    break;
            }

            $cita->save();

            return $cita;
        });

        PantallaEspera::olvidar((string) $cita->delegacion);

        // Ratificaciones: al iniciar se abre el formulario de la cita con los
        // datos de su turno; al terminar, la ratificación queda confirmada.
        $turno = self::esRatificacion($cita) && in_array($accion, ['atender', 'terminar'], true)
            ? $this->turnoDeCita($cita)
            : null;
        // Si la ratificación ya se concluyó (o archivó) antes de "Terminar", no se
        // regresa a Confirmado: eso reabría el botón Concluir y duplicaba conceptos.
        $confirmarTurno = $turno && $accion === 'terminar'
            && ! in_array($turno->estatus, self::RATIFICACION_RESUELTA, true);
        if ($confirmarTurno) {
            \App\Services\HistorialRatificacionRecorder::actualizar($turno, ['estatus' => 'Confirmado'],
                'Atención terminada en Mi módulo (folio '.FolioCita::mostrar($cita).')');
        }

        \Illuminate\Support\Facades\Log::info('flujo_cita.'.$accion, [
            'cita'    => $cita->id,
            'folio'   => FolioCita::mostrar($cita),
            'modulo'  => $cita->modulo_id,
            'usuario' => $usuario->id,
            'prueba'  => (bool) $cita->es_prueba,
        ]);

        $folio = FolioCita::mostrar($cita);
        $mensajes = [
            'llamar'   => 'Se llamó el folio '.$folio.' en la pantalla.',
            'atender'  => 'Atención iniciada: '.$folio.'.',
            'terminar' => 'Folio '.$folio.' atendido.',
            'ausente'  => 'Folio '.$folio.' marcado como no presentado.',
        ];

        if (self::esRatificacion($cita) && $accion === 'atender') {
            return response()->json($turno
                ? ['ok' => true, 'mensaje' => $mensajes[$accion], 'ir' => route('mi_modulo.ratificacion', ['id' => $cita->id])]
                : ['ok' => true, 'mensaje' => $mensajes[$accion], 'aviso' => $this->avisoSinRatificacion($cita)]);
        }
        if ($confirmarTurno) {
            $mensajes['terminar'] .=' La ratificación quedó confirmada: revísala en Ratificaciones → Confirmadas.';
        }

        return response()->json(['ok' => true, 'mensaje' => $mensajes[$accion]]);
    }

    /**
     * Ratificación en atención: el formulario de citas/{origen} con los datos
     * de su turno (turnos.recepcion_id) para revisarlos y corregirlos.
     */
    public function ratificacion(Request $request, int $id)
    {
        [$cita, $turno] = $this->ratificacionEnAtencion($request, $id);

        return view('citas', [
            'estados'    => \App\Models\Estados::all(),
            'municipios' => \App\Models\Municipios::all(),
            'origen'     => 'modulo',
            'turno'      => $turno,
            'cita'       => $cita,
        ]);
    }

    /** "Finalizar" del formulario: guarda los cambios y regresa a Mi módulo para terminar. */
    public function guardarRatificacion(Request $request, int $id)
    {
        [$cita, $turno] = $this->ratificacionEnAtencion($request, $id);

        $request->validate([
            'primero_trabajador'      => 'required',
            'trabajador'              => 'required',
            'trabajador_edad'         => 'required',
            'trabajador_sexo'         => 'required',
            'trabajador_curp'         => 'required',
            'tipo_identificacion'     => 'required',
            'num_identificacion'      => 'required',
            'documentoidentificacion' => 'nullable|file|mimes:pdf|max:10240',
            'cuantificacion'          => 'nullable|file|mimes:pdf|max:10240',
            'fecha_inicio'            => 'required',
            'categoria'               => 'required',
            'monto'                   => 'required',
            'frecuencia'              => 'required',
            'tipo_pago'               => 'required',
            'dias'                    => 'required',
            'JLCA'                    => 'required',
            'motivo'                  => 'required',
            'salario'                 => 'required',
            'municipio_rat'           => 'required',
            'estado_rat'              => 'required',
            'tipo_vialidad'           => 'required',
            'vialidad_calle'          => 'required',
            'colonia'                 => 'required',
            'N_Ext'                   => 'required',
            'cp'                      => 'required',
        ]);

        $mayus = fn (?string $v) => $v === null ? null : mb_strtoupper($v);
        $prestacion = fn (string $campo) => $request->has($campo) && $request->input('motivo') === 'Pago de prestaciones' ? 1 : null;

        $cambios = [
            'primero_trabajador'  => $mayus($request->input('primero_trabajador')),
            'segundo_trabajador'  => $mayus($request->input('segundo_trabajador')),
            'trabajador'          => $mayus($request->input('trabajador')),
            'nacionalidad'        => $request->input('nacionalidad'),
            'edad'                => $request->input('trabajador_edad'),
            'sexo'                => $request->input('trabajador_sexo'),
            'trabajador_curp'     => $mayus($request->input('trabajador_curp')),
            'tipo_identificacion' => $request->input('tipo_identificacion'),
            'tipo_otros'          => $request->input('tipo_identificacion') === 'Otro' ? $request->input('tipo_otros') : null,
            'num_identificacion'  => $mayus($request->input('num_identificacion')),
            'estado_rat'          => $request->input('estado_rat'),
            'municipio_rat'       => $request->input('municipio_rat'),
            'tipo_vialidad'       => $request->input('tipo_vialidad'),
            'calle'               => $request->input('vialidad_calle'),
            'colonia'             => $request->input('colonia'),
            'num_ext'             => $request->input('N_Ext'),
            'num_int'             => $request->input('N_Int'),
            'codigo_postal'       => $request->input('cp'),
            'JLCA'                => $request->input('JLCA'),
            'fecha_inicio'        => $request->input('fecha_inicio'),
            'fecha_termino'       => $request->input('fecha_termino') ?: null,
            'categoria'           => $mayus($request->input('categoria')),
            'frecuencia'          => $request->input('frecuencia'),
            'salario'             => $request->input('salario'),
            'dias'                => $request->input('dias'),
            'motivo'              => $request->input('motivo'),
            'monto'               => $request->input('monto'),
            'tipo_pago'           => $request->input('tipo_pago'),
            'Aguinaldo'           => $prestacion('Aguinaldo'),
            'Vacaciones'          => $prestacion('Vacaciones'),
            'PrimaVacacional'     => $prestacion('PrimaVacacional'),
            'PagoPTU'             => $prestacion('PagoPTU'),
            'Gratificación'       => $prestacion('Gratificación'),
            'PrimaAntigüedad'     => $prestacion('PrimaAntigüedad'),
            'Otras'               => $prestacion('Otras'),
            'Especifique'         => $prestacion('Otras') ? $request->input('Especifique') : null,
        ];

        // Los documentos sólo se reemplazan si suben uno nuevo (mismos nombres que store_publico).
        $curp = $cambios['trabajador_curp'];
        if ($request->hasFile('documentoidentificacion')) {
            $cambios['documentoidentificacion'] = $curp.'_IDENTIFICACION.pdf';
            \Illuminate\Support\Facades\Storage::disk('s3')->putFileAs('documentos_ratificacion/'.$turno->id, $request->file('documentoidentificacion'), $cambios['documentoidentificacion']);
        }
        if ($request->hasFile('cuantificacion')) {
            $cambios['documentoCuanti'] = $curp.'_CUANTIFICACION.pdf';
            \Illuminate\Support\Facades\Storage::disk('s3')->putFileAs('documentos_ratificacion/'.$turno->id, $request->file('cuantificacion'), $cambios['documentoCuanti']);
        }

        \App\Services\HistorialRatificacionRecorder::actualizar($turno, $cambios,
            'Revisión de datos en Mi módulo (folio '.FolioCita::mostrar($cita).')');

        return redirect()->route('mi_modulo', ['modulo' => $cita->modulo_id])
            ->with('success', 'Datos de la ratificación del folio '.FolioCita::mostrar($cita).' guardados. Da clic en "Terminar" para confirmarla.');
    }

    public function descartar(Request $request)
    {
        \App\Support\PrellenadoCita::descartar();

        return back()->with('success', 'Se quitó el prellenado; captura la solicitud como siempre.');
    }

    /**
     * Fase 3: quién sigue en cada uno de sus módulos y cuándo (SigueEnModulo).
     * Lo consulta el aviso que va en todas las pantallas (mi_modulo/_avisos),
     * también mientras captura una solicitud: "ya llegó y sigue" y
     * "prepárate, en 10 minutos sigue…". Un Super Usuario sin módulo recibe
     * los módulos con citas de prueba de hoy, para poder probar.
     */
    public function avisos(Request $request)
    {
        $usuario = $request->user();
        abort_unless(self::puedeVer($usuario), 403);

        $ahora   = now();
        $modulos = self::propios($usuario);
        $soloSU  = $modulos->isEmpty() && $usuario->hasRole('Super Usuario');

        if ($soloSU) {
            $modulos = Modulo::where('activo', 1)
                ->whereIn('id', DB::table('recepcion')->where('fecha', $ahora->toDateString())
                    ->where('es_prueba', 1)->whereNotNull('modulo_id')->distinct()->pluck('modulo_id'))
                ->orderBy('delegacion')->orderBy('orden')
                ->get();
        }

        $resumen = [];
        foreach ($modulos->groupBy('delegacion') as $sede => $deLaSede) {
            $citas = PantallaEspera::citasVigentes((string) $sede, $ahora, true, SigueEnModulo::COLUMNAS);
            if ($soloSU) {
                $citas = $citas->filter(fn ($c) => (bool) $c->es_prueba);
            }

            foreach ($deLaSede as $modulo) {
                $resumen[] = SigueEnModulo::resumen($modulo, $citas->where('modulo_id', $modulo->id)->values(), $ahora);
            }
        }

        return response()->json([
            'minutos'  => FlujoCita::minutosAviso(),
            'generado' => $ahora->toIso8601String(),
            'modulos'  => $resumen,
        ])->header('Cache-Control', 'no-store');
    }

    private static function esRatificacion(object $cita): bool
    {
        return in_array($cita->tipo, ['Ratificación', 'Ratificacion'], true);
    }

    private static function esAsesoria(object $cita): bool
    {
        return in_array($cita->tipo, ['Asesoría', 'Asesoria'], true);
    }

    /** El turno (ratificación) que se agendó con esta cita. */
    private function turnoDeCita(\App\Models\Recepcion $cita): ?\App\Models\Turnos
    {
        return \App\Models\Turnos::where('recepcion_id', $cita->id)->latest('id')->first();
    }

    /**
     * La cita de ratificación de su módulo, vigente y ya en atención, con su
     * turno; si no hay turno ligado, regresa a Mi módulo.
     *
     * @return array{0: \App\Models\Recepcion, 1: \App\Models\Turnos}
     */
    private function ratificacionEnAtencion(Request $request, int $id): array
    {
        abort_unless(self::puedeVer($request->user()), 403);

        $cita = \App\Models\Recepcion::findOrFail($id);

        abort_unless($this->esDeSusModulos($request->user(), $cita), 403, 'La cita no es de tu módulo.');
        abort_unless(self::esRatificacion($cita), 422, 'La cita no es de ratificación.');
        abort_unless(in_array($cita->estatus, self::VIGENTES, true) && $cita->atencion_inicio_at, 422,
            'Primero inicia la atención de la cita en Mi módulo.');

        $turno = $this->turnoDeCita($cita);
        if (! $turno) {
            abort(redirect()->route('mi_modulo', ['modulo' => $cita->modulo_id])
                ->with('aviso_ratificacion', $this->avisoSinRatificacion($cita)));
        }

        return [$cita, $turno];
    }

    /**
     * Aviso de Mi módulo cuando la cita de ratificación no tiene turno ligado:
     * se agendó sin el formulario (p. ej. desde recepción), así que no hay
     * datos que revisar y hay que capturarla en Ratificaciones → Nueva Ratificación.
     */
    private function avisoSinRatificacion(\App\Models\Recepcion $cita): array
    {
        return [
            'folio'    => FolioCita::mostrar($cita),
            'urlNueva' => request()->user()?->can('ratificaciones_crear')
                ? route('create_cita', ['origen' => 'interno'])
                : null,
        ];
    }

    /** Otra cita del mismo módulo ya llamada y sin terminar (se atiende de una en una). */
    private function abiertaEnModulo(\App\Models\Recepcion $cita, bool $bloquear = false): ?\App\Models\Recepcion
    {
        return \App\Models\Recepcion::where('modulo_id', $cita->modulo_id)
            ->whereDate('fecha', now()->toDateString())
            ->where('id', '<>', $cita->id)
            ->whereNotNull('llamado_at')
            ->whereIn('estatus', self::VIGENTES)
            ->when($bloquear, fn ($q) => $q->lockForUpdate())
            ->first();
    }

    private function esDeSusModulos(User $usuario, object $cita): bool
    {
        return $usuario->hasRole('Super Usuario')
            || self::propios($usuario)->pluck('id')->contains((int) $cita->modulo_id);
    }

    /** Módulos activos que atiende la persona. */
    private static function propios(User $usuario): Collection
    {
        static $cache = [];

        return $cache[$usuario->id] ??= Modulo::where('user_id', $usuario->id)
            ->where('activo', 1)
            ->orderBy('delegacion')->orderBy('orden')
            ->get();
    }

    /**
     * El módulo a mostrar y los que se pueden elegir. Un Super Usuario puede
     * ver cualquiera (para probar); los demás, sólo los suyos.
     *
     * @return array{0: ?Modulo, 1: Collection}
     */
    private function elegir(Request $request): array
    {
        $usuario  = $request->user();
        $opciones = $usuario->hasRole('Super Usuario')
            ? Modulo::where('activo', 1)->orderBy('delegacion')->orderBy('orden')->get()
            : self::propios($usuario);

        $pedido = (int) $request->query('modulo');
        $modulo = ($pedido ? $opciones->firstWhere('id', $pedido) : null)
            ?? self::propios($usuario)->first()
            ?? $opciones->first();

        return [$modulo, $opciones];
    }

    private function armar(Modulo $modulo): array
    {
        $ahora = now();
        $hoy   = $ahora->toDateString();
        $aviso = FlujoCita::minutosAviso();

        // Turno actual y siguiente: la misma regla que la pantalla de espera;
        // los tiempos, con la duración de Horarios de citas.
        $vigentes = PantallaEspera::citasVigentes($modulo->delegacion, $ahora, true, SigueEnModulo::COLUMNAS)
            ->where('modulo_id', $modulo->id)->values();
        [$actualFila, $sigueFila] = PantallaEspera::turnoDeModulo($vigentes, $ahora);
        $actualId = $actualFila->id ?? null;
        $sigueId  = $sigueFila->id ?? null;
        $tiempos  = SigueEnModulo::tiempos($actualFila, $sigueFila, $ahora);

        $citas = DB::table('recepcion as r')
            ->leftJoin('municipios as mu', 'mu.id', '=', 'r.municipio')
            ->where('r.fecha', $hoy)
            ->where('r.delegacion', $modulo->delegacion)
            ->where(fn ($q) => $q->where('r.modulo_id', $modulo->id)
                ->orWhere(fn ($v) => $v->whereNull('r.modulo_id')->where('r.lugar_auxiliar', $modulo->nombre)))
            ->orderBy('r.hora')->orderBy('r.id')
            ->get([
                'r.id', 'r.codigo', 'r.consecutivo', 'r.hora', 'r.hora_fin', 'r.tipo', 'r.estatus', 'r.origen',
                'r.es_prueba', 'r.exepcion', 'r.solicitante', 'r.edad', 'r.sexo', 'r.telefono', 'r.correo',
                'r.tipo_caso', 'r.conflicto', 'r.vulnerables', 'r.prestacionSS', 'r.observaciones',
                'r.llamado_at', 'r.atencion_inicio_at',
                ...(FlujoCita::conFinAtencion() ? ['r.atencion_fin_at'] : []),
                ...(FlujoCita::conSeguimiento() ? ['r.confirmada_at'] : []),
                ...(FlujoCita::conTardias() ? ['r.llego_tarde'] : []),
                'mu.nombre as municipio',
            ])
            ->map(function ($c) use ($ahora, $hoy, $aviso, $actualId, $sigueId) {
                $folio   = FolioCita::mostrar($c);
                $corte   = strrpos($folio, '-');
                $minutos = (int) floor($ahora->diffInSeconds(Carbon::parse($hoy.' '.$c->hora), false) / 60);
                $vigente = in_array($c->estatus, ['pendiente', 'confirmada'], true);

                return [
                    'id'          => $c->id,
                    'folio'       => $folio,
                    'prefijo'     => $corte === false ? '' : substr($folio, 0, $corte + 1),
                    'numero'      => $corte === false ? $folio : substr($folio, $corte + 1),
                    'hora'        => substr((string) $c->hora, 0, 5),
                    'tipo'        => (string) $c->tipo,
                    'estatus'     => (string) $c->estatus,
                    'enLinea'     => $c->origen === 'linea',
                    'prueba'      => (bool) $c->es_prueba,
                    'flujoNuevo'  => FlujoCita::aplicaA($c),
                    'solicitante' => \Illuminate\Support\Str::title(mb_strtolower(trim((string) $c->solicitante))),
                    'edad'        => $c->edad,
                    'sexo'        => $c->sexo,
                    'municipio'   => $c->municipio,
                    'telefono'    => $c->telefono,
                    'correo'      => $c->correo,
                    'motivo'      => trim((string) ($c->conflicto ?: $c->observaciones)),
                    'caso'        => $c->tipo_caso,
                    'vulnerables' => $c->vulnerables,
                    'minutos'     => $minutos,
                    'porLlegar'   => $vigente && $minutos >= 0 && $minutos <= $aviso,
                    'esActual'    => $c->id === $actualId,
                    'esSiguiente' => $c->id === $sigueId,
                    'llamado'     => $c->llamado_at ? substr((string) $c->llamado_at, 11, 5) : null,
                    'enAtencion'  => (bool) $c->atencion_inicio_at,
                    'llegoTarde'  => (bool) ($c->llego_tarde ?? false),
                    // Minutos en sala sin que la llamen (aviso si pasa de SigueEnModulo::ESPERA_ALERTA).
                    'esperando'   => SigueEnModulo::esperando($c, $ahora),
                    // Minutos, para los promedios del día.
                    'espera'      => $c->llamado_at
                        ? max(0, (int) round(Carbon::parse($hoy.' '.$c->hora)->diffInSeconds(Carbon::parse($c->llamado_at), false) / 60))
                        : null,
                    'duracion'    => ($c->atencion_inicio_at && ($c->atencion_fin_at ?? null))
                        ? max(0, (int) round(Carbon::parse($c->atencion_inicio_at)->diffInSeconds(Carbon::parse($c->atencion_fin_at), false) / 60))
                        : null,
                ];
            });

        $promedio = fn (string $campo) => ($v = $citas->pluck($campo)->filter(fn ($x) => $x !== null))->isEmpty()
            ? null
            : (int) round($v->avg());

        return [
            'modulo'       => [
                'id'       => $modulo->id,
                'nombre'   => preg_replace('/^m[oó]dulo\b/i', 'Módulo', $modulo->nombre),
                'sede'     => $modulo->delegacion,
                'tramites' => implode(', ', (array) $modulo->tramites),
            ],
            'minutosAviso' => $aviso,
            'esperaAlerta' => SigueEnModulo::ESPERA_ALERTA,
            'duracion'     => (int) HorarioCitas::config($modulo->delegacion, 'Solicitud')->duracion,
            'tiempos'      => $tiempos,
            'generado'     => $ahora->toIso8601String(),
            'actual'       => $citas->firstWhere('esActual', true),
            'siguiente'    => $citas->firstWhere('esSiguiente', true),
            'citas'        => $citas->values()->all(),
            'cifras'       => [
                'total'      => $citas->count(),
                'atendidas'  => $citas->where('estatus', 'atendido')->count(),
                'enSala'     => $citas->where('estatus', 'confirmada')->count(),
                'porLlegar'  => $citas->where('estatus', 'pendiente')->where('minutos', '>=', -5)->count(),
                'esperandoMucho' => $citas->filter(fn ($c) => $c['esperando'] !== null && $c['esperando'] >= SigueEnModulo::ESPERA_ALERTA)->count(),
                'esperaProm'   => $promedio('espera'),
                'atencionProm' => $promedio('duracion'),
            ],
        ];
    }
}
