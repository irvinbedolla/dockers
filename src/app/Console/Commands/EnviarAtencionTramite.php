<?php

namespace App\Console\Commands;

use App\Support\AtencionTramite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Manda el folio y la liga del formulario de atención a las solicitudes que
 * ya concluyeron, siguen dentro del plazo y todavía no lo han recibido (por
 * ejemplo, las que concluyeron antes de instalar el formulario o si el correo
 * falló). Al concluir un caso se manda solo (SeerPerGeneral::booted).
 */
class EnviarAtencionTramite extends Command
{
    protected $signature = 'atencion:enviar {--simular : Sólo cuenta cuántos correos saldrían, no manda nada}';

    protected $description = 'Envía por correo el formulario de atención del trámite a las solicitudes concluidas que no lo han recibido';

    public function handle(): int
    {
        if (! AtencionTramite::migrado()) {
            $this->error('Falta correr la migración del formulario de atención (php artisan migrate).');

            return self::FAILURE;
        }

        $simular = (bool) $this->option('simular');
        $ids = DB::table('seer_general')
            ->whereIn('estatus', AtencionTramite::CONCLUIDOS)
            ->where('fecha', '>=', now()->subDays(AtencionTramite::DIAS)->toDateString())
            ->whereNotNull('NUE')
            ->orderBy('id')
            ->pluck('id');

        $correos = 0;
        $casos = 0;
        foreach ($ids as $id) {
            $n = AtencionTramite::enviar((int) $id, $simular);
            $correos += $n;
            $casos += $n > 0 ? 1 : 0;
        }

        $this->info(($simular ? 'Saldrían ' : 'Se enviaron ').$correos.' correos de '.$casos.' solicitudes (revisadas: '.$ids->count().').');

        return self::SUCCESS;
    }
}
