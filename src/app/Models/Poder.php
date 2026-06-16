<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poder extends Model
{
    //use HasFactory;
    protected $table = 'abogados';
    protected $primaryKey = 'idAbogado';
    protected $fillable = ['nombres_patronal', 'primer_apellido_patronal','segundo_apellido_patronal','telefono_patronal','email_patronal', 
    'curp_patronal', 'rfc_patronal', 'sexo_patronal', 'giroComercial', 'estado_patronal', 'municipio_patronal','tipo_vialidad_patronal', 
    'vialidad_patronal', 'num_ext_patronal', 'mun_int_patronal', 'colonia_patronal', 'cp_patronal', 'vialidadPoder', 'nombre_representante', 
    'segundo_apellido_representante', 'curp_representante', 'sexo_representante', 'correo_representante', 'numero_representante', 'tipo_documento_representante', 
    'descipcion_poder','ineDocumento','cedulaDocumento','anexo_documeto','representacionDocumento','fechaRegistro','fechaVigencia','estatus','tipo','reprecentante',
    'primer_apellido_representante','idUsuario','idUsuario','tipo_identificacion','num_identificacion'];
}
