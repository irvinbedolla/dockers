<?php

namespace App\Support;

use App\Models\HorarioCita;
use App\Models\Recepcion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios de las citas (en línea y ventanilla), leídos de la tabla
 * horarios_citas. Antes vivían escritos en RecepcionController.
 *
 * Hay un horario por sede y por grupo de trámite:
 *   - Solicitud     → solicitudes, asesorías y cumplimientos (comparten lugares)
 *   - Ratificación
 *   - Excepción     → casos de excepción (Departamento de Igualdad de Género)
 */
class HorarioCitas
{
    public const GRUPOS = [
        'Solicitud'    => 'Solicitud y asesoría',
        'Ratificación' => 'Ratificación',
        'Excepción'    => 'Caso de excepción',
    ];

    /** Trámites que comparten los lugares de cada grupo. */
    public const TIPOS_DEL_GRUPO = [
        'Solicitud'    => ['Solicitud', 'Asesoría', 'Cumplimiento'],
        'Ratificación' => ['Ratificación'],
    ];

    public const DIRECCIONES_SEDES = [
        'Morelia' => 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260, MORELIA, MICHOACÁN',
        'Zitácuaro' => '5 DE MAYO NTE. 3, CENTRO, C.P. 61500, ZITÁCUARO, MICHOACÁN.',
        'Zamora' => 'JUSTO SIERRA NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670, ZAMORA DE HIDALGO, MICHOACÁN.',
        'Sahuayo' => 'AV. UNIVERSIDAD SUR NO. 3000, SEGUNDO PISO, EDIFICIO CENTRAL, COL. LOMAS DE UNIVERSIDAD, C.P. 59103, SAHUAYO DE MORELOS, MICHOACÁN.',
        'Uruapan' => 'NUEVO PARICUTÍN NO. 308, COL. SAN RAFAEL, C.P. 60136, URUAPAN, MICHOACÁN.',
        'Lázaro Cárdenas' => 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950, LÁZARO CÁRDENAS, MICHOACÁN.',
    ];

    /** Minutos después de su inicio en que un horario todavía se puede reservar. */
    public const TOLERANCIA_RESERVA = 10;

    /**
     * Valores de fábrica (los mismos que siembra la migración). Se usan si a
     * una sede le falta su renglón. Todas las sedes atienden de 8:00 a 16:00
     * con citas de 40 min (12 horarios, 8:00 a 15:20), excepción incluida.
     */
    public static function predeterminado(string $sede, string $grupo): array
    {
        $morelia = $sede === 'Morelia';

        return match ($grupo) {
            'Excepción' => [
                'hora_inicio' => '08:00:00', 'hora_fin' => '16:00:00', 'intervalo' => 40, 'duracion' => 40,
                'citas_por_horario' => 1, 'horas_un_lugar' => null, 'limite_diario' => null,
            ],
            'Ratificación' => [
                'hora_inicio' => '08:00:00', 'hora_fin' => '16:00:00', 'intervalo' => 40, 'duracion' => 40,
                'citas_por_horario' => $morelia ? 2 : 1, 'horas_un_lugar' => $morelia ? ['13:20', '14:00'] : null,
                'limite_diario' => 15,
            ],
            default => [
                'hora_inicio' => '08:00:00', 'hora_fin' => '16:00:00', 'intervalo' => 40, 'duracion' => 40,
                'citas_por_horario' => $morelia ? 3 : 1, 'horas_un_lugar' => $morelia ? ['13:20', '14:00'] : null,
                'limite_diario' => 10,
            ],
        } + ['comida_inicio' => null, 'comida_minutos' => 30, 'activo' => true];
    }

    public static function grupo(?string $tipo, bool $excepcion = false): string
    {
        if ($excepcion) {
            return 'Excepción';
        }

        return in_array($tipo, ['Ratificación', 'Ratificacion'], true) ? 'Ratificación' : 'Solicitud';
    }

    /** Configuración de la sede y el trámite (la de fábrica si aún no hay renglón). */
    public static function config(string $sede, ?string $tipo, bool $excepcion = false): HorarioCita
    {
        static $cache = [];
        $grupo = self::grupo($tipo, $excepcion);
        $clave = $sede.'|'.$grupo;

        if (! isset($cache[$clave])) {
            $fila = Schema::hasTable('horarios_citas')
                ? HorarioCita::where('delegacion', $sede)->where('tramite', $grupo)->first()
                : null;

            $cache[$clave] = $fila ?? new HorarioCita(
                ['delegacion' => $sede, 'tramite' => $grupo] + self::predeterminado($sede, $grupo)
            );
        }

        return $cache[$clave];
    }

    /**
     * Horas de inicio ('H:i:s') que se ofrecen en un día. hora_fin es el
     * cierre: la última cita tiene que terminar a esa hora o antes. Si hay
     * comida, ninguna cita se encima con ella: la siguiente empieza al
     * terminar la comida.
     */
    public static function horarios(HorarioCita $c): array
    {
        if (! $c->activo || $c->intervalo < 5) {
            return [];
        }

        $t   = self::minutos($c->hora_inicio);
        $fin = self::minutos($c->hora_fin);
        $dur = max(1, (int) $c->duracion);
        $comidaIni = $c->comida_inicio ? self::minutos($c->comida_inicio) : null;
        $comidaFin = $comidaIni !== null ? $comidaIni + max(0, (int) $c->comida_minutos) : null;

        $salida = [];
        while ($t + $dur <= $fin && count($salida) < 100) {
            if ($comidaIni !== null && $t < $comidaFin && $t + $dur > $comidaIni) {
                $t = $comidaFin;
                continue;
            }
            $salida[] = sprintf('%02d:%02d:00', intdiv($t, 60), $t % 60);
            $t += $c->intervalo;
        }

        return $salida;
    }

    /** Cuántas citas caben en un horario. */
    public static function capacidad(HorarioCita $c, string $hora): int
    {
        $hhmm = substr(self::normalizarHora($hora), 0, 5);

        return in_array($hhmm, $c->horas_un_lugar ?? [], true)
            ? min(1, (int) $c->citas_por_horario)
            : (int) $c->citas_por_horario;
    }

    /** Hora de término de una cita que empieza a $hora. */
    public static function horaFin(string $sede, ?string $tipo, bool $excepcion, string $hora): string
    {
        $c = self::config($sede, $tipo, $excepcion);

        return Carbon::parse(self::normalizarHora($hora))->addMinutes((int) $c->duracion)->format('H:i:s');
    }

    /**
     * Citas que ocupan lugar en el calendario de esa sede y trámite.
     * Las de excepción comparten un solo calendario entre todas las sedes.
     */
    public static function citasQueOcupan(string $sede, ?string $tipo, bool $excepcion): Builder
    {
        $consulta = Recepcion::query()->where('estatus', '!=', 'expirada');

        if ($excepcion) {
            return $consulta->where('exepcion', 'Si');
        }

        return $consulta
            ->where('delegacion', $sede)
            ->whereIn('tipo', self::TIPOS_DEL_GRUPO[self::grupo($tipo)])
            ->where(fn ($q) => $q->whereNull('exepcion')->orWhere('exepcion', '!=', 'Si'));
    }

    public static function normalizarHora(string $hora): string
    {
        return strlen($hora) === 5 ? $hora.':00' : substr($hora, 0, 8);
    }

    private static function minutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return $h * 60 + $m;
    }
}
