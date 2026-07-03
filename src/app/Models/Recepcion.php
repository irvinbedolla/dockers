<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    use HasFactory;
    protected $table = 'recepcion';
    protected $primaryKey = 'id';
    protected $fillable = ['consecutivo','fecha','hora','hora_fin','auxiliar','solicitante','tipo','lugar_auxiliar','estatus','delegacion','exepcion','edad','sexo','vulnerables','conflicto','tipo_caso','prestacionSS','orientacion','tarjeta','folio','resultado','INS', 'telefono', 'correo', 'estado','municipio', 'observaciones'];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i'
    ];

    public const ESTADOS = ['pendiente', 'atendida', 'no atendida'];
}
