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
        Schema::create('segundo_encuentro', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('correo', 50);
            $table->string('nombre', 50);
            $table->string('estado', 30);
            $table->string('celular', 14);
            $table->enum('genero', ['Hombre', 'Mujer']);
            $table->enum('estatus', ['Validado', 'Pendiente'])->default('Pendiente');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segundo_encuentro');
    }
};
