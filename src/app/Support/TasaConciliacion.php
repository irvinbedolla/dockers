<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tasa de conciliación del mes en curso, general y por sede.
 *
 * La tasa sale sólo de las audiencias -seer_conciliadores-, no de las
 * ratificaciones: una ratificación llega con el convenio ya acordado, así que
 * sumarla al numerador sin tener un "no conciliado" que la acompañe en el
 * denominador infla la cifra y deja de medir lo que se quiere medir.
 *
 * Qué entra en el denominador depende de la pastilla, porque las dos lecturas
 * son legítimas y responden preguntas distintas:
 *
 *   resueltas  → Conciliación vs No conciliación. De las audiencias en las que
 *                las partes se sentaron y hubo un resultado, cuántas acabaron
 *                en convenio. Es la medida del trabajo conciliatorio.
 *   concluidas → lo anterior más incomparecencias, incompetencias,
 *                desistimientos y archivadas en audiencia. De todo lo que se
 *                cerró en definitiva, cuánto acabó en convenio. Es la medida
 *                del rendimiento del Centro.
 *
 * Quedan fuera de las dos los estatus que no cierran nada -"No conciliacion se
 * reagenda", "Regenerada", "Reinstalacion"-: la audiencia vuelve a agendarse y
 * contarla sería contar el mismo asunto dos veces.
 *
 * Aquí no se filtran cuentas "temporal" ni inactivas, a diferencia de
 * ConveniosDelMes. Esa tarjeta reparte crédito entre personas y por eso le
 * importa quién es el conciliador; ésta mide resultados del Centro, y una
 * audiencia se celebró la haya capturado quien la haya capturado. En
 * septiembre 2026 el filtro no movía ni un registro, de todos modos.
 */
class TasaConciliacion
{
    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /** Etiquetas cortas para que las dos quepan en un renglón dentro de la
     *  tarjeta. El alcance completo va en el title de cada pastilla y en el
     *  pie de la cifra. */
    public const METRICAS = [
        'resueltas'  => 'Resueltas',
        'concluidas' => 'Concluidas',
    ];

    /** Sólo cuenta el convenio logrado en audiencia. */
    private const CONCILIADOS = ['Conciliacion'];

    /** Audiencia celebrada con resultado: hubo convenio o no lo hubo. */
    private const RESUELTAS = ['Conciliacion', 'No conciliacion'];

    /** Todo lo que cierra el asunto en definitiva. */
    private const CONCLUIDAS = [
        'Conciliacion',
        'No conciliacion',
        'Archivado por incomparecencia',
        'Incompetencia',
        'Desistimiento',
        'Archivada en Audiencia',
    ];

    private const MESES = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    /** Son datos del mes en curso: se mueven durante el día. */
    private const MINUTOS_CACHE = 15;

    public static function resumen(): array
    {
        $inicio = Carbon::now()->startOfMonth();

        return Cache::remember(
            'inicio.tasa_conciliacion.'.$inicio->format('Y-m'),
            now()->addMinutes(self::MINUTOS_CACHE),
            fn () => self::calcular($inicio)
        );
    }

    public static function olvidar(): void
    {
        Cache::forget('inicio.tasa_conciliacion.'.Carbon::now()->format('Y-m'));
    }

    private static function calcular(Carbon $inicio): array
    {
        $previo = $inicio->copy()->subMonth();

        // Los dos meses en una sola consulta: el mes anterior sólo se usa para
        // la variación, no vale un viaje aparte a la base.
        $filas = self::consultar(
            $previo->toDateString(),
            $inicio->copy()->addMonth()->toDateString(),
            $inicio->toDateString()
        );

        $acumulado = [];

        foreach ($filas as $fila) {
            $sede = self::sedeCanonica($fila->delegacion);

            if ($sede === null) {
                continue;
            }

            $periodo = $fila->periodo === 'actual' ? 'actual' : 'previo';

            $acumulado[$periodo][$sede] ??= ['conciliados' => 0, 'resueltas' => 0, 'concluidas' => 0];

            $acumulado[$periodo][$sede]['conciliados'] += (int) $fila->conciliados;
            $acumulado[$periodo][$sede]['resueltas']   += (int) $fila->resueltas;
            $acumulado[$periodo][$sede]['concluidas']  += (int) $fila->concluidas;
        }

        $metricas = [];

        foreach (self::METRICAS as $clave => $etiqueta) {
            $metricas[$clave] = self::armarMetrica(
                $clave,
                $etiqueta,
                $acumulado['actual'] ?? [],
                $acumulado['previo'] ?? []
            );
        }

        return [
            'mes'       => self::MESES[(int) $inicio->format('n')].' '.$inicio->format('Y'),
            'mesPrevio' => self::MESES[(int) $previo->format('n')],
            'hayDatos'  => ! empty($acumulado['actual']),
            'metricas'  => $metricas,
        ];
    }

