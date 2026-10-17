<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La tabla `personal_access_tokens` se creó con un generador de migraciones
 * y quedó sin llave primaria, sin autoincremento, sin índices y sin la
 * columna `expires_at`. Así Sanctum no puede crear tokens ("Field 'id'
 * doesn't have a default value") ni hacerlos caducar.
 *
 * Si la tabla está vacía (el caso de QA y, hasta hoy, producción), se vuelve
 * a crear con el esquema estándar de Sanctum. Si ya tuviera registros, en
 * MySQL se corrige en sitio sin perderlos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('personal_access_tokens')->count() === 0) {
            Schema::drop('personal_access_tokens');

            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name', 191);
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });

            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('personal_access_tokens tiene registros: corrígela a mano en este motor.');
        }

        DB::statement('ALTER TABLE personal_access_tokens MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unique('token');
            $table->index(['tokenable_type', 'tokenable_id']);

            if (! Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at')->index();
            }
        });
    }

    public function down(): void
    {
        // La reparación de llaves e índices no se revierte: volver a una tabla
        // sin llave primaria solo rompería Sanctum otra vez.
        if (Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex(['expires_at']);
                $table->dropColumn('expires_at');
            });
        }
    }
};
