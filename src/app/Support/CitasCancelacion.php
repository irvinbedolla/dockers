<?php

namespace App\Support;

use App\Http\Controllers\CancelacionCitasController;
use App\Models\HistorialAdministracion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Administración → Cancelación/Reagenda de citas (CancelacionCitasController):
 * filtros y consultas de sus dos pestañas.
 *
 *   citas        las citas (recepcion) por rango de fechas, sede, trámite,
 *                estatus y origen, o buscadas por folio, CURP o nombre; cada
 *                una con cuántas veces se ha reagendado o si se canceló.
 *   movimientos  cada cancelación y reagenda hecha desde esta pantalla
 *                (historial_administracion), por fecha del movimiento, sede,
 *                tipo de movimiento, trámite y quién lo hizo.
 *
 * El alcance (qué sedes ve cada quien) lo pone el controlador: $sedes = null
 * es todas.
 */
class CitasCancelacion
{
    public const VISTAS = ['citas' => 'Citas', 'movimientos' => 'Movimientos'];

    public const MOVIMIENTOS = ['cancelacion' => 'Cancelación', 'reagenda' => 'Reagenda'];

    public const ESTATUS = [
        'pendiente'  => 'Pendiente',
        'confirmada' => 'Confirmada',
        'atendido'   => 'Atendida',
        'expirada'   => 'Expirada',
        'cancelada'  => 'Cancelada',
    ];

    public const ORIGENES = ['linea' => 'En línea', 'ventanilla' => 'Ventanilla'];

    /** Trámite => cómo puede venir escrito en recepcion. */
    public const TRAMITES = [
        'Solicitud'    => ['Solicitud'],
        'Asesoría'     => ['Asesoría', 'Asesoria'],
        'Ratificación' => ['Ratificación', 'Ratificacion'],
        'Cumplimiento' => ['Cumplimiento'],
    ];

    public const POR_PAGINA = 50;

    /** Rango máximo que se consulta de una vez (y el máximo del Excel). */
    public const MAX_DIAS = 366;
    public const MAX_FILAS = 10000;

    /** Filtros de la petición, ya limpios. $sedes: las que puede ver (los nombres para el selector). */
    public static function filtros(Request $request, array $sedes): array
    {
        $d = $request->validate([
            'vista'      => ['nullable', Rule::in(array_keys(self::VISTAS))],
            'fecha'      => ['nullable', 'date_format:Y-m-d'],
            'desde'      => ['nullable', 'date_format:Y-m-d'],
            'hasta'      => ['nullable', 'date_format:Y-m-d'],
            'sede'       => ['nullable', 'string', 'max:40'],
            'tramite'    => ['nullable', Rule::in(array_keys(self::TRAMITES))],
            'estatus'    => ['nullable', Rule::in(array_keys(self::ESTATUS))],
            'origen'     => ['nullable', Rule::in(array_keys(self::ORIGENES))],
            'movimiento' => ['nullable', Rule::in(array_keys(self::MOVIMIENTOS))],
            'usuario'    => ['nullable', 'integer'],
            'buscar'     => ['nullable', 'string', 'max:100'],
        ]);

        $vista = $d['vista'] ?? 'citas';
        $hoy   = Carbon::today();

        // "fecha" (un solo día) se sigue aceptando: así llegan las flechas y ligas viejas.
        if (! empty($d['fecha'])) {
            $d['desde'] = $d['hasta'] = $d['fecha'];
        }
        $porOmision = $vista === 'movimientos' ? $hoy->copy()->subDays(29) : $hoy;
        $desde = Carbon::parse($d['desde'] ?? ($d['hasta'] ?? $porOmision->toDateString()));
        $hasta = Carbon::parse($d['hasta'] ?? ($vista === 'movimientos' && empty($d['desde']) ? $hoy->toDateString() : $desde->toDateString()));
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        $recortado = false;
        if ($desde->diffInDays($hasta) >= self::MAX_DIAS) {
            $hasta = $desde->copy()->addDays(self::MAX_DIAS - 1);
            $recortado = true;
        }

        $buscar = trim($d['buscar'] ?? '');

        return [
            'vista'      => $vista,
            'desde'      => $desde->toDateString(),
            'hasta'      => $hasta->toDateString(),
            'unDia'      => $desde->equalTo($hasta),
            'recortado'  => $recortado,
            'sede'       => in_array($d['sede'] ?? null, $sedes, true) ? $d['sede'] : null,
            'tramite'    => $d['tramite'] ?? null,
            'estatus'    => $vista === 'citas' ? ($d['estatus'] ?? null) : null,
            'origen'     => $d['origen'] ?? null,
            'movimiento' => $vista === 'movimientos' ? ($d['movimiento'] ?? null) : null,
            'usuario'    => $vista === 'movimientos' && ! empty($d['usuario']) ? (int) $d['usuario'] : null,
            'buscar'     => $buscar,
            'buscando'   => mb_strlen($buscar) >= 3,
        ];
    }

