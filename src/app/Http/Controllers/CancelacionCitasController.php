<?php

namespace App\Http\Controllers;

use App\Exports\CitasCancelacionExport;
use App\Mail\CitaCanceladaMail;
use App\Mail\CitaReagendadaMail;
use App\Models\Recepcion;
use App\Models\Sedes;
use App\Services\BitacoraAdministracionRecorder;
use App\Support\AsignadorModulos;
use App\Support\CitasCancelacion;
use App\Support\FolioCita;
use App\Support\FlujoCita;
use App\Support\HorarioCitas;
use App\Support\PantallaEspera;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Administración → Cancelación/Reagenda de citas.
 * Pestaña Citas: las citas de un rango de fechas con filtros (sede, trámite,
 * estatus, origen) o buscadas por folio, CURP, nombre, correo o teléfono; las
 * pendientes se cancelan o se mueven a otro día y hora con lugar. Pestaña
 * Movimientos: cada cancelación y reagenda hecha aquí, que queda en
 * historial_administracion. Filtros y consultas: App\Support\CitasCancelacion.
 *
 * El Super Usuario, además, puede cambiar la sede y el trámite de la cita al
 * reagendarla (PUEDE_CAMBIAR_TODO), siempre que el horario elegido tenga lugar
 * en la sede y el trámite nuevos.
 */
class CancelacionCitasController extends Controller
{
    public const TIPO_BITACORA = 'cancelacion_reagenda_cita';

    /** Quién puede cambiar también la sede y el trámite de una cita. */
    public const PUEDE_CAMBIAR_TODO = 'Super Usuario';

    public static function puedeCambiarTodo(?\App\Models\User $usuario): bool
    {
        return $usuario !== null && $usuario->hasRole(self::PUEDE_CAMBIAR_TODO);
    }

    /**
     * Pendientes y confirmadas (ya validó su QR), de hoy en adelante. No se
     * mueve la que ya está en atención en su módulo, ni la atendida, expirada
     * o cancelada.
     */
    public static function movible(Recepcion $cita, string $hoy): bool
    {
        return in_array($cita->estatus, ['pendiente', 'confirmada'], true)
            && $cita->fecha->format('Y-m-d') >= $hoy
            && ! self::enAtencion($cita);
    }

    /** La confirmada que su módulo ya empezó a atender. */
    public static function enAtencion(Recepcion $cita): bool
    {
        return $cita->estatus === 'confirmada' && FlujoCita::migrado() && ! empty($cita->atencion_inicio_at);
    }

    /**
     * Citas: hoy si no se elige otro rango; con búsqueda (folio, CURP, nombre,
     * correo o teléfono), de cualquier día. Movimientos: los últimos 30 días.
     */
    public function index(Request $request)
    {
        $sedesAlcance = $this->sedesDelAlcance($request);
        $sedes        = $sedesAlcance ?? \App\Support\Recepcion::TODAS_LAS_SEDES;
        $f            = CitasCancelacion::filtros($request, $sedes);
        $hoy          = Carbon::today()->toDateString();

        $datos = [
            'f'        => $f,
            'sedes'    => $sedes,
            'hoy'      => $hoy,
            'usuarios' => CitasCancelacion::usuarios($sedesAlcance),
        ];

        if ($f['vista'] === 'movimientos') {
            $movs = CitasCancelacion::movimientos($sedesAlcance, $f);
            $datos += [
                'resumen'     => CitasCancelacion::resumenMovimientos($movs),
                'movimientos' => CitasCancelacion::paginar($movs, $request),
            ];
        } else {
            $consulta = CitasCancelacion::consultaCitas($this->delAlcance($request), $f);
            $citas    = CitasCancelacion::listarCitas($consulta, $f);
            $datos += [
                'resumen'   => CitasCancelacion::resumenCitas($consulta),
                'citas'     => $citas,
                'historial' => CitasCancelacion::historialDe($citas->getCollection()->pluck('id')),
            ];
        }

        return view('administracion.citas_cancelacion.index', $datos);
    }

