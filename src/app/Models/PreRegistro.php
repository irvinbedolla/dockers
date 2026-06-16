<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistro extends Model
{
    protected $table = 'pre_registro';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre','rfc','telefono'];
}
