<?php

namespace App\Http\Controllers;

use App\Support\PantallaEspera;

/**
 * Pantallas de sala de espera por sede: /pantalla-morelia, /pantalla-uruapan…
 *
 * Públicas, como las viejas /pantallaMorelia (se quedan mientras se retiran).
 * La página se dibuja una vez y luego se actualiza sola con /datos cada pocos
 * segundos, sin recargar: así no parpadea ni pierde la rotación de paneles.
 */
class PantallaEsperaController extends Controller
{
    public function mostrar(string $sede)
    {
        $nombre = PantallaEspera::sede($sede) ?? abort(404);

        return view('pantallas.espera', [
            'sede'     => $nombre,
            'slug'     => $sede,
            'datos'    => PantallaEspera::datos($nombre),
            'urlDatos' => route('pantalla.datos', $sede),
        ]);
    }

    public function datos(string $sede)
    {
        $nombre = PantallaEspera::sede($sede) ?? abort(404);

        return response()
            ->json(PantallaEspera::datos($nombre))
            ->header('Cache-Control', 'no-store');
    }
}
