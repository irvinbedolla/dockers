<table>
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: #ffffff; font-weight: bold; text-align: center;" colspan="17">
                RESUMEN DE SOLICITUDES Y RATIFICACIONES
            </th>
        </tr>
        <tr>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">NUE</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Clave</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Mes de Registro</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Año de Registro</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Entidad de Registro</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Municipio de Registro</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Municipio de ubicación del establecimiento</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Total de Trabajadores que participaron en la conciliación</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Total de trabajadores hombres</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Total de trabajadores mujeres</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">Total de trabajadores sexo no especificado</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Sexo de trabajador</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">Actividad Economica</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 300px;">Razón social</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Motivo del Convenio</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Monto del pago</th>
            <th style="background-color: #D3D3D3; font-weight: bold; width: 150px;">Estatus Expediente</th>

        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
            @endphp
            @foreach($reportes as $solicitud)
                <tr>
                    <td style=" text-align: center;">{{ $solicitud->NUE}}</td>
                    <td style=" text-align: center;">29</td>
                    <td style=" text-align: center;">{{ $solicitud->mes}}</td>
                    <td style=" text-align: center;">{{ $solicitud->año}}</td>
                    <td style=" text-align: center;">{{ $solicitud->estado}}</td>
                    <td style=" text-align: center;">{{ $solicitud->municipio}}</td>
                    <th style=" text-align: center;">{{ $solicitud->municipio_abogado }}</th>
                    <th style=" text-align: center;">1</th>
                    <th style=" text-align: center;">{{ ($solicitud->sexo ?? '') == 'H' ? 1 : '0' }}</th>
                    <th style=" text-align: center;">{{ ($solicitud->sexo ?? '') == 'M' ? 1 : '0' }}</th>
                    <th style=" text-align: center;">{{ ($solicitud->sexo ?? '') == 'NE' ? 1 : '0' }}</th>
                    <th style=" text-align: center;">{{ $solicitud->sexo }}</th>
                    <th style=" text-align: center;">{{ $solicitud->giroComercial }}</th>
                    <th style=" text-align: center;">{{ $solicitud->nombres_patronal }}{{ $solicitud->primer_apellido_patronal }}{{ $solicitud->segundo_apellido_patronal  }}</th>
                    <th style=" text-align: center;">{{ $solicitud->motivo }}</th>
                    <th style=" text-align: center;">${{ number_format($solicitud->total, 2) }}</th>
                    {{--<th style=" text-align: center;">{{ $solicitud->estatus }}</th>--}}
                    <th style="text-align: center;">{{ $solicitud->estatus == 'Concluida' ? 'Conciliacion' : $solicitud->estatus }}</th>
                    @php
                        $total = $total + $solicitud->total;
                @endphp
                </tr>
            @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td></td><td></td>
            <td></td><td></td>
            <td></td><td></td>
            <td></td><td></td>
            <td></td><td></td>
            <td></td><td></td>
            <td></td><td></td>
            <td style="font-weight: bold;">Total :</td>
            <td style="font-weight: bold;">{{ number_format($total, 2) }}</td>
        </tr>
    </tfoot>
</table>