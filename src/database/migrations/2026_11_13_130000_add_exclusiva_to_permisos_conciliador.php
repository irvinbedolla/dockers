<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sede exclusiva en la agenda por sede (Administración → Conciliadores):
 * mientras el renglón esté vigente, SÓLO los conciliadores que tengan esa
 * sede marcada como exclusiva reciben audiencias nuevas de ahí; los demás
 * que la atienden (por su agenda de siempre o por sede) dejan de recibirlas.
 * Caso: "desde el 9 de diciembre Sahuayo es sólo de X, ya no de Zamora".
 * Ver ObtenerAudiencia y App\Support\SedesConciliador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_conciliador', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos_conciliador', 'exclusiva')) {
                $table->boolean('exclusiva')->default(false)->after('vigente_hasta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permisos_conciliador', function (Blueprint $table) {
            $table->dropColumn('exclusiva');
        });
    }
};
