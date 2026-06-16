<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="../public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="../public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="../public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="../public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('../public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
        .resultado {
            background-color: red;
            color: white;
            font-weight: bold;
        }
        .resultado.ok {
            background-color: green;
        }

        .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            cursor: pointer;
        }

        #calendar {
            width: 100%;
            min-height: 500px;
        }

        .fc-event-disponible {
            color: #ffff !important;
            background-color: #00CE1C !important;
            border-color: #00CE1C !important;
            cursor: pointer;
        }

        .fc-event-expirado {
            color: #ffff !important;
            /*background-color: #F0DF24 !important;
            border-color: #F0DF24 !important;*/
            background-color: #F59727 !important;
            border-color: #F59727 !important;
            cursor: not-allowed;
        }

        .fc-event-inhabil {
            color: #ffff !important;
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            cursor: not-allowed;
        }

        .fc-event-ocupado {
            color: #ffff !important;
            background-color: #DA0909 !important;
            border-color: #DA0909 !important;
            cursor: not-allowed;
        }

        .fc-event-selected {
            border: 2px solid #FFD700 !important;
            box-shadow: 0 0 8px #FFD700;
        }

        .modal-xl {
            max-width: 95% !important;
        }

        .modal-content {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }

        .btn-custom-morado {
            height: 50px;
            font-size: 12px;
            padding: 5px 10px;
            background-color: #6A0F49 !important;
            color: #fff !important;
            border: none;
        }
        .btn-custom-morado:hover, .btn-custom-morado:focus {
            background-color: #530c3a !important;
            color: #fff !important;
        }
    </style>
    @livewireStyles

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="../public/assets/css/components.css">
    @yield('page_css')
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="../public/assets/images/Logos 2.png" class="img" width="260" height="90">
    </div> 
</nav>
<body>
    <main>
        <div class="container">
            <br><br><br><br>
        </div>
        <div id="app">  
            <section class="section"> 
                <div class="section-body">
                    <div class="row"> 
                        <div class="col-lg-12" >
                            <div class="card">
                                <div class="card-body">
                                        @if(session()->has('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>¡Registro correcto!</strong>
                                                {{ session()->get('success') }}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        @endif

                                        <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                                        @if (session()->has('error'))
                                            <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                                <strong>¡Revise los campos!</strong>
                                                {{ session()->get('error') }}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        @endif
                                        <div style="background-color:#D2D3D5;">
                                            <h3 class="text-center" style="color:black">Cita Pendiente</h3>
                                        </div>    
                                        
                                        <br><br>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Nombre</span></label>
                                                    <input type="text" value="{{$citas->nombre}}" class="form-control"> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Asunto</span></label>
                                                    <input type="text" value="{{$citas->descripcion}}" class="form-control"> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">Fecha</label>
                                                    <input type="text" class="form-control" value="{{$citas->fecha}}" >
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">Hora</label>
                                                    <input type="text" class="form-control"  value="{{$citas->hora}}">
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">Lugar</label>
                                                    <input type="text" class="form-control"  value="{{$citas->unidad}}">
                                                </div>
                                            </div>
            
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal para la captura de la ine-->
                <div class="modal fade" id="helpModal" aria-labelledby="helpModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-height: 80vh;">
                      <div class="modal-content" style="height: 100%;">
                        <div class="modal-header">
                          <h5 class="modal-title" id="helpModalLabel">Ubicación de núm. de identificación</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body text-center">
                          <img src="./public/assets/images/capturaIne.png" alt="Instrucciones" class="img-fluid">
                        </div>
                      </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<div id="crear_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="public/assets/js/validaciones-ratificacion.js"></script> 
    <script src="public/assets/js/poderes/general.js"></script>
@endsection

    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <!--script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script-->
    @yield('page_js')


    @yield('scripts')
    

    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>