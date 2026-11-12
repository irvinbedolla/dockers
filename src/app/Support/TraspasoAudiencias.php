<?php

namespace App\Support;

use App\Models\DiasInhabiles;
use App\Models\HistorialAdministracion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Traspaso de audiencias ya agendadas (Administración → Conciliadores →
 * Traspasar audiencias, sólo Super Usuario).
 *
 * Caso: "desde el 9 de diciembre, las audiencias de Zitácuaro las lleva X en
 * virtual". La parte de lo NUEVO la hace la vigencia de la agenda por sede
 * (SedesConciliador, ObtenerAudiencia). Esto es lo YA AGENDADO: las
 * audiencias pendientes de esa sede en el periodo, de los conciliadores que
 * se elijan, pasan al conciliador destino.
 *
 * Antes de mover nada se revisa cada audiencia contra la agenda del destino:
 *   - que tenga esa sede en su agenda ese día (y vigente);
 *   - que sea uno de sus días y la hora caiga en su horario de esa sede;
 *   - que no tenga ese día bloqueado (inhábil propio o permiso);
 *   - que no tenga ya otra audiencia a esa hora (en cualquier sede);
 *   - que no se le junten dos de este mismo traspaso a la misma hora.
 * Las que pasan se mueven; las que chocan se quedan con su conciliador,
 * salvo que se pida pasarlas también (se le encimarían o quedarían fuera de
 * su horario).
 *
 * Se cambia audiencias.id_conciliador (y la sala) y seer_general.conciliador_id.
 * Fecha y hora no cambian. Queda en la bitácora de Administración.
 */
class TraspasoAudiencias
{
    public const TIPO_BITACORA = 'traspaso_audiencias';

    /** Motivos por los que una audiencia no le cabe al destino. */
    public const MOTIVOS = [
        'sin_sede' => 'No tiene esta sede en su agenda ese día',
        'horario'  => 'Fuera de su horario en esta sede',
        'bloqueo'  => 'Tiene ese día bloqueado (inhábil o permiso)',
        'ocupado'  => 'Ya tiene otra audiencia a esa hora',
        'doble'    => 'Otra audiencia de este traspaso es a la misma hora',
    ];

    private const DIA = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes'];

