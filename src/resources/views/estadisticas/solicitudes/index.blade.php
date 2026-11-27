@extends('layouts.app')
@section('title', 'Estadística de Solicitudes')
{{--
    /estadisticas/solicitudes (EstadisticasController::solicitudes,
    App\Support\EstadisticasSolicitudes): qué parte de las solicitudes es de
    cada rama industrial y, de la rama elegida, cuántos trabajadores reclaman
    sus semanas (sin pagar o ante el IMSS). Por periodo, sede y rama; se
    descarga en PDF o Excel (estadisticas.solicitudes.descargar).
--}}
@php
    use App\Support\EstadisticasSolicitudes as ES;
    use App\Support\GraficaSvg;
    $f = $filtros;
    $hoyC = \Illuminate\Support\Carbon::today();
    $atajos = [
        'Esta semana' => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->toDateString(), 'dia'],
        'Este mes'    => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->toDateString(), 'dia'],
        'Mes pasado'  => [$hoyC->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoyC->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(), 'semana'],
    ];
    foreach ($anios as $a) {
        $atajos['Año '.$a] = [$a.'-01-01', $a == $hoyC->year ? $hoyC->toDateString() : $a.'-12-31', 'mes'];
    }
    $primerAnio = end($anios);
    $atajos['Todo'] = [$primerAnio.'-01-01', $hoyC->toDateString(), 'mes'];
    $base = array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'agrupar' => $f['agrupar'], 'sede' => $f['sede'], 'rama' => $f['rama']]);
    $url = fn (array $c) => route('estadisticas.solicitudes', array_filter(array_merge($base, $c), fn ($v) => $v !== null && $v !== ''));
    $descargar = fn (string $formato) => route('estadisticas.solicitudes.descargar', $base + ['formato' => $formato]);
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $r = $resumen;
    $ampliada = $universo === 'ampliada';
    $nRama = $r[$universo];
    $hayDatos = $r['total'] > 0;
    $pctOtros = collect($ramas)->first(fn ($x) => str_starts_with($x['nombre'], 'Otros servicios'))['pct'] ?? 0;
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .es-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .es-migas:hover { color: var(--tn-verde); }
        .es-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 14px; }
        .es-filtros label, .es-etq { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .es-filtros .form-control, .es-filtros .form-select { min-width: 140px; }
        .es-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .es-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .es-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .es-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .es-bloque { margin-top: 16px; }
        .es-cabeza { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .es-titulo { margin: 0; font-size: 16px; font-weight: 800; color: var(--tn-tinta); }
        .es-sub { font-size: 12.5px; color: var(--tn-tenue); }
        .es-cifras { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
        @media (max-width: 1200px) { .es-cifras { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 640px)  { .es-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .es-cifra { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; background: var(--tn-fondo); }
        .es-cifra b { display: block; font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .es-cifra span { font-size: 12px; color: var(--tn-suave); font-weight: 600; }
        .es-cifra small { display: block; font-size: 11px; color: var(--tn-tenue); margin-top: 2px; }
        .es-cifra--oro { background: #FBF4E1; border-color: #F0E2B6; } .es-cifra--oro b { color: #7A5D10; }
        .es-cifra--azul { background: #E8EEF8; border-color: #CCD6F0; } .es-cifra--azul b { color: #1F4E79; }
        .es-respuesta { font-size: 15px; color: var(--tn-tinta); margin: 0 0 12px; }
        .es-respuesta b { font-size: 20px; }
        .es-dos { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px; align-items: start; }
        .es-dos--tabla { grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); }
        @media (max-width: 1100px) { .es-dos, .es-dos--tabla { grid-template-columns: 1fr; } }
        .es-svg svg { width: 100%; height: auto; display: block; }
        .es-tabla td, .es-tabla th { white-space: nowrap; vertical-align: middle; }
        .es-tabla th { font-size: 11.5px; color: var(--tn-suave); font-weight: 700; }
        .es-tabla .es-etq-c { font-weight: 700; color: var(--tn-tinta); white-space: normal; min-width: 150px; }
        .es-tabla .es-etq-c a { color: var(--tn-tinta); text-decoration: none; }
        .es-tabla .es-etq-c a:hover { color: var(--tn-verde); text-decoration: underline; }
        .es-tabla tr.is-elegida td { background: #FFFBF0; }
        .es-tabla tr.es-total td { background: #EEF2F2; font-weight: 800; }
        .es-num { text-align: right; font-variant-numeric: tabular-nums; }
        .es-num small { display: block; font-size: 10.5px; color: var(--tn-tenue); font-weight: 600; }
        .es-scroll { max-height: 460px; overflow: auto; }
        .es-vacio { padding: 18px 8px; text-align: center; color: var(--tn-tenue); font-size: 13.5px; }
        .es-nota { font-size: 12px; color: #7A5D10; background: #FBF4E1; border-radius: 8px; padding: 8px 10px; }
        .es-glosario dt { font-size: 13px; color: var(--tn-tinta); }
        .es-glosario dd { font-size: 12.5px; color: var(--tn-suave); margin-bottom: 8px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="es-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Estadística de Solicitudes</h3>
                <span class="small" style="color: var(--tn-tenue);">Qué parte de las solicitudes es de cada rama industrial y quién reclama sus semanas</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('pdf') }}"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('xlsx') }}"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            </div>
        </div>

        <div class="section-body">
            <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.solicitudes') }}" id="esFiltros">
                <div class="es-filtros">
                    <div>
                        <span class="es-etq">Periodo</span>
                        <div class="es-atajos">
                            @foreach ($atajos as $etiqueta => [$d, $h, $agr])
                                <a class="es-atajo {{ $f['desde'] === $d && $f['hasta'] === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h, 'agrupar' => $agr]) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="es-filtros mt-3">
                    <div><label for="esDesde">Desde</label><input type="date" id="esDesde" name="desde" class="form-control" value="{{ $f['desde'] }}"></div>
                    <div><label for="esHasta">Hasta</label><input type="date" id="esHasta" name="hasta" class="form-control" value="{{ $f['hasta'] }}"></div>
                    <div>
                        <span class="es-etq">Ver</span>
                        <div class="es-atajos">
                            @foreach (ES::AGRUPAR as $clave => $texto)
                                <a class="es-atajo {{ $agrupar === $clave ? 'is-activo' : '' }}" href="{{ $url(['agrupar' => $clave]) }}">{{ $texto }}</a>
                            @endforeach
                        </div>
                        <input type="hidden" name="agrupar" value="{{ $f['agrupar'] }}">
                    </div>
                    <div>
                        <label for="esRama">Rama</label>
                        <select id="esRama" name="rama" class="form-select es-auto" style="max-width: 340px;">
                            @foreach ($ramasCatalogo as $id => $nombre)
                                <option value="{{ $id }}" @selected($f['rama'] === (int) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="esSede">Sede</label>
                        <select id="esSede" name="sede" class="form-select es-auto">
                            <option value="">Todas las sedes</option>
                            @foreach (\App\Support\Recepcion::TODAS_LAS_SEDES as $s)
                                <option value="{{ $s }}" @selected($f['sede'] === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Aplicar</button></div>
                </div>
            </form>

            @if ($ajustado)
                <div class="alert alert-info es-bloque mb-0 py-2"><i class="bi bi-info-circle"></i> El periodo es largo para verlo así; se muestra <b>{{ mb_strtolower($agruparTexto) }}</b>.</div>
            @endif

            {{-- Cifras --}}
            <div class="tn-tarjeta es-bloque">
                <div class="es-cabeza">
                    <h4 class="es-titulo">{{ $filtrosTexto }}</h4>
                    <span class="es-sub">{{ $periodo }}</span>
                </div>
                <div class="es-cifras">
                    <div class="es-cifra"><b>{{ number_format($r['total']) }}</b><span>Solicitudes</span><small>en el periodo</small></div>
                    <div class="es-cifra es-cifra--oro"><b>{{ number_format($r['rama']) }}</b><span>Registradas en {{ $rama['nombre'] }}</span><small>{{ $pct($r['pctRama']) }} de las solicitudes</small></div>
                    @if ($ampliada)
                        <div class="es-cifra es-cifra--oro"><b>{{ number_format($r['ampliada']) }}</b><span>Estimación ampliada</span><small>{{ $pct($r['pctAmpliada']) }} · por actividad o puesto</small></div>
                    @endif
                    <div class="es-cifra"><b>{{ number_format($r['trabajadores']) }}</b><span>Trabajadores de la rama</span><small>{{ number_format($r['hombres']) }} hombres · {{ number_format($r['mujeres']) }} mujeres</small></div>
                    <div class="es-cifra es-cifra--azul"><b>{{ $pct($trabajadores[2]['pct']) }}</b><span>Reclaman sus semanas</span><small>{{ number_format($trabajadores[2]['total']) }} trabajadores (sin pagar o IMSS)</small></div>
                </div>
            </div>

            @if (! $hayDatos)
                <div class="tn-tarjeta es-bloque"><div class="es-vacio"><i class="bi bi-inbox"></i> No hay solicitudes en este periodo con esos filtros.</div></div>
            @else
                {{-- 1. ¿Qué parte es de la rama? --}}
                <div class="tn-tarjeta es-bloque">
                    <div class="es-cabeza">
                        <h4 class="es-titulo">1. ¿Qué parte de las solicitudes es de {{ $rama['nombre'] }}?</h4>
                        <span class="es-sub">{{ $periodo }}</span>
                    </div>
                    <p class="es-respuesta">
                        @if ($ampliada && $r['pctAmpliada'] !== $r['pctRama'])
                            Entre <b>{{ $pct($r['pctRama']) }}</b> y <b>{{ $pct($r['pctAmpliada']) }}</b> de las solicitudes
                            ({{ number_format($r['rama']) }} registradas en la rama y {{ number_format($r['ampliada']) }} con la estimación ampliada).
                        @else
                            <b>{{ $pct($r['pctRama']) }}</b> de las solicitudes ({{ number_format($r['rama']) }} de {{ number_format($r['total']) }}).
                        @endif
                    </p>
                    <div class="es-dos">
                        <div>
                            <span class="es-etq">Solicitudes por rama</span>
                            <div class="es-svg">{!! GraficaSvg::enPagina($graficas['ramas']) !!}</div>
                        </div>
                        <div>
                            <span class="es-etq">% de {{ $rama['nombre'] }} por sede</span>
                            <div class="es-svg">{!! GraficaSvg::enPagina($graficas['sedes']) !!}</div>
                        </div>
                    </div>
                    @if ($pctOtros > 30)
                        <div class="es-nota mt-2"><i class="bi bi-info-circle"></i> El {{ $pct($pctOtros) }} se capturó como «Otros servicios»: la rama registrada se queda corta.
                            @if ($ampliada) Por eso se da también la estimación ampliada. @endif</div>
                    @endif
                </div>

                {{-- 2. ¿Quién reclama sus semanas? --}}
                <div class="tn-tarjeta es-bloque">
                    <div class="es-cabeza">
                        <h4 class="es-titulo">2. ¿Cuántos trabajadores de {{ $rama['nombre'] }} reclaman sus semanas?</h4>
                        <span class="es-sub">{{ number_format($r['trabajadores']) }} trabajadores {{ $ampliada ? '(estimación ampliada)' : '' }}</span>
                    </div>
                    <div class="es-dos es-dos--tabla">
                        <div class="table-responsive">
                            <table class="table es-tabla mb-0">
                                <thead><tr><th>Reclaman</th><th class="es-num">Trabajadores</th><th class="es-num">Hombres</th><th class="es-num">Mujeres</th></tr></thead>
                                <tbody>
                                    @foreach ($trabajadores as $t)
                                        <tr class="{{ $t['clave'] === 'alguno' ? 'es-total' : '' }}">
                                            <td class="es-etq-c">{{ $t['nombre'] }}</td>
                                            <td class="es-num">{{ number_format($t['total']) }}<small>{{ $pct($t['pct']) }}</small></td>
                                            <td class="es-num">{{ number_format($t['hombres']) }}<small>{{ $pct($t['pctH']) }} de {{ number_format($r['hombres']) }}</small></td>
                                            <td class="es-num">{{ number_format($t['mujeres']) }}<small>{{ $pct($t['pctM']) }} de {{ number_format($r['mujeres']) }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="es-nota mt-2"><i class="bi bi-info-circle"></i> Se busca en lo que se escribió en la solicitud: es un piso. Si el trabajador lo pidió y no quedó escrito, no se cuenta.</div>
                        </div>
                        <div>
                            <span class="es-etq">Solicitudes de la rama según lo que reclaman</span>
                            <div class="es-svg">{!! GraficaSvg::enPagina($graficas['reclamos']) !!}</div>
                        </div>
                    </div>
                </div>

                {{-- Por periodo --}}
                <div class="tn-tarjeta es-bloque">
                    <div class="es-cabeza">
                        <h4 class="es-titulo">{{ $rama['nombre'] }} · {{ mb_strtolower($agruparTexto) }}</h4>
                        <span class="es-sub">{{ $periodo }}</span>
                    </div>
                    <div class="es-svg">{!! GraficaSvg::enPagina($graficas['periodo']) !!}</div>
                    <div class="es-scroll mt-3">
                        @include('estadisticas.solicitudes._tabla', ['filas' => $serie, 'primera' => $unidad,
                            'enlace' => $agrupar === 'dia' ? null : (fn ($fila) => $url(['desde' => $fila['desde'], 'hasta' => $fila['hasta'], 'agrupar' => ['anio' => 'mes', 'mes' => 'semana', 'semana' => 'dia'][$agrupar]]))])
                    </div>
                </div>

                {{-- Por sede --}}
                <div class="tn-tarjeta es-bloque">
                    <div class="es-cabeza"><h4 class="es-titulo">Por sede</h4><span class="es-sub">{{ $periodo }}</span></div>
                    @include('estadisticas.solicitudes._tabla', ['filas' => $porSede, 'primera' => 'Sede',
                        'enlace' => $f['sede'] === '' ? (fn ($fila) => in_array($fila['etiqueta'], \App\Support\Recepcion::TODAS_LAS_SEDES, true) ? $url(['sede' => $fila['etiqueta']]) : null) : null])
                </div>

                {{-- Todas las ramas --}}
                <div class="tn-tarjeta es-bloque">
                    <div class="es-cabeza">
                        <div>
                            <h4 class="es-titulo">Todas las ramas</h4>
                            <span class="es-sub">Registradas en cada rama · toca una para verla arriba</span>
                        </div>
                        <span class="es-sub">{{ $periodo }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover es-tabla mb-0">
                            <thead><tr><th>Rama</th><th class="es-num">Solicitudes</th><th class="es-num">Semanas sin pagar</th><th class="es-num">IMSS (alta o semanas)</th><th class="es-num">Cualquiera</th></tr></thead>
                            <tbody>
                                @foreach ($ramas as $rr)
                                    <tr class="{{ $rr['elegida'] ? 'is-elegida' : '' }}">
                                        <td class="es-etq-c"><a href="{{ $url(['rama' => $rr['id']]) }}">{{ $rr['nombre'] }}</a></td>
                                        <td class="es-num">{{ number_format($rr['total']) }}<small>{{ $pct($rr['pct']) }} del total</small></td>
                                        <td class="es-num">{{ number_format($rr['semanas']) }}<small>{{ $pct($rr['pctSemanas']) }}</small></td>
                                        <td class="es-num">{{ number_format($rr['imss']) }}<small>{{ $pct($rr['pctImss']) }}</small></td>
                                        <td class="es-num">{{ number_format($rr['alguno']) }}<small>{{ $pct($rr['pctAlguno']) }}</small></td>
                                    </tr>
                                @endforeach
                                <tr class="es-total">
                                    <td>Total</td>
                                    <td class="es-num">{{ number_format($r['total']) }}</td>
                                    <td class="es-num">{{ number_format(collect($ramas)->sum('semanas')) }}</td>
                                    <td class="es-num">{{ number_format(collect($ramas)->sum('imss')) }}</td>
                                    <td class="es-num">{{ number_format(collect($ramas)->sum('alguno')) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <details class="tn-tarjeta es-bloque es-glosario">
                <summary class="es-titulo" style="cursor:pointer">Qué cuenta cada cifra</summary>
                <dl class="mt-3 mb-0">
                    @foreach ($glosario as $termino => $texto)
                        <dt>{{ $termino }}</dt><dd>{{ $texto }}</dd>
                    @endforeach
                </dl>
            </details>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('#esFiltros .es-auto').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
    </script>
@endsection
