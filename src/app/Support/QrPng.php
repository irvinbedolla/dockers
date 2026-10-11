<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * Código QR en PNG dibujado con GD.
 *
 * QrCode::format('png') de simple-qrcode necesita la extensión Imagick, que el
 * contenedor no trae; y el SVG que genera por defecto no se ve en Gmail ni en
 * Outlook. Aquí se toma la matriz del QR de BaconQrCode (la misma librería
 * que usa simple-qrcode) y se pinta cuadro por cuadro con GD, que sí está.
 */
class QrPng
{
    /**
     * @param int $modulo   píxeles por cuadro del QR
     * @param int $margen   cuadros de margen blanco (el estándar pide 4)
     */
    public static function generar(string $texto, int $modulo = 8, int $margen = 4): string
    {
        // Codificación por defecto (ISO-8859-1): para una URL no agrega el
        // segmento ECI, que algunos lectores (incluido jsQR del escáner) no esperan.
        $matriz = Encoder::encode($texto, ErrorCorrectionLevel::M())->getMatrix();
        $n      = $matriz->getWidth();
        $lado   = ($n + 2 * $margen) * $modulo;

        $img    = imagecreatetruecolor($lado, $lado);
        $blanco = imagecolorallocate($img, 255, 255, 255);
        $tinta  = imagecolorallocate($img, 31, 42, 43);   // casi negro: buen contraste para cualquier lector
        imagefilledrectangle($img, 0, 0, $lado - 1, $lado - 1, $blanco);

        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($matriz->get($x, $y) === 1) {
                    $px = ($x + $margen) * $modulo;
                    $py = ($y + $margen) * $modulo;
                    imagefilledrectangle($img, $px, $py, $px + $modulo - 1, $py + $modulo - 1, $tinta);
                }
            }
        }

        ob_start();
        imagepng($img, null, 9);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }
}
