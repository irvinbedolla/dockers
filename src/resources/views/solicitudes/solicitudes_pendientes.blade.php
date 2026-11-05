@extends('layouts.app')
@section('title', 'Solicitudes Pendientes')

{{-- Solicitudes por validar. Paginado y filtrado en el servidor (SeerController::solicitudes_pendientes). --}}

@php
    $url = fn (array $cambios) => route('solicitudes_pendientes', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $tiposSolicitud = ['1' => 'Trabajador', '2' => 'Patronal', '3' => 'Patronal Colectiva', '4' => 'Sindical'];
    $origenes = ['linea' => 'Solicitud en línea', 'tablet' => 'Solicitud en tablet', 'centro' => 'Personal Centro'];

    // Periodos rápidos (la lista sólo trae las de los últimos 15 días).
    $hoyC = \Illuminate\Support\Carbon::today();
    $periodos = [
        'Hoy'         => [$hoyC->toDateString(), $hoyC->toDateString()],
        'Ayer'        => [$hoyC->copy()->subDay()->toDateString(), $hoyC->copy()->subDay()->toDateString()],
        'Esta semana' => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->copy()->endOfWeek()->toDateString()],
    ];
    $desde = $filtros['desde'] ?? null;
    $hasta = $filtros['hasta'] ?? null;
    $hayPeriodo = $desde || $hasta;
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        /* Filtro por periodo de la lista. */
        .nt-periodo { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--tn-borde); }
        .nt-periodo label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .nt-periodo .form-control { min-width: 150px; }
        .nt-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .nt-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; }
        .nt-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .nt-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .sp-texto { max-width: 260px; line-height: 1.3; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Solicitudes Pendientes</h3>
                <span class="small" style="color: var(--tn-tenue);">Solicitudes por validar de los últimos 15 días</span>
            </div>
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('solicitudes_pendientes') }}" class="tn-tarjeta" id="spFiltros">
                @if (! empty($filtros['por']))<input type="hidden" name="por" value="{{ $filtros['por'] }}">@endif
                <div class="nt-filtros">
                    <div>
                        <label for="spBuscar">Buscar</label>
                        <input type="search" id="spBuscar" name="buscar" class="form-control" maxlength="100"
                               placeholder="Solicitante, rama o actividad" value="{{ $filtros['buscar'] ?? '' }}">
                    </div>
                    @if (count($sedesPermitidas) > 1)
                        <div>
                            <label for="spSede">Delegación</label>
                            <select id="spSede" name="sede" class="form-select nt-auto">
                                <option value="">Todas</option>
                                @foreach ($sedesPermitidas as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        @if (array_filter(request()->except(['page', 'por'])))
                            <a href="{{ route('solicitudes_pendientes') }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>

                {{-- Periodo (fecha de captura) --}}
                <div class="nt-periodo">
                    <div>
                        <label>Fecha de captura</label>
                        <div class="nt-atajos">
                            <a class="nt-atajo {{ ! $hayPeriodo ? 'is-activo' : '' }}" href="{{ $url(['desde' => null, 'hasta' => null]) }}">Todo</a>
                            @foreach ($periodos as $etiqueta => [$d, $h])
                                <a class="nt-atajo {{ $desde === $d && $hasta === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h]) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="spDesde">Desde</label>
                        <input type="date" id="spDesde" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div>
                        <label for="spHasta">Hasta</label>
                        <input type="date" id="spHasta" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                </div>
            </form>

            <div class="tn-tarjeta mt-3">
                @if ($solicitudes->isEmpty())
                    <div class="nt-vacio">
                        <i class="bi bi-inbox" aria-hidden="true"></i>
                        No hay solicitudes pendientes con estos filtros.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover nt-tabla" id="spTabla">
                            <thead>
                                <tr>
                                    <th>Fecha Captura</th>
                                    <th>Solicitante</th>
                                    <th>Rama Industrial</th>
                                    <th>Actividad Económica</th>
                                    <th>Patronal / Trabajador</th>
                                    <th>Estatus</th>
                                    <th>Tipo Solicitud</th>
                                    <th>Delegación</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($solicitudes as $solicitud)
                                    <tr>
                                        <td><span class="nt-nue">{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y') }}</span></td>
                                        <td><span class="nt-citado" title="{{ $solicitud->nombre }}">{{ $solicitud->nombre }}</span></td>
                                        <td><div class="sp-texto">{{ $solicitud->rama_industrial }}</div></td>
                                        <td><div class="sp-texto">{{ $solicitud->actividad }}</div></td>
                                        <td>{{ $tiposSolicitud[(string) $solicitud->tipo_solicitud] ?? '' }}</td>
                                        <td>
                                            <span class="nt-estatus {{ $solicitud->estatus === 'Prevencion' ? 'nt-estatus--rojo' : 'nt-estatus--ambar' }}">{{ $solicitud->estatus }}</span>
                                        </td>
                                        <td>
                                            @if ($solicitud->tipo_generacion == 0)
                                                <span class="nt-tipo">Solicitud en línea</span>
                                            @elseif ($solicitud->tipo_generacion == 1000)
                                                <span class="nt-tipo">Solicitud en tablet</span>
                                            @else
                                                <span class="nt-tipo">Personal Centro</span>
                                            @endif
                                        </td>
                                        <td>{{ $solicitud->delegacion }}</td>
                                        <td>
                                            <div class="nt-acciones">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('solicitud_editar', $solicitud->id) }}" onclick="editar_usuario();">
                                                    <i class="bi bi-eye"></i> Revisar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="nt-pie">
                        <span class="nt-por">
                            Mostrando {{ number_format($solicitudes->firstItem()) }}–{{ number_format($solicitudes->lastItem()) }} de {{ number_format($solicitudes->total()) }}
                            ·
                            <select class="form-select form-select-sm" id="spPorPagina" aria-label="Filas por página">
                                @foreach ($porPagina as $p)
                                    <option value="{{ $p }}" @selected($solicitudes->perPage() === $p)>{{ $p }} por página</option>
                                @endforeach
                            </select>
                        </span>
                        @include('turnos._paginacion', ['pagina' => $solicitudes])
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_usuario" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
    <script>
    (function () {
        'use strict';
        var filtros = document.getElementById('spFiltros');
        document.querySelectorAll('#spFiltros .nt-auto').forEach(function (s) {
            s.addEventListener('change', function () { filtros.submit(); });
        });
        var por = document.getElementById('spPorPagina');
        if (por) por.addEventListener('change', function () {
            var u = new URL(location.href);
            u.searchParams.set('por', this.value);
            u.searchParams.delete('page');
            location.href = u.toString();
        });
    })();
    </script>
@endsection
