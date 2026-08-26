<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Ionicons -->
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>

    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
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
            --color-guinda: #6A0F49;
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
            font-size: 18px;
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
                        <h4 class="m-0 text-center fs-5"><i class="bi bi-calendar-check me-2"></i>Genera tu Cita</h4>
                    </div>
                    <div class="card-body p-4"> 

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('turnos_publico')}}">
                                @csrf
                                <div id="datos_formulario">
                                
                                <!-- SECCIÓN 1: DATOS DEL TRABAJADOR -->
                                <div class="section-title-banner mt-3">
                                    <i class="bi bi-border-width me-2"></i>1. Datos Generales
                                </div>

                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Selecciona el tipo de trámite que deseas realizar <span style="color:red;">(*)</span></label>
                                            <select name="tipo" class="form-control" onchange="blockCalendar();" required>
                                                <option value="">Seleccione</option>
                                                <option value="Asesoría">Asesoría</option>
                                                <option value="Ratificación">Ratificación</option>
                                                <option value="Solicitud">Solicitud</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El tipo de solicitud es obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Municipio donde se localiza la fuente de empleo/persona a citar <span style="color:red;">(*)</span></label>
                                            <select id="municipio_citado" class="form-control" name="municipio_citado" required>
                                                <option value="">Seleccione</option>
                                                    @foreach($municipios as $mun)
                                                        <option value="{{$mun['id']}}" data-delegacion-id="{{ $mun['delegacion_id'] }}">
                                                        {{ $mun['nombre'] }}</option>
                                                    @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo municipio o alcaldía es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3 ">
                                            <span>Oficina del CCL a la que te corresponde acudir:</span><br>
                                            <span id="modulo_delegacion" style="font-weight: bold;"></span>
                                            <input type="hidden" name="delegacion" id="delegacion" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="section-title-banner mt-3">
                                    <i class="bi bi-person-badge me-2"></i>2. Datos del Solicitante
                                </div>   
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Nombre del solicitante <span style="color:red;">(*)</span></label>
                                            <input type="text" name="nombre" class="form-control" maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <div class="form-group mb-3">
                                            <label for="name">Edad <span style="color:red;">(*)</span></label>
                                            <input type="number" name="edad" class="form-control" maxlength="3" max="150" min="0" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> 
                                            <div class="invalid-feedback">
                                                El campo edad es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <div class="form-group mb-3">
                                            <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                            <select name="sexo" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="H">Hombre</option>
                                                <option value="M">Mujer</option>
                                                <option value="NB">No Binarios</option>
                                                <option value="Otros">Otros</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo sexo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Correo electrónico <span style="color:red;">(*)</span></label>
                                            <input type="email" name="email" maxlength="50" class="form-control" required> 
                                            <div class="invalid-feedback">
                                                El email es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Teléfono/Celular <span style="color:red;">(*)</span></label>
                                            <input type="number" name="telefono" class="form-control" maxlength="10" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> 
                                            <div class="invalid-feedback">
                                                El teléfono es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                      
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">¿Perteneces a alguno de estos grupos de atención prioritaria?<span style="color:red;">(*)</span></label>
                                            <select name="vulnerables" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Personas con discapacidad">Personas con discapacidad</option>
                                                <option value="Población indígena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                                <option value="No aplica">A ninguno</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="orientacion" id="orientacion" value="No">
                                    <input type="hidden" name="excepcion" id="excepcion" value="No">
                                    
                                    <div id="tipo_caso"  class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">¿El motivo de tu solicitud se debe a alguno de los siguientes casos? <span style="color:red;">(*)</span></label>
                                            <select name="tipo_caso" class="form-control" onchange="cambiaExcepcion(this); blockCalendar();" required>
                                                <option value="">Seleccione</option>
                                                <option value="Ninguno" style="font-weight: bold;">No, ninguno</option>
                                                <optgroup label="Discriminación en el empleo y ocupación por:">
                                                    <option value="Acoso u hostigamiento sexual">Acoso/Hostigamiento Sexual</option>
                                                    <option value="Discriminación">Condición social</option>
                                                    <option value="Discriminación">Embarazo</option>
                                                    <option value="Discriminación">Raza</option>
                                                    <option value="Discriminación">Razones de sexo</option>
                                                    <option value="Discriminación">Religión</option>
                                                    <option value="Discriminación">Orientación sexual</option>
                                                    <option value="Discriminación">Origen étnico</option>
                                                </optgroup>
                                                    <option value="Designacion" style="font-weight: bold;">Designación de beneficiarios por Muerte</option>
                                                <optgroup label="Prestaciones de seguridad social por:">
                                                    <option value="Prestaciones">Accidentes de trabajo</option>
                                                    <option value="Prestaciones">Enfermedades</option>
                                                    <option value="Prestaciones">Guarderias</option>
                                                    <option value="Prestaciones">Invalidez</option>
                                                    <option value="Prestaciones">Maternidad</option>
                                                    <option value="Prestaciones">Riesgos de trabajo</option>
                                                    <option value="Prestaciones">Prestaciones en especie</option>
                                                    <option value="Prestaciones">Vida</option>
                                                <optgroup label="Tutela de derechos fundamentales y libertades públicas, ambos de carácter laboral relacionados con:">
                                                    <option value="Libertades_publicas">Libertad de asociación</option>
                                                    <option value="Libertades_publicas">Libertad sindical</option>
                                                    <option value="Libertades_publicas">Reconocimiento efectivo de la negociacion colectiva</option>
                                                    <option value="Libertades_publicas">Trabajo infantil</option>
                                                    <option value="Libertades_publicas">Trabajo laboral forzoso y obligatorio</option>
                                                <optgroup label="Disputa de titularidad de:">
                                                    <option value="Titularidad">Contratos colectivos</option>
                                                    <option value="Titularidad">Contratos ley</option>
                                                </optgroup>
                                                    <option value="Titularidad" style="font-weight: bold;">Impugnación de los estatutos de los sindicatos o su modificación</option>
                                                    
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="name">Describe brevemente el motivo de tu solicitud (Máximo 500 caracteres): </label>
                                            <textarea name="conflicto" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" maxlength="500"></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 d-flex align-items-end">
                                
                                        <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                        <input type="hidden" name="hora_turno" id="hora_turno" value="">
                                        <button type="button" id="botonCalendar" class="btn btn-guinda w-100 py-2" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            <i class="bi bi-calendar-week"></i> Seleccionar Fecha y Horario
                                        </button>
                                        <div id="resumenTurno" class="alert alert-info mt-2" style="display:none;"></div>
                                    
                                    </div>

                                    <div class="text-center pt-3 border-top">
                                        <button type="submit" class="btn btn-oro btn-lg px-5 me-2">
                                            <i class="bi bi-box-arrow-down me-1"></i> Guardar Cita
                                        </button>
                                    
                                    </div>
                                </div>
                            </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Posibles Casos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            La Ley Federal del Trabajo en el articulo 685-Ter establece que no estas obligado a agotar la etapa conciliatoria en estos supuestos<br>
                            -Discriminación<br>
                            -Acoso u hostigamiento sexual<br>
                            -Designación de beneficiarios<br>
                            -Prestaciones de Seguridad Social
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">

                        <div class="modal-header card-header-guinda py-2">
                            <h5 class="modal-title text-white fs-6">
                                <i class="bi bi-clock me-2"></i>
                                Seleccionar Horario Disponible
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"
                                    aria-label="Close">
                            </button>
                        </div>

                        <div class="modal-body">
                            <div id="calendarTurno"></div>
                        </div>

                        <div class="modal-footer bg-light py-2">
                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    id="confirmarTurno"
                                    disabled>
                                Confirmar
                            </button>
                        </div>

                    </div>
                </div>
            </div>

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
                            <b>¡No te dejes engañar!</b><br><br>
                            Proteger tus derechos laborales es nuestra prioridad.<br>
                            </p>
                         
                    
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
        

    <script>
        

        function cambiaExcepcion(elemento) {
            var valor = elemento.value;
            var inputExcepcion = document.getElementById('excepcion');
    
            if (inputExcepcion) {
                inputExcepcion.value = (valor === 'Ninguno') ? 'No' : 'Si';
            }
        }

        function blockCalendar() {
            const delegacion = document.getElementById('delegacion').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            const boton = document.getElementById("botonCalendar");

            if (delegacion !== "" && tipo !== "") {
                boton.removeAttribute("disabled");
            } else {
                boton.disabled = true;
            }

            limpiarTurnoSeleccionado();
        }

        function limpiarTurnoSeleccionado() {
            turnoSeleccionado = null;
            document.getElementById("fecha_turno").value = "";
            document.getElementById("hora_turno").value = "";
            document.getElementById("resumenTurno").style.display = "none";
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");
        }

        function pad(numero) {
            return String(numero).padStart(2, "0");
        }

        function formatoFechaLocal(fecha) {
            return fecha.getFullYear() + "-" + pad(fecha.getMonth() + 1) + "-" + pad(fecha.getDate());
        }

        function formatoHoraLocal(fecha) {
            return pad(fecha.getHours()) + ":" + pad(fecha.getMinutes()) + ":" + pad(fecha.getSeconds());
        }

        let calendarTurno = null;
        let turnoSeleccionado = null;

        document.getElementById("botonCalendar").addEventListener("click", function () {
            if (calendarTurno) {
                calendarTurno.destroy();
                calendarTurno = null;
            }
            turnoSeleccionado = null;
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");

            // Esperar a que la animación del modal termine antes de renderizar FullCalendar
            setTimeout(function () {
                const delegacion = document.getElementById('delegacion').value;
                const tipo = document.querySelector('select[name="tipo"]').value;
                const calendarEl = document.getElementById("calendarTurno");
                const inputExcepcion = document.getElementById('excepcion');
                const excepcion = inputExcepcion.value;
                calendarTurno = new FullCalendar.Calendar(calendarEl, {

                    // Vista inicial igual al primer calendario
                    initialView: 'dayGridWeek',

                    locale: 'es',
                    firstDay: 1,

                    // Botones del calendario
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridWeek,timeGridDay'
                    },

                    // No permitir fechas anteriores a hoy
                    validRange: {
                        start: new Date().toISOString().split('T')[0]
                    },

                    // Formato de hora
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit'
                    },

                    // Obtener eventos
                    events: function (fetchInfo, successCallback, failureCallback) {

                        const params = new URLSearchParams({
                            sede: delegacion,
                            tipo: tipo,
                            excepcion: excepcion,
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        });

                        fetch("{{ url('/api/obtenerTurnosDisponibles') }}?" + params.toString())
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => {
                                console.error(
                                    'Error al cargar horarios disponibles:',
                                    error
                                );

                                failureCallback(
                                    'Error al cargar horarios disponibles'
                                );
                            });
                    },

                    // Seleccionar horario
                    eventClick: function (info) {

                        const estado = info.event.extendedProps.estado;

                        // Sólo se pueden seleccionar estos estados
                        if (estado !== 'disponible' && estado !== 'turnos') {
                            alert(
                                'Este horario no está disponible. Por favor seleccione otro.'
                            );
                            return;
                        }

                        // Quitar selección anterior
                        document
                            .querySelectorAll('.fc-event-selected')
                            .forEach(evento => {
                                evento.classList.remove('fc-event-selected');
                            });

                        // Marcar evento seleccionado
                        info.el.classList.add('fc-event-selected');

                        turnoSeleccionado = info.event;

                        document
                            .getElementById('confirmarTurno')
                            .removeAttribute('disabled');
                    },

                    // Aplicar colores según estado
                    eventDidMount: function (info) {
                        const estado = info.event.extendedProps.estado;

                        if (estado) {
                            info.el.classList.add(`fc-event-${estado}`);
                        }
                    }
                });


                calendarTurno.render();
                setTimeout(function () {
                    if (calendarTurno) calendarTurno.updateSize();
                }, 100);
            }, 400);
        });

        document.getElementById("confirmarTurno").addEventListener("click", function () {
            if (!turnoSeleccionado) {
                alert("Por favor selecciona un horario disponible.");
                return;
            }

            const inicio = turnoSeleccionado.start;
            document.getElementById("fecha_turno").value = formatoFechaLocal(inicio);
            document.getElementById("hora_turno").value = formatoHoraLocal(inicio);

            const resumen = document.getElementById("resumenTurno");
            resumen.textContent = "Turno seleccionado: " + formatoFechaLocal(inicio) + " a las " + pad(inicio.getHours()) + ":" + pad(inicio.getMinutes());
            resumen.style.display = "block";

            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarModal')).hide();
        });

        document.getElementById("form_roles").addEventListener("submit", function (e) {
            const fecha = document.getElementById("fecha_turno").value;
            const hora = document.getElementById("hora_turno").value;
            if (!fecha || !hora) {
                e.preventDefault();
                alert("Debes seleccionar la fecha y el horario del turno antes de guardar.");
            }
        });

        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('municipio_citado');
            const delegacionModulo = document.getElementById('modulo_delegacion');

            const delegaciones = {
                1: ['Morelia'],
                2: ['Zitácuaro'],
                3: ['Uruapan'],
                4: ['Lázaro Cárdenas'],
                5: ['Zamora'],
                6: ['Sahuayo']
            };

            municipioSelect.addEventListener('change', function () {
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                const delegacionId = selectedOption.getAttribute('data-delegacion-id');

                // Limpia el select de delegación
                delegacionSelect.innerHTML = '<option value="">Seleccione</option>';

                if (delegacionId && delegaciones[delegacionId]) {
                    delegaciones[delegacionId].forEach(delegacion => {
                        delegacionSelect.value = delegacion;
                        delegacionModulo.textContent = delegacion;
                    });
                }

                blockCalendar();
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