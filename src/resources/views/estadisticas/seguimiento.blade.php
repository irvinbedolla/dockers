@extends('layouts.app')
@section('title', 'Seguimiento de casos')

{{--
    Panel estadístico → Seguimiento de casos: cuántos casos llegan a cada
    etapa y cuánto tardan (App\Support\EmbudoCasos). Cada caso se consulta
    completo en /seguimiento.
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ec-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .ec-filtros label { display: block; font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 4px; }
        .ec-filtros .form-control, .ec-filtros .form-select { min-height: 40px; border-radius: 10px; }
        .ec-par { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 14px; margin-top: 14px; }
        .ec-embudo { display: flex; flex-direction: column; gap: 10px; }
        .ec-paso__cabeza { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; font-size: 13.5px; }
        .ec-paso__cabeza b { color: var(--tn-tinta); }
        .ec-paso__num { font-size: 18px; font-weight: 800; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .ec-paso__num small { font-size: 12px; font-weight: 700; color: var(--tn-tenue); margin-left: 4px; }
        .ec-barra { height: 12px; border-radius: 99px; background: #EEF2F2; overflow: hidden; margin: 4px 0 2px; }
        .ec-barra span { display: block; height: 100%; border-radius: 99px; background: var(--tn-verde); }
        .ec-paso:last-child .ec-barra span { background: var(--tn-dorado); }
        .ec-paso__nota { font-size: 11.5px; color: var(--tn-tenue); }
        .ec-paso__paso { font-size: 11.5px; font-weight: 700; color: #A9862B; }
        .ec-tiempos { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; }
        .ec-tiempo { padding: 14px 16px; border-radius: 10px; background: var(--tn-fondo); }
        .ec-tiempo b { display: block; font-size: 26px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .ec-tiempo span { display: block; font-size: 12.5px; color: var(--tn-suave); margin-top: 4px; }
        .ec-tiempo small { display: block; font-size: 11.5px; color: var(--tn-tenue); }
        .ec-tabla { width: 100%; font-size: 13px; }
        .ec-tabla th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); font-weight: 700; padding: 8px 10px; border-bottom: 1px solid var(--tn-borde); text-align: right; white-space: nowrap; }
        .ec-tabla th:first-child, .ec-tabla td:first-child { text-align: left; }
        .ec-tabla td { padding: 9px 10px; border-bottom: 1px solid #F0F3F3; text-align: right; font-variant-numeric: tabular-nums; color: var(--tn-tinta); }
        .ec-nota { font-size: 12px; color: var(--tn-tenue); margin-top: 10px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ route('estadisticas.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Seguimiento de casos</h3>
            </div>
            @if (\Illuminate\Support\Facades\Route::has('seguimiento'))
                <a href="{{ route('seguimiento') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search me-1"></i> Buscar un caso</a>
            @endif
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('estadisticas.seguimiento') }}" class="tn-tarjeta">
                <div class="ec-filtros">
                    <div>
                        <label for="ecDesde">Desde (mes)</label>
                        <input type="month" id="ecDesde" name="desde" class="form-control" value="{{ $filtros['desde'] }}" max="{{ now()->format('Y-m') }}">
                    </div>
                    <div>
                        <label for="ecHasta">Hasta (mes)</label>
                        <input type="month" id="ecHasta" name="hasta" class="form-control" value="{{ $filtros['hasta'] }}" max="{{ now()->format('Y-m') }}">
                    </div>
                    <div>
                        <label for="ecSede">Sede</label>
                        <select id="ecSede" name="sede" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($sedes as $s)
                                <option value="{{ $s }}" @selected($filtros['sede'] === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Ver</button>
                </div>
                <p class="ec-nota mb-0">Casos cuya cita o solicitud cae en el periodo ({{ $filtros['texto'] }}). Los que siguen en trámite todavía pueden avanzar.</p>
            </form>

            <div class="ec-par">
                @foreach ([['Desde la cita', 'De quienes agendaron cita de solicitud', $datos['desdeCita']], ['Desde la solicitud', 'De las solicitudes levantadas', $datos['desdeSolicitud']]] as [$titulo, $sub, $pasos])
                    <div class="tn-tarjeta">
                        <p class="tn-titulo-seccion mb-1">{{ $titulo }}</p>
                        <p class="ec-nota mt-0 mb-3">{{ $sub }}</p>
                        <div class="ec-embudo">
                            @foreach ($pasos as $p)
                                <div class="ec-paso">
                                    <div class="ec-paso__cabeza">
                                        <b>{{ $p['etiqueta'] }}</b>
                                        <span class="ec-paso__num">{{ number_format($p['total']) }}<small>{{ $p['pct'] }}%</small></span>
                                    </div>
                                    <div class="ec-barra" role="img" aria-label="{{ $p['pct'] }}% del total"><span style="width: {{ max($p['total'] ? 2 : 0, $p['pct']) }}%"></span></div>
                                    <div class="d-flex justify-content-between gap-2">
                                        <span class="ec-paso__nota">{{ $p['nota'] }}</span>
                                        @if ($p['paso'] !== null)<span class="ec-paso__paso" title="De la etapa anterior">{{ $p['paso'] }}% pasó</span>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="tn-tarjeta mt-3">
                <p class="tn-titulo-seccion">Cuánto tarda (mediana en días naturales)</p>
                <div class="ec-tiempos">
                    @foreach ($datos['tiempos'] as $t)
                        <div class="ec-tiempo">
                            <b>{{ $t['mediana'] === null ? '—' : $t['mediana'].' '.($t['mediana'] === 1 ? 'día' : 'días') }}</b>
                            <span>{{ $t['etiqueta'] }}</span>
                            <small>{{ number_format($t['casos']) }} {{ $t['casos'] === 1 ? 'caso' : 'casos' }}</small>
                        </div>
                    @endforeach
                </div>
                <p class="ec-nota mb-0">La mitad de los casos tarda eso o menos. Los tiempos que empiezan en la cita sólo cuentan solicitudes ligadas a su cita.</p>
            </div>

            @if (count($datos['porSede']) > 1)
                <div class="tn-tarjeta mt-3">
                    <p class="tn-titulo-seccion">Por sede</p>
                    <div class="table-responsive">
                        <table class="ec-tabla">
                            <thead><tr><th>Sede</th><th>Citas</th><th>Solicitudes</th><th>Confirmadas</th><th>Resueltas</th><th>Con convenio</th><th>% convenio</th><th>Días a resolver</th></tr></thead>
                            <tbody>
                                @foreach ($datos['porSede'] as $s)
                                    <tr>
                                        <td><b>{{ $s['sede'] }}</b></td>
                                        <td>{{ number_format($s['citas']) }}</td>
                                        <td>{{ number_format($s['total']) }}</td>
                                        <td>{{ number_format($s['confirmadas']) }}</td>
                                        <td>{{ number_format($s['resueltas']) }}</td>
                                        <td>{{ number_format($s['convenio']) }}</td>
                                        <td>{{ $s['pctConvenio'] === null ? '—' : $s['pctConvenio'].'%' }}</td>
                                        <td>{{ $s['resolucion'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="ec-nota mb-0">"% convenio" es sobre las resueltas.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
