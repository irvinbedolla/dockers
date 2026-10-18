<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Motivo exacto del caso de excepción (App\Support\CasosExcepcion::MOTIVOS),
 * para el reporte por motivo (maternidad, orientación sexual, edad...):
 *
 *   recepcion.motivo_excepcion                  lo elegido al agendar la cita;
 *   seer_casos_excepcion.motivo_discriminacion  "Señale el motivo" del formato.
 *
 * recepcion.tipo_caso sólo guardaba el grupo ("Discriminación",
 * "Prestaciones"...), así que de lo anterior sólo se recupera lo que no es
 * ambiguo (acoso sexual, acoso laboral, designación de beneficiarios).
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recepcion', 'motivo_excepcion')) {
            Schema::table('recepcion', function (Blueprint $t) {
                $t->string('motivo_excepcion', 40)->nullable()->after('tipo_caso');
                $t->index(['exepcion', 'motivo_excepcion'], 'idx_recepcion_motivo_excepcion');
            });

            foreach (['Acoso u hostigamiento sexual' => 'acoso_sexual', 'Acoso u hostigamiento laboral' => 'acoso_laboral', 'Designacion' => 'designacion'] as $tipo => $clave) {
                DB::table('recepcion')->where('tipo_caso', $tipo)->whereNull('motivo_excepcion')->update(['motivo_excepcion' => $clave]);
            }
        }

        if (Schema::hasTable('seer_casos_excepcion') && ! Schema::hasColumn('seer_casos_excepcion', 'motivo_discriminacion')) {
            Schema::table('seer_casos_excepcion', fn (Blueprint $t) => $t->string('motivo_discriminacion', 40)->nullable()->after('motivos'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('recepcion', 'motivo_excepcion')) {
            Schema::table('recepcion', function (Blueprint $t) {
                $t->dropIndex('idx_recepcion_motivo_excepcion');
                $t->dropColumn('motivo_excepcion');
            });
        }
        if (Schema::hasColumn('seer_casos_excepcion', 'motivo_discriminacion')) {
            Schema::table('seer_casos_excepcion', fn (Blueprint $t) => $t->dropColumn('motivo_discriminacion'));
        }
    }
};
