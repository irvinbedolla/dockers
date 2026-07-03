<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="{{ public_path('assets/css/bootstrap.min.css') }}" rel="stylesheet">
        
    
        <style>
            @page {
                margin: 0px 0px;
            }
            body{
                padding-top: 95px;
            }
            main{
                margin: 50px 0 40px 0; /*Para colocar el texto*/
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
                font-size: 15px;
                text-align: justify;
                margin-left: 3cm;     
                margin-right: 3cm; 
                line-height: 1.2;
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
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                
                    <p><center><b>
                        DEPARTAMENTO DE CONCILIACIÓN, GÉNERO E IGUALDAD FORMATO DE AUTORIZACIÓN DE LA PERSONA USUARIA PARA SU CANALIZACIÓN A DEPENDENCIAS 
                    </b></center></p><br><br><br>
                
                <p style="text-align: right;"> Fecha: {{$caso->fecha}} </p>
                
                <p><b>
                    Nombre de la persona usuaria: </b> {{ $recepcion->solicitante }}.
                </p>
                
                
                <p><b>
                    Funcionario del Centro de Conciliación Laboral que atiende el caso:</b> {{ $auxiliar->name }}.
                </p>
                
                <p><b>
                    Dependencia a la que se canalizará:</b> {{ $caso->dependencia }}
                    <br><br>
                </p>
                
                
                
                <p>
                Por medio del presente, la persona usuaria otorga su consentimiento expreso, libre e informado para ser canalizada a la dependencia previamente señalada, con
                la finalidad de recibir la atención que corresponda conforme a la naturaleza de su caso. <br><br>
                Asimismo, declara que ha sido debidamente informada sobre el alcance de dicha canalización. <br><br>
                </p>
                
              
                 
                <div class="salto-inteligente"></div>
                <div class="contenedor-firmas">  
                    <br><br>
                    <p style="text-align: center; font-weight: bold;">___________________________________<br> <br>{{ $recepcion->solicitante }}</p>
               
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