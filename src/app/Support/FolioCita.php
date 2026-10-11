<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Folio de las citas de recepción: SEDE-TRÁMITE+AÑO-NÚMERO.
 *
 *   MOR-S26-0142  →  Morelia · Solicitud · 2026 · cita 142 del año
 *
 * El número corre por sede, trámite y año, y vuelve a 1 cada enero.
 * Las claves de sede son las mismas de los expedientes (MOR/SOL/2026/…).
 */
class FolioCita
{
    public const SEDES = [
        'Morelia'         => 'MOR',
        'Uruapan'         => 'URU',
        'Zamora'          => 'ZAM',
        'Zitácuaro'       => 'ZIT',
        'Sahuayo'         => 'SAH',
        'Lázaro Cárdenas' => 'LAZ',
    ];

    public const TRAMITES = [
        'Solicitud'    => 'S',
        'Ratificación' => 'R',
        'Asesoría'     => 'A',
        'Cumplimiento' => 'C',
    ];

    /** Siguiente folio para la sede y el trámite, o null si no se reconocen. */
    public static function generar(?string $sede, ?string $tramite, $cuando = null): ?string
    {
        $prefijo = self::prefijo($sede, $tramite, $cuando);
        if ($prefijo === null) {
            return null;
        }

        // INSERT … ON DUPLICATE KEY UPDATE con LAST_INSERT_ID(expr): MySQL
        // incrementa y devuelve el valor en la misma operación, sin carreras.
        DB::statement(
            'INSERT INTO folios_citas (prefijo, ultimo) VALUES (?, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE ultimo = LAST_INSERT_ID(ultimo + 1)',
            [$prefijo]
        );
        $numero = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS n')->n;

        return $prefijo.'-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public static function prefijo(?string $sede, ?string $tramite, $cuando = null): ?string
    {
        $clave = self::SEDES[trim((string) $sede)] ?? null;
        $letra = self::TRAMITES[self::normalizarTramite($tramite)] ?? null;

        if ($clave === null || $letra === null) {
            return null;
        }

        $anio = ($cuando ? Carbon::parse($cuando) : now())->format('y');

        return "{$clave}-{$letra}{$anio}";
    }

    /**
     * El folio que se le muestra a la gente. Las citas anteriores a este
     * cambio no tienen código y conservan su número de siempre.
     * Sirve igual para el modelo que para un renglón de DB::table().
     */
    public static function mostrar($cita): string
    {
        $codigo = data_get($cita, 'codigo');

        return $codigo ?: str_pad((string) data_get($cita, 'consecutivo'), 5, '0', STR_PAD_LEFT);
    }

    /** ¿Lo escrito tiene forma de folio nuevo? Acepta minúsculas y sin guiones. */
    public static function normalizar(string $texto): ?string
    {
        $texto = strtoupper(preg_replace('/[\s\-_\/]+/', '', $texto));

        if (! preg_match('/^([A-Z]{3})([SRAC])(\d{2})(\d{1,6})$/', $texto, $m)) {
            return null;
        }

        return "{$m[1]}-{$m[2]}{$m[3]}-".str_pad($m[4], 4, '0', STR_PAD_LEFT);
    }

    private static function normalizarTramite(?string $tramite): string
    {
        // El formulario público a veces manda "Asesoria" sin acento.
        return ['Asesoria' => 'Asesoría', 'Ratificacion' => 'Ratificación'][$tramite] ?? (string) $tramite;
    }
}
