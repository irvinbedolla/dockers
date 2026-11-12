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
 * Caso: "desde el 9 de diciembre, las audiencias de Sahuayo las lleva X en
 * virtual". Lo NUEVO lo resuelve la vigencia (y la exclusividad) de la agenda
 * por sede (SedesConciliador, ObtenerAudiencia). Esto es lo YA AGENDADO: las
 * audiencias pendientes de esa sede en el periodo, de los conciliadores que
 * se elijan, pasan al conciliador destino.
 *
 * Cada audiencia se revisa contra la agenda del destino:
 *   - que tenga esa sede en su agenda ese día (y vigente);
 *   - que sea uno de sus días y la hora caiga en su horario de esa sede;
 *   - que no tenga ese día bloqueado (inhábil propio o permiso);
 *   - que no tenga ya otra audiencia a esa hora (en cualquier sede);
 *   - que no se le junten dos de este mismo traspaso a la misma hora.
 * Las que pasan se mueven. Para las que chocan se decide una por una:
 *   - se quedan con su conciliador (lo de siempre);
 *   - pasan al destino de todos modos (se le empalman o quedan fuera de su horario);
 *   - pasan a otro conciliador de la sede que esté libre a esa hora.
 * Dos audiencias del destino a la misma hora se aceptan; tres o más hay que
 * confirmarlas expresamente (EMPALME_MAXIMO).
 *
 * Se cambia audiencias.id_conciliador (y la sala) y seer_general.conciliador_id.
 * Fecha y hora no cambian. Queda en la bitácora de Administración.
 */
class TraspasoAudiencias
{
    public const TIPO_BITACORA = 'traspaso_audiencias';

    /** Audiencias del destino a la misma hora que se aceptan sin confirmar. */
    public const EMPALME_MAXIMO = 2;

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
     * objetos con id, fecha, hora, slot ("Y-m-d|H:i"), nue, folio,
     * conciliador_id, conciliador, pasa (bool), motivos (clave => texto) y
     * existentes (expedientes que el destino ya tiene a esa hora).
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

        [$primera, $ultima] = [substr((string) $audiencias->first()->fecha, 0, 10), substr((string) $audiencias->last()->fecha, 0, 10)];
        $ocupado  = self::ocupacion([$destino->id], $primera, $ultima);
        $bloqueos = self::bloqueos([$destino->id], $primera, $ultima)->get($destino->id, collect());
        $tomadas  = [];

