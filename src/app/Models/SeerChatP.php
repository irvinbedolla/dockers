<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerChatP extends Model
{
    //use HasFactory;
    protected $table = 'chat_preguntas';
    protected $primaryKey = 'id';
    protected $fillable = ['pregunta','respuesta'];

    //define la relación de muchos a muchos con la tabla registros
    public function registros()
    {
        return $this->belongsToMany(SeerChatR::class, 'chat_rp', 'id_registro', 'id_pregunta');
    }
   
}