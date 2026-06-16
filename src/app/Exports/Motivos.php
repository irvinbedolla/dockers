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

    public function __construct(string $fecha_inicial, string $fecha_final, string $sede)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->sede = $sede;
    }

    /**
     * Define las categorías y el CASE SQL en un solo lugar.
     * $columna debe ser el nombre de la columna con la tabla (ej: 'catalogo_motivos.motivo')
     */
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

    /**
     * Asegura que todas las categorías existan con valor 0.
     */
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

    public function view(): View
    {
        $user = Auth::user();
        $sedeUsuario = $user->delegacion ?? '';
        
        $grupos = [
            'Morelia' => ['Morelia', 'Zitácuaro'],
            'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
            'Zamora'  => ['Zamora', 'Sahuayo']
        ];

        // Solicitudes Totales
            $solicitudesTotales = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultados = DB::table(DB::raw("({$solicitudesTotales->toSql()}) as base_limpia"))
            ->mergeBindings($solicitudesTotales) // Une los parámetros de fecha y sede a la consulta
            ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
            ->select(
                DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                DB::raw("COUNT(*) as total_general"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
            )
            ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
            ->get();

        // Solicitudes Totales
            $solicitudesConfirmadas = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosConfirmadas = DB::table(DB::raw("({$solicitudesConfirmadas->toSql()}) as base_limpia"))
            ->mergeBindings($solicitudesConfirmadas) // Une los parámetros de fecha y sede a la consulta
            ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
            ->select(
                DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                DB::raw("COUNT(*) as total_general"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
            )
            ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
            ->get();

        //Ratificaciones
            $ratificaciones = DB::table('turnos')
            ->where(function($query) {
                $query->where('turnos.incidencia', 0)
                    ->orWhereNull('turnos.incidencia');
            })
            ->whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('turnos.delegacion', $delegaciones);
                }
                return $q->where("turnos.delegacion", $this->sede);
            })
            ->select(
                'turnos.id',
                DB::raw("
                    CASE 
                        WHEN turnos.motivo = 'Terminación voluntaria de la relación de trabajo' THEN 7
                        WHEN turnos.motivo = 'Pago de prestaciones' THEN 2
                        ELSE 0 
                    END as id_motivo_principal
                "),
                DB::raw('MIN(turnos.sexo) as sexo_principal')
            )
            ->groupBy('turnos.id', 'turnos.motivo');

            // PASO 2: Realizar el conteo final agrupado por el CASE SQL
            $resultadosRatificaciones = DB::table(DB::raw("({$ratificaciones->toSql()}) as base_limpia"))
            ->mergeBindings($ratificaciones) // Une los parámetros de fecha y sede a la consulta
            ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
            ->select(
                DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                DB::raw("COUNT(*) as total_general"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
            )
            ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
            ->get();

        //Ratificaciones Confirmadas
            $ratificacionesConfirmadas = DB::table('turnos')
            ->where(function($query) {
                $query->where('turnos.incidencia', 0)
                    ->orWhereNull('turnos.incidencia');
            })
            ->whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('turnos.delegacion', $delegaciones);
                }
                return $q->where("turnos.delegacion", $this->sede);
            })
            ->whereIn('turnos.estatus',["Concluida","Concluida Pagos"])
            ->select(
                'turnos.id',
                DB::raw("
                    CASE 
                        WHEN turnos.motivo = 'Terminación voluntaria de la relación de trabajo' THEN 7
                        WHEN turnos.motivo = 'Pago de prestaciones' THEN 2
                        ELSE 0 
                    END as id_motivo_principal
                "),
                DB::raw('MIN(turnos.sexo) as sexo_principal')
            )
            ->groupBy('turnos.id', 'turnos.motivo');

            // PASO 2: Realizar el conteo final agrupado por el CASE SQL
            $resultadosratificacionesConfirmadas = DB::table(DB::raw("({$ratificacionesConfirmadas->toSql()}) as base_limpia"))
                ->mergeBindings($ratificacionesConfirmadas) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();

        //ARCHIVADAS POR FALTA DE INTERES
            $Archivadas = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->where('audiencias.estatus',"Archivada")
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosArchivadas = DB::table(DB::raw("({$Archivadas->toSql()}) as base_limpia"))
                ->mergeBindings($Archivadas) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //CELEBRADAS
            $celebradas = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->whereIn('audiencias.estatus',['Conciliacion','Reinstalacion','No conciliacion reagendada'])
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosCelebradas= DB::table(DB::raw("({$celebradas->toSql()}) as base_limpia"))
                ->mergeBindings($celebradas) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //INCOMPETENCIA
            $Incompetencia = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->where('seer_general.estatus',"Incomparecencia")
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosIncompetencia = DB::table(DB::raw("({$Incompetencia->toSql()}) as base_limpia"))
                ->mergeBindings($Incompetencia) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //NO CONCILIACION EN AUDIENCIA
            $archivadaAudiencia = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->join('seer_citados','seer_citados.id_solicitud', '=', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            //->where('audiencias.estatus',"Archivada en Audiencia")
            ->where('audiencias.estatus',"No conciliacion")
            ->whereNotNull('seer_citados.id_abogado')
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosarchivadaAudiencia = DB::table(DB::raw("({$archivadaAudiencia->toSql()}) as base_limpia"))
                ->mergeBindings($archivadaAudiencia) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //PROGRAMADAS
            $Programadas = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            //->whereIn('audiencias.estatus',['No conciliacion','Reagendada','Archivada','Desistimiento','Pendiente'])
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosProgramadas = DB::table(DB::raw("({$Programadas->toSql()}) as base_limpia"))
                ->mergeBindings($Programadas) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //CONVENIOS
            $Convenios = DB::table('seer_general')
            ->join('users', 'users.id', '=', 'seer_general.user_id')
            ->join('seer_motivos', 'seer_motivos.id_solicitud', '=', 'seer_general.id')
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('audiencias', 'audiencias.id_solicitud', 'seer_general.id')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                    ->orWhereNull('seer_general.incidencia');
            })
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->whereIn('audiencias.estatus',['Conciliacion'])
            ->select(
                'seer_general.id',
                // Subconsulta para el primer motivo ingresado
                DB::raw('(SELECT id_motivo FROM seer_motivos WHERE id_solicitud = seer_general.id ORDER BY id ASC LIMIT 1) as id_motivo_principal'),
                // Si hay 2 solicitantes, MIN asegura un solo sexo para el conteo
                DB::raw('MIN(seer_solicitante.sexo) as sexo_principal')
            )
            ->groupBy('seer_general.id');

            $resultadosConvenios = DB::table(DB::raw("({$Convenios->toSql()}) as base_limpia"))
                ->mergeBindings($Convenios) // Une los parámetros de fecha y sede a la consulta
                ->leftJoin('catalogo_motivos', 'catalogo_motivos.id', '=', 'base_limpia.id_motivo_principal')
                ->select(
                    DB::raw($this->getSqlCase('catalogo_motivos.motivo') . " as categoria"),
                    DB::raw("COUNT(*) as total_general"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'H' THEN 1 ELSE 0 END) as total_hombres"),
                    DB::raw("SUM(CASE WHEN base_limpia.sexo_principal = 'M' THEN 1 ELSE 0 END) as total_mujeres")
                )
                ->groupBy(DB::raw($this->getSqlCase('catalogo_motivos.motivo')))
                ->get();
        //Fin de consultas


        //Return
        return view('excel.motivos', [
            'solicitudes'                           => $this->formatearResultados($resultados),
            'solicitudesConfirmadas'                => $this->formatearResultados($resultadosConfirmadas),
            'ratificaciones'                        => $this->formatearResultados($resultadosRatificaciones),
            'resultadosratificacionesConfirmadas'   => $this->formatearResultados($resultadosratificacionesConfirmadas),
            'archivadas'                            => $this->formatearResultados($resultadosArchivadas),
            'celebradas'                            => $this->formatearResultados($resultadosCelebradas),
            'incompetencia'                         => $this->formatearResultados($resultadosIncompetencia),
            'archivadaAudiencia'                    => $this->formatearResultados($resultadosarchivadaAudiencia),
            'programadas'                           => $this->formatearResultados($resultadosProgramadas),
            'convenios'                             => $this->formatearResultados($resultadosConvenios),
        ]);
    }
}