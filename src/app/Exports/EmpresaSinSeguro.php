<?php

namespace App\Exports;

use App\Models\SeerPerGeneral;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Para que las columnas se ajusten solas
use Maatwebsite\Excel\Concerns\WithTitle;      // Para poner nombre a la pestaña
use Illuminate\Support\Facades\DB;

class EmpresaSinSeguro implements FromView, ShouldAutoSize, WithTitle
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
     * Define el nombre de la pestaña en el Excel
     */
    public function title(): string
    {
        return 'Empresas sin Seguro';
    }

    public function view(): View
    {
        $user = auth()->user();
        $sedeUsuario = $user->delegacion;

        // Consulta Base
        $queryBase = SeerPerGeneral::whereBetween('seer_general.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                if ($this->sede === "TodosDelegado") {
                    $grupos = [
                        'Morelia' => ['Morelia', 'Zitácuaro'],
                        'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'],
                        'Zamora'  => ['Zamora', 'Sahuayo']
                    ];

                    if (array_key_exists($sedeUsuario, $grupos)) {
                        return $q->whereIn('seer_general.delegacion', $grupos[$sedeUsuario]);
                    }
                }
                return $q->where('seer_general.delegacion', $this->sede);
            });
        
        $empresas = (clone $queryBase)
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('seer_citados', 'seer_citados.id_solicitud', '=', 'seer_general.id')
            ->leftJoin('abogados', 'abogados.idAbogado', '=', 'seer_citados.id_abogado')
            ->whereNull('seer_solicitante.nss')
            ->where('seer_citados.resulte_responsable', 'No')
            ->select(
                'seer_general.NUE',
                'seer_general.delegacion',
                'seer_citados.id as id_citado',
                DB::raw("CONCAT_WS(' ', seer_citados.nombre, seer_citados.primer_apellido, seer_citados.segundo_apellido) as nombre_citado"),
                DB::raw("CONCAT_WS(' ', abogados.nombres_patronal, abogados.primer_apellido_patronal, abogados.segundo_apellido_patronal) as nombre_abogado"),
                DB::raw("CONCAT_WS(' ', abogados.nombre_representante, abogados.primer_apellido_representante, abogados.segundo_apellido_representante) as nombre_representante")
            )
            ->toBase()
            ->get();
  
        return view('excel.empresas_seguro', ['empresas' => $empresas]);
    }
}