<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeerConvenios extends Model
{
    //use HasFactory;
    protected $table = 'seer_convenios';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','fecha','NUE','monto','tipo_pago'];

}
