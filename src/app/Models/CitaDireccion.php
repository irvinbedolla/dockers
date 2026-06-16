<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitaDireccion extends Model
{
    //use HasFactory;
    protected $table = 'citas_direccion';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre','descripcion','fecha','hora','estatus','delegacion','unidad','fin'];
    
}
