<?php

namespace App\Support;

use NumberToWords\NumberToWords;

/**
 * Montos en letra para convenios, actas y constancias:
 * 11571.428571429 → "ONCE MIL QUINIENTOS SETENTA Y UN PESOS 43/100".
 *
 * Antes cada controlador tenía su versión y las dos fallaban con decimales:
 *   - SeerController partía el número como texto en el punto y ponía los
 *     decimales tal cual: 11571.428571429 → "428571429/100" y 1234.5 → "05/100".
 *   - TurnosController podía redondear a "100/100" (10.999 → "10 PESOS 100/100").
 * Aquí primero se redondea a centavos y de ahí salen pesos y centavos, así
 * que la letra siempre coincide con la cifra que se imprime con
 * number_format(…, 2).
 */
class MontoEnLetras
{
    public static function texto($valor, bool $conMN = false): string
    {
        $centavosTotales = (int) round(abs(self::numero($valor)) * 100);
        $pesos    = intdiv($centavosTotales, 100);
        $centavos = str_pad((string) ($centavosTotales % 100), 2, '0', STR_PAD_LEFT);

        $letras = mb_strtoupper(
            (new NumberToWords())->getNumberTransformer('es')->toWords($pesos)
        );

        // Ante sustantivo masculino se apocopa: "SETENTA Y UN PESOS",
        // "VEINTIÚN PESOS", "UN PESO" (la librería da "UNO"/"VEINTIUNO").
        $letras = preg_replace('/VEINTIUNO$/u', 'VEINTIÚN', $letras);
        $letras = preg_replace('/\bUNO$/u', 'UN', $letras);

        // "UN MILLÓN DE PESOS", "DOS MILLONES DE PESOS" (millones exactos llevan "DE").
        $moneda = $pesos === 1 ? 'PESO' : ($pesos >= 1000000 && $pesos % 1000000 === 0 ? 'DE PESOS' : 'PESOS');
        $texto  = "{$letras} {$moneda} {$centavos}/100";

        return $conMN ? "{$texto} M.N." : $texto;
    }

    /** Acepta números o texto como "11,571.43" o "$ 1,200". */
    private static function numero($valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $limpio = preg_replace('/[^\d.\-]/', '', (string) $valor);

        return is_numeric($limpio) ? (float) $limpio : 0.0;
    }
}
