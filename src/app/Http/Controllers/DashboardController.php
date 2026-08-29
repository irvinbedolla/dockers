<?php

namespace App\Http\Controllers;

use App\Support\AgendaContexto;

class DashboardController extends Controller
{
    /**
     * Pantalla /agenda: sólo el calendario.
     */
    public function index()
    {
        addVendors(['amcharts', 'amcharts-maps', 'amcharts-stock']);

        $usuario  = auth()->user();
        $userRole = $usuario->roles->pluck('name')->all();

        // El alcance por rol y delegación vive en App\Support\AgendaContexto,
        // porque la pantalla de Inicio muestra el mismo calendario.
        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('pages/dashboards.index', compact('userRole', 'sedes', 'conciliadores'));
    }
}
