<?php

use App\Http\Controllers\Api\Sinotifico\AuthController;
use App\Http\Controllers\Api\Sinotifico\CapturaController;
use App\Http\Controllers\Api\Sinotifico\DiligenciaController;
use App\Http\Controllers\Api\Sinotifico\EvidenciaController;
use App\Http\Controllers\Api\Sinotifico\PresenciaController;
use App\Http\Controllers\Api\Sinotifico\RazonController;
use App\Http\Controllers\Api\Sinotifico\RevisionController;
use App\Http\Controllers\Api\Sinotifico\RutaController;
use App\Http\Controllers\Api\Sinotifico\TableroController;
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
    Route::middleware(AccesoSinotifico::class.':notificar')->group(function () {
        Route::get('/diligencias', [DiligenciaController::class, 'index'])->name('diligencias.index');
        Route::get('/diligencias/{id}', [DiligenciaController::class, 'show'])
            ->whereNumber('id')
            ->name('diligencias.show');

        // Captura en campo (Módulo 2): queda "por revisar" hasta que la valide un supervisor.
        Route::post('/capturas', [CapturaController::class, 'store'])->name('capturas.store');
        Route::get('/capturas', [CapturaController::class, 'index'])->name('capturas.index');

        // Ruta del día (Módulo 4): sólo cuando un supervisor la aprobó.
        Route::get('/ruta', [RutaController::class, 'hoy'])->name('ruta.hoy');

        // Razones de notificación (Módulo 3): las suyas, su firma y firmarlas.
        Route::get('/razones', [RazonController::class, 'index'])->name('razones.index');
        Route::post('/razones/{id}/firmar', [RazonController::class, 'firmar'])->whereNumber('id')->name('razones.firmar');
        Route::get('/firma', [RazonController::class, 'verFirma'])->name('firma.show');
        Route::post('/firma', [RazonController::class, 'guardarFirma'])->name('firma.store');

        // Señal de vida de la app (en línea, capturas sin enviar) para Rutas en SICONCILIO.
        Route::post('/presencia', [PresenciaController::class, 'store'])->middleware('throttle:30,1')->name('presencia');
    });

    // Revisión de lo capturado: Enlaces elegidos, Delegados, Directivos y Super Usuarios.
    Route::middleware(AccesoSinotifico::class.':supervisar')->prefix('revision')->name('revision.')->group(function () {
        Route::get('/', [RevisionController::class, 'index'])->name('index');
        Route::get('/{id}', [RevisionController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/{id}/validar', [RevisionController::class, 'validar'])->whereNumber('id')->name('validar');
        Route::post('/{id}/devolver', [RevisionController::class, 'devolver'])->whereNumber('id')->name('devolver');
        Route::get('/{id}/razones/{citado}', [RazonController::class, 'vistaPrevia'])->whereNumber(['id', 'citado'])->name('razon');
    });

    // Tablero de supervisión (Módulo 5).
    Route::get('/tablero', TableroController::class)->middleware(AccesoSinotifico::class.':supervisar')->name('tablero');

    // PDF de la razón y foto de la firmada en papel: quien la hizo o quien la supervisa.
    Route::get('/razones/{id}/pdf', [RazonController::class, 'pdf'])->whereNumber('id')->name('razones.pdf');
    Route::get('/razones/{id}/papel', [RazonController::class, 'papel'])->whereNumber('id')->name('razones.papel');

    Route::get('/evidencias/{id}', [EvidenciaController::class, 'show'])->whereNumber('id')->name('evidencias.show');
});
