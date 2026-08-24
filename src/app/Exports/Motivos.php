<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Motivos implements FromView
{
    protected $fecha_inicial;
    protected $fecha_final;
    protected $sede;
    protected $delegacionesFiltro;

    public function __construct(string $fecha_inicial, string $fecha_final, string $sede)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->sede = $sede;
        $this->delegacionesFiltro = $this->calcularDelegaciones($sede);
    }

    /**
     * Calcula las delegaciones una sola vez para no repetir el IF en cada consulta
     */
    private function calcularDelegaciones($sede)
    {
        if ($sede !== "TodosDelegado") {
            return [$sede];
        }

        $sedeUsuario = Auth::user()->delegacion ?? '';
        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];

        return $grupos[$sedeUsuario] ?? [$sedeUsuario];
    }

    private function getSqlCase($columna)
    {
        return "CASE 
            WHEN $columna IN ('Despido') THEN 'a. Despido injustificado'                    
            WHEN $columna IN ('Rescisión de la relación de trabajo') THEN 'b. Finiquito por rescisión laboral'
            WHEN $columna IN ('Derecho de preferencia', 'Derecho de antigüedad', 'Derecho de ascenso') THEN 'c. Derecho de preferencia (antigüedad o ascenso)'
            WHEN $columna IN ('Pago de prestaciones') THEN 'd. Pago de prestaciones pendientes'
            WHEN $columna IN ('Terminación voluntaria de la relación de trabajo') THEN 'e. Terminación voluntaria de la relación laboral'
            WHEN $columna IN ('Excepcion', 'Excepción') THEN 'f. Supuestos de Excepción 685-Ter LFT'
            ELSE 'g. Otros'
        END";
    }

    private function formatearResultados($datosQuery)
    {
        $mapa = collect([
            'a. Despido injustificado' => ['h' => 0, 'm' => 0, 'total' => 0],
            'b. Finiquito por rescisión laboral' => ['h' => 0, 'm' => 0, 'total' => 0],
            'c. Derecho de preferencia (antigüedad o ascenso)' => ['h' => 0, 'm' => 0, 'total' => 0],
            'd. Pago de prestaciones pendientes' => ['h' => 0, 'm' => 0, 'total' => 0],
            'e. Terminación voluntaria de la relación laboral' => ['h' => 0, 'm' => 0, 'total' => 0],
            'f. Supuestos de Excepción 685-Ter LFT' => ['h' => 0, 'm' => 0, 'total' => 0],
            'g. Otros' => ['h' => 0, 'm' => 0, 'total' => 0],
        ]);

        foreach ($datosQuery as $registro) {
            if ($mapa->has($registro->categoria)) {
                $mapa->put($registro->categoria, [
                    'h' => (int)$registro->total_hombres,
                    'm' => (int)$registro->total_mujeres,
                    'total' => (int)$registro->total_general,
                ]);
            }
        }
        return $mapa;
    }

    /**
     * Construye la consulta base para las solicitudes (seer_general)
     */
    private function getBaseGeneralQuery($columnaFecha, \Closure $filtrosAdicionales = null)
    {
        $query = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where(function($q) {
                $q->where('seer_general.incidencia', 0)->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween($columnaFecha, [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) {
                return $q->whereIn('seer_general.delegacion', $this->delegacionesFiltro);
            })
            ->select(
                'seer_general.id',
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

        if ($filtrosAdicionales) {
            $filtrosAdicionales($query);
        }

        return $this->procesarAgrupacionFinal($query);
    }

    /**
     * Construye la consulta base para los turnos (Ratificaciones)
     */
    private function getBaseTurnosQuery(\Closure $filtrosAdicionales = null)
    {
        $query = DB::table('turnos')
            ->where(function($q) {
                $q->where('turnos.incidencia', 0)->orWhereNull('turnos.incidencia');
            })
            ->whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) {
                return $q->whereIn('turnos.delegacion', $this->delegacionesFiltro);
            })
            ->select(
                'turnos.id',
                DB::raw("CASE 
                        WHEN turnos.motivo = 'Terminación voluntaria de la relación de trabajo' THEN 7
                        WHEN turnos.motivo = 'Pago de prestaciones' THEN 2
                        ELSE 0 
                    END as id_motivo_principal"),
                DB::raw('MIN(turnos.sexo) as sexo_principal')
            )
            ->groupBy('turnos.id', 'turnos.motivo');

        if ($filtrosAdicionales) {
            $filtrosAdicionales($query);
        }

        return $this->procesarAgrupacionFinal($query);
    }

    /**
     * Envuelve la consulta base y realiza el conteo final de categorías
     */
    private function procesarAgrupacionFinal($baseQuery)
    {
        $resultados = DB::table(DB::raw("({$baseQuery->toSql()}) as base_limpia"))
            ->mergeBindings($baseQuery)
            ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
            ->select(
                DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                DB::raw("COUNT(*) as total_general"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
            )
            ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
            ->get();

        return $this->formatearResultados($resultados);
    }

    public function view(): View
    {
        DB::statement('SET SESSION SQL_BIG_SELECTS=1');

        return view('excel.motivos', [
            'solicitudes' => $this->getBaseGeneralQuery('seer_general.fecha'),
            
            // NOTA: Aquí agregué un filtro ficticio. En tu código original era idéntico a "solicitudes"
            'solicitudesConfirmadas' => $this->getBaseGeneralQuery('seer_general.fecha', function($q) {
                $q->whereNotNull('seer_general.fecha_confirmacion'); // <-- AJUSTA ESTE FILTRO A TU LÓGICA REAL
            }),

            'ratificaciones' => $this->getBaseTurnosQuery(),
            
            'resultadosratificacionesConfirmadas' => $this->getBaseTurnosQuery(function($q) {
                $q->whereIn('turnos.estatus', ["Concluida", "Concluida Pagos"]);
            }),

            'archivadas' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                  ->where('audiencias.estatus', "Archivada");
            }),

            'celebradas' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                  ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion', 'No conciliacion reagendada']);
            }),

            'incompetencia' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                  ->where('seer_general.estatus', "Incomparecencia");
            }),

            'archivadaAudiencia' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                  ->join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
                  ->where('audiencias.estatus', "No conciliacion")
                  ->whereNotNull('seer_citados.id_abogado');
            }),

            'programadas' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id');
            }),

            'convenios' => $this->getBaseGeneralQuery('audiencias.fecha', function($q) {
                $q->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
                  ->whereIn('audiencias.estatus', ['Conciliacion']);
            }),
        ]);
    }
}