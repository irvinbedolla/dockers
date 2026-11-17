<?php

namespace App\Support;

/**
 * Gráficas sencillas en SVG para el Reporte estadístico. Se dibujan en el
 * servidor para que la página, la imagen y el PDF (dompdf no corre
 * JavaScript) salgan iguales.
 *
 *   barras($etiquetas, $series, $op)   barras (agrupadas si hay varias series)
 *   lineas($etiquetas, $series, $op)   líneas con puntos
 *
 * $series: [['nombre' => 'JLCA', 'color' => '#829A9C', 'valores' => [..]], ...]
 * Un valor null no se dibuja (p. ej. años sin dato).
 *
 * Opciones: ancho, alto, eje (fn número → texto del eje), valor (fn → texto
 * sobre la barra/punto, o null para no ponerlo), rotar (etiquetas en
 * diagonal), destacar (etiqueta subrayada, p. ej. Michoacán), leyenda,
 * colores_barra (un color por barra en una serie), max (tope del eje).
 */
class GraficaSvg
{
    /**
     * En el PDF el SVG sólo admite las fuentes base (sans-serif = Helvetica);
     * en la página, enPagina() la cambia por la de las láminas.
     */
    public const FUENTE = 'sans-serif';

    public static function barras(array $etiquetas, array $series, array $op = []): string
    {
        return self::dibujar('barras', $etiquetas, $series, $op);
    }

    public static function lineas(array $etiquetas, array $series, array $op = []): string
    {
        return self::dibujar('lineas', $etiquetas, $series, $op);
    }

    /** SVG en línea para la página: con la fuente de las láminas. */
    public static function enPagina(string $svg): string
    {
        return str_replace('font-family="'.self::FUENTE.'"', 'font-family="ReMontserrat, Arial, sans-serif"', $svg);
    }

