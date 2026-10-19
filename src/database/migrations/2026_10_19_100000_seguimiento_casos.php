<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seguimiento del caso (App\Support\SeguimientoCaso):
 *
 *   - recepcion.confirmada_at: cuándo la persona llegó y confirmó su cita
 *     con el QR (ValidadorCita). Antes sólo cambiaba el estatus.
 *   - seer_general.recepcion_vinculo: cómo se ligó la solicitud con su cita:
 *       prellenado → se capturó desde la cita ("Preparar solicitud");
 *       curp       → al guardar, por la CURP del solicitante y la cita de hoy;
 *       historico  → por `php artisan citas:ligar-solicitudes` (aproximado).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recepcion', 'confirmada_at')) {
            Schema::table('recepcion', function (Blueprint $table) {
                $table->timestamp('confirmada_at')->nullable()->after('estatus');
            });
        }

        if (! Schema::hasColumn('seer_general', 'recepcion_vinculo')) {
            Schema::table('seer_general', function (Blueprint $table) {
                $table->string('recepcion_vinculo', 12)->nullable()->after('recepcion_id');
            });
        }

        // Índice para buscar citas por CURP al ligar.
        if (Schema::hasColumn('recepcion', 'curp')) {
            $existe = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM recepcion WHERE Key_name = 'idx_recepcion_curp'"))->isNotEmpty();
            if (! $existe) {
                Schema::table('recepcion', fn (Blueprint $table) => $table->index('curp', 'idx_recepcion_curp'));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('recepcion', 'confirmada_at')) {
            Schema::table('recepcion', fn (Blueprint $table) => $table->dropColumn('confirmada_at'));
        }
        if (Schema::hasColumn('seer_general', 'recepcion_vinculo')) {
            Schema::table('seer_general', fn (Blueprint $table) => $table->dropColumn('recepcion_vinculo'));
        }
        $existe = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM recepcion WHERE Key_name = 'idx_recepcion_curp'"))->isNotEmpty();
        if ($existe) {
            Schema::table('recepcion', fn (Blueprint $table) => $table->dropIndex('idx_recepcion_curp'));
        }
    }
};
