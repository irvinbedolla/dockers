<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vigencia de la agenda por sede (Administración → Conciliadores): desde y
 * hasta cuándo un conciliador atiende una sede. Sirve para coberturas como
 * "desde el 9 de diciembre atiende Zitácuaro en virtual". Vacío = siempre.
 * El reparto (ObtenerAudiencia) revisa la fecha DE LA AUDIENCIA contra la
 * vigencia; los renglones de siempre (sede = NULL) no la usan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_conciliador', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos_conciliador', 'vigente_desde')) {
                $table->date('vigente_desde')->nullable()->after('sala');
            }
            if (! Schema::hasColumn('permisos_conciliador', 'vigente_hasta')) {
                $table->date('vigente_hasta')->nullable()->after('vigente_desde');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permisos_conciliador', function (Blueprint $table) {
            $table->dropColumn(['vigente_desde', 'vigente_hasta']);
        });
    }
};
