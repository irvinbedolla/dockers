<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\SeerController;
use App\Http\Controllers\TurnosController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\EstadisticaController;
use App\Http\Middleware\VerifyApiKey;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Rutas de auxiliar agregar municipio
Route::get('/munSolicitante/{id}',  [SeerController::class, 'obtenerMunicipio']);
Route::get('/munCitado/{id}',       [SeerController::class, 'obtenerMunicipio']);
//Ruta de auxiliar ver los citados
Route::get('/citados/{id}',         [SeerController::class, 'obtenerCitados']);
//Ruta  de citas para ver el numero de citas por dia
Route::get('/obtenerHorario/{id}/{sede}',  [TurnosController::class, 'obtenerHorario']);

//Ruta para obtener eventos para el calendario de creación de citas
/*Route::prefix('nuevo_siconcilio')->group(function () {
    Route::get('/obtenerEventos', [TurnosController::class, 'obtenerEventos']);
});*/

Route::get('/obtenerEventos',       [TurnosController::class, 'obtenerEventos']);
Route::get('/obtenerCumplimientos', [SeerController::class, 'obtenerCumplimientos']);
Route::get('/obtenerCumplimientosFiltrado', [SeerController::class, 'obtenerCumplimientosFiltrado']);
Route::get('/obtenerAudiencias',    [SeerController::class, 'obtenerAudiencias']);
Route::get('/audiencias-por-solicitud/{id_solicitud}', [SeerController::class, 'audienciasPorSolicitud']);
Route::get('/obtenerAudienciasParte2', [SeerController::class, 'obtenerAudienciasParte2']);
Route::get('/obtenerAudienciasParte3', [SeerController::class, 'obtenerAudienciasParte3']);
Route::get('/dias-inhabiles-centro',[SeerController::class, 'diasInhabilesCentro']);
Route::get('/obtenerTurnosDisponibles', [RecepcionController::class, 'obtenerTurnosDisponibles']);
Route::get('/obtenerAudienciasConciliador', [AdministracionController::class, 'obtenerAudienciasConciliador']);
Route::get('/obtenerCumplimientosAdmin', [AdministracionController::class, 'obtenerCumplimientosAdmin']);
Route::middleware('api.key')->group(function () {
    Route::get('/estadisticas/inicio', [EstadisticaController::class, 'inicio']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    // Las rutas /users, /roles y /permissions venían del template de Metronic
    // y estaban expuestas sin autenticación (permitían crear, borrar y
    // asignar roles a cualquier usuario). Nada del sistema las usaba.

    //Rutas de auxiliar agregar municipio
    Route::get('/munSolicitante/{id}',  [SeerController::class, 'obtenerMunicipio']);
    Route::get('/munCitado/{id}',       [SeerController::class, 'obtenerMunicipio']);
    //Ruta de auxiliar ver los citados
    Route::get('/citados/{id}',         [SeerController::class, 'obtenerCitados']);
    //Ruta  de citas para ver el numero de citas por dia
    Route::get('/obtenerHorario/{id}/{sede}',  [TurnosController::class, 'obtenerHorario']);

    //Rutas solicitud en línea trabajadores
    Route::get('/actividadEconomica/{id}',  [SeerController::class, 'obtenerActEconomica']);

    //Ruta pública para consultar un expediente por número de guía
    Route::get('/expedientes/{numero_guia}', [SeerController::class, 'consultarPorGuia'])
        ->middleware('throttle:30,1')
        ->where('numero_guia', '[0-9]{10}');

});

/*
|--------------------------------------------------------------------------
| SíNotifico
|--------------------------------------------------------------------------
| API de la aplicación de notificadores. Vive en su propio archivo para no
| mezclarse con las rutas internas de SICONCILIO.
*/
Route::prefix('v1/sinotifico')
    ->name('sinotifico.')
    ->group(base_path('routes/sinotifico.php'));
