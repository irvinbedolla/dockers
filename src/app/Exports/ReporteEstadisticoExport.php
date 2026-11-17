<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Reporte estadístico (App\Support\ReporteEstadistico::armar) en Excel: una
 * hoja por tabla de la presentación, con las cifras como números para que se
 * puedan sumar o graficar.
 */
class ReporteEstadisticoExport implements WithMultipleSheets
{
    public function __construct(private array $d)
    {
    }

    public function sheets(): array
    {
        $d = $this->d;
        $p = $d['periodo'];
        $x = $d['externos'];
        $pct = fn ($v) => $v === null ? null : round($v / 100, 4);

        $hojas = [];

        // Periodo: por delegación, sede y oficina.
        $filaDeleg = fn ($nombre, $f) => [$nombre, $f['personas'], $f['asesorias'], $f['solicitudes'], $f['audiencias'], $f['convenios'], $f['ratificaciones'], $f['conv_rat'], $f['constancias'], $f['archivadas'], $f['incompetencias'], $pct($f['efectividad']), round($f['montos'], 2)];
        $cab = ['Personas atendidas', 'Asesorías', 'Solicitudes confirmadas', 'Audiencias programadas', 'Convenios', 'Ratificaciones', 'Convenios + ratificaciones', 'Constancias de no conciliación', 'Archivadas por falta de interés', 'Incompetencias', 'Efectividad', 'Montos convenidos'];
        foreach ([
            ['Por delegación', 'Estadística '.$p.' (cada delegación con su oficina de apoyo)', 'Delegación', $d['delegaciones'], $d['delegacionesTotal']],
            ['Por sede', 'Desglosado por sede ('.$p.')', 'Sede', $d['sedes'], $d['sedesTotal']],
            ['Por oficina de apoyo', 'Desglosado por oficinas de apoyo ('.$p.')', 'Oficina', $d['oficinas'], $d['oficinasTotal']],
        ] as [$titulo, $encabezado, $col, $filas, $total]) {
            $renglones = [];
            foreach ($filas as $nombre => $f) {
                $renglones[] = $filaDeleg($nombre, $f);
            }
            $renglones[] = $filaDeleg('Total', $total);
            $hojas[] = $this->hoja($titulo, $encabezado, array_merge([$col], $cab), $renglones, ['L' => '0.0%', 'M' => '"$"#,##0.00']);
        }

        // Histórica.
        $h = $d['historica'];
        $hojas[] = $this->hoja('Histórica', 'Histórica ('.$d['historicoTexto'].')',
            ['Personas atendidas', 'Asesorías', 'Solicitudes confirmadas', 'Audiencias programadas', 'Constancias de no conciliación', 'Convenios (con ratificaciones)', 'Archivadas por falta de interés', 'Efectividad', 'Montos convenidos'],
            [[$h['personas'], $h['asesorias'], $h['solicitudes'], $h['audiencias'], $h['constancias'], $h['convenios'], $h['archivadas'], $pct($h['efectividad']), round($h['montos'], 2)]],
            ['A' => '#,##0', 'H' => '0.00%', 'I' => '"$"#,##0.00']);

        $renglones = [];
        foreach ($d['historicoDelegaciones'] + ['Total' => $d['historicoDelegacionesTotal']] as $nombre => $f) {
            $renglones[] = [$nombre, $f['solicitudes'], $f['incompetencias'], $f['convenios'], $f['ratificaciones'], $f['constancias'], $f['archivadas'], $pct($f['efectividad'])];
        }
        $hojas[] = $this->hoja('Histórico por delegación', 'Histórico desglosado por delegación ('.$d['historicoTexto'].')',
            ['Delegación', 'Solicitudes confirmadas', 'Incompetencias', 'Convenios', 'Ratificaciones', 'Constancias de no conciliación', 'Archivadas por falta de interés', 'Efectividad'],
            $renglones, ['H' => '0.00%']);

        // Tiempo de resolución.
        $t = $d['tiempos'];
        $renglones = [];
        foreach (array_keys($t['ratificaciones']) as $rango) {
            $renglones[] = [$rango, $t['ratificaciones'][$rango], $pct($t['ratificacionesPct'][$rango]), $t['derivados'][$rango], $pct($t['derivadosPct'][$rango])];
        }
        $hojas[] = $this->hoja('Tiempo de resolución', 'Tiempo de resolución ('.$p.')',
            ['Días', 'Ratificaciones', '%', 'Derivados de conciliación', '%'], $renglones, ['C' => '0.0%', 'E' => '0.0%']);

        // Audiencias por conciliador.
        $renglones = [['Total ('.$d['historicoTexto'].')', $d['audienciasHistoricas'], $d['audienciasPromedio']]];
        foreach ($d['audienciasAnio'] as $a) {
            $renglones[] = [$a['etiqueta'], $a['total'], $a['promedio']];
        }
        $hojas[] = $this->hoja('Audiencias por conciliador', 'Audiencias por conciliador · '.$d['conciliadores'].' conciliadores',
            ['Periodo', 'Audiencias', 'Promedio por conciliador'], $renglones);

        // Datos de referencia.
        $hojas[] = $this->hoja('Conciliadores por estado', 'Conciliadores y PEA por entidad (ordenado por PEA por conciliador)',
            ['No.', 'Entidad', 'Tercera etapa', 'Población', 'Conciliadores al inicio', 'Conciliadores actuales', 'PEA', 'PEA por conciliador'],
            $x['estados']->values()->map(fn ($e, $i) => [$i + 1, $e['estado'], $e['tercera'] ? 'Sí' : 'No', $e['poblacion'], $e['inicio'], $e['actuales'], $e['pea'], $e['pea_conciliador']])->all());
        $hojas[] = $this->hoja('JLCA vs CCL', 'Asuntos por año: Junta Local (demandas) y Centro (solicitudes)',
            ['Año', 'JLCA', 'CCL'], $x['jlca']->map(fn ($r) => [$r['anio'], $r['jlca'], $r['ccl']])->all());
        $hojas[] = $this->hoja('Presupuesto', 'Presupuesto por ejercicio fiscal',
            ['Año', 'JLCA', 'CCL'], $x['presupuesto']->map(fn ($r) => [$r['anio'], $r['jlca'], $r['ccl']])->all(), ['B' => '"$"#,##0.00', 'C' => '"$"#,##0.00']);
        $hojas[] = $this->hoja('Población y empresas', 'Población y empresas por delegación y oficina',
            ['Sede', 'Población', 'Empresas'], $x['poblacion']->values()->map(fn ($r) => [$r['sede'], $r['poblacion'], $r['empresas']])->all());

        return $hojas;
    }

