<?php

namespace App\Services\Sinotifico;

use App\Models\Sinotifico\Parada;
use App\Models\Sinotifico\Ruta;
use App\Models\User;
use App\Support\Sinotifico\Acceso;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Arma, edita y aprueba la ruta del día de un notificador.
 *
 * La propuesta toma sus visitas pendientes dentro de Michoacán, primero las
 * de plazo más cercano (hasta `ruta_max_paradas`), las ubica en el mapa y
 * las ordena por cercanía saliendo de la oficina de la sede. Cualquier cambio
 * deja la ruta en borrador: la app sólo la muestra cuando se aprueba.
 */
class PlanificadorRutas
{
    public function __construct(private readonly Geocodificador $geo) {}

    /** El supervisor puede ver y editar rutas de este notificador. */
    public static function puedeSupervisar(User $supervisor, User $notificador): bool
    {
        return match (Acceso::supervisa($supervisor)) {
            'todas' => true,
            'sede' => $notificador->delegacion === $supervisor->delegacion,
            default => false,
        };
    }

    public function ruta(User $notificador, string $fecha): ?Ruta
    {
        return Ruta::where('notificador_id', $notificador->id)->whereDate('fecha', $fecha)->first();
    }

    public function proponer(User $notificador, string $fecha, User $autor): Ruta
    {
        $visitas = $this->pendientes($notificador)
            ->reject(fn ($v) => $v['domicilio']['fuera_del_estado'])
            ->sortBy('plazo.fecha_limite')
            ->take((int) config('sinotifico.ruta_max_paradas', 20))
            ->values();

        if ($visitas->isEmpty()) {
            throw new HttpException(422, 'Este notificador no tiene visitas pendientes dentro de Michoacán para armar una ruta.');
        }

        return DB::transaction(function () use ($notificador, $fecha, $autor, $visitas) {
            $ruta = $this->ruta($notificador, $fecha) ?? new Ruta([
                'notificador_id' => $notificador->id,
                'fecha' => $fecha,
                'sede' => $notificador->delegacion,
                'creada_por' => $autor->id,
            ]);
            $ruta->fill(['estatus' => Ruta::BORRADOR, 'editada_por' => $autor->id, 'aprobada_por' => null, 'aprobada_at' => null])->save();

            $ordenadas = $this->porCercania($ruta, $visitas->map(fn ($v) => $this->conUbicacion($v))->all());
            $this->reemplazarParadas($ruta, $ordenadas);

            return $ruta->refresh();
        });
    }

    /**
     * Guarda el orden que dejó el supervisor (lista de ids de visita). Puede
     * quitar paradas o agregar visitas pendientes que no estaban.
     *
     * @param  list<int>  $visitaIds
     */
    public function guardar(Ruta $ruta, array $visitaIds, User $autor): Ruta
    {
        $notificador = User::findOrFail($ruta->notificador_id);
        $pendientes = $this->pendientes($notificador)->keyBy('id');
        $actuales = $ruta->paradas->keyBy('visita_id');

        $filas = [];
        foreach (array_values(array_unique(array_map('intval', $visitaIds))) as $id) {
            if ($pendientes->has($id)) {
                $filas[] = $this->conUbicacion($pendientes[$id]);
            } elseif ($actuales->has($id)) {
                // Ya no está pendiente (p. ej. ya se capturó): se conserva tal cual.
                $p = $actuales[$id];
                $filas[] = ['visita' => null, 'parada' => $p, 'lat' => null, 'lng' => null];
            } else {
                throw new HttpException(422, "La visita {$id} ya no está pendiente para este notificador. Recarga la página.");
            }
        }

        return DB::transaction(function () use ($ruta, $filas, $autor) {
            $ruta->fill(['estatus' => Ruta::BORRADOR, 'editada_por' => $autor->id, 'aprobada_por' => null, 'aprobada_at' => null])->save();
            $this->reemplazarParadas($ruta, $filas);

            return $ruta->refresh();
        });
    }

    public function ordenarPorCercania(Ruta $ruta, User $autor): Ruta
    {
        $ruta->load('paradas');
        $ids = collect($this->porCercania($ruta, $ruta->paradas->map(fn (Parada $p) => [
            'id' => $p->visita_id,
            'lat' => $this->coordenadas($p)['lat'],
            'lng' => $this->coordenadas($p)['lng'],
        ])->all()))->pluck('id')->all();

        return $this->guardar($ruta, $ids, $autor);
    }

