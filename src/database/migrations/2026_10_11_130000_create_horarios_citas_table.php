<?php

use App\Support\HorarioCitas;
use App\Support\Recepcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios de citas editables por sede y trámite.
 *
 * Hasta ahora el intervalo, la hora de cierre, cuántas citas caben por
 * horario y el límite diario estaban escritos en RecepcionController. Esta
 * tabla los reemplaza y el Super Usuario los edita en Administración →
 * Horarios de citas.
 *
 * Se siembra con lo que había al 11 de octubre de 2026, salvo dos
 * correcciones: la ratificación dura 50 min (antes 60, se encimaba con el
 * siguiente horario) y la asesoría dura 40 min como la solicitud (antes 60
 * por un acento). El límite diario de Morelia queda en 10, que es el que en
 * realidad aplicaba.
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

        if (! Schema::hasTable('horarios_citas')) {
            Schema::create('horarios_citas', function (Blueprint $table) use ($intercalacion) {
                $table->charset   = 'utf8mb4';
                $table->collation = $intercalacion;

                $table->id();
                $table->string('delegacion', 30);
                $table->string('tramite', 20);          // Solicitud | Ratificación | Excepción
                $table->time('hora_inicio');
                $table->time('hora_fin');               // el último horario empieza antes de esta hora
                $table->unsignedSmallInteger('intervalo');          // minutos entre un horario y el siguiente
                $table->unsignedSmallInteger('duracion');           // minutos que dura la cita
                $table->time('comida_inicio')->nullable();
                $table->unsignedSmallInteger('comida_minutos')->default(30);
                $table->unsignedTinyInteger('citas_por_horario')->default(1);
                $table->json('horas_un_lugar')->nullable();         // horarios con cupo de 1, p. ej. ["13:30"]
                $table->unsignedSmallInteger('limite_diario')->nullable(); // sólo formulario público
                $table->boolean('activo')->default(true);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['delegacion', 'tramite']);
            });
        }

        $ahora = now();
        foreach (Recepcion::TODAS_LAS_SEDES as $sede) {
            foreach (array_keys(HorarioCitas::GRUPOS) as $grupo) {
                $existe = DB::table('horarios_citas')->where('delegacion', $sede)->where('tramite', $grupo)->exists();
                if ($existe) {
                    continue;
                }
                $valores = HorarioCitas::predeterminado($sede, $grupo);
                $valores['horas_un_lugar'] = $valores['horas_un_lugar'] ? json_encode($valores['horas_un_lugar']) : null;

                DB::table('horarios_citas')->insert($valores + [
                    'delegacion' => $sede, 'tramite' => $grupo, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_citas');
    }
};
