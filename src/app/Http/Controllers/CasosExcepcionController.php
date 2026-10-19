<?php

namespace App\Http\Controllers;

use App\Support\CasosExcepcion;
use App\Support\FormatoExcepcion;
use App\Support\OficioCanalizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Bandeja de casos de excepción (art. 685 Ter LFT) del Departamento de
 * Conciliación, Género e Igualdad. Ver App\Support\CasosExcepcion.
 *
 *   vista=citas        citas y turnos de excepción del periodo (hoy por omisión);
 *   vista=pendientes   los de los últimos 30 días que no tienen formato;
 *   vista=solicitudes  solicitudes marcadas como excepción (sólo vista).
 */
class CasosExcepcionController extends Controller
{
    public const VISTAS = ['citas', 'pendientes', 'solicitudes'];

    private const POR_PAGINA = 25;

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'vista'  => ['nullable', Rule::in(self::VISTAS)],
            'desde'  => ['nullable', 'date_format:Y-m-d'],
            'hasta'  => ['nullable', 'date_format:Y-m-d'],
            'sede'   => ['nullable', Rule::in(CasosExcepcion::SEDES)],
            'origen' => ['nullable', Rule::in(['linea', 'ventanilla'])],
            'buscar' => ['nullable', 'string', 'max:80'],
        ]);
        $vista = $filtros['vista'] ?? 'citas';

        // Periodo: hoy en citas, el mes en solicitudes; pendientes no usa fechas.
        if (empty($filtros['desde']) && empty($filtros['hasta'])) {
            [$filtros['desde'], $filtros['hasta']] = $vista === 'solicitudes'
                ? [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]
                : [now()->toDateString(), now()->toDateString()];
        }
        $filtros['desde'] ??= $filtros['hasta'];
        $filtros['hasta'] ??= $filtros['desde'];
        if ($filtros['desde'] > $filtros['hasta']) {
            [$filtros['desde'], $filtros['hasta']] = [$filtros['hasta'], $filtros['desde']];
        }

        $conteos = [
            'citas'       => $this->citas($filtros)->count(),
            'pendientes'  => $this->pendientes($filtros)->count(),
            'solicitudes' => $this->solicitudes($filtros)->count(),
        ];

        $filas = match ($vista) {
            'solicitudes' => $this->listaSolicitudes($filtros),
            'pendientes'  => $this->listaCitas($this->pendientes($filtros)),
            default       => $this->listaCitas($this->citas($filtros)),
        };

        return view('excepciones.index', [
            'vista'   => $vista,
            'filtros' => $filtros,
            'conteos' => $conteos,
            'filas'   => $filas,
            'atiende' => CasosExcepcion::atiende($request->user()),
            'conFormato' => CasosExcepcion::conFormato(),
        ]);
    }

    /**
     * Solicitud en modo vista: sólo si es un caso de excepción. (No se llama
     * solicitud(): el Controller base ya tiene un método con ese nombre.)
     */
    public function verSolicitud(int $id)
    {
        $datos = \App\Support\VistaSolicitud::datos($id);
        abort_unless($datos && CasosExcepcion::solicitudEsCaso($datos['general']), 404);

        return view('excepciones.solicitud', $datos);
    }

    // ------------------------------------------------------------------
    // Formato de atención (art. 685 Ter) — App\Support\FormatoExcepcion
    // ------------------------------------------------------------------

    /** Ruta vieja (agenda, ligas guardadas): manda al formato de la cita. */
    public function atender(int $id)
    {
        return redirect()->route('excepciones.formato', ['tipo' => 'cita', 'id' => $id]);
    }

    public function formato(string $tipo, int $id)
    {
        $origen = FormatoExcepcion::origen($tipo, $id);

        $datos = FormatoExcepcion::datos($origen);
        $sede  = FormatoExcepcion::sede($origen);
        $oficios = [];
        if ($origen['caso'] && OficioCanalizacion::migrado()) {
            $guardados = OficioCanalizacion::delCaso((int) $origen['caso']->id);
            foreach ((array) ($datos['instituciones'] ?? []) as $inst) {
                $oficios[$inst] = OficioCanalizacion::tarjeta($guardados[$inst] ?? null, $inst, $sede, (array) ($datos['tipos'] ?? []), request()->user());
            }
        }

        return view('excepciones.formato', [
            'origen'      => $origen,
            'datos'       => $datos,
            'sede'        => $sede,
            'terminado'   => ! empty($origen['caso']->terminado_at ?? null),
            'migrado'     => CasosExcepcion::conFormato(),
            'oficios'     => $oficios,
            'puedeCerrar' => FormatoExcepcion::puedeCerrar($origen['solicitud']),
        ]);
    }

    public function guardarFormato(Request $request, string $tipo, int $id)
    {
        abort_unless(CasosExcepcion::conFormato(), 503, 'Falta correr la migración del formato 685 Ter.');

        $origen = FormatoExcepcion::origen($tipo, $id);
        $accion = $request->input('accion', 'borrador');
        $terminar = $accion === 'terminar';

        $reglas = [
            'fecha'                  => ['nullable', 'date_format:Y-m-d'],
            'expediente'             => ['nullable', 'string', 'max:60'],
            'nombre'                 => ['nullable', 'string', 'max:255'],
            'edad'                   => ['nullable', 'integer', 'between:0,120'],
            'genero'                 => ['nullable', Rule::in(['M', 'F', 'Otro'])],
            'genero_otro'            => ['nullable', 'string', 'max:60'],
            'telefono'               => ['nullable', 'string', 'max:30'],
            'correo'                 => ['nullable', 'email', 'max:150'],
            'empresa'                => ['nullable', 'string', 'max:255'],
            'area_adscripcion'       => ['nullable', 'string', 'max:255'],
            'puesto'                 => ['nullable', 'string', 'max:255'],
            'conoce_jefe'            => ['nullable', Rule::in(['0', '1'])],
            'jefe_inmediato'         => ['nullable', 'string', 'max:255'],
            'tipos'                  => ['nullable', 'array'],
            'tipos.*'                => [Rule::in(array_keys(CasosExcepcion::TIPOS))],
            'motivos'                => ['nullable', 'string', 'max:255'],
            'motivo_discriminacion'  => ['nullable', Rule::in(array_merge(CasosExcepcion::MOTIVOS_DISCRIMINACION, ['otro']))],
            'riesgo_lugar'           => ['nullable', Rule::in(['dentro', 'fuera'])],
            'frecuencia'             => ['nullable', Rule::in(array_keys(CasosExcepcion::FRECUENCIAS))],
            'situacion_laboral'      => ['nullable', Rule::in(array_keys(CasosExcepcion::SITUACIONES))],
            'situacion_otro'         => ['nullable', 'string', 'max:255'],
            'descripcion_persona'    => ['nullable', 'string', 'max:5000'],
            'descripcion_conductas'  => ['nullable', 'string', 'max:10000'],
            'observaciones'          => ['nullable', 'string', 'max:5000'],
            'vulnerables'            => ['nullable', Rule::in(CasosExcepcion::GRUPOS_VULNERABLES)],
            'resultado'              => ['nullable', Rule::in(array_keys(CasosExcepcion::RESULTADOS))],
            'instituciones'          => ['nullable', 'array'],
            'instituciones.*'        => [Rule::in(array_keys(CasosExcepcion::INSTITUCIONES))],
            'domicilio_procuraduria' => ['nullable', 'string', 'max:255'],
            'cerrar_solicitud'       => ['nullable', Rule::in(['0', '1'])],
        ];

        // Para terminar, lo mínimo que pide el formato y la decisión de la persona.
        if ($terminar) {
            $reglas['nombre'][0]     = 'required';
            $reglas['tipos'][0]      = 'required';
            $reglas['frecuencia'][0] = 'required';
            $reglas['resultado'][0]  = 'required';
            $reglas['instituciones'] = ['required_if:resultado,canaliza', 'array'];
        }

        $datos = $request->validate($reglas, [
            'tipos.required'                => 'Marca al menos un tipo de situación.',
            'frecuencia.required'           => 'Indica la frecuencia de los hechos.',
            'resultado.required'            => 'Indica si la persona continúa con la conciliación o se canaliza.',
            'instituciones.required_if'     => 'Elige al menos una institución para canalizar.',
            'nombre.required'               => 'Falta el nombre de la persona.',
        ]);

        FormatoExcepcion::guardar($origen, $datos, $terminar, $request->user());

        if ($accion === 'pdf') {
            return redirect()->route('excepciones.formato.pdf', ['tipo' => $tipo, 'id' => $id]);
        }

        return $terminar
            ? redirect()->route('excepcion', ['vista' => 'citas'])->with('success', 'Caso atendido: '.(CasosExcepcion::RESULTADOS[$datos['resultado']] ?? '').'.')
            : redirect()->route('excepciones.formato', ['tipo' => $tipo, 'id' => $id])->with('success', 'Borrador guardado. Puedes seguir llenándolo cuando quieras.');
    }

    public function pdf(string $tipo, int $id)
    {
        $origen = FormatoExcepcion::origen($tipo, $id);
        $datos = FormatoExcepcion::datos($origen);
        $atendio = ($origen['caso']->id_user ?? null) ? DB::table('users')->where('id', $origen['caso']->id_user)->value('name') : auth()->user()->name;

        $html = view('PDF.Recepcion.FormatoExcepcion', compact('datos', 'atendio'))->render();

        return \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->stream('formato_685_ter_'.preg_replace('/[^A-Za-z0-9\-]/', '_', (string) ($datos['expediente'] ?? $id)).'.pdf', ['Attachment' => false]);
    }

    // ------------------------------------------------------------------
    // Oficio de canalización (uno por institución) — OficioCanalizacion
    // ------------------------------------------------------------------

    /**
     * Guarda los datos de la canalización a una institución y, según el botón:
     * accion=aceptacion abre la aceptación (no gasta número de oficio),
     * accion=oficio aparta el número y abre el oficio, accion=guardar vuelve
     * al formato.
     */
    public function guardarOficio(Request $request, string $tipo, int $id, string $institucion)
    {
        abort_unless(OficioCanalizacion::migrado(), 503, 'Falta correr la migración de canalización.');
        $origen = FormatoExcepcion::origen($tipo, $id);
        abort_unless($origen['caso'] && array_key_exists($institucion, CasosExcepcion::INSTITUCIONES), 404);

        $datos = $request->validate([
            'accion'             => ['required', Rule::in(['guardar', 'aceptacion', 'oficio'])],
            'numero'             => ['nullable', 'string', 'max:40'],
            'fecha'              => ['required', 'date_format:Y-m-d'],
            'lugar'              => ['nullable', 'string', 'max:80'],
            'destinatario'       => ['nullable', 'string', 'max:255'],
            'cargo'              => ['nullable', 'string', 'max:255'],
            'area_servicio'      => ['nullable', 'string', 'max:255'],
            'motivo'             => ['nullable', 'string', 'max:500'],
            'cargo_atiende'      => ['nullable', 'string', 'max:255'],
            'aceptacion_firmada' => ['nullable', Rule::in(['0', '1'])],
        ]);

        OficioCanalizacion::guardar($origen['caso'], $institucion, $datos, $request->user(), $datos['accion'] === 'oficio');

        $ruta = ['tipo' => $tipo, 'id' => $id, 'institucion' => $institucion];

        return match ($datos['accion']) {
            'aceptacion' => redirect()->route('excepciones.aceptacion.pdf', $ruta),
            'oficio'     => redirect()->route('excepciones.oficio.pdf', $ruta),
            default      => redirect()->to(route('excepciones.formato', ['tipo' => $tipo, 'id' => $id]).'#oficios')
                ->with('success', 'Canalización a '.CasosExcepcion::INSTITUCIONES[$institucion].' guardada.'),
        };
    }

    public function oficioPdf(string $tipo, int $id, string $institucion)
    {
        [$origen, $oficio] = $this->canalizacion($tipo, $id, $institucion);
        abort_unless(! empty($oficio->numero), 404);

        $html = view('PDF.Recepcion.OficioCanalizacion', [
            'oficio'      => $oficio,
            'institucion' => CasosExcepcion::INSTITUCIONES[$institucion] ?? $institucion,
        ])->render();

        return \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->stream('oficio_'.str_replace('/', '-', $oficio->numero).'.pdf', ['Attachment' => false]);
    }

    /** Formato de aceptación de canalización: lo firma la persona usuaria. */
    public function aceptacionPdf(string $tipo, int $id, string $institucion)
    {
        [$origen, $oficio] = $this->canalizacion($tipo, $id, $institucion);
        $datos = FormatoExcepcion::datos($origen);
        $atendio = $oficio->user_id ? DB::table('users')->where('id', $oficio->user_id)->value('name') : null;

        $html = view('PDF.Recepcion.AceptacionCanalizacion', [
            'oficio'      => $oficio,
            'institucion' => CasosExcepcion::INSTITUCIONES[$institucion] ?? $institucion,
            'folio'       => $datos['expediente'] ?? null,
            'persona'     => $datos['nombre'] ?? null,
            'atendio'     => $atendio,
        ])->render();

        return \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->stream('aceptacion_canalizacion_'.$institucion.'.pdf', ['Attachment' => false]);
    }

    /** @return array{0: array, 1: object} */
    private function canalizacion(string $tipo, int $id, string $institucion): array
    {
        $origen = FormatoExcepcion::origen($tipo, $id);
        abort_unless($origen['caso'] && OficioCanalizacion::migrado(), 404);
        $oficio = OficioCanalizacion::delCaso((int) $origen['caso']->id)[$institucion] ?? null;
        abort_unless($oficio, 404);

        return [$origen, $oficio];
    }

    // ------------------------------------------------------------------

    private function citas(array $f)
    {
        return $this->filtrarCitas(CasosExcepcion::citas()->whereBetween('r.fecha', [$f['desde'], $f['hasta']]), $f);
    }

    /** CasosExcepcion::porAtender() con los filtros de la bandeja. */
    private function pendientes(array $f)
    {
        return $this->filtrarCitas(CasosExcepcion::porAtender(), $f);
    }

    private function filtrarCitas($q, array $f)
    {
        return $q
            ->when(! empty($f['sede']), fn ($q) => $q->where('r.delegacion', $f['sede']))
            ->when(! empty($f['origen']), fn ($q) => $q->where('r.origen', $f['origen']))
            ->when(! empty($f['buscar']), function ($q) use ($f) {
                $t = '%'.trim($f['buscar']).'%';
                $q->where(fn ($w) => $w->where('r.solicitante', 'like', $t)->orWhere('r.codigo', 'like', $t)->orWhere('r.curp', 'like', $t));
            });
    }

    private function listaCitas($q)
    {
        return $q
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->leftJoinSub(
                DB::table('seer_casos_excepcion')->whereNotNull('id_turno')
                    ->selectRaw('id_turno, MAX(id) AS id'.(CasosExcepcion::conFormato() ? ', MAX(terminado_at) AS terminado_at, MAX(resultado) AS resultado' : ', MAX(created_at) AS terminado_at, NULL AS resultado'))
                    ->groupBy('id_turno'),
                'x', 'x.id_turno', '=', 'r.id')
            ->leftJoinSub(
                DB::table('seer_general')->whereNotNull('recepcion_id')->selectRaw('recepcion_id, MIN(id) AS id, MIN(NUE) AS NUE')->groupBy('recepcion_id'),
                'g', 'g.recepcion_id', '=', 'r.id')
            ->orderBy('r.fecha')->orderBy('r.hora')
            ->select([
                'r.id', 'r.codigo', 'r.consecutivo', 'r.fecha', 'r.hora', 'r.solicitante', 'r.tipo', 'r.origen',
                'r.delegacion', 'r.tipo_caso', 'r.vulnerables', 'r.estatus', 'r.telefono', 'r.correo',
                'u.name as atiende', 'x.id as formato_id', 'x.terminado_at', 'x.resultado', 'g.id as solicitud_id', 'g.NUE as nue',
            ])
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
    }

    private function solicitudes(array $f)
    {
        return CasosExcepcion::solicitudes()
            ->whereBetween('g.fecha', [$f['desde'], $f['hasta']])
            ->when(! empty($f['sede']), fn ($q) => $q->where('g.delegacion', $f['sede']))
            ->when(! empty($f['buscar']), function ($q) use ($f) {
                $t = '%'.trim($f['buscar']).'%';
                $q->where(fn ($w) => $w->where('g.NUE', 'like', $t)
                    ->orWhereExists(fn ($s) => $s->select(DB::raw(1))->from('seer_solicitante as s')
                        ->whereColumn('s.id_solicitud', 'g.id')->where('s.nombre', 'like', $t)));
            });
    }

    private function listaSolicitudes(array $f)
    {
        // El primer solicitante y el primer citado con subconsultas por fila:
        // sólo se calculan para la página, no para toda la tabla.
        return $this->solicitudes($f)
            ->orderByDesc('g.fecha')->orderByDesc('g.id')
            ->select(['g.id', 'g.NUE as nue', 'g.fecha', 'g.delegacion', 'g.estatus'])
            ->selectRaw('(SELECT s.nombre FROM seer_solicitante s WHERE s.id_solicitud = g.id ORDER BY s.id LIMIT 1) AS solicitante')
            ->selectRaw("(SELECT TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) FROM seer_citados c WHERE c.id_solicitud = g.id ORDER BY c.id LIMIT 1) AS citado")
            ->selectRaw('(SELECT COUNT(*) FROM seer_citados c WHERE c.id_solicitud = g.id) AS citados')
            // Formato del caso: ligado a la solicitud o a la cita de la que nació.
            ->selectRaw(CasosExcepcion::conFormato()
                ? '(SELECT MAX(x.id) FROM seer_casos_excepcion x WHERE x.id_solicitud = g.id OR (g.recepcion_id IS NOT NULL AND x.id_turno = g.recepcion_id)) AS formato_id,
                   (SELECT MAX(x.terminado_at) FROM seer_casos_excepcion x WHERE x.id_solicitud = g.id OR (g.recepcion_id IS NOT NULL AND x.id_turno = g.recepcion_id)) AS terminado_at,
                   (SELECT MAX(x.resultado) FROM seer_casos_excepcion x WHERE x.id_solicitud = g.id OR (g.recepcion_id IS NOT NULL AND x.id_turno = g.recepcion_id)) AS resultado'
                : '(SELECT MAX(x.id) FROM seer_casos_excepcion x WHERE g.recepcion_id IS NOT NULL AND x.id_turno = g.recepcion_id) AS formato_id, NULL AS terminado_at, NULL AS resultado')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
    }
}
