@extends('layouts.app')
@section('title', 'Estadísticas de notificaciones')
{{--
    /estadisticas/notificaciones (EstadisticasController::notificaciones).
    Sección del Panel estadístico con todo lo de notificaciones: cifras del
    periodo, gráfica, desglose por sede y por notificador. Antes era el
    filtro avanzado del historial de notificaciones; el historial quedó
    sólo como lista. Las cifras salen de App\Support\DesempenoNotificadores.
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
    $desde = $filtros['desde'] ?? null;
    $hasta = $filtros['hasta'] ?? null;

    // URL de esta misma página cambiando algunos filtros.
    $url = function (array $cambios) use ($filtros, $todo) {
        $q = array_merge(array_intersect_key($filtros, array_flip(['sede', 'notificador', 'tipo', 'desde', 'hasta'])), $todo ? ['periodo' => 'todo'] : [], $cambios);

        return route('estadisticas.notificaciones', array_filter($q, fn ($v) => $v !== null && $v !== ''));
    };
    $conPeriodo = fn (string $d, string $h) => $url(['desde' => $d, 'hasta' => $h, 'periodo' => null]);

    $fechaCorta = fn ($f) => \Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('D MMM YYYY');
    $textoPeriodo = $todo
        ? 'todo el historial'
        : ($desde === $hasta ? $fechaCorta($desde) : $fechaCorta($desde).' – '.$fechaCorta($hasta));
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $nombre = fn ($n) => \Illuminate\Support\Str::title(mb_strtolower($n));

    // Liga al historial con los mismos filtros (la lista filtra por fecha de diligencia).
    $alHistorial = fn (array $extra = []) => route('notificaciones_consultar', array_filter(array_merge([
        'sede'     => $filtros['sede'] ?? null,
        'tipo'     => $filtros['tipo'] ?? null,
        'desde'    => $desde,
        'hasta'    => $hasta,
        'fecha_de' => $todo ? null : 'notificacion',
    ], $extra)));

    $hayGrafica = array_sum($tendencia['asignadas']) + array_sum($tendencia['exitosas']) + array_sum($tendencia['noExitosas']) > 0;
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .en-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .en-migas:hover { color: var(--tn-verde); }

        .en-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .en-filtros label, .en-etq { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .en-filtros .form-control, .en-filtros .form-select { min-width: 150px; }
        .en-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .en-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .en-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .en-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .en-filtros__fila { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; width: 100%; padding-top: 12px; border-top: 1px dashed var(--tn-borde); }

        .en-bloque { margin-top: 16px; }
        .en-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .en-titulo { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .en-sub { font-size: 12.5px; color: var(--tn-tenue); }

        .en-cifras { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
        @media (max-width: 1100px) { .en-cifras { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 560px)  { .en-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .en-cifra { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; background: var(--tn-fondo); }
        .en-cifra b { display: block; font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .en-cifra span { font-size: 12px; color: var(--tn-suave); font-weight: 600; }
        .en-cifra small { display: block; font-size: 11px; color: var(--tn-tenue); margin-top: 2px; }
        .en-cifra--exito { background: #E9F5EE; border-color: #CBE7D6; }
        .en-cifra--exito b { color: #1E7B3C; }
        .en-cifra--falla b { color: #9A2B1C; }
        .en-cifra--pend b { color: #7A5D10; }

        .en-grafica { position: relative; height: 280px; }
        .en-leyenda { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12.5px; color: var(--tn-suave); }
        .en-leyenda i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
        .en-leyenda i.is-linea { height: 2px; border-radius: 2px; vertical-align: 3px; width: 14px; }
        .en-tabla-grafica summary { font-size: 12.5px; color: var(--tn-verde); font-weight: 600; cursor: pointer; margin-top: 10px; }
        .en-tabla-grafica .table-responsive { max-height: 260px; overflow: auto; margin-top: 8px; }

        .en-tabla td, .en-tabla th { white-space: nowrap; vertical-align: middle; }
        .en-tabla th { font-size: 12px; color: var(--tn-suave); font-weight: 700; }
        .en-num { text-align: right; font-variant-numeric: tabular-nums; }
        .en-barra { display: inline-block; width: 80px; height: 6px; border-radius: 99px; background: #EDF1F1; vertical-align: middle; margin-right: 6px; overflow: hidden; }
        .en-barra i { display: block; height: 100%; background: #1E7B3C; border-radius: 99px; }
        .en-tabla a.en-nombre { color: var(--tn-tinta); font-weight: 700; text-decoration: none; }
        .en-tabla a.en-nombre:hover { color: var(--tn-verde); text-decoration: underline; }
        .en-correo { display: block; font-size: 11.5px; color: var(--tn-tenue); }
        .en-ir { color: var(--tn-tenue); font-size: 15px; }
        .en-ir:hover { color: var(--tn-verde); }
        .en-vacio { padding: 22px 8px; text-align: center; color: var(--tn-tenue); font-size: 13.5px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="en-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Notificaciones</h3>
                <span class="small" style="color: var(--tn-tenue);">Asignadas, diligencias y efectividad por periodo, sede y notificador</span>
            </div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ $alHistorial() }}"><i class="bi bi-list-ul"></i> Ver en el historial</a>
        </div>

        <div class="section-body">
            {{-- ===================== Filtros ===================== --}}
            <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.notificaciones') }}" id="enFiltros">
                <div class="en-filtros">
                    <div>
                        <span class="en-etq">Periodo</span>
                        <div class="en-atajos">
                            @foreach ($periodos as $etiqueta => [$d, $h])
                                <a class="en-atajo {{ ! $todo && $desde === $d && $hasta === $h ? 'is-activo' : '' }}" href="{{ $conPeriodo($d, $h) }}">{{ $etiqueta }}</a>
                            @endforeach
                            <a class="en-atajo {{ $todo ? 'is-activo' : '' }}" href="{{ $url(['desde' => null, 'hasta' => null, 'periodo' => 'todo']) }}">Todo</a>
                        </div>
                    </div>
                    <div class="en-filtros__fila">
                        <div>
                            <label for="enDesde">Desde</label>
                            <input type="date" id="enDesde" name="desde" class="form-control" value="{{ $desde }}">
                        </div>
                        <div>
                            <label for="enHasta">Hasta</label>
                            <input type="date" id="enHasta" name="hasta" class="form-control" value="{{ $hasta }}">
                        </div>
                        <div>
                            <label for="enSede">Sede</label>
                            <select id="enSede" name="sede" class="form-select en-auto">
                                <option value="">Todas las sedes</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="enNotificador">Notificador</label>
                            <select id="enNotificador" name="notificador" class="form-select en-auto">
                                <option value="">Todos los notificadores</option>
                                <option value="sin" @selected(($filtros['notificador'] ?? '') === 'sin')>Sin notificador</option>
                                @if ($elegido && ! $notificadores->flatten()->contains('id', $elegido->id))
                                    <option value="{{ $elegido->id }}" selected>{{ $nombre($elegido->name) }} (inactivo)</option>
                                @endif
                                @foreach ($notificadores as $sede => $gente)
                                    <optgroup label="{{ $sede }}">
                                        @foreach ($gente as $u)
                                            <option value="{{ $u->id }}" @selected(($filtros['notificador'] ?? '') === (string) $u->id)>{{ $nombre($u->name) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="enTipo">Tipo</label>
                            <select id="enTipo" name="tipo" class="form-select en-auto">
                                <option value="">Todos</option>
                                <option value="Citatorio" @selected(($filtros['tipo'] ?? '') === 'Citatorio')>Citatorio</option>
                                <option value="Multa" @selected(($filtros['tipo'] ?? '') === 'Multa')>Multa</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Aplicar</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- ===================== Cifras del periodo ===================== --}}
            <div class="tn-tarjeta en-bloque">
                <div class="en-cabeza">
                    <h4 class="en-titulo">
                        @if ($elegido)
                            {{ $nombre($elegido->name) }}
                        @elseif (($filtros['notificador'] ?? '') === 'sin')
                            Sin notificador
                        @else
                            Todos los notificadores
                        @endif
                        {{ ! empty($filtros['sede']) ? ' · '.$filtros['sede'] : '' }}
                    </h4>
                    <span class="en-sub">
                        @if ($elegido)
                            {{ $elegido->email }} · {{ $elegido->delegacion }} ·
                        @endif
                        {{ $textoPeriodo }}
                    </span>
                </div>
                <div class="en-cifras">
                    <div class="en-cifra" title="Asignadas en el periodo. Antes de guardarse la fecha de asignación se toma la fecha de registro del citado.">
                        <b>{{ number_format($resumen['asignadas']) }}</b><span>Asignadas</span><small>en el periodo</small>
                    </div>
                    <div class="en-cifra en-cifra--exito">
                        <b>{{ number_format($resumen['exitosas']) }}</b><span>Exitosas</span><small>diligencia en el periodo</small>
                    </div>
                    <div class="en-cifra en-cifra--falla">
                        <b>{{ number_format($resumen['noExitosas']) }}</b><span>No exitosas</span><small>diligencia en el periodo</small>
                    </div>
                    <div class="en-cifra">
                        <b>{{ $pct($resumen['efectividad']) }}</b><span>Efectividad</span><small>exitosas / concluidas</small>
                    </div>
                    <div class="en-cifra en-cifra--pend">
                        <b>{{ number_format($resumen['pendientes']) }}</b><span>Pendientes hoy</span><small>asignadas sin concluir</small>
                    </div>
                    <div class="en-cifra" title="Notificadas en audiencia o enviadas a exhorto.">
                        <b>{{ number_format($resumen['otras']) }}</b><span>Audiencia / exhorto</span><small>en el periodo</small>
                    </div>
                </div>
            </div>

            {{-- ===================== Gráfica ===================== --}}
            <div class="tn-tarjeta en-bloque">
                <div class="en-cabeza">
                    <div>
                        <h4 class="en-titulo">Asignadas y diligencias por {{ $tendencia['unidad'] === 'mes' ? 'mes' : 'día' }}</h4>
                        <span class="en-sub">{{ $todo ? 'Últimos doce meses' : $textoPeriodo }}</span>
                    </div>
                    <div class="en-leyenda" aria-hidden="true">
                        <span><i style="background:#1E7B3C"></i>Exitosas</span>
                        <span><i style="background:#E07A5F"></i>No exitosas</span>
                        <span><i class="is-linea" style="background:#2563A8"></i>Asignadas</span>
                    </div>
                </div>
                @if ($hayGrafica)
                    <div class="en-grafica">
                        <canvas id="enGrafica" role="img" aria-label="Asignadas, exitosas y no exitosas por {{ $tendencia['unidad'] === 'mes' ? 'mes' : 'día' }}"></canvas>
                    </div>
                    <details class="en-tabla-grafica">
                        <summary>Ver como tabla</summary>
                        <div class="table-responsive">
                            <table class="table table-sm en-tabla mb-0">
                                <thead><tr><th>{{ $tendencia['unidad'] === 'mes' ? 'Mes' : 'Día' }}</th><th class="en-num">Asignadas</th><th class="en-num">Exitosas</th><th class="en-num">No exitosas</th></tr></thead>
                                <tbody>
                                    @foreach ($tendencia['etiquetas'] as $i => $etiqueta)
                                        <tr>
                                            <td>{{ $etiqueta }}</td>
                                            <td class="en-num">{{ number_format($tendencia['asignadas'][$i]) }}</td>
                                            <td class="en-num">{{ number_format($tendencia['exitosas'][$i]) }}</td>
                                            <td class="en-num">{{ number_format($tendencia['noExitosas'][$i]) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @else
                    <div class="en-vacio"><i class="bi bi-bar-chart"></i> Sin movimiento en este periodo.</div>
                @endif
            </div>

            {{-- ===================== Por sede ===================== --}}
            @if ($porSede->isNotEmpty())
                <div class="tn-tarjeta en-bloque">
                    <div class="en-cabeza">
                        <h4 class="en-titulo">Por sede</h4>
                        <span class="en-sub">Sede del expediente · {{ $textoPeriodo }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover en-tabla mb-0">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th class="en-num">Asignadas</th>
                                    <th class="en-num">Exitosas</th>
                                    <th class="en-num">No exitosas</th>
                                    <th class="en-num">Audiencia / exhorto</th>
                                    <th class="en-num">Pendientes hoy</th>
                                    <th>Efectividad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($porSede as $f)
                                    <tr>
                                        <td><a class="en-nombre" href="{{ $url(['sede' => $f['sede']]) }}" title="Ver sólo esta sede">{{ $f['sede'] }}</a></td>
                                        <td class="en-num">{{ number_format($f['asignadas']) }}</td>
                                        <td class="en-num" style="color:#1E7B3C;font-weight:700;">{{ number_format($f['exitosas']) }}</td>
                                        <td class="en-num">{{ number_format($f['noExitosas']) }}</td>
                                        <td class="en-num">{{ number_format($f['otras']) }}</td>
                                        <td class="en-num">{{ number_format($f['pendientes']) }}</td>
                                        <td>
                                            @if ($f['efectividad'] !== null)
                                                <span class="en-barra"><i style="width: {{ $f['efectividad'] }}%"></i></span>{{ $pct($f['efectividad']) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ===================== Por notificador ===================== --}}
            @if (! $elegido && ($filtros['notificador'] ?? '') !== 'sin')
                <div class="tn-tarjeta en-bloque">
                    <div class="en-cabeza">
                        <h4 class="en-titulo">Por notificador</h4>
                        <span class="en-sub">Ordenado por exitosas · {{ $textoPeriodo }}</span>
                    </div>
                    @if ($porNotificador->isEmpty())
                        <div class="en-vacio">Ningún notificador tuvo movimiento en este periodo.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover en-tabla mb-0">
                                <thead>
                                    <tr>
                                        <th>Notificador</th>
                                        <th>Sede</th>
                                        <th class="en-num">Asignadas</th>
                                        <th class="en-num">Exitosas</th>
                                        <th class="en-num">No exitosas</th>
                                        <th class="en-num">Pendientes hoy</th>
                                        <th>Efectividad</th>
                                        <th><span class="visually-hidden">Historial</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($porNotificador as $f)
                                        <tr>
                                            <td>
                                                <a class="en-nombre" href="{{ $url(['notificador' => $f['id']]) }}" title="Ver sólo sus cifras">{{ $nombre($f['nombre']) }}</a>
                                                @if ($f['correo'])<span class="en-correo">{{ $f['correo'] }}</span>@endif
                                            </td>
                                            <td>{{ $f['sede'] ?: '—' }}</td>
                                            <td class="en-num">{{ number_format($f['asignadas']) }}</td>
                                            <td class="en-num" style="color:#1E7B3C;font-weight:700;">{{ number_format($f['exitosas']) }}</td>
                                            <td class="en-num">{{ number_format($f['noExitosas']) }}</td>
                                            <td class="en-num">{{ number_format($f['pendientes']) }}</td>
                                            <td>
                                                @if ($f['efectividad'] !== null)
                                                    <span class="en-barra"><i style="width: {{ $f['efectividad'] }}%"></i></span>{{ $pct($f['efectividad']) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <a class="en-ir" href="{{ $alHistorial(['notificador' => $f['id']]) }}" title="Ver sus notificaciones en el historial" aria-label="Ver sus notificaciones en el historial"><i class="bi bi-box-arrow-up-right"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
    (function () {
        // Los selects aplican al cambiar; las fechas esperan a "Aplicar".
        var form = document.getElementById('enFiltros');
        form.querySelectorAll('.en-auto').forEach(function (s) {
            s.addEventListener('change', function () { form.submit(); });
        });
    })();
    </script>

    @if ($hayGrafica)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
        (function () {
            var serie = @json($tendencia);
            var lienzo = document.getElementById('enGrafica');
            if (!lienzo || typeof Chart === 'undefined') { return; }

            var tinta = '#5E6E6F', rejilla = '#EDF1F1';
            new Chart(lienzo.getContext('2d'), {
                data: {
                    labels: serie.etiquetas,
                    datasets: [
                        { type: 'bar', label: 'Exitosas', data: serie.exitosas, stack: 'd', backgroundColor: '#1E7B3C',
                          maxBarThickness: 24, order: 2 },
                        { type: 'bar', label: 'No exitosas', data: serie.noExitosas, stack: 'd', backgroundColor: '#E07A5F',
                          borderColor: '#fff', borderWidth: { bottom: 2 }, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: false, maxBarThickness: 24, order: 2 },
                        { type: 'line', label: 'Asignadas', data: serie.asignadas, borderColor: '#2563A8', backgroundColor: '#2563A8',
                          borderWidth: 2, pointRadius: serie.etiquetas.length > 40 ? 0 : 4, pointHoverRadius: 5,
                          pointBorderColor: '#fff', pointBorderWidth: 2, cubicInterpolationMode: 'monotone', order: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                footer: function (items) {
                                    var e = 0, n = 0;
                                    items.forEach(function (i) {
                                        if (i.dataset.label === 'Exitosas') { e = i.raw; }
                                        if (i.dataset.label === 'No exitosas') { n = i.raw; }
                                    });
                                    return e + n ? 'Efectividad: ' + Math.round(e * 1000 / (e + n)) / 10 + '%' : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: tinta, maxRotation: 0, autoSkip: true, autoSkipPadding: 12 } },
                        y: { stacked: true, beginAtZero: true, suggestedMax: 5, grid: { color: rejilla }, border: { display: false },
                             ticks: { color: tinta, precision: 0 } }
                    }
                }
            });
        })();
        </script>
    @endif
@endsection
