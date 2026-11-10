<?php

namespace App\Exports;

use App\Models\Audiencias;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Estadísticas → Convenios: una fila por audiencia concluida con convenio
 * (Conciliación o Reinstalación) del periodo.
 *
 * Citados: una columna por citado (CITADO 1, CITADO 2…), tantas como tenga el
 * convenio con más citados del reporte. Se toman los citados de ESA audiencia
 * (seer_citados.audiencia_id): cada audiencia nueva copia a los citados de la
 * solicitud, así que tomarlos por solicitud los repetía ("NATCLEAN, LIMPEQ,
 * NATCLEAN") y mezclaba nombres ya corregidos con los de antes. Sin el
 * "Quien o quienes resulten responsables" ni las copias para multa.
 */
class Convenios implements FromView
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

        $pagosSubquery = DB::table('pago_solicitud')
            ->select('id_solicitud')
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pagado' AND fecha <= ? THEN id END) as cantidad_pagados", [$this->fecha_final])
            ->selectRaw("COUNT(CASE WHEN estatus = 'Pendiente' OR (estatus = 'Pagado' AND fecha > ?) THEN id END) as cantidad_pendientes", [$this->fecha_final])
            ->selectRaw("SUM(CASE WHEN estatus = 'Pagado' AND fecha <= ? THEN monto ELSE 0 END) as monto_pagado", [$this->fecha_final])
            ->selectRaw("SUM(CASE WHEN estatus = 'Pendiente' OR (estatus = 'Pagado' AND fecha > ?) THEN monto ELSE 0 END) as monto_pendiente", [$this->fecha_final])
            ->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])
            ->groupBy('id_solicitud');

        $convenios = Audiencias::query()
            ->whereBetween('audiencias.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->whereIn('audiencias.estatus', ['Conciliacion', 'Reinstalacion'])
            ->join('seer_general', function ($join) {
                $join->on('seer_general.id', '=', 'audiencias.id_solicitud')
                     ->where(function ($q) {
                         $q->where('seer_general.incidencia', 0)
                           ->orWhereNull('seer_general.incidencia');
                     });
            })
            ->join('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
            ->join('users', 'users.id', '=', 'audiencias.id_conciliador')
            ->leftJoinSub($pagosSubquery, 'pagos_agrupados', function ($join) {
                $join->on('pagos_agrupados.id_solicitud', '=', 'seer_general.id');
            })
            // Filtro por delegación
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario, $grupos) {
                if ($this->sede === "TodosDelegado") {
                    $delegaciones = $grupos[$sedeUsuario] ?? [$sedeUsuario];
                    return $q->whereIn('seer_general.delegacion', $delegaciones);
                }
                return $q->where("seer_general.delegacion", $this->sede);
            })
            ->select(
                'audiencias.id as audiencia_id',
                'seer_general.id as solicitud_id',
                DB::raw('DATE_FORMAT(audiencias.fecha, "%d-%m-%Y") as fecha_formateada'),
                DB::raw('DATE_FORMAT(audiencias.hora, "%H:%i") as hora_formateada'),
                'seer_general.NUE',
                'seer_solicitante.nombre',
                'users.name as conciliador_name',
                'audiencias.estatus',
                // Usamos COALESCE por si no hay pagos, retorne 0 en lugar de null
                DB::raw('COALESCE(pagos_agrupados.cantidad_pagados, 0) as cantidad_pagados'),
                DB::raw('COALESCE(pagos_agrupados.cantidad_pendientes, 0) as cantidad_pendientes'),
                DB::raw('COALESCE(pagos_agrupados.monto_pagado, 0) as monto_pagado'),
                DB::raw('COALESCE(pagos_agrupados.monto_pendiente, 0) as monto_pendiente')
            )
            ->orderBy('seer_general.consecutivo', 'desc')
            ->get();

        $citados = self::citadosDeAudiencias($convenios);
        foreach ($convenios as $convenio) {
            $convenio->lista_citados = $citados[$convenio->audiencia_id] ?? [];
        }

        return view('excel.convenios', [
            'Convenios'      => $convenios,
            'columnasCitado' => max(1, (int) $convenios->max(fn ($c) => count($c->lista_citados))),
        ]);
    }

    /**
     * Nombres de los citados de cada audiencia, sin repetir, en el orden en
     * que se capturaron: [audiencia_id => ['NOMBRE', …]].
     *
     * Los de la propia audiencia; si la audiencia no tiene citados ligados
     * (registros viejos sin audiencia_id), los de su solicitud.
     */
    public static function citadosDeAudiencias(Collection $convenios): array
    {
        $porAudiencia = $porSolicitud = [];

        foreach ($convenios->pluck('solicitud_id')->unique()->chunk(1000) as $ids) {
            $filas = DB::table('seer_citados')
                ->whereIn('id_solicitud', $ids->all())
                ->where('resulte_responsable', 'No')
                ->where(fn ($q) => $q->whereNull('tipo_notificacion')->orWhere('tipo_notificacion', '!=', 'Multa'))
                ->orderBy('id')
                ->get(['id_solicitud', 'audiencia_id', 'nombre', 'primer_apellido', 'segundo_apellido']);

            foreach ($filas as $f) {
                $nombre = self::nombreCitado($f);
                if ($nombre === '') {
                    continue;
                }
                $clave = mb_strtoupper($nombre);
                if ($f->audiencia_id) {
                    $porAudiencia[$f->audiencia_id][$clave] ??= $nombre;
                }
                $porSolicitud[$f->id_solicitud][$clave] ??= $nombre;
            }
        }

        $resultado = [];
        foreach ($convenios as $c) {
            $resultado[$c->audiencia_id] = array_values($porAudiencia[$c->audiencia_id] ?? $porSolicitud[$c->solicitud_id] ?? []);
        }

        return $resultado;
    }

    /** "NOMBRE APELLIDO APELLIDO", sin espacios dobles ni la coma suelta al final ("S.A. DE C.V,"). */
    private static function nombreCitado(object $citado): string
    {
        $nombre = implode(' ', array_filter([$citado->nombre, $citado->primer_apellido, $citado->segundo_apellido], fn ($p) => trim((string) $p) !== ''));

        return rtrim(trim((string) preg_replace('/\s+/u', ' ', $nombre)), ' ,');
    }
}
