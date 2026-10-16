<?php

namespace App\Http\Controllers;

use App\Support\LlegadasAudiencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Audiencias de hoy" (recepción de audiencias, Morelia 02): la recepcionista
 * marca qué parte ya llegó y el conciliador recibe un aviso con sonido
 * (audiencias/_avisos_llegada, que consulta avisos()).
 *
 * La lógica vive en App\Support\LlegadasAudiencia.
 */
class LlegadasAudienciaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(LlegadasAudiencia::puedeMarcar($request->user()), 403);

        $sedes = LlegadasAudiencia::sedes($request->user());
        $sede  = $this->sede($request, $sedes);

        return view('recepcion.audiencias_hoy', [
            'sedes'    => $sedes,
            'sede'     => $sede,
            'datos'    => $this->armar($sede),
            'urlDatos' => route('recepcion.audiencias_hoy.datos', ['sede' => $sede]),
        ]);
    }

    public function datos(Request $request)
    {
        abort_unless(LlegadasAudiencia::puedeMarcar($request->user()), 403);

        $sede = $this->sede($request, LlegadasAudiencia::sedes($request->user()));

        return response()->json($this->armar($sede))->header('Cache-Control', 'no-store');
    }

    public function marcar(Request $request, int $id)
    {
        $usuario = $request->user();
        abort_unless(LlegadasAudiencia::puedeMarcar($usuario), 403);

        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:60'],
            'llego' => ['required', 'boolean'],
        ]);

        $error = LlegadasAudiencia::marcar($id, $datos['clave'], (bool) $datos['llego'], $usuario);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        Log::info('audiencia.llegada', ['audiencia' => $id, 'clave' => $datos['clave'], 'llego' => (bool) $datos['llego'], 'por' => $usuario->id]);

        return response()->json(['ok' => true]);
    }

    /** Para el conciliador: quién llegó a sus audiencias de hoy. */
    public function avisos(Request $request)
    {
        return response()->json(['avisos' => LlegadasAudiencia::avisosPara($request->user())])
            ->header('Cache-Control', 'no-store');
    }

    private function sede(Request $request, array $sedes): string
    {
        $pedida = (string) $request->query('sede', '');

        return in_array($pedida, $sedes, true) ? $pedida : ($sedes[0] ?? '');
    }

    private function armar(string $sede): array
    {
        $audiencias = $sede === '' ? collect() : LlegadasAudiencia::delDia([$sede], now()->toDateString());

        return [
            'sede'       => $sede,
            'generado'   => now()->toIso8601String(),
            'audiencias' => $audiencias->all(),
            'cifras'     => [
                'total'      => $audiencias->count(),
                'completas'  => $audiencias->where('completa', true)->count(),
                'conAlguien' => $audiencias->filter(fn ($a) => $a['llegaron'] > 0 && ! $a['completa'])->count(),
                'nadie'      => $audiencias->where('llegaron', 0)->count(),
            ],
        ];
    }
}
