{{--
    Hojas del Excel de estadísticas de turnos (App\Exports\EstadisticasTurnosExport).
    $hoja: resumen, periodo, sedes, excepcion, tramites o largas. Las tablas
    empiezan en la fila 6 (la exportación fija el encabezado ahí).
--}}
@php
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD; text-align: center;';
    $tot = ' font-weight: bold; background-color: #EEF2F2;';
    $ancho = count($columnas) + 3;
    $num = fn ($fila, $k) => $k === 'promedio' ? ($fila['promedio'] ?? '') : $fila[$k];
    $pctN = fn ($v) => $v === null ? '' : $v;
    $tablaCifras = [
        'citas'       => 'Citas',
        'linea'       => 'En línea',
        'ventanilla'  => 'Ventanilla',
        'atendidas'   => 'Atendidas',
        'noAcudieron' => 'No acudieron',
        'llamadas'    => 'No acudieron aunque se llamaron',
        'sinAtencion' => 'Llegaron sin atención',
        'sinRegistro' => 'Sin registro',
        'porVenir'    => 'Por atender',
        'largas'      => 'Atención larga (≥ '.$umbral.' min)',
        'promedio'    => 'Tiempo promedio de atención (min)',
        'maxMinutos'  => 'Atención más larga (min)',
        'tarde'       => 'Llegaron tarde y se atendieron',
    ];
    $titulos = ['resumen' => 'Resumen', 'periodo' => $agruparTexto, 'sedes' => 'Por sede', 'excepcion' => 'Casos de excepción por sede', 'tramites' => 'Por trámite', 'largas' => 'Atenciones más largas ('.$umbral.' min o más)'];
