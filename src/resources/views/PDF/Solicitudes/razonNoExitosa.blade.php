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
            //texto de el cercioramiento en el prime parrafo del documento
            $textoCercioramiento = match($citado->problema_diligencia) {
                'CERRADO', 'NO ACCESO AL INMUEBLE', 'NO SEÑALA INTERIOR', 'NO LOGRO LOCALIZAR EL NÚMERO', 'NO SE LOCALIZA EL INMUEBLE CON NÚMERO, MANZANA, LOTE, ETC. SEÑALADOS' 
                    => 'éstos el Municipio, Colonia y Vialidad correctas señaladas en la solicitud de conciliación, por',
                'NÚMERO INTERIOR SEÑALADO NO SE LOCALIZÓ EN DOMICILIO' 
                    => 'el domicilio correcto por',
                'NO EXISTE EN COLONIA' 
                    => 'éstos el Municipio, Colonia correctas señaladas en la solicitud de conciliación, por',
                'NO EXISTE EN MUNICIPIO' 
                    => 'éste el Municipio correcto señalado por la parte solicitante en su solicitud de conciliación, por',
                'RAZÓN SOCIAL DIVERSA' 
                    => 'el domicilio correcto señalado en autos, por así indicarlo',
                'OTROS' 
                    => 'vialidad, colonia y municipio correctos por',
                default => '',
            };
            //Texto especifico del problema indicado
            $textoProblemaBasico = match($citado->problema_diligencia) {
                'CERRADO' => 'Hago constar a la autoridad conciliadora competente que el acceso se encuentra cerrado; no obstante, procedí a tocar en repetidas ocasiones, sin haber recibido respuesta. Y después de haber esperado un tiempo prudente, lógico y razonable, nadie acude a mi llamado, por lo que no tengo persona alguna con quien entender la presente diligencia; adicionalmente hago constar que ',
                'NO SEÑALA INTERIOR' => 'Hago constar a la autoridad conciliadora competente que al recorrer la parte señalada del inmueble, no logro localizar el número interior proporcionado por la parte solicitante; adicionalmente hago ',
                'NÚMERO INTERIOR SEÑALADO NO SE LOCALIZÓ EN DOMICILIO', 'NO LOGRO LOCALIZAR EL NÚMERO' => 'Hago constar a la autoridad conciliadora competente que después de haber recorrido en su totalidad dicha vialidad, en el tramo correspondiente a la colonia antes mencionada, no me es posible localizar inmueble alguno marcado con el número oficial proporcionado por el solicitante; adicionalmente hago constar que ',
                'NO SE LOCALIZA EL INMUEBLE CON NÚMERO, MANZANA, LOTE, ETC. SEALADOS' => 'Hago constar a la autoridad conciliadora competente que después de haber recorrido en su totalidad dicha vialidad, en el tramo correspondiente a la colonia antes mencionada, no me es posible localizar el inmueble marcado; adicionalmente hago constar que ',
                'NO EXISTE EN COLONIA' => 'De donde se desprende el nombre de las mismas; después de haber recorrido dicha Colonia, no me es posible localizar VIALIDAD alguna que, a la letra, ostente el nombre oficial de la vialidad proporcionada por el solicitante; adicionalmente hago constar que ',
                'NO EXISTE EN MUNICIPIO' => 'Y después de haber recorrido en dicho Municipio no me es posible localizar colonia alguna que, a la letra, ostente el nombre oficial de la colonia proporcionada por el solicitante; adicionalmente hago constar que ',
                'OTROS' => '<br><br>Hago constar  a esta autoridad conciliadora que ',
                default => ''
            };
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
                <!-- DELIGENCIA NO EXITOSA, SE CONSTITUYE -->
                <div class="razon-notificacion"> 
                    <p><center><b>RAZÓN DE NOTIFICACIÓN</b></center></p>

                    @php
                        $fechaNotificacion = !empty($citado->fecha)
                            ? \Carbon\Carbon::parse($citado->fecha)
                            : null;
                    @endphp
                            
                    <p>Siendo las <b>{{ $fechaNotificacion ? $fechaNotificacion->format('H') : '' }} HORAS CON {{ $fechaNotificacion ? $fechaNotificacion->format('i') : '' }} MINUTOS
                        DEL DÍA {{ $fechaNotificacion ? mb_strtoupper($fechaNotificacion->translatedFormat('d \D\E F \D\E\L Y')) : '' }}, LIC. {{$notificador->name}}</b>, en mi
                        calidad de notificador(a) adscrito al Centro de Conciliación Laboral, oficina estatal <b>{{ $solicitud->delegacion }}</b>, 
                        a efecto de dar cumplimiento al <b>CITATORIO DE CONCILIACIÓN</b> de fecha <b>{{ mb_strtoupper(\Carbon\Carbon::parse($fechaCitatorio)->translatedFormat('d \D\E F \D\E\L Y'), 'UTF-8') }}</b> 
                        en el expediente citado, en el que se ordena NOTIFICAR <b>AL CITADO: {{$citado->nombre}}@if($citado->primer_apellido!=null) {{$citado->primer_apellido}}@endif @if($citado->segundo_apellido!=null) {{$citado->segundo_apellido}}@endif</b>, 
                        en el domicilio señalado en <b>{{mb_strtoupper($citado->tipo_vialidad, 'UTF-8')}} {{$citado->calle}} {{$citado->n_ext}}@if($citado->n_int!=null) INT. {{$citado->n_int}}@endif, COLONIA {{$citado->colonia}}, 
                        {{mb_strtoupper($municipioCitado, 'UTF-8')}}, CP {{$citado->cp}}, {{mb_strtoupper($estadoCitado, 'UTF-8')}}.</b> <br><br>
                        
                        Cerciorándome de ser {{ $textoCercioramiento }} {{-- Texto en el primer parrafo --}}
                        <b>
                        @php
                                $letras = range('A', 'Z');
                                $index = 0;

                                if (is_array($citado->medio)) {
                                    $medios = $citado->medio;
                                } elseif (is_string($citado->medio)) {
                                    $decoded = json_decode($citado->medio, true);
                                    $medios = is_array($decoded)
                                        ? $decoded
                                        : array_map('trim', explode(',', $citado->medio));
                                } else {
                                    $medios = [];
                                }
                            @endphp

                            @foreach($medios as $medioSeleccionado)
                                @if(isset($descripcionesMedio[$medioSeleccionado]))
                                    <strong>{{ $letras[$index] }})</strong>
                                    {{ $descripcionesMedio[$medioSeleccionado] }}
                                    @php $index++; @endphp
                                @endif
                            @endforeach
                        </b> 
                        A mayor abundamiento, verifico que cerca del domicilio se encuentran los siguientes puntos de referencia:
                        <b>{{$citado->abundar_area}}.</b> De igual forma, he constatado que se trata de un inmueble 
                        con las siguientes características:
                        {{--@if($citado->problema_diligencia === 'NO ACCESO AL INMUEBLE')
                            <b>{{$citado->especificar}}.</b><br><br>
                        @else--}}
                        <b>{{$citado->abundar_inmueble}}.</b><br><br>
                        {{--@endif--}}
                        {{-- Aqui se llama al tipo de problema --}}
                        @if($textoProblemaBasico !== '')
                            {!! $textoProblemaBasico !!}
                        @endif

                        {{-- Como No acceso al inmueble y razon scial tienen mas datos en cuenta se agregan por aparte --}}
                        @if($citado->problema_diligencia === 'NO ACCESO AL INMUEBLE' || $citado->problema_diligencia === 'RAZÓN SOCIAL DIVERSA')
                            @if($citado->problema_diligencia === 'NO ACCESO AL INMUEBLE')
                                Hago constar a la autoridad conciliadora competente que se me niega el acceso al inmueble. La persona que me atiende en la entrada dice llamarse 
                            @else
                                Mismos que coinciden con las señaladas en la solicitud de conciliación. Asimismo, por los informes que en tal sentido me proporciona la persona 
                                con quien se entiende la presente diligencia quien dice llamarse 
                            @endif

                            {{-- verifica si se ingreso el nombre --}}
                            @if($citado->nombre_notificacion != null)
                                <b>{{ mb_strtoupper($citado->nombre_notificacion, 'UTF-8') }}</b>
                            @else 
                                <b>NO PROPORCIONA SU NOMBRE, </b>
                            @endif
                            
                            <b>QUIEN</b> 
                                {{-- Bverifica si se agrego una forma de identificacion y si no, se describe a la persona --}}
                            @if(in_array($citado->identificacion_notificacion, ['NO PROPORCIONA', 'NO ATIENDE PRESENCIALMENTE'])) 
                                <b>NO SE IDENTIFICA {{ mb_strtoupper($citado->motivo_identificacion, 'UTF-8') }}. </b> 
                                @if($citado->identificacion_notificacion === 'NO PROPORCIONA')
                                    Procedo a especificar su media filiación, que incluye los siguientes rasgos: <b>SEXO {{ mb_strtoupper($citado->genero, 'UTF-8') }}, 
                                        TEZ {{ mb_strtoupper($citado->tez, 'UTF-8') }}, EDAD {{ mb_strtoupper($citado->edad, 'UTF-8') }} AÑOS, ALTURA {{ mb_strtoupper($citado->altura, 'UTF-8') }} M, 
                                        COMPLEXIÓN {{ mb_strtoupper($citado->complexion, 'UTF-8') }}, CABELLO {{ mb_strtoupper($citado->cabello, 'UTF-8') }} Y OJOS {{ mb_strtoupper($citado->ojos, 'UTF-8') }}. 
                                        LO ANTERIOR SE HACE DE MANERA APROXIMADA, YA QUE EL SUSCRITO NO ES PERITO EN LA MATERIA.</b>
                                @endif
                            @else 
                                <b>SE IDENTIFICA CON {{ mb_strtoupper($citado->identificacion_notificacion, 'UTF-8') }}</b> de número <b>{{$citado->num_identificacion}}</b>
                            @endif

                            {{-- texto del problema --}}
                            @if($citado->problema_diligencia === 'NO ACCESO AL INMUEBLE')
                                Dicha persona me niega el acceso; adicionalmente hago constar que <b>{{ $citado->especificar }}</b><br><br>
                            @else
                                Enseguida me identifico en este acto con credencial expedida por el Centro de Conciliación Laboral, oficina estatal {{ mb_strtoupper($solicitud->delegacion,'UTF-8') }}, que me acredita 
                                como Notificador y le informo el motivo de mi visita, mediante lectura del <b>CITATORIO DE CONCILIACIÓN</b> antes mencionado, requiriéndole 
                                así la presencia del <b>REPRESENTANTE LEGAL DEL CITADO: {{ $citado->nombre }} @if($citado->primer_apellido != null) {{ $citado->primer_apellido }} @endif 
                                @if($citado->segundo_apellido != null) {{ $citado->segundo_apellido }} @endif</b>, a fin de NOTIFICARLO, en cumplimiento a lo ordenado; la persona que 
                                me atiende manifiesta que el citado solicitado no habita, labora ni tiene su principal asiento de negocios en el domicilio en el que se actúa, 
                                acreditando su dicho con la siguiente documentación: <b>{{ $citado->especificar }}</b><br><br>
                            @endif
                            En esa razón, me encuentro imposibilitado para dar cumplimiento a lo ordenado en el <b>CITATORIO DE CONCILIACIÓN</b>; toda vez que no
                            cuento con los elementos de cercioramiento requeridos por el Artículo 743 Fracción I de la Ley Federal del Trabajo, por lo que me es
                            imposible dar cumplimiento al <b>CITATORIO</b> antes citado.
                        @endif

                        
                        {{--@if ($citado->problema_diligencia !== 'RAZÓN SOCIAL DIVERSA')
                            Hago constar a la autoridad conciliadora competente que se me niega el acceso al inmueble. La persona que me atiende en la entrada dice 
                            llamarse <b>{{$citado->especificar}}</b>
                        @endif--}}
                        {{--@if ($citado->problema_diligencia === 'CERRADO')--}}
                        @if ($citado->problema_diligencia !== 'RAZÓN SOCIAL DIVERSA' && $citado->problema_diligencia !== 'NO ACCESO AL INMUEBLE')
                            <b>{{$citado->especificar}}</b><br><br>
                            En esa razón, me encuentro imposibilitado para dar cumplimiento a lo ordenado por la autoridad conciliadora; toda vez que no cuento con los elementos de cercioramiento 
                            requeridos por el Artículo 743 Fracción I de la Ley Federal del Trabajo, por lo que me es imposible dar cumplimiento al <b>CITATORIO</b> antes citado.
                        @endif
                    </p>
                    <div class="seccion-firma">
                        Anexando impresión fotográfica para constancia legal. <br><br>
                        <b>Doy cuenta a la autoridad conciliadora competente y lo hago constar para todos los efectos legales a que haya lugar. DOY FE.</b>
                    
                        <div class="espaciador-firma"><br>
                            <center><b>___________________________________<br>LIC. {{ mb_strtoupper($notificador->name, 'UTF-8')}}<br> FUNCIONARIO/A NOTIFICADOR/A</b></center>
                        </div>
                    </div>
                </div>
                <div class="page-break"></div> <!-- Genera un salto de línea-->
                @foreach($imagenes as $index => $imagen) <!--Muestra una fotografía por hoja, númerando por anexos-->
                    @if($imagen)
                        <div class="content">
                            <div class="table-responsive">
                                <table id="tabla_solicitud" class="table-striped" style="width:65%; float: right;">
                                    <tr>   
                                        <td><b>ANEXO FOTOGRAFÍAS</b></td>
                                        <td><b>{{ $index + 1 }}</b></td>
                                    </tr>
                                    <tr>    
                                        <td><b>Número de identificación único: </b></td>
                                        <td>{{ $solicitud->NUE }}</td>
                                    </tr> 
                                    <tr>   
                                        <td><b>Centro de conciliación: </b></td>
                                        <td>{{ $solicitud->delegacion }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <img src="{{ $imagen }}" style="width: 100%; height: 90%;">
                        </div>
                    @endif
                @endforeach
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