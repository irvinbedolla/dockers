<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * /estadisticas/solicitudes: solicitudes por rama industrial (la que se
 * captura en la solicitud, catalogo_rama) y, de la rama que se elija, cuántos
 * trabajadores reclaman sus semanas: las trabajadas que no se les pagaron o
 * su alta / semanas ante el IMSS.
 *
 * Qué cuenta cada cifra (igual en la pantalla, el PDF y el Excel):
 *
 *   solicitudes   seer_general por su fecha, sin las marcadas como incidencia;
 *   de la rama    las registradas con esa rama. En las ramas que tienen
 *                 PALABRAS (por ahora Construcción) hay además una
 *                 estimación ampliada: las de otra rama cuya actividad o
 *                 puesto del trabajador dice, p. ej., albañil u obra (mucho
 *                 se captura como "Otros servicios");
 *   trabajadores  los solicitantes de las solicitudes de trabajador (tipo 1)
 *                 de la rama, por sexo;
 *   reclamos      no hay un campo de "qué se reclama": se busca en la
 *                 descripción de la solicitud y sus observaciones (RECLAMOS).
 *                 Es un piso: lo que no se escribió no se cuenta.
 *
 * Se agrupa en PHP (no con funciones de fecha de MySQL) para que día, semana,
 * mes y año salgan iguales en cualquier base.
 */
class EstadisticasSolicitudes
{
    public const AGRUPAR = [
        'dia'    => 'Por día',
        'semana' => 'Por semana',
        'mes'    => 'Por mes',
        'anio'   => 'Por año',
    ];

    /** Más periodos que esto no caben en una tabla legible: se agrupa más grueso. */
    private const MAXIMO = ['dia' => 62, 'semana' => 60, 'mes' => 60];

    public const FORMATOS = ['xlsx', 'pdf'];

    /** Rama que se abre por omisión (la que se pidió primero). */
    public const RAMA_INICIAL = 'Construcción';

    /**
     * Palabras que delatan la rama aunque se haya capturado otra
     * (estimación ampliada), sobre el texto sin acentos y en minúsculas.
     */
    public const PALABRAS = [
        'Construcción' => [
            'actividad' => '/constru|albanil|\bobra\b|edificaci/u',
            'puesto'    => '/albanil|\bpeon\b|\bobra\b|construc|fierrero|yesero/u',
            'texto'     => 'su actividad dice construcción, albañil u obra, o el puesto del trabajador dice albañil, peón, obra, fierrero o yesero',
        ],
    ];

    /** Qué se busca en la descripción y las observaciones (sin acentos, minúsculas). */
    public const RECLAMOS = [
        'semanas' => [
            'nombre' => 'Semanas trabajadas sin pagar',
            'patron' => '/(deben|adeud|quedaron a deber|no (le|les|me) (pago|pagaron|han pagado)|sin pag|pendientes? de pago|falta(n|ron)? de pag).{0,40}semana|semanas? (laborad|trabajad)/u',
            'texto'  => 'dice que le deben, no le pagaron o quedaron pendientes semanas trabajadas',
        ],
        'imss' => [
            'nombre' => 'Alta o semanas ante el IMSS',
            'patron' => '/imss|seguro social|cotiza|semanas cotizadas|dad[oa]s? de alta|dar(le|lo|la|nos)? de alta|inscrib|inscripci|no (estaba|estaban|esta) asegurad|sin seguro/u',
            'texto'  => 'menciona el IMSS, el seguro social, que no lo dieron de alta o sus semanas cotizadas',
        ],
    ];

    /** Colores de las gráficas. */
    private const COLORES = ['#496163', '#CEA845', '#829A9C', '#1F4E79', '#9A3A22', '#5B3A82', '#1E7B3C', '#B9C7C8'];

    /** Ramas del catálogo: [id => nombre]. */
    public static function ramas(): array
    {
        static $ramas = null;

        return $ramas ??= DB::table('catalogo_rama')->orderBy('rama_industrial')->pluck('rama_industrial', 'id')
            ->map(fn ($n) => trim((string) $n))->all();
    }

