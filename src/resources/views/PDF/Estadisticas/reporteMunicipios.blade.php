<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte de Solicitudes por Municipio</title>
        <style>
            /* Configuración de Página */
            @page {
                margin: 3.5cm 1.5cm 2cm 1.5cm; /* Espacio para el membrete y pie */
            }

            body {
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333;
                font-size: 11px;
                margin: 0;
                padding: 0;
            }

            .fondo-membrete {
                position: fixed;
                top: -3.5cm; /* Compensa el margen del @page */
                left: -1.5cm;
                width: 21cm;
                height: 29.7cm;
                z-index: -1;
            }

            /* Título Principal */
            .header-report {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #869b9c;
                padding-bottom: 10px;
            }
            .header-report h1 {
                color: #5a6a6b;
                font-size: 18px;
                text-transform: uppercase;
                margin: 0;
            }
            .header-report span {
                color: #888;
                font-size: 10px;
            }

            /* Banner de Delegación */
            .delegacion-banner {
                background-color: #5a6a6b;
                color: white;
                padding: 8px 15px;
                font-weight: bold;
                font-size: 12px;
                margin-top: 25px;
                border-radius: 4px 4px 0 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* Estilo de la Tabla */
            .table-custom {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0px;
            }
            .table-custom thead th {
                background-color: #869b9c;
                color: white;
                text-align: left;
                padding: 10px;
                font-size: 10px;
                border-bottom: 2px solid #5a6a6b;
            }
            .table-custom tbody td {
                padding: 8px 10px;
                border-bottom: 1px solid #eee;
            }
            .table-custom tbody tr:nth-child(even) {
                background-color: #fcfcfc;
            }
            .table-custom tbody tr:hover {
                background-color: #f1f3f3;
            }

            .total-badge {
                background-color: #e9ecef;
                padding: 3px 8px;
                border-radius: 10px;
                font-weight: bold;
                color: #5a6a6b;
                display: inline-block;
            }

            /* Evitar cortes feos en impresión */
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }

        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

        <main>
            <div class="header-report">
                <h1>Reporte Detallado de Solicitudes</h1>
                <span>Generado el: {{ date('d/m/Y H:i') }}</span>
            </div>

            @foreach($agrupados as $delegacion => $municipios)
                <div class="delegacion-banner">
                    Delegación: {{ $delegacion }}
                </div>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th width="70%">Municipio</th>
                            <th width="30%" style="text-align: center;">Total de Solicitudes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($municipios as $item)
                            @php $subtotal += $item->total_solicitudes; @endphp
                            <tr>
                                <td>{{ $item->municipio }}</td>
                                <td style="text-align: center;">
                                    <span class="total-badge">{{ $item->total_solicitudes }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f1f3f3; font-weight: bold;">
                            <td style="text-align: right; padding: 10px;">Subtotal {{ $delegacion }}:</td>
                            <td style="text-align: center; color: #5a6a6b;">{{ $subtotal }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach

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