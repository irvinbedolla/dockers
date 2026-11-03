<?php

namespace App\Support;

use Barryvdh\DomPDF\PDF;

/**
 * El mismo \PDF de siempre, pero pasa el HTML por TextoSeguro antes de
 * generarlo. Así los datos que ya estaban guardados con caracteres raros
 * tampoco salen como "?". No cambia la fuente de ningún documento.
 */
class PdfSeguro extends PDF
{
    public function loadHTML(string $string, ?string $encoding = null): self
    {
        return parent::loadHTML(TextoSeguro::limpiar($string), $encoding);
    }
}
