<?php

namespace App\Http\Controllers;

use App\Support\Recepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Todos los turnos de la tabla recepcion, no sólo los de hoy.
 *
 * Paginado en el servidor: son miles de registros y la pantalla de "hoy"
 * cargaba todo de golpe para que DataTables lo filtrara en el navegador.
 * Aquí cada filtro viaja en la URL, así que un enlace con ?fecha=... se puede
 * compartir o guardar y abre exactamente la misma vista.
 */
class TurnosHistorialController extends Controller
{
    public const TIPOS    = ['Solicitud', 'Ratificación', 'Asesoría', 'Cumplimiento'];
    public const ESTATUS  = ['pendiente', 'confirmada', 'atendido', 'expirada'];
    private const POR_PAGINA = 25;

    public function __invoke(Request $request)
    {
        $sedesPermitidas = Recepcion::sedesVisibles($request->user());

        $filtros = $request->validate([
            'fecha'   => ['nullable', 'date_format:Y-m-d'],
            'sede'    => ['nullable', Rule::in($sedesPermitidas)],
            'tipo'    => ['nullable', Rule::in(self::TIPOS)],
            'estatus' => ['nullable', Rule::in(self::ESTATUS)],
            'origen'  => ['nullable', Rule::in(['linea', 'ventanilla'])],
            'q'       => ['nullable', 'string', 'max:100'],
        ]);

        $sedes = ! empty($filtros['sede']) ? [$filtros['sede']] : $sedesPermitidas;

        // Base con todos los filtros menos el estatus: de aquí salen los
        // conteos de las pastillas, que deben decir cuántos hay de cada uno
        // aunque en ese momento se esté viendo sólo uno.
        $base = DB::table('recepcion as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->whereIn('r.delegacion', $sedes === [] ? ['__ninguna__'] : $sedes);

        if (! empty($filtros['fecha'])) {
            $base->where('r.fecha', $filtros['fecha']);
        }
        if (! empty($filtros['tipo'])) {
            $base->where('r.tipo', $filtros['tipo']);
        }
        if (! empty($filtros['origen'])) {
            $base->where('r.origen', $filtros['origen']);
        }
        if (! empty($filtros['q'])) {
            $texto = trim($filtros['q']);
            $base->where(function ($q) use ($texto) {
                $q->where('r.solicitante', 'like', '%'.$texto.'%');
                if (ctype_digit($texto)) {
                    $q->orWhere('r.consecutivo', (int) $texto);
                }
            });
        }

        $conteos = (clone $base)
            ->select('r.estatus', DB::raw('COUNT(*) as total'))
            ->groupBy('r.estatus')
            ->pluck('total', 'estatus');

        $consulta = clone $base;
        if (! empty($filtros['estatus'])) {
            $consulta->where('r.estatus', $filtros['estatus']);
        }

        // Un día se lee en orden de llegada; varios días, lo más reciente
        // arriba y dentro de cada día por hora.
        if (! empty($filtros['fecha'])) {
            $consulta->orderBy('r.hora');
        } else {
            $consulta->orderByDesc('r.fecha')->orderBy('r.hora');
        }

        $turnos = $consulta
            ->select([
                'r.id', 'r.consecutivo', 'r.solicitante', 'r.tipo', 'r.fecha', 'r.hora',
                'r.hora_fin', 'r.estatus', 'r.exepcion', 'r.lugar_auxiliar', 'r.delegacion',
                'r.correo', 'r.telefono', 'r.origen', 'u.name as auxiliar',
            ])
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        $hoy   = Carbon::today()->toDateString();
        $fecha = $filtros['fecha'] ?? null;

        return view('turnos.todos', [
            'turnos'          => $turnos,
            'filtros'         => $filtros,
            'fecha'           => $fecha,
            'hoy'             => $hoy,
            'diaAnterior'     => Carbon::parse($fecha ?? $hoy)->subDay()->toDateString(),
            'diaSiguiente'    => Carbon::parse($fecha ?? $hoy)->addDay()->toDateString(),
            'sedesPermitidas' => $sedesPermitidas,
            'conteos'         => $conteos,
            'totalSinEstatus' => $conteos->sum(),
        ]);
    }
}
