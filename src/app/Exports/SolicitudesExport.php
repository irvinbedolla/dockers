<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // IMPORTANTE: Agregar esto

class SolicitudesExport implements FromView
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

        $detalleSolicitantes = DB::table('seer_general')
        ->join('users', 'users.id', '=', 'seer_general.user_id')
        ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
        ->join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
        ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
        ->leftJoin('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
        ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
        
        // Join para Pagos (Filtrado por tipo)
        ->leftJoin('pago_solicitud', function($join) {
            $join->on('pago_solicitud.id_solicitud', '=', 'seer_general.id')
                ->whereIn('pago_solicitud.tipo_pago', ["Audiencia", "Conciliador"]);
        })

        // --- NUEVO JOIN PARA AUDIENCIAS ---
        //->leftJoin('audiencias', 'audiencias.id_solicitud', '=', 'seer_general.id')
        // ----------------------------------
        
        ->whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
        ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
            if ($this->sede === "TodosDelegado") {
                $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                return $q->whereIn('seer_general.delegacion', $delegaciones);
            }
            return $q->where("seer_general.delegacion", $this->sede);
        })
        ->select(
            'users.name as auxiliar',
            'seer_general.consecutivo as folio',
            'seer_general.fecha',
            'seer_general.fecha_confirmacion',
            'seer_general.NUE',
            'seer_general.estatus',
            'seer_general.delegacion',
            'seer_general.actividad',
            'seer_solicitante.nombre as solicitante_nombre',
            'seer_general.tipo_solicitud',
            'seer_solicitante.sexo',
            'seer_solicitante.sexo as detalle_audiencias',
            DB::raw('GROUP_CONCAT(DISTINCT catalogo_motivos.motivo SEPARATOR ", ") as motivos'),            
            DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT 
                CONCAT_WS(" ", seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) 
                ORDER BY seer_citados.id ASC SEPARATOR "|"), "|", 1) as primer_citado'),

            // Lógica de Pagos
            DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.id END) as cantidad_pagados"),
            DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.id END) as cantidad_pendientes"),
            DB::raw("SUM(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as monto_pagado"),
            DB::raw("SUM(DISTINCT CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as monto_pendiente"),

            // --- LÓGICA DE AUDIENCIAS POR REGISTRO ---
            // Contamos el total de audiencias
            //DB::raw("COUNT(DISTINCT audiencias.id) as total_audiencias"),
            // Formateamos como: FECHA (ESTATUS) y separamos cada audiencia con una coma
            //DB::raw("GROUP_CONCAT(DISTINCT 
            //CONCAT(audiencias.estatus) 
            //ORDER BY audiencias.fecha ASC SEPARATOR ', ') as detalle_audiencias")
            //DB::raw("GROUP_CONCAT(DISTINCT audiencias.estatus SEPARATOR ', ') as estados_audiencias")
        )
        ->groupBy(
            'users.name', 
            'seer_general.id', 
            'seer_general.NUE',
            'seer_general.consecutivo', 
            'seer_general.fecha', 
            'seer_general.fecha_confirmacion',
            'seer_general.estatus', 
            'seer_general.delegacion', 
            'seer_general.actividad', 
            'seer_solicitante.nombre',
            'seer_general.tipo_solicitud',
            'seer_solicitante.sexo'
        )
        ->orderBy('seer_general.consecutivo', 'desc')
        ->get();

        return view('excel.solicitudes', [
            'Solicitudes' => $detalleSolicitantes, // Corregido el nombre de la variable
        ]);
    }
}