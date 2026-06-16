<table>
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: #ffffff; font-weight: bold; text-align: center;" colspan="6">
                RESUMEN DE NOTIFICACIONES POR NOTIFICADOR (Filtrado por domicilio)
            </th>
        </tr>
        <tr>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">Notificador</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Total Citatorios</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Exitosas</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">No Exitosas</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Pendientes</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Exhortos</th>
        </tr>
    </thead>
    <tbody>
        @foreach($totales as $t)
        <tr>
            <td>{{ $t['nombre'] }}</td>
            <td style="text-align: center;">{{ $t['total'] }}</td>
            <td style="text-align: center; color: #008000;">{{ $t['notificadas'] }}</td>
            <td style="text-align: center; color: #FF0000;">{{ $t['no_notificadas'] }}</td>
            <td style="text-align: center; color: #000;">{{ $t['pendientes'] }}</td>
            <td style="text-align: center; color: #000;">{{ $t['exhorto'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>