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
        Schema::create('seer_casos_excepcion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->string('frecuencia_hechos', 30);
            $table->string('cambios_situacionL', 30);
            $table->string('comunico_hechos', 250);
            $table->string('descripcion_conducta', 250);
            $table->string('responsable_cargo', 200);
            $table->string('actos_cometidos', 200);
            $table->string('momento_hechos', 200);
            $table->string('lugar_hechos', 200);
            $table->string('constancia_hechos', 50);
            $table->enum('solicito_apoyo', ['Si', 'No'])->default('No');
            $table->string('continuacion_solicto_apoyo', 200)->nullable();
            $table->string('incidencia_directa', 50);
            $table->string('recibio_atencion', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_casos_excepcion');
    }
};
