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
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer></footer>
        <main>
            <div class="content">
                <div class="table-responsive">
                    <table id="tabla_solicitud" class="table-striped" style="width:60%; float: right;">
                        <tr>   
                            <td><b>Oficina: </b></td>
                            <td>{{ mb_strtoupper($solicitud->delegacion, 'UTF-8') }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr> 
                    </table>
                </div><br><br><br>
                <p><center><b>
                    CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br><br>
                    ACTA DE AUDIENCIA DE CONCILIACIÓN     </b></center></p><br>
                <p>
                    En el <b>Centro de Conciliación Laboral del Estado de Michoacán de Ocampo con sede en {{ $solicitud->delegacion }}</b>, siendo las <b>{{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} horas del
                    {{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, hora y día señalados para la celebración de la Audiencia de Conciliación 
                    Prejudicial, relativa al expediente electrónico con Número de Identificación Único <b>{{ $solicitud->NUE }}</b>, misma que se celebra ante  
                    <b>{{ $conciliador->name }},</b> Funcionario/a Conciliador/a adscrito al Centro de Conciliación 
                    Laboral del Estado de Michoacán de Ocampo,  con fundamento en los artículos 33, 590-E, 590-F, 684-A, 684-B, 684-C, 684-D, 684-E, 684-F, 684-G y 684-I, de la 
                    Ley Federal del Trabajo, artículo 27 de la Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, y artículo 20 del Reglamento Interior del 
                    Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, <b>declara abierta</b> la Audiencia de Conciliación Prejudicial en la que comparecen: <br><br>
                    
                    @if ($solicitud->tipo_solicitud == 1)
                        La parte <b>solicitante  {{ $solicitante->nombre }}</b> se identifica con <b>{{ strtoupper($solicitante->identificacion) }}</b>, Número <b>{{ $solicitante->num_identificacion }}</b> 
                        expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b>, se declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.
                    @else
                        @if($audienciaPoder->poder->reprecentante == 'Si')
                            La parte <b>solicitante {{ $solicitante->nombre }}</b> comparece a través de su <b>representante legal {{ $audienciaPoder->poder->nombre_representante }} {{ $audienciaPoder->poder->primer_apellido_representante }} {{ $audienciaPoder->poder->segundo_apellido_representante }}</b>, quien se
                            identifica con <b>{{ $audienciaPoder->poder->tipo_identificacion }}</b>, de Número <b>{{ $audienciaPoder->poder->num_identificacion }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b>, así como <b>{{ $audienciaPoder->poder->descipcion_poder }}</b>.
                        @elseif($audienciaPoder->poder->reprecentante == 'No')
                            La parte <b>solicitante {{ $solicitante->nombre }} </b> se identifica con <b>{{ $audienciaPoder->poder->tipo_identificacion }}</b>, de número <b>{{ $audienciaPoder->poder->num_identificacion }}</b> expedida a su favor por <b>{{ $descripcionIdentificacionS }}</b>, declara ser una persona
                            mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.
                        @endif
                    @endif
                    </p> 
                    
                    La parte solicitante manifiesta que presento solicitud el dia: {{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }} para iniciar el procedimiento de conciliacion 
                    prejudicial ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. <br><br>

                    La parte citada:
                    @foreach ($citados as $citado)
                        @if($solicitud->tipo_solicitud == 1)
                            @if($citado->tipo_persona == "Moral")
                                <b>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido ?? ''}}, comparece a través de su representante legal {{ $citado->abogado->nombre_representante }} {{ $citado->abogado->primer_apellido_representante }} {{ $citado->abogado->segundo_apellido_representante }}</b> quien se identifica con 
                                <b>{{ strtoupper($citado->abogado->tipo_identificacion) }}</b>, de Número <b>{{ $citado->abogado->num_identificacion }}</b> expedida a su favor por <b>{{ $citado->abogado->$descripcionIdentificacionP }}</b>, en términos de la  <b>{{  $citado->abogado->descipcion_poder }},</b> 
                                identificaciones que concuerdan  con sus rasgos fisicos y que en este acto se agrega copia de las mismas al expediente para los efectos legales y administrativos correspondientes.<br>
                            @else
                                @if($citado->abogado->reprecentante == "Si")
                                    <b>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido ?? ''}}, comparece a través de su representante legal {{ $citado->abogado->nombre_representante }} {{ $citado->abogado->primer_apellido_representante }} {{ $citado->abogado->segundo_apellido_representante }}</b> quien se identifica con 
                                    <b>{{ strtoupper($citado->abogado->tipo_identificacion) }}</b>, de Número <b>{{ $citado->abogado->num_identificacion }}</b> expedida a su favor por <b>{{ $citado->abogado->$descripcionIdentificacionP }}</b>, en términos de la  <b>{{  $citado->abogado->descipcion_poder }}</b>
                                    </b><br>
                                @else
                                    <b>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido ?? ''}}</b> quien comparece por derecho propio y se identifica con
                                    <b>{{ strtoupper($citado->abogado->tipo_identificacion) }}</b>, de Número <b>{{ $citado->abogado->num_identificacion}}</b> expedida a su favor por <b>{{ $citado->abogado->$descripcionIdentificacionP }}</b>,
                                    y declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.<br>
                                @endif
                            @endif
                        @else
                            @php
                                $descCitado = NULL;
                                if (isset($descripcionIdentificacionCitado) && array_key_exists($citado->id, $descripcionIdentificacionCitado)) {
                                    $descCitado = $descripcionIdentificacionCitado[$citado->id];
                                }
                            @endphp
                            <b>{{ $citado->nombre }} {{ $citado->primer_apellido ?? ''}} {{ $citado->segundo_apellido ?? ''}}</b>, quien se identifica con <b>{{ $citado->tipo_identificacion_comparecencia }}</b>, de número <b>{{ $citado->num_identificacion_comparecencia }}</b> 
                            expedida a su favor por <b>{{ $descCitado }}</b>, se declara ser una persona mayor de edad, por lo que tiene plenas capacidades de goce y ejercicio para convenir o transigir.
                        @endif
                    @endforeach 
                    
                    
                    
                    {{-- La parte citada <b>{{ $solicitante->nombre }}</b> quien se identifica con 
                    <b>{{ strtoupper($solicitante->identificacion) }}</b>, de Número <b>{{ $solicitante->num_identificacion }}</b> expedida a su favor por 
                    <b>{{ $descripcionIdentificacionS }}</b> y, por la parte solicitante
                    <b>@if(is_null($solicitud->nombre_empresa) && is_null($solicitud->primero_empresa))
                           {{ $solicitud->empresa }}
                       @else {{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }} @endif</b>se identifica con 
                    <b>{{ strtoupper($abogado->tipo_identificacion) }}</b>, de Número <b>{{ $abogado->num_identificacion }}</b> expedida a su favor por 
                    <b>{{ $descripcionIdentificacionP }}</b>, identificaciones que concuerdan fisionómicamente con las partes y, que, en este acto, se agrega copia cotejada al 
                    expediente electrónico para que conste como corresponda; documentos que les son devueltos por ser innecesaria su retención. <br><br>--}}




                    <br><br>
                    De lo anterior, una vez realizando el respectivo cotejo con identificaciones, mismas que concuerdan fisionómicamente con las partes y, que en este acto, se agrega copia cotejada 
                    al expediente electrónico para que conste como corresponda; esta Autoridad Conciliadora se encuentra en condiciones para desahogar la <b>Audiencia de Conciliación Prejudicial</b>.<br><br>


                    Se hace del conocimiento del trabajador(a) que podrá comparecer asistido por abogado(a) o persona de su confianza, pero no se reconocerá a ésta como apoderado, por tratarse 
                    de un Procedimiento de Conciliación y no de un juicio; por lo que respecta al empleador, éste podrá comparecer a través de su representante, siempre y cuando cuente con las 
                    facultades suficientes para obligarse en su nombre y lo acredite ante esta instancia.<br><br>

                    Asimismo, se les informa a las partes que las manifestaciones que realicen durante la audiencia, no podrán constituir prueba o indicio en ningún procedimiento administrativo 
                    o judicial ni el personal de las autoridades conciliadoras podrán ser llamados a comparecer como testigos ante los Tribunales Laborales, de conformidad con los establecido en 
                    los artículos 684-C tercer párrafo y 684-J de la Ley Federal del Trabajo.<br><br>

                    El Procedimiento de Conciliación se realiza de conformidad con los principios constitucionales de imparcialidad, neutralidad, flexibilidad, legalidad, equidad, buena fe, información, 
                    honestidad, y confidencialidad. Consecuentemente, es un proceso ágil, objetivo, imparcial, transparente y eficaz, en el que sus costos son menores en comparación a un procedimiento 
                    jurisdiccional, máxime que en el procedimiento ni el patrón ni el trabajador puede estar seguro de ganar el juicio, mientras que en la conciliación se llega a un acuerdo en el que 
                    se benefician ambas partes.<br><br>

                    A continuación, se cede el uso de la voz de manera ordenada y respetuosa a los presentes en esta audiencia, para manifestar en relación al proceso de conciliación:
                </p>
                    <!--[RESOLUCION_PRIMERA_MANIFESTACION]-->
                    <p><b>{{ $datosAudiencia->resolicion_primera }}</b></p>

                <p> Así, resulta procedente exponer a los presentes la propuesta de un acuerdo conciliatorio justo y equitativo que beneficie a ambas partes del conflicto; haciendo de su conocimiento 
                    que, en el caso de estar conformes con dicho acuerdo, se procederá a realizar el convenio por escrito, mismo que deberá ratificarse en el presente acto y, posteriormente, se les 
                    entregará copia certificada del mismo en el que conste su cumplimiento en términos de los artículos 684-E fracción XIV y 684-I, de la ley Federal del Trabajo.<br><br>
                    La propuesta referida para la parte @if($solicitud->tipo_solicitud == 1) trabajadora, @else solicitate, @endif se encuentra formulada en los términos siguientes:<br><br>
                    @if(!empty($prestaciones) && count($prestaciones) > 0)
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
                                        <td>{{ $conceptosTexto[$concepto->id] }}  M.N.</td> 
                                    </tr>
                                @endforeach
                                <!--</p>-->
                            </tbody>
                        </table>  
                    @endif    
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
                                        <td>{{ $deduccionesTexto[$deduccion->id] }} M.N.</td> 
                                    </tr>
                                @endforeach  
                            </tbody>
                        </table> 
                    @endif
                    @if((!empty($deducciones) && count($deducciones) > 0) || (!empty($prestaciones) && count($prestaciones) > 0))
                        <table class="table table-bordered table-compacta" style="width:100%; float: right;">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <td class="text-right"><strong>Neto a pagar: </strong></td>
                                    <td><strong>${{ number_format($pagoTotal, 2) }}</strong></td>
                                </tr>
                            </thead>   
                        </table>   
                    @endif
                </p> 
                <!--[RESOLUCION_PROPUESTAS_TRABAJADORES] -->
                {{--<p><b>{{ $conciliador->resolucion_trabajadores }}</b></p>--}}

                <!--[RESOLUCION_JUSTIFICACION_PROPUESTA]-->
                <p><b>{{ $datosAudiencia->resolicion_justificacion }}</b></p>
                
                <p>
                    A efecto de conocer la opinión de las partes, se cede el uso de la voz de manera ordenada y respetuosa a los presentes en esta audiencia, con la finalidad de escuchar lo que 
                    tengan que expresar en torno a la propuesta y sus alcances, <b>haciendo de su conocimiento que no se podrán negociar derechos y prestaciones irrenunciables en términos de la Ley 
                    Federal del Trabajo,</b> y respetando los adquiridos; de no estar de acuerdo se podrá solicitar una nueva audiencia que tendrá verificativo dentro de los cinco días siguientes al 
                    cierre de esta diligencia.
                </p>

                <!--[RESOLUCION_SEGUNDA_MANIFESTACION]-->
                <p><b>{{ $datosAudiencia->resolicion_segunda }}</b></p>
                <p>
                    Por tanto, en caso de que las partes hayan expresado estar conformes con la propuesta sugerida, se procede a la celebración del convenio respectivo, el cual tendrá valor de cosa juzgada 
                    y, tendrá la calidad de un título para iniciar acciones ejecutivas sin necesidad de ratificación lo anterior con fundamento en el artículo 684-E fracción XIII de la Ley Federal del Trabajo.<br><br>

                    En caso de realizar convenio y éste se incumpla, cualquiera de las partes podrá promover su cumplimiento mediante el proceso de ejecución de sentencia establecido en la Ley Federal del 
                    Trabajo y ante los Tribunales Laborales competentes. <br><br>

                    Ahora bien, se hace del conocimiento de las partes que, la información aportada durante el Procedimiento de Conciliación no podrá comunicarse a persona o autoridad alguna, a excepción de 
                    la Constancia de No Conciliación y, en su caso, del convenio de conciliación que se celebre, mismos que deberán ser remitidos al Tribunal Laboral competente y deberán contener los nombres 
                    y domicilios aportados por las partes, acompañando las constancias relativas a la notificación de la parte citada que haya realizado la Autoridad Conciliadora y los buzones electrónicos 
                    asignados.<br><br>

                    De igual modo, el tratamiento de los datos proporcionados por los interesados y los datos personales recabados por este Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, 
                    serán protegidos, incorporados y tratados únicamente por este Organismo Descentralizado de la Administración Pública Estatal como Sujeto Obligado ante la Ley General de Protección de Datos 
                    Personales en Posesión de Sujetos Obligados y a la Ley General de Transparencia y Acceso a la Información Pública. <br><br>

                    Asimismo, se informa que sus datos no podrán ser difundidos sin el consentimiento expreso, salvo las excepciones previstas en ley.
                </p>
                <div class="salto-inteligente"></div>
                <div class="contenedor-firmas"> 
                    <p>
                        Así lo proveyó, <b>{{ $conciliador->name }}</b>, Funcionario(a) Conciliador(a) adscrito al Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. <b>Doy fe.</b>
                    </p>
                    <br><br>
                    <table style="width:100%; text-align:center; border-collapse: collapse; margin-top:30px;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:0 20px;">
                                <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                <b>
                                    {{ $solicitante->nombre }}<br>
                                    SOLICITANTE
                                </b>
                                </td>
                                <td style="width:50%; vertical-align:top; padding:0 20px;">
                                <div style="border-top: 2px solid #000; width:80%; margin: 0 auto 5px auto;"></div>
                                <b>
                                    @if($solicitud->tipo_solicitud == 2) 
                                        @foreach ($citados as $citado)
                                            {{ $citado->nombre }} {{ $citado->primer_apellido ?? '' }} {{ $citado->segundo_apellido ?? ''}}<br>
                                        @endforeach
                                    @endif
                                    {{-- {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }}<br> --}}
                                    CITADO(S)
                                </b>
                            </td>        
                        </tr>
                    </table>
                    <br><br><br>
                    <p><center><b>__________________________________________________<br> {{ mb_strtoupper($conciliador->name, 'UTF-8') }} <br> FUNCIONARIO/A CONCILIADOR/A<br>
                            DEL CENTRO DE CONCILIACIÓN LABORAL<br>DEL ESTADO DE MICHOACÁN DE OCAMPO</b></center></p>         
                    <br>
                    <p style="font-size: 10px;">
                        LAS PRESENTES FIRMAS FORMAN PARTE INTEGRA DE LA ACTA DE AUDIENCIA DE FECHA <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> EXPEDIENTE NÚMERO <b>{{ $solicitud->NUE }}</b> DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO.
                    </p>           
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