<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio - Reporte de Estadísticas</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <style>
            @page {
                margin: 0px 0px;
            }
            body {
                margin: 0;
                padding: 0;
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333;
            }
            /* Control exacto del espacio para no encimar el membrete */
            main {
                margin: 110px 45px 60px 45px;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1000;
            } 
            
            /* Estilo de Encabezado Superior */
            .info-top-container {
                width: 100%;
                margin-bottom: 30px;
                border-bottom: 2px solid #869b9c;
                padding-bottom: 10px;
            }
            .title-main {
                color: #5a6a6b;
                font-size: 18px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .date-range {
                text-align: right;
                font-size: 13px;
                color: #666;
            }

            /* Títulos de sección profesionales */
            .section-header {
                background-color: #f8f9fa;
                border-left: 5px solid #869b9c;
                padding: 8px 15px;
                font-weight: bold;
                font-size: 14px;
                color: #445455;
                margin-top: 25px;
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* Tablas estilizadas */
            .table-custom {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                margin-bottom: 20px;
            }
            .table-custom thead th {
                background-color: #869b9c;
                color: #ffffff;
                text-align: center;
                padding: 10px;
                border: 1px solid #758a8b;
            }
            .table-custom tbody td {
                padding: 8px;
                border: 1px solid #dee2e6;
                vertical-align: middle;
            }
            .table-custom tbody tr:nth-child(even) {
                background-color: #f2f4f4;
            }
            
            .font-bold { font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

        <main>
            <table class="info-top-container">
                <tr>
                    <td class="title-main">Centro de Conciliación Laboral</td>
                    <td class="date-range">
                        <strong>Periodo:</strong> 
                        {{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/y') }}
                    </td>
                </tr>
            </table>

            <div class="section-header">Resumen de Ratificaciones</div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 50%;">Concepto</th>
                        <th style="width: 20%;">Cantidad</th>
                        <th style="width: 30%;">Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">Total de Ratificaciones</td>
                        <td class="text-center">{{ ($pagosRatificacionPagado->ratificaciones + $pagosRatificacionPendiente->ratificaciones) }}</td>
                        <td class="text-center font-bold">${{ number_format(($pagosRatificacionMontoPendiente->ratificacionesMonto + $pagosRatificacionMontoPagado->ratificacionesMonto), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Ratificaciones Pagadas</td>
                        <td class="text-center">{{ $pagosRatificacionPagado->ratificaciones }}</td>
                        <td class="text-center text-success">${{ number_format($pagosRatificacionMontoPagado->ratificacionesMonto, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Ratificaciones Pendientes</td>
                        <td class="text-center">{{ $pagosRatificacionPendiente->ratificaciones }}</td>
                        <td class="text-center text-danger">${{ number_format($pagosRatificacionMontoPendiente->ratificacionesMonto, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-header">Resumen de Audiencias</div>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 50%;">Concepto</th>
                        <th style="width: 20%;">Cantidad</th>
                        <th style="width: 30%;">Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">Cumplimientos en Audiencia</td>
                        <td class="text-center">{{ $pagosAudiencias->audiencias }}</td>
                        <td class="text-center font-bold">${{ number_format($pagosAudienciasMonto->audienciasMonto, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-header">Promedio Diario por Sede</div>
            <table class="table-custom">
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

            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 9;
                    $y = $pdf->get_height() - 30;
                    $x = ($pdf->get_width() / 2) - 35;
                    $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, array(0.3, 0.3, 0.3));
                }
            </script>
        </main>
    </body>
</html>