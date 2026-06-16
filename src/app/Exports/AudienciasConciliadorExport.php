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

class AudienciasConciliadorExport implements FromView
{
    protected $fecha_inicial;
    protected $fecha_final;
    protected $sede;

    public function __construct(string $fecha_inicial, string $fecha_final, string $sede)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->sede = $sede;
    }

    public function view(): View
    {
        $user = Auth::user();
        $sedeUsuario = $user->delegacion ?? '';
        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];
        
        // Mantenemos la lógica de grupos de tu reporte de Motivos
        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];

        $aplicarFiltros = function ($q) use ($sedeUsuario, $grupos) {
            // Filtramos por la fecha de creación de la solicitud (igual que en Motivos)
            $q->whereBetween("audiencias.fecha", [$this->fecha_inicial, $this->fecha_final]);
            
            // Excluimos Pendientes y Prevenciones para que no inflen el número
            //$q->whereNotIn('audiencias.estatus', ['Pendiente', 'Prevencion']);

            if ($this->sede !== "Todos") {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    $q->whereIn("audiencias.delegacion", $delegaciones);
                } else {
                    $q->where("audiencias.delegacion", $this->sede);
                }
            }
        };

        $detalle = DB::table('audiencias')
            ->join('seer_general', 'seer_general.id', '=', 'audiencias.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as conciliador', 'conciliador.id', '=', 'seer_general.conciliador_id')
            ->join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
            ->where(fn($q) => $aplicarFiltros($q))
            /*
            ->when($this->conciliador !== "Todos", function ($q) {
                return $q->where('seer_general.conciliador_id', $this->conciliador);
            })*/
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