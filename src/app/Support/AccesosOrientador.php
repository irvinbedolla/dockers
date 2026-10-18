<?php

namespace App\Support;

use App\Models\Modulo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Datos rápidos del Inicio para quien atiende un módulo (orientadores y
 * auxiliares que llenan solicitudes), en la tarjeta del saludo, como los de
 * recepción (AccesosRecepcion):
 *
 *   1. Quién sigue en su módulo y el turno de ahora (SigueEnModulo); se
 *      actualiza solo con /mi-modulo/avisos;
 *   2. Sus citas de hoy por estatus y su tiempo promedio de atención contra
 *      la duración de Horarios de citas;
 *   3. Las próximas citas de su módulo;
 *   4. Sus solicitudes capturadas (hoy y en el mes) y el acceso a crear una,
 *      o a continuar la que tiene prellenada.
 *
 * Incluye las citas de prueba de su módulo, igual que Mi módulo.
 */
class AccesosOrientador
{
    private const PROXIMAS = 4;

    /** ¿Tiene un módulo activo asignado? (Administración → Módulos) */
    public static function aplica(?User $usuario): bool
    {
        return $usuario !== null && FlujoCita::migrado() && self::modulos($usuario)->isNotEmpty();
    }

    public static function para(User $usuario): array
    {
        $ahora   = now();
        $hoy     = $ahora->toDateString();
        $modulos = self::modulos($usuario);
        $ids     = $modulos->pluck('id')->all();

        // 1. Quién sigue (la misma regla que la pantalla y Mi módulo).
        $sigue    = [];
        $vigentes = collect();
        foreach ($modulos->groupBy('delegacion') as $sede => $deLaSede) {
            $citas = PantallaEspera::citasVigentes((string) $sede, $ahora, true, SigueEnModulo::COLUMNAS);
            foreach ($deLaSede as $modulo) {
                $suyas    = $citas->where('modulo_id', $modulo->id)->values();
                $vigentes = $vigentes->merge($suyas);
                $sigue[]  = SigueEnModulo::resumen($modulo, $suyas, $ahora);
            }
        }

        // 2. Citas de hoy de sus módulos.
        $hoyCitas = DB::table('recepcion')->where('fecha', $hoy)->whereIn('modulo_id', $ids ?: [0])
            ->get(['estatus', 'atencion_inicio_at', ...(FlujoCita::conFinAtencion() ? ['atencion_fin_at'] : [])]);
        $duraciones = $hoyCitas
            ->filter(fn ($c) => $c->estatus === 'atendido' && $c->atencion_inicio_at && ($c->atencion_fin_at ?? null))
            ->map(fn ($c) => max(0, (int) round(Carbon::parse($c->atencion_inicio_at)->diffInSeconds(Carbon::parse($c->atencion_fin_at), false) / 60)));

        // 3. Próximas: vigentes que nadie ha llamado, en orden de hora.
        $llamadas = $vigentes->filter(fn ($c) => ! empty($c->llamado_at))->pluck('id')->all();
        $proximas = $vigentes
            ->reject(fn ($c) => in_array($c->id, $llamadas, true))
            ->sortBy(fn ($c) => $c->hora.'|'.str_pad((string) $c->id, 10, '0', STR_PAD_LEFT))
            ->take(self::PROXIMAS)
            ->map(fn ($c) => [
                'hora'    => substr((string) $c->hora, 0, 5),
                'folio'   => FolioCita::mostrar($c),
                'persona' => Str::title(mb_strtolower(trim((string) $c->solicitante))) ?: 'Sin nombre',
                'tipo'    => (string) $c->tipo,
                'enSala'  => $c->estatus === 'confirmada',
            ])
            ->values()->all();

        // 4. Sus solicitudes.
        $prellenado = PrellenadoCita::paraVista();

        return [
            'minutos' => FlujoCita::minutosAviso(),
            'sigue'   => $sigue,
            'hoy'     => [
                'total'      => $hoyCitas->count(),
                'atendidas'  => $hoyCitas->where('estatus', 'atendido')->count(),
                'enSala'     => $hoyCitas->where('estatus', 'confirmada')->count(),
                'porLlegar'  => $hoyCitas->where('estatus', 'pendiente')->count(),
                'ausentes'   => $hoyCitas->where('estatus', 'expirada')->count(),
                'promedio'   => $duraciones->isEmpty() ? null : (int) round($duraciones->avg()),
                'duracion'   => (int) HorarioCitas::config((string) $modulos->first()->delegacion, 'Solicitud')->duracion,
            ],
            'proximas'     => $proximas,
            'solicitudes'  => [
                'hoy'        => DB::table('seer_general')->where('user_id', $usuario->id)->whereDate('fecha', $hoy)->count(),
                'mes'        => DB::table('seer_general')->where('user_id', $usuario->id)
                    ->whereBetween('fecha', [$ahora->copy()->startOfMonth()->toDateString(), $hoy])->count(),
                'crear'      => $usuario->can('solicitudes_crear') ? route('solicitud') : null,
                'prellenado' => $prellenado ? ['folio' => $prellenado['folio'], 'persona' => $prellenado['persona'] ?? null] : null,
            ],
            'urls' => [
                'modulo' => route('mi_modulo'),
                'avisos' => route('mi_modulo.avisos'),
            ],
        ];
    }

    private static function modulos(User $usuario): Collection
    {
        static $cache = [];

        return $cache[$usuario->id] ??= Modulo::where('user_id', $usuario->id)->where('activo', 1)
            ->orderBy('delegacion')->orderBy('orden')->get();
    }
}
