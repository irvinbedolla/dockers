<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE recepcion
            MODIFY COLUMN estatus
            ENUM('atendido','confirmada','expirada','pendiente','cancelada')
            NOT NULL
            DEFAULT 'pendiente'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('recepcion')->where('estatus','cancelada')->update(['estatus' => 'expirada']);
        DB::statement("
            ALTER TABLE recepcion
            MODIFY COLUMN estatus
            ENUM('atendido', 'confirmada', 'expirada', 'pendiente')
            NOT NULL
            DEFAULT 'pendiente'
        ");
    }
};
