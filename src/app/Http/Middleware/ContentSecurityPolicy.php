<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Manejar una petición entrante inyectando cabeceras CSP y Permissions-Policy dinámicas.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Definición de directivas permitiendo CDNs esenciales de SiConcilio
        $csp = "default-src 'self'; " .
               // script-src: Añadimos https://cdn.datatables.net para que funcionen los listados de audiencias
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net; " .
               // style-src: Soporte para estilos locales y CDNs declarados
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https: http:; " .
               "font-src 'self' data: https://fonts.gstatic.com http://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               // connect-src: CORRECCIÓN para permitir llamadas AJAX locales del sistema y mapas de origen de CDNs
               "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "frame-ancestors 'none'; " .
               "object-src 'none';";

        // Inyectar la cabecera CSP en la respuesta HTTP
        $response->headers->set('Content-Security-Policy', $csp);
        
        // CORRECCIÓN CRÍTICA: Se eliminó la auto-referencia de la cabecera dentro del string 
        // y se adaptó al formato válido "Structured Headers" requerido por navegadores modernos.
        $permissionsPolicy = "camera=(), " .
                             "microphone=(), " .
                             "geolocation=(self), " .
                             "fullscreen=(self), " .
                             "payment=(), " .
                             "usb=(), " .
                             "screen-wake-lock=(self)";

        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        return $response;
    }
}