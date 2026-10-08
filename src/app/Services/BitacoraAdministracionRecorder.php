<?php

namespace App\Services;

use App\Models\BitacoraAdministracion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Aplica un cambio a un registro y lo deja en bitacora_administracion.
 * El cambio y su registro en la bitácora van en la misma transacción.
 * Si el cambio no modifica ninguna columna no se registra nada.
 */
class BitacoraAdministracionRecorder
{
    public static function actualizar(string $tipo, Model $modelo, array $cambios, ?string $NUE = null, ?string $delegacion = null): void
    {
        DB::transaction(function () use ($tipo, $modelo, $cambios, $NUE, $delegacion) {
            $antes = $modelo->getRawOriginal();

            $modelo->fill($cambios);
            $cambiado = $modelo->getDirty();

            if (empty($cambiado)) {
                return;
            }

            $modelo->save();

            // Se relee de BD para que datos_despues tenga el mismo formato que datos_antes.
            $despues = array_intersect_key($modelo->fresh()->getRawOriginal(), $cambiado);

            BitacoraAdministracion::create([
                'tipo'          => $tipo,
                'tabla'         => $modelo->getTable(),
                'registro_id'   => $modelo->getKey(),
                'NUE'           => $NUE ?? ($antes['NUE'] ?? null),
                'delegacion'    => $delegacion ?? ($antes['delegacion'] ?? null),
                'datos_antes'   => $antes,
                'datos_despues' => $despues,
                'user_id'       => auth()->id(),
                'user_nombre'   => auth()->user()->name ?? null,
                'ip'            => request()->ip(),
            ]);
        });
    }
}
