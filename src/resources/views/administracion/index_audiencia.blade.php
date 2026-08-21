@extends('layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cambio de Fecha en Audiencia</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalRetroceso">
                                        <i class="bi bi-search"></i> Buscar audiencia
                                    </button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <a href="{{ route('configuracion') }}" class="btn btn-secondary">Regresar</a>
                                </div>
                            </div>
                            <br>

                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Retroceso aplicado.</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <strong>Expediente localizado.</strong>
                                    {{ session()->get('message') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="table-responsive">
                                          <input type="hidden" id="modal-id" name="citado" value="">
                                        <table id="example" class="table table-striped table-hover mt-3 border shadow-sm">
                                            <thead style="background-color: #4A001F; color: white;">
                                                <tr>
                                                    <th class="text-center text-white" style="color: white;">NUE</th>
                                                    <th class="text-center text-white" style="color: white;">Delegación</th>
                                                    <th class="text-center text-white" style="color: white;">Solictante</th>
                                                    <th class="text-center text-white" style="color: white;">Citado</th>
                                                    <th class="text-center text-white" style="color: white;">Fecha</th>
                                                    <th class="text-center text-white" style="color: white;">Hora</th>
                                                    <th class="text-center text-white" style="color: white;">Estatus</th>
                                                    <th class="text-center text-white" style="color: white;">Conciliador</th>
                                                    <th class="text-center text-white" style="color: white;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (session('folio'))
                                                   @php
                                                        $folio = session('folio');
                                                    @endphp
                                                        <tr>
                                                            <td class="text-center align-middle"><strong>{{ $folio['NUE'] }}</strong></td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #6c757d;">{{ $folio['delegacion'] ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">{{ $folio['solicitante'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">{{ $folio['citados'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">
                                                                {{ $folio['fecha'] ? \Carbon\Carbon::parse($folio['fecha'])->format('d/m/Y') : 'N/A' }}
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                {{ $folio['hora'] ? \Carbon\Carbon::parse($folio['hora'])->format('H:i') : 'N/A' }}
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #ffc107; color: black;">{{ $folio['estatus'] }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">{{ $folio['conciliador'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">
                                                                <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                                            <input type="hidden" name="hora_turno" id="hora_turno" value="">
                                                            <button type="button" class="btn btn-info open-modal" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#ModalReagendar" 
                                                                data-id="{{ $folio['id'] }}"
                                                                data-sede="{{ $folio['delegacion'] }}"
                                                                data-id-conciliador="{{ $folio['id_conciliador'] }}"
                                                                data-nue="{{ $folio['NUE'] }}">
                                                                Reagendar
                                                            </button>
                                                            <div id="resumenTurno" class="alert alert-info mt-2" style="display:none;"></div>
                                                            </td>
                                                        </tr>
                                                    
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div class="modal fade" id="ModalRetroceso" tabindex="-1" role="dialog" aria-labelledby="retrocesoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retrocesoModalLabel">Buscar audiencia por NUE</h5>
            </div>
            <form class='needs-validation novalidate' id='form_retroceso_buscar' method='POST' action="{{ route('fecha_audiencia_buscar') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Capture las partes del NUE tal como aparece en el expediente.
                        Ejemplo: <code>MOR/SOL/{{ date('Y') }}/00576</code>
                    </p>
                    <div class="row">
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Delegación <span class="text-danger">*</span></label>
                            <select class="form-control" name="delegacion" id="delegacion_retroceso" required>
                                <option value="">Seleccione</option>
                                @foreach ($delegaciones as $prefijo => $nombre)
                                    <option value="{{ $prefijo }}" {{ old('delegacion') == $prefijo ? 'selected' : '' }}>
                                        {{ $prefijo }} — {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Debe seleccionar la delegación.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <select class="form-control" name="anio" id="anio_retroceso" required>
                                <option value="">Seleccione</option>
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ old('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un año.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Consecutivo <span class="text-danger">*</span></label>
                            <input type="number" placeholder="Ej. 576" class="form-control" name="consecutivo"
                                   id="consecutivo_retroceso" min="1" step="1" required value="{{ old('consecutivo') }}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Ingrese un consecutivo válido.</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="text-muted">NUE a buscar</label>
                            <input type="text" class="form-control" id="nue_preview" readonly value="—">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="ModalReagendar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{route('cambiar_fecha')}}">
    @csrf
    <!-- Input para que tu Controlador sepa qué actualizar -->
    <input type="hidden" name="id_audiencia" id="modal-id-reagendar" value="">
    
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <!-- Restaura el header que se había borrado -->
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Estos inputs alimentan al JavaScript del Calendario -->
                <input type="hidden" id="NUE" value="">
                <input type="hidden" id="sedeReagendar" value="">
                <input type="hidden" id="idConciliador" value="">
                <input type="hidden" id="fechaConfirmacion" value="">
                
                <div id="calendarReagendar"></div>
                <input type="hidden" name="fecha" id="fechaSeleccionada">
                <input type="hidden" name="hora" id="horaSeleccionada">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success" id="btnGuardarReagenda" disabled>Guardar</button>
            </div>
        </div>
    </div>
    </form>
</div>

@section('scripts')
    <!--Estilos y Librerías Externas -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .fc-event { padding: 3px 6px !important; border-radius: 4px !important; font-size: 12px !important; cursor: pointer; }
        #calendarReagendar{ width: 100%; min-height: 500px; }
        .fc-event-disponible, .fc-est-disponible{ color:#fff !important; background-color:#00CE1C !important; border-color:#00CE1C !important; }
        .fc-event-expirado, .fc-est-expirado{ color:#fff !important; background-color:#969696 !important; border-color:#969696 !important; }
        .fc-event-inhabil, .fc-est-inhabil{ color:#fff !important; background-color:#3B78DB !important; border-color:#3B78DB !important; }
        .fc-event-ocupado, .fc-est-ocupado{ color:#fff !important; background-color:#eca130 !important; border-color:#eca130 !important; }
        .fc-event-actual, .fc-est-actual{ color:#fff !important; background-color:#8163a8 !important; border-color:#8163a8 !important; }
        .fc-event-selected { border: 2px solid #FFD700 !important; box-shadow: 0 0 8px #FFD700; }
        .fc .fc-event-main, .fc .fc-event-time { color:#fff !important; }
        .fc-list .fc-list-event.fc-event-disponible td, .fc-list .fc-list-event.fc-est-disponible td{ background-color:#00CE1C !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-expirado td, .fc-list .fc-list-event.fc-est-expirado td{ background-color:#969696 !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-inhabil td, .fc-list .fc-list-event.fc-est-inhabil td{ background-color:#3B78DB !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-ocupado td, .fc-list .fc-list-event.fc-est-ocupado td{ background-color:#eca130 !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-ocupado td, .fc-list .fc-list-event.fc-est-actual td{ background-color:#8163a8 !important; color:#fff !important; }
        @media (min-width: 1200px){ .modal-xl{ --bs-modal-width: 95vw; } }
        .modal .modal-body{ max-height: calc(100vh - 200px); overflow-y: auto; }
    </style>

    <!--Inicialización General y Búsqueda -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Vista previa del NUE
            var delegacion  = document.getElementById('delegacion_retroceso');
            var anio        = document.getElementById('anio_retroceso');
            var consecutivo = document.getElementById('consecutivo_retroceso');
            var preview     = document.getElementById('nue_preview');

            function armarNue() {
                if (!preview) return;
                if (delegacion.value && anio.value && consecutivo.value) {
                    preview.value = delegacion.value + '/SOL/' + anio.value + '/' + String(consecutivo.value).padStart(5, '0');
                } else {
                    preview.value = '—';
                }
            }

            [delegacion, anio, consecutivo].forEach(function (el) {
                if (el) { el.addEventListener('change', armarNue); el.addEventListener('input', armarNue); }
            });
            armarNue();

            // Validación de búsqueda
            var formBuscar = document.getElementById('form_retroceso_buscar');
            if (formBuscar) {
                formBuscar.addEventListener('submit', function (e) {
                    var errores = [];
                    if (!delegacion.value) errores.push('Debe seleccionar la delegación del NUE.');
                    if (!anio.value) errores.push('Debe seleccionar el año del NUE.');
                    if (!consecutivo.value || consecutivo.value <= 0 || !Number.isInteger(Number(consecutivo.value))) {
                        errores.push('El consecutivo debe ser un número entero positivo.');
                    }

                    if (errores.length > 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof swal === 'function') {
                            swal({ title: 'Campos incompletos', text: errores.join('\n'), type: 'warning' });
                        } else {
                            alert(errores.join('\n'));
                        }
                    }
                    formBuscar.classList.add('was-validated');
                });
            }
        });
    </script>

    <!-- Logica de DataTables y Calendario -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if ($.fn.DataTable.isDataTable('#tablaAudienciasServerSide')) {
                $('#tablaAudienciasServerSide').DataTable().destroy();
            }

            $('#tablaAudienciasServerSide').DataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 10,
                "searching": true,
                "ordering": false,
                "ajax": { "url": "{{ route('buscar_abogados_audiencia_ajax') }}", "type": "GET" },
                "columnDefs": [{ "targets": 3, "render": function (data, type, row) { return data; } }],
                "language": {
                    "processing": "Consultando...", "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron representantes", "info": "Mostrando _START_ al _END_ de _TOTAL_ registros",
                    "search": "Filtrar:", "paginate": { "next": "Siguiente", "previous": "Anterior" }
                }
            });

            // Carga de datos al abrir el modal (Con jQuery para evitar errores si falta un input)
            $('.open-modal').click(function() {
                const id = $(this).data('id'); 
                const sede = $(this).data('sede');
                const idConc = $(this).data('id-conciliador');
                const nue = $(this).data('nue');

                $('#modal-id').val(id);
                $('#modal-id-reagendar').val(id);
                $('#sedeReagendar').val(sede);
                $('#idConciliador').val(idConc);
                $('#NUE').val(nue);
                
            });

            let calendarReagendar;
            $('#ModalReagendar').on('shown.bs.modal', function () {
                const calEl = document.getElementById('calendarReagendar');
                if (!calEl) return;
                if (calendarReagendar) { calendarReagendar.destroy(); }
                
                const sede = $('#sedeReagendar').val();
                const diasHabilesNotificacion = (/morelia/i.test(String(sede || '')) ? 11 : 7);
                const conciliadorId = $('#idConciliador').val();
                const audiencia = $('#modal-id').val();
                const hoy = new Date();
                hoy.setHours(0,0,0,0);

                function toYMD(dt) {
                    const y = dt.getFullYear();
                    const m = String(dt.getMonth() + 1).padStart(2, '0');
                    const d = String(dt.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                }

                function addDaysYMD(ymd, n) {
                    const [y, m, d] = ymd.split('-').map(Number);
                    const dt = new Date(y, m - 1, d);
                    dt.setDate(dt.getDate() + n);
                    return toYMD(dt);
                }

                async function addNaturalAndInhabilDays(fechaConfirmacionStr, n, centro) {
                    let inhabiles = [];
                    try {
                        const res = await fetch(`{{ url('/api/dias-inhabiles-centro') }}?centro=${encodeURIComponent(centro)}&fecha_confirmacion=${encodeURIComponent(fechaConfirmacionStr)}`);
                        inhabiles = await res.json();
                    } catch(e) { console.error("Error", e); }

                    function isDiaInhabil(dtStr) {
                        for(let i=0; i<inhabiles.length; i++) {
                            if(dtStr >= inhabiles[i].fecha_inicio && dtStr <= inhabiles[i].fecha_final) return true;
                        }
                        return false;
                    }

                    const [y, m, d] = fechaConfirmacionStr.split('-').map(Number);
                    let dt = new Date(y, m - 1, d);
                    let added = 0;
                    while (added < n) {
                        dt.setDate(dt.getDate() + 1);
                        if (!isDiaInhabil(toYMD(dt))) added++;
                    }
                    return toYMD(dt);
                }

                function isWeekend(dt){
                    const day = dt.getDay();
                    return day === 0 || day === 6;
                }

                function fetchEventosGlobales(startDate, endDate){
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: '{{ url('/api/obtenerAudienciasConciliador') }}',
                            data: { sede: sede, start: startDate.toISOString(), end: endDate.toISOString(), conciliador: conciliadorId, audiencia: audiencia },
                            success: (data)=> resolve(Array.isArray(data) ? data : []),
                            error: function(xhr, status, err) {
                                reject(err || status || 'error');
                            }
                        });
                    });
                }

                function buildInhabilIndex(eventos){
                    const set = new Set();
                    for(const ev of (eventos || [])){
                        if(!ev || !ev.start) continue;
                        const ymd = String(ev.start).slice(0,10);
                        const estado = ev.extendedProps && ev.extendedProps.estado ? ev.extendedProps.estado : null;
                        const userId = (ev.extendedProps && (ev.extendedProps.user_id ?? ev.extendedProps.userId)) ?? null;
                        if(estado === 'inhabil' && (userId === null || userId === '')){
                            set.add(ymd);
                        }
                    }
                    return set;
                }

                async function calcularFechaMinimaNotificacionAsync(){
                    const diasHabilesNecesarios = diasHabilesNotificacion;
                    let cursor = new Date(hoy);
                    let contados = 0;

                    const ventanaInicio = new Date(hoy);
                    ventanaInicio.setHours(0,0,0,0);
                    const ventanaFin = new Date(hoy);
                    ventanaFin.setDate(ventanaFin.getDate() + 120);
                    ventanaFin.setHours(23,59,59,999);

                    let eventos = [];
                    try { eventos = await fetchEventosGlobales(ventanaInicio, ventanaFin); } catch(e){ eventos = []; }
                    const inhabilSet = buildInhabilIndex(eventos);

                    while(contados < diasHabilesNecesarios){
                        cursor.setDate(cursor.getDate() + 1);
                        if(isWeekend(cursor)) continue;
                        if(inhabilSet.has(toYMD(cursor))) continue;
                        contados++;
                    }
                    return cursor;
                }

                (async function(){
                    const fechaMinima = new Date(hoy);
                    fechaMinima.setDate(fechaMinima.getDate() + 1);
                    const fechaMinimaStr = fechaMinima.toISOString().slice(0,10);

                    const fechaMinNotificacion = await calcularFechaMinimaNotificacionAsync();
                    const fechaMinNotificacionStr = toYMD(fechaMinNotificacion);
                    
                    const fechaSemanaInicio = new Date(fechaMinima);
                    fechaSemanaInicio.setDate(fechaSemanaInicio.getDate() - ((fechaSemanaInicio.getDay() + 6) % 7));
                    const startOfWeekStr = fechaSemanaInicio.toISOString().slice(0,10);

                    const fechaConfirmacion = document.getElementById('fechaConfirmacion').value;
                    let fechaLimite = null;
                    if (fechaConfirmacion && sede) {
                        fechaLimite = await addNaturalAndInhabilDays(fechaConfirmacion, 46, sede);
                    } else if (fechaConfirmacion) {
                        fechaLimite = addDaysYMD(fechaConfirmacion, 46);
                    }

                    //console.log('SEDE:', sede);
                    //console.log('CONCILIADOR:', conciliadorId);
                    //console.log('NUE:', $('#NUE').val());
                    //console.log('Auciencia:', audiencia);

                    calendarReagendar = new FullCalendar.Calendar(calEl, {
                        locale: 'es',
                        firstDay: 1,
                        initialDate: fechaMinimaStr,
                        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
                        headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                        validRange: function() {
                            const range = { start: startOfWeekStr };
                            if (fechaLimite) range.end = fechaLimite; 
                            return range;
                        },
                        events: function(fetchInfo, success, failure) {
                            $.ajax({
                                url: '{{ url('/api/obtenerAudienciasConciliador') }}',
                                data: { sede: sede, start: fetchInfo.startStr, end: fetchInfo.endStr, conciliador: conciliadorId, audiencia: audiencia},
                                success: success,
                                error: function(xhr, status, error) {
                                    failure('Fallo en el servidor');
                                }
                            });
                        },
                        eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
                        eventClick: function(info) {
                            const slot = new Date(info.event.start);
                            const slotYMD = slot.toISOString().slice(0,10);
                            const estadoClick = info.event.extendedProps?.estado || null;
                            const titulo = info.event?.title ? String(info.event.title) : '';

                            if (estadoClick === 'ocupado') {
                                if (window.Swal){
                                    Swal.fire({
                                        icon: 'warning', title: 'Aviso',
                                        html: 'Esta fecha ya esta esta reservada.<br><br><b>' + '¿Esta seguro de continuar?' + '</b>.'
                                    });
                                    $('.fc-event-selected').removeClass('fc-event-selected');
                                    info.el.classList.add('fc-event-selected');
                                    $('#fechaSeleccionada').val(slot.toISOString().split('T')[0]);
                                    $('#horaSeleccionada').val(slot.toTimeString().substring(0,5)+':00');
                                    $('#btnGuardarReagenda').prop('disabled', false);

                                } 
                                
                            }
                            if (/audiencia\s*\(/i.test(titulo) && window.Swal) {
                                Swal.fire({ icon: 'info', title: 'Empalme', html: 'Este horario ya cuenta con una audiencia.' });
                            }

                            if ((estadoClick === 'disponible' || /audiencia\s*\(/i.test(titulo)) && slot > new Date() ) {
                                $('.fc-event-selected').removeClass('fc-event-selected');
                                info.el.classList.add('fc-event-selected');
                                $('#fechaSeleccionada').val(slot.toISOString().split('T')[0]);
                                $('#horaSeleccionada').val(slot.toTimeString().substring(0,5)+':00');
                                $('#btnGuardarReagenda').prop('disabled', false);

                                
                            }
                        },
                        eventDidMount: function(info){
                            const estado = info.event.extendedProps.estado;
                            if(estado){ info.el.classList.add('fc-est-'+estado, 'fc-event-'+estado); }
                        }
                    });
                    calendarReagendar.render();
                    setTimeout(() => { if (calendarReagendar) { calendarReagendar.updateSize(); calendarReagendar.refetchEvents(); } }, 300);
                })();
            });

            $('#sedeReagendar').on('change', function(){ if(calendarReagendar) calendarReagendar.refetchEvents(); });

            const formReagendar = document.querySelector('#ModalReagendar form');
            if(formReagendar){
                formReagendar.addEventListener('submit', function(e){
                    e.preventDefault();
                    const idAudiencia = document.getElementById('NUE').value;
                    const fecha = document.getElementById('fechaSeleccionada').value;
                    const hora = document.getElementById('horaSeleccionada').value;
                    let mensajeHtml = `<p>Se reagendará la Audiencia con <strong>NUE: ${idAudiencia}</strong></p>`;
                    if(fecha) mensajeHtml += `<p>Fecha: <strong>${fecha}</strong></p>`;
                    if(hora) mensajeHtml += `<p>Hora: <strong>${hora.substring(0,5)}</strong></p>`;
                    
                    function lanzar(){
                        Swal.fire({
                            title: 'Confirmar reagenda', html: mensajeHtml, icon: 'question',
                            showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', reverseButtons: true
                        }).then((result)=>{ if(result.isConfirmed) formReagendar.submit(); });
                    }
                    if(window.Swal) lanzar(); else setTimeout(lanzar, 200);
                });
            }
    });
    </script>
    <script src="{{ asset('assets/js/validaciones.js') }}"></script>
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
@endsection