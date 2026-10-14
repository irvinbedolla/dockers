<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4 del flujo "cita en línea → solicitud" (App\Support\FlujoCita).
 *
 * recepcion.atencion_fin_at: cuándo el auxiliar terminó de atender la cita
 * desde "Mi módulo". No se usa hora_fin porque en las citas nuevas guarda el
 * fin del horario reservado (lo lee el calendario de citas).
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recepcion', 'atencion_fin_at')) {
            Schema::table('recepcion', function (Blueprint $table) {
                $table->timestamp('atencion_fin_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('recepcion', 'atencion_fin_at')) {
            Schema::table('recepcion', function (Blueprint $table) {
                $table->dropColumn('atencion_fin_at');
            });
        }
    }
};
