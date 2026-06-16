<?php

namespace App\Imports;

use App\Models\Pagos;
use Maatwebsite\Excel\Concerns\ToModel;

class PagoSolicitudImport implements ToModel{

    public function model(array $row)
    {
        return new Pagos([
            'id_solicitud' => $row[0],
            'monto'        => $row[1],
            'fecha'        => $row[2],
            'hora'         => $row[3],
            'descripcion'  => $row[4],
            'estatus'      => 'Pendiente',
            'tipo_pago'    => $row['tipo_pago'] ?? 'Ratificacion',
            'delegacion'   => $row['delegacion'] ?? 'Morelia',
        ]);
    }
}
