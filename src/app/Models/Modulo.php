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
        'en_linea', 'pausa_ventanilla', 'pausa_desde', 'en_linea_cambio_at', 'en_linea_cambio_por',
    ];

    protected $casts = [
        'tramites' => 'array',
        'respaldo' => 'array',
        'activo'   => 'boolean',
        'en_linea' => 'boolean',
        'pausa_ventanilla' => 'boolean',
        'pausa_desde' => 'date',
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

    /**
     * ¿Recibe citas nuevas por este canal ('linea', 'ventanilla' o null =
     * cualquiera) para una cita de esa fecha (Y-m-d; null = hoy)? Con la pausa
     * programada (pausa_desde) sólo cuenta de esa fecha en adelante.
     */
    public function recibePor(?string $canal, ?string $fecha = null): bool
    {
        $pausa = $this->pausaPara($fecha);

        return match ($canal) {
            'linea'      => $pausa === null,
            'ventanilla' => $pausa !== 'todas',
            default      => true,
        };
    }

    /** La pausa que aplica a una cita de esa fecha (null = hoy): null, 'linea' o 'todas'. */
    public function pausaPara(?string $fecha = null): ?string
    {
        $pausa = $this->pausa();
        if ($pausa === null || ! $this->pausa_desde) {
            return $pausa;
        }
        $dia = $fecha ? substr($fecha, 0, 10) : now()->toDateString();

        return $dia >= $this->pausa_desde->toDateString() ? $pausa : null;
    }

    /** ¿La pausa está puesta pero empieza en una fecha que todavía no llega? */
    public function pausaProgramada(): bool
    {
        return $this->pausa() !== null && $this->pausa_desde !== null
            && $this->pausa_desde->toDateString() > now()->toDateString();
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

    /** ¿Ya corrió la migración de pausa_desde? */
    public static function pausaDesdeMigrada(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('modulos', 'pausa_desde');
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

        // Con fecha: "a partir del vie 20 nov 2026"; sin ella, desde cuándo se apagó.
        $desde = ($this->pausa_desde
            ? ' a partir del '.$this->pausa_desde->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')
            : ($fecha ? ' desde '.$fecha : '')).$quien;
        $prog = $this->pausaProgramada();

        return match ($this->pausa()) {
            'todas' => ($prog ? 'Programado: sin' : 'Sin').' citas nuevas ni reasignaciones (en línea y ventanilla)'.$desde,
            'linea' => ($prog ? 'Programado: en' : 'En').' línea en pausa'.$desde.' · ventanilla sí',
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
