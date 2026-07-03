<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- jQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        p {
            text-align: justify;
            padding: 20px 20px;

        }
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
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

        #calendarTurno {
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

        .fc-event-turnos {
            color: #ffff !important;
            background-color: #00CE1C !important;
            border-color: #00CE1C !important;
            cursor: pointer;
        }

        .fc-event-selected {
            border: 2px solid #FFD700 !important;
            box-shadow: 0 0 8px #FFD700;
        }

        .modal-xl-2 {
            max-width: 95% !important;
        }
        .modal-xl{
            max-width: 50% !important;
        }

        .modal-content-2 {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }
        .modal-titulo{
            height: 50px;
            font-size: 20px;
            padding: 10px 10px;
            background-color: #6A0F49 !important;
            color: #fff !important;
            border: none;
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
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="public/assets/images/Logos 2.png" class="img" width="260" height="90">
    </div> 
</nav>
<body onload="validarcheckfolio()">
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                                @if (session()->has('error'))
                                    <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                        <strong>¡Revise los campos!</strong>
                                        {{ session()->get('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                    <h3 class="text-center" style="color:black">Datos Generales</h3>
                                </div>   

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('turnos_publico')}}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="name">Tipo de Trámite <span style="color:red;">(*)</span></label>
                                            <select name="tipo" class="form-control" onchange="blockCalendar();" required>
                                                <option value="">Seleccione</option>
                                                <option value="Solicitud">Solicitudes</option>
                                                <option value="Ratificación">Ratificación</option>
                                                <option value="Asesoría">Asesorías</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El tipo de solicitud es obligatoria.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="name">Estado <span style="color:red;">(*)</span></label>
                                            <select id="estado_citado" class="form-control" name="estado_citado" required>
                                                <option value="">Seleccione</option>
                                                @foreach($estados as $es)
                                                    <option value="{{$es['id']}}">{{$es['nombre']}}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo Estado es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                            <select id="municipio_citado" class="form-control" name="municipio_citado" required>
                                                <option value="">Seleccione</option>
                                                    @foreach($municipios as $mun)
                                                        <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                    @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo municipio o alcaldía es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="name">Delegación/Oficina <span style="color:red;">(*)</span></label>
                                            <select name="delegacion" class="form-control" onchange="blockCalendar();" required>
                                                <option value="">Seleccione</option>
                                                <option value="Morelia">Morelia</option>
                                                <option value="Zitácuaro">Zitácuaro</option>
                                                <option value="Uruapan">Uruapan</option>
                                                <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                <option value="Zamora">Zamora</option>
                                                <option value="Sahuayo">Sahuayo</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                    <h3 class="text-center" style="color:black">Datos Solicitante</h3>
                                </div>   
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name">Nombre del solicitante <span style="color:red;">(*)</span></label>
                                            <input type="text" name="nombre" class="form-control" maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="name">Edad <span style="color:red;">(*)</span></label>
                                            <input type="number" name="edad" class="form-control" maxlength="3" max="150" min="0" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> 
                                            <div class="invalid-feedback">
                                                El campo edad es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-3">
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
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name">Email <span style="color:red;">(*)</span></label>
                                            <input type="email" name="email" maxlength="50" class="form-control" required> 
                                            <div class="invalid-feedback">
                                                El email es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name">Teléfono <span style="color:red;">(*)</span></label>
                                            <input type="number" name="telefono" class="form-control" maxlength="10" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> 
                                            <div class="invalid-feedback">
                                                El teléfono es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                      
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Grupos vulnerables <span style="color:red;">(*)</span></label>
                                            <select name="vulnerables" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Personas con discapacidad">Personas con discapacidad</option>
                                                <option value="Población indígena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas Migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                                <option value="No aplica">No aplica</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Requiere Asesoria/Orientación Juridica <span style="color:red;">(*)</span></label>
                                            <select name="orientacion" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo sexo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Posible caso de excepción 
                                                <button type="button" class="btn btn-primary btn-sm lh-1 fs-6" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                    ?
                                                </button>
                                            </label>

                                            <select name="excepcion" class="form-control" onchange="cambiaExcepcion(this); blockCalendar();" required>
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tipo_caso"  class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="name">Tipo de caso de excepción <span style="color:red;">(*)</span></label>
                                            <select name="tipo_caso" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="Discriminación">Maternidad</option>
                                                <option value="Acoso u hostigamiento sexual">Riesgos de trabajo</option>
                                                <option value="Discriminación">Accidentes de Trabajo</option>
                                                <option value="Discriminación">Invalidez</option>
                                                <option value="Discriminación">Seguros de Vida</option>
                                                <option value="Discriminación">Otras</option>
                                                <option value="Discriminación">Libertad y Asociación Sindical</option>
                                                <option value="Discriminación">Trata Laboral y Trabajo Forzoso</option>
                                                <option value="Discriminación">Trabajo Infantil</option>
                                                <option value="Discriminación">Disputa de titularidad de Contrato Coletivo y Contrato Ley</option>
                                                <option value="Discriminación">Impugnación de estatutos de Sindicato y su Modificación</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="name">Observaciones</label>
                                            <textarea name="conflicto" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                        <input type="hidden" name="hora_turno" id="hora_turno" value="">
                                        <button type="button" id="botonCalendar" class="btn btn-lg btn-custom-morado" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            Seleccionar Fecha y Horario
                                        </button>
                                        <div id="resumenTurno" class="alert alert-info mt-2" style="display:none;"></div>
                                    </div>

                                    <div class="d-flex justify-content-center align-items-center">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
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
                        <div class="modal-header">
                            <h5 class="modal-title">Seleccionar Fecha y Horario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="calendarTurno"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmarTurno" disabled>Confirmar</button>
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
                            <h5 class="text-center modal-titulo" >Aviso Importante</h5>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <p>Los Datos Personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, servirán únicamente para realizar el Procedimiento de Conciliación Individual Prejudicial, 
                            serán tratados conforme lo dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                    
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
                            <h5 class="text-center modal-titulo" >Aviso de Privacidad</h5>
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
        document.getElementById("tipo_caso").style.display = "none";

        function cambiaExcepcion(elemento) {
            var valor = elemento.value;
            var tipoCasoDiv = document.getElementById("tipo_caso");
            var tipoCasoSelect = tipoCasoDiv.querySelector('select[name="tipo_caso"]');
            if (valor == "Si") {
                tipoCasoDiv.style.display = "block";
                tipoCasoSelect.setAttribute("required", "required");
            } else {
                tipoCasoDiv.style.display = "none";
                tipoCasoSelect.removeAttribute("required");
                tipoCasoSelect.value = "";
            }
        }

        function blockCalendar() {
            const delegacion = document.querySelector('select[name="delegacion"]').value;
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
                const delegacion = document.querySelector('select[name="delegacion"]').value;
                const tipo = document.querySelector('select[name="tipo"]').value;
                const excepcion = document.querySelector('select[name="excepcion"]').value;
                const calendarEl = document.getElementById("calendarTurno");

                calendarTurno = new FullCalendar.Calendar(calendarEl, {
                    initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
                    locale: 'es',
                    firstDay: 1,
                    headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                    validRange: {
                        start: new Date().toISOString().split('T')[0],
                    },
                    events: function (fetchInfo, successCallback, failureCallback) {
                        const params = new URLSearchParams({
                            sede: delegacion,
                            tipo: tipo,
                            excepcion: excepcion,
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr,
                        });

                        fetch("{{ url('/api/obtenerTurnosDisponibles') }}?" + params.toString())
                            .then(function (res) { return res.json(); })
                            .then(function (data) { successCallback(data); })
                            .catch(function (err) {
                                console.error('Error al cargar horarios disponibles', err);
                                failureCallback('Error al cargar horarios disponibles');
                            });
                    },
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
                    eventClick: function (info) {
                        const estado = info.event.extendedProps.estado;
                        if (estado !== 'disponible' && estado !== 'turnos') {
                            alert('Este horario no está disponible. Por favor seleccione otro.');
                            return;
                        }

                        document.querySelectorAll('.fc-event-selected').forEach(function (el) {
                            el.classList.remove('fc-event-selected');
                        });
                        info.el.classList.add('fc-event-selected');

                        turnoSeleccionado = info.event;
                        document.getElementById("confirmarTurno").removeAttribute("disabled");
                    },
                    eventDidMount: function (info) {
                        info.el.classList.add('fc-event-' + info.event.extendedProps.estado);
                    },
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

        //Cargar municipios conforme al estado seleccionado
        document.getElementById("estado_citado").addEventListener("change", function () {
            const estadoId = this.value;
            const municipioSelect = document.getElementById("municipio_citado");

            municipioSelect.innerHTML = '<option value="">Seleccione</option>';

            if (!estadoId) return;

            fetch("{{ url('/munCitado') }}/" + estadoId)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    data.forEach(function (mun) {
                        const option = document.createElement("option");
                        option.value = mun.id;
                        option.textContent = mun.nombre;
                        municipioSelect.appendChild(option);
                    });
                })
                .catch(function (err) {
                    console.error('Error al cargar municipios', err);
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