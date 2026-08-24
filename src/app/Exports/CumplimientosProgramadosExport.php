<?php

namespace App\Exports;

// Ya no necesitas importar App\Models\Pagos porque usaremos Query Builder puro
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
        $user = Auth::user();
        $sedeUsuario = $user->delegacion ?? '';

        // OPTIMIZACIÓN 1: Calculamos el filtro de delegaciones UNA SOLA VEZ fuera del query
        $delegacionesFiltro = [$this->sede];
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'],
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            $delegacionesFiltro = $grupos[$sedeUsuario] ?? [$sedeUsuario];
        }

        // Se mantiene para consultas pesadas sin índices óptimos
        DB::statement('SET SESSION SQL_BIG_SELECTS=1');

        $subqueryCitados = DB::table('seer_citados')
            ->select('id_solicitud', DB::raw('MIN(id) as first_id'))
            ->groupBy('id_solicitud');

        // OPTIMIZACIÓN 2: Usamos DB::table en lugar del Modelo Eloquent para ahorrar memoria RAM
        $queryRatificaciones = DB::table('pago_solicitud')
            ->whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->join('turnos', 'turnos.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('users', 'users.id', '=', 'turnos.id_conciliador')
            ->where(function($query) {
                $query->where('turnos.incidencia', 0)
                      ->orWhereNull('turnos.incidencia');
            })
            ->select(
                DB::raw("DATE(pago_solicitud.fecha) as fecha"),
                'turnos.hora as hora_programada',
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN turnos.NUE ELSE pago_solicitud.NUE END as NUE"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', turnos.trabajador, turnos.primero_trabajador, turnos.segundo_trabajador) 
                        ELSE pago_solicitud.nombre_trabajador END as nombre_trabajador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', turnos.empresa, turnos.primero_empresa, turnos.segundo_empresa) 
                        ELSE pago_solicitud.empresa_representante END as nombre_empleador"),
                'pago_solicitud.delegacion as sede',
                // CORRECCIÓN UNION: El monto va en la posición 8
                'pago_solicitud.monto as monto_total', 
                // CORRECCIÓN UNION: El conciliador va en la posición 9
                'users.name as conciliador_name'
            )
            ->where('pago_solicitud.tipo_pago', "Ratificacion");


        $queryAudiencias = DB::table('pago_solicitud')
            ->whereBetween('pago_solicitud.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->leftJoin('seer_general', 'seer_general.id', '=', 'pago_solicitud.id_solicitud')
            ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->leftJoinSub($subqueryCitados, 'primera_cita', 'seer_general.id', '=', 'primera_cita.id_solicitud')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                      ->orWhereNull('seer_general.incidencia');
            })
            ->leftJoin('seer_citados', 'seer_citados.id', '=', 'primera_cita.first_id')
            ->leftJoin('users', 'users.id', '=', 'pago_solicitud.id_conciliador')
            ->select(
                DB::raw("DATE(pago_solicitud.fecha) as fecha"),
                'pago_solicitud.hora as hora_programada',
                'pago_solicitud.tipo_pago',
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.NUE ELSE pago_solicitud.NUE END as NUE"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_solicitante.nombre ELSE pago_solicitud.nombre_trabajador END as nombre_trabajador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 
                        THEN CONCAT_WS(' ', seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) 
                        ELSE pago_solicitud.empresa_representante END as nombre_empleador"),
                DB::raw("CASE WHEN pago_solicitud.id_solicitud != 0 THEN seer_general.delegacion ELSE pago_solicitud.delegacion END as sede"),
                // CORRECCIÓN UNION: El monto va en la posición 8, igual que arriba
                'pago_solicitud.monto as monto_total',
                // CORRECCIÓN UNION: El conciliador va en la posición 9, igual que arriba
                'users.name as conciliador_name'
            )
            ->whereIn('pago_solicitud.tipo_pago', ["Audiencia", "Conciliador"]);

        // OPTIMIZACIÓN 3: Aplicamos el filtro usando la variable pre-calculada
        foreach ([$queryRatificaciones, $queryAudiencias] as $query) {
            $query->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('pago_solicitud.delegacion', $delegacionesFiltro);
            });
        }

        $resultadosUnificados = $queryRatificaciones
            ->unionAll($queryAudiencias)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_programada', 'asc')
            ->get();

        return view('excel.cumplimientosProgramados', [
            'cumplimientos' => $resultadosUnificados,
        ]);
    }
}