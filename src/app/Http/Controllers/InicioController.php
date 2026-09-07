<?php

namespace App\Http\Controllers;

use App\Support\AgendaContexto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pantalla de Inicio.
 *
 * Deliberadamente aparte del resto: es el lugar donde más adelante van a
 * colgarse los datos y las notificaciones propias de cada rol, así que no
 * depende de ningún otro controlador. Por ahora saluda al usuario y muestra
 * la agenda, que se arma con el mismo parcial que /agenda.
 *
 * El rol Directivo no ve la agenda: ve un panel de totales y desglose por
 * sede, así que resuelve sus propios datos en vez de pasar por AgendaContexto.
 */
class InicioController extends Controller
{
    private const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    public function index()
    {
        $usuario  = auth()->user();
        $userRole = $usuario->roles->pluck('name')->all();

        if (in_array('Directivo', $userRole, true)) {
            return view('inicio.index', [
                'usuario'      => $usuario,
                'userRole'     => $userRole,
                'resumen'      => $this->resumenDirectivo(),
                'resumenSedes' => $this->resumenPorSedeDetallado(),
            ]);
        }

        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('inicio.index', compact('usuario', 'userRole', 'sedes', 'conciliadores'));
    }

    /**
     * Totales y desglose por sede para el panel del rol Directivo.
     */
    private function resumenDirectivo(): array
    {
        $sinIncidencia = fn ($query) => $query->where(
            fn ($sub) => $sub->whereNull('incidencia')->orWhere('incidencia', 0)
        );

        $sedes = self::SEDES;

        $solicitudesPorSede = $this->totalesPorSede(
            $sinIncidencia(DB::table('seer_general'))->select('delegacion', DB::raw('COUNT(*) as total'))->groupBy('delegacion')->pluck('total', 'delegacion'),
            $sedes
        );

        $audienciasPorSede = $this->totalesPorSede(
            DB::table('audiencias')->select('delegacion', DB::raw('COUNT(*) as total'))->groupBy('delegacion')->pluck('total', 'delegacion'),
            $sedes
        );

        $ratificacionesPorSede = $this->totalesPorSede(
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
    private function totalesPorSede(\Illuminate\Support\Collection $porDelegacionCruda, array $sedes): array
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
    private function resumenPorSedeDetallado(): array
    {
        $sedes = self::SEDES;

        // "Atendida": ya salió del flujo de trámite inicial (no está en
        // ninguno de estos estatus de solicitud todavía sin resolver).
        $solicitudesAtendidas = DB::table('seer_general')
            ->whereNotIn('estatus', ['Pendiente', 'Aceptado', 'Confirmado', 'Rechazado', 'Prevencion'])
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
            ->where($condicionCelebrada)
            ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
            ->select('delegacion', DB::raw('COUNT(*) as total'))
            ->groupBy('delegacion')
            ->pluck('total', 'delegacion');

        $montosConvenios = DB::table('seer_conciliadores')
            ->join('seer_general', 'seer_general.id', '=', 'seer_conciliadores.id_solicitud')
            ->select('seer_general.delegacion', DB::raw('SUM(seer_conciliadores.monto) as total'))
            ->groupBy('seer_general.delegacion')
            ->pluck('total', 'delegacion');

        $solicitudesAtendidas  = $this->totalesPorSede($solicitudesAtendidas, $sedes);
        $audienciasCelebradas  = $this->totalesPorSede($audienciasCelebradas, $sedes);
        $audienciasConciliadas = $this->totalesPorSede($audienciasConciliadas, $sedes);
        $montosConvenios       = $this->totalesPorSede($montosConvenios, $sedes);

        $resumen = [];
        foreach ($sedes as $sede) {
            $celebradas  = (int) $audienciasCelebradas[$sede];
            $conciliadas = (int) $audienciasConciliadas[$sede];

            $resumen[$sede] = [
                'solicitudes_atendidas' => (int) $solicitudesAtendidas[$sede],
                'audiencias_celebradas' => $celebradas,
                'tasa_conciliacion'     => $celebradas > 0 ? round($conciliadas / $celebradas * 100, 1) : 0,
                'montos_convenios'      => (float) $montosConvenios[$sede],
            ];
        }

        return $resumen;
    }
}
