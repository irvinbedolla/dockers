<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerChatRP extends Model
{

    
    //use HasFactory;
    protected $table = 'chat_rp';
    protected $primaryKey = 'id';
    protected $fillable = ['id_registro','id_pregunta'];
    
    
}