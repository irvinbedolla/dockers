<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turnos extends Model {
    //use HasFactory;
    protected $table = 'turnos';
    protected $primaryKey = 'id';
    protected $fillable = ['consecutivo','fecha','hora','hora_fin','auxiliar','tipo','lugar_auxiliar','exepcion',
    'edad','sexo','vulnerables','monto','empresa','trabajador','frecuencia','dias','estatus','delegacion','ine','representacion','email','telefono','turnos','JLCA','motivo',
    'trabajador_curp','documentoCurp','tipo_identificacion','documentoidentificacion','fecha_inicio','fecha_termino','categoria','tipo_pago',
    'Aguinaldo','Vacaciones','PrimaVacacional','PagoPTU','Gratificación','PrimaAntigüedad','Otras','Especifique','documentoCuanti','tipo_otros',
    'observaciones','curp_solicitante','salario','primero_empresa','segundo_empresa','nombre_empresa','primero_trabajador','segundo_trabajador',
    'vacaciones_dias','aguinaldo_dias','otros_dias','horario','comida',/*'domicilio',*/'resolucion_primera','resolucion_trabajadores','resolucion_justificacion','resolucion_segunda',
    'NUE','observaciones','id_conciliador','municipio_rat','tipo_vialidad','calle','colonia','num_ext','num_int','codigo_postal','idAbogado','user_id','num_identificacion','estado_rat','año', 'id_historial','nacionalidad', 'incidencia', 'motivo_incidencia', 'conclucion_id', 'fecha_conclucion', 'year_ptu'];

    public function pagos()
    {
        return $this->hasMany(Pagos::class, 'id_solicitud', 'id');
    }
}
