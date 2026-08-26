@extends('layouts.app')

@php
    $mesesCortos = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Bloqueos de {{ $sede->nombre }}</h3>
        </div>

        <div class="section-body">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Éxito:</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="mb-3">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('configuracion_sedes') }}">
                    <i class="bi bi-arrow-left"></i> Sedes
                </a>
            </div>

            <div class="cal-wrap">
                <div class="cal-toolbar">
                    <div class="cal-datechip">
                        <small>{{ $mesesCortos[(int) now()->format('n')] }}</small>
                        <b>{{ now()->format('j') }}</b>
                    </div>

                    <div class="cal-heading">
                        <h4 class="cal-title" id="calTitulo">&nbsp;</h4>
                        <div class="cal-sub" id="calRango">&nbsp;</div>
                    </div>

                    <div class="cal-actions">
                        <select class="cal-select" id="calFiltro" aria-label="Filtrar bloqueos">
                            <option value="todos">Todos los bloqueos</option>
                            <option value="sede">Solo la sede</option>
                            <option value="conciliador">Solo conciliadores</option>
                        </select>

                        <div class="cal-nav">
                            <button type="button" id="calPrev" aria-label="Mes anterior"><i class="bi bi-arrow-left"></i></button>
                            <button type="button" id="calHoy">Hoy</button>
                            <button type="button" id="calNext" aria-label="Mes siguiente"><i class="bi bi-arrow-right"></i></button>
                        </div>

                        <select class="cal-select" id="calVista" aria-label="Cambiar vista">
                            <option value="dayGridMonth">Vista mes</option>
                            <option value="listMonth">Vista lista</option>
                        </select>

                        <button type="button" class="cal-btn-primary btn-nuevo-bloqueo">
                            <i class="bi bi-plus-lg"></i> Nuevo bloqueo
                        </button>
                    </div>
                </div>

                <div class="cal-leyenda">
                    <span><i class="leyenda" style="background:#6A0F49;"></i> Día inhábil</span>
                    <span><i class="leyenda" style="background:#B5824A;"></i> Horario bloqueado</span>
                    <span><i class="leyenda" style="background:#496163;"></i> Conciliador</span>
                    <span class="ms-auto text-muted">Clic en un día para crear &middot; clic en un bloqueo para editarlo</span>
                </div>

                <div id="calendarioSede"></div>
            </div>

        </div>
    </section>

    {{-- Un solo modal para alta y edición: cambia su action y su _method según el caso. --}}
    <div class="modal fade" id="modalBloqueo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="formBloqueo" action="{{ route('bloqueoSede') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="">
                    <input type="hidden" name="sede_id" id="sede_id" value="{{ $sede->nombre }}">

                    <div class="modal-header" style="background:#6A0F49; color:#fff;">
                        <h5 class="modal-title">
                            <i class="bi bi-shield-lock"></i>
                            <span id="tituloModal">Nuevo bloqueo</span> &middot; {{ $sede->nombre }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">

                        <div class="row mb-3" id="wrapper_cobertura">
                            <div class="col-12">
                                <label class="form-label d-block fw-bold text-dark">1. Ámbito</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cobertura" id="cobSede" value="todos" checked>
                                    <label class="form-check-label text-dark" for="cobSede">Toda la sede</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cobertura" id="cobConciliador" value="individual">
                                    <label class="form-check-label text-dark" for="cobConciliador">Un conciliador</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="wrapper_conciliador" style="display:none;">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">Conciliador</label>
                                <select name="conciliador_id" id="conciliador_id" class="form-control">
                                    <option value="">Selecciona...</option>
                                    @foreach ($conciliadores as $con)
                                        <option value="{{ $con->id }}">{{ $con->name }}</option>
                                    @endforeach
                                </select>
                                @if ($conciliadores->isEmpty())
                                    <small class="text-muted">No hay conciliadores registrados en esta sede.</small>
                                @endif
                            </div>
                        </div>

                        <div id="detalle_edicion" class="alert alert-light border small text-dark" style="display:none;"></div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">2. Fecha inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha final</label>
                                <input type="date" name="fecha_final" id="fecha_final" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">3. Módulo afectado</label>
                                <select name="tipo" id="tipo" class="form-control" required>
                                    <option value="Todos">Todos</option>
                                    <option value="Audiencias">Audiencias</option>
                                    <option value="Ratificaciones">Ratificaciones</option>
                                    <option value="Cumplimientos">Cumplimientos</option>
                                    <option value="Bloqueo por permiso">Bloqueo por permiso</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Régimen</label>
                                <select name="descripcion" id="descripcion" class="form-control" required>
                                    <option value="Inhabil">Inhábil</option>
                                    <option value="No inhabil">No inhábil</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="bloquear_todo_el_dia"
                                           name="bloquear_todo_el_dia" value="1" checked style="cursor:pointer;">
                                    <label class="form-check-label text-dark" for="bloquear_todo_el_dia">Bloquear todo el día</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="wrapper_recurrente">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="es_recurrente"
                                           name="es_recurrente" value="1" style="cursor:pointer;">
                                    <label class="form-check-label text-dark" for="es_recurrente">Repetir por día de la semana</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="wrapper_horas" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Hora inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" id="hora_inicio" value="08:00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Hora final</label>
                                <input type="time" class="form-control" name="hora_final" id="hora_final" value="15:00">
                            </div>
                        </div>

                        <div class="row mb-3" id="wrapper_dias_recurrentes" style="display:none;">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">Días de la semana</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_lunes" value="1">
                                        <label class="form-check-label text-dark" for="chk_lunes">Lunes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_martes" value="2">
                                        <label class="form-check-label text-dark" for="chk_martes">Martes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_miercoles" value="3">
                                        <label class="form-check-label text-dark" for="chk_miercoles">Miércoles</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_jueves" value="4">
                                        <label class="form-check-label text-dark" for="chk_jueves">Jueves</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chk-dia-semana" type="checkbox" name="dias_semana[]" id="chk_viernes" value="5">
                                        <label class="form-check-label text-dark" for="chk_viernes">Viernes</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color:#6A0F49;" id="btnGuardar">Guardar</button>
                    </div>
                </form>

                {{-- Form aparte: un form no puede anidarse dentro de otro. --}}
                <form method="POST" id="formEliminar" action="" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page_css')
    <style>
        /* ---------------------------------------------------------------
           Contenedor y barra superior propios. El headerToolbar de
           FullCalendar viene apagado: estos controles lo sustituyen.
        --------------------------------------------------------------- */
        .cal-wrap {
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 14px;
            overflow: hidden;
        }

        .cal-toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-bottom: 1px solid #eceef1;
            flex-wrap: wrap;
        }

        .cal-datechip {
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            padding: 6px 10px;
            text-align: center;
            min-width: 54px;
            line-height: 1.15;
            flex-shrink: 0;
        }

        .cal-datechip small {
            display: block;
            font-size: 10px;
            letter-spacing: .08em;
            color: #98a2b3;
            font-weight: 700;
        }

        .cal-datechip b { font-size: 18px; color: #1f2937; }

        .cal-heading { min-width: 0; }

        .cal-title {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #111827;
            text-transform: capitalize;
            line-height: 1.2;
        }

        .cal-sub { font-size: 12px; color: #98a2b3; }

        .cal-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cal-select {
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            padding: 7px 10px;
            font-size: 13px;
            color: #374151;
            background-color: #fff;
        }

        .cal-nav {
            display: flex;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .cal-nav button {
            border: 0;
            background: #fff;
            padding: 7px 13px;
            font-size: 13px;
            color: #374151;
        }

        .cal-nav button:hover { background: #f6f7f9; }
        .cal-nav button + button { border-left: 1px solid #e6e8eb; }

        .cal-btn-primary {
            background-color: #6A0F49;
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .cal-btn-primary:hover { background-color: #530b39; color: #fff; }

        .cal-leyenda {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 9px 18px;
            border-bottom: 1px solid #eceef1;
            font-size: 12px;
            color: #667085;
        }

        .cal-leyenda .leyenda {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 3px;
            margin-right: 5px;
        }

        /* ---------------------------------------------------------------
           Rejilla
        --------------------------------------------------------------- */
        #calendarioSede .fc { --fc-border-color: #eceef1; }
        #calendarioSede .fc-scrollgrid { border-width: 0; }
        #calendarioSede .fc-theme-standard td,
        #calendarioSede .fc-theme-standard th { border-color: #eceef1; }

        #calendarioSede .fc-col-header-cell { background: #fafbfc; padding: 9px 0; }

        #calendarioSede .fc-col-header-cell-cushion {
            font-size: 12px;
            font-weight: 600;
            color: #667085;
            text-transform: capitalize;
            text-decoration: none;
        }

        #calendarioSede .fc-daygrid-day-frame { min-height: 118px; padding: 6px; }

        /* FullCalendar alinea el número a la derecha; la referencia lo lleva a la izquierda */
        #calendarioSede .fc-daygrid-day-top { flex-direction: row; }

        #calendarioSede .fc-daygrid-day-number {
            font-size: 12px;
            color: #475467;
            text-decoration: none;
            padding: 2px 4px;
        }

        #calendarioSede .fc-day-other .fc-daygrid-day-number { color: #c8cdd5; }
        #calendarioSede .fc-day-today { background-color: #fff !important; }

        #calendarioSede .fc-day-today .fc-daygrid-day-number {
            background: #111827;
            color: #fff;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-weight: 600;
        }

        #calendarioSede .fc-daygrid-more-link {
            font-size: 11px;
            color: #98a2b3;
            text-decoration: none;
            padding-left: 3px;
        }

        /* ---------------------------------------------------------------
           Píldoras de evento
        --------------------------------------------------------------- */
        #calendarioSede .fc-daygrid-event-harness { margin-bottom: 4px; }

        #calendarioSede .fc-daygrid-event {
            border: 0;
            border-radius: 7px;
            padding: 3px 8px;
            font-size: 11.5px;
            box-shadow: none;
            cursor: pointer;
        }

        #calendarioSede .fc-daygrid-event:hover { filter: brightness(.96); }

        #calendarioSede .evt { display: flex; align-items: center; gap: 6px; width: 100%; }

        #calendarioSede .evt-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex: 0 0 6px;
            background: currentColor;
        }

        #calendarioSede .evt-title {
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }

        #calendarioSede .evt-time { flex: 0 0 auto; font-size: 10.5px; opacity: .7; }

        /* Tintes: fondo suave, texto y punto en el color fuerte de la categoría */
        #calendarioSede .evt-inhabil     { background-color: #f6e9f0 !important; color: #6A0F49 !important; }
        #calendarioSede .evt-horario     { background-color: #f8f0e4 !important; color: #8a5a20 !important; }
        #calendarioSede .evt-conciliador { background-color: #eaf0f0 !important; color: #2f4b4d !important; }

        #calendarioSede .fc-list-event td { cursor: pointer; }
        #calendarioSede .fc-list-day-cushion { background: #fafbfc; }

        @media (max-width: 767px) {
            .cal-actions { margin-left: 0; width: 100%; }
            .cal-toolbar { gap: 10px; }
            #calendarioSede .fc-daygrid-day-frame { min-height: 84px; }
        }
    </style>
@endsection

@section('page_js')
    {{-- El bundle index.global de FullCalendar 6 no incluye los idiomas: sin este
         archivo, locale:'es' se ignora en silencio y el calendario sale en inglés. --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales/es.global.min.js"></script>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            var URL_EVENTOS  = "{{ route('calendario.bloqueos') }}";
            var URL_ALTA     = "{{ route('bloqueoSede') }}";
            var URL_ALTA_CON = "{{ route('bloqueoConciliador') }}";
            var URL_EDITAR   = "{{ url('/bloqueo') }}";
            var SEDE         = "{{ $sede->nombre }}";
            var HOY          = "{{ now()->toDateString() }}";

            // Meses en duro: no dependemos de Intl ni del locale del navegador.
            var MESES  = ['enero','febrero','marzo','abril','mayo','junio',
                          'julio','agosto','septiembre','octubre','noviembre','diciembre'];
            var CORTOS = ['ene','feb','mar','abr','may','jun',
                          'jul','ago','sep','oct','nov','dic'];

            var modal = new bootstrap.Modal(document.getElementById('modalBloqueo'));
            var calendario = null;
            var filtro = 'todos';

            function tituloDe(d) { return MESES[d.getMonth()] + ' ' + d.getFullYear(); }
            function cortaDe(d)  { return d.getDate() + ' ' + CORTOS[d.getMonth()] + ' ' + d.getFullYear(); }

            // ---------------------------------------------------------------- modal
            function mostrarHoras(mostrar) {
                $('#wrapper_horas').toggle(mostrar);
                if (mostrar) {
                    $('#hora_inicio, #hora_final').attr('required', 'required');
                } else {
                    $('#hora_inicio, #hora_final').removeAttr('required');
                }
            }

            function modoAlta(fecha) {
                $('#tituloModal').text('Nuevo bloqueo');
                $('#form_method').val('');
                $('#formBloqueo').attr('action', URL_ALTA);
                $('#formEliminar').attr('action', '').addClass('d-none');
                $('#btnEliminar').remove();

                $('#wrapper_cobertura, #wrapper_recurrente').show();
                $('#detalle_edicion').hide().empty();
                $('#cobSede').prop('checked', true);
                $('#wrapper_conciliador').hide();
                $('#conciliador_id').removeAttr('required').val('');

                $('#bloquear_todo_el_dia').prop('checked', true);
                mostrarHoras(false);
                $('#es_recurrente').prop('checked', false);
                $('#wrapper_dias_recurrentes').hide();
                $('.chk-dia-semana').prop('checked', false);

                $('#tipo').val('Todos');
                $('#descripcion').val('Inhabil');

                // El alta valida after_or_equal:today en el servidor.
                $('#fecha_inicio, #fecha_final').attr('min', HOY);
                $('#fecha_inicio').val(fecha || HOY);
                $('#fecha_final').val(fecha || HOY);

                modal.show();
            }

            function modoEdicion(evento) {
                var p = evento.extendedProps;

                $('#tituloModal').text('Editar bloqueo');
                $('#form_method').val('PUT');
                $('#formBloqueo').attr('action', URL_EDITAR + '/' + p.bloqueo_id);
                $('#formEliminar').attr('action', URL_EDITAR + '/' + p.bloqueo_id);

                // El ámbito y la recurrencia no se cambian al editar: se recaptura.
                $('#wrapper_cobertura, #wrapper_recurrente, #wrapper_dias_recurrentes').hide();
                $('#conciliador_id').removeAttr('required');
                $('.chk-dia-semana').prop('checked', false);
                $('#es_recurrente').prop('checked', false);

                var $detalle = $('#detalle_edicion').empty().show();
                var $linea = $('<span>');

                if (p.ambito === 'sede') {
                    $linea.append(document.createTextNode('Aplica a toda la sede'));
                } else {
                    $linea.append(document.createTextNode('Conciliador: '));
                    $linea.append($('<b>').text(p.conciliador || 'sin nombre'));
                }

                $linea.append(document.createTextNode(' · registro #' + p.bloqueo_id));
                $detalle.append($('<i class="bi bi-info-circle me-1">')).append($linea);

                // Al editar sí se permiten fechas pasadas, para corregir capturas viejas.
                $('#fecha_inicio, #fecha_final').removeAttr('min');
                $('#fecha_inicio').val(p.fecha_inicio);
                $('#fecha_final').val(p.fecha_final);
                $('#tipo').val(p.modulo || 'Todos');
                $('#descripcion').val(p.regimen || 'Inhabil');

                $('#bloquear_todo_el_dia').prop('checked', !!p.jornada);
                mostrarHoras(!p.jornada);
                $('#hora_inicio').val((p.horario_inicio || '08:00:00').substring(0, 5));
                $('#hora_final').val((p.horario_final || '15:00:00').substring(0, 5));

                if (!$('#btnEliminar').length) {
                    $('#btnGuardar').before(
                        $('<button type="button" class="btn btn-danger me-auto" id="btnEliminar">')
                            .html('<i class="bi bi-trash"></i> Eliminar')
                    );
                }

                modal.show();
            }

            // ---------------------------------------------------------------- calendario
            var el = document.getElementById('calendarioSede');

            if (el && typeof FullCalendar !== 'undefined') {
                calendario = new FullCalendar.Calendar(el, {
                    locale: 'es',
                    firstDay: 1,
                    initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
                    headerToolbar: false,
                    height: 'auto',
                    fixedWeekCount: false,
                    dayMaxEvents: 3,
                    dayHeaderFormat: { weekday: 'short' },
                    noEventsText: 'Sin bloqueos en este periodo',

                    datesSet: function (info) {
                        var fin = new Date(info.view.currentEnd.getTime() - 86400000);
                        $('#calTitulo').text(tituloDe(info.view.currentStart));
                        $('#calRango').text(cortaDe(info.view.currentStart) + ' – ' + cortaDe(fin));
                    },

                    events: function (fetchInfo, success, failure) {
                        $.ajax({
                            url: URL_EVENTOS,
                            data: {
                                sede: SEDE,
                                sede_exacta: 1,
                                start: fetchInfo.startStr,
                                end: fetchInfo.endStr
                            },
                            success: function (data) {
                                if (filtro === 'todos') {
                                    success(data);
                                    return;
                                }

                                success(data.filter(function (e) {
                                    return (e.extendedProps || {}).ambito === filtro;
                                }));
                            },
                            error: function () { failure('No se pudieron cargar los bloqueos'); }
                        });
                    },

                    eventContent: function (arg) {
                        var p = arg.event.extendedProps || {};
                        var cont = document.createElement('div');
                        cont.className = 'evt';

                        var dot = document.createElement('span');
                        dot.className = 'evt-dot';
                        cont.appendChild(dot);

                        var titulo = document.createElement('span');
                        titulo.className = 'evt-title';
                        titulo.textContent = arg.event.title;
                        cont.appendChild(titulo);

                        var hora = document.createElement('span');
                        hora.className = 'evt-time';
                        hora.textContent = p.jornada
                            ? 'Todo el día'
                            : (p.horario_inicio || '').substring(0, 5);
                        cont.appendChild(hora);

                        return { domNodes: [cont] };
                    },

                    moreLinkContent: function (arg) { return arg.num + ' más...'; },

                    eventClick: function (info) {
                        info.jsEvent.preventDefault();
                        modoEdicion(info.event);
                    },

                    dateClick: function (info) { modoAlta(info.dateStr); }
                });

                calendario.render();
            }

            // ---------------------------------------------------------------- controles
            $('#calPrev').on('click', function () { if (calendario) calendario.prev(); });
            $('#calNext').on('click', function () { if (calendario) calendario.next(); });
            $('#calHoy').on('click',  function () { if (calendario) calendario.today(); });

            $('#calVista').val(window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth');

            $('#calVista').on('change', function () {
                if (calendario) calendario.changeView(this.value);
            });

            $('#calFiltro').on('change', function () {
                filtro = this.value;
                if (calendario) calendario.refetchEvents();
            });

            $('.btn-nuevo-bloqueo').on('click', function () { modoAlta(null); });

            $('input[name="cobertura"]').on('change', function () {
                if ($(this).val() === 'individual') {
                    $('#wrapper_conciliador').slideDown(200);
                    $('#conciliador_id').attr('required', 'required');
                    $('#formBloqueo').attr('action', URL_ALTA_CON);
                    // bloqueoConciliador siempre exige horario explícito.
                    $('#bloquear_todo_el_dia').prop('checked', false);
                    mostrarHoras(true);
                    $('#wrapper_recurrente').hide();
                    $('#es_recurrente').prop('checked', false);
                    $('#wrapper_dias_recurrentes').hide();
                } else {
                    $('#wrapper_conciliador').slideUp(200);
                    $('#conciliador_id').removeAttr('required').val('');
                    $('#formBloqueo').attr('action', URL_ALTA);
                    $('#wrapper_recurrente').show();
                }
            });

            $('#bloquear_todo_el_dia').on('change', function () { mostrarHoras(!this.checked); });

            $('#es_recurrente').on('change', function () {
                $('#wrapper_dias_recurrentes').toggle(this.checked);
                if (!this.checked) { $('.chk-dia-semana').prop('checked', false); }
            });

            $('#fecha_inicio').on('change', function () {
                $('#fecha_final').attr('min', $(this).val());
                if ($('#fecha_final').val() < $(this).val()) {
                    $('#fecha_final').val($(this).val());
                }
            });

            $(document).on('click', '#btnEliminar', function () {
                if (confirm('Se eliminará este bloqueo de la agenda de ' + SEDE + '. ¿Continuar?')) {
                    $('#formEliminar').removeClass('d-none').trigger('submit');
                }
            });

            $('#formBloqueo').on('submit', function (e) {
                if ($('#es_recurrente').is(':checked') && $('.chk-dia-semana:checked').length === 0) {
                    e.preventDefault();
                    alert('Selecciona al menos un día de la semana para la recurrencia.');
                    return false;
                }
            });
        });
    </script>
@endsection
