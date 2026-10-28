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
 * Formulario de atención del trámite (App\Support\EstadisticasAtencion::armar)
 * en Excel. Todas las hojas salen de excel.estadisticas_atencion con $hoja.
 */
class EstadisticasAtencionExport implements WithMultipleSheets
{
    public const HOJAS = [
        'resumen'       => 'Resumen',
        'sedes'         => 'Por sede',
        'conciliadores' => 'Por conciliador',
        'capturistas'   => 'Por quien capturó',
        'respuestas'    => 'Respuestas',
        'comentarios'   => 'Comentarios',
        'alertas'       => 'Alertas',
    ];

    public function __construct(private array $datos)
    {
    }

    public function sheets(): array
    {
        $hojas = [];
        foreach (self::HOJAS as $hoja => $titulo) {
            $hojas[] = new class($this->datos, $hoja, $titulo) implements FromView, WithTitle, WithEvents {
                public function __construct(private array $datos, private string $hoja, private string $titulo)
                {
                }

                public function view(): View
                {
                    return view('excel.estadisticas_atencion', $this->datos + ['hoja' => $this->hoja]);
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
                            $s->getStyle('A5:'.$s->getHighestColumn().$s->getHighestRow())->getAlignment()->setWrapText(true)->setVertical('top');
                            if ($this->hoja !== 'resumen') {
                                $s->freezePane('A6');
                            }
                            $s->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_LETTER)->setFitToWidth(1)->setFitToHeight(0);
                        },
                    ];
                }
            };
        }

        return $hojas;
    }
}
