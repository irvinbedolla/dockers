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
        Schema::create('recepcion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->time('hora');
            $table->time('hora_fin')->nullable()->useCurrent();
            $table->integer('auxiliar');
            $table->enum('tipo', ['Solicitud', 'Ratificación', 'Cumplimiento']);
            $table->string('lugar_auxiliar', 40);
            $table->enum('exepcion', ['Si', 'No'])->nullable()->default('No');
            $table->integer('edad')->nullable();
            $table->enum('sexo', ['H', 'M', 'NB', 'LGBTTTIQ'])->nullable();
            $table->text('tipo_caso')->nullable();
            $table->text('prestacionSS')->nullable();
            $table->text('vulnerables');
            $table->text('conflicto')->nullable();
            $table->char('solicitante', 100)->nullable();
            $table->enum('estatus', ['atendido', 'no atendido'])->default('no atendido');
            $table->enum('orientacion', ['Si', 'No']);
            $table->string('delegacion', 30);
            $table->string('folio', 20)->nullable();
            $table->text('tarjeta')->nullable();
            $table->string('INS', 24)->nullable();
            $table->string('resultado', 20)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepcion');
    }
};
