{{--
    Hoja de una sede de la agenda de turnos (App\Exports\AgendaTurnosSedeSheet).
    Fila 1 título, fila 2 filtros, fila 3 encabezados (fija y con filtro).
    Los días se alternan con un fondo muy claro para distinguirlos.
--}}
@php
    use App\Support\AgendaTurnos;
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD;';
@endphp
<table>
    <tr>
        <td colspan="18" style="font-size: 15px; font-weight: bold; color: #354647; height: 26px;">{{ $titulo }} · {{ $sede }}</td>
    </tr>
    <tr>
        <td colspan="18" style="color: #5F6B6C;">{{ $periodo }} · {{ $excepcion }}{{ $pruebas ? ' · con citas de prueba' : '' }} · Generado el {{ $generado->format('d/m/Y H:i') }} por {{ $por }}</td>
    </tr>
    <tr>
        <th width="22" style="{{ $th }}">Día</th>
        <th width="8" style="{{ $th }}">Hora</th>
        <th width="14" style="{{ $th }}">Módulo</th>
        <th width="15" style="{{ $th }}">Folio</th>
        <th width="13" style="{{ $th }}">Trámite</th>
        <th width="34" style="{{ $th }}">Persona que llega</th>
        <th width="11" style="{{ $th }}">Origen</th>
        <th width="28" style="{{ $th }}">Le atiende (asignado)</th>
        <th width="9" style="{{ $th }}">Llegó</th>
        <th width="9" style="{{ $th }}">Lo llamaron</th>
        <th width="10" style="{{ $th }}">Inicio de atención</th>
        <th width="10" style="{{ $th }}">Fin de atención</th>
        <th width="9" style="{{ $th }}">Minutos</th>
        <th width="28" style="{{ $th }}">Lo atendió</th>
        <th width="11" style="{{ $th }}">Estatus</th>
        <th width="11" style="{{ $th }}">Caso de excepción</th>
        <th width="30" style="{{ $th }}">Motivo de excepción</th>
        <th width="26" style="{{ $th }}">Turno creado por</th>
    </tr>
    @forelse (collect($dias)->values() as $i => $turnos)
        @php($fondo = $i % 2 ? '#F6F8F8' : '#FFFFFF')
        @foreach ($turnos as $t)
            @php($color = AgendaTurnos::COLORES[$t['estatus_clave']] ?? ['#FFFFFF', '#354647'])
            @php($base = $td.' background-color: '.($t['excepcion'] ? AgendaTurnos::COLOR_EXCEPCION : $fondo).';')
            <tr>
                <td style="{{ $base }}">{{ $t['dia'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['hora'] }}</td>
                <td style="{{ $base }}">{{ $t['modulo'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['folio'] }}</td>
                <td style="{{ $base }}">{{ $t['tipo'] }}</td>
                <td style="{{ $base }} font-weight: bold;">{{ $t['persona'] }}</td>
                <td style="{{ $base }}">{{ $t['origen'] }}</td>
                <td style="{{ $base }}">{{ $t['asignado'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['llego'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['llamado'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['inicio'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['fin'] }}</td>
                <td style="{{ $base }} text-align: center;">{{ $t['minutos'] }}</td>
                <td style="{{ $base }}">{{ $t['atendio'] }}</td>
                <td style="{{ $td }} background-color: {{ $color[0] }}; color: {{ $color[1] }}; font-weight: bold; text-align: center;">{{ $t['estatus'] }}</td>
                <td style="{{ $base }} text-align: center;{{ $t['excepcion'] ? ' color: #8C1D40; font-weight: bold;' : '' }}">{{ $t['excepcion'] ? 'Sí' : 'No' }}</td>
                <td style="{{ $base }}">{{ $t['motivo'] }}</td>
                <td style="{{ $base }}">{{ $t['creo'] }}</td>
            </tr>
        @endforeach
    @empty
        <tr><td colspan="18" style="color: #8A9899;">Sin turnos en este periodo.</td></tr>
    @endforelse
</table>
