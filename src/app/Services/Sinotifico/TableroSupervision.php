<?php

namespace App\Services\Sinotifico;

use App\Http\Controllers\RutasNotificadoresController;
use App\Models\Sinotifico\Ruta;
use App\Models\User;
use App\Support\Sinotifico\Acceso;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Módulo 5: tablero de supervisión de notificadores.
 *
 * Lo usan la app (supervisores) y SICONCILIO (Notificaciones → Tablero). Todo
 * sale de lo que ya registran los módulos anteriores: lista de trabajo,
 * capturas y su revisión, rutas y la señal de vida de la app.
 */
class TableroSupervision
{
    public const PERIODOS = ['hoy', 'semana', 'mes'];

    /** Horas sin señal con capturas en el teléfono para avisar al supervisor. */
    public const HORAS_SIN_ENVIAR = 2;

    public function __construct(private readonly User $supervisor) {}

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public static function rango(string $periodo): array
    {
        $hoy = CarbonImmutable::now();

        return match ($periodo) {
            'semana' => [$hoy->startOfWeek(), $hoy->endOfDay()],
            'mes' => [$hoy->startOfMonth(), $hoy->endOfDay()],
            default => [$hoy->startOfDay(), $hoy->endOfDay()],
        };
    }

    public function datos(string $periodo, ?string $sede = null): array
    {
        $periodo = in_array($periodo, self::PERIODOS, true) ? $periodo : 'hoy';
        [$desde, $hasta] = self::rango($periodo);

        $alcance = Acceso::supervisa($this->supervisor);
        [$sedes, $sede] = RutasNotificadoresController::alcance($this->supervisor, $sede);
        $aLaVista = $sede ? [$sede] : $sedes;

        $notificadores = User::role('Notificador')->where('estatus', 'Activo')
            ->whereIn('delegacion', $aLaVista)
            ->orderBy('name')->get(['id', 'name', 'delegacion']);
        $ids = $notificadores->pluck('id');

        // Capturas del periodo por notificador y estado.
        $capturas = DB::table('sinotifico_diligencias')
            ->whereIn('notificador_id', $ids)
            ->whereBetween('capturada_at', [$desde, $hasta])
            ->get(['id', 'notificador_id', 'estatus', 'resultado', 'llegada_at', 'capturada_at', 'revisada_at']);
        $porNotificador = $capturas->groupBy('notificador_id');

        $presencia = DB::table('sinotifico_presencia')->whereIn('user_id', $ids)->get()->keyBy('user_id');
        $rutasHoy = HistorialRutas::consulta(now()->toDateString(), now()->toDateString())
            ->whereIn('r.notificador_id', $ids)->get()->keyBy('notificador_id');
        $rutasPeriodo = HistorialRutas::consulta($desde->toDateString(), $hasta->toDateString())
            ->whereIn('r.notificador_id', $ids)->where('r.estatus', Ruta::APROBADA)->get()
            ->map(fn ($r) => (object) ((array) $r + HistorialRutas::cifras($r)));

        $filas = $notificadores->map(function (User $n) use ($porNotificador, $presencia, $rutasHoy) {
            $visitas = (new VisitasDelNotificador($n))->pendientes();
            $suyas = $porNotificador->get($n->id, collect());
            $p = $presencia->get($n->id);
            $ruta = $rutasHoy->get($n->id);
            $visto = $p ? CarbonImmutable::parse($p->visto_at) : null;

            return [
                'id' => $n->id,
                'nombre' => $n->name,
                'sede' => $n->delegacion,
                'en_linea' => $visto?->gt(now()->subMinutes(RutasNotificadoresController::MINUTOS_EN_LINEA)) ?? false,
                'visto_at' => $visto?->toIso8601String(),
                'en_cola' => (int) ($p->en_cola ?? 0),
                'pendientes' => $visitas->count(),
                'fuera_de_plazo' => $visitas->where('plazo.estado', 'fuera_de_plazo')->count(),
                'ultimo_dia' => $visitas->where('plazo.estado', 'ultimo_dia')->count(),
                'capturas' => $suyas->count(),
                'exitosas' => $suyas->where('estatus', 'validada')->whereIn('resultado', HistorialRutas::EXITOSAS)->count(),
                'por_revisar' => $suyas->where('estatus', 'por_revisar')->count(),
                'devueltas' => $suyas->where('estatus', 'devuelta')->count(),
                'ruta_hoy' => $ruta ? ['estatus' => $ruta->estatus] + HistorialRutas::cifras($ruta) : null,
                'ultima_captura' => $suyas->max('capturada_at') ? CarbonImmutable::parse($suyas->max('capturada_at'))->toIso8601String() : null,
            ];
        });

        $validadas = $capturas->where('estatus', 'validada');
        $revisadas = $capturas->filter(fn ($c) => $c->revisada_at !== null);
        $horasRevision = $revisadas->avg(fn ($c) => CarbonImmutable::parse($c->capturada_at)->diffInMinutes(CarbonImmutable::parse($c->revisada_at), true) / 60);

        return [
            'periodo' => $periodo,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'alcance' => $alcance,
            'sede' => $sede,
            'sedes_alcance' => $sedes,
            'resumen' => [
                'notificadores' => $notificadores->count(),
                'en_linea' => $filas->where('en_linea', true)->count(),
                'pendientes' => $filas->sum('pendientes'),
                'fuera_de_plazo' => $filas->sum('fuera_de_plazo'),
                'ultimo_dia' => $filas->sum('ultimo_dia'),
                'capturas' => $capturas->count(),
                'por_revisar' => $this->porRevisar($aLaVista),
                'validadas' => $validadas->count(),
                'devueltas' => $capturas->where('estatus', 'devuelta')->count(),
                'exitosas' => $validadas->whereIn('resultado', HistorialRutas::EXITOSAS)->count(),
                'tasa_exito' => $validadas->count() ? (int) round($validadas->whereIn('resultado', HistorialRutas::EXITOSAS)->count() / $validadas->count() * 100) : null,
                'horas_revision' => $horasRevision !== null ? round($horasRevision, 1) : null,
                'en_cola' => $filas->sum('en_cola'),
                'rutas' => $rutasPeriodo->count(),
                'rutas_cumplidas' => $rutasPeriodo->where('estado', 'cumplida')->count(),
                'paradas' => $rutasPeriodo->sum('total'),
                'paradas_hechas' => $rutasPeriodo->sum('hechas'),
                'domicilios_validados' => DB::table('sinotifico_domicilios')->where('validado_en_campo', true)->count(),
            ],
            'alertas' => $this->alertas($filas),
            'notificadores' => $filas->sortByDesc(fn ($f) => [$f['fuera_de_plazo'], $f['ultimo_dia'], $f['pendientes']])->values(),
            // Desglose por sede si hay más de una a la vista (Directivo, o un Delegado de región).
            'sedes' => ! $sede && $filas->pluck('sede')->unique()->count() > 1 ? $this->porSede($filas) : [],
        ];
    }

