<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Apartado de Notificaciones: inicio, "Por notificar" con asignación por
 * lote, historial y "Mis notificaciones" del notificador.
 *
 * Sustituye a SeerController::index_notificaciones, notificaciones,
 * notificaciones_consultar e hitorialnotificacador (las rutas conservan sus
 * nombres). Lo que cambió de fondo:
 *   - Todo pagina en el servidor. "Por notificar" pedía 50 y la paginación de
 *     la vista estaba comentada: lo demás nunca se veía. El historial traía
 *     3,000 filas para que DataTables las filtrara en el navegador.
 *   - Los filtros viajan en la URL.
 *   - Varias asignaciones se preparan y se mandan juntas (asignarLote).
 */
class NotificacionesController extends Controller
{
    /** Estatus en los que un citado todavía se puede asignar. */
    public const ASIGNABLES = ['Sin asignar', 'Pendiente'];

    /** Estatus que cuentan como notificación concluida con éxito. */
    public const EXITOSAS = ['Finalizado exitosamente', 'Exitosa por Instructivo', 'Notificada', 'Notificada en Audiencia', 'Recibe pero no firma'];

    public const ESTATUS = [
        'Sin asignar', 'Pendiente', 'Finalizado exitosamente', 'Exitosa por Instructivo', 'Notificada',
        'Notificada en Audiencia', 'Recibe pero no firma', 'No exitosa se constituye',
        'No exitosa no se constituye', 'No notificada', 'Exhorto',
    ];

    /** Qué sedes atiende cada delegación (la misma regla que ya usaba el sistema). */
    private const REGIONES = [
        'Morelia'   => ['Morelia', 'Zitácuaro'],
        'Uruapan'   => ['Uruapan', 'Lázaro Cárdenas'],
        'Zamora'    => ['Zamora', 'Sahuayo'],
        'Zitácuaro' => ['Zitácuaro'],
    ];

    private const POR_PAGINA = [25, 50, 100];

    private const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    // ------------------------------------------------------------------
    // Inicio
    // ------------------------------------------------------------------

    public function inicio(Request $request)
    {
        $usuario = $request->user();
        $esNotificador = $usuario->hasRole('Notificador');
        $sedes = $this->sedes($usuario);
        $inicioMes = Carbon::now()->startOfMonth();

        $citados = fn () => DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->whereIn('g.delegacion', $sedes);

        $cifras = [
            'por_asignar' => $this->basePorNotificar($sedes)->whereIn('c.estatus', self::ASIGNABLES)->count(),
            'en_campo'    => $citados()->where('c.id_notificador', '<>', 0)->where('c.estatus', 'Pendiente')->count(),
            'del_mes'     => $citados()->whereIn('c.estatus', self::EXITOSAS)->where('c.updated_at', '>=', $inicioMes)->count(),
        ];

        $mias = null;
        if ($esNotificador) {
            $propias = fn () => DB::table('seer_citados')->where('id_notificador', $usuario->id);
            $mias = [
                'pendientes' => $propias()->where('estatus', 'Pendiente')->count(),
                'del_mes'    => $propias()->whereNotIn('estatus', self::ASIGNABLES)->where('updated_at', '>=', $inicioMes)->count(),
            ];
        }

        return view('notificaciones.index_notificaciones', [
            'esNotificador' => $esNotificador,
            'sedes'         => $sedes,
            'cifras'        => $cifras,
            'mias'          => $mias,
            'mes'           => Carbon::now()->locale('es')->isoFormat('MMMM'),
        ]);
    }

    // ------------------------------------------------------------------
    // Por notificar
    // ------------------------------------------------------------------

