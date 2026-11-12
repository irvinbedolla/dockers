<?php

namespace App\Support;

use App\Models\HistorialAdministracion;
use App\Models\PermisosConciliador;
use App\Models\SedeConciliador;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * En qué sedes atiende audiencias cada conciliador (Administración →
 * Conciliadores, sólo Super Usuario).
 *
 * Dos formas de tener agenda, las dos en permisos_conciliador:
 *
 *   - La de siempre (renglón con sede NULL, App\Models\PermisosConciliador):
 *       Presencial → su sede (users.delegacion);
 *       Virtual    → la subsede de su sede (Zitácuaro, Lázaro Cárdenas, Sahuayo);
 *       Ambos      → las dos.
 *     Los mismos días y horario para todas.
 *
 *   - Por sede (renglones con sede, App\Models\SedeConciliador): una o más
 *     sedes, de cualquier delegación, cada una con sus días fijos, su horario
 *     y su sala. A lo más UNA presencial; las demás, virtuales. Quien la tiene
 *     está "configurado" y el reparto de audiencias usa sólo estos renglones.
 *
 * Para no romper lo que hoy hace un conciliador, al guardar la agenda por sede
 * también se reescribe su renglón de siempre (tipo, días y horario
 * equivalentes dentro de su región): las pantallas que leen ese renglón
 * (cumplimientos, pendientes, listados) siguen igual, y donde hace falta
 * ver las sedes de fuera de su región se suman con sumarSedes().
 *
 * Los documentos salen de la sede de la audiencia: ya lo hacen, porque
 * todas las plantillas usan la delegación del expediente, que es la sede
 * donde se celebra. Lo único que dependía del conciliador era la sala (sala()).
 */
class SedesConciliador
{
    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /** Sede principal => su subsede (oficina de apoyo). */
    public const SUBSEDE = ['Morelia' => 'Zitácuaro', 'Uruapan' => 'Lázaro Cárdenas', 'Zamora' => 'Sahuayo'];

    public const PRESENCIAL = 'Precencial';

    public const VIRTUAL = 'Virtual';

    public const MODALIDADES = [self::PRESENCIAL => 'Presencial', self::VIRTUAL => 'Virtual'];

    /** Sala que se imprime cuando atiende fuera de su sede y no se capturó otra. */
    public const SALA_FUERA = 'Sala Única';

    /** Colores por sede (pastillas de la agenda y de la pantalla de conciliadores). */
    public const COLORES = [
        'Morelia'         => '#496163',
        'Zitácuaro'       => '#7A8F3A',
        'Uruapan'         => '#B06B2C',
        'Lázaro Cárdenas' => '#2F7FA3',
        'Zamora'          => '#8C1D40',
        'Sahuayo'         => '#6D5BA8',
    ];

    private static ?bool $migrado = null;

    /** @var array<int, Collection>|null id del conciliador => sus renglones por sede */
    private static ?array $porSede = null;

    /** @var array<int, object>|null id del conciliador => su renglón de siempre */
    private static ?array $general = null;

    /** @var array<int, string|null> id => users.delegacion */
    private static array $delegaciones = [];

    /** Ya corrió la migración (columna sede). Antes de eso todo sigue como siempre. */
    public static function migrado(): bool
    {
        return self::$migrado ??= Schema::hasColumn('permisos_conciliador', 'sede');
    }

    /** Para pruebas y después de guardar. */
    public static function olvidar(): void
    {
        self::$migrado = null;
        self::$porSede = null;
        self::$general = null;
        self::$delegaciones = [];
    }

    // ------------------------------------------------------------------
    // Lectura
    // ------------------------------------------------------------------

    /** Una sola consulta por tabla por petición: la agenda pregunta por cada evento. */
    private static function cargar(): void
    {
        if (self::$porSede !== null) {
            return;
        }

        self::$porSede = self::migrado()
            ? SedeConciliador::orderBy('id')->get()->groupBy('id_conciliador')->all()
            : [];

        // El de siempre: si hay renglones repetidos (los hay), vale el primero, como en resumen().
        self::$general = PermisosConciliador::orderBy('id')->get()->unique('id_conciliador')->keyBy('id_conciliador')->all();

        $ids = array_unique(array_merge(array_keys(self::$porSede), array_keys(self::$general)));
        self::$delegaciones = $ids ? User::whereIn('id', $ids)->pluck('delegacion', 'id')->all() : [];
    }

