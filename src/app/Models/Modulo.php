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
        'en_linea', 'pausa_ventanilla', 'en_linea_cambio_at', 'en_linea_cambio_por',
    ];

    protected $casts = [
        'tramites' => 'array',
        'respaldo' => 'array',
        'activo'   => 'boolean',
        'en_linea' => 'boolean',
        'pausa_ventanilla' => 'boolean',
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

    /**
     * ¿La ventanilla (y la reagenda automática) le puede asignar citas? Se
     * apaga con la pausa "Todas, también ventanilla".
     */
    public function recibeVentanilla(): bool
    {
        return ! ($this->pausa_ventanilla ?? false);
    }

    /** ¿Recibe citas nuevas por este canal? 'linea', 'ventanilla' o null (cualquiera). */
    public function recibePor(?string $canal): bool
    {
        return match ($canal) {
            'linea'      => $this->recibeEnLinea() && $this->recibeVentanilla(),
            'ventanilla' => $this->recibeVentanilla(),
            default      => true,
        };
    }

    /** null (recibe todo), 'linea' (sólo pausa en línea) o 'todas' (también ventanilla). */
    public function pausa(): ?string
    {
        return ! $this->recibeVentanilla() ? 'todas' : (! $this->recibeEnLinea() ? 'linea' : null);
    }

    /** ¿Ya corrió la migración de en_linea? */
    public static function enLineaMigrado(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('modulos', 'en_linea');
    }

    /** ¿Ya corrió la migración de pausa_ventanilla? */
    public static function pausaVentanillaMigrada(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('modulos', 'pausa_ventanilla');
    }

    /** "Recibe en línea y ventanilla", "En línea en pausa desde hoy 10:32 · Ana Pérez"… */
    public function notaPausa(): string
    {
        $cuando = $this->en_linea_cambio_at;
        $quien  = $this->en_linea_cambio_por ? optional($this->cambioEnLineaPor)->name : null;
        $quien  = $quien ? ' · '.Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($quien)))) : '';
        $fecha  = $cuando
            ? ($cuando->isToday() ? 'hoy '.$cuando->format('H:i') : $cuando->locale('es')->isoFormat('D MMM YYYY, HH:mm'))
            : null;

        $desde = ($fecha ? ' desde '.$fecha : '').$quien;

        return match ($this->pausa()) {
            'todas' => 'Sin citas nuevas (ni en línea ni ventanilla)'.$desde,
            'linea' => 'En línea en pausa'.$desde.' · ventanilla sí',
            default => 'Recibe en línea y ventanilla'.($fecha ? ' · reactivado '.$fecha.$quien : ''),
        };
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
