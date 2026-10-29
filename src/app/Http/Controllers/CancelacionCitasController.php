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
use App\Support\HorarioCitas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Administración → Cancelación/Reagenda de citas.
 * Pestaña Citas: las citas de un rango de fechas con filtros (sede, trámite,
 * estatus, origen) o buscadas por folio, CURP, nombre, correo o teléfono; las
 * pendientes se cancelan o se mueven a otro día y hora con lugar. Pestaña
 * Movimientos: cada cancelación y reagenda hecha aquí, que queda en
 * historial_administracion. Filtros y consultas: App\Support\CitasCancelacion.
 */
class CancelacionCitasController extends Controller
{
    public const TIPO_BITACORA = 'cancelacion_reagenda_cita';

    /**
     * Sólo las pendientes, de hoy en adelante: si ya llegó (confirmada), ya
     * se atendió o ya expiró, no tiene sentido cancelarla ni moverla.
     */
    public static function movible(Recepcion $cita, string $hoy): bool
    {
        return $cita->estatus === 'pendiente'
            && $cita->fecha->format('Y-m-d') >= $hoy;
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

    public function reagendar(Request $request, int $id)
    {
        $datos = $request->validate([
            'fecha'  => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora'   => ['required', 'date_format:H:i'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ]);
        $cita      = $this->citaMovible($request, $id);
        $hora      = HorarioCitas::normalizarHora($datos['hora']);
        $excepcion = $cita->exepcion === 'Si';

        // El diálogo sólo ofrece horarios futuros y con lugar (/api/obtenerTurnosDisponibles,
        // igual que /generar-cita); aquí se vuelve a revisar porque pudo ocuparse mientras tanto.
        if (Carbon::parse($datos['fecha'].' '.$hora)->isPast()) {
            return back()->with('error', 'Ese horario ya pasó. Elige otro.');
        }
        if ($datos['fecha'] === $cita->fecha->format('Y-m-d') && $hora === $cita->getRawOriginal('hora')) {
            return back()->with('error', 'Es el mismo horario que ya tiene la cita.');
        }
        if (! $this->hayLugar($cita, $datos['fecha'], $hora)) {
            return back()->with('error', 'Ese horario ya no tiene lugar. Elige otro.');
        }

        // Como al agendar: el primer módulo libre a esa hora; si no hay, se queda en el que tenía.
        $modulo = $excepcion ? null : AsignadorModulos::elegir($cita->delegacion, $cita->tipo, $datos['fecha'], $hora);
        $antes  = $cita->fecha->format('d/m/Y').' '.substr($cita->getRawOriginal('hora'), 0, 5);

        // Para el correo: cómo estaba antes del cambio.
        $fechaAnterior  = Str::ucfirst($cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'))
            .' a las '.substr($cita->getRawOriginal('hora'), 0, 5).' h';
        $moduloAnterior = $cita->lugar_auxiliar;

        BitacoraAdministracionRecorder::actualizar(self::TIPO_BITACORA, $cita, [
            'fecha'          => $datos['fecha'],
            'hora'           => $hora,
            'hora_fin'       => HorarioCitas::horaFin($cita->delegacion, $cita->tipo, $excepcion, $hora),
            'modulo_id'      => $modulo?->id ?? $cita->modulo_id,
            'lugar_auxiliar' => $modulo?->nombre ?? $cita->lugar_auxiliar,
            'auxiliar'       => $modulo?->user_id ?? $cita->auxiliar,
        ], "Reagenda (antes {$antes}): ".$datos['motivo'], $cita->codigo, $cita->delegacion);

        // Como una cita recién agendada: datos nuevos, módulo y acuse en PDF.
        $aviso = $this->avisar($cita, function () use ($cita, $fechaAnterior, $moduloAnterior) {
            $nueva = $cita->fresh();
            $pdf   = app(HomeController::class)->verDocumentoCita($nueva->id);

            return new CitaReagendadaMail($pdf, $nueva, $fechaAnterior,
                $moduloAnterior !== $nueva->lugar_auxiliar ? $moduloAnterior : null);
        });

        return back()->with('success', "La cita {$cita->folio_cita} quedó para el "
            .Carbon::parse($datos['fecha'])->format('d/m/Y')." a las {$datos['hora']}.".$aviso);
    }

    private function hayLugar(Recepcion $cita, string $fecha, string $hora): bool
    {
        return app(RecepcionController::class)
            ->turnoSlotDisponible($cita->delegacion, $cita->tipo, $fecha, $hora, $cita->exepcion);
    }

    private function citaMovible(Request $request, int $id): Recepcion
    {
        $cita = $this->delAlcance($request)->findOrFail($id);
        abort_unless(self::movible($cita, Carbon::today()->toDateString()), 422, 'Sólo se pueden cancelar o reagendar citas pendientes de hoy en adelante.');

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
