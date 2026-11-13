<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Días bloqueados en el calendario de citas de una persona (Administración →
 * Módulos, en la tarjeta de su módulo): ese día no se le asignan citas ni en
 * línea ni de ventanilla, ni se le mueven citas (reasignar, reagendar).
 * Un renglón por persona y día. Ver App\Models\BloqueoCita.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bloqueos_citas')) {
            return;
        }

        Schema::create('bloqueos_citas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha');
            $table->string('motivo', 120)->nullable();
            $table->unsignedBigInteger('modulo_id')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos_citas');
    }
};
