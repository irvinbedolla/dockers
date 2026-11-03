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

    public const ESTADOS = ['Pendiente','Conciliacion','No conciliacion','Reagendada','Archivada','No conciliacion reagendada','Incompetencia','Incomparecencia','Reinstalacion', 'Desistimiento', 'Canalizada'];
    public const TIPOS = ['Pago Parcial', 'Pago Total'];

    /**
     * Al resolverse la audiencia (con cualquier resolución: convenio, no
     * conciliación, archivo, incompetencia…) se manda por correo el folio y
     * la liga del formulario de atención del trámite
     * (App\Support\AtencionTramite). Reagendarla no cuenta. Se manda después
     * de responder, para no hacer esperar, y nunca tumba el guardado.
     */
    protected static function booted(): void
    {
        static::saved(function (Audiencias $audiencia) {
            $cambio = $audiencia->wasRecentlyCreated || $audiencia->wasChanged('estatus');
            if (! $cambio || ! in_array($audiencia->estatus, \App\Support\AtencionTramite::RESOLUCIONES, true)) {
                return;
            }
            $id = (int) $audiencia->id_solicitud;
            dispatch(function () use ($id) {
                try {
                    \App\Support\AtencionTramite::enviar($id);
                } catch (\Throwable $e) {
                    report($e);
                }
            })->afterResponse();
        });
    }

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

    public function conceptoPtu() {
        return $this->hasOne(Concepto::class, 'id_solicitud', 'id_solicitud')
            ->where('tipo_pago', 'Audiencia')
            ->where('descripcion', 'PTU');
    }

    public function poder() {
        return $this->belongsTo(Poder::class, 'poder_id', 'idAbogado');
    }
    public function citado() {
        return $this->hasOne(SeerCitados::class, 'id_solicitud', 'id_solicitud')
                    ->oldest('id');
    }
}