    /** ¿Tiene agenda por sede (configurada en Administración → Conciliadores)? */
    public static function configurado(int $conciliador): bool
    {
        self::cargar();

        return isset(self::$porSede[$conciliador]) && self::$porSede[$conciliador]->isNotEmpty();
    }

    /** @return array<int, int> */
    public static function idsConfigurados(): array
    {
        self::cargar();

        return array_keys(array_filter(self::$porSede, fn ($filas) => $filas->isNotEmpty()));
    }

    /** Renglones por sede de un conciliador. */
    public static function filas(int $conciliador): Collection
    {
        self::cargar();

        return self::$porSede[$conciliador] ?? collect();
    }

    /**
     * Las sedes que atiende, en el mismo formato venga de donde venga:
     * [['sede', 'modalidad' (Precencial|Virtual), 'dias' => [...], 'inicio', 'fin', 'sala', 'por_sede' => bool], …]
     * La presencial primero.
     */
    public static function sedes(User|int $conciliador): array
    {
        self::cargar();
        $id = $conciliador instanceof User ? $conciliador->id : $conciliador;
        $delegacion = $conciliador instanceof User ? $conciliador->delegacion : (self::$delegaciones[$id] ?? null);

        if (self::configurado($id)) {
            $sedes = self::$porSede[$id]->map(fn (SedeConciliador $f) => [
                'sede'      => $f->sede,
                'modalidad' => $f->esPresencial() ? self::PRESENCIAL : self::VIRTUAL,
                'dias'      => $f->dias(),
                'inicio'    => self::hm($f->{($f->dias()[0] ?? 'lunes').'_inicio'}),
                'fin'       => self::hm($f->{($f->dias()[0] ?? 'lunes').'_final'}),
                'sala'      => $f->sala,
                'por_sede'  => true,
            ])->all();
        } else {
            $sedes = self::desdeGeneral(self::$general[$id] ?? null, $delegacion);
        }

        usort($sedes, fn ($a, $b) => [$a['modalidad'] !== self::PRESENCIAL, array_search($a['sede'], self::SEDES)]
            <=> [$b['modalidad'] !== self::PRESENCIAL, array_search($b['sede'], self::SEDES)]);

        return $sedes;
    }

    /** Lo que significa hoy el renglón de siempre, en sedes. */
    public static function desdeGeneral(?object $fila, ?string $delegacion): array
    {
        if (! $fila || ! $delegacion) {
            return [];
        }

        $dias = array_values(array_filter(array_keys(PermisosConciliador::DIAS), fn ($d) => ($fila->{$d} ?? 'No') === 'Si'));
        $primero = $dias[0] ?? 'lunes';
        $base = [
            'dias'     => $dias,
            'inicio'   => self::hm($fila->{$primero.'_inicio'} ?? null) ?: '08:00',
            'fin'      => self::hm($fila->{$primero.'_final'} ?? null) ?: '18:00',
            'sala'     => null,
            'por_sede' => false,
        ];
        $subsede = self::SUBSEDE[$delegacion] ?? null;

        return match ($fila->tipo) {
            'Virtual' => [['sede' => $subsede ?? $delegacion, 'modalidad' => self::VIRTUAL] + $base],
            'Ambos'   => $subsede
                ? [['sede' => $delegacion, 'modalidad' => self::PRESENCIAL] + $base, ['sede' => $subsede, 'modalidad' => self::VIRTUAL] + $base]
                : [['sede' => $delegacion, 'modalidad' => self::PRESENCIAL] + $base],
            default   => [['sede' => $delegacion, 'modalidad' => self::PRESENCIAL] + $base],
        };
    }

    /** ¿Agenda múltiple? Atiende audiencias en más de una sede. */
    public static function multisede(User|int $conciliador): bool
    {
        return count(self::sedes($conciliador)) > 1;
    }

    /** ¿Alguna de sus sedes está fuera de su región? */
    public static function fueraDeRegion(User|int $conciliador): bool
    {
        self::cargar();
        $id = $conciliador instanceof User ? $conciliador->id : $conciliador;
        $region = AgendaContexto::sedesDeRegion($conciliador instanceof User ? $conciliador->delegacion : (self::$delegaciones[$id] ?? null));

        return collect(self::sedes($conciliador))->contains(fn ($s) => ! in_array($s['sede'], $region, true));
    }

    /** Presencial o virtual en esa sede; null si no atiende ahí. */
    public static function modalidadEn(User|int $conciliador, ?string $sede): ?string
    {
        foreach (self::sedes($conciliador) as $s) {
            if ($s['sede'] === $sede) {
                return $s['modalidad'];
            }
        }

        return null;
    }

