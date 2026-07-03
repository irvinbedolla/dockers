<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class NotificacionesTotalesSheet implements FromView, WithTitle
{
    protected $totales;
    protected $totalTrabajador;

    public function __construct($totales, $totalTrabajador = 0) {
        $this->totales = $totales;
        $this->totalTrabajador = $totalTrabajador;
    }

    public function title(): string { return 'Resumen Totales'; }

    public function view(): View {
        return view('excel.hoja_totales', [
            'totales' => $this->totales,
            'totalTrabajador' => $this->totalTrabajador,
        ]);
    }
}