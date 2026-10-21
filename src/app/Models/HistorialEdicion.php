<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEdicion extends Model
{
    protected $table = 'historial_ediciones';
    protected $primaryKey = 'id';
    protected $fillable = ['solicitud_id', 'audiencia_id', 'NUE', 'delegacion', 'origen', 'user_id', 'user_nombre', 'user_rol', 'ip'];

    public function detalles()
    {
        return $this->hasMany(HistorialEdicionDetalle::class, 'historial_edicion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
