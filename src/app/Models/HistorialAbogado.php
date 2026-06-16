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
        'id_user'
    ];

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

