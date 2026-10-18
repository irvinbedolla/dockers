<?php

namespace App\Http\Controllers;

use App\Support\FlujoCita;
use App\Support\PantallaEspera;
use Illuminate\Http\Request;

/**
 * Pantallas de sala de espera por sede: /pantalla-morelia, /pantalla-uruapan…
 *
 * Públicas, como las viejas /pantallaMorelia (se quedan mientras se retiran).
 * La página se dibuja una vez y luego se actualiza sola con /datos cada pocos
 * segundos, sin recargar: así no parpadea ni pierde la rotación de paneles.
 */
class PantallaEsperaController extends Controller
{
    public function mostrar(Request $request, string $sede)
    {
        $nombre = PantallaEspera::sede($sede) ?? abort(404);
        $pruebas = $this->conPruebas($request);

        return view('pantallas.espera', [
            'sede'     => $nombre,
            'slug'     => $sede,
            'pruebas'  => $pruebas,
            'datos'    => PantallaEspera::datos($nombre, $pruebas),
            'urlDatos' => route('pantalla.datos', $pruebas ? [$sede, 'pruebas' => 1] : $sede),
        ]);
    }

    public function datos(Request $request, string $sede)
    {
        $nombre = PantallaEspera::sede($sede) ?? abort(404);

        return response()
            ->json(PantallaEspera::datos($nombre, $this->conPruebas($request)))
            ->header('Cache-Control', 'no-store');
    }

    /** ?pruebas=1 muestra también las citas de prueba. */
    private function conPruebas(Request $request): bool
    {
        return $request->boolean('pruebas') && FlujoCita::migrado();
    }
}
