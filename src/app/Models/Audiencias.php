<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audiencias extends Model
{ 
    use HasFactory;

    protected $table = 'audiencias';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','numero_audiencia','folio_audiencia', 'estatus', 'tipo', 'fecha','hora','id_conciliador','delegacion','sala','proxima_audiencia','pena_convencional','direccion_convenio', 'incidencia', 'poder_id'];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i'
    ];

    public const ESTADOS = ['Pendiente','Conciliacion','No conciliacion','Reagendada','Archivada','No conciliacion reagendada','Incompetencia','Incomparecencia','Reinstalacion', 'Desistimiento'];
    public const TIPOS = ['Pago Parcial', 'Pago Total'];

    // En el archivo app/Models/Audiencias.php
    public function solicitante() {
        return $this->hasOne(SeerSolicitante::class, 'id_solicitud', 'id_solicitud');
    }

    public function expediente() {
        return $this->hasOne(SeerPerGeneral::class, 'id', 'id_solicitud'); 
    }

    public function conciliador() {
        return $this->belongsTo(User::class, 'id_conciliador');
    }

    public function pagos() {
        return $this->hasMany(Pagos::class, 'id_solicitud', 'id_solicitud');
    }

    public function poder() {
        return $this->belongsTo(Poder::class, 'poder_id', 'idAbogado');
    }
}
