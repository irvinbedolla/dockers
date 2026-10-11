<?php

namespace App\Support;

use App\Models\User;

/**
 * Especialidades del Auxiliar de Conciliador.
 *
 * "Auxiliar" se queda como rol base (el código pregunta hasRole('Auxiliar')
 * en muchos lugares) pero sólo con lo que comparten todos. Lo de cada trámite
 * vive en un rol de especialidad que se suma al base:
 *
 *   Auxiliar + Auxiliar Solicitudes     → asesorías y solicitudes
 *   Auxiliar + Auxiliar Ratificaciones  → ratificaciones
 *   Auxiliar + Auxiliar Cumplimientos   → cumplimientos
 *
 * Alguien que cubre varias áreas lleva varias especialidades. Ver la
 * migración split_auxiliar_role para el reparto inicial.
 */
class Especialidades
{
    public const BASE = 'Auxiliar';

    public const SOLICITUDES    = 'Auxiliar Solicitudes';
    public const RATIFICACIONES = 'Auxiliar Ratificaciones';
    public const CUMPLIMIENTOS  = 'Auxiliar Cumplimientos';

    public const ROLES = [self::SOLICITUDES, self::RATIFICACIONES, self::CUMPLIMIENTOS];

    /** Etiqueta corta para chips y listas. */
    public const ETIQUETAS = [
        self::SOLICITUDES    => 'Solicitudes y asesorías',
        self::RATIFICACIONES => 'Ratificaciones',
        self::CUMPLIMIENTOS  => 'Cumplimientos',
    ];

    /** Trámite de módulo → especialidad que lo atiende. */
    public const POR_TRAMITE = [
        'Solicitud'    => self::SOLICITUDES,
        'Asesoría'     => self::SOLICITUDES,
        'Ratificación' => self::RATIFICACIONES,
        'Cumplimiento' => self::CUMPLIMIENTOS,
    ];

    /** Permiso que hace falta para atender cada trámite de un módulo. */
    public const PERMISO_POR_TRAMITE = [
        'Solicitud'    => 'solicitudes_crear',
        'Asesoría'     => 'asesoria_crear',
        'Ratificación' => 'ratificaciones_crear',
        'Cumplimiento' => 'cumplimientos_consultar',
    ];

    /** ¿Esta persona tiene los permisos para atender el trámite? */
    public static function puedeAtender(?User $usuario, string $tramite): bool
    {
        $permiso = self::PERMISO_POR_TRAMITE[$tramite] ?? null;

        return $usuario !== null && $permiso !== null && $usuario->can($permiso);
    }

    /**
     * ¿Sus únicos roles son Auxiliar y especialidades? A esas personas se les
     * limita el menú y las pantallas por especialidad; a quien además tiene
     * otro rol (Delegado, Conciliador…) lo decide ese otro rol, como siempre.
     */
    public static function soloAuxiliar(?User $usuario): bool
    {
        if ($usuario === null || ! $usuario->hasRole(self::BASE)) {
            return false;
        }

        return $usuario->getRoleNames()->diff(array_merge([self::BASE], self::ROLES))->isEmpty();
    }
}
