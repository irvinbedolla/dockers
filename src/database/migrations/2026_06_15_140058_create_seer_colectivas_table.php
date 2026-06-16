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
        Schema::create('seer_colectivas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('conciliador');
            $table->string('solicitante', 40);
            $table->date('fecha');
            $table->string('NUE', 18);
            $table->string('citado', 30);
            $table->string('juzgado', 40);
            $table->string('estado', 40);
            $table->string('delegacion', 10)->default('Morelia');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_colectivas');
    }
};