        return $audiencias->map(function ($a) use ($sede, $destino, $ocupado, $bloqueos, &$tomadas) {
            $fecha = substr((string) $a->fecha, 0, 10);
            $hora  = AgendaAudiencias::hora((string) $a->hora);
            $slot  = $fecha.'|'.substr($hora, 0, 5);
            $motivos = [];

            $agenda = self::agendaEn($destino, $sede, $fecha);
            if (! $agenda) {
                $motivos['sin_sede'] = self::MOTIVOS['sin_sede'];
            } elseif (! self::enHorario($agenda, $fecha, $hora)) {
                $motivos['horario'] = self::MOTIVOS['horario'].' ('.SedesConciliador::diasTexto($agenda['dias']).' '.$agenda['inicio'].'–'.$agenda['fin'].')';
            }
            if (self::bloqueado($bloqueos, $fecha, $hora)) {
                $motivos['bloqueo'] = self::MOTIVOS['bloqueo'];
            }

            $existentes = $ocupado[$destino->id.'|'.$fecha.'|'.$hora] ?? [];
            if ($existentes) {
                $ya = $existentes[0];
                $motivos['ocupado'] = self::MOTIVOS['ocupado'].($ya->nue ? " (expediente {$ya->nue}, {$ya->sede})" : " ({$ya->sede})");
            } elseif (isset($tomadas[$slot])) {
                $motivos['doble'] = self::MOTIVOS['doble'].($tomadas[$slot] ? " (expediente {$tomadas[$slot]})" : '');
            }

            if (! $motivos) {
                $tomadas[$slot] = $a->nue ?: $a->folio_audiencia;
            }

            return (object) [
                'id'             => (int) $a->id,
                'id_solicitud'   => (int) $a->id_solicitud,
                'fecha'          => $fecha,
                'hora'           => substr($hora, 0, 5),
                'slot'           => $slot,
                'nue'            => $a->nue ?: $a->folio_audiencia,
                'sala'           => $a->sala,
                'conciliador_id' => (int) $a->id_conciliador,
                'conciliador'    => self::nombre($a->conciliador) ?: 'Sin conciliador',
                'pasa'           => ! $motivos,
                'motivos'        => $motivos,
                'existentes'     => array_map(fn ($e) => $e->nue ?: 'audiencia #'.$e->id, $existentes),
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
     * Horarios donde, si todas las elegidas pasaran al destino, tendría más de
     * EMPALME_MAXIMO audiencias a la misma hora (contando las que ya tiene).
     * [['fecha', 'hora', 'total', 'expedientes' => [...]], …]
     */
    public static function saturados(Collection $revision): array
    {
        $out = [];
        foreach ($revision->groupBy('slot') as $slot => $filas) {
            $exp = array_merge($filas->first()->existentes, $filas->pluck('nue')->all());
            if (count($exp) > self::EMPALME_MAXIMO) {
                [$fecha, $hora] = explode('|', $slot);
                $out[] = ['fecha' => $fecha, 'hora' => $hora, 'total' => count($exp), 'expedientes' => $exp,
                    'ya' => count($filas->first()->existentes)];
            }
        }

        return $out;
    }

    /**
     * Para cada audiencia que choca, los OTROS conciliadores de la sede (los
     * que la atienden por su agenda de siempre o por sede, sin contar al
     * destino ni a quien ya la tiene) y si están libres a esa hora:
     * [id_audiencia => [['id', 'nombre', 'libre' => bool, 'motivo' => ?string], …]] (libres primero).
     */
    public static function opciones(string $sede, Collection $revision, User $destino): array
    {
        $choques = $revision->where('pasa', false);
        if ($choques->isEmpty()) {
            return [];
        }

        $candidatos = SedesConciliador::quienesAtienden($sede)->where('id', '<>', $destino->id)->values();
        if ($candidatos->isEmpty()) {
            return [];
        }

        $ids      = $candidatos->pluck('id')->map(fn ($id) => (int) $id)->all();
        $primera  = $choques->min('fecha');
        $ultima   = $choques->max('fecha');
        $ocupado  = self::ocupacion($ids, $primera, $ultima);
        $bloqueos = self::bloqueos($ids, $primera, $ultima);

        $out = [];
        foreach ($choques as $a) {
            $hora = AgendaAudiencias::hora($a->hora);
            $lista = [];
            foreach ($candidatos as $c) {
                if ((int) $c->id === $a->conciliador_id) {
                    continue;
                }
                $agenda = self::agendaEn($c, $sede, $a->fecha);
                $motivo = match (true) {
                    ! $agenda                                                      => "No atiende {$sede} ese día",
                    ! self::enHorario($agenda, $a->fecha, $hora)                   => 'Fuera de su horario',
                    self::bloqueado($bloqueos->get($c->id, collect()), $a->fecha, $hora) => 'Día bloqueado',
                    isset($ocupado[$c->id.'|'.$a->fecha.'|'.$hora])                => 'Ocupado a esa hora ('.($ocupado[$c->id.'|'.$a->fecha.'|'.$hora][0]->nue ?: 'otra audiencia').')',
                    default                                                        => null,
                };
                $lista[] = ['id' => (int) $c->id, 'nombre' => self::nombre($c->name), 'libre' => $motivo === null, 'motivo' => $motivo];
            }
            usort($lista, fn ($x, $y) => [! $x['libre'], $x['nombre']] <=> [! $y['libre'], $y['nombre']]);
            $out[$a->id] = $lista;
        }

        return $out;
    }

    /**
     * Mueve las audiencias con el candado de agenda tomado y todo revisado de
     * nuevo en ese momento. Las que pasan van al destino; las que chocan según
     * $decisiones[id]: 'dejar' (o nada), 'destino' u el id de otro conciliador
     * (sólo si sigue libre a esa hora).
     *
     * Si el destino quedaría con más de EMPALME_MAXIMO a la misma hora y no se
     * confirmó, no mueve nada y devuelve 'error' con esos horarios.
     *
     * @return array{error: ?string, saturados: array, movidas: int, quedaron: int, filas: array}
     */
    public static function aplicar(string $sede, string $desde, ?string $hasta, User $destino, array $origenes, array $decisiones, bool $confirmarEmpalmes, string $motivo): array
    {
        return AgendaAudiencias::conBloqueo($sede, function () use ($sede, $desde, $hasta, $destino, $origenes, $decisiones, $confirmarEmpalmes, $motivo) {
            $revision = self::revisar($sede, $desde, $hasta, $destino, $origenes);
            $opciones = self::opciones($sede, $revision, $destino);

            // Plan: id de audiencia => conciliador nuevo (null = se queda).
            $plan = [];
            $usados = [];
            $avisos = [];
            foreach ($revision as $a) {
                $d = (string) ($decisiones[$a->id] ?? 'dejar');
                $nuevo = null;
                if ($a->pasa || $d === 'destino') {
                    $nuevo = $destino->id;
                } elseif (ctype_digit($d)) {
                    $c = collect($opciones[$a->id] ?? [])->firstWhere('id', (int) $d);
                    if ($c && $c['libre'] && ! isset($usados[$c['id'].'|'.$a->slot])) {
                        $nuevo = $c['id'];
                        $usados[$c['id'].'|'.$a->slot] = true;
                    } else {
                        $avisos[] = "{$a->nue}: el conciliador elegido ya no está libre a esa hora; se quedó con {$a->conciliador}.";
                    }
                }
                $plan[$a->id] = $nuevo;
            }

            // Cuántas tendría el destino en cada horario.
            $saturados = [];
            foreach ($revision->groupBy('slot') as $slot => $filas) {
                $exp = array_merge($filas->first()->existentes,
                    $filas->filter(fn ($a) => $plan[$a->id] === $destino->id)->pluck('nue')->all());
                if (count($exp) > self::EMPALME_MAXIMO) {
                    [$fecha, $hora] = explode('|', $slot);
                    $saturados[] = ['fecha' => $fecha, 'hora' => $hora, 'total' => count($exp), 'expedientes' => $exp];
                }
            }
            if ($saturados && ! $confirmarEmpalmes) {
                return ['error' => 'No se traspasó nada: '.$destino->name.' quedaría con '.(self::EMPALME_MAXIMO + 1)
                    .' o más audiencias a la misma hora. Revisa esos horarios y confírmalo, o asígnalas a otro conciliador.',
                    'saturados' => $saturados, 'movidas' => 0, 'quedaron' => $revision->count(), 'filas' => []];
            }

            $nombres = User::whereIn('id', array_filter(array_unique($plan)))->pluck('name', 'id');
            $salaFuera = in_array($sede, SedesConciliador::SUBSEDE, true) ? SedesConciliador::SALA_FUERA : null;

            $filas = [];
            $movidas = DB::transaction(function () use ($revision, $plan, $sede, $salaFuera, $destino, $nombres, &$filas) {
                $n = 0;
                foreach ($revision as $a) {
                    $nuevo = $plan[$a->id];
                    $ok = false;
                    if ($nuevo) {
                        $sala = SedesConciliador::sala($nuevo, $sede) ?? $salaFuera ?? $a->sala;
                        // Sólo si sigue pendiente y con el mismo conciliador (nadie la movió mientras).
                        $ok = DB::table('audiencias')
                            ->where('id', $a->id)->where('id_conciliador', $a->conciliador_id)->where('estatus', 'Pendiente')
                            ->update(['id_conciliador' => $nuevo, 'sala' => $sala, 'updated_at' => now()]) > 0;
                        if ($ok) {
                            DB::table('seer_general')->where('id', $a->id_solicitud)->where('conciliador_id', $a->conciliador_id)
                                ->update(['conciliador_id' => $nuevo]);
                            $n++;
                        }
                    }
                    $filas[] = [
                        'fecha' => $a->fecha, 'hora' => $a->hora, 'nue' => $a->nue,
                        'antes' => $a->conciliador,
                        'ahora' => $ok ? self::nombre($nombres[$nuevo] ?? '') : $a->conciliador,
                        'nota'  => match (true) {
                            ! $ok && $nuevo         => 'Ya no estaba pendiente o la movieron: no se tocó',
                            ! $ok                   => 'Se queda: '.implode('; ', $a->motivos),
                            $a->pasa                => '',
                            $nuevo === $destino->id => 'Pasó de todos modos: '.implode('; ', $a->motivos),
                            default                 => 'Reasignada: estaba libre a esa hora',
                        },
                        'estado' => ! $ok ? 'queda' : ($nuevo === $destino->id ? ($a->pasa ? 'pasa' : 'empalme') : 'otro'),
                    ];
                }

                return $n;
            });

            if ($movidas) {
                self::bitacora($sede, $desde, $hasta, $destino, collect($filas), $motivo);
            }

            return ['error' => null, 'saturados' => $saturados, 'movidas' => $movidas,
                'quedaron' => $revision->count() - $movidas, 'filas' => $filas, 'avisos' => $avisos];
        });
    }

    /**
     * La agenda del conciliador en esa sede vigente ese día, con el horario
     * de cada día: ['dias' => [...], 'inicio', 'fin', 'horas' => [dia => [inicio, fin]], 'modalidad', 'exclusiva', 'vigencia'].
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

        if (SedesConciliador::configurado($conciliador->id)) {
            return null;
        }

        foreach (SedesConciliador::sedes($conciliador) as $s) {
            if ($s['sede'] === $sede) {
                return $s + ['horas' => array_fill_keys($s['dias'], [$s['inicio'], $s['fin']]), 'vigencia' => null, 'exclusiva' => false];
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

    /** Audiencias que ocupan a esos conciliadores en el rango: ["id|Y-m-d|H:i:s" => [{id, nue, sede}, …]]. */
    private static function ocupacion(array $ids, string $primera, string $ultima): array
    {
        $out = [];
        if (! $ids) {
            return $out;
        }

        DB::table('audiencias as a')
            ->leftJoin('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->whereIn('a.id_conciliador', $ids)
            ->whereBetween('a.fecha', [$primera, $ultima])
            ->whereNotIn('a.estatus', AgendaAudiencias::LIBERAN)
            ->get(['a.id', 'a.id_conciliador', 'a.fecha', 'a.hora', 'a.delegacion', 'g.NUE AS nue'])
            ->each(function ($a) use (&$out) {
                $out[(int) $a->id_conciliador.'|'.substr((string) $a->fecha, 0, 10).'|'.AgendaAudiencias::hora((string) $a->hora)][]
                    = (object) ['id' => (int) $a->id, 'nue' => $a->nue, 'sede' => $a->delegacion];
            });

        return $out;
    }

    /** Días inhábiles propios y permisos de esos conciliadores, agrupados por id. */
    private static function bloqueos(array $ids, string $primera, string $ultima): Collection
    {
        return DiasInhabiles::whereIn('user_id', $ids)
            ->whereIn('tipo', ['Todos', 'Audiencias', 'Bloqueo por permiso'])
            ->where('fecha_inicio', '<=', $ultima)
            ->where('fecha_final', '>=', $primera)
            ->get(['user_id', 'fecha_inicio', 'fecha_final', 'descripcion', 'horario_inicio', 'horario_final'])
            ->groupBy('user_id');
    }

    private static function bloqueado(Collection $bloqueos, string $fecha, string $hora): bool
    {
        return $bloqueos->contains(fn ($b) => $b->fecha_inicio <= $fecha && $b->fecha_final >= $fecha
            && ($b->descripcion === 'Inhabil'
                || ($b->descripcion === 'No inhabil' && $b->horario_inicio <= $hora && $b->horario_final >= $hora)));
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

    private static function nombre(?string $nombre): string
    {
        return $nombre ? Str::title(mb_strtolower(trim($nombre))) : '';
    }

    private static function bitacora(string $sede, string $desde, ?string $hasta, User $destino, Collection $filas, string $motivo): void
    {
        $movidas = $filas->where('estado', '!=', 'queda');
        $deQuien = $movidas->groupBy('antes')->map->count()->map(fn ($n, $nombre) => "{$nombre} ({$n})")->implode(', ');
        $aQuien  = $movidas->groupBy('ahora')->map->count()->map(fn ($n, $nombre) => "{$nombre} ({$n})")->implode(', ');
        $periodo = mb_strtolower((string) SedesConciliador::vigenciaTexto($desde, $hasta));
        $quedaron = $filas->where('estado', 'queda')->count();

        HistorialAdministracion::create([
            'tipo'          => self::TIPO_BITACORA,
            'tabla'         => 'audiencias',
            'registro_id'   => $destino->id,
            'NUE'           => null,
            'delegacion'    => $sede,
            'datos_antes'   => ['Audiencias' => $deQuien, 'Expedientes' => '—'],
            'datos_despues' => [
                'Audiencias'  => $movidas->count()." de {$sede} ({$periodo}): {$aQuien}"
                    .($quedaron ? "; {$quedaron} se quedaron con su conciliador" : ''),
                'Expedientes' => $movidas->map(fn ($f) => $f['nue'].' → '.$f['ahora'])->implode(', '),
            ],
            'motivo'        => $motivo,
            'user_id'       => auth()->id(),
            'user_nombre'   => auth()->user()->name ?? null,
            'ip'            => request()->ip(),
        ]);
    }
}
