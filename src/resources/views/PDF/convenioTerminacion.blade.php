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
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $text = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                    $pdf->text(500, 820, $text, $font, $size);
                }
            </script>
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
                    SOLICITUD RATIFICACIÓN DE CONVENIO TERMINACIÓN VOLUNTARIA <br>
                    NÚMERO DE IDENTIFICACIÓN ÚNICO {{ $solicitud->NUE }}<br><br>
                    SOLICITANTES:<br>
                    {{ $solicitud->empresa }}<br>
                    {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}
                </b></p>  
                <p><center><b>CONVENIO DE CONCILIACIÓN</b></center></p><br>
                <p>Con fundamento en los artículos 123, apartado A, fracción XXVII, inciso h) párrafo segundo, de la Constitución Política de los Estados Unidos Mexicanos; 
                    artículos 33, 53 fracción I y 684-E de la Ley Federal del Trabajo; artículo 20, fracción V y X del Reglamento Interior del Centro de Conciliación Laboral de Michoacán de Ocampo, 
                    se celebra el presente convenio por una parte <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> quién en lo 
                    subsecuente se denominará la parte <b>“TRABAJADORA”</b> y, por otro <b>{{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}</b> 
                    a quién en lo subsecuente se le denominará la parte <b>“EMPLEADORA”</b>, 
                    a quienes en lo sucesivo de forma conjunta se les denominará las <b>“PARTES”</b>, quienes se someten y obligan en términos de las siguientes declaraciones y cláusulas:
                </p>

                <p><center><b>D E C L A R A C I O N E S:</b></center></p><br>

                <p><b>PRIMERA</b>. {{ $solicitud->resolucion_primera }}.</p> 

                <p><b>SEGUNDA</b>. {{ $solicitud->resolucion_segunda }}</p>.  

                <b>TERCERA</b>. Declara la parte <b>TRABAJADORA</b>:
                    <p class="sangria">
                        a) Que fue contratada por la parte <b>EMPLEADORA</b> desde el <b>{{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, para prestar sus 
                        servicios como <b>{{ $solicitud->categoria}}</b>, puesto en el que se desempeñó 
                        hasta el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha_termino)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                    </p>
                    <p class="sangria">                
                        b) Que por el desempeño de sus labores contaba con las siguientes prestaciones:<br>
                            - Salario mensual: <b>${{ number_format($salario_mensual, 2) }} {{ $mensualTexto }} M.N</b>. <br>
                            - Días de descanso: <b>{{ $dias_descanso }}</b><br>
                            - Vacaciones: <b>{{ $solicitud->vacaciones_dias }}</b> días al año.<br>
                            - Aguinaldo: <b>{{ $solicitud->aguinaldo_dias }}</b> días al año.<br>
                            - Otras prestaciones (bonos, vales de despensa, seguros de gastos médicos mayores etc): <b>{{ $solicitud->Otras }}</b>.
                    </p>
                    <p class="sangria">
                        c) Que desempeñaba sus actividades laborales en las siguientes condiciones: <br>
                            - Horario: <b>{{ $solicitud->horario }}</b>.<br>
                            - Horario de comida: de <b>{{ $solicitud->comida }}</b> de las instalaciones.<br>
                            - Domicilio donde prestaba sus servicios: <b>{{ $solicitud->domicilio }}</b>.
                    </p>
                        <!-- (APARTADO QUE LLENA MANUALMENTE QUIEN ATIENDE A LAS PARTES)  -->
                    <p class="sangria">
                        d) Que el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> presentó solicitud para solicitar iniciar el procedimiento de conciliación 
                        prejudicial ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, por motivo de Ratificación De Convenio por concepto de <b>{{ $solicitud->motivo }}</b>.
                    </p>
                    <p class="sangria">     
                        e) Que el Centro Estatal, fijó la audiencia de conciliación para el día <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                    </p>  

                    <b>CUARTA</b>. Declara la parte <b>EMPLEADORA</b>:
                        <p class="sangria">
                            a) Que la parte <b>TRABAJADORA</b> fue contratada en los términos señalados en la declaración inmediata anterior. 
                        </p>
                        <p class="sangria">
                            b) Que con motivo del citatorio de fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> emitido por el Centro de Conciliación Laboral 
                            del Estado de Michoacán de Ocampo, la parte <b>EMPLEADORA</b> comparece para desahogar la etapa de conciliación prejudicial conforme al Artículos 33, 53 fracción I y 
                            684-E fracción VI de la Ley Federal del Trabajo.
                        </p> 
                                   
                    <b>QUINTA</b>. Declaran las <b>PARTES</b>:  
                        <p class="sangria">
                            a)  Que el presente convenio se celebra con la finalidad de dar por concluida la relación laboral de manera voluntaria para ambas partes, así como el expediente de Conciliación 
                            en el que se actúa, seguido ante el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, bajo el número de identificación único <b>{{ $solicitud->NUE }}</b>.
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
                                    
                        <b>TERCERA</b>. La <b>EMPLEADORA</b> otorgará en favor de la <b>TRABAJADORA</b> el pago acordado conforme a las disposiciones de la Ley Federal del Trabajo y respetando los derechos 
                            consagrados en el mismo ordenamiento legal. <br>

                        Asimismo, la <b>TRABAJADORA</b> manifiesta su entera conformidad y la aceptación de éste, así como la forma en que se obtuvieron los conceptos que se describen en la cláusula <b>QUINTA</b>.<br><br>
                        
                        <b>CUARTA</b>. La parte <b>TRABAJADORA</b> manifiesta que durante el tiempo que laboró para la parte <b>EMPLEADORA</b>, se cubrió en tiempo y forma el pago su salario; cada una de las 
                            prestaciones ordinarias y extraordinarias y en especie que conforme a derecho le corresponden, así mismo como cualquier riesgo o accidente de trabajo que haya sufrido. Por lo anterior, 
                            la parte <b>EMPLEADORA</b> no adeuda pago de concepto alguno.<br><br>

                        <b>QUINTA</b>. La <b>TRABAJADORA</b> recibirá por parte de la <b>EMPLEADORA</b> la cantidad de <b>${{ number_format($solicitud->monto, 2) }} {{ $montoTexto }} M.N</b>, 
                            conforme a los siguientes conceptos:</p>
                            <p class="sangria">
                                @foreach($prestaciones as $concepto)
                                    @switch($concepto->descripcion)                                   
                                        @case('Vacaciones')
                                            - Vacaciones: <b>${{ number_format($concepto->monto, 2) }} {{ $vacacionesTexto }} M.N</b>.<br>
                                            @break
                                        @case('PrimaVacacional')
                                            - Prima vacacional: <b>${{ number_format($concepto->monto, 2) }} {{ $primaTexto }} M.N</b>.<br>
                                            @break
                                        @case('Aguinaldo')
                                            - Aguinaldo: <b>${{ number_format($concepto->monto, 2) }} {{ $aguinaldoTexto }} M.N</b>.<br>
                                            @break
                                        @case('DSueldo')
                                            - Días de sueldo: <b>${{ number_format($concepto->monto, 2) }} {{ $DSueldoTexto }} M.N</b>.<br>
                                            @break
                                        @case('GraficaciónA')
                                            - Graficación A (Con base al salario integrado): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionATexto }} M.N</b>.<br>
                                            @break
                                        @case('GraficaciónB')
                                            - Graficación B (20 Días por año cumplido): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionBTexto }} M.N</b>.<br>
                                            @break
                                        @case('GratificaciónC')
                                            - Graficación C (Prima de antigüedad topada): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionCTexto }} M.N</b>.<br>
                                            @break
                                        @case('GratificaciónD')
                                            - Graficación D (Incluye cualquier otra prestación): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionDTexto }} M.N</b>.<br>
                                            @break
                                        @case('GratificaciónE')
                                            - Graficación E (Prestaciones en especie): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionETexto }} M.N</b>.<br>
                                            @break
                                        @case('GratificaciónF')
                                            - Graficación F (Reconocimiento de derechos): <b>${{ number_format($concepto->monto, 2) }} {{ $gratificacionFTexto }} M.N</b>.<br>
                                            @break 
                                        @case('Otras')
                                            - Otras prestaciones (bonos, vales de despensa, seguros de gastos médicos mayores etc): <b>${{ number_format($concepto->monto, 2) }} {{ $otrasTexto }} M.N</b>. {{ $solicitud->Especifique }}<br>
                                            @break
                                        @default    
                                    @endswitch
                                @endforeach
                            </p>

                            <!-- (APARTADO QUE LLENA MANUALMENTE QUIEN ATIENDE A LAS PARTES)  -->
                                
                    <p><b>SEXTA</b>. La <b>EMPLEADORA</b> manifiesta en fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> que pagará en <b>{{ $pagosDif->C_pagos}}</b> 
                        exhibiciones, hasta culminar la cantidad de 
                        <b>${{ number_format($solicitud->monto, 2) }} {{ $montoTexto }} M.N</b>, tal como se muestra:
                    </p>
                        <div class="table-responsive">
                            <table id="pagos" class="table-striped" style="width:60%;">
                                <thead>
                                    <th style="display: none;">ID</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                        <tr>
                                            <td style="display: none;">{{$pago->id_solicitud}}</td>
                                            <td>{{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}</td> 
                                            <td>{{$pago->hora}}</td>
                                            <td>${{ number_format($pago->monto, 2) }}</td>
                                            <td>{{$pago->descripcion}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>      
                        </div>
                        <!-- CONDICIONAL EN BASE A LO LLENADO EN FORMULARIO SE AGREGA PAGOS-->
                        <p>{{ $pago->observaciones }}</p>           

                    <p>En caso de que la parte <b>EMPLEADORA</b> no cubra el pago de la cantidad estipulada y dentro del plazo determinado en esta cláusula, deberá pagar a la parte <b>TRABAJADORA</b> 
                        el equivalente a un día de salario diario, el cual se fijará en razón del salario que percibía dicha parte antes de finalizar la relación de trabajo correspondiente a la cantidad de 
                        <b>${{ number_format($salario_diario, 2) }} {{ $diarioTexto }} M.N</b>. Esa cantidad se sumará a la previamente pactada, por cada día que 
                        transcurra, sin que se dé cabal cumplimiento al convenio, con 
                        fundamento en el artículo 684-E, fracción XIV, último párrafo, de la Ley Federal del Trabajo.</p>
                               
                    <p>Asimismo, manifiestan estar de acuerdo que de no pagarse el primero de los pagos convenidos en la fecha de su vencimiento, quedará a salvo el derecho de cualquiera de las partes para 
                        exigir el cumplimiento del pago total de la cantidad pactada ante la autoridad competente, a parte de los días que transcurran de pena convencional. <br><br>

                        <b>SÉPTIMA</b>. Las <b>PARTES</b> solicitan se apruebe y sancione este convenio, toda vez que se elaboró conforme a las disposiciones aplicables de la Ley Federal del Trabajo como 
                        resultado del diálogo de la conciliación entre la parte <b>TRABAJADORA</b> y la parte <b>EMPLEADORA</b>. Así mismo, manifiestan que se encuentran conformes con el presente acuerdo 
                        por no contener cláusula contraria a la costumbre, a la moral, ni renuncia a los derechos de las <b>PARTES</b>.<br><br>
                                    
                        <b>OCTAVA</b>. Las <b>PARTES</b> manifiestan que es su voluntad ratificar el presente convenio en todas y cada una de sus partes y la aprobación de su contenido, por lo que no se 
                        reservan acción legal o derecho alguno para ejercitar con posterioridad a la firma del presente convenio.<br><br>
                                    
                        <b>NOVENA</b>. Las <b>PARTES</b> solicitan ante el Centro Estatal de Conciliación Laboral que les sean expedidas las copias autorizadas del convenio, y en el momento en que se haya 
                        cumplido totalmente, se les expida acta en la que conste el cumplimiento de éste, en términos del artículo 684-E, fracción XIV, primer párrafo, de la Ley Federal del Trabajo.<br><br>
                                    
                        <b>DÉCIMA</b>. Las <b>PARTES</b> manifiestan que en la celebración del presente convenio no existió violencia, mala fe, dolo, lesión o cualquier otro tipo de vicio del consentimiento 
                        que pudiera nulificarlo.<br><br>
                                    
                        <b>DÉCIMA PRIMERA</b>. En caso de que no se cumplan los términos de lo convenido en el presente instrumento, las <b>PARTES</b> deberán acudir a los juzgados Laborales del fuero común a 
                        efecto de que se realice el procedimiento de ejecución que la Ley Federal del Trabajo contempla. <br>
                        <br>Enteradas las <b>PARTES</b> del alcance legal del presente convenio que se eleva a cosa juzgada, conforme al artículo 684-E fracción XIII, mismo que se firma en SEDE de Michoacán de 
                        Ocampo a los <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, ante la fe de <b>{{ $conciliador->name }}</b>, funcionario conciliador, quien 
                        lo sanciona en este mismo acto. <b>Doy fe</b>.
                    </p>
                                    
                    <br><br>
                    <div class="row">
                        <div class="col-12 text-center">
                            <div style="display: inline-block; margin-right: 50px;">
                                <p><center><b>___________________________________<br> {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}  <br> LA PARTE TRABAJADORA<br></b></center></p>
                            </div>
                                    
                            <div style="display: inline-block;">
                                <p><center><b>___________________________________<br> {{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}<br>LA PARTE EMPLEADORA<br></b></center></p>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <p><center><b>___________________________________<br> {{ $conciliador->name }} <br> FUNCIONARIO/A CONCILIADOR/A</b></center> </p>     
            </div>
        </main>    
    </body>
</html>    