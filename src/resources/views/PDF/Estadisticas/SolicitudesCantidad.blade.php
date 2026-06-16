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
            @page { margin: 0px 0px; }
            body {
                margin: 0; padding-top: 90px;
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333; line-height: 1.4;
            }
            main { margin: 20px 40px; }
            
            .fondo-membrete {
                position: fixed;
                top: 0; left: 0; width: 100%; height: 100%;
                z-index: -1000;
            } 

            /* Encabezado de la página */
            .header-info {
                width: 100%;
                margin-bottom: 20px;
                font-size: 11px;
            }

            /* Títulos de sección unificados */
            .section-title {
                background-color: #f1f3f3;
                color: #445455;
                padding: 8px 12px;
                border-left: 4px solid #869b9c;
                font-weight: bold;
                font-size: 13px;
                margin-top: 25px;
                margin-bottom: 10px;
                text-transform: uppercase;
            }

            /* Tabla Estándar para todo el documento */
            .table-report {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                table-layout: fixed; /* Esto ayuda a que los tamaños sean consistentes */
            }
            .table-report thead th {
                background-color: #869b9c;
                color: #ffffff;
                text-align: center;
                padding: 8px 5px;
                font-size: 10px;
                border: 1px solid #758a8b;
                text-transform: uppercase;
            }
            .table-report tbody td {
                padding: 7px 5px;
                border: 1px solid #dee2e6;
                font-size: 10px;
                text-align: center;
                vertical-align: middle;
            }
            .table-report tbody tr:nth-child(even) { background-color: #f9f9f9; }

            /* Clases de utilidad */
            .text-left { text-align: left !important; padding-left: 10px !important; }
            .bold { font-weight: bold; }
            .text-success { color: #28a745; font-weight: bold; }
            .text-danger { color: #dc3545; font-weight: bold; }
            .bg-light { background-color: #f8f9fa; font-weight: bold; }
        </style>
            
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <table class="header-info">
                <tr>
                    <td class="bold" style="font-size: 14px;">Centro de Conciliación Laboral</td>
                    <td style="text-align: right;">
                        <b>Periodo:</b> {{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}
                    </td>
                </tr>
            </table>
                </div><br>
                

                <!-- Reporte cumplimientos Monto -->
                <div class="section-title">Reporte General de Cumplimientos en Ratificaciones</div>
                    <table class="table-report">
                       <thead>
                            <tr>
                                <th style="width: 40%;">Concepto</th>
                                <th>Cantidad de cumplimientos</th>
                                <th>Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-light">
                                <td class="text-left">Total General</td>
                                <td>{{ ($pagosRatificacion->ratificaciones) }}</td>
                                <!--<td>{{ ($pagosRatificacionPagado->ratificaciones + $pagosRatificacionPendiente->ratificaciones) }}</td>-->
                                <td>${{ number_format(($pagosRatificacionMontoPendiente->ratificacionesMonto + $pagosRatificacionMontoPagado->ratificacionesMonto), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-left">Cumplimientos Pagados</td>
                                <td>{{ $pagosRatificacionPagado->ratificaciones }}</td>
                                <td class="text-success">${{ number_format($pagosRatificacionMontoPagado->ratificacionesMonto, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-left">Cumplimientos Pendientes</td>
                                <td>{{ $pagosRatificacionPendiente->ratificaciones }}</td>
                                <td class="text-danger">${{ number_format($pagosRatificacionMontoPendiente->ratificacionesMonto, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-left">Cumplimientos No Pagados por Incomparecencia</td>
                                <td>{{ $pagosRatificacionNoPagado->ratificaciones }}</td>
                                <td class="text-danger">${{ number_format($pagosRatificacionMontoNoPagado->ratificacionesMonto, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="section-title">Promedio Diario de Cumplimientos en Ratificaciones</div>
                        <table class="table-report">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Pagos Totales</th>
                                    <th>Días Activos</th>
                                    <th>Promedio Diario</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($promediosRatificaciones as $info)
                                    <tr>
                                        <td class="font-bold">{{ $info['sede'] }}</td>
                                        <td class="text-center">{{ $info['total_pagos'] }}</td>
                                        <td class="text-center">{{ $info['dias_con_actividad'] }}</td>
                                        <td class="text-center font-bold" style="color: #2c3e50; font-size: 12px;">
                                            {{ number_format($info['promedio_diario'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table> 

                    <div class="section-title">Reporte Cumplimientos derivados de Convenio(s)</div>
                        <table class="table-report">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Concepto</th>
                                    <th>Cantidad</th>
                                    <th>Monto Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left">Total de Convenios en Audiencia</td>
                                    <td>{{ $pagosAudiencias->audiencias }}</td>
                                    <td class="bold">${{ number_format($pagosAudienciasMonto->audienciasMonto, 2) }}</td>
                                <tr>
                                    <td class="text-left">Convenios Pagados</td>
                                    <td>{{ $pagosAudienciasPagado->audiencias }}</td>
                                    <td class="text-success">${{ number_format($pagosAudienciasMontoPagado->audienciasMonto, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-left">Convenios Pendientes</td>
                                    <td>{{ $pagosAudienciaPendiente->audiencias }}</td>
                                    <td class="text-danger">${{ number_format($pagosAudienciaMontoPendiente->audienciasMonto, 2) }}</td>
                                </tr>
                                </tr>
                            </tbody>
                        </table>

                    <div class="section-title">Promedio Cumplimientos de Convenio</div>
                        <table class="table-report">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Pagos Totales</th>
                                    <th>Días Activos</th>
                                    <th>Promedio Diario</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($promediosPagos as $info)
                                    <tr>
                                        <td class="font-bold">{{ $info['sede'] }}</td>
                                        <td class="text-center">{{ $info['total_pagos'] }}</td>
                                        <td class="text-center">{{ $info['dias_con_actividad'] }}</td>
                                        <td class="text-center font-bold" style="color: #2c3e50; font-size: 12px;">
                                            {{ number_format($info['promedio_diario'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table> 
                    </div>

                <!-- Reporte cumplimientos Ratificaciones -->
                <div class="section-title">Reporte Por Usuario</div>
                    <table class="table-report">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Usuario</th>
                            <th style="color: #fff;  text-align: center;">Cantidad</th>
                            <th style="color: #fff;  text-align: center;">Monto</th>
                        </thead>
                        <tbody> 
                            @foreach($usuariosTotal as $usuario)
                                <tr>
                                    <td style=" text-align: center;">{{ $usuario->name }}</td>
                                    <td style=" text-align: center;">{{ $usuario->ratificacion }}</td>
                                    <td style=" text-align: center;">${{ number_format($usuario->ratificacionesMonto, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- Reporte Solicitudes Monto -->
                <div class="section-title">Reporte General de Solicitudes Por Usuario</div>
                    <table class="table-report">
                        <tr>
                            <th style="width: 40%;">Usuario</th>
                            <th>Solicitudes</th>
                            <th>Confirmadas</th>
                        </tr>
                        <tbody> 
                           @foreach($solicitudes as $usuario)
                                <tr>
                                    <td class="text-left">{{ $usuario->name }}</td>
                                    <td>{{ $usuario->solicitudes }}</td>
                                    <td>{{ $usuario->confirmadas }}</td>
                                </tr>
                            @endforeach
                        </tbody>
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