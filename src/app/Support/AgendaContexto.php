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

    /**
     * ¿Este usuario sólo puede ver y descargar su propia agenda?
     *
     * Es la misma regla que AudienciasController@audiencias aplica sobre el
     * calendario: al Conciliador se le fija `audiencias.id_conciliador` a su
     * propio id, y a todos los demás roles se les acota por sede, no por
     * persona. Vive aquí para que la pantalla y la descarga no se separen.
     *
     * Deliberadamente NO restringe por id a Delegado ni Enlace: sus sedes ya
     * acotan lo que ven, y filtrar además por la lista de conciliadores de su
     * delegación escondería las audiencias celebradas en su sede por alguien
     * adscrito a otra.
     */
    public static function soloSuAgenda(User $usuario): bool
    {
        return $usuario->roles->pluck('name')->first() === 'Conciliador';
    }

    /** Meses hacia atrás que cuentan como "con actividad". */
    private const MESES_ACTIVIDAD = 6;

    /**
     * Los conciliadores que la agenda ofrece como filtro.
     *
     * Se limita a los que tienen actividad reciente —una solicitud o una
     * audiencia a su nombre en los últimos meses—. Con la fila de pastillas
     * en pantalla esto importa: la lista completa arrastra cuentas de prueba
     * y gente que ya no opera, y cada una ocupa espacio horizontal.
     *
     * El filtro por id (el propio usuario) se salta la regla a propósito: un
     * conciliador recién llegado debe verse a sí mismo aunque todavía no
     * tenga nada asignado.
     */
    private static function conciliadores(?string $delegacion = null, ?int $id = null)
    {
        $desde = now()->subMonths(self::MESES_ACTIVIDAD)->toDateString();

        return User::whereHas('roles', fn ($q) => $q->where('name', 'Conciliador'))
            ->when($delegacion, fn ($q) => $q->where('delegacion', $delegacion))
            ->when($id, fn ($q) => $q->where('id', $id))
            ->when(! $id, fn ($q) => $q->where(function ($sub) use ($desde) {
                $sub->whereIn('id', fn ($s) => $s->select('conciliador_id')
                        ->from('seer_general')
                        ->whereNotNull('conciliador_id')
                        ->where('fecha', '>=', $desde))
                    ->orWhereIn('id', fn ($s) => $s->select('id_conciliador')
                        ->from('audiencias')
                        ->whereNotNull('id_conciliador')
                        ->where('fecha', '>=', $desde));
            }))
            ->orderBy('name')
            ->get();
    }
}
