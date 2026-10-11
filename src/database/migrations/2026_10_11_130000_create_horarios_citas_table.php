<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios de citas editables por sede y trámite.
 *
 * Hasta ahora el intervalo, el cierre, cuántas citas caben por horario y el
 * límite diario estaban escritos en RecepcionController. Esta tabla los
 * reemplaza y el Super Usuario los edita en Administración → Horarios de citas.
 *
 * Se siembra con el horario acordado el 11 de octubre de 2026: todas las
 * sedes y trámites (excepción incluida) de 8:00 a 16:00, con citas de
 * 40 min: 08:00, 08:40 … 15:20 (la última termina a las 16:00).
 * El cupo por horario y el límite diario quedan como estaban en el código.
 *
 * Los valores van escritos aquí (y no leídos de App\Support\HorarioCitas)
 * para que la migración siembre siempre lo mismo aunque esa clase cambie.
 *
 * Re-ejecutable: si la tabla ya existe, sólo agrega los renglones que falten
 * y pone el horario nuevo en los que nadie ha editado (updated_by vacío).
 */
return new class extends Migration
{
    private const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

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
                $table->time('hora_fin');               // cierre: la última cita termina a esta hora o antes
                $table->unsignedSmallInteger('intervalo');          // minutos entre un horario y el siguiente
                $table->unsignedSmallInteger('duracion');           // minutos que dura la cita
                $table->time('comida_inicio')->nullable();
                $table->unsignedSmallInteger('comida_minutos')->default(30);
                $table->unsignedTinyInteger('citas_por_horario')->default(1);
                $table->json('horas_un_lugar')->nullable();         // horarios con cupo de 1, p. ej. ["13:20"]
                $table->unsignedSmallInteger('limite_diario')->nullable(); // sólo formulario público
                $table->boolean('activo')->default(true);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['delegacion', 'tramite']);
            });
        }

        $ahora = now();
        foreach (self::SEDES as $sede) {
            foreach (['Solicitud', 'Ratificación', 'Excepción'] as $tramite) {
                $valores = $this->valores($sede, $tramite) + ['updated_at' => $ahora];
                $llave   = ['delegacion' => $sede, 'tramite' => $tramite];

                if (! DB::table('horarios_citas')->where($llave)->exists()) {
                    DB::table('horarios_citas')->insert($llave + $valores + ['created_at' => $ahora]);
                } else {
                    DB::table('horarios_citas')->where($llave)->whereNull('updated_by')->update($valores);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_citas');
    }

    private function valores(string $sede, string $tramite): array
    {
        $morelia = $sede === 'Morelia';
        $base = [
            'hora_inicio' => '08:00:00', 'hora_fin' => '16:00:00',
            'comida_inicio' => null, 'comida_minutos' => 30, 'activo' => true,
        ];

        return $base + match ($tramite) {
            'Excepción' => [
                'intervalo' => 40, 'duracion' => 40, 'citas_por_horario' => 1,
                'horas_un_lugar' => null, 'limite_diario' => null,
            ],
            'Ratificación' => [
                'intervalo' => 40, 'duracion' => 40, 'citas_por_horario' => $morelia ? 2 : 1,
                'horas_un_lugar' => $morelia ? '["13:20","14:00"]' : null, 'limite_diario' => 15,
            ],
            default => [
                'intervalo' => 40, 'duracion' => 40, 'citas_por_horario' => $morelia ? 3 : 1,
                'horas_un_lugar' => $morelia ? '["13:20","14:00"]' : null, 'limite_diario' => 10,
            ],
        };
    }
};
