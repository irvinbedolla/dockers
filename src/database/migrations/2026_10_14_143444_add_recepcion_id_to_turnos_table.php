
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('turnos', 'recepcion_id')) {
            Schema::table('turnos', function (Blueprint $table) {
                $table->unsignedBigInteger('recepcion_id')
                    ->nullable()
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('turnos', 'recepcion_id')) {
            Schema::table('turnos', function (Blueprint $table) {
                $table->dropIndex(['recepcion_id']);
                $table->dropColumn('recepcion_id');
            });
        }
    }
};
