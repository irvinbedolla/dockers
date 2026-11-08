<?php

namespace Tests\Feature\Sinotifico;

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

/** Módulo 5: tablero de supervisión (app y SICONCILIO). Miércoles 14 de octubre de 2026. */
class TableroTest extends TestCase
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
        config(['sinotifico.geocodificador' => 'ninguno']);
        DB::table('dias_inhabiles')->where('centro', 'Morelia')->delete();
        DB::table('estados')->insertOrIgnore(['id' => 16, 'nombre' => 'Michoacán', 'pais' => 1]);
        DB::table('municipios')->insertOrIgnore(['id' => 16053, 'nombre' => 'Morelia', 'estado' => 16, 'delegacion_id' => 1]);
        // Sólo los notificadores de esta prueba cuentan.
        DB::table('users')->update(['estatus' => 'Inactivo']);

        $this->notificador = $this->usuario('Notificador');
    }

    private function usuario(string $rol, string $sede = 'Morelia'): User
    {
        $u = new User;
        $u->forceFill([
            'name' => "{$rol} {$sede} tablero", 'email' => 'tablero.'.uniqid().'@test.local', 'password' => Hash::make('x'),
            'delegacion' => $sede, 'type' => 'Si concilio', 'estatus' => 'Activo', 'email_verified_at' => now(),
        ])->save();
        $u->assignRole(Role::findOrCreate($rol, 'web'));

        return $u;
    }

    private function visita(User $n, string $audiencia): int
    {
        $sol = DB::table('seer_general')->insertGetId([
            'año' => 2026, 'id_rama' => 1, 'actividad' => 'Prueba', 'delegacion' => $n->delegacion, 'tipo' => 'Presencial',
            'tipo_generacion' => 1, 'fecha' => '2026-10-01', 'NUE' => 'MOR/SOL/2026/'.random_int(90000, 99999),
        ]);
        $aud = DB::table('audiencias')->insertGetId([
            'id_solicitud' => $sol, 'numero_audiencia' => 1, 'folio_audiencia' => '1/2026', 'fecha' => $audiencia,
            'proxima_audiencia' => $audiencia, 'hora' => '10:00:00', 'id_conciliador' => 1, 'sala' => '1', 'delegacion' => $n->delegacion,
        ]);

        return DB::table('seer_citados')->insertGetId([
            'id_solicitud' => $sol, 'audiencia_id' => $aud, 'id_notificador' => $n->id, 'estatus' => 'Pendiente',
            'notificacion' => 'Centro', 'tipo_notificacion' => 'Citatorio', 'tipo_persona' => 'Moral', 'nombre' => 'EMPRESA '.uniqid(),
            'tipo_vialidad' => 'CALLE', 'calle' => 'HIDALGO', 'n_ext' => (string) random_int(1, 999), 'colonia' => 'CENTRO',
            'cp' => '58000', 'municipio_citado' => 16053, 'estado_citado' => 16, 'referencia' => 'Ninguna',
        ]);
    }

    private function capturar(int $citado): void
    {
        Sanctum::actingAs($this->notificador, [config('sinotifico.ability')]);
        $this->post('/api/v1/sinotifico/capturas', [
            'captura' => json_encode([
                'uuid' => (string) Str::uuid(), 'citados' => [$citado], 'quien_atiende' => 'NADIE', 'resultado' => 'No notificada',
                'llegada_at' => now()->toIso8601String(), 'capturada_at' => now()->toIso8601String(),
                'datos' => ['medio' => ['NÚMERO VISIBLE'], 'abundar_area' => 'Centro', 'abundar_inmueble' => 'Casa azul'],
            ]),
            'fotos' => [UploadedFile::fake()->image('f.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated();
    }

    public function test_el_supervisor_de_sede_ve_cifras_y_alertas_de_su_sede(): void
    {
        $this->visita($this->notificador, '2026-10-16'); // límite ya pasó: fuera de plazo
        $this->visita($this->notificador, '2026-10-28');
        $c = $this->visita($this->notificador, '2026-10-29');
        $this->capturar($c);
        $otro = $this->usuario('Notificador', 'Zamora');
        $this->visita($otro, '2026-10-28');

        Sanctum::actingAs($this->usuario('Delegado'), [config('sinotifico.ability')]);
        $r = $this->getJson('/api/v1/sinotifico/tablero?periodo=hoy')->assertOk();
        $r->assertJsonPath('data.resumen.notificadores', 1)
            ->assertJsonPath('data.resumen.pendientes', 2)
            ->assertJsonPath('data.resumen.fuera_de_plazo', 1)
            ->assertJsonPath('data.resumen.capturas', 1)
            ->assertJsonPath('data.resumen.por_revisar', 1)
            ->assertJsonPath('data.notificadores.0.id', $this->notificador->id)
            ->assertJsonPath('data.alertas.0.tipo', 'fuera_de_plazo')
            ->assertJsonPath('data.sedes', []);
    }

    public function test_el_delegado_de_morelia_ve_tambien_zitacuaro_y_no_otras_regiones(): void
    {
        $this->visita($this->notificador, '2026-10-28');
        $zitacuaro = $this->usuario('Notificador', 'Zitácuaro');
        $this->visita($zitacuaro, '2026-10-28');
        $this->visita($this->usuario('Notificador', 'Uruapan'), '2026-10-28');

        Sanctum::actingAs($this->usuario('Delegado'), [config('sinotifico.ability')]);
        $this->getJson('/api/v1/sinotifico/tablero?periodo=semana')->assertOk()
            ->assertJsonPath('data.resumen.notificadores', 2)
            ->assertJsonPath('data.sedes_alcance', ['Morelia', 'Zitácuaro'])
            ->assertJsonCount(2, 'data.sedes');

        // Puede filtrar dentro de su región, pero no salirse de ella.
        $this->getJson('/api/v1/sinotifico/tablero?sede=Zitácuaro')->assertOk()
            ->assertJsonPath('data.resumen.notificadores', 1)
            ->assertJsonPath('data.notificadores.0.id', $zitacuaro->id);
        $this->getJson('/api/v1/sinotifico/tablero?sede=Uruapan')->assertOk()
            ->assertJsonPath('data.sede', null)
            ->assertJsonPath('data.resumen.notificadores', 2);
    }

    public function test_directivo_ve_todas_las_sedes_y_el_notificador_no_entra(): void
    {
        $this->visita($this->notificador, '2026-10-28');
        $this->visita($this->usuario('Notificador', 'Zamora'), '2026-10-28');

        Sanctum::actingAs($this->usuario('Directivo'), [config('sinotifico.ability')]);
        $this->getJson('/api/v1/sinotifico/tablero?periodo=semana')->assertOk()
            ->assertJsonPath('data.resumen.notificadores', 2)
            ->assertJsonCount(2, 'data.sedes')
            ->assertJsonPath('data.periodo', 'semana');

        Sanctum::actingAs($this->notificador, [config('sinotifico.ability')]);
        $this->getJson('/api/v1/sinotifico/tablero')->assertForbidden();
    }

    public function test_en_siconcilio_se_entra_por_la_tarjeta_y_solo_quien_supervisa(): void
    {
        $this->visita($this->notificador, '2026-10-16');
        $directivo = $this->usuario('Directivo');

        $this->actingAs($directivo)->get('/notificaciones/home')->assertOk()->assertSee('Tablero de notificadores');
        $this->actingAs($directivo)->get('/notificaciones/tablero?periodo=mes')->assertOk()
            ->assertSee($this->notificador->name)->assertSee('fuera de plazo');

        $enlace = $this->usuario('Enlace');
        $this->actingAs($enlace)->get('/notificaciones/home')->assertOk()->assertDontSee('Tablero de notificadores');
        $this->actingAs($enlace)->get('/notificaciones/tablero')->assertForbidden();
    }
}
