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
                /* Definimos márgenes globales para que el texto nunca toque los bordes */
                /* 3cm arriba para dejar espacio al logo/membrete en cada página */
                margin: 3cm 2cm 3cm 2cm; 
            }
            
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: black;
                margin: 0;
                padding: 0;
            }

            .fondo-membrete {
                position: fixed;
                top: -3cm; /* Compensa el margen del @page */
                left: -2cm;
                width: 21cm; /* Ancho estándar A4 */
                height: 29.7cm; /* Alto estándar A4 */
                z-index: -1;
            }

            .content {
                /* Eliminamos el padding excesivo aquí ya lo controla @page */
                position: relative;
                z-index: 1;
            }

            /* Clase para forzar salto si los citados son muchos */
            .page-break {
                page-break-after: always;
            }

            /* Pie fijo: código de delegación + elaboró */
            .etiqueta-iniciales-pie {
                position: fixed;
                bottom: 0px;
                left: 0;
                right: 0;
                padding-left: 0cm;
                padding-right: 0cm;
                text-align: left;
                font-size: 10px;
                z-index: 10;
            }
        </style>
    </head>
   
    @php     
       $direccion_sede='';
        if($solicitud->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 60136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670 ZAMORA, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Zitácuaro') {
            $direccion_sede='5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO';
        } 
        if($solicitud->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO';
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
                            <td>{{ $solicitud->delegacion }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr> 
                    </table>
                </div><br><br><br>
                <div class="col-lg-12">
                    <p><center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
                          ACUSE DE SOLICITUD CONFIRMADA</b></center>
                    </p><br>
                    <p><b>FECHA DE LA SOLICITUD: {{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}<br>
                          FECHA DE CONFIRMACIÓN DE LA SOLICITUD:{{ \Carbon\Carbon::parse($solicitud->fecha_confirmacion)->translatedFormat('d \d\e F \d\e\l Y') }}</b></p>
                    <p><b> 
                        SOLICITANTE: {{ $solicitante->nombre }}<br><br>
                        @php
                            // Concatenamos todos los nombres para contar la longitud total
                            $texto_citados = "";
                            foreach($citados as $citado) {
                                $texto_citados .= $citado->nombre . ' ' . $citado->primer_apellido . ' ' . $citado->segundo_apellido . ', ';
                            }
                            $longitud_citados = strlen($texto_citados);
                        @endphp
                        CITADO (S): @foreach($citados as $citado)
                                        {{ $citado->nombre }} {{ $citado->primer_apellido}} {{ $citado->segundo_apellido}} <br>
                                    @endforeach
                    </b></p>
                    
                    <p><b>{{ $solicitante->nombre }}</b>, ha confirmado exitosamente la solicitud de conciliación con folio <b>{{ $solicitud->NUE }}</b>.<br><br>
                        En el documento NOTIFICACIÓN PARA LA CELEBRACIÓN DE LA AUDIENCIA DE CONCILIACIÓN se le señalará fecha y hora para la celebración de la audiencia de conciliación a la que deberá 
                        comparecer <b>presencialmente</b> en las instalaciones del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, ubicada en <b>{{$direccion_sede}}</b><br><br>
                        
                        <!--Atendiendo la fracción VII del artículo 689-E de la Ley Federal del Trabajo, las trabajadoras y los trabajadores, deberán acudir personalmente a la audiencia conciliatoria, sin 
                        impedimento de poderse acompañar de una persona de su confianza, pero no se reconocerá a ésta como apoderado, por tratarse de un procedimiento de conciliación y no de un juicio; no 
                        obstante, el trabajador también podrá ser asistido por un licenciado en derecho, abogado o un Procurador de la Defensa del Trabajo. <br><br>-->
                        @if($solicitud->tipo_solicitud == 1)
                            Atendiendo la fracción VII del artículo 689-E de la Ley Federal del Trabajo, las trabajadoras y los trabajadores, deberán acudir personalmente a la audiencia conciliatoria, sin 
                            impedimento de poderse acompañar de una persona de su confianza, pero no se reconocerá a ésta como apoderado, por tratarse de un procedimiento de conciliación y no de un juicio; no 
                            obstante, el trabajador también podrá ser asistido por un licenciado en derecho, abogado o un Procurador de la Defensa del Trabajo. <br><br>
                            
                            El patrón deberá asistir personalmente o por conducto de representante con facultades suficientes para obligarse en su nombre, atendiendo a los requisitos establecidos en el 
                            artículo 692 de la Ley Federal del Trabajo.
                            
                            <br><br>
                            En el caso de personas morales empleadoras, se deberá comparecer a través de un representante legal con facultades suficientes y apegándose al artículo señalado con anterioridad.<br><br>
                            @if($longitud_citados > 720)
                                <div class="page-break"></div> 
                                {{-- Esto forzará que lo que sigue (el texto legal) empiece en una hoja limpia --}}
                            @endif
                            De conformidad con la fracción X del artículo 684-E de la Ley Federal del Trabajo, si a la audiencia de conciliación, sólo comparece el citado, se archivará el expediente por falta 
                            de interés del solicitante, reanudándose los plazos de prescripción a partir de día siguiente a la fecha de la audiencia.
                        @else
                            Atendiendo la fracción VI del artículo 684-E de la Ley Federal del Trabajo, deberán acudir personalmente a la audiencia de conciliación, sin perjuicio de comparecer
                            acompañado por una persona de su confianza, pero no se reconocerá a ésta como apoderado por tratarse de un
                            procedimiento de conciliación y no de un juicio, o asistido por un licenciado en derecho, abogado o Procurador de la
                            Defensa del Trabajo. <br><br>
                        
                            Ahora bien, en el caso de personas morales empleadoras, deberán comparecer a través de un representante legal con
                            facultades suficientes para actuar en su representación y cumplir con los requisitos establecidos en el artículo 692 de la
                            Ley Federal del Trabajo; por lo que respecta a las personas físicas empleadoras podrán comparecer por su propio
                            derecho o a través de un representante legal con facultades suficientes para actuar en su representación y deberán
                            cumplir con los requisitos del artículo anteriormente señalado.<br><br>
                            Finalmente, en el caso de no comparecer a la audiencia respectiva, su solicitud se archivará por falta de interés de
                            conformidad con la fracción X del artículo 684-E de la Ley Federal del Trabajo, sin perjuicio de que continúen los plazos
                            de prescripción previstos en dicha Ley
                        @endif
                    </p>
                </div>
            </div>

            @if(!empty($etiquetaIniciales) && !empty($inicialesConcluye) && !empty($solicitud->fecha_confirmacion) && $solicitud->fecha_confirmacion > \Carbon\Carbon::parse('2026-06-03'))
                <div class="etiqueta-iniciales-pie">
                    <small><b>{{ $etiquetaIniciales }}</b></small><br>
                    <small>Elaboró: <b>{{ $inicialesConcluye }}</b></small>
                </div>
            @endif
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
</html>    