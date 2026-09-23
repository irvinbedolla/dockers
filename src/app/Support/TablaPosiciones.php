<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tabla de posiciones del mes: conciliadores y auxiliares.
 *
 * Cada grupo se mide con lo que de verdad hace:
 *
 *   conciliadores → convenios logrados en audiencia (seer_conciliadores con
 *                   estatus 'Conciliacion'). No se usan los expedientes ya
 *                   liquidados porque el pago va rezagado y premiaría
 *                   convenios de meses anteriores.
 *   auxiliares    → solicitudes registradas (seer_general.user_id, que es
 *                   quien capturó la solicitud).
 *
 * Sólo entran cuentas activas y no de prueba. Las cuentas duplicadas -hay
 * gente con dos- se suman por nombre normalizado para que no aparezcan
 * partidas en dos renglones; se conserva el id de la cuenta que tiene foto,
 * y si ninguna la tiene, la de más registros.
 *
 * El puesto es competitivo estándar: dos empatados comparten lugar y el
 * siguiente salta (1, 2, 2, 4).
 */
class TablaPosiciones
{
    /** Cuántos se listan por grupo, contando al primer lugar. */
    private const CUANTOS = 5;

    /** Son datos del mes en curso: se mueven durante el día. */
    private const MINUTOS_CACHE = 15;

    private const MESES = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    public static function resumen(): array
    {
        $inicio = Carbon::now()->startOfMonth();

        return Cache::remember(
            'inicio.tabla_posiciones.'.$inicio->format('Y-m'),
            now()->addMinutes(self::MINUTOS_CACHE),
            fn () => self::calcular($inicio)
        );
    }

    public static function olvidar(): void
    {
        Cache::forget('inicio.tabla_posiciones.'.Carbon::now()->format('Y-m'));
    }

    private static function calcular(Carbon $inicio): array
    {
        $desde = $inicio->toDateString();
        $hasta = $inicio->copy()->addMonth()->toDateString();

        $grupos = [
            [
                'clave'   => 'conciliadores',
                'titulo'  => 'Conciliadores',
                'unidad'  => 'convenios',
                'gente'   => self::ordenar(self::conciliadores($desde, $hasta)),
            ],
            [
                'clave'   => 'auxiliares',
                'titulo'  => 'Auxiliares',
                'unidad'  => 'solicitudes',
                'gente'   => self::ordenar(self::auxiliares($desde, $hasta)),
            ],
        ];

        return [
            'mes'      => self::MESES[(int) $inicio->format('n')].' '.$inicio->format('Y'),
            'grupos'   => $grupos,
            'hayDatos' => (bool) array_filter(array_column($grupos, 'gente')),
        ];
    }

    private static function conciliadores(string $desde, string $hasta): array
    {
        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   SUM(sc.estatus_conciliacion = 'Conciliacion') AS valor,
                   SUM(sc.estatus_conciliacion IN ('Conciliacion', 'No conciliacion')) AS resueltas
              FROM seer_conciliadores sc
              LEFT JOIN audiencias   a ON a.id = sc.audiencia_id
              LEFT JOIN seer_general g ON g.id = sc.id_solicitud
              JOIN users u ON u.id = COALESCE(NULLIF(a.id_conciliador, 0), NULLIF(g.conciliador_id, 0))
             WHERE sc.fecha >= ? AND sc.fecha < ?
               AND u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
             GROUP BY u.id, u.name, u.delegacion, u.foto_perfil
            HAVING valor > 0
        ", [$desde, $hasta]);
    }

    private static function auxiliares(string $desde, string $hasta): array
    {
        // Se exige el rol para que la lista no se llene de gente de otras
        // áreas que capturó una solicitud suelta: en septiembre había un
        // notificador y dos conciliadores con una cada uno.
        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   COUNT(*) AS valor,
                   NULL     AS resueltas
              FROM seer_general g
              JOIN users u ON u.id = g.user_id
              JOIN model_has_roles mr ON mr.model_id = u.id AND mr.model_type = ?
              JOIN roles r ON r.id = mr.role_id AND r.name = 'Auxiliar'
             WHERE g.fecha >= ? AND g.fecha < ?
               AND u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
             GROUP BY u.id, u.name, u.delegacion, u.foto_perfil
        ", [\App\Models\User::class, $desde, $hasta]);
    }

    /**
     * Junta cuentas duplicadas, ordena y reparte puestos.
     */
    private static function ordenar(array $filas): array
    {
        $porNombre = [];

        foreach ($filas as $fila) {
            $nombre = preg_replace('/\s+/', ' ', trim((string) $fila->name));
            $llave  = Str::lower(Str::ascii($nombre));

            $porNombre[$llave] ??= [
                'nombre'    => Str::title($nombre),
                'sede'      => $fila->delegacion,
                'foto'      => null,
                'valor'     => 0,
                'resueltas' => 0,
                'mayor'     => -1,
            ];

            $actual = &$porNombre[$llave];

            $actual['valor']     += (int) $fila->valor;
            $actual['resueltas'] += (int) $fila->resueltas;

            // De las cuentas duplicadas nos quedamos con la foto de la que
            // tenga; si ninguna tiene, con los datos de la más activa.
            $tieneFoto = trim((string) $fila->foto_perfil) !== '';

            if ($actual['foto'] === null && $tieneFoto) {
                $actual['foto'] = $fila->foto_perfil;
                $actual['sede'] = $fila->delegacion;
            }

            if ((int) $fila->valor > $actual['mayor']) {
                $actual['mayor'] = (int) $fila->valor;

                if ($actual['foto'] === null) {
                    $actual['sede'] = $fila->delegacion;
                }
            }

            unset($actual);
        }

        $gente = array_values($porNombre);

        usort($gente, fn ($a, $b) => [$b['valor'], $a['nombre']] <=> [$a['valor'], $b['nombre']]);

        $gente = array_slice($gente, 0, self::CUANTOS);

        $puesto   = 0;
        $anterior = null;

        foreach ($gente as $i => $persona) {
            // Puesto competitivo estándar: el empate comparte lugar y el
            // siguiente salta los que quedaron empatados arriba.
            if ($persona['valor'] !== $anterior) {
                $puesto   = $i + 1;
                $anterior = $persona['valor'];
            }

            $gente[$i]['puesto']    = $puesto;
            $gente[$i]['iniciales'] = self::iniciales($persona['nombre']);

            unset($gente[$i]['mayor']);
        }

        return $gente;
    }

    /**
     * Dos letras para el avatar de quien todavía no tiene foto. Un monograma
     * se lee mucho mejor que la silueta genérica en una tabla de posiciones.
     */
    private static function iniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', Str::ascii($nombre), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Se salta partículas para que "Juan de la Cruz" dé JC y no JD.
        $partes = array_values(array_filter(
            $partes,
            fn ($p) => ! in_array(Str::lower($p), ['de', 'del', 'la', 'las', 'los', 'y'], true)
        ));

        $primera = $partes[0] ?? '';
        $segunda = $partes[1] ?? '';

        return Str::upper(mb_substr($primera, 0, 1).mb_substr($segunda, 0, 1));
    }
}
