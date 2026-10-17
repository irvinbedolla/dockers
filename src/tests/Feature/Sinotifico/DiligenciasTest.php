<?php

namespace Tests\Feature\Sinotifico;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Lista de trabajo del notificador. Corre contra la base configurada (QA)
 * dentro de una transacción que se revierte: no deja datos.
 *
 * Fecha fija: miércoles 14 de octubre de 2026.
 */
class DiligenciasTest extends TestCase
{
    use DatabaseTransactions;

    private User $notificador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-14 09:00:00');

        // Calendario limpio para que los inhábiles reales de QA no muevan los
        // plazos esperados. Se revierte con la transacción.
        DB::table('dias_inhabiles')->where('centro', 'Morelia')->delete();

        DB::table('estados')->insertOrIgnore(['id' => 16, 'nombre' => 'Michoacán', 'pais' => 1]);
        DB::table('municipios')->insertOrIgnore(['id' => 16053, 'nombre' => 'Morelia', 'estado' => 16, 'delegacion_id' => 1]);

        $this->notificador = $this->usuario();
        Sanctum::actingAs($this->notificador, [config('sinotifico.ability')]);
    }

    private function usuario(): User
    {
        $u = new User;
        $u->forceFill([
            'name' => 'Notificador de prueba',
            'email' => 'notif.'.uniqid().'@test.local',
            'password' => Hash::make('x'),
            'delegacion' => 'Morelia',
            'type' => 'Si concilio',
            'estatus' => 'Activo',
        ])->save();
        $u->assignRole(Role::findOrCreate('Notificador', 'web'));

        return $u;
    }

    /** Crea solicitud + audiencia y devuelve [solicitud_id, audiencia_id]. */
    private function solicitud(string $fechaAudiencia): array
    {
        $solicitud = DB::table('seer_general')->insertGetId([
            'año' => 2026, 'id_rama' => 1, 'actividad' => 'Prueba', 'delegacion' => 'Morelia',
            'tipo' => 'Presencial', 'tipo_generacion' => 1, 'fecha' => '2026-10-01', 'NUE' => 'MOR/SOL/2026/'.random_int(90000, 99999),
        ]);

        $audiencia = DB::table('audiencias')->insertGetId([
            'id_solicitud' => $solicitud, 'numero_audiencia' => 1, 'folio_audiencia' => '1/2026',
            'fecha' => $fechaAudiencia, 'proxima_audiencia' => $fechaAudiencia, 'hora' => '10:00:00', 'id_conciliador' => 1, 'sala' => '1',
            'delegacion' => 'Morelia',
        ]);

        return [$solicitud, $audiencia];
    }

    private function citado(array $sol, array $datos = []): int
    {
        return DB::table('seer_citados')->insertGetId(array_merge([
            'id_solicitud' => $sol[0], 'audiencia_id' => $sol[1], 'id_notificador' => $this->notificador->id,
            'estatus' => 'Pendiente', 'notificacion' => 'Centro', 'tipo_notificacion' => 'Citatorio',
            'tipo_persona' => 'Moral', 'nombre' => 'EMPRESA DE PRUEBA S.A. DE C.V.',
            'tipo_vialidad' => 'CALLE', 'calle' => 'RIO CUPATITZIO', 'n_ext' => '21', 'colonia' => 'EDUARDO RUIZ',
            'cp' => '58149', 'municipio_citado' => 16053, 'estado_citado' => 16, 'referencia' => 'Junto a la tortillería',
        ], $datos));
    }

    public function test_agrupa_los_citados_del_mismo_domicilio_en_una_visita(): void
    {
        $sol = $this->solicitud('2026-10-28');
        $empresa = $this->citado($sol);
        $this->citado($sol, ['nombre' => 'QUIEN O QUIENES RESULTEN RESPONSABLES DE LA FUENTE DE EMPLEO']);
        $this->citado($sol, ['calle' => 'JOSE NICOLAS ECHEGARAY', 'n_ext' => '51', 'tipo_persona' => 'Fisica',
            'nombre' => 'FERNANDO', 'primer_apellido' => 'MEJIA', 'segundo_apellido' => 'VILLA']);

        $r = $this->getJson('/api/v1/sinotifico/diligencias')->assertOk();

        $r->assertJsonPath('resumen.visitas', 2)->assertJsonPath('resumen.citados', 3);

        $visita = collect($r->json('data'))->firstWhere('id', $empresa);
        $this->assertCount(2, $visita['citados']);
        $this->assertFalse($visita['citados'][0]['generico']);
        $this->assertTrue($visita['citados'][1]['generico']);
        $this->assertStringContainsString('RIO CUPATITZIO 21', $visita['domicilio']['texto']);
        $this->assertSame('Morelia', $visita['domicilio']['municipio']);
    }

    public function test_el_plazo_cuenta_cinco_dias_habiles_y_salta_inhabiles_de_la_sede(): void
    {
        // Jueves 22 de octubre inhábil en Morelia; en Zamora no cuenta.
        DB::table('dias_inhabiles')->insert([
            ['fecha_inicio' => '2026-10-22', 'fecha_final' => '2026-10-22', 'centro' => 'Morelia', 'tipo' => 'Todos', 'descripcion' => 'Inhabil'],
            ['fecha_inicio' => '2026-10-23', 'fecha_final' => '2026-10-23', 'centro' => 'Zamora', 'tipo' => 'Todos', 'descripcion' => 'Inhabil'],
        ]);

        // Audiencia miércoles 28 → 27, 26, 23, (22 inhábil), 21, 20 → límite martes 20.
        $id = $this->citado($this->solicitud('2026-10-28'));

        $visita = collect($this->getJson('/api/v1/sinotifico/diligencias')->json('data'))->firstWhere('id', $id);

        $this->assertSame('2026-10-20', $visita['plazo']['fecha_limite']);
        // Del miércoles 14 al martes 20: 15, 16, 19, 20.
        $this->assertSame(4, $visita['plazo']['dias_habiles_restantes']);
        $this->assertSame('en_plazo', $visita['plazo']['estado']);
    }

    public function test_marca_fuera_de_plazo_y_ordena_lo_urgente_primero(): void
    {
        $tarde = $this->citado($this->solicitud('2026-11-04'), ['calle' => 'OTRA']);
        // Audiencia lunes 19 → límite lunes 12, ya pasó.
        $vencida = $this->citado($this->solicitud('2026-10-19'));

        $r = $this->getJson('/api/v1/sinotifico/diligencias')->assertOk();

        $this->assertSame($vencida, $r->json('data.0.id'));
        $r->assertJsonPath('data.0.plazo.estado', 'fuera_de_plazo')
            ->assertJsonPath('data.1.id', $tarde)
            ->assertJsonPath('resumen.fuera_de_plazo', 1);
    }

    public function test_en_fin_de_semana_un_limite_de_ayer_ya_esta_fuera_de_plazo(): void
    {
        $this->travelTo('2026-10-17 09:00:00'); // sábado
        DB::table('dias_inhabiles')->insert(['fecha_inicio' => '2026-10-19', 'fecha_final' => '2026-10-19',
            'centro' => 'Morelia', 'tipo' => 'Todos', 'descripcion' => 'Inhabil']);
        // 23, 22, 21, 20, (19 inhábil), 16 → límite viernes 16.
        $id = $this->citado($this->solicitud('2026-10-26'));

        $visita = collect($this->getJson('/api/v1/sinotifico/diligencias')->json('data'))->firstWhere('id', $id);

        $this->assertSame('2026-10-16', $visita['plazo']['fecha_limite']);
        $this->assertSame('fuera_de_plazo', $visita['plazo']['estado']);
    }

    public function test_marca_domicilios_fuera_del_estado(): void
    {
        DB::table('estados')->insertOrIgnore(['id' => 19, 'nombre' => 'Nuevo León', 'pais' => 1]);
        $id = $this->citado($this->solicitud('2026-10-28'), ['estado_citado' => 19, 'municipio_citado' => null]);

        $visita = collect($this->getJson('/api/v1/sinotifico/diligencias')->json('data'))->firstWhere('id', $id);

        $this->assertTrue($visita['domicilio']['fuera_del_estado']);
    }

    public function test_no_muestra_lo_de_otros_ni_lo_ya_notificado_ni_audiencias_pasadas(): void
    {
        $otro = $this->usuario();
        $sol = $this->solicitud('2026-10-28');

        $this->citado($sol, ['id_notificador' => $otro->id]);
        $this->citado($sol, ['estatus' => 'Finalizado exitosamente', 'calle' => 'B']);
        $this->citado($sol, ['notificacion' => 'Trabajador', 'calle' => 'C']);
        $this->citado($this->solicitud('2026-10-10'), ['calle' => 'D']);

        $this->getJson('/api/v1/sinotifico/diligencias')
            ->assertOk()
            ->assertJsonPath('resumen.visitas', 0);
    }

    public function test_detalle_solo_de_diligencias_propias(): void
    {
        $sol = $this->solicitud('2026-10-28');
        $mia = $this->citado($sol);
        $ajena = $this->citado($sol, ['id_notificador' => $this->usuario()->id, 'calle' => 'X']);

        $this->getJson("/api/v1/sinotifico/diligencias/{$mia}")->assertOk()->assertJsonPath('data.id', $mia);
        $this->getJson("/api/v1/sinotifico/diligencias/{$ajena}")->assertNotFound();
    }

    public function test_requiere_sesion(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/sinotifico/diligencias', ['Authorization' => ''])->assertUnauthorized();
    }
}
