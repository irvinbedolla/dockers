<?php

namespace App\Http\Controllers;

use App\Services\Sinotifico\TableroSupervision;
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
        $t = (new TableroSupervision($request->user()))->datos($periodo, (string) $request->query('sede'));

        return view('notificaciones.tablero', [
            't' => $t,
            'sedes' => count($t['sedes_alcance']) > 1 ? $t['sedes_alcance'] : [],
        ]);
    }
}