    public function porNotificar(Request $request)
    {
        $usuario = $request->user();
        $sedes = $this->sedes($usuario);

        $filtros = $request->validate([
            'q'         => ['nullable', 'string', 'max:100'],
            'sede'      => ['nullable', Rule::in($sedes)],
            'municipio' => ['nullable', 'integer'],
            'tipo'      => ['nullable', Rule::in(['Citatorio', 'Multa'])],
            'vista'     => ['nullable', Rule::in(['asignar', 'todos'])],
            'orden'     => ['nullable', Rule::in(['recientes', 'audiencia', 'antiguos'])],
            'por'       => ['nullable', Rule::in(self::POR_PAGINA)],
        ]);
        $vista = $filtros['vista'] ?? 'asignar';
        $orden = $filtros['orden'] ?? 'recientes';

        $base = $this->basePorNotificar($sedes);
        $conteos = [
            'asignar' => (clone $base)->whereIn('c.estatus', self::ASIGNABLES)->count(),
            'todos'   => (clone $base)->count(),
        ];

        if ($vista === 'asignar') {
            $base->whereIn('c.estatus', self::ASIGNABLES);
        }

        // Municipios con pendientes: para repartir por zona.
        $municipios = (clone $base)
            ->whereNotNull('m.id')
            ->when(! empty($filtros['sede']), fn ($q) => $q->where('g.delegacion', $filtros['sede']))
            ->groupBy('m.id', 'm.nombre')
            ->orderBy('m.nombre')
            ->get(['m.id', 'm.nombre', DB::raw('COUNT(*) as total')]);

        $consulta = $base
            ->when(! empty($filtros['sede']), fn ($q) => $q->where('g.delegacion', $filtros['sede']))
            ->when(! empty($filtros['municipio']), fn ($q) => $q->where('c.municipio_citado', $filtros['municipio']))
            ->when(! empty($filtros['tipo']), fn ($q) => $q->where('c.tipo_notificacion', $filtros['tipo']))
            ->when(! empty($filtros['q']), function ($q) use ($filtros) {
                $t = '%'.trim($filtros['q']).'%';
                $q->where(function ($w) use ($t) {
                    $w->where('g.NUE', 'like', $t)
                      ->orWhereRaw("CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido) LIKE ?", [$t])
                      ->orWhere('c.colonia', 'like', $t)
                      ->orWhere('c.calle', 'like', $t)
                      ->orWhere('m.nombre', 'like', $t);
                });
            })
            // Próxima audiencia del expediente: sirve para priorizar.
            ->leftJoinSub(
                DB::table('audiencias')
                    ->select('id_solicitud', DB::raw('MIN(fecha) as proxima'))
                    ->where('fecha', '>=', Carbon::today()->toDateString())
                    ->groupBy('id_solicitud'),
                'a', 'a.id_solicitud', '=', 'c.id_solicitud'
            );

        match ($orden) {
            'audiencia' => $consulta->orderByRaw('a.proxima IS NULL')->orderBy('a.proxima')->orderByDesc('c.created_at'),
            'antiguos'  => $consulta->orderBy('c.created_at'),
            default     => $consulta->orderByDesc('c.created_at'),
        };

        $citados = $consulta
            ->select([
                'c.id as id_citado', 'c.id_solicitud', 'g.NUE', 'g.delegacion',
                'c.nombre', 'c.primer_apellido', 'c.segundo_apellido',
                'c.colonia', 'c.tipo_vialidad', 'c.calle', 'c.n_ext', 'c.n_int', 'c.cp',
                'm.nombre as municipio', 'e.nombre as estado',
                'c.estatus', 'c.tipo_notificacion', 'c.created_at', 'a.proxima',
            ])
            ->paginate((int) ($filtros['por'] ?? 25))
            ->withQueryString();

        return view('notificaciones.index', [
            'citados'       => $citados,
            'filtros'       => $filtros,
            'vista'         => $vista,
            'orden'         => $orden,
            'conteos'       => $conteos,
            'sedes'         => $sedes,
            'municipios'    => $municipios,
            'notificadores' => $this->notificadores($sedes),
            'porPagina'     => self::POR_PAGINA,
            'puedeAsignar'  => $usuario->can('por_notificar_asignar'),
        ]);
    }

