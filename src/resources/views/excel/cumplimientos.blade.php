<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        
    <div>Cumplimiento en Ratificación</div>
    <table>
        <thead style="background-color: #869b9c;">
            <tr>
                <th width="15" style="background-color: #869b9c; color: #ffffff;">Fecha</th>
                <th width="10" style="background-color: #869b9c; color: #ffffff;">Hora</th>
                <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                <!--
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Empleador</th>
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>              
                <th width="30" style="background-color: #869b9c; color: #ffffff;">Descripción</th>
                <th width="15" style="background-color: #869b9c; color: #ffffff;">Conciliador</th>
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Giro Comercial</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Delegacion</th>
                -->
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Totales</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Total</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pendientes</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Pendientes</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pagadas</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Pagadas</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPrice = 0;
            @endphp
             @foreach($pagosRatificacion as $estadistica)
                <tr>
                    <td style=" text-align: center;">{{ $estadistica->fecha ? date_format($estadistica->fecha,'d-m-Y') : "--"}}</td>
                    <td style=" text-align: center;">{{ $estadistica->hora  ? date_format($estadistica->hora, 'H:i:s') : "--" }}</td>
                    <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                    <!--
                    <td style=" text-align: center;">{{ $estadistica->empresa }} {{ $estadistica->primero_empresa }} {{ $estadistica->segundo_empresa }}</td>
                    <td style=" text-align: center;">{{ $estadistica->trabajador }} {{ $estadistica->primero_trabajador }} {{ $estadistica->segundo_trabajador }}</td>
                    <td style=" text-align: center;">{{ $estadistica->descripcion }}</td>
                    <td style=" text-align: center;">{{ $estadistica->conciliador_name }}</td>
                    <td style=" text-align: center;">{{ $estadistica->giroComercial }}</td>
                    <td style=" text-align: center;">{{ $estadistica->turno_delegacion }}</td>
                    -->
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pendientes + $estadistica->cantidad_pagados }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pendiente+$estadistica->monto_pagado , 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pendientes }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pendiente, 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pagados }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pagado, 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->estatus }}</td>
                </tr>
                @php
                    // Suma los valores para el total
                    $totalPrice += $estadistica->monto_totalR;
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
                <td style="font-weight: bold;">Total :</td>
                <td style="font-weight: bold;">${{ number_format($totalPrice, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div>Cumplimiento en Audiencia</div>
    <table>
        <thead style="background-color: #869b9c;">
            <tr>
                <th width="15" style="background-color: #869b9c; color: #ffffff;">Fecha</th>
                <th width="10" style="background-color: #869b9c; color: #ffffff;">Hora</th>
                <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                <!--
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Empleador</th>
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>              
                <th width="30" style="background-color: #869b9c; color: #ffffff;">Descripción</th>
                <th width="15" style="background-color: #869b9c; color: #ffffff;">Conciliador</th>
                <th width="40" style="background-color: #869b9c; color: #ffffff;">Giro Comercial</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Delegacion</th>
                -->
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Totales</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Total</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pendientes</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Pendientes</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pagadas</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Monto Pagadas</th>
                <th width="20" style="background-color: #869b9c; color: #ffffff;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPrice = 0;
            @endphp
            @foreach($pagosAudiencias as $estadistica)
                <tr>
                    <td style=" text-align: center;">{{ $estadistica->fecha ? date_format($estadistica->fecha,'d-m-Y') : "--"}}</td>
                    <td style=" text-align: center;">{{ $estadistica->hora  ? date_format($estadistica->hora, 'H:i:s') : "--" }}</td>
                    <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                    <!--
                    <td style=" text-align: center;">{{ $estadistica->empresa }} {{ $estadistica->primero_empresa }} {{ $estadistica->segundo_empresa }}</td>
                    <td style=" text-align: center;">{{ $estadistica->trabajador }} {{ $estadistica->primero_trabajador }} {{ $estadistica->segundo_trabajador }}</td>
                    <td style=" text-align: center;">{{ $estadistica->descripcion }}</td>
                    <td style=" text-align: center;">{{ $estadistica->conciliador_name }}</td>
                    <td style=" text-align: center;">{{ $estadistica->giroComercial }}</td>
                    <td style=" text-align: center;">{{ $estadistica->turno_delegacion }}</td>
                    -->
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pendientes + $estadistica->cantidad_pagados }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pendiente+$estadistica->monto_pagado , 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pendientes }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pendiente, 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->cantidad_pagados }}</td>
                    <td style=" text-align: center;">${{ number_format($estadistica->monto_pagado, 2) }}</td>
                    <td style=" text-align: center;">{{ $estadistica->estatus }}</td>
                </tr>
                @php
                    $totalPrice += $estadistica->monto_totalA;
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
                <td style="font-weight: bold;">Total :</td>
                <td style="font-weight: bold;">${{ number_format($totalPrice, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>