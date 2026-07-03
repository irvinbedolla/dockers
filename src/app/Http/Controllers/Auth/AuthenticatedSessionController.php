<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        addJavascriptFile('../public/assets/js/custom/authentication/sign-in/general.js');

        return view('pages/auth.login');
    }

    public function store(LoginRequest $request)
    {
        // 1. Autenticar las credenciales del usuario
        $request->authenticate();
        // 2. Regenerar la sesión
        $request->session()->regenerate();

        // 3. Limpiar cualquier proceso de solicitud auxiliar
        $request->session()->forget([
            'solicitud_aux_lock',
            'solicitud_draft_id',
        ]);

        // 4. Actualizar la información de inicio de sesión del usuario
        $request->user()->update([
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => $request->getClientIp()
        ]);
        // 5. CORRECCIÓN CRUCIAL: Forzar la redirección al HOME
        // En hostings compartidos, intended() a veces se queda atrapado enviando al '/' (login) en bucle.
        //return redirect()->intended(RouteServiceProvider::HOME);
        return redirect()->to(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request)
    {

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
