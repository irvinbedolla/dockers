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
        Schema::create('audiencias', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->integer('numero_audiencia');
            $table->string('folio_audiencia', 9);
            $table->date('fecha');
            $table->date('proxima_audiencia')->useCurrent();
            $table->time('hora');
            $table->integer('id_conciliador');
            $table->string('sala', 10);
            $table->string('delegacion', 20);
            $table->enum('estatus', ['Pendiente', 'Conciliacion', 'No conciliacion', 'Reagendada', 'Archivada', 'No conciliacion reagendada', 'Incompetencia', 'Reinstalacion', 'Desistimiento', 'Archivada en Audiencia'])->nullable();
            $table->float('pena_convencional', 11)->nullable();
            $table->text('direccion_convenio')->nullable();
            $table->boolean('incidencia')->nullable();
            $table->integer('poder_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiencias');
    }
};
