<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Si desactivan una cuenta mientras la persona tiene la sesión abierta, en su
 * siguiente clic se cierra la sesión. El login ya rechaza cuentas inactivas
 * (LoginRequest); esto cubre a quien ya estaba dentro.
 */
class CuentaActiva
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = Auth::guard('web')->user();

        if ($usuario && $usuario->estatus === 'Inactivo') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $mensaje = 'Tu cuenta se encuentra inactiva. Contacta al administrador.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 401)
                : redirect()->route('login')->withErrors(['email' => $mensaje]);
        }

        return $next($request);
    }
}
