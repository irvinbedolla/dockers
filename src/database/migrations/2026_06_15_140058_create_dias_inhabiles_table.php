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
        Schema::create('dias_inhabiles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->time('horario_inicio')->nullable();
            $table->time('horario_final')->nullable();
            $table->string('centro', 20);
            $table->integer('user_id')->nullable();
            $table->enum('tipo', ['Cumplimientos', 'Audiencias', 'Ratificaciones', 'Todos', 'Bloqueo por permiso'])->nullable();
            $table->enum('descripcion', ['Inhabil', 'No inhabil'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_inhabiles');
    }
};
