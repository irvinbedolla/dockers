<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    //use HasFactory;
    protected $table = 'asistencia_centro';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','fecha','hora'];   
}