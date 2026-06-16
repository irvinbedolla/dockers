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
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                            <tr>   
                                <td><b>Centro de Conciliación Laboral </b></td>
                                
                                <td>{{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/y') }} a {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/y') }}</td>
                            </tr>
                    </table>
                </div><br><br><br>
                <div class="table-responsive">
                    <spam>Ratificaciones por usuario</spam>
                    <table class="table table-striped mt-2">
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
                <div class="table-responsive">
                    <spam>Promedio</spam>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Sede</th>
                            <th style="color: #fff;  text-align: center;">Promedio por dia</th>
                            <th style="color: #fff;  text-align: center;">Total</th>
                        </thead>
                        <tbody> 
                            @foreach($promedios as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item['sede'] }}</td>
                                    <td class="text-center">
                                        {{ number_format($item['promedio'], 2) }}
                                    </td>
                                    <td class="text-center">{{ $item['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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