    /** Lee y acomoda los filtros (por omisión: este año por mes, rama Construcción). */
    public static function filtros(Request $request): array
    {
        $fecha = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;

        $desde = $fecha($request->query('desde'));
        $hasta = $fecha($request->query('hasta'));
        $desde ??= $hasta ?? now()->startOfYear()->toDateString();
        $hasta ??= max($desde, now()->toDateString());
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $ramas = self::ramas();
        $rama = (int) $request->query('rama', 0);
        if (! isset($ramas[$rama])) {
            $rama = (int) (array_search(self::RAMA_INICIAL, $ramas, true) ?: array_key_first($ramas));
        }

        $sede = (string) $request->query('sede', '');
        $agrupar = (string) $request->query('agrupar', 'mes');

        return [
            'desde'   => $desde,
            'hasta'   => $hasta,
            'agrupar' => array_key_exists($agrupar, self::AGRUPAR) ? $agrupar : 'mes',
            'sede'    => in_array($sede, Recepcion::TODAS_LAS_SEDES, true) ? $sede : '',
            'rama'    => $rama,
        ];
    }

    /** Años con solicitudes (para los atajos "por año"). */
    public static function anios(): array
    {
        $primera = DB::table('seer_general')->min('fecha');
        $desde = $primera ? (int) Carbon::parse($primera)->year : (int) now()->year;

        return range((int) now()->year, $desde);
    }

