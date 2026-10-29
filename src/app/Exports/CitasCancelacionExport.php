<?php

namespace App\Exports;

use App\Support\CitasCancelacion;
use App\Support\FolioCita;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel de Administración → Cancelación/Reagenda de citas, con los filtros de
 * la pantalla (App\Support\CitasCancelacion). $vista: 'citas' (filas
 * ['cita' => Recepcion, 'historial' => ?array]) o 'movimientos' (filas de
 * CitasCancelacion::movimientos()).
 */
class CitasCancelacionExport extends DefaultValueBinder implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private string $vista, private Collection $filas)
    {
    }

    public function title(): string
    {
        return $this->vista === 'movimientos' ? 'Movimientos' : 'Citas';
    }

    public function headings(): array
    {
        return $this->vista === 'movimientos'
            ? ['Fecha del movimiento', 'Movimiento', 'Folio', 'Solicitante', 'Sede', 'Trámite', 'Origen', 'Cita antes', 'Cita nueva', 'Días que se movió', 'Otros cambios', 'Motivo', 'Hecho por', 'Estatus actual', 'Contacto']
            : ['Fecha', 'Hora', 'Folio', 'Solicitante', 'CURP', 'Sede', 'Trámite', 'Origen', 'Estatus', 'Correo', 'Teléfono', 'Veces reagendada', 'Cancelación', 'Último movimiento'];
    }

    public function array(): array
    {
        if ($this->vista === 'movimientos') {
            return $this->filas->map(fn ($m) => [
                $m['cuando']->format('d/m/Y H:i'),
                CitasCancelacion::MOVIMIENTOS[$m['movimiento']],
                $m['folio'],
                $m['solicitante'],
                $m['sede'],
                $m['tramite'],
                $m['origen'],
                $m['antes'],
                $m['despues'] ?? '',
                $m['dias'] ?? '',
                implode(' · ', array_map(fn ($e, $par) => $e.': '.$par[0].' → '.$par[1], array_keys($m['otros']), $m['otros'])),
                $m['motivo'],
                $m['quien'],
                CitasCancelacion::ESTATUS[$m['estatusHoy']] ?? (string) $m['estatusHoy'],
                $m['contacto'],
            ])->all();
        }

        return $this->filas->map(function ($fila) {
            $c = $fila['cita'];
            $h = $fila['historial'];

            return [
                $c->fecha?->format('d/m/Y'),
                substr((string) $c->getRawOriginal('hora'), 0, 5),
                FolioCita::mostrar($c),
                $c->solicitante,
                $c->curp,
                CitasCancelacion::sede($c->delegacion),
                CitasCancelacion::tramite($c->tipo),
                CitasCancelacion::ORIGENES[$c->origen] ?? (string) $c->origen,
                CitasCancelacion::ESTATUS[$c->estatus] ?? (string) $c->estatus,
                $c->correo,
                $c->telefono,
                $h['reagendas'] ?? 0,
                $h && $h['cancelada'] ? $h['cancelada']['cuando'].' · '.$h['cancelada']['quien'].' — '.$h['cancelada']['motivo'] : '',
                $h['ultimo'] ?? '',
            ];
        })->all();
    }

    /** Teléfonos y otros números largos van como texto (si no, Excel los vuelve número y les quita ceros). */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && preg_match('/^\d{6,}$/', $value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '496163']]]];
    }
}
