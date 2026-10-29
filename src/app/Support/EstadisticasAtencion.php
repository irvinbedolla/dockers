<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * /estadisticas/atencion: resultados del formulario de atención del trámite
 * de conciliación (App\Support\AtencionTramite).
 *
 * Índice de satisfacción (0 a 100): Buena = 100, Regular = 50, Mala = 0,
 * promedio de las respuestas. Se calcula por etapa (Recepción, Captura y
 * Conciliador), por sede y por persona; a una persona sólo se le muestra el
 * índice si tiene MINIMO respuestas o más, para no juzgarla por dos opiniones.
 *
 * Se filtra por la fecha en que se contestó.
 */
class EstadisticasAtencion
{
    public const MINIMO = 10;

    public const ETAPAS = [1 => 'Recepción', 2 => 'Captura de datos', 4 => 'Conciliador(a)'];

    private const PUNTOS = ['buena' => 100, 'regular' => 50, 'mala' => 0];

    public static function filtros(Request $request): array
    {
        $fecha = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
        $desde = $fecha($request->query('desde')) ?? now()->startOfMonth()->toDateString();
        $hasta = $fecha($request->query('hasta')) ?? now()->endOfMonth()->toDateString();
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        $sede = (string) $request->query('sede', '');
        $parte = (string) $request->query('parte', '');

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'sede'  => in_array($sede, Recepcion::TODAS_LAS_SEDES, true) ? $sede : '',
            'parte' => array_key_exists($parte, AtencionTramite::PARTES) ? $parte : '',
        ];
    }

    public static function armar(array $f, ?string $por = null): array
    {
        $base = ['filtros' => $f, 'migrado' => AtencionTramite::migrado(), 'generado' => now(), 'por' => $por,
            'periodo' => EstadisticasTurnos::textoPeriodo(Carbon::parse($f['desde']), Carbon::parse($f['hasta']))];
        if (! $base['migrado']) {
            return $base + ['total' => 0];
        }

        $sedes = $f['sede'] !== '' ? array_values(array_unique([$f['sede'], Str::ascii($f['sede'])])) : null;
        $filas = DB::table('atencion_tramite')
            ->whereBetween('created_at', [$f['desde'].' 00:00:00', $f['hasta'].' 23:59:59'])
            ->when($sedes, fn ($q) => $q->whereIn('delegacion', $sedes))
            ->when($f['parte'] !== '', fn ($q) => $q->where('parte', $f['parte']))
            ->orderByDesc('created_at')
            ->get();

        $nombres = DB::table('users')->whereIn('id', $filas->pluck('capturo_id')->merge($filas->pluck('conciliador_id'))->filter()->unique()->all() ?: [0])
            ->pluck('name', 'id')->map(fn ($n) => Str::title(mb_strtolower((string) $n)))->all();

        // Envíos del periodo (para la tasa de respuesta).
        $envios = DB::table('atencion_tramite_envios')
            ->whereBetween('enviado_at', [$f['desde'].' 00:00:00', $f['hasta'].' 23:59:59'])
            ->when($f['parte'] !== '', fn ($q) => $q->where('parte', $f['parte']))
            ->when($sedes, fn ($q) => $q->whereIn('id_solicitud', DB::table('seer_general')->whereIn('delegacion', $sedes)->select('id')))
            ->count();

        // Nombre de la parte que contestó (las respuestas anteriores a la columna lo toman de la solicitud).
        self::completarNombres($filas);

        $total = $filas->count();
        $etapas = [];
        foreach (self::ETAPAS as $n => $nombre) {
            $etapas[$n] = ['nombre' => $nombre] + self::escala($filas, 'p'.$n);
        }

        $sedesLista = [];
        foreach ($filas->groupBy(fn ($r) => $r->delegacion ?: 'Sin sede') as $s => $grupo) {
            $sedesLista[] = self::fila($s, $grupo);
        }
        usort($sedesLista, fn ($a, $b) => $b['n'] <=> $a['n']);

        $conciliadores = [];
        foreach ($filas->whereNotNull('conciliador_id')->groupBy('conciliador_id') as $id => $grupo) {
            $conciliadores[] = self::fila($nombres[$id] ?? 'Usuario '.$id, $grupo);
        }
        usort($conciliadores, fn ($a, $b) => [$b['n'] >= self::MINIMO, $b['n']] <=> [$a['n'] >= self::MINIMO, $a['n']]);

        $capturistas = [];
        foreach ($filas->whereNotNull('capturo_id')->groupBy('capturo_id') as $id => $grupo) {
            $capturistas[] = self::fila($nombres[$id] ?? 'Usuario '.$id, $grupo);
        }
        usort($capturistas, fn ($a, $b) => [$b['n'] >= self::MINIMO, $b['n']] <=> [$a['n'] >= self::MINIMO, $a['n']]);

        $convenios = $filas->where('p6', 'convenio');
        $presion = $convenios->where('p7', 'si')->count();

        return $base + [
            'total'        => $total,
            'porParte'     => ['trabajadora' => $filas->where('parte', 'trabajadora')->count(), 'empleadora' => $filas->where('parte', 'empleadora')->count()],
            'envios'       => $envios,
            'pctRespuesta' => $envios > 0 ? round($total * 100 / $envios, 1) : null,
            'indice'       => self::indiceGeneral($filas),
            'etapas'       => $etapas,
            'derechos'     => self::conteo($filas, 'p3', AtencionTramite::PREGUNTAS[3]['opciones']),
            'imparcialidad'=> self::imparcialidad($filas),
            'resultado'    => self::resultado($filas),
            'porResultado' => [
                'Con convenio'       => self::fila('Con convenio', $filas->filter(fn ($r) => in_array($r->resultado, AtencionTramite::CON_CONVENIO, true))),
                'Sin convenio'       => self::fila('Sin convenio', $filas->filter(fn ($r) => ! in_array($r->resultado, AtencionTramite::CON_CONVENIO, true))),
            ],
            'convenios'    => $convenios->count(),
            'presion'      => $presion,
            'pctPresion'   => $convenios->count() ? round($presion * 100 / $convenios->count(), 1) : null,
            'porSede'      => $sedesLista,
            'conciliadores'=> $conciliadores,
            'capturistas'  => $capturistas,
            'alertas'      => $filas->whereNotNull('seguimiento')->map(fn ($r) => self::renglon($r, $nombres))->values()->all(),
            'pendientes'   => $filas->where('seguimiento', 'pendiente')->count(),
            'comentarios'  => self::comentarios($filas, $nombres),
            'respuestas'   => $filas->map(fn ($r) => self::renglon($r, $nombres))->values()->all(),
            'archivo'      => 'atencion_tramite_'.$f['desde'].'_al_'.$f['hasta'].($f['sede'] ? '_'.Str::slug($f['sede'], '_') : ''),
        ];
    }

    /** Tarjeta del panel: respuestas e índice del mes y alertas pendientes. */
    public static function resumen(): array
    {
        if (! AtencionTramite::migrado()) {
            return ['total' => 0, 'indice' => null, 'pendientes' => 0];
        }
        $filas = DB::table('atencion_tramite')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->get(['p1', 'p2', 'p4']);

        return [
            'total'      => $filas->count(),
            'indice'     => self::indiceGeneral($filas),
            'pendientes' => DB::table('atencion_tramite')->where('seguimiento', 'pendiente')->count(),
        ];
    }

    /** Marca una alerta como atendida. */
    public static function atender(int $id, ?string $nota, int $usuario): bool
    {
        return (bool) DB::table('atencion_tramite')->where('id', $id)->whereNotNull('seguimiento')->update([
            'seguimiento'      => 'atendida',
            'seguimiento_nota' => $nota !== null && trim($nota) !== '' ? mb_substr(trim($nota), 0, 1000) : null,
            'seguimiento_por'  => $usuario,
            'seguimiento_at'   => now(),
            'updated_at'       => now(),
        ]);
    }

    // ------------------------------------------------------------------

    /** Buena/Regular/Mala de una pregunta, con porcentajes e índice. */
    private static function escala($filas, string $col): array
    {
        $c = ['buena' => 0, 'regular' => 0, 'mala' => 0];
        foreach ($filas as $r) {
            if (isset($c[$r->{$col}])) {
                $c[$r->{$col}]++;
            }
        }
        $n = array_sum($c);
        $pct = fn ($v) => $n ? round($v * 100 / $n, 1) : null;

        return [
            'n' => $n, 'buena' => $c['buena'], 'regular' => $c['regular'], 'mala' => $c['mala'],
            'pctBuena' => $pct($c['buena']), 'pctRegular' => $pct($c['regular']), 'pctMala' => $pct($c['mala']),
            'indice' => $n ? (int) round(($c['buena'] * 100 + $c['regular'] * 50) / $n) : null,
        ];
    }

    /** Índice de las tres etapas juntas. */
    private static function indiceGeneral($filas): ?int
    {
        $suma = $n = 0;
        foreach ($filas as $r) {
            foreach (['p1', 'p2', 'p4'] as $col) {
                if (isset(self::PUNTOS[$r->{$col} ?? ''])) {
                    $suma += self::PUNTOS[$r->{$col}];
                    $n++;
                }
            }
        }

        return $n ? (int) round($suma / $n) : null;
    }

    private static function conteo($filas, string $col, array $opciones): array
    {
        $n = $filas->whereNotNull($col)->count();
        $lista = [];
        foreach ($opciones as $valor => $texto) {
            $v = $filas->where($col, $valor)->count();
            $lista[] = ['valor' => $valor, 'texto' => $texto, 'n' => $v, 'pct' => $n ? round($v * 100 / $n, 1) : null];
        }

        return ['n' => $n, 'opciones' => $lista];
    }

    /** Un renglón de tabla (sede, persona o resultado). */
    private static function fila(string $etiqueta, $grupo): array
    {
        $n = $grupo->count();
        $conPct = function (string $col, string $valor) use ($grupo) {
            $con = $grupo->whereNotNull($col)->count();

            return $con ? round($grupo->where($col, $valor)->count() * 100 / $con, 1) : null;
        };
        $convenios = $grupo->where('p6', 'convenio');

        return [
            'etiqueta'     => $etiqueta,
            'n'            => $n,
            'suficiente'   => $n >= self::MINIMO,
            'recepcion'    => self::escala($grupo, 'p1')['indice'],
            'captura'      => self::escala($grupo, 'p2')['indice'],
            'conciliador'  => self::escala($grupo, 'p4')['indice'],
            'malas'        => $grupo->filter(fn ($r) => in_array('mala', [$r->p1, $r->p2, $r->p4], true))->count(),
            'informado'    => $conPct('p3', 'si'),
            'equilibrado'  => $conPct('p5', 'equilibrado'),
            'presion'      => $convenios->where('p7', 'si')->count(),
            'convenios'    => $convenios->count(),
        ];
    }

    /** Texto corto para la tabla de respuestas (pregunta => [clave => texto]). */
    private const CORTAS = [
        6 => ['convenio' => 'Convenio', 'no_conciliacion' => 'No conciliación', 'agotada' => 'Etapa agotada'],
    ];

    /**
     * Pregunta 5 traducida a quién se percibe que favoreció: 'trabajador',
     * 'empleador' o 'equilibrado' (null si no contestó). La respuesta es
     * relativa a quien contesta ("a mi favor" / "a la otra parte"), así que
     * depende de su parte. Las claves anteriores (solicitante / citada) se
     * traducen con el tipo de solicitud: en el tipo 2 solicita el empleador.
     */
    public static function favorecido(object $r): ?string
    {
        $yo = $r->parte === 'empleadora' ? 'empleador' : 'trabajador';
        $otro = $yo === 'empleador' ? 'trabajador' : 'empleador';
        $solicitaEmpleador = (int) ($r->tipo_solicitud ?? 1) === 2;

        return match ($r->p5 ?? null) {
            null, ''      => null,
            'equilibrado' => 'equilibrado',
            'a_mi'        => $yo,
            'otra_parte'  => $otro,
            'solicitante' => $solicitaEmpleador ? 'empleador' : 'trabajador',
            'citada'      => $solicitaEmpleador ? 'trabajador' : 'empleador',
            default       => null,
        };
    }

    /** Pregunta 5 por parte que contesta y traducida a quién se percibe que favoreció. */
    private static function imparcialidad($filas): array
    {
        $porParte = [];
        foreach (AtencionTramite::PARTES as $parte => $texto) {
            $g = $filas->where('parte', $parte);
            $porParte[$parte] = ['texto' => $texto] + self::conteo($g, 'p5', AtencionTramite::PREGUNTAS[5]['opciones']);
        }

        $favorece = ['trabajador' => 0, 'empleador' => 0, 'equilibrado' => 0];
        $favorecePorParte = ['trabajadora' => ['trabajador' => 0, 'empleador' => 0, 'equilibrado' => 0], 'empleadora' => ['trabajador' => 0, 'empleador' => 0, 'equilibrado' => 0]];
        foreach ($filas as $r) {
            $a = self::favorecido($r);
            if ($a === null) {
                continue;
            }
            $favorece[$a]++;
            if (isset($favorecePorParte[$r->parte])) {
                $favorecePorParte[$r->parte][$a]++;
            }
        }

        return ['porParte' => $porParte, 'favorece' => $favorece, 'favorecePorParte' => $favorecePorParte];
    }

    /** Pregunta 6 contra lo registrado en SICONCILIO. */
    private static function resultado($filas): array
    {
        $coinciden = $comparables = 0;
        $matriz = [];
        foreach ($filas as $r) {
            if (! $r->p6) {
                continue;
            }
            $registrado = in_array($r->resultado, AtencionTramite::CON_CONVENIO, true) ? 'Con convenio' : 'Sin convenio';
            $dicho = AtencionTramite::PREGUNTAS[6]['opciones'][$r->p6] ?? $r->p6;
            $matriz[$dicho][$registrado] = ($matriz[$dicho][$registrado] ?? 0) + 1;
            $comparables++;
            $coinciden += ($r->p6 === 'convenio') === ($registrado === 'Con convenio') ? 1 : 0;
        }

        return [
            'matriz'     => $matriz,
            'comparables'=> $comparables,
            'coinciden'  => $coinciden,
            'pct'        => $comparables ? round($coinciden * 100 / $comparables, 1) : null,
        ];
    }

    private static function renglon(object $r, array $nombres): array
    {
        // Por pregunta: el texto completo, uno corto para la tabla y el nivel
        // (bueno / medio / malo) sacado de la clave, no del texto, para que
        // se puedan reescribir las opciones sin romper los colores.
        $resp = $cortas = $niveles = [];
        $favorecido = ['trabajador' => 'Al trabajador', 'empleador' => 'Al empleador', 'equilibrado' => 'Equilibrado'];
        foreach (AtencionTramite::PREGUNTAS as $n => $p) {
            $v = $r->{'p'.$n};
            $resp[$n] = $v ? ($p['opciones'][$v] ?? $v) : null;
            $cortas[$n] = $v ? ($n === 5 ? ($favorecido[self::favorecido($r)] ?? $resp[$n]) : (self::CORTAS[$n][$v] ?? $resp[$n])) : null;
            $niveles[$n] = $v ? self::nivel($n, $v) : null;
        }
        $coment = [];
        foreach (array_keys(AtencionTramite::PREGUNTAS) as $n) {
            if ($r->{'c'.$n}) {
                $coment[$n] = $r->{'c'.$n};
            }
        }

        return [
            'id'          => $r->id,
            'fecha'       => Carbon::parse($r->created_at)->format('d/m/Y H:i'),
            'folio'       => $r->folio,
            'sede'        => $r->delegacion ?: '—',
            'parte'       => AtencionTramite::PARTES[$r->parte] ?? $r->parte,
            'nombre'      => ($r->nombre ?? null) ?: '—',
            'resolucion'  => ($r->resolucion_audiencia ?? null) ?: ($r->resultado ?: '—'),
            'conciliador' => $r->conciliador_id ? ($nombres[$r->conciliador_id] ?? 'Usuario '.$r->conciliador_id) : '—',
            'capturo'     => $r->capturo_id ? ($nombres[$r->capturo_id] ?? 'Usuario '.$r->capturo_id) : '—',
            'resultado'   => $r->resultado ?: '—',
            'respuestas'  => $resp,
            'cortas'      => $cortas,
            'niveles'     => $niveles,
            'comentarios' => $coment,
            'presion'     => $r->p7 === 'si',
            'seguimiento' => $r->seguimiento,
            'nota'        => $r->seguimiento_nota,
            'atendidaAt'  => $r->seguimiento_at ? Carbon::parse($r->seguimiento_at)->format('d/m/Y H:i') : null,
        ];
    }

    /** Pone el nombre de la parte a las respuestas que no lo traen guardado. */
    private static function completarNombres($filas): void
    {
        $faltan = $filas->filter(fn ($r) => empty($r->nombre ?? null));
        if ($faltan->isEmpty()) {
            return;
        }
        $generales = DB::table('seer_general')->whereIn('id', $faltan->pluck('id_solicitud')->unique()->all())->get()->keyBy('id');
        $cache = [];
        foreach ($faltan as $r) {
            $g = $generales->get($r->id_solicitud);
            $clave = $r->id_solicitud.'|'.$r->parte;
            $r->nombre = $g ? ($cache[$clave] ??= AtencionTramite::nombreParte($g, $r->parte)) : null;
        }
    }

    private static function comentarios($filas, array $nombres): array
    {
        $lista = [];
        foreach ($filas as $r) {
            foreach (AtencionTramite::PREGUNTAS as $n => $p) {
                $c = trim((string) $r->{'c'.$n});
                if ($c === '') {
                    continue;
                }
                $lista[] = [
                    'fecha'     => Carbon::parse($r->created_at)->format('d/m/Y'),
                    'folio'     => $r->folio,
                    'sede'      => $r->delegacion ?: '—',
                    'parte'     => AtencionTramite::PARTES[$r->parte] ?? $r->parte,
                    'nombre'    => ($r->nombre ?? null) ?: '—',
                    'pregunta'  => $n.'. '.$p['etapa'],
                    'respuesta' => $r->{'p'.$n} ? ($p['opciones'][$r->{'p'.$n}] ?? $r->{'p'.$n}) : '—',
                    'nivel'     => self::nivel($n, $r->{'p'.$n}),
                    'persona'   => in_array($n, [4, 5, 7], true) && $r->conciliador_id ? ($nombres[$r->conciliador_id] ?? '') : ($n === 2 && $r->capturo_id ? ($nombres[$r->capturo_id] ?? '') : ''),
                    'texto'     => $c,
                ];
            }
        }

        return $lista;
    }

    /** Para pintar el comentario: bueno, medio o malo según lo que contestó. */
    private static function nivel(int $n, ?string $v): string
    {
        return match (true) {
            $n === 7                                => $v === 'si' ? 'malo' : 'bueno',
            $n === 5                                => $v === 'equilibrado' ? 'bueno' : 'medio',
            $n === 6                                => 'medio',
            in_array($v, ['mala', 'no'], true)      => 'malo',
            in_array($v, ['buena', 'si'], true)     => 'bueno',
            default                                 => 'medio',
        };
    }
}
