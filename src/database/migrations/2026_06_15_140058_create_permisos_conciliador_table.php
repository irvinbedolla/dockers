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
        Schema::create('permisos_conciliador', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_conciliador');
            $table->enum('tipo', ['Precencial', 'Virtual', 'Ambos', '']);
            $table->enum('lunes', ['Si', 'No']);
            $table->enum('martes', ['Si', 'No']);
            $table->enum('miercoles', ['Si', 'No']);
            $table->enum('jueves', ['Si', 'No']);
            $table->enum('viernes', ['Si', 'No']);
            $table->time('lunes_inicio');
            $table->time('lunes_final');
            $table->time('martes_inicio');
            $table->time('martes_final');
            $table->time('miercoles_inicio');
            $table->time('miercoles_final');
            $table->time('jueves_inicio');
            $table->time('jueves_final');
            $table->time('viernes_inicio');
            $table->time('viernes_final');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_conciliador');
    }
};
