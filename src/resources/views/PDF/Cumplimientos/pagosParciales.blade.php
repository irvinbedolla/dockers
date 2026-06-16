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
                padding-top: 85px;
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
                line-height: 1.3;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
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
        </style>
    </head>
   {{-- @php
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
    @endphp--}}
    @php
        $nombramiento_delegado = '';
        
        if ($solicitud->delegacion === 'Morelia' || $solicitud->delegacion === 'Zitácuaro') {
            $nombramiento_delegado = "DIRECTOR DE LA DELEGACIÓN REGIONAL MORELIA\nDEL CENTRO DE CONCILIACIÓN LABORAL\nDEL ESTADO DE MICHOACÁN DE OCAMPO";
        }    
        if ($solicitud->delegacion === 'Uruapan' || $solicitud->delegacion === 'Lázaro Cárdenas') {
            $nombramiento_delegado = "DIRECTOR DE LA DELEGACIÓN REGIONAL URUAPAN\nDEL CENTRO DE CONCILIACIÓN LABORAL\nDEL ESTADO DE MICHOACÁN DE OCAMPO";
        }
        if ($solicitud->delegacion === 'Zamora' || $solicitud->delegacion === 'Sahuayo') {
            $nombramiento_delegado = "DIRECTORA DE LA DELEGACIÓN REGIONAL ZAMORA\nDEL CENTRO DE CONCILIACIÓN LABORAL\nDEL ESTADO DE MICHOACÁN DE OCAMPO";
        }  
    @endphp
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_pago" class="table-striped" style="width:60%; float: right;">
                            <tr>    
                                <td><b>Número de identificación único: </b></td>
                                <td>{{ $solicitud->NUE }} </td>
                            </tr> 
                            <tr>   
                                <td><b>Centro de conciliación: </b></td>
                                <td>{{ $solicitud->delegacion }} </td>
                            </tr>
                    </table>
                </div><br><br><br>
                <p><center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</b></center></p><br>

                <p><b>
                    Trabajador(a): {{ $solicitud->nombre_trabajador }} <br> 
                    Empresa/Patrón: {{ $solicitud->empresa_representante }}<br>
                    Funcionario/a Conciliador/a Responsable: {{$conciliador->name}}<br>
                    Fecha y hora de cumplimiento: {{\Carbon\Carbon::parse($solicitud->update)->translatedFormat('d \d\e F \d\e\l Y')}} a las {{\Carbon\Carbon::parse($solicitud->update)->translatedFormat('h:i')}} horas.<br>
                    Asistencia de los interesados: Si<br>
                    Convenio conciliatorio: Si<br>

                    <center><b>CONSTANCIA DE PAGO DE CUMPLIMIENTO</b></center><br>
                </b></p>  
               
                <p>
                    <b>Fundamentación:</b> Artículos 33 párrafo segundo, 590-E, 590-F, 684-C y 684-E fracciones XIII y XIV, 684-F fracción VII de la Ley Federal del Trabajo, artículo 8 fracción I, II y III 
                    de la Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo y artículo 20 del Reglamento Interior del Centro de Conciliación Laboral del Estado de 
                    Michoacán de Ocampo.<br><br>

                    <b>Motivación:</b> Conforme a la determinación de dar por terminado el conflicto laboral, la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b>, celebraron el Convenio de Conciliación 
                    de fecha <b>{{ \Carbon\Carbon::parse($solicitud->update)->translatedFormat('d \d\e F \d\e\l Y') }}</b> ante esta Autoridad Conciliadora como resultado de la audiencia de conciliación 
                    celebrada el día <b>{{ \Carbon\Carbon::parse($solicitud->update)->translatedFormat('d \d\e F \d\e\l Y') }}</b> a las <b>{{ \Carbon\Carbon::parse($solicitud->update)->translatedFormat('h:i')}} horas.</b><!-- de 
                    <b>{{$solicitud->hora}}</b> a <b>{{$solicitud->hora_fin}} hrs--></b><br><br>

                    Las <b>PARTES</b> acordaron <b>PAGO</b> en el convenio referido, en este sentido, el <b>EMPLEADOR</b> da cumplimiento ante esta Autoridad Conciliadora al siguiente concepto:<br>

                    <p><b>{{ $pagos->observaciones}}</b></p> 

                    Quien suscribe da fe del cumplimiento del concepto anteriormente descrito por parte del <b>EMPLEADOR. Doy fe.</b>
                </p>
                <div class="salto-inteligente"></div>
                <div class="contenedor-firmas">
                    <p>
                        <b>Con fecha {{ $pagos->updated_at->translatedFormat('d \d\e F \d\e\l Y') }} se emite la presente Constancia de Pago, con 
                        fundamento en la fracción XIV del artículo 684-E y fracción VIII del artículo 684-F de la Ley Federal del Trabajo.</b>
                    </p>
                    <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:10px;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:0 5px;"><b>Doy fe</b><br><br><br><br>
                                <div style="border-top: 2px solid #000; width:90%; margin: 0 auto 5px auto;"></div>
                                <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                        FUNCIONARIO/A CONCILIADOR/A<br>
                                        DEL CENTRO DE CONCILIACIÓN LABORAL
                                        DEL ESTADO DE MICHOACÁN DE OCAMPO
                                </b>
                            </td>
                        <td style="width:50%; vertical-align:top; padding:0 5px;"><b>Vo. Bo.</b><br><br><br><br>
                                <div style="border-top: 2px solid #000; width:90%; margin: 0 auto 5px auto;"></div>
                                <b>{{ mb_strtoupper($delegado ? $delegado->name : 'N/A', 'UTF-8') }}<br>
                                    {!! nl2br(e($nombramiento_delegado)) !!}                                 
                                </b>
                            </td>
                        </tr>
                    </table><br>
                    <p style="font-size: 10px;">
                        LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DE LA CONSTANCIA DE PAGO DE CUMPLIMIENTO DE FECHA <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                    </p>  
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