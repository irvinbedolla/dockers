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
        Schema::create('capacitaciones_encuesta', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_cap');
            $table->integer('id_modulo');
            $table->string('pregunta', 50);
            $table->string('respuesta1', 50);
            $table->string('respuesta2', 50);
            $table->string('respuesta3', 50);
            $table->string('respuesta4', 50);
            $table->integer('correcta');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacitaciones_encuesta');
    }
};
