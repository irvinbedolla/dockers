<?php

namespace App\Support;

use App\Services\CompresorPdf;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Los PDF de un envío de Poderes, listos para guardarse en S3:
 *
 * - Cuando dos campos traen el mismo documento (p. ej. el acta constitutiva
 *   también acredita la personería), se guarda UNA sola vez y los dos campos
 *   quedan apuntando al mismo archivo. Se detecta de dos formas:
 *     1. El navegador ya lo vio (documentos_duplicados.js): manda el archivo en
 *        un solo campo y avisa con documentos_iguales[campo] = campo que sí lo trae.
 *     2. Llegan los dos archivos y su contenido es idéntico (mismo SHA-256).
 * - Los PDF de más de 20 MB se reducen con CompresorPdf antes de guardarse.
 *
 * Todo eso pasa en preparar(), antes de crear o tocar el registro: si un PDF no
 * se puede reducir, se rechaza el envío sin dejar nada a medias.
 */
class DocumentosPoder
{
    public const CAMPO_IGUALES = 'documentos_iguales';

    /** campo => campo "dueño" del archivo (él mismo si no está repetido). */
    private array $dueno = [];

    /** campo dueño => ruta del PDF que se va a guardar. */
    private array $rutas = [];

    /** campo dueño => nombre con que ya se guardó en S3. */
    private array $guardados = [];

    /** PDF comprimidos que hay que borrar al terminar. */
    private array $temporales = [];

    /**
     * Campos que el navegador marcó como "el mismo archivo que otro campo".
     * Sólo se aceptan entre los campos de este envío, hacia un campo que sí
     * trae archivo y desde uno que no lo trae (si trae, decide el hash).
     *
     * @return array<string, string> campo => campo que trae el archivo
     */
    public static function enlazadosPorNavegador(Request $request, array $campos): array
    {
        $enlaces = [];
        foreach ((array) $request->input(self::CAMPO_IGUALES, []) as $campo => $origen) {
            if (is_string($campo) && is_string($origen)
                && $campo !== $origen
                && in_array($campo, $campos, true)
                && in_array($origen, $campos, true)
                && ! $request->hasFile($campo)
                && $request->hasFile($origen)) {
                $enlaces[$campo] = $origen;
            }
        }

        return $enlaces;
    }

    /**
     * @param  array<int, string>  $campos  campos de archivo de este envío, en el
     *                                      orden en que se guardan (el primero de
     *                                      un par repetido se queda con el archivo)
     * @param  array<string, string>  $etiquetas  campo => nombre para los mensajes
     */
    public static function preparar(Request $request, array $campos, array $etiquetas = []): self
    {
        $docs = new self();

        // 1. Archivos que sí llegaron. Los repetidos se buscan por hash, y sólo
        //    entre los que pesan lo mismo (dos archivos idénticos por fuerza).
        $porTamano = [];
        foreach ($campos as $campo) {
            if ($request->hasFile($campo) && $request->file($campo)->isValid()) {
                $ruta = $request->file($campo)->getRealPath();
                $porTamano[filesize($ruta)][$campo] = $ruta;
            }
        }

        $porHash = [];
        foreach ($porTamano as $grupo) {
            foreach ($grupo as $campo => $ruta) {
                $huella = count($grupo) > 1 ? hash_file('sha256', $ruta) : $campo;
                if (isset($porHash[$huella])) {
                    $docs->dueno[$campo] = $porHash[$huella];
                } else {
                    $porHash[$huella] = $campo;
                    $docs->dueno[$campo] = $campo;
                    $docs->rutas[$campo] = $ruta;
                }
            }
        }

        // 2. Los que el navegador ya marcó como repetidos.
        foreach (self::enlazadosPorNavegador($request, $campos) as $campo => $origen) {
            if (isset($docs->dueno[$origen])) {
                $docs->dueno[$campo] = $docs->dueno[$origen];
            }
        }

        // 3. Comprimir los que pasan de 20 MB (en paralelo).
        $listos = app(CompresorPdf::class)->comprimir($docs->rutas);
        $errores = [];
        foreach ($listos as $campo => $ruta) {
            if ($ruta === null) {
                $megas = round(filesize($docs->rutas[$campo]) / 1048576, 1);
                $errores[$campo] = ucfirst($etiquetas[$campo] ?? 'el documento')." pesa {$megas} MB y no se pudo reducir a 20 MB sin que deje de leerse. "
                    .'Escanéalo de nuevo a menor resolución (150 dpi, en escala de grises) e intenta otra vez.';
            } elseif ($ruta !== $docs->rutas[$campo]) {
                $docs->temporales[] = $ruta;
                $docs->rutas[$campo] = $ruta;
            }
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        return $docs;
    }

    /** ¿El campo trae documento (propio o el mismo que otro campo)? */
    public function tiene(string $campo): bool
    {
        return isset($this->dueno[$campo]);
    }

    /**
     * Guarda el documento del campo en $carpeta con $nombre y regresa el nombre
     * guardado. Si su archivo ya se guardó por otro campo, no lo sube otra vez:
     * regresa el nombre de ese, para que los dos campos abran el mismo PDF.
     */
    public function guardar(string $campo, string $carpeta, string $nombre): string
    {
        $dueno = $this->dueno[$campo];

        if (! isset($this->guardados[$dueno])) {
            Storage::disk('s3')->putFileAs($carpeta, new File($this->rutas[$dueno]), $nombre);
            $this->guardados[$dueno] = $nombre;
        }

        return $this->guardados[$dueno];
    }

    public function __destruct()
    {
        foreach ($this->temporales as $ruta) {
            @unlink($ruta);
        }
    }
}
