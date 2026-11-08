<?php

namespace App\Services\Sinotifico;

use App\Models\Sinotifico\Diligencia;
use App\Models\Sinotifico\Ruta;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Historial de rutas.
 *
 * Cada parada guarda qué pasó en ella (diligencia, hora de llegada,
 * resultado y estado de la revisión) en el momento en que ocurre, así el
 * historial no depende de cómo cambie después el expediente en SICONCILIO.
 * Además, cada ruta lleva una bitácora: propuesta, ediciones y aprobaciones.
 */
class HistorialRutas
{
    /** Resultados que cuentan como notificación lograda. */
    public const EXITOSAS = ['Finalizado exitosamente', 'Recibe pero no firma'];

    /** Liga una diligencia capturada (o recién revisada) con la parada de su ruta del día. */
    public static function registrar(Diligencia $d): void
    {
        $citados = $d->citadoIds();
        $dia = CarbonImmutable::parse($d->llegada_at)->toDateString();

        $parada = DB::table('sinotifico_ruta_paradas as p')
            ->join('sinotifico_rutas as r', 'r.id', '=', 'p.ruta_id')
            ->where('r.notificador_id', $d->notificador_id)
            ->whereDate('r.fecha', $dia)
            ->where(fn ($q) => $q->whereIn('p.visita_id', $citados)->orWhere('p.solicitud_id', $d->solicitud_id))
            ->orderByRaw('CASE WHEN p.visita_id IN ('.implode(',', array_map('intval', $citados ?: [0])).') THEN 0 ELSE 1 END')
            ->select('p.id', 'p.diligencia_id', 'p.revision')
            ->first();

        if (! $parada) {
            return; // Capturada fuera de una ruta aprobada: no hay parada que actualizar.
        }

        // Una captura nueva sólo reemplaza a la anterior si a esa la devolvieron.
        $ocupada = $parada->diligencia_id && (int) $parada->diligencia_id !== (int) $d->id && $parada->revision !== Diligencia::DEVUELTA;
        if ($ocupada) {
            return;
        }

        DB::table('sinotifico_ruta_paradas')->where('id', $parada->id)->update([
            'diligencia_id' => $d->id,
            'llegada_at' => $d->llegada_at,
            'resultado' => $d->resultado ?? 'Exhorto',
            'revision' => $d->estatus,
            'updated_at' => now(),
        ]);
    }

    public static function evento(Ruta $ruta, string $tipo, ?User $autor, array $detalle = []): void
    {
        DB::table('sinotifico_ruta_eventos')->insert([
            'ruta_id' => $ruta->id,
            'tipo' => $tipo,
            'user_id' => $autor?->id,
            'detalle' => $detalle ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
        ]);
    }

    /** Liga todas las diligencias ya capturadas con sus rutas (al instalar el historial). */
    public static function reconstruir(): int
    {
        $n = 0;
        Diligencia::orderBy('id')->each(function (Diligencia $d) use (&$n) {
            self::registrar($d);
            $n++;
        });

        return $n;
    }

    /**
     * Cifras de una ruta para el historial.
     *
     * @return array{total:int, hechas:int, exitosas:int, validadas:int, por_revisar:int, devueltas:int, cumplimiento:int, estado:string}
     */
    public static function cifras(object $fila): array
    {
        $total = (int) $fila->total;
        $hechas = (int) $fila->hechas;
        $cumplimiento = $total ? (int) round($hechas / $total * 100) : 0;

        return [
            'total' => $total,
            'hechas' => $hechas,
            'exitosas' => (int) $fila->exitosas,
            'validadas' => (int) $fila->validadas,
            'por_revisar' => (int) $fila->por_revisar,
            'devueltas' => (int) $fila->devueltas,
            'cumplimiento' => $cumplimiento,
            'estado' => match (true) {
                $fila->estatus !== Ruta::APROBADA => 'no_aprobada',
                $total > 0 && $hechas === $total => 'cumplida',
                $hechas > 0 => 'parcial',
                default => 'sin_avance',
            },
        ];
    }

    /** Consulta base: una fila por ruta con sus cifras. */
    public static function consulta(string $desde, string $hasta)
    {
        $exitosas = "'".implode("','", self::EXITOSAS)."'";

        return DB::table('sinotifico_rutas as r')
            ->leftJoin('sinotifico_ruta_paradas as p', 'p.ruta_id', '=', 'r.id')
            ->whereBetween('r.fecha', [$desde, $hasta])
            ->groupBy('r.id', 'r.notificador_id', 'r.fecha', 'r.sede', 'r.estatus', 'r.distancia_km', 'r.minutos_estimados', 'r.aprobada_at')
            ->select([
                'r.id', 'r.notificador_id', 'r.fecha', 'r.sede', 'r.estatus', 'r.distancia_km', 'r.minutos_estimados', 'r.aprobada_at',
                DB::raw('COUNT(p.id) as total'),
                DB::raw("SUM(CASE WHEN p.diligencia_id IS NOT NULL AND p.revision <> 'devuelta' THEN 1 ELSE 0 END) as hechas"),
                DB::raw("SUM(CASE WHEN p.revision = 'validada' AND p.resultado IN ($exitosas) THEN 1 ELSE 0 END) as exitosas"),
                DB::raw("SUM(CASE WHEN p.revision = 'validada' THEN 1 ELSE 0 END) as validadas"),
                DB::raw("SUM(CASE WHEN p.revision = 'por_revisar' THEN 1 ELSE 0 END) as por_revisar"),
                DB::raw("SUM(CASE WHEN p.revision = 'devuelta' THEN 1 ELSE 0 END) as devueltas"),
            ]);
    }
}
