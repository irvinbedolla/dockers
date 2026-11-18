<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un Excel de ratificación múltiple cargado por la empresa (App\Support\RatificacionMultiple).
 */
class RatificacionLote extends Model
{
    protected $table = 'ratificaciones_lotes';

    protected $fillable = [
        'folio', 'delegacion', 'poder_folio', 'empresa', 'domicilio', 'archivo', 'archivo_nombre',
        'correo', 'telefono', 'trabajadores', 'monto_total', 'estatus', 'ip', 'creado_por',
    ];

    protected $casts = ['domicilio' => 'array', 'monto_total' => 'decimal:2'];

    public function renglones(): HasMany
    {
        return $this->hasMany(RatificacionLoteTrabajador::class, 'lote_id')->orderBy('numero');
    }
}
