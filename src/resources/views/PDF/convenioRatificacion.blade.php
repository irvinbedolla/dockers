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

        <style>
            .etiqueta-iniciales-pie {
                position: fixed;
                bottom: 60px;
                left: 3cm;
                right: 2cm;
                text-align: left;
                font-size: 12px;
            }
        </style>
    </head>
    @php
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
                            <td>{{  mb_strtoupper($solicitud->delegacion, 'UTF-8') }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr>   
                    </table>
                </div><br><br><br>
                <p><center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</b></center><br>
                   <!-- SOLICITUD RATIFICACIÓN DE CONVENIO TERMINACIÓN VOLUNTARIA <br><br>
                    SOLICITANTES:<br>
                    {{--{{ $solicitud->empresa }}<br>--}}-->
                </p>  
                <p><center><b>CONVENIO DE CONCILIACIÓN</b></center></p><br>
                <p>Con fundamento en los artículos 123, apartado A, fracción XXVII, inciso h) párrafo segundo, de la Constitución Política de los Estados Unidos Mexicanos; 
                    artículos 33, 53 fracción I y 684-E de la Ley Federal del Trabajo, artículo 27 de la Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, 
                    y artículo 20, fracción V y X del Reglamento Interior del Centro de Conciliación Laboral de Michoacán de Ocampo, 
                    se celebra el presente convenio por una parte <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> quién en lo 
                    subsecuente se denominará la parte <b>“TRABAJADORA”</b> y, por otro <b>{{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}</b> 
                    a quién en lo subsecuente se le denominará la parte <b>“EMPLEADORA”</b>, 
                    a quienes en lo sucesivo de forma conjunta se les denominará las <b>“PARTES”</b>, quienes se someten y obligan en términos de las siguientes declaraciones y cláusulas:
                </p>

                <p><center><b>D E C L A R A C I O N E S:</b></center></p><br>

                <p><b>PRIMERA.</b> La parte <b>TRABAJADORA {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> se identifica con <b>{{ strtoupper($solicitud->tipo_identificacion) }}</b>, de Número <b>{{ $solicitud->num_identificacion }}</b> 
                    expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b> y declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.</p> 

                <p><b>SEGUNDA.</b> 
                    @if(is_null($abogado->nombre_representante) && is_null($abogado->primer_apellido_representante) && is_null($abogado->segundo_apellido_representante))
                        La parte EMPLEADORA <b>{{$abogado->nombres_patronal}} {{$abogado->primer_apellido_patronal}} {{$abogado->segundo_apellido_patronal}}</b> se identifica con 
                        <b>{{ strtoupper($abogado->tipo_identificacion) }}</b>, de Número <b>{{ $abogado->num_identificacion }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionP }}</b>, 
                        y declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.
                    @else Declara <b>{{$abogado->nombre_representante}} {{$abogado->primer_apellido_representante}} {{$abogado->segundo_apellido_representante}}</b>, <b>ser representante legal de la PARTE EMPLEADORA</b>, quien se identifica con 
                        <b>{{ strtoupper($abogado->tipo_identificacion) }}</b>, de Número <b>{{ $abogado->num_identificacion }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionP }}</b>, así como <b>{{$abogado->descipcion_poder}}</b>  
                    @endif
                </p>
                <b>TERCERA.</b> Declara la parte <b>TRABAJADORA</b>:
                    <p class="sangria">
                        a) Que fue contratada por la parte <b>EMPLEADORA</b> desde el <b>{{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, para prestar sus 
                        servicios como <b>{{ $solicitud->categoria}}</b>, @if($solicitud->fecha_termino) puesto en el que se desempeñó 
                        hasta el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha_termino)->translatedFormat('d \d\e F \d\e\l Y') }}</b> @else puesto que desempeña hasta la fecha de emisión del presente convenio @endif.
                    </p>
                    <p class="sangria">                
                        b) Que por el desempeño de sus labores contaba con las siguientes prestaciones:<br>
                            - Salario mensual: <b>${{ number_format($salario_mensual, 2) }} {{ $mensualTexto }}</b> <br>
                            - Días de descanso: <b>{{ $dias_descanso }}</b><br>
                           @if($solicitud->vacaciones_dias > 0 && !is_null($solicitud->vacaciones_dias))
                            - Vacaciones: <b>{{ $solicitud->vacaciones_dias }}</b> días al año.<br>
                            @else - Vacaciones<br>
                            @endif
                            @if($solicitud->aguinaldo_dias > 0 && !is_null($solicitud->aguinaldo_dias))
                            - Aguinaldo: <b>{{ $solicitud->aguinaldo_dias }}</b> días al año.<br>
                            @else - Aguinaldo<br>
                            @endif
                            @if($solicitud->Otras)
                            - Otras prestaciones (bonos, vales de despensa, seguros de gastos médicos mayores etc): <b>{{ $solicitud->Otras }}</b>
                            @endif
                    </p>
                    <p class="sangria">
                        c) Que desempeñaba sus actividades laborales en las siguientes condiciones: <br>
                            - Horario: <b>{{ $solicitud->horario }}</b> Hrs.<br>
                            - Horario de comida: <b>{{ $solicitud->comida }}.</b><br>
                            - Domicilio donde prestaba sus servicios: <b>{{ $solicitud->tipo_vialidad }} {{ $solicitud->calle }} {{ $solicitud->num_ext }} @if(!empty($solicitud->n_int))
                                    int. {{ $solicitud->n_int }}
                                @endif COLONIA {{ $solicitud->colonia }}, {{ mb_strtoupper($municipioEmpresa, 'UTF-8') }}, {{ mb_strtoupper($estadoEmpresa, 'UTF-8') }} C.P. {{ $solicitud->codigo_postal }}</b>.
                    </p>
                        <!-- (APARTADO QUE LLENA MANUALMENTE QUIEN ATIENDE A LAS PARTES)  -->
                    <p class="sangria">
                        d) Que el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> presentó solicitud para solicitar iniciar el procedimiento de conciliación 
                        prejudicial ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, por motivo de Ratificación De Convenio.
                    </p>
                    <p class="sangria">     
                        e) Que el Centro Estatal, fijó la audiencia de conciliación para el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                    </p>  

                    <b>CUARTA</b>. Declara la parte <b>EMPLEADORA</b>:
                        <p class="sangria">
                            a) Que la parte <b>TRABAJADORA</b> fue contratada en los términos señalados en la declaración inmediata anterior. 
                        </p>
                                   
                    <b>QUINTA</b>. Declaran las <b>PARTES</b>:  
                        <p class="sangria">
                            a)  Que el presente convenio se celebra con la finalidad de ratificar el acuerdo de voluntades de ambas partes, ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, 
                            bajo el Número de Identificación Único <b>{{ $solicitud->NUE }}.</b>
                        </p>
                        <p class="sangria">        
                            b) Que el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, se celebro la audiencia de conciliación y que, por así convenir a sus 
                            intereses, las <b>PARTES</b> al haber llegado a un acuerdo para dirimir el conflicto suscitado, se sujetan al tenor de las siguientes:
                        </p>   
                    
                    <center><b>C L Á U S U L A S:</b></center>
                    
                    <p><br>
                        <b>PRIMERA</b>. Las <b>PARTES</b> han determinado que por así convenir a sus intereses dan por concluida la relación laboral por mutuo acuerdo, conforme a lo estipulado por el artículo 53, 
                            fracción I, de la Ley Federal del Trabajo.<br> <br>

                        <b>SEGUNDA</b>. La parte <b>TRABAJADORA</b> manifiesta bajo protesta de decir verdad, que el vínculo laboral lo mantuvo exclusivamente con la parte <b>EMPLEADORA</b>. Por lo anterior, 
                            expresa que no existió relación laboral alguna con otras personas, incluido el personal que fungía como superior jerárquico en el centro de trabajo donde la parte <b>TRABAJADORA</b> 
                            desempeñaba sus labores.<br><br>
                                    
                        <b>TERCERA</b>. La parte <b>EMPLEADORA</b> otorgará en favor de la parte <b>TRABAJADORA</b> el pago acordado conforme a las disposiciones de la Ley Federal del Trabajo y respetando los derechos 
                            consagrados en el mismo ordenamiento legal. <br>

                        Asimismo, la parte <b>TRABAJADORA</b> manifiesta su entera conformidad y la aceptación de éste, así como la forma en que se obtuvieron los conceptos que se describen en la cláusula <b>QUINTA</b>.<br><br>
                        
                        <b>CUARTA</b>. La parte <b>TRABAJADORA</b> manifiesta que durante el tiempo que laboró para la parte <b>EMPLEADORA</b>, se cubrió en tiempo y forma el pago su salario; cada una de las 
                            prestaciones ordinarias y extraordinarias y en especie que conforme a derecho le corresponden, así mismo como cualquier riesgo o accidente de trabajo que haya sufrido. Por lo anterior, 
                            la parte <b>EMPLEADORA</b> no adeuda pago de concepto alguno.<br><br>

                        <b>QUINTA</b>. La parte <b>TRABAJADORA</b> recibirá de la parte <b>EMPLEADORA</b> la cantidad de <b>${{ number_format($solicitud->monto, 2) }} {{ $montoTexto }}</b>, 
                            conforme a los siguientes conceptos:</p>

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
                                <td class="text-right"><strong>Neto a pagar: </strong>
                                <td><strong>${{ number_format($pagoTotal, 2) }} M.N.</strong></td>
                            </tr>
                            </thead>   
                        </table>
                    </p>
                    <p><b>{{ $solicitud->resolucion_justificacion }}</b></p>
                    <!-- CON PAGOS DIFERIDOS-->       
                    @if($pagosDif->C_pagos>'1')            
                        <p><b>SEXTA</b>. La <b>EMPLEADORA</b> manifiesta en fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> que pagará en <b>{{ $pagosDif->C_pagos}}</b> 
                            exhibiciones, hasta culminar la cantidad de 
                            <b>${{ number_format($solicitud->monto, 2) }} {{ $montoTexto }}</b>, tal como se muestra:
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

                        <p>En caso de que la parte <b>EMPLEADORA</b> no cubra el pago de la cantidad estipulada y dentro del plazo determinado en esta cláusula, deberá pagar a la parte <b>TRABAJADORA</b> 
                            el equivalente a un día de salario diario, el cual se fijará en razón del salario que percibía dicha parte antes de finalizar la relación de trabajo correspondiente a la cantidad de 
                            <b>${{ number_format($salario_diario, 2) }} {{ $diarioTexto }}</b> Esa cantidad se sumará a la previamente pactada, por cada día que 
                            transcurra, sin que se dé cabal cumplimiento al convenio, con fundamento en el artículo 684-E, fracción XIV, último párrafo, de la Ley Federal del Trabajo.</p>
                        
                        <p>Asimismo, manifiestan estar de acuerdo que de no pagarse el primero de los pagos convenidos en la fecha de su vencimiento, quedará a salvo el derecho de cualquiera de las partes para 
                            exigir el cumplimiento del pago total de la cantidad pactada ante la autoridad competente, a parte de los días que transcurran de pena convencional.</p>

                    <!-- CONDICIONAL 1 SOLO PAGO(EN UNA SOLA EXIBICIÓN)--> 
                    @elseif($pagosDif->C_pagos=='1')            
                    <p><b>SEXTA</b>. La <b>EMPLEADORA</b> manifiesta en fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> que pagará en <b>{{ $pagosDif->C_pagos}}</b> 
                        exhibición, la cantidad de 
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
                    </div><br>

                    <p>En caso de que la parte <b>EMPLEADORA</b> no cubra el pago de la cantidad estipulada y dentro del plazo determinado en esta cláusula, deberá pagar a la parte <b>TRABAJADORA</b> 
                        el equivalente a un día de salario diario, el cual se fijará en razón del salario que percibía dicha parte antes de finalizar la relación de trabajo correspondiente a la cantidad de 
                        <b>${{ number_format($salario_diario, 2) }} {{ $diarioTexto }}</b> Esa cantidad se sumará a la previamente pactada, por cada día que 
                        transcurra, sin que se dé cabal cumplimiento al convenio, con fundamento en el artículo 684-E, fracción XIV, último párrafo, de la Ley Federal del Trabajo.</p>
                    @endif  
                    <p>      
                        <b>SÉPTIMA</b>. Las <b>PARTES</b> solicitan se apruebe y sancione este convenio, toda vez que se elaboró conforme a las disposiciones aplicables de la Ley Federal del Trabajo como 
                        resultado del diálogo de la conciliación entre la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b>. Así mismo, manifiestan que se encuentran conformes con el presente acuerdo 
                        por no contener cláusula contraria a la costumbre, a la moral, ni renuncia a los derechos de las <b>PARTES</b>.<br><br>
                                    
                        <b>OCTAVA</b>. Las <b>PARTES</b> manifiestan que es su voluntad ratificar el presente convenio en todas y cada una de sus partes y la aprobación de su contenido, por lo que no se 
                        reservan acción legal o derecho alguno para ejercitar con posterioridad a la firma del presente convenio.<br><br>
                                    
                        <b>NOVENA</b>. Las <b>PARTES</b> solicitan ante el Centro Estatal de Conciliación Laboral que se les expida un tanto original del convenio, y en el momento en que se haya 
                        cumplido totalmente, se les expida acta en la que conste el cumplimiento de éste, en términos del artículo 684-E, fracción XIV, primer párrafo, de la Ley Federal del Trabajo.<br><br>
                                    
                        <b>DÉCIMA</b>. Las <b>PARTES</b> manifiestan que en la celebración del presente convenio no existió violencia, mala fe, dolo, lesión o cualquier otro tipo de vicio del consentimiento 
                        que pudiera nulificarlo.<br><br>
                    
                          
                        <b>DÉCIMA PRIMERA</b>. En caso de que no se cumplan los términos de lo convenido en el presente instrumento, las <b>PARTES</b> deberán acudir al Juzgado Laboral competente a 
                        efecto de que se realice el Procedimiento de Ejecución que la Ley Federal del Trabajo contempla. <br>
                    </p>
                    <div class="salto-inteligente"></div>
                    <div class="contenedor-firmas">
                        <p> 
                            Enteradas las <b>PARTES</b> del alcance legal del presente convenio que se eleva a la categoria de cosa juzgada, conforme al artículo 684-E fracción XIII, mismo que se firma en <b>{{ $solicitud->delegacion }},</b> 
                            Michoacán de Ocampo a los <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\í\a\s \d\e F \d\e\l Y') }}</b>, ante la fe de <b>{{ $conciliador->name }}</b>, funcionario(a) conciliador(a), quien 
                            lo sanciona en este mismo acto. <b>Doy fe.</b>
                        </p>       
                        <br><br><br>
                        <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:10px;">
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b> {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}<br>
                                        LA PARTE TRABAJADORA
                                    </b>
                                </td>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}<br>
                                        LA PARTE EMPLEADORA
                                    </b>
                                </td>
                            </tr>
                            <br>
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><b>Doy fe</b><br><br><br><br>
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                            FUNCIONARIO/A CONCILIADOR/A<br>
                                            DEL CENTRO DE CONCILIACIÓN LABORAL
                                            DEL ESTADO DE MICHOACÁN DE OCAMPO
                                    </b>
                                </td>
                                <!--
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><b>Vo. Bo.</b><br><br><br><br>
                                    <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($delegado->name, 'UTF-8') }}<br>
                                    {{ $nombramiento_delegado }}                         
                                    </b>
                                </td>
                                -->
                            </tr>
                        </table><br>

                        <br><br>

                        @if((!empty($etiquetaIniciales) && !empty($inicialesConcluye)) && $solicitud->fecha_conclucion != NULL && $solicitud->fecha_conclucion > \Carbon\Carbon::parse('2026-06-03'))
                            <div class="etiqueta-iniciales-pie">
                                <small><b>{{ $etiquetaIniciales }}</b></small><br>
                                <small>Elaboró: <b>{{ $inicialesConcluye }}</b></small>
                            </div>
                        @endif
                        <br><br>

                        <p style="font-size: 10px;">
                            LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DEL CONVENIO DE CONCILIACIÓN DE FECHA <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                        </p> 
                    </div>
                    {{--<br><br>
                    <p><center><b>___________________________________<br> {{ strtoupper($conciliador->name) }} <br> FUNCIONARIO/A CONCILIADOR/A<br>
                        DEL CENTRO DE CONCILIACIÓN LABORAL DEL<br>ESTADO DE MICHOACÁN DE OCAMPO</b></p></center> --}}   
                    
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