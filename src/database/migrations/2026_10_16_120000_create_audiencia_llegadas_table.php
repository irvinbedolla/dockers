<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Llegada de las partes a su audiencia, que marca la recepción (Morelia 02)
 * en "Audiencias de hoy" (App\Support\LlegadasAudiencia).
 *
 * Un renglón por parte que llegó: el solicitante o cada citado. "clave"
 * identifica la parte dentro de la audiencia ('solicitante' o
 * 'citado:<hash del nombre>'): seer_citados se replica en cada reagenda y a
 * veces trae el mismo nombre dos veces, así que el id del renglón no sirve.
 *
 * No sustituye la comparecencia (seer_citados.comparecencia), que el
 * conciliador registra con identificación dentro de la audiencia.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audiencia_llegadas')) {
            return;
        }

        Schema::create('audiencia_llegadas', function (Blueprint $table) {
            $table->id();
            $table->integer('audiencia_id')->index();
            $table->string('clave', 60);
            $table->enum('parte', ['solicitante', 'citado']);
            $table->string('nombre', 255);
            $table->timestamp('llegada_at')->useCurrent();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();

            $table->unique(['audiencia_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiencia_llegadas');
    }
};
