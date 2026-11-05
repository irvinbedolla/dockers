<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un exhorto registrado en Notificaciones → Exhortos (ExhortosController).
 */
class Exhorto extends Model
{
    protected $table = 'exhortos';

    protected $fillable = [
        'id_solicitud', 'id_citado', 'NUE', 'delegacion', 'solicitante', 'citado',
        'estado_id', 'municipio_id', 'contestado', 'fecha_contestacion', 'observaciones',
        'registrado_por', 'actualizado_por',
    ];

    protected $casts = [
        'contestado'         => 'boolean',
        'fecha_contestacion' => 'date',
    ];

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
