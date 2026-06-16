<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div>Ratificación</div>
        <table>
            <thead style="background-color: #869b9c;">
                <tr>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Fecha</th>
                    <th width="10" style="background-color: #869b9c; color: #ffffff;">Hora</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">Tipo</th>
                    <th width="60" style="background-color: #869b9c; color: #ffffff;">Empleador</th>
                    <th width="60" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>
                    <th width="50" style="background-color: #869b9c; color: #ffffff;">Motivo</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Monto Total</th> 
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Giro Comercial</th>
                    <!--
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Parcialidades Totales</th>                   
                    <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pendientes</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Monto Pendiente</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pagadas</th>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Monto Pagado</th>
                    -->
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Estatus</th>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Conciliador</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPrice = 0;
                @endphp
                @foreach($Ratificacion as $estadistica)
                    <tr>
                        <td style=" text-align: center;">{{ $estadistica->fecha }}</td>
                        <td style=" text-align: center;">{{ $estadistica->hora}}</td>
                        <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                        <td style=" text-align: center;">Patronal</td>
                        <td style=" text-align: center;">{{ $estadistica->empresa }} {{ $estadistica->primero_empresa }} {{ $estadistica->segundo_empresa }}</td>
                        <td style=" text-align: center;">{{ $estadistica->trabajador }} {{ $estadistica->primero_trabajador }} {{ $estadistica->segundo_trabajador }}</td>
                        <td style=" text-align: center;">{{ $estadistica->motivo }}</td>
                        <td style=" text-align: center;">${{ number_format($estadistica->monto, 2) }}</td>
                        <td style=" text-align: center;">{{ $estadistica->categoria }}</td>
                        <!--
                        <td style=" text-align: center;">{{ $estadistica->pagos_pendientes_count + $estadistica->pagos_pagados_count }}</td>
                        <td style=" text-align: center;">{{ $estadistica->pagos_pendientes_count }}</td>
                        <td style=" text-align: center;">${{ number_format($estadistica->monto_pendientes, 2) }}</td>
                        <td style=" text-align: center;">{{ $estadistica->pagos_pagados_count }}</td>
                        <td style=" text-align: center;">${{ number_format($estadistica->monto_pagados, 2) }}</td>
                        -->
                        <td style=" text-align: center;">{{ $estadistica->estatus }}</td>
                        <td style=" text-align: center;">{{ $estadistica->conciliador_name }}</td>
                    </tr>
                    @php
                        // Suma los valores para el total
                        $totalPrice += $estadistica->monto;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold;">Total :</td>
                    <td style="font-weight: bold;">{{ number_format($totalPrice, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>