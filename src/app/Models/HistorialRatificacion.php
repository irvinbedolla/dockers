<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialRatificacion extends Model
{
    protected $table = 'historial_ratificaciones';
    protected $primaryKey = 'id';
    protected $fillable = ['ratificacion_id', 'NUE', 'delegacion', 'estatus', 'user_id', 'user_nombre', 'user_rol',
    'ip', 'motivo'];

    public function detalles()
    {
        return $this->hasMany(HistorialRatificacionDetalle::class, 'historial_edicion_id');
    }

    public function ratificacion()
    {
        return $this->belongsTo(Turnos::class, 'ratificacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
