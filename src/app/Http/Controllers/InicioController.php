<?php

namespace App\Http\Controllers;

use App\Support\AgendaContexto;

/**
 * Pantalla de Inicio.
 *
 * Deliberadamente aparte del resto: es el lugar donde más adelante van a
 * colgarse los datos y las notificaciones propias de cada rol, así que no
 * depende de ningún otro controlador. Por ahora saluda al usuario y muestra
 * la agenda, que se arma con el mismo parcial que /agenda.
 */
class InicioController extends Controller
{
    public function index()
    {
        $usuario  = auth()->user();
        $userRole = $usuario->roles->pluck('name')->all();

        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('inicio.index', compact('usuario', 'userRole', 'sedes', 'conciliadores'));
    }
}
