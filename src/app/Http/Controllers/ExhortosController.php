<?php

namespace App\Http\Controllers;

use App\Models\Exhorto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Notificaciones → Exhortos. Lo usan quienes pueden asignar notificaciones
 * (permiso por_notificar_asignar).
 *
 * Registra cada exhorto con su expediente, solicitante, citado, estado y
 * municipio, y si ya se contestó. "Por registrar" muestra a los citados que
 * el notificador marcó como "Exhorto" y que todavía no tienen su registro,
 * para darlos de alta con un clic.
 */
class ExhortosController extends Controller
{
    private const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    private const VISTAS = ['pendientes', 'contestados', 'todos', 'por_registrar'];

    private const POR_PAGINA = 25;

    /** ¿Ya corrió la migración? */
    public static function disponible(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasTable('exhortos');
    }

    /** Super Usuario ve todas las sedes; los demás, las de su región (como en Notificaciones). */
    public static function sedes(User $usuario): array
    {
        return $usuario->hasRole('Super Usuario')
            ? self::SEDES
            : NotificacionesController::sedesDeUsuario($usuario);
    }

    /** Cifras para el inicio de Notificaciones. */
    public static function cifras(User $usuario): array
    {
        $sedes = self::sedes($usuario);

        return [
            'sin_contestar' => self::lista($sedes)->where('x.contestado', false)->count(),
            'por_registrar' => self::porRegistrar($sedes)->count(),
        ];
    }

    public function index(Request $request)
    {
        if (! self::disponible()) {
            return redirect()->route('index_notificaciones')
                ->with('error', 'Falta correr la migración de exhortos (php artisan migrate).');
        }

        $usuario = $request->user();
        $sedes = self::sedes($usuario);
        $filtros = $request->validate([
            'vista'  => ['nullable', Rule::in(self::VISTAS)],
            'q'      => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'integer'],
            'sede'   => ['nullable', Rule::in($sedes)],
        ]);
        $vista = $filtros['vista'] ?? 'pendientes';
        $enSedes = ! empty($filtros['sede']) ? [$filtros['sede']] : $sedes;

        $buscar = function ($q, string $nue, string $solicitante, string $citado) use ($filtros) {
            if (! empty($filtros['q'])) {
                $t = '%'.trim($filtros['q']).'%';
                $q->where(fn ($w) => $w->where($nue, 'like', $t)->orWhere($solicitante, 'like', $t)->orWhere($citado, 'like', $t));
            }

            return $q;
        };

        $registrados = $buscar(self::lista($enSedes), 'x.NUE', 'x.solicitante', 'x.citado')
            ->when(! empty($filtros['estado']), fn ($q) => $q->where('x.estado_id', $filtros['estado']));
        $pendientes = $buscar(self::porRegistrar($enSedes), 'g.NUE', 's.nombre', 'c.nombre')
            ->when(! empty($filtros['estado']), fn ($q) => $q->where('c.estado_citado', $filtros['estado']));

        $conteos = [
            'pendientes'    => (clone $registrados)->where('x.contestado', false)->count(),
            'contestados'   => (clone $registrados)->where('x.contestado', true)->count(),
            'todos'         => (clone $registrados)->count(),
            'por_registrar' => (clone $pendientes)->count(),
        ];

        if ($vista === 'por_registrar') {
            $filas = $pendientes->orderByDesc('c.id')->paginate(self::POR_PAGINA)->withQueryString();
        } else {
            $filas = $registrados
                ->when($vista === 'pendientes', fn ($q) => $q->where('x.contestado', false))
                ->when($vista === 'contestados', fn ($q) => $q->where('x.contestado', true))
                ->orderBy('x.contestado')
                ->orderByDesc('x.created_at')
                ->paginate(self::POR_PAGINA)
                ->withQueryString();
        }