@endphp
<table>
    <tr><td colspan="{{ $ancho }}" style="font-size: 16px; font-weight: bold; color: #354647; height: 28px;">{{ $titulo }} · Centro de Conciliación Laboral del Estado de Michoacán</td></tr>
    <tr><td colspan="{{ $ancho }}" style="font-size: 12px; font-weight: bold; color: #496163;">{{ $titulos[$hoja] }} · {{ $periodo }}</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #5F6B6C;">{{ $filtrosTexto }} · atención larga: {{ $umbral }} min o más</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.$por : '' }}.</td></tr>
    <tr><td colspan="{{ $ancho }}"></td></tr>

    @if ($hoja === 'resumen')
        <tr>
            <th width="38" style="{{ $th }} text-align: left;">Cifra</th>
            <th width="16" style="{{ $th }}">Todas las citas</th>
            <th width="12" style="{{ $th }}">%</th>
            <th width="16" style="{{ $th }}">Casos de excepción</th>
            <th width="12" style="{{ $th }}">%</th>
        </tr>
        @foreach ($tablaCifras as $k => $nombre)
            @php $pk = ['atendidas' => 'pctAtendidas', 'noAcudieron' => 'pctNoAcudieron'][$k] ?? null; @endphp
            <tr>
                <td style="{{ $td }} text-align: left;">{{ $nombre }}</td>
                <td style="{{ $td }} font-weight: bold;">{{ $num($general, $k) }}</td>
                <td style="{{ $td }}">{{ $pk ? $pctN($general[$pk]) : '' }}</td>
                <td style="{{ $td }} font-weight: bold; color: #8C1D40;">{{ $num($excepcion, $k) }}</td>
                <td style="{{ $td }}">{{ $pk ? $pctN($excepcion[$pk]) : '' }}</td>
            </tr>
        @endforeach
        <tr><td colspan="5"></td></tr>
        <tr><td colspan="5" style="font-weight: bold; color: #354647;">Qué cuenta cada cifra</td></tr>
        @foreach ($glosario as $termino => $texto)
            <tr><td style="font-weight: bold; color: #2E3C3D;">{{ $termino }}</td><td colspan="4" style="color: #5F6B6C;">{{ $texto }}</td></tr>
        @endforeach
        <tr><td style="font-weight: bold; color: #2E3C3D;">Porcentajes</td><td colspan="4" style="color: #5F6B6C;">Se calculan sobre las citas cuyo día ya pasó (no cuentan las de hoy ni las siguientes).</td></tr>

    @elseif ($hoja === 'largas')
        <tr>
            <th width="10" style="{{ $th }}">Minutos</th>
            <th width="12" style="{{ $th }}">Fecha</th>
            <th width="8" style="{{ $th }}">Hora</th>
            <th width="16" style="{{ $th }}">Folio</th>
            <th width="16" style="{{ $th }}">Sede</th>
            <th width="14" style="{{ $th }}">Trámite</th>
            <th width="12" style="{{ $th }}">Origen</th>
            <th width="10" style="{{ $th }}">Inicio</th>
            <th width="10" style="{{ $th }}">Fin</th>
            <th width="28" style="{{ $th }}">La atendió</th>
            <th width="12" style="{{ $th }}">Excepción</th>
        </tr>
        @forelse ($largas as $l)
            <tr>
                <td style="{{ $td }} font-weight: bold; color: #4B3F7A;">{{ $l['minutos'] }}</td>
                <td style="{{ $td }}">{{ $l['fecha'] }}</td>
                <td style="{{ $td }}">{{ $l['hora'] }}</td>
                <td style="{{ $td }}">{{ $l['folio'] }}</td>
                <td style="{{ $td }}">{{ $l['sede'] }}</td>
                <td style="{{ $td }}">{{ $l['tipo'] }}</td>
                <td style="{{ $td }}">{{ $l['origen'] }}</td>
                <td style="{{ $td }}">{{ $l['inicio'] }}</td>
                <td style="{{ $td }}">{{ $l['fin'] }}</td>
                <td style="{{ $td }} text-align: left;">{{ $l['atendio'] }}</td>
                <td style="{{ $td }}">{{ $l['excepcion'] ? 'Sí' : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="11" style="color: #8A9899;">Ninguna atención duró {{ $umbral }} minutos o más en este periodo.</td></tr>
        @endforelse

    @else
        @php
            [$filas, $primera] = match ($hoja) {
                'periodo'   => [$serie, $unidad],
                'sedes'     => [$porSede, 'Sede'],
                'excepcion' => [$porSedeExc, 'Sede'],
                'tramites'  => [$porTipo, 'Trámite'],
            };
        @endphp
        <tr>
            <th width="{{ $hoja === 'periodo' ? 30 : 22 }}" style="{{ $th }} text-align: left;">{{ $primera }}</th>
            @foreach ($columnas as $k => $col)
                <th width="{{ in_array($k, ['sinAtencion', 'largas', 'promedio'], true) ? 15 : 12 }}" style="{{ $th }}">{{ $col }}</th>
            @endforeach
            <th width="12" style="{{ $th }}">% atendidas</th>
            <th width="12" style="{{ $th }}">% no acudieron</th>
            @if ($hoja === 'periodo')
                <th width="13" style="{{ $th }}">Casos de excepción</th>
            @endif
        </tr>
        @foreach ($filas as $fila)
            @php $t = ! empty($fila['esTotal']) ? $tot : ''; @endphp
            <tr>
                <td style="{{ $td }} text-align: left; font-weight: bold;{{ $t }}">{{ $fila['etiqueta'] }}</td>
                @foreach (array_keys($columnas) as $k)
                    <td style="{{ $td }}{{ $t }}">{{ $num($fila, $k) }}</td>
                @endforeach
                <td style="{{ $td }}{{ $t }}">{{ $pctN($fila['pctAtendidas']) }}</td>
                <td style="{{ $td }}{{ $t }}">{{ $pctN($fila['pctNoAcudieron']) }}</td>
                @if ($hoja === 'periodo')
                    <td style="{{ $td }} color: #8C1D40;">{{ $fila['excepcion']['citas'] }}</td>
                @endif
            </tr>
        @endforeach
        @if ($hoja === 'periodo' && count($filas))
            @php $g = $general; @endphp
            <tr>
                <td style="{{ $td }} text-align: left;{{ $tot }}">Total</td>
                @foreach (array_keys($columnas) as $k)
                    <td style="{{ $td }}{{ $tot }}">{{ $num($g, $k) }}</td>
                @endforeach
                <td style="{{ $td }}{{ $tot }}">{{ $pctN($g['pctAtendidas']) }}</td>
                <td style="{{ $td }}{{ $tot }}">{{ $pctN($g['pctNoAcudieron']) }}</td>
                <td style="{{ $td }}{{ $tot }} color: #8C1D40;">{{ $excepcion['citas'] }}</td>
            </tr>
        @endif
        @if ($hoja === 'excepcion' && $excepcion['citas'] === 0)
            <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">No hay casos de excepción en este periodo.</td></tr>
        @endif
    @endif
</table>
