<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
        <style>
           @page {
                margin: 0px 0px;
            }
            body{
                padding-top: 85px;
            }
            main{
                margin: 50px 50px 50px 40px; /*Para colocar el texto*/
            }
            header {
                position: fixed;
                top: -100px;
                left: 0;
                right: 0;
                height: 100px;
                text-align: center;
                font-size: 14px;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0;
                right: 0;
                height: 50px;
                text-align: center;
                font-size: 12px;
            }
            .content {
                font-family: sans-serif;
                font-size: 12px;
                text-align: justify;
                margin-top: 50px;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            } 
            .page-break {
                page-break-after: always;
            }
        </style>
            
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                            <tr>   
                                <td><b>Centro de Conciliación Laboral del Estado de Michoacán de Ocampo</b></td>
                            </tr>
                    </table>
                </div><br><br><br>
                
                            <div class="table-responsive">
                                <spam>Auxiliares</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #869b9c;">
                                        <th style="color: #fff;  text-align: center;">Nombre</th>
                                        <th style="color: #fff;  text-align: center;">Solicitudes</th>
                                        <th style="color: #fff;  text-align: center;">Solicitudes Confirmadas</th>
                                        <th style="color: #fff;  text-align: center;">Ratificaciones</th>
                                        <th style="color: #fff;  text-align: center;">Incompetencias</th>
                                        <th style="color: #fff;  text-align: center;">Cumplimientos</th>
                                        <th style="color: #fff;  text-align: center;">Monto en Audiencia</th>
                                        <th style="color: #fff;  text-align: center;">Monto en Ratificaciones</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total_solicitudes = 0;
                                            $total_confirmadas = 0;
                                            $total_ratificaciones = 0;
                                            $total_incopetencia = 0;
                                            $total_cumplimientos = 0;
                                            $total_audiencia = 0;
                                            $total_monto_ratificacion = 0;
                                        @endphp
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td style=" text-align: center;">{{ $solicitud->name}}</td>
                                                <td style=" text-align: center;">{{ $solicitud->solicitudes}}</td>
                                                <td style=" text-align: center;">{{ $solicitud->confirmadas}}</td>
                                                <td style=" text-align: center;">{{ $solicitud->ratificaciones}}</td>
                                                <td style=" text-align: center;">{{ $solicitud->incopetencia}}</td>
                                                <th style=" text-align: center;">{{ $solicitud->cumplimientoRatificacion + $solicitud->cumplimientoAudiencia }}</th>
                                                <td style=" text-align: center;">${{ number_format($solicitud->cumplimientoAudienciaMonto, 2) }}</td>
                                                <td style=" text-align: center;">${{ number_format($solicitud->cumplimientoRatificacionMonto, 2) }}</td>
                                                @php 
                                                    $total_solicitudes = $total_solicitudes + $solicitud->solicitudes;
                                                    $total_confirmadas = $total_confirmadas + $solicitud->confirmadas;
                                                    $total_ratificaciones = $total_ratificaciones + $solicitud->ratificaciones;
                                                    $total_incopetencia = $total_incopetencia + $solicitud->incopetencia;
                                                    $total_cumplimientos = $total_cumplimientos + $solicitud->cumplimientoRatificacion + $solicitud->cumplimientoAudiencia;
                                                    $total_audiencia = $total_audiencia + $solicitud->cumplimientoAudienciaMonto;
                                                    $total_monto_ratificacion = $total_monto_ratificacion + $solicitud->cumplimientoRatificacionMonto;
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style=" text-align: center;">Totales:</td>
                                            <td style=" text-align: center;">{{ $total_solicitudes }}</td>
                                            <td style=" text-align: center;">{{ $total_confirmadas }}</td>
                                            <td style=" text-align: center;">{{ $total_ratificaciones }}</td>
                                            <td style=" text-align: center;">{{ $total_incopetencia }}</td>
                                            <td style=" text-align: center;">{{ $total_cumplimientos }}</td>
                                            <td style=" text-align: center;">${{ number_format($total_audiencia,2) }}</td>
                                            <td style=" text-align: center;">${{ number_format($total_monto_ratificacion,2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

             <div class="page-break"></div><br><br>
                            <div class="table-responsive">
                                <spam>Conciliadores</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #869b9c;">
                                        <th style="color: #fff;  text-align: center;">Nombre</th>
                                        <th style="color: #fff;  text-align: center;">N° de Audiencias</th>
                                        <th style="color: #fff;  text-align: center;">N° Cumplimientos en Audiencia</th>
                                        <th style="color: #fff;  text-align: center;">Monto</th>
                                        <th style="color: #fff;  text-align: center;">Convenios</th>
                                        <th style="color: #fff;  text-align: center;">Archivada por falta de interés</th>
                                        <th style="color: #fff;  text-align: center;">Archivada por incompetencia</th>
                                        <th style="color: #fff;  text-align: center;">Número de Multas</th>
                                        <th style="color: #fff;  text-align: center;">Audiencias Virtuales</th>
                                        <th style="color: #fff;  text-align: center;">Concluida en una Audiencia</th>
                                        <th style="color: #fff;  text-align: center;">Concluida en dos Audiencias</th>
                                        <th style="color: #fff;  text-align: center;">Concluida en tres Audiencias</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total_audiencias = 0;
                                            $total_cumplimientoAudiencia = 0;
                                            $total_cumplimientoAudienciaMonto = 0;
                                            $total_cumplimientoAudienciaConvenio = 0;
                                            $total_cumplimientoAudienciaFalta = 0;
                                            $total_cumplimientoAudienciaIncompetencia = 0;
                                            $total_multas = 0;
                                            $total_audiencias_virtuales = 0;
                                            $total_una_audiencias = 0;
                                            $total_dos_audiencias = 0;
                                            $total_tres_audiencias = 0;
                                        @endphp
                                        @foreach($audiencias as $audiencia)
                                            <tr>
                                                <td style=" text-align: center;">{{ $audiencia->name}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->audiencias}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->cumplimientoAudiencia}}</td>
                                                <td style=" text-align: center;">${{ number_format($audiencia->cumplimientoAudienciaMonto, 2) }}</td>
                                                <td style=" text-align: center;">{{ $audiencia->cumplimientoAudienciaConvenio}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->cumplimientoAudienciaFalta}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->cumplimientoAudienciaIncompetencia}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->multas}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->audiencias_virtuales}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->una_audiencias}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->dos_audiencias}}</td>
                                                <td style=" text-align: center;">{{ $audiencia->tres_audiencias}}</td>
                                                @php 
                                                    $total_audiencias = $total_audiencias + $audiencia->audiencias;
                                                    $total_cumplimientoAudiencia = $total_cumplimientoAudiencia + $audiencia->cumplimientoAudiencia;
                                                    $total_cumplimientoAudienciaMonto = $total_cumplimientoAudienciaMonto + $audiencia->cumplimientoAudienciaMonto;
                                                    $total_cumplimientoAudienciaConvenio = $total_cumplimientoAudienciaConvenio + $audiencia->cumplimientoAudienciaConvenio;
                                                    $total_cumplimientoAudienciaFalta = $total_cumplimientoAudienciaFalta + $audiencia->cumplimientoAudienciaFalta;
                                                    $total_cumplimientoAudienciaIncompetencia = $total_cumplimientoAudienciaIncompetencia + $audiencia->cumplimientoAudienciaIncompetencia;
                                                    $total_multas = $total_multas + $audiencia->multas;
                                                    $total_audiencias_virtuales = $total_audiencias_virtuales + $audiencia->audiencias_virtuales;
                                                    $total_una_audiencias = $total_una_audiencias + $audiencia->una_audiencias;
                                                    $total_dos_audiencias = $total_dos_audiencias + $audiencia->dos_audiencias;
                                                    $total_tres_audiencias = $total_tres_audiencias + $audiencia->tres_audiencias;
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style=" text-align: center;">Totales:</td>
                                            <td style=" text-align: center;">{{ $total_audiencias }}</td>
                                            <td style=" text-align: center;">{{ $total_cumplimientoAudiencia }}</td>
                                            <td style=" text-align: center;">${{ number_format($total_cumplimientoAudienciaMonto, 2) }}</td>
                                            <td style=" text-align: center;">{{ $total_cumplimientoAudienciaConvenio }}</td>
                                            <td style=" text-align: center;">{{ $total_cumplimientoAudienciaFalta }}</td>
                                            <td style=" text-align: center;">{{ $total_cumplimientoAudienciaIncompetencia }}</td>
                                            <td style=" text-align: center;">{{ $total_multas }}</td>
                                            <td style=" text-align: center;">{{ $total_audiencias_virtuales }}</td>
                                            <td style=" text-align: center;">{{ $total_una_audiencias }}</td>
                                            <td style=" text-align: center;">{{ $total_dos_audiencias }}</td>
                                            <td style=" text-align: center;">{{ $total_tres_audiencias }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                
                <div class="page-break"></div>

                            <div class="table-responsive">
                                <spam>Notificadores</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #869b9c;">
                                        <th style="color: #fff;  text-align: center;">Nombre</th>
                                        <th style="color: #fff;  text-align: center;">Notificaciones</th>
                                        <th style="color: #fff;  text-align: center;">Notificada</th>
                                        <th style="color: #fff;  text-align: center;">No notificada</th>
                                        <th style="color: #fff;  text-align: center;">Pendiente</th>
                                        <th style="color: #fff;  text-align: center;">Exhorto</th>
                                        <th style="color: #fff;  text-align: center;">No exitosa se constituye</th>
                                        <th style="color: #fff;  text-align: center;">No exitosa no se constituye</th>
                                        <th style="color: #fff;  text-align: center;">Finalizado exitosamente</th>
                                        <th style="color: #fff;  text-align: center;">Recibe pero no firma</th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total_notificaciones = 0;
                                            $total_Notificada = 0;
                                            $total_notificacion_Nonotificada = 0;
                                            $total_notificacion_pendientes = 0;
                                            $total_notificacion_exhortos = 0;
                                            $total_notificacion_NESC = 0;
                                            $total_notificacion_NENSC = 0;
                                            $total_exitosamente = 0;
                                            $total_firma = 0;
                                        @endphp
                                        @foreach($notificaciones as $notificacion)
                                            <tr>
                                                <td style=" text-align: center;">{{ $notificacion->name}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificaciones}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificada}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificacion_Nonotificada}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificacion_pendientes}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificacion_exhortos}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificacion_NESC}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->notificacion_NENSC}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->exitosamente}}</td>
                                                <td style=" text-align: center;">{{ $notificacion->firma}}</td>
                                                @php 
                                                    $total_notificaciones = $total_notificaciones + $notificacion->notificaciones;
                                                    $total_Notificada = $total_Notificada + $notificacion->cumplimientoAudiencia;
                                                    $total_notificacion_Nonotificada = $total_notificacion_Nonotificada + $notificacion->cumplimientoAudienciaMonto;
                                                    $total_notificacion_pendientes = $total_notificacion_pendientes + $notificacion->notificacion_pendientes;
                                                    $total_notificacion_exhortos = $total_notificacion_exhortos + $notificacion->notificacion_exhortos;
                                                    $total_notificacion_NESC = $total_notificacion_NESC + $notificacion->notificacion_NESC;
                                                    $total_notificacion_NENSC = $total_notificacion_NENSC + $notificacion->notificacion_NENSC;
                                                    $total_exitosamente = $total_exitosamente + $notificacion->exitosamente;
                                                    $total_firma = $total_firma + $notificacion->firma;
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style=" text-align: center;">Totales:</td>
                                            <td style=" text-align: center;">{{ $total_notificaciones }}</td>
                                            <td style=" text-align: center;">{{ $total_Notificada }}</td>
                                            <td style=" text-align: center;">{{ $total_notificacion_Nonotificada }}</td>
                                            <td style=" text-align: center;">{{ $total_notificacion_pendientes }}</td>
                                            <td style=" text-align: center;">{{ $total_notificacion_exhortos }}</td>
                                            <td style=" text-align: center;">{{ $total_notificacion_NESC }}</td>
                                            <td style=" text-align: center;">{{ $total_notificacion_NENSC }}</td>
                                            <td style=" text-align: center;">{{ $total_exitosamente }}</td>
                                            <td style=" text-align: center;">{{ $total_firma }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $y = $pdf->get_height() - 30;
                    $x = ($pdf->get_width() / 2) - 50;
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0));
                }
            </script>
        </main>
    </body>