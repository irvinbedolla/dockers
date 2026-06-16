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
        Schema::create('seer_conciliadores', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->string('numero_audiencia', 10)->nullable();
            $table->enum('estatus_conciliacion', ['Conciliacion', 'No conciliacion', 'Archivado por incomparecencia', 'Regenerada', 'Incompetencia', 'No conciliacion se reagenda', 'Reinstalacion', 'Desistimiento', 'Archivada en Audiencia']);
            $table->integer('numero_audiencias');
            $table->float('monto')->nullable();
            $table->text('cumplimiento_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('multa', ['Si', 'No']);
            $table->float('monto_multa')->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('NSS', 18)->nullable();
            $table->enum('tipo', ['Presencial', 'Virtual']);
            $table->enum('motivo_archivo', ['Incompetencia', 'Falta de interes'])->nullable();
            $table->date('fecha_reprogracion')->nullable();
            $table->date('fecha_conclucion')->nullable();
            $table->integer('consecutivo');
            $table->date('fecha')->useCurrent();
            $table->time('hora')->useCurrent();
            $table->text('resolicion_primera')->nullable();
            $table->text('resolicion_justificacion')->nullable();
            $table->text('resolicion_segunda')->nullable();
            $table->string('conclucion', 20)->nullable();
            $table->float('vacaciones')->nullable();
            $table->float('aguinaldo')->nullable();
            $table->float('otros')->nullable();
            $table->string('horario', 120)->nullable();
            $table->string('comida', 50)->nullable();
            $table->enum('tipo_audiencia', ['Presencial', 'Virtual']);
            $table->integer('audiencia_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_conciliadores');
    }
};
