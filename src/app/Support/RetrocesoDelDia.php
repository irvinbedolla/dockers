<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Reglas de quién puede aplicar un retroceso (ratificación y audiencia).
 *
 *   Super Usuario                  cualquier expediente, de cualquier día.
 *   Administrador/Delegado/Enlace  sólo lo que se registró HOY: la última
 *                                  audiencia (o la ratificación) tiene fecha
 *                                  de hoy, o se creó o se le registró el
 *                                  resultado hoy. Delegado y Enlace, además,
 *                                  sólo de su sede y sus oficinas de apoyo.
 *
 * Antes la regla miraba la fecha de la SOLICITUD (cuando se capturó), que
 * casi nunca es hoy: por eso a los Enlaces les salía el botón deshabilitado.
 * Se revisa en la pantalla y otra vez en el servidor.
 */
class RetrocesoDelDia
{
    public const AVISO = 'Sólo puedes retroceder lo que se registró hoy. Para algo de otro día, pídeselo a un Super Usuario.';

    public static function sinLimite(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasRole('Super Usuario');
    }

    /** ¿Alguna de estas fechas es de hoy? Las vacías se ignoran. */
    public static function esDeHoy(...$fechas): bool
    {
        foreach ($fechas as $fecha) {
            if ($fecha === null || $fecha === '') {
                continue;
            }
            try {
                if (Carbon::parse($fecha)->isToday()) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fecha ilegible: no cuenta.
            }
        }

        return false;
    }

    public static function permitido(?User $usuario, ...$fechas): bool
    {
        return self::sinLimite($usuario) || self::esDeHoy(...$fechas);
    }

    /**
     * ¿La sede del expediente está entre las permitidas? null = sin filtro
     * (roles que no son Delegado ni Enlace). Compara sin acentos.
     */
    public static function enSuSede(?array $permitidas, ?string $sede): bool
    {
        if ($permitidas === null) {
            return true;
        }
        $normal = fn ($s) => mb_strtolower(Str::ascii(trim((string) $s)));

        return in_array($normal($sede), array_map($normal, $permitidas), true);
    }
}
