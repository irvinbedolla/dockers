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

        // 1. Consulta de Turnos con contadores y sumas individuales
        // Asumimos que en el modelo Turnos existe la relación: public function pagos() { return $this->hasMany(Pagos::class, 'id_solicitud'); }
        $convenios = Audiencias::whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
            ->join('seer_general', 'seer_general.id', '=', 'audiencias.id_solicitud')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('seer_citados', 'seer_citados.id_solicitud','=', 'seer_general.id')
            ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
            
            // Join para Pagos (Filtrado por tipo)
            ->leftJoin('pago_solicitud', function($join) {
                $join->on('pago_solicitud.id_solicitud', '=', 'seer_general.id')
                    ->whereIn('pago_solicitud.tipo_pago', ["Audiencia", "Conciliador"]);
            })
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->where('seer_citados.resulte_responsable', 'No')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            
            ->select(
                DB::raw('DATE_FORMAT(audiencias.fecha, "%d-%m-%Y") as fecha_formateada'), 
                DB::raw('DATE_FORMAT(audiencias.hora, "%H:%i") as hora_formateada'),
                'seer_general.NUE', 'seer_solicitante.nombre','users.name as conciliador_name','audiencias.estatus',
                DB::raw("GROUP_CONCAT(
                CONCAT_WS(' ', seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) 
                    SEPARATOR ', '
                ) as citados"),
                // Lógica de Pagos
                DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha <= '{$this->fecha_final}' THEN pago_solicitud.id END) as cantidad_pagados"),
                DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pendiente' OR (pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha > '{$this->fecha_final}') THEN pago_solicitud.id END) as cantidad_pendientes"),
                DB::raw("SUM(CASE WHEN (pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha <= '{$this->fecha_final}') THEN pago_solicitud.monto ELSE 0 END) / COUNT(DISTINCT seer_citados.id) as monto_pagado"),
                DB::raw("SUM(CASE WHEN (pago_solicitud.estatus = 'Pendiente' OR (pago_solicitud.estatus = 'Pagado' AND pago_solicitud.fecha > '{$this->fecha_final}')) THEN pago_solicitud.monto ELSE 0 END) / COUNT(DISTINCT seer_citados.id) as monto_pendiente"),
            )
            ->groupBy(
                'audiencias.fecha', 
                'audiencias.hora', 
                'seer_general.NUE', 
                'seer_solicitante.nombre',
                'users.name',
                'audiencias.estatus'
            )
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get();

        return view('excel.convenios', [
            'Convenios' => $convenios
        ]);
    }
}