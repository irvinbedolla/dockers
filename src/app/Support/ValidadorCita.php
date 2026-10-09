<?php

namespace App\Support;

use App\Models\Recepcion;
use Illuminate\Support\Carbon;

/**
 * Qué pasa cuando alguien llega con su cita: la regla que antes vivía dentro
 * de RecepcionController::confirmarAsistencia (el destino del QR) y que ahora
 * comparte con el escáner de recepción.
 *
 *   - Es hoy y está pendiente, y llegó a más tardar 5 minutos después de su
 *     hora: se confirma.
 *   - Es hoy y llegó después de esa tolerancia: se marca expirada.
 *   - Es otro día futuro: no cambia nada.
 *   - Ya pasó y seguía pendiente: se marca expirada.
 *
 * Una cita atendida o confirmada de un día pasado ya no se toca: antes se
 * marcaba expirada al volver a escanear un QR viejo, y eso borraba el dato de
 * que sí se atendió.
 */
class ValidadorCita
{
    public const TOLERANCIA_MINUTOS = 5;

    /**
     * @return array{resultado: string, cambio: bool}
     *   resultado: confirmada | ya_confirmada | atendida | tarde | expirada | otro_dia | pasada
     */
    public static function validar(Recepcion $cita, ?Carbon $ahora = null): array
    {
        $ahora = $ahora ?? now();
        $fecha = $cita->fecha->copy()->startOfDay();
        $hoy   = $ahora->copy()->startOfDay();

        if ($cita->estatus === 'expirada') {
            return ['resultado' => 'expirada', 'cambio' => false];
        }

        if ($fecha->greaterThan($hoy)) {
            return ['resultado' => 'otro_dia', 'cambio' => false];
        }

        if ($fecha->lessThan($hoy)) {
            if ($cita->estatus === 'pendiente') {
                $cita->update(['estatus' => 'expirada']);

                return ['resultado' => 'expirada', 'cambio' => true];
            }

            return ['resultado' => 'pasada', 'cambio' => false];
        }

        // Es hoy.
        if ($cita->estatus === 'atendido') {
            return ['resultado' => 'atendida', 'cambio' => false];
        }
        if ($cita->estatus === 'confirmada') {
            return ['resultado' => 'ya_confirmada', 'cambio' => false];
        }

        $limite = $fecha->copy()->setTimeFromTimeString($cita->getRawOriginal('hora'))
            ->addMinutes(self::TOLERANCIA_MINUTOS);

        if ($ahora->greaterThan($limite)) {
            $cita->update(['estatus' => 'expirada']);

            return ['resultado' => 'tarde', 'cambio' => true];
        }

        $cita->update(['estatus' => 'confirmada']);

        return ['resultado' => 'confirmada', 'cambio' => true];
    }
}
