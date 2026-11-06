<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Administración → Módulos: la pausa de un módulo puede ser sólo de citas en
 * línea (en_linea = false) o de todas, también las de ventanilla
 * (pausa_ventanilla = true). Con la pausa total no se le asigna ninguna cita
 * nueva automáticamente; la reasignación manual sí sigue disponible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'pausa_ventanilla')) {
                $table->boolean('pausa_ventanilla')->default(false)->after('en_linea');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (Schema::hasColumn('modulos', 'pausa_ventanilla')) {
                $table->dropColumn('pausa_ventanilla');
            }
        });
    }
};
