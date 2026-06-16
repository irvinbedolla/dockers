<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacitacionEncuesta extends Model
{
    //use HasFactory;
    protected $table = 'capacitaciones_encuesta';
    protected $primaryKey = 'id';
    protected $fillable = ['id_cap','id_modulo','pregunta','respuesta1','respuesta2','respuesta3','respuesta4','correcta'];

    
}
