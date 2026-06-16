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
        Schema::create('turnos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('consecutivo');
            $table->year('año')->nullable()->default(null);
            $table->date('fecha');
            $table->time('hora');
            $table->time('hora_fin')->nullable();
            $table->integer('auxiliar');
            $table->enum('tipo', ['Solicitud', 'Ratificación'])->default('Ratificación');
            $table->string('lugar_auxiliar', 50);
            $table->enum('exepcion', ['Si', 'No'])->default('No');
            $table->integer('edad')->nullable();
            $table->enum('sexo', ['H', 'M', 'NB', 'LGBTTTIQ']);
            $table->enum('vulnerables', ['Discapacidad', 'Mayores', 'Indigena', 'Violencia'])->nullable();
            $table->float('salario');
            $table->double('monto')->nullable();
            $table->text('empresa');
            $table->text('primero_empresa');
            $table->string('segundo_empresa', 200)->nullable();
            $table->text('nombre_empresa');
            $table->string('trabajador', 100);
            $table->string('primero_trabajador', 100);
            $table->string('segundo_trabajador', 100)->nullable();
            $table->string('frecuencia', 15);
            $table->integer('dias');
            $table->enum('estatus', ['Prevencion', 'Pendiente', 'atendido', 'no atendido', 'Aceptado', 'Confirmado', 'Concluida', 'Concluida Pagos', 'Incumplimiento', 'Archivada'])->default('Confirmado');
            $table->string('delegacion', 30);
            $table->text('ine');
            $table->text('representacion');
            $table->string('email', 50);
            $table->string('telefono', 10);
            $table->enum('JLCA', ['Si', 'No']);
            $table->string('motivo', 48);
            $table->string('trabajador_curp', 18);
            $table->text('documentoCurp')->nullable();
            $table->string('tipo_identificacion', 25);
            $table->string('num_identificacion', 20);
            $table->text('documentoidentificacion')->nullable();
            $table->string('PrimaVacacional', 2)->nullable();
            $table->date('fecha_inicio')->useCurrent();
            $table->date('fecha_termino')->nullable();
            $table->string('categoria', 60);
            $table->string('tipo_pago', 50)->nullable();
            $table->string('Aguinaldo', 2)->nullable();
            $table->string('Vacaciones', 2)->nullable();
            $table->string('PagoPTU', 2)->nullable();
            $table->string('Gratificación', 2)->nullable();
            $table->string('PrimaAntigüedad', 2)->nullable();
            $table->string('Otras', 2)->nullable();
            $table->string('Especifique', 50)->nullable();
            $table->text('documentoCuanti')->nullable();
            $table->text('tipo_otros')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('curp_solicitante', 18);
            $table->text('resolucion_primera')->nullable();
            $table->text('resolucion_trabajadores')->nullable();
            $table->text('resolucion_justificacion')->nullable();
            $table->text('resolucion_segunda')->nullable();
            $table->integer('vacaciones_dias')->nullable();
            $table->integer('aguinaldo_dias')->nullable();
            $table->string('otros_dias', 20)->nullable();
            $table->string('horario', 100)->nullable();
            $table->string('comida', 50)->nullable();
            $table->integer('estado_rat');
            $table->integer('municipio_rat');
            $table->string('tipo_vialidad', 20);
            $table->string('calle', 50);
            $table->string('num_ext', 20);
            $table->string('num_int', 10)->nullable();
            $table->string('colonia', 50);
            $table->text('codigo_postal');
            $table->string('NUE', 18)->nullable();
            $table->integer('id_conciliador')->nullable();
            $table->integer('idAbogado')->nullable()->default(0);
            $table->integer('user_id')->nullable()->default(0);
            $table->enum('nacionalidad', ['MEXICANA', 'EXTRANGERA'])->default('MEXICANA');
            $table->unsignedInteger('id_historial')->nullable();
            $table->boolean('incidencia')->nullable();
            $table->text('motivo_incidencia')->nullable();
            $table->integer('conclucion_id')->nullable();
            $table->date('fecha_conclucion')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
