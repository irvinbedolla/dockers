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

        <div class="section-header">Indicadores</div>
            <div class="kpi-container">
                
                <div class="kpi-card" style="margin-left: 3%;">
                    <span style="font-size: 10px; color: #666;">TOTAL MONTO (AUD + RAT)</span><br>
                    <span class="kpi-value">${{ number_format($solicitudes->sum('cumplimientoAudienciaMonto') + $solicitudes->sum('cumplimientoRatificacionMonto'), 2) }}</span>
                </div>

                <div class="kpi-card" style="margin-left: 3%;">
                    <span style="font-size: 10px; color: #666;">AUDIENCIAS CELEBRADAS</span><br>
                    <span class="kpi-value">{{ $audiencias->sum('total_audiencias') }}</span>
                </div>
            </div>
        </div>

        <div class="section-header">Desempeño de Auxiliares</div>
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
            <tfoot>
                <tr>
                    <td>TOTALES</td>
                    <td>{{ $t['s'] }}</td>
                    <td>{{ $t['c'] }}</td>
                    <td>{{ $t['r'] }}</td>
                    <td>{{ $t['i'] }}</td>
                    <td>{{ $total_cumplimiento }}</td>
                    <td>${{ number_format($t['ma'], 2) }}</td>
                    <td>${{ number_format($t['mr'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="page-break"></div>

        <div class="section-header">Estadísticas de Conciliadores</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Sede</th>
                    <th>Audiencias Programadas</th>
                    <th>Audiencias Celebradas</th>
                    <th>Convenios</th>
                    <th>Falta de Int.</th>
                    <th>Incompetencia</th>
                    <th>1 Aud.</th>
                    <th>2 Aud.</th>
                    <th>3+ Aud.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $audienicas = ['t'=>0, 'n'=>0, 'nn'=>0, 'p'=>0, 'e'=>0, 'nesc'=>0, 'nensc'=>0, 'ex'=>0, 'f'=>0];
                @endphp
                @foreach($audiencias as $a)
                    <tr>
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->audienencias_programadas ?? 0 }}</td>
                        <td>{{ $a->audienencias_celebradas }}</td>
                        <td>{{ $a->convenios}}</td>
                        <td>{{ $a->achivada }}</td>
                        <td>{{ $a->incompetencia ?? 0 }}</td>
                        <td>{{ $a->una_audiencia }}</td>
                        <td>{{ $a->dos_audiencias }}</td>
                        <td>{{ $a->tres_audiencias }}</td>
                    </tr>
                    @php 
                            $audienicas['t'] += $a->audienencias_programadas;
                            $audienicas['n'] += $a->audienencias_celebradas;
                            $audienicas['nn'] += $a->convenios;
                            $audienicas['p'] += $a->achivada;
                            $audienicas['nesc'] += $a->incompetencia;
                            $audienicas['nensc'] += $a->una_audiencia;
                            $audienicas['ex'] += $a->dos_audiencias;
                            $audienicas['f'] += $a->tres_audiencias;
                        @endphp
                @endforeach
            </tbody>
            <tfoot>
                    <tr>
                        <td class="text-left">TOTALES GENERALES</td>
                        <td>{{ $audienicas['t'] }}</td>
                        <td>{{ $audienicas['n'] }}</td>
                        <td>{{ $audienicas['nn'] }}</td>
                        <td>{{ $audienicas['p'] }}</td>
                        <td>{{ $audienicas['nesc'] }}</td>
                        <td>{{ $audienicas['nensc'] }}</td>
                        <td>{{ $audienicas['ex'] }}</td>
                        <td>{{ $audienicas['f'] }}</td>
                    </tr>
                </tfoot>
        </table>

        <div class="page-break"></div> <div class="section-header">Gestión de Notificaciones por Notificador</div>
            <table class="table-report">
                <thead>
                    <tr>
                        <th style="width: 15%;">Nombre</th>
                        <th>Total Notif.</th>
                        <th>Notificada</th>
                        <th>No Notif.</th>
                        <th>Pendiente</th>
                        <th>Exhorto</th>
                        <th>NESC*</th>
                        <th>NENSC**</th>
                        <th>Exitosa</th>
                        <th>Sin Firma</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tn = ['t'=>0, 'n'=>0, 'nn'=>0, 'p'=>0, 'e'=>0, 'nesc'=>0, 'nensc'=>0, 'ex'=>0, 'f'=>0];
                    @endphp
                    @foreach($notificaciones as $notificacion)
                        <tr>
                            <td class="text-left bold">{{ $notificacion->name }}</td>
                            <td>{{ $notificacion->Todas_notificaciones }}</td>
                            <td>{{ $notificacion->notificada }}</td>
                            <td>{{ $notificacion->notificacion_Nonotificada }}</td>
                            <td>{{ $notificacion->notificacion_pendientes }}</td>
                            <td>{{ $notificacion->notificacion_exhortos }}</td>
                            <td>{{ $notificacion->notificacion_NESC }}</td>
                            <td>{{ $notificacion->notificacion_NENSC }}</td>
                            <td>{{ $notificacion->exitosamente }}</td>
                            <td>{{ $notificacion->firma }}</td>
                        </tr>
                        @php 
                            $tn['t'] += $notificacion->Todas_notificaciones;
                            $tn['n'] += $notificacion->notificada;
                            $tn['nn'] += $notificacion->notificacion_Nonotificada;
                            $tn['p'] += $notificacion->notificacion_pendientes;
                            $tn['e'] += $notificacion->notificacion_exhortos;
                            $tn['nesc'] += $notificacion->notificacion_NESC;
                            $tn['nensc'] += $notificacion->notificacion_NENSC;
                            $tn['ex'] += $notificacion->exitosamente;
                            $tn['f'] += $notificacion->firma;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-left">TOTALES GENERALES</td>
                        <td>{{ $tn['t'] }}</td>
                        <td>{{ $tn['n'] }}</td>
                        <td>{{ $tn['nn'] }}</td>
                        <td>{{ $tn['p'] }}</td>
                        <td>{{ $tn['e'] }}</td>
                        <td>{{ $tn['nesc'] }}</td>
                        <td>{{ $tn['nensc'] }}</td>
                        <td>{{ $tn['ex'] }}</td>
                        <td>{{ $tn['f'] }}</td>
                    </tr>
                </tfoot>
            </table>

            <p style="font-size: 8px; color: #666;">
                *NESC: No exitosa se constituye / **NENSC: No exitosa no se constituye
            </p>
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->page_text(500, 570, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, array(0.5, 0.5, 0.5));
        }
    </script>
</body>
</html>