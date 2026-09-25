<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
    
        DB::table('recepcion')
            ->where('estatus', 'no atendido')
            ->update(['estatus' => 'pendiente']);

        
        DB::statement("
            ALTER TABLE recepcion
            MODIFY COLUMN estatus
            ENUM('atendido', 'confirmada', 'expirada', 'pendiente')
            NOT NULL
            DEFAULT 'pendiente'
        ");
    }

    public function down(): void
    {
    
        DB::table('recepcion')
            ->where('estatus', 'pendiente')
            ->update(['estatus' => 'no atendido']);

      
        DB::statement("
            ALTER TABLE recepcion
            MODIFY COLUMN estatus
            ENUM('atendido', 'no atendido')
            NOT NULL
            DEFAULT 'no atendido'
        ");
    }
};
