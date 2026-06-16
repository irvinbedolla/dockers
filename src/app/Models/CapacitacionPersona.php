<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacitacionPersona extends Model
{
    //use HasFactory;
    protected $table = 'capacitaciones_persona';
    protected $primaryKey = 'id';
    protected $fillable = ['capacitacion','modulo','persona','estatus','calificacion'];

    
}
