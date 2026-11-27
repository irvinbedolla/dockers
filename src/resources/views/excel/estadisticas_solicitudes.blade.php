{{--
    Hojas del Excel de solicitudes por rama (App\Exports\EstadisticasSolicitudesExport).
    $hoja: resumen, periodo, sedes, ramas o lista. Las tablas empiezan en la
    fila 6 (la exportación fija el encabezado ahí). Los porcentajes van como
    número (12.5 = 12.5 %).
--}}
@php
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD; text-align: center;';
    $tot = ' font-weight: bold; background-color: #EEF2F2;';
    $amp = $universo === 'ampliada';
    $n = fn ($v) => $v === null ? '' : $v;
    $r = $resumen;
    $titulos = ['resumen' => 'Resumen', 'periodo' => $rama['nombre'].' · '.mb_strtolower($agruparTexto), 'sedes' => 'Por sede', 'ramas' => 'Todas las ramas (registradas en cada rama)', 'lista' => 'Solicitudes de '.$rama['nombre'].($amp ? ' (estimación ampliada)' : '')];
    $ancho = 12;
@endphp
<table>
    <tr><td colspan="{{ $ancho }}" style="font-size: 16px; font-weight: bold; color: #354647; height: 28px;">{{ $titulo }} · Centro de Conciliación Laboral del Estado de Michoacán</td></tr>
    <tr><td colspan="{{ $ancho }}" style="font-size: 12px; font-weight: bold; color: #496163;">{{ $titulos[$hoja] }} · {{ $periodo }}</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #5F6B6C;">{{ $filtrosTexto }}</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.$por : '' }}.</td></tr>
    <tr><td colspan="{{ $ancho }}"></td></tr>

    @if ($hoja === 'resumen')
        <tr><th width="52" style="{{ $th }} text-align: left;">1. ¿Qué parte de las solicitudes es de {{ $rama['nombre'] }}?</th><th width="16" style="{{ $th }}">Cantidad</th><th width="14" style="{{ $th }}">%</th></tr>
        <tr><td style="{{ $td }} text-align: left;">Solicitudes en el periodo</td><td style="{{ $td }}">{{ $r['total'] }}</td><td style="{{ $td }}">100</td></tr>
        <tr><td style="{{ $td }} text-align: left;">Registradas en la rama</td><td style="{{ $td }}">{{ $r['rama'] }}</td><td style="{{ $td }}">{{ $n($r['pctRama']) }}</td></tr>
        @if ($amp)
            <tr><td style="{{ $td }} text-align: left;">Estimación ampliada (rama + actividad o puesto)</td><td style="{{ $td }}">{{ $r['ampliada'] }}</td><td style="{{ $td }}">{{ $n($r['pctAmpliada']) }}</td></tr>
        @endif
        <tr><td colspan="3"></td></tr>
        <tr><th style="{{ $th }} text-align: left;">2. ¿Cuántos trabajadores reclaman sus semanas?</th><th style="{{ $th }}">Trabajadores</th><th style="{{ $th }}">%</th><th width="14" style="{{ $th }}">Hombres</th><th width="12" style="{{ $th }}">% hombres</th><th width="14" style="{{ $th }}">Mujeres</th><th width="12" style="{{ $th }}">% mujeres</th></tr>
        <tr style="{{ $tot }}"><td style="{{ $td }} text-align: left;{{ $tot }}">Trabajadores de la rama</td><td style="{{ $td }}{{ $tot }}">{{ $r['trabajadores'] }}</td><td style="{{ $td }}{{ $tot }}">100</td><td style="{{ $td }}{{ $tot }}">{{ $r['hombres'] }}</td><td style="{{ $td }}{{ $tot }}">100</td><td style="{{ $td }}{{ $tot }}">{{ $r['mujeres'] }}</td><td style="{{ $td }}{{ $tot }}">100</td></tr>
        @foreach ($trabajadores as $t)
            <tr><td style="{{ $td }} text-align: left;">Reclaman: {{ $t['nombre'] }}</td><td style="{{ $td }}">{{ $t['total'] }}</td><td style="{{ $td }}">{{ $n($t['pct']) }}</td><td style="{{ $td }}">{{ $t['hombres'] }}</td><td style="{{ $td }}">{{ $n($t['pctH']) }}</td><td style="{{ $td }}">{{ $t['mujeres'] }}</td><td style="{{ $td }}">{{ $n($t['pctM']) }}</td></tr>
        @endforeach
        <tr><td colspan="7"></td></tr>
        <tr><th style="{{ $th }} text-align: left;">Qué cuenta cada cifra</th><th colspan="6" style="{{ $th }} text-align: left;"></th></tr>
        @foreach ($glosario as $termino => $texto)
            <tr><td style="{{ $td }} text-align: left; font-weight: bold;">{{ $termino }}</td><td colspan="6" style="{{ $td }} text-align: left;">{{ $texto }}</td></tr>
        @endforeach
    @elseif ($hoja === 'periodo' || $hoja === 'sedes')
        <tr>
            <th width="30" style="{{ $th }} text-align: left;">{{ $hoja === 'sedes' ? 'Sede' : $unidad }}</th>
            <th width="14" style="{{ $th }}">Solicitudes</th>
            <th width="14" style="{{ $th }}">Registradas en la rama</th><th width="10" style="{{ $th }}">%</th>
            @if ($amp)<th width="14" style="{{ $th }}">Estimación ampliada</th><th width="10" style="{{ $th }}">%</th>@endif
            <th width="14" style="{{ $th }}">Semanas sin pagar</th><th width="10" style="{{ $th }}">%</th>
            <th width="14" style="{{ $th }}">IMSS (alta o semanas)</th><th width="10" style="{{ $th }}">%</th>
            <th width="14" style="{{ $th }}">Cualquiera</th><th width="10" style="{{ $th }}">%</th>
        </tr>
        @php($filas = $hoja === 'sedes' ? $porSede : $serie)
        @foreach ($filas as $fila)
            <tr>
                <td style="{{ $td }} text-align: left;">{{ $fila['etiqueta'] }}</td>
                <td style="{{ $td }}">{{ $fila['total'] }}</td>
                <td style="{{ $td }}">{{ $fila['rama'] }}</td><td style="{{ $td }}">{{ $n($fila['pctRama']) }}</td>
                @if ($amp)<td style="{{ $td }}">{{ $fila['ampliada'] }}</td><td style="{{ $td }}">{{ $n($fila['pctAmpliada']) }}</td>@endif
                <td style="{{ $td }}">{{ $fila['semanas'] }}</td><td style="{{ $td }}">{{ $n($fila['pctSemanas']) }}</td>
                <td style="{{ $td }}">{{ $fila['imss'] }}</td><td style="{{ $td }}">{{ $n($fila['pctImss']) }}</td>
                <td style="{{ $td }}">{{ $fila['alguno'] }}</td><td style="{{ $td }}">{{ $n($fila['pctAlguno']) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="{{ $td }} text-align: left;{{ $tot }}">Total</td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('total') }}</td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('rama') }}</td><td style="{{ $td }}{{ $tot }}">{{ $n($r['pctRama']) }}</td>
            @if ($amp)<td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('ampliada') }}</td><td style="{{ $td }}{{ $tot }}">{{ $n($r['pctAmpliada']) }}</td>@endif
            <td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('semanas') }}</td><td style="{{ $td }}{{ $tot }}">{{ $n($r['pctSemanas']) }}</td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('imss') }}</td><td style="{{ $td }}{{ $tot }}">{{ $n($r['pctImss']) }}</td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($filas)->sum('alguno') }}</td><td style="{{ $td }}{{ $tot }}">{{ $n($r['pctAlguno']) }}</td>
        </tr>
    @elseif ($hoja === 'ramas')
        <tr>
            <th width="60" style="{{ $th }} text-align: left;">Rama</th>
            <th width="14" style="{{ $th }}">Solicitudes</th><th width="10" style="{{ $th }}">% del total</th>
            <th width="14" style="{{ $th }}">Semanas sin pagar</th><th width="10" style="{{ $th }}">%</th>
            <th width="14" style="{{ $th }}">IMSS (alta o semanas)</th><th width="10" style="{{ $th }}">%</th>
            <th width="14" style="{{ $th }}">Cualquiera</th><th width="10" style="{{ $th }}">%</th>
        </tr>
        @foreach ($ramas as $rr)
            <tr>
                <td style="{{ $td }} text-align: left;{{ $rr['elegida'] ? ' font-weight: bold;' : '' }}">{{ $rr['nombre'] }}</td>
                <td style="{{ $td }}">{{ $rr['total'] }}</td><td style="{{ $td }}">{{ $n($rr['pct']) }}</td>
                <td style="{{ $td }}">{{ $rr['semanas'] }}</td><td style="{{ $td }}">{{ $n($rr['pctSemanas']) }}</td>
                <td style="{{ $td }}">{{ $rr['imss'] }}</td><td style="{{ $td }}">{{ $n($rr['pctImss']) }}</td>
                <td style="{{ $td }}">{{ $rr['alguno'] }}</td><td style="{{ $td }}">{{ $n($rr['pctAlguno']) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="{{ $td }} text-align: left;{{ $tot }}">Total</td>
            <td style="{{ $td }}{{ $tot }}">{{ $r['total'] }}</td><td style="{{ $td }}{{ $tot }}">100</td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($ramas)->sum('semanas') }}</td><td style="{{ $td }}{{ $tot }}"></td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($ramas)->sum('imss') }}</td><td style="{{ $td }}{{ $tot }}"></td>
            <td style="{{ $td }}{{ $tot }}">{{ collect($ramas)->sum('alguno') }}</td><td style="{{ $td }}{{ $tot }}"></td>
        </tr>
    @else
        <tr>
            <th width="22" style="{{ $th }}">NUE</th><th width="12" style="{{ $th }}">Fecha</th><th width="16" style="{{ $th }}">Sede</th>
            <th width="34" style="{{ $th }}">Rama registrada</th><th width="22" style="{{ $th }}">Cómo cuenta</th>
            <th width="34" style="{{ $th }}">Actividad</th><th width="24" style="{{ $th }}">Puesto</th><th width="12" style="{{ $th }}">Sexo</th>
            <th width="14" style="{{ $th }}">Semanas sin pagar</th><th width="14" style="{{ $th }}">IMSS (alta o semanas)</th>
        </tr>
        @forelse ($lista as $l)
            <tr>
                <td style="{{ $td }}">{{ $l['nue'] }}</td><td style="{{ $td }}">{{ $l['fecha'] }}</td><td style="{{ $td }}">{{ $l['sede'] }}</td>
                <td style="{{ $td }} text-align: left;">{{ $l['rama'] }}</td><td style="{{ $td }}">{{ $l['como'] }}</td>
                <td style="{{ $td }} text-align: left;">{{ $l['actividad'] }}</td><td style="{{ $td }} text-align: left;">{{ $l['puesto'] }}</td><td style="{{ $td }}">{{ $l['sexo'] }}</td>
                <td style="{{ $td }}">{{ $l['semanas'] ? 'Sí' : '' }}</td><td style="{{ $td }}">{{ $l['imss'] ? 'Sí' : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" style="{{ $td }}">No hay solicitudes de la rama en este periodo.</td></tr>
        @endforelse
    @endif
</table>
