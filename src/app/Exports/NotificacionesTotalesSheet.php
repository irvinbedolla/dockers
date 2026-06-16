<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class NotificacionesTotalesSheet implements FromView, WithTitle
{
    protected $totales;

    public function __construct($totales) { $this->totales = $totales; }

    public function title(): string { return 'Resumen Totales'; }

    public function view(): View {
        return view('excel.hoja_totales', ['totales' => $this->totales]);
    }
}