<?php

namespace App\Support\Sinotifico;

use App\Http\Controllers\NotificacionesController;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Quién entra a SíNotifico y qué ve. Se decide por permisos de Spatie, no
 * por nombre de rol, para administrarlo desde Roles y Usuarios sin tocar
 * código:
 *
 *   sinotifico_notificar         su lista de diligencias en campo
 *                                (rol Notificador);
 *   sinotifico_supervisar_sede   cómo van los notificadores de su región
 *                                (rol Delegado y, uno por uno, los Enlaces
 *                                de Archivo que se elijan en Usuarios; ver sedes());
 *   sinotifico_supervisar_todas  la actividad de todas las sedes
 *                                (Super Usuario y Directivo).
 *
 * Los Enlaces asignan las notificaciones, pero no todos supervisan: por eso
 * su permiso es directo a la persona y no al rol.
 */
final class Acceso
{
    public const GUARD = 'web';

    public const NOTIFICAR = 'sinotifico_notificar';
    public const SUPERVISAR_SEDE = 'sinotifico_supervisar_sede';
    public const SUPERVISAR_TODAS = 'sinotifico_supervisar_todas';

    /** Lo que ve quien intenta entrar (o seguir dentro) sin permiso. */
    public const SIN_PERMISO = 'No tienes permisos para usar SíNotifico. Si lo necesitas, pide al administrador que te habilite el acceso.';

    public const PERMISOS = [self::NOTIFICAR, self::SUPERVISAR_SEDE, self::SUPERVISAR_TODAS];

    /** Rol cuyas cuentas pueden recibir, una por una, la supervisión de su sede. */
    public const ROL_ELEGIBLE = 'Enlace';

    /** Roles que se muestran en la app, en orden de prioridad para la etiqueta. */
    public const ROLES_VISIBLES = ['Notificador', 'Enlace', 'Delegado', 'Directivo', 'Super Usuario'];

    public static function puedeEntrar(User $usuario): bool
    {
        return self::notifica($usuario) || self::supervisa($usuario) !== null;
    }

    public static function notifica(User $usuario): bool
    {
        return $usuario->checkPermissionTo(self::NOTIFICAR, self::GUARD);
    }

    /** 'todas', 'sede' o null si no supervisa. */
    public static function supervisa(User $usuario): ?string
    {
        return match (true) {
            $usuario->checkPermissionTo(self::SUPERVISAR_TODAS, self::GUARD) => 'todas',
            $usuario->checkPermissionTo(self::SUPERVISAR_SEDE, self::GUARD) => 'sede',
            default => null,
        };
    }

    /**
     * Sedes que supervisa: null = todas; [] = ninguna. Quien supervisa "su sede"
     * ve la región de su delegación, con la misma regla que Notificaciones
     * (Morelia también Zitácuaro, Uruapan también Lázaro Cárdenas y Zamora
     * también Sahuayo).
     *
     * @return list<string>|null
     */
    public static function sedes(User $usuario): ?array
    {
        return match (self::supervisa($usuario)) {
            'todas' => null,
            'sede' => array_values(array_filter(NotificacionesController::sedesDeUsuario($usuario))),
            default => [],
        };
    }

    /** Si la sede (de un notificador o de una diligencia) está en su alcance. */
    public static function supervisaSede(User $usuario, ?string $sede): bool
    {
        $sedes = self::sedes($usuario);

        return $sedes === null || in_array($sede, $sedes, true);
    }

    /** Distintivo que se muestra junto al nombre en la app. */
    public static function distintivo(User $usuario): ?string
    {
        return match (self::supervisa($usuario)) {
            'todas' => 'Supervisión de todas las sedes',
            'sede' => 'Supervisa '.implode(' y ', self::sedes($usuario)),
            default => null,
        };
    }

    /** Si a esta persona se le eligió a mano (permiso directo) para supervisar su sede. */
    public static function supervisaPorEleccion(User $usuario): bool
    {
        if (! $usuario->exists || ! self::existe(self::SUPERVISAR_SEDE)) {
            return false;
        }

        return $usuario->hasDirectPermission(self::SUPERVISAR_SEDE);
    }

    /**
     * Da o quita la supervisión elegida a mano. Sólo se conserva mientras la
     * cuenta tenga el rol Enlace: al quitarle el rol se le quita también, para
     * que no quede un acceso que ya nadie ve en el formulario.
     */
    public static function elegirSupervisor(User $usuario, bool $supervisa): void
    {
        if (! self::existe(self::SUPERVISAR_SEDE)) {
            return;
        }

        $debe = $supervisa && $usuario->hasRole(self::ROL_ELEGIBLE);
        $tiene = $usuario->hasDirectPermission(self::SUPERVISAR_SEDE);

        if ($debe && ! $tiene) {
            $usuario->givePermissionTo(self::SUPERVISAR_SEDE);
        } elseif (! $debe && $tiene) {
            $usuario->revokePermissionTo(self::SUPERVISAR_SEDE);
        }
    }

    /** Antes de correr la migración los permisos no existen: no se truena. */
    private static function existe(string $permiso): bool
    {
        static $existen = [];

        return $existen[$permiso] ??= Permission::where('name', $permiso)->where('guard_name', self::GUARD)->exists();
    }
}
