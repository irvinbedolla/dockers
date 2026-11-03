<?php

namespace App\Support;

/**
 * Corrige lo que se cuela al copiar y pegar (PDF, Word, páginas web) y que en
 * los PDF sale como "?", porque la fuente de dompdf sólo tiene Windows-1252:
 *
 *   - acento separado de la letra ("o" + ´) → "ó";
 *   - letras cirílicas/griegas que se ven igual que las nuestras (А, С, Е, О…);
 *   - espacios especiales y caracteres invisibles.
 *
 * Se aplica sola en TrimStrings (al guardar formularios) y en PdfSeguro (al
 * generar cualquier PDF). Quien captura no tiene que hacer nada distinto.
 */
class TextoSeguro
{
    private const REEMPLAZOS = [
        // Cirílico idéntico al latín
        'А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H', 'І' => 'I',
        'Ј' => 'J', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P', 'Ѕ' => 'S',
        'Т' => 'T', 'Х' => 'X', 'У' => 'Y',
        'а' => 'a', 'с' => 'c', 'е' => 'e', 'і' => 'i', 'ј' => 'j', 'о' => 'o',
        'р' => 'p', 'ѕ' => 's', 'х' => 'x', 'у' => 'y',
        // Griego idéntico al latín
        'Α' => 'A', 'Β' => 'B', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Ι' => 'I',
        'Κ' => 'K', 'Μ' => 'M', 'Ν' => 'N', 'Ο' => 'O', 'Ρ' => 'P', 'Τ' => 'T',
        'Υ' => 'Y', 'Χ' => 'X', 'ο' => 'o',
        // Espacios especiales → espacio normal
        "\u{2002}" => ' ', "\u{2003}" => ' ', "\u{2009}" => ' ', "\u{202F}" => ' ',
        "\u{3000}" => ' ', "\u{2028}" => ' ', "\u{2029}" => ' ',
        // Invisibles → nada
        "\u{200B}" => '', "\u{200C}" => '', "\u{200D}" => '', "\u{200E}" => '',
        "\u{200F}" => '', "\u{2060}" => '', "\u{FEFF}" => '',
        // Guiones que no existen en la fuente
        "\u{2010}" => '-', "\u{2011}" => '-', "\u{2212}" => '-',
    ];

    public static function limpiar(string $texto): string
    {
        // Sólo ASCII (casi todos los campos): no hay nada que corregir.
        if (! preg_match('/[^\x00-\x7F]/', $texto)) {
            return $texto;
        }

        $normalizado = \Normalizer::normalize($texto, \Normalizer::FORM_C);
        if ($normalizado === false) {
            return $texto; // UTF-8 inválido: mejor no tocarlo
        }

        return strtr($normalizado, self::REEMPLAZOS);
    }
}
