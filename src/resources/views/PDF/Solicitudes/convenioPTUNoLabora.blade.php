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
        </style>
    </head>
    @php     
        $direccion_sede='';
        if($solicitud->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO.';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 60136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670 ZAMORA, MICHOACÁN DE OCAMPO.';
        }  
        if($solicitud->delegacion === 'Zitácuaro') {
            $direccion_sede='5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO.';
        } 
        if($solicitud->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO.';
        }  
        if($solicitud->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 300, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO.';
        } 
    @endphp
    @php     
        $nombremiento_delegado='';
        if($solicitud->delegacion === 'Morelia' || $solicitud->delegacion === 'Zitácuaro') {
            $nombremiento_delegado='DIRECTOR DE LA DELEGACIÓN REGIONAL DE MORELIA ';
        }    
        if($solicitud->delegacion === 'Uruapan' || $solicitud->delegacion ==='Lázaro Cárdenas') {
            $nombremiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE URUAPAN';
        }
        if($solicitud->delegacion === 'Zamora' || $solicitud->delegacion ==='Sahuayo') {
            $nombremiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE ZAMORA';
        }  
    @endphp
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer></footer>
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
                </div><br><br><br><br>
                <center><p><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO <br><br>
                CONVENIO DE CONCILIACIÓN DE PAGO DE PARTICIPACIÓN DE UTILIDADES </b></p></center>

                <p>Con fundamento en los artículos 123, apartado A, fracciones XX párrafo segundo y XXVII, inciso h) de la Constitución Política de los Estados Unidos Mexicanos; 33, 98, 117, 
                    122, 130, 590-E fracción I, 684-E fracción VI, XIII y 684-F fracción VIII, IX de la Ley Federal del Trabajo, artículo 8 fracción I, III y artículo 27 de Ley Orgánica del 
                    Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, se celebra el presente convenio por una parte 
                    <b>{{ $solicitante->nombre }}</b> quién en lo subsecuente se denominará la parte <b>“TRABAJADORA”</b> y, por otro <b> 
                    @if(is_null($abogado->primer_apellido_patronal) && is_null($abogado->segundo_apellido_patronal))
                           {{ $abogado->nombres_patronal }}
                       @else {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }} @endif representado(a) por {{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }} 
                       en carácter de apoderado legal</b> a quién en lo subsecuente se le denominará la parte <b>"EMPLEADORA",</b> a quienes en lo sucesivo de forma conjunta se les denominará
                    las <b>"PARTES"</b> quienes se someten y obligan en términos de las siguientes declaraciones y cláusulas: 
                </p>

                <p><center><b>D E C L A R A C I O N E S:</b></center></p><br>

                <p><b>PRIMERA</b>. La parte <b>TRABAJADORA</b> se idéntica con <b>{{ mb_strtoupper($solicitante->identificacion, 'UTF-8') }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b> y declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.</p> 

                <p><b>SEGUNDA</b>. Declara <b>{{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }}</b> quien se identifica con <b>{{ mb_strtoupper($abogado->tipo_identificacion, 'UTF-8') }}</b>, que es apoderado legal de 
                    <b>@if(is_null($abogado->primer_apellido_patronal) && is_null($abogado->segundo_apellido_patronal))
                           {{ $abogado->nombres_patronal }}
                       @else {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }} @endif</b> y que cuenta con facultades suficientes para 
                    convenir a nombre de su representada en términos de <b>{{ $abogado->tipo_documento_representante }}</b>, poder que a la fecha de este convenio no le ha sido revocado.</p> 

                <p><b>TERCERA</b>. Declara la parte <b>TRABAJADORA</b>:</p>
                    <p class="sangria">
                        a) Que fue contratada por la parte <b>EMPLEADORA</b> desde el <b>{{ \Carbon\Carbon::parse($solicitante->fecha_ingreso)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, para prestar sus servicios como <b>{{ $solicitante->puesto }}</b>, puesto en el que se desempeñó hasta el día <b>{{ \Carbon\Carbon::parse($solicitante->fecha_salida)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                    </p>
                    <p class="sangria">                
                        b) Que por el desempeño de sus labores contaba con las siguientes prestaciones:<br>
                            - Salario mensual: <b>${{ number_format($salario_mensual, 2) }} {{ $mensualTexto }}</b>. <br>
                            - Vacaciones: <b>{{ $datosAudiencia->vacaciones}}</b> días al año.<br>
                            - Aguinaldo: <b>{{ $datosAudiencia->aguinaldo }}</b> días al año.<br>
                            - Otras prestaciones (bonos, vales de despensa, seguros de gastos médicos mayores etc): <b>{{ $datosAudiencia->otros }}</b>
                    </p>
                    <p class="sangria">
                        c) Que desempeñaba sus actividades laborales en las siguientes condiciones:<br>
                            - Horario: <b>{{ $datosAudiencia->horario}}</b><br>
                            - Horario de comida: <b>{{ $datosAudiencia->comida}}</b><br>
                            - Domicilio donde prestaba sus servicios: <b>{{ $abogado->tipo_vialidad_patronal}} {{ $abogado->vialidad_patronal }} {{ $abogado->num_ext_patronal }} @if(!empty($abogado->mun_int_patronal))
                                    INT. {{ $abogado->mun_int_patronal }}
                                @endif COLONIA {{ $abogado->colonia_patronal}}, {{ mb_strtoupper($municipioEmpresa, 'UTF-8')}}, {{ mb_strtoupper($estadoEmpresa, 'UTF-8')}} C.P. {{ $abogado->cp_patronal }}.</b>
                    </p>
                    <p class="sangria">
                        d) Que por el desempeño de sus labores contaba con todas las prestaciones, incluido la <b>Participación de los Trabajadores en las Utilidades</b> de la empresa <b>PTU</b>.
                    </p>
                    <p class="sangria">
                        e) Que el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> presentó solicitud para iniciar el procedimiento de conciliación prejudicial ante el Centro de Conciliación Laboral del 
                        Estado de Michoacán de Ocampo, por motivo de <b>Pago de prestaciones</b>, consistente en el <b>reparto de utilidades correspondientes al ejercicio fiscal 2024</b>, 
                        misma que confirmó ante el Centro el <b>{{ \Carbon\Carbon::parse($solicitud->fecha_confirmacion)->translatedFormat('d \d\e F \d\e\l Y') }}.</b>
                    </p>
                    <p class="sangria">
                        f) Que el Centro Estatal, fijó la audiencia de conciliación para el día <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}.</b>
                    </p>

                <p> <b>CUARTA.</b> Declara la parte <b>EMPLEADORA:</b> </p>
                    <p class="sangria">     
                        a) Que la parte <b>TRABAJADORA</b>, fue contratada en los términos señalados en la declaración inmediata anterior.
                    </p> 
                    <p class="sangria"> 
                        b) Que bajo protesta de decir verdad conformó la Comisión Mixta de Participación de Utilidades a qué se refiere el artículo 125 de 
                        la Ley Federal del Trabajo, a efecto de determinar la cantidad que por concepto de utilidades corresponden a los trabajadores de la <b>EMPLEADORA.</b>
                    </p>
                    <p class="sangria">
                        c) Que con motivo del citatorio de fecha <b> FECHA DE NOTIFICACION</b> emitido por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, la parte <b>EMPLEADORA</b> 
                        comparece para desahogar la etapa de conciliación prejudicial conforme al Artículo 684-E de la Ley Federal del Trabajo.
                    </p>

                    <p><b>QUINTA</b>. Declaran las <b>PARTES</b>:  
                        <p class="sangria">
                            a) Que el presente convenio se celebra con la finalidad de dar por terminado el procedimiento de conciliación prejudicial, seguido ante el Centro de Conciliación 
                            Laboral del Estado de Michoacán de Ocampo, bajo el número de identificación único <b>{{ $solicitud->NUE }}</b>.
                        </p>
                        <p class="sangria">        
                           b) Que el día <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, se celebró la audiencia de conciliación y que, por así convenir a sus intereses, la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b> han llegado a un 
                           acuerdo para dirimir el conflicto suscitado, al tenor de las siguientes.
                        </p>   
                    
                        <center><b>C L Á U S U L A S:</b></center><br>
                    </p>
                    <p>
                        <b>PRIMERA</b>. Las <b>PARTES</b> por así convenir a sus intereses dieron por concluida la relación laboral por mutuo acuerdo, conforme a lo estipulado por el artículo 53, fracción I, de 
                        la Ley Federal del Trabajo, en fecha <b>{{ \Carbon\Carbon::parse($solicitante->fecha_salida)->translatedFormat('d \d\e F \d\e\l Y') }};</b> asimismo han determinado que el presente convenio se celebra con la finalidad de dar por 
                        cumplido el pago que por concepto de participación de utilidades corresponden al <b>TRABAJADOR</b> de conformidad a lo determinado por la Comisión Nacional y Mixta para la Participación de 
                        los Trabajadores en las Utilidades de la Empresa, conforme a las disposiciones de la Ley Federal del Trabajo y respetando los derechos consagrados en el mismo ordenamiento legal.<br><br>

                        <b>SEGUNDA</b>. La parte <b>TRABAJADORA</b> manifiesta bajo protesta de decir verdad, que el vínculo laboral lo mantuvo exclusivamente con la parte <b>EMPLEADORA</b>. Por lo anterior, expresa 
                        que no existió relación laboral alguna con otras personas, incluido el personal que fungía como superior jerárquico en el centro de trabajo donde la parte <b>TRABAJADORA</b> desempeñaba sus labores.<br><br>
                                    
                        <b>TERCERA</b>. La <b>EMPLEADORA</b> otorgó en favor del <b>TRABAJADOR</b> el pago acordado conforme a las disposiciones de la Ley Federal del Trabajo y respetando los derechos consagrados en el mismo 
                        ordenamiento legal. Asimismo, el <b>TRABAJADOR</b> manifiesta su entera conformidad y la aceptación del pago que en su momento recibió por concepto de liquidación, así como la cantidad mencionada en la 
                        cláusula QUINTA por concepto de participación de utilidades le corresponden respecto al ejercicio fiscal 2024.<br><br>

                        <b>CUARTA.</b> La parte <b>TRABAJADORA</b> manifiesta que durante el tiempo que laboró para la parte <b>EMPLEADORA</b>, se cubrió en tiempo y forma el pago su salario; cada una de las prestaciones ordinarias y 
                        extraordinarias y en especie que conforme a derecho le corresponden, así mismo como cualquier riesgo o accidente de trabajo que haya sufrido. Por lo anterior, la parte <b>EMPLEADORA</b> no adeuda pago de concepto alguno.<br><br>

                        <b>QUINTA.</b> El <b>TRABAJADOR</b> recibirá por parte de la <b>EMPLEADORA</b> la cantidad de <b>${{ number_format($datosAudiencia->monto, 2) }} {{ $montoTexto }}</b>, conforme a los siguientes conceptos: 
                        
                        <b>Prestaciones</b>
                        <table class="table table-bordered table-compacta">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Monto en letra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--<p class="sangria">-->
                                @foreach($prestaciones as $concepto)
                                    <tr>
                                        <td>{{ mb_strtoupper($concepto->descripcion, 'UTF-8') }}</td>
                                        <td><b>${{ number_format($concepto->monto, 2) }}</b></td>
                                        <td>{{ $conceptosTexto[$concepto->id] }}</td>
                                    </tr>
                                @endforeach
                                <!--</p>-->
                            </tbody>
                        </table>      

                        <!-- Para las deducciones -->
                        @if(!empty($deducciones) && count($deducciones) > 0)
                            <b>Deducciones</b>
                            <table class="table table-bordered table-compacta">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Monto en letra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deducciones as $deduccion)
                                        <tr>
                                            <td>{{ $deduccion->descripcion }}</td>
                                            <td><b>${{ number_format($deduccion->monto, 2) }}</b></td>
                                            <td>{{ $deduccionesTexto[$deduccion->id] }}</td>
                                        </tr>
                                    @endforeach  
                                </tbody>
                            </table> 
                        @endif

                        <table class="table table-bordered table-compacta" style="width:100%; float: right;">
                            <thead>
                            <tr>
                                <td class="text-right"><strong>Total de percepciones: </strong>
                                <td><strong>${{ number_format($pagoTotal, 2) }} M.N.</strong></td>
                            </tr>
                            </thead>   
                        </table>

                        <p>La cantidad antes desglosada cubre lo correspondiente al pago de la Participación de los Trabajadores en las Utilidades de la empresa PTU del ejercicio fiscal 2024, conforme a lo manifestado bajo protesta de decir verdad 
                        por la EMPLEADORA, motivo por el cual se ratifica el acuerdo conciliatorio al que llegaron las partes, al encontrarse apegado a derecho.</p>

                        <!-- CON PAGOS DIFERIDOS-->       
                        @if($pagosDif->C_pagos>'1')            
                            <p><b>SEXTA</b>. La <b>EMPLEADORA</b> manifiesta que pagará en <b>{{ $pagosDif->C_pagos}}</b> exhibiciones, hasta culminar la cantidad de 
                                <b>${{ number_format($datosAudiencia->monto, 2) }} {{ $montoTexto }}</b>, tal como se muestra:
                            </p>
                            <div class="table-responsive">
                                <table id="pagos" class="table-striped" style="width:100%;">
                                    <thead>
                                        <th style="display: none;">ID</th>
                                        <th>Exhibiciones</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Monto</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pagos as $pago)
                                            <tr>
                                                <td style="display: none;">{{$pago->id_solicitud}}</td>
                                                <td>{{$pago->descripcion}}</td>
                                                <td>{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}</td> 
                                                <td>{{ \Carbon\Carbon::parse(str_replace(' HORAS', '', $pago->hora))->format('H:i') }} HRS</td>
                                                <td>${{ number_format($pago->monto, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>      
                            </div>

                        <!-- CONDICIONAL 1 SOLO PAGO(EN UNA SOLA EXIBICIÓN)--> 
                        @elseif($pagosDif->C_pagos=='1')            
                        <p><b>SEXTA</b>. La <b>EMPLEADORA</b> manifiesta que pagará en <b>{{ $pagosDif->C_pagos}}</b> exhibición, la cantidad de 
                            <b>${{ number_format($solicitud->monto, 2) }} {{ $montoTexto }} M.N</b>, tal como se muestra:
                        </p>
                        <div class="table-responsive">
                            <table id="pagos" class="table-striped" style="width:100%;">
                                <thead>
                                    <th style="display: none;">ID</th>
                                    <th>Exhibición</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Monto</th>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                        <tr>
                                            <td style="display: none;">{{$pago->id_solicitud}}</td>
                                            <td>{{$pago->descripcion}}</td>
                                            <td>{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}</td> 
                                            <td>{{ \Carbon\Carbon::parse(str_replace(' HORAS', '', $pago->hora))->format('H:i') }} HRS</td>
                                            <td>${{ number_format($pago->monto, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>      
                        </div>
                        @endif <br><br>
                        En caso de que la parte <b>EMPLEADORA</b> no cubra el pago de la cantidad estipulada y dentro del plazo determinado en esta cláusula, deberá pagar a la parte <b>TRABAJADORA</b> el equivalente a un día de salario diario, el cual se 
                        fijará en razón del salario que percibía dicha parte antes de finalizar la relación de trabajo. Esa cantidad se sumará a la previamente pactada, por cada día que transcurra, sin que se dé cabal cumplimiento al convenio, con 
                        fundamento en el artículo 684-E, fracción XIV, último párrafo, de la Ley Federal del Trabajo.<br><br>

                        <b>SÉPTIMA.</b> Las <b>PARTES</b> solicitan se apruebe y sancione este convenio única y exclusivamente por lo que ve a la Participación de los Trabajadores en las Utilidades de la empresa PTU del ejercicio fiscal 2024, 
                        toda vez que se elaboró conforme a las disposiciones aplicables de la Ley Federal del Trabajo como resultado del diálogo de la conciliación entre la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b>. Asimismo, 
                        manifiestan que se encuentran conformes con el presente acuerdo por no contener cláusula contraria a la costumbre, a la moral, ni renuncia a los derechos de las <b>PARTES</b>. <br><br>
                        
                        <b>OCTAVA.</b> Las <b>PARTES</b> manifiestan que es su voluntad ratificar el presente convenio en todas y cada una de sus partes y la aprobación de su contenido, por lo que no se reservan acción legal o derecho alguno para 
                        ejercitar con posterioridad a la firma del presente convenio por lo que ve a la Participación de los Trabajadores en las Utilidades de la empresa citada (PTU) del ejercicio fiscal 2024.<br><br>

                        <b>NOVENA.</b> Las <b>PARTES</b> solicitan ante el Centro Estatal de Conciliación Laboral que les sean expedidas las copias autorizadas del convenio, y en el momento en que se haya cumplido totalmente, se les expida acta 
                        en la que conste el cumplimiento de éste, en términos del artículo 684-E, fracción XIV, primer párrafo, de la Ley Federal del Trabajo.<br><br>

                        <b>DÉCIMA.</b> Las <b>PARTES</b> manifiestan que en la celebración del presente convenio no existió violencia, mala fe, dolo, lesión o cualquier otro tipo de vicio del consentimiento que pudiera nulificarlo.<br><br>

                        <b>DÉCIMA PRIMERA.</b> En caso de que no se cumplan los términos de lo convenido en el presente instrumento, las <b>PARTES</b> deberán acudir a los juzgados Laborales del fuero común a efecto de que se realice el procedimiento 
                        de ejecución que la Ley Federal del Trabajo contempla.<br><br>
                    </p>
                    <div class="salto-inteligente"></div>
                    <div class="contenedor-firmas">
                        <p>
                            Enteradas las <b>PARTES</b> del alcance legal del presente convenio que se eleva a cosa juzgada, conforme al artículo 684- E fracción XIII, mismo que se firma en <b>Michoacán de Ocampo a {{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, 
                            ante la fe de <b>{{$conciliador->name}}</b>, funcionario(a) conciliador(a), quien lo sanciona en este mismo acto. <b>Doy fe.</b>
                        </p>
                        <br><br><br>
                        <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:5px;">
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b> {{ $solicitante->nombre }}<br>
                                        LA PARTE TRABAJADORA
                                    </b>
                                </td>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }}<br>
                                    {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }}<br>
                                        LA PARTE EMPLEADORA
                                    </b>
                                </td>
                            </tr>
                            <br><br>
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><p><b>Doy fe</b></p><br><br>
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                            FUNCIONARIO/A CONCILIADOR/A<br>
                                            DEL CENTRO DE CONCILIACIÓN LABORAL
                                            DEL ESTADO DE MICHOACÁN DE OCAMPO
                                    </b>
                                </td>
                                <!--
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><p><b>Vo.Bo.</b></p><br><br>
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($delegado->name, 'UTF-8') }}<br>
                                        {{$nombremiento_delegado}}       
                                    </b>
                                </td>
                                -->
                            </tr>
                        </table> <br>
                        <p style="font-size: 10px;">
                            LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DEL CONVENIO DE CONCILIACIÓN DE FECHA <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
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
</html>    