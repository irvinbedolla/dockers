<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div>Solicitudes</div>
        <table>
            <thead style="background-color: #869b9c;">
                <tr>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">Fecha Confirmación</th>
                    <th width="60" style="background-color: #869b9c; color: #ffffff;">Motivo Solicitud</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">Genero</th>
                    <th width="60" style="background-color: #869b9c; color: #ffffff;">Empleador</th>
                    <th width="60" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">Teléfono</th>
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Giro Comercial</th>       
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Tipo Solicitud</th>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Estatus Solicitud</th>     
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPrice = 0;
                @endphp
                @foreach($Solicitudes as $estadistica)
                    <tr>
                        <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                        <td style=" text-align: center;">{{ $estadistica->fecha_confirmacion }}</td>
                        <td style=" text-align: center;">{{ $estadistica->motivos }}</td>
                        <td style=" text-align: center;">{{ $estadistica->sexo }}</td>
                        <td style=" text-align: center;">{{ $estadistica->primer_citado }}</td>
                        <td style=" text-align: center;">{{ $estadistica->solicitante_nombre }}</td>
                        <td style=" text-align: center;">{{ $estadistica->telefono1 }}</td>
                        <td style=" text-align: center;">{{ $estadistica->actividad }}</td>
                        <td style=" text-align: center;">
                            @if($estadistica->tipo_solicitud == '1')
                                TRABAJADOR
                            @elseif($estadistica->tipo_solicitud == '2')
                                PATRONAL    
                            @endif
                        </td>
                        <td style=" text-align: center;">{{ $estadistica->estatus }}</td>
                    </tr>
                @endforeach
            </tbody>
            
        </table>
    </body>
</html>