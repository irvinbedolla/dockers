<?php

namespace App\Http\Controllers;

use App\Services\Sinotifico\TableroSupervision;
use App\Support\Sinotifico\Acceso;
use Illuminate\Http\Request;

/**
 * Notificaciones → Tablero de notificadores (SíNotifico, Módulo 5). Mismo
 * contenido que el tablero de la app; se entra desde la tarjeta en
 * Notificaciones y sólo lo ve quien supervisa notificadores.
 */
class TableroNotificadoresController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(RutasNotificadoresController::puedeEntrar($request->user()), 403, 'Esta sección es para quien supervisa notificadores.');

        $periodo = in_array($request->query('periodo'), TableroSupervision::PERIODOS, true) ? $request->query('periodo') : 'hoy';
        $sede = Acceso::supervisa($request->user()) === 'todas' && array_key_exists((string) $request->query('sede'), config('sinotifico.sedes'))
            ? $request->query('sede') : null;

        return view('notificaciones.tablero', [
            't' => (new TableroSupervision($request->user()))->datos($periodo, $sede),
            'sedes' => Acceso::supervisa($request->user()) === 'todas' ? array_keys(config('sinotifico.sedes')) : [],
        ]);
    }
}
