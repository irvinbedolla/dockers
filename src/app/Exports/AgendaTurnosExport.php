<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Agenda de turnos (App\Support\AgendaTurnos::armar): una hoja de resumen y
 * una por sede, con un renglón por turno.
 */
class AgendaTurnosExport implements WithMultipleSheets
{
    public function __construct(private array $datos)
    {
    }

    public function sheets(): array
    {
        $hojas = [new AgendaTurnosResumenSheet($this->datos)];
        foreach ($this->datos['porSede'] as $sede => $porDia) {
            $hojas[] = new AgendaTurnosSedeSheet($this->datos, $sede, $porDia);
        }

        return $hojas;
    }
}
