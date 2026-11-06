<?php

namespace App\Support;

use App\Http\Controllers\HomeController;
use App\Mail\CitaReasignadaMail;
use App\Models\Modulo;
use App\Models\Recepcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mover una cita a otro módulo de su sede.
 *
 * Antes vivía dentro de ModulosController; ahora también se usa desde
 * Turnos → Todos los turnos, así que las reglas están en un solo lugar:
 *
 *   opcionesDelDia() / opciones()  cómo está cada módulo a la hora de cada
 *                                  cita (para el diálogo).
 *   mover()                        valida otra vez en el servidor, guarda,
 *                                  deja bitácora y, si la cita es en línea,
 *                                  reenvía el acuse con el módulo nuevo.
 */
class ReasignacionCita
{
    /** Estatus en los que una cita todavía se puede mover. */
    public const MOVIBLES = ['pendiente', 'confirmada'];

    public static function movible(object $cita, string $hoy): bool
    {
        $fecha = $cita->fecha instanceof \DateTimeInterface ? $cita->fecha->format('Y-m-d') : (string) $cita->fecha;

        // Las de excepción también se mueven, pero sólo entre módulos de casos de excepción.
        return in_array(strtolower((string) $cita->estatus), self::MOVIBLES, true)
            && $fecha >= $hoy;
    }

    /**
     * Opciones de reasignación para las citas movibles de un mismo día y sede.
     *
     * $modulos : módulos de la sede (AsignadorModulos::deSede).
     * $delDia  : TODAS las citas de ese día y sede, con modulo_clave; con
     *            ellas se sabe qué módulo está ocupado a qué hora.
     * $citas   : las citas a las que se les calculan opciones.
     *
     * @return array<int, Collection> id de cita => opciones
     */
    public static function opcionesDelDia(Collection $modulos, Collection $delDia, iterable $citas, string $hoy): array
    {
        $ocupacion = [];
        foreach ($delDia as $c) {
            if (in_array($c->estatus, ['expirada', 'cancelada'], true)) {
                continue;
            }
            $h = substr($c->hora, 0, 5);
            if ($c->modulo_clave) {
                $ocupacion[$h]['m'.$c->modulo_clave] = $c;
            }
            if ($c->auxiliar) {
                $ocupacion[$h]['u'.$c->auxiliar] = $c;
            }
        }

        $opciones = [];
        foreach ($citas as $c) {
            if (! self::movible($c, $hoy)) {
                continue;
            }
            $h = substr($c->hora, 0, 5);
            // Una cita normal sólo va a módulos normales y una de excepción sólo a módulos de casos de excepción.
            $esExc = ($c->exepcion ?? null) === 'Si';
            $opciones[$c->id] = $modulos->filter(fn (Modulo $m) => $m->id == $c->modulo_clave || $m->esDeExcepcion() === $esExc)->map(function (Modulo $m) use ($c, $h, $ocupacion) {
                $choque = $ocupacion[$h]['m'.$m->id] ?? ($m->user_id ? ($ocupacion[$h]['u'.$m->user_id] ?? null) : null);

                $estado = match (true) {
                    $m->id == $c->modulo_clave                  => 'actual',
                    ! AsignadorModulos::atendible($m)           => $m->activo ? 'sin_persona' : 'inactivo',
                    // Pausa total (Administración → Módulos): no recibe citas nuevas, ni movidas a mano.
                    ! $m->recibeVentanilla()                    => 'pausa',
                    // "Acepta reasignaciones" apagado desde una fecha (Administración → Módulos).
                    ! $m->aceptaReasignacion($c->fecha instanceof \DateTimeInterface ? $c->fecha->format('Y-m-d') : substr((string) $c->fecha, 0, 10)) => 'sin_reasignar',
                    $m->enComida($c->hora)                      => 'comida',
                    $choque !== null && $choque->id != $c->id   => 'ocupado',
                    default                                     => 'libre',
                };

                return [
                    'id'      => $m->id,
                    'nombre'  => $m->nombre,
                    'persona' => optional($m->persona)->name,
                    'recibe'  => $m->recibe($c->tipo) || $m->recibeDeRespaldo($c->tipo),
                    'estado'  => $estado,
                    'choque'  => $estado === 'ocupado'
                        ? FolioCita::mostrar($choque).' · '.$choque->solicitante
                        : null,
                ];
            })->values();
        }

        return $opciones;
    }

