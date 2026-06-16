<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
       
        <!-- Ionicons -->
        <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
        <link href="../public/assets/css/all.css" rel="stylesheet" type="text/css">
        <link href="../public/assets/css/iziToast.min.css" rel="stylesheet">
        <link href="../public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
        <link href="../public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
        <!-- Agregados para los Select del Formulario Personas-->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        @livewireStyles

        @yield('page_css')
        <!-- Template CSS -->
        <link rel="icon"       href="../public/assets/images/ccl-r.png" type="image/x-icon">
        <link rel="stylesheet" href="../public/assets/css/style.css">
        <link rel="stylesheet" href="../public/assets/css/components.css">
        <style>
            footer {
                position: fixed;
                bottom: 0cm;
                left: 0cm;
                right: 0cm;
                height: 2cm;
            }
        </style>
    @yield('page_css')

        @yield('page_css')
        <!-- Template CSS -->
        @yield('page_css')
    </head>
    @php $fecha_actual = date('y-m-d'); @endphp

        <div class="section-header">
            <h3 class="page__heading">Reporte Informativo 
            {{ \Carbon\Carbon::parse($fecha_actual)->translatedFormat('d \d\e F \d\e\l Y') }}</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <spam>Solicitudes</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Fecha</th>
                                        <th style="color: #fff;  text-align: center;">Número de Expediente</th>
                                        <th style="color: #fff;  text-align: center;">Solicitante</th>
                                        <th style="color: #fff;  text-align: center;">Motivo</th>
                                        <th style="color: #fff;  text-align: center;">Actividad Economica</th>
                                        <th style="color: #fff;  text-align: center;">Notificación</th>
                                    </thead>
                                    <tbody>
                                        @foreach($solicitudes as $solicitud)
                                        <tr>
                                            <td style=" text-align: center;">{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d/m/Y') }}</td>
                                            <td style=" text-align: center;">{{$solicitud->NUE}}</td>
                                            <td style=" text-align: center;">{{$solicitud->solicitante}}</td>
                                            <td style=" text-align: center;">{{$solicitud->motivo}}</td>
                                            <td style=" text-align: center;">{{$solicitud->actividad_economica}}</td>
                                            <td style=" text-align: center;">{{$solicitud->notificacion}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><br><br>

                            <div class="table-responsive">
                                <spam>Ratificaciones</spam>
                                <table class="table-striped" style="width:100%;">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Fecha</th>
                                        <th style="color: #fff;  text-align: center;">Número de Expediente</th>
                                        <th style="color: #fff;  text-align: center;">Solicitante</th>
                                        <th style="color: #fff;  text-align: center;">Motivo</th>
                                        <th style="color: #fff;  text-align: center;">Actividad Economica</th>
                                        <th style="color: #fff;  text-align: center;">Notificación</th>
                                        <th style="color: #fff;  text-align: center;">Monto</th>
                                    </thead>
                                    <tbody>
                                    @foreach($ratificaciones as $rati)
                                        <tr>
                                            <td style=" text-align: center;">{{ \Carbon\Carbon::parse($rati->fecha)->translatedFormat('d/m/Y') }}</td>
                                            <td style=" text-align: center;">{{$rati->NUE}}</td>
                                            <td style=" text-align: center;">{{$rati->solicitante}}</td>
                                            <td style=" text-align: center;">{{$rati->motivo}}</td>
                                            <td style=" text-align: center;">{{$rati->actividad_economica}}</td>
                                            <td style=" text-align: center;">{{$rati->notificacion}}</td>
                                            <td style="text-align: center;">${{number_format($rati->monto,2)}}</td>
                                        </tr>
                                     @endforeach
                                    </tbody>
                                </table>
                            </div><br><br>

                            <div class="table-responsive">
                                <spam>Cumplimientos</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Fecha</th>
                                        <th style="color: #fff;  text-align: center;">Número de Expediente</th>
                                        <th style="color: #fff;  text-align: center;">Tipo de Pago</th>
                                        <th style="color: #fff;  text-align: center;">Monto</th>
                                    </thead>
                                    <tbody>
                                        @foreach($convenios as $convenio)
                                            <tr>
                                                <td style="text-align: center;">{{ \Carbon\Carbon::parse($convenio->fecha)->translatedFormat('d/m/Y') }}</td>
                                                <td style="text-align: center;">{{$convenio->NUE}}</td>
                                                <td style="text-align: center;">{{$convenio->tipo_pago}}</td>
                                                <td style="text-align: center;">${{number_format($convenio->monto,2)}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><br><br>

                            <div class="table-responsive">
                                <spam>Asesorias</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Asesorado</th>
                                        <th style="color: #fff;  text-align: center;">Sexo</th>
                                    </thead>
                                    <tbody>
                                        @foreach($asesorias as $asesoria)
                                            <tr>
                                                <td style="text-align: center;">{{$asesoria->nombre}}</td>
                                                <td style="text-align: center;">{{$asesoria->sexo}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <br><br><br>
                            <div >
                                <div class="row">
                                    &emsp;&emsp;<span>Entrega: {{$usuario}}</span> &emsp;&emsp;<span>Recibido: _________________________</span>                
                                </div>
                            </div>
                            

                        </div>
                    </div>
                </div>
            </div>
        </div>

