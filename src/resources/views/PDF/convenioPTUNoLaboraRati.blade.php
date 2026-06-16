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
        $direccion_sede='';
        if($ratificacion->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO.';
        }    
        if($ratificacion->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 60136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($ratificacion->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670 ZAMORA, MICHOACÁN DE OCAMPO.';
        }  
        if($ratificacion->delegacion === 'Zitácuaro') {
            $direccion_sede='5 DE MAYO NORTE NO. 03, PISO 3 COL. CENTRO, C.P. 61500 ZITÁCUARO, MICHOACÁN DE OCAMPO.';
        } 
        if($ratificacion->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P. 60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO.';
        }  
        if($ratificacion->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P. 59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO.';
        } 
    @endphp
    @php
        $nombramiento_delegado='';
        if($ratificacion->delegacion === 'Morelia' || $ratificacion->delegacion === 'Zitácuaro'){
            $nombramiento_delegado='DIRECTOR DE LA DELEGACIÓN REGIONAL DE MORELIA';
        }    
        if($ratificacion->delegacion === 'Uruapan' || $ratificacion->delegacion === 'Lázaro Cárdenas'){
            $nombramiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE URUAPAN';
        }
        if($ratificacion->delegacion === 'Zamora' || $ratificacion->delegacion === 'Sahuayo') {
            $nombramiento_delegado='DIRECTORA DE LA DELEGACIÓN REGIONAL DE ZAMORA';
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
                            <td>{{ strtoupper($ratificacion->delegacion) }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $ratificacion->NUE }} </td>
                        </tr> 
                    </table>
                </div><br><br><br><br>
                <center><p><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO <br><br>
                CONVENIO DE CONCILIACIÓN DE PAGO DE PARTICIPACIÓN DE UTILIDADES </b></p></center>

                <p>Con fundamento en los artículos 123, apartado A, fracciones XX párrafo segundo y XXVII, inciso h) de la Constitución Política de los Estados Unidos Mexicanos; 33, 98, 117, 
                    122, 130, 590-E fracción I, 684-E fracción VI, XIII y 684-F fracción VIII, IX de la Ley Federal del Trabajo, artículo 8 fracción I, III y artículo 27 de Ley Orgánica del 
                    Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, se celebra el presente convenio por una parte 
                    <b>{{ $ratificacion->trabajador }} {{ $ratificacion->primero_trabajador }} {{ $ratificacion->segundo_trabajador }}</b> quién en lo subsecuente se denominará la parte <b>“TRABAJADORA”</b> y, por otro <b> 
                    @if(is_null($abogado->primer_apellido_patronal) && is_null($abogado->segundo_apellido_patronal))
                           {{ $abogado->nombres_patronal }}
                       @else {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }} @endif representado(a) por {{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }} 
                       en carácter de apoderado legal</b> a quién en lo subsecuente se le denominará la parte <b>"EMPLEADORA",</b> a quienes en lo sucesivo de forma conjunta se les denominará
                    las <b>"PARTES"</b> quienes se someten y obligan en términos de las siguientes declaraciones y cláusulas: 
                </p>

                <p><center><b>D E C L A R A C I O N E S:</b></center></p><br>

                <p><b>PRIMERA</b>. La parte <b>TRABAJADORA</b> se idéntica con <b>{{ mb_strtoupper($ratificacion->tipo_identificacion, 'UTF-8') }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b> y declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.</p> 

                <p><b>SEGUNDA</b>. Declara <b>{{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }}</b> quien se identifica con <b>{{ mb_strtoupper($abogado->tipo_identificacion, 'UTF-8') }}</b>, que es apoderado legal de 
                    <b>@if(is_null($abogado->primer_apellido_patronal) && is_null($abogado->segundo_apellido_patronal))
                           {{ $abogado->nombres_patronal }}
                       @else {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }} @endif</b> y que cuenta con facultades suficientes para 
                    convenir a nombre de su representada en términos de <b>{{ $abogado->tipo_documento_representante }}</b>, poder que a la fecha de este convenio no le ha sido revocado.</p> 

                <p><b>TERCERA</b>. Declara la parte <b>TRABAJADORA</b>:</p>
                    <p class="sangria">
                        a) Que fue contratada por la parte <b>EMPLEADORA</b> desde el <b>{{ \Carbon\Carbon::parse($ratificacion->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, para prestar sus servicios como <b>{{ $ratificacion->categoria }}</b>, puesto en el que se desempeñó hasta el día <b>{{ \Carbon\Carbon::parse($ratificacion->fecha_termino)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                    </p>
                    <p class="sangria">                
                        b) Que por el desempeño de sus labores contaba con todas las prestaciones, incluido la <b>Participación de los Trabajadores en las Utilidades</b> de la empresa <b>PTU</b>.
                    </p>
                    <p class="sangria">
                        c) Que el día <b>{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> presentó solicitud para iniciar el procedimiento de conciliación prejudicial ante el Centro de Conciliación Laboral del 
                        Estado de Michoacán de Ocampo, por motivo de <b>Pago de prestaciones</b>, consistente en el <b>reparto de utilidades correspondientes al ejercicio fiscal {{ $ratificacion->year_ptu }}</b>, 
                        misma que confirmó ante el Centro el <b>{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}.</b>
                    </p>
                    <p class="sangria">
                        d) Que el Centro Estatal, fijó la audiencia de conciliación para el día <b>{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}.</b>
                    </p>

                <p> <b>CUARTA.</b> Declara la parte <b>EMPLEADORA:</b> </p>
                    <p class="sangria">     
                        a) Que reconoce adeudo a favor de la parte <b>TRABAJADORA</b> por concepto de Participación de los Trabajadores en las Utilidades (PTU) correspondiente al ejercicio fiscal <b>{{ $ratificacion->year_ptu }}</b>.
                    </p> 
                    <p class="sangria"> 
                        b) Que cuenta con capacidad legal y económica para celebrar el presente convenio.
                    </p>
                    <p class="sangria">
                        c) Que bajo protesta de decir verdad conformó la Comisión Mixta de Participación de Utilidades de conformidad con los artículos 117, 120,125 de la Ley Federal del Trabajo, 
                        con el fin de determinar la cantidad que corresponde a los trabajadores de la <b>EMPLEADORA</b> por concepto de reparto de utilidades del ejercicio fiscal <b>{{ $ratificacion->year_ptu }}</b>.
                    </p>
                    <p class="sangria">
                        d) Que es su voluntad celebrar el presente convenio respecto del pago de Participación de Reparto de Utilidades.
                    </p>

                    <p><b>QUINTA</b>. Declaran las <b>PARTES</b>:  
                        <p class="sangria">
                            a) Que el presente convenio se celebra con la finalidad de dar por terminado el procedimiento de conciliación prejudicial, seguido ante el Centro de Conciliación 
                            Laboral del Estado de Michoacán de Ocampo, bajo el número de identificación único <b>{{ $ratificacion->NUE }}</b>.
                        </p>
                        <p class="sangria">        
                           b) Que el día <b>{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, se celebró la audiencia de conciliación y que, por así convenir a sus intereses, la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b> han llegado a un 
                           acuerdo para dirimir el conflicto suscitado, al tenor de las siguientes.
                        </p>   
                    
                        <center><b>C L Á U S U L A S:</b></center><br>
                    </p>
                    <p>
                        <b>PRIMERA</b>. El presente convenio tiene por objeto fijar la forma y términos en que la parte <b>EMPLEADORA</b> cubrirá a la parte <b>TRABAJADORA</b> el pago correspondiente por concepto de Participación de los Trabajadores en las Utilidades (PTU) del ejercicio fiscal {{ $ratificacion->year_ptu }}.

                        <b>SEGUNDA</b>. La parte <b>EMPLEADORA</b> reconoce adeudar a favor de la parte <b>TRABAJADORA</b> la cantidad de {{ number_format($pagoTotal, 2) }} {{ $montoTexto }} M.N. por concepto de Participación de los Trabajadores en erl Reparto de Utilidades.<br><br>
                                    
                        <b>TERCERA</b>. La parte <b>TRABAJADORA</b> manifiesta bajo protesta de decir verdad, que la cantidad pactada en el presente convenio corresponde al monto determinado conforme al proyecto de reparto aprobado por la Comisión Mixta para la Participación de los Trabajadores en las Utilidades de la empresa,
                         correspondiente al ejercicio fiscal <b>{{ $ratificacion->year_ptu }}</b>.<br><br>

                        <b>CUARTA.</b> Las <b>PARTES</b> solicitan al Centro de Conciliación Laboral del Estado de Michoacán tenga por realizada dicha manifestación para los efectos legales conducentes, precisando que la aprobación y ratificación del presente convenio se efectúa de buena fe y con base en lo manifestado por las partes
                         comparecientes, sin que dicho Centro cuente con facultades materiales, técnicas o medios de verificación y cercioramiento respecto del cálculo, integración o determinación del monto correspondiente al reparto de utilidades.<br><br>
                        En consecuencia, cualquier responsabilidad derivada de la veracidad, suficiencia o exactitud del monto pactado recaerá exclusivamente en la parte <b>EMPLEADORA</b>, quedando a salvo los derechos de la parte <b>TRABAJADORA</b> conforme a la legislación aplicable.<br><br>

                        <b>QUINTA.</b> El <b>TRABAJADOR</b> recibirá por parte de la <b>EMPLEADORA</b> la cantidad de <b>${{ number_format($ratificacion->monto, 2) }} {{ $montoTexto }}</b>, conforme a los siguientes conceptos: 
                        
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
                                <b>${{ number_format($ratificacion->monto, 2) }} {{ $montoTexto }}</b>, tal como se muestra:
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
                            <b>${{ number_format($ratificacion->monto, 2) }} {{ $montoTexto }} M.N</b>, tal como se muestra:
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
                        @endif
                        En caso de que la parte <b>EMPLEADORA</b> no cubra el pago de la cantidad estipulada y dentro del plazo determinado en esta cláusula, deberá pagar a la parte <b>TRABAJADORA</b> el equivalente a un día de salario diario, el cual se 
                        fijará en razón del salario que percibía dicha parte antes de finalizar la relación de trabajo. Esa cantidad se sumará a la previamente pactada, por cada día que transcurra, sin que se dé cabal cumplimiento al convenio, con 
                        fundamento en el artículo 684-E, fracción XIV, último párrafo, de la Ley Federal del Trabajo.<br><br>

                        @if($pagosDif->C_pagos>'1')
                        Asimismo, manifiestan estar de acuerdo que de no pagarse el primero de los pagos convenidos en la fecha de su vencimiento, quedará a salvo el derecho de cualquiera de las partes para exigir el cumplimiento del pago total de la cantidad pactada ante la autoridad competente,
                         a parte de los días que transcurran de pena convencional.<br><br>
                        @endif

                        <b>SÉPTIMA.</b> Las <b>PARTES</b> solicitan se apruebe y sancione el presente convenio que únicamente regula lo relativo al pago de <b>Participación de los Trabajadores en las Utilidades de la empresa PTU del ejercicio fiscal {{ $ratificacion->year_ptu }}</b>,
                         por lo que no implica reconocimiento o renuncia respecto de prestaciones, acciones o derechos diversos; toda vez que se elaboró conforme a las disposiciones aplicables de la Ley Federal del Trabajo como resultado del diálogo de la conciliación entre las <b>PARTES</b>,
                          así como a lo estipulado en la cláusula tercera.<br><br>
                        
                        <b>OCTAVA.</b> Las <b>PARTES</b> manifiestan que es su voluntad ratificar el presente convenio en todas y cada una de sus partes y la aprobación de su contenido, por lo que no se reservan acción legal o derecho alguno para 
                        ejercitar con posterioridad a la firma del presente convenio por lo que ve a la Participación de los Trabajadores en las Utilidades de la empresa citada (PTU) del ejercicio fiscal <b>{{ $ratificacion->year_ptu }}</b>.<br><br>

                        <b>NOVENA.</b> Las <b>PARTES</b> solicitan ante el Centro Estatal de Conciliación Laboral que les sean expedidas las copias autorizadas del convenio, y en el momento en que se haya cumplido totalmente, se les expida acta 
                        en la que conste el cumplimiento de éste, en términos del artículo 33 y 684-E, fracción XIV, primer párrafo, de la Ley Federal del Trabajo.<br><br>

                        <b>DÉCIMA.</b> Las <b>PARTES</b> manifiestan que en la celebración del presente convenio no existió violencia, mala fe, dolo, lesión o cualquier otro tipo de vicio del consentimiento que pudiera nulificarlo,
                         ni clausula contraria a la costumbre, a la moral, ni renuncia a los derechos de ninguna de las <b>PARTES</b>.<br><br>

                        <b>DÉCIMA PRIMERA.</b> En caso de que no se cumplan los términos de lo convenido en el presente instrumento, las <b>PARTES</b> deberán acudir a los juzgados Laborales del fuero común a efecto de que se realice el procedimiento 
                        de ejecución que la Ley Federal del Trabajo contempla.<br><br>
                    </p>
                    <div class="salto-inteligente"></div>
                    <div class="contenedor-firmas">
                        <p>
                            Enteradas las <b>PARTES</b> del alcance legal del presente convenio que se eleva a cosa juzgada, conforme al artículo 684-E fracción XIII, mismo que se firma en <b>Michoacán de Ocampo a {{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, 
                            ante la fe de <b>{{$conciliador->name}}</b>, funcionario(a) conciliador(a), quien lo sanciona en este mismo acto. <b>Doy fe.</b>
                        </p>
                        <br><br>
                        <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:5px;">
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;">
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b> {{ $ratificacion->trabajador }} {{ $ratificacion->primero_trabajador }} {{ $ratificacion->segundo_trabajador }}<br>
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
                            <br><br><br>
                            <tr>
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><b>Doy fe</b><br><br><br><br>
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}<br>
                                            FUNCIONARIO/A CONCILIADOR/A<br>
                                            DEL CENTRO DE CONCILIACIÓN LABORAL
                                            DEL ESTADO DE MICHOACÁN DE OCAMPO
                                    </b>
                                </td>
                                <!--
                                <td style="width:60%; vertical-align:top; padding:0 10px;"><b>Vo. Bo.</b><br><br><br><br>
                                    <div style="border-top: 1px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                    <b>{{ mb_strtoupper($delegado->name, 'UTF-8') }}<br>
                                    {{ $nombramiento_delegado }}                                 
                                    </b>
                                </td>
                                -->
                            </tr>
                        </table>
                        
                        @if((!empty($etiquetaIniciales) && !empty($inicialesConcluye)) && $ratificacion->fecha_conclucion != NULL && $ratificacion->fecha_conclucion > \Carbon\Carbon::parse('2026-06-03'))
                            <div class="etiqueta-iniciales-pie">
                                <small><b>{{ $etiquetaIniciales }}</b></small><br>
                                <small>Elaboró: <b>{{ $inicialesConcluye }}</b></small>
                            </div>
                        @endif
                        
                        <br>
                        <p style="font-size: 10px;">
                            LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DEL CONVENIO DE CONCILIACIÓN DE PAGO DE PARTICIPACIÓN DE UTILIDADES DE FECHA <b>{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $ratificacion->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
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