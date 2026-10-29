@extends('layouts.app')
@section('title', 'Casos de excepción')
{{--
    Bandeja de casos de excepción (CasosExcepcionController::index). La ve el
    Departamento de Conciliación, Género e Igualdad (atiende y canaliza) y,
    sólo para consulta, Directivo, Delegado y Excepcion. Todas las sedes.
--}}
@php
    $hoy = now()->toDateString();
    $periodos = [
        'Hoy'         => [$hoy, $hoy],
        'Esta semana' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
        'Este mes'    => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        'Mes pasado'  => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
    ];
    $con = fn (array $cambios) => route('excepcion', array_filter(array_merge(
        array_intersect_key($filtros, array_flip(['desde', 'hasta', 'sede', 'origen', 'buscar'])),
        ['vista' => $vista],
        $cambios
    ), fn ($v) => $v !== null && $v !== ''));
    $fechaCorta = fn ($f) => \Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('ddd D MMM');
    $textoPeriodo = $filtros['desde'] === $filtros['hasta']
        ? \Illuminate\Support\Carbon::parse($filtros['desde'])->locale('es')->isoFormat('dddd D [de] MMMM YYYY')
        : $fechaCorta($filtros['desde']).' – '.$fechaCorta($filtros['hasta']);
    $titulo = fn ($t) => \Illuminate\Support\Str::title(mb_strtolower((string) $t));
    $estatusCita = ['pendiente' => 'Por llegar', 'confirmada' => 'En sala', 'atendido' => 'Atendida', 'expirada' => 'No llegó'];
    // Estado de la atención (formato 685 Ter): terminado con su resultado, borrador o pendiente.
    $atencion = function ($f) {
        if (! empty($f->terminado_at)) {
            return match ($f->resultado ?? null) {
                'continua' => ['cx-caso--hecho', 'bi-check2-circle', 'Continúa conciliación'],
                'canaliza' => ['cx-caso--canal', 'bi-signpost-split', 'Canalizada'],
                'incompetente' => ['cx-caso--incomp', 'bi-x-octagon', 'No competentes'],
                'no_excepcion' => ['cx-caso--borrador', 'bi-slash-circle', 'No era excepción'],
                default    => ['cx-caso--hecho', 'bi-check2-circle', 'Atendido'],
            };
        }

        return ! empty($f->formato_id)
            ? ['cx-caso--borrador', 'bi-pencil', 'Borrador']
            : ['cx-caso--pend', 'bi-hourglass-split', 'Por atender'];
    };
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .cx-pestanas { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .cx-pestana {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px;
            border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-weight: 600; font-size: 13.5px; text-decoration: none;
        }
        .cx-pestana:hover { border-color: var(--tn-verde-claro); color: var(--tn-tinta); }
        .cx-pestana.is-activa { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); color: var(--tn-tinta); }
        .cx-pestana b { min-width: 24px; padding: 1px 8px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; text-align: center; }
        .cx-pestana--alerta b { background: #F6E4EA; color: #8C1D40; }
        .cx-enlace { display: inline-flex; align-items: center; gap: 5px; margin-left: 8px; padding: 2px 10px; border-radius: 999px; background: #F3E8F7; color: #6B2F86; font-size: 11.5px; font-weight: 700; white-space: nowrap; }

        .cx-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .cx-filtros label, .cx-etq { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .cx-filtros .form-control, .cx-filtros .form-select { min-width: 150px; }
        .cx-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .cx-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .cx-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .cx-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .cx-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 8px; }
        .cx-cabeza h4 { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .cx-cabeza span { font-size: 12.5px; color: var(--tn-tenue); }

        .cx-tabla.table:not(.table-sm) thead th, .cx-tabla thead th {
            --bs-table-bg: #354647; background: #354647; color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; border: 0; padding-top: 11px; padding-bottom: 11px;
        }
        .cx-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
        .cx-sub { display: block; font-size: 11.5px; color: var(--tn-tenue); }
        .cx-nombre { font-weight: 700; }
        .cx-caso { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .cx-caso--pend { background: #FBF4E1; color: #7A5D10; }
        .cx-caso--hecho { background: #E5F3EA; color: #1E7B3C; }
        .cx-caso--canal { background: #F6E4EA; color: #8C1D40; }
        .cx-caso--incomp { background: #ECEAF4; color: #4B3F7A; }
        .cx-caso--borrador { background: #EEF2F2; color: #5E6E6F; }
        .cx-acciones { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
        .cx-vacio { padding: 30px 10px; text-align: center; color: var(--tn-tenue); }
        .cx-vacio .bi { display: block; font-size: 26px; margin-bottom: 6px; color: #C5CFCF; }

        /* Pulso verde: hay una solicitud que revisar. Va en un ::after para no
           pisar el borde/sombra del botón (p. ej. la pestaña activa). */
        .cx-pulso { position: relative; border-color: #1E7B3C !important; }
        .cx-pulso::after {
            content: ''; position: absolute; inset: -1px; border-radius: inherit; pointer-events: none;
            animation: cxPulso 1.8s ease-out infinite;
        }
        @keyframes cxPulso {
            0%   { box-shadow: 0 0 0 0 rgba(30, 123, 60, .55); }
            70%  { box-shadow: 0 0 0 9px rgba(30, 123, 60, 0); }
            100% { box-shadow: 0 0 0 0 rgba(30, 123, 60, 0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .cx-pulso::after { animation: none; box-shadow: 0 0 0 3px rgba(30, 123, 60, .35); }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Casos de excepción</h3>
                <span class="small" style="color: var(--tn-tenue);">Art. 685 Ter LFT · Departamento de Conciliación, Género e Igualdad · todas las sedes</span>
                @if (\App\Support\CasosExcepcion::enlace(auth()->user()))
                    <span class="cx-enlace" title="Apoyas al departamento desde {{ auth()->user()->delegacion }}"><i class="bi bi-person-badge"></i> {{ \App\Support\CasosExcepcion::DISTINTIVO }} · {{ auth()->user()->delegacion }}</span>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
            @if ($atiende)
                @foreach (array_intersect(\App\Support\Recepcion::tramitesQuePuedeCrear(auth()->user()), \App\Support\CasosExcepcion::TRAMITES_TURNO) as $t)
                    <a class="btn btn-primary btn-sm" href="{{ route('nueva_cita', ['tipo' => $t]) }}"><i class="bi bi-calendar-plus"></i> Turno de {{ mb_strtolower($t) }}</a>
                @endforeach
            @endif
            @if (\Illuminate\Support\Facades\Route::has('estadisticas.excepciones') && \App\Support\CasosExcepcion::estadisticas(auth()->user()))
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.excepciones') }}"><i class="bi bi-bar-chart-line"></i> Estadísticas</a>
            @endif
            </div>
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $tipo)
                @if (session($clave))
                    <div class="alert alert-{{ $tipo }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach

            <nav class="cx-pestanas" aria-label="Bandejas">
                <a class="cx-pestana {{ $vista === 'citas' ? 'is-activa' : '' }}" href="{{ $con(['vista' => 'citas']) }}">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i> Citas y turnos <b>{{ number_format($conteos['citas']) }}</b>
                </a>
                <a class="cx-pestana {{ $conteos['pendientes'] ? 'cx-pestana--alerta' : '' }} {{ $vista === 'pendientes' ? 'is-activa' : '' }}" href="{{ $con(['vista' => 'pendientes', 'desde' => null, 'hasta' => null]) }}">
                    <i class="bi bi-hourglass-split" aria-hidden="true"></i> Por atender <b>{{ number_format($conteos['pendientes']) }}</b>
                </a>
                <a class="cx-pestana {{ $vista === 'solicitudes' ? 'is-activa' : '' }} {{ $conteos['solicitudes'] ? 'cx-pulso' : '' }}" href="{{ $con(['vista' => 'solicitudes', 'desde' => null, 'hasta' => null]) }}"
                   @if ($conteos['solicitudes']) title="Hay {{ $conteos['solicitudes'] }} {{ $conteos['solicitudes'] === 1 ? 'solicitud' : 'solicitudes' }} con caso de excepción" @endif>
                    <i class="bi bi-folder2-open" aria-hidden="true"></i> Solicitudes <b>{{ number_format($conteos['solicitudes']) }}</b>
                </a>
                <button type="button" class="cx-pestana" data-bs-toggle="modal" data-bs-target="#cxExpModal" title="Buscar entre todos los expedientes de solicitudes">
                    <i class="bi bi-search" aria-hidden="true"></i> Buscar expedientes
                </button>
            </nav>
            @include('excepciones._buscar_expedientes')

            <form method="GET" action="{{ route('excepcion') }}" class="tn-tarjeta mb-3" id="cxFiltros">
                <input type="hidden" name="vista" value="{{ $vista }}">
                <div class="cx-filtros">
                    @if ($vista !== 'pendientes')
                        <div>
                            <span class="cx-etq">Periodo</span>
                            <div class="cx-atajos">
                                @foreach ($periodos as $etiqueta => [$d, $h])
                                    <a class="cx-atajo {{ $filtros['desde'] === $d && $filtros['hasta'] === $h ? 'is-activo' : '' }}" href="{{ $con(['desde' => $d, 'hasta' => $h]) }}">{{ $etiqueta }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label for="cxDesde">Desde</label>
                            <input type="date" id="cxDesde" name="desde" class="form-control" value="{{ $filtros['desde'] }}">
                        </div>
                        <div>
                            <label for="cxHasta">Hasta</label>
                            <input type="date" id="cxHasta" name="hasta" class="form-control" value="{{ $filtros['hasta'] }}">
                        </div>
                    @endif
                    <div>
                        <label for="cxSede">Sede</label>
                        <select id="cxSede" name="sede" class="form-select cx-auto">
                            <option value="">Todas las sedes</option>
                            @foreach (\App\Support\CasosExcepcion::SEDES as $s)
                                <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($vista !== 'solicitudes')
                        <div>
                            <label for="cxOrigen">Origen</label>
                            <select id="cxOrigen" name="origen" class="form-select cx-auto">
                                <option value="">En línea y ventanilla</option>
                                <option value="linea" @selected(($filtros['origen'] ?? '') === 'linea')>Cita en línea</option>
                                <option value="ventanilla" @selected(($filtros['origen'] ?? '') === 'ventanilla')>Ventanilla</option>
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="cxBuscar">Buscar</label>
                        <input type="search" id="cxBuscar" name="buscar" class="form-control" value="{{ $filtros['buscar'] ?? '' }}"
                               placeholder="{{ $vista === 'solicitudes' ? 'Expediente o nombre' : 'Nombre, folio o CURP' }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </div>
            </form>

            <div class="tn-tarjeta">
                <div class="cx-cabeza">
                    <h4>
                        @switch($vista)
                            @case('pendientes') Por atender @break
                            @case('solicitudes') Solicitudes con caso de excepción @break
                            @default Citas y turnos con caso de excepción
                        @endswitch
                    </h4>
                    <span>
                        {{ $vista === 'pendientes' ? 'Últimos 30 días, sin formato terminado' : $textoPeriodo }}
                        · {{ number_format($filas->total()) }} {{ $filas->total() === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>

                @if ($filas->isEmpty())
                    <div class="cx-vacio"><i class="bi bi-inbox" aria-hidden="true"></i>No hay casos con estos filtros.</div>
                @elseif ($vista === 'solicitudes')
                    <div class="table-responsive">
                        <table class="table table-hover cx-tabla mb-0">
                            <thead>
                                <tr><th>Expediente</th><th>Fecha</th><th>Sede</th><th>Solicitante</th><th>Citado</th><th>Estatus</th><th>Atención</th><th class="text-end">Acciones</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($filas as $f)
                                    <tr>
                                        <td class="cx-nombre">{{ $f->nue ?: 'Sin expediente' }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($f->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $f->delegacion }}</td>
                                        <td>{{ $titulo($f->solicitante) ?: '—' }}</td>
                                        <td>
                                            {{ $f->citado ?: '—' }}
                                            @if (($f->citados ?? 0) > 1)<span class="cx-sub">y {{ $f->citados - 1 }} más</span>@endif
                                        </td>
                                        <td>{{ $f->estatus }}</td>
                                        <td>@php [$clase, $icono, $texto] = $atencion($f); @endphp<span class="cx-caso {{ $clase }}"><i class="bi {{ $icono }}"></i> {{ $texto }}</span></td>
                                        <td>
                                            <div class="cx-acciones">
                                                @if ($atiende)
                                                    <a class="btn btn-sm {{ empty($f->terminado_at) ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('excepciones.formato', ['tipo' => 'solicitud', 'id' => $f->id]) }}">
                                                        <i class="bi bi-clipboard2-pulse"></i> {{ ! empty($f->terminado_at) ? 'Formato' : (! empty($f->formato_id) ? 'Continuar' : 'Atender') }}
                                                    </a>
                                                @endif
                                                @if (! empty($f->formato_id))
                                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('excepciones.formato.pdf', ['tipo' => 'solicitud', 'id' => $f->id]) }}" target="_blank" rel="noopener" title="Formato en PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                                @endif
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('excepciones.solicitud', $f->id) }}"><i class="bi bi-eye"></i> Ver</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover cx-tabla mb-0">
                            <thead>
                                <tr><th>Folio</th><th>Fecha</th><th>Persona</th><th>Trámite</th><th>Sede</th><th>Caso</th><th>Cita</th><th>Atención</th><th class="text-end">Acciones</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($filas as $f)
                                    @php $estatus = strtolower((string) $f->estatus); @endphp
                                    <tr>
                                        <td class="cx-nombre" style="white-space:nowrap">{{ \App\Support\FolioCita::mostrar($f) }}</td>
                                        <td style="white-space:nowrap">
                                            {{ \Illuminate\Support\Carbon::parse($f->fecha)->format('d/m/Y') }}
                                            <span class="cx-sub">{{ substr((string) $f->hora, 0, 5) }} h</span>
                                        </td>
                                        <td>
                                            <span class="cx-nombre">{{ $titulo($f->solicitante) ?: 'Sin nombre' }}</span>
                                            @if ($f->telefono)<span class="cx-sub"><i class="bi bi-telephone"></i> {{ $f->telefono }}</span>@endif
                                        </td>
                                        <td>
                                            {{ $f->tipo }}
                                            <span class="cx-sub">{{ $f->origen === 'linea' ? 'Cita en línea' : 'Ventanilla' }}</span>
                                        </td>
                                        <td>{{ $f->delegacion }}</td>
                                        <td>
                                            {{ $f->tipo_caso ?: 'Sin especificar' }}
                                            @if ($f->vulnerables && ! in_array($f->vulnerables, ['No aplica', 'Ninguno'], true))<span class="cx-sub">{{ $f->vulnerables }}</span>@endif
                                        </td>
                                        <td><span class="tn-estatus tn-estatus--{{ $estatus }}">{{ $estatusCita[$estatus] ?? $f->estatus }}</span></td>
                                        <td>@php [$clase, $icono, $texto] = $atencion($f); @endphp<span class="cx-caso {{ $clase }}"><i class="bi {{ $icono }}"></i> {{ $texto }}</span></td>
                                        <td>
                                            <div class="cx-acciones">
                                                @if ($atiende && ($estatus !== 'expirada' || $f->formato_id))
                                                    <a class="btn btn-sm {{ empty($f->terminado_at) ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('excepciones.formato', ['tipo' => 'cita', 'id' => $f->id]) }}">
                                                        <i class="bi bi-clipboard2-pulse"></i> {{ ! empty($f->terminado_at) ? 'Formato' : ($f->formato_id ? 'Continuar' : 'Atender') }}
                                                    </a>
                                                @endif
                                                @if ($f->formato_id)
                                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('excepciones.formato.pdf', ['tipo' => 'cita', 'id' => $f->id]) }}" target="_blank" rel="noopener" title="Formato en PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                                @endif
                                                @if ($cierraRapido && $estatus === 'pendiente' && ! $f->formato_id && substr((string) $f->fecha, 0, 10) <= $hoy)
                                                    <form method="POST" action="{{ route('excepciones.no_llego', $f->id) }}" class="d-inline"
                                                          onsubmit="return confirm('¿Marcar el folio {{ \App\Support\FolioCita::mostrar($f) }} como «No llegó» y cerrar el caso?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="La persona no llegó: se cierra el caso y sale de Por atender"><i class="bi bi-person-x"></i> No llegó</button>
                                                    </form>
                                                @endif
                                                @if ($f->solicitud_id)
                                                    <a class="btn btn-sm btn-outline-success cx-pulso" href="{{ route('excepciones.solicitud', $f->solicitud_id) }}" title="Ya tiene solicitud: {{ $f->nue ?: 'sin expediente' }}"><i class="bi bi-folder2-open"></i> Solicitud</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-3">@include('turnos._paginacion', ['pagina' => $filas])</div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('#cxFiltros .cx-auto').forEach(function (s) {
            s.addEventListener('change', function () { s.form.submit(); });
        });
    </script>
@endsection
