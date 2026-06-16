<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerCitados_old extends Model
{
    //use HasFactory;
    protected $table = 'seer_citados_old';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','tipo_persona','curp','rfc','nombre','primer_apellido','segundo_apellido','fecha_nacimiento','edad','sexo','nacionalidad','estado_solicitante','traductor','lenguaje',
    'colonia','cp','calle1','calle2','n_ext','n_int','estatus','calle','tipo_vialidad','referencia','direccion']; 
}