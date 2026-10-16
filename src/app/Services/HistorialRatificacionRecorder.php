<?php

namespace App\Services;

use App\Models\HistorialRatificacion;
use App\Models\HistorialRatificacionDetalle;
use App\Models\Turnos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Aplica la edición de una ratificación y la deja en historial_ratificaciones
 * (quién, cuándo, motivo) e historial_ediciones_ratificaciones (antes / después).
 * El cambio y su registro en el historial van en la misma transacción.
 * Si la edición no modifica ninguna columna no se registra nada.
 */
class HistorialRatificacionRecorder
{
    public static function actualizar(Turnos $ratificacion, array $cambios, ?string $motivo): void
    {
        DB::transaction(function () use ($ratificacion, $cambios, $motivo) {
            $antes = $ratificacion->getRawOriginal();

            $ratificacion->update($cambios);

            // Se relee de BD y se compara contra lo que había, así no se registran
            $despues = $ratificacion->fresh()->getRawOriginal();

            $cambiados = [];
            foreach ($despues as $columna => $valor) {
                if ($columna !== 'updated_at' && (string) ($antes[$columna] ?? '') !== (string) $valor) {
                    $cambiados[$columna] = true;
                }
            }

            if (empty($cambiados)) {
                return;
            }

            $user = auth()->user();

            $historial = HistorialRatificacion::create([
                'ratificacion_id' => $ratificacion->getKey(),
                'NUE'             => $antes['NUE'] ?? null,
                'delegacion'      => $antes['delegacion'] ?? null,
                'estatus'         => $antes['estatus'] ?? null,
                'user_id'         => $user->id ?? null,
                'user_nombre'     => $user->name ?? null,
                'user_rol'        => $user ? Str::limit($user->getRoleNames()->implode(', '), 57) : null,
                'ip'              => request()->ip(),
                'motivo'          => $motivo,
            ]);

            HistorialRatificacionDetalle::create([
                'historial_edicion_id' => $historial->id,
                'tabla'                => $ratificacion->getTable(),
                'registro_id'          => $ratificacion->getKey(),
                'accion'               => 'updated',
                'datos_antes'          => array_intersect_key($antes, $cambiados),
                'datos_despues'        => array_intersect_key($despues, $cambiados),
            ]);
        });
    }
}
