<?php

namespace App\Http\Controllers;

use App\Models\Sedes;
use App\Models\User;
use App\Support\ConveniosDelMes;
use App\Support\ConveniosPagados;
use App\Support\ResumenSedes;
use App\Support\TasaConciliacion;

/**
 * /estadisticas: el panel general del Centro.
 *
 * Arriba, la descarga de los mismos reportes que /seer/estadistica (el
 * formulario manda a seer.mostar, que es quien los arma); luego las cifras
 * del mes (convenios pagados, montos convenidos y tasa de conciliación, que
 * antes iban en el Inicio de todos) y abajo el mapa de sedes y las gráficas
 * que antes sólo veía el Directivo en su Inicio. Aquí se irán sumando más.
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
            // Tarjetas de cifras del mes (inicio/_tarjetas).
            'convenios'        => ConveniosPagados::porSede(),
            'conveniosMes'     => ConveniosDelMes::resumen(),
            'tasaConciliacion' => TasaConciliacion::resumen(),
        ]);
    }
}
