<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForoNacional extends Model
{ 
    //use HasFactory;
    protected $table = 'foro_nacional';
    protected $primaryKey = 'id';
    protected $fillable = ['primer_apellido','segundo_apellido','nombre','correo','telefono','lugar','sexo','estatus'];

}

