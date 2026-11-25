<?php

namespace App\Support;

use App\Models\Audiencias;
use App\Models\SeerCitados;
use App\Models\SeerPerGeneral;
use App\Models\SeerSolicitante;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Datos rápidos del Inicio para el conciliador, en la tarjeta del saludo,
 * como los de recepción (AccesosRecepcion) y los del orientador
 * (AccesosOrientador):
 *
 *   1. Mi siguiente audiencia: hora, sala, partes, quién ya llegó (Llegadas
 *      hoy), lo que anotaron recepción y él, y el semáforo de "¿Está lista
 *      la audiencia?" (RevisionAudiencia) en corto;
 *   2. Mi día: sus audiencias de hoy por cómo van, quién está en el Centro y
 *      las que se pasaron de hora sin cerrarse;
 *   3. Lo que sigue hoy: las siguientes, con quién ya llegó;
 *   4. Próximos días: mañana y la semana, y las que tienen citados todavía
 *      sin notificar.
 *
 * hoy() (1 a 3) se refresca cada 30 s desde el Inicio (InicioController::
 * conciliador); proximos() (4) se arma una vez al cargar.
 */
class AccesosConciliador
{
    private const SIGUEN = 4;

    /** Días hacia adelante de "Próximos días" (sin contar hoy). */
    private const DIAS = 7;

    /** Cuántas audiencias con citados sin notificar se listan. */
    private const AVISOS = 4;

    /** Una audiencia abierta que empezó hace más que esto deja de ser "la siguiente" si hay otra. */
    private const EN_CURSO_MINUTOS = 120;

    public static function aplica(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasRole('Conciliador') && LlegadasHoy::migrado();
    }

    /** Tarjetas 1 a 3 (también es la respuesta JSON del refresco). */
    public static function hoy(User $usuario): array
    {
        $ahora = now();
        $hoy   = $ahora->toDateString();

        $mias = DB::table('audiencias')
            ->where('id_conciliador', $usuario->id)
            ->whereDate('fecha', $hoy)
            ->get(['id', 'id_solicitud', 'estatus', 'delegacion']);

        // Llegadas y partes de cada una, de las sedes donde tiene audiencias hoy.
        $registros = $mias->isEmpty() ? collect() : LlegadasHoy::delDia($mias->pluck('delegacion')->filter()->unique()->values()->all(), $hoy)
            ->where('tipo', 'audiencia')
            ->where('conciliador_id', (int) $usuario->id)
            ->values();
        $solicitud = $mias->pluck('id_solicitud', 'id');

        // Minutos desde su hora (negativo: todavía no llega la hora).
        $desde = fn (array $r) => $r['hora'] ? (int) floor(Carbon::parse($hoy.' '.$r['hora'])->diffInMinutes($ahora, false)) : 0;

        $abiertas = $registros->where('abierto', true)->sortBy('hora')->values();
        $siguiente = $abiertas->first(fn ($r) => $desde($r) < self::EN_CURSO_MINUTOS) ?? $abiertas->first();

        $reagendadas = $mias->whereIn('estatus', LlegadasHoy::REAGENDADAS)->count();
        $partes = $abiertas->sum(fn ($r) => count($r['partes']));

        return [
            'generado'  => $ahora->toIso8601String(),
            'siguiente' => $siguiente ? self::tarjeta($siguiente, (int) ($solicitud[$siguiente['id']] ?? 0), $desde($siguiente)) : null,
            'dia'       => [
                'total'       => $mias->count(),
                'pendientes'  => $mias->where('estatus', 'Pendiente')->count(),
                'concluidas'  => $mias->where('estatus', '!=', 'Pendiente')->count() - $reagendadas,
                'convenio'    => $mias->where('estatus', 'Conciliacion')->count(),
                'reagendadas' => $reagendadas,
                'enCentro'    => $abiertas->sum('llegaron'),
                'partes'      => $partes,
                'pasadas'     => $abiertas->filter(fn ($r) => $desde($r) > LlegadasHoy::TOLERANCIA_MINUTOS)->count(),
            ],
            'siguen' => $abiertas
                ->reject(fn ($r) => $siguiente && $r['id'] === $siguiente['id'])
                ->take(self::SIGUEN)
                ->map(fn ($r) => [
                    'hora'     => $r['hora'],
                    'lugar'    => $r['lugar'],
                    'nue'      => $r['nue'],
                    'llegaron' => $r['llegaron'],
                    'total'    => count($r['partes']),
                    'completa' => $r['completa'],
                    'pasada'   => $desde($r) > LlegadasHoy::TOLERANCIA_MINUTOS,
                    'url'      => self::urlRevisar((int) ($solicitud[$r['id']] ?? 0), $r['id']),
                ])->values()->all(),
            'urls' => [
                'llegadas' => route('llegadas.index', ['tipo' => 'audiencia']),
            ],
        ];
    }

