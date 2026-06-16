<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerColectivas extends Model
{
    //use HasFactory;
    protected $table = 'seer_colectivas';
    protected $primaryKey = 'id';
    protected $fillable = ['conciliador','fecha','NUE', 'solicitante', 'citado', 'juzgado', 'estado', 'delegacion'];

}
