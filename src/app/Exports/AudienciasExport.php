<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        \Illuminate\Support\Facades\DB::statement('SET SESSION SQL_BIG_SELECTS=1');
        $user = Auth::user();
        $sedeUsuario = $this->sede;

        // 1. Filtro de Sede
        $delegacionesFiltro = [$this->sede];
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            $delegacionesFiltro = $grupos[$sedeUsuario] ?? [$sedeUsuario];
        }

        // 2. Base de Audiencias
        $audienciasAgrupadas = DB::table('audiencias')
            ->whereBetween('fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('delegacion', $delegacionesFiltro);
            })
            ->select(
                'id_solicitud',
                DB::raw('MAX(fecha) as ultima_fecha'),
                DB::raw('MAX(hora) as ultima_hora')
            )
            ->groupBy('id_solicitud');

        // 3. Obtener el ÚLTIMO estatus (Reemplazo seguro usando IN + MAX)
        $ultimoEstatusSub = DB::table('audiencias')
            ->select('id_solicitud', 'estatus as ultimo_estatus')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                      ->from('audiencias')
                      ->groupBy('id_solicitud');
            });

        // 4. Obtener la ÚLTIMA resolución (Reemplazo seguro usando IN + MAX)
        $ultimaResolucionSub = DB::table('seer_conciliadores')
            ->select('id_solicitud', 'resolicion_primera as ultima_resolucion')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                      ->from('seer_conciliadores')
                      ->groupBy('id_solicitud');
            });

        // 5. Consulta Principal 
        $detalle = DB::table('seer_general')
            ->joinSub($audienciasAgrupadas, 'aud', 'aud.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('users as conciliador', 'conciliador.id', '=', 'seer_general.conciliador_id')
            
            // Unimos nuestras nuevas subconsultas seguras
            ->leftJoinSub($ultimoEstatusSub, 'estatus_sub', 'estatus_sub.id_solicitud', '=', 'seer_general.id')
            ->leftJoinSub($ultimaResolucionSub, 'resolucion_sub', 'resolucion_sub.id_solicitud', '=', 'seer_general.id')
            
            ->when($this->conciliador !== "Todos", function ($q) {
                return $q->where('seer_general.conciliador_id', $this->conciliador);
            })
            ->select(
                'seer_general.NUE',
                'aud.ultima_fecha as fecha',
                'aud.ultima_hora as hora',
                'seer_solicitante.nombre as nombre_solicitante',
                'conciliador.name as nombre_conciliador',
                'seer_general.delegacion',
                
                DB::raw("CASE 
                    WHEN estatus_sub.ultimo_estatus = 'No conciliacion' AND resolucion_sub.ultima_resolucion IS NULL
                    THEN 'No conciliacion (Incomparecencia)'
                    ELSE estatus_sub.ultimo_estatus
                END as estatus")
            )
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        return [
            new AudienciasDetalleSheet($detalle),
        ];
    }
}