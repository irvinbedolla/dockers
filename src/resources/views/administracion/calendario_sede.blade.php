@extends('layouts.app')
@section('title', 'Bloqueos por Sede')

@php
    $mesesCortos = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Bloqueos de {{ $sede->nombre }}</h3>
        </div>

        <div class="section-body">

            {{-- El resultado de guardar, editar o eliminar se avisa con SweetAlert
                 al final de este archivo, no con alertas dentro del contenido. --}}

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
                        <div class="cal-sub">
                            <span id="calRango">&nbsp;</span>
                            <span id="calAgenda" class="cal-agenda"></span>
                        </div>
                    </div>

                    <div class="cal-actions">
                        @if ($conciliadores->isNotEmpty())
                            <select class="cal-select" id="calConciliador" aria-label="Agenda a consultar">
                                <option value="">Toda la sede</option>
                                @foreach ($conciliadores as $con)
                                    <option value="{{ $con->id }}">{{ $con->name }}</option>
                                @endforeach
                            </select>
                        @endif

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
                    <span><i class="leyenda" style="background:#496163;"></i> Día inhábil</span>
                    <span><i class="leyenda" style="background:#CEA845;"></i> Horario bloqueado</span>
                    <span><i class="leyenda" style="background:#8A9A9B;"></i> Conciliador</span>
                    <span id="leyendaJornada" style="display:none;"><i class="leyenda" style="background:#f2f3f5;"></i> Fuera de su jornada</span>
                    <span class="ms-auto text-muted">Clic en un día para crear &middot; clic en un bloqueo para editarlo</span>
                </div>

                <div id="calZona" class="cal-zona" aria-busy="true">
                    {{-- El esqueleto se pinta desde el HTML, sin esperar a que corra
                         el JS: lo primero que ve el usuario es la forma del mes y no
                         una tarjeta vacía. --}}
                    <div id="calSkeleton" class="cal-skeleton">
                        <span class="visually-hidden" role="status">Cargando bloqueos…</span>

                        <div class="sk-head" aria-hidden="true">
                            @for ($i = 0; $i < 7; $i++)
                                <div class="sk-head-cell"><span class="sk-bar sk-dia"></span></div>
                            @endfor
                        </div>

                        <div class="sk-grid" aria-hidden="true">
                            @for ($i = 0; $i < 35; $i++)
                                <div class="sk-cell">
                                    <span class="sk-bar sk-num"></span>
                                    @if ($i % 3 === 0)
                                        <span class="sk-bar sk-evt"></span>
                                    @endif
                                    @if ($i % 7 === 2)
                                        <span class="sk-bar sk-evt sk-evt-corto"></span>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div id="calendarioSede" class="cal-fc is-oculto"></div>
                </div>
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

                    <div class="modal-header" style="background:#496163; color:#fff;">
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
                        <button type="submit" class="btn text-white" style="background-color:#496163;" id="btnGuardar">Guardar</button>
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
    {{-- Los estilos del calendario viven en un solo archivo, compartido con la
         agenda del home: assets/css/calendario.css --}}
    <link href="{{ asset('assets/css/calendario.css') }}" rel="stylesheet">
@endsection

