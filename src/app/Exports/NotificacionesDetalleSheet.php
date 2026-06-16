<?php 

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class NotificacionesDetalleSheet implements FromView, WithTitle
{
    protected $notificaciones;

    public function __construct($notificaciones) { $this->notificaciones = $notificaciones; }

    public function title(): string { return 'Listado Detallado'; }

    public function view(): View {
        return view('excel.hoja_detalle', ['notificaciones' => $this->notificaciones]);
    }
}