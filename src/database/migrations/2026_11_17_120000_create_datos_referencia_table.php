<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de referencia del Reporte estadístico: cifras que no salen del
 * SICONCILIO (histórico capturado, JLCA, presupuesto, PEA, conciliadores por
 * estado, población por delegación y textos). Un renglón por dato del
 * catálogo App\Support\DatosReferencia; el valor va en JSON. Si un dato no
 * tiene renglón, el reporte usa el valor inicial del catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('datos_referencia')) {
            return;
        }

        Schema::create('datos_referencia', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique();
            $table->longText('valor');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_referencia');
    }
};
