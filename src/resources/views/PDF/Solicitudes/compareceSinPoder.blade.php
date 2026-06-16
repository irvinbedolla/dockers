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
                            <tr>   
                                <td><b>Solicitante: </b></td>
                                <td>{{$solicitante->nombre}} </td>
                            </tr>
                            <tr>   
                                <td><b>Citado: </b></td>
                                <td>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }} </td>
                            </tr>
                    </table>
                </div><br><br><br><br><br><br><br><br><br><br>
                
                <p><center><b>CERTIFICACIÓN:</b></center></p><br>

                <p>
                    En la Ciudad de <b>{{ $solicitud->delegacion }}</b>, Michoacán, siendo las <b>{{ \Carbon\Carbon::now()->format('H:i') }}</b> horas, del día 
                    <b>{{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e\l Y') }}</b>, fecha y hora señalada para la Audiencia de Conciliación dentro del 
                    número único de registro citado al rubro; ante la fe pública de la persona Conciliadora de 
                    nombre <b>{{$conciliador->name}}</b>, adscrita a la Delegación Regional <b>{{ $solicitud->delegacion }}</b> del Centro de Conciliación Laboral del Estado 
                    de Michoacán de Ocampo, en ejercicio de mis facultades establecidas en el artículo 684-E fracción IV de la Ley Federal del Trabajo y 20 fracción 
                    I, XIII y XVI del Reglamento Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, hago constar la siguiente:
                </p>

                <p>
                    Que comparece a la audiencia de Conciliación señalada para el día hoy, la <b>C. ________________________________ </b>, quien se identifica con credencial de 
                    elector ___________________________ y  manifiesta ser representante legal de la parte citada <b>________________________________</b>; 
                    sin exhibir documento alguno que acredite (o con documento insuficiente describir el documento y adjuntar copia en el expediente para constancia legal) la personería ostentada; 
                    por tanto, esta actuante se ve impedida a dar trámite a la presente audiencia; por lo que una vez cerciorado que la persona fue legalmente notificado el 
                    día <b>{{ \Carbon\Carbon::parse($citado->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</b> a las <b>___</b>hrs por parte de personal notificador del Centro; se hace efectivo el apercibimiento decretado en el 
                    citatorio tener a las partes por inconformes de todo arreglo conciliatorio, emitiendo ACTA DE MULTA y la CONSTANCIA DE HABER AGOTADO LA CONCILIACIÓN, dejando 
                    a salvo los derechos de la parte solicitante, y para los efectos legales a que haya lugar.- Notifíquese  
                    <br><br>
                        
                    <br><br>
                    Así y con fundamento en los artículos 684-E fracción IV, X, XIV, 684-F, 684-H, 684-I, y demás relativos de la Ley Federal del Trabajo; artículo 8, 27 de la Ley 
                    Orgánica del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo; relacionados con las fracciones I, VII, XIII, XVI y XVII del artículo 20 
                    Reglamento Interior del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. Doy fe.
                </p>
                <br><br><br>       
                <center><br><br> <p><b>___________________________________<br>{{$conciliador->name}}<br>FUNCIONARIO/A CONCILIADOR/A</b></p></center>           
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