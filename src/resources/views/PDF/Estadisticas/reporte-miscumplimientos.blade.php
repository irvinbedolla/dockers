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
            .page-break {
                page-break-after: always;
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
                                <td><b>Centro de Conciliación Laboral</b></td>
                             
                                <td>{{ \Carbon\Carbon::parse($fecha_inicial)->format('d/m/y') }} a {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/y') }}</td>
                            </tr>
                    </table>
                </div><br><br><br>
                <div class="table-responsive">
                    <spam>Cumplimientos en Audiencias</spam>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Fecha</th>
                            <th style="color: #fff;  text-align: center;">Hora</th>
                            <th style="color: #fff;  text-align: center;">Núm. Identificación Único</th>
                            <th style="color: #fff;  text-align: center;">Empleador</th>
                            <th style="color: #fff;  text-align: center;">Trabajador</th>
                            <th style="color: #fff;  text-align: center;">Descripción</th>
                            <th style="color: #fff;  text-align: center;">Monto</th>
                            <th style="color: #fff;  text-align: center;">Delegación</th>
                            <th style="color: #fff;  text-align: center;">Conciliador</th>
                            <th style="color: #fff;  text-align: center;">Estatus</th>
                        </thead>
                        <tbody>
                            @foreach($pagosAudiencias as $estadistica)
                                <tr>
                                    <td style=" text-align: center;">{{ \Carbon\Carbon::parse($estadistica->fecha)->format('d/m/y') }}</td>
                                    <td style=" text-align: center;">{{ date_format($estadistica->hora, 'H:i') }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->NUE }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->empresa_representante }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->nombre_trabajador }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->descripcion }}</td>
                                    <td style=" text-align: center;">${{ number_format($estadistica->monto, 2) }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->delegacion }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->name }}</td>
                                    <td style=" text-align: center;">{{ $estadistica->estatus }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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