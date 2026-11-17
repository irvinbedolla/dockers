{{--
    Las 18 láminas del Reporte estadístico (App\Support\ReporteEstadistico::armar).
    Las usan la página (modo web), la imagen y el PDF (modo pdf): en el PDF las
    imágenes van por ruta de archivo y las gráficas como <img>; en la página,
    las gráficas van como SVG en línea.
--}}
@php
    use App\Support\DatosReferencia as DR;
    use App\Support\GraficaSvg;

    $img  = fn ($f) => $modo === 'pdf' ? public_path('assets/reporte_estadistico/'.$f) : asset('assets/reporte_estadistico/'.$f);
    $graf = fn ($svg) => $modo === 'pdf' ? '<img src="'.GraficaSvg::uri($svg).'" alt="">' : GraficaSvg::enPagina($svg);
    $n    = fn ($v) => $v === null ? '—' : number_format((float) $v);
    $pct  = fn ($v, $d = 1) => $v === null ? '—' : number_format($v, $d).'%';
    $mon  = fn ($v) => '$'.number_format((float) $v, 2);
    $x    = $externos;
    $tx   = $x['textos'];

    $marco = '<div class="re-b re-b1"></div><div class="re-b re-b2"></div><div class="re-b re-b3"></div><div class="re-b re-b4"></div>'
        .'<img class="re-logo" src="'.$img('logo_siconcilio_chico.png').'" alt="SíConcilio">';
    $pie = '<img class="re-pie" src="'.$img('logos_pie.png').'" alt="">';
@endphp

{{-- 1. Portada --}}
<div class="re-lam re-lam--portada" data-lamina="01-portada">
    <div class="re-b re-b1"></div><div class="re-b re-b2"></div><div class="re-b re-b3"></div><div class="re-b re-b4"></div>
    <img src="{{ $img('logo_siconcilio.png') }}" alt="SíConcilio" style="position:absolute; left:276px; top:150px; width:408px; height:136px;">
    <div style="position:absolute; left:180px; top:304px; width:600px; text-align:center; font-size:15px; color:#4A6163;">
        Reporte estadístico · {{ $periodo }}
    </div>
    <img src="{{ $img('logos_pie.png') }}" alt="" style="position:absolute; left:415px; top:372px; width:130px; height:65px;">
</div>

{{-- 2. JLCA vs CCL --}}
<div class="re-lam" data-lamina="02-jlca-ccl">
    {!! $marco !!}
    <div class="re-titulo" style="top:74px;">Comparativo entre los asuntos atendidos por el Centro de Conciliación Laboral<br>(Solicitudes) y la Junta Local de Conciliación y Arbitraje (Demandas):</div>
    <div class="re-marco-graf" style="left:263px; top:158px; width:434px; height:328px;"></div>
    <div class="re-graf" style="left:270px; top:170px; width:420px; height:300px;">{!! $graf($x['graficaJlca']) !!}</div>
</div>

{{-- 3. Presupuesto --}}
<div class="re-lam" data-lamina="03-presupuesto">
    {!! $marco !!}
    <div class="re-titulo" style="top:52px;">Diferencia entre el Presupuesto del Centro de Conciliación Laboral y el<br>Presupuesto de la Junta Local de Conciliación y Arbitraje</div>
    <div class="re-marco-graf re-marco-graf--suave" style="left:226px; top:121px; width:508px; height:298px;">
        <div class="re-graf-titulo">Ejercicio Fiscal</div>
    </div>
    <div class="re-graf" style="left:236px; top:148px; width:488px; height:260px;">{!! $graf($x['graficaPresupuesto']) !!}</div>
    @if ($x['presupuestoDiferencia'] !== null)
        <div class="re-texto re-centro" style="left:150px; top:440px; width:660px; font-size:12px;">
            Existe una diferencia del <b>{{ $x['presupuestoDiferencia'] }}%</b> entre el presupuesto de la JLCA y el CCL
        </div>
        <div class="re-texto re-centro" style="left:150px; top:472px; width:660px; font-size:12px;">
            Presupuesto {{ $x['presupuestoUltimo']['anio'] }}: <b>{{ $mon($x['presupuestoUltimo']['ccl']) }}</b>
            @if ($x['presupuestoAumento'] !== null)&nbsp;({{ $x['presupuestoAumento'] >= 0 ? '+' : '' }}{{ number_format($x['presupuestoAumento'], 1) }}%)@endif
        </div>
    @endif
