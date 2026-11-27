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
 *   - Turno "al momento": recepción lo agendó en ventanilla para dentro de
 *     3 horas o menos (o para un horario que ya había empezado), así que la
 *     persona está ahí. Su tolerancia es de media hora, contada desde lo más
 *     tarde entre la hora de la cita y la hora en que se agendó (limite()).
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

    /** Tolerancia de un turno "al momento" (ventanilla, la persona está ahí). */
    public const TOLERANCIA_AL_MOMENTO = 30;

    /** Un turno de ventanilla para dentro de estas horas o menos es "al momento". */
    public const HORAS_AL_MOMENTO = 3;

    /**
     * ¿Es un turno "al momento"? De ventanilla, agendado el mismo día y a lo
     * más HORAS_AL_MOMENTO antes de su hora. Sirve con el modelo o con una
     * fila de DB::table (necesita fecha, hora, origen y created_at).
     */
    public static function alMomento(object $cita): bool
    {
        if (($cita->origen ?? null) !== 'ventanilla' || empty($cita->created_at)) {
            return false;
        }
        $inicio = self::inicio($cita);
        $creada = Carbon::parse($cita->created_at);

        return $creada->isSameDay($inicio)
            && $creada->greaterThanOrEqualTo($inicio->copy()->subHours(self::HORAS_AL_MOMENTO));
    }

    /** Minutos de tolerancia que le tocan a la cita. */
    public static function tolerancia(object $cita): int
    {
        return self::alMomento($cita) ? self::TOLERANCIA_AL_MOMENTO : self::TOLERANCIA_MINUTOS;
    }

    /**
     * Hasta cuándo se puede validar la llegada: su hora más la tolerancia; si
     * es "al momento" y se agendó después de que empezó su horario, desde que
     * se agendó.
     */
    public static function limite(object $cita): Carbon
    {
        $inicio = self::inicio($cita);
        if (! self::alMomento($cita)) {
            return $inicio->addMinutes(self::TOLERANCIA_MINUTOS);
        }

        return $inicio->max(Carbon::parse($cita->created_at))->addMinutes(self::TOLERANCIA_AL_MOMENTO);
    }

    /** Fecha y hora de la cita. */
    private static function inicio(object $cita): Carbon
    {
        $fecha = $cita->fecha instanceof \DateTimeInterface ? $cita->fecha->format('Y-m-d') : substr((string) $cita->fecha, 0, 10);
        $hora  = $cita instanceof Recepcion ? $cita->getRawOriginal('hora') : (string) $cita->hora;

        return Carbon::parse($fecha.' '.$hora);
    }

    /**
     * Marca expiradas las pendientes de hoy de una sede que ya rebasaron su
     * tolerancia (la usan las pantallas viejas /pantallaMorelia y compañía,
     * que lo hacían con 5 minutos parejos para todas).
     */
    public static function expirarVencidas(string $sede, ?Carbon $ahora = null): int
    {
        $ahora = $ahora ?? now();
        $ids = Recepcion::query()
            ->whereDate('fecha', $ahora->toDateString())
            ->where('delegacion', $sede)
            ->where('estatus', 'pendiente')
            ->where('hora', '<=', $ahora->copy()->subMinutes(self::TOLERANCIA_MINUTOS)->format('H:i:s'))
            ->get(['id', 'fecha', 'hora', 'origen', 'created_at'])
            ->filter(fn (Recepcion $c) => $ahora->greaterThan(self::limite($c)))
            ->pluck('id');

        return $ids->isEmpty() ? 0 : Recepcion::whereIn('id', $ids)->update(['estatus' => 'expirada']);
    }

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

        if ($ahora->greaterThan(self::limite($cita))) {
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

    private const CAMPOS_DESHACER = ['estatus', 'confirmada_at', 'llego_tarde', 'llego_tarde_por', 'llamado_at'];

    public static function foto(Recepcion $cita): array
    {
        $crudo = $cita->getRawOriginal();

        return array_intersect_key($crudo, array_flip(self::CAMPOS_DESHACER));
    }

    public static function deshacer(Recepcion $cita, array $antes, string $despues): bool
    {
        if ($cita->estatus !== $despues || $cita->atencion_inicio_at || ($despues === 'confirmada' && $cita->llamado_at)) {
            return false;
        }

        $confirmadaAt = $cita->confirmada_at;

        $cita->update($antes);

        if ($despues === 'confirmada' && $confirmadaAt) {
            try {
                LlegadasHoy::olvidarCita($cita, $confirmadaAt);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        PantallaEspera::olvidar((string) $cita->delegacion);

        return true;
    }
}
