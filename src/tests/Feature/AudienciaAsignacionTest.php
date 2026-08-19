<?php 

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AudienciaAsignacionTest extends TestCase
{
    /** @test */
    public function valida_horarios_y_sedes_de_apoyo()
    {
        // 1. Tomamos un usuario existente para autenticar la prueba
        $user = User::first();
        if (!$user) {
            $this->fail('No hay usuarios registrados en la base de datos.');
        }
        $this->actingAs($user);

        // Instanciamos el controlador (Ajusta la ruta según tu namespace)
        $controller = app(\App\Http\Controllers\SeerController::class);

        // 2. Probar sede de apoyo Zitácuaro
        $respuestaZitacuaro = $controller->ObtenerAudiencia('Zitácuaro', 'Trabajador');

        // Si devuelve un JsonResponse significa que cayó en un return response()->json(..., 404)
        if ($respuestaZitacuaro instanceof JsonResponse) {
            $data = $respuestaZitacuaro->getData(true);
            $this->fail("ObtenerAudiencia devolvió error 404: " . ($data['error'] ?? 'Sin detalle'));
        }

        // 3. Si pasa la validación, confirmamos que sea el array con los datos asignados
        $this->assertIsArray($respuestaZitacuaro);

        $fechaAsignada = $respuestaZitacuaro[0];
        $horaAsignada  = $respuestaZitacuaro[1];

        // 4. Validar el esquema de horario según la fecha obtenida
        if ($fechaAsignada > '2026-08-07') {
            $this->assertContains($horaAsignada, ["09:00:00", "10:15:00", "12:00:00", "14:15:00", "15:30:00"]);
        } else {
            $this->assertContains($horaAsignada, ["09:00:00", "10:15:00", "11:30:00", "12:45:00", "14:00:00"]);
        }
    }
}