    /** "Morelia (presencial) · Uruapan (virtual)". */
    public static function resumen(User|int $conciliador): string
    {
        return collect(self::sedes($conciliador))
            ->map(fn ($s) => $s['sede'].' ('.mb_strtolower(self::MODALIDADES[$s['modalidad']]).')')
            ->implode(' · ');
    }

    /** Días como "Lun, Mié y Vie". */
    public static function diasTexto(array $dias): string
    {
        $cortos = array_values(array_map(fn ($d) => PermisosConciliador::DIAS[$d], array_intersect(array_keys(PermisosConciliador::DIAS), $dias)));
        if (count($cortos) === 5) {
            return 'Lun a Vie';
        }

        return count($cortos) > 1 ? implode(', ', array_slice($cortos, 0, -1)).' y '.end($cortos) : ($cortos[0] ?? 'Sin días');
    }

    /**
     * Las sedes que ya ve por su rol ($base) más las de su agenda por sede.
     * A quien no tiene agenda por sede no le cambia nada.
     */
    public static function sumarSedes(?User $usuario, array $base): array
    {
        if (! $usuario || ! self::configurado($usuario->id) || ! $usuario->hasRole('Conciliador')) {
            return $base;
        }

        return array_values(array_unique(array_merge($base, array_column(self::sedes($usuario), 'sede'))));
    }

    /** Conciliadores activos con agenda por sede que atienden en alguna de esas sedes. */
    public static function conciliadoresDeSedes(array $sedes): Collection
    {
        if (! self::migrado() || ! $sedes) {
            return collect();
        }

        $ids = SedeConciliador::whereIn('sede', $sedes)->pluck('id_conciliador')->unique();

        return $ids->isEmpty() ? collect() : User::whereIn('id', $ids)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Conciliador'))
            ->where('estatus', 'Activo')
            ->orderBy('name')
            ->get();
    }

    /** Su renglón para esa sede, si tiene agenda por sede ahí. */
    public static function filaDeSede(int $conciliador, string $sede): ?SedeConciliador
    {
        return self::filas($conciliador)->firstWhere('sede', $sede);
    }

    /**
     * Sala que se imprime en el citatorio y la carátula. null = la de siempre
     * (el mapa por conciliador de su sede). Fuera de su sede, la capturada en
     * su agenda o "Sala Única": su sala de casa no existe en otra sede.
     */
    public static function sala(int $conciliador, ?string $sede): ?string
    {
        $fila = $sede ? self::filaDeSede($conciliador, $sede) : null;
        if (! $fila) {
            return null;
        }
        if (filled($fila->sala)) {
            return $fila->sala;
        }

        self::cargar();

        return $sede === (self::$delegaciones[$conciliador] ?? null) ? null : self::SALA_FUERA;
    }

    /**
     * La semana en forma de tabla: ['lunes' => [['sede', 'modalidad', 'inicio', 'fin'], …], …].
     */
    public static function semana(User|int $conciliador): array
    {
        $semana = array_fill_keys(array_keys(PermisosConciliador::DIAS), []);
        foreach (self::sedes($conciliador) as $s) {
            foreach ($s['dias'] as $dia) {
                $semana[$dia][] = ['sede' => $s['sede'], 'modalidad' => $s['modalidad'], 'inicio' => $s['inicio'], 'fin' => $s['fin']];
            }
        }
        foreach ($semana as &$bloques) {
            usort($bloques, fn ($a, $b) => strcmp($a['inicio'], $b['inicio']));
        }

        return $semana;
    }

    // ------------------------------------------------------------------
    // Escritura
    // ------------------------------------------------------------------

    /**
     * Reglas de negocio de una agenda por sede. Devuelve los errores (vacío = válida).
     * $filas: [['sede', 'modalidad', 'dias' => [...], 'inicio' => 'HH:MM', 'fin' => 'HH:MM', 'sala'?], …]
     */
    public static function validar(array $filas): array
    {
        $errores = [];

        if (! $filas) {
            return ['Agrega al menos una sede.'];
        }

        $sedes = array_column($filas, 'sede');
        if (count($sedes) !== count(array_unique($sedes))) {
            $errores[] = 'Una sede aparece dos veces: cada sede va una sola vez.';
        }

        if (count(array_filter($filas, fn ($f) => $f['modalidad'] === self::PRESENCIAL)) > 1) {
            $errores[] = 'Sólo puede tener una sede presencial; las demás deben ser virtuales.';
        }

        foreach ($filas as $f) {
            if (! $f['dias']) {
                $errores[] = "{$f['sede']}: elige al menos un día.";
            }
            if ($f['inicio'] >= $f['fin']) {
                $errores[] = "{$f['sede']}: la hora de salida debe ser después de la de entrada.";
            }
        }

        // Un mismo día puede tener dos sedes con horario encimado (hoy pasa con
        // "Ambos": su sede en sala y la subsede virtual). No se le dan dos
        // audiencias a la misma hora: AgendaAudiencias::ocupado revisa todas sus
        // sedes, y grupoBloqueo() hace que esas sedes compartan candado.

        return array_values(array_unique($errores));
    }

