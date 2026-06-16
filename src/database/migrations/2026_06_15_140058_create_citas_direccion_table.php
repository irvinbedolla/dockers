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
        Schema::create('citas_direccion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre', 50);
            $table->text('descripcion');
            $table->date('fecha');
            $table->time('hora');
            $table->time('fin');
            $table->string('estatus', 20);
            $table->string('delegacion', 20);
            $table->string('unidad', 30);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas_direccion');
    }
};