        return view('notificaciones.exhortos', [
            'vista'   => $vista,
            'filtros' => $filtros,
            'filas'   => $filas,
            'conteos' => $conteos,
            'sedes'   => $sedes,
            'estados' => DB::table('estados')->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Para el formulario: el expediente por su NUE, con su solicitante y sus
     * citados (con estado y municipio), para no capturarlos a mano.
     */
    public function expediente(Request $request)
    {
        $nue = trim((string) $request->query('nue', ''));
        if (mb_strlen($nue) < 5) {
            return response()->json(['expediente' => null]);
        }

        $solicitud = DB::table('seer_general')
            ->where('NUE', $nue)
            ->whereIn('delegacion', self::sedes($request->user()))
            ->first(['id', 'NUE', 'delegacion']);
        if (! $solicitud) {
            return response()->json(['expediente' => null]);
        }

        $citados = DB::table('seer_citados as c')
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->where('c.id_solicitud', $solicitud->id)
            ->where('c.notificacion', '!=', 'Trabajador')
            ->orderBy('c.id')
            ->get(['c.id', 'c.nombre', 'c.primer_apellido', 'c.segundo_apellido', 'c.estatus',
                'c.estado_citado', 'c.municipio_citado', 'e.nombre as estado', 'm.nombre as municipio'])
            ->map(fn ($c) => [
                'id'           => $c->id,
                'nombre'       => self::nombre($c->nombre, $c->primer_apellido, $c->segundo_apellido),
                'estatus'      => $c->estatus,
                'estado_id'    => $c->estado_citado,
                'municipio_id' => $c->municipio_citado,
                'lugar'        => trim(($c->municipio ? $c->municipio.', ' : '').($c->estado ?? ''), ', '),
                'registrado'   => Exhorto::where('id_citado', $c->id)->exists(),
            ]);

        return response()->json(['expediente' => [
            'id'          => $solicitud->id,
            'NUE'         => $solicitud->NUE,
            'delegacion'  => $solicitud->delegacion,
            'solicitante' => (string) DB::table('seer_solicitante')->where('id_solicitud', $solicitud->id)->orderBy('id')->value('nombre'),
            'citados'     => $citados,
        ]])->header('Cache-Control', 'no-store');
    }

    public function guardar(Request $request, ?Exhorto $exhorto = null)
    {
        abort_unless(self::disponible(), 404);
        $sedes = self::sedes($request->user());
        abort_if($exhorto && $exhorto->delegacion && ! in_array($exhorto->delegacion, $sedes, true), 403);

        $datos = $request->validate([
            'NUE'                => ['required', 'string', 'max:60'],
            'solicitante'        => ['required', 'string', 'max:255'],
            'citado'             => ['required', 'string', 'max:2000'],
            'estado_id'          => ['required', 'integer', 'exists:estados,id'],
            'municipio_id'       => ['nullable', 'integer', Rule::exists('municipios', 'id')->where('estado', $request->input('estado_id'))],
            'contestado'         => ['nullable', 'boolean'],
            'fecha_contestacion' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'observaciones'      => ['nullable', 'string', 'max:2000'],
            'id_citado'          => ['nullable', 'integer'],
        ], [
            'estado_id.required'                => 'Elige el estado del exhorto.',
            'municipio_id.exists'               => 'Ese municipio no es del estado elegido.',
            'fecha_contestacion.before_or_equal' => 'La fecha de contestación no puede ser futura.',
        ], [
            'NUE' => 'número de expediente',
        ]);

        // Si el NUE es de un expediente del sistema, se liga (y el citado, si es de él).
        $solicitud = DB::table('seer_general')->where('NUE', trim($datos['NUE']))->first(['id', 'delegacion']);
        if ($solicitud && ! in_array($solicitud->delegacion, $sedes, true)) {
            return back()->withInput()->withErrors(['NUE' => 'Ese expediente es de otra sede.']);
        }
        $idCitado = null;
        if ($solicitud && ! empty($datos['id_citado'])) {
            $idCitado = DB::table('seer_citados')->where('id', $datos['id_citado'])->where('id_solicitud', $solicitud->id)->value('id');
        }
        if ($idCitado && Exhorto::where('id_citado', $idCitado)->when($exhorto, fn ($q) => $q->where('id', '<>', $exhorto->id))->exists()) {
            return back()->withInput()->withErrors(['citado' => 'Ese citado ya tiene su exhorto registrado; edítalo desde la lista.']);
        }

        $contestado = $request->boolean('contestado');
        $valores = [
            'id_solicitud'       => $solicitud->id ?? null,
            'id_citado'          => $idCitado,
            'NUE'                => trim($datos['NUE']),
            'delegacion'         => $solicitud->delegacion ?? ($exhorto->delegacion ?? $request->user()->delegacion),
            'solicitante'        => trim($datos['solicitante']),
            'citado'             => trim($datos['citado']),
            'estado_id'          => $datos['estado_id'],
            'municipio_id'       => $datos['municipio_id'] ?? null,
            'contestado'         => $contestado,
            'fecha_contestacion' => $contestado ? ($datos['fecha_contestacion'] ?? Carbon::today()->toDateString()) : null,
            'observaciones'      => $datos['observaciones'] ?? null,
            'actualizado_por'    => $request->user()->id,
        ];

        if ($exhorto) {
            $exhorto->update($valores);
            $mensaje = "Se guardó el exhorto de {$exhorto->NUE}.";
        } else {
            $exhorto = Exhorto::create($valores + ['registrado_por' => $request->user()->id]);
            $mensaje = "Se registró el exhorto de {$exhorto->NUE}.";
        }

        Log::info('Exhorto guardado', ['exhorto' => $exhorto->id, 'NUE' => $exhorto->NUE, 'contestado' => $contestado, 'por' => $request->user()->id]);

        return redirect()->route('exhortos.index', array_filter([
            'vista' => $contestado ? 'contestados' : 'pendientes',
        ]))->with('success', $mensaje);
    }

    /** Marca o desmarca como contestado desde la lista. */
    public function contestado(Request $request, Exhorto $exhorto)
    {
        abort_if($exhorto->delegacion && ! in_array($exhorto->delegacion, self::sedes($request->user()), true), 403);
        $request->validate(['contestado' => ['required', 'boolean']]);
        $contestado = $request->boolean('contestado');

        $exhorto->update([
            'contestado'         => $contestado,
            'fecha_contestacion' => $contestado ? ($exhorto->fecha_contestacion ?? Carbon::today()) : null,
            'actualizado_por'    => $request->user()->id,
        ]);
        Log::info('Exhorto '.($contestado ? 'contestado' : 'sin contestar'), ['exhorto' => $exhorto->id, 'NUE' => $exhorto->NUE, 'por' => $request->user()->id]);

        return back()->with('success', "El exhorto de {$exhorto->NUE} quedó como ".($contestado ? 'contestado' : 'sin contestar').'.');
    }

    public function borrar(Request $request, Exhorto $exhorto)
    {
        abort_if($exhorto->delegacion && ! in_array($exhorto->delegacion, self::sedes($request->user()), true), 403);
        Log::warning('Exhorto borrado', ['exhorto' => $exhorto->toArray(), 'por' => $request->user()->id]);
        $exhorto->delete();

        return back()->with('success', "Se borró el exhorto de {$exhorto->NUE}.");
    }

    // ------------------------------------------------------------------

    /** Exhortos registrados de estas sedes, con estado, municipio y quién lo registró. */
    private static function lista(array $sedes)
    {
        return DB::table('exhortos as x')
            ->leftJoin('estados as e', 'e.id', '=', 'x.estado_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'x.municipio_id')
            ->leftJoin('users as u', 'u.id', '=', 'x.registrado_por')
            ->whereIn('x.delegacion', $sedes)
            ->select('x.*', 'e.nombre as estado', 'm.nombre as municipio', 'u.name as registro');
    }

    /** Citados marcados como "Exhorto" por el notificador que aún no tienen registro. */
    private static function porRegistrar(array $sedes)
    {
        return DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('seer_solicitante as s', 's.id', '=', DB::raw('(SELECT MIN(s2.id) FROM seer_solicitante s2 WHERE s2.id_solicitud = g.id)'))
            ->leftJoin('estados as e', 'e.id', '=', 'c.estado_citado')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->where('c.estatus', 'Exhorto')
            ->whereIn('g.delegacion', $sedes)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('exhortos as x')->whereColumn('x.id_citado', 'c.id'))
            ->select('c.id as id_citado', 'c.nombre', 'c.primer_apellido', 'c.segundo_apellido', 'c.estado_citado', 'c.municipio_citado',
                'c.updated_at', 'g.id as id_solicitud', 'g.NUE', 'g.delegacion', 's.nombre as solicitante',
                'e.nombre as estado', 'm.nombre as municipio');
    }

    public static function nombre(?string ...$partes): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($partes))));
    }
}
