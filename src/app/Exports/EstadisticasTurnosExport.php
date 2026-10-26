<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Estadísticas de turnos y citas (App\Support\EstadisticasTurnos::armar) en
 * Excel: Resumen, el periodo por día/semana/mes, por sede, casos de
 * excepción, por trámite y las atenciones más largas. Todas las hojas salen
 * de la misma vista (excel.estadisticas_turnos) con $hoja.
 */
class EstadisticasTurnosExport implements WithMultipleSheets
{
    public const HOJAS = [
        'resumen'   => 'Resumen',
        'periodo'   => null, // "Por día", "Por semana" o "Por mes"
        'sedes'     => 'Por sede',
        'excepcion' => 'Casos de excepción',
        'tramites'  => 'Por trámite',
        'largas'    => 'Atención larga',
    ];

    public function __construct(private array $datos)
    {
    }

    public function sheets(): array
    {
        $hojas = [];
        foreach (self::HOJAS as $hoja => $titulo) {
            $hojas[] = new class($this->datos, $hoja, $titulo ?? $this->datos['agruparTexto']) implements FromView, WithTitle, WithEvents {
                public function __construct(private array $datos, private string $hoja, private string $titulo)
                {
                }

                public function view(): View
                {
                    return view('excel.estadisticas_turnos', $this->datos + ['hoja' => $this->hoja]);
                }

                public function title(): string
                {
                    return mb_substr($this->titulo, 0, 31);
                }

                public function registerEvents(): array
                {
                    return [
                        AfterSheet::class => function (AfterSheet $e) {
                            $s = $e->sheet->getDelegate();
                            $s->setShowGridlines(false);
                            $ultima = $s->getHighestRow();
                            $col = $s->getHighestColumn();
                            $s->getStyle("A6:{$col}{$ultima}")->getAlignment()->setWrapText(true)->setVertical('center');
                            if ($this->hoja !== 'resumen') {
                                $s->freezePane('B7');
                            }
                            $s->getPageSetup()
                                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                                ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
                                ->setFitToWidth(1)->setFitToHeight(0);
                        },
                    ];
                }
            };
        }

        return $hojas;
    }
}
