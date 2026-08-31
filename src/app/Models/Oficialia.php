<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficialia extends Model
{
    //use HasFactory;
    protected $table = 'oficialia';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'oficio_id' ,'delegacion', 'tipo_tramite','oficio', 'area_turno','precedencia','usuario_responsable','fecha','hora','fecha_registro', 'hora_registro', 'fecha_turno', 'hora_turno','termino','fecha_termino','hora_termino', 'estatus', 'ruta_oficio', 'conclusion' ,'created_at','updated_at'];   

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuario_responsable', 'id');
    }
}