    /**
     * Manda de una vez varias asignaciones preparadas en "Por notificar".
     * asignaciones[id_citado] = id_notificador.
     *
     * Cada una se valida por separado: si otra persona ya asignó ese citado
     * mientras se preparaba el lote, se omite y se avisa; las demás pasan.
     */
    public function asignarLote(Request $request)
    {
        $usuario = $request->user();
        $sedes = $this->sedes($usuario);

        $datos = $request->validate([
            'asignaciones'   => ['required', 'array', 'min:1', 'max:500'],
            'asignaciones.*' => ['required', 'integer'],
        ], [
            'asignaciones.required' => 'No hay asignaciones preparadas.',
            'asignaciones.max'      => 'Se pueden mandar hasta 500 asignaciones por lote.',
        ]);

        $pedidas = collect($datos['asignaciones'])
            ->mapWithKeys(fn ($notificador, $citado) => [(int) $citado => (int) $notificador])
            ->filter(fn ($notificador, $citado) => $citado > 0);

        // Sin caché: aquí se valida, no se muestra.
        Cache::forget($this->claveNotificadores($sedes));
        $validos = $this->notificadores($sedes)->keyBy('id');

        $citados = DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->whereIn('c.id', $pedidas->keys())
            ->whereIn('g.delegacion', $sedes)
            ->get(['c.id', 'c.id_notificador', 'c.estatus', 'g.NUE'])
            ->keyBy('id');

        $asignados = [];
        $omitidos = [];

        DB::transaction(function () use ($pedidas, $citados, $validos, &$asignados, &$omitidos) {
            foreach ($pedidas as $idCitado => $idNotificador) {
                $citado = $citados->get($idCitado);
                $nue = $citado->NUE ?? "#{$idCitado}";

                $motivo = match (true) {
                    $citado === null                                     => 'no está en tus sedes',
                    ! $validos->has($idNotificador)                       => 'el notificador no es válido para la sede',
                    (int) $citado->id_notificador !== 0                   => 'ya tenía notificador',
                    ! in_array($citado->estatus, self::ASIGNABLES, true)  => "su estatus es \"{$citado->estatus}\"",
                    default                                               => null,
                };

                if ($motivo === null) {
                    // La condición id_notificador = 0 en el UPDATE evita pisar a
                    // quien lo haya asignado entre la lectura y la escritura.
                    $hecho = DB::table('seer_citados')
                        ->where('id', $idCitado)
                        ->where('id_notificador', 0)
                        ->update(['id_notificador' => $idNotificador, 'estatus' => 'Pendiente', 'updated_at' => now()]
                            + (\App\Support\DesempenoNotificadores::conAsignacion() ? ['asignado_at' => now()] : []));
                    $motivo = $hecho ? null : 'ya tenía notificador';
                }

                if ($motivo) {
                    $omitidos[] = ['nue' => $nue, 'motivo' => $motivo];
                } else {
                    $asignados[$idNotificador][] = $nue;
                }
            }
        });

        Cache::forget($this->claveNotificadores($sedes));

        $resumen = collect($asignados)->map(fn ($nues, $id) => [
            'notificador' => $validos[$id]->name,
            'total'       => count($nues),
        ])->sortByDesc('total')->values()->all();

        Log::info('Asignación de notificaciones por lote', [
            'por'       => $usuario->id,
            'asignados' => collect($asignados)->map(fn ($n) => count($n))->all(),
            'omitidos'  => count($omitidos),
        ]);

        return redirect()
            ->to(url()->previous() ?: route('notificaciones'))
            ->with('lote', [
                'total'    => collect($asignados)->flatten()->count(),
                'resumen'  => $resumen,
                'omitidos' => $omitidos,
            ]);
    }

    // ------------------------------------------------------------------
    // Historial (todas las notificaciones)
    // ------------------------------------------------------------------

