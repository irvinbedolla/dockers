<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Última señal de vida de la app de cada notificador. La app avisa cada
 * dos minutos mientras está abierta y con señal, y al terminar de enviar
 * capturas; con eso Rutas muestra "en línea" y lo que tiene sin enviar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinotifico_presencia', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->dateTime('visto_at');
            $t->dateTime('sincronizado_at')->nullable(); // último envío de capturas sin pendientes
            $t->unsignedSmallInteger('en_cola')->default(0); // capturas guardadas en el teléfono sin enviar
            $t->string('version', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinotifico_presencia');
    }
};
