<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Límite de citas (Administración → Módulos, App\Support\LimiteCitas): a qué
 * sedes y en qué canal aplica cada tope.
 *
 *   sedes_ip / _correo / _telefono / _nombre  sedes, una por renglón; null = todas.
 *   canal_ip / _correo / _telefono / _nombre  linea | ventanilla | ambos
 *                                             (la IP siempre es en línea).
 */
return new class extends Migration
{
    private const CRITERIOS = ['ip', 'correo', 'telefono', 'nombre'];

    public function up(): void
    {
        if (! Schema::hasTable('limites_citas')) {
            return;
        }

        Schema::table('limites_citas', function (Blueprint $table) {
            foreach (self::CRITERIOS as $c) {
                if (! Schema::hasColumn('limites_citas', 'sedes_'.$c)) {
                    $table->text('sedes_'.$c)->nullable();
                }
                if (! Schema::hasColumn('limites_citas', 'canal_'.$c)) {
                    $table->string('canal_'.$c, 10)->default('linea');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('limites_citas')) {
            return;
        }
        Schema::table('limites_citas', function (Blueprint $table) {
            foreach (self::CRITERIOS as $c) {
                foreach (['sedes_'.$c, 'canal_'.$c] as $col) {
                    if (Schema::hasColumn('limites_citas', $col)) {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
