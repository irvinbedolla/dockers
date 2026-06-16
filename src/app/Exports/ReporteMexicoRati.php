<?php

namespace App\Exports;

use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\SeerPerGeneral;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class ReporteMexicoRati implements FromView
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

        // Subconsultas (sin cambios)
        $subconsultaMotivos = DB::table('seer_motivos')
            ->join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
            ->whereColumn('seer_motivos.id_solicitud', 'seer_general.id')
            ->select(DB::raw('COALESCE(GROUP_CONCAT(catalogo_motivos.motivo SEPARATOR ", "), "N/A")'));

        $subconsultaPagosTurnos = Pagos::select(DB::raw('SUM(monto)'))
            ->whereColumn('pago_solicitud.id_solicitud', 'turnos.id')
            ->where('pago_solicitud.tipo_pago', 'Ratificacion');

        $subconsultaPagosSeer = Pagos::select(DB::raw('SUM(monto)'))
            ->whereColumn('pago_solicitud.id_solicitud', 'seer_general.id')
            ->whereIn('pago_solicitud.tipo_pago', ['Audiencia','Conciliador']); 

        // --- CONSULTA 1: TURNOS ---
        $reportes = Turnos::whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereNotIn('turnos.estatus', ['Pendiente', 'Prevencion', 'Confirmado'])
            ->leftJoin('users', 'users.id', '=', 'turnos.user_id')
            ->leftJoin('estados', 'estados.id', '=', 'turnos.estado_rat')
            ->leftJoin('municipios', 'municipios.id', '=', 'turnos.municipio_rat')
            ->leftJoin('abogados', 'abogados.idAbogado', '=', 'turnos.idAbogado')
            ->leftJoin('municipios as mun_abogado', 'mun_abogado.id', '=', 'abogados.municipio_patronal')
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                return $this->aplicarFiltroSede($q, 'turnos', $sedeUsuario);
            })
            ->select(
                'turnos.id',
                'turnos.NUE',
                DB::raw('MAX(MONTH(turnos.fecha)) as mes'),
                DB::raw('MAX(YEAR(turnos.fecha)) as año'),
                DB::raw('MAX(estados.nombre) as estado'),
                DB::raw('MAX(municipios.nombre) as municipio'),
                DB::raw('MAX(mun_abogado.nombre) as municipio_abogado'),
                DB::raw('MAX(abogados.giroComercial) as giroComercial'),
                DB::raw('COALESCE(MAX(abogados.nombres_patronal), "No seleccionado") as nombres_patronal'),
                DB::raw('MAX(abogados.primer_apellido_patronal) as primer_apellido_patronal'),
                DB::raw('MAX(abogados.segundo_apellido_patronal) as segundo_apellido_patronal'),
                DB::raw('MAX(turnos.motivo) as motivo'),
                DB::raw('MAX(turnos.user_id) as user_id'),
                DB::raw('MAX(turnos.estatus) as estatus'),
                DB::raw('MAX(turnos.sexo) as sexo')
            )
            ->selectSub($subconsultaPagosTurnos, 'total')
            ->groupBy('turnos.id', 'turnos.NUE')
            ->get();

        // --- CONSULTA 2: SEER GENERAL ---
        $reportesSolicitudes = SeerPerGeneral::whereBetween('seer_general.fecha_terminacion', [$this->fecha_inicial, $this->fecha_final])
            ->join('seer_citados','seer_citados.id_solicitud','seer_general.id')
            ->join('seer_solicitante','seer_solicitante.id_solicitud','seer_general.id')
            ->join('audiencias', 'audiencias.id', '=', 'audiencias.id_solicitud')
            ->whereNotIn('seer_general.estatus', ['Pendiente', 'Prevencion','Confirmado'])
            ->leftJoin('users', 'users.id', '=', 'seer_general.user_id')
            ->leftJoin('estados', 'estados.id', '=', 'seer_solicitante.estado')
            ->leftJoin('municipios', 'municipios.id', '=', 'seer_solicitante.municipio_domicilio')
            ->leftJoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado') 
            ->leftJoin('municipios as mun_abogado', 'mun_abogado.id', '=', 'abogados.municipio_patronal')
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                return $this->aplicarFiltroSede($q, 'seer_general', $sedeUsuario);
            })
            ->select(
                'seer_general.id',
                'seer_general.NUE',
                DB::raw('MAX(MONTH(seer_general.fecha)) as mes'),
                DB::raw('MAX(YEAR(seer_general.fecha)) as año'),
                DB::raw('MAX(estados.nombre) as estado'),
                DB::raw('MAX(municipios.nombre) as municipio'),
                DB::raw('MAX(mun_abogado.nombre) as municipio_abogado'),
                DB::raw('MAX(seer_general.actividad) as giroComercial'),
                // Citados desglosados
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(seer_citados.nombre ORDER BY seer_citados.id ASC SEPARATOR "|"), "|", 1) as nombres_patronal'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(seer_citados.primer_apellido ORDER BY seer_citados.id ASC SEPARATOR "|"), "|", 1) as primer_apellido_patronal'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(seer_citados.segundo_apellido ORDER BY seer_citados.id ASC SEPARATOR "|"), "|", 1) as segundo_apellido_patronal'),
                // Abogado
                //DB::raw('COALESCE(MAX(abogados.nombres_patronal), "No seleccionado") as nombres_patronal'),
                //DB::raw('MAX(abogados.primer_apellido_patronal) as primer_apellido_patronal'),
                //DB::raw('MAX(abogados.segundo_apellido_patronal) as segundo_apellido_patronal'),
                DB::raw('MAX(seer_general.user_id) as user_id'),
                DB::raw('MAX(seer_general.estatus) as estatus'),
                DB::raw('MAX(seer_solicitante.sexo) as sexo')
            )
            ->selectSub($subconsultaMotivos, 'motivo') 
            ->selectSub($subconsultaPagosSeer, 'total') 
            ->groupBy('seer_general.id', 'seer_general.NUE')
            ->get();
        
        // --- COMBINACIÓN Y ORDENAMIENTO ---
        $todoJunto = $reportes->concat($reportesSolicitudes)
            ->map(function($item) {
                $item->NUE = $item->NUE ? trim(preg_replace('/\s+/', ' ', $item->NUE)) : 'S/N';
                $item->total = $item->total ?? 0;
                return $item;
            })
            ->unique(function ($item) {
                return $item->id . $item->NUE; 
            })
            ->sortBy('NUE', SORT_NATURAL)
            ->values();

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