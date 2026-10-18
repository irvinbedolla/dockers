<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formato de aceptación de canalización (App\Support\OficioCanalizacion):
 * va en el mismo renglón de excepcion_oficios que el oficio de esa
 * institución.
 *
 *   area_servicio, motivo     lo que dice la aceptación;
 *   cargo_atiende             cargo de quien brinda la atención;
 *   aceptacion_firmada(_at)   la persona ya firmó la aceptación.
 *
 * El número de oficio ahora se aparta hasta generar el oficio (imprimir sólo
 * la aceptación no gasta número): numero, anio y consecutivo pasan a nullable.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    private const TABLA = 'excepcion_oficios';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA)) {
            return;
        }

        $columnas = [
            'area_servicio'         => fn (Blueprint $t) => $t->string('area_servicio')->nullable()->after('cargo'),
            'motivo'                => fn (Blueprint $t) => $t->string('motivo', 500)->nullable()->after('area_servicio'),
            'cargo_atiende'         => fn (Blueprint $t) => $t->string('cargo_atiende')->nullable()->after('motivo'),
            'aceptacion_firmada'    => fn (Blueprint $t) => $t->boolean('aceptacion_firmada')->default(false)->after('cargo_atiende'),
            'aceptacion_firmada_at' => fn (Blueprint $t) => $t->timestamp('aceptacion_firmada_at')->nullable()->after('aceptacion_firmada'),
        ];
        foreach ($columnas as $nombre => $definir) {
            if (! Schema::hasColumn(self::TABLA, $nombre)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $definir($t));
            }
        }

        Schema::table(self::TABLA, function (Blueprint $t) {
            $t->string('numero', 40)->nullable()->change();
            $t->unsignedSmallInteger('anio')->nullable()->change();
            $t->unsignedInteger('consecutivo')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (['area_servicio', 'motivo', 'cargo_atiende', 'aceptacion_firmada', 'aceptacion_firmada_at'] as $col) {
            if (Schema::hasColumn(self::TABLA, $col)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->dropColumn($col));
            }
        }
    }
};
