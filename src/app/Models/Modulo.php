<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    //use HasFactory;
    protected $table = 'capacitaciones_modulo';
    protected $primaryKey = 'id';
    protected $fillable = ['id_cap','id_modulo', 'nombre', 'introduccion', 'desarrollo', 'anexo1', 'anexo2', 'anexo3', 'anexo4', 'anexo5'];

}
