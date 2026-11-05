<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Administración → Módulos: pausar las citas en línea de un módulo.
 *
 *   en_linea            si el formulario público (solicitud, asesoría y
 *                       ratificación en línea) le puede asignar citas. La
 *                       ventanilla y la reasignación manual no lo miran.
 *   en_linea_cambio_at  cuándo se prendió o apagó por última vez.
 *   en_linea_cambio_por quién lo hizo (users.id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'en_linea')) {
                $table->boolean('en_linea')->default(true)->after('activo');
            }
            if (! Schema::hasColumn('modulos', 'en_linea_cambio_at')) {
                $table->timestamp('en_linea_cambio_at')->nullable()->after('en_linea');
            }
            if (! Schema::hasColumn('modulos', 'en_linea_cambio_por')) {
                $table->unsignedBigInteger('en_linea_cambio_por')->nullable()->after('en_linea_cambio_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            foreach (['en_linea_cambio_por', 'en_linea_cambio_at', 'en_linea'] as $columna) {
                if (Schema::hasColumn('modulos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
