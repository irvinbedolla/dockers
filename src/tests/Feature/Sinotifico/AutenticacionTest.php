<?php

namespace Tests\Feature\Sinotifico;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Corre contra la base configurada (QA). Cada prueba va dentro de una
 * transacción que se revierte al final: no deja usuarios ni tokens.
 */
class AutenticacionTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD = 'Secreta#2026';

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('sinotifico-login');
    }

    private function usuario(string $rol = 'Notificador', string $estatus = 'Activo'): User
    {
        $usuario = new User;
        $usuario->forceFill([
            'name' => 'Prueba SíNotifico',
            'email' => 'prueba.sinotifico.'.uniqid().'@test.local',
            'password' => Hash::make(self::PASSWORD),
            'delegacion' => 'Morelia',
            'type' => 'Si concilio',
            'estatus' => $estatus,
        ])->save();

        $usuario->assignRole(Role::findOrCreate($rol, 'web'));

        return $usuario;
    }

    private function login(User $usuario, string $password = self::PASSWORD)
    {
        return $this->postJson('/api/v1/sinotifico/auth/login', [
            'email' => $usuario->email,
            'password' => $password,
            'dispositivo' => 'phpunit',
        ]);
    }

    public function test_estado_responde_sin_sesion(): void
    {
        $this->getJson('/api/v1/sinotifico/estado')
            ->assertOk()
            ->assertJsonPath('servicio', 'sinotifico');
    }

    public function test_notificador_inicia_sesion_con_su_cuenta_de_siconcilio(): void
    {
        $usuario = $this->usuario();

        $this->login($usuario)
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'expira_en', 'usuario' => ['id', 'nombre', 'rol', 'sede']])
            ->assertJsonPath('usuario.rol', 'Notificador')
            ->assertJsonPath('usuario.sede.clave', 'MOR')
            ->assertJsonMissingPath('usuario.password');

        $this->assertNotNull($usuario->tokens()->first()->expires_at);
    }

    public function test_contrasena_incorrecta_no_revela_si_la_cuenta_existe(): void
    {
        $usuario = $this->usuario();

        $malaContrasena = $this->login($usuario, 'otra')->assertStatus(422)->json('message');

        $noExiste = $this->postJson('/api/v1/sinotifico/auth/login', [
            'email' => 'nadie.'.uniqid().'@test.local',
            'password' => 'otra',
        ])->assertStatus(422)->json('message');

        $this->assertSame($malaContrasena, $noExiste);
    }

    public function test_cuenta_inactiva_no_entra(): void
    {
        $this->login($this->usuario('Notificador', 'Inactivo'))->assertForbidden();
    }

    public function test_rol_sin_acceso_no_entra(): void
    {
        $this->login($this->usuario('Auxiliar'))->assertForbidden();
    }

    public function test_me_devuelve_al_usuario_del_token(): void
    {
        $usuario = $this->usuario();
        $token = $this->login($usuario)->json('token');

        $this->withToken($token)->getJson('/api/v1/sinotifico/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $usuario->id);
    }

    public function test_sin_token_responde_401_y_no_redirige(): void
    {
        $this->get('/api/v1/sinotifico/auth/me')->assertUnauthorized();
    }

    public function test_si_desactivan_la_cuenta_el_token_deja_de_servir(): void
    {
        $usuario = $this->usuario();
        $token = $this->login($usuario)->json('token');

        $usuario->forceFill(['estatus' => 'Inactivo'])->save();

        $this->withToken($token)->getJson('/api/v1/sinotifico/auth/me')->assertUnauthorized();
        $this->assertSame(0, $usuario->tokens()->count());
    }

    public function test_token_de_otra_app_no_abre_sinotifico(): void
    {
        $usuario = $this->usuario();
        $token = $usuario->createToken('otra-app', ['otra'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/sinotifico/auth/me')->assertForbidden();
    }

    public function test_logout_revoca_el_token(): void
    {
        $usuario = $this->usuario();
        $token = $this->login($usuario)->json('token');

        $this->withToken($token)->postJson('/api/v1/sinotifico/auth/logout')->assertOk();
        $this->assertSame(0, $usuario->tokens()->count());
    }

    public function test_demasiados_intentos_se_bloquean(): void
    {
        $usuario = $this->usuario();

        foreach (range(1, config('sinotifico.login_intentos_por_cuenta')) as $_) {
            $this->login($usuario, 'mala');
        }

        $this->login($usuario)->assertStatus(429);
    }

    public function test_las_rutas_demo_de_usuarios_ya_no_existen(): void
    {
        $this->getJson('/api/v1/users')->assertNotFound();
        $this->putJson('/api/v1/users/1', ['role' => 'Super Usuario'])->assertNotFound();
        $this->deleteJson('/api/v1/roles/1')->assertNotFound();
    }
}
