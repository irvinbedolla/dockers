@extends('layouts.app')
@section('title', 'Cambio de Fecha en Cumplimiento')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cambio de Fecha en Cumplimiento</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalBuscarCumplimiento">
                                        <i class="bi bi-search"></i> Buscar expediente
                                    </button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <a href="{{ route('configuracion') }}" class="btn btn-secondary">Regresar</a>
                                </div>
                            </div>
                            <br>

                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Cambio aplicado.</strong>
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
                                        <table id="example" class="table table-striped table-hover mt-3 border shadow-sm">
                                            <thead style="background-color: #354647; color: white;">
                                                <tr>
                                                    <th class="text-center text-white" style="color: white;">NUE</th>
                                                    <th class="text-center text-white" style="color: white;">Interesado</th>
                                                    <th class="text-center text-white" style="color: white;">Tipo</th>
                                                    <th class="text-center text-white" style="color: white;">Descripción</th>
                                                    <th class="text-center text-white" style="color: white;">Monto</th>
                                                    <th class="text-center text-white" style="color: white;">Fecha</th>
                                                    <th class="text-center text-white" style="color: white;">Hora</th>
                                                    <th class="text-center text-white" style="color: white;">Estatus</th>
                                                    <th class="text-center text-white" style="color: white;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $interesado = session('interesado_buscado'); @endphp
                                                @forelse (session('cumplimientos', []) as $cumplimiento)
                                                    <tr>
                                                        <td class="text-center align-middle"><strong>{{ $cumplimiento['NUE'] }}</strong></td>
                                                        <td class="text-center align-middle">{{ $interesado ?: 'N/A' }}</td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge" style="background-color: #6c757d;">{{ $cumplimiento['tipo_pago'] }}</span>
                                                        </td>
                                                        <td class="text-center align-middle">{{ $cumplimiento['descripcion'] ?: 'N/A' }}</td>
                                                        <td class="text-center align-middle">{{ $cumplimiento['monto'] !== null ? '$'.number_format($cumplimiento['monto'], 2) : 'N/A' }}</td>
                                                        <td class="text-center align-middle">
                                                            {{ $cumplimiento['fecha'] ? \Carbon\Carbon::parse($cumplimiento['fecha'])->format('d/m/Y') : 'N/A' }}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            {{ $cumplimiento['hora'] ? \Carbon\Carbon::parse($cumplimiento['hora'])->format('H:i') : 'N/A' }}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge" style="background-color: #ffc107; color: black;">{{ $cumplimiento['estatus'] }}</span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @if ($cumplimiento['estatus'] === 'Pagado')
                                                                <button type="button" class="btn btn-info" disabled title="No se puede reagendar un cumplimiento ya pagado.">
                                                                    Reagendar
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-info open-modal-pago"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#ModalReagendarPago"
                                                                    data-id="{{ $cumplimiento['id'] }}"
                                                                    data-sede="{{ $cumplimiento['delegacion'] }}"
                                                                    data-nue="{{ $cumplimiento['NUE'] }}">
                                                                    Reagendar
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                @endforelse
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

<div class="modal fade" id="ModalBuscarCumplimiento" tabindex="-1" role="dialog" aria-labelledby="buscarCumplimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarCumplimientoModalLabel">Buscar expediente por NUE</h5>
            </div>
            <form class='needs-validation novalidate' id='form_cumplimiento_buscar' method='POST' action="{{ route('fecha_cumplimiento_buscar') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Capture las partes del NUE tal como aparece en el expediente.
                        Ejemplo: <code>MOR/SOL/{{ date('Y') }}/00576</code> o <code>MOR/RAT/{{ date('Y') }}/00576</code>
                    </p>
                    <div class="row">
                        <div class="col-12">
                            <label>Tipo de expediente <span class="text-danger">*</span></label>
                            <select class="form-control" name="tipo" id="tipo_cumplimiento" required>
                                <option value="">Seleccione</option>
                                <option value="SOL" {{ old('tipo') == 'SOL' ? 'selected' : '' }}>Solicitud (Audiencia)</option>
                                <option value="RAT" {{ old('tipo') == 'RAT' ? 'selected' : '' }}>Ratificación</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar el tipo de expediente.</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Delegación <span class="text-danger">*</span></label>
                            <select class="form-control" name="delegacion" id="delegacion_cumplimiento" required disabled>
                                <option value="">Seleccione primero el tipo</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar la delegación.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <select class="form-control" name="anio" id="anio_cumplimiento" required>
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
                                   id="consecutivo_cumplimiento" min="1" step="1" required value="{{ old('consecutivo') }}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Ingrese un consecutivo válido.</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="text-muted">NUE a buscar</label>
                            <input type="text" class="form-control" id="nue_preview_cumplimiento" readonly value="—">
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

