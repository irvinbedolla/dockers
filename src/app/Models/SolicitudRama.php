<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudRama extends Model
{
    //use HasFactory;
    protected $table = 'catalogo_rama';
    protected $primaryKey = 'id';
    protected $fillable = ['rama_industrial'];

}