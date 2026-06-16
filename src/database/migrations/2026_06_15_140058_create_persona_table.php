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
        Schema::create('persona', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_usuario');
            $table->string('nombre', 50);
            $table->string('email', 25);
            $table->string('cargo', 50);
            $table->string('area_adcripcion', 50);
            $table->string('telefono', 10);
            $table->text('estudio_maximo');
            $table->text('tilulo_universitario');
            $table->text('especialidades')->nullable();
            $table->text('diplomados')->nullable();
            $table->text('seminarios')->nullable();
            $table->text('cursos')->nullable();
            $table->text('acciones_desarrollo')->nullable();
            $table->enum('estatus', ['Aceptado', 'Rechazado', 'Pendiente'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