</div>

{{-- 4. Estadística del periodo por delegación --}}
<div class="re-lam" data-lamina="04-estadistica-delegaciones">
    {!! $marco !!}
    <table class="re-tabla" style="left:98px; top:128px; width:774px;">
        <colgroup><col style="width:64px"><col style="width:64px"><col style="width:62px"><col style="width:72px"><col style="width:72px"><col style="width:74px"><col style="width:64px"><col style="width:72px"><col style="width:60px"><col style="width:90px"></colgroup>
        <tr class="re-banda"><th colspan="10" style="height:46px;">Estadística {{ $periodo }}</th></tr>
        <tr class="re-cols re-h54">
            <th style="width:64px">Delegación</th><th style="width:64px">Personas Atendidas</th><th style="width:62px">Asesorías</th><th style="width:72px">Solicitudes Confirmadas</th><th style="width:72px">Audiencias Programadas</th>
            <th style="width:74px">Convenios Ratificaciones</th><th style="width:64px">Constancias de No Conciliación</th><th style="width:72px">Archivadas por falta de interés</th><th style="width:60px">Efectividad</th><th style="width:90px">Montos Convenidos</th>
        </tr>
        @foreach ($delegaciones as $nombre => $f)
            <tr class="re-h46">
                <td class="re-nombre">{{ $nombre }}</td><td>{{ $n($f['personas']) }}</td><td>{{ $n($f['asesorias']) }}</td><td>{{ $n($f['solicitudes']) }}</td>
                <td>{{ $n($f['audiencias']) }}</td><td>{{ $n($f['conv_rat']) }}</td><td>{{ $n($f['constancias']) }}</td><td>{{ $n($f['archivadas']) }}</td>
                <td>{{ $pct($f['efectividad']) }}</td><td>{{ $mon($f['montos']) }}</td>
            </tr>
        @endforeach
        @php($t = $delegacionesTotal)
        <tr class="re-total re-h46">
            <td>Total</td><td>{{ $n($t['personas']) }}</td><td>{{ $n($t['asesorias']) }}</td><td>{{ $n($t['solicitudes']) }}</td>
            <td>{{ $n($t['audiencias']) }}</td><td>{{ $n($t['conv_rat']) }}</td><td>{{ $n($t['constancias']) }}</td><td>{{ $n($t['archivadas']) }}</td>
            <td>{{ $pct($t['efectividad']) }}</td><td>{{ $mon($t['montos']) }}</td>
        </tr>
    </table>
    {!! $pie !!}
</div>

