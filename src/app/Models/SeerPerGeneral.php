<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class SeerPerGeneral extends Model
{
    //use HasFactory;
    protected $table = 'seer_general';
    protected $primaryKey = 'id';
    protected $fillable = ['fecha','hora','fecha_conflicto','fecha_confirmacion','NUE','actividad','id_rama','solicitante', 'estado_solicitante', 'mun_solicitante', 
    'user_id','delegacion','conciliador_id', 'curp','tipo','tipo_solicitud','validado_conciliador','estatus','observaciones','fecha_terminacion','documentoExpediente',
    'documentoCitatoriosT','pendiente_firma','caso_excepcion','tipo_generacion','consecutivo','año', 'incidencia', 'motivo_incidencia','delegado_id', 'poder_id', 'confirmacion_id', 'numero_guia'];

    // Genera un número de guía único de 10 dígitos: AAAA + 6 dígitos aleatorios.
    public static function generarNumeroGuia(): string
    {
        $anio = now()->format('Y');

        do {
            $guia = $anio . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('numero_guia', $guia)->exists());

        return $guia;
    }

    public static function crearConGuia(array $data, int $intentos = 5): self
    {
        for ($i = 1; ; $i++) {
            $data['numero_guia'] = static::generarNumeroGuia();

            try {
                return static::create($data);
            } catch (UniqueConstraintViolationException $e) {
                if ($i >= $intentos || !str_contains($e->getMessage(), 'numero_guia')) {
                    throw $e;
                }
            }
        }
    }

    public function audiencias() {
        return $this->hasMany(Audiencias::class, 'id_solicitud', 'id');
    }

    public function solicitante() {
        return $this->hasOne(SeerSolicitante::class, 'id_solicitud');
    }
    public function citados() {
        return $this->hasMany(SeerCitados::class, 'id_solicitud', 'id');
    }

    public function motivos() {
        return $this->hasManyThrough(CatalogoMotivo::class, SeerMotivo::class, 'id_solicitud', 'id', 'id', 'id_motivo');
    }
}

