<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    //use HasFactory;
    protected $table = 'pago_solicitud';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','fecha','hora','monto','descripcion','observaciones','estatus','tipo_pago','delegacion','NUE','id_conciliador','nombre_trabajador',
    'empresa_representante','tipo_generacion','forma_pago','fecha_audiencia','hora_audiencia', 'incidencia', 'monto_pc', 'user_id', 'fecha_conclucion'];
    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'fecha_audiencia' => 'date:Y-m-d',
        'hora_audiencia'  => 'datetime:H:i:s',
    ];

    /** Estatus con los que un pago programado queda atendido (cumplimiento registrado). */
    public const ATENDIDO = ['Pagado', 'Pagado con pena convencional', 'No pagado', 'Incomparecencia trabajador'];

    /**
     * Quién registró el cumplimiento y cuándo. Antes sólo algunas pantallas
     * lo guardaban (las de ratificación y el pago total, no); ahora se llena
     * siempre que un pago pasa a un estatus de atendido desde el modelo
     * (Pagos::find()->update(...)), sin importar la pantalla.
     *
     * Las actualizaciones masivas (Pagos::where()->update()) no pasan por
     * aquí, a propósito: son los pagos que se cierran solos en un pago total
     * ("Pagado en el cumplimiento #…") y no cuentan como otro cumplimiento.
     * Lo usa la tabla de posiciones del Inicio (App\Support\TablaPosiciones).
     */
    protected static function booted(): void
    {
        static::updating(function (Pagos $pago) {
            if (! $pago->isDirty('estatus') || ! in_array($pago->estatus, self::ATENDIDO, true)) {
                return;
            }

            if (auth()->check()) {
                $pago->user_id = auth()->id();
            }

            if (! $pago->fecha_conclucion || ! $pago->isDirty('fecha_conclucion')) {
                $pago->fecha_conclucion = now()->format('Y-m-d');
            }
        });
    }

    public function turno()
    {
        return $this->hasOne(Turnos::class, 'id', 'id_solicitud');
    }

    public function cumplimiento()
    {
        return $this->hasOne(Pagos::class, 'id', 'id_solicitud');
    }
    
    public function conciliadorUser()
    {
        // 'id_conciliador' es la llave foránea en tu tabla 'pagos'
        // 'id' es la llave primaria en tu tabla 'users'
        return $this->belongsTo(User::class, 'id_conciliador');
    }

    public function pagoturnos()
    {
        return $this->belongsTo(Turnos::class, 'id_solicitud'); 
        // Ajusta 'id_turno' según el nombre real de tu columna
    }

}