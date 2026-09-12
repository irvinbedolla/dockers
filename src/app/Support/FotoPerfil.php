<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Recorta, reduce y convierte a WebP las fotos de perfil.
 *
 * Reencodear es tambien la desinfeccion: lo que sale es un WebP nuevo dibujado
 * pixel por pixel, sin EXIF, sin perfiles de color y sin nada que viniera
 * escondido dentro del archivo original.
 *
 * Requiere GD compilado con soporte de JPEG y WebP. La imagen del contenedor
 * lo trae desde el cambio al Dockerfile que acompana esta funcion; si alguien
 * la reconstruye sin el, guardar() truena con un mensaje claro en vez de
 * escribir un archivo vacio.
 */
class FotoPerfil
{
    /** Lado del cuadro final. La barra lo pinta a 40px; 256 cubre retina y fichas. */
    private const LADO = 256;

    /** Arriba de 80 el archivo crece y el ojo ya no distingue. */
    private const CALIDAD = 80;

    /** Carpeta dentro del disco 'public' (storage/app/public). */
    public const CARPETA = 'usuarios';

    /**
     * Guarda la foto y devuelve la ruta relativa para users.foto_perfil.
     * Si se le pasa la ruta anterior, borra ese archivo al terminar.
     */
    public static function guardar(UploadedFile $archivo, ?string $anterior = null): string
    {
        $destino = self::CARPETA.'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($destino, self::procesar($archivo->getRealPath()));

        // Solo despues de escribir la nueva: si algo falla arriba, el usuario
        // se queda con la que tenia en vez de quedarse sin ninguna.
        self::borrar($anterior);

        return $destino;
    }

    public static function borrar(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }

    /**
     * Recorta al cuadrado, reduce y devuelve el WebP en binario, sin guardarlo.
     * Lo usan guardar() y el comando de siembra.
     */
    public static function procesar(string $rutaLocal): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException(
                'GD no tiene soporte de WebP. Reconstruye la imagen del contenedor: '.
                'docker compose build app'
            );
        }

        $imagen = self::recortarCuadrado(
            self::orientar(self::abrir($rutaLocal), $rutaLocal),
            self::LADO
        );

        ob_start();
        imagewebp($imagen, null, self::CALIDAD);
        $binario = ob_get_clean();
        imagedestroy($imagen);

        if ($binario === false || $binario === '') {
            throw new RuntimeException('No se pudo generar el WebP.');
        }

        return $binario;
    }

    /** El tipo sale de los bytes, nunca de la extension que mando el navegador. */
    private static function abrir(string $ruta)
    {
        $info = @getimagesize($ruta);

        $imagen = $info === false ? null : match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($ruta),
            IMAGETYPE_PNG  => @imagecreatefrompng($ruta),
            IMAGETYPE_WEBP => @imagecreatefromwebp($ruta),
            default        => null,
        };

        if (! $imagen) {
            throw new RuntimeException('El archivo no es una imagen JPG, PNG o WebP valida.');
        }

        return $imagen;
    }

    /**
     * Las fotos de celular salen acostadas: el sensor graba derecho y anota el
     * giro en EXIF. Al reencodear se pierde esa nota, asi que hay que aplicarla.
     */
    private static function orientar($imagen, string $ruta)
    {
        if (! function_exists('exif_read_data')) {
            return $imagen;
        }

        $exif  = @exif_read_data($ruta);
        $grado = match ($exif['Orientation'] ?? 1) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($grado === 0) {
            return $imagen;
        }

        $girada = imagerotate($imagen, $grado, 0);
        imagedestroy($imagen);

        return $girada;
    }

    /** Recorte centrado al cuadrado mayor posible, luego reduccion. */
    private static function recortarCuadrado($origen, int $lado)
    {
        $ancho = imagesx($origen);
        $alto  = imagesy($origen);
        $corte = min($ancho, $alto);

        $destino = imagecreatetruecolor($lado, $lado);
        // Fondo blanco: un PNG con transparencia sale negro si no se rellena.
        imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
        imagecopyresampled(
            $destino, $origen,
            0, 0,
            (int) (($ancho - $corte) / 2), (int) (($alto - $corte) / 2),
            $lado, $lado,
            $corte, $corte
        );
        imagedestroy($origen);

        return $destino;
    }
}
