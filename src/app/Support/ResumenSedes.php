<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cifras por sede del mapa y de las gráficas de pastel.
 *
 * Las usan el Inicio del rol Directivo y la pantalla /estadisticas, así que
 * viven aquí y no en un controlador: un solo cálculo para las dos vistas.
 *
 *   totales() → ['solicitudes'|'audiencias'|'ratificaciones' => ['total' => int, 'porSede' => [sede => int]]]
 *   detalle() → [sede => solicitudes_atendidas, audiencias_celebradas, tasa_conciliacion, montos_convenios]
 */
class ResumenSedes
{
    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /**
     * Totales y desglose por sede (pasteles del panel).
     */
    public static function totales(): array
    {
        $sinIncidencia = fn ($query) => $query->where(
            fn ($sub) => $sub->whereNull('incidencia')->orWhere('incidencia', 0)
        );

        $sedes = self::SEDES;

        $solicitudesPorSede = self::totalesPorSede(
            $sinIncidencia(DB::table('seer_general'))->select('delegacion', DB::raw('COUNT(*) as total'))->groupBy('delegacion')->pluck('total', 'delegacion'),
            $sedes
        );

        $audienciasPorSede = self::totalesPorSede(
            DB::table('audiencias')->select('delegacion', DB::raw('COUNT(*) as total'))->groupBy('delegacion')->pluck('total', 'delegacion'),
            $sedes
        );

        $ratificacionesPorSede = self::totalesPorSede(
            $sinIncidencia(DB::table('turnos')->where('tipo', 'Ratificación'))->select('delegacion', DB::raw('COUNT(*) as total'))->groupBy('delegacion')->pluck('total', 'delegacion'),
            $sedes
        );

        return [
            'solicitudes'    => ['total' => array_sum($solicitudesPorSede),    'porSede' => $solicitudesPorSede],
            'audiencias'     => ['total' => array_sum($audienciasPorSede),     'porSede' => $audienciasPorSede],
            'ratificaciones' => ['total' => array_sum($ratificacionesPorSede), 'porSede' => $ratificacionesPorSede],
        ];
    }

    /**
     * 'audiencias.delegacion' y 'turnos.delegacion' son texto libre, no el
     * enum acentuado que sí tienen 'seer_general'/'users' -en la práctica
     * ahí se guarda "Zitacuaro" sin acento-, así que emparejar contra
     * self::SEDES por igualdad literal deja esas sedes siempre en 0.
     * Se compara sin acentos (Str::ascii) y el resultado usa el nombre
     * canónico como llave, para que el resto del código (incluido el JS
     * de las gráficas) no tenga que saber de esta inconsistencia.
     */
    private static function totalesPorSede(\Illuminate\Support\Collection $porDelegacionCruda, array $sedes): array
    {
        $normalizados = $porDelegacionCruda->mapWithKeys(
            fn ($total, $delegacion) => [Str::ascii(trim($delegacion)) => $total]
        );

        $resultado = [];
        foreach ($sedes as $sede) {
            $resultado[$sede] = $normalizados[Str::ascii($sede)] ?? 0;
        }

        return $resultado;
    }

    /**
     * Las 4 métricas de la tarjeta que se abre al hacer click en una sede
     * del mapa: solicitudes atendidas, audiencias celebradas, tasa de
     * conciliación y montos de convenios, agrupadas por delegación.
     */
    public static function detalle(): array
    {
        $sedes = self::SEDES;

        // "Atendida": ya salió del flujo de trámite inicial (no está en
        // ninguno de estos estatus de solicitud todavía sin resolver) y no
        // está marcada como incidencia.
        $solicitudesAtendidas = DB::table('seer_general')
            //->whereNotIn('estatus', ['Pendiente', 'Aceptado', 'Confirmado', 'Rechazado', 'Prevencion'])
            ->where(fn ($sub) => $sub->whereNull('incidencia')->orWhere('incidencia', 0))
            ->select('delegacion', DB::raw('COUNT(*) as total'))
            ->groupBy('delegacion')
            ->pluck('total', 'delegacion');

        // "Celebrada": no quedó en un estatus de trámite/reagenda, salvo
        // 'No conciliacion' que sí cuenta si su seer_conciliadores (unido
        // por id_solicitud) no tiene resolicion_primera capturada.
        $condicionCelebrada = function ($query) {
            $query->whereNotIn('audiencias.estatus', ['Pendiente', 'Reagendada', 'Archivada', 'No conciliacion'])
                ->orWhere(function ($sub) {
                    $sub->where('audiencias.estatus', 'No conciliacion')
                        ->whereExists(function ($existe) {
                            $existe->selectRaw(1)
                                ->from('seer_conciliadores')
                                ->whereColumn('seer_conciliadores.id_solicitud', 'audiencias.id_solicitud')
                                ->whereNull('seer_conciliadores.resolicion_primera');
                        });
                });
        };

        $audienciasCelebradas = DB::table('audiencias')
            ->where($condicionCelebrada)
            ->select('delegacion', DB::raw('COUNT(*) as total'))
            ->groupBy('delegacion')
            ->pluck('total', 'delegacion');

        $audienciasConciliadas = DB::table('audiencias')
            ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
            ->select('delegacion', DB::raw('COUNT(*) as total'))
            ->groupBy('delegacion')
            ->pluck('total', 'delegacion');

        $montosConvenios = DB::table('seer_conciliadores')
            ->join('seer_general', 'seer_general.id', '=', 'seer_conciliadores.id_solicitud')
            ->select('seer_general.delegacion', DB::raw('SUM(seer_conciliadores.monto) as total'))
            ->groupBy('seer_general.delegacion')
            ->pluck('total', 'delegacion');

        $montosTurnos = DB::table('turnos')
            ->select('delegacion', DB::raw('SUM(monto) as total'))
            ->groupBy('delegacion')
            ->pluck('total', 'delegacion');

        $solicitudesAtendidas  = self::totalesPorSede($solicitudesAtendidas, $sedes);
        $audienciasCelebradas  = self::totalesPorSede($audienciasCelebradas, $sedes);
        $audienciasConciliadas = self::totalesPorSede($audienciasConciliadas, $sedes);
        $montosConvenios       = self::totalesPorSede($montosConvenios, $sedes);
        $montosTurnos          = self::totalesPorSede($montosTurnos, $sedes);

        $resumen = [];
        foreach ($sedes as $sede) {
            $celebradas  = (int) $audienciasCelebradas[$sede];
            $conciliadas = (int) $audienciasConciliadas[$sede];

            $resumen[$sede] = [
                'solicitudes_atendidas' => (int) $solicitudesAtendidas[$sede],
                'audiencias_celebradas' => $celebradas,
                'tasa_conciliacion'     => $celebradas > 0 ? round($conciliadas / $celebradas * 100, 1) : 0,
                'montos_convenios'      => (float) $montosConvenios[$sede] + (float) $montosTurnos[$sede],
            ];
        }

        return $resumen;
    }
}
