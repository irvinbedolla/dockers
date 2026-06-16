<?php

namespace App\Exports;

use App\Models\SeerPerGeneral;
use App\Models\Pagos;
use App\Models\Audiencias;
use App\Models\User; // Importación necesaria
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AudienciasPORConciliadorExport implements FromView
{
    protected $fecha_inicial;
    protected $fecha_final;
    protected $idConcilaidor;

    public function __construct(string $fecha_inicial, string $fecha_final, int $idConcilaidor)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->idConcilaidor = $idConcilaidor;
    }

    public function view(): View
    {
        $detalle = DB::table('audiencias')
            ->join('seer_general', 'seer_general.id', '=', 'audiencias.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as conciliador', 'conciliador.id', '=', 'seer_general.conciliador_id')
            ->join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
            ->whereBetween("audiencias.fecha", [$this->fecha_inicial, $this->fecha_final])
            ->where('audiencias.id_conciliador', '=', $this->idConcilaidor)
            ->select(
                'seer_general.NUE',
                'audiencias.fecha',
                'audiencias.hora',
                'seer_solicitante.nombre as nombre_solicitante',
                'conciliador.name as nombre_conciliador',
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT 
                CONCAT_WS(" ", seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) 
                ORDER BY seer_citados.id ASC SEPARATOR "|"), "|", 1) as primer_citado'),
                ) 
        ->groupBy(
            'seer_general.NUE',
            'audiencias.fecha',
            'audiencias.hora',
            'seer_solicitante.nombre',
            'conciliador.name'
        )
        ->orderBy('audiencias.fecha', 'asc')
        ->orderBy('audiencias.hora', 'asc')
        ->get();


        return view('excel.audienciasConcliliador', [
            'audiencias' => $detalle
        ]);
    }
}