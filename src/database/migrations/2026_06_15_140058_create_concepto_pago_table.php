<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('concepto_pago', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->double('monto');
            $table->text('descripcion');
            $table->enum('tipo_pago', ['Ratificacion', 'Audiencia']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concepto_pago');
    }
};
