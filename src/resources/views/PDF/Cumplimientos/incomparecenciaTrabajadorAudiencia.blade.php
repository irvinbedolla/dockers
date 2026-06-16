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
                margin: 50px 50px 50px 40px; /*Para colocar el texto*/
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
                font-size: 12px;
                text-align: justify;
                margin-top: 50px;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
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
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer></footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                            <tr>    
                                <td><b>Número de identificación único: </b></td>
                                <td>{{ $solicitud->NUE }} </td>
                            </tr> 
                            <tr>   
                                <td><b>Centro de conciliación: </b></td>
                                <td>{{ $solicitud->delegacion }} </td>
                            </tr>
                    </table>
                </div><br><br><br><br><br>
                <p><b>
                    Solicitante: {{$solicitante->nombre}}<br> 
                    Citado(s): 
                    @foreach ($citados as $citado)
                    {{ $citado->nombre }} {{ $citado->primer_apellido ?? '' }} {{ $citado->segundo_apellido ?? '' }}<br>
                    @endforeach
                    <br>
                </b></p><br>  
                <p>
                    En la Ciudad de <b>{{ $solicitud->delegacion }}</b>, Michoacán, siendo las <b>{{\Carbon\Carbon::parse($complimientos->updated_at)->translatedFormat('h:i')}}</b> horas, del día 
                    <b>{{\Carbon\Carbon::parse($complimientos->fecha)->translatedFormat('d \d\e F \d\e\l Y')}}</b>, fecha y hora señalada para El 
                    Cumplimiento de Pago establecido en la Clausula 5° y 6° del Convenio de Conciliación celebrado en Audiencia 
                    <b>{{\Carbon\Carbon::parse($complimientos->fecha_audiencia)->translatedFormat('d \d\e F \d\e\l Y')}}</b> a las
                    <b>{{\Carbon\Carbon::parse($complimientos->hora_audiencia)->translatedFormat('H:i')}} hrs</b> horas, dentro del número único de registro citado al rubro; ante la fe pública de la persona Conciliadora de nombre <b>{{$conciliador->name}}</b>, 
                    adscrita a la Delegación Regional <b>{{ $solicitud->delegacion }}</b> del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, en ejercicio de mis facultades 
                    establecidas en el artículo 684-E fracción XIII y XIV  párrafo cuarto de la Ley Federal del Trabajo y 20 fracción I, VI y XIII del Reglamento Interior del Centro de 
                    Conciliación Laboral del Estado de Michoacán de Ocampo, hago constar la siguiente:
                </p>
                <p><center><b>CERTIFICACIÓN:</b></center></p><br>
                <p>
                    Que comparece la(s) persona(s) de nombre <b>C. 
                    @foreach ($representantes as $representante)
                        @if($representante->nombre_representante != NULL)
                            {{ $representante->nombre_representante }} {{ $representante->primer_apellido_representante }} {{ $representante->segundo_apellido_representante ?? '' }}</b> en su carácter de representante 
                            legal de la parte patronal <b>{{$solicitud->empresa}}</b>
                        @else
                            {{ $representante->nombres_patronal }} {{ $representante->primer_apellido_patronal }} {{ $representante->segundo_apellido_patronal ?? '' }}</b>
                        @endif
                    @endforeach
                    , a dar cumplimiento al Convenio celebrado entre las partes ante este Centro el día 
                    <b>{{ \Carbon\Carbon::parse($solicitud->fecha_audiencia)->translatedFormat('d \d\e F \d\e\l Y') }}</b> a las <b>{{ \Carbon\Carbon::parse($solicitud->hora_audiencia)->translatedFormat('h:i') }}</b> hrs., 
                    haciendo constar la inasistencia de la parte trabajadora, no obstante de encontrarse legal y debidamente notificada de la fecha y hora del cumplimiento de pago de convenio, 
                    sin que exista causa justificada, motivo por el cual se <b>EMITE CONSTANCIA DE INCOMPARECENCIA DE PAGO</b> dejando a salvo los derechos de la parte compareciente para hacerlos valer ante 
                    la autoridad competente, para los efectos legales y administrativos a los que haya lugar.- Archívese el presente asunto y Notifíquese.- 
                    <br><br>

                    Así y con fundamento en los artículos 684-E fracción, X, XVI, 684-F, 684-H, 684-I, 939, y Título Quince de la Ley Federal del Trabajo; artículo 8, 27 de la Ley Orgánica del 
                    Centro de Conciliación Laboral del Estado de Michoacán de Ocampo; y artículo 20 fracciones I, VI, XIII del Reglamento Interior del Centro de Conciliación Laboral del Estado 
                    de Michoacán de Ocampo. <b>Doy fe.</b> 
                    
                </p>
                <br><br><br><br><br> 
                <div class="contenedor-firmas">
                <center><p><b>___________________________________<br>{{ mb_strtoupper($conciliador->name, 'UTF-8') }} 
                <br>FUNCIONARIO/A CONCILIADOR/A
                <br>DEL CENTRO DE CONCILIACIÓN LABORAL
                <br>DEL ESTADO DE MICHOACÁN DE OCAMPO</b></p></center>
                  {{-- <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:10px;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:0 5px;">
                                <div style="border-top: 2px solid #000; width:90%; margin: 0 auto 5px auto;"></div>
                                <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                        FUNCIONARIO/A CONCILIADOR/A<br>
                                        DEL CENTRO DE CONCILIACIÓN LABORAL
                                        DEL ESTADO DE MICHOACÁN DE OCAMPO
                                </b>
                            </td>
                        <td style="width:50%; vertical-align:top; padding:0 5px;">
                                <div style="border-top: 2px solid #000; width:90%; margin: 0 auto 5px auto;"></div>
                                <b>{{ mb_strtoupper($delegado->name, 'UTF-8') }}<br>
                                    DIRECTOR/A DEL CENTRO DE CONCILIACIÓN
                                    LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO                                  
                                </b>
                            </td>
                        </tr>
                    </table><br> --}}
                    <p style="font-size: 10px;">
                        LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DE LA CONSTANCIA DE INCOMPARECENCIA DE PAGO DE FECHA <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                    </p>  
                </div>       
            </div>

            @if((!empty($etiquetaIniciales) && !empty($inicialesConcluye)) && !empty($complimientos->fecha_conclucion) != NULL && $complimientos->fecha_conclucion > \Carbon\Carbon::parse('2026-06-03'))
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