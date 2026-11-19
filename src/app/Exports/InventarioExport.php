<?php

namespace App\Exports;

use App\Models\InventarioDispositivo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Excel de Administración → Inventario: todos los dispositivos. */
class InventarioExport extends DefaultValueBinder implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private Collection $dispositivos)
    {
    }

    public function title(): string
    {
        return 'Inventario';
    }

    public function headings(): array
    {
        return ['#', 'Descripción', 'Marca', 'Modelo', 'Número de serie', 'Estado de uso', 'Razón del mal estado', 'Ubicación física',
            'Resguardante', 'Puesto del resguardante', 'Características específicas', 'Observaciones adicionales',
            'Verificado', 'Verificado por', 'Fecha de verificación', 'Para revisión', 'Nota de revisión', 'Última modificación'];
    }

    public function array(): array
    {
        return $this->dispositivos->map(fn (InventarioDispositivo $d) => [
            $d->id,
            $d->descripcion,
            $d->marca,
            $d->modelo,
            $d->numero_serie ?? '',
            InventarioDispositivo::ESTADOS[$d->estado_uso] ?? $d->estado_uso,
            $d->razon_malo ?? '',
            $d->ubicacion,
            $d->resguardante_nombre ?? '',
            $d->resguardante_puesto ?? '',
            $d->caracteristicas ?? '',
            $d->observaciones ?? '',
            $d->verificado ? 'Sí' : 'No',
            $d->verificado ? ($d->verificador->name ?? '') : '',
            $d->verificado_at?->format('d/m/Y H:i') ?? '',
            $d->revisar ? 'Sí' : 'No',
            $d->revisar_nota ?? '',
            $d->updated_at?->format('d/m/Y H:i') ?? '',
        ])->all();
    }

    /** Números de serie siempre como texto: si no, Excel les quita ceros o los vuelve notación científica. */
    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'E' && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '496163']]]];
    }
}
