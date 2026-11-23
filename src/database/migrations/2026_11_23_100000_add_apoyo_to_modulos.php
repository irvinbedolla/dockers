<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulos de apoyo y los dos switches del formulario de Administración → Módulos:
 *
 *   modulos.apoyo           Módulo de apoyo: no se le asignan citas nuevas (ni en
 *                           línea ni internas), pero sí se le pueden reasignar
 *                           citas ya registradas (Módulos, Turnos → Todos). Sólo
 *                           solicitudes y ratificaciones: al moverle una cita se
 *                           revisa que no se encime con otra suya.
 *   modulos.asigna_citas    Switch "Asignar en línea": se le asignan citas nuevas,
 *                           del formulario público y las que se generan de manera
 *                           interna (ventanilla). Normal y excepción: encendido;
 *                           apoyo: apagado.
 *   modulos.acepta_retorno  Switch "Retornar": se le pueden reasignar citas que ya
 *                           existen. Normal y apoyo: encendido; excepción: apagado.
 *
 * Los módulos que ya existen quedan con los dos encendidos: se comportan igual
 * que antes hasta que alguien los edite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            if (! Schema::hasColumn('modulos', 'apoyo')) {
                $table->boolean('apoyo')->default(false);
            }
            if (! Schema::hasColumn('modulos', 'asigna_citas')) {
                $table->boolean('asigna_citas')->default(true);
            }
            if (! Schema::hasColumn('modulos', 'acepta_retorno')) {
                $table->boolean('acepta_retorno')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            foreach (['apoyo', 'asigna_citas', 'acepta_retorno'] as $columna) {
                if (Schema::hasColumn('modulos', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
