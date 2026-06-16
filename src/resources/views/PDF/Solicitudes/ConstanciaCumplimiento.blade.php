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
                padding-top: 95px;
            }
            main{
                margin: 50px 0 50px 0; /*Para colocar el texto*/
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
                font-size: 14px;
                text-align: justify;
                margin-left: 3cm;     
                margin-right: 2cm; 
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            } 
            .table-compacta td, 
            .table-compacta th {
                padding: 2px 5px !important; /* Reduce el espacio interno arriba y abajo */
                line-height: 1.1 !important;  /* Ajusta la altura del texto */
                vertical-align: middle;
            }
            .table-compacta {
                margin-bottom: 10px !important; /* Reduce espacio entre tablas */
            }
            /* Contenedor que agrupa las firmas */
            .salto-inteligente {
                display: block;
                height: 2cm;           
                margin-bottom: -2cm;    
                page-break-inside: avoid;
            }

            .contenedor-firmas {
                page-break-inside: avoid; 
            }

            .etiqueta-iniciales-pie {
                position: fixed;
                bottom: 60px;
                left: 0;
                right: 0;
                padding-left: 50px;
                padding-right: 50px;
                text-align: left;
                font-size: 10px;
                z-index: 10;
            }
        </style>
    </head>
    @php
        $nombramiento_delegado='';
        if($solicitud->delegacion === 'Morelia' || $solicitud->delegacion === 'Zitácuaro'){
            $nombramiento_delegado='DIRECTOR DE LA DELEGACIÓN REGIONAL DE MORELIA';
        }    
        if($solicitud->delegacion === 'Uruapan' || $solicitud->delegacion === 'Lázaro Cárdenas'){
            $nombramiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE URUAPAN';
        }
        if($solicitud->delegacion === 'Zamora' || $solicitud->delegacion === 'Sahuayo') {
            $nombramiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE ZAMORA';
        }  
    @endphp
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                        <tr>   
                            <td><b>Oficina: </b></td>
                            <td>{{ mb_strtoupper($solicitud->delegacion, 'UTF-8') }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr>  
                    </table>
                </div><br><br><br>
                <p><center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</b></center></p><br><br>
                <p><b>
                    Trabajador(a): {{ $solicitud->solicitante->nombre }} {{ $solicitud->solicitante->primero_trabajador }} {{ $solicitud->solicitante->segundo_trabajador }} <br> 
                    Empleador(a): @foreach ($solicitud->citados as $citado)
                        {{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}} <br>
                    @endforeach
                    Fecha y hora de audiencia: {{ \Carbon\Carbon::parse($audienciaFecha)->translatedFormat('d \d\e F \d\e\l Y') }} a las {{ \Carbon\Carbon::parse($audienciaFecha)->translatedFormat('H:i') }} horas.<br> 
                    Asistencia de los interesados: Si. <br>
                    <!--Fecha del conflicto: [SOLICITUD_FECHA_CONFLICTO]  <br>
                    Posible prescripción de derechos: [SOLICITUD_PRESCRIPCION] <br> -->
                    Convenio conciliatorio: Si.
                </></p> 

                <p>
                    <center><b>CONSTANCIA DE CUMPLIMIENTO TOTAL DE CONVENIO</b></center>
                </p><br>

                <p>
                    @if($solicitud->tipo_solicitud == 1)
                        <b>Fundamentación:</b> Artículos 33 párrafo segundo, 590-E, 590-F, 684-C y 684-E, fracción XIV, 684-F, fracción VII de la Ley Federal del Trabajo, 
                        y artículo 20 del Reglamento Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo.<br><br>
                    @else
                        <b>Fundamentación:</b> Artículos 684-E, fracción XIV, 684-F, fracción VII de la Ley Federal del Trabajo.<br><br>
                    @endif
                    <b>Motivación:</b> Conforme a la determinación de dar por terminado el conflicto laboral, la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b>, 
                    celebraron el Convenio de Conciliación de fecha <b>{{ \Carbon\Carbon::parse($audienciaFecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> ante esta 
                    Autoridad Conciliadora como resultado de la audiencia 
                    de conciliación celebrada el día <b>{{ \Carbon\Carbon::parse($audienciaFecha)->translatedFormat('d \d\e F \d\e\l Y') }}.</b><br><br>
                                
                    De acuerdo con lo establecido en el convenio referido el <b>EMPLEADOR</b> se obligó al pago de los siguientes conceptos:
              
                    <div class="table-responsive">
                        <table id="pagos" class="table-striped table-compacta" style="width:100%;">
                            <thead>
                                <th style="display: none;">ID</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Monto</th>
                                <th>Descripción</th>
                            </thead>
                            <tbody>
                                @foreach($pagos as $pago)
                                    <tr>
                                        <td style="display: none;">{{$pago->id_solicitud}}</td>
                                        <td>{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}</td> 
                                        <td>{{ \Carbon\Carbon::parse(str_replace(' HORAS', '', $pago->hora))->format('H:i') }} HORAS</td>
                                        <td>${{ number_format($pago->monto, 2) }}</td>
                                        <td><p>{{$pago->observaciones}}</p></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>      
                    </div><br>
                                
                    En este sentido, el <b>EMPLEADOR</b> ha dado cumplimiento a la <b>totalidad</b> de los conceptos contenidos en el Convenio de Conciliación 
                    celebrado entre las <b>PARTES. Doy fe.</b></p><br>
                    <div class="salto-inteligente"></div>
                    <div class="contenedor-firmas">       
                        <p> <b> Con fecha {{ $pago->updated_at->translatedFormat('d \d\e F \d\e\l Y') }}
                            se emite la presente Constancia de Cumplimiento del Convenio de Conciliación, con fundamento en la fracción XIV del 
                            artículo 684-E, fracción VIII del artículo 684-F de la Ley Federal del Trabajo, y artículo 20 del Reglamento Interior del Centro de Conciliación 
                            Laboral del Estado de Michoacán de Ocampo.</b>
                        </p>
                        <br><br><br>
                        <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:10px;">
                            <!--tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b> {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}<br>
                                        LA PARTE TRABAJADORA
                                    </b>
                                </td>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}<br>
                                        LA PARTE EMPLEADORA
                                    </b>
                                </td>
                            </!--tr>
                            <br><br><br-->
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><b>Doy fe</b><br><br><br><br>
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                                FUNCIONARIO/A CONCILIADOR/A<br>
                                                DEL CENTRO DE CONCILIACIÓN LABORAL
                                                DEL ESTADO DE MICHOACÁN DE OCAMPO
                                    </b>
                                </td>
                            </tr>
                        </table> 
                        <br>
                        <p style="font-size: 10px;">
                            LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DE LA CONSTANCIA DE CUMPLIMIENTO TOTAL DE CONVENIO DE FECHA <b>{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                        </p>  
                    </div>                      
            </div>

            @if(!empty($etiquetaIniciales) && !empty($inicialesConcluye))
                <div class="etiqueta-iniciales-pie">
                    <small><b>{{ $etiquetaIniciales }}</b></small><br>
                    <small>Elaboró: <b>{{ $inicialesConcluye }}</b></small>
                </div>
            @endif
            
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $y = $pdf->get_height() - 44;
                    $x = ($pdf->get_width() / 2) - 50;
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0));
                }
            </script>
        </main>
    </body>