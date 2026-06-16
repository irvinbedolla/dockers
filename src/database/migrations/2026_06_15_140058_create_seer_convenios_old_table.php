<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seer_convenios_old', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->date('fecha');
            $table->string('NUE', 18);
            $table->float('monto');
            $table->string('tipo_pago', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seer_convenios_old');
    }
};