    public function historial(Request $request)
    {
        $usuario = $request->user();
        $restringido = $usuario->hasAnyRole(['Enlace', 'Estadistica']);
        $sedesPermitidas = $restringido ? $this->sedes($usuario) : self::SEDES;

        $filtros = $request->validate([
            'buscar'      => ['nullable', 'string', 'max:100'],
            'estatus'     => ['nullable', Rule::in(self::ESTATUS)],
            'notificador' => ['nullable', 'string', 'max:12'],
            'tipo'        => ['nullable', Rule::in(['Citatorio', 'Multa'])],
            'por'         => ['nullable', Rule::in(self::POR_PAGINA)],
            // Filtro avanzado: sede, periodo y por qué fecha se filtra la lista.
            'sede'        => ['nullable', Rule::in($sedesPermitidas)],
            'desde'       => ['nullable', 'date_format:Y-m-d'],
            'hasta'       => ['nullable', 'date_format:Y-m-d'],
            'fecha_de'    => ['nullable', Rule::in(['notificacion', 'asignacion'])],
        ]);
        // Si las pusieron al revés, se acomodan en lugar de marcar error.
        if (! empty($filtros['desde']) && ! empty($filtros['hasta']) && $filtros['desde'] > $filtros['hasta']) {
            [$filtros['desde'], $filtros['hasta']] = [$filtros['hasta'], $filtros['desde']];
        }

        $query = DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->leftJoin('users as n', 'n.id', '=', 'c.id_notificador')
            ->where('g.estatus', '!=', 'Pendiente')
            ->when($restringido, fn ($q) => $q->whereIn('g.delegacion', $this->sedes($usuario)));

        $notificador = $filtros['notificador'] ?? null;

        $query
            ->when(! empty($filtros['tipo']), fn ($q) => $q->where('c.tipo_notificacion', $filtros['tipo']))
            ->when($notificador === 'sin', fn ($q) => $q->where('c.id_notificador', 0))
            ->when($notificador && ctype_digit($notificador), fn ($q) => $q->where('c.id_notificador', (int) $notificador))
            ->when(! empty($filtros['sede']), fn ($q) => $q->where('g.delegacion', $filtros['sede']))
            ->when(! empty($filtros['buscar']), function ($q) use ($filtros) {
                $t = '%'.trim($filtros['buscar']).'%';
                $q->where(function ($w) use ($t) {
                    $w->where('g.NUE', 'like', $t)
                      ->orWhereRaw("CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido) LIKE ?", [$t])
                      ->orWhere('c.colonia', 'like', $t)
                      ->orWhere('c.calle', 'like', $t)
                      ->orWhere('m.nombre', 'like', $t);
                });
            });

        // Cifras del periodo (filtro avanzado): sobre la consulta con sede,
        // notificador, tipo y búsqueda, pero sin estatus ni el filtro de fecha
        // de la lista, porque cada cifra usa su propia fecha.
        $rango = \App\Support\DesempenoNotificadores::rango($filtros['desde'] ?? null, $filtros['hasta'] ?? null);
        $resumen = \App\Support\DesempenoNotificadores::resumen($query, $rango);
        $porNotificador = ($notificador && ctype_digit($notificador)) || $notificador === 'sin'
            ? collect()
            : \App\Support\DesempenoNotificadores::porNotificador($query, $rango);

        \App\Support\DesempenoNotificadores::filtrarLista($query, $rango, $filtros['fecha_de'] ?? 'notificacion');

        // Las pestañas de estatus cuentan con los demás filtros ya aplicados.
        $conteos = (clone $query)
            ->select('c.estatus', DB::raw('COUNT(*) as total'))
            ->groupBy('c.estatus')
            ->pluck('total', 'estatus');

        $notificaciones = $query
            ->when(! empty($filtros['estatus']), fn ($q) => $q->where('c.estatus', $filtros['estatus']))
            ->orderByDesc('c.created_at')
            ->select([
                'c.id', 'c.id_solicitud', 'c.id_notificador', 'g.NUE', 'g.delegacion',
                'c.nombre', 'c.primer_apellido', 'c.segundo_apellido',
                'c.colonia', 'c.tipo_vialidad', 'c.calle', 'c.n_ext', 'c.n_int',
                'm.nombre as municipio', 'e.nombre as estado',
                'c.estatus', 'c.notificacion', 'c.tipo_notificacion', 'c.updated_at', 'c.fecha',
                'n.name as notificador_nombre',
            ])
            ->paginate((int) ($filtros['por'] ?? 25))
            ->withQueryString();

        // Notificadores por sede: para reasignar y para el filtro.
        $notificadoresPorSede = User::whereHas('roles', fn ($q) => $q->where('name', 'Notificador'))
            ->when($restringido, fn ($q) => $q->whereIn('delegacion', $this->sedes($usuario)))
            ->orderBy('name')
            ->get(['id', 'name', 'delegacion'])
            ->groupBy('delegacion');

        $elegido = $notificador && ctype_digit($notificador) ? User::find((int) $notificador, ['id', 'name', 'email', 'delegacion']) : null;

        return view('notificaciones.index_busqueda', [
            'notificaciones'       => $notificaciones,
            'filtros'              => $filtros,
            'conteos'              => $conteos,
            'notificadoresPorSede' => $notificadoresPorSede,
            'porPagina'            => self::POR_PAGINA,
            'sedesPermitidas'      => $sedesPermitidas,
            'resumen'              => $resumen,
            'porNotificador'       => $porNotificador,
            'notificadorElegido'   => $elegido,
            'hayPeriodo'           => (bool) $rango,
        ]);
    }

    // ------------------------------------------------------------------
    // Mis notificaciones (notificador)
    // ------------------------------------------------------------------

