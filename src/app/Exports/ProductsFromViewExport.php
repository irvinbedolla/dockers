<?php

namespace App\Exports;

use App\Models\Pagos;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class ProductsFromViewExport implements FromView
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
        $user = auth()->user();
        $sedeUsuario = $user->delegacion ?? '';

        $queryBase = Pagos::query()
            ->whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                if ($this->sede === "TodosDelegado") {
                    $grupos = [
                        'Morelia' => ['Morelia', 'Zitácuaro'],
                        'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                        'Zamora'  => ['Zamora', 'Sahuayo']
                    ];
                    
                    // Si no existe en el grupo, toma su propia sede como fallback
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('pago_solicitud.delegacion', $delegaciones);
                }
                
                return $q->where('pago_solicitud.delegacion', $this->sede);
            });
   
        $pagosRatificacion = (clone $queryBase)
            ->where('pago_solicitud.tipo_pago', "Ratificacion")
            ->join('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('users', 'users.id', '=', 'turnos.id_conciliador')
            ->select(
                'pago_solicitud.id_solicitud',
                'pago_solicitud.fecha',
                'pago_solicitud.estatus',
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN turnos.NUE ELSE pago_solicitud.NUE END as NUE"),
                
                
                DB::raw("COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.id END) as cantidad_pagados"),
                DB::raw("COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.id END) as cantidad_pendientes"),
                DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as monto_pagado"),
                DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as monto_pendiente"),
                
                'users.name as conciliador_name',
                DB::raw("SUM(pago_solicitud.monto) as monto_totalR")
            )
            ->groupBy(
                'pago_solicitud.id_solicitud',
                'pago_solicitud.NUE',
                'pago_solicitud.fecha',
                'pago_solicitud.estatus',
                'pago_solicitud.tipo_pago',
                'turnos.NUE',
                'users.name'
            )
            ->get();
            
  
        $pagosAudiencias = (clone $queryBase)
            ->whereIn('pago_solicitud.tipo_pago', ["Audiencia", "Conciliador"])
            ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('users', 'users.id', '=', 'pago_solicitud.id_conciliador')
            ->select(
                'pago_solicitud.id_solicitud',
                'pago_solicitud.fecha',
                'pago_solicitud.estatus',
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.NUE ELSE pago_solicitud.NUE END as NUE"),
                
                DB::raw("COUNT(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.id END) as cantidad_pagados"),
                DB::raw("COUNT(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.id END) as cantidad_pendientes"),
                DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pagado' THEN pago_solicitud.monto ELSE 0 END) as monto_pagado"),
                DB::raw("SUM(CASE WHEN pago_solicitud.estatus = 'Pendiente' THEN pago_solicitud.monto ELSE 0 END) as monto_pendiente"),
                
                DB::raw("SUM(pago_solicitud.monto) as monto_totalA"),
                'users.name as conciliador_name'
            )
            ->groupBy(
                'pago_solicitud.id_solicitud',
                'pago_solicitud.NUE',
                'pago_solicitud.fecha',
                'pago_solicitud.estatus',
                'pago_solicitud.tipo_pago',
                'seer_general.NUE',
                'users.name'
            )
            ->get();
       
        return view('excel.cumplimientos', [
            'pagosRatificacion' => $pagosRatificacion,
            'pagosAudiencias'   => $pagosAudiencias
        ]);
    }
}