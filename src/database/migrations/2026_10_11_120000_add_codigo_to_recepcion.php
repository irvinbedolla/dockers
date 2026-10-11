<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Folio legible de las citas: SEDE-TRÁMITE+AÑO-NÚMERO (p. ej. MOR-S26-0142).
 *
 * El "consecutivo" de siempre se repite entre sedes, trámites y años, así que
 * no sirve para identificar una cita. Se deja como está (la ventanilla lo usa
 * como número de turno) y se agrega "codigo", único, que es el que ve la
 * persona en su acuse, en la pantalla de confirmación y en el correo.
 *
 * La columna "folio" ya existe y guarda el folio de la solicitud (6950/2025),
 * por eso el nombre es otro.
 *
 * Las citas que ya existen no se tocan: la gente ya recibió su folio anterior
 * por correo, y se sigue mostrando ese.
 */
return new class extends Migration
{
    public function up(): void
    {
        $intercalacion = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'recepcion')
            ->where('COLUMN_NAME', 'delegacion')
            ->value('COLLATION_NAME') ?: 'utf8mb4_general_ci';

        if (! Schema::hasColumn('recepcion', 'codigo')) {
            Schema::table('recepcion', function (Blueprint $table) {
                $table->string('codigo', 20)->nullable()->unique()->after('consecutivo');
            });
        }

        // Un renglón por prefijo (MOR-S26). El siguiente número se toma con un
        // solo UPSERT atómico, así dos citas simultáneas nunca comparten folio.
        if (! Schema::hasTable('folios_citas')) {
            Schema::create('folios_citas', function (Blueprint $table) use ($intercalacion) {
                $table->charset   = 'utf8mb4';
                $table->collation = $intercalacion;

                $table->string('prefijo', 12)->primary();
                $table->unsignedInteger('ultimo')->default(0);
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('folios_citas');

        if (Schema::hasColumn('recepcion', 'codigo')) {
            Schema::table('recepcion', function (Blueprint $table) {
                $table->dropUnique(['codigo']);
                $table->dropColumn('codigo');
            });
        }
    }
};
