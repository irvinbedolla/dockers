<?php

namespace App\Http\Middleware;

use App\Support\TextoSeguro;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Además de quitar espacios, corrige lo pegado desde PDF/Word/web (ver
     * TextoSeguro) para que se guarde bien. No toca las contraseñas.
     */
    protected function transform($key, $value)
    {
        $value = parent::transform($key, $value);

        if (! is_string($value) || $this->shouldSkip($key, array_merge($this->except, static::$neverTrim))) {
            return $value;
        }

        return TextoSeguro::limpiar($value);
    }
}
