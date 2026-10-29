<?php

namespace Tests\Feature\Sinotifico;

use App\Models\User;
use App\Support\Sinotifico\Acceso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Quién entra a SíNotifico y con qué alcance. Requiere la migración
 * 2026_10_20_120000_add_sinotifico_permissions.
 */
class AccesoTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD = 'Secreta#2026';

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('sinotifico-login');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function usuario(array $roles, string $sede = 'Uruapan'): User
    {
        $u = new User;
        $u->forceFill([
            'name' => 'Prueba Acceso SíNotifico',
            'email' => 'acceso.sinotifico.'.uniqid().'@test.local',
            'password' => Hash::make(self::PASSWORD),
            'delegacion' => $sede,
            'type' => 'Si concilio',
            'estatus' => 'Activo',
            'email_verified_at' => now(),
        ])->save();

        foreach ($roles as $rol) {
            $u->assignRole(Role::findOrCreate($rol, 'web'));
        }

        return $u;
    }

    private function login(User $u)
    {
        return $this->postJson('/api/v1/sinotifico/auth/login', [
            'email' => $u->email,
            'password' => self::PASSWORD,
            'dispositivo' => 'phpunit',
        ]);
    }

    public function test_notificador_entra_con_su_lista_de_campo(): void
    {
        $this->login($this->usuario(['Notificador']))
            ->assertOk()
            ->assertJsonPath('usuario.acceso.notifica', true)
            ->assertJsonPath('usuario.acceso.supervisa', null)
            ->assertJsonPath('usuario.distintivo', null);
    }

    public function test_enlace_sin_elegir_no_entra(): void
    {
        $this->login($this->usuario(['Enlace']))->assertForbidden();
    }

    public function test_enlace_elegido_supervisa_su_sede(): void
    {
        $enlace = $this->usuario(['Enlace'], 'Zamora');
        Acceso::elegirSupervisor($enlace, true);

        $this->login($enlace)
            ->assertOk()
            ->assertJsonPath('usuario.rol', 'Enlace')
            ->assertJsonPath('usuario.acceso.notifica', false)
            ->assertJsonPath('usuario.acceso.supervisa', 'sede')
            ->assertJsonPath('usuario.distintivo', 'Supervisa sede Zamora');
    }

    public function test_delegado_supervisa_su_sede_por_rol(): void
    {
        $this->login($this->usuario(['Delegado']))
            ->assertOk()
            ->assertJsonPath('usuario.acceso.supervisa', 'sede');
    }

    public function test_directivo_y_super_usuario_ven_todas_las_sedes(): void
    {
        foreach (['Directivo', 'Super Usuario'] as $rol) {
            $this->login($this->usuario([$rol]))
                ->assertOk()
                ->assertJsonPath('usuario.acceso.supervisa', 'todas')
                ->assertJsonPath('usuario.distintivo', 'Supervisión de todas las sedes');
        }
    }

    public function test_supervisor_sin_rol_de_notificador_no_abre_la_lista_de_campo(): void
    {
        $token = $this->login($this->usuario(['Directivo']))->json('token');

        $this->withToken($token)->getJson('/api/v1/sinotifico/diligencias')
            ->assertForbidden()
            ->assertJsonPath('message', 'Esta sección es sólo para notificadores.');
    }

    public function test_al_quitarle_la_supervision_su_token_deja_de_servir(): void
    {
        $enlace = $this->usuario(['Enlace']);
        Acceso::elegirSupervisor($enlace, true);
        $token = $this->login($enlace)->json('token');

        Acceso::elegirSupervisor($enlace->fresh(), false);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/sinotifico/auth/me')->assertForbidden();
    }

    public function test_solo_un_enlace_puede_ser_elegido(): void
    {
        $auxiliar = $this->usuario(['Auxiliar']);
        Acceso::elegirSupervisor($auxiliar, true);

        $this->assertFalse(Acceso::supervisaPorEleccion($auxiliar->fresh()));
    }

    public function test_desde_usuarios_se_elige_y_al_quitar_enlace_se_retira(): void
    {
        $admin = $this->usuario(['Super Usuario'], 'Morelia');
        $admin->givePermissionTo(Permission::findOrCreate('usuarios_editar', 'web'));
        $enlace = $this->usuario(['Enlace']);

        $datos = fn (array $roles, string $supervisa) => [
            'name' => $enlace->name,
            'email' => $enlace->email,
            'roles' => $roles,
            'delegacion' => 'Uruapan',
            'type' => 'Si concilio',
            'activo' => '1',
            'sinotifico_supervisa' => $supervisa,
        ];

        $this->actingAs($admin)
            ->patch(route('usuarios.update', $enlace->id), $datos(['Enlace'], '1'))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('usuarios'));
        $this->assertTrue(Acceso::supervisaPorEleccion($enlace->fresh()));

        $this->actingAs($admin)
            ->patch(route('usuarios.update', $enlace->id), $datos(['Notificador'], '1'))
            ->assertSessionHasNoErrors();
        $this->assertFalse(Acceso::supervisaPorEleccion($enlace->fresh()));
    }
}
