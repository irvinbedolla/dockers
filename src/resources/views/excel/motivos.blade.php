<table>
    <tbody>
        <!-- Solicitudes -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            1. SOLICITUDES ADMITIDAS
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $solicitudes->sum('h') }}</td>
                        <td style="text-align: center;">{{ $solicitudes->sum('m') }}</td>
                        <td style="text-align: center;">{{ $solicitudes->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- Solicitudes Confirmadas -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            1. SOLICITUDES CONFIRMADAS
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudesConfirmadas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $solicitudesConfirmadas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $solicitudesConfirmadas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $solicitudesConfirmadas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- Ratificaciones -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            3. RATIFICACIONES AGENDADAS			
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultadosratificacionesConfirmadas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- Ratificaciones -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            4. RATIFICACIONES CONCLUIDAS
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultadosratificacionesConfirmadas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $resultadosratificacionesConfirmadas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- Archivadas -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            5. ARCHIVADAS POR FALTA DE INTERES
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivadas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $archivadas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $archivadas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $archivadas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- AUDIENCIAS PROGRAMADAS -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            6. AUDIENCIAS PROGRAMADAS
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programadas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $programadas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $programadas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $programadas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- AUDIENCIAS CELEBRADAS -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            7. AUDIENCIAS CELEBRADAS
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($celebradas as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $celebradas->sum('h') }}</td>
                        <td style="text-align: center;">{{ $celebradas->sum('m') }}</td>
                        <td style="text-align: center;">{{ $celebradas->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!-- CONVENIOS -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            8. CONVENIOS	
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($convenios as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $convenios->sum('h') }}</td>
                        <td style="text-align: center;">{{ $convenios->sum('m') }}</td>
                        <td style="text-align: center;">{{ $convenios->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!--  INCOMPARECENCIA-->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            9. INCOMPARECENCIA	
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incompetencia as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $incompetencia->sum('h') }}</td>
                        <td style="text-align: center;">{{ $incompetencia->sum('m') }}</td>
                        <td style="text-align: center;">{{ $incompetencia->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
        <!--  NO CONCILIACION (AUDIENCIA) -->
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="background-color: #002366; color: #ffffff; text-align: center; font-weight: bold;">
                            10. NO CONCILIACION (AUDIENCIA)
                        </th>
                    </tr>
                    <tr style="background-color: #eeeeee; text-align: center;">
                        <th width="50">Categoría</th>
                        <th width="15">Hombres</th>
                        <th width="15">Mujeres</th>
                        <th width="15">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivadaAudiencia as $nombreCategoria => $valores)
                        <tr>
                            <td style="text-align: left;">{{ $nombreCategoria }}</td>
                            <td style="text-align: center;">{{ $valores['h'] }}</td>
                            <td style="text-align: center;">{{ $valores['m'] }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $valores['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align: right;">TOTAL GENERAL:</td>
                        <td style="text-align: center;">{{ $archivadaAudiencia->sum('h') }}</td>
                        <td style="text-align: center;">{{ $archivadaAudiencia->sum('m') }}</td>
                        <td style="text-align: center;">{{ $archivadaAudiencia->sum('total') }}</td>
                    </tr>
                </tfoot>
            </table>
			
    </tbody>
