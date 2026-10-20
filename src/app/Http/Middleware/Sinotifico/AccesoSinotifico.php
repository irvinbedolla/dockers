<?php

namespace App\Http\Middleware\Sinotifico;

use App\Support\Sinotifico\Acceso;
use Closure;
use Illuminate\Http\Request;

/**
 * Revisa en cada petición, no solo al iniciar sesión, que la cuenta siga
 * pudiendo usar SíNotifico: si la desactivan o le quitan el permiso mientras
 * tiene un token vigente, el siguiente intento se rechaza.
 *
 * Con parámetro limita la ruta a una parte de la app:
 *   AccesoSinotifico::class.':notificar'  sólo quien tiene lista de campo.
 */
class AccesoSinotifico
{
    public function handle(Request $request, Closure $next, ?string $parte = null)
    {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'Inicia sesión para continuar.'], 401);
        }

        if ($usuario->estatus === 'Inactivo') {
            $usuario->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.',
            ], 401);
        }

        if (! $usuario->tokenCan(config('sinotifico.ability'))) {
            return response()->json(['message' => 'Este acceso no es válido para SíNotifico.'], 403);
        }

        if (! Acceso::puedeEntrar($usuario)) {
            return response()->json(['message' => 'Tu cuenta no tiene acceso a SíNotifico.'], 403);
        }

        if ($parte === 'notificar' && ! Acceso::notifica($usuario)) {
            return response()->json(['message' => 'Esta sección es sólo para notificadores.'], 403);
        }

        return $next($request);
    }
}