    /** Una hoja: título, encabezados en verde y formatos por columna. */
    private function hoja(string $titulo, string $encabezado, array $columnas, array $renglones, array $formatos = []): object
    {
        return new class($titulo, $encabezado, $columnas, $renglones, $formatos) implements FromArray, WithTitle, WithEvents {
            public function __construct(private string $titulo, private string $encabezado, private array $columnas, private array $renglones, private array $formatos)
            {
            }

            public function array(): array
            {
                return array_merge([[$this->encabezado], $this->columnas], $this->renglones);
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
                        $ultCol = $s->getHighestColumn();
                        $ultFila = $s->getHighestRow();
                        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('4A6163');
                        $s->getStyle("A2:{$ultCol}2")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4A6163']],
                            'alignment' => ['wrapText' => true, 'vertical' => 'center', 'horizontal' => 'center'],
                        ]);
                        $s->getRowDimension(2)->setRowHeight(34);
                        if ($ultFila > 2) {
                            $s->getStyle("A2:{$ultCol}{$ultFila}")->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setRGB('D9D9D9');
                            $s->getStyle("B3:{$ultCol}{$ultFila}")->getNumberFormat()->setFormatCode('#,##0');
                            foreach ($this->formatos as $col => $formato) {
                                $s->getStyle("{$col}3:{$col}{$ultFila}")->getNumberFormat()->setFormatCode($formato);
                            }
                            if ($s->getCell("A{$ultFila}")->getValue() === 'Total') {
                                $s->getStyle("A{$ultFila}:{$ultCol}{$ultFila}")->getFont()->setBold(true);
                            }
                        }
                        foreach (range('A', $ultCol) as $c) {
                            $s->getColumnDimension($c)->setWidth($c === 'A' ? 26 : 16);
                        }
                        $s->freezePane('A3');
                    },
                ];
            }
        };
    }
}