<div class="modal fade" id="ModalReagendarPago" tabindex="-1" aria-labelledby="reagendarPagoModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{ route('cambiar_fecha_cumplimiento') }}">
    @csrf
    <input type="hidden" name="id_pago" id="modal-id-pago" value="">

    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reagendarPagoModalLabel">Fecha del cumplimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="nuePago" value="">
                <input type="hidden" id="sedePago" value="">

                <div id="calendarReagendarPago"></div>
                <input type="hidden" name="fecha" id="fechaSeleccionadaPago">
                <input type="hidden" name="hora" id="horaSeleccionadaPago">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success" id="btnGuardarReagendaPago" disabled>Guardar</button>
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
        #calendarReagendarPago{ width: 100%; min-height: 500px; }
        .fc-event-disponible, .fc-est-disponible{ color:#fff !important; background-color:#00CE1C !important; border-color:#00CE1C !important; }
        .fc-event-expirado, .fc-est-expirado{ color:#fff !important; background-color:#969696 !important; border-color:#969696 !important; }
        .fc-event-inhabil, .fc-est-inhabil{ color:#fff !important; background-color:#3B78DB !important; border-color:#3B78DB !important; }
        .fc-event-actual, .fc-est-actual{ color:#fff !important; background-color:#8163a8 !important; border-color:#8163a8 !important; }
        .fc-event-selected { border: 2px solid #FFD700 !important; box-shadow: 0 0 8px #FFD700; }
        .fc .fc-event-main, .fc .fc-event-time { color:#fff !important; }
        .fc-list .fc-list-event.fc-event-disponible td, .fc-list .fc-list-event.fc-est-disponible td{ background-color:#00CE1C !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-expirado td, .fc-list .fc-list-event.fc-est-expirado td{ background-color:#969696 !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-inhabil td, .fc-list .fc-list-event.fc-est-inhabil td{ background-color:#3B78DB !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-actual td, .fc-list .fc-list-event.fc-est-actual td{ background-color:#8163a8 !important; color:#fff !important; }
        @media (min-width: 1200px){ .modal-xl{ --bs-modal-width: 95vw; } }
        .modal .modal-body{ max-height: calc(100vh - 200px); overflow-y: auto; }
    </style>

    <!-- Inicialización de la búsqueda: delegación dependiente del tipo, vista previa del NUE -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var delegacionesPorTipo = {
                SOL: @json($delegacionesSol),
                RAT: @json($delegacionesRat)
            };

            var tipo        = document.getElementById('tipo_cumplimiento');
            var delegacion  = document.getElementById('delegacion_cumplimiento');
            var anio        = document.getElementById('anio_cumplimiento');
            var consecutivo = document.getElementById('consecutivo_cumplimiento');
            var preview     = document.getElementById('nue_preview_cumplimiento');
            var oldDelegacion = @json(old('delegacion'));

            function poblarDelegaciones() {
                var mapa = delegacionesPorTipo[tipo.value] || null;
                delegacion.innerHTML = '';

                if (!mapa) {
                    delegacion.disabled = true;
                    delegacion.appendChild(new Option('Seleccione primero el tipo', ''));
                    armarNue();
                    return;
                }

                delegacion.disabled = false;
                delegacion.appendChild(new Option('Seleccione', ''));
                Object.keys(mapa).forEach(function (prefijo) {
                    delegacion.appendChild(new Option(prefijo + ' — ' + mapa[prefijo], prefijo));
                });
                if (oldDelegacion && mapa[oldDelegacion]) {
                    delegacion.value = oldDelegacion;
                }
                armarNue();
            }

            function armarNue() {
                if (!preview) return;
                if (tipo.value && delegacion.value && anio.value && consecutivo.value) {
                    preview.value = delegacion.value + '/' + tipo.value + '/' + anio.value + '/' + String(consecutivo.value).padStart(5, '0');
                } else {
                    preview.value = '—';
                }
            }

            tipo.addEventListener('change', poblarDelegaciones);
            [delegacion, anio, consecutivo].forEach(function (el) {
                el.addEventListener('change', armarNue);
                el.addEventListener('input', armarNue);
            });
            poblarDelegaciones();

            // Validación de búsqueda
            var formBuscar = document.getElementById('form_cumplimiento_buscar');
            if (formBuscar) {
                formBuscar.addEventListener('submit', function (e) {
                    var errores = [];
                    if (!tipo.value) errores.push('Debe seleccionar el tipo de expediente.');
                    if (!delegacion.value) errores.push('Debe seleccionar la delegación del NUE.');
                    if (!anio.value) errores.push('Debe seleccionar el año del NUE.');
                    if (!consecutivo.value || consecutivo.value <= 0 || !Number.isInteger(Number(consecutivo.value))) {
                        errores.push('El consecutivo debe ser un número entero positivo.');
                    }

                    if (errores.length > 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (window.Swal) {
                            Swal.fire({ icon: 'warning', title: 'Campos incompletos', html: errores.join('<br>') });
                        } else {
                            alert(errores.join('\n'));
                        }
                    }
                    formBuscar.classList.add('was-validated');
                });
            }
        });
    </script>

    <!-- Calendario para reagendar el cumplimiento -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $(document).on('click', '.open-modal-pago', function() {
                const id = $(this).data('id');
                const sede = $(this).data('sede');
                const nue = $(this).data('nue');

                $('#modal-id-pago').val(id);
                $('#sedePago').val(sede);
                $('#nuePago').val(nue);
            });

            let calendarReagendarPago;
            $('#ModalReagendarPago').on('shown.bs.modal', function () {
                const calEl = document.getElementById('calendarReagendarPago');
                if (!calEl) return;
                if (calendarReagendarPago) { calendarReagendarPago.destroy(); }

                const sede = $('#sedePago').val();
                const pagoId = $('#modal-id-pago').val();

                const validRangeStart = (() => {
                    const now = new Date();
                    return new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                })();
                const validRangeEnd = (() => {
                    const now = new Date();
                    return new Date(now.getFullYear(), now.getMonth() + 12, 0).toISOString().split('T')[0];
                })();

                calendarReagendarPago = new FullCalendar.Calendar(calEl, {
                    locale: 'es',
                    firstDay: 1,
                    initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
                    headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                    validRange: { start: validRangeStart, end: validRangeEnd },
                    events: function(fetchInfo, success, failure) {
                        $.ajax({
                            url: '{{ url('/api/obtenerCumplimientosAdmin') }}',
                            data: { sede: sede, pago: pagoId, start: fetchInfo.startStr, end: fetchInfo.endStr },
                            success: success,
                            error: function() { failure('Fallo en el servidor'); }
                        });
                    },
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
                    eventClick: function(info) {
                        const slot = new Date(info.event.start);
                        const estadoClick = info.event.extendedProps && info.event.extendedProps.estado ? info.event.extendedProps.estado : null;

                        if (estadoClick === 'disponible' || estadoClick === 'actual') {
                            $('.fc-event-selected').removeClass('fc-event-selected');
                            info.el.classList.add('fc-event-selected');
                            $('#fechaSeleccionadaPago').val(slot.toISOString().split('T')[0]);
                            $('#horaSeleccionadaPago').val(slot.toTimeString().substring(0,5)+':00');
                            $('#btnGuardarReagendaPago').prop('disabled', false);
                        } else if (window.Swal) {
                            Swal.fire({ icon: 'warning', title: 'Horario no disponible', text: 'Por favor seleccione otro horario.' });
                        }
                    },
                    eventDidMount: function(info){
                        const estado = info.event.extendedProps.estado;
                        if(estado){ info.el.classList.add('fc-est-'+estado, 'fc-event-'+estado); }
                    }
                });
                calendarReagendarPago.render();
                setTimeout(() => { if (calendarReagendarPago) { calendarReagendarPago.updateSize(); calendarReagendarPago.refetchEvents(); } }, 300);
            });

            const formReagendarPago = document.querySelector('#ModalReagendarPago form');
            if (formReagendarPago) {
                formReagendarPago.addEventListener('submit', function(e){
                    e.preventDefault();
                    const nue = document.getElementById('nuePago').value;
                    const fecha = document.getElementById('fechaSeleccionadaPago').value;
                    const hora = document.getElementById('horaSeleccionadaPago').value;
                    let mensajeHtml = `<p>Se reprogramará el cumplimiento del expediente <strong>NUE: ${nue}</strong></p>`;
                    if (fecha) mensajeHtml += `<p>Fecha: <strong>${fecha}</strong></p>`;
                    if (hora) mensajeHtml += `<p>Hora: <strong>${hora.substring(0,5)}</strong></p>`;

                    function lanzar(){
                        Swal.fire({
                            title: 'Confirmar cambio de fecha', html: mensajeHtml, icon: 'question',
                            showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', reverseButtons: true
                        }).then((result)=>{ if(result.isConfirmed) formReagendarPago.submit(); });
                    }
                    if (window.Swal) lanzar(); else setTimeout(lanzar, 200);
                });
            }
        });
    </script>
@endsection
