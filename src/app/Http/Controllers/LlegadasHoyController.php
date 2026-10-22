<?php

namespace App\Http\Controllers;

use App\Support\LlegadasHoy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Llegadas hoy" (App\Support\LlegadasHoy): audiencias, ratificaciones y
 * cumplimientos del día con quién ya llegó.
 *
 *   - La recepción de audiencias (Morelia 02) y el Super Usuario marcan.
 *   - El conciliador sólo consulta; por omisión ve lo suyo.
 *   - Al conciliador asignado le llega el aviso con sonido (avisos(), que
 *     consulta llegadas/_avisos desde el layout).
 */
class LlegadasHoyController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        abort_unless(LlegadasHoy::puedeVer($usuario), 403);

        $sedes = LlegadasHoy::sedes($usuario);
        $sede  = $this->sede($request, $sedes);
        $tipo  = array_key_exists((string) $request->query('tipo'), LlegadasHoy::TIPOS) ? (string) $request->query('tipo') : 'todas';

        return view('llegadas.index', [
            'sedes'     => $sedes,
            'sede'      => $sede,
            'tipo'      => $tipo,
            'marca'     => LlegadasHoy::puedeMarcar($usuario),
            'yo'        => $usuario->id,
            'soloMias'  => ! LlegadasHoy::puedeMarcar($usuario),
            'datos'     => $this->armar($sede),
            'urlDatos'  => route('llegadas.datos', ['sede' => $sede]),
        ]);
    }

    public function datos(Request $request)
    {
        abort_unless(LlegadasHoy::puedeVer($request->user()), 403);

        $sede = $this->sede($request, LlegadasHoy::sedes($request->user()));

        return response()->json($this->armar($sede))->header('Cache-Control', 'no-store');
    }

    public function marcar(Request $request, string $tipo, int $id)
    {
        $usuario = $request->user();
        abort_unless(LlegadasHoy::puedeMarcar($usuario), 403);

        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:60'],
            'llego' => ['required', 'boolean'],
        ]);
        abort_unless(array_key_exists($tipo, LlegadasHoy::TIPOS), 404);

        $error = LlegadasHoy::marcar($tipo, $id, $datos['clave'], (bool) $datos['llego'], $usuario);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        Log::info('llegada', ['tipo' => $tipo, 'id' => $id, 'clave' => $datos['clave'], 'llego' => (bool) $datos['llego'], 'por' => $usuario->id]);

        return response()->json(['ok' => true]);
    }

    /** Observaciones de la recepción para una audiencia o ratificación de hoy. */
    public function observaciones(Request $request, string $tipo, int $id)
    {
        $usuario = $request->user();
        abort_unless(LlegadasHoy::puedeMarcar($usuario), 403);

        $datos = $request->validate([
            'silla_ruedas' => ['nullable', 'boolean'],
            'traductor'    => ['nullable', 'boolean'],
            'menor_edad'   => ['nullable', 'boolean'],
            'texto'        => ['nullable', 'string', 'max:1000'],
        ]);

        $error = LlegadasHoy::guardarObservaciones($tipo, $id, $datos, $usuario);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        Log::info('llegada.observaciones', ['tipo' => $tipo, 'id' => $id, 'por' => $usuario->id]);

        return response()->json(['ok' => true]);
    }

    /** Para el conciliador: quién llegó a lo que tiene hoy. */
    public function avisos(Request $request)
    {
        return response()->json(['avisos' => LlegadasHoy::avisosPara($request->user())])
            ->header('Cache-Control', 'no-store');
    }

    private function sede(Request $request, array $sedes): string
    {
        $pedida = (string) $request->query('sede', '');

        return in_array($pedida, $sedes, true) ? $pedida : ($sedes[0] ?? '');
    }

    private function armar(string $sede): array
    {
        $registros = $sede === '' ? collect() : LlegadasHoy::delDia([$sede], now()->toDateString());

        return [
            'sede'      => $sede,
            'generado'  => now()->toIso8601String(),
            'registros' => $registros->all(),
        ];
    }
}
