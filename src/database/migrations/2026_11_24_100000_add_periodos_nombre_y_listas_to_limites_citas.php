<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Límite de citas en línea (Administración → Módulos, App\Support\LimiteCitas):
 *
 *   periodo_ip / _correo / _telefono / _nombre  cada criterio con su propio
 *                         periodo (dia, semana, quincena, mes, reg7, reg15,
 *                         reg30); arrancan con el periodo único que había.
 *   por_nombre            tope por nombre completo; arranca en 3 en 7 días,
 *                         la regla que estaba escrita en el código.
 *   lista_blanca / negra  correos (o @dominio) sin tope / que no pueden
 *                         registrar citas en línea ni turnos en ventanilla.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('limites_citas')) {
            return;
        }

        Schema::table('limites_citas', function (Blueprint $table) {
            if (! Schema::hasColumn('limites_citas', 'por_nombre')) {
                $table->unsignedSmallInteger('por_nombre')->default(0)->after('por_telefono');
            }
            foreach (['ip', 'correo', 'telefono', 'nombre'] as $c) {
                if (! Schema::hasColumn('limites_citas', 'periodo_'.$c)) {
                    $table->string('periodo_'.$c, 10)->default('dia');
                }
            }
            if (! Schema::hasColumn('limites_citas', 'lista_blanca')) {
                $table->text('lista_blanca')->nullable();
            }
            if (! Schema::hasColumn('limites_citas', 'lista_negra')) {
                $table->text('lista_negra')->nullable();
            }
        });

        foreach (DB::table('limites_citas')->get() as $fila) {
            $periodo = in_array($fila->periodo, ['dia', 'semana', 'mes'], true) ? $fila->periodo : 'dia';
            DB::table('limites_citas')->where('id', $fila->id)->update([
                'periodo_ip'       => $periodo,
                'periodo_correo'   => $periodo,
                'periodo_telefono' => $periodo,
                'por_nombre'       => 3,
                'periodo_nombre'   => 'semana',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('limites_citas')) {
            return;
        }
        Schema::table('limites_citas', function (Blueprint $table) {
            foreach (['por_nombre', 'periodo_ip', 'periodo_correo', 'periodo_telefono', 'periodo_nombre', 'lista_blanca', 'lista_negra'] as $col) {
                if (Schema::hasColumn('limites_citas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
