<?php

namespace App\Http\Middleware;

use App\Support\CasosExcepcion;
use App\Support\Recepcion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuente "Citas en línea" de la agenda: la supervisión y la recepción
 * (Recepcion::veCitas) y Conciliación, Género e Igualdad, que sólo recibe las
 * de excepción (lo acota AgendaCitasController).
 */
class VeCitasEnLinea
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        abort_unless(Recepcion::veCitas($usuario) || CasosExcepcion::es($usuario), 403);

        return $next($request);
    }
}
