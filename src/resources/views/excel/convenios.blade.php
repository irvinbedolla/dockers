<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        {{-- App\Exports\Convenios: una columna por citado (CITADO 1, CITADO 2…), tantas como $columnasCitado. --}}
        <div>Ratificación</div>
        <table>
            <thead style="background-color: #869b9c;">
                <tr>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Fecha</th>
                    <th width="10" style="background-color: #869b9c; color: #ffffff;">Hora</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>
                    @for ($i = 1; $i <= $columnasCitado; $i++)
                        <th width="45" style="background-color: #869b9c; color: #ffffff;">CITADO {{ $i }}</th>
                    @endfor
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Parcialidades Totales</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Monto Total</th>
                    <th width="20" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pendientes</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Monto Pendiente</th>
                    <th width="30" style="background-color: #869b9c; color: #ffffff;">Parcialidades Pagadas</th>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Monto Pagado</th>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Estatus</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPrice = 0;
                @endphp
                @foreach($Convenios as $estadistica)
                    <tr>
                        <td style=" text-align: center;">{{ $estadistica->fecha_formateada }}</td>
                        <td style=" text-align: center;">{{ $estadistica->hora_formateada}}</td>
                        <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                        <td style=" text-align: center;">{{ $estadistica->nombre }}</td>
                        @for ($i = 0; $i < $columnasCitado; $i++)
                            <td style=" text-align: center;">{{ $estadistica->lista_citados[$i] ?? '' }}</td>
                        @endfor
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
                        $totalPrice += ($estadistica->monto_pendiente + $estadistica->monto_pagado );
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @for ($i = 0; $i < $columnasCitado; $i++)
                        <td></td>
                    @endfor
                    <td style="font-weight: bold;">Total :</td>
                    <td style="font-weight: bold;">{{ number_format($totalPrice, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
