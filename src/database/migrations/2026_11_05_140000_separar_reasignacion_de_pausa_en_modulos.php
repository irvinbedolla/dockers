<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Administración → Módulos: la fecha deja de ser parte de la pausa de citas
 * (que vuelve a ser inmediata) y pasa a su propio control, "Acepta
 * reasignaciones":
 *
 *   no_reasignar_desde  desde qué fecha de cita ya no se le pueden mover
 *                       citas (reasignación manual o reagenda). null = sí acepta.
 *   no_reasignar_por    quién lo puso (users.id).
 *   no_reasignar_at     cuándo.
 *
 * Lo que había en pausa_desde (2026_11_05_130000) se pasa aquí cuando era
 * una pausa total, y la columna se quita. Una pausa de citas que todavía no
 * empezaba se quita, porque ahora la pausa de citas es inmediata.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'no_reasignar_desde')) {
                $table->date('no_reasignar_desde')->nullable();
            }
            if (! Schema::hasColumn('modulos', 'no_reasignar_por')) {
                $table->unsignedBigInteger('no_reasignar_por')->nullable();
            }
            if (! Schema::hasColumn('modulos', 'no_reasignar_at')) {
                $table->timestamp('no_reasignar_at')->nullable();
            }
        });

        if (Schema::hasColumn('modulos', 'pausa_desde')) {
            if (Schema::hasColumn('modulos', 'pausa_ventanilla')) {
                DB::table('modulos')->whereNotNull('pausa_desde')->where('pausa_ventanilla', true)->update([
                    'no_reasignar_desde' => DB::raw('pausa_desde'),
                    'no_reasignar_por'   => DB::raw('en_linea_cambio_por'),
                    'no_reasignar_at'    => DB::raw('en_linea_cambio_at'),
                ]);
                DB::table('modulos')->where('pausa_desde', '>', now()->toDateString())
                    ->update(['en_linea' => true, 'pausa_ventanilla' => false]);
            }

            Schema::table('modulos', function (Blueprint $table) {
                $table->dropColumn('pausa_desde');
            });
        }
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            foreach (['no_reasignar_desde', 'no_reasignar_por', 'no_reasignar_at'] as $columna) {
                if (Schema::hasColumn('modulos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
