@extends('layouts.app')
@section('title', 'Cambio de fecha en audiencia')

{{--
    Administración → Cambio de fecha en audiencia.
    Arranca con la tabla de audiencias (App\Support\ListadoAudiencias): vistas
    Próximas / Pendientes vencidas / Todas, filtros por texto, sede,
    conciliador y fechas. Se conserva el buscador por NUE (modal) y el
    historial de cambios. Reagendar abre el mismo calendario de siempre.
--}}

@php
    use Illuminate\Support\Carbon;

    $con = fn (array $cambios) => route('cambio_fecha_audiencia', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $fechaLarga = fn ($f) => \Illuminate\Support\Str::ucfirst(Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $hoy = Carbon::today()->toDateString();
    $claseEstatus = function (?string $estatus, $fecha) use ($hoy) {
        if ($estatus === 'Pendiente') {
            return Carbon::parse($fecha)->toDateString() < $hoy ? 'ca-estatus--vencida' : 'ca-estatus--pendiente';
        }
        return match ($estatus) {
            'Conciliacion', 'Reinstalacion'                      => 'ca-estatus--convenio',
            'Reagendada', 'No conciliacion reagendada'           => 'ca-estatus--reagendada',
            'No conciliacion', 'Incompetencia', 'Desistimiento'  => 'ca-estatus--cerrada',
            default                                              => 'ca-estatus--archivo',
        };
    };
    // La base guarda algunos valores sin acento ("Zitacuaro", "Conciliacion").
    $nombreSede = fn ($s) => collect(['Morelia', 'Uruapan', 'Zamora', 'Zitácuaro', 'Lázaro Cárdenas', 'Sahuayo'])
        ->first(fn ($n) => \Illuminate\Support\Str::ascii($n) === \Illuminate\Support\Str::ascii((string) $s)) ?? $s;
    $nombreEstatus = fn ($e) => [
        'Conciliacion'               => 'Conciliación',
        'No conciliacion'            => 'No conciliación',
        'No conciliacion reagendada' => 'No conciliación reagendada',
        'Reinstalacion'              => 'Reinstalación',
    ][$e] ?? ($e ?: 'Sin estatus');
    $hayFiltros = $filtros['q'] !== '' || $filtros['sede'] !== '' || $filtros['conciliador'] || $filtros['desde'] || $filtros['hasta'];
    $multiSede = count($delegaciones) > 1;
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ca-cabeza { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; }
        .ca-cabeza p { margin: 2px 0 0; font-size: 13px; color: var(--tn-tenue); }
        .ca-botones { display: flex; flex-wrap: wrap; gap: 8px; }
        .ca-botones .btn { font-weight: 600; }
        .btn.tn-boton-verde { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; font-weight: 600; }
        .btn.tn-boton-verde:not(.btn-social):not(.btn-social-icon):hover,
        .btn.tn-boton-verde:not(.btn-social):not(.btn-social-icon):focus { background: #354647; border-color: #354647 !important; color: #fff; }

        .ca-filtros { display: grid; grid-template-columns: minmax(0, 2.2fr) repeat(4, minmax(0, 1fr)) auto; gap: 12px; align-items: end; }
        .ca-filtros.sin-sede { grid-template-columns: minmax(0, 2.2fr) repeat(3, minmax(0, 1fr)) auto; }
        @media (max-width: 1200px) { .ca-filtros, .ca-filtros.sin-sede { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ca-filtros .ca-q { grid-column: 1 / -1; } }
        @media (max-width: 640px)  { .ca-filtros, .ca-filtros.sin-sede { grid-template-columns: 1fr; } }
        .ca-filtros label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .ca-acciones-filtro { display: flex; gap: 6px; }
        .ca-acciones-filtro .btn { white-space: nowrap; }

        .ca-vistas { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 12px; }
        .ca-vista {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 10px;
            border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-tinta);
            font-size: 13px; font-weight: 600; text-decoration: none;
        }
        .ca-vista:hover { border-color: var(--tn-verde-claro); color: var(--tn-tinta); }
        .ca-vista.is-activa { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .ca-vista__num { min-width: 24px; padding: 1px 7px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; text-align: center; }
        .ca-vista--vencida .ca-vista__num { background: #FBE9E7; color: #9A2B1C; }

        .ca-tabla { margin: 0; }
        .tn .table.ca-tabla > :not(caption) > * > th,
        .tn .table.ca-tabla > :not(caption) > * > td { padding: 10px 12px; height: auto; }
        .tn .table.ca-tabla thead th {
            background: #354647; --bs-table-bg: #354647; color: #fff !important;
            font-size: 12px; font-weight: 600; letter-spacing: .03em; white-space: nowrap; border: 0;
        }
        .ca-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
        .ca-tabla .ca-dia td { background: var(--tn-fondo); font-size: 12.5px; font-weight: 700; color: var(--tn-verde); padding-block: 8px; }
        .ca-hora { font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .ca-nue { font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .ca-sub { display: block; font-size: 12px; color: var(--tn-tenue); font-weight: 400; }
        .ca-persona { min-width: 170px; }
        .ca-tabla .btn-sm { white-space: nowrap; }
        .ca-mas { font-size: 11.5px; color: var(--tn-tenue); font-weight: 600; white-space: nowrap; }

        .ca-estatus { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .ca-estatus--pendiente  { background: #FBF4E1; color: #7A5D10; }
        .ca-estatus--vencida    { background: #FBE9E7; color: #9A2B1C; }
        .ca-estatus--convenio   { background: #E5F3EA; color: #1E7B3C; }
        .ca-estatus--reagendada { background: #E7EEF9; color: #2A4F8A; }
        .ca-estatus--cerrada    { background: #EEF2F2; color: var(--tn-verde); }
        .ca-estatus--archivo    { background: #F1F1F1; color: #6B6B6B; }

        .ca-localizada { border-left: 4px solid var(--tn-dorado); }
        .ca-localizada dl { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px 18px; margin: 12px 0 0; }
        .ca-localizada dt { font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); }
        .ca-localizada dd { margin: 0; font-size: 14px; color: var(--tn-tinta); font-weight: 600; overflow-wrap: anywhere; }
        .ca-vacio { text-align: center; color: var(--tn-tenue); padding: 36px 12px; font-size: 14px; }
        .ca-pie { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 14px; font-size: 13px; color: var(--tn-tenue); }
        .ca-pie nav { margin: 0; }
        .ca-pie .pagination { margin: 0; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header ca-cabeza">
            <div>
                <h3 class="page__heading mb-0">Cambio de fecha en audiencia</h3>
                <p>Todas las audiencias{{ $multiSede ? '' : ' de '.reset($delegaciones) }}. Filtra o busca un NUE y reagenda desde el calendario.</p>
            </div>
            <div class="ca-botones">
                <button type="button" class="btn tn-boton-verde" data-bs-toggle="modal" data-bs-target="#ModalRetroceso">
                    <i class="bi bi-search"></i> Buscar por NUE
                </button>
                @if (auth()->user()->hasRole('Super Usuario'))
                    <a href="{{ route('administracion_historial', 'cambio_fecha_audiencia') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                @endif
                <a href="{{ route('configuracion') }}" class="btn btn-outline-secondary">Regresar</a>
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Audiencia reagendada.</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>No se pudo completar.</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Resultado del buscador por NUE --}}
            @if (session('folio'))
                @php $folio = session('folio'); @endphp
                <div class="tn-tarjeta ca-localizada mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <p class="tn-titulo-seccion mb-1">Audiencia localizada</p>
                            <span class="ca-nue" style="font-size: 18px;">{{ $folio['NUE'] }}</span>
                            <span class="ca-estatus {{ $claseEstatus($folio['estatus'], $folio['fecha']) }} ms-2">{{ $nombreEstatus($folio['estatus']) }}</span>
                        </div>
                        @can('cambiar_fecha_audiencia')
                            <button type="button" class="btn tn-boton-verde"
                                data-bs-toggle="modal" data-bs-target="#ModalReagendar"
                                data-id="{{ $folio['id'] }}" data-sede="{{ $folio['delegacion'] }}"
                                data-id-conciliador="{{ $folio['id_conciliador'] }}" data-nue="{{ $folio['NUE'] }}">
                                <i class="bi bi-calendar2-week"></i> Reagendar
                            </button>
                        @endcan
                    </div>
                    <dl>
                        <div><dt>Fecha y hora</dt><dd>{{ $folio['fecha'] ? Carbon::parse($folio['fecha'])->format('d/m/Y') : 'N/A' }} · {{ $folio['hora'] ? Carbon::parse($folio['hora'])->format('H:i') : '' }}</dd></div>
                        <div><dt>Sede</dt><dd>{{ $folio['delegacion'] ?: 'N/A' }}</dd></div>
                        <div><dt>Solicitante</dt><dd>{{ $folio['solicitante'] ?: 'N/A' }}</dd></div>
                        <div><dt>Citado</dt><dd>{{ $folio['citados'] ?: 'N/A' }}</dd></div>
                        <div><dt>Conciliador</dt><dd>{{ $folio['conciliador'] ?: 'N/A' }}</dd></div>
                    </dl>
                </div>
            @endif

            <div class="tn-tarjeta">
                <form method="GET" action="{{ route('cambio_fecha_audiencia') }}" class="ca-filtros {{ $multiSede ? '' : 'sin-sede' }}" role="search">
                    <input type="hidden" name="vista" value="{{ $filtros['vista'] }}">
                    <div class="ca-q">
                        <label for="caQ">Buscar</label>
                        <input type="search" id="caQ" name="q" class="form-control" value="{{ $filtros['q'] }}"
                               placeholder="NUE, folio, solicitante, citado o conciliador" autocomplete="off">
                    </div>
                    @if ($multiSede)
                        <div>
                            <label for="caSede">Sede</label>
                            <select id="caSede" name="sede" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($delegaciones as $nombre)
                                    <option value="{{ $nombre }}" @selected($filtros['sede'] === $nombre)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="caConciliador">Conciliador</label>
                        <select id="caConciliador" name="conciliador" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($conciliadores as $c)
                                <option value="{{ $c->id }}" @selected((int) $filtros['conciliador'] === (int) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="caDesde">Desde</label>
                        <input type="date" id="caDesde" name="desde" class="form-control" value="{{ $filtros['desde'] }}">
                    </div>
                    <div>
                        <label for="caHasta">Hasta</label>
                        <input type="date" id="caHasta" name="hasta" class="form-control" value="{{ $filtros['hasta'] }}">
                    </div>
                    <div class="ca-acciones-filtro">
                        <button type="submit" class="btn tn-boton-verde"><i class="bi bi-funnel"></i> Filtrar</button>
                        @if ($hayFiltros)
                            <a href="{{ route('cambio_fecha_audiencia', ['vista' => $filtros['vista']]) }}" class="btn btn-outline-secondary" title="Quitar filtros">Limpiar</a>
                        @endif
                    </div>
                </form>

                <div class="ca-vistas">
                    @foreach (\App\Support\ListadoAudiencias::VISTAS as $clave => $etiqueta)
                        <a href="{{ $con(['vista' => $clave]) }}"
                           class="ca-vista {{ $filtros['vista'] === $clave ? 'is-activa' : '' }} {{ $clave === 'vencidas' && $conteos[$clave] ? 'ca-vista--vencida' : '' }}">
                            {{ $etiqueta }} <span class="ca-vista__num">{{ number_format($conteos[$clave]) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table ca-tabla">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>NUE</th>
                                <th>Solicitante</th>
                                <th>Citado</th>
                                <th>Sede · Sala</th>
                                <th>Conciliador</th>
                                <th>Estatus</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $diaAnterior = null; @endphp
                            @forelse ($audiencias as $a)
                                @php $dia = Carbon::parse($a->fecha)->toDateString(); @endphp
                                @if ($dia !== $diaAnterior)
                                    <tr class="ca-dia"><td colspan="8">{{ $fechaLarga($dia) }}{{ $dia === $hoy ? ' · Hoy' : '' }}</td></tr>
                                    @php $diaAnterior = $dia; @endphp
                                @endif
                                <tr>
                                    <td class="ca-hora">{{ substr((string) $a->hora, 0, 5) }}</td>
                                    <td>
                                        <span class="ca-nue">{{ $a->NUE }}</span>
                                        <span class="ca-sub">Audiencia {{ $a->numero_audiencia }}</span>
                                    </td>
                                    <td class="ca-persona">{{ $a->solicitante ?: '—' }}</td>
                                    <td class="ca-persona">
                                        {{ $a->citado ?: '—' }}
                                        @if ($a->citados_total > 1)
                                            <span class="ca-mas">+{{ $a->citados_total - 1 }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $nombreSede($a->delegacion) }}@if ($a->sala)<span class="ca-sub">{{ $a->sala }}</span>@endif</td>
                                    <td>{{ $a->conciliador ?: '—' }}</td>
                                    <td><span class="ca-estatus {{ $claseEstatus($a->estatus, $a->fecha) }}">{{ $nombreEstatus($a->estatus) }}</span></td>
                                    <td class="text-end">
                                        @if ($a->estatus === 'Pendiente')
                                            @can('cambiar_fecha_audiencia')
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal" data-bs-target="#ModalReagendar"
                                                    data-id="{{ $a->id }}" data-sede="{{ $a->sede_solicitud ?: $a->delegacion }}"
                                                    data-id-conciliador="{{ $a->id_conciliador }}" data-nue="{{ $a->NUE }}">
                                                    <i class="bi bi-calendar2-week"></i> Reagendar
                                                </button>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="ca-vacio">
                                        No hay audiencias {{ $filtros['vista'] === 'todas' ? '' : 'en «'.\App\Support\ListadoAudiencias::VISTAS[$filtros['vista']].'»' }} con estos filtros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="ca-pie">
                    <span>
                        @if ($audiencias->total())
                            Mostrando {{ number_format($audiencias->firstItem()) }}–{{ number_format($audiencias->lastItem()) }} de {{ number_format($audiencias->total()) }}
                        @endif
                    </span>
                    {{ $audiencias->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('modals')
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
                    <button type="submit" class="btn tn-boton-verde">Buscar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="ModalReagendar" tabindex="-1" data-bs-focus="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{route('cambiar_fecha')}}">
    @csrf
    <!-- Input para que tu Controlador sepa qué actualizar -->
    <input type="hidden" name="id_audiencia" id="modal-id-reagendar" value="">
    <input type="hidden" name="motivo" id="motivoReagendar" value="">
    
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <!-- Restaura el header que se había borrado -->
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda <small class="text-muted" id="tituloNueReagendar"></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Estos inputs alimentan al JavaScript del Calendario -->
                <input type="hidden" id="NUE" value="">
                <input type="hidden" id="sedeReagendar" value="">
                <input type="hidden" id="idConciliador" value="">
                <input type="hidden" id="fechaConfirmacion" value="">
                <input type="hidden" id="modal-id" value="">
                @if ($puedeEmpalmar)
                    <div class="alert alert-info py-2 small mb-2">
                        <i class="bi bi-info-circle"></i> Como Super Usuario puedes elegir un horario ocupado: la audiencia quedará empalmada con las del conciliador.
                    </div>
                @endif
                
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
@endpush

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
        .fc-event-ocupado, .fc-est-ocupado{ color:#fff !important; background-color:#287532 !important; border-color:#287532 !important; }
        .fc-event-actual, .fc-est-actual{ color:#fff !important; background-color:#8163a8 !important; border-color:#8163a8 !important; }
        .fc-event-selected { border: 2px solid #FFD700 !important; box-shadow: 0 0 8px #FFD700; }
        .fc .fc-event-main, .fc .fc-event-time { color:#fff !important; }
        .fc-list .fc-list-event.fc-event-disponible td, .fc-list .fc-list-event.fc-est-disponible td{ background-color:#00CE1C !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-expirado td, .fc-list .fc-list-event.fc-est-expirado td{ background-color:#969696 !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-inhabil td, .fc-list .fc-list-event.fc-est-inhabil td{ background-color:#3B78DB !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-ocupado td, .fc-list .fc-list-event.fc-est-ocupado td{ background-color:#287532 !important; color:#fff !important; }
        .fc-list .fc-list-event.fc-event-ocupado td, .fc-list .fc-list-event.fc-est-actual td{ background-color:#8163a8 !important; color:#fff !important; }
        @media (min-width: 1200px){ .modal-xl{ --bs-modal-width: 95vw; } }
        .modal .modal-body{ max-height: calc(100vh - 200px); overflow-y: auto; }
    </style>

    <!--Inicialización General y Búsqueda -->
    <script>
        var PUEDE_EMPALMAR = @json($puedeEmpalmar);
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
            // Botones Reagendar (tabla y audiencia localizada): llenan el modal del calendario.
            $(document).on('click', '[data-bs-target="#ModalReagendar"]', function () {
                const id = $(this).data('id');
                $('#modal-id').val(id);
                $('#modal-id-reagendar').val(id);
                $('#sedeReagendar').val($(this).data('sede'));
                $('#idConciliador').val($(this).data('id-conciliador'));
                $('#NUE').val($(this).data('nue'));
                $('#tituloNueReagendar').text($(this).data('nue') ? '· ' + $(this).data('nue') : '');
                $('#fechaSeleccionada, #horaSeleccionada').val('');
                $('#btnGuardarReagenda').prop('disabled', true);
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
                                        icon: 'warning', title: 'Horario ocupado',
                                        html: PUEDE_EMPALMAR
                                            ? 'El conciliador ya tiene audiencia en este horario.<br><br>Si continúas, la <b>audiencia se empalmará</b>.'
                                            : 'El conciliador ya tiene audiencia en este horario.<br><br>Sólo un Super Usuario puede empalmar; al guardar el sistema lo rechazará.'
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
                            input: 'textarea', inputLabel: 'Motivo del cambio (obligatorio)',
                            inputPlaceholder: 'Ej. Solicitud de las partes', inputAttributes: { maxlength: 1000 },
                            inputValidator: (valor) => { if (!valor || !valor.trim()) return 'El motivo es obligatorio.'; },
                            showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', reverseButtons: true
                        }).then((result)=>{
                            if(result.isConfirmed){
                                document.getElementById('motivoReagendar').value = result.value.trim();
                                formReagendar.submit();
                            }
                        });
                    }
                    if(window.Swal) lanzar(); else setTimeout(lanzar, 200);
                });
            }
    });
    </script>
    <script src="{{ asset('assets/js/validaciones.js') }}"></script>
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
@endsection