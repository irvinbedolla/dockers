<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Estados;
use App\Models\Municipios;

class HistorialAbogado extends Model
{
    use HasFactory;

    protected $table = 'historial_abogados';

    protected $fillable = [
        'nombres_patronal',
        'primer_apellido_patronal',
        'segundo_apellido_patronal',
        'telefono_patronal',
        'email_patronal',
        'curp_patronal',
        'rfc_patronal',
        'sexo_patronal',
        'giroComercial',
        'estado_patronal',
        'municipio_patronal',
        'tipo_vialidad_patronal',
        'vialidad_patronal',
        'num_ext_patronal',
        'mun_int_patronal',
        'colonia_patronal',
        'cp_patronal',
        'nombre_representante',
        'primer_apellido_representante',
        'segundo_apellido_representante',
        'curp_representante',
        'sexo_representante',
        'correo_representante',
        'numero_representante',
        'tipo_documento_representante',
        'descipcion_poder',
        'ineDocumento',
        'cedulaDocumento',
        'anexo_documeto',
        'representacionDocumento',
        'fechaRegistro',
        'fechaVigencia',
        'estatus',
        'tipo',
        'tipo_identificacion',
        'num_identificacion',
        'reprecentante',
        'idUsuario',
        'id_abogado',
        'id_user',
        // App\Support\HistorialPoderes: qué fue (alta, edicion, validacion,
        // representante, baja), de qué folio salió y cuál es la foto anterior.
        'accion',
        'origen_abogado',
        'previo_id',
    ];

    /**
     * Cada foto guarda cuál es la anterior del mismo folio y qué fue: alta,
     * edición o validación (Pendiente → Validado). 'representante' y 'baja'
     * los pone quien la crea (PoderController::guardarHistorial). Así lo leen
     * el modal Historial e Incidencias sin comparar todo cada vez.
     */
    protected static function booted(): void
    {
        static::creating(function (HistorialAbogado $foto) {
            if (! \App\Support\HistorialPoderes::conAccion() || ! $foto->id_abogado) {
                return;
            }
            $previa = static::query()->where('id_abogado', $foto->id_abogado)->orderByDesc('id')->first(['id', 'estatus']);
            if ($foto->accion !== 'representante') {
                $foto->previo_id ??= $previa?->id;
            }
            $foto->accion ??= ! $previa
                ? 'alta'
                : ($previa->estatus === 'Pendiente' && $foto->estatus === 'Validado' ? 'validacion' : 'edicion');
        });
    }

    public function abogado()
    {
        return $this->belongsTo(Poder::class, 'id_abogado');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function estadoPatronal()
    {
        return $this->belongsTo(Estados::class, 'estado_patronal');
    }

    public function municipioPatronal()
    {
        return $this->belongsTo(Municipios::class, 'municipio_patronal');
    }

}

