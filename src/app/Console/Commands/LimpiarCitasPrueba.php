<?php

namespace App\Console\Commands;

use App\Support\FlujoCita;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Borra las citas de prueba del flujo nuevo (recepcion.es_prueba = 1) y lo
 * que cuelga de ellas. Nunca toca una cita real.
 *
 *   php artisan citas:limpiar-pruebas            pregunta antes de borrar
 *   php artisan citas:limpiar-pruebas --force    sin preguntar
 */
class LimpiarCitasPrueba extends Command
{
    protected $signature = 'citas:limpiar-pruebas {--force : No preguntar}';

    protected $description = 'Borra las citas marcadas como prueba del flujo cita → solicitud';

    public function handle(): int
    {
        if (! FlujoCita::migrado()) {
            $this->warn('Falta correr la migración del flujo de citas (fase 0).');

            return self::SUCCESS;
        }

        $citas = DB::table('recepcion')->where('es_prueba', 1);
        $total = (clone $citas)->count();

        if ($total === 0) {
            $this->info('No hay citas de prueba.');

            return self::SUCCESS;
        }

        $this->table(['Folio', 'Fecha', 'Hora', 'Sede', 'Solicitante'], (clone $citas)
            ->orderBy('fecha')->orderBy('hora')->limit(30)
            ->get(['codigo', 'fecha', 'hora', 'delegacion', 'solicitante'])
            ->map(fn ($c) => (array) $c)->all());

        if (! $this->option('force') && ! $this->confirm("¿Borrar {$total} citas de prueba?")) {
            return self::SUCCESS;
        }

        $ids = (clone $citas)->pluck('id');

        DB::transaction(function () use ($ids) {
            // Solicitudes que salieron de una cita de prueba: se desligan, no
            // se borran (pudieron capturarse en serio).
            DB::table('seer_general')->whereIn('recepcion_id', $ids)->update(['recepcion_id' => null]);
            DB::table('recepcion')->whereIn('id', $ids)->delete();
        });

        $this->info("Se borraron {$total} citas de prueba.");

        return self::SUCCESS;
    }
}
