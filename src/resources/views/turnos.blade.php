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
        #calendarTurno .fc-event-selected {
            border: 3px solid var(--color-oro) !important;
            box-shadow: 0 0 10px rgba(206, 168, 69, .8) !important;
            opacity: 1 !important;
            filter: none !important;
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
            font-size: 18px;
        }

        /* Títulos de modal */
        .modal-titulo {
            background-color: var(--color-guinda);
            color: #fff;
            padding: 10px;
            font-size: 20px;
        }

        /* Aviso de cobros indebidos */
        .aviso-engano {
            margin: 6px 0 10px;
            text-align: center;
            font-size: clamp(26px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.15;
            color: var(--color-guinda);
        }
        .aviso-denuncia {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            margin: 22px auto 4px;
            text-align: center;
        }
        .aviso-denuncia__titulo {
            font-size: 18px;
            font-weight: 700;
            color: #2E3C3D;
        }
        .aviso-denuncia__numero {
            font-size: clamp(56px, 9vw, 80px);
            font-weight: 800;
            line-height: 1;
            letter-spacing: .04em;
            color: #C8102E;
            text-decoration: none;
        }
        .aviso-denuncia__numero:hover,
        .aviso-denuncia__numero:focus { color: #A00D25; text-decoration: none; }
        .aviso-denuncia__opcion {
            margin-top: 2px;
            font-size: 20px;
            font-weight: 800;
            color: #C8102E;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .aviso-denuncia__dependencia {
            font-size: 14px;
            color: #5E6E6F;
        }
        .aviso-buzon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            padding: 10px 20px;
            border-radius: 999px;
            background: #E8730C;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(232, 115, 12, .3);
        }
        .aviso-buzon:hover,
        .aviso-buzon:focus { background: #C95F05; color: #fff; text-decoration: none; }
        .aviso-buzon:focus-visible { outline: 3px solid #FFD3A8; outline-offset: 2px; }

        /* Loader */
        .loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: url("{{ asset('assets/images/pageLoader.gif') }}")
                50% 50% no-repeat rgba(255, 255, 255, .85);
        }
        @media (max-width: 768px) {

            #calendarTurno {
                width: 100% !important;
            }

            #calendarTurno .fc-list,
            #calendarTurno .fc-list-table {
                width: 100% !important;
            }

            #calendarTurno .fc-list-table {
                table-layout: fixed !important;
            }

            #calendarTurno .fc-list-event {
                width: 100% !important;
                box-sizing: border-box !important;
                opacity: 1 !important;
                filter: none !important;
                transform: translateZ(0);
            }

            #calendarTurno .fc-list-event td {
                box-sizing: border-box !important;
            }

            #calendarTurno .fc-list-event::before,
            #calendarTurno .fc-list-event::after,
            #calendarTurno .fc-list-event td::before,
            #calendarTurno .fc-list-event td::after {
                display: none !important;
                content: none !important;
            }

        }

    </style>
    @include('turnos._cita_estilos')
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
<body>
    <main class="container mb-5">
        <div id="app">
            <section class="section">
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-guinda py-3">
                        <h4 class="m-0 text-center fs-5"><i class="bi bi-calendar-check me-2"></i>Genera tu Cita</h4>
                    </div>
                    <div class="card-body p-4"> 

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{--
                                Formulario de cita en línea. Mismos campos y nombres que antes
                                (HomeController::turnos_publico no cambió); sólo se reorganizó en
                                cuatro pasos con ayudas, validación en pantalla y confirmación del
                                correo, que es a donde llega el acuse.
                            --}}
                            @include('turnos._cita_formulario', ['modo' => 'publico', 'accion' => route('turnos_publico')])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('turnos._cita_modales')

    <!-- Modal de Aviso -->
