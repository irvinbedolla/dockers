<?php

namespace App\Http\Middleware;

use App\Support\VerComo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Aplica "Ver como" (App\Support\VerComo) en cada petición web:
 *
 *   1. Revisa que quien está en la sesión siga siendo Super Usuario; si no,
 *      apaga el modo.
 *   2. Pone como usuario de la petición a la persona (o el rol) elegido, así
 *      menú, permisos, sedes y pantallas salen como los ve esa persona.
 *   3. Sólo lectura: lo que no sea GET/HEAD se rechaza, y lo que una pantalla
 *      escriba en la base al abrirse se deshace al terminar la petición.
 *
 * Las rutas de "Ver como" (ver_como.*) y cerrar sesión siempre corren con el
 * usuario real.
 */
class VerComoMiddleware
{
    /** Rutas que corren con el usuario real y pueden escribir. */
    private const LIBRES = ['logout'];

    public function handle(Request $request, Closure $next)
    {
        $datos = VerComo::datos();
        if ($datos === null) {
            return $next($request);
        }

        $real = Auth::guard('web')->user();
        if (! VerComo::puede($real)) {
            VerComo::salir($real);

            return $next($request);
        }

        $ruta = (string) optional($request->route())->getName();
        if (str_starts_with($ruta, 'ver_como.') || in_array($ruta, self::LIBRES, true)) {
            return $next($request);
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            $mensaje = 'Estás en "Ver como" (sólo lectura): no se guardó nada. Sal de "Ver como" para hacer cambios.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 423)
                : back()->with('ver_como_bloqueado', $mensaje);
        }

        // Todo dentro de una transacción que se deshace al final: lo que una
        // pantalla escriba al abrirse (vencer citas, ligar registros) y el
        // usuario de prueba del modo rol (VerComo::usuarioParaPeticion).
        $nivel = DB::transactionLevel();
        DB::beginTransaction();
        try {
            $visto = VerComo::usuarioParaPeticion($datos, $real);
            if ($visto === null) {
                VerComo::salir($real);

                return redirect()->route('inicio')->with('error', 'La persona o el rol que estabas viendo ya no existe; se cerró "Ver como".');
            }

            app()->instance('ver_como.real', $real);
            app()->instance('ver_como.datos', $datos);
            Auth::guard('web')->setUser($visto);
            $request->setUserResolver(fn () => $visto);

            $respuesta = $next($request);
        } finally {
            while (DB::transactionLevel() > $nivel) {
                DB::rollBack();
            }
        }

        return $this->conAviso($respuesta);
    }

    /**
     * Páginas que no usan layouts.app (errores 403/404, pantallas sueltas):
     * se les agrega el aviso para poder salir de "Ver como" desde ahí.
     */
    private function conAviso($respuesta)
    {
        if (! $respuesta instanceof \Illuminate\Http\Response
            || ! str_contains((string) $respuesta->headers->get('Content-Type'), 'text/html')) {
            return $respuesta;
        }
        $html = (string) $respuesta->getContent();
        $fin  = strripos($html, '</body>');
        if ($fin === false || str_contains($html, 'class="vc-barra"')) {
            return $respuesta;
        }
        try {
            $aviso = view('layouts._ver_como', ['sinModal' => true])->render();
        } catch (\Throwable $e) {
            report($e);

            return $respuesta;
        }

        return $respuesta->setContent(substr($html, 0, $fin).$aviso.substr($html, $fin));
    }
}
