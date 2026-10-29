<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Citas que expiraron porque la persona llegó tarde (o la marcaron como no
 * presentada) y que recepción decidió atender de todos modos desde el
 * escáner (App\Support\ValidadorCita::atenderTarde).
 *
 *   llego_tarde      1 = se reactivó; en Mi módulo va después de quienes
 *                    llegaron a tiempo y en los reportes se distingue de
 *                    "no llegó".
 *   llego_tarde_por  quién la reactivó.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'llego_tarde')) {
                $columna = $table->boolean('llego_tarde')->default(false);
                if (Schema::hasColumn('recepcion', 'confirmada_at')) {
                    $columna->after('confirmada_at');
                }
            }
            if (! Schema::hasColumn('recepcion', 'llego_tarde_por')) {
                $table->integer('llego_tarde_por')->nullable()->after('llego_tarde');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            foreach (['llego_tarde_por', 'llego_tarde'] as $columna) {
                if (Schema::hasColumn('recepcion', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
