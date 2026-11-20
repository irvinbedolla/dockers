<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tabla de posiciones del mes: conciliadores, auxiliares y orientadores.
 *
 * Cada grupo se mide con lo que de verdad hace:
 *
 *   conciliadores → convenios logrados en audiencia (seer_conciliadores con
 *                   estatus 'Conciliacion'). No se usan los expedientes ya
 *                   liquidados porque el pago va rezagado y premiaría
 *                   convenios de meses anteriores.
 *   auxiliares    → solicitudes registradas (seer_general.user_id, que es
 *                   quien capturó la solicitud) MÁS ratificaciones hechas
 *                   (turnos tipo Ratificación con fecha en el mes y user_id
 *                   de quien la capturó; se cuentan las Confirmadas,
 *                   Concluidas, Concluidas Pagos e Incumplimiento, no las
 *                   archivadas). Muchos auxiliares viven de ratificar: medirlos
 *                   sólo por solicitudes los dejaba abajo. El lugar sale de la
 *                   suma y la tarjeta muestra el desglose.
 *   orientadores  → lo mismo que los auxiliares: en la práctica son quienes
 *                   capturan la solicitud tras orientar a la persona. Las
 *                   asesorías (seer_asesorias) casi no se registran y no
 *                   servirían para medir a nadie.
 *
 *   cumplimientos → pagos programados (pago_solicitud) que la persona
 *                   registró en el mes como pagado, pagado con pena
 *                   convencional, no pagado o incomparecencia, de audiencias
 *                   y de ratificaciones (user_id + fecha_conclucion, que llena
 *                   el modelo Pagos). Un pago total cuenta 1: los pagos que se
 *                   cierran solos con él no llevan user_id. Antes de
 *                   CUMPLIMIENTOS_DESDE varias pantallas no guardaban quién
 *                   fue, así que se cuenta desde esa fecha y la tarjeta lo
 *                   avisa durante ese mes.
 *
 * Quiénes aparecen (aunque lleven 0 en el mes):
 *   conciliadores → todas las cuentas activas con rol Conciliador.
 *   auxiliares    → los Auxiliares de Conciliador: quien atiende un módulo
 *                   activo (Administración → Módulos) con rol Auxiliar y SIN
 *                   rol Orientador.
 *   orientadores  → quien atiende un módulo activo con rol Orientador. El
 *                   módulo deja fuera la cuenta genérica "orientador".
 *   cumplimientos → sólo quien registró al menos uno en el mes. El rol
 *                   Auxiliar Cumplimientos no sirve de lista: lo tienen
 *                   cuentas genéricas ("Auxiliar Morelia") y segundas cuentas
 *                   de conciliadores. Los conciliadores no entran (sí quien
 *                   además es Auxiliar).
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
    /** Cuántos se ven antes del botón "Ver todos", contando al primer lugar. */
    public const VISIBLES = 5;

    /** Ratificaciones que cuentan como hechas (no las archivadas ni pendientes). */
    private const RATIFICACION_HECHA = ['Confirmado', 'Concluida', 'Concluida Pagos', 'Incumplimiento'];

    /** Estatus de un pago que ya se atendió (= App\Models\Pagos::ATENDIDO). */
    private const CUMPLIMIENTO_ATENDIDO = ['Pagado', 'Pagado con pena convencional', 'No pagado', 'Incomparecencia trabajador'];

    /**
     * Desde cuándo se sabe quién registró cada cumplimiento: el día que subió
     * App\Models\Pagos::booted. Ajustar al día real del despliegue.
     */
    public const CUMPLIMIENTOS_DESDE = '2026-11-12';

    /** Partes del desglose: columna d_* => [corto, singular, plural]. */
    private const PARTES = [
        'solicitudes'    => ['sol.', 'solicitud', 'solicitudes'],
        'ratificaciones' => ['rat.', 'ratificación', 'ratificaciones'],
        'audiencia'      => ['aud.', 'de audiencia', 'de audiencia'],
        'ratificacion'   => ['rat.', 'de ratificación', 'de ratificación'],
    ];

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
            'inicio.tabla_posiciones.v5.'.$inicio->format('Y-m'),
            now()->addMinutes(self::MINUTOS_CACHE),
            fn () => self::calcular($inicio)
        );
    }

    public static function olvidar(): void
    {
        Cache::forget('inicio.tabla_posiciones.v5.'.Carbon::now()->format('Y-m'));
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
                'cuenta'  => 'Convenios logrados en audiencia este mes. Junto al primer lugar, de cuántas audiencias resueltas (con o sin convenio).',
                'gente'   => self::ordenar(self::conciliadores($desde, $hasta)),
            ],
            [
                'clave'   => 'auxiliares',
                'titulo'  => 'Auxiliares',
                'unidad'  => 'trámites',
                'cuenta'  => 'Solicitudes que capturaron más ratificaciones que hicieron (confirmadas, concluidas o en incumplimiento; no las archivadas). El lugar sale de la suma.',
                'gente'   => self::ordenar(self::auxiliares($desde, $hasta)),
            ],
            [
                'clave'   => 'orientadores',
                'titulo'  => 'Orientadores',
                'unidad'  => 'solicitudes',
                'cuenta'  => 'Solicitudes que capturaron este mes.',
                'gente'   => self::ordenar(self::orientadores($desde, $hasta)),
            ],
            [
                'clave'   => 'cumplimientos',
                'titulo'  => 'Cumplimientos',
                'unidad'  => 'cumplimientos',
                'cuenta'  => 'Pagos de convenio que registraron (pagado, pagado con pena convencional, no pagado o no comparecencia), de audiencias y de ratificaciones. Un pago total cuenta como uno. Aparece quien registró al menos uno; no incluye a conciliadores.',
                'aviso'   => self::avisoCumplimientos($inicio),
                'gente'   => self::ordenar(self::cumplimientos(max($desde, self::CUMPLIMIENTOS_DESDE), $hasta)),
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
        // Parte de la lista de conciliadores (todos, aunque no tengan
        // audiencias en el mes) y le pega sus números con LEFT JOIN.
        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   COALESCE(s.valor, 0)     AS valor,
                   COALESCE(s.resueltas, 0) AS resueltas
              FROM users u
              LEFT JOIN (
                    SELECT COALESCE(NULLIF(a.id_conciliador, 0), NULLIF(g.conciliador_id, 0)) AS user_id,
                           SUM(sc.estatus_conciliacion = 'Conciliacion') AS valor,
                           SUM(sc.estatus_conciliacion IN ('Conciliacion', 'No conciliacion')) AS resueltas
                      FROM seer_conciliadores sc
                      LEFT JOIN audiencias   a ON a.id = sc.audiencia_id
                      LEFT JOIN seer_general g ON g.id = sc.id_solicitud
                     WHERE sc.fecha >= ? AND sc.fecha < ?
                     GROUP BY 1
                   ) s ON s.user_id = u.id
             WHERE u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%prueba%'
               AND EXISTS (
                    SELECT 1
                      FROM model_has_roles mr
                      JOIN roles r ON r.id = mr.role_id
                     WHERE mr.model_id = u.id
                       AND mr.model_type = ?
                       AND r.name = 'Conciliador'
                   )
        ", [$desde, $hasta, \App\Models\User::class]);
    }

    private static function auxiliares(string $desde, string $hasta): array
    {
        // La lista sale del catálogo de módulos: quien atiende uno activo,
        // con rol Auxiliar y sin rol Orientador. Así aparecen todos, aunque
        // lleven 0 en el mes, y no se cuelan cuentas genéricas ni gente de
        // otras áreas que capturó una solicitud suelta.
        // valor = solicitudes + ratificaciones; las dos van aparte para el desglose.
        $hechas = implode(', ', array_fill(0, count(self::RATIFICACION_HECHA), '?'));

        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   COALESCE(s.valor, 0) + COALESCE(r.valor, 0) AS valor,
                   COALESCE(s.valor, 0) AS d_solicitudes,
                   COALESCE(r.valor, 0) AS d_ratificaciones,
                   NULL                 AS resueltas
              FROM users u
              LEFT JOIN (
                    SELECT g.user_id, COUNT(*) AS valor
                      FROM seer_general g
                     WHERE g.fecha >= ? AND g.fecha < ?
                     GROUP BY g.user_id
                   ) s ON s.user_id = u.id
              LEFT JOIN (
                    SELECT t.user_id, COUNT(*) AS valor
                      FROM turnos t
                     WHERE t.fecha >= ? AND t.fecha < ?
                       AND t.tipo = 'Ratificación'
                       AND t.estatus IN ($hechas)
                     GROUP BY t.user_id
                   ) r ON r.user_id = u.id
             WHERE u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
               AND EXISTS (SELECT 1 FROM modulos m WHERE m.user_id = u.id AND m.activo = 1)
               AND EXISTS (
                    SELECT 1
                      FROM model_has_roles mr
                      JOIN roles r ON r.id = mr.role_id
                     WHERE mr.model_id = u.id AND mr.model_type = ? AND r.name = 'Auxiliar'
                   )
               AND NOT EXISTS (
                    SELECT 1
                      FROM model_has_roles mr
                      JOIN roles r ON r.id = mr.role_id
                     WHERE mr.model_id = u.id AND mr.model_type = ? AND r.name = 'Orientador'
                   )
        ", [$desde, $hasta, $desde, $hasta, ...self::RATIFICACION_HECHA, \App\Models\User::class, \App\Models\User::class]);
    }

    private static function orientadores(string $desde, string $hasta): array
    {
        // Misma medida que los auxiliares; la lista sale de los módulos
        // activos atendidos por alguien con rol Orientador.
        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   COALESCE(s.valor, 0) AS valor,
                   NULL                 AS resueltas
              FROM users u
              LEFT JOIN (
                    SELECT g.user_id, COUNT(*) AS valor
                      FROM seer_general g
                     WHERE g.fecha >= ? AND g.fecha < ?
                     GROUP BY g.user_id
                   ) s ON s.user_id = u.id
             WHERE u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%prueba%'
               AND EXISTS (SELECT 1 FROM modulos m WHERE m.user_id = u.id AND m.activo = 1)
               AND EXISTS (
                    SELECT 1
                      FROM model_has_roles mr
                      JOIN roles r ON r.id = mr.role_id
                     WHERE mr.model_id = u.id AND mr.model_type = ? AND r.name = 'Orientador'
                   )
        ", [$desde, $hasta, \App\Models\User::class]);
    }

    private static function cumplimientos(string $desde, string $hasta): array
    {
        if ($desde >= $hasta) {
            return [];
        }

        $atendido = implode(', ', array_fill(0, count(self::CUMPLIMIENTO_ATENDIDO), '?'));

        // Sólo quien registró al menos uno. Fuera los conciliadores, salvo que
        // también sean Auxiliar.
        return DB::select("
            SELECT u.id,
                   u.name,
                   u.delegacion,
                   u.foto_perfil,
                   COALESCE(s.valor, 0) AS valor,
                   COALESCE(s.aud, 0)   AS d_audiencia,
                   COALESCE(s.rat, 0)   AS d_ratificacion,
                   NULL                 AS resueltas
              FROM users u
              LEFT JOIN (
                    SELECT p.user_id,
                           COUNT(*) AS valor,
                           SUM(p.tipo_pago = 'Audiencia')    AS aud,
                           SUM(p.tipo_pago = 'Ratificacion') AS rat
                      FROM pago_solicitud p
                     WHERE p.fecha_conclucion >= ? AND p.fecha_conclucion < ?
                       AND p.estatus IN ($atendido)
                       AND p.tipo_pago IN ('Audiencia', 'Ratificacion')
                       AND COALESCE(p.observaciones, '') NOT LIKE 'Pagado en el cumplimiento #%'
                     GROUP BY p.user_id
                   ) s ON s.user_id = u.id
             WHERE u.estatus = 'Activo'
               AND u.email NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%temporal%'
               AND u.name  NOT LIKE '%prueba%'
               AND s.valor > 0
               AND NOT (
                    EXISTS (
                        SELECT 1
                          FROM model_has_roles mr
                          JOIN roles r ON r.id = mr.role_id
                         WHERE mr.model_id = u.id AND mr.model_type = ? AND r.name = 'Conciliador'
                    )
                    AND NOT EXISTS (
                        SELECT 1
                          FROM model_has_roles mr
                          JOIN roles r ON r.id = mr.role_id
                         WHERE mr.model_id = u.id AND mr.model_type = ? AND r.name = 'Auxiliar'
                    )
                   )
        ", [$desde, $hasta, ...self::CUMPLIMIENTO_ATENDIDO, \App\Models\User::class, \App\Models\User::class]);
    }

    /**
     * Sólo el mes en que se empezó a guardar quién registra cada cumplimiento:
     * "desde el 12 nov.", junto al título. El mes siguiente ya es un mes completo
     * y el aviso desaparece solo.
     */
    private static function avisoCumplimientos(Carbon $inicio): ?string
    {
        $desde = Carbon::parse(self::CUMPLIMIENTOS_DESDE);

        if (! $desde->isSameMonth($inicio) || $desde->day === 1) {
            return null;
        }

        return 'desde el '.$desde->day.' '.Str::lower(mb_substr(self::MESES[$desde->month], 0, 3)).'.';
    }

    /**
     * "57 solicitudes · 8 ratificaciones" (o, corto, "57 sol. · 8 rat.").
     * Null si la persona no tiene desglose.
     */
    public static function desglose(?array $desglose, bool $corto = false): ?string
    {
        if (! $desglose) {
            return null;
        }

        $partes = [];
        foreach ($desglose as $clave => $n) {
            [$abrev, $uno, $varios] = self::PARTES[$clave] ?? [$clave, $clave, $clave];
            $partes[] = number_format($n).' '.($corto ? $abrev : ($n === 1 ? $uno : $varios));
        }

        return implode(' · ', $partes);
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
                'desglose'  => null,
                'mayor'     => -1,
            ];

            $actual = &$porNombre[$llave];

            $actual['valor']     += (int) $fila->valor;
            $actual['resueltas'] += (int) $fila->resueltas;

            // Desglose (columnas d_*): auxiliares y cumplimientos.
            foreach ((array) $fila as $columna => $n) {
                if (str_starts_with($columna, 'd_')) {
                    $clave = substr($columna, 2);
                    $actual['desglose'][$clave] = ($actual['desglose'][$clave] ?? 0) + (int) $n;
                }
            }

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
