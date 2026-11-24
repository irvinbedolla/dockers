<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Un día bloqueado en el calendario de citas de una persona (Administración
 * → Módulos, "Días bloqueados" en la tarjeta de su módulo). Ese día no se le
 * asignan citas nuevas (en línea ni ventanilla) ni se le mueven citas
 * (reasignar, reagendar): Modulo::bloqueadoEl() lo revisa en
 * AsignadorModulos::elegir, disponibilidadEnRango y aceptaReasignacion.
 *
 * Va por persona (user_id): si cambia de módulo, sus días bloqueados la siguen.
 */
class BloqueoCita extends Model
{
    protected $table = 'bloqueos_citas';

    protected $fillable = ['user_id', 'fecha', 'motivo', 'modulo_id', 'creado_por'];

    protected $casts = ['fecha' => 'date'];

    /** Días que se pueden bloquear de una vez (un trimestre). */
    public const MAX_DIAS = 93;

    /** @var array<int, array<string, true>>|null user_id => ['Y-m-d' => true] */
    private static ?array $mapa = null;

    private static ?bool $migrado = null;

    public static function migrado(): bool
    {
        return self::$migrado ??= Schema::hasTable('bloqueos_citas');
    }

    /** ¿Esa persona tiene bloqueado ese día? Una sola consulta por petición. */
    public static function bloqueado(?int $userId, string $fecha): bool
    {
        if (! $userId || ! self::migrado()) {
            return false;
        }

        if (self::$mapa === null) {
            self::$mapa = [];
            self::where('fecha', '>=', now()->subDays(31)->toDateString())
                ->get(['user_id', 'fecha'])
                ->each(function ($b) {
                    self::$mapa[(int) $b->user_id][$b->fecha->toDateString()] = true;
                });
        }

        return isset(self::$mapa[$userId][substr($fecha, 0, 10)]);
    }

    /** Después de guardar o quitar. */
    public static function olvidar(): void
    {
        self::$mapa = null;
        self::$migrado = null;
    }

    /** Días bloqueados de hoy en adelante, por persona: [user_id => Collection<BloqueoCita>]. */
    public static function proximos(array $userIds): Collection
    {
        if (! $userIds || ! self::migrado()) {
            return collect();
        }

        return self::whereIn('user_id', $userIds)
            ->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->get()
            ->groupBy('user_id');
    }
}
