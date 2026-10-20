<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Una hoja por sede: título, filtros y un renglón por turno. El encabezado
 * (fila 3) queda fijo y con filtro para ordenar por módulo, estatus, etc.
 */
class AgendaTurnosSedeSheet implements FromView, WithTitle, WithEvents
{
    public const COLUMNAS = 18;

    public function __construct(private array $datos, private string $sede, private array $porDia)
    {
    }

    public function view(): View
    {
        return view('excel.agenda_turnos_sede', $this->datos + ['sede' => $this->sede, 'dias' => $this->porDia]);
    }

    public function title(): string
    {
        return Str::limit(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $this->sede), 31, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $hoja   = $e->sheet->getDelegate();
                $ultima = $hoja->getHighestRow();
                $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(self::COLUMNAS);

                $hoja->freezePane('C4');
                if ($ultima > 3 && collect($this->porDia)->flatten(1)->isNotEmpty()) {
                    $hoja->setAutoFilter("A3:{$col}{$ultima}");
                }
                $hoja->getStyle("A3:{$col}{$ultima}")->getAlignment()->setVertical('center')->setWrapText(true);
                $hoja->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
                    ->setFitToWidth(1)->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd(3, 3);
            },
        ];
    }
}
