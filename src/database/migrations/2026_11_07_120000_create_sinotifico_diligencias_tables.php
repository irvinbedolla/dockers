<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diligencias capturadas en campo con SíNotifico.
 *
 * Cada visita queda como un intento propio (con éxito o sin él), con su hora,
 * su ubicación y su evidencia. Nada de esto toca `seer_citados` hasta que un
 * supervisor lo valida: así se revisa antes de que llegue al expediente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinotifico_diligencias', function (Blueprint $t) {
            $t->id();
            // Lo genera el teléfono: si la misma captura llega dos veces, no se duplica.
            $t->uuid('uuid')->unique();
            $t->unsignedBigInteger('notificador_id')->index();
            $t->unsignedBigInteger('solicitud_id')->index();
            $t->string('sede', 40)->nullable()->index();
            $t->string('estatus', 20)->default('por_revisar')->index(); // por_revisar | validada | devuelta
            $t->string('resultado', 60)->nullable();
            $t->string('quien_atiende', 40);
            // Campos del formulario de notificación de SICONCILIO (ver Captura::CAMPOS).
            $t->json('datos');
            $t->dateTime('llegada_at');
            $t->dateTime('capturada_at');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->unsignedInteger('precision_m')->nullable();
            $t->string('dispositivo', 120)->nullable();
            $t->unsignedBigInteger('revisor_id')->nullable();
            $t->dateTime('revisada_at')->nullable();
            $t->text('comentario_revision')->nullable();
            $t->timestamps();
        });

        Schema::create('sinotifico_diligencia_citado', function (Blueprint $t) {
            $t->foreignId('diligencia_id')->constrained('sinotifico_diligencias')->cascadeOnDelete();
            $t->unsignedBigInteger('citado_id')->index();
            $t->primary(['diligencia_id', 'citado_id']);
        });

        Schema::create('sinotifico_evidencias', function (Blueprint $t) {
            $t->id();
            $t->foreignId('diligencia_id')->constrained('sinotifico_diligencias')->cascadeOnDelete();
            $t->string('tipo', 10); // foto | firma
            $t->unsignedTinyInteger('orden')->default(1);
            $t->string('ruta');
            $t->unsignedInteger('bytes')->default(0);
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->dateTime('tomada_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinotifico_evidencias');
        Schema::dropIfExists('sinotifico_diligencia_citado');
        Schema::dropIfExists('sinotifico_diligencias');
    }
};
