<?php

namespace Tests\Feature\Sinotifico;

use App\Models\Sinotifico\Ruta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request as Peticion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Módulo 4: rutas del día. OpenStreetMap se simula: "RIO LERMA" queda cerca
 * de la oficina de Morelia y "MADERO" lejos; "SIN SALIDA" no se encuentra.
 */
class RutasTest extends TestCase
{
    use DatabaseTransactions;

    private User $notificador;

    private User $delegado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-14 09:00:00');
        Storage::fake('local');
        Storage::fake('s3');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config(['sinotifico.geocodificador' => 'nominatim']);

        DB::table('dias_inhabiles')->where('centro', 'Morelia')->delete();
        DB::table('estados')->insertOrIgnore(['id' => 16, 'nombre' => 'Michoacán', 'pais' => 1]);
        DB::table('estados')->insertOrIgnore(['id' => 19, 'nombre' => 'Nuevo León', 'pais' => 1]);
        DB::table('municipios')->insertOrIgnore(['id' => 16053, 'nombre' => 'Morelia', 'estado' => 16, 'delegacion_id' => 1]);
        DB::table('sinotifico_domicilios')->delete();

        Http::fake(['nominatim.openstreetmap.org/*' => function (Peticion $p) {
            $texto = mb_strtoupper(($p->data()['street'] ?? '').' '.($p->data()['q'] ?? ''));

            return Http::response(match (true) {
                str_contains($texto, 'RIO LERMA') => [['lat' => '19.7050', 'lon' => '-101.1900']],
                str_contains($texto, 'MADERO') => [['lat' => '19.7600', 'lon' => '-101.1000']],
                str_contains($texto, 'HIDALGO') => [['lat' => '19.7200', 'lon' => '-101.1500']],
                default => [],
            });
        }]);

