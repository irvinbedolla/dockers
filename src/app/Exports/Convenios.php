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

class Convenios implements FromView
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

        
        $citadosSubquery = DB::table('seer_citados')
            ->select('id_solicitud', DB::raw("GROUP_CONCAT(CONCAT_WS(' ', nombre, primer_apellido, segundo_apellido) SEPARATOR ', ') as citados"))
            ->where('resulte_responsable', 'No')
            ->groupBy('id_solicitud');

        $pagosSubquery = DB::table('pago_solicitud')
            ->select('id_solicitud')
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pagado' AND fecha <= ? THEN id END) as cantidad_pagados", [$this->fecha_final])
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pendiente' OR (estatus = 'Pagado' AND fecha > ?) THEN id END) as cantidad_pendientes", [$this->fecha_final])
            ->selectRaw("SUM(CASE WHEN estatus = 'Pagado' AND fecha <= ? THEN monto ELSE 0 END) as monto_pagado", [$this->fecha_final])
            ->selectRaw("SUM(CASE WHEN estatus = 'Pendiente' OR (estatus = 'Pagado' AND fecha > ?) THEN monto ELSE 0 END) as monto_pendiente", [$this->fecha_final])
            ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
            ->groupBy('id_solicitud');

   
        $convenios = Audiencias::query()
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
            ->join('seer_general', function ($join) {
                $join->on('seer_general.id', '=', 'audiencias.id_solicitud')
                     ->where(function ($q) {
                         $q->where('seer_general.incidencia', 0)
                           ->orWhereNull('seer_general.incidencia');
                     });
            })
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
            
            ->leftJoinSub($citadosSubquery, 'citados_agrupados', function ($join) {
                $join->on('citados_agrupados.id_solicitud', '=', 'seer_general.id');
            })
            ->leftJoinSub($pagosSubquery, 'pagos_agrupados', function ($join) {
                $join->on('pagos_agrupados.id_solicitud', '=', 'seer_general.id');
            })
            // Filtro por delegación
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->select(
                DB::raw('DATE_FORMAT(audiencias.fecha, "%d-%m-%Y") as fecha_formateada'), 
                DB::raw('DATE_FORMAT(audiencias.hora, "%H:%i") as hora_formateada'),
                'seer_general.NUE', 
                'seer_solicitante.nombre',
                'users.name as conciliador_name',
                'audiencias.estatus',
                'citados_agrupados.citados', // Viene listo de la subconsulta
                // Usamos COALESCE por si no hay pagos, retorne 0 en lugar de null
                DB::raw('COALESCE(pagos_agrupados.cantidad_pagados, 0) as cantidad_pagados'),
                DB::raw('COALESCE(pagos_agrupados.cantidad_pendientes, 0) as cantidad_pendientes'),
                DB::raw('COALESCE(pagos_agrupados.monto_pagado, 0) as monto_pagado'),
                DB::raw('COALESCE(pagos_agrupados.monto_pendiente, 0) as monto_pendiente')
            )
            // Ya no es necesario el GROUP BY gigante ni el COUNT(DISTINCT)
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get();

        return view('excel.convenios', [
            'Convenios' => $convenios
        ]);
    }
}