<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    //use HasFactory;
    protected $table = 'segundo_encuentro';
    protected $primaryKey = 'id';
    protected $fillable = ['correo','nombre','estado','celular','genero','estatus'];

}
