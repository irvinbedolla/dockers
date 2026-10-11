<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Horario de citas de una sede para un trámite. Lo edita el Super Usuario
 * en Administración → Horarios de citas; App\Support\HorarioCitas lo usa
 * para generar los horarios del calendario y validar al guardar.
 */
class HorarioCita extends Model
{
    protected $table = 'horarios_citas';

    protected $fillable = [
        'delegacion', 'tramite', 'hora_inicio', 'hora_fin', 'intervalo', 'duracion',
        'comida_inicio', 'comida_minutos', 'citas_por_horario', 'horas_un_lugar',
        'limite_diario', 'activo', 'updated_by',
    ];

    protected $casts = [
        'horas_un_lugar'    => 'array',
        'activo'            => 'boolean',
        'intervalo'         => 'integer',
        'duracion'          => 'integer',
        'comida_minutos'    => 'integer',
        'citas_por_horario' => 'integer',
        'limite_diario'     => 'integer',
    ];

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
