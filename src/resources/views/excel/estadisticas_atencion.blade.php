{{--
    Hojas del Excel del formulario de atención del trámite
    (App\Exports\EstadisticasAtencionExport). La tabla empieza en la fila 5.
--}}
@php
    use App\Support\AtencionTramite as AT;
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD;';
    $num = $td.' text-align: center;';
    $f = $filtros;
@endphp
<table>
    <tr><td colspan="12" style="font-size: 16px; font-weight: bold; color: #354647; height: 26px;">Atención del trámite de conciliación · Centro de Conciliación Laboral del Estado de Michoacán</td></tr>
    <tr><td colspan="12" style="font-weight: bold; color: #496163;">{{ \App\Exports\EstadisticasAtencionExport::HOJAS[$hoja] }} · contestados {{ mb_strtolower($periodo) }} · {{ $f['sede'] ?: 'Todas las sedes' }}{{ $f['parte'] ? ' · '.AT::PARTES[$f['parte']] : '' }}</td></tr>
    <tr><td colspan="12" style="color: #8A9899;">Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.$por : '' }}. Índice: Buena = 100, Regular = 50, Mala = 0.</td></tr>
    <tr><td colspan="12"></td></tr>

    @if (($total ?? 0) === 0 && $hoja !== 'alertas')
        <tr><td colspan="12" style="color: #8A9899;">Nadie contestó el formulario en este periodo.</td></tr>
    @elseif ($hoja === 'resumen')
        <tr><th width="44" style="{{ $th }} text-align: left;">Cifra</th><th width="16" style="{{ $th }}">Valor</th><th width="40" style="{{ $th }}">Detalle</th></tr>
        <tr><td style="{{ $td }}">Formularios contestados</td><td style="{{ $num }}">{{ $total }}</td><td style="{{ $td }}">{{ $porParte['trabajadora'] }} parte trabajadora · {{ $porParte['empleadora'] }} parte empleadora</td></tr>
        <tr><td style="{{ $td }}">Correos enviados en el periodo</td><td style="{{ $num }}">{{ $envios }}</td><td style="{{ $td }}">Tasa de respuesta: {{ $pctRespuesta ?? '—' }}%</td></tr>
        <tr><td style="{{ $td }}">Índice de satisfacción general</td><td style="{{ $num }} font-weight: bold;">{{ $indice ?? '—' }}</td><td style="{{ $td }}">Recepción, captura y conciliador</td></tr>
        @foreach ($etapas as $e)
            <tr><td style="{{ $td }}">Índice · {{ $e['nombre'] }}</td><td style="{{ $num }} font-weight: bold;">{{ $e['indice'] ?? '—' }}</td><td style="{{ $td }}">Buena {{ $e['buena'] }} · Regular {{ $e['regular'] }} · Mala {{ $e['mala'] }} ({{ $e['n'] }} respuestas)</td></tr>
        @endforeach
        @foreach ($derechos['opciones'] as $o)
            <tr><td style="{{ $td }}">Derechos informados · {{ $o['texto'] }}</td><td style="{{ $num }}">{{ $o['n'] }}</td><td style="{{ $td }}">{{ $o['pct'] ?? '—' }}%</td></tr>
        @endforeach
        @foreach ($imparcialidad['favorecePorParte'] as $parte => $c)
            <tr><td style="{{ $td }}">Imparcialidad · contestó {{ mb_strtolower(AT::PARTES[$parte]) }}</td><td style="{{ $num }}">{{ array_sum($c) }}</td><td style="{{ $td }}">Equilibrado {{ $c['equilibrado'] }} · favoreció al trabajador {{ $c['trabajador'] }} · al empleador {{ $c['empleador'] }}</td></tr>
        @endforeach
        <tr><td style="{{ $td }}">Resultado: coincide lo que contestaron con lo registrado</td><td style="{{ $num }}">{{ $resultado['pct'] ?? '—' }}%</td><td style="{{ $td }}">{{ $resultado['coinciden'] }} de {{ $resultado['comparables'] }}</td></tr>
        <tr><td style="{{ $td }}">Sintieron presión al firmar el convenio</td><td style="{{ $num }} font-weight: bold; color: #9A3A22;">{{ $presion }}</td><td style="{{ $td }}">{{ $pctPresion ?? '—' }}% de {{ $convenios }} con convenio</td></tr>
        <tr><td style="{{ $td }}">Alertas sin atender (en el periodo)</td><td style="{{ $num }}">{{ $pendientes }}</td><td style="{{ $td }}"></td></tr>
    @elseif (in_array($hoja, ['sedes', 'conciliadores', 'capturistas'], true))
        @php $filas = ['sedes' => $porSede, 'conciliadores' => $conciliadores, 'capturistas' => $capturistas][$hoja]; @endphp
        <tr>
            <th width="34" style="{{ $th }} text-align: left;">{{ ['sedes' => 'Sede', 'conciliadores' => 'Conciliador(a)', 'capturistas' => 'Capturó'][$hoja] }}</th>
            <th width="12" style="{{ $th }}">Respuestas</th><th width="12" style="{{ $th }}">Recepción</th><th width="12" style="{{ $th }}">Captura</th><th width="12" style="{{ $th }}">Conciliador</th>
            <th width="14" style="{{ $th }}">Con alguna «mala»</th><th width="12" style="{{ $th }}">Informados %</th><th width="12" style="{{ $th }}">Equilibrado %</th><th width="14" style="{{ $th }}">Presión al firmar</th><th width="16" style="{{ $th }}">Nota</th>
        </tr>
        @foreach ($filas as $r)
            <tr>
                <td style="{{ $td }} font-weight: bold;">{{ $r['etiqueta'] }}</td>
                <td style="{{ $num }}">{{ $r['n'] }}</td><td style="{{ $num }}">{{ $r['recepcion'] }}</td><td style="{{ $num }}">{{ $r['captura'] }}</td><td style="{{ $num }}">{{ $r['conciliador'] }}</td>
                <td style="{{ $num }}">{{ $r['malas'] }}</td><td style="{{ $num }}">{{ $r['informado'] }}</td><td style="{{ $num }}">{{ $r['equilibrado'] }}</td>
                <td style="{{ $num }}">{{ $r['presion'] }}{{ $r['convenios'] ? ' de '.$r['convenios'] : '' }}</td>
                <td style="{{ $td }}">{{ $hoja !== 'sedes' && ! $r['suficiente'] ? 'Pocas respuestas (menos de '.\App\Support\EstadisticasAtencion::MINIMO.')' : '' }}</td>
            </tr>
        @endforeach
    @elseif ($hoja === 'respuestas' || $hoja === 'alertas')
        @php $filas = $hoja === 'respuestas' ? $respuestas : ($alertas ?? []); @endphp
        <tr>
            <th width="16" style="{{ $th }}">Fecha</th><th width="20" style="{{ $th }}">Folio</th><th width="14" style="{{ $th }}">Sede</th><th width="16" style="{{ $th }}">Contestó</th><th width="30" style="{{ $th }}">Nombre</th>
            <th width="24" style="{{ $th }}">Conciliador(a)</th><th width="24" style="{{ $th }}">Capturó</th><th width="16" style="{{ $th }}">Resolución</th>
            @foreach (AT::PREGUNTAS as $n => $p)<th width="16" style="{{ $th }}">{{ $n }}. {{ $p['etapa'] }}</th>@endforeach
            <th width="50" style="{{ $th }}">Comentarios</th>
            @if ($hoja === 'alertas')<th width="14" style="{{ $th }}">Seguimiento</th><th width="40" style="{{ $th }}">Nota</th>@endif
        </tr>
        @forelse ($filas as $r)
            <tr>
                <td style="{{ $num }}">{{ $r['fecha'] }}</td><td style="{{ $num }}">{{ $r['folio'] }}</td><td style="{{ $num }}">{{ $r['sede'] }}</td><td style="{{ $num }}">{{ $r['parte'] }}</td><td style="{{ $td }}">{{ $r['nombre'] }}</td>
                <td style="{{ $td }}">{{ $r['conciliador'] }}</td><td style="{{ $td }}">{{ $r['capturo'] }}</td><td style="{{ $num }}">{{ $r['resolucion'] }}</td>
                @foreach (array_keys(AT::PREGUNTAS) as $n)<td style="{{ $num }}">{{ $r['respuestas'][$n] ?? '' }}</td>@endforeach
                <td style="{{ $td }}">{{ implode(' | ', array_map(fn ($n, $c) => $n.': '.$c, array_keys($r['comentarios']), $r['comentarios'])) }}</td>
                @if ($hoja === 'alertas')<td style="{{ $num }}">{{ $r['seguimiento'] === 'atendida' ? 'Atendida '.$r['atendidaAt'] : 'Pendiente' }}</td><td style="{{ $td }}">{{ $r['nota'] }}</td>@endif
            </tr>
        @empty
            <tr><td colspan="12" style="color: #8A9899;">Sin registros.</td></tr>
        @endforelse
    @else
        <tr>
            <th width="12" style="{{ $th }}">Fecha</th><th width="20" style="{{ $th }}">Folio</th><th width="14" style="{{ $th }}">Sede</th><th width="16" style="{{ $th }}">Contestó</th><th width="30" style="{{ $th }}">Nombre</th>
            <th width="20" style="{{ $th }}">Pregunta</th><th width="18" style="{{ $th }}">Respuesta</th><th width="24" style="{{ $th }}">Persona</th><th width="60" style="{{ $th }}">Comentario</th>
        </tr>
        @forelse ($comentarios as $c)
            <tr>
                <td style="{{ $num }}">{{ $c['fecha'] }}</td><td style="{{ $num }}">{{ $c['folio'] }}</td><td style="{{ $num }}">{{ $c['sede'] }}</td><td style="{{ $num }}">{{ $c['parte'] }}</td><td style="{{ $td }}">{{ $c['nombre'] }}</td>
                <td style="{{ $td }}">{{ $c['pregunta'] }}</td><td style="{{ $num }}">{{ $c['respuesta'] }}</td><td style="{{ $td }}">{{ $c['persona'] }}</td><td style="{{ $td }}">{{ $c['texto'] }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="color: #8A9899;">Sin comentarios.</td></tr>
        @endforelse
    @endif
</table>
