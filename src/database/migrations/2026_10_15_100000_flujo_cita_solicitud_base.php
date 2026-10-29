<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 0 del flujo "cita en línea → solicitud" (App\Support\FlujoCita).
 *
 * Sólo agrega; no cambia nada de lo que ya funciona:
 *   - ajustes: interruptor global (apagado | pruebas | activo), correos de
 *     prueba, auxiliares piloto y minutos de aviso. Arranca "apagado".
 *   - recepcion: es_prueba, curp, domicilio (los pedirá el formulario
 *     público en la fase 2), llamado_at y atencion_inicio_at (fase 4). Los
 *     estatus de siempre no se tocan.
 *   - seer_general.recepcion_id: liga la solicitud con la cita que la originó.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ajustes')) {
            Schema::create('ajustes', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 80)->unique();
                $table->text('valor')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        $ahora = now();
        $iniciales = [
            'flujo_cita.estado'         => 'apagado',
            'flujo_cita.correos_prueba' => 'vflorestrejo@gmail.com',
            'flujo_cita.pilotos'        => '[]',
            'flujo_cita.aviso_minutos'  => '10',
        ];
        foreach ($iniciales as $clave => $valor) {
            if (! DB::table('ajustes')->where('clave', $clave)->exists()) {
                DB::table('ajustes')->insert(['clave' => $clave, 'valor' => $valor, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }

        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'es_prueba')) {
                $table->boolean('es_prueba')->default(false)->after('origen');
            }
            if (! Schema::hasColumn('recepcion', 'curp')) {
                $table->string('curp', 18)->nullable()->after('solicitante');
            }
            if (! Schema::hasColumn('recepcion', 'domicilio')) {
                $table->string('domicilio', 255)->nullable()->after('municipio');
            }
            if (! Schema::hasColumn('recepcion', 'llamado_at')) {
                $table->timestamp('llamado_at')->nullable();
            }
            if (! Schema::hasColumn('recepcion', 'atencion_inicio_at')) {
                $table->timestamp('atencion_inicio_at')->nullable();
            }
        });

        if (! Schema::hasColumn('seer_general', 'recepcion_id')) {
            Schema::table('seer_general', function (Blueprint $table) {
                $table->integer('recepcion_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('seer_general', 'recepcion_id')) {
            Schema::table('seer_general', function (Blueprint $table) {
                $table->dropIndex(['recepcion_id']);
                $table->dropColumn('recepcion_id');
            });
        }

        Schema::table('recepcion', function (Blueprint $table) {
            foreach (['es_prueba', 'curp', 'domicilio', 'llamado_at', 'atencion_inicio_at'] as $col) {
                if (Schema::hasColumn('recepcion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        DB::table('ajustes')->where('clave', 'like', 'flujo_cita.%')->delete();
        if (DB::table('ajustes')->count() === 0) {
            Schema::dropIfExists('ajustes');
        }
    }
};
