<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de módulos de atención por sede.
 *
 * Hasta ahora quién atiende cada módulo vivía escrito a mano en tres lugares
 * (HomeController y dos veces en RecepcionController), con listas que ya no
 * coincidían entre sí. Esta tabla es la única fuente: el formulario público,
 * la ventanilla y el botón "Asignar" leen de aquí, y el Super Usuario la
 * edita desde Administración → Módulos.
 *
 * El sembrado reproduce lo que asignarModulo() decía al 9 de octubre de 2026.
 */
return new class extends Migration
{
    private const S = ['Solicitud', 'Asesoría'];
    private const R = ['Ratificación'];

    public function up(): void
    {
        // La tabla recepcion es vieja y no usa la intercalación por defecto de
        // Laravel. Si modulos sale con otra, MySQL se niega a compararlas
        // (error 1267). Se toma la de recepcion, sea cual sea en cada base.
        $intercalacion = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'recepcion')
            ->where('COLUMN_NAME', 'delegacion')
            ->value('COLLATION_NAME') ?: 'utf8mb4_general_ci';

        // Re-ejecutable: un primer intento pudo quedarse a la mitad (MySQL no
        // deshace un CREATE TABLE), así que cada paso revisa si ya está hecho.
        if (Schema::hasTable('modulos')) {
            DB::statement("ALTER TABLE modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE {$intercalacion}");
        } else {
            $this->crearTabla($intercalacion);
        }

        $this->agregarColumnas();

        if (DB::table('modulos')->count() === 0) {
            $this->sembrar();
        }

        // Origen de lo que ya existe: la mejor aproximación disponible.
        DB::update("UPDATE recepcion SET origen = IF(correo IS NOT NULL AND correo <> '', 'linea', 'ventanilla') WHERE origen IS NULL");

        // Las citas de hoy en adelante quedan ligadas a su módulo por nombre.
        // El histórico se deja como está: sus lugar_auxiliar son nombres de
        // personas, "Mesa 1", "Recepción"... y no corresponden al catálogo.
        DB::update("
            UPDATE recepcion r
            JOIN modulos m
              ON m.delegacion = r.delegacion COLLATE {$intercalacion}
             AND m.nombre     = r.lugar_auxiliar COLLATE {$intercalacion}
            SET r.modulo_id = m.id
            WHERE r.fecha >= CURDATE() AND r.modulo_id IS NULL
        ");
    }

    private function crearTabla(string $intercalacion): void
    {
        Schema::create('modulos', function (Blueprint $table) use ($intercalacion) {
            $table->charset   = 'utf8mb4';
            $table->collation = $intercalacion;

            $table->id();
            $table->string('delegacion', 30);
            $table->string('nombre', 60);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // Trámites que el módulo recibe de primera mano.
            $table->json('tramites');
            // Trámites que recibe sólo si ningún módulo principal está libre.
            $table->json('respaldo')->nullable();
            // Media hora en que el módulo no recibe citas.
            $table->time('hora_comida')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['delegacion', 'nombre']);
        });
    }

    private function agregarColumnas(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (! Schema::hasColumn('recepcion', 'modulo_id')) {
                $table->unsignedBigInteger('modulo_id')->nullable()->after('lugar_auxiliar')->index();
            }
            // Hasta hoy "en línea" se deducía de que hubiera correo, pero la
            // ventanilla también lo pide. Desde aquí se guarda explícito.
            if (! Schema::hasColumn('recepcion', 'origen')) {
                $table->enum('origen', ['linea', 'ventanilla'])->nullable()->after('correo')->index();
            }
        });
    }

    private function sembrar(): void
    {
        $semilla = [
            ['Morelia', 'Modulo 1', 5,    self::S, null,    '13:00:00'],
            ['Morelia', 'Modulo 2', 209,  self::S, null,    '13:30:00'],
            ['Morelia', 'Modulo 3', 65,   self::S, null,    null],
            ['Morelia', 'Modulo 4', 4,    self::R, null,    null],
            ['Morelia', 'Modulo 5', 10,   self::R, null,    null],
            ['Uruapan', 'Modulo 1', 2817, self::S, null,    null],
            ['Uruapan', 'Modulo 2', 32,   self::R, null,    null],
            ['Uruapan', 'Modulo 3', 28,   self::R, null,    null],
            ['Zamora',  'Modulo 1', 2664, self::S, null,    null],
            ['Zamora',  'Modulo 2', 2663, self::R, self::S, null],
            ['Zamora',  'Modulo 3', 74,   self::R, self::S, null],
            ['Lázaro Cárdenas', 'Modulo 1', 2814, self::S, null, null],
            ['Lázaro Cárdenas', 'Modulo 2', 731,  self::R, null, null],
            ['Lázaro Cárdenas', 'Modulo 3', 154,  self::R, null, null],
            ['Sahuayo',   'Modulo 1', 70, self::S, null,    null],
            ['Sahuayo',   'Modulo 2', 44, self::R, self::S, null],
            ['Zitácuaro', 'Modulo 1', 61, self::S, null,    null],
            ['Zitácuaro', 'Modulo 2', 47, self::R, self::S, null],
        ];

        $ahora = now();
        foreach ($semilla as $i => [$sede, $nombre, $usuario, $tramites, $respaldo, $comida]) {
            DB::table('modulos')->insert([
                'delegacion'  => $sede,
                'nombre'      => $nombre,
                // Si la cuenta no existe en esta base, el módulo queda sin
                // persona en vez de apuntar a un id que no es nadie.
                'user_id'     => DB::table('users')->where('id', $usuario)->exists() ? $usuario : null,
                'tramites'    => json_encode($tramites, JSON_UNESCAPED_UNICODE),
                'respaldo'    => $respaldo ? json_encode($respaldo, JSON_UNESCAPED_UNICODE) : null,
                'hora_comida' => $comida,
                'activo'      => true,
                'orden'       => $i,
                'created_at'  => $ahora,
                'updated_at'  => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('recepcion', function (Blueprint $table) {
            if (Schema::hasColumn('recepcion', 'modulo_id')) {
                $table->dropIndex(['modulo_id']);
                $table->dropColumn('modulo_id');
            }
            if (Schema::hasColumn('recepcion', 'origen')) {
                $table->dropIndex(['origen']);
                $table->dropColumn('origen');
            }
        });

        Schema::dropIfExists('modulos');
    }
};
