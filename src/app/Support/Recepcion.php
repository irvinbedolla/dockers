<?php

namespace App\Support;

use App\Models\User;

/**
 * Los tres roles de recepción y lo que cada uno alcanza a ver.
 *
 * Es el único lugar donde se escriben sus nombres. Las rutas, el menú, la
 * agenda y la migración que los crea leen de aquí: un nombre de rol mal
 * escrito en un middleware no truena, simplemente deja fuera a la persona sin
 * avisar, y con tres nombres con acento eso es fácil de provocar.
 *
 * Por qué tres roles y no uno con la sede en la cuenta: el alcance de
 * recepción no sigue a users.delegacion. La recepción regional atiende las
 * cinco sedes que no son Morelia a la vez, y eso no se puede expresar con una
 * sola delegación. Morelia 01 y Morelia 02 van separados porque no hacen lo
 * mismo: Morelia 02 también recibe audiencias, ratificaciones y
 * cumplimientos (ver FUENTES).
 */
class Recepcion
{
    public const MORELIA_01 = 'Recepción Morelia 01';
    public const MORELIA_02 = 'Recepción Morelia 02';
    public const REGIONAL   = 'Recepción General';

    public const ROLES = [self::MORELIA_01, self::MORELIA_02, self::REGIONAL];

    /** Ven las citas en línea de todas las sedes, además de su agenda normal. */
    public const SUPERVISAN = ['Super Usuario', 'Administrador'];

    public const TODAS_LAS_SEDES = [
        'Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo',
    ];

    /** Lo que necesitan las pantallas de turnos; ahí sólo se revisan estos. */
    public const PERMISOS = ['turnos_ver', 'turnos_crear', 'turnos_asignar', 'turnos_revisar'];

    /**
     * Qué fuentes de la agenda ve cada rol (los ids de calFuente en
     * calendar.js). Morelia 02 además recibe a la gente que llega a
     * audiencias, ratificaciones y cumplimientos, así que ve esas agendas.
     * Nadie de recepción ve solicitudes ni descarga la agenda.
     */
    private const FUENTES = [
        self::MORELIA_01 => ['citasLinea'],
        self::MORELIA_02 => ['citasLinea', 'audiencias', 'ratificaciones', 'citas', 'conciliador', 'pagos'],
        self::REGIONAL   => ['citasLinea'],
    ];

    private const SEDES = [
        self::MORELIA_01 => ['Morelia'],
        self::MORELIA_02 => ['Morelia'],
        self::REGIONAL   => ['Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'],
    ];

    /**
     * Fuentes de la agenda de esta cuenta de recepción. Vacío si no es de
     * recepción (para los demás roles esta clase no decide nada).
     *
     * @return array<int, string>
     */
    public static function fuentes(?User $usuario): array
    {
        if (! self::es($usuario)) {
            return [];
        }

        $fuentes = [];
        foreach ($usuario->getRoleNames() as $rol) {
            $fuentes = array_merge($fuentes, self::FUENTES[$rol] ?? []);
        }

        return array_values(array_unique($fuentes));
    }

    public static function veFuente(?User $usuario, string $fuente): bool
    {
        return in_array($fuente, self::fuentes($usuario), true);
    }

    /** ¿La cuenta tiene alguno de los tres roles de recepción? */
    public static function es(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(self::ROLES);
    }

    /** ¿Ve la pestaña "Citas en línea"? Recepción y quienes la supervisan. */
    public static function veCitas(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(array_merge(self::SUPERVISAN, self::ROLES));
    }

    /**
     * Sedes cuyas citas ve este usuario. Vacío si no es de recepción.
     *
     * @return array<int, string>
     */
    public static function sedes(?User $usuario): array
    {
        if (! self::es($usuario)) {
            return [];
        }

        $sedes = [];
        foreach ($usuario->getRoleNames() as $rol) {
            $sedes = array_merge($sedes, self::SEDES[$rol] ?? []);
        }

        return array_values(array_unique($sedes));
    }

    /**
     * Sedes cuyos turnos puede consultar alguien en las pantallas de turnos:
     * quien supervisa, todas; la recepción, las de su rol; cualquier otro
     * (rol Turnos, Auxiliar), la de su cuenta, como siempre ha sido.
     *
     * @return array<int, string>
     */
    public static function sedesVisibles(?User $usuario): array
    {
        if ($usuario === null) {
            return [];
        }
        if ($usuario->hasAnyRole(self::SUPERVISAN)) {
            return self::TODAS_LAS_SEDES;
        }
        if (self::es($usuario)) {
            return self::sedes($usuario);
        }

        return $usuario->delegacion ? [$usuario->delegacion] : [];
    }

    /** Para el middleware de rutas: 'role:A|B|C'. */
    public static function middleware(string ...$ademas): string
    {
        return 'role:'.implode('|', array_merge($ademas, self::ROLES));
    }
}
