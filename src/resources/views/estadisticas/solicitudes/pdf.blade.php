<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} · {{ $periodo }}</title>
    {{-- PDF de /estadisticas/solicitudes (dompdf): tablas y las gráficas en SVG de GraficaSvg. Datos: App\Support\EstadisticasSolicitudes::armar. --}}
    @php
        use App\Support\GraficaSvg;
        $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
        $r = $resumen;
        $amp = $universo === 'ampliada';
        $sum = fn ($filas, $k) => collect($filas)->sum($k);
    @endphp
    <style>
        @page { margin: 26px 28px 34px; }
        body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #2E3C3D; font-size: 9px; }
        .cab { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .cab td { vertical-align: bottom; padding: 0; }
        .h1 { font-size: 17px; font-weight: bold; color: #354647; }
        .h2 { font-size: 11px; font-weight: bold; color: #496163; margin-top: 2px; }
        .fil { font-size: 9px; color: #6B7A7B; margin-top: 2px; }
        .gen { font-size: 8px; color: #8A9899; text-align: right; }
        .barra { height: 4px; background: #496163; margin: 4px 0 10px; }
        .barra span { display: block; width: 90px; height: 4px; background: #CEA845; }
        .sec { font-size: 11px; font-weight: bold; color: #fff; background: #354647; padding: 4px 8px; margin: 12px 0 4px; }
        .resp { font-size: 10.5px; margin: 4px 0 6px; }
        .nota { font-size: 8px; color: #7A5D10; background: #FBF4E1; padding: 4px 6px; margin-top: 4px; }
        .cifras { width: 100%; border-collapse: separate; border-spacing: 4px 0; margin: 2px -4px 0; }
        .cifras td { border: 1px solid #DDE4E4; background: #F7F9F9; padding: 5px 6px; vertical-align: top; }
        .cifras b { display: block; font-size: 15px; }
        .cifras span { font-size: 8px; font-weight: bold; color: #5E6E6F; }
        .cifras small { display: block; font-size: 7px; color: #8A9899; }
        .t { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .t th { background: #496163; color: #fff; font-size: 7.5px; font-weight: bold; padding: 4px 3px; text-align: center; border: 1px solid #3A4E50; }
        .t th.i, .t td.i { text-align: left; }
        .t td { padding: 3px 3px; border: 1px solid #DDE4E4; text-align: center; }
        .t td small { color: #6B7A7B; font-size: 7px; }
        .t tr.tot td { font-weight: bold; background: #EEF2F2; }
        .t tr.sel td { background: #FFFBF0; }
        .graf { width: 100%; border-collapse: collapse; }
        .graf td { vertical-align: top; padding: 0 4px; width: 50%; }
        .graf img { width: 100%; }
        .glo td { border: 0; padding: 2px 4px; vertical-align: top; font-size: 8px; color: #5E6E6F; }
        .glo td.i { font-weight: bold; color: #2E3C3D; white-space: nowrap; }
        .salto { page-break-before: always; }
    </style>
</head>
<body>
    <table class="cab">
        <tr>
            <td>
                <div class="h1">{{ $titulo }}</div>
                <div class="h2">Centro de Conciliación Laboral del Estado de Michoacán · {{ $periodo }}</div>
                <div class="fil">{{ $filtrosTexto }}</div>
            </td>
            <td class="gen">Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.\Illuminate\Support\Str::title(mb_strtolower($por)) : '' }}</td>
        </tr>
    </table>
    <div class="barra"><span></span></div>

    <table class="cifras">
        <tr>
            <td><b>{{ number_format($r['total']) }}</b><span>Solicitudes</span><small>en el periodo</small></td>
            <td><b>{{ number_format($r['rama']) }}</b><span>Registradas en {{ $rama['nombre'] }}</span><small>{{ $pct($r['pctRama']) }} de las solicitudes</small></td>
            @if ($amp)<td><b>{{ number_format($r['ampliada']) }}</b><span>Estimación ampliada</span><small>{{ $pct($r['pctAmpliada']) }} · por actividad o puesto</small></td>@endif
            <td><b>{{ number_format($r['trabajadores']) }}</b><span>Trabajadores de la rama</span><small>{{ number_format($r['hombres']) }} hombres · {{ number_format($r['mujeres']) }} mujeres</small></td>
            <td><b>{{ $pct($trabajadores[2]['pct']) }}</b><span>Reclaman sus semanas</span><small>{{ number_format($trabajadores[2]['total']) }} trabajadores</small></td>
        </tr>
    </table>

    <div class="sec">1. ¿Qué parte de las solicitudes es de {{ $rama['nombre'] }}?</div>
    <p class="resp">
        @if ($amp && $r['pctAmpliada'] !== $r['pctRama'])
            Entre <b>{{ $pct($r['pctRama']) }}</b> y <b>{{ $pct($r['pctAmpliada']) }}</b> de las solicitudes ({{ number_format($r['rama']) }} registradas en la rama y {{ number_format($r['ampliada']) }} con la estimación ampliada).
        @else
            <b>{{ $pct($r['pctRama']) }}</b> de las solicitudes ({{ number_format($r['rama']) }} de {{ number_format($r['total']) }}).
        @endif
    </p>
    <table class="graf">
        <tr>
            <td><img src="{{ GraficaSvg::uri($graficas['ramas']) }}" alt="Solicitudes por rama"></td>
            <td><img src="{{ GraficaSvg::uri($graficas['sedes']) }}" alt="Porcentaje por sede"></td>
        </tr>
    </table>

    <div class="sec">2. ¿Cuántos trabajadores de {{ $rama['nombre'] }} reclaman sus semanas?</div>
    <table class="graf">
        <tr>
            <td>
                <table class="t">
                    <tr><th class="i">Reclaman</th><th>Trabajadores</th><th>Hombres</th><th>Mujeres</th></tr>
                    @foreach ($trabajadores as $t)
                        <tr class="{{ $t['clave'] === 'alguno' ? 'tot' : '' }}">
                            <td class="i">{{ $t['nombre'] }}</td>
                            <td>{{ number_format($t['total']) }} <small>{{ $pct($t['pct']) }}</small></td>
                            <td>{{ number_format($t['hombres']) }} <small>{{ $pct($t['pctH']) }}</small></td>
                            <td>{{ number_format($t['mujeres']) }} <small>{{ $pct($t['pctM']) }}</small></td>
                        </tr>
                    @endforeach
                </table>
                <div class="nota">De {{ number_format($r['trabajadores']) }} trabajadores ({{ number_format($r['hombres']) }} hombres y {{ number_format($r['mujeres']) }} mujeres). Se busca en lo que se escribió en la solicitud: es un piso.</div>
            </td>
            <td><img src="{{ GraficaSvg::uri($graficas['reclamos']) }}" alt="Lo que reclaman"></td>
        </tr>
    </table>

    <div class="sec salto">{{ $rama['nombre'] }} · {{ mb_strtolower($agruparTexto) }}</div>
    <img src="{{ GraficaSvg::uri($graficas['periodo']) }}" style="width:100%" alt="Por periodo">
    @foreach ([[$serie, $unidad], [$porSede, 'Sede']] as [$filas, $primera])
        @if ($primera === 'Sede')<div class="sec">Por sede</div>@endif
        <table class="t">
            <tr>
                <th class="i">{{ $primera }}</th><th>Solicitudes</th><th>Registradas en la rama</th>
                @if ($amp)<th>Estimación ampliada</th>@endif
                <th>Semanas sin pagar</th><th>IMSS (alta o semanas)</th><th>Cualquiera</th>
            </tr>
            @foreach ($filas as $fila)
                <tr>
                    <td class="i">{{ $fila['etiqueta'] }}</td>
                    <td>{{ number_format($fila['total']) }}</td>
                    <td>{{ number_format($fila['rama']) }} <small>{{ $pct($fila['pctRama']) }}</small></td>
                    @if ($amp)<td>{{ number_format($fila['ampliada']) }} <small>{{ $pct($fila['pctAmpliada']) }}</small></td>@endif
                    <td>{{ number_format($fila['semanas']) }} <small>{{ $pct($fila['pctSemanas']) }}</small></td>
                    <td>{{ number_format($fila['imss']) }} <small>{{ $pct($fila['pctImss']) }}</small></td>
                    <td>{{ number_format($fila['alguno']) }} <small>{{ $pct($fila['pctAlguno']) }}</small></td>
                </tr>
            @endforeach
            <tr class="tot">
                <td class="i">Total</td><td>{{ number_format($sum($filas, 'total')) }}</td><td>{{ number_format($sum($filas, 'rama')) }}</td>
                @if ($amp)<td>{{ number_format($sum($filas, 'ampliada')) }}</td>@endif
                <td>{{ number_format($sum($filas, 'semanas')) }}</td><td>{{ number_format($sum($filas, 'imss')) }}</td><td>{{ number_format($sum($filas, 'alguno')) }}</td>
            </tr>
        </table>
    @endforeach

    <div class="sec">Todas las ramas (registradas en cada rama)</div>
    <table class="t">
        <tr><th class="i">Rama</th><th>Solicitudes</th><th>Semanas sin pagar</th><th>IMSS (alta o semanas)</th><th>Cualquiera</th></tr>
        @foreach ($ramas as $rr)
            <tr class="{{ $rr['elegida'] ? 'sel' : '' }}">
                <td class="i">{{ $rr['nombre'] }}</td>
                <td>{{ number_format($rr['total']) }} <small>{{ $pct($rr['pct']) }}</small></td>
                <td>{{ number_format($rr['semanas']) }} <small>{{ $pct($rr['pctSemanas']) }}</small></td>
                <td>{{ number_format($rr['imss']) }} <small>{{ $pct($rr['pctImss']) }}</small></td>
                <td>{{ number_format($rr['alguno']) }} <small>{{ $pct($rr['pctAlguno']) }}</small></td>
            </tr>
        @endforeach
        <tr class="tot"><td class="i">Total</td><td>{{ number_format($r['total']) }}</td><td>{{ number_format($sum($ramas, 'semanas')) }}</td><td>{{ number_format($sum($ramas, 'imss')) }}</td><td>{{ number_format($sum($ramas, 'alguno')) }}</td></tr>
    </table>

    <div class="sec">Qué cuenta cada cifra</div>
    <table class="glo">
        @foreach ($glosario as $termino => $texto)
            <tr><td class="i">{{ $termino }}</td><td>{{ $texto }}</td></tr>
        @endforeach
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text($pdf->get_width() - 110, $pdf->get_height() - 22, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 7, [0.45, 0.5, 0.5]);
        }
    </script>
</body>
</html>
