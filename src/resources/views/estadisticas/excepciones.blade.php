@extends('layouts.app')
@section('title', 'Estadísticas de casos de excepción')
{{--
    /estadisticas/excepciones (EstadisticasController::excepciones,
    App\Support\EstadisticasExcepcion): casos del art. 685 Ter LFT por
    periodo y sede. Lo ven el Panel estadístico y Conciliación, Género e
    Igualdad.
--}}
@php
    $hoyC = \Illuminate\Support\Carbon::today();
    $periodos = [
        'Hoy'         => [$hoyC->toDateString(), $hoyC->toDateString()],
        'Esta semana' => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->copy()->endOfWeek()->toDateString()],
        'Este mes'    => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->copy()->endOfMonth()->toDateString()],
        'Mes pasado'  => [$hoyC->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoyC->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        'Este año'    => [$hoyC->copy()->startOfYear()->toDateString(), $hoyC->copy()->endOfYear()->toDateString()],
    ];
    $desde = $filtros['desde']; $hasta = $filtros['hasta'];
    $url = fn (array $c) => route('estadisticas.excepciones', array_filter(array_merge(['desde' => $desde, 'hasta' => $hasta, 'sede' => $filtros['sede'] ?? null], $c)));
    $corta = fn ($f) => \Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('D MMM YYYY');
    $textoPeriodo = $desde === $hasta ? $corta($desde) : $corta($desde).' – '.$corta($hasta);
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $hayGrafica = array_sum($tendencia['detectados']) + array_sum($tendencia['continua']) + array_sum($tendencia['canaliza']) > 0;
    $r = $resumen;
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ex-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .ex-migas:hover { color: var(--tn-verde); }
        .ex-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .ex-filtros label, .ex-etq { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .ex-filtros .form-control, .ex-filtros .form-select { min-width: 150px; }
        .ex-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .ex-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .ex-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .ex-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .ex-bloque { margin-top: 16px; }
        .ex-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .ex-titulo { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .ex-sub { font-size: 12.5px; color: var(--tn-tenue); }

        .ex-cifras { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
        @media (max-width: 1100px) { .ex-cifras { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 560px)  { .ex-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ex-cifra { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; background: var(--tn-fondo); }
        .ex-cifra b { display: block; font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .ex-cifra span { font-size: 12px; color: var(--tn-suave); font-weight: 600; }
        .ex-cifra small { display: block; font-size: 11px; color: var(--tn-tenue); margin-top: 2px; }
        .ex-cifra--exito { background: #E9F5EE; border-color: #CBE7D6; } .ex-cifra--exito b { color: #1E7B3C; }
        .ex-cifra--canal { background: #FBEDEA; border-color: #F2D2CA; } .ex-cifra--canal b { color: #9A3A22; }
        .ex-cifra--pend b { color: #7A5D10; }

        .ex-grafica { position: relative; height: 280px; }
        .ex-leyenda { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12.5px; color: var(--tn-suave); }
        .ex-leyenda i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
        .ex-leyenda i.is-linea { height: 2px; width: 14px; vertical-align: 3px; }
        .ex-tabla-grafica summary { font-size: 12.5px; color: var(--tn-verde); font-weight: 600; cursor: pointer; margin-top: 10px; }
        .ex-tabla-grafica .table-responsive { max-height: 260px; overflow: auto; margin-top: 8px; }

        .ex-columnas { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .ex-columnas--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        @media (max-width: 900px) { .ex-columnas, .ex-columnas--3 { grid-template-columns: 1fr; } }
        .ex-columnas > .tn-tarjeta { margin-top: 16px; }

        .ex-barras { display: flex; flex-direction: column; gap: 10px; }
        .ex-barra__fila { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--tn-tinta); margin-bottom: 4px; }
        .ex-barra__fila b { font-variant-numeric: tabular-nums; }
        .ex-barra { height: 8px; border-radius: 99px; background: #EDF1F1; overflow: hidden; }
        .ex-barra i { display: block; height: 100%; border-radius: 99px; background: #496163; }

        .ex-tabla td, .ex-tabla th { white-space: nowrap; vertical-align: middle; }
        .ex-tabla th { font-size: 12px; color: var(--tn-suave); font-weight: 700; }
        .ex-num { text-align: right; font-variant-numeric: tabular-nums; }
        .ex-vacio { padding: 18px 8px; text-align: center; color: var(--tn-tenue); font-size: 13.5px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                @if ($esPanel)
                    <a class="ex-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                @else
                    <a class="ex-migas" href="{{ route('excepcion') }}"><i class="bi bi-arrow-left"></i> Casos de excepción</a>
                @endif
                <h3 class="page__heading mb-0">Casos de excepción</h3>
                <span class="small" style="color: var(--tn-tenue);">Art. 685 Ter LFT · detectados, atendidos y canalizados por periodo y sede</span>
            </div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('excepcion', array_filter(['vista' => 'citas', 'desde' => $desde, 'hasta' => $hasta, 'sede' => $filtros['sede'] ?? null])) }}"><i class="bi bi-list-ul"></i> Ver los casos</a>
        </div>

        <div class="section-body">
            @unless ($migrado)
                <div class="alert alert-warning">Falta correr la migración del formato 685 Ter (<code>php artisan migrate</code>); mientras tanto sólo se cuentan los casos detectados.</div>
            @endunless

            <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.excepciones') }}" id="exFiltros">
                <div class="ex-filtros">
                    <div>
                        <span class="ex-etq">Periodo</span>
                        <div class="ex-atajos">
                            @foreach ($periodos as $etiqueta => [$d, $h])
                                <a class="ex-atajo {{ $desde === $d && $hasta === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h]) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div><label for="exDesde">Desde</label><input type="date" id="exDesde" name="desde" class="form-control" value="{{ $desde }}"></div>
                    <div><label for="exHasta">Hasta</label><input type="date" id="exHasta" name="hasta" class="form-control" value="{{ $hasta }}"></div>
                    <div>
                        <label for="exSede">Sede</label>
                        <select id="exSede" name="sede" class="form-select ex-auto">
                            <option value="">Todas las sedes</option>
                            @foreach (\App\Support\CasosExcepcion::SEDES as $s)
                                <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Aplicar</button></div>
                </div>
            </form>

            {{-- Cifras --}}
            <div class="tn-tarjeta ex-bloque">
                <div class="ex-cabeza">
                    <h4 class="ex-titulo">{{ $filtros['sede'] ?? 'Todas las sedes' }}</h4>
                    <span class="ex-sub">{{ $textoPeriodo }}</span>
                </div>
                <div class="ex-cifras">
                    <div class="ex-cifra" title="Citas y turnos marcados como excepción, más las solicitudes marcadas que no vienen de una de esas citas.">
                        <b>{{ number_format($r['detectados']) }}</b><span>Detectados</span>
                        <small>{{ $r['enLinea'] }} en línea · {{ $r['ventanilla'] }} ventanilla · {{ $r['solicitudes'] }} solicitud</small>
                    </div>
                    <div class="ex-cifra"><b>{{ number_format($r['atendidos']) }}</b><span>Atendidos</span><small>formato terminado</small></div>
                    <div class="ex-cifra ex-cifra--exito"><b>{{ number_format($r['continua']) }}</b><span>Continúan</span><small>con la conciliación</small></div>
                    <div class="ex-cifra ex-cifra--canal"><b>{{ number_format($r['canaliza']) }}</b><span>Canalizados</span><small>{{ $pct($r['pctCanaliza']) }} de los atendidos</small></div>
                    <div class="ex-cifra ex-cifra--pend"><b>{{ number_format($r['porAtender']) }}</b><span>Por atender hoy</span><small>últimos 30 días</small></div>
                    <div class="ex-cifra"><b>{{ number_format($r['borradores']) }}</b><span>Borradores</span><small>formato sin terminar</small></div>
                </div>
            </div>

            {{-- Gráfica --}}
            <div class="tn-tarjeta ex-bloque">
                <div class="ex-cabeza">
                    <div>
                        <h4 class="ex-titulo">Detectados y atendidos por {{ $tendencia['unidad'] === 'mes' ? 'mes' : 'día' }}</h4>
                        <span class="ex-sub">{{ $textoPeriodo }}</span>
                    </div>
                    <div class="ex-leyenda" aria-hidden="true">
                        <span><i style="background:#1E7B3C"></i>Continúan</span>
                        <span><i style="background:#E07A5F"></i>Canalizados</span>
                        <span><i class="is-linea" style="background:#2563A8"></i>Detectados</span>
                    </div>
                </div>
                @if ($hayGrafica)
                    <div class="ex-grafica"><canvas id="exGrafica" role="img" aria-label="Casos detectados y atendidos por {{ $tendencia['unidad'] === 'mes' ? 'mes' : 'día' }}"></canvas></div>
                    <details class="ex-tabla-grafica">
                        <summary>Ver como tabla</summary>
                        <div class="table-responsive">
                            <table class="table table-sm ex-tabla mb-0">
                                <thead><tr><th>{{ $tendencia['unidad'] === 'mes' ? 'Mes' : 'Día' }}</th><th class="ex-num">Detectados</th><th class="ex-num">Continúan</th><th class="ex-num">Canalizados</th></tr></thead>
                                <tbody>
                                    @foreach ($tendencia['etiquetas'] as $i => $e)
                                        @continue($tendencia['detectados'][$i] === null)
                                        <tr><td>{{ $e }}</td><td class="ex-num">{{ $tendencia['detectados'][$i] }}</td><td class="ex-num">{{ $tendencia['continua'][$i] }}</td><td class="ex-num">{{ $tendencia['canaliza'][$i] }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @else
                    <div class="ex-vacio"><i class="bi bi-bar-chart"></i> Sin casos en este periodo.</div>
                @endif
            </div>

            {{-- Instituciones y tipos --}}
            <div class="ex-columnas">
                @foreach ([['Canalizaciones por institución', 'Un caso puede canalizarse a varias', $instituciones], ['Tipo de situación', 'Un caso puede tener varios tipos', $tipos]] as [$titulo, $nota, $lista])
                    <div class="tn-tarjeta">
                        <div class="ex-cabeza"><h4 class="ex-titulo">{{ $titulo }}</h4><span class="ex-sub">{{ $nota }}</span></div>
                        @php $max = max(1, (int) $lista->max('total')); @endphp
                        @if ($lista->sum('total') === 0)
                            <div class="ex-vacio">Sin datos en este periodo.</div>
                        @else
                            <div class="ex-barras">
                                @foreach ($lista as $f)
                                    <div>
                                        <div class="ex-barra__fila"><span>{{ $f['etiqueta'] }}</span><b>{{ number_format($f['total']) }}</b></div>
                                        <div class="ex-barra"><i style="width: {{ round($f['total'] * 100 / $max) }}%"></i></div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Por sede --}}
            @if ($porSede->isNotEmpty())
                <div class="tn-tarjeta ex-bloque">
                    <div class="ex-cabeza"><h4 class="ex-titulo">Por sede</h4><span class="ex-sub">{{ $textoPeriodo }}</span></div>
                    <div class="table-responsive">
                        <table class="table table-hover ex-tabla mb-0">
                            <thead><tr><th>Sede</th><th class="ex-num">Detectados</th><th class="ex-num">Atendidos</th><th class="ex-num">Continúan</th><th class="ex-num">Canalizados</th><th class="ex-num">Por atender hoy</th></tr></thead>
                            <tbody>
                                @foreach ($porSede as $f)
                                    <tr>
                                        <td><a href="{{ $url(['sede' => $f['sede']]) }}" style="color:var(--tn-tinta);font-weight:700;text-decoration:none">{{ $f['sede'] }}</a></td>
                                        <td class="ex-num">{{ number_format($f['detectados']) }}</td>
                                        <td class="ex-num">{{ number_format($f['atendidos']) }}</td>
                                        <td class="ex-num" style="color:#1E7B3C;font-weight:700">{{ number_format($f['continua']) }}</td>
                                        <td class="ex-num" style="color:#9A3A22;font-weight:700">{{ number_format($f['canaliza']) }}</td>
                                        <td class="ex-num">{{ number_format($f['porAtender']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Perfil de los casos atendidos --}}
            <div class="ex-columnas ex-columnas--3">
                @foreach ([['Género', $generos], ['Grupo vulnerable', $vulnerables], ['Frecuencia de los hechos', $frecuencias]] as [$titulo, $lista])
                    <div class="tn-tarjeta">
                        <div class="ex-cabeza"><h4 class="ex-titulo">{{ $titulo }}</h4><span class="ex-sub">casos atendidos</span></div>
                        @php $max = max(1, (int) $lista->max('total')); @endphp
                        @if ($lista->isEmpty())
                            <div class="ex-vacio">Sin datos en este periodo.</div>
                        @else
                            <div class="ex-barras">
                                @foreach ($lista as $f)
                                    <div>
                                        <div class="ex-barra__fila"><span>{{ rtrim($f['etiqueta'], '.') }}</span><b>{{ number_format($f['total']) }}</b></div>
                                        <div class="ex-barra"><i style="width: {{ round($f['total'] * 100 / $max) }}%"></i></div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('#exFiltros .ex-auto').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
    </script>
    @if ($hayGrafica)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
        (function () {
            var serie = @json($tendencia);
            var lienzo = document.getElementById('exGrafica');
            if (!lienzo || typeof Chart === 'undefined') { return; }
            new Chart(lienzo.getContext('2d'), {
                data: {
                    labels: serie.etiquetas,
                    datasets: [
                        { type: 'bar', label: 'Continúan', data: serie.continua, stack: 'a', backgroundColor: '#1E7B3C', maxBarThickness: 24, order: 2 },
                        { type: 'bar', label: 'Canalizados', data: serie.canaliza, stack: 'a', backgroundColor: '#E07A5F', borderColor: '#fff',
                          borderWidth: { bottom: 2 }, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: false, maxBarThickness: 24, order: 2 },
                        { type: 'line', label: 'Detectados', data: serie.detectados, borderColor: '#2563A8', backgroundColor: '#2563A8', borderWidth: 2,
                          pointRadius: serie.etiquetas.length > 40 ? 0 : 4, pointHoverRadius: 5, pointBorderColor: '#fff', pointBorderWidth: 2,
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
