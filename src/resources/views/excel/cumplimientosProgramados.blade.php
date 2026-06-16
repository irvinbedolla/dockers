<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div>Audiencias</div>
        <table>
            <thead style="background-color: #869b9c;">
                <tr>
                    <th width="15" style="background-color: #869b9c; color: #ffffff;">Fecha</th>
                    <th width="10" style="background-color: #869b9c; color: #ffffff;">Hora</th>
                    <th width="25" style="background-color: #869b9c; color: #ffffff;">NUE</th>
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Trabajador</th>
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Citado</th>
                    <th width="40" style="background-color: #869b9c; color: #ffffff;">Conciliador</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cumplimientos as $estadistica)
                    <tr>
                        <td style=" text-align: center;">{{ \Carbon\Carbon::parse($estadistica->fecha)->format('d/m/Y') }}</td>
                        <td style=" text-align: center;">{{ $estadistica->hora_programada}}</td>
                        <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                        <td style=" text-align: center;">{{ $estadistica->nombre_trabajador }}</td>
                        <td style=" text-align: center;">{{ $estadistica->nombre_empleador }} </td>
                        <td style=" text-align: center;">{{ $estadistica->conciliador_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>