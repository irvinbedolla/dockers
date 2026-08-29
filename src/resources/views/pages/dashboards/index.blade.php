@extends('layouts.app')
@section('title', 'Agenda')

{{--
    Esta pantalla era un layout completo aparte: repetía navbar, sidebar y footer,
    y cargaba su propio Bootstrap 4.1.1 mientras el resto del sistema va en 5.3.
    Ahora extiende layouts.app, así que la barra superior y el sidebar salen de
    layouts/header.blade.php y layouts/sidebar.blade.php, una sola vez.
--}}

@section('page_css')
    {{-- Mismos estilos de calendario que la pantalla de bloqueos por sede --}}
    <link href="{{ asset('assets/css/calendario.css') }}" rel="stylesheet">
    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat rgb(249,249,249);
            opacity: .8;
        }
        /* El display:block de este selector por id le ganaba a .cal-fc.is-oculto,
           así que el esqueleto se quedaba arriba y el calendario aparecía debajo
           al mismo tiempo. El ancho lo resuelve el contenedor. */
        #calendar {
            width: 100% !important;
        }

        /* Evita que las celdas se vean comprimidas */
        .fc-view-harness {
            background-color: #fff;
        }

        /* Ajuste para que la Card de Bootstrap no le ponga padding excesivo */
        .card-body {
            padding: 15px !important;
        }
        @media (max-width: 768px) {
            /* El contenedor padre deja de ser horizontal */
            .flex-column.flex-md-row {
                align-items: stretch !important;
            }


            /* Quitamos el justify-content-center para que no baile el contenido */
            .justify-content-center {
                justify-content: flex-start !important;
            }

            .fc .fc-toolbar {
                display: flex;
                flex-direction: column;
                gap: 10px; /* Espacio entre fecha y botones */
            }

            /* Centrar el título y reducir su tamaño */
            .fc .fc-toolbar-title {
                font-size: 1.2rem !important;
                text-align: center;
                width: 100%;
            }

            /* Asegurar que los botones ocupen el ancho necesario sin amontonarse */
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 5px;
            }

            /* Ajustar tamaño de botones para que quepan mejor */
            .fc .fc-button {
                padding: 0.4em 0.6em !important;
                font-size: 0.85em !important;
            }
            .fc-event {
                background-color: transparent !important;
                border: 1px solid transparent !important; /* Borde invisible para que no brinque */
                box-shadow: none !important;
                border-radius: 6px !important;
                transition: background-color 0.2s ease, border-color 0.2s ease; /* Efecto de transición suave */
                cursor: pointer;
            }
            .fc-event:hover, .fc-event:focus {
                background-color: #f0f0f0 !important; /* Gris claro */
                border-color: #e2e2e2 !important;     /* Borde gris para enmarcarlo */
            }

            /* Forzar que el texto no se salga del cuadro */
            .custom-event-content .text-truncate {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
                width: 100%;
            }

        }
    </style>
@endsection

