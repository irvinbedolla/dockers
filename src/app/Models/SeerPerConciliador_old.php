<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerPerConciliador_old extends Model
{
    //use HasFactory;
    protected $table = 'seer_conciliadores_old';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','numero_audiencia', 'estatus_conciliacion','numero_audiencias', 'monto', 
    'rfc','NSS','multa','monto_multa','tipo','motivo_archivo','fecha_reprogracion','fecha_conclucion'];
}
