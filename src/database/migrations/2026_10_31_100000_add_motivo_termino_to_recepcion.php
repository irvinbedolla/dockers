<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Por qué terminó la atención de una cita de Solicitud o Asesoría
 * (App\Support\MotivoTermino):
 *
 *   recepcion.motivo_termino        clave de MotivoTermino::OPCIONES
 *   recepcion.motivo_termino_texto  comentario (obligatorio con "Otra")
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'motivo_termino')) {
                $table->string('motivo_termino', 30)->nullable();
                $table->index(['fecha', 'motivo_termino']);
            }
            if (! Schema::hasColumn('recepcion', 'motivo_termino_texto')) {
                $table->string('motivo_termino_texto', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (Schema::hasColumn('recepcion', 'motivo_termino')) {
                $table->dropIndex(['fecha', 'motivo_termino']);
                $table->dropColumn('motivo_termino');
            }
            if (Schema::hasColumn('recepcion', 'motivo_termino_texto')) {
                $table->dropColumn('motivo_termino_texto');
            }
        });
    }
};
