<?php

namespace App\Http\Controllers;

use App\Support\AgendaNotificador;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Fuente "Mis notificaciones" de la agenda del notificador (App\Support\AgendaNotificador). */
class AgendaNotificadorController extends Controller
{
    public function __invoke(Request $request)
    {
        $datos = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date'],
        ]);

        return response()->json(AgendaNotificador::eventos(
            $request->user(),
            Carbon::parse($datos['start'])->toDateString(),
            Carbon::parse($datos['end'])->toDateString()
        ));
    }
}
