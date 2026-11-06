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
        'no_reasignar_desde', 'no_reasignar_por', 'no_reasignar_at',
    ];

    protected $casts = [
        'tramites' => 'array',
        'respaldo' => 'array',
        'activo'   => 'boolean',
        'en_linea' => 'boolean',
        'pausa_ventanilla' => 'boolean',
        'no_reasignar_desde' => 'date',
        'no_reasignar_at' => 'datetime',
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

    /** Quién bloqueó o desbloqueó por última vez las reasignaciones. */
    public function cambioReasignacionPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'no_reasignar_por');
    }

    /**
     * ¿Se le puede mover (reasignar o reagendar) una cita de esa fecha (Y-m-d;
     * null = hoy)? No, si tiene la pausa total de citas o si desde esa fecha
     * tiene bloqueadas las reasignaciones ("Acepta reasignaciones").
     */
    public function aceptaReasignacion(?string $fecha = null): bool
    {
        if (! $this->recibeVentanilla()) {
            return false;
        }
        $desde = $this->no_reasignar_desde;
        $dia   = $fecha ? substr($fecha, 0, 10) : now()->toDateString();

        return ! ($desde && $dia >= $desde->toDateString());
    }

    /** null (acepta), 'vigente' (ya no acepta) o 'programado' (desde una fecha que no llega). */
    public function bloqueoReasignacion(): ?string
    {
        $desde = $this->no_reasignar_desde;
        if (! $desde) {
            return null;
        }

        return $desde->toDateString() > now()->toDateString() ? 'programado' : 'vigente';
    }

    /** ¿Ya corrió la migración del bloqueo de reasignaciones? */
    public static function reasignacionMigrada(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('modulos', 'no_reasignar_desde');
    }

    /** "Acepta reasignaciones", "Sin reasignaciones a partir del lunes 20 de noviembre de 2026 · Ana Pérez"… */
    public function notaReasignacion(): string
    {
        if ($this->pausa() === 'todas') {
            return 'Tampoco se le reasigna: tiene la pausa total de citas';
        }
        $desde = $this->no_reasignar_desde;
        if (! $desde) {
            return 'Se le pueden mover citas de otros módulos';
        }
        $quien = $this->no_reasignar_por ? optional($this->cambioReasignacionPor)->name : null;
        $quien = $quien ? ' · '.Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($quien)))) : '';
        $fecha = $desde->isToday() ? 'hoy' : 'el '.$desde->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');

        return $this->bloqueoReasignacion() === 'programado'
            ? 'Programado: sin reasignaciones a partir del '.$desde->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY').$quien
            : 'Sin reasignaciones de citas desde '.$fecha.$quien;
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

    /**
     * ¿Es un módulo de casos de excepción? En cada sede, aparte de los
     * normales, puede haber uno o más; sólo reciben el trámite "Excepción" y
     * sólo los atiende quien tiene el rol de Conciliación, Género e Igualdad
     * o el distintivo de Enlace de Género e Igualdad.
     */
    public function esDeExcepcion(): bool
    {
        return in_array(\App\Support\AsignadorModulos::TRAMITE_EXCEPCION, $this->tramites ?? [], true);
    }

    /** Los módulos normales (sin los de casos de excepción, que no van en Mi módulo). */
    public function scopeNormales($query)
    {
        // tramites es JSON ("Excepci\u00f3n"): basta con "Excepci".
        return $query->where(fn ($q) => $q->whereNull('tramites')->orWhere('tramites', 'not like', '%Excepci%'));
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
