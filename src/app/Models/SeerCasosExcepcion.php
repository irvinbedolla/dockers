<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerCasosExcepcion extends Model
{
    //use HasFactory;
    protected $table = 'seer_casos_excepcion';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','frecuencia_hechos','cambios_situacionL','comunico_hechos','descripcion_conducta','responsable_cargo','actos_cometidos','momento_hechos', 'lugar_hechos',
    'constancia_hechos','solicito_apoyo','continuacion_solicto_apoyo', 'incidencia_directa','recibio_atencion']; 
}