    /** Tarjeta 1: la audiencia que sigue. */
    private static function tarjeta(array $r, int $idSolicitud, int $minutos): array
    {
        $obs = [];
        foreach (['obs' => 'Recepción', 'obsConc' => 'Tú'] as $campo => $quien) {
            $o = $r[$campo] ?? null;
            if ($o) {
                $obs[] = ['quien' => $quien, 'textos' => array_values(array_filter(array_merge($o['etiquetas'] ?? [], [$o['texto'] ?? null])))];
            }
        }

        return [
            'id'         => $r['id'],
            'hora'       => $r['hora'],
            'lugar'      => $r['lugar'],
            'nue'        => $r['nue'],
            'minutos'    => $minutos,
            'reagendada' => ! empty($r['historial']['previas']),
            'partes'     => array_map(fn ($p) => ['rol' => $p['rol'], 'nombre' => \Illuminate\Support\Str::title(mb_strtolower($p['nombre'])), 'llegada' => $p['llegada']], $r['partes']),
            'llegaron'   => $r['llegaron'],
            'completa'   => $r['completa'],
            'obs'        => $obs,
            'revision'   => $idSolicitud ? self::revision($idSolicitud, (int) $r['id']) : null,
            'urls'       => [
                'revisar' => self::urlRevisar($idSolicitud, $r['id']),
                'iniciar' => $idSolicitud ? url('solicitud/iniciar/'.$idSolicitud).'?audiencia_id='.$r['id'] : null,
            ],
        ];
    }