    /** Excel de la pestaña que se está viendo, con los mismos filtros. */
    public function descargar(Request $request)
    {
        $sedesAlcance = $this->sedesDelAlcance($request);
        $f            = CitasCancelacion::filtros($request, $sedesAlcance ?? \App\Support\Recepcion::TODAS_LAS_SEDES);

        if ($f['vista'] === 'movimientos') {
            $filas = CitasCancelacion::movimientos($sedesAlcance, $f);
        } else {
            $consulta = CitasCancelacion::consultaCitas($this->delAlcance($request), $f);
            if ($f['estatus']) {
                $consulta->where('estatus', $f['estatus']);
            }
            $citas = $consulta->orderBy('fecha')->orderBy('hora')->orderBy('id')->limit(CitasCancelacion::MAX_FILAS)->get();
            $hist  = CitasCancelacion::historialDe($citas->pluck('id'));
            $filas = $citas->map(fn ($c) => ['cita' => $c, 'historial' => $hist[$c->id] ?? null]);
        }

        $nombre = 'citas_'.($f['vista'] === 'movimientos' ? 'movimientos' : 'citas').'_'
            .($f['buscando'] ? 'busqueda' : $f['desde'].($f['unDia'] ? '' : '_a_'.$f['hasta'])).'.xlsx';

        return Excel::download(new CitasCancelacionExport($f['vista'], $filas), $nombre);
    }

    public function cancelar(Request $request, int $id)
    {
        $motivo = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:500']])['motivo'];
        $cita   = $this->citaMovible($request, $id);

        BitacoraAdministracionRecorder::actualizar(
            self::TIPO_BITACORA, $cita, ['estatus' => 'cancelada'], 'Cancelación: '.$motivo, $cita->codigo, $cita->delegacion
        );

        Log::info('Cita cancelada desde Administración', ['cita' => $cita->id, 'por' => $request->user()->id]);

        $aviso = $this->avisar($cita, fn () => new CitaCanceladaMail($cita->fresh()));

        return back()->with('success', "Se canceló la cita {$cita->folio_cita}.".$aviso);
    }

