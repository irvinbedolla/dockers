<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Citas en línea: desde qué conexión se agendan y un tope por conexión.
 *
 *   recepcion.ip / user_agent  IP pública y navegador con que se registró la
 *                              cita en línea (las de ventanilla quedan en null).
 *   limites_citas              el tope que se fija en Administración → Módulos
 *                              (una sola fila; 0 = sin límite).
 *   citas_bloqueadas           cada intento que el tope detuvo, para verlo en
 *                              /estadisticas/turnos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'ip')) {
                $table->string('ip', 45)->nullable()->after('origen');
                $table->index('ip');
            }
            if (! Schema::hasColumn('recepcion', 'user_agent')) {
                $table->string('user_agent', 255)->nullable()->after('ip');
            }
        });

        if (! Schema::hasTable('limites_citas')) {
            Schema::create('limites_citas', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('por_ip')->default(0);
                $table->unsignedSmallInteger('por_correo')->default(0);
                $table->unsignedSmallInteger('por_telefono')->default(0);
                $table->string('periodo', 10)->default('dia');
                $table->text('exentas')->nullable();
                $table->unsignedBigInteger('actualizado_por')->nullable();
                $table->string('actualizado_por_nombre', 120)->nullable();
                $table->timestamps();
            });
            DB::table('limites_citas')->insert(['id' => 1, 'periodo' => 'dia', 'created_at' => now(), 'updated_at' => now()]);
        }

        if (! Schema::hasTable('citas_bloqueadas')) {
            Schema::create('citas_bloqueadas', function (Blueprint $table) {
                $table->id();
                $table->string('motivo', 10);            // ip | correo | telefono
                $table->unsignedSmallInteger('limite');
                $table->unsignedSmallInteger('llevaba');
                $table->string('periodo', 10);
                $table->string('ip', 45)->nullable()->index();
                $table->string('user_agent', 255)->nullable();
                $table->string('correo', 150)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('curp', 18)->nullable();
                $table->string('nombre', 150)->nullable();
                $table->string('delegacion', 40)->nullable();
                $table->string('tipo', 30)->nullable();
                $table->date('fecha_turno')->nullable()->index();
                $table->string('hora_turno', 5)->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('citas_bloqueadas');
        Schema::dropIfExists('limites_citas');
        Schema::table('recepcion', function (Blueprint $table) {
            if (Schema::hasColumn('recepcion', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('recepcion', 'ip')) {
                $table->dropIndex(['ip']);
                $table->dropColumn('ip');
            }
        });
    }
};
