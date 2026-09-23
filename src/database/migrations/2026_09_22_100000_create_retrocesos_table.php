<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de retrocesos: un renglón por cada retroceso aplicado.
     *
     * Lo que cada retroceso borró o modificó queda en retroceso_detalles.
     * NUE y delegacion se copian aquí para poder filtrar el historial sin
     * depender de que el expediente original siga existiendo.
     */
    public function up(): void
    {
        Schema::create('retrocesos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30);                     // ratificacion, audiencia, solicitud, cumplimiento
            $table->string('entidad_tipo', 64);             // tabla del registro principal (turnos, seer_general, pago_solicitud)
            $table->unsignedBigInteger('entidad_id');
            $table->string('NUE', 30)->nullable();
            $table->string('delegacion', 30)->nullable();
            $table->string('estatus_previo', 60)->nullable();
            $table->string('estatus_nuevo', 60)->nullable();
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nombre')->nullable();      // se conserva aunque el usuario se elimine
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index('NUE');
            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index(['tipo', 'created_at']);
            $table->index(['delegacion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retrocesos');
    }
};