{{-- 5 y 6. Desglose por sede y por oficina de apoyo --}}
@foreach ([['05-sedes', 'Desglosado por Sede', 'Sede', $sedes, $sedesTotal], ['06-oficinas', 'Desglosado por Oficinas de Apoyo', 'Oficina', $oficinas, $oficinasTotal]] as [$clave, $titulo, $col, $filas, $t])
    <div class="re-lam" data-lamina="{{ $clave }}">
        {!! $marco !!}
        <table class="re-tabla" style="left:153px; top:122px; width:654px;">
            <colgroup><col style="width:66px"><col style="width:70px"><col style="width:76px"><col style="width:76px"><col style="width:66px"><col style="width:84px"><col style="width:76px"><col style="width:74px"><col style="width:66px"></colgroup>
            <tr class="re-banda"><th colspan="9" style="height:48px;">{{ $titulo }}<br>({{ $periodo }})</th></tr>
            <tr class="re-cols re-h56">
                <th style="width:66px">{{ $col }}</th><th style="width:70px">Personas Atendidas</th><th style="width:76px">Solicitudes Confirmadas</th><th style="width:76px">Audiencias Programadas</th><th style="width:66px">Convenios</th>
                <th style="width:84px">Ratificaciones</th><th style="width:76px">Constancias de No Conciliación</th><th style="width:74px">Archivadas por falta de interés</th><th style="width:66px">Efectividad</th>
            </tr>
            @foreach ($filas as $nombre => $f)
                <tr class="re-h47">
                    <td class="re-nombre">{{ $nombre }}</td><td>{{ $n($f['personas']) }}</td><td>{{ $n($f['solicitudes']) }}</td><td>{{ $n($f['audiencias']) }}</td>
                    <td>{{ $n($f['convenios']) }}</td><td>{{ $n($f['ratificaciones']) }}</td><td>{{ $n($f['constancias']) }}</td><td>{{ $n($f['archivadas']) }}</td>
                    <td>{{ $pct($f['efectividad']) }}</td>
                </tr>
            @endforeach
            <tr class="re-total re-h47">
                <td>Total</td><td>{{ $n($t['personas']) }}</td><td>{{ $n($t['solicitudes']) }}</td><td>{{ $n($t['audiencias']) }}</td>
                <td>{{ $n($t['convenios']) }}</td><td>{{ $n($t['ratificaciones']) }}</td><td>{{ $n($t['constancias']) }}</td><td>{{ $n($t['archivadas']) }}</td>
                <td>{{ $pct($t['efectividad']) }}</td>
            </tr>
        </table>
        {!! $pie !!}
    </div>
    @endforeach

{{-- 7. Histórica --}}
@php($h = $historica)
<div class="re-lam" data-lamina="07-historica">
    {!! $marco !!}
    <table class="re-tabla" style="left:94px; top:165px; width:772px;">
        <colgroup><col style="width:70px"><col style="width:72px"><col style="width:80px"><col style="width:80px"><col style="width:80px"><col style="width:68px"><col style="width:80px"><col style="width:80px"><col style="width:108px"></colgroup>
        <tr class="re-banda"><th colspan="9" style="height:58px;">Histórica<br>({{ $historicoTexto }})</th></tr>
        <tr class="re-cols re-h54">
            <th style="width:70px">Personas Atendidas</th><th style="width:72px">Asesorías</th><th style="width:80px">Solicitudes Confirmadas</th><th style="width:80px">Audiencias Programadas</th><th style="width:80px">Constancias de No Conciliación</th>
            <th style="width:68px">Convenios</th><th style="width:80px">Archivadas por falta de interés</th><th style="width:80px">Porcentaje de Efectividad</th><th style="width:108px">Montos Convenidos</th>
        </tr>
        <tr class="re-h44">
            <td>{{ $n($h['personas']) }}</td><td>{{ $n($h['asesorias']) }}</td><td>{{ $n($h['solicitudes']) }}</td><td>{{ $n($h['audiencias']) }}</td>
            <td>{{ $n($h['constancias']) }}</td><td>{{ $n($h['convenios']) }}</td><td>{{ $n($h['archivadas']) }}</td>
            <td>{{ $pct($h['efectividad'], 2) }}</td><td>{{ $mon($h['montos']) }}</td>
        </tr>
    </table>
    {!! $pie !!}
</div>

