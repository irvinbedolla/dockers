<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        $delegacionesFiltro = [$this->sede];
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            $delegacionesFiltro = $grupos[$sedeUsuario] ?? [$sedeUsuario];
        }

        $motivosSub = DB::table('seer_motivos')
            ->join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
            ->select('id_solicitud', DB::raw('GROUP_CONCAT(catalogo_motivos.motivo SEPARATOR ", ") as motivos'))
            ->groupBy('id_solicitud');


        $primerCitadoSub = DB::table('seer_citados')
            ->select('id_solicitud', DB::raw('MIN(id) as primer_citado_id'))
            ->groupBy('id_solicitud');


        $pagosSub = DB::table('pago_solicitud')
            ->select('id_solicitud')
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pagado' THEN id END) as cantidad_pagados")
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pendiente' THEN id END) as cantidad_pendientes")
            ->selectRaw("SUM(CASE WHEN estatus = 'Pagado' THEN monto ELSE 0 END) as monto_pagado")
            ->selectRaw("SUM(CASE WHEN estatus = 'Pendiente' THEN monto ELSE 0 END) as monto_pendiente")
            ->whereIn('tipo_pago', ["Audiencia", "Conciliador"])
            ->groupBy('id_solicitud');

        // Subconsulta D (Opcional): Audiencias (La preparo por si quieres descomentarla después)
        /*
        $audienciasSub = DB::table('audiencias')
            ->select('id_solicitud')
            ->selectRaw("COUNT(id) as total_audiencias")
            ->selectRaw("GROUP_CONCAT(estatus ORDER BY fecha ASC SEPARATOR ', ') as detalle_audiencias")
            ->groupBy('id_solicitud');
        */


        $detalleSolicitantes = DB::table('seer_general')
            ->whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                      ->orWhereNull('seer_general.incidencia');
            })
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('seer_general.delegacion', $delegacionesFiltro);
            })
            

            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            
            ->leftJoinSub($motivosSub, 'motivos_agrupados', 'motivos_agrupados.id_solicitud', '=', 'seer_general.id')
            ->leftJoinSub($pagosSub, 'pagos_agrupados', 'pagos_agrupados.id_solicitud', '=', 'seer_general.id')
            // Para el citado, unimos la subconsulta del ID mínimo, y luego unimos la tabla real para sacar sus datos
            ->leftJoinSub($primerCitadoSub, 'citado_minimo', 'citado_minimo.id_solicitud', '=', 'seer_general.id')
            ->leftJoin('seer_citados', 'seer_citados.id', '=', 'citado_minimo.primer_citado_id')
            
            // ->leftJoinSub($audienciasSub, 'audiencias_agrupadas', 'audiencias_agrupadas.id_solicitud', '=', 'seer_general.id')
            
            ->select(
                'users.name as auxiliar',
                'seer_general.consecutivo as folio',
                'seer_general.fecha',
                'seer_general.fecha_confirmacion',
                'seer_general.NUE',
                'seer_general.estatus',
                'seer_general.delegacion',
                'seer_general.actividad',
                'seer_general.tipo_solicitud',
                'seer_solicitante.nombre as solicitante_nombre',
                'seer_solicitante.sexo',
                'seer_solicitante.telefono1',
                
                'motivos_agrupados.motivos',
                DB::raw('CONCAT_WS(" ", seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) as primer_citado'),
                DB::raw("COALESCE(pagos_agrupados.cantidad_pagados, 0) as cantidad_pagados"),
                DB::raw("COALESCE(pagos_agrupados.cantidad_pendientes, 0) as cantidad_pendientes"),
                DB::raw("COALESCE(pagos_agrupados.monto_pagado, 0) as monto_pagado"),
                DB::raw("COALESCE(pagos_agrupados.monto_pendiente, 0) as monto_pendiente")
                
                /* Datos de audiencias si lo descomentas:
                DB::raw("COALESCE(audiencias_agrupadas.total_audiencias, 0) as total_audiencias"),
                'audiencias_agrupadas.detalle_audiencias'
                */
            )
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get();

        return view('excel.solicitudes', [
            'Solicitudes' => $detalleSolicitantes,
        ]);
    }
}