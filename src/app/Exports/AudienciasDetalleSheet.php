<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class AudienciasDetalleSheet implements FromView, WithTitle
{
    protected $audiencias;

    public function __construct($audiencias) { $this->audiencias = $audiencias; }

    public function title(): string { return 'Listado Detallado'; }

    public function view(): View {
        return view('excel.hoja_detalle_audiencias', ['audiencias' => $this->audiencias]);
    }
}