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
        Schema::create('turno_disponible', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_auxiliar');
            $table->date('fecha');
            $table->time('hora');
            $table->enum('estatus', ['Disponible', 'Ocupado']);
            $table->string('delegacion_turno', 30)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_disponible');
    }
};
