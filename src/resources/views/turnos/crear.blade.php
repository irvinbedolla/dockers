@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Turnos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
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


                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('turnos_guardar_nuevo')}}">
                                @csrf
                                <div class="row">
                                    <h3 class="text-center">Datos generales</h3>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Selecciona el tipo de trámite que se desea realizar <span style="color:red;">(*)</span></label>
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
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Municipio donde se localiza la fuente de empleo/persona a citar <span style="color:red;">(*)</span></label>
                                                <select id="municipio_solicitante" class="form-control" name="municipio_solicitante" required>
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
                                            <div class="form-group">
                                                <label for="name">Oficina del CCL a la que le corresponde acudir:</label>
                                                <p><span id="modulo_delegacion" style="font-weight: bold;"></span></p>
                                                <input type="hidden" name="delegacion" id="delegacion" value="">
                                            </div>
                                        </div>
                                        <!--div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Delegación/Oficina<span style="color:red;">(*)</span></label>
                                                <select id="delegacion" name="delegacion" class="form-control" onchange="blockCalendar();" required>
                                                    <option value="">Seleccione</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div-->

                                    <h3 class="text-center">Datos solicitante</h3>


                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Nombre del Solicitante<span style="color:red;">(*)</span></label>
                                            <input type="text" name="nombre" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <div class="form-group">
                                            <label for="name">Edad<span style="color:red;">(*)</span></label>
                                            <input type="number" name="edad" class="form-control" required> 
                                            <div class="invalid-feedback">
                                                El campo edad es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <div class="form-group">
                                            <label for="name">Sexo<span style="color:red;">(*)</span></label>
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
                                        <div class="form-group">
                                            <label for="name">Correo electrónico <span style="color:red;">(*)</span></label>
                                            <input type="text" name="correo" maxlength="30" class="form-control"  required> 
                                            <div class="invalid-feedback">
                                                El campo correo es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Teléfono/Celular <span style="color:red;">(*)</span></label>
                                            <input type="number" name="telefono" class="form-control" maxlength="10" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> 
                                            <div class="invalid-feedback">
                                                El campo puesto es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">¿Pertenece a alguno de estos grupos de atención prioritaria?<span style="color:red;">(*)</span></label>
                                            <select name="vulnerables" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Discapacidad">Personas con discapacidad</option>
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
                                    <input type="hidden" name="orientacion" id="orientacion" value="No">
                                    <input type="hidden" name="excepcion" id="excepcion" value="No">
                                    <!--div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">

                                            <label for="name">Posible caso de excepción <span style="color:red;">(*)</span></label>

                                            <select name="excepcion" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div-->
                                    <div id="tipo_caso"  class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">¿El motivo de la solicitud se debe a alguno de los siguientes casos? <span style="color:red;">(*)</span></label>
                                            <button type="button" class="btn btn-primary btn-sm lh-1 fs-6" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                    ?
                                            </button>
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
                                                    <option value="Ninguno" style="font-weight: bold;">No, ninguno</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="name">Descripción breve del motivo de la solicitud (Máximo 500 caracteres): </label>
                                            <textarea name="conflicto" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" maxlength="500"></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12 d-flex align-items-center gap-2">
                                        <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                        <input type="hidden" name="hora_turno" id="hora_turno" value="">
                                        <button type="button" id="botonCalendar" class="btn btn-lg btn-custom-morado" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            Seleccionar Fecha y Horario
                                        </button>
                                        <div id="resumenTurno" class="alert alert-info mt-2" style="display:none;"></div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12 d-flex justify-content-center align-items-center">
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
                        </div>
                        <div class="modal-body">
                            La Ley Federal del Trabajo en el articulo 685-Ter establece que no estas obligado a agotar la etapa conciliatoria en estos supuestos<br>
                            -Discriminación<br>
                            -Acoso u hostigamiento sexual<br>
                            -Designación de beneficiarios<br>
                            -Prestaciones de Seguridad Social
                        </div>
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content modal-grande">
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

            <style>
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
        .modal-xl {
            max-width: 95% !important;
        }

        .modal-content.modal-grande{
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }

        .btn-custom-morado {
            height: 50px;
            font-size: 12px;
            padding: 5px 10px;
            background-color: #496163 !important;
            color: #fff !important;
            border: none;
        }
        .btn-custom-morado:hover, .btn-custom-morado:focus {
            background-color: #530c3a !important;
            color: #fff !important;
        }
            </style>

        </section>
    @endsection

    @section('scripts')
    <script>
        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {

            const municipioSelect = document.getElementById('municipio_solicitante');
            const delegacionInput = document.getElementById('delegacion');
            const delegacionModulo = document.getElementById('modulo_delegacion');

            const delegaciones = {
                1: 'Morelia',
                2: 'Zitácuaro',
                3: 'Uruapan',
                4: 'Lázaro Cárdenas',
                5: 'Zamora',
                6: 'Sahuayo'
            };

            municipioSelect.addEventListener('change', function () {

                const selectedOption =
                    municipioSelect.options[municipioSelect.selectedIndex];

                const delegacionId =
                    selectedOption.getAttribute('data-delegacion-id');

                console.log('Municipio:', selectedOption.textContent.trim());
                console.log('Delegacion ID:', delegacionId);
                console.log('Delegacion encontrada:', delegaciones[delegacionId]);
                // Limpiar
                delegacionInput.value = '';
                delegacionModulo.textContent = '';

                // Asignar delegación
                if (delegacionId && delegaciones[delegacionId]) {

                    const delegacion = delegaciones[delegacionId];

                    delegacionInput.value = delegacion;
                    delegacionModulo.textContent = delegacion;
                }

                blockCalendar();
            });

        });
    </script>
    <script>

        function cambiaExcepcion(elemento){
            var valor = elemento.value;
            var inputExcepcion = document.getElementById('excepcion');
    
            if (inputExcepcion) {
                inputExcepcion.value = (valor === 'Ninguno') ? 'No' : 'Si';
            }
        }

        // El botón solo se habilita cuando ya se eligió delegación y tipo de trámite,
        // pues ambos datos determinan los horarios que ofrecerá el calendario.
        function blockCalendar(){
            const delegacion = document.getElementById('delegacion').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            const boton = document.getElementById("botonCalendar");

            if(delegacion !== "" && tipo !== ""){
                boton.removeAttribute("disabled");
            } else {
                boton.disabled = true;
            }

            limpiarTurnoSeleccionado();
        }

        function limpiarTurnoSeleccionado(){
            turnoSeleccionado = null;
            document.getElementById("fecha_turno").value = "";
            document.getElementById("hora_turno").value = "";
            document.getElementById("resumenTurno").style.display = "none";
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");
        }

        function pad(numero){
            return String(numero).padStart(2, "0");
        }

        function formatoFechaLocal(fecha){
            return fecha.getFullYear() + "-" + pad(fecha.getMonth() + 1) + "-" + pad(fecha.getDate());
        }

        function formatoHoraLocal(fecha){
            return pad(fecha.getHours()) + ":" + pad(fecha.getMinutes()) + ":" + pad(fecha.getSeconds());
        }

        let calendarTurno = null;
        let turnoSeleccionado = null;

        document.getElementById("calendarModal").addEventListener("shown.bs.modal", function () {
            if (calendarTurno) {
                calendarTurno.destroy();
            }

            turnoSeleccionado = null;
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");

            const delegacion = document.getElementById('delegacion').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            const calendarEl = document.getElementById("calendarTurno");
            const inputExcepcion = document.getElementById('excepcion');
            const excepcion = inputExcepcion.value;

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
                if (calendarTurno) {
                    calendarTurno.updateSize();
                    calendarTurno.refetchEvents();
                }
            }, 200);
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

            document.querySelector('#calendarModal [data-bs-dismiss="modal"]').click();
        });

        document.getElementById("form_roles").addEventListener("submit", function (e) {
            const fecha = document.getElementById("fecha_turno").value;
            const hora = document.getElementById("hora_turno").value;
            if (!fecha || !hora) {
                e.preventDefault();
                alert("Debes seleccionar la fecha y el horario del turno antes de guardar.");
            }
        });
    </script>
    @endsection
