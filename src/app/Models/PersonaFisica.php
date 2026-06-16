<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaFisica extends Model
{
    //use HasFactory;
    protected $table = 'persona_fisica';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','id_citado','nombre','primer_apellido','segundo_apellido','identificacion', 'documentoIdentificacion',];

    
}