    /**
     * Mueve la cita a otro día y hora con lugar. El Super Usuario puede
     * cambiar también la sede y el trámite: el horario se revisa contra el
     * calendario de la sede y el trámite nuevos, y la cita recibe un folio
     * nuevo (el QR del acuse es por id, así que sigue sirviendo).
     */
    public function reagendar(Request $request, int $id)
    {
        $datos = $request->validate([
            'fecha'  => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora'   => ['required', 'date_format:H:i'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
            'sede'   => ['nullable', Rule::in(array_keys(FolioCita::SEDES))],
            'tipo'   => ['nullable', Rule::in(array_keys(CitasCancelacion::TRAMITES))],
        ]);
        $cita      = $this->citaMovible($request, $id);
        $hora      = HorarioCitas::normalizarHora($datos['hora']);
        $excepcion = $cita->exepcion === 'Si';

        // Sede y trámite: los de la cita, salvo que el Super Usuario elija otros.
        $sedeAntes = CitasCancelacion::sede($cita->delegacion);
        $tipoAntes = CitasCancelacion::tramite($cita->tipo);
        $sede      = $datos['sede'] ?? $sedeAntes;
        $tipo      = $datos['tipo'] ?? $tipoAntes;
        $cambiaSede = $sede !== $sedeAntes;
        $cambiaTipo = $tipo !== $tipoAntes;
        if (($cambiaSede || $cambiaTipo) && ! self::puedeCambiarTodo($request->user())) {
            abort(403, 'Sólo el Super Usuario puede cambiar la sede o el trámite de una cita.');
        }
        if (($cambiaSede || $cambiaTipo) && ($ligada = $this->ligadaA($cita))) {
            return back()->with('error', "La cita {$cita->folio_cita} ya tiene {$ligada} ligada: sólo se puede cambiar su fecha y hora.");
        }

        // Sin cambio de sede o trámite se consulta con lo guardado tal cual (hay "Lazaro Cardenas" sin acento).
        $sedeReal = $cambiaSede ? $sede : $cita->delegacion;
        $tipoReal = $cambiaTipo ? $tipo : $cita->tipo;

        $mismaFechaHora = $datos['fecha'] === $cita->fecha->format('Y-m-d') && $hora === $cita->getRawOriginal('hora');
        // ¿Sigue en el mismo calendario? (misma sede y mismo grupo de trámite, o las dos de excepción)
        $mismoCalendario = $excepcion
            || (! $cambiaSede && HorarioCitas::grupo($tipo) === HorarioCitas::grupo($tipoAntes));

        // El diálogo sólo ofrece horarios futuros y con lugar (/api/obtenerTurnosDisponibles,
        // igual que /generar-cita); aquí se vuelve a revisar porque pudo ocuparse mientras tanto.
        if (Carbon::parse($datos['fecha'].' '.$hora)->isPast()) {
            return back()->with('error', 'Ese horario ya pasó. Elige otro.');
        }
        if ($mismaFechaHora && ! $cambiaSede && ! $cambiaTipo) {
            return back()->with('error', 'Es el mismo horario que ya tiene la cita.');
        }
        // Si se queda en su mismo lugar (sólo cambia el trámite dentro del mismo calendario) ya lo tiene apartado.
        if (! ($mismaFechaHora && $mismoCalendario) && ! $this->hayLugar($cita, $sedeReal, $tipoReal, $datos['fecha'], $hora)) {
            return back()->with('error', 'Ese horario ya no tiene lugar'.($cambiaSede || $cambiaTipo ? " en {$sede} para {$tipo}" : '').'. Elige otro.');
        }

        // Como al agendar: el primer módulo libre a esa hora. Si no hay, se queda en
        // el que tenía; si cambió de sede o de trámite, ese ya no le toca: queda sin módulo.
        $modulo = $excepcion ? null : AsignadorModulos::elegir($sedeReal, $tipoReal, $datos['fecha'], $hora);
        $conservaModulo = ! $cambiaSede && ! $cambiaTipo;
        $antes  = $cita->fecha->format('d/m/Y').' '.substr($cita->getRawOriginal('hora'), 0, 5)
            .($cambiaSede || $cambiaTipo ? " · {$sedeAntes} · {$tipoAntes} · {$cita->folio_cita}" : '');

        // Para el correo: cómo estaba antes del cambio.
        $fechaAnterior  = Str::ucfirst($cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'))
            .' a las '.substr($cita->getRawOriginal('hora'), 0, 5).' h';
        $moduloAnterior = $cita->lugar_auxiliar;
        $folioAnterior  = $cita->folio_cita;

        $cambios = [
            'fecha'          => $datos['fecha'],
            'hora'           => $hora,
            'hora_fin'       => HorarioCitas::horaFin($sedeReal, $tipoReal, $excepcion, $hora),
            'modulo_id'      => $modulo?->id ?? ($conservaModulo ? $cita->modulo_id : null),
            'lugar_auxiliar' => $modulo?->nombre ?? ($conservaModulo ? $cita->lugar_auxiliar : null),
            'auxiliar'       => $modulo?->user_id ?? ($conservaModulo ? $cita->auxiliar : null),
        ];
        // Ya había llegado (confirmada): a otro día vuelve a "pendiente" y tendrá que
        // validar su QR al llegar; el mismo día sigue confirmada (ya está aquí) y su
        // módulo la vuelve a llamar a la hora nueva.
        $confirmada  = $cita->estatus === 'confirmada';
        $otroDia     = $datos['fecha'] !== $cita->fecha->format('Y-m-d');
        if ($confirmada) {
            $cambios += FlujoCita::migrado() ? ['llamado_at' => null] : [];
            if ($otroDia) {
                $cambios += ['estatus' => 'pendiente']
                    + (FlujoCita::conSeguimiento() ? ['confirmada_at' => null] : [])
                    + (FlujoCita::conTardias() ? ['llego_tarde' => false, 'llego_tarde_por' => null] : []);
            }
        }
        if ($cambiaSede || $cambiaTipo) {
            // El folio lleva la sede y el trámite (MOR-S26-0142): se le da uno nuevo.
            $cambios += [
                'delegacion' => $sede,
                'tipo'       => $tipo,
                'codigo'     => FolioCita::generar($sede, $tipo) ?? $cita->codigo,
            ];
        }

        $sedeEspera = (string) $cita->delegacion;
        BitacoraAdministracionRecorder::actualizar(self::TIPO_BITACORA, $cita, $cambios,
            "Reagenda (antes {$antes}".($confirmada ? ', ya confirmada' : '')."): ".$datos['motivo'], $cita->codigo, $sedeAntes);

        // La pantalla de espera y Mi módulo se recalculan con el cambio (en las dos sedes si cambió).
        PantallaEspera::olvidar($sedeEspera);
        if ((string) $cita->delegacion !== $sedeEspera) {
            PantallaEspera::olvidar((string) $cita->delegacion);
        }

        // Lo que cambió además de la fecha, para el correo: [etiqueta => [antes, ahora]].
        $otros = array_filter([
            'Sede'    => $cambiaSede ? [$sedeAntes, $sede] : null,
            'Trámite' => $cambiaTipo ? [$tipoAntes, $tipo] : null,
            'Folio'   => $cambiaSede || $cambiaTipo ? [$folioAnterior, $cita->folio_cita] : null,
        ]);

        // Como una cita recién agendada: datos nuevos, módulo y acuse en PDF.
        $aviso = $this->avisar($cita, function () use ($cita, $fechaAnterior, $moduloAnterior, $otros) {
            $nueva = $cita->fresh();
            $pdf   = app(HomeController::class)->verDocumentoCita($nueva->id);

            return new CitaReagendadaMail($pdf, $nueva, $fechaAnterior,
                $moduloAnterior !== $nueva->lugar_auxiliar ? $moduloAnterior : null, $otros);
        });

        $mensaje = $cambiaSede || $cambiaTipo
            ? "La cita {$folioAnterior} ahora es {$cita->folio_cita}: {$tipo} en {$sede}, el "
            : "La cita {$cita->folio_cita} quedó para el ";

        return back()->with('success', $mensaje
            .Carbon::parse($datos['fecha'])->format('d/m/Y')." a las {$datos['hora']}."
            .(! $excepcion && ! $cita->modulo_id ? ' No había módulo libre a esa hora: queda sin módulo asignado.' : '')
            .($confirmada ? ($otroDia
                ? ' Ya había llegado: como cambió de día, quedó pendiente y tendrá que validar su QR en recepción al llegar.'
                : ' Sigue confirmada: su módulo la llamará a la nueva hora.') : '')
            .$aviso);
    }

    private function hayLugar(Recepcion $cita, string $sede, string $tipo, string $fecha, string $hora): bool
    {
        return app(RecepcionController::class)
            ->turnoSlotDisponible($sede, $tipo, $fecha, $hora, $cita->exepcion);
    }

    /**
     * ¿La cita ya tiene una ratificación o una solicitud ligada? Entonces no se
     * le cambia la sede ni el trámite (quedarían en otra sede o en otro trámite).
     */
    private function ligadaA(Recepcion $cita): ?string
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('turnos', 'recepcion_id')
            && DB::table('turnos')->where('recepcion_id', $cita->id)->exists()) {
            return 'una ratificación';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('seer_general', 'recepcion_id')
            && DB::table('seer_general')->where('recepcion_id', $cita->id)->exists()) {
            return 'una solicitud';
        }

