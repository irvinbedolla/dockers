<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo 4: rutas del día y catálogo de domicilios ubicados.
 *
 * - sinotifico_domicilios: cada domicilio distinto (normalizado) con su
 *   ubicación. Empieza con la de OpenStreetMap, se corrige a mano en el mapa
 *   y se confirma con el GPS de las diligencias exitosas: así se arma el
 *   catálogo de domicilios validados en campo.
 * - sinotifico_rutas / _paradas: la ruta de un notificador para un día. La
 *   app sólo la muestra cuando un supervisor la aprueba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinotifico_domicilios', function (Blueprint $t) {
            $t->id();
            $t->char('clave', 40)->unique(); // sha1 del domicilio normalizado
            $t->string('texto', 500);
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->string('fuente', 12)->nullable(); // osm | manual | campo
            $t->boolean('validado_en_campo')->default(false);
            $t->unsignedInteger('notificaciones_exitosas')->default(0);
            $t->dateTime('verificado_at')->nullable();
            $t->dateTime('geocodificado_at')->nullable(); // último intento con OpenStreetMap
            $t->unsignedBigInteger('actualizado_por')->nullable();
            $t->timestamps();
        });

        Schema::create('sinotifico_rutas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('notificador_id');
            $t->date('fecha');
            $t->string('sede', 40)->nullable()->index();
            $t->string('estatus', 12)->default('borrador'); // borrador | aprobada
            $t->decimal('distancia_km', 8, 2)->default(0);
            $t->unsignedSmallInteger('minutos_estimados')->default(0);
            $t->unsignedBigInteger('creada_por')->nullable();
            $t->unsignedBigInteger('editada_por')->nullable();
            $t->unsignedBigInteger('aprobada_por')->nullable();
            $t->dateTime('aprobada_at')->nullable();
            $t->timestamps();
            $t->unique(['notificador_id', 'fecha']);
        });

        Schema::create('sinotifico_ruta_paradas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruta_id')->constrained('sinotifico_rutas')->cascadeOnDelete();
            $t->unsignedSmallInteger('orden');
            // La visita se identifica, como en la app, por el citado de id menor.
            $t->unsignedBigInteger('visita_id');
            $t->unsignedBigInteger('solicitud_id');
            $t->json('citados');
            // Lo que se mostraba al armarla (expediente, citado, audiencia, plazo), por si la visita
            // deja de estar pendiente durante el día.
            $t->json('resumen');
            $t->foreignId('domicilio_id')->nullable()->constrained('sinotifico_domicilios')->nullOnDelete();
            $t->timestamps();
            $t->index(['ruta_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinotifico_ruta_paradas');
        Schema::dropIfExists('sinotifico_rutas');
        Schema::dropIfExists('sinotifico_domicilios');
    }
};
