<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerCasosExcepcion extends Model
{
    //use HasFactory;
    protected $table = 'seer_casos_excepcion';
    protected $primaryKey = 'id';
    protected $fillable = ['id_turno','id_user','tipo_caso','motivos','vulnerables','frecuencia','situacion_laboral','dependencia',
    'expediente','descripcion_persona','descripcion_conductas', 'observaciones','jefe_inmediato','ubicacion', 'empresa','puesto', 'area_adscripcion','fecha','hora','created_at','updated_at'];
}
