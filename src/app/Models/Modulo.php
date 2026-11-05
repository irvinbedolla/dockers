<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Un módulo de atención de una sede y la persona que lo atiende.
 * Ver la migración create_modulos_table para el porqué.
 */
class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'delegacion', 'nombre', 'user_id', 'tramites', 'respaldo', 'hora_comida', 'activo', 'orden',
        'en_linea', 'en_linea_cambio_at', 'en_linea_cambio_por',
    ];

    protected $casts = [
        'tramites' => 'array',
        'respaldo' => 'array',
        'activo'   => 'boolean',
        'en_linea' => 'boolean',
        'en_linea_cambio_at' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Quién prendió o apagó por última vez las citas en línea. */
    public function cambioEnLineaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'en_linea_cambio_por');
    }

    /**
     * ¿El formulario público le puede asignar citas? Se pausa con el switch
     * "Citas en línea" de Administración → Módulos. Sin la columna (migración
     * pendiente) todos reciben, como antes.
     */
    public function recibeEnLinea(): bool
    {
        return $this->en_linea ?? true;
    }

    /** ¿Ya corrió la migración de en_linea? */
    public static function enLineaMigrado(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('modulos', 'en_linea');
    }

    /** "Recibe citas en línea" o "En pausa desde hoy 10:32 · Ana Pérez". */
    public function notaEnLinea(): string
    {
        $cuando = $this->en_linea_cambio_at;
        $quien  = $this->en_linea_cambio_por ? optional($this->cambioEnLineaPor)->name : null;
        $quien  = $quien ? ' · '.Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($quien)))) : '';
        $fecha  = $cuando
            ? ($cuando->isToday() ? 'hoy '.$cuando->format('H:i') : $cuando->locale('es')->isoFormat('D MMM YYYY, HH:mm'))
            : null;

        if (! $this->recibeEnLinea()) {
            return 'En pausa'.($fecha ? ' desde '.$fecha : '').$quien;
        }

        return 'Recibe citas en línea'.($fecha ? ' · reactivado '.$fecha.$quien : '');
    }

    public function recibe(string $tramite): bool
    {
        return in_array($tramite, $this->tramites ?? [], true);
    }

    public function recibeDeRespaldo(string $tramite): bool
    {
        return in_array($tramite, $this->respaldo ?? [], true);
    }

    /** ¿Esta hora cae en su media hora de comida? */
    public function enComida(string $hora): bool
    {
        return $this->hora_comida !== null && substr($this->hora_comida, 0, 5) === substr($hora, 0, 5);
    }
}
