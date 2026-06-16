<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

class CorreosController extends Controller
{
    public function correo_prueba()
    {
        // 1. Simulación de datos del usuario
        $user = [
            'name' => 'Juan Pérez',
            'email' => 'irvinsbm@gmail.com'
        ];

        // 2. Envío del correo
        // El método Mail::to() toma el email del destinatario
        Mail::to($user['email'])->send(new WelcomeMail($user));

        return redirect('/')->with('status', '¡Usuario registrado y correo de bienvenida enviado!');
    }
}