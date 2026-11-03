<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de ediciones guardadas desde "Revisar audiencia"
     * (SeerController@audiencia_confirmar): un renglón por cada guardado
     * que cambió al menos un dato. Lo que cambió queda en
     * historial_edicion_detalles.
     */
    public function up(): void
    {
        Schema::create('historial_ediciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('audiencia_id')->nullable();
            $table->string('NUE', 30)->nullable();
            $table->string('delegacion', 30)->nullable();
            $table->string('origen', 20)->nullable();       // Solicitud o Audiencia, según desde dónde se editó
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nombre')->nullable();      // se conserva aunque el usuario se elimine
            $table->string('user_rol', 60)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index('solicitud_id');
            $table->index('NUE');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_ediciones');
    }
};
