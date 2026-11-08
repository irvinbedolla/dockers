@extends('layouts.app')
@section('title', 'Rutas de notificadores')

{{--
    Notificaciones → Rutas (RutasNotificadoresController). Ruta del día de
    cada notificador: se arma, se ajusta en el mapa y se aprueba. En la app
    SíNotifico el notificador sólo ve las aprobadas.
--}}

@php
    $hoy = now()->toDateString();
    $fechaLegible = \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .rt-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 12px; }
        .rt-chip { display: inline-flex; gap: 8px; align-items: center; padding: 6px 12px; border-radius: 10px; background: #fff; border: 1px solid var(--tn-borde); font-size: 13px; font-weight: 600; }
        .rt-chip b { font-variant-numeric: tabular-nums; }
        .rt-estado { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .rt-estado--aprobada { background: #E6F2EA; color: #1E7B3C; }
        .rt-estado--borrador { background: #FBF4E1; color: #8A6A12; }
        .rt-estado--sin { background: var(--tn-fondo); color: var(--tn-suave); }
        .rt-urgentes, .rt-sub.rt-urgentes { color: #C0392B; font-weight: 700; }
        .rt-conexion { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; white-space: nowrap; }
        .rt-punto { width: 9px; height: 9px; border-radius: 50%; background: #B8C2C3; }
        .rt-punto--linea { background: #2E8B57; box-shadow: 0 0 0 3px rgba(46,139,87,.18); }
        .rt-sub { display: block; font-size: 11.5px; color: var(--tn-tenue); white-space: nowrap; }
        .rt-sub--aviso { color: #8A6A12; font-weight: 600; }
        .rt-ultima { max-width: 260px; }
        .rt-ultima__nombre { display: block; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <span class="small" style="color: var(--tn-verde-claro);"><i class="bi bi-phone"></i> SíNotifico</span>
                <h3 class="page__heading mb-0">Rutas de notificadores</h3>
            </div>
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('rutas.index') }}" class="tn-tarjeta">
                <div class="nt-filtros" style="grid-template-columns: repeat(auto-fit, minmax(180px, 240px));">
                    <div>
                        <label for="rtFecha">Día de la ruta</label>
                        <input type="date" id="rtFecha" name="fecha" class="form-control" value="{{ $fecha }}" min="{{ now()->subDays(7)->toDateString() }}">
                    </div>
                    @if ($sedes)
                        <div>
                            <label for="rtSede">Sede</label>
                            <select id="rtSede" name="sede" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected($sede === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Ver</button>
                        @if ($fecha !== $hoy)
                            <a href="{{ route('rutas.index', array_filter(['sede' => $sede])) }}" class="btn btn-outline-secondary">Hoy</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="rt-chips">
                <span class="rt-chip text-capitalize">{{ $fechaLegible }}</span>
                <span class="rt-chip"><span class="rt-estado rt-estado--aprobada">Aprobadas</span> <b>{{ $resumen['aprobadas'] }}</b></span>
                <span class="rt-chip"><span class="rt-estado rt-estado--borrador">Por aprobar</span> <b>{{ $resumen['borradores'] }}</b></span>
                <span class="rt-chip"><span class="rt-estado rt-estado--sin">Sin ruta</span> <b>{{ $resumen['sin_ruta'] }}</b></span>
            </div>

            <div class="tn-tarjeta p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table nt-tabla align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Notificador</th>
                                <th>Conexión</th>
                                <th class="text-end">Pendientes</th>
                                <th>Ruta del día</th>
                                <th>Última parada</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filas as $f)
                                @php
                                    $r = $f['ruta'];
                                    $c = $f['conexion'];
                                    $u = $f['ultima'];
                                    $hace = fn ($t) => $t->isToday() ? $t->diffForHumans() : $t->locale('es')->isoFormat('D MMM, HH:mm');
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $f['usuario']->name }}</span>
                                        <span class="rt-sub">{{ $f['usuario']->delegacion }}</span>
                                    </td>
                                    <td>
                                        @if (! $c)
                                            <span class="rt-conexion text-muted"><span class="rt-punto"></span>Sin datos</span>
                                            <span class="rt-sub">Aún no abre la app</span>
                                        @else
                                            <span class="rt-conexion" title="Última señal: {{ $c['visto_at']->format('d/m/Y H:i') }}">
                                                <span class="rt-punto {{ $c['en_linea'] ? 'rt-punto--linea' : '' }}"></span>
                                                {{ $c['en_linea'] ? 'En línea' : $hace($c['visto_at']) }}
                                            </span>
                                            @if ($c['en_cola'] > 0)
                                                <span class="rt-sub rt-sub--aviso">{{ $c['en_cola'] }} {{ $c['en_cola'] === 1 ? 'captura' : 'capturas' }} sin enviar</span>
                                            @elseif ($c['sincronizado_at'])
                                                <span class="rt-sub">Sincronizado {{ $hace($c['sincronizado_at']) }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ $f['visitas'] }}
                                        @if ($f['urgentes'])<span class="rt-sub rt-urgentes">{{ $f['urgentes'] }} urgentes</span>@endif
                                    </td>
                                    <td>
                                        @if (! $r)
                                            <span class="rt-estado rt-estado--sin">Sin ruta</span>
                                        @elseif ($r->estatus === 'aprobada')
                                            <span class="rt-estado rt-estado--aprobada">Aprobada</span>
                                            <span class="rt-sub">{{ $f['hechas'] }}/{{ $r->paradas_count }} paradas hechas · {{ $r->distancia_km }} km</span>
                                        @else
                                            <span class="rt-estado rt-estado--borrador">Por aprobar</span>
                                            <span class="rt-sub">{{ $r->paradas_count }} paradas</span>
                                        @endif
                                    </td>
                                    <td class="rt-ultima">
                                        @if ($u)
                                            <span class="rt-ultima__nombre" title="{{ $u['citado'] }}">{{ $u['citado'] }}</span>
                                            <span class="rt-sub">{{ $u['expediente'] }} · {{ $u['resultado'] }}</span>
                                            <span class="rt-sub">{{ $hace($u['capturada_at']) }}{{ $u['estatus'] === 'por_revisar' ? ' · por revisar' : '' }}</span>
                                        @else
                                            <span class="text-muted small">Sin capturas desde la app</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('rutas.editor', [$f['usuario']->id, $fecha]) }}"
                                           class="btn btn-sm {{ $r ? 'btn-outline-secondary' : 'tn-boton-dorado' }}">
                                            <i class="bi bi-map me-1"></i>{{ $r ? 'Ver ruta' : 'Armar ruta' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No hay notificadores activos en esta sede.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @if ($fecha === $hoy)
        {{-- Hoy: se refresca cada minuto para ver quién está en línea y su última parada. --}}
        <script>setTimeout(function () { if (!document.hidden) location.reload(); else document.addEventListener('visibilitychange', function () { location.reload(); }, { once: true }); }, 60000);</script>
    @endif
@endsection
