<?php

use App\Actions\SamplePermissionApi;
use App\Actions\SampleRoleApi;
use App\Actions\SampleUserApi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\SeerController;
use App\Http\Controllers\TurnosController;
use App\Http\Controllers\Controller;

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
Route::get('/obtenerAudienciasParte3', [SeerController::class, 'obtenerAudienciasParte3']);
Route::get('/dias-inhabiles-centro',[SeerController::class, 'diasInhabilesCentro']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    Route::get('/users', function (Request $request) {
        return app(SampleUserApi::class)->datatableList($request);
    });

    Route::post('/users-list', function (Request $request) {
        return app(SampleUserApi::class)->datatableList($request);
    });

    Route::post('/users', function (Request $request) {
        return app(SampleUserApi::class)->create($request);
    });

    Route::get('/users/{id}', function ($id) {
        return app(SampleUserApi::class)->get($id);
    });

    Route::put('/users/{id}', function ($id, Request $request) {
        return app(SampleUserApi::class)->update($id, $request);
    });

    Route::delete('/users/{id}', function ($id) {
        return app(SampleUserApi::class)->delete($id);
    });


    Route::get('/roles', function (Request $request) {
        return app(SampleRoleApi::class)->datatableList($request);
    });

    Route::post('/roles-list', function (Request $request) {
        return app(SampleRoleApi::class)->datatableList($request);
    });

    Route::post('/roles', function (Request $request) {
        return app(SampleRoleApi::class)->create($request);
    });

    Route::get('/roles/{id}', function ($id) {
        return app(SampleRoleApi::class)->get($id);
    });

    Route::put('/roles/{id}', function ($id, Request $request) {
        return app(SampleRoleApi::class)->update($id, $request);
    });

    Route::delete('/roles/{id}', function ($id) {
        return app(SampleRoleApi::class)->delete($id);
    });

    Route::post('/roles/{id}/users', function (Request $request, $id) {
        $request->merge(['id' => $id]);
        return app(SampleRoleApi::class)->usersDatatableList($request);
    });

    Route::delete('/roles/{id}/users/{user_id}', function ($id, $user_id) {
        return app(SampleRoleApi::class)->deleteUser($id, $user_id);
    });



    Route::get('/permissions', function (Request $request) {
        return app(SamplePermissionApi::class)->datatableList($request);
    });

    Route::post('/permissions-list', function (Request $request) {
        return app(SamplePermissionApi::class)->datatableList($request);
    });

    Route::post('/permissions', function (Request $request) {
        return app(SamplePermissionApi::class)->create($request);
    });

    Route::get('/permissions/{id}', function ($id) {
        return app(SamplePermissionApi::class)->get($id);
    });

    Route::put('/permissions/{id}', function ($id, Request $request) {
        return app(SamplePermissionApi::class)->update($id, $request);
    });

    Route::delete('/permissions/{id}', function ($id) {
        return app(SamplePermissionApi::class)->delete($id);
    });

    //Rutas de auxiliar agregar municipio
    Route::get('/munSolicitante/{id}',  [SeerController::class, 'obtenerMunicipio']);
    Route::get('/munCitado/{id}',       [SeerController::class, 'obtenerMunicipio']);
    //Ruta de auxiliar ver los citados
    Route::get('/citados/{id}',         [SeerController::class, 'obtenerCitados']);
    //Ruta  de citas para ver el numero de citas por dia
    Route::get('/obtenerHorario/{id}/{sede}',  [TurnosController::class, 'obtenerHorario']);

    //Rutas solicitud en línea trabajadores
    Route::get('/actividadEconomica/{id}',  [SeerController::class, 'obtenerActEconomica']);

});
