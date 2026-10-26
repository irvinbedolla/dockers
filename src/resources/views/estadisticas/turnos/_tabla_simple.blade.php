{{-- Tabla de cifras para PDF e imagen (estadisticas/turnos/_contenido). Espera $filas, $primera y $columnas. --}}
@php($pctS = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%')
<table class="et-t">
    <thead>
        <tr>
            <th class="i">{{ $primera }}</th>
            @foreach ($columnas as $col)<th>{{ $col }}</th>@endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr class="{{ ! empty($fila['esTotal']) ? 'tot' : '' }}">
                <td class="i"><b>{{ $fila['etiqueta'] }}</b></td>
                @foreach (array_keys($columnas) as $k)
                    <td>
                        @if ($k === 'promedio')
                            {{ $fila['promedio'] ?? '—' }}
                        @else
                            {{ number_format($fila[$k]) }}
                            @if ($k === 'atendidas' && $fila[$k] && $fila['pctAtendidas'] !== null)<br><small>{{ $pctS($fila['pctAtendidas']) }}</small>@endif
                            @if ($k === 'noAcudieron' && $fila[$k] && $fila['pctNoAcudieron'] !== null)<br><small>{{ $pctS($fila['pctNoAcudieron']) }}</small>@endif
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