{{-- 8. Histórico por delegación --}}
<div class="re-lam" data-lamina="08-historico-delegaciones">
    {!! $marco !!}
    <table class="re-tabla" style="left:190px; top:122px; width:580px;">
        <colgroup><col style="width:68px"><col style="width:76px"><col style="width:66px"><col style="width:68px"><col style="width:76px"><col style="width:76px"><col style="width:76px"><col style="width:74px"></colgroup>
        <tr class="re-banda"><th colspan="8" style="height:48px;">Histórico Desglosado por Delegación<br>({{ $historicoTexto }})</th></tr>
        <tr class="re-cols re-h56">
            <th style="width:68px">Delegación</th><th style="width:76px">Solicitudes Confirmadas</th><th style="width:66px">Incompet.</th><th style="width:68px">Convenios</th><th style="width:76px">Ratificaciones</th>
            <th style="width:76px">Constancias de No Conciliación</th><th style="width:76px">Archivadas por falta de interés</th><th style="width:74px">Efectividad</th>
        </tr>
        @foreach ($historicoDelegaciones as $nombre => $f)
            <tr class="re-h47">
                <td class="re-nombre">{{ $nombre }}</td><td>{{ $n($f['solicitudes']) }}</td><td>{{ $n($f['incompetencias']) }}</td><td>{{ $n($f['convenios']) }}</td>
                <td>{{ $n($f['ratificaciones']) }}</td><td>{{ $n($f['constancias']) }}</td><td>{{ $n($f['archivadas']) }}</td><td>{{ $pct($f['efectividad'], 2) }}</td>
            </tr>
        @endforeach
        @php($t = $historicoDelegacionesTotal)
        <tr class="re-total re-h47">
            <td>Total</td><td>{{ $n($t['solicitudes']) }}</td><td>{{ $n($t['incompetencias']) }}</td><td>{{ $n($t['convenios']) }}</td>
            <td>{{ $n($t['ratificaciones']) }}</td><td>{{ $n($t['constancias']) }}</td><td>{{ $n($t['archivadas']) }}</td><td>{{ $pct($t['efectividad'], 2) }}</td>
        </tr>
    </table>
    {!! $pie !!}
</div>

{{-- 9. Tiempo de resolución --}}
@php($ti = $tiempos)
<div class="re-lam" data-lamina="09-tiempo-resolucion">
    {!! $marco !!}
    <div class="re-titulo" style="top:66px;">Tiempo de Resolución<br><span class="re-sub">({{ $periodo }})</span></div>
    @foreach ([[102, 'Ratificaciones', $ti['graficaRatificaciones']], [490, 'Derivados de Conciliación', $ti['graficaDerivados']]] as [$izq, $nombre, $svg])
        <div class="re-marco-graf re-marco-graf--suave" style="left:{{ $izq }}px; top:150px; width:368px; height:238px;">
            <div class="re-graf-titulo">{{ $nombre }}</div>
        </div>
        <div class="re-graf" style="left:{{ $izq + 5 }}px; top:182px; width:358px; height:190px;">{!! $graf($svg) !!}</div>
    @endforeach
    <div class="re-texto re-centro" style="left:120px; top:432px; width:720px; font-size:12.5px;">
        @if ($ti['derivados35'] === null && $ti['ratificaciones5'] === null)
            No hay casos concluidos en este periodo.
        @else
            @if ($ti['derivados35'] !== null)El <b>{{ $ti['derivados35'] }}%</b> de los casos derivados de conciliación se resuelven en <b>menos de 35 días</b>@endif
            @if ($ti['derivados35'] !== null && $ti['ratificaciones5'] !== null)<br>y el @elseif ($ti['ratificaciones5'] !== null)El @endif
            @if ($ti['ratificaciones5'] !== null)<b>{{ $ti['ratificaciones5'] }}%</b> de las ratificaciones se concluyen en <b>menos de 5 días</b>@endif
        @endif
    </div>
</div>

{{-- 10. Audiencias por conciliador --}}
@php($anios = $audienciasAnio)
<div class="re-lam" data-lamina="10-audiencias-conciliador">
    {!! $marco !!}
    <div class="re-titulo" style="top:92px;">Cantidad de Audiencias por Conciliador en Michoacán</div>
    <table class="re-tabla re-tabla--oscura" style="left:238px; top:148px; width:507px;">
        <colgroup><col style="width:132px"><col style="width:219px"><col style="width:156px"></colgroup>
        <tr class="re-h61"><th>Total de Conciliadores</th><th>Audiencias<br>({{ $historicoTexto }})</th><th>Promedio de Audiencias por Conciliador</th></tr>
        <tr class="re-h44"><td>{{ $n($conciliadores) }}</td><td>{{ $n($audienciasHistoricas) }}</td><td>{{ $n($audienciasPromedio) }}</td></tr>
    </table>
    @php($anchoCol = 767 / max(1, count($anios) * 2))
    <table class="re-tabla re-tabla--oscura" style="left:108px; top:310px; width:767px;">
        <tr class="re-h24">
            @foreach ($anios as $a)<th colspan="2" style="font-weight:bold;">{{ $a['etiqueta'] }}</th>@endforeach
        </tr>
        <tr class="re-h50">
            @foreach ($anios as $a)<th style="width:{{ $anchoCol }}px;">Total</th><th style="width:{{ $anchoCol }}px;">Promedio Anual por conciliador</th>@endforeach
        </tr>
        <tr class="re-h26">
            @foreach ($anios as $a)<td>{{ $n($a['total']) }}</td><td>{{ $n($a['promedio']) }}</td>@endforeach
        </tr>
    </table>
