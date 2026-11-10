<?php

namespace Tests\Feature\Sinotifico;

use App\Models\Sinotifico\Diligencia;
use App\Models\User;
use App\Services\Sinotifico\Razones;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Módulo 3: razones de notificación. El PDF es el de SICONCILIO; aquí se
 * prueba cuál toca, la vista previa sin efectos y la firma del notificador.
 */
class RazonesTest extends TestCase
{
    use DatabaseTransactions;

    private User $notificador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-14 09:00:00');
        Storage::fake('local');
        Storage::fake('s3');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::table('dias_inhabiles')->where('centro', 'Morelia')->delete();
        DB::table('estados')->insertOrIgnore(['id' => 16, 'nombre' => 'Michoacán', 'pais' => 1]);
        DB::table('municipios')->insertOrIgnore(['id' => 16053, 'nombre' => 'Morelia', 'estado' => 16, 'delegacion_id' => 1]);

        $this->notificador = $this->usuario('Notificador');
        Razones::olvidar();
    }

    private function usuario(string $rol, string $sede = 'Morelia'): User
    {
        $u = new User;
        $u->forceFill([
            'name' => "{$rol} de prueba", 'email' => strtolower(str_replace(' ', '', $rol)).'.'.uniqid().'@test.local',
            'password' => Hash::make('x'), 'delegacion' => $sede, 'type' => 'Si concilio', 'estatus' => 'Activo',
        ])->save();
        $u->assignRole(Role::findOrCreate($rol, 'web'));

        return $u;
    }

    private function como(User $u): void
    {
        Sanctum::actingAs($u, [config('sinotifico.ability')]);
    }

    /** Solicitud con empresa + citado genérico en el mismo domicilio. */
    private function visita(): array
    {
        $sol = DB::table('seer_general')->insertGetId([
            'año' => 2026, 'id_rama' => 1, 'actividad' => 'Prueba', 'delegacion' => 'Morelia',
            'tipo' => 'Presencial', 'tipo_generacion' => 1, 'fecha' => '2026-10-01', 'NUE' => 'MOR/SOL/2026/'.random_int(90000, 99999),
        ]);
        $aud = DB::table('audiencias')->insertGetId([
            'id_solicitud' => $sol, 'numero_audiencia' => 1, 'folio_audiencia' => '1/2026', 'fecha' => '2026-10-28',
            'proxima_audiencia' => '2026-10-28', 'hora' => '10:00:00', 'id_conciliador' => 1, 'sala' => '1', 'delegacion' => 'Morelia',
        ]);
        // Las plantillas de la razón muestran al solicitante.
        DB::table('seer_solicitante')->insert([
            'id_solicitud' => $sol, 'curp' => 'XAXX010101HMNXXX01', 'nombre' => 'PERSONA SOLICITANTE', 'sexo' => 'H', 'nacionalidad' => 'Mexicana',
            'estado' => 'Michoacán', 'fecha_nacimiento' => '1990-01-01', 'edad' => 36, 'telefono1' => '4430000000', 'email' => 'demo@test.local',
            'estado_domicilio' => 'Michoacán', 'tipo_vialidad' => 'CALLE', 'calle' => 'UNO', 'num_ext' => '1', 'colonia' => 'CENTRO',
            'municipio_domicilio' => 'Morelia', 'codigo_postal' => '58000', 'puesto' => 'Auxiliar', 'pago' => 0, 'horas_semana' => 40,
            'fecha_ingreso' => '2020-01-01', 'descripcionSolicitud' => 'Prueba',
        ]);
        $base = [
            'id_solicitud' => $sol, 'audiencia_id' => $aud, 'id_notificador' => $this->notificador->id,
            'estatus' => 'Pendiente', 'notificacion' => 'Centro', 'tipo_notificacion' => 'Citatorio', 'tipo_persona' => 'Moral',
            'tipo_vialidad' => 'CALLE', 'calle' => 'RIO LERMA', 'n_ext' => '21', 'colonia' => 'VISTA BELLA', 'cp' => '58090',
            'municipio_citado' => 16053, 'estado_citado' => 16, 'referencia' => 'Junto a la papelería',
        ];

        return [
            DB::table('seer_citados')->insertGetId($base + ['nombre' => 'EMPRESA DE PRUEBA S.A. DE C.V.']),
            DB::table('seer_citados')->insertGetId($base + ['nombre' => 'QUIEN O QUIENES RESULTEN RESPONSABLES DE LA FUENTE DE EMPLEO']),
        ];
    }

    private function captura(array $citados, array $cambios = []): array
    {
        return array_replace_recursive([
            'uuid' => (string) Str::uuid(),
            'citados' => $citados,
            'quien_atiende' => 'CITADO O REPRESENTANTE',
            'resultado' => 'Finalizado exitosamente',
            'llegada_at' => '2026-10-14T10:15:00-06:00',
            'capturada_at' => '2026-10-14T10:32:00-06:00',
            'ubicacion' => ['lat' => 19.7021, 'lng' => -101.1923, 'precision' => 12],
            'dispositivo' => 'phpunit',
            'fotos' => [['lat' => 19.7021, 'lng' => -101.1923, 'tomada_at' => '2026-10-14T10:20:00-06:00']],
            'datos' => [
                'nombre_notificacion' => 'JUAN PÉREZ', 'relacion_notificacion' => 'TRABAJA', 'puesto' => 'Gerente',
                'identificacion_notificacion' => 'CREDENCIAL PARA VOTAR', 'num_identificacion' => '123456',
                'medio' => ['NÚMERO VISIBLE', 'RÓTULOS VISIBLES'], 'vialidad_notificacion' => 'CALLE',
                'abundar_area' => 'Zona comercial', 'abundar_inmueble' => 'Local de dos pisos, portón gris',
                'firma' => 'FIRMA Y SELLA', 'observaciones' => 'Sin novedad',
            ],
        ], $cambios);
    }

    private function enviar(array $captura, int $fotos = 1, bool $firma = false)
    {
        $archivos = [];
        for ($i = 0; $i < $fotos; $i++) {
            $archivos[] = UploadedFile::fake()->image("foto{$i}.jpg", 800, 600);
        }
        $datos = ['captura' => json_encode($captura), 'fotos' => $archivos];
        if ($firma) {
            $datos['firma_imagen'] = UploadedFile::fake()->image('firma.png', 400, 200);
        }

        return $this->post('/api/v1/sinotifico/capturas', $datos, ['Accept' => 'application/json']);
    }

    /** Captura validada por un Delegado de la sede; regresa [diligencia, citados]. */
    private function validada(): array
    {
        $citados = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura($citados), 2)->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');
        $this->como($this->usuario('Delegado'));
        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertOk();

        return [$id, $citados];
    }

    private function firmaPng()
    {
        return UploadedFile::fake()->image('firma.png', 400, 160);
    }

    public function test_validar_deja_una_razon_por_firmar_por_citado_con_su_pdf(): void
    {
        [$id, [$empresa, $generico]] = $this->validada();

        $this->como($this->notificador);
        $r = $this->getJson('/api/v1/sinotifico/razones')->assertOk()
            ->assertJsonCount(2, 'data.razones')
            ->assertJsonPath('data.razones.0.estatus', 'por_firmar')
            ->assertJsonPath('data.razones.0.documento', 'Razón de notificación')
            ->assertJsonPath('permite_papel', true)
            ->assertJsonPath('tiene_firma', false);

        $pdf = $this->get('/api/v1/sinotifico/razones/'.$r->json('data.razones.0.id').'/pdf')->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        // Otro notificador no la ve.
        $this->como($this->usuario('Notificador'));
        $this->get('/api/v1/sinotifico/razones/'.$r->json('data.razones.0.id').'/pdf')->assertNotFound();
        $this->getJson('/api/v1/sinotifico/razones')->assertOk()->assertJsonCount(0, 'data.razones');
    }

    public function test_firmar_en_la_app_estampa_la_firma_en_el_pdf(): void
    {
        $this->validada();
        $this->como($this->notificador);
        $razon = $this->getJson('/api/v1/sinotifico/razones')->json('data.razones.0.id');
        $sinFirma = $this->get("/api/v1/sinotifico/razones/{$razon}/pdf")->getContent();

        $this->postJson("/api/v1/sinotifico/razones/{$razon}/firmar", ['modo' => 'app'])
            ->assertStatus(422)->assertJsonPath('message', 'Primero guarda tu firma en la app.');

        $this->post('/api/v1/sinotifico/firma', ['firma' => $this->firmaPng()], ['Accept' => 'application/json'])->assertOk();
        $this->get('/api/v1/sinotifico/firma')->assertOk();

        $this->postJson("/api/v1/sinotifico/razones/{$razon}/firmar", ['modo' => 'app'])->assertOk()
            ->assertJsonPath('data.estatus', 'firmada')->assertJsonPath('data.modo', 'app');
        $this->postJson("/api/v1/sinotifico/razones/{$razon}/firmar", ['modo' => 'app'])->assertStatus(409);

        $conFirma = $this->get("/api/v1/sinotifico/razones/{$razon}/pdf")->getContent();
        $this->assertSame(substr_count($sinFirma, '/Subtype /Image') + 1, substr_count($conFirma, '/Subtype /Image'));
    }

    public function test_firmar_en_papel_con_foto_mientras_este_permitido(): void
    {
        $this->validada();
        $this->como($this->notificador);
        [$a, $b] = collect($this->getJson('/api/v1/sinotifico/razones')->json('data.razones'))->pluck('id')->all();

        $antes = substr_count($this->get("/api/v1/sinotifico/razones/{$a}/pdf")->getContent(), '/Subtype /Image');
        $this->post("/api/v1/sinotifico/razones/{$a}/firmar", ['modo' => 'papel', 'foto' => UploadedFile::fake()->image('hoja.jpg', 900, 1200)], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.modo', 'papel')->assertJsonPath('data.con_foto', true);
        $this->get("/api/v1/sinotifico/razones/{$a}/papel")->assertOk();

        // El PDF no cambia: la firma va en el papel.
        $this->assertSame($antes, substr_count($this->get("/api/v1/sinotifico/razones/{$a}/pdf")->getContent(), '/Subtype /Image'));

        config(['sinotifico.firma_en_papel' => false]);
        $this->postJson("/api/v1/sinotifico/razones/{$b}/firmar", ['modo' => 'papel'])->assertStatus(422);
        $this->getJson('/api/v1/sinotifico/razones')->assertJsonPath('permite_papel', false);
    }

    public function test_el_supervisor_ve_la_razon_antes_de_validar_sin_que_cambie_nada(): void
    {
        [$empresa, $generico] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa, $generico]), 2)->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $this->como($this->usuario('Delegado'));
        $this->getJson("/api/v1/sinotifico/revision/{$id}")->assertOk()
            ->assertJsonPath('data.lista_citados.0.razon.documento', 'Razón de notificación')
            ->assertJsonPath('data.lista_citados.0.razon.id', null);

        $pdf = $this->get("/api/v1/sinotifico/revision/{$id}/razones/{$empresa}")->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->assertSame('Pendiente', DB::table('seer_citados')->where('id', $empresa)->value('estatus'));
        $this->assertSame([], Storage::disk('s3')->allFiles());
        $this->assertSame(0, DB::table('sinotifico_razones')->count());

        // De otra sede, no.
        $this->como($this->usuario('Delegado', 'Zamora'));
        $this->get("/api/v1/sinotifico/revision/{$id}/razones/{$empresa}")->assertNotFound();
    }

    public function test_las_devueltas_quedan_como_observaciones_hasta_recapturarlas(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa]))->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $this->como($this->usuario('Delegado'));
        $this->postJson("/api/v1/sinotifico/revision/{$id}/devolver", ['comentario' => 'Falta la foto del número exterior.'])->assertOk();

        $this->como($this->notificador);
        $this->getJson('/api/v1/sinotifico/razones')->assertOk()
            ->assertJsonCount(1, 'data.observaciones')
            ->assertJsonPath('data.observaciones.0.comentario', 'Falta la foto del número exterior.')
            ->assertJsonPath('data.observaciones.0.citado_id', $empresa)
            ->assertJsonPath('data.observaciones.0.pendiente', true);

        $this->enviar($this->captura([$empresa]))->assertCreated();
        $this->getJson('/api/v1/sinotifico/razones')->assertJsonCount(0, 'data.observaciones');
    }

    public function test_un_exhorto_no_genera_razon(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa], ['quien_atiende' => 'EXHORTO', 'resultado' => null]), 0)->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');
        $this->como($this->usuario('Delegado'));
        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertOk();

        $this->assertSame(0, DB::table('sinotifico_razones')->count());
    }
}
