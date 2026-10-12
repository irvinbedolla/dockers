<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // se obtine la API Key que envía el cliente en los headers
        $apiKey = $request->header('X-API-Key');

        // Comprobar con la clave secreta configurada en el servidor
        if (!$apiKey || $apiKey !== config('services.api_key')) {
            return response()->json([
                'error' => 'No autorizado. API Key inválida o faltante.'
            ], 401);
        }

        return $next($request);
    }
}