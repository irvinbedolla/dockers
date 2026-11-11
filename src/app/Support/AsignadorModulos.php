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
    public static function capacidadExcepcion(string $sede, string $canal, string $hora, ?Collection $modulos = null): ?int
    {
        if (! self::hayModulosExcepcion($sede)) {
            return null;
        }
        $modulos ??= self::modulosExcepcion($sede);
        $config   = HorarioCitas::config($sede, null, true);
        $duracion = (int) $config->duracion;

        $personas = $modulos->filter(fn (Modulo $m) => self::atendible($m) && $m->recibePor($canal) && ! $m->enComida($hora, $duracion))->count();

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
            ->filter(fn (Modulo $m) => $m->recibePor($canal))
            ->filter(fn (Modulo $m) => ! $reasignacion || $m->aceptaReasignacion($fecha));

        foreach (['recibe', 'recibeDeRespaldo'] as $criterio) {
            $libres = $modulos
                ->filter(fn (Modulo $m) => $m->{$criterio}($tramite))
                ->filter(fn (Modulo $m) => ! $m->enComida($hora, $duracionExc) && self::choque($m, $fecha, $hora, $excepto) === null);

            if ($libres->isNotEmpty()) {
                return $libres->random();
            }
        }

        return null;
    }

    /**
     * Para el calendario de horarios: cuántos módulos podrían recibir la cita
     * en cada horario, con la MISMA regla que elegir() (activo, con persona
     * activa, que reciba el trámite de primera mano o de respaldo, que no esté
     * en pausa para ese canal, que no esté en su comida y que ni el módulo ni
     * su persona tengan ya otra cita a esa hora). Así el calendario no ofrece
     * un horario en el que al guardar saldría "No se ha encontrado auxiliar".
     *
     * Una sola consulta para todo el rango. Devuelve
     * ['libres' => fn (string $fecha, string $hora): int, 'hayQuienReciba' => bool].
     */
    public static function libresPorHorario(string $sede, string $tramite, string $desde, string $hasta, bool $enLinea = false, bool $reasignacion = false): array
    {
        $canal = $enLinea ? 'linea' : 'ventanilla';
        $modulos = self::deSede($sede, true)
            ->filter(fn (Modulo $m) => self::atendible($m))
            ->filter(fn (Modulo $m) => $m->recibe($tramite) || $m->recibeDeRespaldo($tramite))
            ->values();
        $hayQuienReciba = $modulos->isNotEmpty();
        $modulos = $modulos->filter(fn (Modulo $m) => $m->recibePor($canal))->values();

        if ($modulos->isEmpty()) {
            return ['libres' => fn () => 0, 'hayQuienReciba' => $hayQuienReciba];
        }

        // Citas que ocupan a esos módulos o a sus personas, como choque().
        $ocupado = [];
        DB::table('recepcion')
            ->whereBetween('fecha', [$desde, $hasta])
            ->whereNotIn('estatus', ['expirada', 'cancelada'])
            ->where(fn ($q) => $q->whereIn('modulo_id', $modulos->pluck('id'))->orWhereIn('auxiliar', $modulos->pluck('user_id')))
            ->get(['fecha', 'hora', 'modulo_id', 'auxiliar'])
            ->each(function ($c) use (&$ocupado) {
                $clave = substr((string) $c->fecha, 0, 10).'|'.substr((string) $c->hora, 0, 5);
                $ocupado[$clave]['m'][(int) $c->modulo_id] = true;
                $ocupado[$clave]['u'][(int) $c->auxiliar] = true;
            });

        $libres = function (string $fecha, string $hora) use ($modulos, $ocupado, $reasignacion): int {
            $clave = $fecha.'|'.substr($hora, 0, 5);

            return $modulos->filter(fn (Modulo $m) => ! $m->enComida($hora)
                && ! isset($ocupado[$clave]['m'][$m->id])
                && ! isset($ocupado[$clave]['u'][(int) $m->user_id])
                && (! $reasignacion || $m->aceptaReasignacion($fecha)))->count();
        };

        return ['libres' => $libres, 'hayQuienReciba' => $hayQuienReciba];
    }

    /**
     * ¿Algún módulo de estos (activo, con persona) recibe el trámite, de
     * primera mano o de respaldo? Con $canal ('linea' o 'ventanilla'), sólo
     * los que no lo tienen en pausa para ese canal.
     */
    public static function hayQuienReciba(Collection $modulos, string $tramite, ?string $canal = null): bool
    {
        return $modulos->contains(fn (Modulo $m) => self::atendible($m)
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
