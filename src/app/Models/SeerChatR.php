<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerChatR extends Model
{
    protected $table = 'chat_registro';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre_completo','ciudad']; 

//define la relación de muchos a muchos con la tabla preguntas
    public function preguntas()
    {
        return $this->belongsToMany(SeerChatP::class, 'chat_rp', 'id_registro', 'id_pregunta');
    }

}