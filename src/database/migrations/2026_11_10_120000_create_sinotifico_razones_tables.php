<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo 3: razones de notificación.
 *
 * El PDF lo sigue generando SICONCILIO con sus plantillas (SeerController);
 * aquí sólo se guarda la firma del notificador y si cada razón ya se firmó,
 * en la app (la firma se estampa sobre la línea del PDF) o en papel.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Firma autógrafa del notificador, dibujada una vez en la app.
        Schema::create('sinotifico_firmas', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->string('ruta'); // disco local; cada cambio es un archivo nuevo
            $t->timestamps();
        });

        // Una por citado: la razón que resulta de su última diligencia validada.
        Schema::create('sinotifico_razones', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('citado_id')->unique();
            $t->unsignedBigInteger('diligencia_id')->index();
            $t->unsignedBigInteger('notificador_id')->index();
            $t->string('estatus', 12)->default('por_firmar'); // por_firmar | firmada
            $t->string('modo', 6)->nullable(); // app | papel
            $t->dateTime('firmada_at')->nullable();
            $t->string('firma_ruta')->nullable(); // la firma tal como estaba al firmar
            $t->string('papel_ruta')->nullable(); // foto opcional de la razón firmada en papel
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinotifico_razones');
        Schema::dropIfExists('sinotifico_firmas');
    }
};
