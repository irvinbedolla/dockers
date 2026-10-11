<?php

namespace App\Http\Controllers;

use App\Models\HorarioCita;
use App\Models\Recepcion;
use App\Support\HorarioCitas;
use App\Support\Recepcion as RecepcionRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Administración → Horarios de citas (Super Usuario).
 * Un horario por sede y por trámite; ver App\Support\HorarioCitas.
 */
class HorariosCitasController extends Controller
{
    public function index(Request $request)
    {
        $sedes = RecepcionRoles::TODAS_LAS_SEDES;
        $sede  = in_array($request->query('sede'), $sedes, true) ? $request->query('sede') : $sedes[0];

        $configs = collect(array_keys(HorarioCitas::GRUPOS))
            ->mapWithKeys(fn ($g) => [$g => HorarioCitas::config($sede, $g === 'Excepción' ? null : $g, $g === 'Excepción')]);

        // Resumen de todas las sedes para comparar de un vistazo.
        $resumen = collect($sedes)->mapWithKeys(fn ($s) => [$s => collect(array_keys(HorarioCitas::GRUPOS))
            ->mapWithKeys(function ($g) use ($s) {
                $c = HorarioCitas::config($s, $g === 'Excepción' ? null : $g, $g === 'Excepción');
                return [$g => ['config' => $c, 'horarios' => HorarioCitas::horarios($c)]];
            })]);

        return view('administracion.horarios.index', compact('sedes', 'sede', 'configs', 'resumen'));
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'delegacion'        => ['required', Rule::in(RecepcionRoles::TODAS_LAS_SEDES)],
            'tramite'           => ['required', Rule::in(array_keys(HorarioCitas::GRUPOS))],
            'hora_inicio'       => ['required', 'date_format:H:i'],
            'hora_fin'          => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'intervalo'         => ['required', 'integer', 'min:10', 'max:240'],
            'duracion'          => ['required', 'integer', 'min:5', 'lte:intervalo'],
            'comida_inicio'     => ['nullable', 'date_format:H:i'],
            'comida_minutos'    => ['nullable', 'integer', 'min:0', 'max:180'],
            'citas_por_horario' => ['required', 'integer', 'min:1', 'max:20'],
            'horas_un_lugar'    => ['nullable', 'string', 'max:200'],
            'limite_diario'     => ['nullable', 'integer', 'min:1', 'max:500'],
        ], [
            'hora_fin.after'    => 'La hora de cierre debe ser después de la hora de inicio.',
            'duracion.lte'      => 'La cita no puede durar más que el intervalo, o se encima con la siguiente.',
        ], [
            'hora_inicio' => 'hora de inicio', 'hora_fin' => 'hora de cierre', 'duracion' => 'duración',
            'citas_por_horario' => 'citas por horario', 'limite_diario' => 'límite diario',
            'comida_inicio' => 'inicio de comida', 'comida_minutos' => 'minutos de comida',
        ]);

        $horasUnLugar = $this->listaDeHoras($datos['horas_un_lugar'] ?? '');

        $horario = HorarioCita::firstOrNew(['delegacion' => $datos['delegacion'], 'tramite' => $datos['tramite']]);
        $antes   = $horario->exists ? HorarioCitas::horarios($horario) : [];

        $horario->fill([
            'hora_inicio'       => $datos['hora_inicio'].':00',
            'hora_fin'          => $datos['hora_fin'].':00',
            'intervalo'         => (int) $datos['intervalo'],
            'duracion'          => (int) $datos['duracion'],
            'comida_inicio'     => ! empty($datos['comida_inicio']) ? $datos['comida_inicio'].':00' : null,
            'comida_minutos'    => (int) ($datos['comida_minutos'] ?? 30),
            'citas_por_horario' => (int) $datos['citas_por_horario'],
            'horas_un_lugar'    => $horasUnLugar ?: null,
            'limite_diario'     => $datos['tramite'] === 'Excepción' ? null : ($datos['limite_diario'] ?? null),
            'activo'            => $request->boolean('activo'),
            'updated_by'        => $request->user()->id,
        ]);

        $nuevos = HorarioCitas::horarios($horario);
        if ($horario->activo && empty($nuevos)) {
            throw ValidationException::withMessages(['hora_fin' => 'Con esos valores no queda ningún horario en el día.']);
        }

        $horario->save();

        Log::info('Horario de citas actualizado', [
            'sede' => $horario->delegacion, 'tramite' => $horario->tramite, 'por' => $request->user()->id,
            'antes' => $antes, 'ahora' => $nuevos,
        ]);

        $etiqueta = HorarioCitas::GRUPOS[$horario->tramite];
        $aviso = "Se guardó el horario de {$etiqueta} en {$horario->delegacion}: ".count($nuevos).' horarios por día.';

        // Las citas ya agendadas no se mueven solas: se avisa cuántas quedaron
        // en una hora que ya no existe para que recepción las revise.
        $fuera = $this->citasFueraDeHorario($horario, $nuevos);
        if ($fuera > 0) {
            $aviso .= " Ojo: {$fuera} ".($fuera === 1 ? 'cita ya agendada quedó' : 'citas ya agendadas quedaron')
                .' en una hora que ya no está en el nuevo horario; se respetan, pero conviene revisarlas en Módulos y citas.';
        }

        $sinHorario = array_diff(array_map(fn ($h) => $h.':00', $horasUnLugar), $nuevos);
        if ($sinHorario) {
            $aviso .= ' Las horas '.implode(', ', array_map(fn ($h) => substr($h, 0, 5), $sinHorario))
                .' de "cupo de 1" no coinciden con ningún horario y no tienen efecto.';
        }

        return redirect()->route('horarios.index', ['sede' => $horario->delegacion])
            ->with($fuera > 0 || $sinHorario ? 'warning' : 'success', $aviso);
    }

    /** "13:30, 14:20" → ['13:30', '14:20'] (ordenadas, sin repetir). */
    private function listaDeHoras(string $texto): array
    {
        $horas = [];
        foreach (preg_split('/[\s,;]+/', trim($texto), -1, PREG_SPLIT_NO_EMPTY) as $pieza) {
            if (! preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $pieza, $m)) {
                throw ValidationException::withMessages([
                    'horas_un_lugar' => "\"{$pieza}\" no es una hora válida. Escríbelas como 13:30, separadas por coma.",
                ]);
            }
            $horas[] = sprintf('%02d:%02d', $m[1], $m[2]);
        }
        $horas = array_values(array_unique($horas));
        sort($horas);

        return $horas;
    }

    private function citasFueraDeHorario(HorarioCita $horario, array $horarios): int
    {
        $excepcion = $horario->tramite === 'Excepción';

        $consulta = HorarioCitas::citasQueOcupan($horario->delegacion, $excepcion ? null : $horario->tramite, $excepcion)
            ->where('fecha', '>=', Carbon::today()->toDateString())
            ->whereIn('estatus', ['pendiente', 'confirmada']);

        if ($excepcion) {
            $consulta->where('delegacion', $horario->delegacion);
        }

        return $horarios ? $consulta->whereNotIn('hora', $horarios)->count() : $consulta->count();
    }
}
