<?php

namespace App\Support;

/**
 * Validación de CURP como la hace RENAPO, sin consultar su servicio:
 *   - formato de 18 caracteres con entidad federativa válida;
 *   - fecha de nacimiento que existe (no 31 de febrero);
 *   - dígito verificador (el último) correcto.
 *
 * La misma regla está en JavaScript en turnos/_campos_flujo para avisar en
 * el formulario antes de enviar.
 */
class Curp
{
    public const REGEX = '/^[A-Z][AEIOUX][A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HMX](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d]\d$/';

    private const DICCIONARIO = '0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

    public static function normalizar(?string $curp): string
    {
        return mb_strtoupper(preg_replace('/\s+/', '', (string) $curp));
    }

    /** null si es válida; si no, el motivo para mostrarlo. */
    public static function error(?string $curp): ?string
    {
        $curp = self::normalizar($curp);

        if (strlen($curp) !== 18) {
            return 'La CURP debe tener 18 caracteres.';
        }
        if (! preg_match(self::REGEX, $curp)) {
            return 'La CURP no tiene un formato válido. Revísala letra por letra.';
        }
        if (! self::fecha($curp)) {
            return 'La fecha de nacimiento de la CURP no existe.';
        }
        if ((int) $curp[17] !== self::digito(substr($curp, 0, 17))) {
            return 'La CURP no es válida (el último dígito no corresponde). Revisa que esté bien escrita.';
        }

        return null;
    }

    public static function valida(?string $curp): bool
    {
        return self::error($curp) === null;
    }

    /** Fecha de nacimiento (Y-m-d) o null. El caracter 17 dice el siglo. */
    public static function fecha(string $curp): ?string
    {
        $aa = (int) substr($curp, 4, 2);
        $mm = (int) substr($curp, 6, 2);
        $dd = (int) substr($curp, 8, 2);
        $anio = (ctype_digit($curp[16]) ? 1900 : 2000) + $aa;

        return checkdate($mm, $dd, $anio) ? sprintf('%04d-%02d-%02d', $anio, $mm, $dd) : null;
    }

    /** 'H' o 'M' (o 'X' si no binario). */
    public static function sexo(string $curp): string
    {
        return $curp[10];
    }

    private static function digito(string $diecisiete): int
    {
        $suma = 0;
        $chars = mb_str_split($diecisiete);
        foreach ($chars as $i => $c) {
            $suma += mb_strpos(self::DICCIONARIO, $c) * (18 - $i);
        }

        return (10 - ($suma % 10)) % 10;
    }
}
