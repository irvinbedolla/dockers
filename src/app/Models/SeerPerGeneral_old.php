<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerPerGeneral_old extends Model
{
    //use HasFactory;
    protected $table = 'seer_general_old';
    protected $primaryKey = 'id';
    protected $fillable = ['fecha', 'fecha_conflicto','fecha_confirmacion','NUE', 'id_motivo','actividad','id_rama','solicitante', 'estado_solicitante', 'mun_solicitante', 'user_id','delegacion','conciliador_id',
    'curp','tipo','documentoINEFrente','documentoINEAtras','documentoCurp','documentoActa','validado_conciliador','citado','estado_citado','mun_citado'];

    public function solicitante() {
        return $this->hasOne(SeerSolicitante::class, 'id_solicitud');
    }
}


