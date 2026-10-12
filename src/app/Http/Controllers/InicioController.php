<?php

namespace App\Http\Controllers;

use App\Support\AgendaContexto;
use App\Support\ConveniosDelMes;
use App\Support\ConveniosPagados;
use App\Support\ResumenSedes;
use App\Support\Saludo;
use App\Support\TablaPosiciones;
use App\Support\TasaConciliacion;

/**
 * Pantalla de Inicio.
 *
 * Deliberadamente aparte del resto: es el lugar donde más adelante van a
 * colgarse los datos y las notificaciones propias de cada rol, así que no
 * depende de ningún otro controlador. Por ahora saluda al usuario y muestra
 * la agenda, que se arma con el mismo parcial que /agenda.
 *
 * El rol Directivo no ve la agenda: ve un panel de totales y desglose por
 * sede, así que resuelve sus propios datos en vez de pasar por AgendaContexto.
 */
class InicioController extends Controller
{
    public function index()
    {
        $usuario  = auth()->user();
        $userRole = $usuario->roles->pluck('name')->all();
        $saludo   = Saludo::para($usuario);

        // Las tarjetas de estadísticas las ven todos los roles, así que se
        // resuelven antes de la bifurcación.
        $convenios    = ConveniosPagados::porSede();
        $conveniosMes = ConveniosDelMes::resumen();
        $tasaConciliacion = TasaConciliacion::resumen();
        $posiciones       = TablaPosiciones::resumen();

        if (in_array('Directivo', $userRole, true)) {
            return view('inicio.index', [
                'usuario'      => $usuario,
                'userRole'     => $userRole,
                'saludo'       => $saludo,
                'convenios'    => $convenios,
                'conveniosMes' => $conveniosMes,
                'tasaConciliacion' => $tasaConciliacion,
                'posiciones'       => $posiciones,
                'resumen'      => ResumenSedes::totales(),
                'resumenSedes' => ResumenSedes::detalle(),
            ]);
        }

        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('inicio.index', compact('usuario', 'userRole', 'saludo', 'convenios', 'conveniosMes', 'tasaConciliacion', 'posiciones', 'sedes', 'conciliadores'));
    }
}
