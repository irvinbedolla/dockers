<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerPerAuxiliar extends Model
{
    //use HasFactory;
    protected $table = 'seer_auxiliares';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','sexo', 'tipo_persona', 'motivo', 'actividad_economica','monto','estatus','notificacion','tipo_solicitud'];

}
