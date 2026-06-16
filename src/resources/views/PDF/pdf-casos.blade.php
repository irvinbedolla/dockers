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
                <div class="table-responsive row">
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #4A001F;">
                            <th style="color: #fff;  text-align: center;">Fecha</th>
                            <th style="color: #fff;  text-align: center;">Solicitante</th>
                            <th style="color: #fff;  text-align: center;">Solicitud</th>
                            <th style="color: #fff;  text-align: center;">Sexo</th>
                            <th style="color: #fff;  text-align: center;">Tipo</th>
                            <th style="color: #fff;  text-align: center;">Grupo</th>
                            <th style="color: #fff;  text-align: center;">Tarjeta</th>
                            <th style="color: #fff;  text-align: center;">Resultado</th>
                        </thead>
                        <tbody>
                            @foreach($turnos as $turno)
                            <tr>
                                <td style=" text-align: center;">{{ \Carbon\Carbon::parse($turno->fecha)->translatedFormat('d/m/Y') }}</td>
                                <td style=" text-align: center;">{{$turno->solicitante}}</td>
                                <td style=" text-align: center;">{{$turno->tipo}}</td>
                                <td style=" text-align: center;">{{$turno->sexo}}</td>
                                <td style=" text-align: center;">{{$turno->tipo_caso}}</td>
                                <td style=" text-align: center;">{{$turno->vulnerables}}</td>
                                <td style=" text-align: center;">{{$turno->tarjeta}}</td>
                                <td style=" text-align: center;">{{$turno->resultado}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>    
    </body>
</html>    