<?php

namespace App\Http\Controllers;

use App\Models\Sinotifico\Ruta;
use App\Models\User;
use App\Services\Sinotifico\Geocodificador;
use App\Services\Sinotifico\HistorialRutas;
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
    /** La app avisa cada 2 minutos; con 5 sin noticias deja de contar como en línea. */
    public const MINUTOS_EN_LINEA = 5;

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

        $ids = $notificadores->pluck('id');
        $presencia = DB::table('sinotifico_presencia')->whereIn('user_id', $ids)->get()->keyBy('user_id');
        $ultimas = $this->ultimasCapturas($ids);
        $avance = $this->avance($rutas);

        $filas = $notificadores->map(function (User $n) use ($rutas, $presencia, $ultimas, $avance) {
            $visitas = (new VisitasDelNotificador($n))->pendientes();
            $p = $presencia->get($n->id);

            return [
                'usuario' => $n,
                'visitas' => $visitas->count(),
                'urgentes' => $visitas->whereIn('plazo.estado', ['fuera_de_plazo', 'ultimo_dia'])->count(),
                'ruta' => $rutas->get($n->id),
                'hechas' => $avance[$n->id] ?? 0,
                'ultima' => $ultimas[$n->id] ?? null,
                'conexion' => $p ? [
                    'en_linea' => CarbonImmutable::parse($p->visto_at)->gt(now()->subMinutes(self::MINUTOS_EN_LINEA)),
                    'visto_at' => CarbonImmutable::parse($p->visto_at),
                    'sincronizado_at' => $p->sincronizado_at ? CarbonImmutable::parse($p->sincronizado_at) : null,
                    'en_cola' => (int) $p->en_cola,
                ] : null,
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

    /** Historial semanal: rutas de cada notificador y cuánto se cumplió de cada una. */
    public function historial(Request $request)
    {
        $yo = $this->supervisor($request);
        $todas = Acceso::supervisa($yo) === 'todas';
        $sedes = array_keys(config('sinotifico.sedes'));
        $sede = $todas ? ($request->query('sede') ?: null) : $yo->delegacion;
        if ($sede !== null && ! in_array($sede, $sedes, true)) {
            $sede = null;
        }

        try {
            [$anio, $num] = array_map('intval', explode('-W', (string) $request->query('semana', now()->format('o-\\WW'))));
            $lunes = CarbonImmutable::now()->setISODate($anio, $num)->startOfWeek();
        } catch (\Throwable) {
            $lunes = CarbonImmutable::now()->startOfWeek();
        }
        $domingo = $lunes->addDays(6);

        $rutas = HistorialRutas::consulta($lunes->toDateString(), $domingo->toDateString())
            ->when($sede, fn ($q) => $q->where('r.sede', $sede))
            ->get()
            ->map(fn ($r) => (object) ((array) $r + HistorialRutas::cifras($r) + ['dia' => CarbonImmutable::parse($r->fecha)->toDateString()]));

        $nombres = DB::table('users')->whereIn('id', $rutas->pluck('notificador_id')->unique())->pluck('name', 'id');
        $dias = collect(range(0, 6))->map(fn ($i) => $lunes->addDays($i))
            ->filter(fn ($d) => ! $d->isSunday() || $rutas->contains('dia', $d->toDateString()))->values();

        $filas = $rutas->groupBy('notificador_id')->map(function ($suyas, $id) use ($nombres) {
            $aprobadas = $suyas->where('estatus', 'aprobada');

            return [
                'id' => (int) $id,
                'nombre' => $nombres[$id] ?? "Usuario {$id}",
                'sede' => $suyas->first()->sede,
                'por_dia' => $suyas->keyBy('dia'),
                'rutas' => $aprobadas->count(),
                'cumplidas' => $aprobadas->where('estado', 'cumplida')->count(),
                'total' => $aprobadas->sum('total'),
                'hechas' => $aprobadas->sum('hechas'),
                'exitosas' => $aprobadas->sum('exitosas'),
                'km' => round($aprobadas->sum('distancia_km'), 1),
            ];
        })->sortBy('nombre')->values();

        $aprobadas = $rutas->where('estatus', 'aprobada');

        return view('notificaciones.rutas.historial', [
            'filas' => $filas,
            'dias' => $dias,
            'lunes' => $lunes,
            'sede' => $sede,
            'sedes' => $todas ? $sedes : [],
            'totales' => [
                'rutas' => $aprobadas->count(),
                'cumplidas' => $aprobadas->where('estado', 'cumplida')->count(),
                'total' => $aprobadas->sum('total'),
                'hechas' => $aprobadas->sum('hechas'),
                'exitosas' => $aprobadas->sum('exitosas'),
                'km' => round($aprobadas->sum('distancia_km'), 1),
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
            // Días pasados: historial de sólo lectura.
            'soloLectura' => $fecha < now()->toDateString(),
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

    /**
     * Última diligencia que capturó cada notificador (su última parada).
     *
     * @return array<int, array{expediente: ?string, citado: ?string, resultado: string, capturada_at: CarbonImmutable, estatus: string}>
     */
    private function ultimasCapturas($ids): array
    {
        $ultimas = DB::table('sinotifico_diligencias')
            ->whereIn('id', DB::table('sinotifico_diligencias')->selectRaw('MAX(id)')->whereIn('notificador_id', $ids)->groupBy('notificador_id'))
            ->get();

        return $ultimas->mapWithKeys(function ($d) {
            $citado = DB::table('sinotifico_diligencia_citado as dc')->join('seer_citados as c', 'c.id', '=', 'dc.citado_id')
                ->where('dc.diligencia_id', $d->id)->orderBy('c.id')
                ->first(['c.nombre', 'c.primer_apellido', 'c.segundo_apellido']);

            return [(int) $d->notificador_id => [
                'expediente' => DB::table('seer_general')->where('id', $d->solicitud_id)->value('NUE'),
                'citado' => $citado ? trim(implode(' ', array_filter([$citado->nombre, $citado->primer_apellido, $citado->segundo_apellido]))) : null,
                'resultado' => $d->resultado ?? 'Exhorto',
                'capturada_at' => CarbonImmutable::parse($d->capturada_at),
                'estatus' => $d->estatus,
            ]];
        })->all();
    }

    /** Paradas de cada ruta ya capturadas desde el día de la ruta. @return array<int, int> */
    private function avance($rutas): array
    {
        $avance = [];
        foreach ($rutas as $r) {
            $avance[$r->notificador_id] = DB::table('sinotifico_ruta_paradas as p')
                ->where('p.ruta_id', $r->id)
                ->whereExists(fn ($q) => $q->select(DB::raw(1))
                    ->from('sinotifico_diligencia_citado as dc')
                    ->join('sinotifico_diligencias as d', 'd.id', '=', 'dc.diligencia_id')
                    ->whereColumn('dc.citado_id', 'p.visita_id')
                    ->where('d.estatus', '!=', 'devuelta')
                    ->where('d.capturada_at', '>=', $r->fecha->toDateString()))
                ->count();
        }

        return $avance;
    }

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

        return [
            'ruta' => $this->planificador->presentar($ruta),
            'oficina' => $oficina,
            'fuera' => $ruta->fecha->toDateString() < now()->toDateString() ? [] : $this->planificador->fuera($ruta->load('paradas')),
            'eventos' => DB::table('sinotifico_ruta_eventos as e')->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->where('e.ruta_id', $ruta->id)->orderBy('e.id')
                ->get(['e.tipo', 'e.detalle', 'e.created_at', 'u.name as usuario'])
                ->map(fn ($e) => [
                    'tipo' => $e->tipo,
                    'usuario' => $e->usuario,
                    'fecha' => CarbonImmutable::parse($e->created_at)->toIso8601String(),
                    'reabierta' => (bool) (json_decode((string) $e->detalle, true)['reabierta'] ?? false),
                    'paradas' => count(json_decode((string) $e->detalle, true)['paradas'] ?? []),
                ]),
        ];
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
