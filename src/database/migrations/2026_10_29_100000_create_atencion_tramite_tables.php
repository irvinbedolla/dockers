<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formulario de atención del trámite de conciliación (/atencion-tramite-conciliacion).
 *
 *   atencion_tramite         una respuesta por solicitud y parte (trabajadora o
 *                            empleadora), con una copia de los datos de la
 *                            solicitud al momento de contestar (sede, quién la
 *                            capturó, conciliador, resultado…) para que las
 *                            estadísticas no cambien si después se corrige el caso.
 *   atencion_tramite_envios  a quién se le mandó por correo el folio y la liga.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('atencion_tramite')) {
            Schema::create('atencion_tramite', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_solicitud')->index();
                $table->string('folio', 40)->index();
                $table->string('parte', 12);                  // trabajadora | empleadora
                foreach (range(1, 7) as $n) {
                    $table->string('p'.$n, 20)->nullable();
                    $table->text('c'.$n)->nullable();
                }
                // Copia de la solicitud al contestar.
                $table->string('delegacion', 40)->nullable()->index();
                $table->unsignedTinyInteger('tipo_solicitud')->nullable();
                $table->date('fecha_solicitud')->nullable();
                $table->date('fecha_conclusion')->nullable();
                $table->string('resultado', 30)->nullable();  // seer_general.estatus
                $table->unsignedBigInteger('capturo_id')->nullable()->index();
                $table->unsignedBigInteger('conciliador_id')->nullable()->index();
                $table->unsignedSmallInteger('audiencias')->nullable();
                $table->boolean('caso_excepcion')->default(false);
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                // Seguimiento de alertas (presión al firmar, calificaciones malas).
                $table->string('seguimiento', 15)->nullable(); // pendiente | atendida
                $table->text('seguimiento_nota')->nullable();
                $table->unsignedBigInteger('seguimiento_por')->nullable();
                $table->timestamp('seguimiento_at')->nullable();
                $table->timestamps();
                $table->unique(['id_solicitud', 'parte']);
            });
        }

        if (! Schema::hasTable('atencion_tramite_envios')) {
            Schema::create('atencion_tramite_envios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_solicitud')->index();
                $table->string('parte', 12)->nullable();
                $table->string('correo', 150);
                $table->timestamp('enviado_at')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
                $table->unique(['id_solicitud', 'correo']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atencion_tramite_envios');
        Schema::dropIfExists('atencion_tramite');
    }
};
