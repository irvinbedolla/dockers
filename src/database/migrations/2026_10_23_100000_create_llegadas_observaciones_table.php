<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observaciones de "Llegadas hoy" (App\Support\LlegadasHoy::guardarObservaciones):
 * las anota la recepción y las ve el conciliador (también en su aviso).
 *
 *   audiencia     silla de ruedas, traductor, menor de edad y texto libre;
 *   ratificacion  sólo texto libre.
 *
 * Una fila por registro (tipo + registro_id). Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('llegadas_observaciones')) {
            return;
        }

        Schema::create('llegadas_observaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->unsignedBigInteger('registro_id');
            $table->boolean('silla_ruedas')->default(false);
            $table->boolean('traductor')->default(false);
            $table->boolean('menor_edad')->default(false);
            $table->text('texto')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->timestamps();
            $table->unique(['tipo', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llegadas_observaciones');
    }
};
