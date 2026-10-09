<?php

namespace App\Http\Middleware;

use App\Support\Recepcion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra a los roles de recepción las fuentes de la agenda que no son suyas.
 *
 * Uso: ->middleware(SinRecepcion::class.':audiencias'). El parámetro es el id
 * de la fuente (el mismo de calFuente en calendar.js); si el rol de recepción
 * la tiene en Recepcion::fuentes(), pasa. Sin parámetro (la descarga) no pasa
 * nadie de recepción.
 *
 * Esconder la pestaña no basta: son URLs que cualquiera con sesión puede abrir
 * a mano. Por la misma razón, a quien sí pasa se le fija la sede a las suyas:
 * los controladores de eventos aceptan la sede que venga en la URL.
 *
 * Si la cuenta tiene además otro rol que no es de recepción, pasa sin tocarla.
 */
class SinRecepcion
{
    public function handle(Request $request, Closure $next, ?string $fuente = null): Response
    {
        $usuario = $request->user();

        if (! Recepcion::es($usuario) || $usuario->getRoleNames()->diff(Recepcion::ROLES)->isNotEmpty()) {
            return $next($request);
        }

        if ($fuente === null || ! Recepcion::veFuente($usuario, $fuente)) {
            abort(403, 'Esta agenda no está disponible para recepción.');
        }

        $sedes = Recepcion::sedes($usuario);
        $sede  = $request->input('sede');

        if (count($sedes) === 1) {
            $request->merge(['sede' => $sedes[0]]);
        } elseif ($sede !== 'Todos' && ! in_array($sede, $sedes, true)) {
            $request->merge(['sede' => $sedes[0] ?? '__ninguna__']);
        }

        return $next($request);
    }
}
