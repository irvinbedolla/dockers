{{--
    Agenda de un conciliador para el rango exportado.

    El formato es el mismo de excel/audienciasConcliliador.blade.php —de ahí
    salía el archivo que se recortaba a mano— con dos diferencias: aquí sólo
    van las audiencias de un conciliador, y entre día y día se mete una fila
    en blanco para que se lea igual que el original.
--}}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
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
                @foreach($porDia as $fecha => $audiencias)
                    @if(! $loop->first)
                        <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endif

                    @foreach($audiencias as $audiencia)
                        <tr>
                            <td style="text-align: center;">{{ $audiencia->fecha }}</td>
                            <td style="text-align: center;">{{ $audiencia->hora }}</td>
                            <td style="text-align: center;">{{ $audiencia->nue }}</td>
                            <td style="text-align: center;">{{ $audiencia->trabajador }}</td>
                            <td style="text-align: center;">{{ $audiencia->citado }}</td>
                            <td style="text-align: center;">{{ $audiencia->conciliador }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </body>
</html>
