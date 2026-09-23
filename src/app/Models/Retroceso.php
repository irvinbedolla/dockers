<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retroceso extends Model
{
    protected $table = 'retrocesos';
    protected $primaryKey = 'id';
    protected $fillable = ['tipo', 'entidad_tipo', 'entidad_id', 'NUE', 'delegacion', 'estatus_previo', 'estatus_nuevo',
    'motivo', 'user_id', 'user_nombre', 'ip'];

    public function detalles()
    {
        return $this->hasMany(RetrocesoDetalle::class, 'retroceso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
