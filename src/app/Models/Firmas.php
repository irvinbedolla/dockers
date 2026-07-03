<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firmas extends Model
{ 
    use HasFactory;

    protected $table = 'firmas';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','ruta_firma','tipo'];

}
