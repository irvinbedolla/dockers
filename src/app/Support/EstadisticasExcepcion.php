<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cifras de /estadisticas/excepciones (casos del art. 685 Ter LFT).
 *
 *   detectados   citas y turnos marcados como excepción (por su fecha) más
 *                las solicitudes marcadas que no nacieron de una de esas citas;
 *   atendidos    formatos terminados (seer_casos_excepcion.terminado_at), por
 *                la fecha del formato;
 *   continúan / canalizados  según la decisión de la persona;
 *   no competentes  se cerraron con "No somos competentes" (resultado
 *                   'incompetente'): no siguen ni se canalizan;
 *   por atender  lo que hoy está pendiente (CasosExcepcion::porAtender).
 *
 * Un caso puede tener varios tipos de situación y canalizarse a varias
 * instituciones: esos desgloses suman más que el total de casos.
 */
class EstadisticasExcepcion
{
    /** [desde 00:00:00, hasta 23:59:59] */
    public static function rango(string $desde, string $hasta): array
    {
        return [Carbon::parse($desde)->toDateString(), Carbon::parse($hasta)->toDateString()];
    }

    /** Formatos terminados del periodo ("x"). */
    public static function atendidos(array $rango, ?string $sede): Builder
    {
        return DB::table('seer_casos_excepcion as x')
            ->whereNotNull('x.terminado_at')
            ->whereBetween('x.fecha', $rango)
            ->when($sede, fn ($q) => $q->where('x.delegacion', $sede));
    }

    private static function citasDetectadas(array $rango, ?string $sede): Builder
    {
        return CasosExcepcion::citas()
            ->whereBetween('r.fecha', $rango)
            ->when($sede, fn ($q) => $q->where('r.delegacion', $sede));
    }

    /** Solicitudes marcadas que no vienen de una cita de excepción (para no contarlas dos veces). */
    private static function solicitudesDetectadas(array $rango, ?string $sede): Builder
    {
        return CasosExcepcion::solicitudes()
            ->whereBetween('g.fecha', $rango)
            ->when($sede, fn ($q) => $q->where('g.delegacion', $sede))
            ->where(fn ($q) => $q->whereNull('g.recepcion_id')
                ->orWhereNotExists(fn ($s) => $s->select(DB::raw(1))->from('recepcion as r2')
                    ->whereColumn('r2.id', 'g.recepcion_id')->where('r2.exepcion', 'Si')));
    }

