<?php

namespace App\Http\Middleware\Sinotifico;

use Closure;
use Illuminate\Http\Request;

/**
 * Revisa en cada petición, no solo al iniciar sesión, que la cuenta siga
 * pudiendo usar SíNotifico: si la desactivan o le quitan el rol mientras
 * tiene un token vigente, el siguiente intento se rechaza.
 */
class AccesoSinotifico
{
    public function handle(Request $request, Closure $next)
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

        if (! $usuario->hasAnyRole(config('sinotifico.roles'))) {
            return response()->json(['message' => 'Tu cuenta no tiene acceso a SíNotifico.'], 403);
        }

        return $next($request);
    }
}
