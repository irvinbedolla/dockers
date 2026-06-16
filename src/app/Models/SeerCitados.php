<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerCitados extends Model
{
    //use HasFactory;
    protected $table = 'seer_citados';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','tipo_persona','curp','rfc','nombre','primer_apellido','segundo_apellido','fecha_nacimiento','edad','sexo','nacionalidad',
    'estado_solicitante','traductor','lenguaje','tipo_notificacion','id_notificador','notificacion','colonia','cp','calle1','calle2','n_ext','n_int','estatus','calle',
    'tipo_vialidad','referencia','documento','observaciones','id_abogado','id_fisica','documento1','documento2','fecha','municipio_citado','quien_atiende','medio',
    'vialidad_notificacion','abundar_area','abundar_inmueble','nombre_notificacion','relacion_notificacion','puesto','identificacion_notificacion', 'motivo_identificacion',
    'firma','problema_diligencia','genero','tez','edad_filiacion','altura','complexion','cabello','ojos','particulares','especificar','imagen_domicilio1','imagen_domicilio2',
    'estado_citado','aparece_convenio', 'resulte_responsable','giro_comercial','updated_at', 'id_historial','num_identificacion', 'audiencia_id','comparecencia', 
    'num_identificacion_comparecencia', 'tipo_identificacion_comparecencia', 'identificacion_comparecencia'
    ];
    
    protected $casts = [
        'medio' => 'array',
    ];
                     
}