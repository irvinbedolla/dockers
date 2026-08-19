<?php

namespace App\Http\Controllers;

use App\Models\Convenio; // o Notificacion / Solicitud
use Illuminate\Http\Request;

class ValidacionDocumentoController extends Controller
{
    public function validar($uuid)
    {
        // Buscar el documento por su identificador único
        $documento = Convenio::with(['solicitud', 'partes'])
            ->where('uuid_validacion', $uuid)
            ->first();

        // Si no existe, el documento es apócrifo o no fue emitido por el sistema
        if (!$documento) {
            return view('validacion.falsificado_o_invalido');
        }

        // Si existe, mostrar la ficha técnica de autenticidad
        return view('validacion.resultado_autentico', compact('documento'));
    }
}