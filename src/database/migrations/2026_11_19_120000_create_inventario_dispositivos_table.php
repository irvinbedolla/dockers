<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventario de dispositivos (Administración → Inventario, sólo Super Usuario).
 * Un renglón por equipo. Mientras no esté verificado se puede corregir en la
 * misma tabla; ya verificado queda bloqueado hasta que se le quite el check.
 * Los cambios van a historial_administracion (tabla = inventario_dispositivos).
 * Los textos se guardan en mayúsculas (InventarioController::validar).
 * Ver App\Models\InventarioDispositivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventario_dispositivos')) {
            return;
        }

        Schema::create('inventario_dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 120);                 // qué es: computadora, laptop, router, impresora…
            $table->string('marca', 80);
            $table->string('modelo', 120);
            $table->string('numero_serie', 120)->nullable()->unique();
            $table->enum('estado_uso', ['bueno', 'regular', 'malo'])->default('bueno');
            $table->text('razon_malo')->nullable();             // obligatoria cuando estado_uso = malo
            $table->string('ubicacion', 150);
            $table->foreignId('resguardante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resguardante_nombre', 191)->nullable(); // se conserva aunque el usuario se elimine
            $table->string('resguardante_puesto', 150)->nullable();
            $table->text('caracteristicas')->nullable();
            $table->text('observaciones')->nullable();

            $table->boolean('verificado')->default(false);
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_at')->nullable();

            $table->boolean('revisar')->default(false);
            $table->string('revisar_nota', 500)->nullable();

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('descripcion');
            $table->index('verificado');
            $table->index('revisar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_dispositivos');
    }
};
