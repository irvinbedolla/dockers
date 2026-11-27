<?php

namespace App\Services;

use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

/**
 * Reduce PDFs con Ghostscript hasta que pesen como máximo LIMITE_BYTES.
 *
 * - Un PDF que ya cabe NO se toca: se guarda tal cual, así no se pierde una
 *   firma electrónica ni se degrada un documento que no lo necesita.
 * - Los que pasan del límite se reescriben bajando la resolución de las
 *   imágenes (lo que pesa en un escaneo) por niveles: primero 150 dpi y, si
 *   todavía no cabe, 120 y 100. Debajo de eso una identificación ya no se lee.
 * - Los archivos de un mismo envío se comprimen en paralelo.
 *
 * Ghostscript se instala en la imagen de Docker (ver Dockerfile). Si no está
 * (por ejemplo en una máquina de desarrollo sin él), los PDF que no caben se
 * reportan como no comprimibles en lugar de tronar.
 */
class CompresorPdf
{
    public const LIMITE_BYTES = 20 * 1024 * 1024;

    /** Resoluciones que se prueban, de la más fiel a la más agresiva. */
    private const NIVELES_DPI = [150, 120, 100];

    /** Segundos que puede tardar Ghostscript con un archivo en un nivel. */
    private const TIMEOUT = 240;

    /**
     * @param  array<string, string>  $rutas  clave => ruta del PDF original
     * @return array<string, string|null>  clave => ruta del PDF listo para guardar
     *                                     (el original si ya cabía), o null si no
     *                                     se pudo dejar en el límite
     */
    public function comprimir(array $rutas): array
    {
        $resultado  = [];
        $pendientes = [];

        foreach ($rutas as $clave => $ruta) {
            if (filesize($ruta) <= self::LIMITE_BYTES) {
                $resultado[$clave] = $ruta;
            } else {
                $pendientes[$clave] = $ruta;
            }
        }

        foreach (self::NIVELES_DPI as $dpi) {
            if (! $pendientes) {
                break;
            }

            $salidas = [];
            foreach ($pendientes as $clave => $ruta) {
                $salidas[$clave] = $this->archivoTemporal();
            }

            try {
                $procesos = Process::concurrently(function (Pool $pool) use ($pendientes, $salidas, $dpi) {
                    foreach ($pendientes as $clave => $ruta) {
                        $pool->as((string) $clave)
                            ->timeout(self::TIMEOUT)
                            ->command($this->comando($ruta, $salidas[$clave], $dpi));
                    }
                });
            } catch (\Throwable $e) {
                // Ghostscript no está instalado o algún archivo pasó del tiempo.
                report($e);
                foreach ($salidas as $salida) {
                    @unlink($salida);
                }
                break;
            }

            foreach ($pendientes as $clave => $ruta) {
                $salida = $salidas[$clave];
                clearstatcache(true, $salida);

                if ($procesos[(string) $clave]->successful()
                    && is_file($salida)
                    && filesize($salida) > 0
                    && filesize($salida) <= self::LIMITE_BYTES) {
                    $resultado[$clave] = $salida;
                    unset($pendientes[$clave]);
                } else {
                    @unlink($salida);
                }
            }
        }

        foreach ($pendientes as $clave => $ruta) {
            $resultado[$clave] = null;
        }

        return $resultado;
    }

    protected function comando(string $entrada, string $salida, int $dpi): array
    {
        return [
            config('services.ghostscript.bin', 'gs'),
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.5',
            '-dPDFSETTINGS=/ebook',
            '-dNOPAUSE', '-dBATCH', '-dQUIET',
            // Que el PDF no pueda leer ni escribir nada fuera de lo que se le da.
            '-dSAFER',
            '-dDetectDuplicateImages=true',
            '-dCompressFonts=true',
            '-dDownsampleColorImages=true',
            '-dColorImageDownsampleType=/Bicubic',
            '-dColorImageResolution='.$dpi,
            '-dDownsampleGrayImages=true',
            '-dGrayImageDownsampleType=/Bicubic',
            '-dGrayImageResolution='.$dpi,
            // Blanco y negro puro pesa poco y es donde más se nota la letra.
            '-dDownsampleMonoImages=true',
            '-dMonoImageResolution='.($dpi * 2),
            '-sOutputFile='.$salida,
            $entrada,
        ];
    }

    private function archivoTemporal(): string
    {
        return tempnam(sys_get_temp_dir(), 'pdfc_');
    }
}
