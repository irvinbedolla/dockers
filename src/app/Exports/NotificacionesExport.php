<?php

namespace App\Exports;

use App\Models\SeerPerGeneral;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
        // 1. Ejecutamos tu lógica de consulta una sola vez
        $user = auth()->user();
        $sedeUsuario = $user->delegacion;

        $notificaciones = SeerPerGeneral::whereBetween('seer_citados.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
            ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
            ->join('municipios','municipios.id','seer_citados.municipio_citado')
            ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                ->orWhereNull('seer_general.incidencia');
            })
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                if ($this->sede === "TodosDelegado") {
                    $grupos = ['Morelia' => ['Morelia', 'Zitácuaro'], 'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'], 'Zamora' => ['Zamora', 'Sahuayo']];
                    if (array_key_exists($sedeUsuario, $grupos)) return $q->whereIn('seer_general.delegacion', $grupos[$sedeUsuario]);
                }
                return $q->where('seer_general.delegacion', $this->sede);
            })
            ->when($this->auxiliar !== "Todos", function ($q) { return $q->where('seer_general.user_id', $this->auxiliar); })
            ->when($this->notificador !== "Todos", function ($q) { return $q->where('seer_citados.id_notificador', $this->notificador); })
            ->select(
                'seer_general.NUE','seer_general.actividad','seer_general.delegacion',
                'seer_citados.fecha','seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
                'seer_citados.calle','seer_citados.n_ext','seer_citados.colonia','seer_citados.calle','seer_citados.estatus',
                'seer_solicitante.nombre as nombre_solicitante', 'notificador.name as nombre_notificador', 'auxiliar.name as auxiliar')
            ->orderBy('seer_citados.fecha')
            ->get();

        $notificacionesDomicilio = SeerPerGeneral::whereBetween('seer_citados.fecha', [$this->fecha_inicial, $this->fecha_final])
            ->join('catalogo_rama', 'catalogo_rama.id', '=', 'seer_general.id_rama')
            ->join('seer_citados', 'seer_general.id', '=', 'seer_citados.id_solicitud')
            ->join('seer_solicitante', 'seer_general.id', '=', 'seer_solicitante.id_solicitud')
            ->join('users as auxiliar', 'auxiliar.id', '=', 'seer_general.user_id')
            ->join('municipios','municipios.id','seer_citados.municipio_citado')
            ->leftJoin('users as notificador', 'notificador.id', '=', 'seer_citados.id_notificador')
            ->where(function($query) {
                $query->where('seer_general.incidencia', 0)
                ->orWhereNull('seer_general.incidencia');
            })
            ->when($this->sede !== "Todos", function ($q) use ($sedeUsuario) {
                if ($this->sede === "TodosDelegado") {
                    $grupos = ['Morelia' => ['Morelia', 'Zitácuaro'], 'Uruapan' => ['Uruapan', 'Lázaro Cárdenas'], 'Zamora' => ['Zamora', 'Sahuayo']];
                    if (array_key_exists($sedeUsuario, $grupos)) return $q->whereIn('seer_general.delegacion', $grupos[$sedeUsuario]);
                }
                return $q->where('seer_general.delegacion', $this->sede);
            })
            ->when($this->auxiliar !== "Todos", function ($q) { return $q->where('seer_general.user_id', $this->auxiliar); })
            ->when($this->notificador !== "Todos", function ($q) { return $q->where('seer_citados.id_notificador', $this->notificador); })
            ->select(
                'seer_general.NUE','seer_general.actividad','seer_general.delegacion',
                'seer_citados.fecha','seer_citados.nombre','seer_citados.primer_apellido','seer_citados.segundo_apellido',
                'seer_citados.calle','seer_citados.n_ext','seer_citados.colonia','seer_citados.calle','seer_citados.estatus', 'seer_citados.notificacion',
                'seer_solicitante.nombre as nombre_solicitante', 'notificador.name as nombre_notificador', 'auxiliar.name as auxiliar','municipios.nombre as municipio')
            ->get()
        
            ->map(function ($item) {
                $estatusActual = trim($item->estatus);

                $estatusInvalidos = [
                    'Sin asignar', 
                    //'No exitosa se constituye', 
                    'Pendiente', 
                    //'No notificada'
                ];

                if (in_array($item->estatus, $estatusInvalidos)) {
                    $item->fecha = null;
                    //$item->hora = null;
                }

                return $item;
            });

        // 2. Filtramos la colección con limpieza de texto (Case-insensitive y Trim)
        $notificacionesDireccion = $notificacionesDomicilio->unique(function ($item) {
            // Creamos una "llave" normalizada
            $colonia = strtolower(trim($item->colonia));
            $calle   = strtolower(trim($item->calle));
            $numero  = strtolower(trim($item->n_ext));

            // Retornamos la combinación única
            return "{$colonia}|{$calle}|{$numero}";
        });

        // 3. Calculamos los totales

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
                    'nombre' => $grupo,
                    'total' => $grupo === 'Notificación en Audiencia'
                        ? $row->count()
                        : $row->whereNotIn('estatus', ['Notificada en Audiencia'])->count(),
                    'notificadas' => $row->whereIn('estatus', ['Notificada','Finalizado exitosamente','Recibe pero no firma','Exitosa por Instructivo'])->count(),
                    'no_notificadas' => $row->whereIn('estatus', ['No notificada','No exitosa se constituye','No exitosa no se constituye'])->count(),
                    'pendientes' => $row->whereIn('estatus', ['Pendiente', 'Sin asignar'])->count(),
                    'exhorto' => $row->whereIn('estatus', ['Exhorto'])->count(),
                ];
            });


        // 4. Retornamos las hojas pasando los datos específicos a cada una
        return [
            new NotificacionesTotalesSheet($totalesPorNotificador), // Hoja 1
            new NotificacionesDetalleSheet($notificacionesDomicilio),       // Hoja 2
        ];
    }
}