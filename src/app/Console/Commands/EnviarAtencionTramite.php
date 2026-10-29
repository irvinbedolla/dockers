<?php

namespace App\Console\Commands;

use App\Support\AtencionTramite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Manda el folio y la liga del formulario de atención a las solicitudes cuya
 * audiencia se resolvió en los últimos días y todavía no lo han recibido (por
 * ejemplo, las que se resolvieron antes de instalar el formulario o si el
 * correo falló). Al resolverse una audiencia se manda solo (Audiencias::booted).
 */
class EnviarAtencionTramite extends Command
{
    protected $signature = 'atencion:enviar
        {--dias=15 : Audiencias resueltas en los últimos N días}
        {--simular : Sólo cuenta cuántos correos saldrían, no manda nada}';

    protected $description = 'Envía por correo el formulario de atención del trámite a las audiencias resueltas que no lo han recibido';

    public function handle(): int
    {
        if (! AtencionTramite::migrado()) {
            $this->error('Falta correr la migración del formulario de atención (php artisan migrate).');

            return self::FAILURE;
        }

        $dias = max(1, (int) $this->option('dias'));
        $simular = (bool) $this->option('simular');
        // Por la fecha de la audiencia resuelta: así no se escribe a casos de hace meses.
        $ids = DB::table('audiencias')
            ->whereIn('estatus', AtencionTramite::RESOLUCIONES)
            ->whereBetween('fecha', [now()->subDays($dias)->toDateString(), now()->toDateString()])
            ->distinct()
            ->orderBy('id_solicitud')
            ->pluck('id_solicitud');

        $correos = 0;
        $casos = 0;
        foreach ($ids as $id) {
            $n = AtencionTramite::enviar((int) $id, $simular);
            $correos += $n;
            $casos += $n > 0 ? 1 : 0;
        }

        $this->info(($simular ? 'Saldrían ' : 'Se enviaron ').$correos.' correos de '.$casos.' solicitudes (audiencias resueltas en los últimos '.$dias.' días: '.$ids->count().' solicitudes).');

        return self::SUCCESS;
    }
}
