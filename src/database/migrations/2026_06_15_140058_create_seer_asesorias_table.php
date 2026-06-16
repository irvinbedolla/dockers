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
        Schema::create('seer_asesorias', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_usuario');
            $table->string('nombre', 50);
            $table->date('fecha');
            $table->string('sexo', 10);
            $table->string('delegacion', 30);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_asesorias');
    }
};
