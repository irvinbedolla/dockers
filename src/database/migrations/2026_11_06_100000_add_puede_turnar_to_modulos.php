<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Casos de excepción:
 *
 *   modulos.puede_turnar          switch "Puede turnar" de los módulos de
 *                                 casos de excepción (Administración →
 *                                 Módulos). Apagado, quien atiende no pasa a
 *                                 la persona a un orientador o auxiliar:
 *                                 si no es caso de excepción, levanta la
 *                                 solicitud ella misma.
 *   seer_casos_excepcion.siguiente qué se hizo al cerrar como "No es caso de
 *                                 excepción" (hoy, otra_fecha, levantar,
 *                                 terminar). Con "levantar" el caso queda en
 *                                 "Solicitudes por levantar" hasta que la
 *                                 solicitud se termina y se liga a la cita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'puede_turnar')) {
                $table->boolean('puede_turnar')->default(true);
            }
        });
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('seer_casos_excepcion', 'siguiente')) {
                $table->string('siguiente', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (Schema::hasColumn('modulos', 'puede_turnar')) {
                $table->dropColumn('puede_turnar');
            }
        });
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            if (Schema::hasColumn('seer_casos_excepcion', 'siguiente')) {
                $table->dropColumn('siguiente');
            }
        });
    }
};
