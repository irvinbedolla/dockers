<?php

namespace App\Support;

use App\Models\User;

/**
 * Qué sedes y qué conciliadores puede ver cada usuario en la agenda.
 *
 * Esta lógica vivía dentro de DashboardController::index(). Al aparecer la
 * pantalla de Inicio —que también muestra el calendario— se sacó aquí para que
 * las dos pantallas resuelvan el alcance con la misma regla y no se separen
 * cuando alguien toque una sola.
 */
class AgendaContexto
{
    /** Sedes que se atienden desde cada delegación. */
    private const SEDES_POR_DELEGACION = [
        'Morelia'         => ['Morelia', 'Zitácuaro'],
        'Zitácuaro'       => ['Morelia', 'Zitácuaro'],
        'Uruapan'         => ['Uruapan', 'Lázaro Cárdenas'],
        'Lázaro Cárdenas' => ['Uruapan', 'Lázaro Cárdenas'],
        'Zamora'          => ['Zamora', 'Sahuayo'],
        'Sahuayo'         => ['Zamora', 'Sahuayo'],
    ];

    private const TODAS_LAS_SEDES = [
        'Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo',
    ];

    /** Ven todas las sedes y a todos los conciliadores. */
    private const ROLES_SIN_LIMITE = ['Super Usuario', 'Administrador', 'Estadistica'];

    /** Ven su región y a los conciliadores de su delegación. */
    private const ROLES_DE_REGION = ['Delegado', 'Enlace'];

    /**
     * @return array{sedes: array<int, string>, conciliadores: \Illuminate\Database\Eloquent\Collection}
     */
    public static function para(User $usuario): array
    {
        $rol         = $usuario->roles->pluck('name')->first();
        $delegacion  = $usuario->delegacion;

        if (in_array($rol, self::ROLES_SIN_LIMITE, true)) {
            return [
                'sedes'         => self::TODAS_LAS_SEDES,
                'conciliadores' => self::conciliadores(),
            ];
        }

        // Antes, si la delegación no estaba en el mapa, $sedes se quedaba sin
        // definir y la vista reventaba. El ?? [] evita ese caso.
        $sedes = self::SEDES_POR_DELEGACION[$delegacion] ?? [];

        if (in_array($rol, self::ROLES_DE_REGION, true)) {
            return [
                'sedes'         => $sedes,
                'conciliadores' => self::conciliadores($delegacion),
            ];
        }

        // El resto sólo se ve a sí mismo en el selector de conciliador.
        return [
            'sedes'         => $sedes,
            'conciliadores' => self::conciliadores(null, $usuario->id),
        ];
    }

    private static function conciliadores(?string $delegacion = null, ?int $id = null)
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', 'Conciliador'))
            ->when($delegacion, fn ($q) => $q->where('delegacion', $delegacion))
            ->when($id, fn ($q) => $q->where('id', $id))
            ->get();
    }
}
