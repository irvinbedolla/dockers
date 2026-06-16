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
        Schema::create('seer_solicitante', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->enum('tipo_persona', ['Fisica', 'Moral'])->nullable();
            $table->string('curp', 18);
            $table->string('nombre', 150);
            $table->string('rfc', 13)->nullable();
            $table->enum('sexo', ['H', 'M', 'NC']);
            $table->enum('nacionalidad', ['Mexicana', 'Otra']);
            $table->integer('estado');
            $table->enum('traductor', ['Si', 'No'])->nullable()->default('No');
            $table->string('lenguaje', 40)->nullable();
            $table->enum('discapacidad', ['Si', 'No'])->default('No');
            $table->string('tipo_discapacidad', 50)->nullable();
            $table->date('fecha_nacimiento');
            $table->integer('edad');
            $table->string('telefono1', 10);
            $table->string('telefono2', 10)->nullable();
            $table->string('email', 50);
            $table->integer('estado_domicilio');
            $table->string('tipo_vialidad', 20);
            $table->string('calle', 100);
            $table->string('num_ext', 50);
            $table->string('num_int', 50)->nullable();
            $table->string('colonia', 50);
            $table->integer('municipio_domicilio');
            $table->string('codigo_postal', 5);
            $table->text('referencia')->nullable();
            $table->string('calle2', 30)->nullable();
            $table->string('calle3', 30)->nullable();
            $table->string('nss', 12)->nullable();
            $table->string('puesto', 50);
            $table->double('pago');
            $table->enum('periodo_pago', ['Semanal', 'Mensual', 'Quincenal', 'Diario'])->nullable();
            $table->integer('horas_semana');
            $table->date('fecha_ingreso');
            $table->date('fecha_salida')->nullable();
            $table->string('jornada', 200)->nullable();
            $table->text('documentoCurp')->nullable();
            $table->text('documentoIdentificacion')->nullable();
            $table->enum('identificacion', ['Credencial de elector', 'Pasaporte', 'Cédula profesional', 'Licencia de conducir', 'Otro', 'Credencial de inapam', 'Cartilla militar', 'Documento migratorio', 'Constancia de identidad'])->nullable();
            $table->string('num_identificacion', 50)->nullable();
            $table->enum('labora', ['Si', 'No'])->default('No');
            $table->text('descripcionSolicitud');
            $table->integer('poder_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_solicitante');
    }
};
