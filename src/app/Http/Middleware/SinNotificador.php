<?php

namespace App\Http\Middleware;

use App\Support\AgendaNotificador;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra al notificador lo que no es suyo: el historial de notificaciones y
 * las agendas que no son audiencias ni "Mis notificaciones". Sólo aplica a
 * quien únicamente tiene el rol Notificador (AgendaNotificador::soloNotificador).
 */
class SinNotificador
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(AgendaNotificador::soloNotificador($request->user()), 403, 'Esta sección no está disponible para notificadores.');

        return $next($request);
    }
}
