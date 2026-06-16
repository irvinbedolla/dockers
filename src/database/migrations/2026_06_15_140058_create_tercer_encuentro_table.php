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
        Schema::create('tercer_encuentro', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();
            $table->string('nombre', 60);
            $table->string('correo', 60);
            $table->string('telefono', 10);
            $table->string('lugar', 60);
            $table->string('sexo', 20);
            $table->string('estatus', 30);
            $table->string('convesatorio1', 100)->nullable()->comment('10:20 - 11:00 h. Conferencia Inaugural: “Implementación del Mecanismo
                                                            Laboral de Respuesta Rápida (MLRR) del T- MEC”');
            $table->string('convesatorio2', 120)->nullable();
            $table->string('convesatorio3', 150)->nullable();
            $table->string('convesatorio4', 140)->nullable();
            $table->string('convesatorio5', 150)->nullable();
            $table->string('convesatorio6', 100)->nullable();
            $table->string('convesatorio7', 100)->nullable();
            $table->string('convesatorio8', 100)->nullable();
            $table->string('convesatorio9', 100)->nullable();
            $table->string('convesatorio10', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tercer_encuentro');
    }
};
