<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seguimiento del caso: la historia de una persona en el Centro, con fecha y
 * día, desde que agenda su cita (en línea o en ventanilla) hasta en qué
 * quedó su solicitud y el pago.
 *
 *   cita (recepcion) ── seer_general.recepcion_id ──> solicitud (seer_general)
 *     → notificación de los citados (seer_citados)
 *     → audiencias (audiencias, audiencia_llegadas, seer_conciliadores)
 *     → resultado (seer_general.estatus / fecha_terminacion)
 *     → cumplimiento (pago_solicitud)
 *
 * La cita y la solicitud se ligan al guardar (PrellenadoCita::ligar) o, para
 * las anteriores, con `php artisan citas:ligar-solicitudes` (aproximado).
 * Sólo de lectura: no cambia nada.
 */
class SeguimientoCaso
{
    /** seer_general.estatus → cómo se lee. */
    public const ESTATUS = [
        'Pendiente'       => 'Pendiente de validar',
        'Prevencion'      => 'En prevención',
        'Aceptado'        => 'Aceptada',
        'Confirmado'      => 'En trámite (audiencia agendada)',
        'Reagendada'      => 'Audiencia reagendada',
        'Conciliacion'    => 'Convenio',
        'No conciliacion' => 'Sin convenio (constancia de no conciliación)',
        'Archivada'       => 'Archivada',
        'Incompetencia'   => 'Incompetencia',
        'Incomparecencia' => 'Incomparecencia',
        'Desistimiento'   => 'Desistimiento',
        'Canalizada'      => 'Canalizada a otra institución',
        'Reinstalacion'   => 'Reinstalación',
        'Concluida'       => 'Concluida',
        'Rechazado'       => 'Rechazada',
        'Incumplimiento'  => 'Incumplimiento de convenio',
    ];

    /** Estatus con los que el caso ya se resolvió. */
    public const RESUELTOS = [
        'Conciliacion', 'No conciliacion', 'Archivada', 'Incompetencia', 'Incomparecencia',
        'Desistimiento', 'Canalizada', 'Reinstalacion', 'Concluida', 'Rechazado', 'Incumplimiento',
    ];

    /** Resultados favorables para la persona (tono verde). */
    public const FAVORABLES = ['Conciliacion', 'Reinstalacion', 'Concluida'];

    /** seer_citados.estatus que ya son notificación hecha o fallida. */
    private const NOTIFICADA = ['Notificada', 'Finalizado exitosamente', 'Recibe pero no firma', 'Exitosa por Instructivo', 'Notificada en Audiencia'];
    private const NO_NOTIFICADA = ['No exitosa se constituye', 'No exitosa no se constituye', 'Exhorto'];

    public const ETAPAS = [
        'cita'         => 'Cita',
        'solicitud'    => 'Solicitud',
        'notificacion' => 'Notificación',
        'audiencia'    => 'Audiencia',
        'resultado'    => 'Resultado',
        'cumplimiento' => 'Cumplimiento',
    ];

    /** Sedes que puede consultar (las mismas que su agenda), con variantes sin acento. */
    public static function sedes(User $usuario): array
    {
        $sedes = AgendaContexto::para($usuario)['sedes'] ?? [];

        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }

    /**
     * Casos que coinciden con lo buscado: folio de cita, NUE, CURP o nombre.
     * Primero las solicitudes (con su cita, si la tienen) y luego las citas
     * que todavía no tienen solicitud.
     *
     * @return Collection<int, array>
     */
    public static function buscar(string $texto, array $sedes): Collection
    {
        $texto = trim(preg_replace('/\s+/', ' ', $texto));
        if (mb_strlen($texto) < 3 || ! $sedes) {
            return collect();
        }

        $folio  = FolioCita::normalizar($texto);
        $curpN  = Curp::normalizar($texto);
        $curp   = strlen($curpN) === 18 ? $curpN : null;
        $nombre = ! $folio && ! $curp && ! preg_match('/\d/', $texto) && mb_strlen($texto) >= 4;
        $like   = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtoupper($texto)).'%';
        $nombreSub = '(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = g.id ORDER BY s.id LIMIT 1)';

