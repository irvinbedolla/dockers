{{--
    Tabla de /estadisticas/solicitudes (por periodo o por sede). Espera
    $filas, $primera (encabezado de la primera columna), $rama, $universo y,
    opcional, $enlace (fn fila → url o null).
--}}
@php
    $pctT = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $amp = $universo === 'ampliada';
    $enlace = $enlace ?? null;
    $sum = fn ($k) => collect($filas)->sum($k);
@endphp
<div class="table-responsive">
    <table class="table table-hover es-tabla mb-0">
        <thead>
            <tr>
                <th>{{ $primera }}</th>
                <th class="es-num">Solicitudes</th>
                <th class="es-num">Registradas en la rama</th>
                @if ($amp)<th class="es-num">Estimación ampliada</th>@endif
                <th class="es-num">Semanas sin pagar</th>
                <th class="es-num">IMSS (alta o semanas)</th>
                <th class="es-num">Cualquiera</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filas as $fila)
                @php($ir = $enlace && $fila['total'] ? $enlace($fila) : null)
                <tr>
                    <td class="es-etq-c">@if ($ir)<a href="{{ $ir }}">{{ $fila['etiqueta'] }}</a>@else{{ $fila['etiqueta'] }}@endif</td>
                    <td class="es-num">{{ number_format($fila['total']) }}</td>
                    <td class="es-num">{{ number_format($fila['rama']) }}<small>{{ $pctT($fila['pctRama']) }}</small></td>
                    @if ($amp)<td class="es-num">{{ number_format($fila['ampliada']) }}<small>{{ $pctT($fila['pctAmpliada']) }}</small></td>@endif
                    <td class="es-num">{{ number_format($fila['semanas']) }}<small>{{ $pctT($fila['pctSemanas']) }}</small></td>
                    <td class="es-num">{{ number_format($fila['imss']) }}<small>{{ $pctT($fila['pctImss']) }}</small></td>
                    <td class="es-num">{{ number_format($fila['alguno']) }}<small>{{ $pctT($fila['pctAlguno']) }}</small></td>
                </tr>
            @endforeach
            <tr class="es-total">
                <td>Total</td>
                <td class="es-num">{{ number_format($sum('total')) }}</td>
                <td class="es-num">{{ number_format($sum('rama')) }}</td>
                @if ($amp)<td class="es-num">{{ number_format($sum('ampliada')) }}</td>@endif
                <td class="es-num">{{ number_format($sum('semanas')) }}</td>
                <td class="es-num">{{ number_format($sum('imss')) }}</td>
                <td class="es-num">{{ number_format($sum('alguno')) }}</td>
            </tr>
        </tbody>
    </table>
</div>
<p class="es-sub mt-2 mb-0">Los porcentajes de reclamos son sobre las solicitudes de {{ $rama['nombre'] }}{{ $amp ? ' (estimación ampliada)' : '' }}.</p>
