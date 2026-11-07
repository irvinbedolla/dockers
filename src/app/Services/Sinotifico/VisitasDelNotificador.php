<?php

namespace App\Services\Sinotifico;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Arma la lista de trabajo de un notificador a partir de `seer_citados`.
 *
 * En SICONCILIO una misma solicitud suele tener varios citados en el mismo
 * domicilio (la empresa o persona y "quien o quienes resulten responsables
 * de la fuente de empleo ubicada en…"). El notificador hace una sola visita
 * para todos, así que aquí se agrupan por solicitud + domicilio.
 */
class VisitasDelNotificador
{
    public function __construct(private readonly User $notificador) {}

    /** @return Collection<int, array<string, mixed>> */
    public function pendientes(?CarbonImmutable $hoy = null): Collection
    {
        $hoy = ($hoy ?? CarbonImmutable::now())->startOfDay();

        $filas = DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->join('audiencias as a', 'a.id', '=', 'c.audiencia_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->where('c.id_notificador', $this->notificador->id)
            ->whereIn('c.estatus', config('sinotifico.estatus_pendientes'))
            ->whereIn('c.notificacion', config('sinotifico.notifica_el_centro'))
            ->whereDate('a.fecha', '>=', $hoy->toDateString())
            // Lo ya enviado desde la app espera revisión: no se vuelve a mostrar.
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('sinotifico_diligencia_citado as dc')
                ->join('sinotifico_diligencias as d', 'd.id', '=', 'dc.diligencia_id')
                ->whereColumn('dc.citado_id', 'c.id')
                ->where('d.estatus', 'por_revisar'))
            ->orderBy('a.fecha')
            ->orderBy('a.hora')
            ->orderBy('c.id')
            ->limit(500)
            ->get([
                'c.id', 'c.id_solicitud', 'c.estatus', 'c.tipo_persona', 'c.nombre', 'c.primer_apellido',
                'c.segundo_apellido', 'c.tipo_vialidad', 'c.calle', 'c.n_ext', 'c.n_int', 'c.colonia', 'c.cp',
                'c.calle1', 'c.calle2', 'c.referencia', 'c.tipo_notificacion', 'c.estado_citado',
                'g.NUE as expediente', 'g.delegacion as sede',
                'a.id as audiencia_id', 'a.fecha as audiencia_fecha', 'a.hora as audiencia_hora',
                'a.folio_audiencia',
                'm.nombre as municipio', 'e.nombre as estado',
            ]);

        if ($filas->isEmpty()) {
            return collect();
        }

        $sede = $this->notificador->delegacion ?: $filas->first()->sede;
        $ultimaAudiencia = CarbonImmutable::parse($filas->max('audiencia_fecha'));
        $calendario = new CalendarioHabil($sede, $hoy->subDays(60), $ultimaAudiencia);
        $devueltas = $this->devueltas($filas->pluck('id'));

        return $filas
            ->groupBy(fn ($f) => $f->id_solicitud.'|'.$this->claveDomicilio($f))
            ->map(fn (Collection $grupo) => $this->visita($grupo, $calendario, $hoy)
                + ['devuelta' => $grupo->pluck('id')->map(fn ($id) => $devueltas[$id] ?? null)->filter()->sortByDesc('fecha')->first()])
            ->sortBy([
                ['plazo.fecha_limite', 'asc'],
                ['audiencia.fecha', 'asc'],
                ['audiencia.hora', 'asc'],
            ])
            ->values();
    }

    /** Diligencias por revisar que el notificador ya envió. */
    public function enRevision(): int
    {
        return DB::table('sinotifico_diligencias')
            ->where('notificador_id', $this->notificador->id)
            ->where('estatus', 'por_revisar')
            ->count();
    }

    /**
     * Última diligencia devuelta por el supervisor para cada citado, para
     * mostrarle al notificador qué corregir.
     *
     * @return array<int, array{comentario: ?string, fecha: string}>
     */
    private function devueltas(Collection $citadoIds): array
    {
        return DB::table('sinotifico_diligencia_citado as dc')
            ->join('sinotifico_diligencias as d', 'd.id', '=', 'dc.diligencia_id')
            ->whereIn('dc.citado_id', $citadoIds)
            ->where('d.estatus', 'devuelta')
            ->orderBy('d.revisada_at')
            ->get(['dc.citado_id', 'd.comentario_revision', 'd.revisada_at'])
            ->mapWithKeys(fn ($r) => [(int) $r->citado_id => [
                'comentario' => $r->comentario_revision,
                'fecha' => CarbonImmutable::parse($r->revisada_at)->toIso8601String(),
            ]])
            ->all();
    }

    /** Citados que el notificador cerró hoy (cualquier resultado final). */
    public function finalizadasHoy(?CarbonImmutable $hoy = null): int
    {
        $hoy = ($hoy ?? CarbonImmutable::now())->startOfDay();

        return DB::table('seer_citados')
            ->where('id_notificador', $this->notificador->id)
            ->whereNotIn('estatus', config('sinotifico.estatus_pendientes'))
            ->whereDate('updated_at', $hoy->toDateString())
            ->count();
    }

    private function visita(Collection $grupo, CalendarioHabil $calendario, CarbonImmutable $hoy): array
    {
        $f = $grupo->first();
        $audiencia = CarbonImmutable::parse($f->audiencia_fecha)->startOfDay();
        $limite = $calendario->restarHabiles($audiencia, config('sinotifico.dias_habiles_anticipacion'));
        $restantes = $calendario->habilesEntre($hoy, $limite);

        // En fin de semana o día inhábil, un límite de ayer da 0 días hábiles de
        // diferencia; aun así ya pasó.
        if ($limite->lt($hoy)) {
            $restantes = min($restantes, -1);
        }

        return [
            // El citado de id menor identifica la visita; es estable mientras no se reasigne.
            'id' => (int) $grupo->min('id'),
            'solicitud_id' => (int) $f->id_solicitud,
            'expediente' => $f->expediente,
            'tipo' => $f->tipo_notificacion ?: 'Citatorio',
            'audiencia' => [
                'id' => (int) $f->audiencia_id,
                'fecha' => $audiencia->toDateString(),
                'hora' => substr((string) $f->audiencia_hora, 0, 5),
                'folio' => $f->folio_audiencia,
            ],
            'plazo' => [
                'fecha_limite' => $limite->toDateString(),
                'dias_habiles_restantes' => $restantes,
                'estado' => match (true) {
                    $restantes < 0 => 'fuera_de_plazo',
                    $restantes === 0 => 'ultimo_dia',
                    $restantes <= 2 => 'proximo',
                    default => 'en_plazo',
                },
            ],
            'domicilio' => [
                'texto' => self::textoDomicilio($f),
                'tipo_vialidad' => $f->tipo_vialidad,
                'calle' => $f->calle,
                'numero_exterior' => $f->n_ext,
                'numero_interior' => $f->n_int,
                'colonia' => $f->colonia,
                'cp' => $f->cp,
                'municipio' => $f->municipio,
                'estado' => $f->estado,
                'entre_calles' => collect([$f->calle1, $f->calle2])->filter()->implode(' y ') ?: null,
                'referencia' => $this->limpio($f->referencia),
                // Fuera del estado normalmente se notifica por exhorto, no en ruta.
                'fuera_del_estado' => (int) $f->estado_citado !== config('sinotifico.estado_id'),
            ],
            'citados' => $grupo->map(fn ($c) => [
                'id' => (int) $c->id,
                'nombre' => $this->nombre($c),
                'tipo_persona' => $c->tipo_persona,
                // "Quien o quienes resulten responsables…": se notifica junto con el principal.
                'generico' => str_starts_with(mb_strtoupper(trim((string) $c->nombre)), 'QUIEN O QUIENES'),
                'estatus' => $c->estatus,
            ])->values()->all(),
        ];
    }

    private function nombre(object $c): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $c->nombre, $c->primer_apellido, $c->segundo_apellido,
        ]))));
    }

    private function claveDomicilio(object $f): string
    {
        return mb_strtoupper(preg_replace('/[^\p{L}\p{N}]+/u', '', implode('', [
            $f->tipo_vialidad, $f->calle, $f->n_ext, $f->n_int, $f->colonia, $f->cp, $f->municipio,
        ])));
    }

    /** Domicilio en una línea; $f trae columnas de seer_citados y municipio/estado por nombre. */
    public static function textoDomicilio(object $f): string
    {
        $limpio = fn (?string $v) => trim((string) $v) === '' ? null : trim((string) $v);
        $numero = $limpio($f->n_ext);
        $interior = $limpio($f->n_int);

        $partes = [
            trim(implode(' ', array_filter([$f->tipo_vialidad, $f->calle, $numero]))).($interior ? ", int. {$interior}" : ''),
            $f->colonia ? "col. {$f->colonia}" : null,
            $f->cp ? "C.P. {$f->cp}" : null,
            $f->municipio,
            $f->estado,
        ];

        return implode(', ', array_filter($partes));
    }

    private function limpio(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
