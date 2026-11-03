{{--
    Estadísticas de turnos y citas para PDF (dompdf) y para la imagen
    (html2canvas): sólo tablas y CSS sencillo para que se vean igual.
    Datos: App\Support\EstadisticasTurnos::armar.
--}}
@php
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $maxSerie = max(1, (int) collect($serie)->max('citas'));
@endphp
<style>
    .et { font-family: DejaVu Sans, Arial, sans-serif; color: #2E3C3D; font-size: 9px; }
    .et-cab { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .et-cab td { vertical-align: bottom; padding: 0; }
    .et-h1 { font-size: 17px; font-weight: bold; color: #354647; }
    .et-h2 { font-size: 11px; font-weight: bold; color: #496163; margin-top: 2px; }
    .et-fil { font-size: 9px; color: #6B7A7B; margin-top: 2px; }
    .et-gen { font-size: 8px; color: #8A9899; text-align: right; }
    .et-barra { height: 4px; background: #496163; margin: 4px 0 10px; }
    .et-barra span { display: block; width: 90px; height: 4px; background: #CEA845; }
    .et-sec { font-size: 11.5px; font-weight: bold; color: #fff; background: #354647; padding: 4px 8px; margin: 12px 0 0; }
    .et-sec--exc { background: #8C1D40; }
    .et-nota { font-size: 8px; color: #6B7A7B; margin: 3px 0 0; }
    .et-t { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .et-t th { background: #496163; color: #fff; font-size: 7.5px; font-weight: bold; padding: 4px 3px; text-align: center; border: 1px solid #3A4E50; }
    .et-t th.i, .et-t td.i { text-align: left; }
    .et-t td { padding: 3px 3px; border: 1px solid #DDE4E4; text-align: center; vertical-align: middle; }
    .et-t td small { color: #6B7A7B; font-size: 7px; }
    .et-t tr.tot td { font-weight: bold; background: #EEF2F2; }
    .et-cifras { width: 100%; border-collapse: separate; border-spacing: 4px 0; margin: 2px -4px 0; }
    .et-cifras td { border: 1px solid #DDE4E4; background: #F7F9F9; padding: 5px 6px; vertical-align: top; width: 12.5%; }
    .et-cifras b { display: block; font-size: 15px; color: #2E3C3D; }
    .et-cifras span { font-size: 8px; font-weight: bold; color: #5E6E6F; }
    .et-cifras small { display: block; font-size: 7px; color: #8A9899; }
    .et-gr { width: 100%; border-collapse: collapse; }
    .et-gr td { padding: 1px 0; border: 0; height: 9px; }
    .et-ley { font-size: 8px; color: #5E6E6F; margin-top: 4px; }
    .et-ley i { display: inline-block; width: 8px; height: 8px; margin: 0 3px 0 8px; vertical-align: -1px; }
    .et-salto { page-break-before: always; }
    .et-glo td { border: 0; padding: 2px 4px; vertical-align: top; font-size: 8px; color: #5E6E6F; }
    .et-glo td.i { font-weight: bold; color: #2E3C3D; white-space: nowrap; }
</style>

<div class="et">
    <table class="et-cab">
        <tr>
            <td>
                <div class="et-h1">{{ $titulo }}</div>
                <div class="et-h2">{{ $periodo }} · {{ mb_strtolower($agruparTexto) }}</div>
                <div class="et-fil">{{ $filtrosTexto }} · atención larga: {{ $umbral }} min o más</div>
            </td>
            <td class="et-gen">
                Centro de Conciliación Laboral del Estado de Michoacán<br>
                Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.$por : '' }}
            </td>
        </tr>
    </table>
    <div class="et-barra"><span></span></div>

    @foreach ([['Todas las citas', $general, ''], ['Casos de excepción (art. 685 Ter)', $excepcion, 'exc']] as [$nombre, $c, $clase])
        <div class="et-sec {{ $clase ? 'et-sec--exc' : '' }}">{{ $nombre }}</div>
        <table class="et-cifras">
            <tr>
                <td><b>{{ number_format($c['citas']) }}</b><span>Citas</span><small>{{ $c['linea'] }} en línea · {{ $c['ventanilla'] }} ventanilla</small></td>
                <td><b style="color:#1E7B3C">{{ number_format($c['atendidas']) }}</b><span>Atendidas</span><small>{{ $pct($c['pctAtendidas']) }}</small></td>
                <td><b style="color:#9A3A22">{{ number_format($c['noAcudieron']) }}</b><span>No acudieron</span><small>{{ $pct($c['pctNoAcudieron']) }}</small></td>
                <td><b style="color:#7A5D10">{{ number_format($c['sinAtencion']) }}</b><span>Llegaron sin atención</span></td>
                <td><b style="color:#7A5D10">{{ number_format($c['sinRegistro']) }}</b><span>Sin registro</span></td>
                <td><b>{{ number_format($c['porVenir']) }}</b><span>Por atender</span></td>
                <td><b style="color:#4B3F7A">{{ number_format($c['largas']) }}</b><span>Atención larga</span><small>promedio {{ $c['promedio'] ?? '—' }} min</small></td>
                <td><b>{{ number_format($c['tarde']) }}</b><span>Llegaron tarde</span></td>
            </tr>
        </table>
    @endforeach

    {{-- Por periodo, con una barra por renglón --}}
    <div class="et-sec">{{ $agruparTexto }}</div>
    @if ($general['citas'] === 0)
        <p class="et-nota">No hay citas en este periodo con esos filtros.</p>
    @else
        <table class="et-t">
            <thead>
                <tr>
                    <th class="i" style="width: 13%">{{ $unidad }}</th>
                    @foreach ($columnas as $col)<th>{{ $col }}</th>@endforeach
                    <th>Excepción</th>
                    <th style="width: 18%">Gráfica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serie as $p)
                    <tr>
                        <td class="i"><b>{{ $p['etiqueta'] }}</b></td>
                        @foreach (array_keys($columnas) as $k)
                            <td>{{ $k === 'promedio' ? ($p['promedio'] ?? '—') : $p[$k] }}</td>
                        @endforeach
                        <td style="color:#8C1D40">{{ $p['excepcion']['citas'] }}</td>
                        <td class="i">
                            @if ($p['citas'])
                                @php
                                    $w = fn ($v) => round($v * 100 / $maxSerie, 2);
                                    $partes = [['#1E7B3C', $p['atendidas']], ['#E07A5F', $p['noAcudieron']], ['#D9A93A', $p['sinCerrar']], ['#B9C7C8', $p['porVenir']]];
                                    $resto = 100 - $w($p['atendidas'] + $p['noAcudieron'] + $p['sinCerrar'] + $p['porVenir']);
                                @endphp
                                <table class="et-gr"><tr>
                                    @foreach ($partes as [$colorP, $v])
                                        @if ($v)<td style="width: {{ $w($v) }}%; background: {{ $colorP }};"></td>@endif
                                    @endforeach
                                    @if ($resto > 0)<td style="width: {{ $resto }}%;"></td>@endif
                                </tr></table>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="et-ley">
            Gráfica: <i style="background:#1E7B3C"></i>Atendidas <i style="background:#E07A5F"></i>No acudieron <i style="background:#D9A93A"></i>Sin cerrar <i style="background:#B9C7C8"></i>Por atender
        </div>
    @endif

    <div class="et-sec">Por sede</div>
    @include('estadisticas.turnos._tabla_simple', ['filas' => $porSede, 'primera' => 'Sede'])

    <div class="et-sec et-sec--exc">Casos de excepción por sede</div>
    @if ($excepcion['citas'] === 0)
        <p class="et-nota">No hay casos de excepción en este periodo.</p>
    @else
        @include('estadisticas.turnos._tabla_simple', ['filas' => $porSedeExc, 'primera' => 'Sede'])
    @endif

    @if (count($porTipo))
        <div class="et-sec">Por trámite</div>
        @include('estadisticas.turnos._tabla_simple', ['filas' => $porTipo, 'primera' => 'Trámite'])
    @endif

    <div class="et-sec">Atenciones más largas ({{ $umbral }} min o más)</div>
    @if (empty($largas))
        <p class="et-nota">Ninguna atención duró {{ $umbral }} minutos o más en este periodo.</p>
    @else
        <table class="et-t">
            <thead><tr><th>Minutos</th><th>Fecha</th><th>Folio</th><th>Sede</th><th>Trámite</th><th>Origen</th><th>Atención</th><th class="i">La atendió</th></tr></thead>
            <tbody>
                @foreach ($largas as $l)
                    <tr>
                        <td><b style="color:#4B3F7A">{{ $l['minutos'] }}</b></td>
                        <td>{{ $l['fecha'] }} {{ $l['hora'] }}</td>
                        <td><b>{{ $l['folio'] }}</b>{{ $l['excepcion'] ? ' · Excepción' : '' }}</td>
                        <td>{{ $l['sede'] }}</td>
                        <td>{{ $l['tipo'] }}</td>
                        <td>{{ $l['origen'] }}</td>
                        <td>{{ $l['inicio'] }} – {{ $l['fin'] }}</td>
                        <td class="i">{{ $l['atendio'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($general['largas'] > count($largas))
            <p class="et-nota">Se muestran las {{ count($largas) }} más largas de {{ number_format($general['largas']) }}.</p>
        @endif
    @endif

    @include('estadisticas.turnos._agendadores_pdf', ['ag' => $ag ?? []])

    <div class="et-sec">Qué cuenta cada cifra</div>
    <table class="et-glo">
        @foreach ($glosario as $termino => $texto)
            <tr><td class="i">{{ $termino }}</td><td>{{ $texto }}</td></tr>
        @endforeach
        <tr><td class="i">Porcentajes</td><td>Se calculan sobre las citas cuyo día ya pasó (no cuentan las de hoy ni las siguientes).</td></tr>
    </table>
</div>
