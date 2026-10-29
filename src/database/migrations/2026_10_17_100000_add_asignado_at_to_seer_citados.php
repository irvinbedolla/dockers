<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * seer_citados.asignado_at: cuándo se le asignó la notificación al
 * notificador. Lo usa el filtro avanzado del historial
 * (App\Support\DesempenoNotificadores) para contar "asignadas en el periodo".
 * Antes de esta columna no se guardaba; para lo anterior se toma created_at.
 *
 * También indexa seer_citados.fecha (fecha de la diligencia), que es por la
 * que se filtra el historial.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seer_citados', function (Blueprint $table) {
            if (! Schema::hasColumn('seer_citados', 'asignado_at')) {
                $table->timestamp('asignado_at')->nullable()->after('id_notificador');
                $table->index(['id_notificador', 'asignado_at'], 'idx_citados_notificador_asignado');
            }
        });

        if (! collect(Schema::getIndexes('seer_citados'))->contains(fn ($i) => $i['name'] === 'idx_citados_fecha')) {
            Schema::table('seer_citados', fn (Blueprint $table) => $table->index('fecha', 'idx_citados_fecha'));
        }
    }

    public function down(): void
    {
        Schema::table('seer_citados', function (Blueprint $table) {
            if (Schema::hasColumn('seer_citados', 'asignado_at')) {
                $table->dropIndex('idx_citados_notificador_asignado');
                $table->dropColumn('asignado_at');
            }
        });

        if (collect(Schema::getIndexes('seer_citados'))->contains(fn ($i) => $i['name'] === 'idx_citados_fecha')) {
            Schema::table('seer_citados', fn (Blueprint $table) => $table->dropIndex('idx_citados_fecha'));
        }
    }
};
