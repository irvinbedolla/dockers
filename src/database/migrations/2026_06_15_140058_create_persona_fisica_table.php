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
        Schema::create('persona_fisica', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->integer('id_citado');
            $table->string('nombre', 50);
            $table->string('primer_apellido', 50);
            $table->string('segundo_apellido', 50);
            $table->enum('identificacion', ['ine', 'pasaporte', 'cedula', 'licencia', 'otros']);
            $table->text('documentoIdentificacion')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_fisica');
    }
};
