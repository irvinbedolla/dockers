<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observaciones de "Llegadas hoy" (App\Support\LlegadasHoy): además de las de
 * la recepción, el conciliador anota las suyas en sus audiencias, con las
 * mismas casillas. Una fila por registro y por autor:
 *
 *   autor   recepcion | conciliador   (las que ya había son de la recepción)
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('llegadas_observaciones') || Schema::hasColumn('llegadas_observaciones', 'autor')) {
            return;
        }

        Schema::table('llegadas_observaciones', function (Blueprint $table) {
            $table->string('autor', 15)->default('recepcion')->after('registro_id');
        });

        Schema::table('llegadas_observaciones', function (Blueprint $table) {
            if (Schema::hasIndex('llegadas_observaciones', ['tipo', 'registro_id'], 'unique')) {
                $table->dropUnique(['tipo', 'registro_id']);
            }
            $table->unique(['tipo', 'registro_id', 'autor']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('llegadas_observaciones') || ! Schema::hasColumn('llegadas_observaciones', 'autor')) {
            return;
        }

        // Al volver a una fila por registro sólo se quedan las de la recepción.
        \Illuminate\Support\Facades\DB::table('llegadas_observaciones')->where('autor', '!=', 'recepcion')->delete();

        Schema::table('llegadas_observaciones', function (Blueprint $table) {
            $table->dropUnique(['tipo', 'registro_id', 'autor']);
            $table->unique(['tipo', 'registro_id']);
        });
        Schema::table('llegadas_observaciones', function (Blueprint $table) {
            $table->dropColumn('autor');
        });
    }
};
