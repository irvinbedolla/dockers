<?php

namespace App\Imports;

use App\Models\Concepto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError; // <-- Importar esto
use Throwable; // <-- Importar esto

class ConceptoPagoImport implements ToModel{

    public function model(array $row)
    {

        return new Concepto([
            'id_solicitud' => $row[0],
            'monto'        => $row[1],
            'descripcion'  => $row[2],
            'tipo_pago'    => 'Ratificacion',
        ]);
    }
}