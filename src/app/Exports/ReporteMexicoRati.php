<?php

namespace App\Exports;

use App\Models\Turnos;
use App\Models\Pagos;
use App\Models\SeerPerGeneral;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;
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
        // 1. Centralizamos el cálculo de la sede
        $user = auth()->user();
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

        // 2. Preparamos nuestras subconsultas limpias (Regla 3)
        $pagosTurnosSub = DB::table('pago_solicitud')
            ->where('tipo_pago', 'Ratificacion')
            ->select('id_solicitud', DB::raw('SUM(monto) as total'))
            ->groupBy('id_solicitud');

        $pagosSeerSub = DB::table('pago_solicitud')
            ->whereIn('tipo_pago', ['Audiencia','Conciliador'])
            ->select('id_solicitud', DB::raw('SUM(monto) as total'))
            ->groupBy('id_solicitud');

        $motivosSub = DB::table('seer_motivos')
            ->join('catalogo_motivos', 'catalogo_motivos.id', '=', 'seer_motivos.id_motivo')
            ->select('id_solicitud', DB::raw('GROUP_CONCAT(catalogo_motivos.motivo SEPARATOR ", ") as motivo'))
            ->groupBy('id_solicitud');

        // Aislar al primer citado para evitar SUBSTRING_INDEX
        $primerCitadoSub = DB::table('seer_citados')
            ->select('id_solicitud', DB::raw('MIN(id) as min_id'))
            ->groupBy('id_solicitud');


        // --- CONSULTA 1: TURNOS ---
        // Usamos DB::table para no cargar Modelos innecesariamente y liberar RAM
        $reportes = DB::table('turnos')
            ->whereBetween('turnos.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereNotIn('turnos.estatus', ['Pendiente', 'Prevencion', 'Confirmado'])
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('turnos.delegacion', $delegacionesFiltro);
            })
            // ELIMINADO: leftJoin('users') porque solo usas el user_id, y ese ya está en turnos.
            ->leftJoin('estados', 'estados.id', '=', 'turnos.estado_rat')
            ->leftJoin('municipios', 'municipios.id', '=', 'turnos.municipio_rat')
            ->leftJoin('abogados', 'abogados.idAbogado', '=', 'turnos.idAbogado')
            ->leftJoin('municipios as mun_abogado', 'mun_abogado.id', '=', 'abogados.municipio_patronal')
            
            // Unimos los pagos como tabla derivada
            ->leftJoinSub($pagosTurnosSub, 'pagos', 'pagos.id_solicitud', '=', 'turnos.id')
            
            ->select(
                'turnos.id',
                'turnos.NUE',
                DB::raw('MONTH(turnos.fecha) as mes'),
                DB::raw('YEAR(turnos.fecha) as año'),
                'estados.nombre as estado',
                'municipios.nombre as municipio',
                'mun_abogado.nombre as municipio_abogado',
                'abogados.giroComercial',
                DB::raw('COALESCE(abogados.nombres_patronal, "No seleccionado") as nombres_patronal'),
                'abogados.primer_apellido_patronal',
                'abogados.segundo_apellido_patronal',
                'turnos.motivo',
                'turnos.user_id',
                'turnos.estatus',
                'turnos.sexo',
                DB::raw('COALESCE(pagos.total, 0) as total') // Protegemos matemáticamente
            )
            // ELIMINADO: Todos los MAX() y el GROUP BY, la relación ya es perfecta 1 a 1.
            ->get();


        // --- CONSULTA 2: SEER GENERAL ---
        $reportesSolicitudes = DB::table('seer_general')
            ->whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereNotIn('seer_general.estatus', ['Pendiente', 'Prevencion','Confirmado'])
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('seer_general.delegacion', $delegacionesFiltro);
            })
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->leftJoin('estados', 'estados.id', '=', 'seer_solicitante.estado')
            ->leftJoin('municipios', 'municipios.id', '=', 'seer_solicitante.municipio_domicilio')
            
            // Unimos primero nuestra subconsulta para saber el ID del primer citado, luego sacamos sus datos
            ->leftJoinSub($primerCitadoSub, 'citado_min', 'citado_min.id_solicitud', '=', 'seer_general.id')
            ->leftJoin('seer_citados', 'seer_citados.id', '=', 'citado_min.min_id')
            ->leftJoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado') 
            ->leftJoin('municipios as mun_abogado', 'mun_abogado.id', '=', 'abogados.municipio_patronal')
            
            // Unimos motivos y pagos ya pre-calculados
            ->leftJoinSub($motivosSub, 'motivos_agrupados', 'motivos_agrupados.id_solicitud', '=', 'seer_general.id')
            ->leftJoinSub($pagosSeerSub, 'pagos', 'pagos.id_solicitud', '=', 'seer_general.id')
            
            ->select(
                'seer_general.id',
                'seer_general.NUE',
                DB::raw('MONTH(seer_general.fecha) as mes'),
                DB::raw('YEAR(seer_general.fecha) as año'),
                'estados.nombre as estado',
                'municipios.nombre as municipio',
                'mun_abogado.nombre as municipio_abogado',
                'seer_general.actividad as giroComercial',
                'seer_citados.nombre as nombres_patronal',
                'seer_citados.primer_apellido as primer_apellido_patronal',
                'seer_citados.segundo_apellido as segundo_apellido_patronal',
                'seer_general.user_id',
                'seer_general.estatus',
                'seer_solicitante.sexo',
                DB::raw('COALESCE(motivos_agrupados.motivo, "N/A") as motivo'),
                DB::raw('COALESCE(pagos.total, 0) as total')
            )
            // ELIMINADO: MAX() y GROUP BY innecesarios.
            ->get();

        // --- COMBINACIÓN Y ORDENAMIENTO ---
        // (Se mantiene intacto, la lógica de colección funciona perfecto)
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
}