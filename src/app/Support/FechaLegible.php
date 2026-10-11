<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Fechas como las diría una persona:
 *   Hoy a las 7:05 am · Ayer a las 8:30 am · Sábado 2 de agosto del 2026 a las 12:00 pm
 */
class FechaLegible
{
    public static function conHora(?CarbonInterface $fecha, string $siVacia = 'Nunca'): string
    {
        if ($fecha === null) {
            return $siVacia;
        }

        $fecha = Carbon::instance($fecha)->setTimezone(config('app.timezone'));
        $hoy   = Carbon::today(config('app.timezone'));

        if ($fecha->isSameDay($hoy) || $fecha->greaterThan($hoy)) {
            $dia = 'Hoy';
        } elseif ($fecha->isSameDay($hoy->copy()->subDay())) {
            $dia = 'Ayer';
        } else {
            $dia = Str::ucfirst($fecha->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY'));
        }

        return $dia.' '.self::hora($fecha);
    }

    /** "a las 7:05 am", "a la 1:30 pm". */
    public static function hora(CarbonInterface $fecha): string
    {
        $articulo = (int) $fecha->format('g') === 1 ? 'a la' : 'a las';

        return $articulo.' '.$fecha->format('g:i').' '.($fecha->format('A') === 'AM' ? 'am' : 'pm');
    }
}