        $this->notificador = $this->usuario('Notificador');
        $this->delegado = $this->usuario('Delegado');
    }

    private function usuario(string $rol, string $sede = 'Morelia'): User
    {
        $u = new User;
        $u->forceFill([
            'name' => "{$rol} rutas", 'email' => 'rutas.'.uniqid().'@test.local', 'password' => Hash::make('x'),
            'delegacion' => $sede, 'type' => 'Si concilio', 'estatus' => 'Activo', 'email_verified_at' => now(),
        ])->save();
        $u->assignRole(Role::findOrCreate($rol, 'web'));

        return $u;
    }

    private function visita(string $calle, array $extra = []): int
    {
        $sol = DB::table('seer_general')->insertGetId([
            'año' => 2026, 'id_rama' => 1, 'actividad' => 'Prueba', 'delegacion' => 'Morelia', 'tipo' => 'Presencial',
            'tipo_generacion' => 1, 'fecha' => '2026-10-01', 'NUE' => 'MOR/SOL/2026/'.random_int(90000, 99999),
        ]);
        $aud = DB::table('audiencias')->insertGetId([
            'id_solicitud' => $sol, 'numero_audiencia' => 1, 'folio_audiencia' => '1/2026', 'fecha' => '2026-10-28',
            'proxima_audiencia' => '2026-10-28', 'hora' => '10:00:00', 'id_conciliador' => 1, 'sala' => '1', 'delegacion' => 'Morelia',
        ]);

        return DB::table('seer_citados')->insertGetId(array_merge([
            'id_solicitud' => $sol, 'audiencia_id' => $aud, 'id_notificador' => $this->notificador->id,
            'estatus' => 'Pendiente', 'notificacion' => 'Centro', 'tipo_notificacion' => 'Citatorio', 'tipo_persona' => 'Moral',
            'nombre' => "EMPRESA {$calle}", 'tipo_vialidad' => 'CALLE', 'calle' => $calle, 'n_ext' => '10', 'colonia' => 'CENTRO',
            'cp' => '58000', 'municipio_citado' => 16053, 'estado_citado' => 16, 'referencia' => 'Ninguna',
        ], $extra));
    }

    private function rutaUrl(string $accion = ''): string
    {
        return "/notificaciones/rutas/{$this->notificador->id}/2026-10-14".($accion ? "/{$accion}" : '');
    }

    private function apiRuta()
    {
        Sanctum::actingAs($this->notificador, [config('sinotifico.ability')]);

        return $this->getJson('/api/v1/sinotifico/ruta?fecha=2026-10-14');
    }

    public function test_propone_la_ruta_por_cercania_y_la_app_solo_la_ve_aprobada(): void
    {
        $lejos = $this->visita('MADERO');
        $cerca = $this->visita('RIO LERMA');
        $sinUbicar = $this->visita('SIN SALIDA');
        $this->visita('PEÑON BLANCO', ['estado_citado' => 19]); // fuera del estado: no entra

        $r = $this->actingAs($this->delegado)->postJson($this->rutaUrl('generar'))->assertOk();
        $r->assertJsonPath('ruta.estatus', 'borrador')
            ->assertJsonPath('ruta.paradas.0.visita_id', $cerca)
            ->assertJsonPath('ruta.paradas.1.visita_id', $lejos)
            ->assertJsonPath('ruta.paradas.2.visita_id', $sinUbicar)
            ->assertJsonPath('ruta.paradas.2.lat', null)
            ->assertJsonPath('ruta.paradas.0.ubicacion', 'osm')
            ->assertJsonCount(3, 'ruta.paradas')
            ->assertJsonCount(1, 'fuera');
        $this->assertGreaterThan(0, $r->json('ruta.distancia_km'));

        $this->apiRuta()->assertOk()->assertJsonPath('data', null)->assertJsonPath('en_preparacion', true);

        $this->actingAs($this->delegado)->postJson($this->rutaUrl('aprobar'))->assertOk()->assertJsonPath('ruta.estatus', 'aprobada');

        $this->apiRuta()->assertOk()
            ->assertJsonPath('data.estatus', 'aprobada')
            ->assertJsonPath('data.paradas.0.visita_id', $cerca)
            ->assertJsonPath('data.oficina.lat', 19.7027)
            ->assertJsonPath('data.paradas.0.plazo.estado', 'en_plazo');
    }

    public function test_editar_una_ruta_aprobada_la_regresa_a_por_aprobar(): void
    {
        $a = $this->visita('RIO LERMA');
        $b = $this->visita('MADERO');
        $c = $this->visita('HIDALGO');
        $this->actingAs($this->delegado)->postJson($this->rutaUrl('generar'))->assertOk();
        $this->actingAs($this->delegado)->postJson($this->rutaUrl('aprobar'))->assertOk();

        // Reordena y quita una parada.
        $this->actingAs($this->delegado)->putJson($this->rutaUrl('paradas'), ['visitas' => [$b, $a]])
            ->assertOk()
            ->assertJsonPath('ruta.estatus', 'borrador')
            ->assertJsonPath('ruta.paradas.0.visita_id', $b)
            ->assertJsonCount(2, 'ruta.paradas')
            ->assertJsonPath('fuera.0.visita_id', $c);
        $this->apiRuta()->assertJsonPath('data', null);

        // Ordenar por cercanía la deja como al principio.
        $this->actingAs($this->delegado)->postJson($this->rutaUrl('ordenar'))->assertOk()->assertJsonPath('ruta.paradas.0.visita_id', $a);

        // Una visita que no es suya no se puede meter.
        $ajena = $this->visita('MADERO', ['id_notificador' => $this->delegado->id]);
        $this->actingAs($this->delegado)->putJson($this->rutaUrl('paradas'), ['visitas' => [$a, $ajena]])->assertStatus(422);
    }

    public function test_mover_el_pin_corrige_el_domicilio_para_siempre(): void
    {
        $this->visita('SIN SALIDA');
        $r = $this->actingAs($this->delegado)->postJson($this->rutaUrl('generar'))->assertOk();
        $domicilio = $r->json('ruta.paradas.0.domicilio_id');

        $this->actingAs($this->delegado)->putJson("/notificaciones/rutas/domicilios/{$domicilio}", ['lat' => 19.71, 'lng' => -101.2])->assertOk();
        $this->actingAs($this->delegado)->putJson("/notificaciones/rutas/domicilios/{$domicilio}", ['lat' => 40.4, 'lng' => -3.7])->assertStatus(422);

        $this->actingAs($this->delegado)->postJson($this->rutaUrl('generar'))->assertOk()
            ->assertJsonPath('ruta.paradas.0.lat', 19.71)
            ->assertJsonPath('ruta.paradas.0.ubicacion', 'manual');
    }

    public function test_solo_quien_supervisa_esa_sede_entra(): void
    {
        $this->visita('RIO LERMA');

        $this->actingAs($this->delegado)->get('/notificaciones/rutas')->assertOk()->assertSee($this->notificador->name);
        $this->actingAs($this->delegado)->get($this->rutaUrl())->assertOk()->assertSee('rtMapa', false);

        $this->actingAs($this->usuario('Delegado', 'Zamora'))->postJson($this->rutaUrl('generar'))->assertNotFound();
        $this->actingAs($this->notificador)->get('/notificaciones/rutas')->assertForbidden();
        $this->actingAs($this->usuario('Enlace'))->get('/notificaciones/rutas')->assertForbidden();
        $this->actingAs($this->usuario('Directivo', 'Zamora'))->get($this->rutaUrl())->assertOk();
    }

    public function test_la_diligencia_validada_confirma_el_domicilio_en_campo_y_la_parada_se_marca(): void
    {
        $id = $this->visita('MADERO');
        $this->actingAs($this->delegado)->postJson($this->rutaUrl('generar'))->assertOk();
        $this->actingAs($this->delegado)->postJson($this->rutaUrl('aprobar'))->assertOk();

        Sanctum::actingAs($this->notificador, [config('sinotifico.ability')]);
        $this->post('/api/v1/sinotifico/capturas', [
            'captura' => json_encode([
                'uuid' => (string) Str::uuid(), 'citados' => [$id], 'quien_atiende' => 'NADIE', 'resultado' => 'No notificada',
                'llegada_at' => '2026-10-14T10:00:00-06:00', 'capturada_at' => '2026-10-14T10:10:00-06:00',
                'ubicacion' => ['lat' => 19.7611, 'lng' => -101.1012, 'precision' => 8],
                'datos' => ['medio' => ['NÚMERO VISIBLE'], 'abundar_area' => 'Centro', 'abundar_inmueble' => 'Casa azul'],
            ]),
            'fotos' => [UploadedFile::fake()->image('f.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated();

        $this->apiRuta()->assertJsonPath('data.paradas.0.estado', 'en_revision');

        $diligencia = DB::table('sinotifico_diligencias')->latest('id')->value('id');
        Sanctum::actingAs($this->delegado, [config('sinotifico.ability')]);
        $this->postJson("/api/v1/sinotifico/revision/{$diligencia}/validar")->assertOk();

        $dom = DB::table('sinotifico_domicilios')->where('texto', 'like', '%MADERO%')->first();
        $this->assertSame('campo', $dom->fuente);
        $this->assertEquals(19.7611, (float) $dom->lat);
        $this->assertTrue((bool) $dom->validado_en_campo);
        $this->assertSame(0, (int) $dom->notificaciones_exitosas); // instructivo: se estuvo ahí, pero no fue notificación personal

        $this->apiRuta()->assertJsonPath('data.paradas.0.estado', 'cerrada');
    }

    public function test_el_acceso_es_una_tarjeta_en_notificaciones_solo_para_quien_supervisa(): void
    {
        $this->actingAs($this->delegado)->get('/notificaciones/rutas')->assertOk()->assertDontSee('href="'.route('rutas.index').'"', false);

        $enlace = $this->usuario('Enlace');
        $this->actingAs($enlace)->get('/notificaciones/home')->assertOk()->assertDontSee('Rutas de notificadores');

        \App\Support\Sinotifico\Acceso::elegirSupervisor($enlace, true);
        $this->actingAs($enlace->fresh())->get('/notificaciones/home')->assertOk()->assertSee('Rutas de notificadores');
        $this->actingAs($this->usuario('Directivo'))->get('/notificaciones/home')->assertOk()->assertSee('Rutas de notificadores');
    }
}
