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
        // 1. Eliminar de forma segura las columnas viejas solo si existen
        $columnasAEliminar = [
            'id_solicitud', 
            'frecuencia_hechos', 
            'cambios_situacionL', 
            'comunico_hechos',
            'descripcion_conducta',  // <--- Agregado en singular (la causante del error)
            'descripcion_conductas', // <--- Mantenido en plural por precaución
            'responsable_cargo', 
            'actos_cometidos', 
            'momento_hechos', 
            'lugar_hechos', 
            'constancia_hechos', 
            'solicito_apoyo', 
            'continuacion_solicitud', 
            'incidencia_directa', 
            'recibio_atencion'
        ];

        foreach ($columnasAEliminar as $columna) {
            if (Schema::hasColumn('seer_casos_excepcion', $columna)) {
                Schema::table('seer_casos_excepcion', function (Blueprint $table) use ($columna) {
                    $table->dropColumn($columna);
                });
            }
        }

        // 2. Agregar los nuevos campos solo si no existen previamente
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            if (!Schema::hasColumn('seer_casos_excepcion', 'id_turno')) {
                $table->unsignedBigInteger('id_turno')->nullable()->after('id');
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable()->after('id_turno');
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'tipo_caso')) {
                $table->string('tipo_caso')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'motivos')) {
                $table->string('motivos')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'vulnerables')) {
                $table->string('vulnerables')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'frecuencia')) {
                $table->string('frecuencia')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'situacion_laboral')) {
                $table->string('situacion_laboral')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'dependencia')) {
                $table->string('dependencia')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'expediente')) {
                $table->string('expediente')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'descripcion_persona')) {
                $table->text('descripcion_persona')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'descripcion_conductas')) { // Se recrea con el tipo Text y nombre correcto
                $table->text('descripcion_conductas')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'observaciones')) {
                $table->text('observaciones')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'jefe_inmediato')) {
                $table->string('jefe_inmediato')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'ubicacion')) {
                $table->string('ubicacion')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'empresa')) {
                $table->string('empresa')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'puesto')) {
                $table->string('puesto')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'area_adscripcion')) {
                $table->string('area_adscripcion')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'fecha')) {
                $table->date('fecha')->nullable();
            }
            if (!Schema::hasColumn('seer_casos_excepcion', 'hora')) {
                $table->time('hora')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seer_casos_excepcion', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'id_turno', 'id_user', 'tipo_caso', 'motivos', 'vulnerables', 
                'frecuencia', 'situacion_laboral', 'dependencia', 'expediente', 
                'descripcion_persona', 'descripcion_conductas', 'observaciones', 
                'jefe_inmediato', 'ubicacion', 'empresa', 'puesto', 
                'area_adscripcion', 'fecha', 'hora'
            ]);

            // Restaurar los campos originales (incluyendo el singular correcto)
            $table->integer('id_solicitud')->nullable();
            $table->string('frecuencia_hechos', 30)->nullable();
            $table->string('cambios_situacionL', 30)->nullable();
            $table->string('comunico_hechos', 250)->nullable();
            $table->string('descripcion_conducta', 250)->nullable(); // <--- Corregido a singular en el down()
            $table->string('responsable_cargo', 200)->nullable();
            $table->string('actos_cometidos', 200)->nullable();
            $table->string('momento_hechos', 200)->nullable();
            $table->string('lugar_hechos', 200)->nullable();
            $table->string('constancia_hechos', 50)->nullable();
            $table->enum('solicito_apoyo', ['Si', 'No'])->nullable();
            $table->string('continuacion_solicitud', 200)->nullable();
            $table->string('incidencia_directa', 50)->nullable();
            $table->string('recibio_atencion', 50)->nullable();
        });
    }
};