        $solicitudes = DB::table('seer_general as g')
            ->leftJoin('recepcion as r', 'r.id', '=', 'g.recepcion_id')
            ->whereIn('g.delegacion', $sedes)
            ->where(function ($w) use ($folio, $curp, $nombre, $like) {
                $w->where('g.NUE', 'like', $like);
                if ($folio) {
                    $w->orWhere('r.codigo', $folio);
                }
                if ($curp) {
                    $w->orWhere('g.curp', $curp)
                      ->orWhereExists(fn ($q) => $q->selectRaw(1)->from('seer_solicitante as s')->whereColumn('s.id_solicitud', 'g.id')->where('s.curp', $curp));
                }
                if ($nombre) {
                    $w->orWhereExists(fn ($q) => $q->selectRaw(1)->from('seer_solicitante as s')->whereColumn('s.id_solicitud', 'g.id')->where('s.nombre', 'like', $like));
                }
            })
            ->orderByDesc('g.id')
            ->limit(25)
            ->get(['g.id', 'g.NUE', 'g.fecha', 'g.estatus', 'g.delegacion', 'r.codigo', 'r.consecutivo', DB::raw($nombreSub.' AS nombre')])
            ->map(fn ($s) => [
                'clave'    => 's'.$s->id,
                'tipo'     => 'solicitud',
                'titulo'   => self::nombre($s->nombre) ?: 'Sin nombre',
                'detalle'  => ($s->NUE ?: 'Sin NUE').($s->codigo ? ' · cita '.$s->codigo : '').' · '.$s->delegacion,
                'fecha'    => substr((string) $s->fecha, 0, 10),
                'estatus'  => self::ESTATUS[$s->estatus] ?? (string) $s->estatus,
                'resuelto' => in_array($s->estatus, self::RESUELTOS, true),
            ]);

