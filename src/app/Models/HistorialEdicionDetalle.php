<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEdicionDetalle extends Model
{
    protected $table = 'historial_edicion_detalles';
    protected $primaryKey = 'id';
    protected $fillable = ['historial_edicion_id', 'tabla', 'registro_id', 'accion', 'datos_antes', 'datos_despues'];
    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function edicion()
    {
        return $this->belongsTo(HistorialEdicion::class, 'historial_edicion_id');
    }
}
