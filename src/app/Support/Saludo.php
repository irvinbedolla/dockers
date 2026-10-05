<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * El saludo del Inicio: "Buenas tardes, Conciliadora".
 *
 * Dos piezas, y las dos pueden faltar sin que el saludo se rompa.
 *
 * La hora sale de Carbon, que corre en la zona de config/app.php
 * -America/Mexico_City-. Es lo que hace que esto funcione: la base guarda en
 * UTC, y leer la hora de ahí daría "buenas noches" a media mañana.
 *
 * El tratamiento sólo se usa cuando se puede escribir bien, y eso pide dos
 * condiciones:
 *
 *   - que el rol sea un tratamiento de persona. "Conciliadora" lo es;
 *     "Turnos", "Tercer Encuentro" o "Registro" son nombres de módulo y
 *     saludar con ellos suena a etiqueta de sistema.
 *   - que se sepa cómo escribirlo. La mitad de las cuentas no tiene
 *     users.sexo capturado, y decirle "Conciliador" a una conciliadora cada
 *     vez que entra es peor que no decirle nada. Los roles invariables
 *     -Auxiliar- se salvan de esta condición porque se escriben igual.
 *
 * Si algo falta, el saludo se queda en "Buenas tardes" a secas. El nombre ya
 * va en el renglón de arriba, así que se lee completo igual.
 */
class Saludo
{
    /**
     * Roles que son tratamiento de persona, con su forma segun users.sexo.
     * La llave '*' marca los que no cambian y por eso no necesitan el dato.
     *
     * Los que no estan aqui -Enlace, Super Usuario, Registro, Turnos,
     * Estadistica, Tercer Encuentro, Particular, Cumplimientos,
     * Capacitacion- se saludan sin rol, a proposito.
     */
    private const TRATAMIENTOS = [
        'Conciliador'   => ['H' => 'Conciliador',   'M' => 'Conciliadora'],
        'Notificador'   => ['H' => 'Notificador',   'M' => 'Notificadora'],
        'Delegado'      => ['H' => 'Delegado',      'M' => 'Delegada'],
        'Directivo'     => ['H' => 'Directivo',     'M' => 'Directiva'],
        'Administrador' => ['H' => 'Administrador', 'M' => 'Administradora'],
        'Orientador'    => ['H' => 'Orientador',    'M' => 'Orientadora'],
        'Auxiliar'      => ['*' => 'Auxiliar'],
    ];

    public static function para(User $usuario, ?Carbon $ahora = null): string
    {
        $momento     = self::momento($ahora ?? Carbon::now());
        $tratamiento = self::tratamiento($usuario);

        return $tratamiento === null ? $momento : $momento.', '.$tratamiento;
    }

    /**
     * De 5 a 11 es mañana, de 12 a 18 tarde, y el resto noche -incluida la
     * madrugada, que en español se saluda igual-.
     */
    private static function momento(Carbon $ahora): string
    {
        $hora = (int) $ahora->format('G');

        return match (true) {
            $hora >= 5  && $hora < 12 => 'Buenos días',
            $hora >= 12 && $hora < 19 => 'Buenas tardes',
            default                   => 'Buenas noches',
        };
    }

    private static function tratamiento(User $usuario): ?string
    {
        // roles ya viene cargada desde el controlador, así que esto no cuesta
        // una consulta extra.
        foreach ($usuario->roles->pluck('name') as $rol) {
            $formas = self::TRATAMIENTOS[$rol] ?? null;

            if ($formas === null) {
                continue;
            }

            if (isset($formas['*'])) {
                return $formas['*'];
            }

            $sexo = trim((string) $usuario->sexo);

            if (isset($formas[$sexo])) {
                return $formas[$sexo];
            }
        }

        return null;
    }
}
