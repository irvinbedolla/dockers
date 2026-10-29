<?php

namespace App\Support;

use App\Models\Recepcion;
use App\Models\User;
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
 *
 * Si expiró hoy (llegó tarde o el módulo la marcó "No se presentó") y la
 * persona sí está, recepción puede atenderla de todos modos: atenderTarde().
 */
class ValidadorCita
{
    public const TOLERANCIA_MINUTOS = 5;

    /**
     * @return array{resultado: string, cambio: bool}
     *   resultado: confirmada | ya_confirmada | atendida | tarde | expirada | otro_dia | pasada | cancelada
     */
    public static function validar(Recepcion $cita, ?Carbon $ahora = null): array
    {
        $ahora = $ahora ?? now();
        $fecha = $cita->fecha->copy()->startOfDay();
        $hoy   = $ahora->copy()->startOfDay();

        // Cancelada desde Administración: nunca se revive al escanear el QR.
        if ($cita->estatus === 'cancelada') {
            return ['resultado' => 'cancelada', 'cambio' => false];
        }

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

        // Cuándo llegó (para el seguimiento del caso).
        $cita->update(['estatus' => 'confirmada'] + (FlujoCita::conSeguimiento() ? ['confirmada_at' => $ahora] : []));

        // Si es de Ratificación, cuenta como llegada en "Llegadas hoy" y le
        // avisa al conciliador. Nunca debe impedir la validación.
        try {
            LlegadasHoy::desdeCita($cita);
        } catch (\Throwable $e) {
            report($e);
        }

        return ['resultado' => 'confirmada', 'cambio' => true];
    }

    /**
     * ¿Quién puede reactivar una cita expirada? La recepción y el Super
     * Usuario (como el escáner). Con $cita, además que sea de sus sedes y de
     * los trámites que recibe (Morelia 02 sólo ratificaciones).
     */
    public static function puedeReactivar(?User $usuario, ?Recepcion $cita = null): bool
    {
        if ($usuario === null || ! $usuario->hasAnyRole(['Super Usuario', ...\App\Support\Recepcion::ROLES])) {
            return false;
        }
        if ($cita === null) {
            return true;
        }

        $sedes = $usuario->hasAnyRole(\App\Support\Recepcion::SUPERVISAN)
            ? \App\Support\Recepcion::TODAS_LAS_SEDES
            : \App\Support\Recepcion::sedes($usuario);
        $tramites = \App\Support\Recepcion::tramitesVisibles($usuario);

        return in_array($cita->delegacion, $sedes, true)
            && ($tramites === null || in_array($cita->tipo, $tramites, true));
    }

    /** ¿Se puede atender de todos modos? Sólo si expiró hoy. */
    public static function puedeAtenderTarde(Recepcion $cita, ?Carbon $ahora = null): bool
    {
        return FlujoCita::conTardias()
            && $cita->estatus === 'expirada'
            && $cita->fecha->isSameDay($ahora ?? now());
    }

    /**
     * Recepción decide atender una cita que expiró hoy: vuelve a "confirmada"
     * con la hora real de llegada y la marca llego_tarde. En Mi módulo va
     * después de quienes llegaron a tiempo (PantallaEspera::turnoDeModulo) y
     * en los reportes se distingue de "no llegó".
     */
    public static function atenderTarde(Recepcion $cita, User $quien, ?Carbon $ahora = null): bool
    {
        $ahora = $ahora ?? now();
        if (! self::puedeAtenderTarde($cita, $ahora)) {
            return false;
        }

        $cita->update([
            'estatus'         => 'confirmada',
            'llego_tarde'     => true,
            'llego_tarde_por' => $quien->id,
        ]
            // Si el módulo ya la había llamado y la marcó "no se presentó", que la vuelva a llamar.
            + (FlujoCita::migrado() ? ['llamado_at' => null] : [])
            + (FlujoCita::conSeguimiento() ? ['confirmada_at' => $ahora] : []));

        PantallaEspera::olvidar((string) $cita->delegacion);

        try {
            LlegadasHoy::desdeCita($cita);
        } catch (\Throwable $e) {
            report($e);
        }

        return true;
    }
}
