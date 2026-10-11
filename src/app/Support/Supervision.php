<?php

namespace App\Support;

use App\Models\User;

/**
 * Roles que ven la agenda completa: todas las sedes y todas las fuentes
 * (audiencias, solicitudes, ratificaciones, cumplimientos y citas en línea).
 *
 * Antes esto estaba repetido como "Super Usuario" (y a veces
 * "Administrador") en cada endpoint del calendario. Directivo se suma aquí
 * para ver lo mismo que el Super Usuario.
 */
class Supervision
{
    public const VEN_TODAS_LAS_SEDES = ['Super Usuario', 'Directivo'];

    public static function veTodasLasSedes(?User $usuario, array $ademas = []): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(array_merge(self::VEN_TODAS_LAS_SEDES, $ademas));
    }
}
