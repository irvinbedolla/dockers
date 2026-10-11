<?php

namespace App\Http\Middleware;

use App\Support\Especialidades;
use Closure;
use Illuminate\Http\Request;

/**
 * Las pantallas de Solicitudes, Ratificaciones y Cumplimientos están abiertas
 * a todo el personal. Esto sólo frena a quien entra como Auxiliar y no tiene
 * la especialidad: así el de ratificaciones no llega a Solicitudes escribiendo
 * la URL. Cualquier otro rol pasa igual que antes.
 *
 * Uso: ->middleware('especialidad:ratificaciones_consultar')
 */
class EspecialidadAuxiliar
{
    public function handle(Request $request, Closure $next, string $permiso)
    {
        $usuario = $request->user();

        if (Especialidades::soloAuxiliar($usuario) && ! $usuario->can($permiso)) {
            abort(403, 'Esta sección no corresponde a tu especialidad. Si debes atenderla, pide que te la asignen en Usuarios.');
        }

        return $next($request);
    }
}
