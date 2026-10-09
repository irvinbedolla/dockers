<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un módulo de atención de una sede y la persona que lo atiende.
 * Ver la migración create_modulos_table para el porqué.
 */
class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'delegacion', 'nombre', 'user_id', 'tramites', 'respaldo', 'hora_comida', 'activo', 'orden',
    ];

    protected $casts = [
        'tramites' => 'array',
        'respaldo' => 'array',
        'activo'   => 'boolean',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recibe(string $tramite): bool
    {
        return in_array($tramite, $this->tramites ?? [], true);
    }

    public function recibeDeRespaldo(string $tramite): bool
    {
        return in_array($tramite, $this->respaldo ?? [], true);
    }

    /** ¿Esta hora cae en su media hora de comida? */
    public function enComida(string $hora): bool
    {
        return $this->hora_comida !== null && substr($this->hora_comida, 0, 5) === substr($hora, 0, 5);
    }
}
