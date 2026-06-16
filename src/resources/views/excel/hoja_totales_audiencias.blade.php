<table>
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: #ffffff; font-weight: bold; text-align: center;" colspan="7">
                RESUMEN DE AUDIENCIAS POR CONCILIADOR
            </th>
        </tr>
        <tr>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">Conciliador</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Audiencias Programadas</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Audiencias Celebradas</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Finalización 1 audiencia</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Finalización 2 audiencias</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Finalización 3 audiencias</th>
            <!--<th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Total Audiencias</th>-->
        </tr>
    </thead>
    <tbody>
        @foreach($totales as $t)
        <tr>
            <td>{{ $t['nombre'] }}</td>
            <td style="text-align: center;">{{ $t['programadas'] }}</td>
            <td style="text-align: center;">{{ $t['celebradas'] }}</td>
            <td style="text-align: center;">{{ $t['final_1'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $t['final_2'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $t['final_3'] ?? 0 }}</td>
            <!--<td style="text-align: center;">{{ $t['total'] }}</td>-->
        </tr>
        @endforeach
    </tbody>
</table>