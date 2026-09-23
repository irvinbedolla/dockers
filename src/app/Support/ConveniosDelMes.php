<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Montos convenidos en el mes en curso, por sede y por conciliador.
 *
 * "Convenido" no es lo mismo que "pagado": es el monto que quedó acordado en
 * el convenio, se haya cobrado o no. Sale de dos lugares, que son los mismos
 * que ya usa el panel del rol Directivo:
 *
 *   - seer_conciliadores.monto  → convenios de audiencia
 *   - turnos.monto              → convenios de ratificación
 *
 * La sede y el conciliador de una audiencia no viven en seer_conciliadores,
 * así que se recuperan de la audiencia y, si ahí faltan, de la solicitud.
 *
 * Quedan fuera las cuentas de prueba -las que llevan "temporal" en el nombre
 * o en el correo- y las que ya no están activas. Los convenios que no tienen
 * conciliador identificado sí se conservan: el monto es real y se muestra
 * como "Sin conciliador asignado" para que el total de la sede cuadre.
 */
class ConveniosDelMes
{
    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    private const MESES = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    /** Son datos del mes en curso: se mueven durante el día. */
    private const MINUTOS_CACHE = 15;

    public static function resumen(): array
    {
        $inicio = Carbon::now()->startOfMonth();

        // El mes va en la llave para que el caché ruede solo al cambiar de mes.
        return Cache::remember(
            'inicio.convenios_mes.'.$inicio->format('Y-m'),
            now()->addMinutes(self::MINUTOS_CACHE),
            fn () => self::calcular($inicio)
        );
    }

    public static function olvidar(): void
    {
        Cache::forget('inicio.convenios_mes.'.Carbon::now()->format('Y-m'));
    }

    private static function calcular(Carbon $inicio): array
    {
        $desde = $inicio->toDateString();
        $hasta = $inicio->copy()->addMonth()->toDateString();

        $sedes = [];

        foreach (self::consultar($desde, $hasta) as $fila) {
            $sede = self::sedeCanonica($fila->delegacion);

            if ($sede === null) {
                continue;
            }

            $sedes[$sede] ??= ['sede' => $sede, 'convenios' => 0, 'monto' => 0.0, 'gente' => []];

            $sedes[$sede]['convenios'] += (int) $fila->convenios;
            $sedes[$sede]['monto']     += (float) $fila->monto;

            // Se acumula por nombre y no por id: hay conciliadores con dos
            // cuentas y sin esto aparecen partidos en dos renglones dentro de
            // la misma sede. Es un parche mientras se consolidan las cuentas.
            $llave = $fila->nombre === null ? '' : Str::lower(Str::ascii(preg_replace('/\s+/', ' ', trim($fila->nombre))));

            $sedes[$sede]['gente'][$llave] ??= [
                'nombre'    => $fila->nombre === null
                    ? 'Sin conciliador asignado'
                    : Str::title(preg_replace('/\s+/', ' ', trim($fila->nombre))),
                'convenios' => 0,
                'monto'     => 0.0,
                'anonimo'   => $fila->nombre === null,
            ];

            $sedes[$sede]['gente'][$llave]['convenios'] += (int) $fila->convenios;
            $sedes[$sede]['gente'][$llave]['monto']     += (float) $fila->monto;
        }

        foreach ($sedes as $sede => $datos) {
            $gente = array_values($datos['gente']);
            // Los que sí tienen nombre primero, por monto; el renglón sin
            // conciliador siempre al final.
            usort($gente, fn ($a, $b) => [$a['anonimo'], $b['monto']] <=> [$b['anonimo'], $a['monto']]);
            $sedes[$sede]['gente'] = $gente;
        }

        $orden = array_values($sedes);
        usort($orden, fn ($a, $b) => $b['monto'] <=> $a['monto']);

        return [
            'mes'     => self::MESES[(int) $inicio->format('n')].' '.$inicio->format('Y'),
            'total'   => array_sum(array_column($orden, 'convenios')),
            'monto'   => array_sum(array_column($orden, 'monto')),
            'maximo'  => $orden ? $orden[0]['monto'] : 0.0,
            'sedes'   => $orden,
        ];
    }

    private static function consultar(string $desde, string $hasta): array
    {
        return DB::select("
            SELECT x.delegacion,
                   u.name          AS nombre,
                   COUNT(*)        AS convenios,
                   SUM(x.monto)    AS monto
              FROM (
                    SELECT COALESCE(NULLIF(a.id_conciliador, 0), NULLIF(g.conciliador_id, 0)) AS conciliador,
                           COALESCE(NULLIF(a.delegacion, ''), g.delegacion) AS delegacion,
                           sc.monto
                      FROM seer_conciliadores sc
                      LEFT JOIN audiencias   a ON a.id = sc.audiencia_id
                      LEFT JOIN seer_general g ON g.id = sc.id_solicitud
                     WHERE sc.estatus_conciliacion = 'Conciliacion'
                       AND sc.monto > 0
                       AND sc.fecha >= ? AND sc.fecha < ?

                    UNION ALL

                    SELECT NULLIF(t.id_conciliador, 0), t.delegacion, t.monto
                      FROM turnos t
                     WHERE t.tipo = 'Ratificación'
                       AND t.monto > 0
                       AND (t.incidencia IS NULL OR t.incidencia = 0)
                       AND t.fecha >= ? AND t.fecha < ?
                   ) x
              LEFT JOIN users u ON u.id = x.conciliador
             WHERE u.id IS NULL
                OR (u.estatus = 'Activo'
                    AND u.email NOT LIKE '%temporal%'
                    AND u.name  NOT LIKE '%temporal%')
             GROUP BY x.delegacion, x.conciliador, u.name
        ", [$desde, $hasta, $desde, $hasta]);
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
