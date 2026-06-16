<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosSolicitud extends Model
{
    //use HasFactory;
    protected $table = 'documentos';
    protected $primaryKey = 'id';
    protected $fillable = ['id_solicitud','nombre_documento','tipo_documentos', 'tramite'];

}
