<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Una pestaña del libro: la agenda de un solo conciliador.
 *
 * Va por FromView y no por FromCollection porque el formato (encabezado
 * #869b9c en blanco, celdas centradas, anchos de columna) ya vive en la
 * plantilla, igual que en el resto de los reportes del sistema.
 */
class AgendaConciliadorSheet implements FromView, WithTitle
{
    public function __construct(
        private string $titulo,
        private Collection $filas,
    ) {
    }

    public function view(): View
    {
        return view('excel.agenda_conciliador', [
            // Agrupadas por día para poder intercalar la fila en blanco que
            // separa cada fecha, como en el archivo que se armaba a mano.
            'porDia' => $this->filas->groupBy('fecha'),
        ]);
    }

    public function title(): string
    {
        return $this->titulo;
    }
}
