<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La foto de perfil necesita columna propia.
     *
     * profile_photo_path, que por nombre deberia servir, esta ocupada: guarda
     * la CURP. Es varchar(18) UNIQUE, el alta de usuarios la pide etiquetada
     * como "CURP" y 5,140 de los 5,291 registros traen una CURP valida. No
     * cabe una ruta ahi ni tiene sentido el indice unico para una foto.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto_perfil', 120)->nullable()->after('profile_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto_perfil');
        });
    }
};
