<table>
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: #ffffff; font-weight: bold; text-align: center;" colspan="5">
                RESUMEN DE EMPLEADOS SIN SEGURO SOCIAL
            </th>
        </tr>
        <tr>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 200px;">NUE</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Delegacion</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 400px;">Nombre Citado</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 400px;">Nombre Empresa</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 400px;">Nombre Representante</th>

        </tr>
    </thead>
    <tbody>
        @foreach($empresas as $solicitud)
            <tr>
                <td style=" text-align: center;">{{ $solicitud->NUE}}</td>
                <td style=" text-align: center;">{{ $solicitud->delegacion}}</td>
                <td style=" text-align: center;">{{ $solicitud->nombre_citado}}</td>
                <td style=" text-align: center;">{{ $solicitud->nombre_abogado}}</td>
                <td style=" text-align: center;">{{ $solicitud->nombre_representante}}</td>
            </tr>
        @endforeach
    </tbody>
</table>