    /**
     * Guarda la agenda por sede y reescribe su renglón de siempre para que
     * lo de hoy (cumplimientos, listados) lo siga viendo igual. Deja el cambio
     * en la bitácora de Administración.
     */
    public static function guardar(User $conciliador, array $filas, ?string $motivo = null): void
    {
        $antes = self::describir($conciliador);

        DB::transaction(function () use ($conciliador, $filas) {
            SedeConciliador::where('id_conciliador', $conciliador->id)->delete();

            foreach ($filas as $f) {
                SedeConciliador::create(self::valoresDia($f['dias'], $f['inicio'], $f['fin']) + [
                    'id_conciliador' => $conciliador->id,
                    'sede'           => $f['sede'],
                    'tipo'           => $f['modalidad'],
                    'sala'           => filled($f['sala'] ?? null) ? trim($f['sala']) : null,
                ]);
            }

            $general = self::generalEquivalente($conciliador->delegacion, $filas);
            PermisosConciliador::guardarPara($conciliador->id, $general['tipo'], $general['dias'], $general['inicio'], $general['fin']);
        });

        self::olvidar();
        self::bitacora($conciliador, $antes, self::describir($conciliador), $motivo);
    }

    /**
     * Quita la agenda por sede: vuelve a la de siempre con el renglón que se
     * dejó al guardar (las sedes de su región). Las audiencias ya asignadas no se mueven.
     */
    public static function quitar(User $conciliador, ?string $motivo = null): void
    {
        $antes = self::describir($conciliador);
        SedeConciliador::where('id_conciliador', $conciliador->id)->delete();
        self::olvidar();
        self::bitacora($conciliador, $antes, self::describir($conciliador), $motivo);
    }

    /**
     * El renglón de siempre que equivale a una agenda por sede, dentro de su
     * región: Ambos si cubre su sede y su subsede; Virtual si sólo la subsede;
     * Presencial en otro caso. Días: la unión; horario: el más amplio.
     */
    public static function generalEquivalente(?string $delegacion, array $filas): array
    {
        $sedes = array_column($filas, 'sede');
        $subsede = self::SUBSEDE[$delegacion] ?? null;
        $casa = in_array($delegacion, $sedes, true);
        $sub = $subsede && in_array($subsede, $sedes, true);

        $dias = [];
        foreach ($filas as $f) {
            $dias = array_merge($dias, $f['dias']);
        }

        return [
            'tipo'   => $casa && $sub ? 'Ambos' : ($sub ? 'Virtual' : 'Precencial'),
            'dias'   => array_values(array_intersect(array_keys(PermisosConciliador::DIAS), $dias)),
            'inicio' => $filas ? min(array_column($filas, 'inicio')) : '08:00',
            'fin'    => $filas ? max(array_column($filas, 'fin')) : '18:00',
        ];
    }

    /** Audiencias por celebrar que tiene en cada sede: ['Uruapan' => 3, …]. */
    public static function pendientesPorSede(int $conciliador): array
    {
        $porSede = [];
        $filas = DB::table('audiencias')
            ->where('id_conciliador', $conciliador)
            ->where('fecha', '>=', now()->toDateString())
            ->where('estatus', 'Pendiente')
            ->selectRaw('delegacion, COUNT(*) AS total')
            ->groupBy('delegacion')
            ->get();

        // audiencias.delegacion a veces va sin acento ("Zitacuaro"): se junta con su sede.
        foreach ($filas as $f) {
            $sede = self::canonica($f->delegacion) ?? (string) $f->delegacion;
            $porSede[$sede] = ($porSede[$sede] ?? 0) + (int) $f->total;
        }

        return $porSede;
    }

