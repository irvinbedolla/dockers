<?php

namespace App\Http\Controllers;

use App\Models\Sinotifico\Ruta;
use App\Models\User;
use App\Services\Sinotifico\Geocodificador;
use App\Services\Sinotifico\PlanificadorRutas;
use App\Services\Sinotifico\VisitasDelNotificador;
use App\Support\Sinotifico\Acceso;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Notificaciones → Rutas (SíNotifico, Módulo 4).
 *
 * Quien supervisa notificadores (Enlaces elegidos, Delegados, Directivos y
 * Super Usuarios) entra desde la tarjeta "Rutas de notificadores" en
 * Notificaciones (no hay acceso en el menú lateral) y ve la ruta del día de cada notificador en el mismo mapa que
 * usa la app, la ajusta y la aprueba. El notificador sólo la ve aprobada.
 */
class RutasNotificadoresController extends Controller
{
    public function __construct(private readonly PlanificadorRutas $planificador) {}

    public static function puedeEntrar(?User $u): bool
    {
        return $u !== null && Acceso::supervisa($u) !== null;
    }

    /** Rutas de hoy por aprobar dentro de su alcance (número de la tarjeta en Notificaciones). */
    public static function porAprobarHoy(User $u): int
    {
        return Ruta::whereDate('fecha', now()->toDateString())
            ->where('estatus', Ruta::BORRADOR)
            ->when(Acceso::supervisa($u) === 'sede', fn ($q) => $q->where('sede', $u->delegacion))
            ->count();
    }

    public function index(Request $request)
    {
        $yo = $this->supervisor($request);
        $fecha = $this->fecha($request->query('fecha'));
        $todas = Acceso::supervisa($yo) === 'todas';
        $sedes = array_keys(config('sinotifico.sedes'));
        $sede = $todas ? ($request->query('sede') ?: null) : $yo->delegacion;
        if ($sede !== null && ! in_array($sede, $sedes, true)) {
            $sede = null;
        }

        $notificadores = User::role('Notificador')
            ->where('estatus', 'Activo')
            ->when($sede, fn ($q) => $q->where('delegacion', $sede))
            ->orderBy('delegacion')->orderBy('name')
            ->get(['id', 'name', 'delegacion']);

        $rutas = Ruta::whereDate('fecha', $fecha)->whereIn('notificador_id', $notificadores->pluck('id'))
            ->withCount('paradas')->get()->keyBy('notificador_id');

        $filas = $notificadores->map(function (User $n) use ($rutas) {
            $visitas = (new VisitasDelNotificador($n))->pendientes();

            return [
                'usuario' => $n,
                'visitas' => $visitas->count(),
                'urgentes' => $visitas->whereIn('plazo.estado', ['fuera_de_plazo', 'ultimo_dia'])->count(),
                'ruta' => $rutas->get($n->id),
            ];
        });

        return view('notificaciones.rutas.index', [
            'filas' => $filas,
            'fecha' => $fecha,
            'sede' => $sede,
            'sedes' => $todas ? $sedes : [],
            'resumen' => [
                'aprobadas' => $rutas->where('estatus', Ruta::APROBADA)->count(),
                'borradores' => $rutas->where('estatus', Ruta::BORRADOR)->count(),
                'sin_ruta' => $filas->filter(fn ($f) => ! $f['ruta'] && $f['visitas'] > 0)->count(),
            ],
        ]);
    }

    public function editor(Request $request, int $notificador, string $fecha)
    {
        [$n, $fecha] = $this->notificador($request, $notificador, $fecha);
        $ruta = $this->planificador->ruta($n, $fecha);

        return view('notificaciones.rutas.editor', [
            'notificador' => $n,
            'fecha' => $fecha,
            'datos' => $this->datos($n, $ruta),
            'urls' => [
                'generar' => route('rutas.generar', [$n->id, $fecha]),
                'guardar' => route('rutas.guardar', [$n->id, $fecha]),
                'ordenar' => route('rutas.ordenar', [$n->id, $fecha]),
                'aprobar' => route('rutas.aprobar', [$n->id, $fecha]),
                'ubicar' => route('rutas.ubicar', ['domicilio' => '__ID__']),
            ],
        ]);
    }

