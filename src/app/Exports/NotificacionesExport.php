<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;

class NotificacionesExport implements WithMultipleSheets
{
    protected $fecha_inicial, $fecha_final, $sede, $auxiliar, $notificador;

    public function __construct($fecha_inicial, $fecha_final, $sede, $auxiliar, $notificador)
    {
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->sede = $sede;
        $this->auxiliar = $auxiliar;
        $this->notificador = $notificador;
    }

    public function sheets(): array
    {
        $user = auth()->user();
        $sedeUsuario = $user->delegacion ?? '';

        // 1. Centralizamos el cálculo de la sede
        $delegacionesFiltro = [$this->sede];
        if ($this->sede === "TodosDelegado") {
            $grupos = [
                'Morelia' => ['Morelia', 'Zitácuaro'], 
                'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'], 
                'Zamora'  => ['Zamora', 'Sahuayo']
            ];
            $delegacionesFiltro = $grupos[$sedeUsuario] ?? [$sedeUsuario];
        }

        // 2. UNA SOLA CONSULTA OPTIMIZADA (Se eliminó $notificaciones que era código muerto)
        // Usamos DB::table en lugar de Modelos para ahorrar RAM
        $notificacionesDomicilio = DB::table('seer_general')
            ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
            ->join('municipios', 'municipios.id', '=', 'seer_citados.municipio_citado')
            ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
            // ELIMINADO: El join a 'catalogo_rama', ya que nunca pedías datos de esa tabla.
            
            ->whereBetween('seer_citados.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                      ->orWhereNull('seer_general.incidencia');
            })
            ->when($this->sede !== "Todos", function ($q) use ($delegacionesFiltro) {
                return $q->whereIn('seer_general.delegacion', $delegacionesFiltro);
            })
            ->when($this->auxiliar !== "Todos", function ($q) { 
                return $q->where('seer_general.user_id', $this->auxiliar); 
            })
            ->when($this->notificador !== "Todos", function ($q) { 
                return $q->where('seer_citados.id_notificador', $this->notificador); 
            })
            ->select(
                'seer_general.NUE',
                'seer_general.actividad',
                'seer_general.delegacion',
                'seer_citados.nombre',
                'seer_citados.primer_apellido',
                'seer_citados.segundo_apellido',
                'seer_citados.calle',
                'seer_citados.n_ext',
                'seer_citados.colonia',
                'seer_citados.estatus',
                'seer_citados.notificacion',
                'seer_solicitante.nombre as nombre_solicitante', 
                'notificador.name as nombre_notificador', 
                'auxiliar.name as auxiliar',
                'municipios.nombre as municipio',
                
                // REEMPLAZO DEL ->map() DE PHP:
                // Le decimos a SQL que nos devuelva la fecha en NULL si el estatus es inválido.
                // Esto es instantáneo y nos ahorra el bucle foreach/map en PHP.
                DB::raw("CASE WHEN seer_citados.estatus IN ('Sin asignar', 'Pendiente') THEN NULL ELSE seer_citados.fecha END as fecha")
            )
            ->get();
            // ELIMINADO: La variable $notificacionesDireccion (código muerto)

        // 3. Calculamos los totales
        // Usamos DB::table también aquí para mayor eficiencia
        $notificacionesTrabajador = DB::table('seer_citados')
            ->where('notificacion', 'Trabajador')
            ->whereBetween('created_at', [$this->fecha_inicial, $this->fecha_final])
            ->count();

        // Esta parte se queda en Colecciones de PHP porque REUTILIZA los datos que ya obtuvimos,
        // evitando hacer 6 consultas count() a la base de datos. ¡Muy bien pensado originalmente!
        $totalesPorNotificador = $notificacionesDomicilio
            ->groupBy(function ($item) {
                $estatus = trim((string) ($item->estatus ?? ''));
                if (strcasecmp($estatus, 'Notificada en Audiencia') === 0) {
                    return 'Notificación en Audiencia';
                }

                $nombreNotificador = trim((string) ($item->nombre_notificador ?? ''));
                return $nombreNotificador !== '' ? $nombreNotificador : 'Sin asignar';
            })
            ->map(function ($row, $grupo) {
                return [
                    'nombre'         => $grupo,
                    'total'          => $grupo === 'Notificación en Audiencia'
                                            ? $row->count()
                                            : $row->whereNotIn('estatus', ['Notificada en Audiencia'])->count(),
                    'notificadas'    => $row->whereIn('estatus', ['Notificada','Finalizado exitosamente','Recibe pero no firma','Exitosa por Instructivo'])->count(),
                    'no_notificadas' => $row->whereIn('estatus', ['No notificada','No exitosa se constituye','No exitosa no se constituye'])->count(),
                    'pendientes'     => $row->whereIn('estatus', ['Pendiente', 'Sin asignar'])->count(),
                    'exhorto'        => $row->whereIn('estatus', ['Exhorto'])->count(),
                ];
            });

        // 4. Retornamos las hojas
        return [
            new NotificacionesTotalesSheet($totalesPorNotificador, $notificacionesTrabajador),
            new NotificacionesDetalleSheet($notificacionesDomicilio),
        ];
    }
}