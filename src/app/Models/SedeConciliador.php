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
 * vigente_desde / vigente_hasta: desde y hasta cuándo atiende esa sede
 * (coberturas). Vacíos = siempre.
 * exclusiva: mientras esté vigente, sólo quien tenga esa sede exclusiva
 * recibe audiencias nuevas de ahí (los demás que la atienden dejan de recibirlas).
 */
class SedeConciliador extends Model
{
    protected $table = 'permisos_conciliador';

    protected $fillable = ['id_conciliador', 'sede', 'tipo', 'sala', 'vigente_desde', 'vigente_hasta', 'exclusiva', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes',
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

    protected $casts = ['exclusiva' => 'boolean'];

    /** ¿Atiende esta sede ese día (Y-m-d) según su vigencia? */
    public function vigenteEn(string $fecha): bool
    {
        $fecha = substr($fecha, 0, 10);
        $desde = $this->vigente_desde ? substr((string) $this->vigente_desde, 0, 10) : null;
        $hasta = $this->vigente_hasta ? substr((string) $this->vigente_hasta, 0, 10) : null;

        return (! $desde || $desde <= $fecha) && (! $hasta || $hasta >= $fecha);
    }

    /** Días que atiende en esta sede: ['lunes', 'miercoles', …]. */
    public function dias(): array
    {
        return array_values(array_filter(array_keys(PermisosConciliador::DIAS), fn ($d) => $this->{$d} === 'Si'));
    }
}
