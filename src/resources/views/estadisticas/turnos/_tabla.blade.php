{{--
    Tabla de cifras de turnos en pantalla (estadisticas/turnos/index).
    Espera: $filas (EstadisticasTurnos::armar), $columnas, $primera (encabezado
    de la primera columna); opcionales $enlace (fn (array $fila): ?string) y
    $conExcepcion (columna con los casos de excepción de cada fila).
--}}
@php
    $enlace = $enlace ?? null;
    $conExcepcion = $conExcepcion ?? false;
    $pctTxt = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $color = ['atendidas' => '#1E7B3C', 'noAcudieron' => '#9A3A22', 'sinAtencion' => '#7A5D10', 'sinRegistro' => '#7A5D10', 'largas' => '#4B3F7A'];
@endphp
<div class="table-responsive">
    <table class="table table-hover et-tabla mb-0">
        <thead>
            <tr>
                <th>{{ $primera }}</th>
                @foreach ($columnas as $k => $titulo)
                    <th class="et-num">{{ $titulo }}</th>
                @endforeach
                @if ($conExcepcion)
                    <th class="et-num">Casos de excepción</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($filas as $f)
                <tr class="{{ ! empty($f['esTotal']) ? 'et-total' : '' }}">
                    <td class="et-etq">
                        @if ($enlace && empty($f['esTotal']) && ($url = $enlace($f)))
                            <a href="{{ $url }}">{{ $f['etiqueta'] }}</a>
                        @else
                            {{ $f['etiqueta'] }}
                        @endif
                    </td>
                    @foreach ($columnas as $k => $titulo)
                        <td class="et-num" @isset($color[$k]) style="color: {{ $color[$k] }}" @endisset>
                            @if ($k === 'promedio')
                                {{ $f['promedio'] ?? '—' }}
                            @else
                                <b>{{ number_format($f[$k]) }}</b>
                                @if ($k === 'atendidas' && $f['pctAtendidas'] !== null && $f[$k])<small>{{ $pctTxt($f['pctAtendidas']) }}</small>@endif
                                @if ($k === 'noAcudieron' && $f['pctNoAcudieron'] !== null && $f[$k])<small>{{ $pctTxt($f['pctNoAcudieron']) }}</small>@endif
                            @endif
                        </td>
                    @endforeach
                    @if ($conExcepcion)
                        <td class="et-num" style="color:#8C1D40"><b>{{ number_format($f['excepcion']['citas']) }}</b></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
