<?php

namespace App\Http\Controllers;

use App\Support\AccesosRecepcion;
use App\Support\CasosExcepcion;
use App\Support\AgendaContexto;
use App\Support\ResumenSedes;
use App\Support\Saludo;
use App\Support\TablaPosiciones;

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

        // Recepción (Morelia 01, 02 y General) ve accesos rápidos en lugar de
        // la tabla de posiciones; los demás roles, la tabla. Se resuelve antes
        // de la bifurcación. Las cifras del mes (convenios, montos y tasa) se
        // mudaron al Panel estadístico (EstadisticasController).
        // Conciliación, Género e Igualdad ve sus casos de excepción de hoy.
        $casos      = CasosExcepcion::es($usuario) ? CasosExcepcion::inicio() : null;
        $accesos    = ! $casos && AccesosRecepcion::aplica($usuario) ? AccesosRecepcion::para($usuario) : null;
        $posiciones = ($accesos || $casos) ? null : TablaPosiciones::resumen();

        if (in_array('Directivo', $userRole, true)) {
            return view('inicio.index', [
                'usuario'      => $usuario,
                'userRole'     => $userRole,
                'saludo'       => $saludo,
                'posiciones'   => $posiciones,
                'accesos'      => $accesos,
                'casos'        => $casos,
                'resumen'      => ResumenSedes::totales(),
                'resumenSedes' => ResumenSedes::detalle(),
            ]);
        }

        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('inicio.index', compact('usuario', 'userRole', 'saludo', 'posiciones', 'accesos', 'casos', 'sedes', 'conciliadores'));
    }
}