        return null;
    }

    private function citaMovible(Request $request, int $id): Recepcion
    {
        $cita = $this->delAlcance($request)->findOrFail($id);
        abort_unless(self::movible($cita, Carbon::today()->toDateString()), 422, 'Sólo se pueden cancelar o reagendar citas pendientes o confirmadas, de hoy en adelante, que no estén en atención.');

        return $cita;
    }

    /**
     * Super Usuario y Directivo ven todas las sedes. Delegado y Enlace, su
     * sede con su pareja de la tabla sedes (oficina_apoyo): Morelia-Zitácuaro,
     * Uruapan-Lázaro Cárdenas, Zamora-Sahuayo; la ven igual desde cualquiera
     * de las dos.
     */
    private function delAlcance(Request $request)
    {
        $sedes = $this->sedesDelAlcance($request);

        // En recepcion hay registros sin acento ("Lazaro Cardenas").
        return $sedes === null ? Recepcion::query() : Recepcion::whereIn('delegacion', CitasCancelacion::variantes($sedes));
    }

    /** Las sedes que ve (null = todas). Ver delAlcance(). */
    private function sedesDelAlcance(Request $request): ?array
    {
        $usuario = $request->user();
        if ($usuario->hasAnyRole(['Super Usuario', 'Directivo']) || ! $usuario->hasAnyRole(['Delegado', 'Enlace'])) {
            return null;
        }

        $sede  = Sedes::where('nombre', $usuario->delegacion)->first();
        $madre = $sede && $sede->oficina_apoyo ? Sedes::find($sede->oficina_apoyo) : $sede;

        return $madre
            ? Sedes::where('id', $madre->id)->orWhere('oficina_apoyo', $madre->id)->pluck('nombre')->all()
            : [$usuario->delegacion];
    }

    /**
     * Le avisa a la persona por correo, sea la cita en línea o de ventanilla.
     * Si el correo falla, el cambio ya quedó: sólo se dice en pantalla para
     * que se le avise por otro medio. Devuelve esa frase.
     */
    private function avisar(Recepcion $cita, \Closure $correo): string
    {
        $telefono = $cita->telefono ? " ({$cita->telefono})" : '';

        if (! filter_var($cita->correo, FILTER_VALIDATE_EMAIL)) {
            return " La cita no tiene un correo válido: avísale por teléfono{$telefono}.";
        }

        try {
            Mail::to($cita->correo)->send($correo());

            return " Se le avisó por correo a {$cita->correo}.";
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de cancelación/reagenda', ['cita' => $cita->id, 'error' => $e->getMessage()]);

            return " No se pudo enviar el correo: avísale por teléfono{$telefono}.";
        }
    }
}
