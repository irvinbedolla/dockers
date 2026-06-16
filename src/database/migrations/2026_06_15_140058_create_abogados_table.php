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
        Schema::create('abogados', function (Blueprint $table) {
            $table->integer('idAbogado', true);
            $table->text('nombres_patronal')->nullable();
            $table->string('primer_apellido_patronal', 200)->nullable();
            $table->string('segundo_apellido_patronal', 200)->nullable();
            $table->string('telefono_patronal', 10)->nullable();
            $table->string('email_patronal', 40)->nullable();
            $table->string('curp_patronal', 18)->nullable();
            $table->string('rfc_patronal', 13);
            $table->enum('sexo_patronal', ['Femenino', 'Masculino', 'Prefiero no responder'])->nullable();
            $table->text('giroComercial');
            $table->integer('estado_patronal');
            $table->integer('municipio_patronal');
            $table->string('tipo_vialidad_patronal', 30);
            $table->string('vialidad_patronal', 100);
            $table->string('num_ext_patronal', 50);
            $table->string('mun_int_patronal', 50)->nullable();
            $table->string('colonia_patronal', 50);
            $table->string('cp_patronal', 5);
            $table->string('nombre_representante', 50)->nullable();
            $table->string('primer_apellido_representante', 50)->nullable();
            $table->string('segundo_apellido_representante', 50)->nullable();
            $table->string('curp_representante', 18)->nullable();
            $table->enum('sexo_representante', ['Femenino', 'Masculino', 'Prefiero no responder'])->nullable();
            $table->string('correo_representante', 50)->nullable();
            $table->string('numero_representante', 10)->nullable();
            $table->string('tipo_documento_representante', 50)->nullable();
            $table->text('descipcion_poder')->nullable();
            $table->text('ineDocumento');
            $table->text('cedulaDocumento')->nullable()->comment('Representante ine');
            $table->text('anexo_documeto')->nullable()->comment('anexo');
            $table->text('representacionDocumento')->nullable()->comment('poder acredite');
            $table->date('fechaRegistro')->nullable();
            $table->date('fechaVigencia')->nullable();
            $table->enum('estatus', ['Pendiente', 'Validado']);
            $table->enum('tipo', ['Fisica', 'Moral']);
            $table->enum('reprecentante', ['Si', 'No'])->default('No');
            $table->integer('idUsuario')->nullable();
            $table->string('tipo_identificacion', 50)->nullable();
            $table->string('num_identificacion', 20)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abogados');
    }
};