    public static function resumen(array $rango, ?string $sede): array
    {
        $citas = self::citasDetectadas($rango, $sede);
        $porOrigen = (clone $citas)->selectRaw('r.origen, COUNT(*) AS total')->groupBy('r.origen')->pluck('total', 'origen');
        $solicitudes = self::solicitudesDetectadas($rango, $sede)->count();

        $fila = CasosExcepcion::conFormato()
            ? self::atendidos($rango, $sede)->selectRaw(
                "COUNT(*) AS atendidos,
                 SUM(CASE WHEN x.resultado = 'continua' THEN 1 ELSE 0 END) AS continua,
                 SUM(CASE WHEN x.resultado = 'canaliza' THEN 1 ELSE 0 END) AS canaliza,
                 SUM(CASE WHEN x.resultado = 'incompetente' THEN 1 ELSE 0 END) AS incompetente,
                 SUM(CASE WHEN x.resultado = 'continua' AND COALESCE(x.instituciones, '[]') <> '[]' THEN 1 ELSE 0 END) AS integral")->first()
            : null;

        $borradores = CasosExcepcion::conFormato()
            ? DB::table('seer_casos_excepcion as x')->whereNull('x.terminado_at')
                ->when($sede, fn ($q) => $q->where('x.delegacion', $sede))->count()
            : 0;

        $atendidos = (int) ($fila->atendidos ?? 0);
        $canaliza  = (int) ($fila->canaliza ?? 0);
        $incompetente = (int) ($fila->incompetente ?? 0);

        return [
            'detectados'  => (int) $porOrigen->sum() + $solicitudes,
            'enLinea'     => (int) ($porOrigen['linea'] ?? 0),
            'ventanilla'  => (int) ($porOrigen['ventanilla'] ?? 0),
            'solicitudes' => $solicitudes,
            'atendidos'   => $atendidos,
            'continua'    => (int) ($fila->continua ?? 0),
            // Siguen con la conciliación y además se canalizaron para atención integral.
            'integral'    => (int) ($fila->integral ?? 0),
            'canaliza'    => $canaliza,
            'pctCanaliza' => $atendidos ? round($canaliza * 100 / $atendidos, 1) : null,
            'incompetente'    => $incompetente,
            'pctIncompetente' => $atendidos ? round($incompetente * 100 / $atendidos, 1) : null,
            'porAtender'  => CasosExcepcion::porAtender()->when($sede, fn ($q) => $q->where('r.delegacion', $sede))->count(),
            'borradores'  => $borradores,
        ];
    }

    /**
     * Serie de la gráfica: detectados (línea) y atendidos por resultado
     * (barras), por día o por mes si el rango pasa de dos meses.
     */
    public static function tendencia(array $rango, ?string $sede): array
    {
        $inicio = Carbon::parse($rango[0])->startOfDay();
        $fin    = Carbon::parse($rango[1])->startOfDay();
        $porMes = abs($inicio->diffInDays($fin)) > 62;
        $formato = $porMes ? '%Y-%m' : '%Y-%m-%d';

        $detectados = self::citasDetectadas($rango, $sede)
            ->selectRaw("DATE_FORMAT(r.fecha, '{$formato}') AS clave, COUNT(*) AS total")->groupBy('clave')->pluck('total', 'clave');
        foreach (self::solicitudesDetectadas($rango, $sede)->selectRaw("DATE_FORMAT(g.fecha, '{$formato}') AS clave, COUNT(*) AS total")->groupBy('clave')->pluck('total', 'clave') as $k => $n) {
            $detectados[$k] = ($detectados[$k] ?? 0) + $n;
        }

        $atendidos = CasosExcepcion::conFormato()
            ? self::atendidos($rango, $sede)->selectRaw("DATE_FORMAT(x.fecha, '{$formato}') AS clave,
                SUM(CASE WHEN x.resultado = 'continua' THEN 1 ELSE 0 END) AS continua,
                SUM(CASE WHEN x.resultado = 'canaliza' THEN 1 ELSE 0 END) AS canaliza,
                SUM(CASE WHEN x.resultado = 'incompetente' THEN 1 ELSE 0 END) AS incompetente")
                ->groupBy('clave')->get()->keyBy('clave')
            : collect();

        $serie = ['unidad' => $porMes ? 'mes' : 'dia', 'etiquetas' => [], 'detectados' => [], 'continua' => [], 'canaliza' => [], 'incompetente' => []];
        $cursor = $porMes ? $inicio->copy()->startOfMonth() : $inicio->copy();
        while ($cursor <= $fin) {
            $clave = $cursor->format($porMes ? 'Y-m' : 'Y-m-d');
            $serie['etiquetas'][]  = $cursor->locale('es')->isoFormat($porMes ? 'MMM YY' : 'D MMM');
            // Lo que aún no pasa va vacío (la línea no cae a cero en días futuros).
            $futuro = $porMes ? $cursor->copy()->startOfMonth()->isAfter(now()) : $cursor->isAfter(now()->endOfDay());
            $serie['detectados'][] = $futuro ? null : (int) ($detectados[$clave] ?? 0);
            $serie['continua'][]   = $futuro ? null : (int) ($atendidos[$clave]->continua ?? 0);
            $serie['canaliza'][]   = $futuro ? null : (int) ($atendidos[$clave]->canaliza ?? 0);
            $serie['incompetente'][] = $futuro ? null : (int) ($atendidos[$clave]->incompetente ?? 0);
            $porMes ? $cursor->addMonthNoOverflow() : $cursor->addDay();
        }

        return $serie;
    }

    /** Cuántos casos atendidos marcan cada clave de una columna JSON (tipos o instituciones). */
    public static function porJson(string $columna, array $catalogo, array $rango, ?string $sede, ?string $soloResultado = null): Collection
    {
        if (! CasosExcepcion::conFormato()) {
            return collect();
        }

        $cols = [];
        foreach (array_keys($catalogo) as $i => $clave) {
            $cols[] = "SUM(CASE WHEN JSON_CONTAINS(COALESCE(x.{$columna}, '[]'), '\"{$clave}\"') THEN 1 ELSE 0 END) AS c{$i}";
        }
        $fila = self::atendidos($rango, $sede)
            ->when($soloResultado, fn ($q) => $q->where('x.resultado', $soloResultado))
            ->selectRaw(implode(', ', $cols))->first();

        return collect(array_keys($catalogo))->map(fn ($clave, $i) => [
            'clave' => $clave, 'etiqueta' => $catalogo[$clave], 'total' => (int) ($fila->{"c{$i}"} ?? 0),
        ])->sortByDesc('total')->values();
    }

    /** Por sede: detectados, atendidos, continúan, canalizados y por atender. */
    public static function porSede(array $rango): Collection
    {
        $citas = self::citasDetectadas($rango, null)->selectRaw('r.delegacion AS sede, COUNT(*) AS total')->groupBy('r.delegacion')->pluck('total', 'sede');
        $sols  = self::solicitudesDetectadas($rango, null)->selectRaw('g.delegacion AS sede, COUNT(*) AS total')->groupBy('g.delegacion')->pluck('total', 'sede');
        $pend  = CasosExcepcion::porAtender()->selectRaw('r.delegacion AS sede, COUNT(*) AS total')->groupBy('r.delegacion')->pluck('total', 'sede');
        $at    = CasosExcepcion::conFormato()
            ? self::atendidos($rango, null)->selectRaw("x.delegacion AS sede, COUNT(*) AS atendidos,
                SUM(CASE WHEN x.resultado = 'continua' THEN 1 ELSE 0 END) AS continua,
                SUM(CASE WHEN x.resultado = 'canaliza' THEN 1 ELSE 0 END) AS canaliza,
                SUM(CASE WHEN x.resultado = 'incompetente' THEN 1 ELSE 0 END) AS incompetente")->groupBy('x.delegacion')->get()->keyBy('sede')
            : collect();

        return collect(CasosExcepcion::SEDES)->map(fn ($s) => [
            'sede'       => $s,
            'detectados' => (int) ($citas[$s] ?? 0) + (int) ($sols[$s] ?? 0),
            'atendidos'  => (int) ($at[$s]->atendidos ?? 0),
            'continua'   => (int) ($at[$s]->continua ?? 0),
            'canaliza'   => (int) ($at[$s]->canaliza ?? 0),
            'incompetente' => (int) ($at[$s]->incompetente ?? 0),
            'porAtender' => (int) ($pend[$s] ?? 0),
        ])->filter(fn ($f) => $f['detectados'] + $f['atendidos'] + $f['porAtender'] > 0)->values();
    }

    /** Atendidos por una columna simple (género, grupo vulnerable, frecuencia). */
    public static function porColumna(string $columna, array $etiquetas, array $rango, ?string $sede): Collection
    {
        if (! CasosExcepcion::conFormato()) {
            return collect();
        }

        $conteo = self::atendidos($rango, $sede)
            ->selectRaw("COALESCE(NULLIF(x.{$columna}, ''), '_') AS valor, COUNT(*) AS total")
            ->groupBy('valor')->pluck('total', 'valor');

        return $conteo->map(fn ($n, $v) => ['etiqueta' => $v === '_' ? 'Sin dato' : ($etiquetas[$v] ?? $v), 'total' => (int) $n])
            ->sortByDesc('total')->values();
    }

    /** Renglones del reporte por motivo que no son un motivo del catálogo. */
    public const MOTIVOS_EXTRA = [
        'discriminacion_otro' => ['Discriminación por otro motivo', 'Discriminación en el empleo'],
        'discriminacion_sin'  => ['Discriminación (motivo no registrado)', 'Discriminación en el empleo'],
        'prestaciones_sin'    => ['Prestaciones de seguridad social (sin detalle)', 'Prestaciones de seguridad social'],
        'libertades_sin'      => ['Derechos fundamentales (sin detalle)', 'Derechos fundamentales y libertades laborales'],
        'titularidad_sin'     => ['Titularidad o estatutos sindicales (sin detalle)', 'Disputa de titularidad o estatutos sindicales'],
        'sin_motivo'          => ['Sin motivo registrado', 'Sin dato'],
        'solicitud_sin'       => ['Solicitud marcada como excepción (sin motivo)', 'Sin dato'],
    ];

    /**
     * Casos detectados por motivo (el reporte mensual del departamento),
     * con su desglose por mes del periodo.
     *
     * Motivo de cada cita: el que se eligió en el formato 685 Ter para la
     * discriminación; si no, el de la cita (recepcion.motivo_excepcion); si
     * la cita es de antes de guardar el motivo, sólo se sabe su grupo
     * (recepcion.tipo_caso) y cae en "(sin detalle)".
     *
     * @return array{meses: array<string, string>, filas: array<int, array>, total: int}
     */
    public static function porMotivo(array $rango, ?string $sede): array
    {
        $q = self::citasDetectadas($rango, $sede)->select('r.id', 'r.fecha', 'r.tipo_caso');
        if (CasosExcepcion::conMotivo()) {
            $q->addSelect('r.motivo_excepcion');
        }
        if (Schema::hasColumn('seer_casos_excepcion', 'motivo_discriminacion')) {
            $q->addSelect(DB::raw('(SELECT x.motivo_discriminacion FROM seer_casos_excepcion x WHERE x.id_turno = r.id AND x.motivo_discriminacion IS NOT NULL ORDER BY x.id DESC LIMIT 1) AS motivo_formato'));
        }

        $conteo = [];
        $sumar = function (string $clave, $fecha) use (&$conteo) {
            $mes = substr((string) $fecha, 0, 7);
            $conteo[$clave][$mes] = ($conteo[$clave][$mes] ?? 0) + 1;
        };
        foreach ($q->get() as $c) {
            $sumar(self::claveMotivo($c), $c->fecha);
        }
        foreach (self::solicitudesDetectadas($rango, $sede)->pluck('g.fecha') as $fecha) {
            $sumar('solicitud_sin', $fecha);
        }

        $meses = [];
        $cursor = Carbon::parse($rango[0])->startOfMonth();
        while ($cursor->format('Y-m') <= substr($rango[1], 0, 7)) {
            $meses[$cursor->format('Y-m')] = $cursor->locale('es')->isoFormat('MMM YYYY');
            $cursor->addMonthNoOverflow();
        }

        $filas = [];
        foreach ($conteo as $clave => $porMes) {
            [$etiqueta, $grupo] = isset(CasosExcepcion::MOTIVOS[$clave])
                ? [CasosExcepcion::MOTIVOS[$clave][3], preg_replace('/\s*(por|de)?:\s*$/u', '', CasosExcepcion::MOTIVOS[$clave][1])]
                : self::MOTIVOS_EXTRA[$clave];
            $filas[] = ['clave' => $clave, 'etiqueta' => $etiqueta, 'grupo' => $grupo, 'total' => array_sum($porMes), 'meses' => $porMes];
        }
        usort($filas, fn ($a, $b) => [$b['total'], $a['etiqueta']] <=> [$a['total'], $b['etiqueta']]);

        return ['meses' => $meses, 'filas' => $filas, 'total' => array_sum(array_column($filas, 'total'))];
    }

    private static function claveMotivo(object $c): string
    {
        $formato = $c->motivo_formato ?? null;
        if ($formato === 'otro') {
            return 'discriminacion_otro';
        }
        if ($formato && isset(CasosExcepcion::MOTIVOS[$formato])) {
            return $formato;
        }
        if ($clave = CasosExcepcion::motivoValido($c->motivo_excepcion ?? null)) {
            return $clave;
        }

        return match (trim((string) $c->tipo_caso)) {
            'Acoso u hostigamiento sexual'  => 'acoso_sexual',
            'Acoso u hostigamiento laboral' => 'acoso_laboral',
            'Designacion'                   => 'designacion',
            'Discriminación'                => 'discriminacion_sin',
            'Prestaciones'                  => 'prestaciones_sin',
            'Libertades_publicas'           => 'libertades_sin',
            'Titularidad'                   => 'titularidad_sin',
            default                         => 'sin_motivo',
        };
    }
}
