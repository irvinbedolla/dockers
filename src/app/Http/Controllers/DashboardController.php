<?php

namespace App\Http\Controllers;

use App\Exports\AgendaSemanalExport;
use App\Support\AgendaContexto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    /**
     * Tope de días exportables. La agenda se consulta por semana o por mes;
     * el límite existe para que un rango escrito a mano en la URL no se lleve
     * medio año de audiencias por delante.
     */
    private const MAX_DIAS = 92;

    /**
     * Pantalla /agenda: sólo el calendario.
     */
    public function index()
    {
        addVendors(['amcharts', 'amcharts-maps', 'amcharts-stock']);

        $usuario  = auth()->user();
        $userRole = $usuario->roles->pluck('name')->all();

        // El alcance por rol y delegación vive en App\Support\AgendaContexto,
        // porque la pantalla de Inicio muestra el mismo calendario.
        ['sedes' => $sedes, 'conciliadores' => $conciliadores] = AgendaContexto::para($usuario);

        return view('pages/dashboards.index', compact('userRole', 'sedes', 'conciliadores'));
    }

    /**
     * Descarga la agenda del rango que el usuario tiene en pantalla, una hoja
     * por conciliador.
     *
     * El rango y los filtros llegan del calendario, pero no se confían: las
     * sedes y los conciliadores se cruzan contra lo que AgendaContexto le
     * permite ver a este usuario. Un conciliador sólo puede bajar la suya
     * aunque escriba otro id en la URL.
     */
    public function exportar(Request $request)
    {
        $datos = $request->validate([
            'start'       => ['required', 'date_format:Y-m-d'],
            'end'         => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'sede'        => ['nullable', 'string', 'max:30'],
            'conciliador' => ['nullable', 'integer'],
        ]);

        $usuario = auth()->user();
        ['sedes' => $sedesPermitidas] = AgendaContexto::para($usuario);

        $desde = Carbon::createFromFormat('Y-m-d', $datos['start'])->startOfDay();
        $hasta = Carbon::createFromFormat('Y-m-d', $datos['end'])->startOfDay();

        if ($desde->diffInDays($hasta) > self::MAX_DIAS) {
            $hasta = $desde->copy()->addDays(self::MAX_DIAS);
        }

        // "Todos" y el valor vacío significan lo mismo: todas las sedes que
        // este usuario alcanza. Cualquier otra se acepta sólo si está en su
        // lista; si no, se ignora y se cae al alcance completo.
        $sede  = $datos['sede'] ?? 'Todos';
        $sedes = in_array($sede, $sedesPermitidas, true) ? [$sede] : $sedesPermitidas;

        // Un conciliador sólo baja la suya, escriba lo que escriba en la URL.
        // Es la misma regla que ya rige el calendario. Para el resto de los
        // roles el alcance lo dan las sedes: null significa "sin filtro por
        // persona", igual que la opción "Todos los conciliadores".
        if (AgendaContexto::soloSuAgenda($usuario)) {
            $ids = [$usuario->id];
        } else {
            $elegido = $datos['conciliador'] ?? null;
            $ids = $elegido ? [(int) $elegido] : null;
        }

        $nombre = sprintf(
            'agenda_conciliadores_%s_%s.xlsx',
            $desde->format('d-m-Y'),
            $hasta->format('d-m-Y')
        );

        return Excel::download(
            new AgendaSemanalExport($desde->toDateString(), $hasta->toDateString(), $sedes, $ids),
            $nombre
        );
    }
}
