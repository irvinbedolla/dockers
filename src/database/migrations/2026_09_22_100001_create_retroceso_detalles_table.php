<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto de cada registro que un retroceso borró o modificó.
     *
     * - deleted: datos_antes guarda el renglón completo tal como estaba en BD,
     *   por lo que puede reinsertarse sin transformaciones.
     * - updated: datos_antes / datos_despues guardan solo las columnas que
     *   cambiaron.
     */
    public function up(): void
    {
        Schema::create('retroceso_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retroceso_id')->constrained('retrocesos')->cascadeOnDelete();
            $table->string('tabla', 64);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->enum('accion', ['deleted', 'updated']);
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->timestamps();

            $table->index(['tabla', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retroceso_detalles');
    }
};
