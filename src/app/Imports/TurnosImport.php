<?php

namespace App\Imports;

use App\Models\Turnos;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TurnosImport implements ToModel, WithHeadingRow{

    public function model(array $row)
    {
        return new Turnos([
            'consecutivo'       => $row['consecutivo'],
            'año'               => $row['ano'],
            'fecha'             => $row['fecha'],
            'hora'              => $row['hora'], 
            'hora_fin'          => $row['hora_fin'], 
            'auxiliar'          => $row['auxiliar'],
            'tipo'              => 'Ratificación',
            'lugar_auxiliar'    => $row['lugar_auxiliar'],
            'exepcion'          => $row["exepcion"],
            'edad'              => $row["edad"],
            'sexo'              => $row["sexo"],
            'salario'           => $row["salario"],
            'monto'             => $row["monto"],
            'empresa'           => $row["empresa"],
            'primero_empresa'   => $row["primero_empresa"],
            'segundo_empresa'   => $row["segundo_empresa"],
            'nombre_empresa'    => $row["nombre_empresa"],
            'trabajador'        => $row["trabajador"],
            'primero_trabajador'=> $row["primero_trabajador"],
            'segundo_trabajador'=> $row["segundo_trabajador"],
            'frecuencia'        => "Diario",    
            'dias'              => $row["dias"],
            'estatus'           => "Concluida",
            'delegacion'        => "Morelia",
            'email'             => $row["email"],
            'telefono'          => $row["telefono"],
            'JLCA'              => "No",
            'motivo'            => "Terminacion de la relacion laboral",
            'tipo_identificacion'   => $row["tipo_identificacion"],
            'num_identificacion'    => $row["num_identificacion"],
            'PrimaVacacional'   => $row["primavacacional"],
            'fecha_inicio'      => $row["fecha_inicio"],
            'fecha_termino'     => $row["fecha_termino"],
            'categoria'         => $row["categoria"],
            'tipo_pago'     => $row["tipo_pago"],
            'Aguinaldo'     => $row["aguinaldo"],
            'Vacaciones'    => $row["vacaciones"],
            'Otras'         => 1,
            'Especifique'   => 'Especifique',    
            'resolucion_primera'            => $row["resolucion_primera"],
            'resolucion_justificacion'      => $row["resolucion_justificacion"],
            'resolucion_segunda'            => $row["resolucion_segunda"],
            'vacaciones_dias'   => $row["vacaciones_dias"],
            'aguinaldo_dias'    => $row["aguinaldo_dias"],
            'horario'       => $row["horario"],
            'comida'        => $row["comida"],
            'estado_rat'    => $row["estado_rat"],
            'municipio_rat' => $row["municipio_rat"],
            'NUE'           => $row["nue"],
            'id_conciliador'=> $row["id_conciliador"],
            'idAbogado'     => $row["idabogado"],
            'user_id'       => $row["user_id"],
            'nacionalidad'  => $row["nacionalidad"],
            'id_historial'  => $row["id_historial"],
            'ine'           => "",
            'representacion'=> "",
            'trabajador_curp'   => "",
            'curp_solicitante'  => "",
            'tipo_vialidad'     => "",
            'calle'     => "",
            'num_ext'     => "",
            'colonia'     => "",
            'codigo_postal' => "",
        ]);
    }
}
