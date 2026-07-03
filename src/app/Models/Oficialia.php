<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficialia extends Model
{
    //use HasFactory;
    protected $table = 'oficialia';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'oficio_id' ,'delegacion', 'tipo_tramite','oficio', 'area_turno','usuario_responsable','fecha','hora', 'estatus', 'ruta_oficio', 'conclusion' ,'created_at','updated_at'];   

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuario_responsable', 'id');
    }
}