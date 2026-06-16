<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoDisponible extends Model
{
    //use HasFactory;
    protected $table = 'turno_disponible';
    protected $primaryKey = 'id';
    protected $fillable = ['id_auxiliar','fecha','hora','estatus'];
}
