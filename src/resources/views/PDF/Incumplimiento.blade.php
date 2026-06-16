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
                            <td>{{ mb_strtoupper($solicitud->delegacion) }} </td>
                        </tr>
                        <tr>    
                            <td><b>Número de identificación único: </b></td>
                            <td>{{ $solicitud->NUE }} </td>
                        </tr>    
                    </table>
                </div><br><br><br><br><br>
                <p><b>
                    Trabajador(a): {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador ?? '' }} {{ $solicitud->segundo_trabajador ?? '' }}<br> 
                    Empleador(a): {{ $solicitud->empresa }} {{ $solicitud->primera_empresa ?? '' }} {{ $solicitud->segunda_empresa ?? '' }}<br>
                    Fecha y hora de audiencia: {{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }} a las {{ $solicitud->hora }} horas.<br> 
                    Fecha en que se emite la constancia de incumplimiento: {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e\l Y') }}<br>
                    Pena Convencional: Si<br>
                    Salario diario: ${{ number_format($salario_diario, 2) }} M.N.
                </b></p>  

                <p><center><b>CONSTANCIA DE INCUMPLIMIENTO DE CONVENIO</b></center></p><br>

                <p>
                    Cenrtificación. Ante la falta de pago pactado en las cláusulas <b>QUINTA</b> y <b>SEXTA</b> del <b>CONVENIO DE CONCILIACIÓN</b>, relacionadas con el expediente de Número de Identificación Único <b>{{ $solicitud->NUE }}</b> 
                    de fecha <b>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b>, entre las partes <b>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b> y <b>{{ $solicitud->empresa }}</b>, 
                    ante esta autoridad conciliatoria, y en atención a los principios de legalidad, imparcialidad, confiabilidad, eficacia, objetividad, profesionalismo, y transparencia se emite <b>CONSTANCIA DE INCUMPLIMIENTO DE CONVENIO</b> 
                    a favor de la <b>PARTE TRABAJADORA {{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</b>; dejando a salvo sus derechos para ejercer las 
                    acciones pertinentes ante el Tribunal Laboral que corresponda.<br><br>

                    De conformidad con el artículo 123 fracción XX párrafo segundo de la Constitución Política de los Estados Unidos Mexicanos y artículos 33, 590-E, 590-F, 684-C y 684-E fracción VIII, XIII, XIV, penúltimo y último párrafo, 
                    987 y 990 de la Ley Federal del Trabajo, así como los artículos 17 y 20 del Reglamento Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. Se ordena el archivo del presente <b>asunto como concluido. Doy Fe.</b>
                </p>

                <br><br><br><br>       
                <center><p><b>___________________________________<br>{{ mb_strtoupper($conciliador->name, 'UTF-8') }} <br>FUNCIONARIO/A CONCILIADOR/A<br>
                    DEL CENTRO DE CONCILIACIÓN LABORAL<br>DEL ESTADO DE MICHOACÁN DE OCAMPO</b></p></center>         

                @if((!empty($etiquetaIniciales) && !empty($inicialesConcluye)) && !empty($pagos->fecha_conclucion) != NULL && $pagos->fecha_conclucion > \Carbon\Carbon::parse('2026-06-03'))
                    <div class="etiqueta-iniciales-pie">
                        <small><b>{{ $etiquetaIniciales }}</b></small><br>
                        <small>Elaboró: <b>{{ $inicialesConcluye }}</b></small>
                    </div>
                @endif
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