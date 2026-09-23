<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetrocesoDetalle extends Model
{
    protected $table = 'retroceso_detalles';
    protected $primaryKey = 'id';
    protected $fillable = ['retroceso_id', 'tabla', 'registro_id', 'accion', 'datos_antes', 'datos_despues'];
    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function retroceso()
    {
        return $this->belongsTo(Retroceso::class, 'retroceso_id');
    }
}
