<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notificaciones → Exhortos: registro de los exhortos que se mandan para
 * notificar a citados fuera del alcance de la sede (otro estado o
 * municipio), y si ya se contestaron.
 *
 * id_solicitud / id_citado ligan el exhorto al expediente y al citado cuando
 * existe en el sistema; NUE, solicitante y citado se guardan como texto para
 * que el registro se lea igual aunque el expediente cambie (o no exista aquí).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exhortos')) {
            return;
        }

        Schema::create('exhortos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud')->nullable()->index();
            $table->unsignedBigInteger('id_citado')->nullable()->index();
            $table->string('NUE', 60)->index();
            $table->string('delegacion', 60)->nullable()->index();
            $table->string('solicitante', 255);
            $table->text('citado');
            $table->unsignedInteger('estado_id')->nullable()->index();
            $table->unsignedInteger('municipio_id')->nullable();
            $table->boolean('contestado')->default(false)->index();
            $table->date('fecha_contestacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhortos');
    }
};
