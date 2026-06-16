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
                </div><br><br>
                <p><center><b>CONSTANCIA DE INCOMPETENCIA</b></center></p><br>
                <p><b>
                    Solicitante: {{ $solicitante->nombre }} <br> 
                    Citado(s):
                    @foreach($citados as $citado)    
                        {{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}}<br>
                    @endforeach
                    <br>
                    Fecha de presentación de solicitud: {{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }} <br>
                    Posible prescripción de derechos: No <br>
                </b></p>  
                <p>
                    <b>Fundamentación: </b>Artículos 123 fracción XXXI de la Constitución Política de los Estados Unidos Mexicanos, 527, 684-E, fracción V de la Ley Federal del Trabajo 5 y 8, 
                    fracción I de la Ley Orgánica del centro de Conciliación Laboral del Estado de Michoacán de Ocampo.<br><br>

                    <b>Motivación: </b>Con fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, <b>{{ $solicitante->nombre }}</b> presentó ante la Oficina Regional del Centro de Conciliación Laboral del 
                    Estado de Michoacán Delegación <b>{{ $solicitud->delegacion }}</b> la solicitud <b>{{ $solicitud->NUE }}.</b><br><br>

                    La Oficina Regional del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, de conformidad con la información aportada y derivado del análisis de la solicitud mencionada, esta Autoridad 
                    Conciliadora se declara incompetente por declinatoria, toda vez que la rama industrial o de servicio materia de la solicitud presentada es de cáracter federal de conformidad con la fracción XXXI 
                    del apartado A del artículo 123 Constitucional, así como del artículo 527 de la Ley Federal del Trabajo.<br><br>

                    <!-- LLenado de los conciliadores -->
                    <b>{{$solicitud->observaciones}}</b><br><br>

                    En este sentido y de conformidad con los principios constitucionales de legalidad, imparcialidad, confiabilidad, eficacia, confidencialidad, objetividad, profesionalismo, transparencia y publicidad, se notifica al Solicitante 
                    de la incompetencia por declinatoria y se remite copia certificada de la presente constancia al Centro de Conciliación Laboral competente.<br><br>

                    Se emite la presente constancia con fecha <b>{{ $solicitud->updated_at->translatedFormat('d \d\e F \d\e\l Y') }}</b> dejando a salvo los derechos del solicitante para continuar con el procedimiento de conciliación 
                    ante la Autoridad Conciliadora competente.
                </p>
                <div class="salto-inteligente"></div>
                <div class="contenedor-firmas">
                    <p>
                        Finalmente, se dejan a salvo los derechos de los interesados para continuar con el procedimiento de conciliación ante el Centro de Conciliación Laboral competente, en términos de los artículos 527 y 684-E fracción 
                        V párrafo segundo de la Ley Federal del Trabajo. Artículo 18 fracción XII del Reglamento Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo.<b>Doy Fe.</b>
                    </p>
                    <table style="width: 100%; margin-top: 50px;">
                        <tr>
                            <td style="width: 100%; vertical-align: top; padding: 0 5px; text-align: center;">
                                <div style="border-top: 2px solid #000; width: 50%; margin: 0 auto 5px auto;"></div>
                                <b>
                                    {{ mb_strtoupper($delegado ? $delegado->name : 'N/A', 'UTF-8') }}<br>
                                    {!! nl2br(e($nombramiento_delegado)) !!}
                                </b>
                            </td>
                        </tr>
                    </table> <br>
                    <p style="font-size: 10px;">
                        LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DE LA CONSTANCIA DE INCOMPETENCIA DE FECHA <b>{{ $solicitud->updated_at->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                    </p>  
                </div>     
            </div>
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