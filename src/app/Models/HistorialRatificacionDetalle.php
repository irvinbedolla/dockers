<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialRatificacionDetalle extends Model
{
    protected $table = 'historial_ediciones_ratificaciones';
    protected $primaryKey = 'id';
    protected $fillable = ['historial_edicion_id', 'tabla', 'registro_id', 'accion', 'datos_antes', 'datos_despues'];
    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function historial()
    {
        return $this->belongsTo(HistorialRatificacion::class, 'historial_edicion_id');
    }
}
