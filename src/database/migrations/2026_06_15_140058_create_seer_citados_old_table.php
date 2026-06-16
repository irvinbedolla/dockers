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
        Schema::create('seer_citados_old', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->date('fecha')->nullable();
            $table->string('nombre', 300);
            $table->integer('id_municipio')->nullable();
            $table->integer('id_estado')->nullable();
            $table->string('direccion', 300)->nullable();
            $table->integer('id_notificador')->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estatus', ['Notificada', 'No notificada', 'Pendiente', 'Persona', 'Puerta', 'Constituye', 'Amparo', 'Juez'])->default('Pendiente');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_citados_old');
    }
};
