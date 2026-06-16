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
                padding: 3cm 2cm 3cm 2cm;
                position: relative;
                /*padding: 4cm 2cm 3cm 2cm; /* Deja espacio para encabezado y pie  padding: 100px 50px;*/
                z-index: 1;
            }
            p {
                line-height: 1.5;
                text-align: justify;
            }

            /* Pie fijo: código de delegación + elaboró */
            .etiqueta-iniciales-pie {
                position: fixed;
                bottom: 60px;
                left: 2cm;
                right: 2cm;
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
                    <p><center><b>
                        CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO
                        </b></center></p><br>
                    <p><b>
                        ASUNTO: NOTIFICACIÓN PARA LA CELEBRACIÓN DE LA AUDIENCIA DE CONCILIACIÓN<br>
                        SOLICITANTE: {{ $solicitante->nombre }}<br>
                        CITADO (S): @foreach($citados as $citado)
                                        {{ $citado->nombre }} {{ $citado->primer_apellido}} {{ $citado->segundo_apellido}} <br>
                                    @endforeach
                        <br>
                        FECHA DE EMISIÓN DE DOCUMENTOS: {{ \Carbon\Carbon::parse($audiencia->created_at)->translatedFormat('d \d\e F \d\e\l Y') }}.<br>
                    </b></p>

                    <p> Con fecha <b>{{ \Carbon\Carbon::parse($audiencia->created_at)->translatedFormat('d \d\e F \d\e\l Y') }}</b> siendo las <b>{{ \Carbon\Carbon::parse($audiencia->created_at)->translatedFormat('H:i') }}</b> horas, ante esta 
                        Autoridad Conciliadora, <b>{{ $solicitante->nombre }}</b>, me doy por notificado(a) personalmente de la fecha para la celebración de la Audiencia de Conciliación derivada de 
                        la solicitud con número de identificación único <b>{{ $solicitud->NUE }}</b>, misma que tendrá verificativo el día 
                        <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> a las <b>{{ $audiencia->hora }}</b> horas, en la <b>{{ $audiencia->sala }}</b> de la Delegación 
                        Regional de {{ $solicitud->delegacion }} del Centro de Conciliación Laboral 
                        del Estado de Michoacán de Ocampo, con domicilio en <b>{{$direccion_sede}}</b>.             
                    </p>

                    @if ($solicitud->tipo_solicitud == 2)
                    <p>
                        En términos del artículo 684 E, fracciones VII y VIII, primer párrafo, de la Ley Federal del Trabajo, el empleador podrá comparecer de manera presencial, en cuyo caso deberá identificarse con cualquier documento oficial; o bien, 
                        a través de un representante, siempre que este cuente con facultades suficientes para obligarlo y lo acredite ante esta instancia.
                    </p>         
                    @endif
                    <p>
                        Se le exhorta a presentarse con al menos 15 minutos de anticipación a la hora señalada, a efecto de llevar el registro correspondiente de ingreso de este Centro de Conciliación y dar inicio de manera puntual a la audiencia prejudicial.
                    </p>  
                    <p>
                        Asimismo, de conformidad con la fracción X del artículo 684-E, me hago conocedor que <b>de no comparecer se archivará el presente asunto por falta de interés</b>.
                    </p>
                    <br><br><br><br><br><br><br>
                    <div class="row">
                        <div class="col-12 text-center">
                            
                            <div style="display: inline-block; margin-right: 50px;">
                                <p><center><b>___________________________________<br> {{ $solicitante->nombre }} <br> SOLICITANTE</b></center></p>
                            </div>
                        
                            <div style="display: inline-block; margin-right: 50px;">
                                <p><center><b>___________________________________<br> {{ $conciliador->name }} <br> FUNCIONARIO/A CONCILIADOR/A</b></center> </p>
                            </div>
                        </div>
                    </div>
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