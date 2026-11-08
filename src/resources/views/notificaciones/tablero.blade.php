@extends('layouts.app')
@section('title', 'Tablero de notificadores')

{{--
    Notificaciones → Tablero de notificadores (TableroNotificadoresController,
    App\Services\Sinotifico\TableroSupervision). Mismas cifras que el
    tablero de la app SíNotifico.
--}}

@php
    $r = $t['resumen'];
    $etiquetaPeriodo = ['hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes'];
    $url = fn (array $c) => route('notificaciones.tablero', array_filter(array_merge(['periodo' => $t['periodo'], 'sede' => $t['alcance'] === 'todas' ? $t['sede'] : null], $c)));
    $hoy = now()->toDateString();
    $pct = fn ($a, $b) => $b ? round($a / $b * 100) : 0;
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .tb-periodos { display: inline-flex; background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px; padding: 3px; gap: 2px; }
        .tb-periodos a { padding: 6px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; color: var(--tn-suave); text-decoration: none; }
        .tb-periodos a.is-activo { background: var(--tn-verde); color: #fff; }
        .tb-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin: 16px 0; }
        .tb-kpi { background: #fff; border: 1px solid var(--tn-borde); border-radius: 14px; padding: 14px 16px; }
        .tb-kpi b { display: block; font-size: 26px; line-height: 1.1; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .tb-kpi span { font-size: 12px; color: var(--tn-tenue); }
        .tb-kpi small { display: block; font-size: 12px; margin-top: 4px; color: var(--tn-suave); }
        .tb-kpi--peligro b { color: #A93226; }
        .tb-kpi--exito b { color: #1E7B3C; }
        .tb-alertas { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
        .tb-alerta { display: flex; gap: 10px; align-items: flex-start; padding: 9px 12px; border-radius: 10px; font-size: 13px; }
        .tb-alerta i { margin-top: 1px; }
        .tb-alerta--peligro { background: #FBEAE8; color: #8E2A20; }
        .tb-alerta--aviso { background: #FBF4E1; color: #7A5A12; }
        .tb-alerta--info { background: var(--tn-fondo); color: var(--tn-suave); }
        .tb-punto { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #B8C2C3; margin-right: 6px; }
        .tb-punto--linea { background: #2E8B57; box-shadow: 0 0 0 3px rgba(46,139,87,.18); }
        .tb-sub { display: block; font-size: 11.5px; color: var(--tn-tenue); }
        .tb-rojo { color: #A93226; font-weight: 700; }
        .tb-ambar { color: #8A6A12; font-weight: 700; }
        .tb-barra { display: block; height: 6px; border-radius: 99px; background: var(--tn-fondo); overflow: hidden; margin-top: 4px; min-width: 80px; }
        .tb-barra i { display: block; height: 100%; background: #2E8B57; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('index_notificaciones') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Notificaciones
                </a>
                <h3 class="page__heading mb-0">Tablero de notificadores</h3>
                <div class="small text-muted">
                    {{ $t['alcance'] === 'todas' ? ($t['sede'] ?: 'Todas las sedes') : 'Sede '.$t['sede'] }} ·
                    {{ \Carbon\Carbon::parse($t['desde'])->locale('es')->isoFormat('D MMM') }}{{ $t['desde'] !== $t['hasta'] ? ' – '.\Carbon\Carbon::parse($t['hasta'])->locale('es')->isoFormat('D MMM') : '' }}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if ($sedes)
                    <form method="GET" action="{{ route('notificaciones.tablero') }}">
                        <input type="hidden" name="periodo" value="{{ $t['periodo'] }}">
                        <select name="sede" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sede">
                            <option value="">Todas las sedes</option>
                            @foreach ($sedes as $s)
                                <option value="{{ $s }}" @selected($t['sede'] === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <nav class="tb-periodos" aria-label="Periodo">
                    @foreach ($etiquetaPeriodo as $clave => $texto)
                        <a href="{{ $url(['periodo' => $clave]) }}" class="{{ $t['periodo'] === $clave ? 'is-activo' : '' }}">{{ $texto }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        <div class="section-body">
            <div class="tb-kpis">
                <div class="tb-kpi"><b>{{ $r['pendientes'] }}</b><span>Visitas pendientes</span>
                    <small>{{ $r['ultimo_dia'] }} vencen hoy</small></div>
                <div class="tb-kpi {{ $r['fuera_de_plazo'] ? 'tb-kpi--peligro' : '' }}"><b>{{ $r['fuera_de_plazo'] }}</b><span>Fuera de plazo</span>
                    <small>Ya pasó el límite para notificar</small></div>
                <div class="tb-kpi"><b>{{ $r['capturas'] }}</b><span>Diligencias capturadas</span>
                    <small>{{ $r['validadas'] }} validadas · {{ $r['devueltas'] }} devueltas</small></div>
                <div class="tb-kpi {{ $r['por_revisar'] ? 'tb-kpi--peligro' : '' }}"><b>{{ $r['por_revisar'] }}</b><span>Por revisar ahora</span>
                    <small>{{ $r['horas_revision'] !== null ? 'Se revisan en '.$r['horas_revision'].' h en promedio' : 'Sin revisiones en el periodo' }}</small></div>
                <div class="tb-kpi tb-kpi--exito"><b>{{ $r['exitosas'] }}</b><span>Notificaciones exitosas</span>
                    <small>{{ $r['tasa_exito'] !== null ? $r['tasa_exito'].'% de lo validado' : '—' }}</small></div>
                <div class="tb-kpi"><b>{{ $r['rutas_cumplidas'] }}/{{ $r['rutas'] }}</b><span>Rutas cumplidas</span>
                    <small>{{ $r['paradas_hechas'] }} de {{ $r['paradas'] }} paradas hechas</small></div>
                <div class="tb-kpi"><b>{{ $r['en_linea'] }}/{{ $r['notificadores'] }}</b><span>Notificadores en línea</span>
                    <small>{{ $r['en_cola'] ? $r['en_cola'].' capturas sin enviar' : 'Nada sin enviar' }}</small></div>
                <div class="tb-kpi"><b>{{ $r['domicilios_validados'] }}</b><span>Domicilios validados en campo</span>
                    <small>Catálogo de domicilios</small></div>
            </div>

            @if ($t['alertas'])
                <div class="tn-tarjeta">
                    <p class="tn-titulo-seccion">Para atender</p>
                    <ul class="tb-alertas">
                        @foreach (array_slice($t['alertas'], 0, 8) as $a)
                            <li class="tb-alerta tb-alerta--{{ $a['nivel'] }}">
                                <i class="bi {{ ['peligro' => 'bi-exclamation-octagon', 'aviso' => 'bi-exclamation-triangle', 'info' => 'bi-info-circle'][$a['nivel']] }}"></i>
                                <span>{{ $a['texto'] }}
                                    @if (in_array($a['tipo'], ['ruta_sin_aprobar', 'sin_ruta'], true))
                                        <a href="{{ route('rutas.editor', [$a['notificador_id'], $hoy]) }}">Ver ruta</a>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    @if (count($t['alertas']) > 8)
                        <p class="small text-muted mt-2 mb-0">Y {{ count($t['alertas']) - 8 }} más.</p>
                    @endif
                </div>
            @endif

            @if ($t['sedes'])
                <div class="tn-tarjeta p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table nt-tabla align-middle mb-0">
                            <thead><tr><th>Sede</th><th class="text-end">Notificadores</th><th class="text-end">Pendientes</th><th class="text-end">Fuera de plazo</th><th class="text-end">Capturadas</th><th class="text-end">Exitosas</th><th class="text-end">Devueltas</th></tr></thead>
                            <tbody>
                                @foreach ($t['sedes'] as $s)
                                    <tr>
                                        <td class="fw-semibold"><a href="{{ $url(['sede' => $s['sede']]) }}">{{ $s['sede'] }}</a></td>
                                        <td class="text-end">{{ $s['notificadores'] }}</td>
                                        <td class="text-end">{{ $s['pendientes'] }}</td>
                                        <td class="text-end {{ $s['fuera_de_plazo'] ? 'tb-rojo' : '' }}">{{ $s['fuera_de_plazo'] }}</td>
                                        <td class="text-end">{{ $s['capturas'] }}</td>
                                        <td class="text-end">{{ $s['exitosas'] }}</td>
                                        <td class="text-end">{{ $s['devueltas'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="tn-tarjeta p-0 overflow-hidden mt-3">
                <div class="table-responsive">
                    <table class="table nt-tabla align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Notificador</th>
                                <th class="text-end">Pendientes</th>
                                <th class="text-end">Capturadas</th>
                                <th class="text-end">Exitosas</th>
                                <th class="text-end">Por revisar</th>
                                <th class="text-end">Devueltas</th>
                                <th>Ruta de hoy</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($t['notificadores'] as $n)
                                <tr>
                                    <td>
                                        <span class="fw-semibold"><span class="tb-punto {{ $n['en_linea'] ? 'tb-punto--linea' : '' }}" title="{{ $n['en_linea'] ? 'En línea' : 'Sin conexión' }}"></span>{{ $n['nombre'] }}</span>
                                        <span class="tb-sub">{{ $n['sede'] }}{{ $n['en_cola'] ? ' · '.$n['en_cola'].' sin enviar' : '' }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $n['pendientes'] }}
                                        @if ($n['fuera_de_plazo'])<span class="tb-sub tb-rojo">{{ $n['fuera_de_plazo'] }} fuera de plazo</span>@endif
                                        @if ($n['ultimo_dia'])<span class="tb-sub tb-ambar">{{ $n['ultimo_dia'] }} vencen hoy</span>@endif
                                    </td>
                                    <td class="text-end">{{ $n['capturas'] }}</td>
                                    <td class="text-end">{{ $n['exitosas'] }}</td>
                                    <td class="text-end">{{ $n['por_revisar'] ?: '—' }}</td>
                                    <td class="text-end {{ $n['devueltas'] ? 'tb-ambar' : '' }}">{{ $n['devueltas'] ?: '—' }}</td>
                                    <td>
                                        @if (! $n['ruta_hoy'])
                                            <span class="text-muted small">Sin ruta</span>
                                        @elseif ($n['ruta_hoy']['estatus'] !== 'aprobada')
                                            <span class="tb-ambar small">Por aprobar</span>
                                        @else
                                            <span class="small fw-semibold">{{ $n['ruta_hoy']['hechas'] }}/{{ $n['ruta_hoy']['total'] }} paradas</span>
                                            <span class="tb-barra"><i style="width: {{ $pct($n['ruta_hoy']['hechas'], $n['ruta_hoy']['total']) }}%"></i></span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('rutas.editor', [$n['id'], $hoy]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-map"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No hay notificadores activos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="small text-muted mt-2">Capturadas, exitosas y devueltas son del periodo elegido; pendientes y por revisar, de este momento.</p>
        </div>
    </section>
@endsection

@section('scripts')
    @if ($t['periodo'] === 'hoy')
        <script>setTimeout(function () { if (!document.hidden) location.reload(); }, 120000);</script>
    @endif
@endsection
