<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agenda del conciliador por sede (Administración → Conciliadores).
 *
 * permisos_conciliador sigue teniendo el renglón de siempre (sede = NULL):
 * tipo Presencial / Virtual / Ambos para su sede y su subsede. Ese renglón
 * no cambia de significado y lo siguen leyendo las pantallas de hoy.
 *
 * Los renglones con sede son la agenda por sede: uno por sede que atiende,
 * con su modalidad (tipo = Precencial o Virtual), sus días, su horario y su
 * sala. Sólo los tiene quien se configuró desde la pantalla nueva; ver
 * App\Support\SedesConciliador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_conciliador', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos_conciliador', 'sede')) {
                $table->string('sede', 40)->nullable()->after('id_conciliador');
            }
            if (! Schema::hasColumn('permisos_conciliador', 'sala')) {
                $table->string('sala', 40)->nullable()->after('tipo');
            }
        });

        Schema::table('permisos_conciliador', function (Blueprint $table) {
            $table->index(['id_conciliador', 'sede'], 'permisos_conciliador_sede_idx');
        });
    }

    public function down(): void
    {
        // Los renglones por sede sobran sin la columna: se borran primero.
        if (Schema::hasColumn('permisos_conciliador', 'sede')) {
            \Illuminate\Support\Facades\DB::table('permisos_conciliador')->whereNotNull('sede')->delete();
        }

        Schema::table('permisos_conciliador', function (Blueprint $table) {
            $table->dropIndex('permisos_conciliador_sede_idx');
        });

        Schema::table('permisos_conciliador', function (Blueprint $table) {
            $table->dropColumn(['sede', 'sala']);
        });
    }
};
