<?php

use Illuminate\Support\Facades\URL;

if (!function_exists('signedDocRoute')) {
    /**
     * Genera una URL firmada y temporal para las rutas de consulta de documentos.
     * Evita que el id/nombre de archivo de la URL pueda alterarse o adivinarse,
     * ya que la firma se invalida si cualquier parámetro cambia o si expira.
     *
     * @param string $name Nombre de la ruta (debe tener el middleware 'signed').
     * @param mixed $parameters Parámetros de la ruta, igual que en route().
     * @param int $minutes Minutos de vigencia del enlace.
     * @return string
     */
    function signedDocRoute(string $name, $parameters = [], int $minutes = 60)
    {
        if (!is_array($parameters)) {
            $parameters = ['id' => $parameters];
        }

        return URL::temporarySignedRoute($name, now()->addMinutes($minutes), $parameters);
    }
}
