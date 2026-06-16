<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacitacion extends Model
{
    //use HasFactory;
    protected $table = 'capacitaciones';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre','modulos','inicio','fin','estatus'];

    
}