    /** El semáforo de "¿Está lista la audiencia?", en corto: cuántos y los primeros pendientes. */
    private static function revision(int $idSolicitud, int $idAudiencia): ?array
    {
        $general = SeerPerGeneral::find($idSolicitud);
        $audiencia = Audiencias::find($idAudiencia);
        if (! $general || ! $audiencia) {
            return null;
        }

        try {
            $rev = RevisionAudiencia::armar(
                $general,
                SeerSolicitante::where('id_solicitud', $idSolicitud)->get(),
                self::citados($idSolicitud, $idAudiencia),
                $audiencia
            );
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        $pendientes = array_values(array_filter($rev['puntos'], fn ($p) => in_array($p['nivel'], ['falta', 'aviso'], true)));

        return [
            'lista'  => $rev['lista'],
            'falta'  => $rev['cuenta']['falta'],
            'aviso'  => $rev['cuenta']['aviso'],
            'puntos' => array_map(fn ($p) => ['nivel' => $p['nivel'], 'texto' => $p['parte'].': '.$p['texto']], array_slice($pendientes, 0, 3)),
        ];
    }

    /**
     * Citados de una audiencia, con la misma regla que Revisar audiencia
     * (SeerController::solicitud_audiencia_revisar): los ligados a ella y, si
     * es la primera de la solicitud, también los que no traen audiencia.
     */
    private static function citados(int $idSolicitud, int $idAudiencia): Collection
    {
        $primera = (int) DB::table('audiencias')->where('id_solicitud', $idSolicitud)->min('id');

        return SeerCitados::where('id_solicitud', $idSolicitud)
            ->where(function ($q) use ($idAudiencia, $primera) {
                $q->where('audiencia_id', $idAudiencia);
                if ($primera === $idAudiencia) {
                    $q->orWhereNull('audiencia_id');
                }
            })
            ->get();
    }

    /** Tarjeta 4: mañana, la semana y las que tienen citados sin notificar. */
    public static function proximos(User $usuario): array
    {
        $manana = now()->addDay()->toDateString();
        $hasta  = now()->addDays(self::DIAS)->toDateString();

        $audiencias = DB::table('audiencias as a')
            ->leftJoin('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->where('a.id_conciliador', $usuario->id)
            ->where('a.estatus', 'Pendiente')
            ->whereDate('a.fecha', '>=', $manana)
            ->whereDate('a.fecha', '<=', $hasta)
            ->orderBy('a.fecha')->orderBy('a.hora')
            ->get(['a.id', 'a.id_solicitud', 'a.fecha', 'a.hora', 'a.sala', 'g.NUE']);

        $solicitudes = $audiencias->pluck('id_solicitud')->unique()->values();
        $primeras = $solicitudes->isEmpty() ? collect() : DB::table('audiencias')
            ->whereIn('id_solicitud', $solicitudes)
            ->groupBy('id_solicitud')
            ->select('id_solicitud', DB::raw('MIN(id) as primera'))
            ->pluck('primera', 'id_solicitud');
        $citados = $solicitudes->isEmpty() ? collect() : DB::table('seer_citados')
            ->whereIn('id_solicitud', $solicitudes)
            ->get(['id_solicitud', 'audiencia_id', 'estatus', 'notificacion', 'tipo_notificacion'])
            ->groupBy('id_solicitud');

        $conPendientes = [];
        foreach ($audiencias as $a) {
            $esPrimera = (int) ($primeras[$a->id_solicitud] ?? 0) === (int) $a->id;
            $suyos = collect($citados[$a->id_solicitud] ?? [])
                ->filter(fn ($c) => (int) $c->audiencia_id === (int) $a->id || ($esPrimera && $c->audiencia_id === null))
                // Los de multa no se notifican, y el citatorio que entrega el solicitante no pasa por notificaciones.
                ->reject(fn ($c) => ($c->tipo_notificacion ?? null) === 'Multa' || ($c->notificacion ?? null) === 'Trabajador');
            $fallidas = $suyos->filter(fn ($c) => in_array((string) $c->estatus, RevisionAudiencia::NO_NOTIFICADO, true))->count();
            $sin = $suyos->reject(fn ($c) => in_array((string) $c->estatus, RevisionAudiencia::NOTIFICADO, true))->count();
            if ($sin > 0) {
                $fecha = Carbon::parse($a->fecha);
                $conPendientes[] = [
                    'cuando'   => ($fecha->isTomorrow() ? 'Mañana' : \Illuminate\Support\Str::ucfirst($fecha->locale('es')->isoFormat('ddd D/MM'))).' · '.substr((string) $a->hora, 0, 5),
                    'nue'      => (string) $a->NUE,
                    'sin'      => $sin,
                    'fallidas' => $fallidas,
                    'url'      => self::urlRevisar((int) $a->id_solicitud, (int) $a->id),
                ];
            }
        }

        return [
            'manana'        => $audiencias->filter(fn ($a) => Carbon::parse($a->fecha)->toDateString() === $manana)->count(),
            'semana'        => $audiencias->count(),
            'dias'          => self::DIAS,
            'conPendientes' => count($conPendientes),
            'lista'         => array_slice($conPendientes, 0, self::AVISOS),
            'urls'          => [
                'agenda' => \Illuminate\Support\Facades\Route::has('agenda') ? route('agenda') : null,
            ],
        ];
    }

    private static function urlRevisar(int $idSolicitud, int $idAudiencia): ?string
    {
        return $idSolicitud && \Illuminate\Support\Facades\Route::has('solicitud_audiencia')
            ? route('solicitud_audiencia', ['id' => $idSolicitud]).'?isAudiencia=Si&audiencia_id='.$idAudiencia
            : null;
    }
}
