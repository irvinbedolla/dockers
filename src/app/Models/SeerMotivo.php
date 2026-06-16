<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerMotivo extends Model
{
    //use HasFactory;
    protected $table = 'seer_motivos';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','id_motivo'];

}
