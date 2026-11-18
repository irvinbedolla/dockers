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
        'solicitante', 'motivo', 'fecha_propuesta', 'trabajadores_declarados', 'observaciones', 'constancia_fiscal', 'declaracion_at',
    ];

    /** Motivo de la ratificación (formato institucional, sección III). */
    public const MOTIVOS = [
        'terminacion' => 'Terminación de la relación laboral',
        'durante'     => 'Convenio durante la relación laboral',
        'otro'        => 'Otro supuesto',
    ];

    protected $casts = [
        'domicilio' => 'array',
        'monto_total' => 'decimal:2',
        'fecha_propuesta' => 'date',
        'declaracion_at' => 'datetime',
    ];

    public function renglones(): HasMany
    {
        return $this->hasMany(RatificacionLoteTrabajador::class, 'lote_id')->orderBy('numero');
    }
}
