<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Lee y revisa el "Formato para ratificaciones múltiples" (Excel) que sube la
 * empresa en línea. No confía en las fórmulas del archivo: con los datos que
 * capturó la empresa vuelve a calcular cada mínimo legal, igual que el
 * formato, y no deja seguir si algo queda por debajo.
 *
 * Hoja 1 "DATOS LABORALES Y CONCEPTOS"
 *   E6  empresa / solicitante          E9..X9 domicilio de la fuente de empleo
 *   E10 folio de representación patronal
 *   Renglones 13 en adelante (el 13 es el EJEMPLO y se salta):
 *     A No.  B nombre completo  C puesto  D salario diario  E ingreso  F terminación
 *     M días base de aguinaldo (mín. 15)  N indemnización  O vacaciones  Q prima
 *     vacacional  S aguinaldo  U salarios pendientes  V otras prestaciones (SI/NO)
 *     Y impuesto a retener  AA observaciones
 *   Lo demás (G a L, P, R, T, W, X, Z) son fórmulas; aquí se recalculan.
 * Hoja 2 "OTRAS PRESTACIONES": por No., hasta 7 pares tipo/monto (C-D ... O-P).
 *
 * leer() regresa:
 *   encabezado  empresa, domicilio y folio
 *   trabajadores [...] cada uno con sus datos, conceptos, 'minimos',
 *                'errores' (no deja seguir) y 'avisos'
 *   errores     del archivo completo
 */
class RatificacionMultiple
{
    /** Salario mínimo general 2026 (la validación del propio formato). */
    public const SALARIO_MINIMO = 315.04;

    /** Desde cuántos trabajadores es ratificación múltiple, y el tope del formato. */
    public const MINIMO_TRABAJADORES = 5;
    public const MAXIMO_TRABAJADORES = 100;

    /** Cada cita dura 15 min y el lote se reparte en a lo más 3 días hábiles. */
    public const MINUTOS_CITA = 15;
    public const MAXIMO_DIAS = 3;

    private const HOJA_DATOS = 'DATOS LABORALES Y CONCEPTOS';
    private const HOJA_OTRAS = 'OTRAS PRESTACIONES';
    private const PRIMER_RENGLON = 13;

    public static function leer(string $ruta): array
    {
        $resultado = ['encabezado' => [], 'trabajadores' => [], 'errores' => []];

        try {
            $lector = IOFactory::createReaderForFile($ruta);
            $lector->setReadDataOnly(true);
            $libro = $lector->load($ruta);
        } catch (\Throwable) {
            $resultado['errores'][] = 'No se pudo abrir el archivo. Sube el formato en Excel (.xlsx) tal como se descargó.';

            return $resultado;
        }

        $datos = $libro->getSheetByName(self::HOJA_DATOS) ?? $libro->getSheet(0);
        $otras = $libro->getSheetByName(self::HOJA_OTRAS);
        if (! str_contains(mb_strtoupper((string) $datos->getCell('A5')->getValue()), 'RATIFICACI')) {
            $resultado['errores'][] = 'El archivo no es el "Formato para ratificaciones múltiples". Descárgalo de nuevo y llénalo sin cambiar su acomodo.';

            return $resultado;
        }

        $texto = fn (string $celda) => self::texto($datos->getCell($celda)->getValue());
        $resultado['encabezado'] = [
            'empresa'   => $texto('E6'),
            'folio'     => $texto('E10'),
            'domicilio' => array_filter([
                'calle'     => $texto('E9'),
                'numero'    => $texto('I9'),
                'colonia'   => $texto('L9'),
                'cp'        => $texto('P9'),
                'municipio' => $texto('S9'),
                'estado'    => $texto('X9'),
            ]),
        ];
        if ($resultado['encabezado']['empresa'] === '') {
            $resultado['errores'][] = 'Falta el nombre de la empresa o solicitante (celda E6).';
        }
        if ($resultado['encabezado']['folio'] === '') {
            $resultado['errores'][] = 'Falta el folio de representación patronal (celda E10).';
        }

        $otrasPorNumero = $otras ? self::otrasPrestaciones($otras) : [];

        for ($r = self::PRIMER_RENGLON; $r < self::PRIMER_RENGLON + self::MAXIMO_TRABAJADORES + 1; $r++) {
            $no = $datos->getCell("A$r")->getValue();
            if (mb_strtoupper(self::texto($no)) === 'EJEMPLO') {
                continue;
            }
            // Renglón vacío: sólo cuenta si la empresa capturó algo propio
            // (las fórmulas y el número ya vienen en el formato).
            $capturado = array_filter(['B', 'C', 'D', 'E', 'F', 'N', 'O', 'Q', 'S', 'U', 'Y'], fn ($c) => self::texto($datos->getCell("$c$r")->getValue()) !== '');
            if (! $capturado) {
                continue;
            }
            $resultado['trabajadores'][] = self::trabajador($datos, $r, (int) $no ?: count($resultado['trabajadores']) + 1, $otrasPorNumero);
        }

        $n = count($resultado['trabajadores']);
        if ($n === 0) {
            $resultado['errores'][] = 'El archivo no trae trabajadores. Captúralos a partir del renglón 14 (el 13 es el ejemplo).';
        } elseif ($n < self::MINIMO_TRABAJADORES) {
            $resultado['errores'][] = 'La ratificación múltiple es para '.self::MINIMO_TRABAJADORES.' trabajadores o más; el archivo trae '.$n.'. Para menos, agenda una cita de ratificación por cada uno.';
        }

        return $resultado;
    }

