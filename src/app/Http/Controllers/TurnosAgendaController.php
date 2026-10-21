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
 * Turnos → Todos los turnos → "Descargar agenda": la agenda de un día o de
 * una semana, de una sede o de todas, en Excel, PDF o imagen
 * (App\Support\AgendaTurnos). Super Usuario y Directivo de todas las sedes;
 * el Delegado con permiso turnos_ver, de su región.
 */
class TurnosAgendaController extends Controller
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        abort_unless(AgendaTurnos::puedeDescargar($usuario), 403);

        $f = $request->validate([
            'fecha'     => ['required', 'date_format:Y-m-d'],
            'periodo'   => ['required', Rule::in(array_keys(AgendaTurnos::PERIODOS))],
            'sede'      => ['nullable', Rule::in(Recepcion::sedesVisibles($usuario))],
            'excepcion' => ['required', Rule::in(array_keys(AgendaTurnos::EXCEPCION))],
            'formato'   => ['required', Rule::in(AgendaTurnos::FORMATOS)],
            'pruebas'   => ['nullable', 'boolean'],
        ], [
            'fecha.required' => 'Elige el día.',
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
