<?php

namespace App\Models\Sinotifico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parada extends Model
{
    protected $table = 'sinotifico_ruta_paradas';

    protected $guarded = ['id'];

    protected $casts = [
        'citados' => 'array',
        'resumen' => 'array',
        'llegada_at' => 'datetime',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
