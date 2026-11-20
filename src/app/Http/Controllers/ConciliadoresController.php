<?php

namespace App\Http\Controllers;

use App\Models\DiasInhabiles;
use App\Models\PermisosConciliador;
use App\Models\User;
use App\Support\AgendaContexto;
use App\Support\SedesConciliador;
use App\Support\TraspasoAudiencias;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            'bloqueos'  => self::bloqueosDe($visibles->map(fn ($r) => $r->usuario->id)->all()),
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
            'susBloqueos' => self::bloqueosDe([$conciliador->id])->get($conciliador->id, collect()),
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
            'nombres' => $conciliadores->pluck('name', 'id'), 'resultado' => session('resultado')];

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
            'saturados' => TraspasoAudiencias::saturados($revision),
            'opciones'  => TraspasoAudiencias::opciones($f['sede'], $revision, $destino),
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
            'decisiones'      => ['nullable', 'array'],
            'decisiones.*'    => ['nullable', 'string', 'max:20'],
            'confirmar_empalmes' => ['nullable', 'boolean'],
            'motivo'          => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'origenes.required' => 'Elige al menos un conciliador cuyas audiencias se pasen.',
            'motivo.required'   => 'Escribe el motivo del traspaso (queda en la bitácora).',
        ]);

        $destino = $this->conciliador((int) $f['destino']);

        try {
            $r = TraspasoAudiencias::aplicar($f['sede'], $f['desde'], $f['hasta'] ?? null, $destino,
                array_map('intval', $f['origenes']), $f['decisiones'] ?? [], ! empty($f['confirmar_empalmes']), $f['motivo']);
        } catch (\App\Support\AgendaOcupadaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($r['error']) {
            $detalle = collect($r['saturados'])->map(fn ($s) => \Illuminate\Support\Carbon::parse($s['fecha'])->format('d/m/Y')
                ." {$s['hora']} ({$s['total']}: ".implode(', ', $s['expedientes']).')')->implode(' · ');

            return back()->withInput()->with('error', $r['error'].' '.$detalle);
        }

        $volver = array_filter(['sede' => $f['sede'], 'destino' => $destino->id, 'desde' => $f['desde'], 'hasta' => $f['hasta'] ?? null]);

        // Para la tarjeta "Resumen del traspaso" (se descarga como imagen).
        $resultado = [
            'sede'      => $f['sede'],
            'destino'   => $destino->name,
            'periodo'   => SedesConciliador::vigenciaTexto($f['desde'], $f['hasta'] ?? null),
            'motivo'    => $f['motivo'],
            'cuando'    => now()->format('d/m/Y H:i'),
            'por'       => $request->user()->name,
            'movidas'   => $r['movidas'],
            'quedaron'  => $r['quedaron'],
            'filas'     => $r['filas'],
        ];

        return redirect()->route('conciliadores.traspaso', $volver)
            ->with('resultado', $resultado)
            ->with('success', $r['movidas'] === 1 ? 'Se traspasó 1 audiencia.' : "Se traspasaron {$r['movidas']} audiencias.")
            ->with('warning', trim(($r['quedaron'] ? "{$r['quedaron']} se quedaron con su conciliador. " : '').implode(' ', $r['avisos'] ?? [])) ?: null);
    }

    /**
     * Qué se bloquea (dias_inhabiles.tipo). Los tres los respeta el reparto de
     * audiencias (ObtenerAudiencia) y Traspasar audiencias.
     */
    public const TIPOS_BLOQUEO = [
        'Bloqueo por permiso' => 'Permiso (vacaciones, incapacidad, comisión)',
        'Audiencias'          => 'Sólo audiencias',
        'Todos'               => 'Todo (audiencias, ratificaciones y cumplimientos)',
    ];

    private const TIPOS_CORTO = ['Bloqueo por permiso' => 'Permiso', 'Audiencias' => 'Audiencias', 'Todos' => 'Todo'];

    /**
     * Bloquear días de un conciliador desde su tarjeta (lo mismo que
     * Administración → Sedes → "Bloquear conciliador": un renglón de
     * dias_inhabiles con su user_id). Todo el día = de 00:00 a 23:59.
     */
    public function bloquear(Request $request, int $id)
    {
        $conciliador = $this->conciliador($id);
        $hoy = Carbon::today()->toDateString();

        $d = $request->validate([
            'desde'       => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$hoy],
            'hasta'       => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'tipo'        => ['required', Rule::in(array_keys(self::TIPOS_BLOQUEO))],
            'todo_el_dia' => ['nullable', 'boolean'],
            'hora_inicio' => ['exclude_if:todo_el_dia,1', 'required', 'date_format:H:i'],
            'hora_final'  => ['exclude_if:todo_el_dia,1', 'required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'desde.required'       => 'Elige desde qué día.',
            'desde.after_or_equal' => 'No se puede bloquear un día que ya pasó.',
            'hasta.after_or_equal' => 'La fecha "hasta" no puede ser antes de "desde".',
            'hora_final.after'     => 'La hora final debe ser después de la inicial.',
        ]);

        $desde = $d['desde'];
        $hasta = $d['hasta'] ?? $d['desde'];
        $todo  = ! empty($d['todo_el_dia']);
        $hi    = $todo ? '00:00:00' : $d['hora_inicio'].':00';
        $hf    = $todo ? '23:59:00' : $d['hora_final'].':00';

        $cruce = DiasInhabiles::where('user_id', $conciliador->id)
            ->whereDate('fecha_inicio', '<=', $hasta)
            ->whereDate('fecha_final', '>=', $desde)
            ->where('horario_inicio', '<', $hf)
            ->where('horario_final', '>', $hi)
            ->first();
        if ($cruce) {
            return back()->with('error', "{$conciliador->name} ya tiene un bloqueo que se cruza: ".self::bloqueoTexto($cruce, true).'. Quítalo o elige otras fechas.');
        }

        DiasInhabiles::create([
            'fecha_inicio'   => $desde,
            'fecha_final'    => $hasta,
            'horario_inicio' => $hi,
            'horario_final'  => $hf,
            'centro'         => $conciliador->delegacion,
            'user_id'        => $conciliador->id,
            // "No inhabil" + horario: así lo leen ObtenerAudiencia y el calendario de la sede.
            // ("Inhabil" en dias_inhabiles es suspensión de términos de la sede.)
            'descripcion'    => 'No inhabil',
            'tipo'           => $d['tipo'],
        ]);

        // Lo ya agendado en ese periodo no se mueve solo.
        $agendadas = DB::table('audiencias')
            ->where('id_conciliador', $conciliador->id)
            ->where('estatus', 'Pendiente')
            ->whereBetween('fecha', [$desde, $hasta])
            ->when(! $todo, fn ($q) => $q->whereBetween('hora', [$hi, $hf]))
            ->count();

        $periodo = $desde === $hasta
            ? 'el '.Carbon::parse($desde)->locale('es')->isoFormat('dddd D [de] MMMM')
            : 'del '.Carbon::parse($desde)->format('d/m').' al '.Carbon::parse($hasta)->format('d/m/Y');

        return back()
            ->with('success', "Se bloqueó a {$conciliador->name} {$periodo}".($todo ? '' : ' de '.substr($hi, 0, 5).' a '.substr($hf, 0, 5))
                .': el reparto no le dará audiencias nuevas.')
            ->with('warning', $agendadas
                ? "Ojo: ya tiene {$agendadas} ".($agendadas === 1 ? 'audiencia agendada' : 'audiencias agendadas').' en ese periodo. Siguen a su nombre: muévelas con "Traspasar audiencias".'
                : null);
    }

    /** Quita un bloqueo del conciliador. */
    public function quitarBloqueo(int $id, int $bloqueo)
    {
        $conciliador = $this->conciliador($id);
        $b = DiasInhabiles::where('id', $bloqueo)->where('user_id', $conciliador->id)->first();
        if (! $b) {
            return back()->with('warning', 'Ese bloqueo ya no existe.');
        }
        $texto = self::bloqueoTexto($b);
        $b->delete();

        return back()->with('success', "Se quitó el bloqueo de {$conciliador->name} ({$texto}).");
    }

    /** Bloqueos de hoy en adelante de esos conciliadores: [user_id => Collection]. */
    public static function bloqueosDe(array $ids): Collection
    {
        if (! $ids) {
            return collect();
        }

        return DiasInhabiles::whereIn('user_id', $ids)
            ->whereDate('fecha_final', '>=', Carbon::today())
            ->orderBy('fecha_inicio')
            ->get()
            ->groupBy('user_id');
    }

    /** "Lun 14 dic", "14–18 dic", "14 dic – 9 ene", con horario si no es todo el día; $largo agrega qué bloquea. */
    public static function bloqueoTexto(DiasInhabiles $b, bool $largo = false): string
    {
        $i = Carbon::parse($b->fecha_inicio)->locale('es');
        $f = Carbon::parse($b->fecha_final)->locale('es');
        $fechas = match (true) {
            $i->isSameDay($f)    => \Illuminate\Support\Str::ucfirst($i->isoFormat('ddd D MMM')),
            $i->isSameMonth($f)  => $i->format('j').'–'.$f->isoFormat('D MMM'),
            default              => $i->isoFormat('D MMM').' – '.$f->isoFormat('D MMM'),
        };
        $hi = substr((string) $b->horario_inicio, 0, 5);
        $hf = substr((string) $b->horario_final, 0, 5);
        $diaCompleto = $b->descripcion === 'Inhabil' || ($hi <= '00:00' && $hf >= '23:59');
        $horas = $diaCompleto ? '' : " {$hi}–{$hf}";
        $tipo = self::TIPOS_CORTO[$b->tipo] ?? $b->tipo;

        return $largo
            ? $fechas.$horas.' · '.(self::TIPOS_BLOQUEO[$b->tipo] ?? $b->tipo).($diaCompleto ? ' · todo el día' : '')
            : $fechas.$horas.' · '.$tipo;
    }

    /** ¿El bloqueo incluye hoy? (para resaltarlo). */
    public static function bloqueoHoy(DiasInhabiles $b): bool
    {
        $hoy = Carbon::today()->toDateString();

        return substr((string) $b->fecha_inicio, 0, 10) <= $hoy && substr((string) $b->fecha_final, 0, 10) >= $hoy;
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
