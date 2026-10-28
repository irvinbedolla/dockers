@extends('layouts.app')
@section('title', 'Atención del trámite de conciliación')

{{--
    /estadisticas/atencion (EstadisticasAtencionController, App\Support\EstadisticasAtencion):
    resultados del formulario de atención del trámite (/atencion-tramite-conciliacion).
--}}

@php
    use App\Support\AtencionTramite as AT;
    use App\Support\EstadisticasAtencion as EA;
    $f = $filtros;
    $hoyC = \Illuminate\Support\Carbon::today();
    $atajos = [
        'Este mes'       => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->copy()->endOfMonth()->toDateString()],
        'Mes pasado'     => [$hoyC->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoyC->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        'Últimos 90 días'=> [$hoyC->copy()->subDays(89)->toDateString(), $hoyC->toDateString()],
        'Este año'       => [$hoyC->copy()->startOfYear()->toDateString(), $hoyC->copy()->endOfYear()->toDateString()],
    ];
    $base = array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'sede' => $f['sede'], 'parte' => $f['parte']]);
    $url = fn (array $c) => route('estadisticas.atencion', array_filter(array_merge($base, $c), fn ($v) => $v !== null && $v !== ''));
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $ind = fn ($v) => $v === null ? '—' : $v;
    $colorInd = fn ($v) => $v === null ? '' : ($v >= 80 ? 'color:#1E7B3C' : ($v >= 60 ? 'color:#7A5D10' : 'color:#9A3A22'));
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ea-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .ea-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .ea-filtros label, .ea-etq { display: block; font-size: 11.5px; font-weight: 700; color: var(--tn-suave); margin-bottom: 5px; }
        .ea-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .ea-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; }
        .ea-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .ea-bloque { margin-top: 16px; }
        .ea-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .ea-titulo { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .ea-sub { font-size: 12.5px; color: var(--tn-tenue); }
        .ea-cifras { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
        @media (max-width: 1300px) { .ea-cifras { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 700px)  { .ea-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ea-cifra { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; background: var(--tn-fondo); }
        .ea-cifra b { display: block; font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; }
        .ea-cifra span { font-size: 12px; color: var(--tn-suave); font-weight: 600; }
        .ea-cifra small { display: block; font-size: 11px; color: var(--tn-tenue); margin-top: 2px; }
        .ea-cifra--alerta { background: #FBEDEA; border-color: #F2D2CA; } .ea-cifra--alerta b { color: #9A3A22; }
        .ea-barra { display: flex; height: 22px; border-radius: 6px; overflow: hidden; background: #EEF2F2; }
        .ea-barra span { display: block; height: 100%; }
        .ea-etapa { display: grid; grid-template-columns: 160px minmax(0, 1fr) 70px; gap: 10px; align-items: center; margin-bottom: 10px; }
        @media (max-width: 700px) { .ea-etapa { grid-template-columns: 1fr 60px; } .ea-etapa .ea-barra { grid-column: 1 / -1; grid-row: 2; } }
        .ea-etapa__nombre { font-weight: 700; color: var(--tn-tinta); font-size: 14px; }
        .ea-etapa__ind { text-align: right; font-weight: 800; font-size: 18px; }
        .ea-ley { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12.5px; color: var(--tn-suave); }
        .ea-ley i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
        .ea-tabla td, .ea-tabla th { white-space: nowrap; vertical-align: middle; }
        .ea-tabla th { font-size: 11.5px; color: var(--tn-suave); font-weight: 700; }
        .ea-num { text-align: right; font-variant-numeric: tabular-nums; }
        .ea-pocas { color: var(--tn-tenue); font-size: 11px; }
        .ea-vacio { padding: 18px 8px; text-align: center; color: var(--tn-tenue); font-size: 13.5px; }
        .ea-chip { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
        .ea-chip--malo { background: #FBEDEA; color: #9A3A22; }
        .ea-chip--medio { background: #FBF4E1; color: #7A5D10; }
        .ea-chip--bueno { background: #E9F5EE; color: #1E7B3C; }
        .ea-chip--ok { background: #E9F5EE; color: #1E7B3C; }
        .ea-alerta { border: 1px solid #F2D2CA; border-left: 4px solid #9A3A22; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; background: #fff; }
        .ea-alerta.is-atendida { border-color: #CBE7D6; border-left-color: #1E7B3C; opacity: .85; }
        .ea-alerta__cab { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 6px 12px; font-size: 13px; color: var(--tn-suave); }
        .ea-alerta__cab b { color: var(--tn-tinta); }
        .ea-alerta blockquote { margin: 8px 0 0; padding: 6px 10px; border-left: 3px solid #E3E8E8; font-size: 14px; color: var(--tn-tinta); background: #FAFBFB; }
        .ea-scroll { max-height: 520px; overflow: auto; }
    </style>
@endsection

@section('content')
<section class="section tn">
    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
        <div>
            <a class="ea-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
            <h3 class="page__heading mb-0">Atención del trámite de conciliación</h3>
            <span class="small" style="color: var(--tn-tenue);">Lo que contestan las partes en el formulario
                (<a href="{{ route('atencion.formulario') }}" target="_blank" rel="noopener">/atencion-tramite-conciliacion</a>)</span>
        </div>
        @if ($migrado)
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.atencion.descargar', $base) }}"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        @endif
    </div>

    <div class="section-body">
        @foreach (['success' => 'success', 'error' => 'danger'] as $k => $c)
            @if (session($k))<div class="alert alert-{{ $c }} alert-dismissible fade show">{{ session($k) }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>@endif
        @endforeach

        @if (! $migrado)
            <div class="tn-tarjeta"><div class="ea-vacio">Falta correr la migración del formulario de atención (<code>php artisan migrate</code>).</div></div>
        @else
        <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.atencion') }}" id="eaFiltros">
            <div class="ea-filtros">
                <div>
                    <span class="ea-etq">Contestados en</span>
                    <div class="ea-atajos">
                        @foreach ($atajos as $t => [$d, $h])
                            <a class="ea-atajo {{ $f['desde'] === $d && $f['hasta'] === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h]) }}">{{ $t }}</a>
                        @endforeach
                    </div>
                </div>
                <div><label for="eaDesde">Desde</label><input type="date" id="eaDesde" name="desde" class="form-control" value="{{ $f['desde'] }}"></div>
                <div><label for="eaHasta">Hasta</label><input type="date" id="eaHasta" name="hasta" class="form-control" value="{{ $f['hasta'] }}"></div>
                <div>
                    <label for="eaSede">Sede</label>
                    <select id="eaSede" name="sede" class="form-select ea-auto">
                        <option value="">Todas las sedes</option>
                        @foreach (\App\Support\Recepcion::TODAS_LAS_SEDES as $s)<option value="{{ $s }}" @selected($f['sede'] === $s)>{{ $s }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="eaParte">Contestó</label>
                    <select id="eaParte" name="parte" class="form-select ea-auto">
                        <option value="">Ambas partes</option>
                        @foreach (AT::PARTES as $k => $t)<option value="{{ $k }}" @selected($f['parte'] === $k)>{{ $t }}</option>@endforeach
                    </select>
                </div>
                <div><button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Aplicar</button></div>
            </div>
        </form>

        {{-- Cifras --}}
        <div class="tn-tarjeta ea-bloque">
            <div class="ea-cabeza"><h4 class="ea-titulo">{{ $f['sede'] ?: 'Todas las sedes' }}{{ $f['parte'] ? ' · '.AT::PARTES[$f['parte']] : '' }}</h4><span class="ea-sub">{{ $periodo }}</span></div>
            <div class="ea-cifras">
                <div class="ea-cifra"><b>{{ number_format($total) }}</b><span>Formularios contestados</span><small>{{ $porParte['trabajadora'] }} trabajadora · {{ $porParte['empleadora'] }} empleadora</small></div>
                <div class="ea-cifra"><b>{{ $pct($pctRespuesta) }}</b><span>Tasa de respuesta</span><small>{{ number_format($envios) }} correos enviados en el periodo</small></div>
                <div class="ea-cifra"><b style="{{ $colorInd($indice) }}">{{ $ind($indice) }}</b><span>Índice de satisfacción</span><small>0 a 100 · Recepción, captura y conciliador</small></div>
                <div class="ea-cifra"><b>{{ $pct(collect($derechos['opciones'])->firstWhere('valor', 'si')['pct'] ?? null) }}</b><span>Les informaron sus derechos</span><small>pregunta 3</small></div>
                <div class="ea-cifra"><b>{{ $pct($imparcialidad['favorece']['equilibrado'] && array_sum($imparcialidad['favorece']) ? round($imparcialidad['favorece']['equilibrado'] * 100 / array_sum($imparcialidad['favorece']), 1) : null) }}</b><span>Trato equilibrado</span><small>pregunta 5</small></div>
                @foreach ($etapas as $n => $e)
                    <div class="ea-cifra"><b style="{{ $colorInd($e['indice']) }}">{{ $ind($e['indice']) }}</b><span>{{ $e['nombre'] }}</span><small>{{ $e['n'] }} respuestas · {{ $pct($e['pctMala']) }} mala</small></div>
                @endforeach
                <div class="ea-cifra {{ $presion ? 'ea-cifra--alerta' : '' }}"><b>{{ $presion }}</b><span>Presión al firmar</span><small>{{ $pct($pctPresion) }} de {{ $convenios }} con convenio</small></div>
                <div class="ea-cifra {{ $pendientes ? 'ea-cifra--alerta' : '' }}"><b>{{ $pendientes }}</b><span>Alertas sin atender</span><small><a href="#alertas">ver alertas</a></small></div>
            </div>
        </div>

        @if ($total === 0)
            <div class="tn-tarjeta ea-bloque"><div class="ea-vacio"><i class="bi bi-inbox"></i> Nadie ha contestado el formulario en este periodo con esos filtros.</div></div>
        @else
        {{-- Por etapa --}}
        <div class="tn-tarjeta ea-bloque">
            <div class="ea-cabeza">
                <div><h4 class="ea-titulo">Satisfacción por etapa</h4><span class="ea-sub">Índice: Buena = 100, Regular = 50, Mala = 0</span></div>
                <div class="ea-ley"><span><i style="background:#1E7B3C"></i>Buena</span><span><i style="background:#D9A93A"></i>Regular</span><span><i style="background:#C0392B"></i>Mala</span></div>
            </div>
            @foreach ($etapas as $e)
                <div class="ea-etapa">
                    <span class="ea-etapa__nombre">{{ $e['nombre'] }}<br><small class="ea-sub">{{ $e['n'] }} respuestas</small></span>
                    <div class="ea-barra" title="Buena {{ $pct($e['pctBuena']) }} · Regular {{ $pct($e['pctRegular']) }} · Mala {{ $pct($e['pctMala']) }}">
                        <span style="width:{{ $e['pctBuena'] ?? 0 }}%;background:#1E7B3C"></span>
                        <span style="width:{{ $e['pctRegular'] ?? 0 }}%;background:#D9A93A"></span>
                        <span style="width:{{ $e['pctMala'] ?? 0 }}%;background:#C0392B"></span>
                    </div>
                    <span class="ea-etapa__ind" style="{{ $colorInd($e['indice']) }}">{{ $ind($e['indice']) }}</span>
                </div>
            @endforeach
            <div class="row g-3 mt-1">
                <div class="col-12 col-lg-6">
                    <span class="ea-etq">¿Se te informó de tus derechos y obligaciones? (pregunta 3)</span>
                    @foreach ($derechos['opciones'] as $o)
                        <div class="d-flex justify-content-between border-bottom py-1"><span>{{ $o['texto'] }}</span><b>{{ $o['n'] }} · {{ $pct($o['pct']) }}</b></div>
                    @endforeach
                </div>
                <div class="col-12 col-lg-6">
                    <span class="ea-etq">Satisfacción según el resultado (no castiga a quien le tocaron casos sin convenio)</span>
                    <table class="table table-sm ea-tabla mb-0">
                        <thead><tr><th></th><th class="ea-num">Respuestas</th><th class="ea-num">Recepción</th><th class="ea-num">Captura</th><th class="ea-num">Conciliador</th></tr></thead>
                        <tbody>
                            @foreach ($porResultado as $r)
                                <tr><td><b>{{ $r['etiqueta'] }}</b></td><td class="ea-num">{{ $r['n'] }}</td><td class="ea-num" style="{{ $colorInd($r['recepcion']) }}">{{ $ind($r['recepcion']) }}</td><td class="ea-num" style="{{ $colorInd($r['captura']) }}">{{ $ind($r['captura']) }}</td><td class="ea-num" style="{{ $colorInd($r['conciliador']) }}">{{ $ind($r['conciliador']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Imparcialidad --}}
        <div class="tn-tarjeta ea-bloque">
            <div class="ea-cabeza"><div><h4 class="ea-titulo">Imparcialidad del conciliador (pregunta 5)</h4><span class="ea-sub">A quién perciben que favoreció, según quién contesta. Si cada parte siente que favoreció a la otra, es buena señal; si ambas dicen lo mismo, es alerta.</span></div></div>
            <div class="table-responsive">
                <table class="table ea-tabla mb-0">
                    <thead><tr><th>Contestó</th><th class="ea-num">Respuestas</th><th class="ea-num">Equilibrado y justo</th><th class="ea-num">Favoreció al trabajador</th><th class="ea-num">Favoreció al empleador</th></tr></thead>
                    <tbody>
                        @foreach ($imparcialidad['favorecePorParte'] as $parte => $c)
                            @php $n = array_sum($c); $p = fn ($v) => $n ? round($v * 100 / $n, 1) : null; @endphp
                            <tr>
                                <td><b>{{ AT::PARTES[$parte] }}</b></td>
                                <td class="ea-num">{{ $n }}</td>
                                <td class="ea-num" style="color:#1E7B3C"><b>{{ $pct($p($c['equilibrado'])) }}</b></td>
                                <td class="ea-num">{{ $pct($p($c['trabajador'])) }}</td>
                                <td class="ea-num">{{ $pct($p($c['empleador'])) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Resultado dicho vs registrado --}}
        <div class="tn-tarjeta ea-bloque">
            <div class="ea-cabeza"><div><h4 class="ea-titulo">¿Saben qué firmaron? (pregunta 6 contra SICONCILIO)</h4><span class="ea-sub">Coincide en {{ $pct($resultado['pct']) }} de {{ $resultado['comparables'] }} respuestas (convenio o sin convenio).</span></div></div>
            <div class="table-responsive">
                <table class="table ea-tabla mb-0">
                    <thead><tr><th>Contestaron</th><th class="ea-num">Registrado con convenio</th><th class="ea-num">Registrado sin convenio</th></tr></thead>
                    <tbody>
                        @foreach (AT::PREGUNTAS[6]['opciones'] as $texto)
                            <tr><td><b>{{ $texto }}</b></td><td class="ea-num">{{ $resultado['matriz'][$texto]['Con convenio'] ?? 0 }}</td><td class="ea-num">{{ $resultado['matriz'][$texto]['Sin convenio'] ?? 0 }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tablas por sede y por persona --}}
        @foreach ([['porSede', 'Por sede', 'Sede', false], ['conciliadores', 'Por conciliador(a)', 'Conciliador(a)', true], ['capturistas', 'Por quien capturó la solicitud', 'Capturó', true]] as [$clave, $titulo, $col, $persona])
            <div class="tn-tarjeta ea-bloque">
                <div class="ea-cabeza"><div><h4 class="ea-titulo">{{ $titulo }}</h4>@if ($persona)<span class="ea-sub">El índice se muestra con {{ EA::MINIMO }} respuestas o más.</span>@endif</div></div>
                @if (empty($$clave))
                    <div class="ea-vacio">Sin datos.</div>
                @else
                    <div class="table-responsive ea-scroll">
                        <table class="table table-hover ea-tabla mb-0">
                            <thead><tr><th>{{ $col }}</th><th class="ea-num">Respuestas</th><th class="ea-num">Recepción</th><th class="ea-num">Captura</th><th class="ea-num">Conciliador</th><th class="ea-num">Con alguna «mala»</th><th class="ea-num">Informados</th><th class="ea-num">Equilibrado</th><th class="ea-num">Presión al firmar</th></tr></thead>
                            <tbody>
                                @foreach ($$clave as $r)
                                    @php $ver = ! $persona || $r['suficiente']; @endphp
                                    <tr>
                                        <td><b>{{ $r['etiqueta'] }}</b> @if (! $ver)<span class="ea-pocas">· pocas respuestas</span>@endif</td>
                                        <td class="ea-num">{{ $r['n'] }}</td>
                                        @foreach (['recepcion', 'captura', 'conciliador'] as $k)
                                            <td class="ea-num" style="{{ $ver ? $colorInd($r[$k]) : '' }}">{{ $ver ? $ind($r[$k]) : '—' }}</td>
                                        @endforeach
                                        <td class="ea-num">{{ $r['malas'] }}</td>
                                        <td class="ea-num">{{ $ver ? $pct($r['informado']) : '—' }}</td>
                                        <td class="ea-num">{{ $ver ? $pct($r['equilibrado']) : '—' }}</td>
                                        <td class="ea-num" style="{{ $r['presion'] ? 'color:#9A3A22;font-weight:800' : '' }}">{{ $r['presion'] }}{{ $r['convenios'] ? ' de '.$r['convenios'] : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
        @endif

        {{-- Cada formulario contestado: quién y qué contestó --}}
        @if (! empty($respuestas))
            @php $mostrar = array_slice($respuestas, 0, 500); @endphp
            <div class="tn-tarjeta ea-bloque" id="respuestas">
                <div class="ea-cabeza">
                    <div>
                        <h4 class="ea-titulo">Formularios contestados</h4>
                        <span class="ea-sub">Cada persona que contestó, con sus respuestas.{{ count($respuestas) > 500 ? ' Se muestran los 500 más recientes de '.number_format(count($respuestas)).'; el Excel trae todos.' : '' }}</span>
                    </div>
                    <input type="search" id="eaBuscar" class="form-control form-control-sm" style="max-width:280px" placeholder="Buscar nombre, folio o conciliador…" aria-label="Buscar en los formularios">
                </div>
                <div class="table-responsive ea-scroll">
                    <table class="table table-hover ea-tabla ea-respuestas mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th><th>Folio</th><th>Contestó</th><th>Nombre</th><th>Sede</th><th>Conciliador(a)</th><th>Resolución</th>
                                @foreach (AT::PREGUNTAS as $n => $p)<th class="text-center" title="{{ $p['texto'] }}">{{ $n }}. {{ $p['etapa'] }}</th>@endforeach
                                <th>Comentarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mostrar as $r)
                                @php
                                    $chip = function (int $n, ?string $v) {
                                        if ($v === null) return 'ea-chip';
                                        $malo = in_array($v, ['Mala', 'Malo', 'No'], true) || ($n === 7 && $v === 'Sí');
                                        $bueno = in_array($v, ['Buena', 'Bueno', 'Sí'], true) && $n !== 7 || ($n === 7 && $v === 'No') || ($n === 5 && str_starts_with($v, 'Su trato'));
                                        return 'ea-chip '.($malo ? 'ea-chip--malo' : ($bueno ? 'ea-chip--bueno' : 'ea-chip--medio'));
                                    };
                                @endphp
                                <tr data-buscar="{{ mb_strtolower($r['nombre'].' '.$r['folio'].' '.$r['conciliador'].' '.$r['sede']) }}">
                                    <td>{{ $r['fecha'] }}</td>
                                    <td><b>{{ $r['folio'] }}</b></td>
                                    <td>{{ $r['parte'] }}</td>
                                    <td style="white-space:normal;min-width:180px"><b>{{ $r['nombre'] }}</b></td>
                                    <td>{{ $r['sede'] }}</td>
                                    <td>{{ $r['conciliador'] }}</td>
                                    <td>{{ $r['resolucion'] }}</td>
                                    @foreach (array_keys(AT::PREGUNTAS) as $n)
                                        @php $v = $r['respuestas'][$n]; @endphp
                                        <td class="text-center">
                                            @if ($v === null)
                                                <span class="ea-sub">—</span>
                                            @else
                                                <span class="{{ $chip($n, $v) }}" title="{{ $v }}">{{ $n === 5 && str_starts_with($v, 'Su trato') ? 'Equilibrado' : ($n === 6 ? \Illuminate\Support\Str::limit(str_replace(['Convenio de Conciliación', 'Constancia de No Conciliación', 'Constancia de Haber Agotado la Etapa de Conciliación Prejudicial'], ['Convenio', 'No conciliación', 'Etapa agotada'], $v), 20) : \Illuminate\Support\Str::limit($v, 26)) }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td style="white-space:normal;min-width:260px">
                                        @forelse ($r['comentarios'] as $n => $c)
                                            <div class="small"><span class="ea-sub">{{ $n }}.</span> {{ $c }}</div>
                                        @empty
                                            <span class="ea-sub">—</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="ea-sub mb-0 mt-2" id="eaSinResultados" hidden>Ningún formulario coincide con la búsqueda.</p>
            </div>
        @endif

        {{-- Alertas --}}
        <div class="tn-tarjeta ea-bloque" id="alertas">
            <div class="ea-cabeza"><div><h4 class="ea-titulo"><i class="bi bi-exclamation-triangle" style="color:#9A3A22"></i> Alertas</h4><span class="ea-sub">Presión al firmar el convenio, o una calificación «mala» con comentario. Contestadas en el periodo.</span></div></div>
            @forelse ($alertas ?? [] as $a)
                <div class="ea-alerta {{ $a['seguimiento'] === 'atendida' ? 'is-atendida' : '' }}">
                    <div class="ea-alerta__cab">
                        <span><b>{{ $a['folio'] }}</b> · {{ $a['nombre'] }} ({{ mb_strtolower($a['parte']) }}) · {{ $a['sede'] }} · {{ $a['fecha'] }}</span>
                        <span>Conciliador(a): <b>{{ $a['conciliador'] }}</b> · Capturó: <b>{{ $a['capturo'] }}</b></span>
                    </div>
                    <div class="mt-1">
                        @if ($a['presion'])<span class="ea-chip ea-chip--malo">Sintió presión al firmar</span>@endif
                        @foreach ([1, 2, 4] as $n)
                            @if (in_array($a['respuestas'][$n], ['Mala', 'Malo'], true))<span class="ea-chip ea-chip--malo">{{ AT::PREGUNTAS[$n]['etapa'] }}: {{ $a['respuestas'][$n] }}</span>@endif
                        @endforeach
                        @if ($a['seguimiento'] === 'atendida')<span class="ea-chip ea-chip--ok"><i class="bi bi-check2"></i> Atendida {{ $a['atendidaAt'] }}</span>@endif
                    </div>
                    @foreach ($a['comentarios'] as $n => $c)
                        <blockquote><small class="ea-sub">{{ $n }}. {{ AT::PREGUNTAS[$n]['etapa'] }}:</small> {{ $c }}</blockquote>
                    @endforeach
                    @if ($a['seguimiento'] === 'atendida')
                        @if ($a['nota'])<p class="ea-sub mb-0 mt-2"><b>Seguimiento:</b> {{ $a['nota'] }}</p>@endif
                    @else
                        <form method="POST" action="{{ route('estadisticas.atencion.atender', $a['id']) }}" class="d-flex flex-wrap gap-2 mt-2">
                            @csrf
                            <input type="text" name="nota" class="form-control form-control-sm" style="max-width:520px" maxlength="1000" placeholder="Qué se hizo (opcional)">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check2-circle"></i> Marcar como atendida</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="ea-vacio">Sin alertas en este periodo.</div>
            @endforelse
        </div>

        {{-- Comentarios --}}
        @if (! empty($comentarios))
            <div class="tn-tarjeta ea-bloque">
                <div class="ea-cabeza"><div><h4 class="ea-titulo">Comentarios</h4><span class="ea-sub">{{ count($comentarios) }} en el periodo</span></div></div>
                <div class="table-responsive ea-scroll">
                    <table class="table table-hover ea-tabla mb-0">
                        <thead><tr><th>Fecha</th><th>Folio</th><th>Sede</th><th>Contestó</th><th>Nombre</th><th>Pregunta</th><th>Respuesta</th><th>Persona</th><th>Comentario</th></tr></thead>
                        <tbody>
                            @foreach ($comentarios as $c)
                                <tr>
                                    <td>{{ $c['fecha'] }}</td><td><b>{{ $c['folio'] }}</b></td><td>{{ $c['sede'] }}</td><td>{{ $c['parte'] }}</td><td>{{ $c['nombre'] }}</td>
                                    <td>{{ $c['pregunta'] }}</td><td><span class="ea-chip ea-chip--{{ $c['nivel'] }}">{{ $c['respuesta'] }}</span></td>
                                    <td>{{ $c['persona'] ?: '—' }}</td>
                                    <td style="white-space:normal;min-width:260px">{{ $c['texto'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        @endif
    </div>
</section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('#eaFiltros .ea-auto').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
        (function () {
            var buscar = document.getElementById('eaBuscar');
            if (!buscar) { return; }
            var filas = document.querySelectorAll('.ea-respuestas tbody tr');
            var vacio = document.getElementById('eaSinResultados');
            buscar.addEventListener('input', function () {
                var q = buscar.value.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                var vistas = 0;
                filas.forEach(function (tr) {
                    var t = (tr.getAttribute('data-buscar') || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    var ver = q === '' || t.indexOf(q) !== -1;
                    tr.hidden = !ver;
                    vistas += ver ? 1 : 0;
                });
                vacio.hidden = vistas > 0;
            });
        })();
    </script>
@endsection
