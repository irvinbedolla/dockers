<?php

namespace App\Http\Middleware;

use App\Support\CasosExcepcion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Acceso a los casos de excepción (App\Support\CasosExcepcion).
 *
 *   ->middleware(CasosExcepcionAcceso::class)            consultar la bandeja
 *   ->middleware(CasosExcepcionAcceso::class.':atender') atender y canalizar
 *   ->middleware(CasosExcepcionAcceso::class.':estadisticas') sus estadísticas
 *
 * Existe porque el rol "Conciliación, Genero e Igualdad" lleva coma y no
 * cabe en un 'role:...' (Laravel parte los parámetros por coma).
 */
class CasosExcepcionAcceso
{
    public function handle(Request $request, Closure $next, string $nivel = 'consultar'): Response
    {
        $usuario = $request->user();
        $puede = match ($nivel) {
            'atender'      => CasosExcepcion::atiende($usuario),
            // Estadísticas: el rol y quien ve el Panel estadístico.
            'estadisticas' => $usuario !== null && $usuario->hasAnyRole(['Super Usuario', 'Estadistica', 'Directivo', CasosExcepcion::ROL]),
            default        => CasosExcepcion::consulta($usuario),
        };

        abort_unless($puede, 403, 'Esta sección es del Departamento de Conciliación, Género e Igualdad.');

        return $next($request);
    }
}
