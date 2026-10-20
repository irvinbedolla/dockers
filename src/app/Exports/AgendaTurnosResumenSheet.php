<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AgendaTurnosResumenSheet implements FromView, WithTitle, WithEvents
{
    public function __construct(private array $datos)
    {
    }

    public function view(): View
    {
        return view('excel.agenda_turnos_resumen', $this->datos);
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $hoja = $e->sheet->getDelegate();
                $hoja->setShowGridlines(false);
                // Encabezados largos ("Casos de excepción") en dos renglones.
                $hoja->getStyle('A6:I'.$hoja->getHighestRow())->getAlignment()->setWrapText(true)->setVertical('center');
                $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setFitToWidth(1)->setFitToHeight(0);
            },
        ];
    }
}
