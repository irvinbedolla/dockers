<?php

namespace App\Exports;

use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\SeerPerGeneral;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class ReporteGeneral implements FromView
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

        $solicitudes = DB::table('users')
            ->join('seer_general', 'users.id', '=', 'seer_general.user_id')
            ->leftJoin('pago_solicitud', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('seer_general.fecha', [$fecha_inicial, $fecha_final])
            ->when($sede !== "Todos", function ($q) use ($sede) {
                if ($sede === "TodosDelegado") {
                    $id = auth()->user()->id;
                    $user = User::find($id);
                    $sedeUsuario = $user->delegacion;
    
                    if($sedeUsuario == "Morelia"){
                        $delegaciones = ['Morelia', 'Zitácuaro'];
                        return $q->whereIn('seer_general.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Uruapan"){
                        $delegaciones = ['Uruapan', 'Lázaro Cárdenas'];
                        return $q->whereIn('seer_general.delegacion', $delegaciones);
                    }
                    else if($sedeUsuario == "Zamora"){
                    $delegaciones = ['Zamora', 'Sahuayo'];
                            return $q->whereIn('seer_general.delegacion', $delegaciones);
                        }
                    }
                    return $q->where("seer_general.delegacion", $sede);
                    })
                    ->select(
                        'users.id as user_id', 
                        'users.name',
                        DB::raw('COUNT(DISTINCT seer_general.id) as solicitudes'),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus NOT IN ('Pendiente','Prevencion','Rechazado') THEN seer_general.id END) as confirmadas"),
                        DB::raw("COUNT(DISTINCT CASE WHEN seer_general.estatus = 'Incompetencia' THEN seer_general.id END) as incompetencia"),
                        
                        // Totales de Audiencia (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.id END) as cumplimientoAudiencia"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMonto"),
                        
                        // Totales de Audiencia (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoAudienciaPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Audiencia' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoAudienciaMontPagado"),

                        // Totales de Ratificación vía Pago (General)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.id END) as cumplimientoRatificacion"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMonto"),

                        // Totales de Ratificación vía Pago (Pagado)
                        DB::raw("COUNT(DISTINCT CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.id END) as cumplimientoRatificacionPagado"),
                        DB::raw("SUM(CASE WHEN pago_solicitud.tipo_pago = 'Ratificacion' AND pago_solicitud.estatus = 'pagado' THEN pago_solicitud.monto ELSE 0 END) as cumplimientoRatificacionMontoPagado")
                    )
                    ->groupBy('users.id', 'users.name')
                    ->get()
                    ->keyBy('user_id');

                // 2. Consulta de Turnos (La parte de Ratificaciones que viene de otra tabla)
                $dataTurnos = DB::table('turnos')
                    ->join('pago_solicitud', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
                    ->where(function($query) {
                        $query->where('turnos.incidencia', 0)
                            ->orWhereNull('turnos.incidencia');
                    })
                    ->whereBetween('turnos.fecha', [$fecha_inicial, $fecha_final])
                    ->when($sede !== "Todos", function ($q) use ($sede) {
                        return $q->where('turnos.delegacion', $sede);
                    })
                    ->select(
                        'turnos.user_id',
                        DB::raw('COUNT(turnos.id) as ratificaciones'),
                        DB::raw('SUM(turnos.monto) as ratificacionesMonto')
                    )
                    ->groupBy('turnos.user_id')
                    ->get()
                    ->keyBy('user_id');

                // 3. Unir los resultados en una sola colección
                foreach ($solicitudes as $id => $solicitud) {
                    $turno = $dataTurnos->get($id);
                    $solicitud->ratificaciones = $turno ? $turno->ratificaciones : 0;
                    $solicitud->ratificacionesMonto = $turno ? $turno->ratificacionesMonto : 0;
                }


        return view('excel.reporte-mexico', ['reportes' => $todoJunto]);
    }

    /**
     * Función auxiliar para no repetir la lógica de la sede
     */
    private function aplicarFiltroSede($query, $tabla, $sedeUsuario) {
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            if (array_key_exists($sedeUsuario, $grupos)) {
                return $query->whereIn("$tabla.delegacion", $grupos[$sedeUsuario]);
            }
        }
        return $query->where("$tabla.delegacion", $this->sede);
    }
}