    /** ¿Se puede agendar? (sin errores en el archivo ni en ningún trabajador) */
    public static function valido(array $leido): bool
    {
        return ! $leido['errores'] && ! array_filter($leido['trabajadores'], fn ($t) => $t['errores']);
    }

    // ------------------------------------------------------------------ renglón

    private static function trabajador(Worksheet $h, int $r, int $no, array $otrasPorNumero): array
    {
        $v = fn (string $c) => $h->getCell("$c$r")->getValue();
        $errores = [];
        $avisos = [];

        $nombre = mb_strtoupper(preg_replace('/\s+/u', ' ', self::texto($v('B'))));
        $puesto = mb_strtoupper(self::texto($v('C')));
        $salario = self::numero($v('D'));
        $ingreso = self::fecha($v('E'));
        $termino = self::fecha($v('F'));
        $baseAguinaldo = self::numero($v('M'));

        if ($nombre === '') {
            $errores[] = 'Falta el nombre completo.';
        } elseif (count(preg_split('/\s+/u', $nombre)) < 3) {
            $avisos[] = 'El nombre parece incompleto (se esperan nombre y dos apellidos).';
        }
        if ($puesto === '') {
            $errores[] = 'Falta el puesto.';
        }
        if ($salario === null) {
            $errores[] = 'Falta el salario diario.';
        } elseif ($salario < self::SALARIO_MINIMO) {
            $errores[] = 'El salario diario ($'.number_format($salario, 2).') es menor al salario mínimo ($'.number_format(self::SALARIO_MINIMO, 2).').';
        }
        if (! $ingreso) {
            $errores[] = 'Falta la fecha de ingreso o no es una fecha.';
        }
        if (! $termino) {
            $errores[] = 'Falta la fecha de terminación o no es una fecha.';
        }
        if ($ingreso && $termino && $termino->lt($ingreso)) {
            $errores[] = 'La fecha de terminación es anterior a la de ingreso.';
        }
        if ($baseAguinaldo === null) {
            $baseAguinaldo = 15;
            $avisos[] = 'Sin días base de aguinaldo: se tomaron 15 (el mínimo).';
        } elseif ($baseAguinaldo < 15) {
            $errores[] = 'Los días base de aguinaldo deben ser 15 o más.';
        }

        // Conceptos que captura la empresa.
        $c = [
            'indemnizacion'       => self::numero($v('N')),
            'vacaciones'          => self::numero($v('O')),
            'prima_vacacional'    => self::numero($v('Q')),
            'aguinaldo'           => self::numero($v('S')),
            'salarios_pendientes' => self::numero($v('U')),
        ];
        foreach ($c as $k => $monto) {
            if ($monto !== null && $monto < 0) {
                $errores[] = 'Hay un monto negativo en '.self::NOMBRES[$k].'.';
            }
        }
        $impuesto = self::numero($v('Y')) ?? 0.0;

        // Otras prestaciones (hoja 2), sólo si dijo que sí.
        $conOtras = mb_strtoupper(self::texto($v('V'))) === 'SI';
        $otras = $otrasPorNumero[$no] ?? [];
        if (! $conOtras && $otras) {
            $errores[] = 'Marcó "NO" en otras prestaciones pero capturó montos en la hoja "Otras prestaciones".';
        }
        if ($conOtras && ! $otras) {
            $avisos[] = 'Marcó "SI" en otras prestaciones pero no hay ninguna en la hoja "Otras prestaciones".';
        }
        foreach ($otras as $o) {
            if ($o['tipo'] === '') {
                $errores[] = 'Hay un monto de otras prestaciones ($'.number_format($o['monto'], 2).') sin tipo de prestación.';
            }
        }

        // Mínimos legales, como el formato (columnas G a L, P, R y T).
        $minimos = null;
        if ($salario && $ingreso && $termino && ! $termino->lt($ingreso)) {
            $minimos = self::minimos($salario, $ingreso, $termino, $baseAguinaldo);
            foreach ([['vacaciones', 'vacaciones'], ['prima_vacacional', 'prima_vacacional'], ['aguinaldo', 'aguinaldo']] as [$concepto, $minimo]) {
                $pagado = $c[$concepto] ?? 0.0;
                if ($pagado + 0.005 < round($minimos[$minimo], 2)) {
                    $errores[] = self::NOMBRES[$concepto].': $'.number_format($pagado, 2).' es menor al mínimo legal de $'.number_format($minimos[$minimo], 2).'.';
                }
            }
        }

        $sumaOtras = array_sum(array_column($otras, 'monto'));
        $total = round(array_sum(array_map('floatval', $c)) + $sumaOtras, 2);
        if ($impuesto < 0) {
            $errores[] = 'El impuesto a retener no puede ser negativo.';
        } elseif ($impuesto > $total) {
            $errores[] = 'El impuesto a retener es mayor que el total a pagar.';
        }
        if ($total <= 0 && ! $errores) {
            $errores[] = 'El total a pagar es cero.';
        }

        return [
            'renglon'         => $r,
            'numero'          => $no,
            'nombre_completo' => $nombre,
            'nombre_partes'   => CitaPatronal::desglosarNombre($nombre),
            'puesto'          => $puesto,
            'salario_diario'  => $salario,
            'fecha_ingreso'   => $ingreso?->toDateString(),
            'fecha_termino'   => $termino?->toDateString(),
            'aguinaldo_dias'  => (int) $baseAguinaldo,
            'conceptos'       => array_map(fn ($m) => round((float) $m, 2), $c),
            'otras'           => $otras,
            'total'           => $total,
            'impuesto'        => round($impuesto, 2),
            'neto'            => round($total - $impuesto, 2),
            'minimos'         => $minimos,
            'observaciones'   => self::texto($v('AA')),
            'errores'         => $errores,
            'avisos'          => $avisos,
        ];
    }