<div class="modal fade" id="avisoModal" tabindex="-1" aria-labelledby="avisoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            
            <div class="modal-body">
                    
                <div class="row">

                   <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <h5 class="text-center modal-titulo" >¡ATENCIÓN!</h5>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group" >
                          
                            <p class="text-center">Todos los servicios que brinda este Centro de Conciliación Laboral son completamente gratuitos<br><br>
                            Ningún servidor público, asociación, sindicato o gestor particular está autorizado para solicitar dinero o gratificaciones para agendar una cita, iniciar una solicitud o realizar tus trámites de conciliación.<br><br>
                            Si detectas o eres víctima de cualquier cobro indebido, ¡denúncialo inmediatamente por nuestros medios oficiales!<br><br>
                            </p>
                            <p class="aviso-engano">¡No te dejes engañar!</p>
                            <p class="text-center mb-0">Proteger tus derechos laborales es nuestra prioridad.</p>

                            {{-- Línea de denuncia. En celular el número se puede tocar para llamar. --}}
                            <div class="aviso-denuncia">
                                <span class="aviso-denuncia__titulo">Centro de Atención Telefónica</span>
                                <a class="aviso-denuncia__numero" href="tel:070" aria-label="Llamar al 070, opción 1">070</a>
                                <span class="aviso-denuncia__opcion">Opción 1</span>
                                <span class="aviso-denuncia__dependencia">Secretaría de Contraloría del Estado de Michoacán de Ocampo</span>
                                <a class="aviso-buzon" href="https://intra.secoem.michoacan.gob.mx/denuncias" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-mailbox2" aria-hidden="true"></i> Denuncia en el Buzón Naranja
                                </a>
                            </div>
                         
                    
                        </div>
                    </div>
                
                </div> 
                    
        
            </div> 
            
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            
        </div>
    </div>
</div>

   
    <div class="modal fade" id="seguridadModal" tabindex="-1" aria-labelledby="seguridadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

            
                <div class="modal-body">
                    
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <h5 class="text-center modal-titulo" > <i class="bi bi-shield-lock me-2"></i>Aviso de Privacidad</h5>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <p>Los Datos Personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, servirán únicamente para realizar el Procedimiento de Conciliación Individual Prejudicial, 
                            serán tratados conforme lo dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                            <p>Los datos considerados sensibles no serán recabados, ni tratados, ni se realizaran transferencias de datos personales, salvo aquellos que no requieran el consentimiento de los titulares y que 
                                sean necesarios para atender su solicitud o requerimientos de información realizados por autoridad competente, siempre y cuando se encuentren debidamente fundados y motivados; lo anterior, de 
                                conformidad con los artículos 22, 66 y 70 de la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.</p>
                            <p>Los datos personales que sean recabados serán empleados con los fines siguientes:</p>
                            <ul>
                                <li>Registrar al usuario en la plataforma digital denominada SICONCILIO (Sistema Integral para la Conciliación), dar seguimiento y trámite a su solicitud.</li><br>
                                <li>Administrar la información y datos del solicitante para efectuar el Procedimiento de Conciliación Prejudicial obligatorio y las notificaciones que deriven del mismo.</li><br>
                                <li>Generar información estadística y de control, en la que sus datos personales serán disociados de la información estadística para que no sea posible identificar a los titulares.</li><br>
                                <li>Establecer comunicación con los trabajadores y patrones por correo electrónico, por escrito, mediante correo ordinario o por teléfono, sobre aspectos relacionados con las 
                                    fases y etapas del procedimiento de conciliación individual.</li>
                            </ul>
                        </div>
                    </div>
                </div> 
                    
            
            </div> 
            
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aceptar</button>
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
        

    @include('turnos._cita_scripts', ['modo' => 'publico'])


    <script>
        $(document).ready(function() {
            

            $('#tipo').on('change', function() {
                var tipoSeleccionado = $(this).val();
                
                if (tipoSeleccionado === 'Ratificación') {

                    $('#calidad').fadeIn();
                } else {

                    $('#calidad').fadeOut();
                    $('#puesto').val(''); 
                }
            });
            $('#puesto').on('change', function() {
                var tipoSeleccionado = $(this).val();
                
                if (tipoSeleccionado === 'empleador') {

                    $('#si_ratificacion').fadeIn();
                } else {

                    $('#si_ratificacion').fadeOut();
                    $('#folio_patronal').val(''); 
                }
            });

            // 2. Detectar cuando cambia el select de "folio patronal"
            $('#folio_patronal').on('change', function() {
                var tieneFolio = $(this).val();
                
                if (tieneFolio === 'si') {
                    window.location.href = '{{ route('create_cita', ['origen' => 'externo']) }}';
                }
            });

        });
    </script>
@section('scripts')
    <script src="{{ asset('assets/js/validaciones-ratificacion.js') }}"></script> 
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
@endsection

    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nicescroll.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')