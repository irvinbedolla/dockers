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

class CumplimientosProgramadosExport implements FromView
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
        // Optimizamos la obtención del usuario
        $user = auth()->user();
        $sedeUsuario = $user->delegacion;


        // 1. Subconsulta para el primer citado (evitar duplicados)
        $subqueryCitados = DB::table('seer_citados')
            ->select('id_solicitud', DB::raw('MIN(id) as first_id'))
            ->groupBy('id_solicitud');

        // 2. Consulta de Ratificaciones
        $queryRatificaciones = Pagos::whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->join('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('users', 'users.id', '=', 'turnos.id_conciliador')
            ->where(function($query) {
                $query->where('turnos.incidencia', 0)
                    ->orWhereNull('turnos.incidencia');
            })
            ->select(
                DB::raw("DATE(pago_solicitud.fecha) as fecha"),
                'turnos.hora as hora_programada', // Extraemos la hora
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN turnos.NUE ELSE pago_solicitud.NUE END as NUE"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', turnos.trabajador, turnos.primero_trabajador, turnos.segundo_trabajador) 
                        ELSE pago_solicitud.nombre_trabajador END as nombre_trabajador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', turnos.empresa, turnos.primero_empresa, turnos.segundo_empresa) 
                        ELSE pago_solicitud.empresa_representante END as nombre_empleador"),
                DB::raw("pago_solicitud.delegacion as sede"),
                DB::raw("pago_solicitud.monto as monto_totalR"),
                'users.name as conciliador_name'
            )
            ->where('pago_solicitud.tipo_pago', "Ratificacion");

        // 3. Consulta de Audiencias
        $queryAudiencias = Pagos::whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->leftJoinSub($subqueryCitados, 'primera_cita', function ($join) {
                $join->on('seer_general.id', '=', 'primera_cita.id_solicitud');
            })
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->leftJoin('seer_citados', 'seer_citados.id', '=', 'primera_cita.first_id')
            ->leftJoin('users', 'users.id', '=', 'pago_solicitud.id_conciliador')
            ->select(
                DB::raw("DATE(pago_solicitud.fecha) as fecha"),
                'pago_solicitud.hora as hora_programada', // Extraemos la hora
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.NUE ELSE pago_solicitud.NUE END as NUE"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_solicitante.nombre ELSE pago_solicitud.nombre_trabajador END as nombre_trabajador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) 
                        ELSE pago_solicitud.empresa_representante END as nombre_empleador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.delegacion ELSE pago_solicitud.delegacion END as sede"),
                'users.name as conciliador_name',
                DB::raw("pago_solicitud.monto as monto_totalA"),
            )
            ->whereIn('pago_solicitud.tipo_pago', ["Audiencia", "Conciliador"]);

        // 4. Aplicar filtros de Sede a ambas consultas antes de unirlas
        foreach ([$queryRatificaciones, $queryAudiencias] as $query) {
            $query->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                if ($this->sede === "TodosDelegado") {
                    $grupos = [
                        'Morelia' => ['Morelia', 'Zitácuaro'],
                        'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                        'Zamora'  => ['Zamora', 'Sahuayo']
                    ];
                    if (array_key_exists($sedeUsuario, $grupos)) {
                        return $q->whereIn('pago_solicitud.delegacion', $grupos[$sedeUsuario]);
                    }
                }
                return $q->where('pago_solicitud.delegacion', $this->sede);
            });
        }
        // 5. UNIFICAR Y ORDENAR
        $resultadosUnificados = $queryRatificaciones
            ->unionAll($queryAudiencias)
            ->orderBy('fecha', 'asc')           // Fecha de menor a mayor
            ->orderBy('hora_programada', 'asc') // Hora de menor a mayor
            ->get();
        

        return view('excel.cumplimientosProgramados', [
            'cumplimientos' => $resultadosUnificados,
        ]);
    }
}