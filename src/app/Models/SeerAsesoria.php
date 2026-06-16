<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerAsesoria extends Model
{
    //use HasFactory;
    protected $table = 'seer_asesorias';
    protected $primaryKey = 'id';
    protected $fillable = ['id_usuario','nombre', 'fecha', 'sexo','delegacion'];
}
