<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table>
            <thead>
                <th style="  text-align: center;">ID</th>
                <th style="  text-align: center;">Fecha</th>
                <th style="  text-align: center;">NUE</th>
                <th style="  text-align: center;">Solicitante</th>
                <th style="  text-align: center;">Citado</th>
                <th style="  text-align: center;">Dirección</th>
                <th style="  text-align: center;">Razón Social</th>
                <th style="  text-align: center;">Actividad Economica</th>
                <th style="  text-align: center;">Delegacion</th>
                <th style="  text-align: center;">Auxiliar</th>
                <th style="  text-align: center;">Notificador</th>
                <th style="  text-align: center;">Estatus</th>
            </thead>
            <tbody>
                @php
                    $totalPrice = 0;
                @endphp
                @foreach($notificaciones as $notificacion)
                    <tr>
                        <td style=" text-align: center;">{{ $notificacion->id }}</td>
                        <td style=" text-align: center;">{{ $notificacion->fecha }}</td>
                        <td style=" text-align: center;">{{ $notificacion->NUE }}</td>
                        <td style=" text-align: center;">{{ $notificacion->nombre_solicitante }} </td>
                        <td style=" text-align: center;">{{ $notificacion->nombre }} {{ $notificacion->primer_apellido }} {{ $notificacion->segundo_apellido }}</td>
                        <td style=" text-align: center;">Colonia: {{ $notificacion->colonia }} Calle: {{ $notificacion->calle }} Num. Ext.: {{ $notificacion->n_ext }} Num. Int.: {{ $notificacion->n_int }}</td>
                        <td style=" text-align: center;">{{ $notificacion->rama_industrial }} </td>
                        <td style=" text-align: center;">{{ $notificacion->actividad }} </td>
                        <td style=" text-align: center;">{{ $notificacion->delegacion }}</td>
                        <td style=" text-align: center;">{{ $notificacion->auxiliar }}</td>
                        <td style=" text-align: center;">{{ $notificacion->notificador }}</td>
                        <td style=" text-align: center;">{{ $notificacion->estatus }}</td>
                    </tr>
                    @php
                        // Suma los valores para el total
                        $totalPrice += $notificacion->monto;
                    @endphp
                @endforeach
            </tbody>
        </table>
    </body>
</html>