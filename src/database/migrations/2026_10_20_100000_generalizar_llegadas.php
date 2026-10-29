<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Llegadas hoy" (App\Support\LlegadasHoy): un solo registro de llegadas
 * para audiencias, ratificaciones y cumplimientos.
 *
 * audiencia_llegadas pasa a llamarse llegadas y gana:
 *   tipo         audiencia | ratificacion | cumplimiento
 *   registro_id  id en audiencias, turnos o pago_solicitud (antes audiencia_id)
 *   origen       manual (lo marcó la recepción) | cita (validó su cita con QR)
 *   parte        texto libre: solicitante, citado, trabajador, empresa
 *
 * Lo que ya estaba marcado se conserva como tipo = audiencia.
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audiencia_llegadas') && ! Schema::hasTable('llegadas')) {
            Schema::rename('audiencia_llegadas', 'llegadas');
        }

        if (! Schema::hasTable('llegadas')) {
            Schema::create('llegadas', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 15)->default('audiencia');
                $table->integer('registro_id');
                $table->string('clave', 60);
                $table->string('parte', 20);
                $table->string('nombre', 255);
                $table->string('origen', 10)->default('manual');
                $table->timestamp('llegada_at')->useCurrent();
                $table->unsignedBigInteger('registrado_por')->nullable();
                $table->timestamps();

                $table->unique(['tipo', 'registro_id', 'clave']);
            });

            return;
        }

        if (! Schema::hasColumn('llegadas', 'tipo')) {
            Schema::table('llegadas', function (Blueprint $table) {
                $table->string('tipo', 15)->default('audiencia')->after('id');
                $table->string('origen', 10)->default('manual')->after('nombre');
            });
        }

        if (Schema::hasColumn('llegadas', 'audiencia_id')) {
            Schema::table('llegadas', function (Blueprint $table) {
                if (Schema::hasIndex('llegadas', 'audiencia_llegadas_audiencia_id_clave_unique')) {
                    $table->dropUnique('audiencia_llegadas_audiencia_id_clave_unique');
                }
                if (Schema::hasIndex('llegadas', 'audiencia_llegadas_audiencia_id_index')) {
                    $table->dropIndex('audiencia_llegadas_audiencia_id_index');
                }
            });
            Schema::table('llegadas', function (Blueprint $table) {
                $table->renameColumn('audiencia_id', 'registro_id');
            });
        }

        Schema::table('llegadas', function (Blueprint $table) {
            $table->string('parte', 20)->change();
        });

        if (! Schema::hasIndex('llegadas', ['tipo', 'registro_id', 'clave'], 'unique')) {
            Schema::table('llegadas', function (Blueprint $table) {
                $table->unique(['tipo', 'registro_id', 'clave']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('llegadas') || Schema::hasTable('audiencia_llegadas')) {
            return;
        }

        DB::table('llegadas')->where('tipo', '!=', 'audiencia')->delete();

        Schema::table('llegadas', function (Blueprint $table) {
            $table->dropUnique(['tipo', 'registro_id', 'clave']);
        });
        Schema::table('llegadas', function (Blueprint $table) {
            $table->renameColumn('registro_id', 'audiencia_id');
        });
        Schema::table('llegadas', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'origen']);
        });
        Schema::rename('llegadas', 'audiencia_llegadas');
        Schema::table('audiencia_llegadas', function (Blueprint $table) {
            $table->index('audiencia_id');
            $table->unique(['audiencia_id', 'clave']);
        });
    }
};
