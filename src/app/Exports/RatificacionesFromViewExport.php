<?php

namespace App\Exports;

use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\User; // Importación necesaria
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // IMPORTANTE: Agregar esto

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
        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];

        // 1. Consulta de Turnos con contadores y sumas individuales
        // Asumimos que en el modelo Turnos existe la relación: public function pagos() { return $this->hasMany(Pagos::class, 'id_solicitud'); }
        $ratificaciones = Turnos::whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->join('users', 'users.id', '=', 'turnos.id_conciliador')
            ->join('users as user_usuario', 'user_usuario.id', '=', 'turnos.user_id')
            ->select('turnos.*', 'users.name as conciliador_name', 'user_usuario.name as auxiliar')
            
            // Contamos cuántos pagos tiene cada turno según su estatus
            /*
            ->withCount([
                'pagos as pagos_pendientes_count' => function ($query) {
                    $query->where('estatus', 'Pendiente');
                },
                'pagos as pagos_pagados_count' => function ($query) {
                    $query->where('estatus', 'Pagado');
                }
            ])
            // Sumamos los montos de cada turno según su estatus
            ->withSum(['pagos as monto_pendientes' => function ($query) {
                $query->where('estatus', 'Pendiente');
            }], 'monto')
            ->withSum(['pagos as monto_pagados' => function ($query) {
                $query->where('estatus', 'Pagado');
            }], 'monto')
            */
            ->when($this->sede !== "Todos", function ($query) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $listaSedes = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $query->whereIn('turnos.delegacion', $listaSedes);
                }
                return $query->where('turnos.delegacion', $this->sede);
            })
            ->orderBy('user_usuario.name')
            ->get();

        // 2. Totales Globales (Para el resumen al final del Excel)
        $totalesGlobales = Pagos::whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->where('pago_solicitud.tipo_pago', "Ratificacion")
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $listaSedes = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('pago_solicitud.delegacion', $listaSedes);
                }
                return $q->where('pago_solicitud.delegacion', $this->sede);
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