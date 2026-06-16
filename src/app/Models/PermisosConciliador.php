<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisosConciliador extends Model
{
    //use HasFactory;
    protected $table = 'permisos_conciliador';
    protected $primaryKey = 'id';
    protected $fillable = ['id_conciliador','tipo','lunes','martes','miercoles','jueves','viernes','lunes_inicio','lunes_final','martes_inicio','martes_final','miercoles_inicio','miercoles_final'
    ,'jueves_inicio','jueves_final','viernes_inicio','viernes_final'];

    
}
