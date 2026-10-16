<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * "Audiencias de hoy" de la recepción (Morelia 02): marcar que llegó cada
 * parte (el trabajador y cada citado) y avisarle al conciliador.
 *
 *   delDia()     audiencias pendientes del día con sus partes y quién llegó;
 *   marcar()     registra (o quita) la llegada de una parte;
 *   avisosPara() llegadas recientes de las audiencias de un conciliador, para
 *                el aviso con sonido (audiencias/_avisos_llegada).
 *
 * Las partes salen de seer_solicitante y seer_citados. Los citados se
 * replican en cada reagenda y a veces traen el mismo nombre repetido, así que
 * se toman los de esta audiencia (o los de la solicitud si no hay) y se
 * agrupan por nombre. La "clave" de una parte se arma del nombre por lo mismo.
 */
class LlegadasAudiencia
{
    public const TABLA = 'audiencia_llegadas';

    /** Quién marca: la recepción de audiencias y quien la supervisa. */
    public const ROLES = ['Super Usuario', Recepcion::MORELIA_02];

    /** El aviso al conciliador sólo trae llegadas de los últimos minutos. */
    private const MINUTOS_AVISO = 90;

    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasTable(self::TABLA);
    }

    public static function puedeMarcar(?User $usuario): bool
    {
        return $usuario !== null && self::migrado() && $usuario->hasAnyRole(self::ROLES);
    }

    /** Sedes cuyas audiencias atiende: Morelia 02 la suya; el Super Usuario, todas. */
    public static function sedes(User $usuario): array
    {
        return $usuario->hasRole('Super Usuario') ? Recepcion::TODAS_LAS_SEDES : Recepcion::sedes($usuario);
    }

    /**
     * @return Collection<int, array>
     */
    public static function delDia(array $sedes, string $fecha, string $texto = ''): Collection
    {
        $audiencias = DB::table('audiencias as a')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador')
            ->where('a.fecha', $fecha)
            ->where('a.estatus', 'Pendiente')
            ->whereIn('a.delegacion', self::variantes($sedes))
            ->orderBy('a.hora')->orderBy('a.sala')
            ->get(['a.id', 'a.id_solicitud', 'a.hora', 'a.sala', 'a.delegacion', 'a.id_conciliador', 'g.NUE', 'u.name as conciliador']);

        if ($audiencias->isEmpty()) {
            return collect();
        }

        $partes   = self::partes($audiencias);
        $llegadas = DB::table(self::TABLA.' as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.registrado_por')
            ->whereIn('l.audiencia_id', $audiencias->pluck('id'))
            ->get(['l.audiencia_id', 'l.clave', 'l.llegada_at', 'u.name as por'])
            ->groupBy('audiencia_id');

        $texto = Str::lower(Str::ascii(trim($texto)));

        return $audiencias->map(function ($a) use ($partes, $llegadas) {
            $deEsta = ($llegadas[$a->id] ?? collect())->keyBy('clave');
            $lista  = array_map(function ($p) use ($deEsta) {
                $l = $deEsta[$p['clave']] ?? null;

                return $p + [
                    'llegada' => $l ? Carbon::parse($l->llegada_at)->format('H:i') : null,
                    'por'     => $l ? PantallaEspera::nombreCorto($l->por, false) : null,
                ];
            }, $partes[$a->id] ?? []);

            $llegaron = count(array_filter($lista, fn ($p) => $p['llegada']));

            return [
                'id'          => $a->id,
                'hora'        => substr((string) $a->hora, 0, 5),
                'sala'        => (string) $a->sala,
                'sede'        => (string) $a->delegacion,
                'nue'         => (string) $a->NUE,
                'conciliador' => PantallaEspera::nombreCorto($a->conciliador, false),
                'partes'      => $lista,
                'llegaron'    => $llegaron,
                'completa'    => $lista && $llegaron === count($lista),
            ];
        })
        ->filter(function ($a) use ($texto) {
            if ($texto === '') {
                return true;
            }
            $donde = Str::lower(Str::ascii($a['nue'].' '.$a['sala'].' '.$a['conciliador'].' '.implode(' ', array_column($a['partes'], 'nombre'))));

            return Str::contains($donde, $texto);
        })
        ->values();
    }

    /**
     * Marca (o quita) la llegada de una parte. Devuelve el texto de error, o
     * null si salió bien.
     */
    public static function marcar(int $audienciaId, string $clave, bool $llego, User $usuario): ?string
    {
        $audiencia = DB::table('audiencias')->where('id', $audienciaId)->first(['id', 'id_solicitud', 'fecha', 'estatus', 'delegacion']);

        if (! $audiencia) {
            return 'La audiencia ya no existe.';
        }
        if (! in_array($audiencia->delegacion, self::variantes(self::sedes($usuario)), true)) {
            return 'La audiencia no es de tu sede.';
        }
        if (Carbon::parse($audiencia->fecha)->toDateString() !== now()->toDateString()) {
            return 'Sólo se marca la llegada a audiencias de hoy.';
        }

        $parte = collect(self::partes(collect([$audiencia]))[$audiencia->id] ?? [])->firstWhere('clave', $clave);
        if (! $parte) {
            return 'Esa persona no es parte de la audiencia.';
        }

        if (! $llego) {
            DB::table(self::TABLA)->where('audiencia_id', $audienciaId)->where('clave', $clave)->delete();

            return null;
        }

        $ahora = now();
        DB::table(self::TABLA)->insertOrIgnore([
            'audiencia_id'   => $audienciaId,
            'clave'          => $clave,
            'parte'          => $parte['tipo'],
            'nombre'         => Str::limit($parte['nombre'], 250, ''),
            'llegada_at'     => $ahora,
            'registrado_por' => $usuario->id,
            'created_at'     => $ahora,
            'updated_at'     => $ahora,
        ]);

        return null;
    }

    /**
     * Llegadas recientes a las audiencias de hoy de este conciliador.
     *
     * @return Collection<int, array>
     */
    public static function avisosPara(User $conciliador): Collection
    {
        if (! self::migrado()) {
            return collect();
        }

        $ahora = now();

        return DB::table(self::TABLA.' as l')
            ->join('audiencias as a', 'a.id', '=', 'l.audiencia_id')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            ->where('a.id_conciliador', $conciliador->id)
            ->where('a.fecha', $ahora->toDateString())
            ->where('l.llegada_at', '>=', $ahora->copy()->subMinutes(self::MINUTOS_AVISO))
            ->orderBy('l.id')
            ->limit(30)
            ->get(['l.id', 'l.parte', 'l.nombre', 'l.llegada_at', 'a.id as audiencia_id', 'a.id_solicitud', 'a.hora', 'a.sala', 'g.NUE'])
            ->map(fn ($l) => [
                'id'        => $l->id,
                'quien'     => $l->parte === 'solicitante' ? 'el trabajador' : 'la parte citada',
                'nombre'    => Str::title(Str::lower($l->nombre)),
                'llegada'   => Carbon::parse($l->llegada_at)->format('H:i'),
                'nue'       => $l->NUE,
                'sala'      => $l->sala,
                'hora'      => substr((string) $l->hora, 0, 5),
                'url'       => url('solicitud/iniciar/'.$l->id_solicitud).'?audiencia_id='.$l->audiencia_id,
            ]);
    }

    /**
     * Partes de cada audiencia: [audiencia_id => [[clave, tipo, nombre], ...]].
     */
    private static function partes(Collection $audiencias): array
    {
        $solicitudes = $audiencias->pluck('id_solicitud')->unique()->values();
        $ids         = $audiencias->pluck('id')->all();

        $solicitantes = DB::table('seer_solicitante')
            ->whereIn('id_solicitud', $solicitudes)
            ->orderBy('id')
            ->get(['id_solicitud', 'nombre'])
            ->groupBy('id_solicitud');

        $citados = DB::table('seer_citados')
            ->whereIn('id_solicitud', $solicitudes)
            ->orderBy('id')
            ->get(['id_solicitud', 'audiencia_id', 'nombre', 'primer_apellido', 'segundo_apellido'])
            ->groupBy('id_solicitud');

        $resultado = [];
        foreach ($audiencias as $a) {
            $lista = [];

            foreach ($solicitantes[$a->id_solicitud] ?? [] as $i => $s) {
                $nombre = self::limpio($s->nombre);
                if ($nombre !== '') {
                    $lista[] = ['clave' => $i === 0 ? 'solicitante' : 'solicitante:'.self::hash($nombre), 'tipo' => 'solicitante', 'nombre' => $nombre];
                }
            }

            $todos   = collect($citados[$a->id_solicitud] ?? []);
            $deEsta  = $todos->where('audiencia_id', $a->id);
            $vistos  = [];
            foreach (($deEsta->isNotEmpty() ? $deEsta : $todos) as $c) {
                $nombre = self::limpio($c->nombre.' '.$c->primer_apellido.' '.$c->segundo_apellido);
                $hash   = self::hash($nombre);
                if ($nombre === '' || isset($vistos[$hash])) {
                    continue;
                }
                $vistos[$hash] = true;
                $lista[] = ['clave' => 'citado:'.$hash, 'tipo' => 'citado', 'nombre' => $nombre];
            }

            $resultado[$a->id] = $lista;
        }

        return $resultado;
    }

    private static function limpio(?string $nombre): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtoupper((string) $nombre)));
    }

    private static function hash(string $nombre): string
    {
        return substr(sha1(Str::ascii($nombre)), 0, 16);
    }

    /** audiencias.delegacion guarda "Zitacuaro" sin acento. */
    private static function variantes(array $sedes): array
    {
        return array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));
    }
}
