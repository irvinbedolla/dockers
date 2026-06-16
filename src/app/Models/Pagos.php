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