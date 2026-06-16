<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AudienciasExport implements WithMultipleSheets
{
    protected $fecha_inicial, $fecha_final, $sede, $conciliador;

    public function __construct($fecha_inicial, $fecha_final, $sede, $conciliador)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->sede = $sede;
        $this->conciliador = $conciliador;
    }

    public function sheets(): array
    {
        $user = Auth::user();
        $sedeUsuario = $this->sede;

        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];

        // Definición de filtros reutilizables
        $aplicarFiltros = function ($q) use ($sedeUsuario, $grupos) {
            $q->whereBetween("audiencias.fecha", [$this->fecha_inicial, $this->fecha_final]);
            
            if ($this->sede !== "Todos") {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    $q->whereIn("audiencias.delegacion", $delegaciones);
                } else {
                    $q->where("audiencias.delegacion", $this->sede);
                }
            }
        };

        $audienciasPorExpediente = DB::table('audiencias')
                ->select('id_solicitud', DB::raw('COUNT(*) as total_audiencias'))
                ->where(fn($q) => $aplicarFiltros($q))
                ->groupBy('id_solicitud');


        $detalle = DB::table('audiencias')
            ->join('seer_general', 'seer_general.id', '=', 'audiencias.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as conciliador', 'conciliador.id', '=', 'seer_general.conciliador_id')
            ->joinSub($audienciasPorExpediente, 'a_count', function ($join) {
                $join->on('a_count.id_solicitud', '=', 'seer_general.id');
            })
            ->when($this->conciliador !== "Todos", function ($q) {
                return $q->where('seer_general.conciliador_id', $this->conciliador);
            })
            ->select(
                'seer_general.NUE',
                DB::raw('MAX(audiencias.fecha) as fecha'),
                DB::raw('MAX(audiencias.hora) as hora'),
                'seer_solicitante.nombre as nombre_solicitante',
                'conciliador.name as nombre_conciliador',
                'seer_general.delegacion',
                DB::raw("CASE 
                            WHEN (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1) = 'No conciliacion'
                                AND (SELECT sc.resolicion_primera FROM seer_conciliadores sc WHERE sc.id_solicitud = seer_general.id ORDER BY sc.id DESC LIMIT 1) IS NULL
                            THEN 'No conciliacion (Incomparecencia)'
                            ELSE (SELECT a_last.estatus FROM audiencias a_last WHERE a_last.id_solicitud = seer_general.id ORDER BY a_last.id DESC LIMIT 1)
                        END as estatus")
            )
            ->groupBy(
                'seer_general.id', 
                'seer_general.NUE', 
                'seer_solicitante.nombre', 
                'conciliador.name', 
                'seer_general.delegacion'
            )
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // Devolvemos únicamente la hoja de detalle
        return [
            new AudienciasDetalleSheet($detalle),
        ];
    }
}