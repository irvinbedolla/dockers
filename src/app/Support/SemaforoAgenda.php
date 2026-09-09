<?php

namespace App\Support;

/**
 * Colores del semáforo de la agenda.
 *
 * Antes cada endpoint traía su propia escalera de if/elseif con los hex
 * escritos a mano —seis bloques repartidos entre CitaController y
 * AudienciasController, ya desincronizados entre sí: la misma "Pendiente"
 * salía #d4ad00 en audiencias y #EAE300 en cumplimientos—. Aquí viven una
 * sola vez, por tipo de agenda, y la leyenda de la vista se pinta de la
 * misma fuente para que no vuelvan a separarse.
 *
 * Los estatus que no aparecen en el catálogo caen en GRIS (en proceso):
 * es preferible un evento gris a uno invisible o de color arbitrario.
 */
class SemaforoAgenda
{
    public const GRIS    = '#9AA4B2'; // en proceso / pendiente
    public const VERDE   = '#1F9D55'; // conciliación / cumplimiento / confirmada
    public const ROJO    = '#D92D20'; // no conciliación / incumplimiento
    public const AMARILLO= '#E3A008'; // reagenda
    public const PLOMO   = '#4B5563'; // archivado (no viene solicitante)
    public const NARANJA = '#F97316'; // incomparecencia (no viene citado)
    public const GUINDA  = '#7B1E3A'; // incompetencia (conciliación federal)

    /** pago_solicitud.estatus */
    private const CUMPLIMIENTOS = [
        'pendiente'                    => self::GRIS,
        'pagado'                       => self::VERDE,
        'pagado con pena convencional' => self::VERDE,
        'no pagado'                    => self::ROJO,
        'incomparecencia trabajador'   => self::ROJO,
    ];

    /** audiencias.estatus */
    private const AUDIENCIAS = [
        'pendiente'                   => self::GRIS,
        'conciliacion'                => self::VERDE,
        'no conciliacion'             => self::ROJO,
        'reagendada'                  => self::AMARILLO,
        'no conciliacion reagendada'  => self::AMARILLO,
        'archivada'                   => self::PLOMO,
        'archivada en audiencia'      => self::PLOMO,
        'incomparecencia'             => self::NARANJA,
        'incompetencia'               => self::GUINDA,
    ];

    /** turnos.estatus */
    private const RATIFICACIONES = [
        'prevencion'       => self::GRIS,
        'pendiente'        => self::GRIS,
        'aceptado'         => self::GRIS,
        'confirmado'       => self::GRIS,
        'atendido'         => self::GRIS,
        'concluida'        => self::VERDE,
        'concluida pagos'  => self::VERDE,
        'incumplimiento'   => self::ROJO,
        'no atendido'      => self::ROJO,
        'archivada'        => self::PLOMO,
    ];

    /**
     * seer_general.estatus
     *
     * El catálogo pedido para Solicitudes sólo nombra tres colores
     * (pendiente / confirmada / no conciliación), pero el enum trae quince
     * valores: una solicitud ya concluida o archivada saldría gris "pendiente
     * de confirmar", que es justo lo contrario de lo que pasó. Se completa
     * con el mismo criterio de Audiencias, que comparte vocabulario.
     */
    private const SOLICITUDES = [
        'pendiente'       => self::GRIS,
        'prevencion'      => self::GRIS,
        'aceptado'        => self::GRIS,
        'confirmado'      => self::VERDE,
        'conciliacion'    => self::VERDE,
        'concluida'       => self::VERDE,
        'no conciliacion' => self::ROJO,
        'rechazado'       => self::ROJO,
        'incumplimiento'  => self::ROJO,
        'reagendada'      => self::AMARILLO,
        'archivada'       => self::PLOMO,
        'incomparecencia' => self::NARANJA,
        'incompetencia'   => self::GUINDA,
    ];

    public static function cumplimiento(?string $estatus): string
    {
        return self::resolver(self::CUMPLIMIENTOS, $estatus);
    }

    public static function audiencia(?string $estatus): string
    {
        return self::resolver(self::AUDIENCIAS, $estatus);
    }

    public static function ratificacion(?string $estatus): string
    {
        return self::resolver(self::RATIFICACIONES, $estatus);
    }

    public static function solicitud(?string $estatus): string
    {
        return self::resolver(self::SOLICITUDES, $estatus);
    }

    /**
     * Leyenda que pinta la vista, por tipo de agenda. La clave es el
     * data-tipo de la pastilla; así la leyenda cambia con lo que se ve.
     *
     * @return array<string, array<int, array{color: string, texto: string}>>
     */
    public static function leyenda(): array
    {
        return [
            // "Todos" mezcla las cuatro agendas, asi que la leyenda va en
            // terminos genericos: los mismos colores, sin casarlos con el
            // vocabulario de una sola.
            'todos' => [
                ['color' => self::GRIS,    'texto' => 'En proceso'],
                ['color' => self::VERDE,   'texto' => 'Conciliacion / cumplimiento'],
                ['color' => self::ROJO,    'texto' => 'No conciliacion / incumplimiento'],
                ['color' => self::AMARILLO,'texto' => 'Reagenda'],
                ['color' => self::PLOMO,   'texto' => 'Archivado'],
                ['color' => self::NARANJA, 'texto' => 'Incomparecencia'],
                ['color' => self::GUINDA,  'texto' => 'Incompetencia'],
            ],
            'solicitudes' => [
                ['color' => self::GRIS,    'texto' => 'Pendiente de confirmar'],
                ['color' => self::VERDE,   'texto' => 'Confirmada'],
                ['color' => self::ROJO,    'texto' => 'No conciliación'],
                ['color' => self::AMARILLO,'texto' => 'Reagenda'],
                ['color' => self::PLOMO,   'texto' => 'Archivada'],
                ['color' => self::NARANJA, 'texto' => 'Incomparecencia'],
                ['color' => self::GUINDA,  'texto' => 'Incompetencia'],
            ],
            'audiencias' => [
                ['color' => self::GRIS,    'texto' => 'En proceso'],
                ['color' => self::VERDE,   'texto' => 'Conciliación'],
                ['color' => self::ROJO,    'texto' => 'No conciliación'],
                ['color' => self::AMARILLO,'texto' => 'Reagenda'],
                ['color' => self::PLOMO,   'texto' => 'Archivado'],
                ['color' => self::NARANJA, 'texto' => 'Incomparecencia'],
                ['color' => self::GUINDA,  'texto' => 'Incompetencia'],
            ],
            'cumplimientos' => [
                ['color' => self::GRIS,  'texto' => 'En proceso'],
                ['color' => self::VERDE, 'texto' => 'Cumplimiento'],
                ['color' => self::ROJO,  'texto' => 'Incumplimiento'],
            ],
            'ratificaciones' => [
                ['color' => self::GRIS,  'texto' => 'En proceso'],
                ['color' => self::VERDE, 'texto' => 'Conciliación'],
                ['color' => self::ROJO,  'texto' => 'No conciliación'],
            ],
        ];
    }

    /**
     * La comparación va normalizada porque los enum del esquema mezclan
     * mayúsculas ('Pendiente', 'atendido', 'no atendido') y en la base hay
     * valores con espacios de sobra.
     *
     * @param array<string, string> $mapa
     */
    private static function resolver(array $mapa, ?string $estatus): string
    {
        $clave = mb_strtolower(trim((string) $estatus));

        return $mapa[$clave] ?? self::GRIS;
    }
}
