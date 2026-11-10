<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agenda del conciliador en UNA sede: los renglones de permisos_conciliador
 * que tienen sede. Uno por sede que atiende, con su modalidad, sus días, su
 * horario y su sala. Los administra App\Support\SedesConciliador
 * (Administración → Conciliadores).
 *
 * tipo: Precencial (sic, como el enum) o Virtual. A lo más una presencial.
 */
class SedeConciliador extends Model
{
    protected $table = 'permisos_conciliador';

    protected $fillable = ['id_conciliador', 'sede', 'tipo', 'sala', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes',
        'lunes_inicio', 'lunes_final', 'martes_inicio', 'martes_final', 'miercoles_inicio', 'miercoles_final',
        'jueves_inicio', 'jueves_final', 'viernes_inicio', 'viernes_final'];

    protected static function booted(): void
    {
        static::addGlobalScope('por_sede', fn ($consulta) => $consulta->whereNotNull($consulta->getModel()->getTable().'.sede'));
    }

    public function conciliador()
    {
        return $this->belongsTo(User::class, 'id_conciliador');
    }

    public function esPresencial(): bool
    {
        return $this->tipo === 'Precencial';
    }

    /** Días que atiende en esta sede: ['lunes', 'miercoles', …]. */
    public function dias(): array
    {
        return array_values(array_filter(array_keys(PermisosConciliador::DIAS), fn ($d) => $this->{$d} === 'Si'));
    }
}
