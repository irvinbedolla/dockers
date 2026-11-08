@extends('layouts.app')
@section('title', 'Historial de rutas')

{{--
    Historial semanal de rutas (RutasNotificadoresController::historial).
    Cada celda es la ruta de un notificador en un día: paradas hechas de las
    planeadas. Lo hecho se guarda en cada parada al momento de capturar
    (App\Services\Sinotifico\HistorialRutas), así que no cambia después.
--}}

@php
    $semana = $lunes->format('o-\WW');
    $anterior = $lunes->subWeek()->format('o-\WW');
    $siguiente = $lunes->addWeek()->format('o-\WW');
    $esActual = $lunes->isSameWeek(now());
    $rango = $lunes->locale('es')->isoFormat('D MMM').' – '.$lunes->addDays(6)->locale('es')->isoFormat('D MMM YYYY');
    $pct = fn ($a, $b) => $b ? round($a / $b * 100) : 0;
    $url = fn (array $cambios) => route('rutas.historial', array_filter(array_merge(['semana' => $semana, 'sede' => $sede], $cambios)));
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .rh-nav { display: flex; align-items: center; gap: 8px; }
        .rh-nav .btn { padding: 4px 10px; }
        .rh-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 14px 0; }
        .rh-cifra { background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px; padding: 12px 14px; }
        .rh-cifra b { display: block; font-size: 22px; line-height: 1.1; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .rh-cifra span { font-size: 12px; color: var(--tn-tenue); }
        .rh-tabla th.rh-dia, .rh-tabla td.rh-dia { text-align: center; min-width: 96px; }
        .rh-celda { display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px 8px; border-radius: 10px; text-decoration: none; min-width: 78px; border: 1px solid transparent; }
        .rh-celda:hover { border-color: var(--tn-verde-claro); }
        .rh-celda b { font-size: 14px; font-variant-numeric: tabular-nums; }
        .rh-celda small { font-size: 10.5px; font-weight: 700; letter-spacing: .02em; }
        .rh--cumplida { background: #E6F2EA; color: #1E7B3C; }
        .rh--parcial { background: #FBF4E1; color: #8A6A12; }
        .rh--sin_avance { background: #FBEAE8; color: #A93226; }
        .rh--no_aprobada { background: var(--tn-fondo); color: var(--tn-suave); }
        .rh-vacio { color: #C9D1D2; }
        .rh-barra { display: block; height: 6px; border-radius: 99px; background: var(--tn-fondo); overflow: hidden; margin-top: 4px; min-width: 90px; }
        .rh-barra i { display: block; height: 100%; background: #2E8B57; }
        .rh-leyenda { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: var(--tn-suave); margin-top: 10px; }
        .rh-leyenda span::before { content: ''; display: inline-block; width: 11px; height: 11px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; }
        .rh-l--cumplida::before { background: #CDE6D5; } .rh-l--parcial::before { background: #F3E2B3; }
        .rh-l--sin_avance::before { background: #F3CFCB; } .rh-l--no_aprobada::before { background: #E3E8E8; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('rutas.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Rutas de notificadores
                </a>
                <h3 class="page__heading mb-0">Historial de rutas</h3>
            </div>
            <div class="rh-nav">
                <a class="btn btn-outline-secondary" href="{{ $url(['semana' => $anterior]) }}" aria-label="Semana anterior"><i class="bi bi-chevron-left"></i></a>
                <span class="fw-semibold">{{ $rango }}</span>
                <a class="btn btn-outline-secondary {{ $esActual ? 'disabled' : '' }}" href="{{ $url(['semana' => $siguiente]) }}" aria-label="Semana siguiente"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('rutas.historial') }}" class="tn-tarjeta">
                <div class="nt-filtros" style="grid-template-columns: repeat(auto-fit, minmax(180px, 240px));">
                    <div>
                        <label for="rhSemana">Semana</label>
                        <input type="week" id="rhSemana" name="semana" class="form-control" value="{{ $semana }}">
                    </div>
                    @if ($sedes)
                        <div>
                            <label for="rhSede">Sede</label>
                            <select id="rhSede" name="sede" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected($sede === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Ver</button>
                        @unless ($esActual)
                            <a class="btn btn-outline-secondary" href="{{ route('rutas.historial', array_filter(['sede' => $sede])) }}">Esta semana</a>
                        @endunless
                    </div>
                </div>
            </form>

            <div class="rh-cifras">
                <div class="rh-cifra"><b>{{ $totales['rutas'] }}</b><span>Rutas aprobadas</span></div>
                <div class="rh-cifra"><b>{{ $totales['cumplidas'] }}</b><span>Cumplidas completas</span></div>
                <div class="rh-cifra"><b>{{ $pct($totales['hechas'], $totales['total']) }}%</b><span>Paradas hechas ({{ $totales['hechas'] }} de {{ $totales['total'] }})</span></div>
                <div class="rh-cifra"><b>{{ $totales['exitosas'] }}</b><span>Notificaciones exitosas validadas</span></div>
                <div class="rh-cifra"><b>{{ $totales['km'] }} km</b><span>Recorrido planeado</span></div>
            </div>

            <div class="tn-tarjeta p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table nt-tabla rh-tabla align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Notificador</th>
                                @foreach ($dias as $d)
                                    <th class="rh-dia text-capitalize">{{ $d->locale('es')->isoFormat('ddd D') }}</th>
                                @endforeach
                                <th>Semana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filas as $f)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $f['nombre'] }}</span>
                                        <span class="d-block small text-muted">{{ $f['sede'] }}</span>
                                    </td>
                                    @foreach ($dias as $d)
                                        @php $r = $f['por_dia']->get($d->toDateString()); @endphp
                                        <td class="rh-dia">
                                            @if ($r)
                                                <a class="rh-celda rh--{{ $r->estado }}" href="{{ route('rutas.editor', [$f['id'], $d->toDateString()]) }}"
                                                   title="{{ $r->hechas }} de {{ $r->total }} paradas · {{ $r->exitosas }} exitosas · {{ $r->distancia_km }} km">
                                                    <b>{{ $r->hechas }}/{{ $r->total }}</b>
                                                    <small>{{ ['cumplida' => 'CUMPLIDA', 'parcial' => $r->cumplimiento.'%', 'sin_avance' => 'SIN AVANCE', 'no_aprobada' => 'NO APROBADA'][$r->estado] }}</small>
                                                </a>
                                            @else
                                                <span class="rh-vacio">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        <span class="fw-semibold">{{ $f['hechas'] }}/{{ $f['total'] }}</span>
                                        <span class="small text-muted">· {{ $f['cumplidas'] }} de {{ $f['rutas'] }} rutas cumplidas</span>
                                        <span class="rh-barra"><i style="width: {{ $pct($f['hechas'], $f['total']) }}%"></i></span>
                                        <span class="d-block small text-muted">{{ $f['exitosas'] }} exitosas · {{ $f['km'] }} km</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $dias->count() + 2 }}" class="text-center text-muted py-4">No hubo rutas esta semana.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rh-leyenda">
                <span class="rh-l--cumplida">Cumplida: todas sus paradas capturadas</span>
                <span class="rh-l--parcial">Parcial</span>
                <span class="rh-l--sin_avance">Sin avance</span>
                <span class="rh-l--no_aprobada">No se aprobó (el notificador no la vio)</span>
                <span>Toca una celda para ver la ruta de ese día en el mapa.</span>
            </div>
        </div>
    </section>
@endsection
