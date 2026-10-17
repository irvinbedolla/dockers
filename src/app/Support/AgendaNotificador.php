<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Agenda del notificador: sólo ve las audiencias y "Mis notificaciones",
 * sus citados pendientes de diligenciar (no el historial ni las demás agendas).
 *
 * Cada notificación pendiente se pinta en el día de su audiencia, que es el
 * límite para notificar, con un color de urgencia: rojo si faltan 2 días o
 * menos, ámbar si faltan 5 o menos, verde si hay más tiempo. Si la cita ya
 * no tiene audiencia se pinta hoy.
 *
 * Sólo se acota a quien únicamente tiene el rol Notificador; si la cuenta
 * tiene otro rol, ve la agenda completa como siempre.
 */
class AgendaNotificador
{
    public const ROL = 'Notificador';

    public static function soloNotificador(?User $usuario): bool
    {
        return $usuario !== null
            && $usuario->hasRole(self::ROL)
            && $usuario->getRoleNames()->diff([self::ROL])->isEmpty();
    }

    /**
     * Eventos de FullCalendar con las notificaciones pendientes del notificador.
     *
     * @return Collection<int, array>
     */
    public static function eventos(User $notificador, string $desde, string $hasta): Collection
    {
        $hoy = Carbon::today();

        // Audiencia de la cita: la que tiene ligada o, si no, la más reciente de la solicitud.
        $fechaAudiencia = "COALESCE(a.fecha, (SELECT a2.fecha FROM audiencias a2 WHERE a2.id_solicitud = c.id_solicitud ORDER BY a2.id DESC LIMIT 1))";
        $horaAudiencia  = "COALESCE(a.hora, (SELECT a2.hora FROM audiencias a2 WHERE a2.id_solicitud = c.id_solicitud ORDER BY a2.id DESC LIMIT 1))";

        return DB::table('seer_citados as c')
            ->join('seer_general as g', 'g.id', '=', 'c.id_solicitud')
            ->leftJoin('audiencias as a', 'a.id', '=', 'c.audiencia_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->where('c.id_notificador', $notificador->id)
            ->where('c.estatus', 'Pendiente')
            ->select([
                'c.id', 'c.id_solicitud', 'c.nombre', 'c.primer_apellido', 'c.segundo_apellido',
                'c.tipo_vialidad', 'c.calle', 'c.n_ext', 'c.n_int', 'c.colonia', 'c.tipo_notificacion',
                'g.NUE', 'g.delegacion', 'm.nombre as municipio',
                DB::raw("{$fechaAudiencia} AS fecha_audiencia"),
                DB::raw("{$horaAudiencia} AS hora_audiencia"),
            ])
            ->get()
            ->map(function ($c) use ($hoy) {
                $fecha = $c->fecha_audiencia ? Carbon::parse($c->fecha_audiencia)->startOfDay() : null;
                $dia   = $fecha && $fecha->greaterThanOrEqualTo($hoy) ? $fecha : $hoy->copy();
                $dias  = $fecha ? (int) $hoy->diffInDays($fecha, false) : null;

                $color = match (true) {
                    $dias === null || $dias <= 2 => SemaforoAgenda::ROJO,
                    $dias <= 5                   => SemaforoAgenda::AMARILLO,
                    default                      => SemaforoAgenda::VERDE,
                };

                $citado    = trim(preg_replace('/\s+/', ' ', "{$c->nombre} {$c->primer_apellido} {$c->segundo_apellido}"));
                $domicilio = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
                    $c->tipo_vialidad, $c->calle, $c->n_ext ? '#'.$c->n_ext : null, $c->n_int ? 'int. '.$c->n_int : null,
                ]))));
                $lugar = trim(implode(', ', array_filter([$c->colonia ? 'Col. '.$c->colonia : null, $c->municipio])));
                $horaAud = $c->hora_audiencia ? substr((string) $c->hora_audiencia, 0, 5) : null;

                return [
                    'id'     => 'notif-'.$c->id,
                    'title'  => $citado ?: 'Sin nombre',
                    'start'  => $dia->toDateString(),
                    'allDay' => true,
                    'extendedProps' => [
                        'id_citado'   => $c->id,
                        'hora'        => $fecha ? ($dias < 0 ? 'Audiencia vencida' : 'Audiencia '.$horaAud) : 'Sin audiencia',
                        'color'       => $color,
                        'nue'         => $c->NUE,
                        'citado'      => $citado,
                        'tipo'        => $c->tipo_notificacion,
                        'domicilio'   => $domicilio,
                        'lugar'       => $lugar,
                        'delegacion'  => $c->delegacion,
                        'audiencia'   => $fecha ? $fecha->locale('es')->isoFormat('dddd D [de] MMMM').($horaAud ? ' · '.$horaAud : '') : null,
                        'dias'        => $dias,
                        'url'         => route('seer.notificador', $c->id),
                        'lineas'      => [
                            ['Expediente', $c->NUE],
                            ['Domicilio', Str::limit(trim($domicilio.($lugar ? ', '.$lugar : '')), 80)],
                            ['Tipo', $c->tipo_notificacion],
                        ],
                    ],
                ];
            })
            ->filter(fn ($e) => $e['start'] >= $desde && $e['start'] <= $hasta)
            ->values();
    }
}