    /**
     * Lo mismo que opcionesDelDia() pero para citas sueltas de distintos días
     * y sedes (una página de Todos los turnos): carga lo necesario por grupo.
     */
    public static function opciones(iterable $citas, ?string $hoy = null): array
    {
        $hoy = $hoy ?? Carbon::today()->toDateString();

        $grupos = collect($citas)
            ->filter(fn ($c) => self::movible($c, $hoy))
            ->groupBy(fn ($c) => $c->delegacion.'|'.(string) $c->fecha);

        $opciones = [];
        foreach ($grupos as $clave => $citasGrupo) {
            [$sede, $fecha] = explode('|', $clave, 2);

            $modulos   = AsignadorModulos::deSede($sede);
            $porNombre = $modulos->keyBy('nombre');
            $conClave  = function ($c) use ($porNombre) {
                // Las citas viejas no tienen modulo_id: se reconocen por nombre.
                $c->modulo_clave = $c->modulo_id ?? optional($porNombre->get($c->lugar_auxiliar))->id;

                return $c;
            };

            $delDia = DB::table('recepcion')
                ->where('delegacion', $sede)
                ->where('fecha', $fecha)
                ->get(['id', 'consecutivo', 'codigo', 'solicitante', 'hora', 'estatus', 'modulo_id', 'lugar_auxiliar', 'auxiliar'])
                ->map($conClave);

            $opciones += self::opcionesDelDia($modulos, $delDia, $citasGrupo->map($conClave), $hoy);
        }

        return $opciones;
    }

    /**
     * Mueve la cita. Regresa ['success' => ?string, 'error' => ?string]; los
     * dos pueden venir llenos (se guardó pero no salió el correo).
     */
    public static function mover(Recepcion $cita, Modulo $destino, ?int $porUsuario): array
    {
        $destino->loadMissing('persona');

        $fecha = $cita->fecha->format('Y-m-d');
        $hora  = $cita->getRawOriginal('hora');
        $hoy   = Carbon::today()->toDateString();

        $error = match (true) {
            $cita->exepcion === 'Si' && ! $destino->esDeExcepcion() => 'Un caso de excepción sólo pasa a un módulo de casos de excepción.',
            ! in_array($cita->estatus, self::MOVIBLES, true)  => 'Sólo se pueden mover citas pendientes o confirmadas.',
            $fecha < $hoy                                     => 'La cita ya pasó.',
            $destino->delegacion !== $cita->delegacion        => 'El módulo es de otra sede.',
            $cita->exepcion !== 'Si' && $destino->esDeExcepcion() => "{$destino->nombre} es de casos de excepción: no recibe citas normales.",
            $destino->id == $cita->modulo_id                  => 'La cita ya está en ese módulo.',
            ! AsignadorModulos::atendible($destino)           => "{$destino->nombre} está inactivo o no tiene persona asignada.",
            ! $destino->recibeVentanilla()                    => "{$destino->nombre} tiene en pausa todas sus citas (también ventanilla): no recibe citas nuevas. Reactívalo en Administración → Módulos.",
            ! $destino->aceptaReasignacion($fecha)            => "{$destino->nombre} no acepta reasignaciones de citas a partir del ".$destino->no_reasignar_desde?->format('d/m/Y').'. Cámbialo en Administración → Módulos.',
            $destino->enComida($hora, $destino->esDeExcepcion() ? (int) HorarioCitas::config($cita->delegacion, null, true)->duracion : null)
                                                              => "{$destino->nombre} está en su hora de comida a las ".substr($hora, 0, 5).'.',
            default                                           => null,
        };

        if (! $error && ($choque = AsignadorModulos::choque($destino, $fecha, $hora, $cita->id))) {
            $error = "{$destino->nombre} ya tiene a las ".substr($hora, 0, 5).' la cita '
                .FolioCita::mostrar($choque)." de {$choque->solicitante}.";
        }

        if ($error) {
            return ['success' => null, 'error' => $error];
        }

        $antes = $cita->lugar_auxiliar;
        $cita->update([
            'modulo_id'      => $destino->id,
            'lugar_auxiliar' => $destino->nombre,
            'auxiliar'       => $destino->user_id,
        ]);

        Log::info('Cita reasignada de módulo', [
            'cita' => $cita->id, 'de' => $antes, 'a' => $destino->nombre, 'por' => $porUsuario,
        ]);

        $aviso = 'La cita '.$cita->folio_cita
            .' pasó de '.($antes ?: 'sin módulo')." a {$destino->nombre} ({$destino->persona->name}).";

        // En línea: se le reenvía el acuse con el módulo nuevo. A un caso de excepción
        // no: sólo cambia quién lo atiende por dentro, no su día ni su hora.
        if ($cita->exepcion !== 'Si' && $cita->origen === 'linea' && filter_var($cita->correo, FILTER_VALIDATE_EMAIL)) {
            try {
                $pdf = app(HomeController::class)->verDocumentoCita($cita->id);
                Mail::to($cita->correo)->send(new CitaReasignadaMail($pdf, $cita->fresh(), $antes));
                $aviso .= " Se le envió el acuse actualizado a {$cita->correo}.";
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el acuse de reasignación', ['cita' => $cita->id, 'error' => $e->getMessage()]);

                return [
                    'success' => $aviso,
                    'error'   => 'El cambio se guardó, pero no se pudo enviar el correo. Avísale por teléfono.',
                ];
            }
        }

        return ['success' => $aviso, 'error' => null];
    }
}
