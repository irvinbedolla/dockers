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
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260 MORELIA, MICHOACÁN DE OCAMPO';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P. 60136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA PONIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59600 ZAMORA, MICHOACÁN DE OCAMPO';
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
    @php     
        $cargo_conciliador='';
        if($solicitud->delegacion === 'Morelia' || $solicitud->delegacion === 'Zitácuaro') {
            $cargo_conciliador='ADSCRITO (A) A LA DELEGACIÓN REGIONAL DE MORELIA ';
            $fraccion='I';
        }    
        if($solicitud->delegacion === 'Uruapan' || $solicitud->delegacion ==='Lázaro Cárdenas') {
            $cargo_conciliador='ADSCRITO (A) A LA DELEGACIÓN REGIONAL DE URUAPAN';
             $fraccion='II';
        }
        if($solicitud->delegacion === 'Zamora' || $solicitud->delegacion ==='Sahuayo') {
            $cargo_conciliador='ADSCRITO (A) A LA DELEGACIÓN REGIONAL DE ZAMORA';
            $fraccion='III';
        }  
    @endphp
    @php
        use Carbon\Carbon;
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
                <center><p><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br><br>
                        ACTA DE MULTA POR INCOMPARECENCIA</b></p></center>
                @php
                    //$fechaNotificacion = !empty($citado->fecha) ? \Carbon\Carbon::parse($citado->fecha) : null;
                    $fechaNotificacion = !empty($citadoOriginal->fecha) ? \Carbon\Carbon::parse($citadoOriginal->fecha) : null;
                @endphp
                <p>En <b>{{ $direccion_sede }}</b> a <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, el(la) funcionario(a) 
                    conciliador(a) <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}</b>, adscrito al Centro 
                    de Conciliación Laboral del Estado de Michoacán de Ocampo, <b>hace constar y certifica</b> que la parte citada 
                    <b>{{ $citado->nombre }} @if(!empty($citado->primer_apellido)){{ $citado->primer_apellido }}@endif  @if(!empty($citado->segundo_apellido)){{ $citado->segundo_apellido }}@endif no compareció,</b> 
                    a la Audiencia de Conciliación prevista para las 
                    <b>{{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }}</b> horas de esta misma fecha, a pesar de encontrarse debidamente notificado(a) para tal efecto, circunstancia que se corrobora 
                    con <b>la razón de notificación de fecha {{ $fechaNotificacion ? mb_strtoupper($fechaNotificacion->translatedFormat('d \D\E F \D\E\L Y')) : '' }}. Doy fe</b>.
                </p>
                {{--<p>
                    <b>Michoacán de Ocampo</b>, a <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>.
                </p>--}}
                <p>
                    Advirtiéndose así, que la parte citada <b>{{ $citado->nombre }} @if(!empty($citado->primer_apellido)){{ $citado->primer_apellido }}@endif  @if(!empty($citado->segundo_apellido)){{ $citado->segundo_apellido }}@endif</b>, no compareció a la
                    audiencia de conciliación prevista para las <b>{{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }}</b> horas de esta misma fecha, a pesar de encontrarse debidamente notificado(a) para tal efecto, circunstancia que se
                    corrobora con la notificación de fecha <b>{{ (!empty($citadoOriginal) && !empty($citadoOriginal->fecha)) ? mb_strtoupper(\Carbon\Carbon::parse($citadoOriginal->fecha)->translatedFormat('d \D\E F \D\E\L Y')) : '' }}</b>, por lo que con fundamento en los artículos 16, primer párrafo,
                    de la Constitución Política de los Estados Unidos Mexicanos; 590-E, 590-F, 684-E, fracciones IV, X, 684-I, fracción II de la Ley Federal del Trabajo; y 27 de 
                    la Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo; artículos 19 fracción {{ $fraccion }} y 20 fracción XVI y XVII del Reglamento Interior del Centro de 
                    Conciliación del Estado de Michoacán de Ocampo, <b>SE ACUERDA</b>:
                </p>

                <p>
                    En atención a lo anterior, se tiene a la parte citada <b>{{ $citado->nombre }} @if(!empty($citado->primer_apellido)){{ $citado->primer_apellido }}@endif  @if(!empty($citado->segundo_apellido)){{ $citado->segundo_apellido }}@endif</b> por <b>inconforme con todo 
                    arreglo conciliatorio</b>.
                </p>

                <p>
                    En este acto, <b>se hace efectivo el apercibimiento decretado</b> en el citatorio notificado el <b>{{ $fechaNotificacion ? mb_strtoupper($fechaNotificacion->translatedFormat('d \D\E F \D\E\L Y')) : '' }}</b> 
                    y se impone a la parte citada <b>{{ $citado->nombre }} @if(!empty($citado->primer_apellido)){{ $citado->primer_apellido }}@endif  @if(!empty($citado->segundo_apellido)){{ $citado->segundo_apellido }}@endif una 
                    multa mínima por el monto de $5,865.50 (cinco mil ochocientos sesenta y cinco pesos 50/100 M.N.)(equivalente a Cincuenta veces la Unidad de Medida y Actualización)</b>.
                </p>
                            
                <p>
                    Gírese atento oficio electrónico <b>al Servicio de Administración Tributaria</b>, para que haga efectivo el cobro de la multa impuesta a la parte 
                    citada <b>{{ $citado->nombre }} @if(!empty($citado->primer_apellido)){{ $citado->primer_apellido }}@endif  @if(!empty($citado->segundo_apellido)){{ $citado->segundo_apellido }}@endif</b> con los datos de identificación con los que se cuenta:
                

                <table style="margin-left: 20px; font-weight: bold;">
                    <tr>
                        <td style="width: 200px;">Nombre o razón social:</td>
                        <td>{{ $citado->nombre }}
                            @if(!empty($citado->primer_apellido)) {{ $citado->primer_apellido }} @endif
                            @if(!empty($citado->segundo_apellido)) {{ $citado->segundo_apellido }} @endif
                        </td>
                    </tr>
                    @if(!empty($citado->curp))
                        <tr>
                            <td>CURP:</td>
                            <td>{{ $citado->curp }}</td>
                        </tr>
                    @endif
                    @if(!empty($citado->rfc))
                        <tr>
                            <td>RFC:</td>
                            <td>{{ $citado->rfc }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Domicilio:</td>
                        <td>
                            {{ $citado->tipo_vialidad }} {{ $citado->calle }} NO. {{ $citado->n_ext }}
                            @if(!empty($citado->n_int))
                                INT. {{ $citado->n_int }}
                            @endif
                            {{ $citado->colonia }},
                            {{ mb_strtoupper($municipioEmpresa, 'UTF-8') }},
                            {{ mb_strtoupper($estadoEmpresa, 'UTF-8') }},
                            C.P. {{ $citado->cp }}.
                        </td>
                    </tr>
                </table><br>
                <!--
                <b>
                    Notifíquese personalmente a la parte citada dentro de los próximos 15 días hábiles y por buzón electrónico a la parte solicitante.<br>
                </b>
                -->
                    Así lo proveyó <b>{{ mb_strtoupper($conciliador->name, 'UTF-8') }}</b>, funcionario(a) conciliador(a) adscrito al Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. <b>Doy fe.</b>
                </p><br>
                <p><center><b>___________________________________<br>{{ mb_strtoupper($conciliador->name, 'UTF-8') }} <br>FUNCIONARIO/A CONCILIADOR/A<br>{{$cargo_conciliador}} </b></center> </p>                
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