<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    //Asigna un número de guía (AAAA + 6 dígitos aleatorios) a las solicitudes con estatus "Confirmado" que aún no tienen uno. El año se toma del registro.
    public function up(): void
    {
        // Guías ya usadas, para no repetir
        $usadas = DB::table('seer_general')
            ->whereNotNull('numero_guia')
            ->pluck('numero_guia')
            ->flip()
            ->all();

        DB::table('seer_general')
            ->whereNull('numero_guia')
            ->select('id', 'año', 'fecha')
            ->chunkById(500, function ($registros) use (&$usadas) {
                foreach ($registros as $registro) {
                    $anio = $registro->año ?: date('Y', strtotime($registro->fecha ?? 'now'));

                    do {
                        $guia = $anio . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    } while (isset($usadas[$guia]));

                    $usadas[$guia] = true;

                    DB::table('seer_general')
                        ->where('id', $registro->id)
                        ->update(['numero_guia' => $guia]);
                }
            });
    }

    public function down(): void
    {
    }
};
