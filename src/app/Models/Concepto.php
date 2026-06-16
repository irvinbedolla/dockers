<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    //use HasFactory;
    protected $table = 'concepto_pago';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_solicitud',
        'monto',
        'descripcion',
        'tipo_pago',
    ];
}
