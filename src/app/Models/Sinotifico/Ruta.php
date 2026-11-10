<?php

namespace App\Models\Sinotifico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Ruta de un notificador para un día. La app sólo la muestra aprobada. */
class Ruta extends Model
{
    public const BORRADOR = 'borrador';
    public const APROBADA = 'aprobada';

    protected $table = 'sinotifico_rutas';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'date',
        'aprobada_at' => 'datetime',
        'distancia_km' => 'float',
    ];

    public function paradas(): HasMany
    {
        return $this->hasMany(Parada::class, 'ruta_id')->orderBy('orden');
    }
}
