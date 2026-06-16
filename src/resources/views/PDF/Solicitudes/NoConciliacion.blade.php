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
                margin: 50px 50px 80px 50px; /*Para colocar el texto*/
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
        <footer></footer>
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
                <center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</b></center><br>
                <p><b>
                    Solicitante: {{ $solicitante->nombre }}<br> 
                    Citado(a): {{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}<br>
                    Domicilio del citado(a): {{ $citado->tipo_vialidad }} {{ $citado->calle }} #{{ $citado->n_ext }} 
                    @if(!empty($citado->n_int))
                        int. {{ $citado->n_int }}
                    @endif 
                    {{ $citado->colonia }}, {{ mb_strtoupper($municipioEmpresa, 'UTF-8') }}, {{ mb_strtoupper($estadoEmpresa, 'UTF-8') }}, C.P. {{ $citado->cp }}<br>
                    Fecha de registro de la solicitud: {{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}<br>
                    Fecha y hora de audiencia: {{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }} a las {{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} hrs.<br>
                    Funcionario(a) conciliador(a) responsable: {{ $conciliador->name }}<br>
                    Asistencia del citado: 
                    @if($solicitud->tipo_solicitud == 1) 
                        @if($citado->id_abogado!=null)
                        Si 
                        @else 
                        No 
                        @endif 
                    @else 
                        @if($citado->comparecencia == 'Si')
                        Si
                        @else
                        No
                        @endif
                    @endif
                </b>
                    <center><b>CONSTANCIA DE NO CONCILIACIÓN</b></center><br>
                </p> 
                <p>
                    @if($solicitud->tipo_solicitud == 1)
                        <!--Cuando el citado SI se presenta-->
                        @if($citado->id_abogado!=null)
                            <b>Motivación:</b> Una vez agotada la etapa de conciliación prejudicial, trás dialogar ambas partes y no llegar a un acuerdo conciliatorio, se dejan a salvo los derechos de las partes 
                            para solicitar una nueva fecha de audiencia en términos del artículo 684-E, fracción VIII, último párrafo.<br><br>

                            Con fundamento en los artículos 684-E, fracción VIII, tercer párrafo y 684-F, fracción VIII, de la Ley Federal del Trabajo y artículos 5, primer párrafo y 8, fracción I y IV de la 
                            Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo y atendiendo los principios constitucionales de legalidad, imparcialidad, confiabilidad, eficacia, objetividad, 
                            confidencialidad, profesionalismo, transparencia y publicidad, se expide con fecha <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> 
                            la presente <b>CONSTANCIA DE NO CONCILIACIÓN</b>.<br><br>
                        @endif
                        <!--Cuando el citado NO se presenta-->
                        @if($citado->id_abogado==0 || $citado->id_abogado=='null')
                            <b>Motivación:</b> Toda vez que a la audiencia de conciliación, sólo comparecio el solicitante, esta autoridad conciliatoria emite constancia de haber agotado la etapa de conciliación prejudicial obligatoria.<br><br>

                            Con fundamento en el artículo 684-E, fracción X y 684-F, de la Ley Federal del Trabajo y artículos 5, primer párrafo y 8, fracción I y IV de la 
                            Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo y atendiendo los principios constitucionales de legalidad, imparcialidad, confiabilidad, eficacia, objetividad, 
                            confidencialidad, profesionalismo, transparencia y publicidad, se expide con fecha <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> 
                            la presente <b>CONSTANCIA DE NO CONCILIACIÓN</b>.<br><br>
                        @endif
                    @else
                        @if($citado->comparecencia == 'Si')
                            <b>Motivación:</b> Una vez agotada la etapa de conciliación prejudicial, trás dialogar ambas partes y no llegar a un acuerdo conciliatorio, se dejan a salvo los derechos de las partes 
                            para solicitar una nueva fecha de audiencia en términos del artículo 684-E, fracción VIII, último párrafo.<br><br>

                            Con fundamento en los artículos 684-E, fracción VIII, tercer párrafo y 684-F, fracción VIII, de la Ley Federal del Trabajo y artículos 5, primer párrafo y 8, fracción I y IV de la 
                            Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo y atendiendo los principios constitucionales de legalidad, imparcialidad, confiabilidad, eficacia, objetividad, 
                            confidencialidad, profesionalismo, transparencia y publicidad, se expide con fecha <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> 
                            la presente <b>CONSTANCIA DE NO CONCILIACIÓN</b>.<br><br>

                        <!--Cuando el citado NO se presenta-->
                        @else
                            <b>Motivación:</b> Toda vez que a la audiencia de conciliación, sólo comparecio el solicitante, esta autoridad conciliatoria emite constancia de haber agotado la etapa de conciliación prejudicial obligatoria.<br><br>

                            Con fundamento en el artículo 684-E, fracción X y 684-F, de la Ley Federal del Trabajo y artículos 5, primer párrafo y 8, fracción I y IV de la 
                            Ley Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo y atendiendo los principios constitucionales de legalidad, imparcialidad, confiabilidad, eficacia, objetividad, 
                            confidencialidad, profesionalismo, transparencia y publicidad, se expide con fecha <b>{{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> 
                            la presente <b>CONSTANCIA DE NO CONCILIACIÓN</b>.<br><br>
                        @endif
                    @endif
                    Finalmente, se dejan a salvo los derechos de los interesados para ejercer las acciones respectivas ante el Tribunal Laboral competente, en términos de los artículos 123, apartado A, 
                    fracción XX, de la Constitución Política de los Estados Unidos Mexicanos; 521, fracción III, 870 Bis, de la Ley Federal del Trabajo. <b>Doy fe</b>.
                         
                </p>    
                <br><br>
                <center><p><b>___________________________________<br>{{ mb_strtoupper($conciliador->name, 'UTF-8') }} <br> FUNCIONARIO/A CONCILIADOR<br>
                DEL CENTRO DE CONCILIACIÓN LABORAL DEL<br>ESTADO DE MICHOACÁN DE OCAMPO</b></p></center>   
                
                <p style="font-size:10px"><b>En caso de que el conflicto se relacione con prestaciones de seguridad social, pensiones, designación de beneficiarios
                    y devolución de aportaciones, puedes acudir a la Procuraduría de la Defensa del Trabajo.<br><br>
                    Se hace del conocimiento de los trabajadores, sus beneficiarios y sindicatos que en caso de requerirlo, pueden ser
                    asesorados y en su caso, representados legalmente para presentar una demanda de forma GRATUITA, por la Procuraduría de la Defensa
                    del Trabajo, con domicilio en la calle Dr. Miguel Silva G., número 486, de la colonia Centro histórico, en la ciudad de
                    Morelia, Michoacán. Puedes llamar al teléfono 4433179002 extensiones 107 y 1315.</b>
                </p> 
            </div>
            <!--<script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $y = $pdf->get_height() - 44;
                    $x = ($pdf->get_width() / 2) - 50;
                    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                    $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0));
                }
            </script>-->
        </main>
    </body>
</html>    