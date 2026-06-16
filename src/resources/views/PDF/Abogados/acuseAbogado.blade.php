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
            body {
                counter-reset: page;
                font-family: sans-serif;
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

            .footer-content::after {
                content: "Página " counter(page) " de " counter(pages);
            }
            body {
                margin: 0cm;
                padding: 0cm;
                background-color: transparent !important;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: black;
            }

            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
            .content {
                padding: 3cm 2cm 3cm 2cm;
                position: relative;
                /*padding: 4cm 2cm 3cm 2cm; /* Deja espacio para encabezado y pie  padding: 100px 50px;*/
                z-index: 1;
            }
            p {
                line-height: 1.5;
                text-align: justify;
            }
        </style>
    </head>
    <body>
        <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
        <footer>
            
        </footer>
        <main>
            <div class="content">
                <div class="col-lg-12">
                    <p><center><b>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
                        ACUSE DE REGISTRO DE REPRESENTANTE LEGAL</b></center></p><br><br><br>
                    <p><b>FECHA DE REGISTRO: {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}</b><br>
                       <b>FOLIO DE REGISTRO: {{ $abogado->idAbogado}}</b>
                    </p><br>
                    
                    <p> 
                        Estimado/a <strong>{{ $abogado->nombre_representante }} {{ $abogado->primer_apellido_representante }} {{ $abogado->segundo_apellido_representante }}</strong>, <br><br>
                        Le informamos que su registro en el sistema <b>SiConcilio</b> ha sido realizado con éxito.
                        Usted ha sido registrado como <strong>representante legal</strong> de la empresa o persona moral indicada a continuación con la siguiente información:<br><br>
                    </p>

                    <div class="table-responsive">
                        <table id="abogado" class="table-striped" style="width:100%;">
                            <thead>
                                <th>Folio de registro</th>
                                <th>Empresa/patrón</th>
                                <th>Giro comercial</th>
                                <th>RFC</th>
                                <th>Documento</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $abogado->idAbogado}}</td>
                                    <td>{{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal }} {{ $abogado->segundo_apellido_patronal }}</td> 
                                    <td>{{ $abogado->giroComercial }}</td>
                                    <td>{{ $abogado->rfc_patronal }}</td>
                                    <td>{{ $abogado->tipo_documento_representante }}</td>
                                </tr>
                            </tbody>
                        </table>      
                    </div><br><br>
                    <p>
                        <span style="color: red;"><b>NOTA:</b></span> Es de suma importancia conservar el número de folio que le ha sido asignado, ya que será requerido para 
                        futuras gestiones y validaciones ante este Centro.<br><br>
                        Su folio de registro servirá como referencia oficial en trámites posteriores dentro del Centro de Conciliación Laboral del Estado de Michoacán. Por 
                        ello, le solicitamos guardar este acuse de registro y no extraviar su número de folio.
                    </p>
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
</html>    