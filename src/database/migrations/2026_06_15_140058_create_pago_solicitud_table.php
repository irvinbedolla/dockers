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
        Schema::create('pago_solicitud', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud')->default(0);
            $table->date('fecha');
            $table->time('hora');
            $table->double('monto');
            $table->text('descripcion');
            $table->text('observaciones')->nullable();
            $table->enum('estatus', ['Pendiente', 'Pagado', 'No pagado', 'Incomparecencia trabajador', 'Pagado con pena convencional'])->default('Pendiente');
            $table->enum('tipo_pago', ['Ratificacion', 'Audiencia', 'Conciliador', 'Borrado']);
            $table->enum('delegacion', ['Morelia', 'Uruapan', 'Zamora', 'Zitácuaro', 'Lázaro Cárdenas', 'Sahuayo']);
            $table->string('NUE', 18)->nullable();
            $table->integer('id_conciliador')->nullable();
            $table->string('nombre_trabajador', 80)->nullable();
            $table->text('empresa_representante')->nullable();
            $table->enum('tipo_generacion', ['0', '1'])->nullable()->default('0');
            $table->text('forma_pago')->nullable();
            $table->date('fecha_audiencia')->nullable();
            $table->boolean('incidencia')->nullable();
            $table->time('hora_audiencia')->nullable();
            $table->decimal('monto_pc', 12)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_solicitud');
    }
};
