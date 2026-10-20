@extends('layouts.app')
@section('title', 'Incidencias')

{{--
    Panel estadístico → Incidencias: retrocesos, correcciones y borrados hechos
    a mano en ratificaciones, solicitudes, audiencias y cumplimientos, con
    quién, cuándo y por qué (App\Support\BitacoraIncidencias).
--}}

@use('App\Support\BitacoraIncidencias', 'BI')
@php
    $url = fn (array $cambios) => route('estadisticas.incidencias', array_filter(array_merge($filtros, $cambios)));
    $esSuper = auth()->user()?->hasRole('Super Usuario');
    $mesesLargos = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $totalPeriodo = array_sum(array_column($porArea, 'total'));
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ic-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
        .ic-filtros > div { display: flex; flex-direction: column; }
        .ic-filtros label { font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 4px; }
        .ic-filtros .form-control, .ic-filtros .form-select { min-height: 40px; border-radius: 10px; }
        .ic-filtros .ic-buscar { flex: 1 1 200px; }
        .ic-filtros .btn { min-height: 40px; border-radius: 10px; }
        .ic-nota { font-size: 12px; color: var(--tn-tenue); margin: 10px 0 0; }

        .ic-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-top: 14px; }
        .ic-cifra { padding: 14px 16px; border-radius: 12px; background: #fff; border: 1px solid var(--tn-borde); }
        .ic-cifra b { display: block; font-size: 28px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .ic-cifra span { display: block; font-size: 12.5px; color: var(--tn-suave); margin-top: 4px; }

        .ic-par { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 14px; margin-top: 14px; }
        .ic-cabeza { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
        .ic-cabeza .tn-titulo-seccion { margin: 0; }
        .ic-leyenda { display: flex; flex-wrap: wrap; gap: 10px; font-size: 11.5px; color: var(--tn-suave); }
        .ic-leyenda i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 4px; vertical-align: -1px; }

        .ic-columnas { display: flex; align-items: flex-end; gap: 3px; height: 170px; padding-top: 18px; border-bottom: 1px solid var(--tn-borde); }
        .ic-col { flex: 1 1 0; min-width: 0; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: stretch; text-decoration: none; position: relative; border-radius: 6px 6px 0 0; }
        .ic-col:hover { background: #F4F7F7; }
        .ic-col.is-activa { background: #FBF4E1; }
        .ic-col__num { font-size: 11px; font-weight: 800; color: var(--tn-tinta); text-align: center; font-variant-numeric: tabular-nums; margin-bottom: 2px; }
        .ic-col__pila { display: flex; flex-direction: column-reverse; border-radius: 4px 4px 0 0; overflow: hidden; margin: 0 2px; }
        .ic-col__pila span { display: block; }
        .ic-etiquetas { display: flex; gap: 3px; margin-top: 4px; }
        .ic-etiquetas span { flex: 1 1 0; min-width: 0; text-align: center; font-size: 10.5px; color: var(--tn-tenue); white-space: nowrap; overflow: visible; }
        .ic-columnas--dias .ic-col__num { font-size: 9.5px; }

        .ic-barras { display: flex; flex-direction: column; gap: 9px; }
        .ic-barra__cabeza { display: flex; justify-content: space-between; font-size: 13px; color: var(--tn-tinta); gap: 8px; }
        .ic-barra__cabeza a { color: inherit; text-decoration: none; }
        .ic-barra__cabeza a:hover { text-decoration: underline; }
        .ic-barra { height: 10px; border-radius: 99px; background: #EEF2F2; overflow: hidden; margin-top: 3px; }
        .ic-barra span { display: block; height: 100%; border-radius: 99px; background: var(--tn-verde); }

        .ic-tabla { width: 100%; font-size: 13px; border-collapse: separate; border-spacing: 0; }
        .ic-tabla th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); font-weight: 700; padding: 9px 10px; border-bottom: 1px solid var(--tn-borde); white-space: nowrap; }
        .ic-tabla td { padding: 10px; border-bottom: 1px solid #F0F3F3; vertical-align: top; color: var(--tn-tinta); }
        .ic-tabla .num { text-align: right; font-variant-numeric: tabular-nums; }
        .ic-tabla small { display: block; color: var(--tn-tenue); font-size: 11.5px; }
        .ic-tabla a { color: var(--tn-verde); }
        .ic-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; color: #fff; }
        .ic-motivo { min-width: 200px; max-width: 360px; overflow-wrap: anywhere; }
        .ic-tabla--registro { min-width: 860px; }
        .ic-cambios { margin: 4px 0 0; padding: 0; list-style: none; font-size: 11.5px; color: var(--tn-suave); }
        .ic-activos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .ic-activo { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: #FBF4E1; color: #6B5210; font-size: 12px; font-weight: 600; text-decoration: none; }
        .ic-activo:hover { color: #4F3C08; background: #F6EAC6; }
        @media (max-width: 576px) {
            .ic-par { grid-template-columns: minmax(0, 1fr); }
            .ic-columnas { height: 140px; }
            .ic-columnas--dias .ic-col__num { display: none; }
            .ic-et-5 { visibility: hidden; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ route('estadisticas.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Incidencias</h3>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('estadisticas.incidencias.csv', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i> Descargar CSV</a>
                @if ($esSuper && \Illuminate\Support\Facades\Route::has('incidencias.busqueda.index'))
                    <a href="{{ route('incidencias.busqueda.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-flag me-1"></i> Marcar expediente</a>
                @endif
            </div>
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('estadisticas.incidencias') }}" class="tn-tarjeta">
                <div class="ic-filtros">
                    <div>
                        <label for="icAnio">Año</label>
                        <select id="icAnio" name="anio" class="form-select">
                            @foreach ($anios as $a)
                                <option value="{{ $a }}" @selected($filtros['anio'] === $a)>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icMes">Mes</label>
                        <select id="icMes" name="mes" class="form-select">
                            <option value="">Todo el año</option>
                            @foreach ($mesesLargos as $i => $m)
                                <option value="{{ $i + 1 }}" @selected($filtros['mes'] === $i + 1)>{{ ucfirst($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icDia">Día</label>
                        <input type="date" id="icDia" name="dia" class="form-control" value="{{ $filtros['dia'] }}" max="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label for="icArea">Área</label>
                        <select id="icArea" name="area" class="form-select">
                            <option value="">Todas</option>
                            @foreach (BI::AREAS as $clave => $nombre)
                                <option value="{{ $clave }}" @selected($filtros['area'] === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icAccion">Movimiento</label>
                        <select id="icAccion" name="accion" class="form-select">
                            <option value="">Todos</option>
                            @foreach (BI::ACCIONES as $clave => $nombre)
                                <option value="{{ $clave }}" @selected($filtros['accion'] === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icUsuario">Quién</label>
                        <select id="icUsuario" name="usuario" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($usuarios as $id => $nombre)
                                <option value="{{ $id }}" @selected($filtros['usuario'] === (int) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icSede">Sede</label>
                        <select id="icSede" name="sede" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($sedes as $s)
                                <option value="{{ $s }}" @selected($filtros['sede'] === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ic-buscar">
                        <label for="icQ">Expediente o motivo</label>
                        <input type="search" id="icQ" name="q" class="form-control" value="{{ $filtros['q'] }}" maxlength="60" placeholder="MOR/SOL/2026/…">
                    </div>
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Ver</button>
                    @if (request()->query())
                        <a href="{{ route('estadisticas.incidencias') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
                <p class="ic-nota">Retrocesos, cambios de fecha, cumplimientos borrados, ediciones de ratificación y expedientes marcados como incidencia. Cada uno con quién lo hizo, cuándo y por qué.</p>
            </form>

            <div class="ic-cifras">
                <div class="ic-cifra"><b>{{ number_format($cifras['hoy']) }}</b><span>Hoy</span></div>
                <div class="ic-cifra"><b>{{ number_format($cifras['mes']) }}</b><span>En {{ $mesesLargos[now()->month - 1] }}</span></div>
                <div class="ic-cifra"><b>{{ number_format($cifras['anio']) }}</b><span>En {{ now()->year }}</span></div>
                <div class="ic-cifra"><b>{{ number_format($cifras['usuarios']) }}</b><span>{{ $cifras['usuarios'] === 1 ? 'Persona las hizo' : 'Personas las hicieron' }} en {{ now()->year }}</span></div>
            </div>

            <div class="ic-par">
                {{-- Por mes del año --}}
                <div class="tn-tarjeta">
                    <div class="ic-cabeza">
                        <p class="tn-titulo-seccion">Por mes · {{ $filtros['anio'] }}</p>
                        <span class="ic-nota mt-0">Toca un mes para verlo</span>
                    </div>
                    <div class="ic-columnas" role="img" aria-label="Incidencias por mes de {{ $filtros['anio'] }}">
                        @foreach ($porMes['columnas'] as $n => $c)
                            <a class="ic-col {{ $filtros['mes'] === $n && ! $filtros['dia'] ? 'is-activa' : '' }}" href="{{ $url(['mes' => $n, 'dia' => null]) }}" title="{{ ucfirst($mesesLargos[$n - 1]) }}: {{ $c['total'] }}">
                                @if ($c['total'])<span class="ic-col__num">{{ $c['total'] }}</span>@endif
                                <span class="ic-col__pila" style="height: {{ round($c['total'] / $porMes['maximo'] * 100, 1) }}%;">
                                    @foreach (BI::AREAS as $clave => $nombre)
                                        @if (! empty($c['areas'][$clave]))<span style="flex: {{ $c['areas'][$clave] }} 1 0; background: {{ BI::COLORES[$clave] }};"></span>@endif
                                    @endforeach
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <div class="ic-etiquetas">@foreach ($porMes['columnas'] as $c)<span>{{ $c['etiqueta'] }}</span>@endforeach</div>
                </div>

                {{-- Por día del mes --}}
                <div class="tn-tarjeta">
                    <div class="ic-cabeza">
                        <p class="tn-titulo-seccion">Por día · {{ ucfirst($mesesLargos[$mesDias - 1]) }} {{ $filtros['anio'] }}</p>
                        <span class="ic-nota mt-0">Toca un día para verlo</span>
                    </div>
                    <div class="ic-columnas ic-columnas--dias" role="img" aria-label="Incidencias por día">
                        @foreach ($porDia['columnas'] as $n => $c)
                            @php($fechaDia = sprintf('%04d-%02d-%02d', $filtros['anio'], $mesDias, $n))
                            <a class="ic-col {{ $filtros['dia'] === $fechaDia ? 'is-activa' : '' }}" href="{{ $url(['dia' => $fechaDia]) }}" title="{{ $n }} de {{ $mesesLargos[$mesDias - 1] }}: {{ $c['total'] }}">
                                @if ($c['total'])<span class="ic-col__num">{{ $c['total'] }}</span>@endif
                                <span class="ic-col__pila" style="height: {{ round($c['total'] / $porDia['maximo'] * 100, 1) }}%;">
                                    @foreach (BI::AREAS as $clave => $nombre)
                                        @if (! empty($c['areas'][$clave]))<span style="flex: {{ $c['areas'][$clave] }} 1 0; background: {{ BI::COLORES[$clave] }};"></span>@endif
                                    @endforeach
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <div class="ic-etiquetas">@foreach ($porDia['columnas'] as $n => $c)<span class="{{ $n === 1 || $n % 10 === 0 ? '' : 'ic-et-5' }}">{{ $n === 1 || $n % 5 === 0 ? $n : '' }}</span>@endforeach</div>
                </div>
            </div>
            <div class="ic-leyenda mt-2 mb-4">
                @foreach (BI::AREAS as $clave => $nombre)
                    <span><i style="background: {{ BI::COLORES[$clave] }};"></i>{{ $nombre }}</span>
                @endforeach
            </div>

            <h3 class="tn-titulo-seccion mt-4">{{ $texto }} · {{ number_format($totalPeriodo) }} {{ $totalPeriodo === 1 ? 'incidencia' : 'incidencias' }}</h3>
            @if ($filtros['mes'] || $filtros['dia'])
                <div class="ic-activos mb-2">
                    @if ($filtros['dia'])<a class="ic-activo" href="{{ $url(['dia' => null]) }}">{{ $texto }} <i class="bi bi-x-lg"></i></a>
                    @elseif ($filtros['mes'])<a class="ic-activo" href="{{ $url(['mes' => null]) }}">{{ $texto }} <i class="bi bi-x-lg"></i></a>@endif
                </div>
            @endif

            <div class="ic-par mt-2">
                <div class="tn-tarjeta">
                    <p class="tn-titulo-seccion">Por área</p>
                    <div class="ic-barras">
                        @php($maxArea = max(1, ...array_column($porArea, 'total')))
                        @foreach ($porArea as $a)
                            <div>
                                <div class="ic-barra__cabeza"><a href="{{ $url(['area' => $a['clave']]) }}">{{ $a['nombre'] }}</a><b>{{ number_format($a['total']) }}</b></div>
                                <div class="ic-barra"><span style="width: {{ round($a['total'] / $maxArea * 100, 1) }}%; background: {{ BI::COLORES[$a['clave']] }};"></span></div>
                            </div>
                        @endforeach
                    </div>
                    @if ($porAccion)
                        <p class="tn-titulo-seccion mt-4">Por movimiento</p>
                        <div class="ic-barras">
                            @php($maxAccion = max(1, ...array_column($porAccion, 'total')))
                            @foreach ($porAccion as $a)
                                <div>
                                    <div class="ic-barra__cabeza"><a href="{{ $url(['accion' => $a['clave']]) }}">{{ $a['nombre'] }}</a><b>{{ number_format($a['total']) }}</b></div>
                                    <div class="ic-barra"><span style="width: {{ round($a['total'] / $maxAccion * 100, 1) }}%;"></span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tn-tarjeta">
                    <p class="tn-titulo-seccion">Quién las hace</p>
                    @if (! $porUsuario)
                        <p class="ic-nota">Nadie registró incidencias en este periodo.</p>
                    @else
                        <div class="table-responsive">
                            <table class="ic-tabla">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        @foreach (BI::AREAS as $clave => $nombre)<th class="num" title="{{ $nombre }}">{{ \Illuminate\Support\Str::limit($nombre, 5, '.') }}</th>@endforeach
                                        <th class="num">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($porUsuario as $u)
                                        <tr>
                                            <td>
                                                @if ($u['id'])<a href="{{ $url(['usuario' => $u['id']]) }}"><b>{{ $u['nombre'] }}</b></a>@else<b>{{ $u['nombre'] }}</b>@endif
                                                <small>Última: {{ $u['ultima']->locale('es')->isoFormat('D MMM YYYY, HH:mm') }}</small>
                                            </td>
                                            @foreach (BI::AREAS as $clave => $nombre)<td class="num">{{ $u['areas'][$clave] ?? '' }}</td>@endforeach
                                            <td class="num"><b>{{ number_format($u['total']) }}</b></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($porSede)
                        <p class="tn-titulo-seccion mt-4">Por sede</p>
                        <div class="ic-barras">
                            @php($maxSede = max(1, ...array_values($porSede)))
                            @foreach ($porSede as $sede => $n)
                                <div>
                                    <div class="ic-barra__cabeza"><a href="{{ $url(['sede' => $sede]) }}">{{ $sede }}</a><b>{{ number_format($n) }}</b></div>
                                    <div class="ic-barra"><span style="width: {{ round($n / $maxSede * 100, 1) }}%;"></span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bitácora --}}
            <div class="tn-tarjeta mt-3">
                <div class="ic-cabeza">
                    <p class="tn-titulo-seccion">Registro</p>
                    <span class="ic-nota mt-0">{{ number_format($registro->total()) }} {{ $registro->total() === 1 ? 'movimiento' : 'movimientos' }}, del más reciente al más antiguo</span>
                </div>
                @if ($registro->isEmpty())
                    <p class="text-center mb-0 py-4" style="color: var(--tn-tenue);">No hay incidencias con esos filtros.</p>
                @else
                    <div class="table-responsive">
                        <table class="ic-tabla ic-tabla--registro">
                            <thead>
                                <tr><th>Fecha</th><th>Área</th><th>Qué pasó</th><th>Expediente</th><th>Motivo</th><th>Lo hizo</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($registro as $r)
                                    @php($cuando = \Illuminate\Support\Carbon::parse($r->created_at))
                                    @php($caso = BI::urlCaso($r))
                                    <tr>
                                        <td style="white-space: nowrap;">{{ \Illuminate\Support\Str::ucfirst($cuando->locale('es')->isoFormat('D MMM YYYY')) }}<small>{{ $cuando->format('H:i') }}</small></td>
                                        <td><span class="ic-chip" style="background: {{ BI::COLORES[$r->area] ?? '#6B7A7B' }};">{{ BI::AREAS[$r->area] ?? $r->area }}</span></td>
                                        <td>
                                            <b>{{ BI::ACCIONES[$r->accion] ?? $r->accion }}</b>
                                            @if ($r->estatus_previo || $r->estatus_nuevo)
                                                <small>{{ $r->estatus_previo ?: '—' }}@if ($r->estatus_nuevo) → {{ $r->estatus_nuevo }}@endif</small>
                                            @endif
                                            @if ($cambios = BI::cambios($r))
                                                <ul class="ic-cambios">@foreach ($cambios as $c)<li>{{ $c }}</li>@endforeach</ul>
                                            @endif
                                            @if ($r->origen === 'retroceso' && $esSuper && \Illuminate\Support\Facades\Route::has('retrocesos_historial_detalle'))
                                                <small><a href="{{ route('retrocesos_historial_detalle', $r->origen_id) }}">Qué se borró o cambió</a></small>
                                            @endif
                                        </td>
                                        <td style="white-space: nowrap;">
                                            @if ($caso)<a href="{{ $caso }}">{{ $r->NUE ?: 'Ver caso' }}</a>@else{{ $r->NUE ?: '—' }}@endif
                                            <small>{{ $r->delegacion }}</small>
                                        </td>
                                        <td class="ic-motivo">{{ $r->motivo ?: '—' }}</td>
                                        <td>{{ $r->user_nombre ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">@include('turnos._paginacion', ['pagina' => $registro])</div>
                @endif
                @if ($marcadas['solicitudes'] || $marcadas['ratificaciones'])
                    <p class="ic-nota mb-0"><i class="bi bi-flag"></i> Hoy hay {{ number_format($marcadas['solicitudes']) }} {{ $marcadas['solicitudes'] === 1 ? 'solicitud' : 'solicitudes' }} y {{ number_format($marcadas['ratificaciones']) }} {{ $marcadas['ratificaciones'] === 1 ? 'ratificación' : 'ratificaciones' }} marcadas como incidencia (no cuentan en los reportes). Las que se marcaron antes de esta bitácora no tienen quién ni cuándo.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
