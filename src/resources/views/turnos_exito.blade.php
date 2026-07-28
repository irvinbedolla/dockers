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
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
    
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
            <img src="{{ asset('assets/images/Logos 2.png') }}" class="img" width="260" height="90">
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
            background-color: #8a959e !important;
            border-color: #8a959e !important;
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

        .modal-xl.grande {
            max-width: 95% !important;
        }
        .modal-xl{
            max-width: 40% !important;
        }

      .modal-content.modal-grande {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
            font-size: 18px;
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
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    @yield('page_css')
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="{{ asset('assets/images/Logos 2.png') }}" class="img" width="260" height="90">
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
                                <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                    <h3 class="text-center" style="color:black">Cita Agendada</h3>
                                </div>   
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <br><strong>¡Turno agendado con éxito!</strong>
                                    
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
                                        <i class="bi bi-record"></i>•Nombre, CURP y domicilio dentro del lugar de residencia del Centro de Conciliación al que acuda.<br>
                                        <i class="bi bi-record"></i>•Nombre de la persona, sindicato o empresa a quien se citará para la conciliación prejudicial.<br>
                                        <i class="bi bi-record"></i>•Domicilio para notificar a la persona, sindicato o empresa a quien se citará.<br>
                                        <i class="bi bi-record"></i>•Objeto de la cita de conciliación.<br>
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

        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('municipio_citado');

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
                        const option = document.createElement('option');
                        option.value = delegacion;
                        option.textContent = delegacion;
                        delegacionSelect.appendChild(option);
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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>

    <!-- Template JS File -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')