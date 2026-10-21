<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\FolioCita;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    use HasFactory;
    protected $table = 'recepcion';
    protected $primaryKey = 'id';
    protected $fillable = ['consecutivo','codigo','fecha','hora','hora_fin','auxiliar','solicitante','tipo','lugar_auxiliar','estatus','delegacion','exepcion','edad','sexo','vulnerables','conflicto','tipo_caso','motivo_excepcion','prestacionSS','orientacion','tarjeta','folio','resultado','INS', 'telefono', 'correo', 'estado','municipio', 'observaciones', 'modulo_id', 'origen',
        'es_prueba', 'curp', 'domicilio', 'llamado_at', 'atencion_inicio_at', 'atencion_fin_at', 'confirmada_at',
        'creado_por', 'creado_por_nombre', 'atendido_por', 'atendido_por_nombre', 'llego_tarde', 'llego_tarde_por'];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'es_prueba' => 'boolean',
        'llamado_at' => 'datetime',
        'atencion_inicio_at' => 'datetime',
        'atencion_fin_at' => 'datetime',
        'confirmada_at' => 'datetime',
        'llego_tarde'   => 'boolean',
    ];

    public const ESTADOS = ['pendiente', 'atendida', 'no atendida'];

    /**
     * Toda cita nueva sale con su folio MOR-S26-0142, venga del formulario
     * público o de la ventanilla, sin que cada controlador tenga que pedirlo.
     */
    protected static function booted(): void
    {
        static::creating(function (Recepcion $cita) {
            if (empty($cita->codigo)) {
                $cita->codigo = FolioCita::generar($cita->delegacion, $cita->tipo);
            }

            // Quién registró el turno en el Centro (en línea no hay sesión: lo agendó la persona).
            if (\App\Support\FlujoCita::conResponsables() && empty($cita->creado_por) && auth()->check()) {
                $cita->creado_por = auth()->id();
                $cita->creado_por_nombre = mb_substr((string) auth()->user()->name, 0, 120);
            }
        });

        // Quién la atendió: el primero que inicia su atención o la da por atendida.
        static::saving(function (Recepcion $cita) {
            if (! \App\Support\FlujoCita::conResponsables() || ! empty($cita->atendido_por) || ! auth()->check()) {
                return;
            }
            $empezo  = $cita->isDirty('atencion_inicio_at') && $cita->atencion_inicio_at;
            $termino = $cita->isDirty('estatus') && $cita->estatus === 'atendido';
            if ($empezo || $termino) {
                $cita->atendido_por = auth()->id();
                $cita->atendido_por_nombre = mb_substr((string) auth()->user()->name, 0, 120);
            }
        });
    }

    /** Folio para mostrar: el nuevo, o el consecutivo en citas anteriores. */
    public function getFolioCitaAttribute(): string
    {
        return FolioCita::mostrar($this);
    }
}
