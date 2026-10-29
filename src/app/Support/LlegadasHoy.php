<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * "Llegadas hoy": quién ya llegó a su audiencia, ratificación o cumplimiento
 * del día. Trae también lo que ya se cerró hoy (con su estatus, sin poder
 * marcar) y señala lo abierto cuya hora ya pasó y le falta alguien. La
 * recepción que recibe a la gente (Morelia 02 y, en las sedes regionales, la
 * Recepción General) lo marca y el conciliador asignado
 * recibe un aviso con sonido (llegadas/_avisos), igual que el auxiliar que
 * lleva la ratificación o el cumplimiento (AUXILIARES). Ambos también ven
 * la pantalla, sólo para consultar.
 *
 *   delDia()      lo de hoy de unas sedes, con sus partes y quién llegó;
 *   marcar()      registra (o quita) la llegada de una parte;
 *   desdeCita()   cuando recepción valida con QR una cita de Ratificación, la
 *                 liga por CURP con su ratificación (turnos.recepcion_id) y
 *                 cuenta como llegada;
 *   avisosPara()  llegadas recientes de lo que lleva un conciliador o un
 *                 auxiliar (responsables()).
 *   guardarObservaciones()  lo que la recepción anota de una audiencia
 *                 (silla de ruedas, traductor, menor de edad y texto) o de
 *                 una ratificación (sólo texto); lo ve el conciliador.
 *
 * Un solo registro (tabla llegadas): tipo + registro_id + clave de la parte.
 *   audiencia     audiencias.id      partes: 'solicitante' y 'citado:<hash>'
 *   ratificacion  turnos.id          partes: 'trabajador' y 'empresa'
 *   cumplimiento  pago_solicitud.id  partes: 'trabajador' y 'empresa'
 *
 * No sustituye la comparecencia, que el conciliador registra con
 * identificación dentro de la audiencia o la ratificación.
 */
class LlegadasHoy
{
    public const TABLA = 'llegadas';

    public const TIPOS = [
        'audiencia'    => 'Audiencias',
        'ratificacion' => 'Ratificaciones',
        'cumplimiento' => 'Cumplimientos',
    ];

    public const SINGULAR = [
        'audiencia'    => 'Audiencia',
        'ratificacion' => 'Ratificación',
        'cumplimiento' => 'Cumplimiento',
    ];

    /**
     * Quién marca: la recepción que recibe audiencias, ratificaciones y
     * cumplimientos (Morelia 02 en Morelia; la General, que atiende sola las
     * sedes regionales) y quien la supervisa. Cada una, en sus sedes
     * (Recepcion::sedes).
     */
    public const MARCAN = ['Super Usuario', Recepcion::MORELIA_02, Recepcion::REGIONAL];

    /**
     * Auxiliares que reciben el aviso de lo que llevan: la ratificación que
     * capturaron o cuya cita cayó en su módulo, y el cumplimiento que
     * agendaron (o el de una ratificación suya). Las audiencias son sólo del
     * conciliador.
     */
    public const AUXILIARES = ['Auxiliar Ratificaciones', 'Auxiliar Cumplimientos'];

    /** Quién sólo consulta (las de sus sedes; por omisión, las suyas). */
    public const CONSULTAN = ['Conciliador', ...self::AUXILIARES];

    /** Tabla de observaciones (una fila por registro). */
    public const TABLA_OBS = 'llegadas_observaciones';

    /** Casillas de observación. */
    public const OBSERVACIONES = [
        'silla_ruedas' => 'Viene en silla de ruedas',
        'traductor'    => 'Ocupa traductor',
        'menor_edad'   => 'Es menor de edad',
    ];

    /** Dónde se anotan observaciones y qué casillas lleva cada uno (el texto libre va en todos). */
    public const OBSERVAN = [
        'audiencia'    => ['silla_ruedas', 'traductor', 'menor_edad'],
        'ratificacion' => [],
    ];

    /** Ratificaciones que ya no esperan a nadie. */
    private const RATIFICACION_CERRADA = ['Concluida', 'Concluida Pagos', 'Incumplimiento', 'Archivada', 'Prevencion', 'no atendido'];

    /** Minutos de gracia antes de marcar "Se pasó la hora". */
    public const TOLERANCIA_MINUTOS = 5;

