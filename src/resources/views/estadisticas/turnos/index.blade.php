@extends('layouts.app')
@section('title', 'Estadísticas de turnos y citas')
{{--
    /estadisticas/turnos (EstadisticasController::turnos,
    App\Support\EstadisticasTurnos): citas y turnos de recepción por sede y
    por día, semana o mes; cuántas se atendieron, cuántas no acudieron,
    cuántas quedaron sin cerrar, cuáles tardaron más y lo mismo de los casos
    de excepción. Se descarga en PDF, Excel o imagen
    (estadisticas.turnos.descargar).
--}}
@php
    use App\Support\EstadisticasTurnos as ET;
    $f = $filtros;
    $hoyC = \Illuminate\Support\Carbon::today();
    $atajos = [
        'Hoy'         => [$hoyC->toDateString(), $hoyC->toDateString(), 'dia'],
        'Esta semana' => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->copy()->endOfWeek()->toDateString(), 'dia'],
        'Este mes'    => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->copy()->endOfMonth()->toDateString(), 'dia'],
        'Mes pasado'  => [$hoyC->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoyC->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(), 'dia'],
        'Este año'    => [$hoyC->copy()->startOfYear()->toDateString(), $hoyC->copy()->endOfYear()->toDateString(), 'mes'],
    ];
    $base = array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'agrupar' => $f['agrupar'], 'sede' => $f['sede'], 'tipo' => $f['tipo'], 'origen' => $f['origen'], 'minutos' => $f['minutos'] !== ET::UMBRAL ? $f['minutos'] : null]);
    $url = fn (array $c) => route('estadisticas.turnos', array_filter(array_merge($base, $c), fn ($v) => $v !== null && $v !== ''));
    $descargar = fn (string $formato) => route('estadisticas.turnos.descargar', $base + ['formato' => $formato]);
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $g = $general;
    $x = $excepcion;
    $hayDatos = $g['citas'] > 0;
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .et-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .et-migas:hover { color: var(--tn-verde); }
        .et-descargas { display: flex; flex-wrap: wrap; gap: 8px; }
        .et-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .et-filtros label, .et-etq-f { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .et-filtros .form-control, .et-filtros .form-select { min-width: 140px; }
        .et-atajos, .et-segmento { display: flex; flex-wrap: wrap; gap: 6px; }
        .et-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .et-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .et-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .et-bloque { margin-top: 16px; }
        .et-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .et-titulo { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .et-sub { font-size: 12.5px; color: var(--tn-tenue); }

        .et-cifras { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: 10px; }
        @media (max-width: 1400px) { .et-cifras { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 700px)  { .et-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .et-cifra { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; background: var(--tn-fondo); }
        .et-cifra b { display: block; font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .et-cifra span { font-size: 12px; color: var(--tn-suave); font-weight: 600; }
        .et-cifra small { display: block; font-size: 11px; color: var(--tn-tenue); margin-top: 2px; }
        .et-cifra--ok { background: #E9F5EE; border-color: #CBE7D6; } .et-cifra--ok b { color: #1E7B3C; }
        .et-cifra--no { background: #FBEDEA; border-color: #F2D2CA; } .et-cifra--no b { color: #9A3A22; }
        .et-cifra--pend { background: #FBF4E1; border-color: #F0E2B6; } .et-cifra--pend b { color: #7A5D10; }
        .et-cifra--larga { background: #F1EFF8; border-color: #DCD6EE; } .et-cifra--larga b { color: #4B3F7A; }
        .et-cifra--exc { background: #F6E4EA; border-color: #EBC9D4; } .et-cifra--exc b { color: #8C1D40; }

        .et-grafica { position: relative; height: 300px; }
        .et-leyenda { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12.5px; color: var(--tn-suave); }
        .et-leyenda i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
        .et-leyenda i.is-linea { height: 2px; width: 14px; vertical-align: 3px; }

        .et-tabla td, .et-tabla th { white-space: nowrap; vertical-align: middle; }
        .et-tabla th { font-size: 11.5px; color: var(--tn-suave); font-weight: 700; }
        .et-tabla .et-etq { font-weight: 700; color: var(--tn-tinta); }
        .et-tabla .et-etq a { color: var(--tn-tinta); text-decoration: none; }
        .et-tabla .et-etq a:hover { color: var(--tn-verde); text-decoration: underline; }
        .et-tabla tr.et-total td { background: #EEF2F2; font-weight: 800; }
        .et-num { text-align: right; font-variant-numeric: tabular-nums; }
        .et-num small { display: block; font-size: 10.5px; color: var(--tn-tenue); font-weight: 600; }
        .et-scroll { max-height: 460px; overflow: auto; }
        .et-vacio { padding: 18px 8px; text-align: center; color: var(--tn-tenue); font-size: 13.5px; }
        .et-chip-exc { display: inline-block; padding: 1px 7px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: #F6E4EA; color: #8C1D40; }
        .et-glosario dt { font-size: 13px; color: var(--tn-tinta); }
        .et-glosario dd { font-size: 12.5px; color: var(--tn-suave); margin-bottom: 8px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="et-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Turnos y citas</h3>
                <span class="small" style="color: var(--tn-tenue);">Citas en línea y turnos de ventanilla: atendidas, no acudieron, sin cerrar y tiempos de atención</span>
            </div>
            <div class="et-descargas">
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('pdf') }}"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('xlsx') }}"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('png') }}" target="_blank" rel="noopener"><i class="bi bi-image"></i> Imagen</a>
            </div>
        </div>

        <div class="section-body">
            <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.turnos') }}" id="etFiltros">
                <div class="et-filtros">
                    <div>
                        <span class="et-etq-f">Periodo</span>
                        <div class="et-atajos">
                            @foreach ($atajos as $etiqueta => [$d, $h, $agr])
                                <a class="et-atajo {{ $f['desde'] === $d && $f['hasta'] === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h, 'agrupar' => $agr]) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div><label for="etDesde">Desde</label><input type="date" id="etDesde" name="desde" class="form-control" value="{{ $f['desde'] }}"></div>
                    <div><label for="etHasta">Hasta</label><input type="date" id="etHasta" name="hasta" class="form-control" value="{{ $f['hasta'] }}"></div>
                    <div>
                        <span class="et-etq-f">Ver</span>
                        <div class="et-segmento">
                            @foreach (ET::AGRUPAR as $clave => $texto)
                                <a class="et-atajo {{ $agrupar === $clave ? 'is-activo' : '' }}" href="{{ $url(['agrupar' => $clave]) }}">{{ $texto }}</a>
                            @endforeach
                        </div>
                        <input type="hidden" name="agrupar" value="{{ $f['agrupar'] }}">
                    </div>
                </div>
                <div class="et-filtros mt-3">
                    <div>
                        <label for="etSede">Sede</label>
                        <select id="etSede" name="sede" class="form-select et-auto">
                            <option value="">Todas las sedes</option>
                            @foreach (\App\Support\Recepcion::TODAS_LAS_SEDES as $s)
                                <option value="{{ $s }}" @selected($f['sede'] === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="etTipo">Trámite</label>
                        <select id="etTipo" name="tipo" class="form-select et-auto">
                            <option value="">Todos</option>
                            @foreach (ET::TIPOS as $t)
                                <option value="{{ $t }}" @selected($f['tipo'] === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="etOrigen">Origen</label>
                        <select id="etOrigen" name="origen" class="form-select et-auto">
                            <option value="">En línea y ventanilla</option>
                            @foreach (ET::ORIGENES as $clave => $texto)
                                <option value="{{ $clave }}" @selected($f['origen'] === $clave)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="etMinutos">Atención larga</label>
                        <select id="etMinutos" name="minutos" class="form-select et-auto">
                            @foreach (ET::UMBRALES as $m)
                                <option value="{{ $m }}" @selected($f['minutos'] === $m)>{{ $m }} minutos o más</option>
                            @endforeach
                        </select>
                    </div>
                    <div><button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Aplicar</button></div>
                </div>
            </form>

            @if ($ajustado)
                <div class="alert alert-info et-bloque mb-0 py-2"><i class="bi bi-info-circle"></i> El periodo es largo para verlo así; se muestra <b>{{ mb_strtolower($agruparTexto) }}</b>.</div>
            @endif

            {{-- Cifras --}}
            <div class="tn-tarjeta et-bloque">
                <div class="et-cabeza">
                    <h4 class="et-titulo">{{ $filtrosTexto }}</h4>
                    <span class="et-sub">{{ $periodo }}</span>
                </div>
                <div class="et-cifras">
                    <div class="et-cifra"><b>{{ number_format($g['citas']) }}</b><span>Citas</span><small>{{ number_format($g['linea']) }} en línea · {{ number_format($g['ventanilla']) }} ventanilla</small></div>
                    <div class="et-cifra et-cifra--ok"><b>{{ number_format($g['atendidas']) }}</b><span>Atendidas</span><small>{{ $pct($g['pctAtendidas']) }} de las que ya pasaron</small></div>
                    <div class="et-cifra et-cifra--no"><b>{{ number_format($g['noAcudieron']) }}</b><span>No acudieron</span><small>{{ $pct($g['pctNoAcudieron']) }}{{ $g['llamadas'] ? ' · '.$g['llamadas'].' se llamaron' : '' }}</small></div>
                    <div class="et-cifra et-cifra--pend" title="Ya pasó el día: recepción registró que llegaron, pero nadie registró que se atendieron."><b>{{ number_format($g['sinAtencion']) }}</b><span>Llegaron sin atención</span><small>quedaron en espera</small></div>
                    <div class="et-cifra et-cifra--pend" title="Ya pasó el día y nadie registró ni su llegada ni su atención."><b>{{ number_format($g['sinRegistro']) }}</b><span>Sin registro</span><small>quedaron pendientes</small></div>
                    <div class="et-cifra"><b>{{ number_format($g['porVenir']) }}</b><span>Por atender</span><small>hoy y días siguientes</small></div>
                    <div class="et-cifra et-cifra--larga"><b>{{ number_format($g['largas']) }}</b><span>Atención larga</span><small>{{ $umbral }} min o más · promedio {{ $g['promedio'] ?? '—' }} min{{ $g['maxMinutos'] ? ' · máx. '.$g['maxMinutos'] : '' }}</small></div>
                    <div class="et-cifra"><b>{{ number_format($g['tarde']) }}</b><span>Llegaron tarde</span><small>y sí se atendieron</small></div>
                </div>
            </div>

            {{-- Por periodo --}}
            <div class="tn-tarjeta et-bloque">
                <div class="et-cabeza">
                    <div>
                        <h4 class="et-titulo">{{ $agruparTexto }}</h4>
                        <span class="et-sub">{{ $periodo }}</span>
                    </div>
                    <div class="et-leyenda" aria-hidden="true">
                        <span><i style="background:#1E7B3C"></i>Atendidas</span>
                        <span><i style="background:#E07A5F"></i>No acudieron</span>
                        <span><i style="background:#D9A93A"></i>Sin cerrar</span>
                        <span><i style="background:#B9C7C8"></i>Por atender</span>
                    </div>
                </div>
                @if ($hayDatos)
                    <div class="et-grafica"><canvas id="etGrafica" role="img" aria-label="Citas {{ mb_strtolower($agruparTexto) }}"></canvas></div>
                    <div class="et-scroll mt-3">
                        @include('estadisticas.turnos._tabla', ['filas' => $serie, 'primera' => $unidad, 'conExcepcion' => true,
                            'enlace' => $agrupar === 'dia' ? null : (fn ($fila) => $url(['desde' => $fila['desde'], 'hasta' => $fila['hasta'], 'agrupar' => $agrupar === 'mes' ? 'semana' : 'dia']))])
                    </div>
                @else
                    <div class="et-vacio"><i class="bi bi-calendar-x"></i> No hay citas en este periodo con esos filtros.</div>
                @endif
            </div>

            {{-- Por sede --}}
            <div class="tn-tarjeta et-bloque">
                <div class="et-cabeza"><h4 class="et-titulo">Por sede</h4><span class="et-sub">{{ $periodo }}</span></div>
                @include('estadisticas.turnos._tabla', ['filas' => $porSede, 'primera' => 'Sede',
                    'enlace' => $f['sede'] === '' ? (fn ($fila) => in_array($fila['etiqueta'], \App\Support\Recepcion::TODAS_LAS_SEDES, true) ? $url(['sede' => $fila['etiqueta']]) : null) : null])
            </div>

            {{-- Casos de excepción --}}
            <div class="tn-tarjeta et-bloque">
                <div class="et-cabeza">
                    <div>
                        <h4 class="et-titulo"><span class="et-chip-exc">Art. 685 Ter</span> Casos de excepción</h4>
                        <span class="et-sub">Citas marcadas como caso de excepción · {{ $periodo }}</span>
                    </div>
                    @if (\App\Support\CasosExcepcion::estadisticas(auth()->user()))
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.excepciones', array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'sede' => $f['sede']])) }}"><i class="bi bi-shield-exclamation"></i> Atendidos y canalizados</a>
                    @endif
                </div>
                @if ($x['citas'] === 0)
                    <div class="et-vacio">No hay casos de excepción en este periodo.</div>
                @else
                    <div class="et-cifras mb-3">
                        <div class="et-cifra et-cifra--exc"><b>{{ number_format($x['citas']) }}</b><span>Citas</span><small>{{ number_format($x['linea']) }} en línea · {{ number_format($x['ventanilla']) }} ventanilla</small></div>
                        <div class="et-cifra et-cifra--ok"><b>{{ number_format($x['atendidas']) }}</b><span>Atendidas</span><small>{{ $pct($x['pctAtendidas']) }}</small></div>
                        <div class="et-cifra et-cifra--no"><b>{{ number_format($x['noAcudieron']) }}</b><span>No acudieron</span><small>{{ $pct($x['pctNoAcudieron']) }}</small></div>
                        <div class="et-cifra et-cifra--pend"><b>{{ number_format($x['sinAtencion']) }}</b><span>Llegaron sin atención</span></div>
                        <div class="et-cifra et-cifra--pend"><b>{{ number_format($x['sinRegistro']) }}</b><span>Sin registro</span></div>
                        <div class="et-cifra"><b>{{ number_format($x['porVenir']) }}</b><span>Por atender</span></div>
                        <div class="et-cifra et-cifra--larga"><b>{{ number_format($x['largas']) }}</b><span>Atención larga</span><small>promedio {{ $x['promedio'] ?? '—' }} min</small></div>
                        <div class="et-cifra"><b>{{ number_format($x['tarde']) }}</b><span>Llegaron tarde</span></div>
                    </div>
                    @include('estadisticas.turnos._tabla', ['filas' => $porSedeExc, 'primera' => 'Sede'])
                @endif
            </div>

            {{-- Por trámite --}}
            @if (count($porTipo))
                <div class="tn-tarjeta et-bloque">
                    <div class="et-cabeza"><h4 class="et-titulo">Por trámite</h4><span class="et-sub">{{ $periodo }}</span></div>
                    @include('estadisticas.turnos._tabla', ['filas' => $porTipo, 'primera' => 'Trámite',
                        'enlace' => $f['tipo'] === '' ? (fn ($fila) => in_array($fila['etiqueta'], ET::TIPOS, true) ? $url(['tipo' => $fila['etiqueta']]) : null) : null])
                </div>
            @endif

            {{-- Atenciones más largas --}}
            <div class="tn-tarjeta et-bloque">
                <div class="et-cabeza">
                    <div>
                        <h4 class="et-titulo">Atenciones más largas</h4>
                        <span class="et-sub">{{ $umbral }} minutos o más, de la más larga a la más corta{{ $g['largas'] > count($largas) ? ' · las '.count($largas).' más largas de '.number_format($g['largas']) : '' }}</span>
                    </div>
                </div>
                @if (empty($largas))
                    <div class="et-vacio">Ninguna atención duró {{ $umbral }} minutos o más en este periodo{{ $g['conTiempo'] ? '' : ' (o todavía no hay atenciones con hora de inicio y de fin)' }}.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover et-tabla mb-0">
                            <thead><tr><th class="et-num">Minutos</th><th>Fecha</th><th>Folio</th><th>Sede</th><th>Trámite</th><th>Origen</th><th>Atención</th><th>La atendió</th></tr></thead>
                            <tbody>
                                @foreach ($largas as $l)
                                    <tr>
                                        <td class="et-num" style="color:#4B3F7A"><b>{{ $l['minutos'] }}</b></td>
                                        <td>{{ $l['fecha'] }} <span style="color:var(--tn-tenue)">{{ $l['hora'] }}</span></td>
                                        <td><b>{{ $l['folio'] }}</b> @if ($l['excepcion'])<span class="et-chip-exc">Excepción</span>@endif</td>
                                        <td>{{ $l['sede'] }}</td>
                                        <td>{{ $l['tipo'] }}</td>
                                        <td>{{ $l['origen'] }}</td>
                                        <td>{{ $l['inicio'] }} – {{ $l['fin'] }}</td>
                                        <td>{{ $l['atendio'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Qué cuenta cada cifra --}}
            <details class="tn-tarjeta et-bloque et-glosario">
                <summary class="et-titulo" style="cursor:pointer">Qué cuenta cada cifra</summary>
                <dl class="mt-3 mb-0">
                    @foreach ($glosario as $termino => $texto)
                        <dt>{{ $termino }}</dt><dd>{{ $texto }}</dd>
                    @endforeach
                    <dt>Porcentajes</dt><dd>Se calculan sobre las citas cuyo día ya pasó (no cuentan las de hoy ni las siguientes).</dd>
                </dl>
            </details>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('#etFiltros .et-auto').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
    </script>
    @if ($hayDatos)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
        (function () {
            var serie = {!! json_encode(array_map(fn ($p) => [
                'e' => $p['corta'], 'citas' => $p['citas'], 'atendidas' => $p['atendidas'], 'no' => $p['noAcudieron'],
                'sin' => $p['sinCerrar'], 'venir' => $p['porVenir'],
            ], $serie)) !!};
            var lienzo = document.getElementById('etGrafica');
            if (!lienzo || typeof Chart === 'undefined') { return; }
            var col = function (k) { return serie.map(function (p) { return p[k]; }); };
            var barra = { type: 'bar', stack: 'a', maxBarThickness: 28, borderColor: '#fff', borderWidth: { bottom: 1 }, order: 2 };
            new Chart(lienzo.getContext('2d'), {
                data: {
                    labels: col('e'),
                    datasets: [
                        Object.assign({}, barra, { label: 'Atendidas', data: col('atendidas'), backgroundColor: '#1E7B3C' }),
                        Object.assign({}, barra, { label: 'No acudieron', data: col('no'), backgroundColor: '#E07A5F' }),
                        Object.assign({}, barra, { label: 'Sin cerrar', data: col('sin'), backgroundColor: '#D9A93A' }),
                        Object.assign({}, barra, { label: 'Por atender', data: col('venir'), backgroundColor: '#B9C7C8', borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: false }),
                        { type: 'line', label: 'Citas', data: col('citas'), borderColor: '#2563A8', backgroundColor: '#2563A8', borderWidth: 2,
                          pointRadius: serie.length > 40 ? 0 : 3, pointHoverRadius: 5, pointBorderColor: '#fff', pointBorderWidth: 2,
                          cubicInterpolationMode: 'monotone', order: 1 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: '#5E6E6F', maxRotation: 0, autoSkip: true, autoSkipPadding: 12 } },
                        y: { stacked: true, beginAtZero: true, suggestedMax: 5, grid: { color: '#EDF1F1' }, border: { display: false }, ticks: { color: '#5E6E6F', precision: 0 } }
                    }
                }
            });
        })();
        </script>
    @endif
@endsection
