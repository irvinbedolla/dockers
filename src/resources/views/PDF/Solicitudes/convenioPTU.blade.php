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
            .sangria {
                margin-left: 20px;
                text-indent: -15px; 
                padding-left: 15px;
            }
        </style>
    </head>
    @php     
        $direccion_sede='';
        if($solicitud->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P.58260 MORELIA, MICHOACÁN DE OCAMPO.';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P.30136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P.59670 ZAMORA, MICHOACÁN DE OCAMPO.';
        }  
        if($solicitud->delegacion === 'Zitácuaro') {
            $direccion_sede='CUAUHTEMOC ORIENTE NO. 15, COL. CUAUHTEMOC, C.P. 61506ZITÁCUARO, MICHOACÁN DE OCAMPO.';
        } 
        if($solicitud->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P.60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO.';
        }  
        if($solicitud->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 300, COL. LOMAS DE UNIVERSIDAD, C.P.59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO.';
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
                            <td>{{ strtoupper($solicitud->delegacion) }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr> 
                    </table>
                </div><br><br><br>
                <p><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
                    SOLICITANTE: {{ $solicitante->nombre }}<br>
                </b></p>  
                <p><center><b>CONVENIO DE CONCILIACIÓN DE PAGO DE PARTICIPACIÓN DE UTILIDADES </b></center></p><br>
                <p>Con fundamento en los artículos 123, apartado A, fracciones XX párrafo segundo y  XXVII, inciso h)  de la Constitución Política de los Estados Unidos Mexicanos; 33, 98, 117, 
                    122, 130, 590-E fracción I, 684-E fracción VI, XIII y 684-F fracción VIII, IX de la Ley Federal del Trabajo, artículo 8 fracción I, III y artículo 27 de Ley Orgánica del 
                    Centro de Conciliación Laboral del Estado de Michoacán con domicilio en <b>{{$direccion_sede}}</b> se celebra el presente convenio por una parte 
                    <b>{{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}</b> quién en lo subsecuente se denominará la parte <b>“EMPLEADORA”</b> y, por otro el <b>C. 
                    {{ $solicitante->nombre }}</b>, a quién en lo subsecuente se le denominará la parte <b>“TRABAJADOR”</b>, a quienes en lo sucesivo de forma conjunta se les denominará las <b>“PARTES”</b>, quienes 
                    se someten y obligan en términos de las siguientes declaraciones y cláusulas:
                </p>

                <p><center><b>D E C L A R A C I O N E S:</b></center></p><br>

                <p><b>PRIMERA</b>. {{ $datosAudiencia->resolucion_primera }}.</p> 

                <p><b>SEGUNDA</b>. {{ $datosAudiencia->resolucion_segunda }}</p>.  

                <p><b>TERCERA</b>. Declara la parte <b>TRABAJADORA</b>:</p>
                    <p class="sangria">
                        a) Que fue contratada por la parte <b>EMPLEADORA</b> desde el <b>{{ \Carbon\Carbon::parse($solicitante->fecha_ingreso)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, para prestar sus 
                        servicios como <b>{{$solicitante->puesto}}</b>, puesto en el que se desempeñó 
                        hasta el día <b>{{ \Carbon\Carbon::parse($solicitante->fecha_salida)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, puesto que dio origen a la solicitud de <b>PAGO DE UTILIDADES</b> objeto de este convenio.
                    </p>
                    <p class="sangria">                
                        b) Que por el desempeño de sus labores contaba con todas las prestaciones, incluido el REPARTO DE UTILIDAD.
                    </p>
                    <p class="sangria"> <!-- REVISAR PORQUE INDICA QUE EL CITATORIO FUE ENTREGADO POR EL SOLICITANTE -->
                        c) Que con motivo del citatorio de fecha 19 de mayo del 2025 emitido por el Centro de Conciliación Laboral del Estado de Morelia, Michoacán, la parte <b>TRABAJADORA</b> fue notificada y comparece para desahogar la 
                        etapa de conciliación prejudicial conforme a los artículos 684-E de la Ley Federal del Trabajo.
                    </p>

                <p> <b>CUARTA.</b> Declara la parte <b>EMPLEADORA:</b> </p>
                    <p class="sangria">     
                        a) Que la parte <b>TRABAJADORA</b>, fue contratada para laborar como <b>{{$solicitante->puesto}}</b> en el domicilio ubicado en <b>LIBRAMIENTO SUR #2389, SAN MIGUEL CURAHUANGO, MARAVATIO MICHOACAN.</b>
                    </p> 
                    <p class="sangria"> 
                        b) <b>Que bajo protesta de decir verdad conformo la Comisión Mixta de Participación de Utilidades a qué se refiere el artículo 125 de la Ley Federal del Trabajo, a efecto de determinar la cantidad que por 
                        concepto de utilidades corresponden a los trabajadores de la EMPLEADORA.</b>
                    </p>
                    <p class="sangria">
                        c) Que presentó solicitud el día <b>19 DE MAYO DEL 2025</b> para iniciar el procedimiento de conciliación prejudicial ante el Centro de Conciliación Laboral del Estado de <b>Michoacán</b>, con objeto de 
                        PAGO DE PRESTACIONES, misma que confirmó ante el centro el día antes citado, otorgando como numero de solicitud <b>{{$solicitud->NUE}}</b>.
                    </p>
                    <p class="sangria">
                        d) Que el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, fijó la audiencia de conciliación para el día <b>03 DE JUNIO DEL 2025 A LAS 14:00 hrs</b>, no obstante, ello la misma 
                        fue suspendida por platicas conciliatorias, señalándose de nueva cuenta las <b>12:45 HORAS DEL DIA 11 DE JUNIO DEL 2025</b> para la continuación de la misma. 
                    </p> 

                <p><b>QUINTA</b>. Declaran las <b>PARTES</b>:  
                        <p class="sangria">
                            a)  Que el presente convenio se celebra con la finalidad de dar por cumplido el pago que por concepto de participación de utilidades corresponden a la <b>TRABAJADORA</b> de conformidad a lo determinado 
                            por la Comisión Nacional y Mixta para la Participación de los Trabajadores en las Utilidades de la Empresa</b>.
                        </p>
                        <p class="sangria">        
                            b) Por lo que el día <b>11 de junio del 2025</b>, celebran audiencia de conciliación y, que, por así convenir a sus intereses, la <b>TRABAJADORA</b> y <b>EMPLEADORA</b> han llegado a un acuerdo para ratificar 
                            lo convenido, al tenor de las siguientes:
                        </p>   
                    
                    <center><b>C L Á U S U L A S:</b></center>
                    
                    <p><br>
                        <b>PRIMERA</b>. Las <b>PARTES</b> por así convenir a sus intereses concluyeron la relación laboral por mutuo acuerdo, conforme a lo estipulado por el artículo 53, fracción I, de la Ley Federal del Trabajo 
                        en 24 de noviembre del 2023.<br> <br> <!-- revisar campo para fecha -->

                        <b>SEGUNDA</b>. La parte <b>TRABAJADORA</b> manifiesta bajo protesta de decir verdad, que el vínculo laboral lo mantuvo exclusivamente con la moral <b>GRUPO LEOMAR S.A. DE C.V.</b>, Por lo anterior, expresa que 
                        no existió relación laboral alguna con otras personas, incluido el personal que fungía como superior jerárquico en el centro de trabajo donde la <b>TRABAJADORA</b> desempeñaba sus labores.<br><br>
                                    
                        <b>TERCERA</b>. La <b>EMPLEADORA</b> otorgó en favor de la TRABAJADORA el pago acordado conforme a las disposiciones de la Ley Federal del Trabajo y respetando los derechos consagrados en el mismo 
                        ordenamiento legal. Asimismo, la <b>TRABAJADORA</b> manifiesta su entera conformidad y la aceptación del pago que en su momento recibió por concepto de finiquito, así como la cantidad mencionada en la 
                        cláusula <b>QUINTA por concepto de participación de utilidades le corresponden respecto al ejercicio fiscal 2023.</b><br><br>

                        <b>CUARTA.</b> La <b>TRABAJADORA</b> manifiesta que durante el tiempo que laboró para la parte <b>EMPLEADORA</b>, se cubrió en tiempo y forma el pago su salario; cada una de las prestaciones ordinarias y 
                        extraordinarias y en especie que conforme a derecho le corresponden, así mismo como cualquier riesgo o accidente de trabajo que haya sufrido. Por lo anterior, la parte <b>EMPLEADORA</b> no adeuda pago de concepto alguno.<br><br>

                        <b>QUINTA.</b> La <b>TRABAJADORA</b> recibirá por parte de la <b>EMPLEADORA</b> la cantidad de <b>${{ number_format($datosAudiencia->monto, 2) }} {{ $montoTexto }}</b> conforme a los siguientes conceptos: 
                        <b>Participación de Reparto de utilidades respecto de ejercicio fiscal 2023.</b><br><br>

                        <b>SEXTA.</b> La <b>EMPLEADORA</b> manifiesta que en este acto, le pagará a la <b>TRABAJADORA en una sola exhibición la cantidad ${{ number_format($datosAudiencia->monto, 2) }} {{ $montoTexto }}</b> en el domicilio que ocupa el 
                        Centro de Conciliación Laboral del Estado de Michoacán, ubicado en <b>{{$direccion_sede}}</b>, con lo que se certifique el cumplimiento de su obligación de conformidad con lo establecido en el artículo 684-E, fracción 
                        XIV, de la Ley Federal del Trabajo, el día <b>@foreach($pagos as $pago)
                        {{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d \d\e F \d\e\l Y') }} a las {{ \Carbon\Carbon::parse(str_replace(' HORAS', '', $pago->hora))->format('H:i') }} @endforeach</b>, en el recinto que 
                        ocupa este Centro de Conciliación Laboral, asimismo  manifiestan además que de conformidad con lo establecido 
                        en el artículo 684-E fracción XIV cuarto párrafo señalan como pena convencional en caso de incumplimiento en mora de dicho convenio, la cantidad de <b>$800.00 pesos</b>.<br><br>

                        <b>SÉPTIMA.</b> Las <b>PARTES</b> solicitan se apruebe y sancione este convenio, toda vez que se elaboró conforme a las disposiciones aplicables de la Ley Federal del Trabajo como resultado del diálogo de la conciliación 
                        entre la <b>TRABAJADORA</b> y la <b>EMPLEADORA</b>. Asimismo, manifiestan que se encuentran conformes con el presente acuerdo por no contener cláusula contraria a la costumbre, a la moral, ni renuncia a los derechos de las <b>PARTES</b>.<br><br>
                        
                        <b>OCTAVA.</b> Las <b>PARTES</b> manifiestan que es su voluntad ratificar el presente convenio en todas y cada una de sus partes y la aprobación de su contenido, por lo que no se reservan acción legal o derecho alguno para 
                        ejercitar con posterioridad a la firma del presente convenio.<br><br>

                        <b>NOVENA.</b> Las <b>PARTES</b> solicitan ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo que les sean expedidas las copias autorizadas del convenio, y en el momento que se haya cumplido totalmente, 
                        se les expida acta en la que conste el cumplimiento de éste, en términos del artículo 684-E, fracción XIV, primer párrafo, de la Ley Federal del Trabajo.<br><br>

                        <b>DÉCIMA.</b> Las <b>PARTES</b> manifiestan que, en la celebración del presente convenio, no existió violencia, mala fe, dolo, lesión o cualquier otro tipo de vicio del consentimiento que pudiera nulificarlo.<br><br>

                        <b>DÉCIMA PRIMERA.</b> En caso de que no se cumpla los términos de lo convenido en el presente instrumento, las <b>PARTES</b> deberán acudir a los Tribunales Laborales a efecto de que se realice el procedimiento de ejecución 
                        que la Ley Federal del Trabajo contempla.<br><br>
                        
                        Enteradas las <b>PARTES</b> del alcance legal del presente convenio que se eleva a cosa juzgada, conforme al artículo 684-E fracción XIII, mismo que se firma en el CENTRO DE CONCILIACION LABORAL DEL ESTADO DE MICHOACÁN, 
                        DELEGACIÓN Morelia ubicado en <b>{{$direccion_sede}}</b>, ante la fe del/la <b>LIC. {{ strtoupper($conciliador->name) }}</b>, funcionario/a conciliadoro/a, quien lo sanciona en este mismo acto. <b>Doy fe.</b>
                    </p>
                    <br><br>
                    <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:30px;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:0 20px;">
                            <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                            <b>
                                {{ $solicitante->nombre }}<br>
                                LA PARTE TRABAJADORA
                            </b>
                            </td>
                            <td style="width:50%; vertical-align:top; padding:0 20px;">
                            <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                            <b>
                                {{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}<br>
                                LA PARTE EMPLEADORA
                            </b>
                            </td>
                        </tr>
                    </table>
                    <br><br>
                    <p><center><b>___________________________________<br> {{ strtoupper($conciliador->name) }} <br> FUNCIONARIO/A CONCILIADOR/A<br>
                        DEL CENTRO DE CONCILIACIÓN LABORAL DEL<br>ESTADO DE MICHOACÁN DE OCAMPO</b></p></center>               
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