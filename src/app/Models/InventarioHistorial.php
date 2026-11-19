<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Movimiento del historial de un dispositivo del inventario. Ver la migración create_inventario_historial_table. */
class InventarioHistorial extends Model
{
    protected $table = 'inventario_historial';

    public const MOVIMIENTOS = [
        'alta'         => 'Alta',
        'edicion'      => 'Corrección',
        'verificacion' => 'Verificación',
        'revision'     => 'Revisión',
    ];

    protected $fillable = ['dispositivo_id', 'movimiento', 'datos_antes', 'datos_despues', 'nota', 'user_id', 'user_nombre', 'ip'];

    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function dispositivo()
    {
        return $this->belongsTo(InventarioDispositivo::class, 'dispositivo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
