{{-- Hoja "Resumen" de la agenda de turnos (App\Exports\AgendaTurnosResumenSheet). --}}
@php
    use App\Support\AgendaTurnos;
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD; text-align: center;';
@endphp
<table>
    <tr><td colspan="9" style="font-size: 16px; font-weight: bold; color: #354647; height: 28px;">{{ $titulo }} · Centro de Conciliación Laboral del Estado de Michoacán</td></tr>
    <tr><td colspan="9" style="font-size: 12px; font-weight: bold; color: #496163;">{{ $periodo }}</td></tr>
    <tr><td colspan="9" style="color: #5F6B6C;">{{ $sedesTexto }} · {{ $excepcion }}{{ $pruebas ? ' · con citas de prueba' : '' }}</td></tr>
    <tr><td colspan="9" style="color: #8A9899;">Generado el {{ $generado->format('d/m/Y H:i') }} por {{ $por }}. Cada sede tiene su hoja con un renglón por turno.</td></tr>
    <tr><td colspan="9"></td></tr>
    <tr>
        <th width="22" style="{{ $th }}">Sede</th>
        <th width="11" style="{{ $th }}">Turnos</th>
        <th width="12" style="{{ $th }}">Pendientes</th>
        <th width="12" style="{{ $th }}">Llegaron</th>
        <th width="12" style="{{ $th }}">Atendidos</th>
        <th width="12" style="{{ $th }}">No llegaron</th>
        <th width="14" style="{{ $th }}">Casos de excepción</th>
        <th width="12" style="{{ $th }}">En línea</th>
        <th width="12" style="{{ $th }}">Ventanilla</th>
    </tr>
    @foreach ($resumen as $r)
        @php($negrita = $r['sede'] === 'Total' ? ' font-weight: bold; background-color: #EEF2F2;' : '')
        <tr>
            <td style="{{ $td }} text-align: left;{{ $negrita }}">{{ $r['sede'] }}</td>
            <td style="{{ $td }} font-weight: bold;{{ $negrita }}">{{ $r['total'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['pendiente'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['confirmada'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['atendido'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['expirada'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['excepcion'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['linea'] }}</td>
            <td style="{{ $td }}{{ $negrita }}">{{ $r['ventanilla'] }}</td>
        </tr>
    @endforeach
    @if (count($porDia) > 1)
        <tr><td colspan="9"></td></tr>
        <tr>
            <th style="{{ $th }}">Día</th>
            <th style="{{ $th }}">Turnos</th>
            <th style="{{ $th }}">Atendidos</th>
            <th style="{{ $th }}">No llegaron</th>
            <th style="{{ $th }}">Casos de excepción</th>
        </tr>
        @foreach ($porDia as $d)
            <tr>
                <td style="{{ $td }} text-align: left;">{{ $d['dia'] }}</td>
                <td style="{{ $td }} font-weight: bold;">{{ $d['total'] }}</td>
                <td style="{{ $td }}">{{ $d['atendido'] }}</td>
                <td style="{{ $td }}">{{ $d['expirada'] }}</td>
                <td style="{{ $td }}">{{ $d['excepcion'] }}</td>
            </tr>
        @endforeach
    @endif
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9" style="font-weight: bold; color: #354647;">Qué significa cada estatus</td></tr>
    @foreach (['pendiente' => 'Tiene turno y todavía no se presenta.', 'confirmada' => 'Ya llegó y recepción validó su cita; espera a que lo atiendan.', 'atendido' => 'Ya lo atendieron.', 'expirada' => 'No se presentó.'] as $clave => $texto)
        <tr>
            <td style="{{ $td }} background-color: {{ AgendaTurnos::COLORES[$clave][0] }}; color: {{ AgendaTurnos::COLORES[$clave][1] }}; font-weight: bold;">{{ AgendaTurnos::ESTATUS[$clave] }}</td>
            <td colspan="8" style="color: #5F6B6C;">{{ $texto }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="{{ $td }} background-color: {{ AgendaTurnos::COLOR_EXCEPCION }}; color: #8C1D40; font-weight: bold;">Excepción</td>
        <td colspan="8" style="color: #5F6B6C;">Renglón de un caso de excepción (art. 685 Ter LFT).</td>
    </tr>
</table>