</div>

{{-- 11. Crecimiento de conciliadores (tercera etapa) --}}
<div class="re-lam" data-lamina="11-crecimiento-conciliadores">
    {!! $marco !!}
    <div class="re-titulo" style="top:38px;">Crecimiento de Conciliadores (Tercera Etapa)</div>
    <div class="re-graf" style="left:216px; top:70px; width:528px; height:350px;">{!! $graf($x['graficaCrecimiento']) !!}</div>
    <div class="re-texto" style="left:73px; top:446px; width:796px;">{!! DR::conNegritas($tx['crecimiento']) !!}</div>
</div>

{{-- 12. Tabla tercera etapa --}}
@php($filasT = $x['tercera'])
@php($altoT = min(22, (int) floor(290 / max(1, count($filasT)))))
<div class="re-lam" data-lamina="12-conciliadores-estado">
    {!! $marco !!}
    <div class="re-titulo" style="top:92px;">Crecimiento en Conciliadores por Estado (Tercera Etapa)</div>
    <table class="re-tabla re-tabla--oscura" style="left:149px; top:140px; width:662px;">
        <colgroup><col style="width:120px"><col style="width:100px"><col style="width:100px"><col style="width:100px"><col style="width:120px"><col style="width:122px"></colgroup>
        <tr class="re-h53 re-cabeza-chica"><th style="width:120px">Entidad Federativa</th><th style="width:100px">Población</th><th style="width:100px">Conciliadores al inicio</th><th style="width:100px">Conciliadores actuales (A)</th><th style="width:120px">Población Económicamente Activa (B)</th><th style="width:122px">PEA por cada Conciliador (B/A)</th></tr>
        @foreach ($filasT as $e)
            @php($c = 'style=height:'.$altoT.'px')
            <tr class="re-chica {{ $e['estado'] === 'Michoacán' ? 're-destacado' : '' }}">
                <td {{ $c }}>{{ $e['estado'] }}</td><td {{ $c }}>{{ $n($e['poblacion']) }}</td><td {{ $c }}>{{ $n($e['inicio']) }}</td><td {{ $c }}>{{ $n($e['actuales']) }}</td><td {{ $c }}>{{ $n($e['pea']) }}</td><td {{ $c }}>{{ $n($e['pea_conciliador']) }}</td>
            </tr>
        @endforeach
    </table>
</div>

{{-- 13. PEA por estado --}}
<div class="re-lam" data-lamina="13-pea-estado">
    {!! $marco !!}
    <div class="re-titulo" style="top:38px;">Población Económicamente Activa por Estado (Tercera Etapa)</div>
    <div class="re-graf" style="left:187px; top:80px; width:560px; height:330px;">{!! $graf($x['graficaPea']) !!}</div>
    <div class="re-texto" style="left:130px; top:430px; width:700px;">
        <ul>@foreach (DR::vinetas($tx['pea']) as $l)<li>{!! DR::conNegritas($l) !!}</li>@endforeach</ul>
    </div>
</div>