    public const NOMBRES = [
        'indemnizacion'       => 'Indemnización constitucional',
        'vacaciones'          => 'Vacaciones proporcionales',
        'prima_vacacional'    => 'Prima vacacional',
        'aguinaldo'           => 'Aguinaldo proporcional',
        'salarios_pendientes' => 'Salarios pendientes',
    ];

    /**
     * Los cálculos del formato:
     *   años (G) = años cumplidos entre ingreso y terminación
     *   días de vacaciones (H) = tabla del formato según años
     *   último aniversario (K); días desde él (L) = terminación − K + 1
     *   días del año trabajados (J) = día del año de la terminación
     *   vacaciones (P) = H / 365 × L × salario; prima (R) = P × 25 %
     *   aguinaldo (T) = días base / 365 × J × salario
     */
    public static function minimos(float $salario, Carbon $ingreso, Carbon $termino, float $baseAguinaldo = 15): array
    {
        $anios = (int) $ingreso->diffInYears($termino);
        $diasVacaciones = self::diasVacaciones($anios);

        $aniversario = $ingreso->copy()->year($termino->year);
        if ($aniversario->gt($termino)) {
            $aniversario->subYear();
        }
        $diasDesdeAniversario = (int) $aniversario->diffInDays($termino) + 1;
        $diasDelAnio = $termino->dayOfYear;

        $vacaciones = $diasVacaciones / 365 * $diasDesdeAniversario * $salario;

        return [
            'anios'                  => $anios,
            'dias_vacaciones'        => $diasVacaciones,
            'dias_relacion'          => (int) $ingreso->diffInDays($termino) + 1,
            'dias_del_anio'          => $diasDelAnio,
            'ultimo_aniversario'     => $aniversario->toDateString(),
            'dias_desde_aniversario' => $diasDesdeAniversario,
            'vacaciones'             => round($vacaciones, 2),
            'prima_vacacional'       => round($vacaciones * 0.25, 2),
            'aguinaldo'              => round($baseAguinaldo / 365 * $diasDelAnio * $salario, 2),
        ];
    }

