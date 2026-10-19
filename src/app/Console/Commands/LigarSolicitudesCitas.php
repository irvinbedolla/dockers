<?php

namespace App\Console\Commands;

use App\Support\FlujoCita;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Liga hacia atrás las solicitudes con la cita que las originó, para el
 * seguimiento del caso (App\Support\SeguimientoCaso). Desde ahora se ligan
 * solas al guardar (PrellenadoCita::ligar); esto es para las anteriores.
 *
 * Regla (aproximada, por eso queda como recepcion_vinculo = 'historico'):
 * la cita de Solicitud con la misma CURP del solicitante, de hasta 30 días
 * antes o después de la solicitud (en línea se puede llenar antes de la
 * cita), que no esté ligada a otra; si hay varias, la más cercana. Sólo
 * sirve para citas que ya pedían CURP.
 *
 *   php artisan citas:ligar-solicitudes              últimos 180 días
 *   php artisan citas:ligar-solicitudes --dias=365
 *   php artisan citas:ligar-solicitudes --simular    sólo cuenta, no guarda
 */
class LigarSolicitudesCitas extends Command
{
    protected $signature = 'citas:ligar-solicitudes {--dias=180 : Solicitudes de cuántos días atrás} {--simular : No guarda, sólo cuenta}';

    protected $description = 'Liga solicitudes anteriores con su cita por CURP (para el seguimiento del caso)';

    private const VENTANA = 30;

    public function handle(): int
    {
        if (! FlujoCita::conSeguimiento()) {
            $this->warn('Falta correr la migración del seguimiento (php artisan migrate).');

            return self::FAILURE;
        }

        $desde = now()->subDays(max(1, (int) $this->option('dias')))->toDateString();

        $solicitudes = DB::table('seer_general as g')
            ->join('seer_solicitante as s', 's.id_solicitud', '=', 'g.id')
            ->whereNull('g.recepcion_id')
            ->where('g.fecha', '>=', $desde)
            ->whereRaw('CHAR_LENGTH(s.curp) = 18')
            ->orderBy('g.fecha')
            ->get(['g.id', 'g.fecha', 's.curp'])
            ->unique('id');

        $usadas = DB::table('seer_general')->whereNotNull('recepcion_id')->pluck('recepcion_id')->flip();
        $ligadas = 0;

        foreach ($solicitudes as $sol) {
            $fecha = substr((string) $sol->fecha, 0, 10);
            $cita = DB::table('recepcion')
                ->where('curp', strtoupper(trim($sol->curp)))
                ->where('tipo', 'Solicitud')
                ->whereBetween('fecha', [now()->parse($fecha)->subDays(self::VENTANA)->toDateString(), now()->parse($fecha)->addDays(self::VENTANA)->toDateString()])
                ->where('estatus', '!=', 'expirada')
                ->orderByRaw('ABS(DATEDIFF(fecha, ?))', [$fecha])->orderByDesc('hora')
                ->get(['id'])
                ->first(fn ($c) => ! isset($usadas[$c->id]));

            if (! $cita) {
                continue;
            }

            $usadas[$cita->id] = true;
            $ligadas++;

            if (! $this->option('simular')) {
                DB::table('seer_general')->where('id', $sol->id)->update(['recepcion_id' => $cita->id, 'recepcion_vinculo' => 'historico']);
            }
        }

        $this->info(($this->option('simular') ? 'Se ligarían ' : 'Se ligaron ').$ligadas.' de '.$solicitudes->count().' solicitudes con CURP desde '.$desde.'.');

        return self::SUCCESS;
    }
}