    /**
     * Para <img src="..."> en el PDF. El SVG de dompdf sólo encuentra la
     * fuente si se le da la ruta del archivo (sin extensión, con su .ufm al
     * lado): así las gráficas salen en Montserrat como el resto.
     */
    public static function uri(string $svg): string
    {
        $ruta = fn ($peso) => public_path('assets/reporte_estadistico/fonts/Montserrat-'.$peso);
        $svg = preg_replace_callback('/<text [^>]*>/', function ($m) use ($ruta) {
            $fuente = str_contains($m[0], 'font-weight="bold"') ? $ruta('Bold') : $ruta('Regular');
            if (! is_file($fuente.'.ufm')) {
                return $m[0];
            }

            return str_replace('font-family="'.self::FUENTE.'"', 'font-family="'.e($fuente).'"', $m[0]);
        }, $svg);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private static function dibujar(string $tipo, array $etiquetas, array $series, array $op): string
    {
        $W = $op['ancho'] ?? 640;
        $H = $op['alto'] ?? 360;
        $eje = $op['eje'] ?? fn ($v) => number_format($v);
        $fmtValor = array_key_exists('valor', $op) ? $op['valor'] : fn ($v) => number_format($v);
        $rotar = $op['rotar'] ?? false;
        $leyenda = $op['leyenda'] ?? count($series) > 1;
        $n = max(count($etiquetas), 1);

        $todos = [];
        foreach ($series as $s) {
            foreach ($s['valores'] as $v) {
                if ($v !== null) {
                    $todos[] = (float) $v;
                }
            }
        }
        [$tope, $paso] = self::escala($op['max'] ?? (empty($todos) ? 1 : max($todos)));

        // Márgenes: a la izquierda el eje; abajo las etiquetas.
        $anchoEje = max(array_map(fn ($v) => mb_strlen($eje($v)), range(0, $tope, $paso))) * 6.6 + 14;
        $izq = max(36, $anchoEje);
        $der = 14;
        $arriba = $leyenda ? 46 : 22;
        $abajo = $rotar ? 78 : 30;
        $x0 = $izq;
        $y0 = $H - $abajo;
        $ancho = $W - $izq - $der;
        $alto = $y0 - $arriba;
        $y = fn ($v) => $y0 - ($tope > 0 ? $v / $tope : 0) * $alto;
        $banda = $ancho / $n;

        $f = self::FUENTE;
        $o = [];
        $o[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">', $W, $H, $W, $H);

        // Líneas guía y eje.
        for ($v = 0; $v <= $tope + 1e-9; $v += $paso) {
            $yy = round($y($v), 1);
            $o[] = sprintf('<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="1"/>', $x0, $yy, $W - $der, $yy, $v == 0 ? '#BFBFBF' : '#E6E6E6');
            $o[] = sprintf('<text x="%s" y="%s" font-size="11" font-weight="normal" text-anchor="end" font-family="%s" fill="#555">%s</text>', $x0 - 8, $yy + 4, $f, e($eje($v)));
        }

        // Leyenda centrada arriba.
        if ($leyenda) {
            $total = 0;
            foreach ($series as $s) {
                $total += 26 + mb_strlen($s['nombre']) * 7.4;
            }
            $lx = ($W - $total) / 2;
            foreach ($series as $s) {
                $o[] = sprintf('<circle cx="%s" cy="18" r="7" fill="%s"/>', round($lx + 7, 1), $s['color']);
                $o[] = sprintf('<text x="%s" y="23" font-size="13" font-weight="normal" font-family="%s" fill="#3A3F5C">%s</text>', round($lx + 19, 1), $f, e($s['nombre']));
                $lx += 26 + mb_strlen($s['nombre']) * 7.4;
            }
        }

        if ($tipo === 'barras') {
            $ns = max(count($series), 1);
            $grueso = min($op['grueso_max'] ?? 52, $banda * 0.78 / $ns);
            foreach ($series as $si => $s) {
                foreach ($s['valores'] as $i => $v) {
                    if ($v === null) {
                        continue;
                    }
                    $bx = $x0 + $banda * $i + ($banda - $grueso * $ns) / 2 + $grueso * $si;
                    $by = $y((float) $v);
                    $color = $op['colores_barra'][$i] ?? $s['color'];
                    $o[] = sprintf('<rect x="%s" y="%s" width="%s" height="%s" fill="%s"/>', round($bx, 1), round($by, 1), round($grueso - 2, 1), round(max($y0 - $by, 0), 1), $color);
                    if ($fmtValor) {
                        $o[] = sprintf('<text x="%s" y="%s" font-size="%s" font-weight="bold" text-anchor="middle" font-family="%s" fill="#555">%s</text>', round($bx + $grueso / 2 - 1, 1), round($by - 6, 1), $op['tam_valor'] ?? 11, $f, e($fmtValor((float) $v, $i)));
                    }
                }
            }
        } else {
            // Dónde coinciden dos series, el número de la de abajo va debajo del punto.
            $maximos = [];
            foreach ($series as $s) {
                foreach ($s['valores'] as $i => $v) {
                    if ($v !== null) {
                        $maximos[$i] = max($maximos[$i] ?? $v, $v);
                    }
                }
            }
            foreach ($series as $s) {
                $puntos = [];
                foreach ($s['valores'] as $i => $v) {
                    if ($v !== null) {
                        $puntos[$i] = [round($x0 + $banda * ($i + 0.5), 1), round($y((float) $v), 1), (float) $v];
                    }
                }
                if (count($puntos) > 1) {
                    $o[] = sprintf('<polyline points="%s" fill="none" stroke="%s" stroke-width="%s"/>', implode(' ', array_map(fn ($p) => $p[0].','.$p[1], $puntos)), $s['color'], $op['grosor'] ?? 3);
                }
                foreach ($puntos as $i => [$px, $py, $v]) {
                    $o[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s"/>', $px, $py, $op['punto'] ?? 6, $s['color']);
                    if ($fmtValor) {
                        $abajo = $v < $maximos[$i];
                        $o[] = sprintf('<text x="%s" y="%s" font-size="11" font-weight="normal" text-anchor="middle" font-family="%s" fill="#222">%s</text>', $px, round($abajo ? $py + 20 : $py - 12, 1), $f, e($fmtValor($v, $i)));
                    }
                }
            }
        }

        // Etiquetas del eje horizontal.
        foreach ($etiquetas as $i => $et) {
            $cx = round($x0 + $banda * ($i + 0.5), 1);
            $peso = ($op['destacar'] ?? null) === $et ? 'bold' : 'normal';
            if ($rotar) {
                $ty = $y0 + 14;
                $o[] = sprintf('<text x="%s" y="%s" font-size="12" font-weight="%s" text-anchor="end" transform="rotate(-45 %s %s)" font-family="%s" fill="#333">%s</text>', $cx + 4, $ty, $peso, $cx + 4, $ty, $f, e($et));
            } else {
                $o[] = sprintf('<text x="%s" y="%s" font-size="%s" font-weight="%s" text-anchor="middle" font-family="%s" fill="#555">%s</text>', $cx, $y0 + 20, ! empty($op['etiquetas_chicas']) ? 10.5 : 12, $peso, $f, e($et));
            }
        }

        $o[] = '</svg>';

        return implode('', $o);
    }

    /** Tope "redondo" del eje y su paso (unas 5 a 8 líneas). */
    private static function escala(float $max): array
    {
        if ($max <= 0) {
            return [1, 1];
        }
        $crudo = $max / 6;
        $mag = 10 ** floor(log10($crudo));
        foreach ([1, 2, 2.5, 5, 10] as $m) {
            if ($m * $mag >= $crudo) {
                $paso = $m * $mag;
                break;
            }
        }
        $tope = ceil($max * 1.08 / $paso) * $paso;

        return [$tope, $paso];
    }
}
