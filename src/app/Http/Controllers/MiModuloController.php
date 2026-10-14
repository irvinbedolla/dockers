<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\User;
use App\Support\FlujoCita;
use App\Support\FolioCita;
use App\Support\PantallaEspera;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "Mi módulo" (fase 1 del flujo cita → solicitud): lo que tiene hoy el
 * módulo de quien entra. Por ahora sólo consulta: turno actual, el que sigue
 * con cuántos minutos faltan, y la lista del día con los datos que traerá el
 * prellenado (fase 2). Los avisos con sonido (fase 3) y los botones Llamar /
 * Atender (fase 4) se cuelgan de aquí.
 *
 * El turno actual y el siguiente salen de la misma regla que la pantalla de
 * espera (PantallaEspera), para que el auxiliar y la sala vean lo mismo.
 *
 * Sólo se ve si el interruptor lo habilita (FlujoCita::habilitadoPara).
 */
class MiModuloController extends Controller
{
    public static function puedeVer(?User $usuario): bool
    {
        if ($usuario === null || ! FlujoCita::migrado() || ! FlujoCita::habilitadoPara($usuario)) {
            return false;
        }

        return $usuario->hasRole('Super Usuario') || self::propios($usuario)->isNotEmpty();
    }

    public function index(Request $request)
    {
        abort_unless(self::puedeVer($request->user()), 403);

        [$modulo, $opciones] = $this->elegir($request);

        return view('mi_modulo.index', [
            'modulo'   => $modulo,
            'opciones' => $opciones,
            'datos'    => $modulo ? $this->armar($modulo) : null,
            'urlDatos' => $modulo ? route('mi_modulo.datos', ['modulo' => $modulo->id]) : null,
        ]);
    }

    public function datos(Request $request)
    {
        abort_unless(self::puedeVer($request->user()), 403);

        [$modulo] = $this->elegir($request);
        abort_unless($modulo, 404);

        return response()->json($this->armar($modulo))->header('Cache-Control', 'no-store');
    }

    /** Módulos activos que atiende la persona. */
    private static function propios(User $usuario): Collection
    {
        static $cache = [];

        return $cache[$usuario->id] ??= Modulo::where('user_id', $usuario->id)
            ->where('activo', 1)
            ->orderBy('delegacion')->orderBy('orden')
            ->get();
    }

    /**
     * El módulo a mostrar y los que se pueden elegir. Un Super Usuario puede
     * ver cualquiera (para probar); los demás, sólo los suyos.
     *
     * @return array{0: ?Modulo, 1: Collection}
     */
    private function elegir(Request $request): array
    {
        $usuario  = $request->user();
        $opciones = $usuario->hasRole('Super Usuario')
            ? Modulo::where('activo', 1)->orderBy('delegacion')->orderBy('orden')->get()
            : self::propios($usuario);

        $pedido = (int) $request->query('modulo');
        $modulo = ($pedido ? $opciones->firstWhere('id', $pedido) : null)
            ?? self::propios($usuario)->first()
            ?? $opciones->first();

        return [$modulo, $opciones];
    }

    private function armar(Modulo $modulo): array
    {
        $ahora = now();
        $hoy   = $ahora->toDateString();
        $aviso = FlujoCita::minutosAviso();

        // Turno actual y siguiente: la misma regla que la pantalla de espera.
        $tablero  = PantallaEspera::calcular($modulo->delegacion, $ahora, ! FlujoCita::apagado());
        $suyo     = collect($tablero['modulos'])->firstWhere('id', $modulo->id);
        $actualId = $suyo['actual']['id'] ?? null;
        $sigueId  = $suyo['siguiente']['id'] ?? null;

        $citas = DB::table('recepcion as r')
            ->leftJoin('municipios as mu', 'mu.id', '=', 'r.municipio')
            ->where('r.fecha', $hoy)
            ->where('r.delegacion', $modulo->delegacion)
            ->where(fn ($q) => $q->where('r.modulo_id', $modulo->id)
                ->orWhere(fn ($v) => $v->whereNull('r.modulo_id')->where('r.lugar_auxiliar', $modulo->nombre)))
            ->orderBy('r.hora')->orderBy('r.id')
            ->get([
                'r.id', 'r.codigo', 'r.consecutivo', 'r.hora', 'r.hora_fin', 'r.tipo', 'r.estatus', 'r.origen',
                'r.es_prueba', 'r.exepcion', 'r.solicitante', 'r.edad', 'r.sexo', 'r.telefono', 'r.correo',
                'r.tipo_caso', 'r.conflicto', 'r.vulnerables', 'r.prestacionSS', 'r.observaciones',
                'mu.nombre as municipio',
            ])
            ->map(function ($c) use ($ahora, $hoy, $aviso, $actualId, $sigueId) {
                $folio   = FolioCita::mostrar($c);
                $corte   = strrpos($folio, '-');
                $minutos = (int) floor($ahora->diffInSeconds(Carbon::parse($hoy.' '.$c->hora), false) / 60);
                $vigente = in_array($c->estatus, ['pendiente', 'confirmada'], true);

                return [
                    'id'          => $c->id,
                    'folio'       => $folio,
                    'prefijo'     => $corte === false ? '' : substr($folio, 0, $corte + 1),
                    'numero'      => $corte === false ? $folio : substr($folio, $corte + 1),
                    'hora'        => substr((string) $c->hora, 0, 5),
                    'tipo'        => (string) $c->tipo,
                    'estatus'     => (string) $c->estatus,
                    'enLinea'     => $c->origen === 'linea',
                    'prueba'      => (bool) $c->es_prueba,
                    'flujoNuevo'  => FlujoCita::aplicaA($c),
                    'solicitante' => \Illuminate\Support\Str::title(mb_strtolower(trim((string) $c->solicitante))),
                    'edad'        => $c->edad,
                    'sexo'        => $c->sexo,
                    'municipio'   => $c->municipio,
                    'telefono'    => $c->telefono,
                    'correo'      => $c->correo,
                    'motivo'      => trim((string) ($c->conflicto ?: $c->observaciones)),
                    'caso'        => $c->tipo_caso,
                    'vulnerables' => $c->vulnerables,
                    'minutos'     => $minutos,
                    'porLlegar'   => $vigente && $minutos >= 0 && $minutos <= $aviso,
                    'esActual'    => $c->id === $actualId,
                    'esSiguiente' => $c->id === $sigueId,
                ];
            });

        return [
            'modulo'       => [
                'id'       => $modulo->id,
                'nombre'   => preg_replace('/^m[oó]dulo\b/i', 'Módulo', $modulo->nombre),
                'sede'     => $modulo->delegacion,
                'tramites' => implode(', ', (array) $modulo->tramites),
            ],
            'estado'       => FlujoCita::estado(),
            'minutosAviso' => $aviso,
            'generado'     => $ahora->toIso8601String(),
            'actual'       => $citas->firstWhere('esActual', true),
            'siguiente'    => $citas->firstWhere('esSiguiente', true),
            'citas'        => $citas->values()->all(),
            'cifras'       => [
                'total'      => $citas->count(),
                'atendidas'  => $citas->where('estatus', 'atendido')->count(),
                'enSala'     => $citas->where('estatus', 'confirmada')->count(),
                'porLlegar'  => $citas->where('estatus', 'pendiente')->where('minutos', '>=', -5)->count(),
            ],
        ];
    }
}
