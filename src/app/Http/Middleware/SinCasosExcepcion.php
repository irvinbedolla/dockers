<?php

namespace App\Http\Middleware;

use App\Support\CasosExcepcion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra a quien sólo tiene el rol de Conciliación, Género e Igualdad las
 * agendas que no son suyas: en la agenda sólo ve las citas en línea con caso
 * de excepción (CasosExcepcion::soloRol).
 */
class SinCasosExcepcion
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(CasosExcepcion::soloRol($request->user()), 403, 'Esta agenda no está disponible para tu rol.');

        return $next($request);
    }
}
