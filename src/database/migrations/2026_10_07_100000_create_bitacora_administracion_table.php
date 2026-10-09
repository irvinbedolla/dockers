<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_administracion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 40);                     // borrar_cumplimiento, cambio_fecha_audiencia, cambio_fecha_cumplimiento
            $table->string('tabla', 64);
            $table->unsignedBigInteger('registro_id');
            $table->string('NUE', 30)->nullable();
            $table->string('delegacion', 30)->nullable();
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->text('motivo')->nullable();             // obligatorio desde la pantalla y el controlador
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nombre')->nullable();      // se conserva aunque el usuario se elimine
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
            $table->index('NUE');
            $table->index(['tabla', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_administracion');
    }
};
