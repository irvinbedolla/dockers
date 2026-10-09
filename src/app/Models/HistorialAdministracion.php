<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAdministracion extends Model
{
    protected $table = 'historial_administracion';
    protected $primaryKey = 'id';
    protected $fillable = ['tipo', 'tabla', 'registro_id', 'NUE', 'delegacion', 'datos_antes', 'datos_despues', 'motivo',
    'user_id', 'user_nombre', 'ip'];
    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