{{-- 14. PEA por conciliador, todas las entidades --}}
@php($todos = $x['estados']->values())
@php($mitad = (int) ceil($todos->count() / 2))
@php($altoE = min(19, (int) floor(330 / max(1, $mitad))))
<div class="re-lam" data-lamina="14-pea-conciliador-tabla">
    {!! $marco !!}
    <div class="re-titulo" style="top:70px;">Población Económicamente Activa por Conciliador</div>
    @foreach ([[123, $todos->slice(0, $mitad)], [513, $todos->slice($mitad)]] as [$izq, $parte])
        <table class="re-tabla re-tabla--oscura" style="left:{{ $izq }}px; top:118px; width:345px;">
            <colgroup><col style="width:26px"><col style="width:98px"><col style="width:66px"><col style="width:80px"><col style="width:75px"></colgroup>
            <tr class="re-h42 re-cabeza-chica"><th style="width:26px">No.</th><th style="width:98px">Entidad Federativa</th><th style="width:66px">Concilia&shy;dores Actuales (A)</th><th style="width:80px">Población Económica&shy;mente Activa (B)</th><th style="width:75px">PEA por cada Conciliador</th></tr>
            @foreach ($parte as $i => $e)
                @php($c = 'style=height:'.$altoE.'px')
                <tr class="re-chica {{ $e['estado'] === 'Michoacán' ? 're-destacado' : '' }}">
                    <td {{ $c }}>{{ $i + 1 }}</td><td {{ $c }}>{{ $e['estado'] }}</td><td {{ $c }}>{{ $n($e['actuales']) }}</td><td {{ $c }}>{{ $n($e['pea']) }}</td><td {{ $c }}>{{ $n($e['pea_conciliador']) }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
</div>

{{-- 15. PEA por conciliador (gráfica) --}}
<div class="re-lam" data-lamina="15-pea-conciliador">
    {!! $marco !!}
    <div class="re-titulo" style="top:38px;">PEA por Conciliador (Tercera Etapa)<br><span class="re-sub">Miles de personas por conciliador</span></div>
    <div class="re-graf" style="left:187px; top:88px; width:560px; height:330px;">{!! $graf($x['graficaPeaConciliador']) !!}</div>
    <div class="re-texto" style="left:130px; top:440px; width:700px;">
        <ul>@foreach (DR::vinetas($tx['pea_conciliador']) as $l)<li>{!! DR::conNegritas($l) !!}</li>@endforeach</ul>
    </div>
</div>

{{-- 16. Mapa: población y empresas --}}
@php($pob = $x['poblacion'])
{{-- Donde iba cada dato en el mapa original (px sobre el mapa de 890 × 501). --}}
@php($etiquetas = ['Zamora' => [93, 54], 'Sahuayo' => [131, 147], 'Uruapan' => [87, 236], 'Morelia' => [620, 45], 'Zitácuaro' => [790, 163], 'Lázaro Cárdenas' => [538, 426]])
<div class="re-lam" data-lamina="16-mapa">
    <div class="re-b re-b1"></div><div class="re-b re-b2"></div><div class="re-b re-b3"></div><div class="re-b re-b4"></div>
    <img class="re-mapa" src="{{ $img('mapa_michoacan.jpg') }}" alt="Mapa de delegaciones">
    @foreach ($etiquetas as $sede => [$l, $t])
        @if ($p = $pob[$sede] ?? null)
            <div class="re-mapa-dato" style="left:{{ 50 + $l }}px; top:{{ 28 + $t }}px;">
                <b>Población:</b> {{ $n($p['poblacion']) }}<br><b>Empresas:</b> {{ $n($p['empresas']) }}
            </div>
        @endif
    @endforeach
</div>

{{-- 17. Conclusión --}}
<div class="re-lam" data-lamina="17-conclusion">
    {!! $marco !!}
    <div class="re-titulo" style="top:80px; font-size:19px; font-weight:bold;">Conclusión del Análisis</div>
    <div class="re-texto" style="left:130px; top:140px; width:700px; font-size:14px; line-height:1.6; color:#444;">
        <p style="margin:0 0 14px;">{!! DR::conNegritas($tx['conclusion_intro']) !!}</p>
        <ul style="padding-left:22px;">@foreach (DR::vinetas($tx['conclusion']) as $l)<li>{!! DR::conNegritas($l) !!}</li>@endforeach</ul>
    </div>
</div>

{{-- 18. Cierre --}}
<div class="re-lam re-lam--portada" data-lamina="18-cierre">
    <div class="re-b re-b1"></div><div class="re-b re-b2"></div><div class="re-b re-b3"></div><div class="re-b re-b4"></div>
    <img src="{{ $img('logos_cierre.png') }}" alt="" style="position:absolute; left:322px; top:175px; width:316px; height:158px;">
</div>
