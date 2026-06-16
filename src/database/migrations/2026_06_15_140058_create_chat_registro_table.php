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
        Schema::create('chat_registro', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nombre_completo', 200);
            $table->string('ciudad', 50);
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->time('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_registro');
    }
};
