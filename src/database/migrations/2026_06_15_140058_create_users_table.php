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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('email', 60)->unique();
            $table->string('profile_photo_path', 18)->nullable()->unique('profile_photo_path');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 191);
            $table->enum('type', ['Seer', 'Si concilio', 'Ambos']);
            $table->enum('delegacion', ['Morelia', 'Uruapan', 'Zamora', 'Sahuayo', 'Lázaro Cárdenas', 'Zitácuaro']);
            $table->rememberToken();
            $table->timestamps();
            $table->dateTime('last_login_at')->nullable()->useCurrent();
            $table->string('last_login_ip', 191)->nullable();
            $table->enum('sexo', ['H', 'M', 'NC'])->nullable();
            $table->integer('tipo')->nullable()->default(1)->comment('0 por defecto para los usuario del CCL y 1 para los solicitantes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