@section('content')
            <section class="section">
                <div class="section-header">
                    <h3 class="page__heading">Sistema integral para la Conciliación</h3>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        {{-- El <br> que separaba el logo del calendario se cambió por
                                             padding: se controla mejor y no depende de una línea vacía. --}}
                                        <div class="w-100 text-center" style="padding-bottom: 28px;">
                                            <img src="{{ asset('assets/images/ccl-r.png') }}" alt="SiConcilio"
                                                 class="img-fluid" style="max-height: 68px; width: auto;">
                                        </div>
                                        @if($userRole[0] != 'Solicitante')
                                            @php
                                                $mesesCortos = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

                                                // Un conciliador solo puede ver su propia agenda: el selector no
                                                // le ofrece ninguna decisión, así que va oculto y ya seleccionado.
                                                $esConciliador = ($userRole[0] ?? '') === 'Conciliador';
                                                $idUsuario     = auth()->id();
                                            @endphp

                                            <div class="col-12">
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
                                                            <select id="filtro-sede" class="cal-select" aria-label="Filtrar por sede">
                                                                <option value="Todos">Todas las sedes</option>
                                                                @foreach($sedes as $sede)
                                                                    <option value="{{ $sede }}">{{ $sede }}</option>
                                                                @endforeach
                                                            </select>

                                                            @if ($esConciliador)
                                                                {{-- Oculto, no eliminado: calendar.js lee su valor para filtrar. --}}
                                                                <input type="hidden" id="filter-conciliador" value="{{ $idUsuario }}">
                                                            @else
                                                                <select id="filter-conciliador" class="cal-select" aria-label="Filtrar por conciliador">
                                                                    <option value="">Todos los conciliadores</option>
                                                                    @foreach($conciliadores as $conciliador)
                                                                        <option value="{{ $conciliador['id'] }}" data-delegacion-id="{{ $conciliador['delegacion'] }}">{{ $conciliador['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @endif

                                                            <div class="cal-nav">
                                                                <button type="button" id="calPrev" aria-label="Anterior"><i class="bi bi-arrow-left"></i></button>
                                                                <button type="button" id="calHoy">Hoy</button>
                                                                <button type="button" id="calNext" aria-label="Siguiente"><i class="bi bi-arrow-right"></i></button>
                                                            </div>

                                                            <select id="calVista" class="cal-select" aria-label="Cambiar vista">
                                                                <option value="dayGridMonth">Vista mes</option>
                                                                <option value="dayGridWeek">Vista semana</option>
                                                                <option value="listWeek">Vista lista</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    {{-- Los cinco botones morados pasaron a ser pestañas: caben en una
                                                         línea, se envuelven solas en móvil y ya no hacen falta el
                                                         desplegable aparte ni la versión de escritorio duplicada. --}}
                                                    <div class="cal-tabs">
                                                        @if ($esConciliador)
                                                            {{-- Vista de mes con todas sus agendas juntas. Es la entrada
                                                                 natural para quien solo consulta lo suyo. --}}
                                                            <button type="button" class="cal-tab btn-calendar active" data-tipo="btn-todos">Todos</button>
                                                        @endif
                                                        <button type="button" class="cal-tab btn-calendar {{ $esConciliador ? '' : 'active' }}" data-tipo="btn-pagos">Cumplimientos</button>
                                                        <button type="button" class="cal-tab btn-calendar" data-tipo="btn-audiencias">Audiencias</button>
                                                        <button type="button" class="cal-tab btn-calendar" data-tipo="btn-conciliador">Cumplimientos en Audiencia</button>
                                                        <button type="button" class="cal-tab btn-calendar" data-tipo="btn-citas">Cumplimientos de Ratificación</button>
                                                        <button type="button" class="cal-tab btn-calendar" data-tipo="btn-ratificaciones">Ratificaciones</button>
                                                    </div>

                                                    <div class="cal-leyenda" id="calLeyenda" style="display:none;">
                                                        <span><i class="leyenda" style="background:#6A0F49;"></i> Cumplimientos</span>
                                                        <span><i class="leyenda" style="background:#496163;"></i> Audiencias</span>
                                                        <span><i class="leyenda" style="background:#2F6B6B;"></i> Cumplimientos en audiencia</span>
                                                        <span><i class="leyenda" style="background:#7A5C8E;"></i> Cumplimientos de ratificación</span>
                                                        <span><i class="leyenda" style="background:#B5824A;"></i> Ratificaciones</span>
                                                    </div>

                                                    <div id="calZona" class="cal-zona" aria-busy="true">
                                                        <div id="calSkeleton" class="cal-skeleton">
                                                            <span class="sr-only" role="status">Cargando agenda…</span>

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

                                                        <div id="calendar" class="cal-fc is-oculto"></div>
                                                    </div>

                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

    <div class="modal fade" id="evento" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                Cita
            </div>
        </div>
    </div>
</div>

    @push('body_end')
    <div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
    @endpush
@endsection

@section('page_js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>
    <script>
        const urlCitas          = "{{ route('citas.eventos') }}";
        const urlPagos          = "{{ route('pagos.eventos') }}";
        const urlConciliadores  = "{{ route('conciliador.eventos') }}";
        const urlAudiencias     = "{{ route('audiencias.eventos') }}";
        const urlRatificaciones = "{{ route('ratificaciones.eventos') }}";
        // El conciliador entra directo a "Todos", en vista de mes.
        const calArranqueTodos  = {{ ($userRole[0] ?? '') === 'Conciliador' ? 'true' : 'false' }};
        
        // Por si también la usas dentro de tu configuración de FullCalendar:
        const urlBloqueos       = "{{ route('calendario.bloqueos') }}"; 
    </script>
@endsection

@section('scripts')
    {{-- calendar.js se cargaba dos veces: su listener de DOMContentLoaded corría
         doble y montaba dos juegos de calendarios sobre el mismo contenedor. --}}
    <script src="{{ asset('assets/js/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/general/menu.js') }}"></script>
@endsection