    /** Cómo se lee el estatus de lo que ya se cerró hoy. */
    private const CERRADO = [
        'Reagendada'                    => 'Reagendada',
        'No conciliacion reagendada'    => 'Reagendada',
        'Conciliacion'                  => 'Con convenio',
        'No conciliacion'               => 'Sin convenio',
        'Archivada'                     => 'Archivada',
        'Archivada en Audiencia'        => 'Archivada',
        'Incompetencia'                 => 'Incompetencia',
        'Desistimiento'                 => 'Desistimiento',
        'Reinstalacion'                 => 'Reinstalación',
        'Concluida'                     => 'Concluida',
        'Concluida Pagos'               => 'Concluida',
        'Incumplimiento'                => 'Incumplimiento',
        'Prevencion'                    => 'En prevención',
        'no atendido'                   => 'No atendida',
        'Pagado'                        => 'Pagado',
        'No pagado'                     => 'No pagado',
        'Incomparecencia trabajador'    => 'No se presentó el trabajador',
        'Pagado con pena convencional'  => 'Pagado con pena convencional',
    ];

    /** El aviso al conciliador sólo trae llegadas de los últimos minutos. */
    private const MINUTOS_AVISO = 90;

    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasTable(self::TABLA) && Schema::hasColumn(self::TABLA, 'tipo');
    }

    public static function conObservaciones(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasTable(self::TABLA_OBS);
    }

    /**
     * Guarda (o borra, si queda vacío) las observaciones de un registro de
     * hoy. Devuelve el texto de error, o null si salió bien.
     */
    public static function guardarObservaciones(string $tipo, int $id, array $datos, User $usuario): ?string
    {
        if (! self::conObservaciones() || ! array_key_exists($tipo, self::OBSERVAN)) {
            return 'Aquí no se anotan observaciones.';
        }

        $registro = self::registro($tipo, $id);
        if (! $registro) {
            return 'Ese registro ya no existe.';
        }
        if (! in_array($registro['sede'], self::variantes(self::sedes($usuario)), true)) {
            return 'No es de tu sede.';
        }
        if ($registro['fecha'] !== now()->toDateString()) {
            return 'Sólo se anotan observaciones de lo de hoy.';
        }

        $texto = trim(preg_replace('/[ \t]+/', ' ', (string) ($datos['texto'] ?? '')));
        $fila = ['texto' => $texto !== '' ? $texto : null];
        foreach (array_keys(self::OBSERVACIONES) as $casilla) {
            $fila[$casilla] = in_array($casilla, self::OBSERVAN[$tipo], true) && ! empty($datos[$casilla]);
        }

        $donde = DB::table(self::TABLA_OBS)->where('tipo', $tipo)->where('registro_id', $id);
        if ($fila['texto'] === null && ! in_array(true, array_intersect_key($fila, self::OBSERVACIONES), true)) {
            $donde->delete();

            return null;
        }

        $fila += ['registrado_por' => $usuario->id, 'updated_at' => now()];
        if ((clone $donde)->exists()) {
            $donde->update($fila);
        } else {
            DB::table(self::TABLA_OBS)->insert($fila + ['tipo' => $tipo, 'registro_id' => $id, 'created_at' => now()]);
        }

        return null;
    }

    /** Observaciones guardadas de unos registros de un tipo, por registro_id. */
    private static function observacionesDe(string $tipo, $ids): Collection
    {
        if (! self::conObservaciones() || ! array_key_exists($tipo, self::OBSERVAN)) {
            return collect();
        }

        return DB::table(self::TABLA_OBS . ' as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.registrado_por')
            ->where('o.tipo', $tipo)
            ->whereIn('o.registro_id', collect($ids)->all() ?: [0])
            ->get(['o.registro_id', 'o.silla_ruedas', 'o.traductor', 'o.menor_edad', 'o.texto', 'o.updated_at', 'u.name as por'])
            ->keyBy('registro_id');
    }

    /** Lo que se pinta: casillas, texto y una lista corta para el aviso. */
    private static function observacion(?object $o): ?array
    {
        if (! $o) {
            return null;
        }

        $marcadas = array_values(array_filter(array_keys(self::OBSERVACIONES), fn ($c) => (bool) $o->{$c}));

        return [
            'silla_ruedas' => (bool) $o->silla_ruedas,
            'traductor'    => (bool) $o->traductor,
            'menor_edad'   => (bool) $o->menor_edad,
            'texto'        => $o->texto,
            'etiquetas'    => array_map(fn ($c) => self::OBSERVACIONES[$c], $marcadas),
            'por'          => $o->por ? PantallaEspera::nombreCorto($o->por, false) : null,
            'hora'         => $o->updated_at ? Carbon::parse($o->updated_at)->format('H:i') : null,
        ];
    }

    /** Roles con la pestaña en el menú. */
    public static function roles(): array
    {
        return array_merge(self::MARCAN, self::CONSULTAN);
    }

    public static function puedeMarcar(?User $usuario): bool
    {
        return $usuario !== null && self::migrado() && $usuario->hasAnyRole(self::MARCAN);
    }

    public static function puedeVer(?User $usuario): bool
    {
        return $usuario !== null && self::migrado() && $usuario->hasAnyRole(self::roles());
    }

    /** ¿Le llegan los avisos con sonido? (layouts/app incluye llegadas/_avisos) */
    public static function recibeAvisos(?User $usuario): bool
    {
        return $usuario !== null && self::migrado() && $usuario->hasAnyRole(self::CONSULTAN);
    }

    /**
     * Sedes que ve: Super Usuario todas; la recepción, las de su rol (Morelia
     * 02, Morelia; la General, la de su cuenta, o las cinco regionales si su
     * cuenta no es de una); el conciliador y los auxiliares, su región.
     */
    public static function sedes(User $usuario): array
    {
        if ($usuario->hasRole('Super Usuario')) {
            return Recepcion::TODAS_LAS_SEDES;
        }
        if (Recepcion::es($usuario)) {
            return Recepcion::sedes($usuario);
        }

        return AgendaContexto::para($usuario)['sedes'] ?: array_filter([$usuario->delegacion]);
    }

    /**
     * Todo lo de hoy de esas sedes, ordenado por hora.
     *
     * @return Collection<int, array>
     */
    public static function delDia(array $sedes, string $fecha): Collection
    {
        if ($sedes === []) {
            return collect();
        }

        self::ligarRatificaciones($sedes, $fecha);

        return self::audiencias($sedes, $fecha)
            ->concat(self::ratificaciones($sedes, $fecha))
            ->concat(self::cumplimientos($sedes, $fecha))
            ->sortBy(fn ($r) => $r['hora'].'|'.$r['tipo'].'|'.$r['nue'])
            ->values();
    }

    /**
     * Marca (o quita) la llegada de una parte. Devuelve el texto de error, o
     * null si salió bien.
     */
    public static function marcar(string $tipo, int $id, string $clave, bool $llego, User $usuario): ?string
    {
        $registro = self::registro($tipo, $id);

        if (! $registro) {
            return 'Ese registro ya no existe.';
        }
        if (! in_array($registro['sede'], self::variantes(self::sedes($usuario)), true)) {
            return 'No es de tu sede.';
        }
        if ($registro['fecha'] !== now()->toDateString()) {
            return 'Sólo se marca la llegada de hoy.';
        }
        if (! $registro['abierto']) {
            return 'Ya se cerró; ya no se marcan llegadas.';
        }

        $parte = collect($registro['partes'])->firstWhere('clave', $clave);
        if (! $parte) {
            return 'Esa persona no es parte de este trámite.';
        }

        if (! $llego) {
            DB::table(self::TABLA)->where('tipo', $tipo)->where('registro_id', $id)->where('clave', $clave)->delete();

            return null;
        }

        self::registrar($tipo, $id, $parte, now(), $usuario->id, 'manual');

        return null;
    }

    /**
     * Recepción validó (QR) una cita: si es de Ratificación, se liga con su
     * ratificación de hoy por CURP y cuenta como llegada. Si la ratificación
     * todavía no se captura, lo hace delDia()/avisosPara() en cuanto exista.
     */
    public static function desdeCita(object $cita): void
    {
        if (! self::migrado() || ($cita->tipo ?? null) !== 'Ratificación' || strlen((string) ($cita->curp ?? '')) !== 18) {
            return;
        }

        $fecha = Carbon::parse($cita->fecha)->toDateString();
        self::ligarRatificaciones([(string) $cita->delegacion], $fecha);
    }

    /**
     * Llegadas recientes a lo de hoy que lleva esta persona: como conciliador
     * asignado o como auxiliar (ver AUXILIARES).
     *
     * @return Collection<int, array>
     */
    public static function avisosPara(User $usuario): Collection
    {
        $esConc = $usuario->hasRole('Conciliador');
        $esAux  = $usuario->hasAnyRole(self::AUXILIARES);

        if (! self::migrado() || (! $esConc && ! $esAux)) {
            return collect();
        }

        $id    = $usuario->id;
        $ahora = now();
        $hoy   = $ahora->toDateString();
        $desde = $ahora->copy()->subMinutes(self::MINUTOS_AVISO);

        // Por si la cita se validó antes de capturar la ratificación.
        $sedesRat = DB::table('turnos')->where('tipo', 'Ratificación')->where('fecha', $hoy)
            ->where(fn ($q) => $q->where('id_conciliador', $id)->orWhere('user_id', $id))
            ->distinct()->pluck('delegacion')->filter()->all();
        if ($esAux) {
            $sedesRat = array_values(array_unique(array_merge($sedesRat, self::sedes($usuario))));
        }
        if ($sedesRat) {
            self::ligarRatificaciones($sedesRat, $hoy);
        }

        $base = fn (string $tipo) => DB::table(self::TABLA.' as l')
            ->where('l.tipo', $tipo)
            ->where('l.llegada_at', '>=', $desde)
            ->select(['l.id', 'l.tipo', 'l.parte', 'l.nombre', 'l.origen', 'l.llegada_at', 'l.registro_id']);

        $audiencias = ! $esConc ? collect() : $base('audiencia')
            ->join('audiencias as a', 'a.id', '=', 'l.registro_id')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->where('a.id_conciliador', $id)->where('a.fecha', $hoy)
            ->addSelect(['a.hora', 'a.sala as lugar', 'g.NUE', 'a.id_solicitud'])
            ->get();

        $ratificaciones = $base('ratificacion')
            ->join('turnos as t', 't.id', '=', 'l.registro_id')
            ->where('t.fecha', $hoy)
            ->where(function ($q) use ($id, $esConc, $esAux) {
                if ($esConc) {
                    $q->orWhere('t.id_conciliador', $id);
                }
                if ($esAux) {
                    // La capturó, o su cita cayó en su módulo.
                    $q->orWhere('t.user_id', $id)
                      ->orWhereExists(fn ($e) => $e->select(DB::raw(1))->from('recepcion as rc')
                          ->leftJoin('modulos as mo', 'mo.id', '=', 'rc.modulo_id')
                          ->whereColumn('rc.id', 't.recepcion_id')
                          ->where(fn ($w) => $w->where('mo.user_id', $id)->orWhere('rc.auxiliar', $id)));
                }
            })
            ->addSelect(['t.hora', DB::raw("'' as lugar"), 't.NUE', DB::raw('NULL as id_solicitud')])
            ->get();

        $cumplimientos = $base('cumplimiento')
            ->join('pago_solicitud as p', 'p.id', '=', 'l.registro_id')
            ->where('p.fecha', $hoy)
            ->where(function ($q) use ($id, $esConc, $esAux) {
                if ($esConc) {
                    $q->orWhere('p.id_conciliador', $id);
                }
                if ($esAux) {
                    // Lo agendó, o es el pago de una ratificación que capturó.
                    $q->orWhere('p.user_id', $id)
                      ->orWhereExists(fn ($e) => $e->select(DB::raw(1))->from('turnos as tr')
                          ->whereColumn('tr.id', 'p.id_solicitud')->where('p.tipo_pago', 'Ratificacion')
                          ->where('tr.user_id', $id));
                }
            })
            ->addSelect(['p.hora', DB::raw("'' as lugar"), DB::raw("COALESCE(NULLIF(p.NUE, ''), (SELECT g2.NUE FROM seer_general g2 WHERE g2.id = p.id_solicitud AND p.tipo_pago = 'Audiencia'), (SELECT t2.NUE FROM turnos t2 WHERE t2.id = p.id_solicitud AND p.tipo_pago = 'Ratificacion')) as NUE"), DB::raw('NULL as id_solicitud')])
            ->get();

        $todas = $audiencias->concat($ratificaciones)->concat($cumplimientos)->sortBy('id')->take(40);
        // Observaciones de la recepción: van en el aviso (silla de ruedas, traductor...).
        $obs = [];
        foreach (array_keys(self::OBSERVAN) as $t) {
            $obs[$t] = self::observacionesDe($t, $todas->where('tipo', $t)->pluck('registro_id'));
        }

        return $todas
            ->map(fn ($l) => [
                'id'      => $l->id,
                'tipo'    => self::SINGULAR[$l->tipo] ?? $l->tipo,
                'quien'   => self::quien($l->parte),
                'nombre'  => Str::title(Str::lower((string) $l->nombre)),
                'llegada' => Carbon::parse($l->llegada_at)->format('H:i'),
                'cita'    => $l->origen === 'cita',
                'nue'     => (string) $l->NUE,
                'lugar'   => (string) $l->lugar,
                'hora'    => substr((string) $l->hora, 0, 5),
                'url'     => self::url($l->tipo, (int) $l->registro_id, $l->id_solicitud),
                'obs'     => ($o = self::observacion(($obs[$l->tipo] ?? collect())[$l->registro_id] ?? null))
                    ? array_values(array_filter(array_merge($o['etiquetas'], [$o['texto']])))
                    : [],
            ])
            ->values();
    }

    public static function quien(string $parte): string
    {
        return match ($parte) {
            'solicitante', 'trabajador' => 'el trabajador',
            'empresa'                   => 'la empresa',
            default                     => 'la parte citada',
        };
    }

    /* ------------------------------------------------------------------ */

    private static function audiencias(array $sedes, string $fecha): Collection
    {
        $audiencias = DB::table('audiencias as a')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador')
            ->where('a.fecha', $fecha)
            ->whereIn('a.delegacion', self::variantes($sedes))
            ->orderBy('a.hora')->orderBy('a.sala')
            ->get(['a.id', 'a.id_solicitud', 'a.hora', 'a.sala', 'a.delegacion', 'a.id_conciliador', 'a.estatus', 'g.NUE', 'u.name as conciliador']);

        if ($audiencias->isEmpty()) {
            return collect();
        }

        $partes = self::partesAudiencia($audiencias);

        return self::armar('audiencia', $fecha, $audiencias, fn ($a) => [
            'hora'   => $a->hora,
            'estatus' => $a->estatus,
            'abierto' => $a->estatus === 'Pendiente',
            'lugar'  => $a->sala ?: 'Sin sala',
            'sede'   => $a->delegacion,
            'nue'    => $a->NUE,
            'conc'   => $a->conciliador,
            'concId' => $a->id_conciliador,
            'partes' => $partes[$a->id] ?? [],
        ]);
    }

    private static function ratificaciones(array $sedes, string $fecha): Collection
    {
        $turnos = self::consultaRatificaciones()
            ->where('t.fecha', $fecha)
            ->whereIn('t.delegacion', self::variantes($sedes))
            ->orderBy('t.hora')
            ->get();

        if ($turnos->isEmpty()) {
            return collect();
        }

        $citas = DB::table('recepcion as r')
            ->leftJoin('modulos as m', 'm.id', '=', 'r.modulo_id')
            ->whereIn('r.id', $turnos->pluck('recepcion_id')->filter()->unique())
            ->get(array_merge(['r.id', 'r.codigo', 'r.consecutivo', 'r.estatus', 'r.auxiliar', 'm.user_id as modulo_user'],
                FlujoCita::conSeguimiento() ? ['r.confirmada_at'] : [],
                FlujoCita::conTardias() ? ['r.llego_tarde'] : []))
            ->keyBy('id');

        return self::armar('ratificacion', $fecha, $turnos, function ($t) use ($citas) {
            $cita = $t->recepcion_id ? ($citas[$t->recepcion_id] ?? null) : null;

            return [
                'hora'   => $t->hora,
                'estatus' => $t->estatus,
                'abierto' => ! in_array($t->estatus, self::RATIFICACION_CERRADA, true),
                'lugar'  => 'Ratificación',
                'sede'   => $t->delegacion,
                'nue'    => $t->NUE ?: 'Sin expediente',
                'conc'   => $t->conciliador,
                'concId' => $t->id_conciliador,
                'partes' => self::partesRatificacion($t),
                'aux'    => [$t->user_id, $cita->modulo_user ?? null, $cita->auxiliar ?? null],
                'cita'   => $cita ? [
                    'folio'  => FolioCita::mostrar($cita),
                    'valido' => ! empty($cita->confirmada_at ?? null) ? Carbon::parse($cita->confirmada_at)->format('H:i') : null,
                    'tarde'  => ! empty($cita->llego_tarde ?? null),
                ] : null,
            ];
        });
    }

    private static function cumplimientos(array $sedes, string $fecha): Collection
    {
        $pagos = self::consultaCumplimientos()
            ->where('p.fecha', $fecha)
            ->whereIn('p.delegacion', self::variantes($sedes))
            ->orderBy('p.hora')
            ->get();

        return self::armar('cumplimiento', $fecha, $pagos, fn ($p) => [
            'hora'   => $p->hora,
            'estatus' => $p->estatus,
            'abierto' => $p->estatus === 'Pendiente',
            'lugar'  => $p->tipo_pago === 'Ratificacion' ? 'Pago de ratificación' : 'Pago de audiencia',
            'sede'   => $p->delegacion,
            'nue'    => $p->NUE ?: 'Sin expediente',
            'conc'   => $p->conciliador,
            'concId' => $p->id_conciliador,
            'partes' => self::partesCumplimiento($p),
            'aux'    => [$p->user_id, $p->tipo_pago === 'Ratificacion' ? $p->ratificacion_user : null],
        ]);
    }

    /** Une cada registro con sus llegadas y arma lo que pinta la pantalla. */
    private static function armar(string $tipo, string $fecha, Collection $filas, callable $datos): Collection
    {
        $ahora = now();
        $obs   = self::observacionesDe($tipo, $filas->pluck('id'));

        $llegadas = DB::table(self::TABLA.' as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.registrado_por')
            ->where('l.tipo', $tipo)
            ->whereIn('l.registro_id', $filas->pluck('id'))
            ->get(['l.registro_id', 'l.clave', 'l.llegada_at', 'l.origen', 'u.name as por'])
            ->groupBy('registro_id');

        return $filas->map(function ($fila) use ($tipo, $fecha, $ahora, $datos, $llegadas, $obs) {
            $d      = $datos($fila);
            $deEste = ($llegadas[$fila->id] ?? collect())->keyBy('clave');
            $lista  = array_map(function ($p) use ($deEste) {
                $l = $deEste[$p['clave']] ?? null;

                return $p + [
                    'llegada' => $l ? Carbon::parse($l->llegada_at)->format('H:i') : null,
                    'por'     => $l ? ($l->origen === 'cita' ? 'Validó su cita en recepción' : 'Marcó '.PantallaEspera::nombreCorto($l->por, false)) : null,
                    'cita'    => $l && $l->origen === 'cita',
                ];
            }, $d['partes']);

            $llegaron = count(array_filter($lista, fn ($p) => $p['llegada']));
            $completa = $lista && $llegaron === count($lista);

            // Abierta, le falta alguien y ya pasó su hora (con unos minutos de gracia).
            $atraso = null;
            if ($d['abierto'] && ! $completa && $d['hora']) {
                $minutos = (int) floor(Carbon::parse($fecha.' '.substr((string) $d['hora'], 0, 5))->diffInMinutes($ahora, false));
                $atraso  = $minutos > self::TOLERANCIA_MINUTOS ? $minutos : null;
            }

            return [
                'tipo'           => $tipo,
                'id'             => $fila->id,
                'hora'           => substr((string) $d['hora'], 0, 5),
                'lugar'          => (string) $d['lugar'],
                'sede'           => (string) $d['sede'],
                'nue'            => (string) $d['nue'],
                'conciliador'    => PantallaEspera::nombreCorto($d['conc'], false) ?: '',
                'conciliador_id' => (int) $d['concId'],
                // Quiénes la llevan (conciliador y auxiliares): filtro "Sólo las mías".
                'responsables'   => array_values(array_unique(array_filter(array_map('intval', array_merge([$d['concId']], $d['aux'] ?? []))))),
                'cita'           => $d['cita'] ?? null,
                'partes'         => $lista,
                'llegaron'       => $llegaron,
                'completa'       => $completa,
                'abierto'        => (bool) $d['abierto'],
                'estatus'        => $d['abierto'] ? null : (self::CERRADO[$d['estatus']] ?? (string) $d['estatus']),
                'atraso'         => $atraso,
                // Observaciones de la recepción (audiencias y ratificaciones).
                'conObs'         => self::conObservaciones() && array_key_exists($tipo, self::OBSERVAN),
                'obs'            => self::observacion($obs[$fila->id] ?? null),
            ];
        });
    }

    /** Un solo registro con sus partes, para validar al marcar. */
    private static function registro(string $tipo, int $id): ?array
    {
        if ($tipo === 'audiencia') {
            $a = DB::table('audiencias')->where('id', $id)->first(['id', 'id_solicitud', 'fecha', 'delegacion', 'estatus']);

            return $a ? ['fecha' => Carbon::parse($a->fecha)->toDateString(), 'sede' => $a->delegacion, 'abierto' => $a->estatus === 'Pendiente', 'partes' => self::partesAudiencia(collect([$a]))[$a->id] ?? []] : null;
        }
        if ($tipo === 'ratificacion') {
            $t = self::consultaRatificaciones()->where('t.id', $id)->first();

            return $t ? ['fecha' => Carbon::parse($t->fecha)->toDateString(), 'sede' => $t->delegacion, 'abierto' => ! in_array($t->estatus, self::RATIFICACION_CERRADA, true), 'partes' => self::partesRatificacion($t)] : null;
        }
        if ($tipo === 'cumplimiento') {
            $p = self::consultaCumplimientos()->where('p.id', $id)->first();

            return $p ? ['fecha' => Carbon::parse($p->fecha)->toDateString(), 'sede' => $p->delegacion, 'abierto' => $p->estatus === 'Pendiente', 'partes' => self::partesCumplimiento($p)] : null;
        }

        return null;
    }

    private static function consultaRatificaciones()
    {
        return DB::table('turnos as t')
            ->leftJoin('users as u', 'u.id', '=', 't.id_conciliador')
            ->where('t.tipo', 'Ratificación')
            ->where(fn ($w) => $w->whereNull('t.incidencia')->orWhere('t.incidencia', 0))
            ->select([
                't.id', 't.fecha', 't.hora', 't.delegacion', 't.NUE', 't.estatus', 't.id_conciliador', 't.recepcion_id', 't.user_id',
                't.trabajador', 't.primero_trabajador', 't.segundo_trabajador', 't.trabajador_curp',
                't.nombre_empresa', 't.empresa', 't.primero_empresa', 't.segundo_empresa', 't.curp_solicitante',
                'u.name as conciliador',
            ]);
    }

    private static function consultaCumplimientos()
    {
        return DB::table('pago_solicitud as p')
            ->leftJoin('seer_general as g', fn ($j) => $j->on('g.id', '=', 'p.id_solicitud')->where('p.tipo_pago', '=', 'Audiencia'))
            ->leftJoin('turnos as t', fn ($j) => $j->on('t.id', '=', 'p.id_solicitud')->where('p.tipo_pago', '=', 'Ratificacion'))
            ->leftJoin('users as u', 'u.id', '=', 'p.id_conciliador')
            ->whereIn('p.tipo_pago', ['Audiencia', 'Ratificacion'])
            ->where(fn ($w) => $w->whereNull('p.incidencia')->orWhere('p.incidencia', 0))
            ->select([
                'p.id', 'p.fecha', 'p.hora', 'p.delegacion', 'p.tipo_pago', 'p.estatus', 'p.id_conciliador', 'u.name as conciliador',
                'p.user_id', 't.user_id as ratificacion_user',
                DB::raw("COALESCE(NULLIF(p.NUE, ''), g.NUE, t.NUE) as NUE"),
                DB::raw("COALESCE(NULLIF(p.nombre_trabajador, ''),
                    (SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = g.id ORDER BY s.id LIMIT 1),
                    TRIM(CONCAT(COALESCE(t.trabajador, ''), ' ', COALESCE(t.primero_trabajador, ''), ' ', COALESCE(t.segundo_trabajador, '')))) as nombre_trabajador"),
                DB::raw("COALESCE(NULLIF(p.empresa_representante, ''),
                    (SELECT TRIM(CONCAT(COALESCE(c.nombre, ''), ' ', COALESCE(c.primer_apellido, ''), ' ', COALESCE(c.segundo_apellido, ''))) FROM seer_citados c WHERE c.id_solicitud = g.id ORDER BY c.id LIMIT 1),
                    NULLIF(t.nombre_empresa, ''),
                    TRIM(CONCAT(COALESCE(t.empresa, ''), ' ', COALESCE(t.primero_empresa, ''), ' ', COALESCE(t.segundo_empresa, '')))) as nombre_empresa"),
            ]);
    }

    private static function partesRatificacion(object $t): array
    {
        $empresa = self::limpio($t->nombre_empresa) ?: self::limpio($t->empresa.' '.$t->primero_empresa.' '.$t->segundo_empresa);

        return array_values(array_filter([
            ['clave' => 'trabajador', 'tipo' => 'trabajador', 'rol' => 'Trabajador', 'nombre' => self::limpio($t->trabajador.' '.$t->primero_trabajador.' '.$t->segundo_trabajador)],
            ['clave' => 'empresa', 'tipo' => 'empresa', 'rol' => 'Empresa', 'nombre' => $empresa],
        ], fn ($p) => $p['nombre'] !== ''));
    }

    private static function partesCumplimiento(object $p): array
    {
        return array_values(array_filter([
            ['clave' => 'trabajador', 'tipo' => 'trabajador', 'rol' => 'Trabajador (cobra)', 'nombre' => self::limpio($p->nombre_trabajador)],
            ['clave' => 'empresa', 'tipo' => 'empresa', 'rol' => 'Empresa (paga)', 'nombre' => self::limpio($p->nombre_empresa)],
        ], fn ($x) => $x['nombre'] !== ''));
    }

    /**
     * Partes de cada audiencia: [audiencia_id => [[clave, tipo, rol, nombre], ...]].
     * Los citados se replican en cada reagenda y a veces traen el mismo nombre
     * repetido: se toman los de esta audiencia (o los de la solicitud) y se
     * agrupan por nombre. Por eso la clave se arma del nombre.
     */
    private static function partesAudiencia(Collection $audiencias): array
    {
        $solicitudes = $audiencias->pluck('id_solicitud')->unique()->values();

        $solicitantes = DB::table('seer_solicitante')
            ->whereIn('id_solicitud', $solicitudes)
            ->orderBy('id')
            ->get(['id_solicitud', 'nombre'])
            ->groupBy('id_solicitud');

        $citados = DB::table('seer_citados')
            ->whereIn('id_solicitud', $solicitudes)
            ->orderBy('id')
            ->get(['id_solicitud', 'audiencia_id', 'nombre', 'primer_apellido', 'segundo_apellido'])
            ->groupBy('id_solicitud');

        $resultado = [];
        foreach ($audiencias as $a) {
            $lista = [];

            foreach ($solicitantes[$a->id_solicitud] ?? [] as $i => $s) {
                $nombre = self::limpio($s->nombre);
                if ($nombre !== '') {
                    $lista[] = ['clave' => $i === 0 ? 'solicitante' : 'solicitante:'.self::hash($nombre), 'tipo' => 'solicitante', 'rol' => 'Trabajador', 'nombre' => $nombre];
                }
            }

            $todos  = collect($citados[$a->id_solicitud] ?? []);
            $deEsta = $todos->where('audiencia_id', $a->id);
            $vistos = [];
            foreach (($deEsta->isNotEmpty() ? $deEsta : $todos) as $c) {
                $nombre = self::limpio($c->nombre.' '.$c->primer_apellido.' '.$c->segundo_apellido);
                $hash   = self::hash($nombre);
                if ($nombre === '' || isset($vistos[$hash])) {
                    continue;
                }
                $vistos[$hash] = true;
                $lista[] = ['clave' => 'citado:'.$hash, 'tipo' => 'citado', 'rol' => 'Citado', 'nombre' => $nombre];
            }

            $resultado[$a->id] = $lista;
        }

        return $resultado;
    }

    /**
     * Liga las ratificaciones del día con su cita de Ratificación por CURP
     * (la del trabajador o la de quien la solicitó; un representante puede
     * traer varias ratificaciones con una sola cita) y, si la cita ya se
     * validó, cuenta como llegada de esa parte.
     */
    private static function ligarRatificaciones(array $sedes, string $fecha): void
    {
        $variantes = self::variantes($sedes);

        $citas = DB::table('recepcion')
            ->where('tipo', 'Ratificación')
            ->where('fecha', $fecha)
            ->whereIn('delegacion', $variantes)
            ->whereNotNull('curp')->where('curp', '!=', '')
            ->when(FlujoCita::migrado(), fn ($q) => $q->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba')))
            ->orderBy('hora')
            ->get(array_merge(['id', 'curp', 'delegacion'], FlujoCita::conSeguimiento() ? ['confirmada_at'] : []))
            ->filter(fn ($c) => strlen(trim((string) $c->curp)) === 18);

        if ($citas->isEmpty()) {
            return;
        }

        $porCurp = $citas->groupBy(fn ($c) => strtoupper(trim($c->curp)));

        $turnos = self::consultaRatificaciones()
            ->where('t.fecha', $fecha)
            ->whereIn('t.delegacion', $variantes)
            ->get();

        foreach ($turnos as $t) {
            $cita = null;
            $parte = null;
            foreach (['trabajador' => $t->trabajador_curp, 'empresa' => $t->curp_solicitante] as $quien => $curp) {
                $deEsa = $porCurp[strtoupper(trim((string) $curp))] ?? null;
                if (! $deEsa) {
                    continue;
                }
                // La que ya tiene ligada; si no, la de su misma sede.
                $cita = $t->recepcion_id ? $deEsa->firstWhere('id', $t->recepcion_id) : null;
                $cita ??= $deEsa->first(fn ($c) => self::mismaSede($c->delegacion, $t->delegacion));
                if ($cita) {
                    $parte = $quien;
                    break;
                }
            }

            if (! $cita) {
                continue;
            }
            if (empty($t->recepcion_id)) {
                DB::table('turnos')->where('id', $t->id)->whereNull('recepcion_id')->update(['recepcion_id' => $cita->id]);
            }

            if (! empty($cita->confirmada_at ?? null)) {
                $p = collect(self::partesRatificacion($t))->firstWhere('clave', $parte);
                if ($p) {
                    self::registrar('ratificacion', (int) $t->id, $p, Carbon::parse($cita->confirmada_at), null, 'cita');
                }
            }
        }
    }

    private static function registrar(string $tipo, int $id, array $parte, Carbon $cuando, ?int $por, string $origen): void
    {
        $ahora = now();
        DB::table(self::TABLA)->insertOrIgnore([
            'tipo'           => $tipo,
            'registro_id'    => $id,
            'clave'          => $parte['clave'],
            'parte'          => $parte['tipo'],
            'nombre'         => Str::limit($parte['nombre'], 250, ''),
            'origen'         => $origen,
            'llegada_at'     => $cuando,
            'registrado_por' => $por,
            'created_at'     => $ahora,
            'updated_at'     => $ahora,
        ]);
    }

    private static function url(string $tipo, int $id, $idSolicitud): ?string
    {
        return match ($tipo) {
            'audiencia'    => $idSolicitud ? url('solicitud/iniciar/'.$idSolicitud).'?audiencia_id='.$id : null,
            'ratificacion' => \Illuminate\Support\Facades\Route::has('consultar_ratificacion') ? route('consultar_ratificacion', $id) : null,
            'cumplimiento' => \Illuminate\Support\Facades\Route::has('pago_cumplimiento') ? route('pago_cumplimiento', $id) : null,
            default        => null,
        };
    }

    private static function limpio(?string $nombre): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtoupper((string) $nombre)));
    }

    private static function hash(string $nombre): string
    {
        return substr(sha1(Str::ascii($nombre)), 0, 16);
    }

    private static function mismaSede(?string $a, ?string $b): bool
    {
        return Str::lower(Str::ascii((string) $a)) === Str::lower(Str::ascii((string) $b));
    }

    /** audiencias, turnos y pago_solicitud guardan la sede a veces sin acento. */
    private static function variantes(array $sedes): array
    {
        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }
}
