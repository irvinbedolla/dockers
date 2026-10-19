<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién intervino en cada cita, con su nombre guardado (si después cambia
 * el nombre o se da de baja la cuenta, el seguimiento sigue diciendo quién
 * fue):
 *
 *   creado_por / creado_por_nombre      quien registró el turno en el Centro
 *                                        (en línea queda vacío: lo agendó la
 *                                        persona);
 *   atendido_por / atendido_por_nombre  el orientador o auxiliar que la
 *                                        atendió (inició su atención o la
 *                                        terminó).
 *
 * Lo llena el modelo Recepcion solo (booted()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'creado_por')) {
                $table->integer('creado_por')->nullable()->after('origen');
                $table->string('creado_por_nombre', 120)->nullable()->after('creado_por');
            }
            if (! Schema::hasColumn('recepcion', 'atendido_por')) {
                $table->integer('atendido_por')->nullable()->after('atencion_fin_at');
                $table->string('atendido_por_nombre', 120)->nullable()->after('atendido_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            foreach (['creado_por', 'creado_por_nombre', 'atendido_por', 'atendido_por_nombre'] as $col) {
                if (Schema::hasColumn('recepcion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
