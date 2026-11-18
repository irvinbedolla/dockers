<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de la solicitud patronal de ratificación múltiple (formato
 * institucional): quién la registra, motivo, fecha propuesta, cuántos
 * trabajadores declaró, observaciones, la constancia de situación fiscal
 * (opcional) y la aceptación de la declaración bajo protesta.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ratificaciones_lotes') || Schema::hasColumn('ratificaciones_lotes', 'motivo')) {
            return;
        }

        Schema::table('ratificaciones_lotes', function (Blueprint $table) {
            $table->string('solicitante', 150)->nullable()->after('empresa');
            $table->string('motivo', 40)->nullable()->after('solicitante');
            $table->date('fecha_propuesta')->nullable()->after('motivo');
            $table->unsignedSmallInteger('trabajadores_declarados')->nullable()->after('trabajadores');
            $table->text('observaciones')->nullable()->after('trabajadores_declarados');
            $table->string('constancia_fiscal', 255)->nullable()->after('archivo_nombre');
            $table->timestamp('declaracion_at')->nullable()->after('constancia_fiscal');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('ratificaciones_lotes', 'motivo')) {
            return;
        }

        Schema::table('ratificaciones_lotes', function (Blueprint $table) {
            $table->dropColumn(['solicitante', 'motivo', 'fecha_propuesta', 'trabajadores_declarados', 'observaciones', 'constancia_fiscal', 'declaracion_at']);
        });
    }
};
