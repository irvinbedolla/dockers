<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cita de solicitud patronal individual (App\Support\CitaPatronal): el
 * empleador agenda con su folio de representante patronal y dice a quién
 * va a citar, para prellenar "Soy patronal individual" en Mi módulo.
 *
 *   recepcion.poder_folio  abogados.idAbogado (folio de representante patronal)
 *   recepcion.citado       JSON con la persona trabajadora (nombre desglosado y,
 *                          si agendó el empleador, su CURP y domicilio)
 *   recepcion.calidad      quién agendó: 'trabajador' | 'empleador'
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'poder_folio')) {
                $table->unsignedInteger('poder_folio')->nullable()->index();
            }
            if (! Schema::hasColumn('recepcion', 'citado')) {
                $table->text('citado')->nullable();
            }
            if (! Schema::hasColumn('recepcion', 'calidad')) {
                $table->string('calidad', 12)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (Schema::hasColumn('recepcion', 'poder_folio')) {
                $table->dropIndex(['poder_folio']);
                $table->dropColumn('poder_folio');
            }
            if (Schema::hasColumn('recepcion', 'citado')) {
                $table->dropColumn('citado');
            }
            if (Schema::hasColumn('recepcion', 'calidad')) {
                $table->dropColumn('calidad');
            }
        });
    }
};
