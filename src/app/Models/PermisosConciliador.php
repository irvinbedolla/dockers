<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agenda del conciliador: qué tipo de audiencias recibe y en qué días y horas.
 * SeerController::ObtenerAudiencia sólo reparte audiencias a quien tiene el
 * rol Conciliador Y un renglón aquí; sin renglón no le llega ninguna.
 *
 * tipo:
 *   Precencial (sic, así está el enum) → audiencias de su sede principal.
 *   Virtual                            → las de la subsede (Zitácuaro,
 *                                        Lázaro Cárdenas, Sahuayo).
 *   Ambos                              → las dos. También le deja ver los
 *                                        cumplimientos de la subsede.
 */
class PermisosConciliador extends Model
{
    protected $table = 'permisos_conciliador';
    protected $primaryKey = 'id';
    protected $fillable = ['id_conciliador','tipo','lunes','martes','miercoles','jueves','viernes','lunes_inicio','lunes_final','martes_inicio','martes_final','miercoles_inicio','miercoles_final'
    ,'jueves_inicio','jueves_final','viernes_inicio','viernes_final'];

    /** Valor guardado => etiqueta. */
    public const TIPOS = [
        'Precencial' => 'Presencial',
        'Virtual'    => 'Virtual',
        'Ambos'      => 'Ambas',
    ];

    /** Columna => etiqueta corta. */
    public const DIAS = [
        'lunes'     => 'Lun',
        'martes'    => 'Mar',
        'miercoles' => 'Mié',
        'jueves'    => 'Jue',
        'viernes'   => 'Vie',
    ];

    /** Lo que se le pone a un conciliador nuevo (igual que los que ya hay). */
    public const PREDETERMINADO = [
        'tipo'   => 'Precencial',
        'dias'   => ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'],
        'inicio' => '08:00',
        'fin'    => '18:00',
    ];

    /**
     * La agenda en forma simple para el formulario de usuarios: tipo, días
     * que trabaja y un solo horario (el del primer día que trabaja; en la
     * práctica todos tienen el mismo de lunes a viernes).
     */
    public static function resumen(?int $userId): array
    {
        $fila = $userId ? static::where('id_conciliador', $userId)->orderBy('id')->first() : null;

        if (! $fila) {
            return self::PREDETERMINADO + ['existe' => false];
        }

        $dias = array_values(array_filter(array_keys(self::DIAS), fn ($d) => $fila->{$d} === 'Si'));
        $primero = $dias[0] ?? 'lunes';

        return [
            'tipo'   => array_key_exists($fila->tipo, self::TIPOS) ? $fila->tipo : self::PREDETERMINADO['tipo'],
            'dias'   => $dias,
            'inicio' => substr((string) $fila->{$primero.'_inicio'}, 0, 5) ?: self::PREDETERMINADO['inicio'],
            'fin'    => substr((string) $fila->{$primero.'_final'}, 0, 5) ?: self::PREDETERMINADO['fin'],
            'existe' => true,
        ];
    }

    /**
     * Crea o actualiza la agenda. Hay conciliadores con dos renglones
     * repetidos: se actualizan todos para que no se contradigan.
     */
    public static function guardarPara(int $userId, string $tipo, array $dias, string $inicio, string $fin): void
    {
        $valores = ['tipo' => $tipo];
        foreach (array_keys(self::DIAS) as $dia) {
            $valores[$dia]            = in_array($dia, $dias, true) ? 'Si' : 'No';
            $valores[$dia.'_inicio']  = $inicio.':00';
            $valores[$dia.'_final']   = $fin.':00';
        }

        if (static::where('id_conciliador', $userId)->exists()) {
            static::where('id_conciliador', $userId)->update($valores);
        } else {
            static::create($valores + ['id_conciliador' => $userId]);
        }
    }
}
