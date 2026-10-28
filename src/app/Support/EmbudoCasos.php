<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Vista general del seguimiento (Panel estadístico → Seguimiento de casos):
 * cuántos casos llegan a cada etapa y cuántos días pasan entre etapas.
 *
 *   Desde la cita       citas de solicitud del periodo → asistieron →
 *                       tienen solicitud ligada.
 *   Desde la solicitud  solicitudes del periodo → confirmadas (audiencia
 *                       agendada) → con audiencia celebrada → resueltas →
 *                       con convenio → pagadas.
 *
 * Los tiempos son medianas en días naturales (la mediana no se va por los
 * casos atípicos como el promedio). La unión cita ↔ solicitud depende de
 * seer_general.recepcion_id (PrellenadoCita::ligar), así que "desde la
 * cita" crece conforme se usa el flujo nuevo.
 */
class EmbudoCasos
{
    /** Audiencias que ya se celebraron (no siguen agendadas ni se movieron). */
    private const SIN_CELEBRAR = ['Pendiente', 'Reagendada'];

    private const CONVENIO = ['Conciliacion', 'Reinstalacion'];

    public static function calcular(string $desde, string $hasta, ?string $sede): array
    {
        $variantes = $sede ? array_values(array_unique([$sede, Str::ascii($sede)])) : null;

        // ── Desde la cita ──────────────────────────────────────────────
        $citas = DB::table('recepcion as r')
            ->where('r.tipo', 'Solicitud')
            ->where('r.estatus', '!=', 'cancelada')
            ->whereBetween('r.fecha', [$desde, $hasta])
            ->when(FlujoCita::migrado(), fn ($q) => $q->where('r.es_prueba', 0))
            ->when($variantes, fn ($q) => $q->whereIn('r.delegacion', $variantes))
            ->get([
                'r.id', 'r.fecha', 'r.estatus', 'r.origen', 'r.created_at', 'r.delegacion',
                DB::raw('(SELECT MIN(g.fecha) FROM seer_general g WHERE g.recepcion_id = r.id) AS solicitud_fecha'),
            ]);

        $hoy = now()->toDateString();
        $pasadas = $citas->filter(fn ($c) => substr((string) $c->fecha, 0, 10) <= $hoy);
        $asistieron = $pasadas->filter(fn ($c) => in_array($c->estatus, ['confirmada', 'atendido'], true));
        $conSolicitud = $citas->filter(fn ($c) => $c->solicitud_fecha !== null);

        $desdeCita = [
            ['etiqueta' => 'Citas de solicitud', 'total' => $citas->count(), 'nota' => $citas->where('origen', 'linea')->count().' en línea · '.$citas->where('origen', '!=', 'linea')->count().' en ventanilla'],
            ['etiqueta' => 'Su cita ya pasó', 'total' => $pasadas->count(), 'nota' => ($citas->count() - $pasadas->count()).' todavía por venir'],
            ['etiqueta' => 'Asistieron', 'total' => $asistieron->count(), 'nota' => $pasadas->where('estatus', 'expirada')->count().' no se presentaron'],
            ['etiqueta' => 'Levantaron solicitud', 'total' => $conSolicitud->count(), 'nota' => 'Ligada a su cita'],
        ];

        // ── Desde la solicitud ─────────────────────────────────────────
        $sinCelebrar = "'".implode("','", self::SIN_CELEBRAR)."'";
        $solicitudes = DB::table('seer_general as g')
            ->whereIn('g.tipo_solicitud', ['1', '2'])
            ->whereBetween('g.fecha', [$desde, $hasta])
            ->when($variantes, fn ($q) => $q->whereIn('g.delegacion', $variantes))
            ->get([
                'g.id', 'g.fecha', 'g.fecha_confirmacion', 'g.fecha_terminacion', 'g.estatus', 'g.delegacion', 'g.recepcion_id',
                DB::raw("(SELECT MIN(a.fecha) FROM audiencias a WHERE a.id_solicitud = g.id AND a.estatus NOT IN ($sinCelebrar)) AS audiencia_fecha"),
                DB::raw("(SELECT MAX(p.fecha_conclucion) FROM pago_solicitud p WHERE p.id_solicitud = g.id AND p.estatus LIKE 'Pagado%') AS pago_fecha"),
                DB::raw('(SELECT r.fecha FROM recepcion r WHERE r.id = g.recepcion_id) AS cita_fecha'),
                DB::raw('(SELECT r.created_at FROM recepcion r WHERE r.id = g.recepcion_id) AS cita_creada'),
            ]);

        // Cada etapa cuenta sólo a quien pasó la anterior, para que el embudo
        // no "crezca" (hay solicitudes que se resuelven sin audiencia).
        $resuelta  = fn ($s) => in_array($s->estatus, SeguimientoCaso::RESUELTOS, true);
        $convenio  = fn ($s) => in_array($s->estatus, self::CONVENIO, true);
        $etapasSolicitud = function ($grupo) use ($resuelta, $convenio) {
            $conf = $grupo->filter(fn ($s) => $s->fecha_confirmacion !== null);
            $aud  = $conf->filter(fn ($s) => $s->audiencia_fecha !== null);
            $res  = $aud->filter($resuelta);
            $conv = $res->filter($convenio);

            return [
                'total'       => $grupo->count(),
                'confirmadas' => $conf->count(),
                'audiencia'   => $aud->count(),
                'resueltas'   => $res->count(),
                'convenio'    => $conv->count(),
                'pagadas'     => $conv->filter(fn ($s) => $s->pago_fecha !== null)->count(),
                'resueltasTodas' => $grupo->filter($resuelta)->count(),
                'conveniosTodos' => $grupo->filter($convenio)->count(),
            ];
        };
        $e = $etapasSolicitud($solicitudes);
        $sinAudiencia = $e['resueltasTodas'] - $e['resueltas'];

        $desdeSolicitud = [
            ['etiqueta' => 'Solicitudes', 'total' => $e['total'], 'nota' => $solicitudes->whereNotNull('recepcion_id')->count().' ligadas a su cita'],
            ['etiqueta' => 'Confirmadas', 'total' => $e['confirmadas'], 'nota' => 'Audiencia agendada y citatorios'],
            ['etiqueta' => 'Con audiencia celebrada', 'total' => $e['audiencia'], 'nota' => ''],
            ['etiqueta' => 'Resueltas en audiencia', 'total' => $e['resueltas'], 'nota' => $sinAudiencia ? $sinAudiencia.' más se resolvieron sin audiencia (rechazo, incompetencia, archivo…)' : ''],
            ['etiqueta' => 'Con convenio', 'total' => $e['convenio'], 'nota' => 'Convenio o reinstalación'],
            ['etiqueta' => 'Convenio pagado', 'total' => $e['pagadas'], 'nota' => 'Al menos un pago registrado'],
        ];

        // ── Tiempos (medianas en días) ─────────────────────────────────
        $dias = fn ($a, $b) => ($a && $b) ? Carbon::parse(substr((string) $a, 0, 10))->diffInDays(Carbon::parse(substr((string) $b, 0, 10)), false) : null;

        $tiempos = [
            self::tiempo('De agendar a su cita', $citas->map(fn ($c) => $dias($c->created_at, $c->fecha))),
            self::tiempo('De su cita a la solicitud', $conSolicitud->map(fn ($c) => $dias($c->fecha, $c->solicitud_fecha))),
            self::tiempo('De la solicitud a la confirmación', $solicitudes->map(fn ($s) => $dias($s->fecha, $s->fecha_confirmacion))),
            self::tiempo('De la solicitud a su primera audiencia', $solicitudes->map(fn ($s) => $dias($s->fecha, $s->audiencia_fecha))),
            self::tiempo('De la solicitud a que se resolvió', $solicitudes->map(fn ($s) => in_array($s->estatus, SeguimientoCaso::RESUELTOS, true) ? $dias($s->fecha, $s->fecha_terminacion ?: $s->audiencia_fecha) : null)),
            self::tiempo('Del convenio al pago', $solicitudes->map(fn ($s) => in_array($s->estatus, self::CONVENIO, true) ? $dias($s->fecha_terminacion, $s->pago_fecha) : null)),
            self::tiempo('De agendar la cita a que se resolvió', $solicitudes->map(fn ($s) => in_array($s->estatus, SeguimientoCaso::RESUELTOS, true) ? $dias($s->cita_creada, $s->fecha_terminacion ?: $s->audiencia_fecha) : null)),
        ];

        // ── Por sede ───────────────────────────────────────────────────
        $porSede = $solicitudes->groupBy('delegacion')->map(function ($grupo, $nombre) use ($etapasSolicitud, $dias, $citas) {
            $x = $etapasSolicitud($grupo);
            $resolucion = self::mediana($grupo->map(fn ($s) => in_array($s->estatus, SeguimientoCaso::RESUELTOS, true) ? $dias($s->fecha, $s->fecha_terminacion ?: $s->audiencia_fecha) : null));

            return [
                'sede'       => (string) $nombre,
                'citas'      => $citas->where('delegacion', $nombre)->count(),
                'resueltas'  => $x['resueltasTodas'],
                'convenio'   => $x['conveniosTodos'],
                'pctConvenio'=> $x['resueltasTodas'] ? round($x['conveniosTodos'] * 100 / $x['resueltasTodas']) : null,
                'resolucion' => $resolucion,
            ] + $x;
        })->sortByDesc('total')->values()->all();

        return [
            'desdeCita'      => self::porcentajes($desdeCita),
            'desdeSolicitud' => self::porcentajes($desdeSolicitud),
            'tiempos'        => $tiempos,
            'porSede'        => $porSede,
        ];
    }

    /** Agrega % del total (primera etapa) y % que pasó desde la etapa anterior. */
    private static function porcentajes(array $etapas): array
    {
        $base = max(1, (int) ($etapas[0]['total'] ?? 0));
        $previo = null;

        foreach ($etapas as &$e) {
            $e['pct']   = $etapas[0]['total'] ? round($e['total'] * 100 / $base) : 0;
            $e['paso']  = $previo ? round($e['total'] * 100 / $previo) : null;
            $previo     = max(1, (int) $e['total']);
        }

        return $etapas;
    }

    private static function tiempo(string $etiqueta, $valores): array
    {
        $v = collect($valores)->filter(fn ($d) => $d !== null && $d >= 0)->values();

        return ['etiqueta' => $etiqueta, 'mediana' => self::mediana($v), 'casos' => $v->count()];
    }

    private static function mediana($valores): ?int
    {
        $v = collect($valores)->filter(fn ($d) => $d !== null && $d >= 0)->sort()->values();
        $n = $v->count();
        if (! $n) {
            return null;
        }

        return (int) round($n % 2 ? $v[intdiv($n, 2)] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2);
    }
}
