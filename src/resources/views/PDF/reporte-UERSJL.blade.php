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
    @yield('page_css')

        @yield('page_css')
        <!-- Template CSS -->
        @yield('page_css')
    </head>
   

        <div class="section-header">
            <h3 class="page__heading">Reporte Informativo</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <spam>I. Informacíon General</spam>
                            <div class="table-responsive">
                                <spam>1. Señale el número de conciliadoras y conciliadores con los que cuenta el Centro de Conciliación Local/Federal:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;"></th>
                                        <th style="color: #fff;  text-align: center;">Individuales</th>
                                        <th style="color: #fff;  text-align: center;">Colectivos</th>
                                        <th style="color: #fff;  text-align: center;">Mixtos</th>
                                        <th style="color: #fff;  text-align: center;">Total</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">Mujeres</td>
                                            <td style=" text-align: center;">{{$conciliadoras->mujeres}}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{$conciliadoras->mujeres}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">Hombres</td>
                                            <td style=" text-align: center;">{{$conciliadores->hombres}}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{$conciliadores->hombres}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">Total</td>
                                            <td style=" text-align: center;">{{ ($conciliadores->hombres + $conciliadoras->mujeres) }}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{($conciliadores->hombres + $conciliadoras->mujeres)}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive">
                                <spam>2. Señale el número de notificadoras y notificadores con los que cuenta el Centro de Conciliación Local/Federal:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;"></th>
                                        <th style="color: #fff;  text-align: center;">Individuales</th>
                                        <th style="color: #fff;  text-align: center;">Colectivos</th>
                                        <th style="color: #fff;  text-align: center;">Mixtos</th>
                                        <th style="color: #fff;  text-align: center;">Total</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">Mujeres</td>
                                            <td style=" text-align: center;">{{$notificadora->mujeres}}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{$notificadora->mujeres}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">Hombres</td>
                                            <td style=" text-align: center;">{{$notificador->hombres}}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{$notificador->hombres}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">Total</td>
                                            <td style=" text-align: center;">{{ ($notificador->hombres + $notificadora->mujeres) }}</td>
                                            <td></td>
                                            <td></td>
                                            <td style=" text-align: center;">{{($notificador->hombres + $notificadora->mujeres)}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <spam>II. Conciliacion en Materia Individual</spam><br>
                            <spam>3. Total de asesorías brindadas: {{$asesorias->asesorias}}</spam>
                            <div class="table-responsive">
                                <spam>4. Indique las solicitudes presentadas, por rubro y por género, como se señala a continuación:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;"></th>
                                        <th style="color: #fff;  text-align: center;">Mujeres</th>
                                        <th style="color: #fff;  text-align: center;">Hombres</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">a. Despido injustificado</td>
                                            <td style=" text-align: center;">{{$despido_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$despido_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">b. Finiquito por rescisión laboral</td>
                                            <td style=" text-align: center;">{{$prestaciones_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$prestaciones_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">c. Derecho de preferencia (antigüedad o ascenso)</td>
                                            <td style=" text-align: center;">{{$preferencia_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$preferencia_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">d. Pago de prestaciones pendientes</td>
                                            <td style=" text-align: center;">{{$recision_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$recision_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">e. Terminación voluntaria de la relación laboral</td>
                                            <td style=" text-align: center;">{{$terminacion_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$terminacion_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">f. Supuestos de Excepción 685-Ter LFT</td>
                                            <td style=" text-align: center;">{{$supuestos_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$supuestos_m->solicitudes}}</td>
                                        </tr>
                                        <tr>
                                            <td style=" text-align: center;">g. Otros</td>
                                            <td style=" text-align: center;">{{$otros_h->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$otros_m->solicitudes}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive">
                                <spam>6. Total de solicitudes que fueron declaradas como incompetencia por parte del Centro de Conciliación Local/Federal:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Antes de admisión (confirmación):</th>
                                        <th style="color: #fff;  text-align: center;">Después de admisión:</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">{{$incopetencia->solicitudes}}</td>
                                            <td style=" text-align: center;">{{$incopetencia->solicitudes}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive">
                                <spam>7. Citatorios emitidos durante el periodo:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">a. Diligenciados por autoridad</th>
                                        <th style="color: #fff;  text-align: center;">b. Diligenciados por trabajador</th>
                                        <th style="color: #fff;  text-align: center;">c. Dilegenciados por ambos</th>
                                        <th style="color: #fff;  text-align: center;">Total</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">{{$citatorios_C->centro}}</td>
                                            <td style=" text-align: center;">{{$citatorios_N->centro}}</td>
                                            <td style=" text-align: center;">{{$citatorios_A->centro}}</td>
                                            <td style=" text-align: center;">{{ $citatorios_N->centro + $citatorios_A->centro + $citatorios_C->centro}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <spam>8. Asuntos archivados por falta de interés durante el periodo: {{$falta_interes->falta_interes}}</spam><br>
                            <spam>9. Total de asuntos en trámite a la fecha (incluyendo asuntos no resueltos de periodos anteriores): {{$tramite->tramite}}</spam><br>
                            <spam>10. Audiencias celebradas durante el periodo: {{$audiencias->audiencias}}</spam><br>

                            <div class="table-responsive">
                                <spam>11. Total de asuntos NO conciliados durante el periodo:</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">a. Derivados de conciliación:</th>
                                        <th style="color: #fff;  text-align: center;">b. Incomparecencia del citado:</th>
¿                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style=" text-align: center;">{{$citatorios_C->centro}}</td>
                                            <td style=" text-align: center;">{{$citatorios_N->centro}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

