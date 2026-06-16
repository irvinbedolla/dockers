<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seer_general', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('consecutivo')->nullable();
            $table->integer('año');
            $table->date('fecha')->useCurrent();
            $table->date('fecha_conflicto')->nullable();
            $table->date('fecha_confirmacion')->nullable();
            $table->date('fecha_terminacion')->nullable();
            $table->string('NUE', 18)->nullable();
            $table->integer('id_rama');
            $table->text('actividad');
            $table->integer('user_id')->nullable();
            $table->integer('conciliador_id')->nullable();
            $table->enum('validado_conciliador', ['Pendiente', 'Guardado'])->default('Pendiente');
            $table->enum('delegacion', ['Morelia', 'Zamora', 'Uruapan', 'Zitácuaro', 'Sahuayo', 'Lázaro Cárdenas']);
            $table->string('curp', 18)->nullable();
            $table->enum('tipo', ['Presencial', 'Virtual']);
            $table->enum('tipo_solicitud', ['1', '2', '3', '4'])->nullable();
            $table->integer('validacion')->default(0);
            $table->enum('estatus', ['Pendiente', 'Aceptado', 'Confirmado', 'Concluida', 'Reagendada', 'Incompetencia', 'Incomparecencia', 'Archivada', 'No conciliacion', 'Conciliacion', 'Incumplimiento', 'Rechazado', 'Prevencion', 'Reinstalacion', 'Desistimiento'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->enum('caso_excepcion', ['Si', 'No'])->default('No');
            $table->text('documentoExpediente')->nullable();
            $table->enum('pendiente_firma', ['Si', 'No'])->default('No');
            $table->integer('tipo_generacion');
            $table->boolean('incidencia')->nullable();
            $table->text('motivo_incidencia')->nullable();
            $table->integer('delegado_id')->nullable();
            $table->integer('poder_id')->nullable();
            $table->integer('confirmacion_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_general');
    }
};
