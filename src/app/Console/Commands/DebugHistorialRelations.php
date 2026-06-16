<?php

namespace App\Console\Commands;

use App\Models\HistorialAbogado;
use Illuminate\Console\Command;

class DebugHistorialRelations extends Command
{

    protected $signature = 'debug:historial-relations {id? : ID del historial a inspeccionar}';
    protected $description = 'Depura relaciones de HistorialAbogado (estado/municipio patronal).';

    public function handle(): int
    {
        $id = $this->argument('id');

        $q = HistorialAbogado::query()->with(['estadoPatronal', 'municipioPatronal']);
        $h = $id ? $q->find($id) : $q->first();

        if (!$h) {
            $this->error('No se encontró historial.');
            return self::FAILURE;
        }

        $this->info('HistorialAbogado id=' . $h->id);
        $this->line('estado_patronal (FK)   : ' . ($h->estado_patronal ?? 'NULL'));
        $this->line('municipio_patronal (FK): ' . ($h->municipio_patronal ?? 'NULL'));
        $this->line('estadoPatronal->nombre : ' . ($h->estadoPatronal->nombre ?? 'NULL'));
        $this->line('municipioPatronal->nombre: ' . ($h->municipioPatronal->nombre ?? 'NULL'));

        return self::SUCCESS;
    }
}