    /** Tabla de vacaciones del formato (años cumplidos → días). */
    public static function diasVacaciones(int $anios): int
    {
        return match (true) {
            $anios <= 5 => [12, 14, 16, 18, 20, 22][$anios],
            $anios <= 9 => 22,
            default => 24 + 2 * intdiv($anios - 10, 5), // 10-14: 24, 15-19: 26, 20-24: 28 …
        };
    }

    // ------------------------------------------------------------------ apoyo

    /** Hoja 2: No. => [[tipo, monto], ...] (sólo los pares con monto). */
    private static function otrasPrestaciones(Worksheet $h): array
    {
        $r = [];
        for ($fila = self::PRIMER_RENGLON; $fila <= $h->getHighestRow(); $fila++) {
            $no = $h->getCell("A$fila")->getValue();
            if (! is_numeric($no)) {
                continue;
            }
            foreach ([['C', 'D'], ['E', 'F'], ['G', 'H'], ['I', 'J'], ['K', 'L'], ['M', 'N'], ['O', 'P']] as [$ct, $cm]) {
                $monto = self::numero($h->getCell("$cm$fila")->getValue());
                if ($monto !== null && $monto != 0.0) {
                    $r[(int) $no][] = ['tipo' => mb_strtoupper(self::texto($h->getCell("$ct$fila")->getValue())), 'monto' => round($monto, 2)];
                }
            }
        }

        return $r;
    }

    private static function texto(mixed $v): string
    {
        if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $v = $v->getPlainText();
        }

        return trim((string) $v);
    }

    private static function numero(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_string($v)) {
            $v = str_replace([',', '$', ' '], '', $v);
            // Las fórmulas que no se calcularon llegan como texto "=…".
            if ($v === '' || str_starts_with($v, '=')) {
                return null;
            }
        }

        return is_numeric($v) ? (float) $v : null;
    }

    private static function fecha(mixed $v): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        try {
            if (is_numeric($v)) {
                return Carbon::instance(FechaExcel::excelToDateTimeObject((float) $v))->startOfDay();
            }
            $s = trim((string) $v);
            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y-m-d H:i:s', 'd/m/y'] as $formato) {
                $f = \DateTime::createFromFormat('!'.$formato, $s);
                if ($f && $f->format($formato) === $s) {
                    return Carbon::instance($f)->startOfDay();
                }
            }
        } catch (\Throwable) {
        }

        return null;
    }
}
