<?php

namespace App\Support;

use App\Models\Modulo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Elige y valida módulos para una cita. Lo usan el formulario público, la
 * ventanilla, el botón "Asignar" de Turnos y la reasignación manual.
 *
 * Un módulo puede recibir una cita a una hora si:
 *   - está activo y tiene persona asignada (y esa persona está Activa),
 *   - no es su media hora de comida,
 *   - ni el módulo ni su persona tienen ya otra cita no expirada a esa hora.
 *
 * Lo segundo mira también a la persona porque alguien puede quedarse con
 * citas viejas de cuando atendía otro módulo.
 *
 * Además se saltan los módulos en pausa (switch "Recibe citas" de
 * Administración → Módulos): las citas en línea, los que tienen pausadas las
 * de en línea o todas; la ventanilla y la reagenda, sólo los que tienen
 * pausadas todas (Modulo::recibePor).
 */
class AsignadorModulos
{
    public const TRAMITES = ['Solicitud', 'Asesoría', 'Ratificación', 'Cumplimiento'];

    /**
     * Trámite de los módulos de casos de excepción (Modulo::esDeExcepcion).
     * Cada sede puede tener los suyos; la cita de excepción de esa sede se le
     * asigna a uno libre y el cupo de cada horario es cuántos están libres
     * (capacidadExcepcion()). Una sede sin módulo de excepción sigue como
     * antes: al Departamento, con el calendario compartido.
     */
    public const TRAMITE_EXCEPCION = 'Excepción';

    /** Nombres con que se guardaban las citas de excepción antes de los módulos. */
    public const LUGARES_EXCEPCION_ANTERIORES = ['Departamento de genero e igualdad', 'Departamento de Igualdad de Género'];

    private static array $excepcionPorSede = [];

    private static ?array $idsExcepcion = null;

    /** ¿La sede ya tiene al menos un módulo de casos de excepción activo? */
    public static function hayModulosExcepcion(string $sede): bool
    {
        return self::$excepcionPorSede[$sede] ??= self::modulosExcepcion($sede)->isNotEmpty();
    }

    /** Ids de todos los módulos de casos de excepción (de todas las sedes). */
    public static function idsExcepcion(): array
    {
        return self::$idsExcepcion ??= Modulo::where('tramites', 'like', '%Excepci%')->pluck('id')->all();
    }

    /** Vuelve a preguntar (al crear o editar un módulo). */
    public static function olvidarExcepcion(): void
    {
        self::$excepcionPorSede = [];
        self::$idsExcepcion = null;
    }

    /** Módulos de casos de excepción activos de la sede. */
    public static function modulosExcepcion(string $sede): Collection
    {
        return self::deSede($sede, true)->filter(fn (Modulo $m) => $m->esDeExcepcion())->values();
    }

    /** A quién de la sede se le asigna una cita de excepción (en línea o de ventanilla). */
    public static function elegirExcepcion(string $sede, string $fecha, string $hora, bool $enLinea = false): ?Modulo
    {
        return self::elegir($sede, self::TRAMITE_EXCEPCION, $fecha, $hora, $enLinea);
    }

    /**
     * Cupo de un horario de excepción en la sede, como en los demás trámites:
     * lo que dice Administración → Horarios (Caso de excepción: citas por
     * horario), pero nunca más que las personas de sus módulos de excepción
     * que lo pueden atender por ese canal ('linea' o 'ventanilla') a esa hora
     * (activas, sin pausa y fuera de su comida). Null si la sede no tiene
     * módulo de excepción (manda sólo Horarios, como antes).
     */
    public static function capacidadExcepcion(string $sede, string $canal, string $hora, ?Collection $modulos = null, ?string $fecha = null): ?int
    {
        if (! self::hayModulosExcepcion($sede)) {
            return null;
        }
        $modulos ??= self::modulosExcepcion($sede);
        $config   = HorarioCitas::config($sede, null, true);
        $duracion = (int) $config->duracion;

        $personas = $modulos->filter(fn (Modulo $m) => self::atendible($m) && $m->recibePor($canal) && ! $m->enComida($hora, $duracion)
            && ! ($fecha && $m->bloqueadoEl($fecha)))->count();

        return min($personas, HorarioCitas::capacidad($config, $hora));
    }

