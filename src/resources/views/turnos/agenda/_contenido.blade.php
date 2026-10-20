{{--
    Agenda de turnos para PDF (dompdf) y para la imagen (html2canvas): sólo
    tablas y CSS sencillo para que se vea igual en los dos.
    Datos: App\Support\AgendaTurnos::armar.
--}}
@php use App\Support\AgendaTurnos; @endphp
<style>
    .ag { font-family: DejaVu Sans, Arial, sans-serif; color: #2E3C3D; font-size: 9px; }
    .ag-cabeza { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .ag-cabeza td { vertical-align: bottom; padding: 0; }
    .ag-titulo { font-size: 17px; font-weight: bold; color: #354647; }
    .ag-sub { font-size: 11px; font-weight: bold; color: #496163; margin-top: 2px; }
    .ag-filtros { font-size: 9px; color: #6B7A7B; margin-top: 2px; }
    .ag-generado { font-size: 8px; color: #8A9899; text-align: right; }
    .ag-barra { height: 4px; background: #496163; margin: 4px 0 10px; }
    .ag-barra span { display: block; width: 90px; height: 4px; background: #CEA845; }
    .ag-tabla { width: 100%; border-collapse: collapse; }
    .ag-tabla th { background: #496163; color: #fff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 4px 4px; text-align: left; border: 1px solid #3A4E50; }
    .ag-tabla td { padding: 3px 4px; border: 1px solid #DDE4E4; vertical-align: top; }
    .ag-tabla .c { text-align: center; }
    .ag-tabla tr.ag-exc td { background: {{ AgendaTurnos::COLOR_EXCEPCION }}; }
    .ag-tabla small { color: #6B7A7B; font-size: 7.5px; }
    .ag-resumen td, .ag-resumen th { text-align: center; }
    .ag-resumen td:first-child, .ag-resumen th:first-child { text-align: left; }
    .ag-resumen tr.ag-total td { font-weight: bold; background: #EEF2F2; }
    .ag-sede { font-size: 13px; font-weight: bold; color: #fff; background: #354647; padding: 5px 8px; margin: 14px 0 0; }
    .ag-tabla--dia { margin-top: 8px; }
    .ag-tabla td.ag-dia { font-size: 10.5px; font-weight: bold; color: #354647; background: #EEF2F2; padding: 5px 8px; border: 0; border-left: 4px solid #CEA845; }
    .ag-dia span { font-weight: normal; color: #6B7A7B; }
    .ag-chip { font-weight: bold; padding: 1px 5px; border-radius: 6px; font-size: 8px; white-space: nowrap; }
    .ag-exc-chip { color: #8C1D40; font-weight: bold; font-size: 8px; }
    .ag-vacio { color: #8A9899; padding: 6px 8px; font-style: italic; }
    .ag-leyenda { margin-top: 6px; font-size: 8px; color: #6B7A7B; }
    .ag-leyenda .ag-chip { margin-right: 2px; }
    .ag-salto { page-break-before: always; }
</style>

<div class="ag">
    <table class="ag-cabeza">
        <tr>
            <td>
                <div class="ag-titulo">{{ $titulo }}</div>
                <div class="ag-sub">{{ $periodo }}</div>
                <div class="ag-filtros">{{ $sedesTexto }} · {{ $excepcion }}{{ $pruebas ? ' · con citas de prueba' : '' }}</div>
            </td>
            <td class="ag-generado">
                Centro de Conciliación Laboral del Estado de Michoacán<br>
                Generado el {{ $generado->format('d/m/Y H:i') }} por {{ $por }}
            </td>
        </tr>
    </table>
    <div class="ag-barra"><span></span></div>

    {{-- Resumen --}}
    <table class="ag-tabla ag-resumen">
        <thead>
            <tr>
                <th>Sede</th><th>Turnos</th><th>Pendientes</th><th>Llegaron</th><th>Atendidos</th><th>No llegaron</th><th>Casos de excepción</th><th>En línea</th><th>Ventanilla</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumen as $r)
                <tr class="{{ $r['sede'] === 'Total' ? 'ag-total' : '' }}">
                    <td><b>{{ $r['sede'] }}</b></td>
                    <td><b>{{ $r['total'] }}</b></td>
                    <td>{{ $r['pendiente'] }}</td>
                    <td>{{ $r['confirmada'] }}</td>
                    <td>{{ $r['atendido'] }}</td>
                    <td>{{ $r['expirada'] }}</td>
                    <td>{{ $r['excepcion'] }}</td>
                    <td>{{ $r['linea'] }}</td>
                    <td>{{ $r['ventanilla'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="ag-leyenda">
        @foreach (AgendaTurnos::ESTATUS as $clave => $texto)
            <span class="ag-chip" style="background: {{ AgendaTurnos::COLORES[$clave][0] }}; color: {{ AgendaTurnos::COLORES[$clave][1] }};">{{ $texto }}</span>
        @endforeach
        &nbsp; <span class="ag-chip" style="background: {{ AgendaTurnos::COLOR_EXCEPCION }}; color: #8C1D40;">Caso de excepción</span>
        &nbsp; "Llegó" es la hora en que recepción validó su cita.
    </div>

    {{-- Detalle por sede y día --}}
    @foreach ($porSede as $sede => $dias)
        <div class="ag-sede {{ ! empty($saltoPorSede) && ! $loop->first ? 'ag-salto' : '' }}">{{ $sede }} · {{ collect($dias)->flatten(1)->count() }} turnos</div>
        @forelse ($dias as $fecha => $turnos)
            {{-- El día va dentro del encabezado: si la tabla sigue en otra página, se repite. --}}
            <table class="ag-tabla ag-tabla--dia">
                <thead>
                    <tr><td colspan="10" class="ag-dia">{{ AgendaTurnos::dia($fecha) }} <span>· {{ $sede }} · {{ count($turnos) }} {{ count($turnos) === 1 ? 'turno' : 'turnos' }}</span></td></tr>
                    <tr>
                        <th style="width: 5%;">Hora</th>
                        <th style="width: 8%;">Módulo</th>
                        <th style="width: 10%;">Folio</th>
                        <th style="width: 8%;">Trámite</th>
                        <th style="width: 20%;">Persona que llega</th>
                        <th style="width: 15%;">Le atiende</th>
                        <th style="width: 5%;">Llegó</th>
                        <th style="width: 8%;">Atención</th>
                        <th style="width: 13%;">Lo atendió</th>
                        <th style="width: 8%;">Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($turnos as $t)
                        @php($color = AgendaTurnos::COLORES[$t['estatus_clave']] ?? ['#FFFFFF', '#354647'])
                        <tr class="{{ $t['excepcion'] ? 'ag-exc' : '' }}">
                            <td class="c"><b>{{ $t['hora'] }}</b></td>
                            <td>{{ $t['modulo'] }}</td>
                            <td>{{ $t['folio'] }}<br><small>{{ $t['origen'] }}</small></td>
                            <td>{{ $t['tipo'] }}</td>
                            <td>
                                <b>{{ $t['persona'] }}</b>
                                @if ($t['excepcion'])<br><span class="ag-exc-chip">Caso de excepción:</span> <small>{{ $t['motivo'] }}</small>@endif
                            </td>
                            <td>{{ $t['asignado'] }}</td>
                            <td class="c">{{ $t['llego'] ?: '—' }}</td>
                            <td class="c">
                                @if ($t['inicio']){{ $t['inicio'] }}{{ $t['fin'] ? '–'.$t['fin'] : '' }}@if ($t['minutos'] !== null)<br><small>{{ $t['minutos'] }} min</small>@endif
                                @else — @endif
                            </td>
                            <td>{{ $t['atendio'] ?: '—' }}</td>
                            <td class="c"><span class="ag-chip" style="background: {{ $color[0] }}; color: {{ $color[1] }};">{{ $t['estatus'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <div class="ag-vacio">Sin turnos en este periodo.</div>
        @endforelse
    @endforeach
    @if (! $porSede)
        <div class="ag-vacio">No hay turnos con esos filtros.</div>
    @endif
</div>
