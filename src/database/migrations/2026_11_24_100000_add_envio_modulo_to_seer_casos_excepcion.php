<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Enviar a casos de excepción" desde Mi módulo: el orientador o auxiliar que
 * atiende una cita deja un borrador del formato 685 Ter ligado a la cita
 * (id_turno) para que el Departamento de Conciliación, Género e Igualdad lo
 * continúe (FormatoExcepcion::enviarDesdeModulo).
 *
 *   enviado_por   quién lo envió (users.id). No es id_user: ése es quien atendió
 *                 el caso y sale en el PDF; lo pone el departamento al guardar.
 *   enviado_at    cuándo.
 *   nota_envio    nota del orientador para el departamento (no va en el formato).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('seer_casos_excepcion', 'enviado_por')) {
                $table->unsignedBigInteger('enviado_por')->nullable();
            }
            if (! Schema::hasColumn('seer_casos_excepcion', 'enviado_at')) {
                $table->timestamp('enviado_at')->nullable();
            }
            if (! Schema::hasColumn('seer_casos_excepcion', 'nota_envio')) {
                $table->text('nota_envio')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            foreach (['enviado_por', 'enviado_at', 'nota_envio'] as $columna) {
                if (Schema::hasColumn('seer_casos_excepcion', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
