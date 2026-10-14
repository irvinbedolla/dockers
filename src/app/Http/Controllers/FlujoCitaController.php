<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\User;
use App\Support\FlujoCita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Administración → Flujo de citas en línea (sólo Super Usuario).
 *
 * El interruptor global del flujo nuevo (App\Support\FlujoCita), la lista de
 * correos de prueba, los auxiliares piloto y los minutos de aviso.
 */
class FlujoCitaController extends Controller
{
    public function index()
    {
        abort_unless(FlujoCita::migrado(), 503, 'Falta correr la migración del flujo de citas (php artisan migrate).');

        // Candidatos a piloto: quien atiende un módulo activo.
        $personal = DB::table('modulos as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.activo', 1)
            ->where('u.estatus', 'Activo')
            ->orderBy('m.delegacion')->orderBy('m.orden')
            ->get(['u.id', 'u.name', 'm.delegacion', 'm.nombre as modulo'])
            ->groupBy('delegacion');

        $hoy = now()->toDateString();

        return view('administracion.flujo_cita.index', [
            'estado'   => FlujoCita::estado(),
            'correos'  => implode("\n", FlujoCita::correosPrueba()),
            'pilotos'  => FlujoCita::pilotos(),
            'minutos'  => FlujoCita::minutosAviso(),
            'personal' => $personal,
            'pruebas'  => [
                'hoy'   => DB::table('recepcion')->where('es_prueba', 1)->where('fecha', $hoy)->count(),
                'total' => DB::table('recepcion')->where('es_prueba', 1)->count(),
            ],
        ]);
    }

    public function guardar(Request $request)
    {
        abort_unless(FlujoCita::migrado(), 503);

        $datos = $request->validate([
            'estado'    => ['required', Rule::in(array_keys(FlujoCita::ESTADOS))],
            'correos'   => ['nullable', 'string', 'max:2000'],
            'pilotos'   => ['nullable', 'array'],
            'pilotos.*' => ['integer', 'exists:users,id'],
            'minutos'   => ['required', 'integer', 'min:1', 'max:60'],
        ], [
            'minutos.*' => 'Los minutos de aviso van de 1 a 60.',
        ]);

        $correos = collect(preg_split('/[\s,;]+/', (string) ($datos['correos'] ?? '')) ?: [])
            ->map(fn ($c) => mb_strtolower(trim($c)))->filter()->unique()->values();

        $malos = $correos->reject(fn ($c) => filter_var($c, FILTER_VALIDATE_EMAIL));
        if ($malos->isNotEmpty()) {
            return back()->withInput()->withErrors(['correos' => 'Correos no válidos: '.$malos->implode(', ')]);
        }

        $antes = FlujoCita::estado();
        $yo = $request->user()->id;

        Ajuste::guardar('flujo_cita.estado', $datos['estado'], $yo);
        Ajuste::guardar('flujo_cita.correos_prueba', $correos->implode("\n"), $yo);
        Ajuste::guardar('flujo_cita.pilotos', json_encode(array_values(array_unique(array_map('intval', $datos['pilotos'] ?? [])))), $yo);
        Ajuste::guardar('flujo_cita.aviso_minutos', (string) $datos['minutos'], $yo);

        if ($antes !== $datos['estado']) {
            Log::warning('Flujo de citas: cambio de estado', ['de' => $antes, 'a' => $datos['estado'], 'por' => $yo]);
        }

        return redirect()->route('flujo_cita.index')
            ->with('success', 'Se guardó. Flujo de citas: '.FlujoCita::ESTADOS[$datos['estado']].'.');
    }
}
