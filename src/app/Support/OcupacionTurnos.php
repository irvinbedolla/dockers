<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Qué tan llena va la agenda de turnos (citas en línea y de ventanilla)
 * contra lo que permite Administración → Horarios de citas
 * (App\Support\HorarioCitas): cupo de cada horario, límite diario de citas
 * en línea y días inhábiles. Lo pinta el calendario "Ocupación" de
 * Turnos → Todos los turnos.
 *
 * Cuenta como ocupado todo lo que no expiró (igual que HorarioCitas::
 * citasQueOcupan). Los casos de excepción tienen su propio calendario, así
 * que no gastan cupo de su trámite: se cuentan aparte. Las citas de prueba
 * no se cuentan.
 */
class OcupacionTurnos
{
    public const GRUPOS = [
        'Solicitud'    => 'Solicitudes y asesorías',
        'Ratificación' => 'Ratificaciones',
    ];

    /** Rango máximo que se pide de una vez (un mes con sus orillas). */
    public const MAX_DIAS = 62;

    /**
     * @param array<int, string> $sedes
     * @param string|null        $grupo   Solicitud | Ratificación | null (los dos)
     * @param bool               $conHoras desglose por horario (vista de semana y de día)
     */
    public static function calcular(array $sedes, string $desde, string $hasta, ?string $grupo, bool $conHoras): array
    {
        $grupos = $grupo ? [$grupo] : array_keys(self::GRUPOS);
        $tipos  = array_merge(...array_map(fn ($g) => HorarioCitas::TIPOS_DEL_GRUPO[$g], $grupos));

        $citas = DB::table('recepcion')
            ->whereBetween('fecha', [$desde, $hasta])
            ->whereIn('delegacion', self::variantes($sedes))
            ->whereIn('tipo', $tipos)
            ->where('estatus', '!=', 'expirada')
            ->when(FlujoCita::migrado(), fn ($q) => $q->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba')))
            ->get(['fecha', 'hora', 'tipo', 'origen', 'exepcion', 'estatus']);

        $inhabiles = DB::table('dias_inhabiles')
            ->whereNull('user_id')
            ->whereIn('centro', self::variantes($sedes))
            ->where('fecha_inicio', '<=', $hasta)
            ->where('fecha_final', '>=', $desde)
            ->get(['centro', 'fecha_inicio', 'fecha_final', 'horario_inicio', 'horario_final', 'descripcion']);

        $dias = [];
        for ($d = Carbon::parse($desde); $d->lte(Carbon::parse($hasta)); $d->addDay()) {
            $dias[$d->toDateString()] = self::diaVacio($d);
        }

        // Cupo: cada sede y trámite con su horario, quitando lo inhábil.
        foreach ($sedes as $sede) {
            $deLaSede = $inhabiles->filter(fn ($i) => self::mismaSede($i->centro, $sede));

            foreach ($grupos as $g) {
                $cfg      = HorarioCitas::config($sede, $g, false);
                $horarios = HorarioCitas::horarios($cfg);

                foreach ($dias as $fecha => &$dia) {
                    if (! $dia['habil'] || ! $horarios) {
                        continue;
                    }

                    $cupoDia = 0;
                    $bloqueados = 0;
                    foreach ($horarios as $hora) {
                        $bloqueo = self::inhabil($deLaSede, $fecha, $hora);
                        if ($bloqueo !== null) {
                            $bloqueados++;
                            $dia['motivoInhabil'] ??= $bloqueo;
                            continue;
                        }
                        $cap = HorarioCitas::capacidad($cfg, $hora);
                        $cupoDia += $cap;
                        if ($conHoras) {
                            $clave = substr($hora, 0, 5);
                            $dia['horas'][$clave] ??= self::horaVacia($clave);
                            $dia['horas'][$clave]['capacidad'] += $cap;
                        }
                    }

                    $dia['capacidad'] += $cupoDia;
                    $dia['grupos'][$g]['capacidad'] += $cupoDia;
                    if ($cfg->limite_diario !== null && $cupoDia > 0) {
                        $limite = min($cfg->limite_diario, $cupoDia);
                        $dia['limite'] = ($dia['limite'] ?? 0) + $limite;
                        $dia['grupos'][$g]['limite'] = ($dia['grupos'][$g]['limite'] ?? 0) + $limite;
                    }
                    if ($bloqueados > 0) {
                        $dia['inhabil'] = $bloqueados >= count($horarios) && $dia['inhabil'] !== 'parcial' ? 'total' : 'parcial';
                    }
                }
                unset($dia);
            }
        }

        // Lo que ya está agendado.
        foreach ($citas as $c) {
            $fecha = Carbon::parse($c->fecha)->toDateString();
            if (! isset($dias[$fecha])) {
                continue;
            }
            $dia   = &$dias[$fecha];
            $linea = $c->origen === 'linea';
            $hora  = substr((string) $c->hora, 0, 5);

            if ($c->exepcion === 'Si') {
                $dia['excepcion']++;
                $dia['excepcionLinea'] += $linea ? 1 : 0;
            } else {
                $g = HorarioCitas::grupo($c->tipo);
                $dia['total']++;
                $dia[$linea ? 'linea' : 'ventanilla']++;
                $dia['grupos'][$g]['total']++;
                $dia['grupos'][$g][$linea ? 'linea' : 'ventanilla']++;
            }
            if (strtolower((string) $c->estatus) === 'atendido') {
                $dia['atendidos']++;
            }

            if ($conHoras) {
                $dia['horas'][$hora] ??= self::horaVacia($hora);
                $clave = $c->exepcion === 'Si' ? 'excepcion' : ($linea ? 'linea' : 'ventanilla');
                $dia['horas'][$hora][$clave]++;
            }
            unset($dia);
        }

        $resumen = ['total' => 0, 'capacidad' => 0, 'linea' => 0, 'ventanilla' => 0, 'excepcion' => 0, 'llenos' => 0, 'limiteAlcanzado' => 0];
        foreach ($dias as &$dia) {
            if ($conHoras) {
                ksort($dia['horas']);
                $dia['horas'] = array_values($dia['horas']);
            } else {
                unset($dia['horas']);
            }
            $dia['porcentaje'] = $dia['capacidad'] > 0 ? (int) round($dia['total'] / $dia['capacidad'] * 100) : null;
            $dia['limiteAlcanzado'] = $dia['limite'] !== null && $dia['linea'] >= $dia['limite'];

            foreach (['total', 'capacidad', 'linea', 'ventanilla', 'excepcion'] as $k) {
                $resumen[$k] += $dia[$k];
            }
            $resumen['llenos'] += $dia['capacidad'] > 0 && $dia['total'] >= $dia['capacidad'] ? 1 : 0;
            $resumen['limiteAlcanzado'] += $dia['limiteAlcanzado'] ? 1 : 0;
        }
        unset($dia);
        $resumen['porcentaje'] = $resumen['capacidad'] > 0 ? (int) round($resumen['total'] / $resumen['capacidad'] * 100) : null;

        return ['dias' => $dias, 'resumen' => $resumen];
    }

    private static function diaVacio(Carbon $d): array
    {
        $grupo = fn () => ['capacidad' => 0, 'limite' => null, 'total' => 0, 'linea' => 0, 'ventanilla' => 0];

        return [
            'fecha'          => $d->toDateString(),
            'habil'          => ! $d->isWeekend(),
            'inhabil'        => null,
            'motivoInhabil'  => null,
            'capacidad'      => 0,
            'limite'         => null,
            'total'          => 0,
            'linea'          => 0,
            'ventanilla'     => 0,
            'excepcion'      => 0,
            'excepcionLinea' => 0,
            'atendidos'      => 0,
            'grupos'         => array_map(fn () => $grupo(), self::GRUPOS),
            'horas'          => [],
        ];
    }

    private static function horaVacia(string $hora): array
    {
        return ['hora' => $hora, 'capacidad' => 0, 'linea' => 0, 'ventanilla' => 0, 'excepcion' => 0];
    }

    /** Motivo si ese horario cae en un día (o rango de horas) inhábil de la sede. */
    private static function inhabil($inhabiles, string $fecha, string $hora): ?string
    {
        $momento = $fecha.'T'.HorarioCitas::normalizarHora($hora);
        foreach ($inhabiles as $i) {
            $inicio = substr((string) $i->fecha_inicio, 0, 10).'T'.($i->horario_inicio ?? '00:00:00');
            $fin    = substr((string) $i->fecha_final, 0, 10).'T'.($i->horario_final ?? '23:59:59');
            if ($momento >= $inicio && $momento <= $fin) {
                return trim((string) $i->descripcion) ?: 'Inhábil';
            }
        }

        return null;
    }

    private static function mismaSede(?string $a, ?string $b): bool
    {
        return Str::lower(Str::ascii((string) $a)) === Str::lower(Str::ascii((string) $b));
    }

    private static function variantes(array $sedes): array
    {
        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }
}
