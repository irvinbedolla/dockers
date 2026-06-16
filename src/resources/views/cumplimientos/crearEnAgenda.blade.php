@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimientos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Agregar Cumplimiento</h3>
                            <h6 class="text-left">*En este apartado se agregan los cumplimientos parciales o totales que realizará el Auxiliar de Conciliador(a), derivado de audiencia.</h6>
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
                                @if ($errors->any())
                                    <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                        <strong>¡Revise los campos!</strong>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif  
                                <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                <form class="needs-validation novalidate" method="POST" action="{{route('guardar_cumplimiento')}}" onsubmit="return validacionCamposInput()">
                                    @csrf
                                    <br><br>
                                    <div class="row">
                                        <div id="empresa" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Número de identificación único<span style="color:red;">(*)</span></label>
                                                <input type="text" name="NUE" id="NUE" class="form-control" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">
                                                    El Número de identificación es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div id="empresa" class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Empresa/Patrón/Representante legal <span style="color:red;">(*)</span></label>
                                                <input type="text" name="empresa" id="empresa" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                <div class="invalid-feedback">
                                                    El nombre empresa/patrón/representante legal es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Nombre(s) y apellidos trabajador <span style="color:red;">(*)</span></label>
                                                <input type="text" name="trabajador" class="form-control soloLetras" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">
                                                    El nombre es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2">
                                            <div class="form-group">
                                                <label for="name">Monto<span style="color:red;">(*)</span></label>
                                                <input type="text" name="monto" class="form-control soloMontos" required> 
                                                <div class="invalid-feedback">
                                                    El campo edad es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Forma de pago<span style="color:red;">(*)</span></label>
                                                <input type="text" name="forma_pago" class="form-control soloLetras"required>
                                                <div class="invalid-feedback">
                                                    El campo forma de pago es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-7">
                                            <div class="form-group">
                                                <label for="name">Descripción<span style="color:red;">(*)</span></label>
                                                <input type="text" name="descripcion" class="form-control"required>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Sedes <span style="color:red;">(*)</span></label>
                                                <select id="sede" name="sede" class="form-control" onchange="modalCalendar();" required>
                                                    <option value="">Seleccione la sede</option>
                                                    <option value="Morelia">Morelia</option>
                                                    <option value="Zitácuaro">Zitácuaro</option>
                                                    <option value="Uruapan">Uruapan</option>
                                                    <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                    <option value="Zamora">Zamora</option>
                                                    <option value="Sahuayo">Sahuayo</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    La sede es obligatoria.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="fecha" id="fechaSeleccionada" required>
                                        <input type="hidden" name="hora" id="horaSeleccionada" required>
                                        <!-- Botón para abrir el modal -->  
                                        <div class="col-xs-12 col-sm-12 col-md-3 d-flex align-items-center">
                                            <div style="display: flex; align-items: center; justify-content: center;">
                                                <button type="button" id="botonCalendar" class="btn btn-lg btn-custom-morado" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                                    Seleccionar Fecha y Horario
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="resumenCita" class="col-xs-12 col-sm-12 col-md-12" style="margin-top: 10px; display: none;">
                                        <div class="alert alert-info">
                                            <strong>Cita seleccionada:</strong> <span id="fechaResumen"></span> a las <span id="horaResumen"></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div align="center">
                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color: #CEA845">Guardar</button> 
                                        </div>
                                    </div>     
                                </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Fecha y Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="calendar"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmarSeleccion">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
                             
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    @section('scripts')
       <script>
            function sedes(){
                document.getElementById("fecha").removeAttribute("disabled");
            }

            function modalCalendar(){
                document.getElementById("botonCalendar").removeAttribute("disabled");
            }

            document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                },
                validRange: {
                    start: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                    })(),
                    end: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear() + 1, now.getMonth() + 16, 0).toISOString().split('T')[0];
                    })()
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Obtener sede seleccionada
                    var sede = document.getElementById('sede').value;
                    
                    // Hacer petición AJAX con parámetro sede
                    $.ajax({
                        url: '../api/obtenerCumplimientos',
                        method: 'GET',
                        data: {
                            sede: sede,
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(data) {
                            console.log(data);
                            successCallback(data);
                        },
                        error: function() {
                            failureCallback('Error al cargar eventos');
                        }
                    });
                },


                eventClick: function(info) {
                    // Solo permitir selección de horarios disponibles
                    let ahora = new Date();
                    let slotDate = new Date(info.event.start);

                    if (info.event.extendedProps.estado === 'disponible') {
                        // Deseleccionar evento anterior
                        document.querySelectorAll('.fc-event-selected').forEach(el => {
                            el.classList.remove('fc-event-selected');
                        });
                        
                        // Seleccionar este evento
                        info.el.classList.add('fc-event-selected');
                        window.selectedEvent = info.event;
                    } else {
                        alert('Este horario no está disponible. Por favor seleccione otro.');
                    }
                },
                eventDidMount: function(info) {
                    // Añade clases CSS según el tipo de evento
                    if (info.event.extendedProps.estado === 'disponible') {
                        info.el.classList.add('fc-event-disponible');
                    } else {
                        info.el.classList.add('fc-event-ocupado');
                    }
                },
            });

            calendar.render();

            $('#calendarModal').on('shown.bs.modal', function () {
                calendar.refetchEvents();
                calendar.updateSize();
            });

            // Confirmar selección
            document.getElementById('confirmarSeleccion').addEventListener('click', function() {
                if (window.selectedEvent) {
                    const fechaHora = new Date(window.selectedEvent.start);
                    const fecha = fechaHora.toISOString().split('T')[0];
                    const hora = fechaHora.toTimeString().substring(0, 8);
                    
                    // Guardar en campos ocultos
                    document.getElementById('fechaSeleccionada').value = fecha;
                    document.getElementById('horaSeleccionada').value = hora;
                    
                    // Mostrar resumen al usuario
                    document.getElementById('fechaResumen').textContent = fecha;
                    document.getElementById('horaResumen').textContent = hora;
                    document.getElementById('resumenCita').style.display = 'block';
                    
                    // Cerrar modal
                    //$('#calendarModal').modal('hide');
                    const modalEl = document.getElementById('calendarModal');
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                } else {
                    alert('Por favor selecciona un horario disponible');
                }
            });
        });
        </script>
        <script src="../public/assets/js/poderes/general.js"></script>
    @endsection