    /** Conciliadores con audiencias pendientes en esa sede y periodo (sin el destino), con cuántas. */
    public static function origenes(string $sede, string $desde, ?string $hasta, int $destino): Collection
    {
        return self::base($sede, $desde, $hasta, $destino)
            ->groupBy('a.id_conciliador', 'u.name')
            ->selectRaw('a.id_conciliador AS id, u.name AS nombre, COUNT(*) AS total')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($o) => (object) ['id' => (int) $o->id, 'nombre' => $o->nombre ?: 'Sin conciliador (#'.$o->id.')', 'total' => (int) $o->total]);
    }

    /**
     * Cada audiencia de esos conciliadores con su veredicto para el destino:
     * objetos con id, fecha, hora, nue, folio, conciliador_id, conciliador,
     * pasa (bool) y motivos (claves de MOTIVOS => texto).
     */
    public static function revisar(string $sede, string $desde, ?string $hasta, User $destino, array $origenes): Collection
    {
        if (! $origenes) {
            return collect();
        }

        $audiencias = self::base($sede, $desde, $hasta, $destino->id)
            ->whereIn('a.id_conciliador', $origenes)
            ->orderBy('a.fecha')->orderBy('a.hora')->orderBy('a.id')
            ->get(['a.id', 'a.id_solicitud', 'a.fecha', 'a.hora', 'a.sala', 'a.id_conciliador', 'a.folio_audiencia', 'u.name AS conciliador', 'g.NUE AS nue']);

        if ($audiencias->isEmpty()) {
            return collect();
        }

        $primera = substr((string) $audiencias->first()->fecha, 0, 10);
        $ultima  = substr((string) $audiencias->last()->fecha, 0, 10);

        // Lo que ya tiene el destino en esas fechas, en cualquier sede.
        $ocupado = DB::table('audiencias as a')
            ->leftJoin('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->where('a.id_conciliador', $destino->id)
            ->whereBetween('a.fecha', [$primera, $ultima])
            ->whereNotIn('a.estatus', AgendaAudiencias::LIBERAN)
            ->get(['a.fecha', 'a.hora', 'a.delegacion', 'g.NUE AS nue'])
            ->keyBy(fn ($a) => substr((string) $a->fecha, 0, 10).'|'.AgendaAudiencias::hora((string) $a->hora));

        // Sus bloqueos (días inhábiles propios y permisos).
        $bloqueos = DiasInhabiles::where('user_id', $destino->id)
            ->whereIn('tipo', ['Todos', 'Audiencias', 'Bloqueo por permiso'])
            ->where('fecha_inicio', '<=', $ultima)
            ->where('fecha_final', '>=', $primera)
            ->get(['fecha_inicio', 'fecha_final', 'descripcion', 'horario_inicio', 'horario_final']);

        $tomadas = [];

        return $audiencias->map(function ($a) use ($sede, $destino, $ocupado, $bloqueos, &$tomadas) {
            $fecha = substr((string) $a->fecha, 0, 10);
            $hora  = AgendaAudiencias::hora((string) $a->hora);
            $clave = $fecha.'|'.$hora;
            $motivos = [];

            $agenda = self::agendaEn($destino, $sede, $fecha);
            if (! $agenda) {
                $motivos['sin_sede'] = self::MOTIVOS['sin_sede'];
            } elseif (! self::enHorario($agenda, $fecha, $hora)) {
                $motivos['horario'] = self::MOTIVOS['horario'].' ('.SedesConciliador::diasTexto($agenda['dias']).' '.$agenda['inicio'].'–'.$agenda['fin'].')';
            }

            $bloqueado = $bloqueos->contains(fn ($b) => $b->fecha_inicio <= $fecha && $b->fecha_final >= $fecha
                && ($b->descripcion === 'Inhabil'
                    || ($b->descripcion === 'No inhabil' && $b->horario_inicio <= $hora && $b->horario_final >= $hora)));
            if ($bloqueado) {
                $motivos['bloqueo'] = self::MOTIVOS['bloqueo'];
            }

            if ($ya = $ocupado->get($clave)) {
                $motivos['ocupado'] = self::MOTIVOS['ocupado'].($ya->nue ? " (expediente {$ya->nue}, {$ya->delegacion})" : " ({$ya->delegacion})");
            } elseif (isset($tomadas[$clave])) {
                $motivos['doble'] = self::MOTIVOS['doble'].($tomadas[$clave] ? " (expediente {$tomadas[$clave]})" : '');
            }

            if (! $motivos) {
                $tomadas[$clave] = $a->nue ?: $a->folio_audiencia;
            }

            return (object) [
                'id'             => (int) $a->id,
                'id_solicitud'   => (int) $a->id_solicitud,
                'fecha'          => $fecha,
                'hora'           => substr($hora, 0, 5),
                'nue'            => $a->nue,
                'folio'          => $a->folio_audiencia,
                'sala'           => $a->sala,
                'conciliador_id' => (int) $a->id_conciliador,
                'conciliador'    => $a->conciliador ? Str::title(mb_strtolower($a->conciliador)) : 'Sin conciliador',
                'pasa'           => ! $motivos,
                'motivos'        => $motivos,
            ];
        });
    }

    /** Totales de una revisión: total, pasan, chocan y cuántas por motivo. */
    public static function resumen(Collection $revision): array
    {
        $porMotivo = array_fill_keys(array_keys(self::MOTIVOS), 0);
        foreach ($revision->where('pasa', false) as $a) {
            foreach (array_keys($a->motivos) as $m) {
                $porMotivo[$m]++;
            }
        }

        return [
            'total'     => $revision->count(),
            'pasan'     => $revision->where('pasa', true)->count(),
            'chocan'    => $revision->where('pasa', false)->count(),
            'porMotivo' => array_filter($porMotivo),
        ];
    }

    /**
     * Mueve las audiencias (las que pasan, o todas si $incluirChoques) con el
     * candado de agenda tomado y la revisión hecha de nuevo en ese momento.
     *
     * @return array{movidas: int, quedaron: int}
     */
    public static function aplicar(string $sede, string $desde, ?string $hasta, User $destino, array $origenes, bool $incluirChoques, string $motivo): array
    {
        return AgendaAudiencias::conBloqueo($sede, function () use ($sede, $desde, $hasta, $destino, $origenes, $incluirChoques, $motivo) {
            $revision = self::revisar($sede, $desde, $hasta, $destino, $origenes);
            $mover = $revision->filter(fn ($a) => $a->pasa || $incluirChoques)->values();
            $sala = SedesConciliador::sala($destino->id, $sede)
                ?? (in_array($sede, SedesConciliador::SUBSEDE, true) ? SedesConciliador::SALA_FUERA : null);

            $movidas = DB::transaction(function () use ($mover, $destino, $sala) {
                $n = 0;
                foreach ($mover as $a) {
                    // Sólo si sigue pendiente y con el mismo conciliador (nadie la movió mientras).
                    $n += DB::table('audiencias')
                        ->where('id', $a->id)
                        ->where('id_conciliador', $a->conciliador_id)
                        ->where('estatus', 'Pendiente')
                        ->update(['id_conciliador' => $destino->id, 'sala' => $sala ?? $a->sala, 'updated_at' => now()]);
                    DB::table('seer_general')
                        ->where('id', $a->id_solicitud)
                        ->where('conciliador_id', $a->conciliador_id)
                        ->update(['conciliador_id' => $destino->id]);
                }

                return $n;
            });

            if ($movidas) {
                self::bitacora($sede, $desde, $hasta, $destino, $mover, $revision->count() - $mover->count(), $motivo);
            }

            return ['movidas' => $movidas, 'quedaron' => $revision->count() - $movidas];
        });
    }

    /**
     * La agenda del conciliador en esa sede vigente ese día, con el horario
     * de cada día: ['dias' => [...], 'inicio', 'fin', 'horas' => [dia => [inicio, fin]], 'modalidad', 'vigencia'].
     */
    public static function agendaEn(User $conciliador, string $sede, string $fecha): ?array
    {
        $fila = SedesConciliador::configurado($conciliador->id) ? SedesConciliador::filaDeSede($conciliador->id, $sede) : null;

        if ($fila) {
            if (! $fila->vigenteEn($fecha)) {
                return null;
            }
            $horas = [];
            foreach ($fila->dias() as $d) {
                $horas[$d] = [substr((string) $fila->{$d.'_inicio'}, 0, 5), substr((string) $fila->{$d.'_final'}, 0, 5)];
            }
            $primero = $fila->dias()[0] ?? 'lunes';

            return [
                'dias'      => $fila->dias(),
                'inicio'    => substr((string) $fila->{$primero.'_inicio'}, 0, 5),
                'fin'       => substr((string) $fila->{$primero.'_final'}, 0, 5),
                'horas'     => $horas,
                'modalidad' => $fila->tipo,
                'exclusiva' => (bool) ($fila->exclusiva ?? false),
                'vigencia'  => SedesConciliador::vigenciaTexto(
                    $fila->vigente_desde ? substr((string) $fila->vigente_desde, 0, 10) : null,
                    $fila->vigente_hasta ? substr((string) $fila->vigente_hasta, 0, 10) : null
                ),
            ];
        }

        foreach (SedesConciliador::sedes($conciliador) as $s) {
            if ($s['sede'] === $sede && ! $s['por_sede']) {
                return $s + ['horas' => array_fill_keys($s['dias'], [$s['inicio'], $s['fin']]), 'vigencia' => null];
            }
        }

        return null;
    }

    private static function enHorario(array $agenda, string $fecha, string $hora): bool
    {
        $dia = self::DIA[Carbon::parse($fecha)->dayOfWeekIso] ?? null;
        if (! $dia || ! isset($agenda['horas'][$dia])) {
            return false;
        }
        [$inicio, $fin] = $agenda['horas'][$dia];
        $h = substr($hora, 0, 5);

        return $inicio <= $h && $h <= $fin;
    }

    /** Pendientes de esa sede en el periodo, sin las del destino. */
    private static function base(string $sede, string $desde, ?string $hasta, int $destino)
    {
        // audiencias.delegacion a veces va sin acento ("Zitacuaro").
        $nombres = array_values(array_unique([$sede, Str::ascii($sede)]));

        return DB::table('audiencias as a')
            ->leftJoin('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador')
            ->where('a.estatus', 'Pendiente')
            ->whereIn('a.delegacion', $nombres)
            ->where('a.fecha', '>=', $desde)
            ->when($hasta, fn ($q) => $q->where('a.fecha', '<=', $hasta))
            ->where('a.id_conciliador', '<>', $destino);
    }

    private static function bitacora(string $sede, string $desde, ?string $hasta, User $destino, Collection $movidas, int $quedaron, string $motivo): void
    {
        $deQuien = $movidas->groupBy('conciliador')->map->count()
            ->map(fn ($n, $nombre) => "{$nombre} ({$n})")->implode(', ');
        $periodo = SedesConciliador::vigenciaTexto($desde, $hasta);

        HistorialAdministracion::create([
            'tipo'          => self::TIPO_BITACORA,
            'tabla'         => 'audiencias',
            'registro_id'   => $destino->id,
            'NUE'           => null,
            'delegacion'    => $sede,
            'datos_antes'   => ['Audiencias' => $deQuien, 'Expedientes' => '—'],
            'datos_despues' => [
                'Audiencias'  => $movidas->count()." de {$sede} (".mb_strtolower((string) $periodo).') pasan a '.$destino->name
                    .($quedaron ? "; {$quedaron} se quedaron con su conciliador" : ''),
                'Expedientes' => $movidas->map(fn ($a) => $a->nue ?: $a->folio)->filter()->implode(', '),
            ],
            'motivo'        => $motivo,
            'user_id'       => auth()->id(),
            'user_nombre'   => auth()->user()->name ?? null,
            'ip'            => request()->ip(),
        ]);
    }
}
