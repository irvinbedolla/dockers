<?php

namespace App\Support;

use App\Models\Modulo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Quién sigue en un módulo y cuándo, con la duración de las citas de
 * Administración → Horarios de citas (Solicitud y asesoría, Ratificación o
 * Excepción, según la cita).
 *
 *   - El turno "ahora" termina, aproximadamente, a su inicio de atención (o
 *     a su llamado) más la duración de su cita. Si ya se pasó, va "excedido".
 *   - El que "sigue" (PantallaEspera::turnoDeModulo: quien ya llegó y
 *     confirmó, si hay) empieza a lo que pase después: su hora o que termine
 *     el turno de ahora.
 *
 * Lo usan Mi módulo y el aviso que el auxiliar ve en todas las pantallas,
 * incluida la captura de la solicitud (mi_modulo/_avisos).
 */
class SigueEnModulo
{
    /** Columnas de más que necesita de PantallaEspera::citasVigentes. */
    public const COLUMNAS = ['r.delegacion', 'r.exepcion', 'r.solicitante'];

    /** Minutos que dura esta cita según Horarios de citas. */
    public static function duracion(object $c): int
    {
        $config = HorarioCitas::config((string) ($c->delegacion ?? ''), $c->tipo ?? null, ($c->exepcion ?? null) === 'Si');

        return max(1, (int) $config->duracion);
    }

    /**
     * @return array{actual: ?array, siguiente: ?array}
     */
    public static function tiempos(?object $actual, ?object $siguiente, Carbon $ahora): array
    {
        $hoy     = $ahora->toDateString();
        $termina = null;
        $ahoraT  = null;

        if ($actual) {
            $marca    = $actual->atencion_inicio_at ?? $actual->llamado_at ?? null;
            $inicio   = $marca ? Carbon::parse($marca) : Carbon::parse($hoy.' '.$actual->hora);
            $duracion = self::duracion($actual);
            $termina  = $inicio->copy()->addMinutes($duracion);
            $lleva    = max(0, (int) floor($inicio->diffInSeconds($ahora, false) / 60));

            $ahoraT = [
                'desde'    => $inicio->format('H:i'),
                'termina'  => $termina->format('H:i'),
                'duracion' => $duracion,
                'lleva'    => $lleva,
                'excedido' => max(0, $lleva - $duracion),
                'enAtencion' => ! empty($actual->atencion_inicio_at ?? null),
            ];
        }

        $sigueT = null;
        if ($siguiente) {
            $empieza = Carbon::parse($hoy.' '.$siguiente->hora);
            if ($empieza->lt($ahora)) {
                $empieza = $ahora->copy();
            }
            if ($termina && $termina->gt($empieza)) {
                $empieza = $termina->copy();
            }

            $sigueT = [
                'empieza'  => $empieza->format('H:i'),
                'minutos'  => (int) ceil(max(0, $ahora->diffInSeconds($empieza, false)) / 60),
                'duracion' => self::duracion($siguiente),
            ];
        }

        return ['actual' => $ahoraT, 'siguiente' => $sigueT];
    }

    /**
     * Lo que ve el aviso del auxiliar de uno de sus módulos.
     *
     * @param  Collection  $suyas  citas vigentes del módulo (citasVigentes con COLUMNAS)
     */
    public static function resumen(Modulo $modulo, Collection $suyas, Carbon $ahora): array
    {
        [$actual, $siguiente] = PantallaEspera::turnoDeModulo($suyas, $ahora);
        $t = self::tiempos($actual, $siguiente, $ahora);

        // Si sigue alguien que ya llegó, pero antes tenía cita otra persona que aún no llega.
        $antes = null;
        if ($siguiente && $siguiente->estatus === 'confirmada') {
            $antes = $suyas->first(fn ($c) => $c->estatus === 'pendiente'
                && empty($c->llamado_at ?? null)
                && $c->id !== ($actual->id ?? null)
                && $c->hora < $siguiente->hora);
        }

        return [
            'id'        => $modulo->id,
            'modulo'    => preg_replace('/^m[oó]dulo\b/i', 'Módulo', (string) $modulo->nombre).' · '.$modulo->delegacion,
            'actual'    => $actual ? self::persona($actual) + $t['actual'] : null,
            'siguiente' => $siguiente ? self::persona($siguiente) + $t['siguiente'] + [
                // Preparar la solicitud sólo con el módulo libre (se atiende de una en una).
                'iniciar' => ! $actual && $siguiente->tipo === 'Solicitud' ? route('mi_modulo.prellenar', $siguiente->id) : null,
            ] : null,
            'antes'     => $antes ? self::persona($antes) : null,
        ];
    }

    private static function persona(object $c): array
    {
        return [
            'id'      => (int) $c->id,
            'folio'   => FolioCita::mostrar($c),
            'persona' => Str::title(mb_strtolower(trim((string) ($c->solicitante ?? '')))) ?: 'Sin nombre',
            'tipo'    => (string) $c->tipo,
            'hora'    => substr((string) $c->hora, 0, 5),
            'enSala'  => $c->estatus === 'confirmada',
            'prueba'  => (bool) ($c->es_prueba ?? false),
        ];
    }
}