    /** "Zitacuaro" → "Zitácuaro"; null si no es una de las seis sedes. */
    public static function canonica(?string $sede): ?string
    {
        $clave = mb_strtolower(\Illuminate\Support\Str::ascii(trim((string) $sede)));
        foreach (self::SEDES as $s) {
            if (mb_strtolower(\Illuminate\Support\Str::ascii($s)) === $clave) {
                return $s;
            }
        }

        return null;
    }

    /**
     * Candado de agenda que comparten las oficinas unidas por un conciliador
     * con agenda por sede. Hoy cada oficina (Morelia con Zitácuaro, Uruapan con
     * Lázaro Cárdenas, Zamora con Sahuayo) tiene el suyo; si alguien atiende
     * Morelia y Uruapan, dos confirmaciones al mismo tiempo, una en cada
     * oficina, podrían darle la misma hora. Con esto Morelia y Uruapan toman el
     * mismo candado. Sin agendas por sede entre oficinas, el nombre es el de siempre.
     */
    public static function grupoBloqueo(string $oficina): string
    {
        if (! self::migrado()) {
            return $oficina;
        }
        self::cargar();

        $padre = [];
        $raiz = function (string $x) use (&$padre, &$raiz): string {
            $padre[$x] ??= $x;

            return $padre[$x] === $x ? $x : ($padre[$x] = $raiz($padre[$x]));
        };

        foreach (self::idsConfigurados() as $id) {
            $oficinas = array_map(fn ($s) => AgendaAudiencias::oficina($s), array_column(self::sedes($id), 'sede'));
            $oficinas[] = AgendaAudiencias::oficina(self::$delegaciones[$id] ?? null);
            $oficinas = array_values(array_unique(array_filter($oficinas, fn ($o) => $o !== 'general')));
            foreach (array_slice($oficinas, 1) as $o) {
                $padre[$raiz($o)] = $raiz($oficinas[0]);
            }
        }

        $mia = $raiz($oficina);
        $grupo = array_values(array_filter(array_keys($padre), fn ($o) => $raiz($o) === $mia));
        sort($grupo);

        return count($grupo) > 1 ? implode('-', $grupo) : $oficina;
    }

    /** Últimos cambios a la agenda (bitácora de Administración), del conciliador o de todos. */
    public static function historial(?int $conciliador = null, int $cuantos = 15): Collection
    {
        return HistorialAdministracion::where('tipo', 'agenda_conciliador')
            ->when($conciliador, fn ($q) => $q->where('registro_id', $conciliador))
            ->orderByDesc('id')
            ->limit($cuantos)
            ->get();
    }

    private static function valoresDia(array $dias, string $inicio, string $fin): array
    {
        $valores = [];
        foreach (array_keys(PermisosConciliador::DIAS) as $dia) {
            $valores[$dia] = in_array($dia, $dias, true) ? 'Si' : 'No';
            $valores[$dia.'_inicio'] = $inicio.':00';
            $valores[$dia.'_final'] = $fin.':00';
        }

        return $valores;
    }

    /** Para la bitácora: ['Morelia' => 'Presencial · Lun a Vie 08:00–18:00 · Sala 2', …]. */
    private static function describir(User $conciliador): array
    {
        self::olvidar();
        $texto = [];
        foreach (self::sedes($conciliador) as $s) {
            $texto[$s['sede']] = self::MODALIDADES[$s['modalidad']].' · '.self::diasTexto($s['dias'])." {$s['inicio']}–{$s['fin']}"
                .(filled($s['sala']) ? " · {$s['sala']}" : '');
        }
        $texto['Forma'] = self::configurado($conciliador->id) ? 'Por sede' : 'La de siempre';

        return $texto;
    }

    private static function bitacora(User $conciliador, array $antes, array $despues, ?string $motivo): void
    {
        $cambio = array_diff_assoc($despues, $antes) + array_fill_keys(array_keys(array_diff_key($antes, $despues)), '— (ya no)');
        if (! $cambio) {
            return;
        }

        HistorialAdministracion::create([
            'tipo'          => 'agenda_conciliador',
            'tabla'         => 'permisos_conciliador',
            'registro_id'   => $conciliador->id,
            'NUE'           => null,
            'delegacion'    => $conciliador->delegacion,
            'datos_antes'   => $antes + array_fill_keys(array_keys(array_diff_key($despues, $antes)), '—'),
            'datos_despues' => $cambio,
            'motivo'        => $motivo,
            'user_id'       => auth()->id(),
            'user_nombre'   => auth()->user()->name ?? null,
            'ip'            => request()->ip(),
        ]);
    }

    private static function hm(?string $hora): string
    {
        return $hora ? substr($hora, 0, 5) : '';
    }
}
