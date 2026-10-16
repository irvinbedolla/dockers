<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de ediciones de ratificaciones guardadas desde
     * "Edición ratificación" (TurnosController@guardarEdicion_citas):
     * un renglón por cada guardado que cambió al menos un dato.
     * estatus guarda el estatus de la ratificación antes de la edición.
     * Lo que cambió queda en historial_ediciones_ratificaciones.
     */
    public function up(): void
    {
        Schema::create('historial_ratificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ratificacion_id');
            $table->string('NUE', 30)->nullable();
            $table->string('delegacion', 30)->nullable();
            $table->string('estatus')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nombre')->nullable();      // se conserva aunque el usuario se elimine
            $table->string('user_rol', 60)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index('ratificacion_id');
            $table->index('NUE');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_ratificaciones');
    }
};