    public function generar(Request $request, int $notificador, string $fecha): JsonResponse
    {
        [$n, $fecha] = $this->notificador($request, $notificador, $fecha);
        $ruta = $this->planificador->proponer($n, $fecha, $request->user());

        return response()->json($this->datos($n, $ruta));
    }

    public function guardar(Request $request, int $notificador, string $fecha): JsonResponse
    {
        [$n, $fecha] = $this->notificador($request, $notificador, $fecha);
        $ids = $request->validate(['visitas' => ['present', 'array', 'max:60'], 'visitas.*' => ['integer']])['visitas'];
        $ruta = $this->planificador->ruta($n, $fecha) ?? Ruta::create([
            'notificador_id' => $n->id, 'fecha' => $fecha, 'sede' => $n->delegacion, 'creada_por' => $request->user()->id,
        ]);
        $ruta = $this->planificador->guardar($ruta, $ids, $request->user());

        return response()->json($this->datos($n, $ruta));
    }

    public function ordenar(Request $request, int $notificador, string $fecha): JsonResponse
    {
        [$n, $fecha] = $this->notificador($request, $notificador, $fecha);
        $ruta = $this->planificador->ruta($n, $fecha);
        abort_unless($ruta, 404, 'Primero genera la ruta.');

        return response()->json($this->datos($n, $this->planificador->ordenarPorCercania($ruta, $request->user())));
    }

    public function aprobar(Request $request, int $notificador, string $fecha): JsonResponse
    {
        [$n, $fecha] = $this->notificador($request, $notificador, $fecha);
        $ruta = $this->planificador->ruta($n, $fecha);
        abort_unless($ruta, 404, 'Primero genera la ruta.');

        return response()->json($this->datos($n, $this->planificador->aprobar($ruta, $request->user())));
    }

    /** Mover el pin de un domicilio: corrige la ubicación para esta y futuras rutas. */
    public function ubicar(Request $request, Geocodificador $geo, int $domicilio): JsonResponse
    {
        $this->supervisor($request);
        $datos = $request->validate([
            'lat' => ['required', 'numeric', 'between:14,33'],
            'lng' => ['required', 'numeric', 'between:-119,-86'],
        ], ['between' => 'La ubicación debe estar dentro de México.']);

        abort_unless(DB::table('sinotifico_domicilios')->where('id', $domicilio)->exists(), 404);
        $geo->fijarManual($domicilio, (float) $datos['lat'], (float) $datos['lng'], $request->user()->id);

        return response()->json(['ok' => true]);
    }

    /* ---------------------------------------------------------------- */

    private function datos(User $n, ?Ruta $ruta): array
    {
        $oficina = config('sinotifico.oficinas')[$n->delegacion] ?? null;

        if (! $ruta) {
            $pendientes = (new VisitasDelNotificador($n))->pendientes();

            return [
                'ruta' => null,
                'oficina' => $oficina,
                'fuera' => $pendientes->map(fn ($v) => [
                    'visita_id' => $v['id'],
                    'expediente' => $v['expediente'],
                    'principal' => collect($v['citados'])->firstWhere('generico', false)['nombre'] ?? $v['citados'][0]['nombre'],
                    'domicilio' => $v['domicilio']['texto'],
                    'fuera_del_estado' => $v['domicilio']['fuera_del_estado'],
                    'plazo' => $v['plazo'],
                    'audiencia' => $v['audiencia'],
                ])->values(),
            ];
        }

        return ['ruta' => $this->planificador->presentar($ruta), 'oficina' => $oficina, 'fuera' => $this->planificador->fuera($ruta->load('paradas'))];
    }

    private function supervisor(Request $request): User
    {
        $yo = $request->user();
        abort_unless(self::puedeEntrar($yo), 403, 'Esta sección es para quien supervisa notificadores.');

        return $yo;
    }

    /** @return array{0: User, 1: string} */
    private function notificador(Request $request, int $id, string $fecha): array
    {
        $yo = $this->supervisor($request);
        $n = User::findOrFail($id);
        abort_unless($n->hasRole('Notificador') && PlanificadorRutas::puedeSupervisar($yo, $n), 404);

        return [$n, $this->fecha($fecha)];
    }

    private function fecha(?string $valor): string
    {
        try {
            return $valor ? CarbonImmutable::createFromFormat('Y-m-d', $valor)->toDateString() : now()->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }
}
