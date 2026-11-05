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
     * módulos en pausa para ese canal.
     */
    public static function elegir(string $sede, string $tramite, string $fecha, string $hora, bool $enLinea = false): ?Modulo
    {
        $canal = $enLinea ? 'linea' : 'ventanilla';
        $modulos = self::deSede($sede, true)
            ->filter(fn (Modulo $m) => self::atendible($m))
            ->filter(fn (Modulo $m) => $m->recibePor($canal));

        foreach (['recibe', 'recibeDeRespaldo'] as $criterio) {
            $libres = $modulos
                ->filter(fn (Modulo $m) => $m->{$criterio}($tramite))
                ->filter(fn (Modulo $m) => ! $m->enComida($hora) && self::choque($m, $fecha, $hora) === null);

            if ($libres->isNotEmpty()) {
                return $libres->random();
            }
        }

        return null;
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
                $q->where('modulo_id', $modulo->id);
                if ($modulo->user_id) {
                    $q->orWhere('auxiliar', $modulo->user_id);
                }
            })
            ->first(['id', 'consecutivo', 'codigo', 'solicitante', 'hora', 'delegacion']);
    }
}
