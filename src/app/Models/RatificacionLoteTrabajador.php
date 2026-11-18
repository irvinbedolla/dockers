<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un trabajador de una ratificación múltiple: lo que la empresa capturó en el
 * Excel y los mínimos legales que calculó el sistema. Con recepcion_id queda
 * ligado a su cita; con turno_id, a la ratificación ya capturada.
 */
class RatificacionLoteTrabajador extends Model
{
    protected $table = 'ratificaciones_lote_trabajadores';

    protected $fillable = [
        'lote_id', 'numero', 'nombre_completo', 'nombre', 'primer_apellido', 'segundo_apellido', 'puesto',
        'salario_diario', 'fecha_ingreso', 'fecha_termino', 'aguinaldo_dias',
        'indemnizacion', 'vacaciones', 'prima_vacacional', 'aguinaldo', 'salarios_pendientes', 'otras',
        'total', 'impuesto', 'neto', 'minimos', 'observaciones', 'recepcion_id', 'turno_id',
    ];

    protected $casts = [
        'otras' => 'array',
        'minimos' => 'array',
        'fecha_ingreso' => 'date',
        'fecha_termino' => 'date',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(RatificacionLote::class, 'lote_id');
    }
}
