@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Gestión de Agenda y Sedes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- El resultado de guardar se avisa con SweetAlert al final
                                 de este archivo, no con alertas dentro del contenido. --}}

                            <div class="tab mb-4">
                                <a class="btn btn-secondary" href="{{ route('configuracion') }}"><i class="bi bi-arrow-left"></i> Regresar</a>
                            </div>

                            {{-- Una tarjeta por sede del catálogo. Reemplaza la tabla
                                 "Delegación / Configurar Bloqueos" para que cada sede
                                 tenga su propia entrada al calendario. --}}
                            <h5 class="mt-4 mb-3 text-dark"><i class="bi bi-buildings"></i> Sedes</h5>

                            <div class="row g-3">
                                @forelse ($sedes as $sede)
                                    @php
                                        $esModelo    = $sede instanceof \App\Models\Sedes;
                                        $nombreSede  = $esModelo ? ($sede->nombre ?? '') : $sede;
                                        $idSede      = $esModelo ? $sede->id : null;
                                        $hoy         = now()->toDateString();

                                        $deSede      = $bloqueos->where('centro', $nombreSede)->whereNull('user_id');
                                        $deConc      = $bloqueos->where('centro', $nombreSede)->whereNotNull('user_id');

                                        $vigentesSede = $deSede->filter(function ($b) use ($hoy) {
                                            return $b->fecha_final >= $hoy;
                                        })->count();

                                        $vigentesConc = $deConc->filter(function ($b) use ($hoy) {
                                            return $b->fecha_final >= $hoy;
                                        })->count();

                                        $proximo = $deSede->filter(function ($b) use ($hoy) {
                                            return $b->fecha_final >= $hoy;
                                        })->sortBy('fecha_inicio')->first();
                                    @endphp

                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                                          style="width:44px; height:44px; background:#496163; color:#fff;">
                                                        <i class="bi bi-geo-alt-fill"></i>
                                                    </span>
                                                    <div>
                                                        <h5 class="mb-0 text-dark">{{ $nombreSede }}</h5>
                                                        <small class="text-muted">
                                                            {{ $esModelo && $sede->oficina_apoyo ? 'Oficina de apoyo' : 'Sede regional' }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2 mb-3">
                                                    <span class="badge {{ $vigentesSede ? 'bg-danger' : 'bg-secondary' }}">
                                                        {{ $vigentesSede }} {{ $vigentesSede == 1 ? 'bloqueo de sede' : 'bloqueos de sede' }}
                                                    </span>
                                                    <span class="badge {{ $vigentesConc ? 'text-dark bg-warning' : 'bg-secondary' }}">
                                                        {{ $vigentesConc }} de {{ $vigentesConc == 1 ? 'conciliador' : 'conciliadores' }}
                                                    </span>
                                                </div>

                                                <p class="small text-muted mb-3">
                                                    @if ($proximo)
                                                        Próximo: <b>{{ \Carbon\Carbon::parse($proximo->fecha_inicio)->format('d/m/Y') }}</b>
                                                        &middot; {{ $proximo->descripcion }}
                                                    @else
                                                        Sin bloqueos vigentes.
                                                    @endif
                                                </p>

                                                <div class="d-grid gap-2 mt-auto">
                                                    @if ($idSede)
                                                        <a href="{{ route('sede.calendario', $idSede) }}"
                                                           class="btn text-white" style="background-color:#496163;">
                                                            <i class="bi bi-calendar3"></i> Ver calendario
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-secondary btn-abrir-bloqueo"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalBloqueoUnificado"
                                                            data-sede="{{ $nombreSede }}">
                                                        <i class="bi bi-calendar-plus"></i> Nuevo bloqueo
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-center text-muted my-4">No hay sedes registradas en el catálogo.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalBloqueoUnificado" tabindex="-1" aria-labelledby="modalBloqueoUnificadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('bloqueoSede') }}" method="POST" id="formBloqueoMaster">
                    @csrf
                    <input type="hidden" name="sede_id" id="modal_sede_id" value="">

                    <div class="modal-header" style="background:#354647; color: white;">
                        <h5 class="modal-title"><i class="bi bi-shield-lock"></i> Restricción de Agenda: <span id="txtSedeTitulo" class="fw-bold text-warning"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label d-block fw-bold text-dark">1. Ámbito de Cobertura:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cobertura" id="cobSede" value="todos" checked>
                                    <label class="form-check-label text-dark fw-semibold" for="cobSede">Bloquear Sede Completa</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cobertura" id="cobConciliador" value="individual">
                                    <label class="form-check-label text-dark fw-semibold" for="cobConciliador">Bloquear Conciliador Específico</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="div_selector_conciliador" style="display: none;">
                            <div class="col-md-12">
                                <label for="conciliador_id" class="fw-semibold text-danger">Seleccione al Conciliador:</label>
                                <select name="conciliador_id" id="conciliador_id" class="form-control">
                                    <option value="">-- Seleccione un Conciliador --</option>
                                    @foreach($conciliadores as $con)
                                        <option value="{{ $con->id }}">{{ $con->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="opacity-25">

                        <label class="form-label d-block fw-bold text-dark">2. Periodo de la Restricción:</label>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Fecha de inicio:</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Fecha final:</label>
                                <input type="date" name="fecha_final" id="fecha_final" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Módulo del Centro a Afectar:</label>
                                <select name="tipo" class="form-control" required>
                                    <option value="Todos">Todos (Bloqueo Completo)</option>
                                    <option value="Audiencias">Audiencias</option>
                                    <option value="Ratificaciones">Ratificaciones</option>
                                    <option value="Cumplimientos">Cumplimientos</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Régimen Laboral del Día:</label>
                                <select name="descripcion" class="form-control" required>
                                    <option value="Inhabil">Día Inhábil (Suspensión de Términos)</option>
                                    <option value="No inhabil" selected>No Inhábil (Suspensión Interna / Junta)</option>
                                </select>
                            </div>
                        </div>

                        <hr class="opacity-25">

                        <label class="form-label d-block fw-bold text-dark">4. Configuración de Tiempo:</label>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="bloquear_todo_el_dia" name="bloquear_todo_el_dia" value="1" checked style="cursor:pointer;">
                                    <label class="form-check-label fw-bold text-dark" for="bloquear_todo_el_dia" style="cursor:pointer;">
                                        <i class="bi bi-clock-fill text-primary"></i> Bloquear todo el día
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="es_recurrente" name="es_recurrente" value="1" style="cursor:pointer;">
                                    <label class="form-check-label fw-bold text-dark" for="es_recurrente" style="cursor:pointer;">
                                        <i class="bi bi-repeat text-success"></i> ¿Es bloqueo recurrente?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="wrapper_horas" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-danger">Hora Inicio:</label>
                                <input type="time" class="form-control" name="hora_inicio" id="hora_inicio" value="08:00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-danger">Hora Final:</label>
                                <input type="time" class="form-control" name="hora_final" id="hora_final" value="15:00">
                            </div>
                        </div>

                        <div class="row mb-3" id="wrapper_dias_recurrentes" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary d-block">Selecciona los días a repetir:</label>
                                <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-light">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_lunes" value="1">
                                        <label class="form-check-label text-dark" for="chk_lunes">Lunes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_martes" value="2">
                                        <label class="form-check-label text-dark" for="chk_martes">Martes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_miercoles" value="3">
                                        <label class="form-check-label text-dark" for="chk_miercoles">Miércoles</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_jueves" value="4">
                                        <label class="form-check-label text-dark" for="chk_jueves">Jueves</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_viernes" value="5">
                                        <label class="form-check-label text-dark" for="chk_viernes">Viernes</label>
                                    </div>
                                </div>
                                <small class="text-muted"><i class="bi bi-info-circle"></i> Solo se restringirán los días seleccionados dentro de las fechas configuradas.</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-lock-fill"></i> Aplicar Restricción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('body_end')
    <div id="menu_carga" style="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
    @endpush
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/estadistica/estadistica.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Prevenir congelamiento de fondo gris moviendo el modal al final del body
            if ($('#modalBloqueoUnificado').length) {
                $('#modalBloqueoUnificado').appendTo("body");
            }

            // NUEVO NUEVO: Capturar y pintar los datos de la sede seleccionada en el modal al dar clic
            $(document).on('click', '.btn-abrir-bloqueo', function() {
                let sede = $(this).data('sede');
                $('#modal_sede_id').val(sede);
                $('#txtSedeTitulo').text(sede);
            });

            // Cambio de cobertura (Sede completa vs Conciliador)
            $('input[name="cobertura"]').on('change', function() {
                if ($(this).val() === 'individual') {
                    $('#div_selector_conciliador').slideDown(200);
                    $('#conciliador_id').attr('required', 'required');
                    $('#formBloqueoMaster').attr('action', "{{ route('bloqueoConciliador') }}");
                } else {
                    $('#div_selector_conciliador').slideUp(200);
                    $('#conciliador_id').removeAttr('required').val('');
                    $('#formBloqueoMaster').attr('action', "{{ route('bloqueoSede') }}");
                }
            });

            // Control de "Bloquear todo el día"
            $('#bloquear_todo_el_dia').on('change', function() {
                if (this.checked) {
                    $('#wrapper_horas').slideUp(200);
                    $('#hora_inicio').removeAttr('required');
                    $('#hora_final').removeAttr('required');
                } else {
                    $('#wrapper_horas').slideDown(200);
                    $('#hora_inicio').attr('required', 'required');
                    $('#hora_final').attr('required', 'required');
                }
            });

            // Control de "Es Recurrente"
            $('#es_recurrente').on('change', function() {
                if (this.checked) {
                    $('#wrapper_dias_recurrentes').slideDown(200);
                } else {
                    $('#wrapper_dias_recurrentes').slideUp(200);
                    $('.chk-dia-semana').prop('checked', false);
                }
            });

            // Sincronización lógica de fechas elementales
            $('#fecha_inicio').on('change', function() {
                $('#fecha_final').attr('min', $(this).val());
            });

            // Validación antes de enviar el formulario
            $('#formBloqueoMaster').on('submit', function(e) {
                if ($('#es_recurrente').is(':checked') && $('.chk-dia-semana:checked').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta un dato',
                        text: 'Selecciona al menos un día de la semana para aplicar la recurrencia.',
                        confirmButtonColor: '#496163'
                    });
                    return false;
                }
            });

            // El alta responde con back(), así que el aviso llega en la recarga.
            // Éxito como toast; error en modal, para que no se pierda el motivo.
            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: @json($errors->first()),
                    confirmButtonColor: '#496163'
                });
            @endif
        });

        // [SICONCILIO] mostrar_sedes()/mostrar_conciliador() se eliminaron junto con la
        // tabla de bloqueos por sede: ese historial ahora vive en el calendario de cada sede.
    </script>
@endsection