        $citas = DB::table('recepcion as r')
            ->whereIn('r.delegacion', $sedes)
            ->when(FlujoCita::migrado(), fn ($q) => $q->where('r.es_prueba', 0))
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('seer_general as g')->whereColumn('g.recepcion_id', 'r.id'))
            ->where(function ($w) use ($folio, $curp, $nombre, $like) {
                $w->whereRaw('1 = 0');
                if ($folio) {
                    $w->orWhere('r.codigo', $folio);
                }
                if ($curp && FlujoCita::migrado()) {
                    $w->orWhere('r.curp', $curp);
                }
                if ($nombre) {
                    $w->orWhere('r.solicitante', 'like', $like);
                }
            })
            ->orderByDesc('r.fecha')->orderByDesc('r.id')
            ->limit(25)
            ->get(['r.id', 'r.codigo', 'r.consecutivo', 'r.fecha', 'r.tipo', 'r.estatus', 'r.delegacion', 'r.solicitante', 'r.origen'])
            ->map(fn ($c) => [
                'clave'    => 'c'.$c->id,
                'tipo'     => 'cita',
                'titulo'   => self::nombre($c->solicitante) ?: 'Sin nombre',
                'detalle'  => FolioCita::mostrar($c).' · '.$c->tipo.' · '.($c->origen === 'linea' ? 'en línea' : 'ventanilla').' · '.$c->delegacion,
                'fecha'    => substr((string) $c->fecha, 0, 10),
                'estatus'  => 'Cita '.$c->estatus.' · sin solicitud',
                'resuelto' => false,
            ]);

        return $solicitudes->concat($citas)->values();
    }

    /**
     * La historia completa de un caso ('s123' solicitud o 'c45' cita), o
     * null si no existe o no es de sus sedes.
     */
    public static function linea(string $clave, array $sedes): ?array
    {
        if (! preg_match('/^([sc])(\d+)$/', $clave, $m)) {
            return null;
        }

        $solicitud = $m[1] === 's'
            ? DB::table('seer_general')->where('id', (int) $m[2])->first()
            : DB::table('seer_general')->where('recepcion_id', (int) $m[2])->orderBy('id')->first();
        $citaId = $m[1] === 'c' ? (int) $m[2] : ($solicitud->recepcion_id ?? null);
        $cita   = $citaId ? DB::table('recepcion')->where('id', $citaId)->first() : null;

        if (! $solicitud && ! $cita) {
            return null;
        }
        $sede = (string) ($solicitud->delegacion ?? $cita->delegacion);
        if (! in_array($sede, $sedes, true) && ! in_array(Str::ascii($sede), $sedes, true)) {
            return null;
        }

        $eventos = collect();
        $hechas  = [];

        if ($cita) {
            self::eventosCita($cita, $eventos);
            $hechas['cita'] = true;
        }

        $persona = self::nombre($cita->solicitante ?? null);
        $curp    = $cita->curp ?? null;

        if ($solicitud) {
            $solicitante = DB::table('seer_solicitante')->where('id_solicitud', $solicitud->id)->orderBy('id')->first(['nombre', 'curp']);
            $persona = self::nombre($solicitante->nombre ?? null) ?: $persona;
            $curp    = ($solicitante->curp ?? null) ?: ($solicitud->curp ?? null) ?: $curp;
            $hechas['solicitud'] = true;
            self::eventosSolicitud($solicitud, $eventos, $hechas);
        }

        $eventos = $eventos->filter(fn ($e) => $e['cuando'] !== null)
            ->sortBy(fn ($e) => $e['cuando']->format('YmdHis').str_pad((string) $e['orden'], 3, '0', STR_PAD_LEFT))
            ->values();

        // Días desde el evento anterior (sólo entre los que ya pasaron).
        $ahora  = now();
        $previo = null;
        $eventos = $eventos->map(function ($e) use (&$previo, $ahora) {
            $e['futuro'] = $e['cuando']->gt($ahora);
            $e['dias']   = ($previo && ! $e['futuro']) ? (int) $previo->copy()->startOfDay()->diffInDays($e['cuando']->copy()->startOfDay()) : null;
            if (! $e['futuro']) {
                $previo = $e['cuando'];
            }

            return $e;
        });

        $pasados  = $eventos->where('futuro', false);
        $inicio   = $pasados->first()['cuando'] ?? null;
        $resuelto = $solicitud && in_array($solicitud->estatus, self::RESUELTOS, true);
        $fin      = $resuelto ? ($eventos->firstWhere('etapa', 'resultado')['cuando'] ?? ($pasados->last()['cuando'] ?? null)) : $ahora;

        return [
            'clave'     => $clave,
            'persona'   => $persona ?: 'Sin nombre',
            'curp'      => $curp,
            'nue'       => $solicitud->NUE ?? null,
            'folio'     => $cita ? FolioCita::mostrar($cita) : null,
            'sede'      => $sede,
            'estado'    => $solicitud
                ? (self::ESTATUS[$solicitud->estatus] ?? (string) $solicitud->estatus)
                : 'Cita '.($cita->estatus ?? '').', sin solicitud',
            'resuelto'  => $resuelto,
            'favorable' => $solicitud && in_array($solicitud->estatus, self::FAVORABLES, true),
            'vinculo'   => $solicitud->recepcion_vinculo ?? null,
            'dias'      => ($inicio && $fin) ? (int) $inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay()) : null,
            'etapas'    => collect(self::ETAPAS)->map(fn ($nombre, $k) => ['nombre' => $nombre, 'hecha' => ! empty($hechas[$k])])->all(),
            'eventos'   => $eventos->all(),
        ];
    }

    private static function eventosCita(object $c, Collection $eventos): void
    {
        $folio   = FolioCita::mostrar($c);
        $enLinea = $c->origen === 'linea';
        $hora    = substr((string) $c->hora, 0, 5);
        $fecha   = substr((string) $c->fecha, 0, 10);
        $aux     = self::usuarios([(int) $c->auxiliar])[(int) $c->auxiliar] ?? null;

        $eventos->push(self::evento('cita', $c->created_at, $enLinea ? 'Agendó su cita en línea' : 'Se registró su turno en ventanilla',
            $folio.' · '.$c->tipo.' para el '.self::fechaLarga($fecha).' a las '.$hora, $enLinea ? 'globe2' : 'person-vcard', 'neutro', 1));

        $eventos->push(self::evento('cita', $fecha.' '.$c->hora, 'Hora de su cita',
            implode(' · ', array_filter([$c->tipo, $c->lugar_auxiliar ?: null, $aux])), 'calendar-event', 'neutro', 2));

        if (! empty($c->confirmada_at ?? null)) {
            $eventos->push(self::evento('cita', $c->confirmada_at, 'Llegó y confirmó su cita', 'Escaneó su código QR en el Centro.', 'qr-code-scan', 'ok', 3));
        }
        if (! empty($c->llamado_at ?? null)) {
            $eventos->push(self::evento('cita', $c->llamado_at, 'Lo llamaron a su módulo', (string) $c->lugar_auxiliar, 'megaphone', 'neutro', 4));
        }
        if (! empty($c->atencion_inicio_at ?? null)) {
            $eventos->push(self::evento('cita', $c->atencion_inicio_at, 'Inició su atención', $aux ? 'Con '.$aux : '', 'person-check', 'neutro', 5));
        }
        if (! empty($c->atencion_fin_at ?? null)) {
            $min = ! empty($c->atencion_inicio_at) ? (int) round(Carbon::parse($c->atencion_inicio_at)->diffInSeconds(Carbon::parse($c->atencion_fin_at)) / 60) : null;
            $eventos->push(self::evento('cita', $c->atencion_fin_at, 'Terminó su atención', $min !== null ? 'Duró '.$min.' min.' : '', 'check2-circle', 'ok', 6));
        }
        if ($c->estatus === 'expirada') {
            $eventos->push(self::evento('cita', $fecha.' '.$c->hora, 'No se presentó a su cita', 'La cita quedó expirada.', 'person-x', 'alerta', 7));
        }
    }

    private static function eventosSolicitud(object $g, Collection $eventos, array &$hechas): void
    {
        $capturo = self::usuarios([(int) $g->user_id])[(int) $g->user_id] ?? null;
        $vinculo = [
            'curp'      => 'se ligó con su cita por la CURP',
            'historico' => 'ligada con su cita por la CURP (registro anterior, aproximado)',
        ][$g->recepcion_vinculo ?? ''] ?? null;

        $eventos->push(self::evento('solicitud', $g->created_at ?: $g->fecha, 'Se levantó su solicitud',
            implode(' · ', array_filter([
                $g->NUE ?: 'Sin NUE',
                (string) $g->tipo_solicitud === '2' ? 'patronal' : 'de la persona trabajadora',
                $capturo ? 'capturó '.$capturo : null,
                $vinculo,
            ])), 'file-earmark-text', 'neutro', 10));

        if (! empty($g->fecha_confirmacion)) {
            $eventos->push(self::evento('solicitud', substr((string) $g->fecha_confirmacion, 0, 10).' 23:58:00', 'Se confirmó la solicitud',
                'Se agendó su audiencia y se emitieron los citatorios.', 'patch-check', 'ok', 11, true));
        }

        // Notificación de cada citado.
        $citados = DB::table('seer_citados')->where('id_solicitud', $g->id)->orderBy('id')
            ->get(['id', 'nombre', 'primer_apellido', 'segundo_apellido', 'estatus', 'notificacion', 'id_notificador', 'asignado_at', 'fecha', 'updated_at']);
        $notificadores = self::usuarios($citados->pluck('id_notificador')->filter()->map(fn ($i) => (int) $i)->all());
        foreach ($citados as $ct) {
            $quien = self::nombre(trim($ct->nombre.' '.$ct->primer_apellido.' '.$ct->segundo_apellido)) ?: 'el citado';
            if (! empty($ct->asignado_at)) {
                $n = $notificadores[(int) $ct->id_notificador] ?? null;
                $eventos->push(self::evento('notificacion', $ct->asignado_at, 'Se envió a notificar a '.$quien, $n ? 'Notificador: '.$n : '', 'send', 'neutro', 20));
            }
            $ok = in_array($ct->estatus, self::NOTIFICADA, true);
            if ($ok || in_array($ct->estatus, self::NO_NOTIFICADA, true)) {
                $hechas['notificacion'] = ($hechas['notificacion'] ?? false) || $ok;
                // seer_citados.fecha es el día de la diligencia (sin hora); si falta, cuando se registró.
                $soloDia = $ct->fecha && strlen((string) $ct->fecha) <= 10;
                $eventos->push(self::evento('notificacion', $soloDia ? $ct->fecha.' 23:50:00' : ($ct->fecha ?: $ct->updated_at), ($ok ? 'Se notificó a ' : 'No se pudo notificar a ').$quien,
                    (string) $ct->estatus, $ok ? 'envelope-check' : 'envelope-exclamation', $ok ? 'ok' : 'alerta', 21, $soloDia));
            } elseif ($ct->notificacion === 'Trabajador') {
                // La persona trabajadora entrega el citatorio ella misma.
                $hechas['notificacion'] = true;
            }
        }

        // Audiencias, quién llegó y su resultado.
        $audiencias = DB::table('audiencias')->where('id_solicitud', $g->id)->orderBy('fecha')->orderBy('hora')->orderBy('id')
            ->get(['id', 'numero_audiencia', 'fecha', 'hora', 'id_conciliador', 'sala', 'delegacion', 'estatus']);
        $conciliadores = self::usuarios($audiencias->pluck('id_conciliador')->filter()->map(fn ($i) => (int) $i)->all());
        $resultados = DB::table('seer_conciliadores')->where('id_solicitud', $g->id)->whereNotNull('audiencia_id')
            ->get(['audiencia_id', 'monto', 'tipo'])->keyBy('audiencia_id');
        $llegadas = LlegadasAudiencia::migrado()
            ? DB::table('audiencia_llegadas')->whereIn('audiencia_id', $audiencias->pluck('id')->all() ?: [0])->orderBy('llegada_at')->get(['audiencia_id', 'parte', 'nombre', 'llegada_at'])
            : collect();

        foreach ($audiencias->values() as $i => $a) {
            $hechas['audiencia'] = true;
            $res     = $resultados[$a->id] ?? null;
            $estatus = (string) $a->estatus;
            $tono    = match (true) {
                $estatus === 'Pendiente'                                              => 'neutro',
                in_array($estatus, ['Reagendada', 'No conciliacion reagendada'], true) => 'alerta',
                in_array($estatus, self::FAVORABLES, true)                           => 'ok',
                default                                                                => 'neutro',
            };
            $c = $conciliadores[(int) $a->id_conciliador] ?? null;
            $detalle = implode(' · ', array_filter([
                $c ? 'Conciliador(a): '.$c : null,
                $a->sala ?: null,
                $a->delegacion,
                $res && $res->tipo ? $res->tipo : null,
                $estatus === 'Pendiente' ? null : (self::ESTATUS[$estatus] ?? $estatus),
                $res && (float) $res->monto > 0 ? 'Monto $'.number_format((float) $res->monto, 2) : null,
            ]));
            $eventos->push(self::evento('audiencia', substr((string) $a->fecha, 0, 10).' '.$a->hora,
                ($a->numero_audiencia ? 'Audiencia '.$a->numero_audiencia : 'Audiencia '.($i + 1)).($estatus === 'Reagendada' ? ' (se reagendó)' : ''),
                $detalle, 'people', $tono, 30));

            foreach ($llegadas->where('audiencia_id', $a->id) as $l) {
                $eventos->push(self::evento('audiencia', $l->llegada_at, 'Llegó a su audiencia: '.($l->parte === 'citado' ? 'la parte citada' : 'el solicitante'),
                    self::nombre($l->nombre), 'door-open', 'ok', 29));
            }
        }

        // Resultado del caso.
        if (in_array($g->estatus, self::RESUELTOS, true)) {
            $hechas['resultado'] = true;
            $favorable = in_array($g->estatus, self::FAVORABLES, true);
            $ultima = $audiencias->last();
            $dia = $g->fecha_terminacion ?: ($ultima->fecha ?? $g->updated_at);
            $eventos->push(self::evento('resultado', substr((string) $dia, 0, 10).' 23:59:00', 'Se resolvió: '.(self::ESTATUS[$g->estatus] ?? $g->estatus),
                $g->fecha_terminacion ? '' : 'Fecha aproximada (la de su última audiencia).', $favorable ? 'trophy' : 'flag', $favorable ? 'ok' : 'neutro', 40, true));
        }

        // Cumplimiento del convenio.
        $pagos = DB::table('pago_solicitud')->where('id_solicitud', $g->id)->orderBy('fecha')->orderBy('hora')
            ->get(['fecha', 'hora', 'monto', 'estatus', 'fecha_conclucion']);
        foreach ($pagos as $p) {
            $pagado = str_starts_with((string) $p->estatus, 'Pagado');
            $monto  = (float) $p->monto > 0 ? '$'.number_format((float) $p->monto, 2) : null;
            $eventos->push(self::evento('cumplimiento', substr((string) $p->fecha, 0, 10).' '.($p->hora ?: '00:00:00'), 'Cita de cumplimiento',
                implode(' · ', array_filter([$monto, $p->estatus])), 'cash-coin', $pagado ? 'ok' : ($p->estatus === 'Pendiente' ? 'neutro' : 'alerta'), 50));
            if ($pagado) {
                $hechas['cumplimiento'] = true;
                if ($p->fecha_conclucion) {
                    $eventos->push(self::evento('cumplimiento', substr((string) $p->fecha_conclucion, 0, 10).' 23:59:30', 'Se registró el pago', (string) $monto, 'check2-all', 'ok', 51, true));
                }
            }
        }
    }

    private static function evento(string $etapa, $cuando, string $titulo, string $detalle, string $icono, string $tono, int $orden, bool $soloFecha = false): array
    {
        try {
            $momento = $cuando ? Carbon::parse($cuando) : null;
        } catch (\Throwable) {
            $momento = null;
        }

        return compact('etapa', 'titulo', 'detalle', 'icono', 'tono', 'orden', 'soloFecha') + ['cuando' => $momento];
    }

    /** @return array<int, string> id => nombre */
    private static function usuarios(array $ids): array
    {
        static $cache = [];
        $ids = array_values(array_unique(array_filter($ids)));
        $faltan = array_diff($ids, array_keys($cache));
        if ($faltan) {
            foreach ($faltan as $id) {
                $cache[$id] = null;
            }
            foreach (DB::table('users')->whereIn('id', $faltan)->pluck('name', 'id') as $id => $n) {
                $cache[(int) $id] = self::nombre($n);
            }
        }

        return array_filter(array_intersect_key($cache, array_flip($ids)));
    }

    public static function nombre(?string $n): string
    {
        return Str::title(mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $n))));
    }

    private static function fechaLarga(string $fecha): string
    {
        return Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
    }
}
