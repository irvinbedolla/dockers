<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Límite de citas (Administración → Módulos, App\Support\LimiteCitas): a qué
 * trámites aplica cada tope.
 *
 *   tramites_ip / _correo / _telefono / _nombre  trámites (Solicitud,
 *       Asesoría, Ratificación, Cumplimiento), uno por renglón; null = todos.
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
                if (! Schema::hasColumn('limites_citas', 'tramites_'.$c)) {
                    $table->text('tramites_'.$c)->nullable();
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
                if (Schema::hasColumn('limites_citas', 'tramites_'.$c)) {
                    $table->dropColumn('tramites_'.$c);
                }
            }
        });
    }
};
