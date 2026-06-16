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
        Schema::create('capacitaciones_modulo', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_cap');
            $table->integer('id_modulo');
            $table->text('nombre');
            $table->text('introduccion');
            $table->text('desarrollo');
            $table->enum('estatus', ['Pendiente', 'Termiando', 'Activo'])->default('Pendiente');
            $table->text('anexo1')->nullable();
            $table->text('anexo2')->nullable();
            $table->text('anexo3')->nullable();
            $table->text('anexo4')->nullable();
            $table->text('anexo5')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacitaciones_modulo');
    }
};
