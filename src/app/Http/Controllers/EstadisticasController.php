<?php

namespace App\Http\Controllers;

use App\Models\Sedes;
use App\Models\User;
use App\Support\ResumenSedes;

/**
 * /estadisticas: el panel general del Centro.
 *
 * Arriba, la descarga de los mismos reportes que /seer/estadistica (el
 * formulario manda a seer.mostar, que es quien los arma); abajo, el mapa de
 * sedes y las gráficas que antes sólo veía el Directivo en su Inicio. Aquí se
 * irán sumando más gráficas.
 *
 * Sólo Super Usuario, Estadistica y Directivo (lo cuida la ruta). Los tres
 * ven todas las sedes.
 */
class EstadisticasController extends Controller
{
    public function index()
    {
        $activosConRol = fn (string $rol) => User::role($rol)
            ->where('estatus', 'Activo')
            ->where('email', 'not like', '%temporal%')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('estadisticas.panel', [
            'sedes'         => Sedes::orderBy('id')->pluck('nombre'),
            'conciliadores' => $activosConRol('Conciliador'),
            'notificadores' => $activosConRol('Notificador'),
            'resumen'       => ResumenSedes::totales(),
            'resumenSedes'  => ResumenSedes::detalle(),
        ]);
    }
}
