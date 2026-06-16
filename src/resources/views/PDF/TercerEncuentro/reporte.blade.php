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
                    <table id="tabla_solicitud" class="table-striped" style="width:100%; float: center;">
                            <tr>   
                                <td><b>Asistencia</b></td>
                            </tr>
                    </table>
                </div><br><br><br>
                <div class="table-responsive">
                    <label>Conferencia Inaugural: “Implementación del Mecanismo Laboral de Respuesta Rápida (MLRR) del T- MEC”</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia1 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conversatorio 1: “La Conciliación Laboral como Mecanismo de la Solución Pacífica de los Conflictos Laborales”</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia2 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conversatorio 2: “Implicación y Aplicación de la Ley Silla, Regulación del Trabajo en Plataformas Digitales y Reducción de las Jornadas Laborales”</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia3 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conversatorio 3: “La Seguridad Social como Derecho Humano y su Impacto en las Resoluciones Judiciales"</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia4 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Presentación del Libro “Conciliación y Justicia Laboral” Coordinadores: Andrés Medina Guzmán y Sergio Carmelo Domínguez Mota</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia5 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conversatorio 4: “Criterios Relevantes en la Ejecución de las Sentencias en Materia Laboral</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia6 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conversatorio 5: ILTRAS “Modelo de la Conciliación Laboral Comparada Internacionalmente”</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia7 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Presentación del Libro ILTRAS “El Despido en Latinoamérica: Una Visión de Derecho Comparado”</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia8 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Conferencia Magistral de Clausura</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia9 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-break"></div><br><br>
                <div class="table-responsive">
                    <label>Ceremonia de Clausura</label>
                    <table class="table table-striped mt-2">
                        <thead style="background-color: #869b9c;">
                            <th style="color: #fff;  text-align: center;">Nombre</th>
                            <th style="color: #fff;  text-align: center;">Telefono</th>
                            <th style="color: #fff;  text-align: center;">Email</th>
                            <th style="color: #fff;  text-align: center;">Visita</th>
                            <th style="color: #fff;  text-align: center;">Firma</th>
                        </thead>
                        <tbody> 
                            @foreach($personas_conferencia10 as $persona)
                                <tr>
                                    <td >{{$persona->primer_apellido}} {{$persona->segundo_apellido}} {{$persona->nombre}}</td>
                                    <td>{{$persona->telefono}}</td>
                                    <td>{{$persona->correo}}</td>
                                    <td>{{$persona->lugar}}</td>
                                    <td>___________</td>
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