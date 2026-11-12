<?php

namespace App\Http\Controllers;

use App\Exports\AgendaTurnosExport;
use App\Support\AgendaTurnos;
use App\Support\Recepcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Turnos → Todos los turnos → "Descargar agenda": la agenda de un día, una
 * semana o un rango de fechas, de una sede o de todas, y si se elige, sólo
 * la de una persona (quien atiende), en Excel, PDF o imagen
 * (App\Support\AgendaTurnos). Super Usuario y Directivo de todas las sedes;
 * el Delegado con permiso turnos_ver, de su región.
 */
class TurnosAgendaController extends Controller
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        abort_unless(AgendaTurnos::puedeDescargar($usuario), 403);

        $sedes = Recepcion::sedesVisibles($usuario);
        $f = $request->validate([
            'periodo'   => ['required', Rule::in(array_keys(AgendaTurnos::PERIODOS))],
            'fecha'     => ['exclude_if:periodo,rango', 'required', 'date_format:Y-m-d'],
            'desde'     => ['exclude_unless:periodo,rango', 'required', 'date_format:Y-m-d'],
            'hasta'     => ['exclude_unless:periodo,rango', 'required', 'date_format:Y-m-d', 'after_or_equal:desde',
                function ($campo, $valor, $falla) use ($request) {
                    $dias = \Illuminate\Support\Carbon::parse($request->input('desde'))->diffInDays(\Illuminate\Support\Carbon::parse($valor));
                    if ($dias >= AgendaTurnos::MAX_DIAS_RANGO) {
                        $falla('El rango puede ser de hasta '.AgendaTurnos::MAX_DIAS_RANGO.' días.');
                    }
                }],
            'sede'      => ['nullable', Rule::in($sedes)],
            'persona'   => ['nullable', 'integer', Rule::in(AgendaTurnos::personas($sedes)->pluck('id')->all())],
            'excepcion' => ['required', Rule::in(array_keys(AgendaTurnos::EXCEPCION))],
            'formato'   => ['required', Rule::in(AgendaTurnos::FORMATOS)],
            'pruebas'   => ['nullable', 'boolean'],
        ], [
            'fecha.required'       => 'Elige el día.',
            'desde.required'       => 'Elige desde qué día.',
            'hasta.required'       => 'Elige hasta qué día.',
            'hasta.after_or_equal' => 'La fecha final no puede ser antes de la inicial.',
            'persona.in'           => 'Esa persona no es de tus sedes.',
        ]);
        $f['pruebas'] = ! empty($f['pruebas']) && $usuario->hasRole('Super Usuario');

        $datos = AgendaTurnos::armar($f, $usuario);

        return match ($f['formato']) {
            'xlsx' => Excel::download(new AgendaTurnosExport($datos), $datos['archivo'].'.xlsx'),
            'pdf'  => Pdf::loadView('turnos.agenda.pdf', $datos)
                ->setPaper('letter', 'landscape')
                ->setOption('isPhpEnabled', true)
                ->download($datos['archivo'].'.pdf'),
            'png'  => view('turnos.agenda.imagen', $datos),
        };
    }
}
