<?php

namespace App\Support;

use App\Models\Audiencias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Que un conciliador no tenga dos audiencias a la misma hora.
 *
 * Antes cada camino revisaba (o no) por su cuenta y sin bloqueo:
 *   - confirmar solicitud (ObtenerAudiencia): revisaba, pero entre revisar y
 *     guardar otro usuario podía tomar el mismo horario;
 *   - reagendar (las dos variantes): no revisaba nada, guardaba la fecha y
 *     hora que se escribieran;
 *   - cambio de fecha en Administración: tampoco revisaba.
 *
 * Ahora todos usan ocupado() y hacen revisar-y-guardar dentro de
 * conBloqueo(), un candado de MySQL (GET_LOCK) por oficina. Las tres oficinas
 * no comparten conciliadores, así que Morelia no espera a Uruapan.
 */
class AgendaAudiencias
{
    /** Subsede → oficina cuyos conciliadores la atienden. */
    public const OFICINA = [
        'Zitácuaro'       => 'Morelia',
        'Lázaro Cárdenas' => 'Uruapan',
        'Sahuayo'         => 'Zamora',
    ];

    /** Una audiencia en estos estatus ya no ocupa su horario: se movió a otro. */
    public const LIBERAN = ['Reagendada'];

    /** Segundos que se espera el candado antes de rendirse. */
    private const ESPERA = 20;

    public static function oficina(?string $sede): string
    {
        $sede = trim((string) $sede);
        $canonica = collect(array_merge(array_keys(self::OFICINA), array_values(self::OFICINA)))
            ->first(fn ($s) => Str::ascii($s) === Str::ascii($sede)) ?? $sede;

        return self::OFICINA[$canonica] ?? ($canonica ?: 'general');
    }

    /**
     * La audiencia con la que chocaría, o null si el horario está libre.
     * $excepto deja fuera la audiencia que se está moviendo.
     */
    public static function choque(int $conciliador, string $fecha, string $hora, ?int $excepto = null): ?Audiencias
    {
        return Audiencias::where('id_conciliador', $conciliador)
            ->where('fecha', substr($fecha, 0, 10))
            ->where('hora', self::hora($hora))
            ->whereNotIn('estatus', self::LIBERAN)
            ->when($excepto, fn ($q) => $q->where('id', '<>', $excepto))
            ->first();
    }

    public static function ocupado(int $conciliador, string $fecha, string $hora, ?int $excepto = null): bool
    {
        return self::choque($conciliador, $fecha, $hora, $excepto) !== null;
    }

    /** Texto para el aviso cuando ya hay alguien en ese horario. */
    public static function mensajeChoque(Audiencias $choque): string
    {
        $nue = DB::table('seer_general')->where('id', $choque->id_solicitud)->value('NUE');

        return 'El conciliador ya tiene una audiencia el '.substr((string) $choque->getRawOriginal('fecha'), 0, 10)
            .' a las '.substr(self::hora((string) $choque->getRawOriginal('hora')), 0, 5)
            .($nue ? " (expediente {$nue})" : '').'. Elige otro horario.';
    }

    /**
     * Corre $hacer con el candado de la oficina tomado. Lanza
     * AgendaOcupadaException si no se consigue a tiempo.
     */
    public static function conBloqueo(?string $sede, callable $hacer)
    {
        if (DB::getDriverName() !== 'mysql') {
            return $hacer();
        }

        $nombre = 'siconcilio_agenda_'.Str::slug(Str::ascii(self::oficina($sede)));
        $tomado = (int) (DB::selectOne('SELECT GET_LOCK(?, ?) AS ok', [$nombre, self::ESPERA])->ok ?? 0);

        if ($tomado !== 1) {
            throw new AgendaOcupadaException(
                'En este momento se está agendando otra audiencia en la misma sede. Intenta de nuevo en unos segundos.'
            );
        }

        try {
            return $hacer();
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$nombre]);
        }
    }

    /** "9:00", "09:00" o "09:00:00" → "09:00:00". */
    public static function hora(string $hora): string
    {
        $hora = trim($hora);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?/', $hora, $m)) {
            return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3] ?? 0);
        }

        return $hora;
    }
}
