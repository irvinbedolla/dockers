<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Administración → Módulos: la pausa de un módulo puede empezar ya o en una
 * fecha. pausa_desde = null: desde ahora. Con fecha: las citas de ese día en
 * adelante no se le asignan (ni se le reasignan, si la pausa es total); las
 * de antes, sí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'pausa_desde')) {
                $table->date('pausa_desde')->nullable()->after('pausa_ventanilla');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (Schema::hasColumn('modulos', 'pausa_desde')) {
                $table->dropColumn('pausa_desde');
            }
        });
    }
};
