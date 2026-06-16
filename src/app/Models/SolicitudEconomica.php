<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudEconomica extends Model
{
    //use HasFactory;
    protected $table = 'catalogo_actividad';
    protected $primaryKey = 'id';
    protected $fillable = ['act_economica','id_rama'];

}