<?php

namespace App\Http\Controllers;

use App\Models\PermisosConciliador;
use App\Models\User;
use App\Support\AgendaContexto;
use App\Support\SedesConciliador;
use App\Support\TraspasoAudiencias;
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
            'sedes.*.desde'     => ['nullable', 'date_format:Y-m-d'],
            'sedes.*.hasta'     => ['nullable', 'date_format:Y-m-d'],
            'sedes.*.exclusiva' => ['nullable', 'boolean'],
            'motivo'            => ['nullable', 'string', 'max:255'],
        ], [
            'sedes.required'          => 'Agrega al menos una sede.',
            'sedes.*.sede.required'   => 'Elige la sede.',
            'sedes.*.dias.required'   => 'Elige al menos un día en cada sede.',
            'sedes.*.inicio.required' => 'Falta la hora de entrada.',
            'sedes.*.fin.required'    => 'Falta la hora de salida.',
            'sedes.*.inicio.date_format' => 'La hora va como HH:MM.',
            'sedes.*.fin.date_format'    => 'La hora va como HH:MM.',
            'sedes.*.desde.date_format'  => 'La fecha "desde" no es válida.',
            'sedes.*.hasta.date_format'  => 'La fecha "hasta" no es válida.',
        ]);

        $filas = array_values(array_map(fn ($f) => [
            'sede'      => $f['sede'],
            'modalidad' => $f['modalidad'],
            'dias'      => array_values(array_unique($f['dias'])),
            'inicio'    => $f['inicio'],
            'fin'       => $f['fin'],
            'sala'      => $f['sala'] ?? null,
            'desde'     => $f['desde'] ?? null,
            'hasta'     => $f['hasta'] ?? null,
            'exclusiva' => ! empty($f['exclusiva']),
        ], $datos['sedes']));

        if ($errores = SedesConciliador::validar($filas)) {
            return back()->withInput()->withErrors($errores);
        }

        $antes = array_column(SedesConciliador::sedes($conciliador), 'sede');
        // Antes de guardar: quiénes atienden hoy las sedes que quedan exclusivas.
        $desplazados = [];
        foreach (array_filter($filas, fn ($f) => $f['exclusiva']) as $f) {
            $otros = SedesConciliador::quienesAtienden($f['sede'])->where('id', '<>', $conciliador->id)->pluck('name')->all();
            if ($otros) {
                $desplazados[] = $f['sede'].(($v = SedesConciliador::vigenciaTexto($f['desde'], $f['hasta'])) ? ' ('.mb_strtolower($v).')' : '')
                    .': '.implode(', ', $otros);
            }
        }
        SedesConciliador::guardar($conciliador, $filas, $datos['motivo'] ?? null);

        $avisos = array_filter([
            $this->avisoPendientes($conciliador, array_diff($antes, array_column($filas, 'sede'))),
            $desplazados ? 'Sede exclusiva: estos conciliadores dejan de recibir audiencias nuevas de ahí mientras esté vigente — '
                .implode(' · ', $desplazados).'. Las que ya tienen agendadas siguen a su nombre: muévelas con "Traspasar audiencias".' : null,
        ]);

        return redirect()->route('conciliadores.editar', $conciliador->id)
            ->with('success', "Agenda de {$conciliador->name} guardada: ".SedesConciliador::resumen($conciliador).'.')
            ->with('warning', $avisos ? implode(' ', $avisos) : null);
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

    /**
     * Traspasar audiencias ya agendadas de una sede a un conciliador
     * (App\Support\TraspasoAudiencias). Primero se revisa: qué
     * conciliadores tienen audiencias ahí desde esa fecha, cuáles se pasan
     * y cuántas chocarían con la agenda del destino.
     */
    public function traspaso(Request $request)
    {
        $f = $request->validate([
            'sede'       => ['nullable', Rule::in(SedesConciliador::SEDES)],
            'destino'    => ['nullable', 'integer'],
            'desde'      => ['nullable', 'date_format:Y-m-d'],
            'hasta'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'elegir'     => ['nullable', 'boolean'],
            'origenes'   => ['nullable', 'array'],
            'origenes.*' => ['integer'],
        ], ['hasta.after_or_equal' => 'La fecha "hasta" no puede ser antes de "desde".']);

        $conciliadores = User::whereHas('roles', fn ($q) => $q->where('name', 'Conciliador'))
            ->where('estatus', 'Activo')->orderBy('delegacion')->orderBy('name')->get(['id', 'name', 'delegacion']);

        $vista = ['filtros' => $f, 'conciliadores' => $conciliadores, 'listo' => false,
            'historial' => SedesConciliador::historial(null, 10)->where('tipo', TraspasoAudiencias::TIPO_BITACORA)->values(),
            'nombres' => $conciliadores->pluck('name', 'id')];

        if (empty($f['sede']) || empty($f['destino']) || empty($f['desde'])) {
            return view('administracion.conciliadores.traspaso', $vista);
        }

        $destino  = $this->conciliador((int) $f['destino']);
        $hasta    = $f['hasta'] ?? null;
        $origenes = TraspasoAudiencias::origenes($f['sede'], $f['desde'], $hasta, $destino->id);

        // La primera vez van todos marcados; después, los que se dejaron marcados.
        $elegidos = ! empty($f['elegir'])
            ? array_values(array_intersect($origenes->pluck('id')->all(), array_map('intval', $f['origenes'] ?? [])))
            : $origenes->pluck('id')->all();

        // Por conciliador, cuántas le cabrían al destino si se pasaran sólo las suyas.
        $porOrigen = [];
        foreach ($origenes as $o) {
            $porOrigen[$o->id] = TraspasoAudiencias::resumen(TraspasoAudiencias::revisar($f['sede'], $f['desde'], $hasta, $destino, [$o->id]));
        }

        $revision = TraspasoAudiencias::revisar($f['sede'], $f['desde'], $hasta, $destino, $elegidos);

        return view('administracion.conciliadores.traspaso', array_merge($vista, [
            'listo'     => true,
            'destino'   => $destino,
            'agenda'    => TraspasoAudiencias::agendaEn($destino, $f['sede'], $f['desde']),
            'origenes'  => $origenes,
            'elegidos'  => $elegidos,
            'porOrigen' => $porOrigen,
            'revision'  => $revision,
            'resumen'   => TraspasoAudiencias::resumen($revision),
        ]));
    }

    public function aplicarTraspaso(Request $request)
    {
        $f = $request->validate([
            'sede'            => ['required', Rule::in(SedesConciliador::SEDES)],
            'destino'         => ['required', 'integer'],
            'desde'           => ['required', 'date_format:Y-m-d'],
            'hasta'           => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'origenes'        => ['required', 'array', 'min:1'],
            'origenes.*'      => ['integer'],
            'incluir_choques' => ['nullable', 'boolean'],
            'motivo'          => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'origenes.required' => 'Elige al menos un conciliador cuyas audiencias se pasen.',
            'motivo.required'   => 'Escribe el motivo del traspaso (queda en la bitácora).',
        ]);

        $destino = $this->conciliador((int) $f['destino']);

        try {
            $r = TraspasoAudiencias::aplicar($f['sede'], $f['desde'], $f['hasta'] ?? null, $destino,
                array_map('intval', $f['origenes']), ! empty($f['incluir_choques']), $f['motivo']);
        } catch (\App\Support\AgendaOcupadaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $volver = array_filter(['sede' => $f['sede'], 'destino' => $destino->id, 'desde' => $f['desde'], 'hasta' => $f['hasta'] ?? null]);

        return redirect()->route('conciliadores.traspaso', $volver)
            ->with('success', $r['movidas'] === 1
                ? "Se pasó 1 audiencia a {$destino->name}."
                : "Se pasaron {$r['movidas']} audiencias a {$destino->name}.")
            ->with('warning', $r['quedaron'] ? "{$r['quedaron']} se quedaron con su conciliador porque chocaban con la agenda de {$destino->name}." : null);
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
