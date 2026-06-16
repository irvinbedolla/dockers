<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerPerGeneral extends Model
{
    //use HasFactory;
    protected $table = 'seer_general';
    protected $primaryKey = 'id';
    protected $fillable = ['fecha','hora','fecha_conflicto','fecha_confirmacion','NUE','actividad','id_rama','solicitante', 'estado_solicitante', 'mun_solicitante', 
    'user_id','delegacion','conciliador_id', 'curp','tipo','tipo_solicitud','validado_conciliador','estatus','observaciones','fecha_terminacion','documentoExpediente',
    'documentoCitatoriosT','pendiente_firma','caso_excepcion','tipo_generacion','consecutivo','año', 'incidencia', 'motivo_incidencia','delegado_id', 'poder_id', 'confirmacion_id']; 

    public function solicitante() {
        return $this->hasOne(SeerSolicitante::class, 'id_solicitud');
    }

    public function motivos() {
        return $this->hasManyThrough(CatalogoMotivo::class, SeerMotivo::class, 'id_solicitud', 'id', 'id', 'id_motivo');
    }
}

