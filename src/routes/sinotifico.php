<?php

use App\Http\Controllers\Api\Sinotifico\AuthController;
use App\Http\Controllers\Api\Sinotifico\DiligenciaController;
use App\Http\Middleware\Sinotifico\AccesoSinotifico;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de SíNotifico — /api/v1/sinotifico/...
|--------------------------------------------------------------------------
| Autenticación por token Bearer (Sanctum). La app no usa cookies ni sesión,
| así que estas rutas no necesitan CSRF y funcionan desde otro dominio.
| Los errores salen en JSON (ver shouldRenderJsonWhen en Exceptions/Handler).
*/

// Sin autenticación: la app lo usa para saber si hay conexión con el servidor.
Route::get('/estado', [AuthController::class, 'estado'])->name('estado');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:sinotifico-login')
    ->name('auth.login');

Route::middleware(['auth:sanctum', AccesoSinotifico::class])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Lista de trabajo del notificador (visitas agrupadas por domicilio).
    Route::get('/diligencias', [DiligenciaController::class, 'index'])->name('diligencias.index');
    Route::get('/diligencias/{id}', [DiligenciaController::class, 'show'])
        ->whereNumber('id')
        ->name('diligencias.show');
});
