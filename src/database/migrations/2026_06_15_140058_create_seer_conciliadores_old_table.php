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
        Schema::create('seer_conciliadores_old', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_solicitud');
            $table->string('numero_audiencia', 10)->nullable();
            $table->enum('estatus_conciliacion', ['Conciliacion', 'No conciliacion', 'Archivado por incomparecencia', 'Regenerada', 'Incompetencia', 'Archivada']);
            $table->integer('numero_audiencias');
            $table->float('monto');
            $table->text('cumplimiento_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('multa', ['Si', 'No']);
            $table->float('monto_multa')->nullable();
            $table->string('rfc', 13);
            $table->string('NSS', 18);
            $table->enum('tipo', ['Presencial', 'Virtual', 'Linea']);
            $table->enum('motivo_archivo', ['Incompetencia', 'Falta de interes']);
            $table->date('fecha_reprogracion')->nullable();
            $table->date('fecha_conclucion')->nullable();
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_conciliadores_old');
    }
};
