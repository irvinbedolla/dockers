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
    protected $fillable = ['consecutivo','codigo','fecha','hora','hora_fin','auxiliar','solicitante','tipo','lugar_auxiliar','estatus','delegacion','exepcion','edad','sexo','vulnerables','conflicto','tipo_caso','prestacionSS','orientacion','tarjeta','folio','resultado','INS', 'telefono', 'correo', 'estado','municipio', 'observaciones', 'modulo_id', 'origen',
        'es_prueba', 'curp', 'domicilio', 'llamado_at', 'atencion_inicio_at', 'atencion_fin_at'];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'es_prueba' => 'boolean',
        'llamado_at' => 'datetime',
        'atencion_inicio_at' => 'datetime',
        'atencion_fin_at' => 'datetime',
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

            // Citas agendadas con un correo de prueba (Administración → Flujo de
            // citas) quedan marcadas solas: así se prueba desde el formulario
            // público sin tocar las citas reales.
            if (! $cita->es_prueba && \App\Support\FlujoCita::migrado()
                && \App\Support\FlujoCita::esCorreoPrueba($cita->correo)) {
                $cita->es_prueba = true;
            }
        });
    }

    /** Folio para mostrar: el nuevo, o el consecutivo en citas anteriores. */
    public function getFolioCitaAttribute(): string
    {
        return FolioCita::mostrar($this);
    }
}
