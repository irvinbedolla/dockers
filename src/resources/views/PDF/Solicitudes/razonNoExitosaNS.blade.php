<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        
        <style>
            @page {
                margin: 0px 0px;
            }
            body {
                counter-reset: page;
                font-family: sans-serif;
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

            .footer-content::after {
                content: "Página " counter(page) " de " counter(pages);
            }
            body {
                margin: 0cm;
                padding: 0cm;
                background-color: transparent !important;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: black;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
            .content {
                padding: 3cm 2cm 2cm 2cm;
                position: relative;
                /*padding: 4cm 2cm 3cm 2cm; /* Deja espacio para encabezado y pie  padding: 100px 50px;*/
                z-index: 1;
            }
            p {
                line-height: 1.2;
                text-align: justify;
            }
            .page-break {
                page-break-after: always;
            }
            table, th, td {
                border: 1px solid #869b9c;
                border-collapse: collapse;
            }
            .header-table-container {
                width: 100%;
                display: block;
                clear: both;
                margin-bottom: 0;
            }

            #tabla_solicitud {
                width: 60%;
                float: right;
                border-collapse: collapse;
            }

            .razon-notificacion {
                clear: both; 
                padding-top: 1px; /* Controla la distancia con la tabla superior */
            }

            .seccion-firma {
                page-break-inside: avoid; /* Evita que la firma se parta en dos hojas */
                width: 100%;
            }

            .espaciador-firma {
                margin-top: 17px; /* Espacio para que el notificador firme manualmente */
            }
        </style>
        @php
            $descripcionesMedio = [
                'PLACAS OFICIALES' => 'LA(S) PLACAS DE SEÑALIZACIÓN OFICIAL MÁS PRÓXIMA(S) AL DOMICILIO EN QUE SE ACTÚA, CON EL RESPECTIVO NOMBRE DE LA ALCALDÍA, COLONIA Y CALLE,',
                'NÚMERO VISIBLE' => 'EL MÚMERO VISIBLE DEL INMUEBLE,',
                'NUMERACIÓN CONSISTENTE' => 'EL NÚMERO DEL INMUEBLE ES CONSISTENTE CON LA NUMERACIÓN DE LA CALLE,',
                'INFORMES DE VECINOS' => 'LOS INFORMES DE VECINOS DEL LUGAR, QUIENES CONFIRMAN QUE SE TRATA DEL DOMICILIO CORRECTO,',
                'RÓTULOS VISIBLES' => 'LOS RÓTULOS VISIBLES EN EL INMUEBLE'
            ];
        @endphp
    </head>

    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer></footer>
        <main>
            <div class="content">
                <div class="header-table-container">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }}</td>
                        </tr> 
                        <tr>   
                            <td><b>Centro de conciliación: </b></td>
                            <td>{{ $solicitud->delegacion }}</td>
                        </tr>
                        <tr>   
                            <td><b>Solicitante: </b></td>
                            <td>{{$solicitante->nombre}}</td>
                        </tr>
                        <tr>   
                            <td><b>Citado: </b></td>
                            <td>{{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}}</td>
                        </tr>
                    </table>
                </div>
                <!-- DELIGENCIA NO EXITOSA, NO SE LOCALIZA INTERIOR -->
                <div class="razon-notificacion"> 
                    <p><center><b>RAZÓN DE NOTIFICACIÓN</b></center></p><br>

                    @php
                        $fechaNotificacion = !empty($citado->fecha)
                            ? \Carbon\Carbon::parse($citado->fecha)
                            : null;
                    @endphp
                            
                    <p>Siendo las <b>{{ $fechaNotificacion ? $fechaNotificacion->format('H') : '' }} HORAS CON {{ $fechaNotificacion ? $fechaNotificacion->format('i') : '' }} MINUTOS
                        DEL DÍA {{ $fechaNotificacion ? mb_strtoupper($fechaNotificacion->translatedFormat('d \D\E F \D\E\L Y')) : '' }}, LIC. {{mb_strtoupper($notificador->name,'UTF-8')}}</b> en mi
                        calidad de notificador(a) adscrito al Centro de Conciliación Laboral, oficina estatal {{ $solicitud->delegacion }}, en 
                        ejercicio de las facultades conferidas en los artículos de la Ley Orgánica del Centro de Conciliación Laboral del 
                        Estado de Michoacán de Ocampo y 21 del reglamento interior del Centro de Conciliación Laboral del Estado de Michoacán 
                        de Ocampo, a efecto de dar cumplimiento al <b>CITATORIO DE CONCILIACIÓN</b> de fecha <b>{{ mb_strtoupper(\Carbon\Carbon::parse($fechaCitatorio)->translatedFormat('d \D\E F \D\E\L Y'), 'UTF-8') }}</b> 
                        en el expediente citado, en el que se ordena NOTIFICAR <b>AL CITADO: {{$citado->nombre}}@if($citado->primer_apellido!=null) {{$citado->primer_apellido}}@endif @if($citado->segundo_apellido!=null) {{$citado->segundo_apellido}}@endif</b>, 
                        en el domicilio señalado en <b>{{strtoupper($citado->tipo_vialidad)}} {{$citado->calle}} {{$citado->n_ext}}@if($citado->n_int!=null) INT. {{$citado->n_int}}@endif, COLONIA {{$citado->colonia}}, 
                        {{mb_strtoupper($municipioCitado, 'UTF-8')}}, CP {{$citado->cp}}, {{mb_strtoupper($estadoCitado, 'UTF-8')}}.</b><br><br>

                        Devuelvo el presente expediente sin poder diligenciar en sus términos el <b>CITATORIO DE CONCILIACIÓN</b> de fecha <b>{{ mb_strtoupper(\Carbon\Carbon::parse($fechaCitatorio)->translatedFormat('d \D\E F \D\E\L Y'), 'UTF-8') }}</b>, por lo que hace a la notificación de 
                        <b>{{$citado->nombre}} {{$citado->primer_apellido ?? ''}} {{$citado->segundo_apellido ?? ''}}</b>, parte citada en el presente expediente; en virtud de que 

                        @switch($citado->problema_diligencia)
                            @case('OMITE NÚMERO')
                                la parte solicitante es omisa en señalar el número del domicilio donde debe de realizarse la precitada diligencia; por lo que el domicilio indicado se encuentra impreciso, al estar imcompleto.
                                En esa tesitura, al carecer del requisito establecido en los artículos 712, párrafo primero, y 739, párrafo segundo, ambos de la Ley Federal del Trabajo, me encuentro imposibilitado para dar cumplimiento
                                total al <b>CITATORIO DE CONCILIACIÓN</b> y a efecto de evitar posibles nulidades durante el procedimiento.
                                @break
                            @case('OMITE VIALIDAD')
                                la parte solicitante es omisa en señalar la vialidad del domicilio donde debe de realizarse la precitada diligencia; por lo que el domicilio indicado se encuentra impreciso, al estar imcompleto.
                                En esa tesitura, al carecer del requisito establecido en los artículos 712, párrafo primero, y 739, párrafo segundo, ambos de la Ley Federal del Trabajo, me encuentro imposibilitado para dar cumplimiento
                                total al <b>CITATORIO DE CONCILIACIÓN</b> y a efecto de evitar posibles nulidades durante el procedimiento.
                                @break
                            @case('OMITE COLONIA')
                                la parte solicitante es omisa en señalar la colonia del domicilio donde debe de realizarse la precitada diligencia; por lo que el domicilio indicado se encuentra impreciso, al estar imcompleto.
                                En esa tesitura, al carecer del requisito establecido en los artículos 712, párrafo primero, y 739, párrafo segundo, ambos de la Ley Federal del Trabajo, me encuentro imposibilitado para dar cumplimiento
                                total al <b>CITATORIO DE CONCILIACIÓN</b> y a efecto de evitar posibles nulidades durante el procedimiento.
                                @break
                            @case('OMITE MUNICIPIO')
                                la parte solicitante es omisa en señalar el municipio del domicilio donde debe de realizarse la precitada diligencia; por lo que el domicilio indicado se encuentra impreciso, al estar imcompleto.
                                En esa tesitura, al carecer del requisito establecido en los artículos 712, párrafo primero, y 739, párrafo segundo, ambos de la Ley Federal del Trabajo, me encuentro imposibilitado para dar cumplimiento
                                total al <b>CITATORIO DE CONCILIACIÓN</b> y a efecto de evitar posibles nulidades durante el procedimiento.
                                @break
                            @case('FUERA DE LA JURISDICCIÓN')
                                la parte solicitante señaló un domicilio que se encuentra fuera de la jurisdicción de esta Autoridad Conciliadora, por lo que existe imposibilidad para dar cumplimiento total a lo establecido en el citado
                                proveído, al carecer de facultades para realizar diligencias inherentes al cargo de Notificador fuera de la jurisdicción de esta Autoridad. 
                                @break
                            @case('NO HAY COPIAS SUFICIENTES')
                                la parte solicitante se abstiene de cumplir con lo establecido en la parte inicial del artículo 872 de la Ley Federal del Trabajo, en razón de que no proporciona las copias de translado suficientes y
                                necesarias para darle cumplimiento a lo ordenado por la autoridad.
                                @break
                            @default
                                @break
                        @endswitch

                    </p>  

                    <div class="seccion-firma">
                        <br><br>
                        <b>Doy cuenta a la autoridad conciliadora competente y lo hago constar para todos los efectos legales a que haya lugar. DOY FE.</b>
                    
                        <div class="espaciador-firma"><br>
                            <center><b>___________________________________<br>LIC. {{ mb_strtoupper($notificador->name, 'UTF-8')}}<br> FUNCIONARIO/A NOTIFICADOR/A</b></center>
                        </div>
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
</html>