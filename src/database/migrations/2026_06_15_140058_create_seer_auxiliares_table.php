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
        Schema::create('seer_auxiliares', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->enum('sexo', ['H', 'M', 'No Binario', 'Prefiero no decir', 'Otro']);
            $table->enum('tipo_persona', ['Fisica', 'Moral'])->nullable();
            $table->enum('motivo', ['Despido', 'Pago de prestaciones', 'Recision de la relación laboral', 'Derecho de preferencia', 'Derecho de antiguedad', 'Derecho de ascesnso', 'Terminación voluntaria de relación laboral', 'Supuestos de Excepción 685-Ter LFT', 'Otros']);
            $table->float('monto')->nullable();
            $table->string('actividad_economica', 100);
            $table->enum('estatus', ['Pendiente', 'Parcial', 'Cumplido']);
            $table->enum('notificacion', ['Centro', 'Trabajador', 'Ambos', 'Exhorto']);
            $table->enum('tipo_solicitud', ['Solicitud', 'Ratificación']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_auxiliares');
    }
};
