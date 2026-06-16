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
        </style>
    </head>
    @php     
        $direccion_sede='';
        if($solicitud->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P.58260 MORELIA, MICHOACÁN DE OCAMPO';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P.30136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P.59670 ZAMORA, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Zitácuaro') {
            $direccion_sede='CUAUHTEMOC ORIENTE NO. 15, COL. CUAUHTEMOC, C.P. 61506ZITÁCUARO, MICHOACÁN DE OCAMPO';
        } 
        if($solicitud->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P.60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P.59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO';
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
                </div><br><br><br><br><br>
                <p><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
                        Asunto: Archivo de asunto por falta de interés<br>
                        Solicitante: {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }} <br> 
                </b></p>
                <p>
                    <center><b>CONSTANCIA DE NO CONCILIACIÓN</b></center>
                </p><br>  
                <p>En <b>{{ $direccion_sede }} a [FECHA_ACTUAL],</b></p>
                <p>
                    <b>VISTO</b> el estado que guarda el expediente identificado con el número <b>{{ $solicitud->NUE }}</b> relativo a la solicitud de conciliación realizada por
                    <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b>, por falta de interés se formula resolución en atención a los siguientes:
                </p>
                <p>
                    <center><b>RESULTANDOS</b></center>
                </p><br>
                <p>
                    <b>Primero.</b> El <b>[SOLICITUD_FECHA_RECEPCION]</b>, <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> solicitó ante este Centro, 
                    iniciar con el Procedimiento de Conciliación Prejudicial con el(los) citados <b>[SOLICITUD_NOMBRES_CITADOS]</b> por objeto de <b>{{ $solicitud->motivo }}</b>.<br><br>

                    <b>Segundo.</b> El <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, el Centro de Conciliación <b>{{ $solicitud->delegacion }}</b> admitió la 
                    solicitud de Conciliación, señalando que la celebración de la Audiencia de Conciliación se realizaría el <b>[AUDIENCIA_FECHA_AUDIENCIA]</b> a las <b>[AUDIENCIA_HORA_INICIO]</b> 
                    horas en la sala de audiencia <b>SALA VIRTUAL</b>, en las instalaciones de este Centro.<br><br>

                    <b>Tercero.</b> El <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, se concluyó la notificación personal de él(los) citado(s).<br><br>

                    <b>Cuarto.</b> El día de la audiencia, <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> no se presentó en ningún momento 
                              durante el tiempo que se tenía programado para la audiencia.<br>
                                
                    <br>En esas condiciones, este Centro expone los siguientes: 
                </p>
                <p>
                    <center><b>CONSIDERANDOS</b></center>
                </p><br>

                <p>
                    <b>I.</b> Esta Autoridad es competente para conocer del presente asunto en términos de lo dispuesto por los artículos 123, apartado A, fracción XX, 
                    párrafos tercero y cuarto de la Constitución Política de los Estados Unidos Mexicanos; artículos 590-E, 590-F, 684-B y 684-D, y 684-E de la Ley Federal 
                    de Trabajo; artículos 5 y 27 de la Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo; y artículos 17 y 20 del Reglamento 
                    Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo.<br>

                    Y toda vez que la solicitud de Ratificación de Convenio presentada y admitida de conformidad con lo establecido por los artículos 33 párrafo segundo, 
                    684-C y 684-E de la Ley Federal del Trabajo. Señalándose <b>[FECHA Y HORA]</b> para la Audiencia de Ratificación de Convenio, se notificó a la parte 
                    solicitante <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b>, sin embargo, no acudió, no 
                    obrando una causa justificada de la incomparecencia. <br><br> 
                    Por lo anteriormente expuesto, se:
                </p>       
                <p>
                    <center><b>RESUELVE</b></center>
                </p><br>
                <p>
                    <b>Primero.</b> Se archiva el expediente <b>{{ $solicitud->NUE }}</b> que consta desde el <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, 
                    en este Centro, por falta de interés del Solicitante.<br><br>

                    <b>Segundo.</b> Se le informa que el plazo de prescripción se reanuda a partir del día siguiente en que fue programada la audiencia, de conformidad con el artículo 684-E, 
                    fracción X de la Ley Federal del Trabajo.<br><br>

                    <b>Tercero.</b> Conforme al artículo 521 fracción III, de la Ley Federal del Trabajo, se dejan a salvo los derechos del trabajador para solicitar nuevamente la conciliación 
                    y con ello interrumpir nuevamente la prescripción.<br><br>

                    <b>Cuarto.</b> La interrupción de la prescripción cesa al día siguiente en que se emite esta Resolución, de conformidad con el artículo 521, fracción III de la Ley Federal 
                    del Trabajo.
                </p>

                <br><br><br><br>  
                <center><br><br> <p><b>___________________________________<br>{{ $conciliador->name }} <br> FUNCIONARIO/A CONCILIADOR/A</b></p></center>     
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
</html>    