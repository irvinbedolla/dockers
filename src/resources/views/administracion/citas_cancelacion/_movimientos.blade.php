{{--
    Pestaña Movimientos de Cancelación/Reagenda (CancelacionCitasController::index):
    cada cancelación y reagenda hecha desde la pantalla (historial_administracion).
    Espera $f, $movimientos (paginados), $resumen, $usuarios, $rango, $ruta y $barras.
--}}
@php
    use App\Support\CitasCancelacion as CC;

    $kpis = [
        ''            => ['Movimientos', $resumen['total']],
        'cancelacion' => ['Cancelaciones', $resumen['cancelacion']],
        'reagenda'    => ['Reagendas', $resumen['reagenda']],
    ];
    $porUsuario = [];
    foreach ($resumen['porUsuario'] as $nombre => $n) {
        $porUsuario[$nombre] = $n;
    }
    $idUsuario = array_flip($usuarios);
@endphp

<div class="rg-kpis">
    @foreach ($kpis as $clave => [$texto, $n])
        <a href="{{ $ruta(['movimiento' => $clave ?: null, 'page' => null]) }}"
           class="rg-kpi rg-kpi--{{ $clave ?: 'total' }} {{ ($f['movimiento'] ?? '') === $clave ? 'is-activo' : '' }}"
           @if (($f['movimiento'] ?? '') === $clave) aria-current="true" @endif>
            <b>{{ number_format($n) }}</b><span>{{ $texto }}</span>
        </a>
    @endforeach
    <div class="rg-kpi"><b>{{ number_format($resumen['citas']) }}</b><span>Citas distintas</span></div>
    <div class="rg-kpi" title="Cuántos días se movieron las citas reagendadas, en promedio">
        <b>{{ $resumen['diasPromedio'] === null ? '—' : ($resumen['diasPromedio'] > 0 ? '+' : '').$resumen['diasPromedio'] }}</b><span>Días que se mueve una reagenda</span>
    </div>
</div>

@if ($resumen['total'])
    <div class="rg-desglose">
        <div class="rg-caja"><h6>Por sede</h6>{!! $barras($resumen['porSede'], 'sede') !!}</div>
        <div class="rg-caja"><h6>Por trámite</h6>{!! $barras($resumen['porTramite'], 'tramite') !!}</div>
        <div class="rg-caja">
            <h6>Quién los hizo</h6>
            @php $max = max($porUsuario ?: [1]); @endphp
            @foreach ($porUsuario as $nombre => $n)
                <div class="rg-barra-fila" style="--p: {{ round($n * 100 / max($max, 1)) }}%">
                    @if (isset($idUsuario[$nombre]))
                        <a href="{{ $ruta(['usuario' => $idUsuario[$nombre], 'page' => null]) }}" title="Ver sólo los de {{ $nombre }}">{{ $nombre }}</a>
                    @else
                        <span class="rg-barra-nombre">{{ $nombre }}</span>
                    @endif
                    <b>{{ $n }}</b><i></i>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="rg-titulo-fila">
    <p class="rg-titulo">
        {{ $f['buscando'] ? 'Movimientos de “'.$f['buscar'].'”' : $rango }}
        <small>· {{ number_format($movimientos->total()) }} {{ $movimientos->total() === 1 ? 'movimiento' : 'movimientos' }}</small>
    </p>
</div>

@if ($movimientos->isEmpty())
    <p class="text-muted mb-0">No hay cancelaciones ni reagendas con estos filtros.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle rg-tabla">
            <thead><tr><th>Cuándo</th><th>Movimiento</th><th>Folio</th><th>Solicitante</th><th>Sede</th><th>Cita</th><th>Motivo</th><th>Hecho por</th><th>Hoy</th></tr></thead>
            <tbody>
            @foreach ($movimientos as $m)
                <tr>
                    <td class="text-nowrap">{{ $m['cuando']->format('d/m/Y') }}<span class="rg-contacto">{{ $m['cuando']->format('H:i') }}</span></td>
                    <td><span class="rg-chip rg-chip--{{ $m['movimiento'] }}">{{ CC::MOVIMIENTOS[$m['movimiento']] }}</span></td>
                    <td class="text-nowrap">
                        <a href="{{ route('citas_cancelacion.index', ['buscar' => $m['folio']]) }}" title="Ver la cita">{{ $m['folio'] }}</a>
                    </td>
                    <td>
                        {{ $m['solicitante'] }}
                        @if ($m['contacto'])<span class="rg-contacto">{{ $m['contacto'] }}</span>@endif
                    </td>
                    <td>{{ $m['sede'] }}<span class="rg-contacto">{{ $m['tramite'] }}{{ $m['origen'] !== '—' ? ' · '.$m['origen'] : '' }}</span></td>
                    <td class="rg-cambio">
                        @if ($m['movimiento'] === 'reagenda')
                            <s title="Antes">{{ $m['antes'] }}</s>
                            <b title="Nueva"><i class="bi bi-arrow-return-right" aria-label="nueva:"></i> {{ $m['despues'] }}</b>
                            @if ($m['dias'] !== null)
                                <span class="rg-dias">{{ $m['dias'] === 0 ? 'mismo día' : ($m['dias'] > 0 ? '+' : '').$m['dias'].' '.(abs($m['dias']) === 1 ? 'día' : 'días') }}</span>
                            @endif
                            @foreach ($m['otros'] as $etiqueta => [$antesO, $ahoraO])
                                <span class="rg-dias rg-otro">{{ $etiqueta }}: {{ $antesO }} → <b>{{ $ahoraO }}</b></span>
                            @endforeach
                        @else
                            <s>{{ $m['antes'] }}</s>
                        @endif
                    </td>
                    <td class="rg-motivo-txt">{{ $m['motivo'] }}</td>
                    <td>{{ $m['quien'] }}</td>
                    <td>
                        @if ($m['estatusHoy'])
                            <span class="rg-estatus rg-estatus--{{ $m['estatusHoy'] }}">{{ $m['estatusHoy'] }}</span>
                        @else
                            <span class="rg-sin-accion">Ya no existe</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="rg-pie-tabla">
        <span class="rg-sin-accion">Mostrando {{ $movimientos->firstItem() }}–{{ $movimientos->lastItem() }} de {{ number_format($movimientos->total()) }}</span>
        {{ $movimientos->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif
