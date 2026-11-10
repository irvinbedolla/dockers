<?php

namespace App\Models\Sinotifico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Fotografía o firma de una diligencia. El archivo vive en el disco local. */
class Evidencia extends Model
{
    protected $table = 'sinotifico_evidencias';

    protected $guarded = ['id'];

    protected $casts = [
        'tomada_at' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function diligencia(): BelongsTo
    {
        return $this->belongsTo(Diligencia::class);
    }
}
