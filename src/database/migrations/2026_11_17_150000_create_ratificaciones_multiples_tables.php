<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ratificaciones múltiples: la empresa sube en línea el "Formato para
 * ratificaciones múltiples" (Excel) con varios trabajadores del mismo patrón.
 *
 *   ratificaciones_lotes        un renglón por Excel cargado (la empresa, su
 *                               folio de representación y el archivo).
 *   ratificaciones_lote_trabajadores
 *                               un renglón por trabajador del Excel, con sus
 *                               datos laborales, conceptos, impuesto y los
 *                               mínimos legales calculados por el sistema.
 *                               recepcion_id: su cita de 15 min; turno_id: la
 *                               ratificación cuando el auxiliar la captura.
 *
 * Ver App\Support\RatificacionMultiple.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ratificaciones_lotes')) {
            Schema::create('ratificaciones_lotes', function (Blueprint $table) {
                $table->id();
                $table->string('folio', 30)->nullable()->unique();
                $table->string('delegacion', 60);
                $table->string('poder_folio', 30);
                $table->string('empresa', 255);
                $table->json('domicilio')->nullable();
                $table->string('archivo', 255)->nullable();
                $table->string('archivo_nombre', 255)->nullable();
                $table->string('correo', 150)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->unsignedSmallInteger('trabajadores')->default(0);
                $table->decimal('monto_total', 14, 2)->default(0);
                $table->string('estatus', 20)->default('agendado'); // agendado | cancelado
                $table->string('ip', 45)->nullable();
                $table->unsignedBigInteger('creado_por')->nullable();
                $table->timestamps();

                $table->index(['delegacion', 'created_at']);
                $table->index('poder_folio');
            });
        }

        if (! Schema::hasTable('ratificaciones_lote_trabajadores')) {
            Schema::create('ratificaciones_lote_trabajadores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lote_id');
                $table->unsignedSmallInteger('numero');
                $table->string('nombre_completo', 255);
                $table->string('nombre', 120)->nullable();
                $table->string('primer_apellido', 80)->nullable();
                $table->string('segundo_apellido', 80)->nullable();
                $table->string('puesto', 150)->nullable();
                $table->decimal('salario_diario', 12, 2);
                $table->date('fecha_ingreso');
                $table->date('fecha_termino');
                $table->unsignedSmallInteger('aguinaldo_dias')->default(15);
                $table->decimal('indemnizacion', 14, 2)->default(0);
                $table->decimal('vacaciones', 14, 2)->default(0);
                $table->decimal('prima_vacacional', 14, 2)->default(0);
                $table->decimal('aguinaldo', 14, 2)->default(0);
                $table->decimal('salarios_pendientes', 14, 2)->default(0);
                $table->json('otras')->nullable();           // [{tipo, monto}]
                $table->decimal('total', 14, 2)->default(0);
                $table->decimal('impuesto', 14, 2)->default(0);
                $table->decimal('neto', 14, 2)->default(0);
                $table->json('minimos')->nullable();         // {vacaciones, prima_vacacional, aguinaldo, ...}
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('recepcion_id')->nullable();
                $table->unsignedBigInteger('turno_id')->nullable();
                $table->timestamps();

                $table->index('lote_id');
                $table->index('recepcion_id');
                $table->index('turno_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ratificaciones_lote_trabajadores');
        Schema::dropIfExists('ratificaciones_lotes');
    }
};
