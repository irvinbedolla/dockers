@extends('layouts.app')
@section('title', 'Seguimiento de casos')

{{--
    Seguimiento: todos los casos de sus sedes, del más reciente al más
    antiguo (App\Support\SeguimientoCaso::tabla). Cada renglón abre la línea
    de tiempo del caso (seguimiento/ver).
--}}

@php
    $fecha = fn ($c) => $c ? \Illuminate\Support\Str::ucfirst($c->locale('es')->isoFormat('D MMM YYYY')) : '—';
@endphp

@section('page_css')
    @include('turnos._estilos')
    @include('seguimiento._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ $soloExcepcion ? route('excepcion') : route('solicitudes_index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> {{ $soloExcepcion ? 'Casos de excepción' : 'Solicitudes' }}
                </a>
                <h3 class="page__heading mb-0">Seguimiento de casos</h3>
                @if ($soloExcepcion)
                    <span class="sg-chip sg-chip--excepcion"><i class="bi bi-shield-exclamation"></i> Sólo casos de excepción, todas las sedes</span>
                @endif
            </div>
            <span class="small" style="color: var(--tn-tenue);">{{ number_format($casos->total()) }} {{ $casos->total() === 1 ? 'caso' : 'casos' }}</span>
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('seguimiento') }}" class="tn-tarjeta mb-3" role="search">
                <div class="sg-filtros">
                    <div class="sg-filtros__buscar">
                        <label for="sgQ">Buscar</label>
                        <input type="search" id="sgQ" name="q" class="form-control" value="{{ $filtros['q'] }}" maxlength="80"
                               placeholder="Folio de cita (MOR-S26-0142), NUE, CURP o nombre">
                    </div>
                    @if (count($sedes) > 1)
                        <div>
                            <label for="sgSede">Sede</label>
                            <select id="sgSede" name="sede" class="form-select" onchange="this.form.submit()">
                                <option value="">Todas</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected($filtros['sede'] === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="sgEstado">Estado</label>
                        <select id="sgEstado" name="estado" class="form-select" onchange="this.form.submit()">
                            @foreach ($estados as $valor => $texto)
                                <option value="{{ $valor }}" @selected($filtros['estado'] === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-search me-1"></i> Buscar</button>
                    @if (array_filter($filtros))
                        <a href="{{ route('seguimiento') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="tn-tarjeta">
                @if ($casos->isEmpty())
                    <p class="text-center mb-0 py-4" style="color: var(--tn-tenue);">No hay casos con esos filtros.</p>
                @else
                    <div class="table-responsive">
                        <table class="sg-tabla">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Persona</th>
                                    <th>Cita</th>
                                    <th>Expediente</th>
                                    <th>Estado</th>
                                    <th>Lo atendió</th>
                                    <th>Próxima audiencia</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($casos as $c)
                                    @php($url = route('seguimiento.ver', $c['clave']))
                                    <tr onclick="if (!event.target.closest('a')) { window.location = '{{ $url }}'; }">
                                        <td style="white-space:nowrap;">{{ $fecha($c['creado']) }}<small>{{ $c['creado'] ? $c['creado']->format('H:i') : '' }}</small></td>
                                        <td class="sg-celda-persona"><b>{{ $c['persona'] }}</b><small><i class="bi bi-geo-alt"></i> {{ $c['sede'] }}</small></td>
                                        <td>
                                            @if ($c['folio'])
                                                {{ $c['folio'] }}
                                                <small><span class="sg-origen {{ $c['origen'] === 'En línea' ? 'sg-origen--linea' : '' }}">{{ $c['origen'] }}</span>@if ($c['creo']) · {{ $c['creo'] }}@endif</small>
                                            @else
                                                <span style="color: var(--tn-tenue);">Sin cita</span>
                                            @endif
                                        </td>
                                        <td>{{ $c['nue'] ?: '—' }}</td>
                                        <td>
                                            <span class="sg-chip sg-chip--{{ $c['tono'] }}">{{ $c['estado'] }}</span>
                                            @if ($c['excepcion'])
                                                <small class="mt-1"><span class="sg-chip sg-chip--excepcion" title="Caso de excepción"><i class="bi bi-shield-exclamation"></i> {{ $c['excepcion'] }}</span></small>
                                            @endif
                                        </td>
                                        <td>{{ $c['atendio'] ?: '—' }}</td>
                                        <td>
                                            @if ($c['proxima'])
                                                {{ $fecha($c['proxima']['cuando']) }} · {{ $c['proxima']['cuando']->format('H:i') }}
                                                <small>{{ $c['proxima']['conciliador'] ?: 'Sin conciliador' }}</small>
                                            @else
                                                <span style="color: var(--tn-tenue);">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end"><a href="{{ $url }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="small" style="color: var(--tn-tenue);">{{ number_format($casos->firstItem()) }}–{{ number_format($casos->lastItem()) }} de {{ number_format($casos->total()) }}</span>
                        @include('turnos._paginacion', ['pagina' => $casos])
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
