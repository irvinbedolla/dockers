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
        Schema::create('oficialia', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id')->default(0);
            $table->integer('oficio_id');
            $table->enum('delegacion', ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'])->default('Morelia');
            $table->text('tipo_tramite');
            $table->string('oficio',20)->default('');
            $table->string('area_turno', 50);
            $table->string('precedencia', 50);
            $table->integer('usuario_responsable')->default(0);
            $table->date('fecha');
            $table->time('hora');
            $table->date('fecha_registro')->nullable();
            $table->time('hora_registro')->nullable();
            $table->date('fecha_turno')->nullable();
            $table->time('hora_turno')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->time('hora_termino')->nullable();
            $table->enum('estatus',['creado', 'turnado', 'concluido'])->default('creado');
            $table->text('ruta_oficio')->nullable();
            $table->text('conclusion')->nullable();
            $table->boolean('termino')->nullable();
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oficialia');
    }
};
