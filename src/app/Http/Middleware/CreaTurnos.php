<?php

namespace App\Http\Middleware;

use App\Support\CasosExcepcion;
use App\Support\Recepcion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Crear un turno en ventanilla (/turnos/nuevo) e imprimir su acuse.
 *
 * Pueden los roles de siempre del grupo de recepción y, además,
 * Conciliación, Género e Igualdad, que sólo agenda solicitudes y asesorías
 * (Recepcion::tramitesQuePuedeCrear). Ese rol no entra al resto de las
 * pantallas de turnos: por eso estas rutas tienen su propio middleware en
 * vez de darle el rol "Turnos". Clase y no 'role:...' porque el nombre del
 * rol lleva coma.
 */
class CreaTurnos
{
    public const ROLES = ['Super Usuario', 'Auxiliar', 'Recepción General', 'Turnos', 'Recepción Morelia 1', 'Recepción Morelia 2'];

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        abort_unless(
            $usuario && ($usuario->hasAnyRole(array_merge(self::ROLES, Recepcion::ROLES)) || CasosExcepcion::es($usuario)),
            403
        );

        return $next($request);
    }
}
