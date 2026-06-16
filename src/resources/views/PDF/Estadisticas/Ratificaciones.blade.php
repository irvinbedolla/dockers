<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio - Reporte Detallado</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <style>
            @page { margin: 0px 0px; }
            body {
                padding-top: 85px;
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333;
                line-height: 1.2;
            }
            main { margin: 40px 40px 60px 40px; }
            
            .fondo-membrete {
                position: fixed;
                top: 0; left: 0; width: 100%; height: 100%;
                z-index: -1;
            } 

            /* Encabezado */
            .header-info-table {
                width: 100%;
                margin-bottom: 25px;
                border-bottom: 2px solid #869b9c;
            }
            .header-title {
                color: #5a6a6b;
                font-size: 19px;
                font-weight: bold;
                padding-bottom: 5px;
            }
            .header-date {
                text-align: right;
                font-size: 12px;
                color: #555;
            }

            /* Secciones */
            .section-title {
                background-color: #f1f3f3;
                color: #445455;
                padding: 7px 12px;
                border-left: 5px solid #869b9c;
                font-weight: bold;
                font-size: 13px;
                margin: 20px 0 10px 0;
                text-transform: uppercase;
            }

            /* Tablas */
            .table-custom {
                width: 100%;
                border-collapse: collapse;
                font-size: 9.5px;
                margin-bottom: 10px;
            }
            .table-custom thead th {
                background-color: #869b9c;
                color: #ffffff;
                text-align: center;
                padding: 8px 3px;
                border: 1px solid #758a8b;
            }
            .table-custom tbody td {
                padding: 6px 3px;
                border: 1px solid #dee2e6;
                text-align: center;
            }
            .table-custom tbody tr:nth-child(even) { background-color: #f9f9f9; }

            /* Fila de Totales en Tablas */
            .row-total {
                background-color: #e9ecef !important;
                font-weight: bold;
                font-size: 10.5px;
                color: #2c3e50;
            }
            .total-label { text-align: right !important; padding-right: 15px !important; }

            /* Cuadro Resumen Final */
            .resumen-container {
                margin-top: 30px;
                width: 40%;
                float: right; /* Alineado a la derecha para un look más ejecutivo */
                border: 1px solid #869b9c;
            }
            .resumen-table {
                width: 100%;
                font-size: 11px;
            }
            .resumen-table th {
                background-color: #5a6a6b;
                color: white;
                padding: 8px;
                text-align: center;
            }
            .resumen-table td {
                padding: 8px;
                border-bottom: 1px solid #dee2e6;
            }
            .resumen-grand-total {
                background-color: #869b9c;
                color: white;
                font-weight: bold;
                font-size: 13px;
            }

            .monto-bold { font-weight: bold; color: #1a1a1a; }
            .estatus-pill {
                padding: 2px 5px;
                border-radius: 3px;
                background-color: #d1d8d9;
                font-size: 8.5px;
            }
            .clearfix { clear: both; }

            @page {
                margin: 0cm; /* Margen global: Top, Right, Bottom, Left */
            }
            
            /* Ajuste de tablas para evitar cortes bruscos */
            table {
                page-break-inside: auto;
                width: 100%;
                margin-bottom: 20px;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

        <main>
            <table class="info-header-table">
                <tr>
                    <td class="report-title">Centro de Conciliación Laboral</td>
                    <td class="report-date">
                        <strong>Periodo:</strong> 
                        {{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/y') }}
                    </td>
                </tr>
            </table>

            @php $totalMonto = 0; $totalRegistros = 0; @endphp

            <div class="section-label">Detalle de Ratificaciones</div>
            
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>NUE</th>
                        <th style="width: 15%">Empleador</th>
                        <th style="width: 15%">Trabajador</th>
                        <th>Monto</th>
                        <th>Delegación</th>
                        <th>Conciliador</th>
                        <th>Usuario</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody> 
                    @foreach($Ratificacion as $estadistica)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($estadistica->fecha)->format('d/m/y') }}</td>
                            <td>{{ $estadistica->hora }}</td>
                            <td style="font-weight: bold;">{{ $estadistica->NUE }}</td>
                            <td>{{ $estadistica->empresa }} {{ $estadistica->primero_empresa }} {{ $estadistica->segundo_empresa }}</td>
                            <td>{{ $estadistica->trabajador }} {{ $estadistica->primero_trabajador }} {{ $estadistica->segundo_trabajador }}</td>
                            <td class="monto-cell">${{ number_format($estadistica->monto, 2) }}</td>
                            <td>{{ $estadistica->delegacion }}</td>
                            <td>{{ $estadistica->conciliador_name }}</td>
                            <td>{{ $estadistica->auxiliar_name }}</td>
                            <td><span class="estatus-pill">{{ $estadistica->estatus }}</span></td>
                        </tr>
                        @php 
                            $totalMonto += $estadistica->monto; 
                            $totalRegistros++;
                        @endphp
                    @endforeach
                </tbody>
            </table>

            <div class="clearfix"></div>
            <div class="resumen-container">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th colspan="2">RESUMEN DE REGISTROS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total de Ratificaciones:</td>
                            <td align="right"><strong>{{ $totalRegistros }}</strong></td>
                        </tr>
                        <tr class="grand-total">
                            <td>MONTO TOTAL:</td>
                            <td align="right">${{ number_format($totalMonto, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>TOTAL PAGADO:</td>
                            <td align="right">${{ number_format($ratificacionePagadas->pagado_monto, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>TOTAL PENDIENTES:</td>
                            <td align="right">${{ number_format($totalMonto - $ratificacionePagadas->pagado_monto, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 9;
                    $y = $pdf->get_height() - 35;
                    $x = ($pdf->get_width() / 2) - 40;
                    $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
                }
            </script>
        </main>
    </body>
</html>