    private static function armarMetrica(string $clave, string $etiqueta, array $actual, array $previo): array
    {
        $sedes = [];

        foreach ($actual as $sede => $datos) {
            // Una sede sin base no tiene tasa: 0 de 0 no es 0 %, es "todavía
            // nada". Se omite en vez de pintar una barra vacía que se lee como
            // un mal resultado.
            if ($datos[$clave] === 0) {
                continue;
            }

            $sedes[] = [
                'sede'        => $sede,
                'conciliados' => $datos['conciliados'],
                'base'        => $datos[$clave],
                'tasa'        => round($datos['conciliados'] / $datos[$clave] * 100, 1),
            ];
        }

        usort($sedes, fn ($a, $b) => $b['tasa'] <=> $a['tasa']);

        $conciliados = array_sum(array_column($actual, 'conciliados'));
        $base        = array_sum(array_column($actual, $clave));
        $tasa        = $base > 0 ? round($conciliados / $base * 100, 1) : null;

        $conciliadosPrevios = array_sum(array_column($previo, 'conciliados'));
        $basePrevia         = array_sum(array_column($previo, $clave));
        $tasaPrevia         = $basePrevia > 0 ? round($conciliadosPrevios / $basePrevia * 100, 1) : null;

        return [
            'etiqueta'    => $etiqueta,
            'tasa'        => $tasa,
            'conciliados' => $conciliados,
            'base'        => $base,
            'previa'      => $tasaPrevia,
            'delta'       => ($tasa !== null && $tasaPrevia !== null) ? round($tasa - $tasaPrevia, 1) : null,
            'sedes'       => $sedes,
        ];
    }

    private static function consultar(string $desde, string $hasta, string $corte): array
    {
        $conciliados = self::listaSql(self::CONCILIADOS);
        $resueltas   = self::listaSql(self::RESUELTAS);
        $concluidas  = self::listaSql(self::CONCLUIDAS);

        return DB::select("
            SELECT COALESCE(NULLIF(a.delegacion, ''), g.delegacion) AS delegacion,
                   CASE WHEN sc.fecha >= ? THEN 'actual' ELSE 'previo' END AS periodo,
                   SUM(sc.estatus_conciliacion IN ($conciliados)) AS conciliados,
                   SUM(sc.estatus_conciliacion IN ($resueltas))   AS resueltas,
                   SUM(sc.estatus_conciliacion IN ($concluidas))  AS concluidas
              FROM seer_conciliadores sc
              LEFT JOIN audiencias   a ON a.id = sc.audiencia_id
              LEFT JOIN seer_general g ON g.id = sc.id_solicitud
             WHERE sc.fecha >= ? AND sc.fecha < ?
             GROUP BY delegacion, periodo
        ", [$corte, $desde, $hasta]);
    }

    /**
     * Los estatus son constantes de esta clase, no entrada del usuario, pero
     * se escapan igual: es una lista que se interpola en el SQL y no hay razón
     * para dejar el hueco abierto.
     */
    private static function listaSql(array $valores): string
    {
        return implode(',', array_map(fn ($v) => DB::getPdo()->quote($v), $valores));
    }

    private static function sedeCanonica(?string $delegacion): ?string
    {
        $buscado = Str::ascii(trim((string) $delegacion));

        foreach (self::SEDES as $sede) {
            if (Str::ascii($sede) === $buscado) {
                return $sede;
            }
        }

        return null;
    }
}
