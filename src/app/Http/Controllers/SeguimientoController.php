<?php

namespace App\Http\Controllers;

use App\Support\SeguimientoCaso;
use Illuminate\Http\Request;

/**
 * Seguimiento del caso (/seguimiento): se busca a la persona por folio de
 * cita, NUE, CURP o nombre y se ve su historia completa con fechas
 * (App\Support\SeguimientoCaso). Cada quien consulta sólo las sedes que ve
 * en su agenda.
 */
class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $sedes   = SeguimientoCaso::sedes($usuario);
        $buscar  = trim((string) $request->query('q', ''));
        $clave   = (string) $request->query('caso', '');

        $resultados = $buscar !== '' ? SeguimientoCaso::buscar($buscar, $sedes) : collect();

        // Si sólo hay un resultado, se abre directo.
        if ($clave === '' && $resultados->count() === 1) {
            $clave = $resultados->first()['clave'];
        }

        $caso = $clave !== '' ? SeguimientoCaso::linea($clave, $sedes) : null;

        return view('seguimiento.index', [
            'buscar'     => $buscar,
            'resultados' => $resultados,
            'clave'      => $clave,
            'caso'       => $caso,
            'noEncontrado' => $clave !== '' && ! $caso,
        ]);
    }
}