    /** Módulos de una sede, ordenados, con su persona. */
    public static function deSede(string $sede, bool $soloActivos = false): Collection
    {
        return Modulo::with('persona:id,name,estatus,foto_perfil')
            ->where('delegacion', $sede)
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Elige al azar un módulo libre que reciba el trámite. Si no hay, prueba
     * con los que lo reciben de respaldo. Null si nadie puede.
     * $enLinea: es una cita del formulario público. Siempre se saltan los
     * módulos en pausa para ese canal. $reasignacion: es una cita que ya
     * existía y se mueve (reagenda); se saltan también los que no aceptan
     * reasignaciones para esa fecha.
     */
    public static function elegir(string $sede, string $tramite, string $fecha, string $hora, bool $enLinea = false, bool $reasignacion = false, ?int $excepto = null): ?Modulo
    {
        $canal = $enLinea ? 'linea' : 'ventanilla';
        // Los de excepción usan la duración de su horario (Horarios → Caso de excepción) para la comida.
        $duracionExc = $tramite === self::TRAMITE_EXCEPCION ? (int) HorarioCitas::config($sede, null, true)->duracion : null;
        $modulos = self::deSede($sede, true)
            ->filter(fn (Modulo $m) => self::atendible($m))
            ->filter(fn (Modulo $m) => $m->recibePor($canal, $reasignacion))
            // Día bloqueado en el calendario de su persona (Administración → Módulos).
            ->filter(fn (Modulo $m) => ! $m->bloqueadoEl($fecha))
            ->filter(fn (Modulo $m) => ! $reasignacion || $m->aceptaReasignacion($fecha));

        // Módulos de apoyo: sus solicitudes y ratificaciones no caen en las mismas horas (choqueApoyo).
        $duracionCita = $modulos->contains(fn (Modulo $m) => $m->esDeApoyo())
            ? (int) HorarioCitas::config($sede, $tramite, $tramite === self::TRAMITE_EXCEPCION)->duracion
            : 0;

        foreach (['recibe', 'recibeDeRespaldo'] as $criterio) {
            $libres = $modulos
                ->filter(fn (Modulo $m) => $m->{$criterio}($tramite))
                ->filter(fn (Modulo $m) => ! $m->enComida($hora, $duracionExc) && self::choque($m, $fecha, $hora, $excepto) === null
                    && ! ($m->esDeApoyo() && self::choqueApoyo($m, $fecha, $hora, $duracionCita, $excepto)));

            if ($libres->isNotEmpty()) {
                return $libres->random();
            }
        }

        return null;
    }

    /**
     * Para el calendario: lo mismo que elegir() !== null, pero para todo un
     * rango de fechas con una sola consulta. Devuelve una función
     * (fecha 'Y-m-d', hora 'H:i:s') => bool que dice si a esa hora hay al
     * menos un módulo que podría recibir la cita (activo, con persona activa,
     * sin pausa para el canal, que reciba el trámite o lo tenga de respaldo,
     * fuera de su comida y sin otra cita a esa hora, ni el módulo ni su
     * persona). Así no se ofrece un horario en el que luego saldría
     * "No se ha encontrado auxiliar disponible".
     *
     * $reasignacion y $excepto: como en elegir(), para la reagenda (sólo
     * módulos que aceptan citas movidas a esa fecha, sin contar la cita que
     * se está moviendo).
     */
    public static function disponibilidadEnRango(string $sede, string $tramite, string $desde, string $hasta, bool $enLinea = false, bool $reasignacion = false, ?int $excepto = null): \Closure
    {
        // Hay citas guardadas sin acento ("Lazaro Cardenas", "Asesoria"); los módulos usan los nombres del catálogo.
        $sede    = CitasCancelacion::sede($sede);
        $tramite = CitasCancelacion::tramite($tramite) ?? $tramite;
        $canal = $enLinea ? 'linea' : 'ventanilla';
        $duracionExc = $tramite === self::TRAMITE_EXCEPCION ? (int) HorarioCitas::config($sede, null, true)->duracion : null;
        $modulos = self::deSede($sede, true)
            ->filter(fn (Modulo $m) => self::atendible($m)
                && $m->recibePor($canal, $reasignacion)
                && ($m->recibe($tramite) || $m->recibeDeRespaldo($tramite)))
            ->values();

        if ($modulos->isEmpty()) {
            return fn (string $fecha, string $hora): bool => false;
        }

        // Las citas de esos módulos o de sus personas en el rango (las mismas que mira choque()).
        $personas = $modulos->pluck('user_id')->filter()->unique()->values()->all();
        $porHorario = DB::table('recepcion')
            ->whereBetween('fecha', [$desde, $hasta])
            ->whereNotIn('estatus', ['expirada', 'cancelada'])
            ->when($excepto, fn ($q) => $q->where('id', '<>', $excepto))
            ->where(fn ($q) => $q->whereIn('modulo_id', $modulos->pluck('id')->all())
                ->when($personas, fn ($w) => $w->orWhereIn('auxiliar', $personas)))
            ->get(['fecha', 'hora', 'modulo_id', 'auxiliar', 'exepcion'])
            ->groupBy(fn ($c) => substr((string) $c->fecha, 0, 10).'T'.HorarioCitas::normalizarHora((string) $c->hora));

        // Módulos de apoyo: sus citas del rango, para ver traslapes (choqueApoyo).
        $deApoyo = $modulos->filter(fn (Modulo $m) => $m->esDeApoyo());
        $citasApoyo = $deApoyo->isNotEmpty() ? self::citasDeApoyo($deApoyo, $desde, $hasta) : collect();
        $duracionCita = $deApoyo->isNotEmpty() ? (int) HorarioCitas::config($sede, $tramite, $tramite === self::TRAMITE_EXCEPCION)->duracion : 0;

        return function (string $fecha, string $hora) use ($modulos, $porHorario, $duracionExc, $reasignacion, $citasApoyo, $duracionCita, $excepto): bool {
            $hora  = HorarioCitas::normalizarHora($hora);
            $citas = $porHorario->get($fecha.'T'.$hora, collect());

            return $modulos->contains(fn (Modulo $m) => ! $m->bloqueadoEl($fecha)
                && (! $reasignacion || $m->aceptaReasignacion($fecha))
                && ! $m->enComida($hora, $duracionExc)
                && ! $citas->contains(fn ($c) => self::citaOcupa($m, $c))
                && ! ($m->esDeApoyo() && self::choqueApoyo($m, $fecha, $hora, $duracionCita, $excepto, $citasApoyo)));
        };
    }

    /** La misma regla que choque(), para una cita ya leída. */
    private static function citaOcupa(Modulo $modulo, object $cita): bool
    {
        $deModulo  = $cita->modulo_id !== null && (int) $cita->modulo_id === (int) $modulo->id;
        $dePersona = $modulo->user_id && $cita->auxiliar !== null && (int) $cita->auxiliar === (int) $modulo->user_id;

        if ($modulo->esDeExcepcion()) {
            return ($deModulo && $cita->exepcion === 'Si') || ($dePersona && ! $deModulo);
        }

        return $deModulo || $dePersona;
    }

    /**
     * ¿Algún módulo de estos (activo, con persona) recibe el trámite, de
     * primera mano o de respaldo? Con $canal ('linea' o 'ventanilla'), sólo
     * los que no lo tienen en pausa para ese canal.
     */
    public static function hayQuienReciba(Collection $modulos, string $tramite, ?string $canal = null): bool
    {
        // Sin "Asignar en línea" (p. ej. los de apoyo) no recibe citas nuevas por ningún canal.
        return $modulos->contains(fn (Modulo $m) => self::atendible($m)
            && $m->asignaCitas()
            && $m->recibePor($canal)
            && ($m->recibe($tramite) || $m->recibeDeRespaldo($tramite)));
    }

    /**
     * ¿Todos los módulos que recibirían este trámite en la sede lo tienen en
     * pausa para ese canal ('linea' o 'ventanilla')? Si no hay ninguno que lo
     * reciba, false (eso no es una pausa, es que la sede no está configurada
     * para él).
     */
    public static function enPausa(string $sede, string $tramite, string $canal, ?Collection $modulos = null): bool
    {
        $modulos ??= self::deSede($sede, true);

        return self::hayQuienReciba($modulos, $tramite) && ! self::hayQuienReciba($modulos, $tramite, $canal);
    }

    /** Activo, con persona y persona activa. */
    public static function atendible(Modulo $modulo): bool
    {
        return $modulo->activo
            && $modulo->user_id !== null
            && $modulo->persona !== null
            && $modulo->persona->estatus === 'Activo';
    }

    /** Trámites que puede atender un módulo de apoyo (orientadores y auxiliares). */
    public const TRAMITES_APOYO = ['Solicitud', 'Ratificación'];

    /**
     * Minutos que ocupa una cita: su hora_fin si la trae con duración (las
     * ratificaciones a veces la guardan igual a la hora); si no, lo que dice
     * Horarios para su trámite.
     */
    public static function duracionCita(string $sede, object $cita): int
    {
        $hora = substr((string) ($cita->hora ?? ''), 0, 5);
        $fin  = substr((string) ($cita->hora_fin ?? ''), 0, 5);
        if ($hora !== '' && $fin !== '' && $fin > $hora) {
            return self::minutos($fin) - self::minutos($hora);
        }

        return (int) HorarioCitas::config($sede, $cita->tipo ?? null, ($cita->exepcion ?? null) === 'Si')->duracion;
    }

    /**
     * Citas (que no están expiradas ni canceladas) de esos módulos o de sus
     * personas entre dos fechas. Para el traslape de los módulos de apoyo.
     */
    public static function citasDeApoyo(Collection $modulos, string $desde, string $hasta): Collection
    {
        $ids      = $modulos->pluck('id')->all();
        $personas = $modulos->pluck('user_id')->filter()->unique()->values()->all();
        if (! $ids) {
            return collect();
        }

        return DB::table('recepcion')
            ->whereBetween('fecha', [substr($desde, 0, 10), substr($hasta, 0, 10)])
            ->whereNotIn('estatus', ['expirada', 'cancelada'])
            ->where(fn ($q) => $q->whereIn('modulo_id', $ids)->when($personas, fn ($w) => $w->orWhereIn('auxiliar', $personas)))
            ->get(['id', 'consecutivo', 'codigo', 'solicitante', 'fecha', 'hora', 'hora_fin', 'tipo', 'exepcion', 'delegacion', 'modulo_id', 'auxiliar']);
    }

    /**
     * Módulo de apoyo: atiende solicitudes (cada 30 min) y ratificaciones (cada
     * 50 min), que no caen en las mismas horas. choque() sólo ve la misma hora
     * exacta; esto ve si la cita nueva (hora + $duracion minutos) se encima con
     * alguna de las que ya tiene el módulo o su persona. Devuelve esa cita o
     * null. $citas (de citasDeApoyo()) evita una consulta por horario.
     */
    public static function choqueApoyo(Modulo $modulo, string $fecha, string $hora, int $duracion, ?int $excepto = null, ?Collection $citas = null): ?object
    {
        $fecha = substr($fecha, 0, 10);
        $citas ??= self::citasDeApoyo(collect([$modulo]), $fecha, $fecha);
        $inicio = self::minutos($hora);
        $fin    = $inicio + max($duracion, 1);

        return $citas->first(function ($c) use ($modulo, $fecha, $excepto, $inicio, $fin) {
            if (substr((string) $c->fecha, 0, 10) !== $fecha || ($excepto && (int) $c->id === $excepto)) {
                return false;
            }
            $suya = ($c->modulo_id !== null && (int) $c->modulo_id === (int) $modulo->id)
                || ($modulo->user_id && $c->auxiliar !== null && (int) $c->auxiliar === (int) $modulo->user_id);
            if (! $suya) {
                return false;
            }
            $empieza = self::minutos((string) $c->hora);

            return $empieza < $fin && $empieza + self::duracionCita((string) $c->delegacion, $c) > $inicio;
        });
    }

    /** "09:20" o "09:20:00" → minutos del día. */
    private static function minutos(string $hora): int
    {
        return (int) substr($hora, 0, 2) * 60 + (int) substr($hora, 3, 2);
    }

    /**
     * La cita con la que chocaría este módulo a esa fecha y hora, o null.
     * $excepto deja fuera a la cita que se está moviendo.
     */
    public static function choque(Modulo $modulo, string $fecha, string $hora, ?int $excepto = null): ?object
    {
        return DB::table('recepcion')
            ->where('fecha', $fecha)
            ->where('hora', strlen($hora) === 5 ? $hora.':00' : $hora)
            ->whereNotIn('estatus', ['expirada','cancelada'])
            ->when($excepto, fn ($q) => $q->where('id', '<>', $excepto))
            ->where(function ($q) use ($modulo) {
                if ($modulo->esDeExcepcion()) {
                    // Sólo sus citas que siguen siendo de excepción (la que se reclasificó
                    // ya no le quita lugar) y lo que esa persona tenga en otro módulo.
                    $q->where(fn ($w) => $w->where('modulo_id', $modulo->id)->where('exepcion', 'Si'));
                    if ($modulo->user_id) {
                        $q->orWhere(fn ($w) => $w->where('auxiliar', $modulo->user_id)
                            ->where(fn ($m) => $m->whereNull('modulo_id')->orWhere('modulo_id', '<>', $modulo->id)));
                    }

                    return;
                }
                $q->where('modulo_id', $modulo->id);
                if ($modulo->user_id) {
                    $q->orWhere('auxiliar', $modulo->user_id);
                }
            })
            ->first(['id', 'consecutivo', 'codigo', 'solicitante', 'hora', 'delegacion']);
    }
}
