<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiasInhabiles extends Model
{
    //use HasFactory;
    protected $table = 'dias_inhabiles';
    protected $primaryKey = 'id';
    protected $fillable = ['fecha_inicio','fecha_final','horario_inicio','horario_final','centro','user_id','tipo','descripcion'];

}
