<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sinotifico\LoginRequest;
use App\Http\Resources\Sinotifico\UsuarioResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Inicio y cierre de sesión de SíNotifico con las mismas cuentas de
 * SICONCILIO. Valida la contraseña igual que el login web (hash de `users`)
 * y entrega un token Bearer con vigencia de una jornada.
 */
class AuthController extends Controller
{
    public function estado(): JsonResponse
    {
        return response()->json([
            'servicio' => 'sinotifico',
            'version' => 1,
            'hora_servidor' => now()->toIso8601String(),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', $request->string('email')->lower()->trim())->first();

        // Mismo mensaje si no existe el correo o si la contraseña no coincide,
        // para no revelar qué cuentas existen.
        if (! $usuario || ! Hash::check($request->input('password'), $usuario->password)) {
            return response()->json([
                'message' => 'El usuario o la contraseña no son correctos.',
                'errors' => ['email' => ['El usuario o la contraseña no son correctos.']],
            ], 422);
        }

        if ($usuario->estatus === 'Inactivo') {
            return response()->json([
                'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.',
            ], 403);
        }

        if (! $usuario->hasAnyRole(config('sinotifico.roles'))) {
            return response()->json([
                'message' => 'Tu cuenta no tiene acceso a SíNotifico. Pide al administrador el rol de Notificador.',
            ], 403);
        }

        $expira = now()->addHours(config('sinotifico.token_horas'));
        $dispositivo = $request->input('dispositivo') ?: 'navegador';

        $token = $usuario->createToken(
            'sinotifico:'.$dispositivo,
            [config('sinotifico.ability')],
            $expira,
        );

        $usuario->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->getClientIp(),
        ])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expira_en' => $expira->toIso8601String(),
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    public function me(Request $request): UsuarioResource
    {
        return new UsuarioResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