    public function misNotificaciones(Request $request)
    {
        $usuario = $request->user();

        $filtros = $request->validate([
            'buscar'  => ['nullable', 'string', 'max:100'],
            'estatus' => ['nullable', Rule::in(self::ESTATUS)],
            'por'     => ['nullable', Rule::in(self::POR_PAGINA)],
        ]);

        // El solicitante se toma con una subconsulta (el primero) para no
        // repetir la fila cuando el expediente tiene varios.
        $solicitante = DB::table('seer_solicitante')
            ->select('id_solicitud', DB::raw('MIN(nombre) as nombre'))
            ->groupBy('id_solicitud');

        $query = DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoinSub($solicitante, 's', 's.id_solicitud', '=', 'g.id')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->where('c.id_notificador', $usuario->id)
            ->where('c.estatus', '!=', 'Pendiente');

        $query->when(! empty($filtros['buscar']), function ($q) use ($filtros) {
                $t = '%'.trim($filtros['buscar']).'%';
                $q->where(function ($w) use ($t) {
                    $w->where('g.NUE', 'like', $t)
                      ->orWhere('s.nombre', 'like', $t)
                      ->orWhereRaw("CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido) LIKE ?", [$t])
                      ->orWhere('c.colonia', 'like', $t)
                      ->orWhere('c.calle', 'like', $t)
                      ->orWhere('m.nombre', 'like', $t);
                });
            });

        $conteos = (clone $query)
            ->select('c.estatus', DB::raw('COUNT(*) as total'))
            ->groupBy('c.estatus')
            ->pluck('total', 'estatus');

        $notificaciones = $query
            ->when(! empty($filtros['estatus']), fn ($q) => $q->where('c.estatus', $filtros['estatus']))
            ->orderByDesc('c.updated_at')
            ->select([
                'c.id as id_citado', 'c.id_solicitud', 'g.NUE', 's.nombre as nombre_solicitado',
                'c.nombre', 'c.primer_apellido', 'c.segundo_apellido',
                'c.colonia', 'c.tipo_vialidad', 'c.calle', 'c.n_ext', 'c.n_int',
                'm.nombre as municipio', 'e.nombre as estado',
                'c.estatus', 'c.tipo_notificacion', 'c.updated_at',
            ])
            ->paginate((int) ($filtros['por'] ?? 25))
            ->withQueryString();

        return view('notificaciones.indexHitorial', [
            'mis_notificaciones' => $notificaciones,
            'filtros'            => $filtros,
            'conteos'            => $conteos,
            'porPagina'          => self::POR_PAGINA,
        ]);
    }

    // ------------------------------------------------------------------

    private function sedes(User $usuario): array
    {
        return self::REGIONES[$usuario->delegacion] ?? [$usuario->delegacion];
    }

    /** Citados sin notificador de estas sedes (lo que "Por notificar" siempre mostró). */
    private function basePorNotificar(array $sedes)
    {
        return DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->whereIn('g.delegacion', $sedes)
            ->where('c.id_notificador', 0)
            ->where('c.notificacion', '!=', 'Trabajador')
            ->whereNotIn('g.estatus', ['Pendiente', 'Prevencion']);
    }

    private function claveNotificadores(array $sedes): string
    {
        return 'notificadores_carga_'.md5(implode('|', $sedes));
    }

    /**
     * Notificadores activos de las sedes, con cuántas tienen en campo: ayuda
     * a repartir parejo. Se cachea un minuto.
     */
    private function notificadores(array $sedes): Collection
    {
        return Cache::remember($this->claveNotificadores($sedes), 60, function () use ($sedes) {
            $gente = User::whereHas('roles', fn ($q) => $q->where('name', 'Notificador'))
                ->whereIn('delegacion', $sedes)
                ->where('estatus', 'Activo')
                ->orderBy('name')
                ->get(['id', 'name', 'delegacion']);

            $carga = DB::table('seer_citados')
                ->whereIn('id_notificador', $gente->pluck('id'))
                ->where('estatus', 'Pendiente')
                ->groupBy('id_notificador')
                ->select('id_notificador', DB::raw('COUNT(*) as total'))
                ->pluck('total', 'id_notificador');

            return $gente->map(function ($u) use ($carga) {
                $u->en_campo = (int) ($carga[$u->id] ?? 0);

                return $u;
            });
        });
    }
}