    /** Sólo los filtros que cambian algo (para ligas, "Limpiar" y el Excel). */
    public static function query(array $f, array $cambios = []): array
    {
        $f = array_merge($f, $cambios);

        return array_filter([
            'vista'      => $f['vista'] !== 'citas' ? $f['vista'] : null,
            'desde'      => $f['desde'],
            'hasta'      => $f['hasta'],
            'sede'       => $f['sede'],
            'tramite'    => $f['tramite'],
            'estatus'    => $f['estatus'],
            'origen'     => $f['origen'],
            'movimiento' => $f['movimiento'],
            'usuario'    => $f['usuario'],
            'buscar'     => $f['buscar'] !== '' ? $f['buscar'] : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** Nombres de sede con y sin acento (en recepcion hay "Lazaro Cardenas"). */
    public static function variantes(array $sedes): array
    {
        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }

    // ── Citas ────────────────────────────────────────────────────────────

    /** Las citas filtradas, sin orden (sirve para contar y para listar). */
    public static function consultaCitas(Builder $base, array $f): Builder
    {
        $q = $base->clone();

        if ($f['sede']) {
            $q->whereIn('delegacion', self::variantes([$f['sede']]));
        }
        if ($f['buscando']) {
            // La búsqueda es de cualquier día: no usa el rango de fechas.
            $buscar = $f['buscar'];
            $q->where(fn ($w) => $w->where('codigo', $buscar)
                ->orWhere('codigo', FolioCita::normalizar($buscar) ?? $buscar)
                ->orWhere('consecutivo', $buscar)
                ->orWhere('curp', mb_strtoupper($buscar))
                ->orWhere('solicitante', 'like', '%'.$buscar.'%')
                ->orWhere('correo', $buscar)
                ->orWhere('telefono', $buscar));
        } else {
            $q->whereBetween('fecha', [$f['desde'], $f['hasta']]);
        }
        if ($f['tramite']) {
            $q->whereIn('tipo', self::TRAMITES[$f['tramite']]);
        }
        if ($f['origen']) {
            $q->where('origen', $f['origen']);
        }

        return $q;
    }

    /** Cuántas hay de cada estatus (sin el filtro de estatus, para las tarjetas). */
    public static function resumenCitas(Builder $q): array
    {
        $porEstatus = $q->clone()->toBase()->selectRaw('estatus, COUNT(*) as n')->groupBy('estatus')->pluck('n', 'estatus')->map(fn ($n) => (int) $n);

        $res = ['total' => (int) $porEstatus->sum()];
        foreach (array_keys(self::ESTATUS) as $e) {
            $res[$e] = (int) ($porEstatus[$e] ?? 0);
        }
        $res['enLinea'] = (int) $q->clone()->where('origen', 'linea')->count();

        return $res;
    }

    public static function listarCitas(Builder $q, array $f): LengthAwarePaginator
    {
        $q = $q->clone();
        if ($f['estatus']) {
            $q->where('estatus', $f['estatus']);
        }
        $f['buscando'] ? $q->orderByDesc('fecha')->orderBy('hora') : $q->orderBy('fecha')->orderBy('hora')->orderBy('delegacion');

        return $q->orderBy('id')->paginate(self::POR_PAGINA)->withQueryString();
    }

    /**
     * Lo que ya se le hizo a cada cita desde esta pantalla:
     * [id => ['reagendas' => n, 'cancelada' => ['cuando', 'quien', 'motivo'] | null, 'ultimo' => texto]].
     */
    public static function historialDe(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        $movs = HistorialAdministracion::where('tipo', CancelacionCitasController::TIPO_BITACORA)
            ->where('tabla', 'recepcion')
            ->whereIn('registro_id', $ids->all())
            ->orderBy('id')
            ->get(['registro_id', 'motivo', 'user_nombre', 'created_at']);

        $res = [];
        foreach ($movs->groupBy('registro_id') as $id => $suyos) {
            $reagendas = $suyos->filter(fn ($m) => self::tipoMovimiento($m->motivo) === 'reagenda');
            $cancelo   = $suyos->first(fn ($m) => self::tipoMovimiento($m->motivo) === 'cancelacion');
            $ultimo    = $suyos->last();
            $res[$id] = [
                'reagendas' => $reagendas->count(),
                'cancelada' => $cancelo ? [
                    'cuando' => Carbon::parse($cancelo->created_at)->format('d/m/Y H:i'),
                    'quien'  => self::nombre($cancelo->user_nombre),
                    'motivo' => self::motivoLimpio($cancelo->motivo),
                ] : null,
                'ultimo' => self::MOVIMIENTOS[self::tipoMovimiento($ultimo->motivo)].' · '
                    .Carbon::parse($ultimo->created_at)->format('d/m/Y H:i').' · '.self::nombre($ultimo->user_nombre)
                    .' — '.self::motivoLimpio($ultimo->motivo),
            ];
        }

        return $res;
    }

    // ── Movimientos ──────────────────────────────────────────────────────

    /** Cancelaciones y reagendas filtradas, ya armadas (más recientes primero). */
    public static function movimientos(?array $sedes, array $f, int $max = self::MAX_FILAS): Collection
    {
        $q = DB::table('historial_administracion as h')
            ->leftJoin('recepcion as r', 'r.id', '=', 'h.registro_id')
            ->where('h.tipo', CancelacionCitasController::TIPO_BITACORA)
            ->where('h.tabla', 'recepcion');

        if ($sedes !== null) {
            $q->whereIn('h.delegacion', self::variantes($sedes));
        }
        if ($f['sede']) {
            $q->whereIn('h.delegacion', self::variantes([$f['sede']]));
        }
        if ($f['buscando']) {
            $buscar = $f['buscar'];
            $q->where(fn ($w) => $w->where('h.NUE', $buscar)
                ->orWhere('h.NUE', FolioCita::normalizar($buscar) ?? $buscar)
                ->orWhere('r.consecutivo', $buscar)
                ->orWhere('r.curp', mb_strtoupper($buscar))
                ->orWhere('r.solicitante', 'like', '%'.$buscar.'%'));
        } else {
            $q->whereBetween('h.created_at', [$f['desde'].' 00:00:00', $f['hasta'].' 23:59:59']);
        }
        if ($f['movimiento'] === 'cancelacion') {
            $q->where('h.motivo', 'like', 'Cancelaci%');
        } elseif ($f['movimiento'] === 'reagenda') {
            $q->where('h.motivo', 'like', 'Reagenda%');
        }
        if ($f['usuario']) {
            $q->where('h.user_id', $f['usuario']);
        }
        if ($f['tramite']) {
            $q->whereIn('r.tipo', self::TRAMITES[$f['tramite']]);
        }
        if ($f['origen']) {
            $q->where('r.origen', $f['origen']);
        }

        return $q->orderByDesc('h.created_at')->orderByDesc('h.id')->limit($max)
            ->get([
                'h.id', 'h.registro_id', 'h.NUE', 'h.delegacion', 'h.motivo', 'h.user_id', 'h.user_nombre', 'h.created_at',
                'h.datos_antes', 'h.datos_despues',
                'r.codigo', 'r.consecutivo', 'r.tipo', 'r.solicitante', 'r.estatus', 'r.origen', 'r.correo', 'r.telefono',
            ])
            ->map(fn ($m) => self::armarMovimiento($m));
    }

    public static function resumenMovimientos(Collection $movs): array
    {
        $cuenta = fn (string $campo) => $movs->countBy($campo)->sortDesc()->all();

        return [
            'total'        => $movs->count(),
            'cancelacion'  => $movs->where('movimiento', 'cancelacion')->count(),
            'reagenda'     => $movs->where('movimiento', 'reagenda')->count(),
            'citas'        => $movs->pluck('cita_id')->filter()->unique()->count(),
            'porSede'      => $cuenta('sede'),
            'porUsuario'   => array_slice($cuenta('quien'), 0, 6, true),
            'porTramite'   => $cuenta('tramite'),
            // Días que se movió una reagenda, en promedio (positivo: a después).
            'diasPromedio' => ($d = $movs->where('movimiento', 'reagenda')->pluck('dias')->filter(fn ($x) => $x !== null))->isEmpty()
                ? null : round($d->avg(), 1),
        ];
    }

    public static function paginar(Collection $filas, Request $request): LengthAwarePaginator
    {
        $pagina = max(1, (int) $request->query('page', 1));

        return (new LengthAwarePaginator(
            $filas->forPage($pagina, self::POR_PAGINA)->values(), $filas->count(), self::POR_PAGINA, $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        ));
    }

    /** Quiénes han hecho movimientos (para el filtro), dentro de su alcance. */
    public static function usuarios(?array $sedes): array
    {
        $q = DB::table('historial_administracion')
            ->where('tipo', CancelacionCitasController::TIPO_BITACORA)
            ->whereNotNull('user_id');
        if ($sedes !== null) {
            $q->whereIn('delegacion', self::variantes($sedes));
        }

        return $q->select('user_id', DB::raw('MAX(user_nombre) as nombre'))->groupBy('user_id')->get()
            ->mapWithKeys(fn ($u) => [(int) $u->user_id => self::nombre($u->nombre)])
            ->sort()->all();
    }

    private static function armarMovimiento(object $m): array
    {
        $antes   = self::json($m->datos_antes);
        $despues = self::json($m->datos_despues);
        $tipo    = self::tipoMovimiento($m->motivo);

        $fechaAntes = substr((string) ($antes['fecha'] ?? ''), 0, 10) ?: null;
        $horaAntes  = substr((string) ($antes['hora'] ?? ''), 0, 5) ?: null;
        $fechaNueva = $tipo === 'reagenda' ? (substr((string) ($despues['fecha'] ?? ''), 0, 10) ?: $fechaAntes) : null;
        $horaNueva  = $tipo === 'reagenda' ? (substr((string) ($despues['hora'] ?? ''), 0, 5) ?: $horaAntes) : null;
        $tramite    = (string) ($m->tipo ?? ($antes['tipo'] ?? ''));

        return [
            'id'          => $m->id,
            'cita_id'     => $m->registro_id,
            'cuando'      => Carbon::parse($m->created_at),
            'movimiento'  => $tipo,
            'folio'       => $m->codigo !== null || $m->consecutivo !== null ? FolioCita::mostrar($m) : ((string) $m->NUE ?: '—'),
            'solicitante' => Str::title(mb_strtolower(trim((string) ($m->solicitante ?? ($antes['solicitante'] ?? ''))))) ?: '—',
            'sede'        => self::sede($m->delegacion) ?: '—',
            'tramite'     => self::tramite($tramite) ?: '—',
            'origen'      => self::ORIGENES[$m->origen ?? ($antes['origen'] ?? '')] ?? '—',
            'estatusHoy'  => $m->estatus,
            'antes'       => $fechaAntes ? Carbon::parse($fechaAntes)->format('d/m/Y').($horaAntes ? ' '.$horaAntes : '') : '—',
            'despues'     => $fechaNueva ? Carbon::parse($fechaNueva)->format('d/m/Y').($horaNueva ? ' '.$horaNueva : '') : null,
            'dias'        => $fechaAntes && $fechaNueva ? (int) Carbon::parse($fechaAntes)->diffInDays(Carbon::parse($fechaNueva), false) : null,
            'motivo'      => self::motivoLimpio($m->motivo),
            'quien'       => self::nombre($m->user_nombre),
            'contacto'    => trim(implode(' · ', array_filter([$m->correo ?? null, $m->telefono ?? null]))),
            // Lo que cambió el Super Usuario además de la fecha: ['Sede' => [antes, ahora], ...].
            'otros'       => array_filter([
                'Sede'    => isset($despues['delegacion']) ? [self::sede($antes['delegacion'] ?? null), self::sede($despues['delegacion'])] : null,
                'Trámite' => isset($despues['tipo']) ? [self::tramite($antes['tipo'] ?? null), self::tramite($despues['tipo'])] : null,
                'Folio'   => isset($despues['codigo']) ? [(string) ($antes['codigo'] ?? ''), (string) $despues['codigo']] : null,
            ]),
        ];
    }

    public static function tipoMovimiento(?string $motivo): string
    {
        return str_starts_with((string) $motivo, 'Cancelaci') ? 'cancelacion' : 'reagenda';
    }

    /** Sin el "Cancelación: " / "Reagenda (antes …): " que le pone el controlador. */
    public static function motivoLimpio(?string $motivo): string
    {
        return trim((string) preg_replace('/^(Cancelaci[oó]n|Reagenda(\s*\([^)]*\))?)\s*:\s*/u', '', (string) $motivo));
    }

    /** El nombre de la sede como en el catálogo ("Lazaro Cardenas" → "Lázaro Cárdenas"). */
    public static function sede(?string $sede): string
    {
        foreach (Recepcion::TODAS_LAS_SEDES as $nombre) {
            if ($sede === $nombre || ($sede !== null && Str::ascii($sede) === Str::ascii($nombre))) {
                return $nombre;
            }
        }

        return (string) $sede;
    }

    public static function tramite(?string $tipo): ?string
    {
        foreach (self::TRAMITES as $nombre => $formas) {
            if (in_array($tipo, $formas, true)) {
                return $nombre;
            }
        }

        return $tipo ?: null;
    }

    private static function nombre(?string $nombre): string
    {
        return Str::title(mb_strtolower(trim((string) $nombre))) ?: 'Sin usuario';
    }

    private static function json($valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }
        $d = json_decode((string) $valor, true);

        return is_array($d) ? $d : [];
    }
}
