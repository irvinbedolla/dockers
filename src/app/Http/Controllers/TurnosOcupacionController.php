<?php

namespace App\Http\Controllers;

use App\Support\AgendaTurnos;
use App\Support\OcupacionTurnos;
use App\Support\Recepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Turnos → Todos los turnos → "Ocupación": cuántos turnos hay agendados por
 * día y por horario contra el cupo de Horarios de citas
 * (App\Support\OcupacionTurnos). JSON para el calendario del diálogo.
 *
 * Los mismos que descargan la agenda (AgendaTurnos::puedeDescargar):
 * Super Usuario y Directivo de todas las sedes; el Delegado con turnos_ver
 * (p. ej. con el rol Turnos además del suyo), de su región.
 */
class TurnosOcupacionController extends Controller
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        abort_unless(AgendaTurnos::puedeDescargar($usuario), 403);

        $sedes = Recepcion::sedesVisibles($usuario);
        $f = $request->validate([
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'sede'  => ['nullable', Rule::in($sedes)],
            'grupo' => ['nullable', Rule::in(array_keys(OcupacionTurnos::GRUPOS))],
            'horas' => ['nullable', 'boolean'],
        ]);

        $desde = Carbon::parse($f['desde']);
        $hasta = Carbon::parse($f['hasta']);
        if ($desde->diffInDays($hasta) > OcupacionTurnos::MAX_DIAS) {
            $hasta = $desde->copy()->addDays(OcupacionTurnos::MAX_DIAS);
        }

        $datos = OcupacionTurnos::calcular(
            ! empty($f['sede']) ? [$f['sede']] : $sedes,
            $desde->toDateString(),
            $hasta->toDateString(),
            $f['grupo'] ?? null,
            ! empty($f['horas'])
        );

        return response()->json($datos + ['generado' => now()->toIso8601String()])
            ->header('Cache-Control', 'no-store');
    }
}
