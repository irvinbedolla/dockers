<?php

namespace Tests\Feature\Sinotifico;

use App\Models\Sinotifico\Diligencia;
use App\Models\User;
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
 * Módulo 2: captura en campo, revisión y paso a SICONCILIO. Corre dentro de
 * una transacción que se revierte; los archivos van a discos falsos.
 */
class CapturaTest extends TestCase
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

    public function test_la_captura_queda_por_revisar_sin_tocar_seer_citados(): void
    {
        [$empresa, $generico] = $this->visita();
        $this->como($this->notificador);

        $r = $this->enviar($this->captura([$empresa, $generico]), 2, true)->assertCreated();
        $r->assertJsonPath('data.estatus', 'por_revisar')->assertJsonPath('data.citados', [$empresa, $generico]);

        $this->assertSame('Pendiente', DB::table('seer_citados')->where('id', $empresa)->value('estatus'));
        $d = Diligencia::where('uuid', $r->json('data.uuid'))->firstOrFail();
        $this->assertCount(3, $d->evidencias);
        Storage::disk('local')->assertExists($d->evidencias->first()->ruta);

        // Ya no aparece en su lista y cuenta como "en revisión".
        $lista = $this->getJson('/api/v1/sinotifico/diligencias')->assertOk();
        $this->assertNull(collect($lista->json('data'))->firstWhere('id', $empresa));
        $lista->assertJsonPath('resumen.en_revision', 1);
    }

    public function test_reenviar_la_misma_captura_no_la_duplica(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $captura = $this->captura([$empresa]);

        $this->enviar($captura)->assertCreated();
        $this->enviar($captura)->assertOk();

        $this->assertSame(1, Diligencia::where('uuid', $captura['uuid'])->count());
    }

    public function test_valida_los_datos_como_el_formulario_de_siconcilio(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);

        $this->enviar($this->captura([$empresa], ['datos' => ['nombre_notificacion' => '', 'firma' => null, 'medio' => null]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['datos.nombre_notificacion', 'datos.firma', 'datos.medio']);

        $this->enviar($this->captura([$empresa]), 0)->assertStatus(422)->assertJsonValidationErrors(['fotos']);

        $this->enviar($this->captura([$empresa], ['resultado' => 'No exitosa se constituye', 'quien_atiende' => 'NADIE']))
            ->assertStatus(422)->assertJsonValidationErrors(['datos.problema_diligencia']);

        $this->enviar($this->captura([$empresa], ['datos' => ['identificacion_notificacion' => 'NO PROPORCIONA']]))
            ->assertStatus(422)->assertJsonValidationErrors(['datos.motivo_identificacion', 'datos.genero', 'datos.edad_filiacion']);
    }

    public function test_no_acepta_citados_ajenos_ni_dos_capturas_del_mismo_domicilio(): void
    {
        [$empresa] = $this->visita();
        $otro = $this->usuario('Notificador');
        $this->como($otro);
        $this->enviar($this->captura([$empresa]))->assertStatus(422);

        $this->como($this->notificador);
        $this->enviar($this->captura([$empresa]))->assertCreated();
        $this->enviar($this->captura([$empresa]))->assertStatus(409);
    }

    public function test_el_supervisor_de_la_sede_valida_y_pasa_a_siconcilio(): void
    {
        [$empresa, $generico] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa, $generico]), 2)->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $delegado = $this->usuario('Delegado');
        $this->como($delegado);
        $this->getJson('/api/v1/sinotifico/revision')->assertOk()
            ->assertJsonPath('conteo.por_revisar', 1)
            ->assertJsonPath('data.0.id', $id);
        $this->getJson("/api/v1/sinotifico/revision/{$id}")->assertOk()
            ->assertJsonCount(2, 'data.evidencias')
            ->assertJsonPath('data.lista_citados.0.id', $empresa);

        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertOk()->assertJsonPath('data.estatus', 'validada');

        $c = DB::table('seer_citados')->where('id', $generico)->first();
        $this->assertSame('Finalizado exitosamente', $c->estatus);
        $this->assertSame('CITADO O REPRESENTANTE', $c->quien_atiende);
        $this->assertSame('JUAN PÉREZ', $c->nombre_notificacion);
        $this->assertSame('FIRMA Y SELLA', $c->firma);
        $this->assertSame(['NÚMERO VISIBLE', 'RÓTULOS VISIBLES'], json_decode($c->medio, true));
        $this->assertStringStartsWith('2026-10-14 10:15', (string) $c->fecha);
        $this->assertSame("{$generico}-foto1.jpg", $c->documento);
        $this->assertSame('Sin documento', $c->documento2);
        Storage::disk('s3')->assertExists("documentos_notificacion/{$c->id_solicitud}/{$generico}-foto2.jpg");

        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertStatus(409);
    }

    public function test_un_supervisor_de_otra_sede_no_la_ve(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa]))->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $this->como($this->usuario('Delegado', 'Zamora'));
        $this->getJson('/api/v1/sinotifico/revision')->assertOk()->assertJsonPath('conteo.por_revisar', 0);
        $this->getJson("/api/v1/sinotifico/revision/{$id}")->assertNotFound();
        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertNotFound();

        $this->como($this->usuario('Directivo', 'Zamora'));
        $this->getJson('/api/v1/sinotifico/revision')->assertOk()->assertJsonPath('conteo.por_revisar', 1);
    }

    public function test_devuelta_vuelve_a_la_lista_con_el_comentario(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa]))->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $this->como($this->usuario('Delegado'));
        $this->postJson("/api/v1/sinotifico/revision/{$id}/devolver", ['comentario' => ''])->assertStatus(422);
        $this->postJson("/api/v1/sinotifico/revision/{$id}/devolver", ['comentario' => 'La foto no muestra el número exterior.'])
            ->assertOk()->assertJsonPath('data.estatus', 'devuelta');
        $this->assertSame('Pendiente', DB::table('seer_citados')->where('id', $empresa)->value('estatus'));

        $this->como($this->notificador);
        $visita = collect($this->getJson('/api/v1/sinotifico/diligencias')->json('data'))->firstWhere('id', $empresa);
        $this->assertSame('La foto no muestra el número exterior.', $visita['devuelta']['comentario']);
        $this->getJson('/api/v1/sinotifico/capturas')->assertOk()->assertJsonPath('data.0.estatus', 'devuelta');

        // Puede volver a capturarla.
        $this->enviar($this->captura([$empresa]))->assertCreated();
    }

    public function test_exhorto_pasa_al_notificador_de_exhortos_de_la_sede(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa], ['quien_atiende' => 'EXHORTO', 'resultado' => null]), 0)
            ->assertCreated()->json('data.uuid');
        $id = Diligencia::where('uuid', $uuid)->value('id');

        $this->como($this->usuario('Delegado'));
        $this->postJson("/api/v1/sinotifico/revision/{$id}/validar")->assertOk();

        $c = DB::table('seer_citados')->where('id', $empresa)->first();
        $this->assertSame('Exhorto', $c->estatus);
        $this->assertSame('Exhorto', $c->notificacion);
        $this->assertSame(57, (int) $c->id_notificador);
        $this->assertNull($c->nombre_notificacion);
    }

    public function test_solo_supervisores_revisan_y_las_fotos_son_privadas(): void
    {
        [$empresa] = $this->visita();
        $this->como($this->notificador);
        $uuid = $this->enviar($this->captura([$empresa]))->json('data.uuid');
        $foto = Diligencia::where('uuid', $uuid)->firstOrFail()->evidencias->first()->id;

        $this->getJson('/api/v1/sinotifico/revision')->assertForbidden();
        $this->get("/api/v1/sinotifico/evidencias/{$foto}")->assertOk();

        $this->como($this->usuario('Notificador'));
        $this->getJson("/api/v1/sinotifico/evidencias/{$foto}")->assertNotFound();
    }
}
