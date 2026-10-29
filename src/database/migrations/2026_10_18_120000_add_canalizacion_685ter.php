<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Canalización de casos de excepción (art. 685 Ter LFT):
 *
 *   - estatus "Canalizada" en seer_general y audiencias: la persona no
 *     continúa con la conciliación y se canaliza a otra institución
 *     (App\Support\FormatoExcepcion::cerrarSolicitud). No es desistimiento:
 *     en el 685 Ter la persona está exenta de agotar la conciliación.
 *   - excepcion_oficios: los oficios de canalización (uno por institución),
 *     numerados CCL/UJDCGI/001/2026 (App\Support\OficioCanalizacion).
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->agregarEstatus('seer_general', 'estatus');
        $this->agregarEstatus('audiencias', 'estatus');

        if (! Schema::hasTable('excepcion_oficios')) {
            Schema::create('excepcion_oficios', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('caso_id');
                $t->string('institucion', 20);
                $t->string('numero', 40);
                $t->unsignedSmallInteger('anio');
                $t->unsignedInteger('consecutivo');
                $t->date('fecha');
                $t->string('lugar', 80)->nullable();
                $t->string('destinatario')->nullable();
                $t->string('cargo')->nullable();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->timestamps();

                $t->unique(['caso_id', 'institucion'], 'uq_oficio_caso_inst');
                $t->index(['anio', 'consecutivo'], 'idx_oficio_consecutivo');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('excepcion_oficios');
        // El valor "Canalizada" se deja en los enum: quitarlo truncaría los registros que lo usen.
    }

    /** Agrega "Canalizada" al enum sin tocar los valores que ya tiene. */
    private function agregarEstatus(string $tabla, string $columna): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $col = DB::selectOne(
            'SELECT COLUMN_TYPE AS tipo, IS_NULLABLE AS nulo, COLUMN_DEFAULT AS omision FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$tabla, $columna]
        );
        if (! $col || ! str_starts_with($col->tipo, 'enum(') || str_contains($col->tipo, "'Canalizada'")) {
            return;
        }

        $tipo = substr($col->tipo, 0, -1).",'Canalizada')";
        $nulo = $col->nulo === 'YES' ? 'NULL' : 'NOT NULL';
        $omision = $col->omision !== null ? ' DEFAULT '.DB::getPdo()->quote($col->omision) : '';

        DB::statement("ALTER TABLE `{$tabla}` MODIFY `{$columna}` {$tipo} {$nulo}{$omision}");
    }
};
