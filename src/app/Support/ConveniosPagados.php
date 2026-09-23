<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Convenios pagados en el mes en curso, agrupados por sede.
 *
 * "Pagado" no es que se haya firmado el convenio, sino que el dinero llegó
 * completo al trabajador. Tres precisiones que la consulta tiene que
 * respetar, las tres medidas sobre los datos reales antes de escribirla:
 *
 * 1. Hay dos pagos por expediente en promedio -8,516 filas para 4,248
 *    expedientes-: son parcialidades. Un expediente cuenta como pagado sólo
 *    cuando TODOS sus pagos lo están, no cuando hay uno.
 * 2. tipo_pago 'Conciliador' y el vacío son pruebas colgadas de un
 *    id_solicitud inválido; se filtran con id_solicitud > 0.
 * 3. El mes al que pertenece un expediente es el de su última parcialidad
 *    cubierta, y esa fecha sale de updated_at, no de pago_solicitud.fecha.
 *    'fecha' es el día PROGRAMADO de la parcialidad, no el día en que se
 *    cobró: hay expedientes ya liquidados con parcialidades fechadas en
 *    octubre, diciembre e incluso agosto de 2028, porque alguien pagó por
 *    adelantado y se marcaron todas. Agruparlos por esa fecha los mandaba al
 *    futuro. updated_at es cuando el Centro registró el último pago, que es
 *    el hecho que la tarjeta cuenta.
 *
 * Se agrupa por sede y no por conciliador a propósito. La atribución
 * individual hoy no es confiable: hay personas con dos y tres cuentas, alguna
 * con el correo de otra persona, cuentas de prueba con expedientes encima, y
 * un tercio de los pagos hereda su conciliador de seer_general. La sede, en
 * cambio, es inequívoca: el expediente es de Morelia o de Zamora sin importar
 * qué cuenta lo haya tocado.
 */
class ConveniosPagados
{
    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /** Las tres lecturas que ofrecen las pastillas. Las etiquetas son de una
     *  palabra para que las tres quepan en un renglón: con cuatro tarjetas en
     *  la fila, "Promedio por convenio" se bajaba solo y dejaba las pastillas
     *  en tres renglones. Lo que mide cada una lo dice el pie de la cifra. */
    public const METRICAS = [
        'expedientes' => 'Expedientes',
        'monto'       => 'Monto',
        'promedio'    => 'Promedio',
    ];

    private const MESES = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    /** Son datos del mes en curso: se mueven durante el día. */
    private const MINUTOS_CACHE = 15;

    private const ESTATUS_PAGADO = ['Pagado', 'Pagado con pena convencional'];

    public static function porSede(): array
    {
        $inicio = Carbon::now()->startOfMonth();

        // El mes va en la llave para que el caché ruede solo al cambiar de mes.
        return Cache::remember(
            'inicio.convenios_pagados.'.$inicio->format('Y-m'),
            now()->addMinutes(self::MINUTOS_CACHE),
            fn () => self::calcular($inicio)
        );
    }

    public static function olvidar(): void
    {
        Cache::forget('inicio.convenios_pagados.'.Carbon::now()->format('Y-m'));
    }

    private static function calcular(Carbon $inicio): array
    {
        $desde = $inicio->toDateString();
        $hasta = $inicio->copy()->addMonth()->toDateString();

        $sedes = [];

        foreach (self::consultar($desde, $hasta) as $fila) {
            $sede = self::sedeCanonica($fila->delegacion);

            if ($sede === null || (int) $fila->expedientes === 0) {
                continue;
            }

            $sedes[$sede] = [
                'sede'        => $sede,
                'expedientes' => (int) $fila->expedientes,
                'monto'       => (float) $fila->monto,
                'promedio'    => (float) $fila->monto / (int) $fila->expedientes,
            ];
        }

        $metricas = [];

        foreach (array_keys(self::METRICAS) as $metrica) {
            $orden = array_values($sedes);
            usort($orden, fn ($a, $b) => $b[$metrica] <=> $a[$metrica]);

            $metricas[$metrica] = [
                // El máximo sirve para el ancho de las barras: la primera
                // llena la fila y las demás se leen contra ella.
                'maximo' => $orden ? $orden[0][$metrica] : 0,
                'sedes'  => $orden,
            ];
        }

        return [
            'mes'      => self::MESES[(int) $inicio->format('n')].' '.$inicio->format('Y'),
            'total'    => array_sum(array_column($sedes, 'expedientes')),
            'monto'    => array_sum(array_column($sedes, 'monto')),
            'metricas' => $metricas,
        ];
    }

    private static function consultar(string $desde, string $hasta): array
    {
        $pagado = "p.estatus IN ('".implode("','", self::ESTATUS_PAGADO)."')";

        // La subconsulta no se acota por fecha a propósito: para saber si un
        // expediente está liquidado hay que ver TODAS sus parcialidades, no
        // sólo las del mes. El recorte por mes va afuera, sobre la fecha en
        // que se cubrió la última.
        return DB::select("
            SELECT e.delegacion,
                   COUNT(*)                  AS expedientes,
                   COALESCE(SUM(e.monto), 0) AS monto
              FROM (
                    SELECT p.id_solicitud,
                           MAX(p.delegacion)  AS delegacion,
                           SUM(p.monto)       AS monto,
                           COUNT(*)           AS pagos,
                           SUM({$pagado})     AS pagados,
                           MAX(p.updated_at)  AS liquidado
                      FROM pago_solicitud p
                     WHERE p.id_solicitud > 0
                     GROUP BY p.id_solicitud
                   ) e
             WHERE e.pagos = e.pagados
               AND e.liquidado >= ? AND e.liquidado < ?
             GROUP BY e.delegacion
        ", [$desde, $hasta]);
    }

    /**
     * pago_solicitud.delegacion es un enum acentuado, pero conviene comparar
     * sin acentos: en otras tablas del sistema la sede es texto libre y
     * "Zitacuaro" aparece sin acento. Devuelve el nombre canónico.
     */
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
