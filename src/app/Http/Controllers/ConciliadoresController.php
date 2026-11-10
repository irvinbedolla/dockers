<?php

namespace App\Http\Controllers;

use App\Models\PermisosConciliador;
use App\Models\User;
use App\Support\AgendaContexto;
use App\Support\SedesConciliador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administración → Conciliadores (sólo Super Usuario): en qué sedes atiende
 * audiencias cada conciliador, presencial o virtual, con qué días y horario y
 * en qué sala. Ver App\Support\SedesConciliador.
 */
class ConciliadoresController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'sede'      => ['nullable', Rule::in(SedesConciliador::SEDES)],
            'buscar'    => ['nullable', 'string', 'max:60'],
            'inactivos' => ['nullable', 'boolean'],
        ]);

        $todos = User::whereHas('roles', fn ($q) => $q->where('name', 'Conciliador'))
            ->when(empty($f['inactivos']), fn ($q) => $q->where('estatus', 'Activo'))
            ->orderBy('delegacion')->orderBy('name')
            ->get();

        $filas = $todos->map(fn (User $u) => (object) [
            'usuario'     => $u,
            'sedes'       => $sedes = SedesConciliador::sedes($u),
            'semana'      => SedesConciliador::semana($u),
            'porSede'     => SedesConciliador::configurado($u->id),
            'multisede'   => count($sedes) > 1,
            'fuera'       => $sedes && SedesConciliador::fueraDeRegion($u),
            'sinAgenda'   => ! $sedes || ! collect($sedes)->contains(fn ($s) => $s['dias']),
        ]);

        $cifras = [
            'total'     => $filas->count(),
            'multisede' => $filas->where('multisede', true)->count(),
            'fuera'     => $filas->where('fuera', true)->count(),
            'sinAgenda' => $filas->where('sinAgenda', true)->count(),
        ];

        $visibles = $filas
            ->when(! empty($f['sede']), fn ($c) => $c->filter(fn ($r) => $r->usuario->delegacion === $f['sede']
                || collect($r->sedes)->contains('sede', $f['sede'])))
            ->when(! empty($f['buscar']), fn ($c) => $c->filter(fn ($r) => str_contains(
                mb_strtolower(\Illuminate\Support\Str::ascii($r->usuario->name)),
                mb_strtolower(\Illuminate\Support\Str::ascii(trim($f['buscar'])))
            )))
            ->values();

        return view('administracion.conciliadores.index', [
            'filas'     => $visibles,
            'cifras'    => $cifras,
            'filtros'   => $f,
            'historial' => SedesConciliador::historial(null, 10),
            'nombres'   => $todos->pluck('name', 'id'),
        ]);
    }

    public function editar(int $id)
    {
        $conciliador = $this->conciliador($id);

        return view('administracion.conciliadores.editar', [
            'conciliador' => $conciliador,
            'sedes'       => SedesConciliador::sedes($conciliador),
            'porSede'     => SedesConciliador::configurado($conciliador->id),
            'region'      => AgendaContexto::sedesDeRegion($conciliador->delegacion),
            'pendientes'  => SedesConciliador::pendientesPorSede($conciliador->id),
            'historial'   => SedesConciliador::historial($conciliador->id),
        ]);
    }

    public function guardar(Request $request, int $id)
    {
        $conciliador = $this->conciliador($id);

        $datos = $request->validate([
            'sedes'             => ['required', 'array', 'min:1', 'max:'.count(SedesConciliador::SEDES)],
            'sedes.*.sede'      => ['required', Rule::in(SedesConciliador::SEDES)],
            'sedes.*.modalidad' => ['required', Rule::in(array_keys(SedesConciliador::MODALIDADES))],
            'sedes.*.dias'      => ['required', 'array', 'min:1'],
            'sedes.*.dias.*'    => [Rule::in(array_keys(PermisosConciliador::DIAS))],
            'sedes.*.inicio'    => ['required', 'date_format:H:i'],
            'sedes.*.fin'       => ['required', 'date_format:H:i'],
            'sedes.*.sala'      => ['nullable', 'string', 'max:40'],
            'motivo'            => ['nullable', 'string', 'max:255'],
        ], [
            'sedes.required'          => 'Agrega al menos una sede.',
            'sedes.*.sede.required'   => 'Elige la sede.',
            'sedes.*.dias.required'   => 'Elige al menos un día en cada sede.',
            'sedes.*.inicio.required' => 'Falta la hora de entrada.',
            'sedes.*.fin.required'    => 'Falta la hora de salida.',
            'date_format'             => 'La hora va como HH:MM.',
        ]);

        $filas = array_values(array_map(fn ($f) => [
            'sede'      => $f['sede'],
            'modalidad' => $f['modalidad'],
            'dias'      => array_values(array_unique($f['dias'])),
            'inicio'    => $f['inicio'],
            'fin'       => $f['fin'],
            'sala'      => $f['sala'] ?? null,
        ], $datos['sedes']));

        if ($errores = SedesConciliador::validar($filas)) {
            return back()->withInput()->withErrors($errores);
        }

        $antes = array_column(SedesConciliador::sedes($conciliador), 'sede');
        SedesConciliador::guardar($conciliador, $filas, $datos['motivo'] ?? null);

        return redirect()->route('conciliadores.editar', $conciliador->id)
            ->with('success', "Agenda de {$conciliador->name} guardada: ".SedesConciliador::resumen($conciliador).'.')
            ->with('warning', $this->avisoPendientes($conciliador, array_diff($antes, array_column($filas, 'sede'))));
    }

    /** Vuelve a la agenda de siempre (la de su región, como quedó al guardar). */
    public function quitar(Request $request, int $id)
    {
        $conciliador = $this->conciliador($id);
        abort_unless(SedesConciliador::configurado($conciliador->id), 404);

        $antes = array_column(SedesConciliador::sedes($conciliador), 'sede');
        SedesConciliador::quitar($conciliador, $request->input('motivo'));

        return redirect()->route('conciliadores.editar', $conciliador->id)
            ->with('success', "{$conciliador->name} volvió a la agenda de siempre: ".(SedesConciliador::resumen($conciliador) ?: 'sin sedes').'.')
            ->with('warning', $this->avisoPendientes($conciliador, array_diff($antes, array_column(SedesConciliador::sedes($conciliador), 'sede'))));
    }

    private function conciliador(int $id): User
    {
        $usuario = User::findOrFail($id);
        abort_unless($usuario->hasRole('Conciliador'), 404, 'Ese usuario no es conciliador.');

        return $usuario;
    }

    /** Quitar una sede no mueve sus audiencias ya agendadas ahí: se avisa. */
    private function avisoPendientes(User $conciliador, array $quitadas): ?string
    {
        $pendientes = array_intersect_key(SedesConciliador::pendientesPorSede($conciliador->id), array_flip($quitadas));
        if (! $pendientes) {
            return null;
        }

        $texto = collect($pendientes)->map(fn ($n, $sede) => "{$n} en {$sede}")->implode(', ');

        return "Tiene audiencias por celebrar en sedes que ya no atiende ({$texto}). Siguen a su nombre: "
            .'si no las va a celebrar, cámbialas en Administración → Cambiar fecha de audiencia.';
    }
}
