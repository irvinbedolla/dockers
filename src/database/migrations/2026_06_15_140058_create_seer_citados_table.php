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
        Schema::create('seer_citados', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->timestamp('fecha')->nullable();
            $table->enum('tipo_persona', ['Fisica', 'Moral'])->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->text('nombre')->nullable();
            $table->string('primer_apellido', 50)->nullable();
            $table->string('segundo_apellido', 50)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('estatus', ['Notificada', 'No notificada', 'Pendiente', 'Exhorto', 'No exitosa se constituye', 'Sin asignar', 'No exitosa no se constituye', 'Finalizado exitosamente', 'Recibe pero no firma', 'Exitosa por Instructivo', 'Notificada en Audiencia'])->default('Sin asignar');
            $table->integer('edad')->nullable();
            $table->enum('sexo', ['H', 'M', 'NB', 'LGBTTTIQ'])->nullable();
            $table->enum('nacionalidad', ['Mexicana', 'Otra'])->nullable();
            $table->integer('estado_solicitante')->nullable();
            $table->integer('traductor')->nullable()->default(0)->comment('1 es Si y 0 no');
            $table->text('lenguaje')->nullable();
            $table->string('colonia', 100);
            $table->string('cp', 20);
            $table->string('calle1', 100)->nullable();
            $table->string('calle2', 100)->nullable();
            $table->string('n_ext', 50);
            $table->string('n_int', 50)->nullable();
            $table->string('calle', 100);
            $table->string('tipo_vialidad', 40);
            $table->text('referencia');
            $table->enum('tipo_notificacion', ['Citatorio', 'Multa'])->default('Citatorio');
            $table->integer('id_notificador')->default(0);
            $table->integer('id_abogado')->nullable();
            $table->integer('id_fisica')->nullable();
            $table->enum('notificacion', ['Centro', 'Trabajador', 'Ambos', 'Exhorto'])->default('Trabajador');
            $table->string('documento', 20)->nullable();
            $table->string('documento1', 20)->nullable();
            $table->string('documento2', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('municipio_citado')->nullable();
            $table->text('quien_atiende')->nullable();
            $table->text('medio')->nullable();
            $table->text('vialidad_notificacion')->nullable();
            $table->text('abundar_area')->nullable();
            $table->text('abundar_inmueble')->nullable();
            $table->text('nombre_notificacion')->nullable();
            $table->text('relacion_notificacion')->nullable();
            $table->text('puesto')->nullable();
            $table->text('identificacion_notificacion')->nullable();
            $table->text('motivo_identificacion')->nullable();
            $table->text('firma')->nullable();
            $table->text('problema_diligencia')->nullable();
            $table->text('genero')->nullable();
            $table->text('tez')->nullable();
            $table->text('edad_filiacion')->nullable();
            $table->text('altura')->nullable();
            $table->text('complexion')->nullable();
            $table->text('cabello')->nullable();
            $table->text('ojos')->nullable();
            $table->text('particulares')->nullable();
            $table->text('especificar')->nullable();
            $table->integer('estado_citado');
            $table->string('imagen_domicilio1')->nullable();
            $table->string('imagen_domicilio2')->nullable();
            $table->enum('resulte_responsable', ['Si', 'No'])->default('No');
            $table->string('pendiente_firma', 2)->nullable()->default('No');
            $table->integer('aparece_convenio')->nullable();
            $table->string('giro_comercial', 100)->nullable();
            $table->string('num_identificacion', 30)->nullable();
            $table->unsignedInteger('id_historial')->nullable();
            $table->enum('comparecencia', ['Si', 'No'])->nullable();
            $table->enum('tipo_identificacion_comparecencia', ['Credencial de elector', 'Pasaporte', 'Cédula profesional', 'Licencia de conducir', 'Otro', 'Credencial de inapam', 'Cartilla militar', 'Documento migratorio', 'Constancia de identidad'])->nullable();
            $table->string('num_identificacion_comparecencia', 50)->nullable();
            $table->text('identificacion_comparecencia')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->integer('audiencia_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_citados');
    }
};
