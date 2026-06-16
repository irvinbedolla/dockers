<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deducciones extends Model
{
    //use HasFactory;
    protected $table = 'deducciones';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','descripcion','monto','tipo_pago'];
    
}
