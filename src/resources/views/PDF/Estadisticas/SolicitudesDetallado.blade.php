<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio - Detalle de Solicitantes</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <style>
            @page { margin: 0px 0px; }
            body {
                margin: 0; padding-top: 85px;
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #333; line-height: 1.2;
            }
            /* Margen superior exacto para tu membrete */
            main { margin: 40px 30px 50px 30px; }
            
            .fondo-membrete {
                position: fixed;
                top: 0; left: 0; width: 100%; height: 100%;
                z-index: -1000;
            } 

            /* Cabecera */
            .header-info-table {
                width: 100%;
                border-bottom: 2px solid #869b9c;
                margin-bottom: 15px;
                padding-bottom: 8px;
            }
            .report-title {
                color: #5a6a6b;
                font-size: 18px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .report-date {
                text-align: right;
                font-size: 11px;
                color: #666;
            }

            /* Secciones de Título */
            .section-label {
                background-color: #f1f3f3;
                color: #445455;
                padding: 6px 12px;
                border-left: 4px solid #869b9c;
                font-weight: bold;
                font-size: 12px;
                margin: 15px 0 10px 0;
                text-transform: uppercase;
            }

            /* Tabla Principal */
            .table-custom {
                width: 100%;
                border-collapse: collapse;
                font-size: 8.5px;
            }
            .table-custom thead th {
                background-color: #869b9c;
                color: #ffffff;
                text-align: center;
                padding: 6px 3px;
                border: 1px solid #758a8b;
            }
            .table-custom tbody td {
                padding: 5px 3px;
                border: 1px solid #dee2e6;
                text-align: center;
                vertical-align: middle;
            }
            .table-custom tbody tr:nth-child(even) { background-color: #f9f9f9; }

            /* Indicadores Visuales */
            .badge-trabajador { color: #0d6efd; font-weight: bold; }
            .badge-patronal { color: #dc3545; font-weight: bold; }
            .folio-text { font-weight: bold; color: #333; }
            
            /* Cuadro Resumen */
            .resumen-compacto {
                width: 100%;
                margin-bottom: 20px;
                border-collapse: collapse;
            }
            .resumen-compacto td {
                padding: 10px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                text-align: center;
            }
            .resumen-val {
                display: block;
                font-size: 16px;
                font-weight: bold;
                color: #869b9c;
            }
            
            .clearfix { clear: both; }
        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

        <main>
            <table class="header-info-table">
                <tr>
                    <td class="report-title">Detalle General de Solicitudes</td>
                    <td class="report-date">
                        <strong>Periodo:</strong> 
                        {{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/y') }}
                    </td>
                </tr>
            </table>

            @php 
                $total = $detalleSolicitantes->count();
                $trabajadores = $detalleSolicitantes->where('tipo_solicitud', 1)->count();
                $patronales = $detalleSolicitantes->where('tipo_solicitud', 2)->count();
            @endphp
            
            <table class="resumen-compacto">
                <tr>
                    <td><small>TOTAL SOLICITUDES</small><span class="resumen-val">{{ $total }}</span></td>
                    <td><small>SOLICITUD TRABAJADOR</small><span class="resumen-val" style="color: #0d6efd;">{{ $trabajadores }}</span></td>
                    <td><small>SOLICITUD PATRONAL</small><span class="resumen-val" style="color: #dc3545;">{{ $patronales }}</span></td>
                </tr>
            </table>

            <div class="section-label">Listado Detallado de Registros</div>

            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Auxiliar</th>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th style="width: 15%">Nombre Solicitante</th>
                        <th>Estatus</th>
                        <th style="width: 15%">Actividad Económica</th>
                        <th style="width: 20%">Motivo / Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalleSolicitantes as $registro)
                        <tr>
                            <td>{{ $registro->auxiliar }}</td>
                            <td class="folio-text">{{ $registro->folio }}</td>
                            <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/y') }}</td>
                            <td>
                                @if($registro->tipo_solicitud == 1)
                                    <span class="badge-trabajador">Trabajador</span>
                                @else
                                    <span class="badge-patronal">Patronal</span>
                                @endif
                            </td>
                            <td style="text-align: left;">{{ $registro->nombre }}</td>
                            <td>{{ $registro->estatus }}</td>
                            <td style="text-align: left; font-size: 7.5px;">{{ $registro->actividad }}</td>
                            <td style="text-align: left; font-size: 7.5px;">{{ $registro->motivos }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 8;
                    $y = $pdf->get_height() - 30;
                    $x = ($pdf->get_width() / 2) - 40;
                    $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, array(0.4, 0.4, 0.4));
                }
            </script>
        </main>
    </body>
</html>