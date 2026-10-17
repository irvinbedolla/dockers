{{--
    Parcial de la agenda. Lo comparten la pantalla /agenda y el Inicio, así que
    el calendario existe una sola vez: cualquier arreglo aquí llega a las dos.
--}}
                <div class="section-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        @if($userRole[0] != 'Solicitante')
                                            @php
                                                $mesesCortos = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

                                                // Un conciliador solo puede ver su propia agenda: el selector no
                                                // le ofrece ninguna decisión, así que va oculto y ya seleccionado.
                                                $esConciliador = ($userRole[0] ?? '') === 'Conciliador';
                                                $idUsuario     = auth()->id();

                                                // La recepción sólo ve sus citas en línea: sin conciliadores,
                                                // sin descarga y con una sola pastilla. El servidor le cierra
                                                // además las otras fuentes (middleware SinRecepcion).
                                                $esRecepcion   = \App\Support\Recepcion::es(auth()->user());

                                                // El notificador sólo ve audiencias y sus notificaciones
                                                // pendientes (App\Support\AgendaNotificador; el servidor le
                                                // cierra lo demás con el middleware SinNotificador).
                                                $esNotificador = \App\Support\AgendaNotificador::soloNotificador(auth()->user());
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

                                                            {{-- Baja lo mismo que está en pantalla: el rango del calendario
                                                                 con los filtros de sede y conciliador ya aplicados. --}}
                                                            {{-- Sábado y domingo arrancan encogidos: casi nunca hay
                                                                 audiencias y se comían dos séptimas partes del ancho.
                                                                 No se ocultan, se minimizan: si se apagan de verdad,
                                                                 FullCalendar deja de pedir esos días al servidor. --}}
                                                            <button type="button" id="calFinde" class="cal-select" aria-pressed="false"
                                                                    title="Mostrar u ocultar las columnas de sábado y domingo">
                                                                <i class="bi bi-arrows-angle-expand"></i> Fin de semana
                                                            </button>

                                                            @unless ($esRecepcion || $esNotificador)
                                                                <button type="button" id="calExportar" class="cal-select" title="Descargar en Excel la agenda del rango visible">
                                                                    <i class="bi bi-file-earmark-excel"></i> Exportar
                                                                </button>
                                                            @endunless
                                                        </div>
                                                    </div>

                                                    {{-- Conciliadores como pastillas, en lugar del desplegable. La sede
                                                         va al frente y decide cuáles se ven: al elegir una, las de las
                                                         demás delegaciones se ocultan. Sólo salen los conciliadores con
                                                         actividad reciente (ver AgendaContexto). --}}
                                                    <div class="cal-personas">
                                                        <select id="filtro-sede" class="cal-select" aria-label="Filtrar por sede">
                                                            {{-- Recepción de una sola sede no elige: sin "Todas",
                                                                 porque las agendas viejas leen "Todos" como la región. --}}
                                                            @unless ($esRecepcion && count($sedes) === 1)
                                                                <option value="Todos">Todas las sedes</option>
                                                            @endunless
                                                            @foreach($sedes as $sede)
                                                                <option value="{{ $sede }}">{{ $sede }}</option>
                                                            @endforeach
                                                        </select>

                                                        @if ($esConciliador)
                                                            {{-- Un conciliador no elige: el valor va fijo y oculto,
                                                                 calendar.js lo lee igual que antes. --}}
                                                            <input type="hidden" id="filter-conciliador" value="{{ $idUsuario }}">
                                                        @elseif ($esNotificador || ($esRecepcion && $conciliadores->isEmpty()))
                                                            {{-- Las citas no tienen conciliador todavía: no hay nada
                                                                 que elegir. El input vacío hace que calendar.js no
                                                                 mande filtro, igual que "Todos". --}}
                                                            <input type="hidden" id="filter-conciliador" value="">
                                                        @else
                                                            <button type="button" class="cal-persona active" data-conciliador="">Todos</button>
                                                            @foreach($conciliadores as $conciliador)
                                                                <button type="button" class="cal-persona"
                                                                        data-conciliador="{{ $conciliador['id'] }}"
                                                                        data-delegacion="{{ $conciliador['delegacion'] }}"
                                                                        title="{{ $conciliador['name'] }}">{{ $conciliador['name'] }}</button>
                                                            @endforeach
                                                        @endif
                                                    </div>

                                                    {{-- Orden fijo para todos los roles. "Cumplimientos" y
                                                         "Ratificaciones" son filtro y contenedor a la vez: al
                                                         elegirlas pintan sus hijas juntas y despliegan la fila de
                                                         abajo para acotar a una sola. --}}
                                                    <div class="cal-tabs">
                                                        @if ($esNotificador)
                                                            <button type="button" class="cal-tab btn-calendar active" data-tipo="btn-mis-notificaciones">Mis notificaciones</button>
                                                            <button type="button" class="cal-tab btn-calendar" data-tipo="btn-audiencias">Audiencias</button>
                                                        @elseif ($esRecepcion)
                                                            @php $fuentesRecepcion = \App\Support\Recepcion::fuentes(auth()->user()); @endphp
                                                            <button type="button" class="cal-tab btn-calendar active" data-tipo="btn-citas-linea">Citas en línea</button>
                                                            @if (in_array('audiencias', $fuentesRecepcion, true))
                                                                <button type="button" class="cal-tab btn-calendar" data-tipo="btn-audiencias">Audiencias</button>
                                                            @endif
                                                            @if (in_array('pagos', $fuentesRecepcion, true))
                                                                <button type="button" class="cal-tab btn-calendar" data-tipo="btn-cumplimientos" data-hijas="sub-cumplimientos">Cumplimientos</button>
                                                            @endif
                                                            @if (in_array('ratificaciones', $fuentesRecepcion, true))
                                                                <button type="button" class="cal-tab btn-calendar" data-tipo="btn-ratificaciones" data-hijas="sub-ratificaciones">Ratificaciones</button>
                                                            @endif
                                                        @else
                                                            <button type="button" class="cal-tab btn-calendar active" data-tipo="btn-todos">Todos</button>
                                                            <button type="button" class="cal-tab btn-calendar" data-tipo="btn-solicitudes">Solicitudes</button>
                                                            <button type="button" class="cal-tab btn-calendar" data-tipo="btn-audiencias">Audiencias</button>
                                                            <button type="button" class="cal-tab btn-calendar" data-tipo="btn-cumplimientos" data-hijas="sub-cumplimientos">Cumplimientos</button>
                                                            <button type="button" class="cal-tab btn-calendar" data-tipo="btn-ratificaciones" data-hijas="sub-ratificaciones">Ratificaciones</button>
                                                            {{-- Supervisión de las citas en línea. No entra en "Todos":
                                                                 una cita aún no tiene conciliador, así que con una
                                                                 persona elegida en la barra aparecería igual y
                                                                 confundiría el filtro. --}}
                                                            @if (\App\Support\Recepcion::veCitas(auth()->user()))
                                                                <button type="button" class="cal-tab btn-calendar" data-tipo="btn-citas-linea">Citas en línea</button>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    {{-- Sub-pastillas. Se muestran solo cuando su padre esta activo. --}}
                                                    <div class="cal-subtabs" id="sub-cumplimientos" hidden>
                                                        <button type="button" class="cal-tab cal-tab-hija btn-calendar" data-tipo="btn-cumpl-audiencias">Audiencias</button>
                                                        <button type="button" class="cal-tab cal-tab-hija btn-calendar" data-tipo="btn-cumpl-generales">Generales</button>
                                                    </div>

                                                    <div class="cal-subtabs" id="sub-ratificaciones" hidden>
                                                        <button type="button" class="cal-tab cal-tab-hija btn-calendar" data-tipo="btn-rati-cumplimientos">Cumplimientos</button>
                                                    </div>

                                                    {{-- La pinta calendar.js con el semaforo del tipo activo; el
                                                         catalogo viene de App\Support\SemaforoAgenda. --}}
                                                    <div class="cal-leyenda" id="calLeyenda" style="display:none;"></div>

                                                    {{-- Vista para celulares (agenda-movil.js). En escritorio va
                                                         oculta y no se usa; en un teléfono sustituye a la
                                                         cuadrícula de FullCalendar con los mismos filtros. --}}
                                                    <div id="calMovil" class="cm" hidden>
                                                        <div class="cm-cabeza">
                                                            <div class="cm-cabeza__texto">
                                                                <h4 class="cm-mes" id="cmMes">&nbsp;</h4>
                                                                <span class="cm-sub" id="cmSub"></span>
                                                            </div>
                                                            <div class="cm-acciones">
                                                                <button type="button" class="cm-btn" id="cmPrev" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                                                                <button type="button" class="cm-btn cm-btn--texto" id="cmHoy">Hoy</button>
                                                                <button type="button" class="cm-btn" id="cmNext" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                                                                <button type="button" class="cm-btn cm-btn--vista" id="cmVista" aria-pressed="false" aria-label="Cambiar entre días y mes completo"><i class="bi bi-calendar3"></i></button>
                                                                @unless ($esRecepcion || $esNotificador)
                                                                    <button type="button" class="cm-btn" id="cmExportar" aria-label="Descargar la agenda del mes en Excel" title="Descargar el mes en Excel"><i class="bi bi-file-earmark-excel"></i></button>
                                                                @endunless
                                                            </div>
                                                        </div>

                                                        <div class="cm-tira" id="cmTira" role="listbox" aria-label="Días del mes"></div>
                                                        <div class="cm-rejilla" id="cmRejilla" hidden></div>

                                                        <div class="cm-dia">
                                                            <div class="cm-dia__cabeza">
                                                                <h5 class="cm-dia__titulo" id="cmDiaTitulo"></h5>
                                                                <span class="cm-dia__cuenta" id="cmDiaCuenta"></span>
                                                            </div>
                                                            <div class="cm-aviso" id="cmAviso" hidden></div>
                                                            <div class="cm-lista" id="cmLista" aria-live="polite"></div>
                                                            <p class="cm-pista"><i class="bi bi-arrow-left-right"></i> Desliza la lista para cambiar de día</p>
                                                        </div>
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

{{--
    El modal se empuja al final del <body>, fuera de <section class="section">.
    Esa clase trae `position: relative; z-index: 1` en style.css, lo que abre un
    contexto de apilamiento: adentro, el z-index 1055 del modal sólo compite con
    sus hermanos, mientras que el .modal-backdrop que Bootstrap cuelga del <body>
    queda en 1050 por encima de toda la sección. El modal se abría detrás del
    velo oscuro.
--}}
@push('body_end')
    <div class="modal fade" id="evento" tabindex="-1" aria-labelledby="tituloEvento" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloEvento">Detalles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@endpush
