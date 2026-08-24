<?php

namespace App\Exports;

use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\User; // Importación necesaria
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatificacionesFromViewExport implements FromView
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
        
        
        $delegacionesFiltro = [$this->sede];
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            // Si la sede no está en el grupo, toma su propia sede
            $delegacionesFiltro = $grupos[$sedeUsuario] ?? [$sedeUsuario];
        }

        
        $pagosSubquery = DB::table('pago_solicitud')
            ->select('id_solicitud')
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pendiente' THEN id END) as pagos_pendientes_count")
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pagado' THEN id END) as pagos_pagados_count")
            ->selectRaw("SUM(CASE WHEN estatus = 'Pendiente' THEN monto ELSE 0 END) as monto_pendientes")
            ->selectRaw("SUM(CASE WHEN estatus = 'Pagado' THEN monto ELSE 0 END) as monto_pagados")
            // Si los pagos en 'turnos' siempre son de tipo Ratificacion, esto mejora la velocidad
            ->where('tipo_pago', 'Ratificacion') 
            ->groupBy('id_solicitud');

      
        $ratificaciones = Turnos::query()
            ->whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($query) use ($delegacionesFiltro) {
                // Usamos whereIn aprovechando el arreglo que creamos arriba
                return $query->whereIn('turnos.delegacion', $delegacionesFiltro);
            })
            ->join('users', 'users.id', '=', 'turnos.id_conciliador')
            ->join('users as user_usuario', 'user_usuario.id', '=', 'turnos.user_id')
            
            // Unimos la subconsulta de pagos
            ->leftJoinSub($pagosSubquery, 'pagos_agrupados', function ($join) {
                $join->on('turnos.id', '=', 'pagos_agrupados.id_solicitud');
            })
            ->select(
                'turnos.*', 
                'users.name as conciliador_name', 
                'user_usuario.name as auxiliar',
                
                // Usamos COALESCE para devolver 0 si no hay pagos (en lugar de null)
                DB::raw('COALESCE(pagos_agrupados.pagos_pendientes_count, 0) as pagos_pendientes_count'),
                DB::raw('COALESCE(pagos_agrupados.pagos_pagados_count, 0) as pagos_pagados_count'),
                DB::raw('COALESCE(pagos_agrupados.monto_pendientes, 0) as monto_pendientes'),
                DB::raw('COALESCE(pagos_agrupados.monto_pagados, 0) as monto_pagados')
            )
            ->orderBy('user_usuario.name')
            ->get();

        $totalesGlobales = DB::table('pago_solicitud')
            ->whereBetween('fecha', [$this->fecha_inicial, $this->fecha_final])
            ->where('tipo_pago', "Ratificacion")
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('delegacion', $delegacionesFiltro);
            })
            ->selectRaw("
                SUM(CASE WHEN estatus = 'Pendiente' THEN monto ELSE 0 END) as global_monto_pendientes,
                SUM(CASE WHEN estatus = 'Pagado' THEN monto ELSE 0 END) as global_monto_pagados
            ")
            ->first();

        return view('excel.ratificaciones', [
            'Ratificacion' => $ratificaciones,
            'TotalesGlobales' => $totalesGlobales
        ]);
    }
}