<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Ionicons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Libraries CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet">
    
    <!-- jQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    

    <style>
       
        :root {
            --color-guinda: #496163;
            --color-guinda-dark: #530c3a;
            --color-oro: #CEA845;
            --color-oro-dark: #b59238;
            --color-gris-bg: #f8f9fa;
        }

        /* Página */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-gris-bg);
            color: #333;
            padding-top: 100px;
        }

        /* Navbar */
        .navbar-institucional {
            background-color: #fff;
            border-bottom: 3px solid var(--color-oro);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        /* Encabezados */
        .card-header-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            font-weight: 600;
        }

        .section-title-banner {
            background-color: #f1f3f5;
            border-left: 4px solid var(--color-guinda);
            padding: 8px 15px;
            margin-bottom: 20px;
            font-weight: 700;
            color: var(--color-guinda);
        }

        /* Botones */
        .btn-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            border: none;
        }

        .btn-guinda:hover,
        .btn-guinda:focus {
            background-color: var(--color-guinda-dark);
            color: #fff;
        }

        .btn-oro {
            background-color: var(--color-oro);
            color: #fff;
            border: none;
            font-weight: 500;
        }

        .btn-oro:hover,
        .btn-oro:focus {
            background-color: var(--color-oro-dark);
            color: #fff;
        }

        /* =========================
        FULLCALENDAR
        ========================= */

        #calendarTurno {
            width: 100%;
            min-height: 520px;
        }

        /* Eventos */
        .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
        }

        /* Disponible */
        .fc-event-disponible,
        .fc-event-turnos {
            background-color: #26c03a !important;
            border-color: #26c03a !important;
            color: #fff !important;
            cursor: pointer;
        }

        /* Expirado */
        .fc-event-expirado {
            background-color: #8a959e !important;
            border-color: #8a959e !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Día inhábil */
        .fc-event-inhabil {
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Ocupado */
        .fc-event-ocupado {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Evento seleccionado */
        .fc-event-selected {
            border: 3px solid var(--color-oro) !important;
            box-shadow: 0 0 10px rgba(206, 168, 69, .8);
        }

        /* Modal del calendario */
        .modal-xl.grande {
            max-width: 95% !important;
        }

        .modal-content.modal-grande {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }

        /* Títulos de modal */
        .modal-titulo {
            background-color: var(--color-guinda);
            color: #fff;
            padding: 10px;
            font-size: 20px;
        }

        /* Loader */
        .loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: url("{{ asset('assets/images/pageLoader.gif') }}")
                50% 50% no-repeat rgba(255, 255, 255, .85);
        }
        


    </style>
    @livewireStyles

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

</head>
<nav class="navbar navbar-expand-lg navbar-light navbar-institucional fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="65">
            </a>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">
                Centro de Conciliación Laboral del Estado de Michoacán
            </span>
        </div>
    </nav>
<body onload="validarcheckfolio()">
    <main class="container mb-5">
        <div id="app">
            <section class="section">
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-guinda py-3">
                        <h4 class="m-0 text-center fs-5"><i class="bi bi-check-square me-2"></i>Cita Agendada</h4>
                    </div>
                    <div class="card-body p-4"> 
                                 
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check-circle-fill text-success fs-2 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-0 fw-bold">¡Turno agendado con éxito!</h5>
                                        <p class="mb-0 text-muted small">Por favor tome captura o conserve los datos de su cita.</p>
                                    </div>
                                </div>
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <strong>Folio:</strong> {{ session('folio') }} <br>
                                        <strong>Fecha:</strong> {{ session('fecha') }} <br>
                                        <strong>Hora:</strong> {{ session('hora') }} <br>
                                        <strong>Delegación:</strong> {{ session('delegacion') }}<br>
                                        <strong>Modulo:</strong> {{ session('modulo') }}
                                    </div>
                                    
                                    <strong>Recuerda presentarte con identificación oficial vigente, además de llevar la siguiente información:</strong>
                                    <p class="mb-0">
                                        <i class="bi bi-record"></i>Nombre, CURP y domicilio dentro del lugar de residencia del Centro de Conciliación al que acuda.<br>
                                        <i class="bi bi-record"></i>Nombre de la persona, sindicato o empresa a quien se citará para la conciliación prejudicial.<br>
                                        <i class="bi bi-record"></i>Domicilio para notificar a la persona, sindicato o empresa a quien se citará.<br>
                                        <i class="bi bi-record"></i>Objeto de la cita de conciliación.<br>
                                    </p>
                                
                                    En caso de acudir en representación de una persona física, presenta: Identificación oficial vigente, original o copia certificada del poder notarial, <br>
                                    o carta poder firmada por el otorgante ante dos testigos, adjuntando copia de las identificaciones de quienes intervienen.<br><br>
                                    
                                    En caso de acudir en representación de una persona moral, presenta: Identificación oficial vigente, original o copia certificada del instrumento <br>
                                    notarial, o carta poder firmada y otorgada ante dos testigos, anexando el original o copia certificada del instrumento notarial que acredite que <br>
                                    la persona que otorga el poder está legalmente autorizada para ello.<br><br>
                                    
                                    Su turno se ha asegurado correctamente. Por favor, asegúrese de guardar su número de folio para cualquier duda o aclaración.<br><br>
                                    
                                    <strong>¡Le esperamos!</strong><br><br>
                                    <div class="col-xs-12 col-sm-4 col-md-2 ">
                                        <a href="{{ route('citas') }}" class="btn btn-secondary"  style="width: 100%">Generar Nueva Cita</a>

                                    </div>
                                    
                                </div>

                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </section>
        
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const avisoModalEl = document.getElementById('avisoModal');
        const seguridadModalEl = document.getElementById('seguridadModal');

        const avisoModal = bootstrap.Modal.getOrCreateInstance(avisoModalEl);
        const seguridadModal = bootstrap.Modal.getOrCreateInstance(seguridadModalEl);

        // Mostrar el primer modal
        avisoModal.show();

        avisoModalEl.addEventListener('hidden.bs.modal', function () {
            seguridadModal.show();
        });

        seguridadModalEl.addEventListener('hidden.bs.modal', function () {

            document.body.classList.remove('modal-open');

            document.body.style.removeProperty('padding-right');

            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop){
                backdrop.remove();
            });

        });

    });
</script>
        

    

@section('scripts')
    <script src="public/assets/js/validaciones-ratificacion.js"></script> 
    <script src="public/assets/js/poderes/general.js"></script>
@endsection

    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')