<?php

use App\Services\Sinotifico\HistorialRutas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de rutas: qué pasó en cada parada y bitácora de cada ruta
 * (ver App\Services\Sinotifico\HistorialRutas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sinotifico_ruta_paradas', function (Blueprint $t) {
            $t->unsignedBigInteger('diligencia_id')->nullable()->index()->after('domicilio_id');
            $t->dateTime('llegada_at')->nullable()->after('diligencia_id');
            $t->string('resultado', 60)->nullable()->after('llegada_at');
            $t->string('revision', 12)->nullable()->after('resultado'); // por_revisar | validada | devuelta
        });

        Schema::create('sinotifico_ruta_eventos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruta_id')->constrained('sinotifico_rutas')->cascadeOnDelete();
            $t->string('tipo', 20); // propuesta | editada | ordenada | aprobada
            $t->unsignedBigInteger('user_id')->nullable();
            $t->json('detalle')->nullable();
            $t->dateTime('created_at');
        });

        // Lo ya capturado antes de esta migración también entra al historial.
        HistorialRutas::reconstruir();
    }

    public function down(): void
    {
        Schema::dropIfExists('sinotifico_ruta_eventos');
        Schema::table('sinotifico_ruta_paradas', function (Blueprint $t) {
            $t->dropIndex(['diligencia_id']);
            $t->dropColumn(['diligencia_id', 'llegada_at', 'resultado', 'revision']);
        });
    }
};
