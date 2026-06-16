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
        Schema::create('chat_rp', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_registro')->index('chat_registro_id_registro_chat_rp');
            $table->integer('id_pregunta')->index('chat_preguntas_id_pregunta_chat_rp');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rp');
    }
};
