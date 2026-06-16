<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Routing\Controller as BaseController;
/*
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SeerChatP; 
use App\Models\SeerChatR; 
use App\Models\SeerChatRP;
*/

//use Carbon\Carbon;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\PDFController;
use Spatie\Permission\Models\Role; 
use App\Models\User;
use App\Models\Asistencia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AsistenciaController extends Controller
{
    public function AsistenciaCrear($id)
    {   
        // 1. Buscamos al usuario (si no existe, lanza un error 404)
        $usuario = User::findOrFail($id);

        if($usuario == null){
            return "Codigo QR no Valido";
        }

        $delegacion = $usuario->delegacion;
        $ipCliente = request()->ip();
        if($delegacion == "Morelia"){
            $ipOficina = '193.186.4.242';
        }
        /*
        if ($ipCliente !== $ipOficina) {
            return "Acceso denegado: Solo puedes registrar asistencia conectado al Wi-Fi oficial.";
        }
        */
        // 2. Usamos Carbon para fechas (más limpio en Laravel)
        $hoy = Carbon::today()->format('Y-m-d');
        $ahora = Carbon::now()->format('H:i:s');

        // 3. Validación opcional: Evitar duplicados el mismo día
        $yaRegistro = Asistencia::where('user_id', $id)
                                ->where('fecha', $hoy)
                                ->exists();

        if ($yaRegistro) {
            return view('asistencia', [
                'status' => 'warning',
                'titulo' => 'Ya registrado',
                'mensaje' => "{$usuario->name}, ya habías registrado tu asistencia hoy."
            ]);
        }

        // 4. Creamos el registro
        Asistencia::create([
            'user_id' => $id,
            'fecha'   => $hoy,
            'hora'    => $ahora
        ]);

        // 5. Retornamos la vista de éxito
        return view('asistencia', [
            'status' => 'success',
            'titulo' => '¡Registro Exitoso!',
            'mensaje' => "Bienvenido(a), {$usuario->name}. Tu asistencia ha sido guardada."
        ]);
    }


    public function generarQrUsuario($id)
    {
        // Generamos la URL completa que debe leer la tablet
        $url = "https://siconcilio.cclmichoacan.gob.mx/asistencia/".$id;

        // Creamos el QR (en formato SVG por defecto)
        $codigoQR = QrCode::size(300)
                    //->edgeColor(0, 0, 0)
                    ->generate($url);

        return view('generaQR', compact('codigoQR'));
    }
}