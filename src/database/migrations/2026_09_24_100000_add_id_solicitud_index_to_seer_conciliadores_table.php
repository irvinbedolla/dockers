<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('seer_conciliadores', ['id_solicitud'])) {
            return;
        }

        Schema::table('seer_conciliadores', function (Blueprint $table) {
            $table->index('id_solicitud');
        });
    }

    public function down(): void
    {
        if (!Schema::hasIndex('seer_conciliadores', 'seer_conciliadores_id_solicitud_index')) {
            return;
        }

        Schema::table('seer_conciliadores', function (Blueprint $table) {
            $table->dropIndex(['id_solicitud']);
        });
    }
};
