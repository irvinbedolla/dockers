<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada registro que una edición creó, modificó o eliminó.
     *
     * - created: datos_despues guarda el renglón completo.
     * - updated: datos_antes / datos_despues guardan solo las columnas que cambiaron.
     * - deleted: datos_antes guarda el renglón completo.
     */
    public function up(): void
    {
        Schema::create('historial_edicion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historial_edicion_id')->constrained('historial_ediciones')->cascadeOnDelete();
            $table->string('tabla', 64);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->enum('accion', ['created', 'updated', 'deleted']);
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->timestamps();

            $table->index(['tabla', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_edicion_detalles');
    }
};
