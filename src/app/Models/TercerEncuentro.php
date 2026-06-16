<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TercerEncuentro extends Model
{ 
    //use HasFactory;
    protected $table = 'tercer_encuentro';
    protected $primaryKey = 'id';
    protected $fillable = ['primer_apellido','segundo_apellido','nombre','correo','telefono','lugar','sexo','estatus','convesatorio1','convesatorio2','convesatorio3','convesatorio4','convesatorio5','convesatorio6','convesatorio7'
    ,'convesatorio8','convesatorio9','convesatorio10'];

}

