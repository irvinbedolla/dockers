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
                            <div class="table-responsive">
                                <spam>Solicitudes</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Usuario</th>
                                        <th style="color: #fff;  text-align: center;">Solicitudes</th>
                                    </thead>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <spam>Ratificaciones</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff; text-align: center;">Usuario</th>
                                        <th style="color: #fff; text-align: center;">Ratificaciones</th>
                                        <th style="color: #fff; text-align: center;">Monto</th>
                                    </thead>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <spam>Audiencias</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Usuario</th>
                                        <th style="color: #fff;  text-align: center;">Audiencia</th>
                                        <th style="color: #fff;  text-align: center;">Monto</th>
                                    </thead>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <spam>Audiencias Colectivas</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Usuario</th>
                                        <th style="color: #fff;  text-align: center;">Audiencia</th>
                                    </thead>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <spam>Pagos</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Usuario</th>
                                        <th style="color: #fff;  text-align: center;">Pagos</th>
                                        <th style="color: #fff;  text-align: center;">Total</th>
                                    </thead>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <spam>Asesorias</spam>
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;  text-align: center;">Usuario</th>
                                        <th style="color: #fff;  text-align: center;">Total</th>
                                    </thead>
                                </table>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

