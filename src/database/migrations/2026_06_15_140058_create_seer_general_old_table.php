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
        Schema::create('seer_general_old', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('fecha')->useCurrent();
            $table->date('fecha_confirmacion')->useCurrent();
            $table->string('NUE', 18);
            $table->text('solicitante');
            $table->integer('estado_solicitante');
            $table->integer('mun_solicitante');
            $table->integer('user_id');
            $table->integer('conciliador_id')->default(0);
            $table->string('citado', 50)->nullable();
            $table->integer('estado_citado')->nullable();
            $table->integer('mun_citado')->nullable();
            $table->enum('validado_conciliador', ['Pendiente', 'Guardado'])->default('Pendiente');
            $table->string('delegacion', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_general_old');
    }
};
