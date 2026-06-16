<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class AudienciasTotalesSheet implements FromView, WithTitle
{
    protected $totales;

    public function __construct($totales) { $this->totales = $totales; }

    public function title(): string { return 'Resumen Totales'; }

    public function view(): View {
        return view('excel.hoja_totales_audiencias', ['totales' => $this->totales]);
    }
}