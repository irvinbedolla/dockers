<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('seer_general', 'numero_guia')) {
            return;
        }

        Schema::table('seer_general', function (Blueprint $table) {
            // Formato: AAAA + 6 dígitos aleatorios (10 dígitos en total)
            $table->char('numero_guia', 10)->nullable()->unique()->after('NUE');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('seer_general', 'numero_guia')) {
            return;
        }

        Schema::table('seer_general', function (Blueprint $table) {
            $table->dropUnique(['numero_guia']);
            $table->dropColumn('numero_guia');
        });
    }
};