@section('page_js')
    {{-- El bundle index.global de FullCalendar 6 no incluye los idiomas: sin este
         archivo, locale:'es' se ignora en silencio y el calendario sale en inglés. --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Jornada semanal de cada conciliador (permisos_conciliador) en formato
            // businessHours. Va completa a la vista porque el selector cambia de
            // persona sin recargar la página.
            var JORNADAS = {!! json_encode($jornadas) !!};

            var modal = new bootstrap.Modal(document.getElementById('modalBloqueo'));
            var calendario = null;
            var filtro = 'todos';

            // Cada mes se pide una sola vez y se guarda en memoria. Volver a un mes ya
            // visto —o cambiar el filtro— no vuelve a pegarle al servidor. El caché
            // muere en la recarga que sigue a cualquier alta, edición o baja, así que
            // no puede quedarse con datos viejos.
            var cacheMeses = {};
            var desdeCache = false;
            var vigiaEsqueleto = null;
            var conciliador = new URLSearchParams(window.location.search).get('conciliador') || '';

            // El filtro de ámbito se aplica sobre lo ya descargado: cambiarlo no
            // justifica otra consulta.
            function aplicarFiltro(data) {
                if (filtro === 'todos') {
                    return data;
                }

                return data.filter(function (e) {
                    return (e.extendedProps || {}).ambito === filtro;
                });
            }

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
                if (conciliador) {
                    // Consultando a una persona, lo natural es bloquearla a ella.
                    $('#cobConciliador').prop('checked', true).trigger('change');
                    $('#conciliador_id').val(conciliador);
                } else {
                    $('#cobSede').prop('checked', true);
                    $('#wrapper_conciliador').hide();
                    $('#conciliador_id').removeAttr('required').val('');
                }

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

                    loading: function (cargando) {
                        var $sk = $('#calSkeleton');
                        var $zona = $('#calZona');

                        // El contenedor nunca se esconde: si el aviso de fin de carga
                        // no llegara, se ve la rejilla y no un esqueleto eterno.
                        $('#calendarioSede').removeClass('is-oculto');
                        $zona.attr('aria-busy', cargando ? 'true' : 'false')
                             .toggleClass('is-cargando', !!cargando);

                        clearTimeout(vigiaEsqueleto);

                        if (cargando) {
                            if (!desdeCache) { $sk.show(); }
                            // Red de seguridad: pase lo que pase, el velo se quita.
                            vigiaEsqueleto = setTimeout(function () {
                                $sk.hide().addClass('is-overlay');
                                $zona.removeClass('is-cargando').attr('aria-busy', 'false');
                            }, 6000);
                            return;
                        }

                        $sk.hide().addClass('is-overlay');

                        setTimeout(function () {
                            if (calendario) calendario.updateSize();
                        }, 0);
                    },

                    datesSet: function (info) {
                        var fin = new Date(info.view.currentEnd.getTime() - 86400000);
                        $('#calTitulo').text(tituloDe(info.view.currentStart));
                        $('#calRango').text(cortaDe(info.view.currentStart) + ' – ' + cortaDe(fin));
                    },

                    events: function (fetchInfo, success, failure) {
                        var clave = conciliador + '|' + fetchInfo.startStr + '|' + fetchInfo.endStr;

                        if (cacheMeses[clave]) {
                            // Bandera para que el esqueleto no parpadee en una respuesta
                            // que se resuelve en el mismo frame.
                            desdeCache = true;
                            success(aplicarFiltro(cacheMeses[clave]));
                            desdeCache = false;
                            return;
                        }

                        $.ajax({
                            url: URL_EVENTOS,
                            data: {
                                sede: SEDE,
                                sede_exacta: 1,
                                conciliador: conciliador,
                                start: fetchInfo.startStr,
                                end: fetchInfo.endStr
                            },
                            success: function (data) {
                                cacheMeses[clave] = data;
                                success(aplicarFiltro(data));
                            },
                            error: function () {
                                failure('No se pudieron cargar los bloqueos');

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'No se pudieron cargar los bloqueos',
                                    text: 'Revisa tu conexión y vuelve a intentar.',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true
                                });
                            }
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

            function aplicarConciliador(id, refrescar) {
                conciliador = id || '';

                // Consultando a una persona, el filtro sede/conciliador no aplica.
                $('#calFiltro').toggle(!conciliador);
                $('#leyendaJornada').toggle(!!conciliador);

                if (conciliador) {
                    filtro = 'todos';
                    $('#calFiltro').val('todos');
                    $('#calAgenda').text($('#calConciliador option:selected').text());
                } else {
                    $('#calAgenda').text('');
                }

                if (calendario) {
                    var jornada = conciliador ? JORNADAS[conciliador] : null;
                    // null = no hay horario capturado: mejor no sombrear nada.
                    calendario.setOption('businessHours', jornada ? jornada : false);

                    if (refrescar) {
                        calendario.refetchEvents();
                    }
                }

                var url = new URL(window.location.href);

                if (conciliador) {
                    url.searchParams.set('conciliador', conciliador);
                } else {
                    url.searchParams.delete('conciliador');
                }

                window.history.replaceState({}, '', url);
            }

            $('#calConciliador').on('change', function () {
                aplicarConciliador(this.value, true);
            });

            // Estado inicial: respeta el ?conciliador= de la URL.
            if (conciliador && $('#calConciliador option[value="' + conciliador + '"]').length) {
                $('#calConciliador').val(conciliador);
                aplicarConciliador(conciliador, false);
            } else {
                aplicarConciliador('', false);
            }

            $('.btn-nuevo-bloqueo').on('click', function () { modoAlta(null); });

            $('input[name="cobertura"]').on('change', function () {
                if ($(this).val() === 'individual') {
                    $('#wrapper_conciliador').slideDown(200);
                    $('#conciliador_id').attr('required', 'required');
                    $('#formBloqueo').attr('action', URL_ALTA_CON);
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
                // Se cierra el modal antes de abrir SweetAlert: si no, el foco que
                // atrapa el modal de Bootstrap pelea con el del diálogo.
                modal.hide();

                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar este bloqueo?',
                    text: 'Se quitará de la agenda de ' + SEDE + ' y la fecha volverá a estar disponible.',
                    showCancelButton: true,
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then(function (resultado) {
                    if (resultado.isConfirmed) {
                        $('#formEliminar').removeClass('d-none').trigger('submit');
                    } else {
                        modal.show();
                    }
                });
            });

            $('#formBloqueo').on('submit', function (e) {
                if ($('#es_recurrente').is(':checked') && $('.chk-dia-semana:checked').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta un dato',
                        text: 'Selecciona al menos un día de la semana para la recurrencia.',
                        confirmButtonColor: '#496163',
                        heightAuto: false
                    });
                    return false;
                }
            });

            // ---------------------------------------------------------------- resultado
            // El alta, la edición y la baja responden con back(), así que el aviso
            // llega en la recarga. Éxito como toast; error en modal, para que no se
            // pierda el motivo del rechazo.
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
    </script>
@endsection
