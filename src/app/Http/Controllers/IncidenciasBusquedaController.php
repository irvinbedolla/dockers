<?php

namespace App\Http\Controllers;

use App\Models\Audiencias;
use App\Models\SeerPerGeneral;
use App\Models\Pagos;
use App\Models\SeerSolicitante;
use App\Models\SeerCitados;
use App\Models\Turnos;
use App\Models\Incidencias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncidenciasBusquedaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $tipo = (string) $request->get('tipo', '');
        $tipo = in_array($tipo, ['SOLICITUD', 'RATIFICACION'], true) ? $tipo : '';

        $resultados = collect();

        $user = Auth::user();
        $esSuperUsuario = $user ? $user->hasRole('Super Usuario') : false;

        if ($q !== '' && $tipo !== '') {
            if ($tipo === 'SOLICITUD') {
                $generales = SeerPerGeneral::query()
                    ->leftJoin('seer_solicitante', 'seer_solicitante.id_solicitud', '=', 'seer_general.id')
                    ->select('seer_general.id', 'seer_general.NUE', 'seer_general.estatus', 'seer_general.incidencia', 'seer_general.motivo_incidencia', 'seer_solicitante.nombre as solicitante_nombre')
                    ->when(!$esSuperUsuario, function ($q) {
                        $q->where(function ($w) {
                            $w->whereNull('seer_general.incidencia')
                                ->orWhere('seer_general.incidencia', 0)
                                ->orWhere('seer_general.incidencia', false);
                        });
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('seer_general.NUE', 'like', "%{$q}%")
                            ->orWhere('seer_solicitante.nombre', 'like', "%{$q}%");
                    })
                    ->orderByDesc('seer_general.id')
                    ->get();

                if ($generales->isNotEmpty()) {
                    $ids = $generales->pluck('id')->all();

                    $citadosPorSolicitud = SeerCitados::query()
                        ->whereIn('id_solicitud', $ids)
                        ->get(['id_solicitud', 'nombre', 'primer_apellido', 'segundo_apellido'])
                        ->groupBy('id_solicitud')
                        ->map(function ($items) {
                            return $items->map(function ($c) {
                                return trim(($c->nombre ?? '') . ' ' . ($c->primer_apellido ?? '') . ' ' . ($c->segundo_apellido ?? ''));
                            })->filter()->implode(', ');
                        });

                    $resultados = $generales->map(function ($general) use ($citadosPorSolicitud) {
                        return (object) [
                            'tipo' => 'SOLICITUD',
                            'id' => $general->id,
                            'NUE' => $general->NUE,
                            'solicitante' => $general->solicitante_nombre,
                            'citados' => $citadosPorSolicitud->get($general->id, ''),
                            'estatus' => $general->estatus,
                            'incidencia' => (bool) ($general->incidencia ?? false),
                            'motivo_incidencia' => $general->motivo_incidencia,
                        ];
                    });
                }
            } else {
                //RATIFICACION
                $turnos = Turnos::query()
                    ->select(
                        'id',
                        'NUE',
                        'empresa',
                        'primero_empresa',
                        'segundo_empresa',
                        'trabajador',
                        'primero_trabajador',
                        'segundo_trabajador',
                        'estatus',
                        'incidencia',
                        'motivo_incidencia'
                    )
                    ->when(!$esSuperUsuario, function ($q) {
                        $q->where(function ($w) {
                            $w->whereNull('incidencia')
                                ->orWhere('incidencia', 0)
                                ->orWhere('incidencia', false);
                        });
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('NUE', 'like', "%{$q}%")
                            ->orWhere('empresa', 'like', "%{$q}%")
                            ->orWhere('primero_empresa', 'like', "%{$q}%")
                            ->orWhere('segundo_empresa', 'like', "%{$q}%")
                            ->orWhere(DB::raw("TRIM(CONCAT(IFNULL(empresa,''),' ',IFNULL(primero_empresa,''),' ',IFNULL(segundo_empresa,'')))"), 'like', "%{$q}%")
                            ->orWhere(DB::raw("TRIM(CONCAT(IFNULL(trabajador,''),' ',IFNULL(primero_trabajador,''),' ',IFNULL(segundo_trabajador,'')))"), 'like', "%{$q}%")
                            ->orWhere('trabajador', 'like', "%{$q}%")
                            ->orWhere('primero_trabajador', 'like', "%{$q}%")
                            ->orWhere('segundo_trabajador', 'like', "%{$q}%");
                    })
                    ->orderByDesc('id')
                    ->get();

                $resultados = $turnos->map(function ($t) {
                    return (object) [
                        'tipo' => 'RATIFICACION',
                        'id' => $t->id,
                        'NUE' => $t->NUE,
                        'empresa' => trim(($t->empresa ?? '') . ' ' . ($t->primero_empresa ?? '') . ' ' . ($t->segundo_empresa ?? '')),
                        'trabajador' => trim(($t->trabajador ?? '') . ' ' . ($t->primero_trabajador ?? '') . ' ' . ($t->segundo_trabajador ?? '')),
                        'estatus' => $t->estatus,
                        'incidencia' => (bool) ($t->incidencia ?? false),
                        'motivo_incidencia' => $t->motivo_incidencia,
                    ];
                });
            }
        }

        return view('incidencias.index', [
            'q' => $q,
            'tipo' => $tipo,
            'resultados' => $resultados,
        ]);
    }

    public function marcar(Request $request)
    {
        $request->validate([
            'id_solicitud' => ['required', 'integer'],
            'tipo' => ['required', 'in:SOLICITUD,RATIFICACION'],
            'motivo_incidencia' => ['required', 'string', 'max:500'],
        ]);

        $expediente_id = $request->get('id_solicitud');
        $tipo = (string) $request->get('tipo');
        $motivo = trim((string) $request->get('motivo_incidencia'));

        if($tipo == 'SOLICITUD'){
            SeerPerGeneral::where('id', $expediente_id)->update(['incidencia' => true]);
            SeerPerGeneral::where('id', $expediente_id)->update(['motivo_incidencia' => $motivo]);
            Audiencias::where('id_solicitud', $expediente_id)->update(['incidencia' => true]);
            Pagos::where('id_solicitud', $expediente_id)->where('tipo_pago', 'Audiencia')->update(['incidencia' => true]);
        }
        else{
            Turnos::where('id', $expediente_id)->update(['incidencia' => true]);
            Turnos::where('id', $expediente_id)->update(['motivo_incidencia' => $motivo]);
            Pagos::where('id_solicitud', $expediente_id)->where('tipo_pago', 'Ratificacion')->update(['incidencia' => true]);
        }

        return redirect()
            ->route('incidencias.busqueda.index', ['q' => $request->get('q'), 'tipo' => $tipo])
            ->with('status', 'Incidencia marcada correctamente.');
    }

    public function desmarcar(Request $request)
    {
        $request->validate([
            'id_solicitud' => ['required', 'integer'],
            'tipo' => ['required', 'in:SOLICITUD,RATIFICACION'],
        ]);

        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Usuario')) {
            abort(403);
        }

        $expediente_id = $request->integer('id_solicitud');
        $tipo = (string) $request->get('tipo');

        if ($tipo === 'SOLICITUD') {
            SeerPerGeneral::where('id', $expediente_id)->update([
                'incidencia' => false,
                'motivo_incidencia' => null,
            ]);
            Audiencias::where('id_solicitud', $expediente_id)->update(['incidencia' => false]);
            Pagos::where('id_solicitud', $expediente_id)->where('tipo_pago', 'Audiencia')->update(['incidencia' => false]);
        } else {
            Turnos::where('id', $expediente_id)->update([
                'incidencia' => false,
                'motivo_incidencia' => null,
            ]);
            Pagos::where('id_solicitud', $expediente_id)->where('tipo_pago', 'Ratificacion')->update(['incidencia' => false]);
        }

        return redirect()
            ->route('incidencias.busqueda.index', ['q' => $request->get('q'), 'tipo' => $tipo])
            ->with('status', 'Incidencia desmarcada correctamente.');
    }
}
