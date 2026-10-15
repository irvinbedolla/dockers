<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Accesos rápidos del Inicio para las cuentas de recepción (Morelia 01,
 * Morelia 02 y General), dentro de la misma tarjeta del saludo:
 *
 *   1. Crear un turno, ya con el trámite elegido;
 *   2. Turnos de hoy, desglosados por trámite;
 *   3. Citas en línea de hoy;
 *   4. Su pantalla de sala de espera.
 *
 * Qué trámites ve cada una:
 *   - Morelia 01: solicitudes y asesorías;
 *   - Morelia 02: recibe a quien llega a audiencias, ratificaciones y
 *     cumplimientos (audiencias y cumplimientos salen de su agenda, no de
 *     recepcion: no se crean como turno);
 *   - General: todo lo de su sede (atiende sola las sedes regionales).
 *
 * Las citas de prueba del flujo nuevo (FlujoCita) no se cuentan.
 */
class AccesosRecepcion
{
    /** Trámites de turno (recepcion.tipo) por rol. */
    private const TRAMITES = [
        Recepcion::MORELIA_01 => ['Solicitud', 'Asesoría'],
        Recepcion::MORELIA_02 => ['Ratificación', 'Cumplimiento'],
        Recepcion::REGIONAL   => ['Solicitud', 'Asesoría', 'Ratificación', 'Cumplimiento'],
    ];

    /** Además de los turnos, Morelia 02 recibe las audiencias y cumplimientos de su agenda. */
    private const AGENDA = [Recepcion::MORELIA_02];

    private const PLURAL = [
        'Solicitud'    => 'Solicitudes',
        'Asesoría'     => 'Asesorías',
        'Ratificación' => 'Ratificaciones',
        'Cumplimiento' => 'Cumplimientos',
    ];

    /** ¿Se le muestran los accesos en lugar de la tabla de posiciones? */
    public static function aplica(?User $usuario): bool
    {
        return Recepcion::es($usuario) && ! $usuario->hasAnyRole(Recepcion::SUPERVISAN);
    }

    public static function para(User $usuario): array
    {
        $roles   = $usuario->getRoleNames()->intersect(Recepcion::ROLES)->values()->all();
        $sedes   = Recepcion::sedes($usuario);
        $hoy     = now()->toDateString();
        $agenda  = (bool) array_intersect($roles, self::AGENDA);

        $tramites = [];
        foreach ($roles as $rol) {
            $tramites = array_merge($tramites, self::TRAMITES[$rol] ?? []);
        }
        // En el orden de TRAMITES de la General, sin repetidos.
        $tramites = array_values(array_intersect(self::TRAMITES[Recepcion::REGIONAL], $tramites));

        $hoyTurnos = DB::table('recepcion')
            ->where('fecha', $hoy)
            ->whereIn('delegacion', $sedes ?: ['__ninguna__'])
            ->whereIn('tipo', $tramites ?: ['__ninguno__'])
            ->when(FlujoCita::migrado(), fn ($q) => $q->where('es_prueba', 0));

        $porTramite = (clone $hoyTurnos)
            ->selectRaw('tipo, COUNT(*) AS total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $desglose = [];
        foreach ($tramites as $tramite) {
            // Los turnos de cumplimiento casi no se usan: sólo salen si hay alguno
            // (Morelia 02 además ve los cumplimientos de su agenda, abajo).
            if ($tramite === 'Cumplimiento' && ! ($porTramite[$tramite] ?? 0)) {
                continue;
            }
            $desglose[] = [
                'etiqueta' => self::PLURAL[$tramite],
                'total'    => (int) ($porTramite[$tramite] ?? 0),
                'url'      => route('turnos.todos', ['fecha' => $hoy, 'tipo' => $tramite]),
            ];
        }

        if ($agenda) {
            $variantes = array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));

            array_unshift($desglose, [
                'etiqueta' => 'Audiencias',
                'total'    => DB::table('audiencias')->where('fecha', $hoy)->whereIn('delegacion', $variantes)
                    ->whereNotIn('estatus', AgendaAudiencias::LIBERAN)->count(),
                'url'      => null,
            ]);
            $desglose[] = [
                'etiqueta' => 'Cumplimientos',
                'total'    => DB::table('pago_solicitud')->where('fecha', $hoy)->whereIn('delegacion', $variantes)->count(),
                'url'      => null,
            ];
        }

        $enLinea = (clone $hoyTurnos)->where('origen', 'linea');

        return [
            'crear' => array_map(fn ($t) => [
                'etiqueta' => $t,
                'url'      => route('nueva_cita', ['tipo' => $t]),
            ], Recepcion::tramitesQuePuedeCrear($usuario)),
            'hoy' => [
                'total'    => array_sum(array_column($desglose, 'total')),
                'desglose' => $desglose,
                'url'      => route('turnos.todos', ['fecha' => $hoy]),
            ],
            'enLinea' => [
                'total'     => (clone $enLinea)->count(),
                'porLlegar' => (clone $enLinea)->where('estatus', 'pendiente')->count(),
                'enSala'    => (clone $enLinea)->where('estatus', 'confirmada')->count(),
                'url'       => route('turnos.todos', ['fecha' => $hoy, 'origen' => 'linea']),
            ],
            'pantallas' => collect($sedes)
                ->mapWithKeys(fn ($s) => [$s => PantallaEspera::slug($s)])
                ->filter()
                ->map(fn ($slug) => route('pantalla.espera', $slug))
                ->all(),
        ];
    }
}