    public function aprobar(Ruta $ruta, User $autor): Ruta
    {
        if ($ruta->paradas()->count() === 0) {
            throw new HttpException(422, 'La ruta no tiene paradas.');
        }
        $ruta->fill(['estatus' => Ruta::APROBADA, 'aprobada_por' => $autor->id, 'aprobada_at' => now()])->save();

        return $ruta;
    }

    /** Todo lo que necesita el mapa (el de la app y el de SICONCILIO son el mismo). */
    public function presentar(Ruta $ruta): array
    {
        $ruta->load('paradas');
        $notificador = User::find($ruta->notificador_id);
        $pendientes = $notificador ? $this->pendientes($notificador)->keyBy('id') : collect();
        $estados = $this->estadoDeCitados($ruta->paradas->flatMap(fn ($p) => $p->citados)->all());

        $paradas = $ruta->paradas->map(function (Parada $p) use ($pendientes, $estados) {
            $vigente = $pendientes->get($p->visita_id);
            $resumen = $vigente ? $this->resumen($vigente) : $p->resumen;
            $c = $this->coordenadas($p);

            $estado = 'pendiente';
            if (! $vigente) {
                $de = collect($p->citados)->map(fn ($id) => $estados[$id] ?? 'cerrada');
                $estado = $de->contains('en_revision') ? 'en_revision' : ($de->contains('reasignada') ? 'reasignada' : 'cerrada');
            }

            return [
                'orden' => $p->orden,
                'visita_id' => $p->visita_id,
                'estado' => $estado,
                'domicilio_id' => $p->domicilio_id,
                'lat' => $c['lat'],
                'lng' => $c['lng'],
                'ubicacion' => $c['fuente'],
            ] + $resumen;
        })->values();

        return [
            'id' => $ruta->id,
            'fecha' => $ruta->fecha->toDateString(),
            'estatus' => $ruta->estatus,
            'sede' => $ruta->sede,
            'notificador' => ['id' => $ruta->notificador_id, 'nombre' => $notificador?->name],
            'oficina' => config('sinotifico.oficinas')[$ruta->sede] ?? null,
            'distancia_km' => $ruta->distancia_km,
            'minutos_estimados' => $ruta->minutos_estimados,
            'aprobada_por' => $ruta->aprobada_por ? DB::table('users')->where('id', $ruta->aprobada_por)->value('name') : null,
            'aprobada_at' => $ruta->aprobada_at?->toIso8601String(),
            'paradas' => $paradas,
        ];
    }

    /** Visitas pendientes que no están en la ruta (para agregarlas desde el editor). */
    public function fuera(Ruta $ruta): Collection
    {
        $notificador = User::findOrFail($ruta->notificador_id);
        $en = $ruta->paradas->pluck('visita_id')->all();

        return $this->pendientes($notificador)
            ->reject(fn ($v) => in_array($v['id'], $en, true))
            ->map(fn ($v) => ['visita_id' => $v['id']] + $this->resumen($v))
            ->values();
    }

    /* ---------------------------------------------------------------- */

    private function pendientes(User $notificador): Collection
    {
        return (new VisitasDelNotificador($notificador))->pendientes();
    }

    /** Lo que se guarda y se muestra de cada visita. */
    private function resumen(array $v): array
    {
        $principal = collect($v['citados'])->firstWhere('generico', false) ?? $v['citados'][0];

        return [
            'expediente' => $v['expediente'],
            'tipo' => $v['tipo'],
            'principal' => $principal['nombre'],
            'citados_n' => count($v['citados']),
            'domicilio' => $v['domicilio']['texto'],
            'referencia' => $v['domicilio']['referencia'],
            'fuera_del_estado' => $v['domicilio']['fuera_del_estado'],
            'audiencia' => ['fecha' => $v['audiencia']['fecha'], 'hora' => $v['audiencia']['hora']],
            'plazo' => $v['plazo'],
        ];
    }

    /** Busca (o recupera) la ubicación del domicilio de la visita. */
    private function conUbicacion(array $v): array
    {
        $dom = $this->geo->ubicar($v['domicilio']);

        return ['visita' => $v, 'id' => $v['id'], 'domicilio_id' => $dom->id, 'lat' => $dom->lat, 'lng' => $dom->lng];
    }