    /** Lo que el supervisor debería atender primero. */
    private function alertas(Collection $filas): array
    {
        $alertas = [];
        foreach ($filas as $f) {
            if ($f['fuera_de_plazo'] > 0) {
                $alertas[] = ['tipo' => 'fuera_de_plazo', 'nivel' => 'peligro', 'notificador_id' => $f['id'],
                    'texto' => "{$f['nombre']} tiene {$f['fuera_de_plazo']} ".($f['fuera_de_plazo'] === 1 ? 'visita fuera de plazo' : 'visitas fuera de plazo').'.'];
            }
            $sinSenal = $f['visto_at'] && CarbonImmutable::parse($f['visto_at'])->lt(now()->subHours(self::HORAS_SIN_ENVIAR));
            if ($f['en_cola'] > 0 && $sinSenal) {
                $alertas[] = ['tipo' => 'sin_enviar', 'nivel' => 'aviso', 'notificador_id' => $f['id'],
                    'texto' => "{$f['nombre']} tiene {$f['en_cola']} ".($f['en_cola'] === 1 ? 'captura' : 'capturas').' sin enviar y no da señal desde hace más de '.self::HORAS_SIN_ENVIAR.' horas.'];
            }
            if ($f['ruta_hoy'] && $f['ruta_hoy']['estatus'] !== Ruta::APROBADA) {
                $alertas[] = ['tipo' => 'ruta_sin_aprobar', 'nivel' => 'aviso', 'notificador_id' => $f['id'],
                    'texto' => "La ruta de hoy de {$f['nombre']} sigue sin aprobar: no la ve en su app."];
            } elseif (! $f['ruta_hoy'] && $f['pendientes'] > 0 && now()->isWeekday()) {
                $alertas[] = ['tipo' => 'sin_ruta', 'nivel' => 'info', 'notificador_id' => $f['id'],
                    'texto' => "{$f['nombre']} no tiene ruta para hoy ({$f['pendientes']} visitas pendientes)."];
            }
        }
        $orden = ['peligro' => 0, 'aviso' => 1, 'info' => 2];
        usort($alertas, fn ($a, $b) => $orden[$a['nivel']] <=> $orden[$b['nivel']]);

        return $alertas;
    }

    private function porSede(Collection $filas): array
    {
        return $filas->groupBy('sede')->map(fn ($g, $sede) => [
            'sede' => $sede,
            'notificadores' => $g->count(),
            'pendientes' => $g->sum('pendientes'),
            'fuera_de_plazo' => $g->sum('fuera_de_plazo'),
            'capturas' => $g->sum('capturas'),
            'exitosas' => $g->sum('exitosas'),
            'devueltas' => $g->sum('devueltas'),
        ])->sortBy('sede')->values()->all();
    }

    /** Por revisar en este momento (no sólo las del periodo). */
    private function porRevisar(array $sedes): int
    {
        return DB::table('sinotifico_diligencias')->where('estatus', 'por_revisar')
            ->whereIn('sede', $sedes)->count();
    }
}
