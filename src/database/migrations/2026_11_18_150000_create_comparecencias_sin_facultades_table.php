<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que el conciliador captura para el formato "Comparecencia sin
 * Acreditación de Facultades" (/pdfsinPoder/{solicitud}): uno por citado,
 * se puede volver a editar las veces que haga falta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparecencias_sin_facultades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('solicitud_id')->index();     // seer_general.id
            $table->unsignedInteger('citado_id');                 // seer_citados.id
            $table->unsignedInteger('audiencia_id')->nullable();  // audiencias.id en la que se levantó
            $table->date('fecha_acta');
            $table->time('hora_acta');
            $table->string('compareciente', 200);
            $table->string('tipo_identificacion', 60);
            $table->string('num_identificacion', 50);
            $table->string('representa', 255);
            $table->string('documento', 20);                      // ninguno | insuficiente
            $table->text('documento_descripcion')->nullable();
            $table->date('fecha_notificacion')->nullable();
            $table->time('hora_notificacion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();     // quién lo capturó o editó por última vez
            $table->timestamps();

            $table->unique(['solicitud_id', 'citado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparecencias_sin_facultades');
    }
};
