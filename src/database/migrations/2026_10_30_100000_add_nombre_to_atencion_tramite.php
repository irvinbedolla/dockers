<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formulario de atención: quién es la parte que contestó (nombre del
 * trabajador o de la empresa en la solicitud) y la audiencia cuya
 * resolución abrió el formulario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atencion_tramite', function (Blueprint $table) {
            if (! Schema::hasColumn('atencion_tramite', 'nombre')) {
                $table->string('nombre', 255)->nullable()->after('parte');
            }
            if (! Schema::hasColumn('atencion_tramite', 'audiencia_id')) {
                $table->unsignedBigInteger('audiencia_id')->nullable()->after('id_solicitud');
            }
            if (! Schema::hasColumn('atencion_tramite', 'resolucion_audiencia')) {
                $table->string('resolucion_audiencia', 40)->nullable()->after('resultado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('atencion_tramite', function (Blueprint $table) {
            foreach (['nombre', 'audiencia_id', 'resolucion_audiencia'] as $col) {
                if (Schema::hasColumn('atencion_tramite', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
