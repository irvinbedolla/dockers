<table>
    <thead>
        <tr>
            <th style="background-color: #2196F3; color: #ffffff; font-weight: bold; text-align: center;" colspan="11">
                LISTADO DETALLADO DE AUDIENCIAS
            </th>
        </tr>
        <tr>
            <th width="30" style="font-weight: bold; background-color: #EFEFEF;">Número de Expediente</th>
            <th width="15" style="font-weight: bold; background-color: #EFEFEF;">Fecha Audiencia</th>
            <th width="15" style="font-weight: bold; background-color: #EFEFEF;">Hora Audiencia</th>
            <th width="40" style="font-weight: bold; background-color: #EFEFEF;">Solicitante</th>
            <!--<th width="40" style="font-weight: bold; background-color: #EFEFEF;">Citado</th>
            <th width="60" style="font-weight: bold; background-color: #EFEFEF;">Domicilio</th>
            
            <th width="35" style="font-weight: bold; background-color: #EFEFEF;">Auxiliar</th>-->
            <th width="30" style="font-weight: bold; background-color: #EFEFEF;">Conciliador</th>
            <th width="30" style="font-weight: bold; background-color: #EFEFEF;">Delegación</th>
            <th width="30" style="font-weight: bold; background-color: #EFEFEF;">Estatus</th>
        </tr>
    </thead>
    <tbody>
        @foreach($audiencias as $a)
        <tr>
            <td>{{ $a['NUE'] ?? '' }}</td>
            <td>{{ isset($a['fecha']) ? \Carbon\Carbon::parse($a['fecha'])->format('d/m/Y') : '' }}</td>
            <td>{{ isset($a['hora']) ? \Carbon\Carbon::parse($a['hora'])->format('H:i:s') : '' }}</td>
            <td>{{ $a['nombre_solicitante'] ?? '' }}</td>
            <td>{{ $a['nombre_conciliador'] ?? '' }}</td>
            <td>{{ $a['delegacion'] ?? '' }}</td>
            <td>{{ $a['estatus'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>