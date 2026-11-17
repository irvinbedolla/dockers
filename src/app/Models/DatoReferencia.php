<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un dato de referencia del Reporte estadístico (Panel estadístico → Datos de
 * referencia). Qué datos hay y cómo se capturan lo define
 * App\Support\DatosReferencia; aquí sólo se guarda el valor.
 */
class DatoReferencia extends Model
{
    protected $table = 'datos_referencia';

    protected $fillable = ['clave', 'valor', 'updated_by'];

    protected $casts = ['valor' => 'array'];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
