<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
        'excepcion'    => 'Excepción',
        'solicitud'    => 'Solicitud',
        'notificacion' => 'Notificación',
        'audiencia'    => 'Audiencia',
        'resultado'    => 'Resultado',
        'cumplimiento' => 'Cumplimiento',
    ];

    /** Ven todos los casos de todas las sedes. */
    public const VEN_TODO = ['Super Usuario', 'Directivo'];

    /**
     * Sedes que puede consultar, con variantes sin acento:
     *   - Super Usuario y Directivo: todas;
     *   - Conciliación, Género e Igualdad: todas, pero sólo casos de
     *     excepción (soloExcepcion);
     *   - los demás: las de su agenda (AgendaContexto).
     */
    public static function sedes(User $usuario): array
    {
        $sedes = $usuario->hasAnyRole(self::VEN_TODO) || CasosExcepcion::es($usuario)
            ? CasosExcepcion::SEDES
            : (AgendaContexto::para($usuario)['sedes'] ?? []);

        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }

    /** Conciliación, Género e Igualdad sólo ve casos de excepción (salvo que también vea todo). */
    public static function soloExcepcion(User $usuario): bool
    {
        return CasosExcepcion::es($usuario) && ! $usuario->hasAnyRole(self::VEN_TODO);
    }

    /** Motivo del caso de excepción para mostrar: el exacto de la cita o, si no, el grupo. */
    public static function motivoExcepcion(?object $cita, ?object $formato = null): ?string
    {
        $clave = $cita->motivo_excepcion ?? null;
        if ($clave && isset(CasosExcepcion::MOTIVOS[$clave])) {
            return CasosExcepcion::MOTIVOS[$clave][3];
        }
        $tipos = $formato ? (json_decode((string) ($formato->tipos ?? ''), true) ?: []) : [];
        if ($tipos) {
            return implode(', ', array_map(fn ($t) => CasosExcepcion::TIPOS[$t] ?? $t, $tipos));
        }
        $grupo = trim((string) ($cita->tipo_caso ?? ''));

        return $grupo !== '' && $grupo !== 'Ninguno' ? str_replace('_', ' ', $grupo) : null;
    }

    /**
     * Todos los casos de sus sedes, del más reciente al más antiguo: cada
     * solicitud (con su cita, si la tiene) y cada cita que todavía no tiene
     * solicitud. Filtros: q (folio de cita, NUE, CURP o nombre), sede y
     * estado ('tramite', 'resueltos', 'citas' o 'excepcion'). Con
     * $soloExcepcion, sólo casos de excepción (Conciliación, Género e
     * Igualdad).
     */
    public static function tabla(array $sedes, array $filtros, int $porPagina = 25, bool $soloExcepcion = false): LengthAwarePaginator
    {
        $texto  = trim(preg_replace('/\s+/', ' ', (string) ($filtros['q'] ?? '')));
        $estado = (string) ($filtros['estado'] ?? '');
        $excepcion = $soloExcepcion || $estado === 'excepcion';
        if (! empty($filtros['sede'])) {
            $sedes = array_values(array_intersect($sedes, [$filtros['sede'], Str::ascii($filtros['sede'])])) ?: ['__ninguna__'];
        }
        $sedes = $sedes ?: ['__ninguna__'];

        $folio  = $texto !== '' ? FolioCita::normalizar($texto) : null;
        $curpN  = Curp::normalizar($texto);
        $curp   = strlen($curpN) === 18 ? $curpN : null;
        $nombre = $texto !== '' && ! $folio && ! $curp && ! preg_match('/\d/', $texto) && mb_strlen($texto) >= 3;
        $like   = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtoupper($texto)).'%';

        $solicitudes = DB::table('seer_general as g')
            ->leftJoin('recepcion as r', 'r.id', '=', 'g.recepcion_id')
            ->whereIn('g.delegacion', $sedes)
            ->when($estado === 'tramite', fn ($q) => $q->whereNotIn('g.estatus', self::RESUELTOS))
            ->when($estado === 'resueltos', fn ($q) => $q->whereIn('g.estatus', self::RESUELTOS))
            ->when($estado === 'citas', fn ($q) => $q->whereRaw('1 = 0'))
            ->when($excepcion, fn ($q) => $q->where(fn ($w) => $w->where('g.caso_excepcion', 'Si')->orWhere('r.exepcion', 'Si')))
            ->when($texto !== '', fn ($q) => $q->where(function ($w) use ($folio, $curp, $nombre, $like) {
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
            }))
            ->selectRaw('g.id AS sid, g.recepcion_id AS cid, COALESCE(g.created_at, g.fecha) AS creado');

        $citas = DB::table('recepcion as r')
            ->whereIn('r.delegacion', $sedes)
            ->when(FlujoCita::migrado(), fn ($q) => $q->where('r.es_prueba', 0))
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('seer_general as g')->whereColumn('g.recepcion_id', 'r.id'))
            ->when(in_array($estado, ['tramite', 'resueltos'], true), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($excepcion, fn ($q) => $q->where('r.exepcion', 'Si'))
            ->when($texto !== '', fn ($q) => $q->where(function ($w) use ($folio, $curp, $nombre, $like) {
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
            }))
            ->selectRaw('NULL AS sid, r.id AS cid, r.created_at AS creado');

        $pagina = DB::query()->fromSub($solicitudes->unionAll($citas), 'u')
            ->orderByDesc('creado')->orderByDesc('sid')->orderByDesc('cid')
            ->paginate($porPagina)
            ->withQueryString();

        $filas = $pagina->getCollection();
        $sids  = $filas->pluck('sid')->filter()->map(fn ($i) => (int) $i)->all();
        $cids  = $filas->pluck('cid')->filter()->map(fn ($i) => (int) $i)->all();

        $gen = DB::table('seer_general as g')->whereIn('g.id', $sids ?: [0])
            ->get(['g.id', 'g.NUE', 'g.fecha', 'g.created_at', 'g.estatus', 'g.delegacion', 'g.user_id', 'g.caso_excepcion',
                DB::raw('(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = g.id ORDER BY s.id LIMIT 1) AS persona')])
            ->keyBy('id');
        $cit = DB::table('recepcion')->whereIn('id', $cids ?: [0])->get()->keyBy('id');
        $prox = self::proximas($sids);
        $nombres = self::usuarios($gen->pluck('user_id')->filter()->map(fn ($i) => (int) $i)->all());

        $pagina->setCollection($filas->map(function ($f) use ($gen, $cit, $prox, $nombres) {
            $g = $f->sid ? ($gen[(int) $f->sid] ?? null) : null;
            $c = $f->cid ? ($cit[(int) $f->cid] ?? null) : null;
            $capturo = $g && $g->user_id ? ($nombres[(int) $g->user_id] ?? null) : null;
            $resuelto = $g && in_array($g->estatus, self::RESUELTOS, true);

            return [
                'clave'    => $g ? 's'.$g->id : 'c'.$c->id,
                'creado'   => $f->creado ? Carbon::parse($f->creado) : null,
                'persona'  => self::nombre($g->persona ?? null) ?: self::nombre($c->solicitante ?? null) ?: 'Sin nombre',
                'folio'    => $c ? FolioCita::mostrar($c) : null,
                'origen'   => $c ? ($c->origen === 'linea' ? 'En línea' : 'Ventanilla') : null,
                'nue'      => $g->NUE ?? null,
                'sede'     => $g->delegacion ?? $c->delegacion ?? '',
                'estado'   => $g ? (self::ESTATUS[$g->estatus] ?? (string) $g->estatus) : 'Cita '.$c->estatus.', sin solicitud',
                'tono'     => $g ? (in_array($g->estatus, self::FAVORABLES, true) ? 'favorable' : ($resuelto ? 'resuelto' : 'tramite')) : 'cita',
                'excepcion' => (($g->caso_excepcion ?? null) === 'Si' || ($c->exepcion ?? null) === 'Si')
                    ? (self::motivoExcepcion($c) ?? 'Caso de excepción') : null,
                'atendio'  => self::nombre($c->atendido_por_nombre ?? null) ?: $capturo,
                'creo'     => $c && $c->origen !== 'linea' ? (self::nombre($c->creado_por_nombre ?? null) ?: null) : null,
                'proxima'  => $g ? ($prox[(int) $g->id] ?? null) : null,
            ];
        }));

        return $pagina;
    }

    /**
     * Próxima audiencia (pendiente, de hoy en adelante) de cada solicitud.
     *
     * @param  int[]  $solicitudes
     * @return array<int, array{cuando: Carbon, conciliador: ?string, sala: ?string, numero: ?int}>
     */
    private static function proximas(array $solicitudes): array
    {
        if (! $solicitudes) {
            return [];
        }

        $filas = DB::table('audiencias')->whereIn('id_solicitud', $solicitudes)
            ->where('estatus', 'Pendiente')->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')->orderBy('hora')
            ->get(['id_solicitud', 'fecha', 'hora', 'id_conciliador', 'sala', 'numero_audiencia'])
            ->unique('id_solicitud');
        $conc = self::usuarios($filas->pluck('id_conciliador')->filter()->map(fn ($i) => (int) $i)->all());

        return $filas->mapWithKeys(fn ($a) => [(int) $a->id_solicitud => [
            'cuando'      => Carbon::parse(substr((string) $a->fecha, 0, 10).' '.$a->hora),
            'conciliador' => $conc[(int) $a->id_conciliador] ?? null,
            'sala'        => $a->sala ?: null,
            'numero'      => $a->numero_audiencia ? (int) $a->numero_audiencia : null,
        ]])->all();
    }

    /**
     * La historia completa de un caso ('s123' solicitud o 'c45' cita), o
     * null si no existe o no es de sus sedes.
     */
    public static function linea(string $clave, array $sedes, bool $soloExcepcion = false): ?array
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

        $esExcepcion = (($cita->exepcion ?? null) === 'Si') || ($solicitud && CasosExcepcion::solicitudEsCaso($solicitud));
        if ($soloExcepcion && ! $esExcepcion) {
            return null;
        }

        $eventos = collect();
        $hechas  = [];

        if ($cita) {
            self::eventosCita($cita, $eventos);
            $hechas['cita'] = true;
        }

        // Caso de excepción (art. 685 Ter): formato, canalización y aceptación.
        $formato = $esExcepcion ? self::eventosExcepcion($cita, $solicitud, $eventos, $hechas) : null;

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

        // Quién intervino.
        $prox     = $solicitud ? (self::proximas([(int) $solicitud->id])[(int) $solicitud->id] ?? null) : null;
        $capturo  = $solicitud && $solicitud->user_id ? (self::usuarios([(int) $solicitud->user_id])[(int) $solicitud->user_id] ?? null) : null;
        $ultimaConc = $solicitud ? DB::table('audiencias')->where('id_solicitud', $solicitud->id)->whereNotNull('id_conciliador')
            ->orderByDesc('fecha')->orderByDesc('hora')->value('id_conciliador') : null;
        $atendioExc = $formato && $formato->id_user ? (self::usuarios([(int) $formato->id_user])[(int) $formato->id_user] ?? null) : null;
        $responsables = array_values(array_filter([
            $atendioExc ? ['rol' => 'Atendió el caso de excepción', 'nombre' => $atendioExc, 'icono' => 'shield-check'] : null,
            $cita ? ['rol' => $cita->origen === 'linea' ? 'Agendó su cita' : 'Le creó el turno',
                     'nombre' => $cita->origen === 'linea' ? 'La persona, en línea' : (self::nombre($cita->creado_por_nombre ?? null) ?: 'Sin registro'), 'icono' => 'calendar-plus'] : null,
            ($cita || $capturo) ? ['rol' => 'Lo atendió', 'nombre' => self::nombre($cita->atendido_por_nombre ?? null) ?: ($capturo ?: 'Sin registro'), 'icono' => 'person-check'] : null,
            $solicitud ? ['rol' => 'Capturó la solicitud', 'nombre' => $capturo ?: 'La persona, en línea', 'icono' => 'file-earmark-text'] : null,
            $solicitud ? ['rol' => $prox ? 'Le toca audiencia con' : 'Conciliador(a)',
                          'nombre' => $prox['conciliador'] ?? ($ultimaConc ? (self::usuarios([(int) $ultimaConc])[(int) $ultimaConc] ?? null) : null) ?? 'Sin asignar', 'icono' => 'people'] : null,
        ]));

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
            'solicitud' => $solicitud ? (int) $solicitud->id : null,
            'proxima'   => $prox,
            'responsables' => $responsables,
            'dias'      => ($inicio && $fin) ? (int) $inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay()) : null,
            'excepcion' => $esExcepcion ? (self::motivoExcepcion($cita, $formato) ?? 'Caso de excepción') : null,
            'etapas'    => collect(self::ETAPAS)
                ->when(! $esExcepcion, fn ($e) => $e->except('excepcion'))
                ->map(fn ($nombre, $k) => ['nombre' => $nombre, 'hecha' => ! empty($hechas[$k])])->all(),
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
        $atendio = self::nombre($c->atendido_por_nombre ?? null) ?: $aux;
        $creo    = self::nombre($c->creado_por_nombre ?? null);

        $eventos->push(self::evento('cita', $c->created_at, $enLinea ? 'Agendó su cita en línea' : 'Se registró su turno en ventanilla',
            $folio.' · '.$c->tipo.' para el '.self::fechaLarga($fecha).' a las '.$hora.(! $enLinea && $creo ? ' · lo registró '.$creo : ''),
            $enLinea ? 'globe2' : 'person-vcard', 'neutro', 1, false, [self::doc('Comprobante de la cita', 'citas.documento', ['id' => $c->id])]));

        $eventos->push(self::evento('cita', $fecha.' '.$c->hora, 'Hora de su cita',
            implode(' · ', array_filter([$c->tipo, $c->lugar_auxiliar ?: null, $aux])), 'calendar-event', 'neutro', 2));

        if (! empty($c->confirmada_at ?? null)) {
            $eventos->push(self::evento('cita', $c->confirmada_at, 'Llegó y confirmó su cita', 'Escaneó su código QR en el Centro.', 'qr-code-scan', 'ok', 3));
        }
        if (! empty($c->llamado_at ?? null)) {
            $eventos->push(self::evento('cita', $c->llamado_at, 'Lo llamaron a su módulo', (string) $c->lugar_auxiliar, 'megaphone', 'neutro', 4));
        }
        if (! empty($c->atencion_inicio_at ?? null)) {
            $eventos->push(self::evento('cita', $c->atencion_inicio_at, 'Inició su atención', $atendio ? 'Lo atendió '.$atendio : '', 'person-check', 'neutro', 5));
        }
        if (! empty($c->atencion_fin_at ?? null)) {
            $min = ! empty($c->atencion_inicio_at) ? (int) round(Carbon::parse($c->atencion_inicio_at)->diffInSeconds(Carbon::parse($c->atencion_fin_at)) / 60) : null;
            $eventos->push(self::evento('cita', $c->atencion_fin_at, 'Terminó su atención', $min !== null ? 'Duró '.$min.' min.' : '', 'check2-circle', 'ok', 6));
        }
        if ($c->estatus === 'expirada') {
            $eventos->push(self::evento('cita', $fecha.' '.$c->hora, 'No se presentó a su cita', 'La cita quedó expirada.', 'person-x', 'alerta', 7));
        }
    }

    /**
     * Formato 685 Ter, oficios de canalización y aceptación firmada. Devuelve
     * el formato (o null). Los PDFs sólo se ligan a quien puede consultar los
     * casos de excepción (la ruta lo cuida igual).
     */
    private static function eventosExcepcion(?object $cita, ?object $solicitud, Collection $eventos, array &$hechas): ?object
    {
        $formato = DB::table('seer_casos_excepcion')
            ->where(function ($q) use ($cita, $solicitud) {
                $q->whereRaw('1 = 0');
                if ($cita) {
                    $q->orWhere('id_turno', $cita->id);
                }
                if ($solicitud && CasosExcepcion::conFormato()) {
                    $q->orWhere('id_solicitud', $solicitud->id);
                }
            })
            ->orderByDesc('id')
            ->first();

        if (! $formato) {
            return null;
        }

        $usuario = auth()->user();
        $docs    = $usuario && (CasosExcepcion::consulta($usuario) || CasosExcepcion::es($usuario));
        $origen  = $cita ? ['tipo' => 'cita', 'id' => $cita->id] : ['tipo' => 'solicitud', 'id' => $solicitud->id];
        $atendio = $formato->id_user ? (self::usuarios([(int) $formato->id_user])[(int) $formato->id_user] ?? null) : null;
        $terminado = ! empty($formato->terminado_at ?? null) || ! CasosExcepcion::conFormato();
        $resultado = CasosExcepcion::RESULTADOS[$formato->resultado ?? ''] ?? null;
        $tipos = array_map(fn ($t) => CasosExcepcion::TIPOS[$t] ?? $t, json_decode((string) ($formato->tipos ?? ''), true) ?: []);

        $hechas['excepcion'] = $terminado;
        $eventos->push(self::evento('excepcion', ($formato->terminado_at ?? null) ?: $formato->created_at,
            $terminado ? 'Se llenó el Formato 685 Ter' : 'Formato 685 Ter en captura',
            implode(' · ', array_filter([
                $tipos ? implode(', ', $tipos) : null,
                $resultado,
                $atendio ? 'lo atendió '.$atendio : null,
            ])), 'shield-check', $terminado ? 'ok' : 'neutro', 8, false,
            $docs ? [self::doc('Formato 685 Ter', 'excepciones.formato.pdf', $origen)] : []));

        if (OficioCanalizacion::migrado()) {
            $oficios = DB::table('excepcion_oficios')->where('caso_id', $formato->id)->orderBy('fecha')->orderBy('id')->get();
            foreach ($oficios as $o) {
                $inst   = CasosExcepcion::INSTITUCIONES[$o->institucion] ?? (string) $o->institucion;
                $numero = $o->numero ?: ($o->consecutivo ? OficioCanalizacion::numero((int) $o->consecutivo, (int) $o->anio) : null);
                // La fecha del oficio no trae hora: si es el mismo día en que se registró, se usa ese momento.
                $diaOficio = $o->fecha ? substr((string) $o->fecha, 0, 10) : null;
                $mismoDia  = $o->created_at && (! $diaOficio || substr((string) $o->created_at, 0, 10) === $diaOficio);
                $eventos->push(self::evento('excepcion', $mismoDia ? $o->created_at : $diaOficio.' 12:00:00',
                    'Se canalizó a '.$inst, $numero ? 'Oficio '.$numero : 'Oficio sin número todavía', 'send-check', 'alerta', 9, ! $mismoDia,
                    $docs ? [self::doc('Oficio de canalización', 'excepciones.oficio.pdf', $origen + ['institucion' => $o->institucion])] : []));

                if (! empty($o->aceptacion_firmada ?? null)) {
                    $eventos->push(self::evento('excepcion', ($o->aceptacion_firmada_at ?? null) ?: $o->updated_at,
                        'Firmó la aceptación de la canalización', $inst, 'pen', 'ok', 9, false,
                        $docs ? [self::doc('Aceptación de canalización', 'excepciones.aceptacion.pdf', $origen + ['institucion' => $o->institucion])] : []));
                }
            }
        }

        return $formato;
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
            ])), 'file-earmark-text', 'neutro', 10, false, self::documentosSolicitud($g)));

        $citados = DB::table('seer_citados')->where('id_solicitud', $g->id)->orderBy('id')
            ->get(['id', 'nombre', 'primer_apellido', 'segundo_apellido', 'estatus', 'notificacion', 'id_notificador', 'asignado_at', 'fecha', 'updated_at']);

        if (! empty($g->fecha_confirmacion)) {
            $docs = [self::doc('Acuse de solicitud confirmada', 'PDFacuseConfirmada', ['id' => $g->id])];
            foreach ($citados as $ct) {
                $docs[] = self::doc('Citatorio · '.(self::nombre(trim($ct->nombre.' '.$ct->primer_apellido)) ?: 'citado'), 'PDFSolicitud', ['id' => $ct->id]);
            }
            $eventos->push(self::evento('solicitud', substr((string) $g->fecha_confirmacion, 0, 10).' 23:58:00', 'Se confirmó la solicitud',
                'Se agendó su audiencia y se emitieron los citatorios.', 'patch-check', 'ok', 11, true, $docs));
        }

        // Documentos que se fueron cargando al expediente (tabla documentos).
        foreach (DB::table('documentos')->where('id_solicitud', $g->id)->where('tramite', 'Audiencia')->orderBy('created_at')->get(['id', 'nombre_documento', 'tipo_documentos', 'created_at']) as $d) {
            $eventos->push(self::evento('solicitud', $d->created_at, 'Se cargó un documento', (string) ($d->tipo_documentos ?: $d->nombre_documento),
                'paperclip', 'neutro', 12, false, [self::doc((string) ($d->nombre_documento ?: 'Documento'), 'documento_solicitud_ver', $d->id, true)]));
        }

        // Notificación de cada citado.
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
                $ruta = [
                    'Finalizado exitosamente'     => 'PDFRazonNoticacion',
                    'Recibe pero no firma'        => 'PDFRazonNoticacion',
                    'Notificada'                  => 'PDFRazonNoticacion',
                    'Exitosa por Instructivo'     => 'PDFInstructivo',
                    'No exitosa se constituye'    => 'VerPDFNoExitConstituye',
                    'No exitosa no se constituye' => 'PDFNoExitosaInt',
                ][$ct->estatus] ?? null;
                $eventos->push(self::evento('notificacion', $soloDia ? $ct->fecha.' 23:50:00' : ($ct->fecha ?: $ct->updated_at), ($ok ? 'Se notificó a ' : 'No se pudo notificar a ').$quien,
                    (string) $ct->estatus, $ok ? 'envelope-check' : 'envelope-exclamation', $ok ? 'ok' : 'alerta', 21, $soloDia,
                    $ruta ? [self::doc('Constancia de notificación', $ruta, ['id' => $ct->id, 'id_solicitud' => $g->id])] : []));
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
            $docs = [];
            if ($audiencias->contains(fn ($a) => ! in_array($a->estatus, ['Pendiente', 'Reagendada'], true))) {
                $docs[] = self::doc('Acta de audiencia', 'VerPDFAudiencia', ['id' => $g->id]);
            }
            $docs[] = match ($g->estatus) {
                'Conciliacion'    => self::doc('Convenio', 'PDFconveniosolicitud', ['id' => $g->id]),
                'Reinstalacion'   => self::doc('Convenio de reinstalación', 'PDFconvenioreinstalacion', ['id' => $g->id]),
                'No conciliacion' => self::doc('Constancia de no conciliación', 'PDFno_conciliacion', ['id' => $g->id]),
                'Incompetencia'   => self::doc('Constancia de incompetencia', 'PDFincompetencia', ['id' => $g->id]),
                default           => null,
            };
            $eventos->push(self::evento('resultado', substr((string) $dia, 0, 10).' 23:59:00', 'Se resolvió: '.(self::ESTATUS[$g->estatus] ?? $g->estatus),
                $g->fecha_terminacion ? '' : 'Fecha aproximada (la de su última audiencia).', $favorable ? 'trophy' : 'flag', $favorable ? 'ok' : 'neutro', 40, true, $docs));
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

    private static function evento(string $etapa, $cuando, string $titulo, string $detalle, string $icono, string $tono, int $orden, bool $soloFecha = false, array $docs = []): array
    {
        try {
            $momento = $cuando ? Carbon::parse($cuando) : null;
        } catch (\Throwable) {
            $momento = null;
        }
        $docs = array_values(array_filter($docs));

        return compact('etapa', 'titulo', 'detalle', 'icono', 'tono', 'orden', 'soloFecha', 'docs') + ['cuando' => $momento];
    }

    /**
     * Lo que la persona entregó al levantar la solicitud: acuse,
     * identificación, CURP y fotos del domicilio de los citados.
     */
    private static function documentosSolicitud(object $g): array
    {
        $s = DB::table('seer_solicitante')->where('id_solicitud', $g->id)->orderBy('id')->first(['documentoIdentificacion', 'documentoCurp']);
        $archivo = fn ($v) => ($v && $v !== 'Sin documento') ? basename((string) $v) : null;

        $docs = [self::doc('Acuse de la solicitud', 'PDFacuse_solicitud', ['id' => $g->id])];
        if ($f = $archivo($s->documentoIdentificacion ?? null)) {
            $docs[] = self::doc('Identificación', 'documentos.ver', ['tipo' => 'solicitud', 'id' => $g->id, 'archivo' => $f], true);
        }
        if ($f = $archivo($s->documentoCurp ?? null)) {
            $docs[] = self::doc('CURP', 'documentos.ver', ['tipo' => 'solicitud', 'id' => $g->id, 'archivo' => $f], true);
        }
        foreach (DB::table('seer_citados')->where('id_solicitud', $g->id)->get(['imagen_domicilio1', 'imagen_domicilio2']) as $i => $c) {
            foreach ([$c->imagen_domicilio1, $c->imagen_domicilio2] as $n => $img) {
                if ($f = $archivo($img)) {
                    $docs[] = self::doc('Foto del domicilio '.($n + 1).' (citado '.($i + 1).')', 'documentos.ver_imagen', ['id_solicitud' => $g->id, 'filename' => $f], true);
                }
            }
        }

        return $docs;
    }

    /** Liga a un documento si la ruta existe (firmada si así la pide la ruta). */
    private static function doc(string $nombre, string $ruta, $parametros, bool $firmada = false): ?array
    {
        if (! Route::has($ruta)) {
            return null;
        }

        try {
            $url = $firmada && function_exists('signedDocRoute') ? signedDocRoute($ruta, $parametros) : route($ruta, $parametros);
        } catch (\Throwable) {
            return null;
        }

        return ['nombre' => $nombre, 'url' => $url];
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
