<?php

namespace App\Http\Controllers;

use App\Mail\CitaCanceladaMail;
use App\Mail\CitaReagendadaMail;
use App\Models\Recepcion;
use App\Models\Sedes;
use App\Services\BitacoraAdministracionRecorder;
use App\Support\AsignadorModulos;
use App\Support\HorarioCitas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Administración → Cancelación/Reagenda de citas.
 * Se ven las citas de un día (o se busca por folio, CURP o nombre) y las
 * pendientes se cancelan o se mueven a otro día y hora con lugar. Cada
 * cambio queda en historial_administracion.
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
     * Sin búsqueda: todas las citas de un día (hoy si no se elige otro).
     * Con búsqueda: por folio, CURP o nombre, de cualquier día.
     */
    public function index(Request $request)
    {
        $datos = $request->validate([
            'fecha'  => ['nullable', 'date_format:Y-m-d'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $hoy    = Carbon::today()->toDateString();
        $fecha  = $datos['fecha'] ?? $hoy;
        $buscar = trim($datos['buscar'] ?? '');

        if (mb_strlen($buscar) >= 3) {
            $citas = $this->delAlcance($request)
                ->where(fn ($q) => $q->where('codigo', $buscar)
                    ->orWhere('consecutivo', $buscar)
                    ->orWhere('curp', mb_strtoupper($buscar))
                    ->orWhere('solicitante', 'like', '%'.$buscar.'%'))
                ->orderByDesc('fecha')
                ->orderBy('hora')
                ->limit(50)
                ->get();
        } else {
            $citas = $this->delAlcance($request)
                ->where('fecha', $fecha)
                ->orderBy('hora')
                ->orderBy('delegacion')
                ->get();
        }

        return view('administracion.citas_cancelacion.index', [
            'buscar'       => $buscar,
            'citas'        => $citas,
            'hoy'          => $hoy,
            'fecha'        => $fecha,
            'diaAnterior'  => Carbon::parse($fecha)->subDay()->toDateString(),
            'diaSiguiente' => Carbon::parse($fecha)->addDay()->toDateString(),
        ]);
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
        $usuario = $request->user();
        if ($usuario->hasAnyRole(['Super Usuario', 'Directivo']) || ! $usuario->hasAnyRole(['Delegado', 'Enlace'])) {
            return Recepcion::query();
        }

        $sede  = Sedes::where('nombre', $usuario->delegacion)->first();
        $madre = $sede && $sede->oficina_apoyo ? Sedes::find($sede->oficina_apoyo) : $sede;
        $sedes = $madre
            ? Sedes::where('id', $madre->id)->orWhere('oficina_apoyo', $madre->id)->pluck('nombre')->all()
            : [$usuario->delegacion];

        // En recepcion hay registros sin acento ("Lazaro Cardenas").
        $nombres = array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));

        return Recepcion::whereIn('delegacion', $nombres);
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
