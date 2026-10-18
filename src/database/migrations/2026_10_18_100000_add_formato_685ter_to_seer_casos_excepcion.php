<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Formato de atención para casos previstos en el art. 685 Ter LFT
 * (App\Support\FormatoExcepcion). Se guarda en la misma tabla de siempre,
 * seer_casos_excepcion; lo ya capturado no se toca. Columnas nuevas:
 *
 *   id_solicitud            cuando el caso nace de una solicitud y no de una cita;
 *   delegacion              sede del caso, para las estadísticas;
 *   nombre ... correo       datos de la persona como quedaron en el formato;
 *   tipos                   tipos de situación marcados (JSON);
 *   riesgo_lugar            dentro / fuera del centro de trabajo;
 *   conoce_jefe, situacion_otro;
 *   resultado               continua (sigue con la conciliación) | canaliza;
 *   instituciones           a dónde se canalizó (JSON);
 *   domicilio_procuraduria  el "ubicado en ___" del formato;
 *   terminado_at            cuándo se cerró la atención (null = borrador).
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    private const TABLA = 'seer_casos_excepcion';

    public function up(): void
    {
        $columnas = [
            'id_solicitud'           => fn (Blueprint $t) => $t->unsignedBigInteger('id_solicitud')->nullable()->after('id_turno'),
            'delegacion'             => fn (Blueprint $t) => $t->string('delegacion', 40)->nullable()->after('id_user'),
            'nombre'                 => fn (Blueprint $t) => $t->string('nombre')->nullable(),
            'edad'                   => fn (Blueprint $t) => $t->unsignedSmallInteger('edad')->nullable(),
            'genero'                 => fn (Blueprint $t) => $t->string('genero', 20)->nullable(),
            'genero_otro'            => fn (Blueprint $t) => $t->string('genero_otro', 60)->nullable(),
            'telefono'               => fn (Blueprint $t) => $t->string('telefono', 30)->nullable(),
            'correo'                 => fn (Blueprint $t) => $t->string('correo', 150)->nullable(),
            'tipos'                  => fn (Blueprint $t) => $t->json('tipos')->nullable(),
            'riesgo_lugar'           => fn (Blueprint $t) => $t->string('riesgo_lugar', 10)->nullable(),
            'conoce_jefe'            => fn (Blueprint $t) => $t->boolean('conoce_jefe')->nullable(),
            'situacion_otro'         => fn (Blueprint $t) => $t->string('situacion_otro')->nullable(),
            'resultado'              => fn (Blueprint $t) => $t->string('resultado', 12)->nullable(),
            'instituciones'          => fn (Blueprint $t) => $t->json('instituciones')->nullable(),
            'domicilio_procuraduria' => fn (Blueprint $t) => $t->string('domicilio_procuraduria')->nullable(),
            'terminado_at'           => fn (Blueprint $t) => $t->timestamp('terminado_at')->nullable(),
        ];

        $backfill = ! Schema::hasColumn(self::TABLA, 'terminado_at');

        foreach ($columnas as $nombre => $definir) {
            if (! Schema::hasColumn(self::TABLA, $nombre)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $definir($t));
            }
        }

        // Lo capturado con el formulario anterior ya estaba cerrado: no debe
        // volver a salir como "por atender".
        if ($backfill) {
            DB::table(self::TABLA)->whereNull('terminado_at')->update(['terminado_at' => DB::raw('created_at')]);
        }

        $indices = collect(Schema::getIndexes(self::TABLA))->pluck('name');
        foreach ([
            'idx_casos_exc_turno'     => ['id_turno'],
            'idx_casos_exc_solicitud' => ['id_solicitud'],
            'idx_casos_exc_fecha'     => ['fecha', 'resultado'],
        ] as $nombre => $cols) {
            if (! $indices->contains($nombre)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->index($cols, $nombre));
            }
        }
    }

    public function down(): void
    {
        $indices = collect(Schema::getIndexes(self::TABLA))->pluck('name');
        foreach (['idx_casos_exc_turno', 'idx_casos_exc_solicitud', 'idx_casos_exc_fecha'] as $nombre) {
            if ($indices->contains($nombre)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->dropIndex($nombre));
            }
        }

        foreach (['id_solicitud', 'delegacion', 'nombre', 'edad', 'genero', 'genero_otro', 'telefono', 'correo', 'tipos',
                  'riesgo_lugar', 'conoce_jefe', 'situacion_otro', 'resultado', 'instituciones', 'domicilio_procuraduria', 'terminado_at'] as $col) {
            if (Schema::hasColumn(self::TABLA, $col)) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->dropColumn($col));
            }
        }
    }
};