    /**
     * Todo lo que necesitan la pantalla, el PDF y el Excel.
     *
     * @param array{desde: string, hasta: string, agrupar: string, sede: string, rama: int} $f
     */
    public static function armar(array $f, ?string $por = null, bool $detalle = false): array
    {
        $desde = Carbon::parse($f['desde'])->startOfDay();
        $hasta = Carbon::parse($f['hasta'])->startOfDay();
        $ramas = self::ramas();
        $nombreRama = $ramas[$f['rama']] ?? 'Sin rama';
        $palabras = self::PALABRAS[$nombreRama] ?? null;

        [$agrupar, $ajustado] = self::agrupacion($f['agrupar'], $desde, $hasta);
        $periodos = self::periodos($agrupar, $desde, $hasta);

        // Solicitudes del periodo.
        $sedes = $f['sede'] !== '' ? array_values(array_unique([$f['sede'], Str::ascii($f['sede'])])) : null;
        $generales = DB::table('seer_general')
            ->whereBetween('fecha', [$f['desde'], $f['hasta'].' 23:59:59'])
            ->where(fn ($w) => $w->whereNull('incidencia')->orWhere('incidencia', 0))
            ->when($sedes, fn ($q) => $q->whereIn('delegacion', $sedes))
            ->orderBy('id')
            ->get(['id', 'NUE', 'fecha', 'delegacion', 'id_rama', 'tipo_solicitud', 'actividad', 'observaciones']);

        // Sus solicitantes (en un trabajador, la descripción de lo que pasó).
        $solicitantes = [];
        foreach ($generales->pluck('id')->chunk(1000) as $ids) {
            foreach (DB::table('seer_solicitante')->whereIn('id_solicitud', $ids->all())
                ->get(['id_solicitud', 'sexo', 'puesto', 'descripcionSolicitud']) as $s) {
                $solicitantes[$s->id_solicitud][] = $s;
            }
        }

        $limpio = fn ($t) => Str::lower(Str::ascii((string) $t));
        $vacio = fn () => ['total' => 0, 'rama' => 0, 'ampliada' => 0, 'semanas' => 0, 'imss' => 0, 'alguno' => 0];

        $tot = $vacio();
        $porRama = [];
        $porSede = [];
        $porPeriodo = [];
        $trab = ['H' => 0, 'M' => 0, 'otro' => 0];
        $trabReclamo = [];
        foreach (array_keys(self::RECLAMOS) as $k) {
            $trabReclamo[$k] = ['H' => 0, 'M' => 0, 'otro' => 0];
        }
        $trabReclamo['alguno'] = ['H' => 0, 'M' => 0, 'otro' => 0];
        $combinados = ['ambos' => 0, 'semanas' => 0, 'imss' => 0, 'ninguno' => 0];
        $lista = [];

        foreach ($generales as $g) {
            $suyos = $solicitantes[$g->id] ?? [];
            $texto = $limpio(implode(' ', array_merge(array_map(fn ($s) => $s->descripcionSolicitud, $suyos), [$g->observaciones])));
            $reclama = [];
            foreach (self::RECLAMOS as $k => $r) {
                $reclama[$k] = (bool) preg_match($r['patron'], $texto);
            }
            $alguno = in_array(true, $reclama, true);

            $idRama = (int) $g->id_rama;
            $esRama = $idRama === $f['rama'];
            $esAmpliada = $esRama;
            if (! $esRama && $palabras) {
                $esAmpliada = (bool) preg_match($palabras['actividad'], $limpio($g->actividad))
                    || collect($suyos)->contains(fn ($s) => (bool) preg_match($palabras['puesto'], $limpio($s->puesto)));
            }
            $sede = self::sede((string) $g->delegacion);
            $clave = self::clavePeriodo($agrupar, Carbon::parse($g->fecha));

            // Todas las ramas (sólo la registrada).
            $porRama[$idRama] ??= $vacio();
            $porRama[$idRama]['total']++;
            foreach (array_keys(self::RECLAMOS) as $k) {
                $porRama[$idRama][$k] += $reclama[$k] ? 1 : 0;
            }
            $porRama[$idRama]['alguno'] += $alguno ? 1 : 0;

            foreach ([&$tot, &$porSede[$sede], &$porPeriodo[$clave]] as &$acum) {
                $acum ??= $vacio();
                $acum['total']++;
                $acum['rama'] += $esRama ? 1 : 0;
                $acum['ampliada'] += $esAmpliada ? 1 : 0;
                if ($esAmpliada) {
                    foreach (array_keys(self::RECLAMOS) as $k) {
                        $acum[$k] += $reclama[$k] ? 1 : 0;
                    }
                    $acum['alguno'] += $alguno ? 1 : 0;
                }
            }
            unset($acum);

            if (! $esAmpliada) {
                continue;
            }
            $combinados[$reclama['semanas'] && $reclama['imss'] ? 'ambos' : ($reclama['semanas'] ? 'semanas' : ($reclama['imss'] ? 'imss' : 'ninguno'))]++;

            // Trabajadores: solicitantes de las solicitudes de trabajador.
            if ((int) $g->tipo_solicitud === 1) {
                foreach ($suyos ?: [(object) ['sexo' => null]] as $s) {
                    $sx = in_array($s->sexo, ['H', 'M'], true) ? $s->sexo : 'otro';
                    $trab[$sx]++;
                    foreach (array_keys(self::RECLAMOS) as $k) {
                        $trabReclamo[$k][$sx] += $reclama[$k] ? 1 : 0;
                    }
                    $trabReclamo['alguno'][$sx] += $alguno ? 1 : 0;
                }
            }

            if ($detalle) {
                $lista[] = [
                    'nue'       => (string) $g->NUE,
                    'fecha'     => Carbon::parse($g->fecha)->format('d/m/Y'),
                    'sede'      => $sede,
                    'rama'      => $ramas[$idRama] ?? 'Sin rama',
                    'como'      => $esRama ? 'Registrada en la rama' : 'Por actividad o puesto',
                    'actividad' => trim((string) $g->actividad),
                    'puesto'    => trim(implode(', ', array_filter(array_map(fn ($s) => trim((string) $s->puesto), $suyos)))),
                    'sexo'      => implode(', ', array_filter(array_map(fn ($s) => ['H' => 'Hombre', 'M' => 'Mujer'][$s->sexo] ?? null, $suyos))),
                    'semanas'   => $reclama['semanas'],
                    'imss'      => $reclama['imss'],
                ];
            }
        }

        $pct = fn ($n, $d) => $d > 0 ? round($n * 100 / $d, 1) : null;
        $universo = $palabras ? 'ampliada' : 'rama';

        // Tabla de todas las ramas (orden: más solicitudes primero).
        $tablaRamas = [];
        foreach ($porRama as $id => $r) {
            $tablaRamas[] = [
                'id'       => $id,
                'nombre'   => $ramas[$id] ?? 'Sin rama',
                'total'    => $r['total'],
                'pct'      => $pct($r['total'], $tot['total']),
                'semanas'  => $r['semanas'],
                'pctSemanas' => $pct($r['semanas'], $r['total']),
                'imss'     => $r['imss'],
                'pctImss'  => $pct($r['imss'], $r['total']),
                'alguno'   => $r['alguno'],
                'pctAlguno' => $pct($r['alguno'], $r['total']),
                'elegida'  => $id === $f['rama'],
            ];
        }
        usort($tablaRamas, fn ($a, $b) => [$b['total'], $a['nombre']] <=> [$a['total'], $b['nombre']]);

        $fila = fn (string $etiqueta, array $a) => [
            'etiqueta' => $etiqueta,
            'total'    => $a['total'],
            'rama'     => $a['rama'],
            'pctRama'  => $pct($a['rama'], $a['total']),
            'ampliada' => $a['ampliada'],
            'pctAmpliada' => $pct($a['ampliada'], $a['total']),
            'semanas'  => $a['semanas'],
            'pctSemanas' => $pct($a['semanas'], $a[$universo]),
            'imss'     => $a['imss'],
            'pctImss'  => $pct($a['imss'], $a[$universo]),
            'alguno'   => $a['alguno'],
            'pctAlguno' => $pct($a['alguno'], $a[$universo]),
        ];

        $tablaSedes = [];
        $nombresSede = $f['sede'] !== '' ? [$f['sede']] : Recepcion::TODAS_LAS_SEDES;
        foreach (array_merge($nombresSede, array_values(array_diff(array_keys($porSede), $nombresSede))) as $s) {
            $tablaSedes[] = $fila($s, $porSede[$s] ?? $vacio());
        }
        $serie = [];
        foreach ($periodos as $clave => $p) {
            $serie[] = $p + $fila($p['etiqueta'], $porPeriodo[$clave] ?? $vacio());
        }

        $nTrab = array_sum($trab);
        $resumen = $fila('Total', $tot) + [
            'trabajadores' => $nTrab,
            'hombres'      => $trab['H'],
            'mujeres'      => $trab['M'],
        ];
        $trabajadores = [];
        foreach (array_merge(self::RECLAMOS, ['alguno' => ['nombre' => 'Cualquiera de los dos']]) as $k => $r) {
            $n = array_sum($trabReclamo[$k]);
            $trabajadores[] = [
                'clave'   => $k,
                'nombre'  => $r['nombre'],
                'total'   => $n,
                'pct'     => $pct($n, $nTrab),
                'hombres' => $trabReclamo[$k]['H'],
                'pctH'    => $pct($trabReclamo[$k]['H'], $trab['H']),
                'mujeres' => $trabReclamo[$k]['M'],
                'pctM'    => $pct($trabReclamo[$k]['M'], $trab['M']),
            ];
        }

        $periodoTexto = self::textoPeriodo($desde, $hasta);
        $filtrosTexto = 'Rama: '.$nombreRama.' · '.($f['sede'] !== '' ? 'Sede '.$f['sede'] : 'Todas las sedes');

        return [
            'filtros'      => $f,
            'titulo'       => 'Estadística de Solicitudes',
            'periodo'      => $periodoTexto,
            'filtrosTexto' => $filtrosTexto,
            'agrupar'      => $agrupar,
            'agruparTexto' => self::AGRUPAR[$agrupar],
            'ajustado'     => $ajustado,
            'unidad'       => ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'anio' => 'Año'][$agrupar],
            'rama'         => ['id' => $f['rama'], 'nombre' => $nombreRama, 'palabras' => $palabras['texto'] ?? null],
            'universo'     => $universo,
            'resumen'      => $resumen,
            'trabajadores' => $trabajadores,
            'combinados'   => $combinados,
            'serie'        => $serie,
            'porSede'      => $tablaSedes,
            'ramas'        => $tablaRamas,
            'lista'        => $lista,
            'graficas'     => self::graficas($tablaRamas, $combinados, $serie, $tablaSedes, $nombreRama, $universo, $tot['total']),
            'generado'     => now(),
            'por'          => $por,
            'archivo'      => 'Estadistica de solicitudes '.Str::slug($nombreRama).' '.$f['desde'].' a '.$f['hasta'],
            'glosario'     => self::glosario($palabras),
        ];
    }

    /** Gráficas en SVG (las mismas en la pantalla y en el PDF). */
    private static function graficas(array $ramas, array $combinados, array $serie, array $sedes, string $nombreRama, string $universo, int $total): array
    {
        // Pastel de ramas: las 7 con más solicitudes y "Otras".
        $partes = [];
        $otras = 0;
        foreach ($ramas as $i => $r) {
            if ($i < 7) {
                $partes[] = ['nombre' => Str::limit($r['nombre'], 34), 'valor' => $r['total'], 'color' => self::COLORES[$i], 'destacar' => $r['elegida']];
            } else {
                $otras += $r['total'];
            }
        }
        if ($otras) {
            $partes[] = ['nombre' => 'Otras ramas', 'valor' => $otras, 'color' => '#D5DEDE'];
        }

        $reclamos = [
            ['nombre' => 'Semanas sin pagar', 'valor' => $combinados['semanas'], 'color' => '#CEA845'],
            ['nombre' => 'IMSS (alta o semanas)', 'valor' => $combinados['imss'], 'color' => '#1F4E79'],
            ['nombre' => 'Las dos', 'valor' => $combinados['ambos'], 'color' => '#9A3A22'],
            ['nombre' => 'No lo mencionan', 'valor' => $combinados['ninguno'], 'color' => '#D5DEDE'],
        ];

        $etiquetas = array_map(fn ($p) => $p['corta'], $serie);
        $sedesCon = array_values(array_filter($sedes, fn ($s) => $s['total'] > 0));

        return [
            'ramas'    => GraficaSvg::pastel($partes, ['centro' => number_format($total), 'centro_sub' => 'solicitudes']),
            'reclamos' => GraficaSvg::pastel($reclamos, ['centro' => number_format(array_sum($combinados)), 'centro_sub' => 'de la rama']),
            'periodo'  => GraficaSvg::barras($etiquetas, [
                ['nombre' => $nombreRama, 'color' => '#496163', 'valores' => array_map(fn ($p) => $p[$universo], $serie)],
            ], ['ancho' => 900, 'alto' => 300, 'rotar' => count($etiquetas) > 14, 'valor' => count($etiquetas) > 24 ? null : fn ($v) => number_format($v), 'etiquetas_chicas' => true]),
            'sedes'    => GraficaSvg::barras(array_map(fn ($s) => $s['etiqueta'], $sedesCon), [
                ['nombre' => '% de '.$nombreRama, 'color' => '#CEA845', 'valores' => array_map(fn ($s) => $s[$universo === 'ampliada' ? 'pctAmpliada' : 'pctRama'] ?? 0, $sedesCon)],
            ], ['ancho' => 640, 'alto' => 300, 'eje' => fn ($v) => rtrim(rtrim(number_format($v, 1), '0'), '.').'%', 'valor' => fn ($v) => rtrim(rtrim(number_format($v, 1), '0'), '.').'%', 'etiquetas_chicas' => true]),
        ];
    }

    private static function glosario(?array $palabras): array
    {
        $g = [
            'Solicitudes'        => 'Solicitudes de conciliación registradas en esas fechas (por la fecha de la solicitud), sin las marcadas como incidencia.',
            'Registradas en la rama' => 'Las que se capturaron con esa rama industrial en la solicitud.',
        ];
        if ($palabras) {
            $g['Estimación ampliada'] = 'Las registradas en la rama más las de otra rama donde '.$palabras['texto'].'. Mucho se captura como «Otros servicios», por eso la rama sola se queda corta.';
        }
        $g['Trabajadores'] = 'Las personas trabajadoras que presentaron la solicitud (solicitudes de trabajador), por sexo.';
        foreach (self::RECLAMOS as $r) {
            $g[$r['nombre']] = 'La descripción de la solicitud o sus observaciones '.$r['texto'].'.';
        }
        $g['Importante'] = 'No hay un campo de «qué se reclama»: se busca en el texto que se escribió. Es un piso: si no quedó escrito, no se cuenta.';

        return $g;
    }

    // ------------------------------------------------------------------

    private static function sede(string $delegacion): string
    {
        foreach (Recepcion::TODAS_LAS_SEDES as $s) {
            if (Str::lower(Str::ascii($s)) === Str::lower(Str::ascii(trim($delegacion)))) {
                return $s;
            }
        }

        return trim($delegacion) !== '' ? trim($delegacion) : 'Sin sede';
    }

    /** Si hay demasiados periodos, se agrupa más grueso. */
    private static function agrupacion(string $agrupar, Carbon $desde, Carbon $hasta): array
    {
        $original = $agrupar;
        foreach (['dia' => 'semana', 'semana' => 'mes', 'mes' => 'anio'] as $de => $a) {
            if ($agrupar === $de && count(self::periodos($de, $desde, $hasta)) > self::MAXIMO[$de]) {
                $agrupar = $a;
            }
        }

        return [$agrupar, $agrupar !== $original];
    }

    private static function clavePeriodo(string $agrupar, Carbon $f): string
    {
        return match ($agrupar) {
            'dia'    => $f->toDateString(),
            'semana' => $f->copy()->startOfWeek()->toDateString(),
            'mes'    => $f->format('Y-m'),
            default  => $f->format('Y'),
        };
    }

    /** Todos los periodos del rango, aunque estén vacíos: [clave => etiqueta, corta, desde, hasta]. */
    private static function periodos(string $agrupar, Carbon $desde, Carbon $hasta): array
    {
        $out = [];
        $c = match ($agrupar) {
            'semana' => $desde->copy()->startOfWeek(),
            'mes'    => $desde->copy()->startOfMonth(),
            'anio'   => $desde->copy()->startOfYear(),
            default  => $desde->copy(),
        };
        while ($c->lte($hasta)) {
            [$fin, $etiqueta, $corta] = match ($agrupar) {
                'dia'    => [$c->copy(), Str::ucfirst($c->locale('es')->isoFormat('ddd D [de] MMM YYYY')), $c->format('d/m')],
                'semana' => [$c->copy()->endOfWeek(), 'Semana del '.$c->format('d/m/Y'), $c->format('d/m')],
                'mes'    => [$c->copy()->endOfMonth(), Str::ucfirst($c->locale('es')->isoFormat('MMMM YYYY')), Str::ucfirst($c->locale('es')->isoFormat('MMM YY'))],
                default  => [$c->copy()->endOfYear(), $c->format('Y'), $c->format('Y')],
            };
            $out[self::clavePeriodo($agrupar, $c)] = [
                'etiqueta' => $etiqueta,
                'corta'    => $corta,
                'desde'    => max($c->toDateString(), $desde->toDateString()),
                'hasta'    => min($fin->toDateString(), $hasta->toDateString()),
            ];
            $c = match ($agrupar) {
                'dia'    => $c->addDay(),
                'semana' => $c->addWeek(),
                'mes'    => $c->addMonthNoOverflow(),
                default  => $c->addYear(),
            };
        }

        return $out;
    }

    private static function textoPeriodo(Carbon $desde, Carbon $hasta): string
    {
        $f = fn (Carbon $d) => $d->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        return $desde->equalTo($hasta) ? $f($desde) : 'Del '.$f($desde).' al '.$f($hasta);
    }
}
