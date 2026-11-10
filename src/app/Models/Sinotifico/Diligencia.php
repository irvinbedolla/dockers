<?php

namespace App\Models\Sinotifico;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/** Una visita capturada en campo (ver migración create_sinotifico_diligencias_tables). */
class Diligencia extends Model
{
    public const POR_REVISAR = 'por_revisar';
    public const VALIDADA = 'validada';
    public const DEVUELTA = 'devuelta';

    protected $table = 'sinotifico_diligencias';

    protected $guarded = ['id'];

    protected $casts = [
        'datos' => 'array',
        'llegada_at' => 'datetime',
        'capturada_at' => 'datetime',
        'revisada_at' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function notificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notificador_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class)->orderBy('tipo')->orderBy('orden');
    }

    /** @return list<int> */
    public function citadoIds(): array
    {
        return DB::table('sinotifico_diligencia_citado')
            ->where('diligencia_id', $this->id)
            ->orderBy('citado_id')
            ->pluck('citado_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
