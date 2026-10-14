<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Ajustes del sistema que se cambian desde la pantalla, sin desplegar:
 * un renglón por clave. Se leen con caché; guardar() la limpia.
 */
class Ajuste extends Model
{
    protected $table = 'ajustes';
    protected $fillable = ['clave', 'valor', 'updated_by'];

    private const CACHE = 'ajustes.todos';

    public static function valor(string $clave, $porOmision = null)
    {
        $todos = Cache::remember(self::CACHE, 300, function () {
            try {
                return static::pluck('valor', 'clave')->all();
            } catch (\Throwable $e) {
                // Antes de correr la migración la tabla no existe: todo apagado.
                return [];
            }
        });

        return array_key_exists($clave, $todos) ? $todos[$clave] : $porOmision;
    }

    public static function guardar(string $clave, ?string $valor, ?int $por = null): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor, 'updated_by' => $por]);
        Cache::forget(self::CACHE);
    }
}
