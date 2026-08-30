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
