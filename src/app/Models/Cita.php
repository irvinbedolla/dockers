<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'motivo',
        'fecha',
        'hora',
        'usuario',
        'estatus',
        'tipo'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i'
    ];

    public const ESTADOS = ['pendiente', 'confirmada', 'cancelada'];
    public const TIPOS = ['solicitud', 'ratificacion'];

    /*public function user()
    {
        return $this->belongsTo(User::class);
    }*/
}