    private function coordenadas(Parada $p): array
    {
        $d = $p->domicilio_id ? DB::table('sinotifico_domicilios')->find($p->domicilio_id) : null;

        return [
            'lat' => $d?->lat !== null ? (float) $d->lat : null,
            'lng' => $d?->lng !== null ? (float) $d->lng : null,
            'fuente' => $d?->lat !== null ? $d->fuente : null,
        ];
    }

    /**
     * Vecino más cercano desde la oficina de la sede. Las visitas sin
     * ubicación quedan al final, en el orden en que venían.
     */
    private function porCercania(Ruta $ruta, array $puntos): array
    {
        $oficina = config('sinotifico.oficinas')[$ruta->sede] ?? null;
        $con = array_values(array_filter($puntos, fn ($p) => $p['lat'] !== null));
        $sin = array_values(array_filter($puntos, fn ($p) => $p['lat'] === null));

        $actual = $oficina ?? ($con[0] ?? null);
        $orden = [];
        while ($con) {
            usort($con, fn ($a, $b) => self::km($actual, $a) <=> self::km($actual, $b));
            $actual = array_shift($con);
            $orden[] = $actual;
        }

        return array_merge($orden, $sin);
    }

    private function reemplazarParadas(Ruta $ruta, array $filas): void
    {
        $ruta->paradas()->delete();

        $oficina = config('sinotifico.oficinas')[$ruta->sede] ?? null;
        $anterior = $oficina;
        $km = 0.0;

        foreach (array_values($filas) as $i => $f) {
            if ($f['visita'] ?? null) {
                $v = $f['visita'];
                Parada::create([
                    'ruta_id' => $ruta->id,
                    'orden' => $i + 1,
                    'visita_id' => $v['id'],
                    'solicitud_id' => $v['solicitud_id'],
                    'citados' => collect($v['citados'])->pluck('id')->all(),
                    'domicilio_id' => $f['domicilio_id'],
                    'resumen' => $this->resumen($v),
                ]);
                $punto = ['lat' => $f['lat'], 'lng' => $f['lng']];
            } else {
                $p = $f['parada'];
                Parada::create(['ruta_id' => $ruta->id, 'orden' => $i + 1] + $p->only(['visita_id', 'solicitud_id', 'citados', 'domicilio_id', 'resumen']));
                $punto = $this->coordenadas($p);
            }

            if ($punto['lat'] !== null && $anterior) {
                $km += self::km($anterior, $punto);
                $anterior = $punto;
            } elseif ($punto['lat'] !== null) {
                $anterior = $punto;
            }
        }

        // Distancia en línea recta × 1.35 para aproximar el recorrido por calles.
        $kmCalle = round($km * 1.35, 1);
        $ruta->forceFill([
            'distancia_km' => $kmCalle,
            'minutos_estimados' => (int) round($kmCalle / max(1, config('sinotifico.ruta_kmh', 25)) * 60
                + count($filas) * config('sinotifico.ruta_minutos_por_parada', 15)),
        ])->save();
    }

    /** @return array<int, string> en_revision | reasignada | cerrada por citado */
    private function estadoDeCitados(array $ids): array
    {
        if (! $ids) {
            return [];
        }
        $enRevision = DB::table('sinotifico_diligencia_citado as dc')
            ->join('sinotifico_diligencias as d', 'd.id', '=', 'dc.diligencia_id')
            ->whereIn('dc.citado_id', $ids)->where('d.estatus', 'por_revisar')
            ->pluck('dc.citado_id')->map(fn ($x) => (int) $x)->all();

        return DB::table('seer_citados')->whereIn('id', $ids)->get(['id', 'estatus'])
            ->mapWithKeys(fn ($c) => [(int) $c->id => match (true) {
                in_array((int) $c->id, $enRevision, true) => 'en_revision',
                in_array($c->estatus, config('sinotifico.estatus_pendientes'), true) => 'reasignada',
                default => 'cerrada',
            }])->all();
    }

    public static function km(array $a, array $b): float
    {
        $rad = M_PI / 180;
        $dLat = ($b['lat'] - $a['lat']) * $rad;
        $dLng = ($b['lng'] - $a['lng']) * $rad;
        $h = sin($dLat / 2) ** 2 + cos($a['lat'] * $rad) * cos($b['lat'] * $rad) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($h)));
    }
}
