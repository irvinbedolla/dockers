<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Estadístico por Sede - Sí Concilio</title>
    <style>
        /* Tipografía Institucional */
        @page { margin: 0px 0px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 10px;
            margin: 0;
            padding-top: 90px;
        }
        main { margin: 20px 40px; }

        .fondo-membrete {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1000;
        }

        /* Títulos de Sección */
        .section-header {
            background-color: #f2f4f4;
            border-left: 5px solid #869b9c;
            padding: 8px 12px;
            margin: 25px 0 10px 0;
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Estilo de Tablas Profesional */
        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            table-layout: fixed;
        }
        .table-report th {
            background-color: #869b9c;
            color: #ffffff;
            padding: 8px 4px;
            border: 1px solid #758a8b;
            text-transform: uppercase;
            font-size: 9px;
        }
        .table-report td {
            padding: 7px 4px;
            border: 1px solid #e0e0e0;
            text-align: center;
            vertical-align: middle;
        }
        .table-report tr:nth-child(even) { background-color: #f9f9f9; }

        /* Clases de Utilidad */
        .bold { font-weight: bold; }
        .text-left { text-align: left !important; padding-left: 10px !important; }
        .monto { color: #2e7d32; font-weight: bold; }
        .efectividad { font-weight: bold; color: #869b9c; }

        /* Indicadores KPI */
        .kpi-container { width: 100%; margin-bottom: 20px; }
        .kpi-card {
            width: 30%;
            display: inline-block;
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
            vertical-align: top;
        }
        .kpi-value { font-size: 18px; font-weight: bold; color: #5a6a6b; display: block; }
        .progress-bar {
            width: 100%; background: #e9ecef; height: 10px; border-radius: 5px; margin-top: 8px;
        }
        .progress-fill { height: 100%; background: #869b9c; border-radius: 5px; }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">

    <main>
        <table style="width: 100%; border-bottom: 2px solid #869b9c; margin-bottom: 15px;">
            <tr>
                <td style="text-align: left; border: none;">
                    <h2 style="margin:0; color:#869b9c; font-size: 18px;">REPORTE ESTADÍSTICO POR SEDE</h2>
                    <span style="font-size: 11px;">Consolidado Regional Michoacán</span>
                </td>
                <td style="text-align: right; border: none;">
                    <b>Periodo:</b> {{ $fecha_inicial }} al {{ $fecha_final }}
                </td>
            </tr>
        </table>

        @php
            $total_sol = $solicitudes->sum('numeroSolicitudes');
            $total_conf = $solicitudes->sum('confirmadas');
            $perc_conf = ($total_sol > 0) ? ($total_conf / $total_sol) * 100 : 0;
        @endphp

        <div class="section-header">Resumen de Solicitudes y Ratificaciones</div>
        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 18%;">Sede</th>
                    <th>Solicitudes</th>
                    <th>Confirmadas</th>
                    <!--th>Efect. %</th-->
                    <th>Ratif.</th>
                    <th>Cumplimientos</th>
                    <th>Monto Aud.</th>
                    <th>Monto Rat.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitudes as $s)
                @php $efect = ($s->numeroSolicitudes > 0) ? ($s->confirmadas / $s->numeroSolicitudes)*100 : 0; @endphp
                <tr>
                    <td class="text-left bold">{{ $s->sede_nombre }}</td>
                    <td>{{ $s->numeroSolicitudes ?? 0 }}</td>
                    <td>{{ $s->confirmadas }}</td>
                    <!--td class="efectividad">{{ number_format($efect, 1) }}%</td-->
                    <td>{{ $s->ratificaciones ?? 0 }}</td>
                    <td class="bold">{{ $s->cumplimientos }}</td>
                    <td class="monto">${{ number_format($s->audienciasMonto, 2) }}</td>
                    <td class="monto">${{ number_format($s->ratificacionesMonto, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-header">Resultados de Audiencias de Conciliación</div>
        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 18%;">Sede</th>
                    <th>Audiencias Programadas</th>
                    <th>Audiencias Celebradas</th>
                    <th>Convenios</th>
                    <th>No Conciliación</th>
                    <th>Falta de Int.</th>
                    <th>Incompetencia</th>
                    <th>1 Aud.</th>
                    <th>2 Aud.</th>
                    <th>3+ Aud.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audiencias as $a)
                <tr>
                    <td class="text-left bold">{{ $a->sede_nombre }}</td>
                    <td>{{ $a->audienencias_programadas ?? 0 }}</td>
                    <td>{{ $a->audienencias_celebradas }}</td>
                    <td>{{ $a->convenios}}</td>
                    <td>{{ $a->no_conciliacion}}</td>
                    <td>{{ $a->achivada }}</td>
                    <td>{{ $a->incompetencia ?? 0 }}</td>
                    <td>{{ $a->convenios }}</td>
                    <td>{{ $a->convenios }}</td>
                    <td>{{ $a->convenios }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-header">Estatus de Notificaciones Regionales</div>
        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 18%;">Sede</th>
                    <th>Total</th>
                    <th>Exitosa</th>
                    <!--th>Efect. %</th-->
                    <th>No Notif.</th>
                    <th>Pend.</th>
                    <th>Exhorto</th>
                    <th>Notificadas por el Centro</th>
                    <th>Notificadas por el Solicitante</th>
                    <!--th>NESC*</th-->
                    <!--th>NENSC**</th-->
                </tr>
            </thead>
            <tbody>
                @foreach($notificaciones as $n)
                @php $efect_n = ($n->Todas_notificaciones > 0) ? ($n->exitosamente / $n->Todas_notificaciones)*100 : 0; @endphp
                <tr>
                    <td class="text-left bold">{{ $n->sede_nombre }}</td>
                    <td>{{ $n->Todas_notificaciones ?? 0 }}</td>
                    <td class="bold">{{ $n->exitosamente }}</td>
                    <!--td class="efectividad">{{ number_format($efect_n, 1) }}%</td-->
                    <td>{{ $n->notificacion_Nonotificada }}</td>
                    <td>{{ $n->notificacion_pendientes }}</td>
                    <td>{{ $n->notificacion_exhortos }}</td>
                    <td>{{ $n->notificadas_centro }}</td>
                    <td>{{ $n->notificadas_trabajador }}</td>
                    <!--td>{{ $n->notificacion_NESC }}</td-->
                    <!--td>{{ $n->notificacion_NENSC }}</td-->
                </tr>
                @endforeach
            </tbody>
        </table>

        

        <p style="font-size: 10px; color: #888;">*NESC: No exitosa se constituye / **NENSC: No exitosa no se constituye</p>
        <p style="font-size: 10px; color: #888;">1 AUD, 2 AUD y 3 AUD representan expedientes en conjunto (solicitudes), no audiencias individuales.</p>
        

    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->page_text(280, 575, "Página {PAGE_NUM} de {PAGE_COUNT} - Centro de Conciliación Laboral", $font, 8, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>