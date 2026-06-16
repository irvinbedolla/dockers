<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Cuantitativo - Sí Concilio</title>
    <style>
        /* Tipografía institucional */
        @page { margin: 1.5cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 9px; /* Tamaño optimizado para Landscape */
            margin: 0;
            padding-top: 60px;
        }

        /* Membrete y Fondos */
        .fondo-membrete {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1000;
        }

        /* Encabezados de Sección */
        .section-header {
            background-color: #f2f4f4;
            border-left: 5px solid #869b9c;
            padding: 8px 12px;
            margin: 20px 0 10px 0;
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Estilo de Tablas Profesional */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        th {
            background-color: #869b9c;
            color: #ffffff;
            padding: 8px 4px;
            border: 1px solid #758a8b;
            text-align: center;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #e0e0e0;
            text-align: center;
            word-wrap: break-word;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }

        /* Pie de página de tabla (Totales) */
        tfoot tr td {
            background-color: #e8eded;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #869b9c;
        }

        .monto { color: #2e7d32; font-weight: bold; }
        .page-break { page-break-after: always; }
        
        .header-top {
            width: 100%;
            border-bottom: 2px solid #869b9c;
            margin-bottom: 20px;
        }
        /* Estilos para indicadores visuales */
        .kpi-container {
            width: 100%;
            margin-bottom: 25px;
        }
        .kpi-card {
            width: 30%;
            display: inline-block;
            vertical-align: top;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        .progress-bar-container {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 4px;
            height: 15px;
            margin-top: 10px;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #869b9c;
            border-radius: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
            color: #5a6a6b;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

    <main>
        <table class="header-top">
            <tr>
                <td style="text-align: left; border: none; background: none;">
                    <h2 style="margin:0; color:#869b9c;">REPORTE CUANTITATIVO</h2>
                    <span style="font-size: 12px;">Centro de Conciliación Laboral</span>
                </td>
                <td style="text-align: right; border: none; background: none;">
                    <b>Periodo:</b> {{ $fecha_inicial }} - {{ $fecha_final }}
                </td>
            </tr>
        </table>


        <div class="section-header">Desempeño de Solicitudes</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Nombre</th>
                    <th>Solicitudes</th>
                    <th>Confirmadas</th>
                    <th>Ratificaciones</th>
                    <th>Incomplimientos</th>
                    <th>Cumplimientos</th>
                    <th>Monto Audiencia</th>
                    <th>Monto Ratificación</th>
                </tr>
            </thead>
            <tbody>
                @php $t = ['s'=>0, 'c'=>0, 'r'=>0, 'i'=>0, 'cu'=>0, 'ma'=>0, 'mr'=>0]; @endphp
                @foreach($solicitudes as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->solicitudes }}</td>
                    <td>{{ $s->confirmadas }}</td>
                    <td>{{ $s->ratificaciones }}</td>
                    <td>{{ $s->incompetencia }}</td>
                    <td>{{ $s->cumplimientos }}</td>
                    <td class="monto">${{ number_format($s->cumplimientoAudienciaMonto, 2) }}</td>
                    <td class="monto">${{ number_format($s->ratificacionesMonto, 2) }}</td>
                </tr>
                @php
                    $t['s'] += $s->solicitudes; 
                    $t['c'] += $s->confirmadas;
                    $t['r'] += $s->ratificaciones; 
                    $t['i'] += $s->incompetencia;
                    $t['cu'] += ($s->cumplimientoRatificacion + $s->cumplimientoAudiencia);
                    $t['ma'] +=  $s->cumplimientoAudienciaMonto; 
                    $t['mr'] += $s->ratificacionesMonto;
                @endphp
                @endforeach
            </tbody>
        </table>

       
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->page_text(500, 570, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, array(0.5, 0.5, 0.5));
        }
    </script>
</body>
</html>