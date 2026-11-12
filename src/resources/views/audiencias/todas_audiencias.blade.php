@extends('layouts.app')
@section('title', 'Índice de Audiencias')

@php
    // Tonos de las solicitudes
    $tonosEstatus = [
        'Pendiente'                  => 'tn-estatus--pendiente',
        'Confirmado'                 => 'so-t-confirmadas',
        'Conciliacion'               => 'so-t-conciliadas',
        'Concluida'                  => 'so-t-conciliadas',
        'Concluida Pagos'            => 'so-t-conciliadas',
        'Reinstalacion'              => 'so-t-conciliadas',
        'No conciliacion'            => 'so-t-no_conciliacion',
        'No conciliacion reagendada' => 'au-t-reagendada',
        'Reagendada'                 => 'au-t-reagendada',
        'Incumplimiento'             => 'so-t-incumplimiento',
        'Archivada'                  => 'so-t-archivadas',
        'Archivada en Audiencia'     => 'so-t-archivadas',
        'Desistimiento'              => 'so-t-archivadas',
    ];
    $tonoEstatus = fn (?string $e) => $tonosEstatus[$e] ?? 'so-t-otras';

    $urlCon = fn (array $cambios) => url()->current().'?'.http_build_query(array_filter(
        array_merge(request()->only(['buscar', 'orden', 'fecha']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $hoy = now()->toDateString();
    $ayer = now()->subDay()->toDateString();
    $manana = now()->addDay()->toDateString();
    $base = $fecha ? \Illuminate\Support\Carbon::parse($fecha) : now();
    $fechaLarga = fn ($f) => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $ordenesVista = [
        'recientes'      => 'Registradas más recientes',
        'antiguas'       => 'Registradas más antiguas',
        'fecha_reciente' => 'Fecha de audiencia más reciente',
        'fecha_antigua'  => 'Fecha de audiencia más antigua',
        'nue'            => 'Expediente',
    ];

    $estatusConDocumentos = [
        'Archivada en Audiencia', 'Archivada', 'Incompetencia', 'Comparecencia', 'Reagendada',
        'No conciliacion reagendada', 'No conciliacion', 'Incumplimiento', 'Conciliacion',
        'Concluida', 'Reinstalacion', 'Confirmado', 'Pendiente', 'Desistimiento',
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        /* Un tono por estatus (los so-t-* son los de Solicitudes). */
        .so-t-confirmadas     { background: #E3EFF3; color: #1D5F73; }
        .so-t-conciliadas     { background: #E5F3EA; color: #1E7B3C; }
        .so-t-no_conciliacion { background: #E7ECEC; color: #496163; }
        .so-t-incumplimiento  { background: #F6E4EA; color: #8C1D40; }
        .so-t-archivadas      { background: #EEF0F0; color: #5B6667; }
        .so-t-otras           { background: #EEEAF4; color: #5B4A7A; }
        .au-t-reagendada      { background: #FBF4E1; color: #8A6A12; }

        /* Filtros con la misma disposición que Solicitudes. */
        .rt-filtros { display: grid; grid-template-columns: auto minmax(0, 2fr) minmax(0, 1fr) auto; gap: 12px; align-items: end; }
        @media (max-width: 1200px) { .rt-filtros { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px)  { .rt-filtros { grid-template-columns: 1fr; } }
        .rt-filtros label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .rt-dia { display: flex; align-items: stretch; gap: 6px; }
        .rt-dia .form-control { min-width: 150px; }
        .rt-dia .btn { padding-inline: 10px; }
        .rt-buscar { position: relative; }
        .rt-buscar i { position: absolute; left: 95%; top: 50%; transform: translateY(-50%); color: var(--tn-tenue); }
        .rt-buscar input { padding-left: 36px; }

        .rt-atajos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .rt-atajo {
            padding: 4px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff;
            color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none;
        }
        .rt-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .rt-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .rt-encabezado-dia { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
        .rt-encabezado-dia h4 { margin: 0; font-size: 18px; font-weight: 700; color: var(--tn-tinta); }
        .rt-encabezado-dia span { font-size: 13px; color: var(--tn-tenue); }

        /* Encabezado oscuro de la tabla. Con .tn delante para ganarle a
           .table:not(.table-sm) thead th de style.css. */
        .tn-tabla { margin: 0; }
        .tn .table.tn-tabla > :not(caption) > * > th,
        .tn .table.tn-tabla > :not(caption) > * > td { padding: 10px 12px; height: auto; }
        .tn .table.tn-tabla thead th {
            background: #354647; --bs-table-bg: #354647; color: #fff !important;
            font-size: 12px; font-weight: 600; letter-spacing: .03em; white-space: nowrap; border: 0;
        }
        .tn-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }

        .rt-nue { font-weight: 700; color: var(--tn-verde); white-space: nowrap; }
        .rt-principal { display: block; font-weight: 700; color: var(--tn-tinta); line-height: 1.25; }
        .rt-fecha { display: block; font-weight: 600; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .rt-hora { display: block; font-size: 12px; color: var(--tn-tenue); white-space: nowrap; }
        .rt-sin { color: var(--tn-tenue); }

        .rt-acciones { display: flex; flex-direction: column; gap: 6px; align-items: stretch; min-width: 150px; }
        .tn-tabla .btn-sm { white-space: nowrap; font-weight: 600; }

        /*
         * Botones de acción con color propio pero dentro de la paleta:
         *   Revisar      → azul petróleo (el tono de "Confirmado"), suave.
         *   Iniciar      → verde (el tono de "Conciliación"), sólido: es la acción principal.
         *   Cumplimiento → verde institucional, suave.
         * Cada variante sólo define sus --au-*; .au-boton los pasa a las
         * variables de Bootstrap y le gana a style.css, que pinta de blanco
         * todo .btn en hover/focus/active (mismo truco que .tn-boton-dorado).
         */
        .au-boton {
            --bs-btn-color: var(--au-c);
            --bs-btn-bg: var(--au-bg);
            --bs-btn-border-color: var(--au-borde);
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: var(--au-hover);
            --bs-btn-hover-border-color: var(--au-hover);
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: var(--au-activo);
            --bs-btn-active-border-color: var(--au-activo);
            font-weight: 600;
        }
        .btn.au-boton:not(.btn-social):not(.btn-social-icon):focus {
            background-color: var(--au-bg);
            border-color: var(--au-borde) !important;
            color: var(--au-c);
        }
        .btn.au-boton:not(.btn-social):not(.btn-social-icon):hover {
            background-color: var(--au-hover);
            border-color: var(--au-hover) !important;
            color: #fff;
        }
        .btn.au-boton:not(.btn-social):not(.btn-social-icon):active {
            background-color: var(--au-activo);
            border-color: var(--au-activo) !important;
            color: #fff;
        }
        .btn.au-boton:focus-visible { outline: 2px solid var(--au-hover); outline-offset: 2px; }

        .au-boton--revisar {
            --au-c: #1D5F73; --au-bg: #E3EFF3; --au-borde: #C3DCE5;
            --au-hover: #1D5F73; --au-activo: #164B5B;
        }
        .au-boton--iniciar {
            --au-c: #fff; --au-bg: #1E7B3C; --au-borde: #1E7B3C;
            --au-hover: #186632; --au-activo: #13522A;
        }
        .au-boton--cumplimiento {
            --au-c: #496163; --au-bg: #E7ECEC; --au-borde: #CED9D9;
            --au-hover: #496163; --au-activo: #3A4E50;
        }

        .rt-pie { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 14px; font-size: 13px; color: var(--tn-tenue); }
        .rt-vacio { padding: 48px 16px; text-align: center; color: var(--tn-suave); }
        .rt-vacio i { font-size: 34px; display: block; margin-bottom: 10px; color: var(--tn-verde-claro); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Índice de Audiencias</h3>
                <span class="small" style="color: var(--tn-tenue);">Audiencias de conciliación programadas en SICONCILIO</span>
            </div>
        </div>

        <div class="section-body">
            <!-- Muestra los mensajes de éxito y/o error según sea el caso, al subir el expediente -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @can('audiencias_consultar')
                <div class="tn-tarjeta">
                    {{-- Filtros en el servidor. GET: la URL guarda la vista y se puede compartir. --}}
                    <form action="{{ url()->current() }}" method="GET" id="auFiltros">
                        <div class="rt-filtros">
                            <div>
                                <label for="auFecha">Fecha de audiencia</label>
                                <div class="rt-dia">
                                    <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->subDay()->toDateString()]) }}" title="Día anterior" aria-label="Día anterior">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                    <input type="date" id="auFecha" name="fecha" class="form-control" value="{{ $fecha }}">
                                    <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->addDay()->toDateString()]) }}" title="Día siguiente" aria-label="Día siguiente">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            </div>

                            <div>
                                <label for="auBuscar">Buscar</label>
                                <div class="rt-buscar">
                                    <i class="bi bi-search" aria-hidden="true"></i>
                                    <input type="search" id="auBuscar" name="buscar" class="form-control" placeholder="NUE, solicitante o citado" value="{{ request('buscar') }}">
                                </div>
                            </div>

                            <div>
                                <label for="auOrden">Ordenar</label>
                                <select id="auOrden" name="orden" class="form-select">
                                    @foreach ($ordenesVista as $clave => $texto)
                                        <option value="{{ $clave }}" @selected($orden === $clave)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                                @if (request('buscar') || $fecha || $orden !== 'recientes')
                                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Quitar filtros">Limpiar</a>
                                @endif
                            </div>
                        </div>

                        <div class="rt-atajos">
                            <a class="rt-atajo {{ $fecha === null ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => null]) }}">Todos los días</a>
                            <a class="rt-atajo {{ $fecha === $ayer ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $ayer]) }}">Ayer</a>
                            <a class="rt-atajo {{ $fecha === $hoy ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $hoy]) }}">Hoy</a>
                            <a class="rt-atajo {{ $fecha === $manana ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $manana]) }}">Mañana</a>
                        </div>
                    </form>
                </div>

                <div class="tn-tarjeta mt-3">
                    <div class="rt-encabezado-dia" aria-live="polite">
                        <h4>{{ $fecha ? $fechaLarga($fecha) : 'Todos los días' }}</h4>
                        <span>
                            {{ number_format($audiencias->total()) }} {{ $audiencias->total() === 1 ? 'audiencia' : 'audiencias' }}@if(request('buscar')) que coinciden con "{{ request('buscar') }}"@endif
                        </span>
                    </div>

                    @if ($audiencias->isEmpty())
                        <div class="rt-vacio">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            No hay audiencias con estos filtros.
                        </div>
                    @else
                        {{-- Los desplegables de Documentos se posicionan con Popper en
                             estrategia 'fixed' (más abajo), así que el scroll horizontal
                             no los recorta. --}}
                        <div class="table-responsive mt-2">
                            <table id="example" class="table table-hover tn-tabla w-100">
                                <thead>
                                    <tr>
                                        <th>Expediente</th>
                                        <th>Fecha y hora</th>
                                        <th>Solicitante</th>
                                        <th>Conciliador</th>
                                        <th class="text-center">Estatus</th>
                                        @can('audiencias_revisar')
                                            <th class="text-center">Detalles</th>
                                        @endcan
                                        @can('audiencias_iniciar')
                                            <th class="text-center">Acciones</th>
                                        @endcan
                                        <th class="text-center">Cumplimientos</th>
                                        <th class="text-center">Documentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($audiencias as $audiencia)
                                        @php $estatus = $audiencia->estatus_modelo; @endphp
                                        <tr>
                                            <td><span class="rt-nue">{{ $audiencia->NUE }}</span></td>
                                            <td data-order="{{ $audiencia->fecha }} {{ $audiencia->hora }}">
                                                <span class="rt-fecha">{{ \Carbon\Carbon::parse($audiencia->fecha)->format('d-m-Y') }}</span>
                                                <span class="rt-hora">{{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} hrs.</span>
                                            </td>
                                            <td><span class="rt-principal">{{ $audiencia->nombre }}</span></td>
                                            <td>{{ $audiencia->conciliador_nombre }}</td>
                                            <td class="text-center">
                                                <span class="tn-estatus {{ $tonoEstatus($estatus) }}">{{ $estatus }}</span>
                                            </td>
                                            @can('audiencias_revisar')
                                                <td class="text-center">
                                                    <a class="btn btn-sm au-boton au-boton--revisar" href="{{ route('solicitud_audiencia', $audiencia->id_solicitud) }}?isAudiencia=Si&audiencia_id={{ $audiencia->id }}" onclick="editar_usuario();">
                                                        <i class="bi bi-eye me-1"></i> Revisar
                                                    </a>
                                                </td>
                                            @endcan
                                            @can('audiencias_iniciar')
                                                <td class="text-center">
                                                    @if($estatus == "Pendiente")
                                                        <a class="btn btn-sm au-boton au-boton--iniciar" href="{{ route('inicioAudiencia', $audiencia->id_solicitud) }}?audiencia_id={{ $audiencia->id }}">
                                                            <i class="bi bi-play-fill me-1"></i> Iniciar
                                                        </a>
                                                    @else
                                                        <span class="rt-sin" aria-hidden="true">—</span>
                                                    @endif
                                                </td>
                                            @endcan
                                            <td class="text-center">
                                                @if(in_array($estatus, ['Conciliacion', 'Reinstalacion', 'Concluida Pagos']))
                                                    <a class="btn btn-sm au-boton au-boton--cumplimiento" href="{{ route('audiencia_cumplimientos', $audiencia->id_solicitud) }}">
                                                        <i class="bi bi-receipt me-1"></i> Cumplimiento
                                                    </a>
                                                @else
                                                    <span class="rt-sin" aria-hidden="true">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="rt-acciones">
                                                    @can('audiencias_subir_documentos')
                                                        <button type="button" class="btn tn-boton-dorado btn-sm open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $audiencia->id_solicitud }}">
                                                            <i class="bi bi-upload me-1"></i> Subir Documento
                                                        </button>
                                                    @endcan

                                                    @can('audiencia_ver_documento_digital')
                                                        @if(in_array($estatus, $estatusConDocumentos))
                                                            <div class="dropdown">
                                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bi bi-folder2-open me-1"></i> Documentos
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                    <li>
                                                                        <button type="button" class="dropdown-item btn-cargar-lista-docs" data-id="{{ $audiencia->id_solicitud }}" data-doc-url="{{ signedDocRoute('VerDocumentosAudiencia', ['id' => $audiencia->id_solicitud]) }}">
                                                                            Documentos Digitales
                                                                        </button>
                                                                    </li>

                                                                    @if($estatus == "Archivada en Audiencia")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}" target="_blank">Acta de Archivo</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}" target="_blank">Acta de Audiencia</a></li>
                                                                    @elseif($estatus == "Archivada")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}" target="_blank">Acta de Archivo</a></li>
                                                                    @elseif($estatus == "Incompetencia")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFincompetencia', $audiencia->id_solicitud) }}" target="_blank">Incompetencia</a></li>
                                                                    @elseif($estatus == "Comparecencia")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFinteres', $audiencia->id_solicitud) }}" target="_blank">Acta de incomparecencia</a></li>
                                                                    @elseif($estatus == "Reagendada" || $estatus == "No conciliacion reagendada")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFnotificacion_solicitante', $audiencia->id_solicitud) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    @elseif($estatus == "No conciliacion")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFno_conciliacion', $audiencia->id_solicitud) }}" target="_blank">Constancias de no conciliación (En un solo archivo)</a></li>
                                                                        <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#noConciliacion" data-id="{{ $audiencia->id_solicitud }}">Constancias de no conciliación</button></li>
                                                                    @elseif($estatus == "Incumplimiento")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFincumplimientoAudiencia', $audiencia->id_solicitud) }}" target="_blank">Constancia de Incumplimiento</a></li>
                                                                    @elseif(in_array($estatus, ['Conciliacion', 'Concluida', 'Reinstalacion']))
                                                                        <li><a class="dropdown-item" href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}" target="_blank">Acta de Audiencia</a></li>
                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                               href="{{ ($audiencia->tienePTU ? route('PDFconvenioPTU_SI_S', $audiencia->id_solicitud) : ($estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $audiencia->id_solicitud) : route('PDFconveniosolicitud', $audiencia->id_solicitud))) . '?audiencia_id=' . $audiencia->id }}"
                                                                               target="_blank">Convenio</a>
                                                                        </li>
                                                                        @if($audiencia->constancia == 0)
                                                                            <li><a class="dropdown-item" href="{{ route('PDFcumplimientoTotal', $audiencia->id_solicitud) }}" target="_blank">Constancia de cumplimiento</a></li>
                                                                        @endif
                                                                    @elseif($estatus == "Desistimiento")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}" target="_blank">Acta de Desistimiento</a></li>
                                                                    @endif

                                                                    @can('audiencias_ver_citatorios')
                                                                        <li><button type="button" class="dropdown-item btn-mostrar-registros" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}" data-audiencia-id="{{ $audiencia->id }}">Citatorios</button></li>
                                                                    @endcan
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="rt-pie">
                            <span>Mostrando {{ number_format($audiencias->firstItem()) }}–{{ number_format($audiencias->lastItem()) }} de {{ number_format($audiencias->total()) }}</span>
                            @include('turnos._paginacion', ['pagina' => $audiencias])
                        </div>
                    @endif
                </div>
            @endcan
        </div>
    </section>

    <!-- Modal Documentos -->
    <div class="modal fade" id="documentos" tabindex="-1" aria-labelledby="modalLabelCitatorios" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelCitatorios">Citatorios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" style="width: 100%; text-align: center;">
                        <thead style="background-color: #D2D3D5;">
                            <tr>
                                <th>Citatorios</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listaRegistros"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Expediente -->
    <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabelExpediente" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('subir_expediente') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="audiencia_id" id="expediente_audiencia_id">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabelExpediente">Subir expediente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Documento en PDF</label>
                                <input type="file" name="documentoExpediente" class="form-control" accept=".pdf" required>
                                <div class="invalid-feedback">El documento es obligatorio.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Nombre de expediente <span class="text-danger">(*)</span></label>
                                <input type="text" name="nombreExpediente" class="form-control" required>
                                <div class="invalid-feedback">El nombre para el expediente es obligatorio.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn tn-boton-dorado">Agregar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Constancias de no conciliación -->
    <div class="modal fade" id="noConciliacion" tabindex="-1" aria-labelledby="modalLabelNoConciliacion" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelNoConciliacion">Constancias de No Conciliación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" style="width: 100%; text-align: center;">
                        <thead style="background-color: #D2D3D5;">
                            <tr>
                                <th>Citado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listaNoConciliacion"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Listado de Documentos Digitales -->
    <div class="modal fade" id="modalListaDocs" tabindex="-1" aria-labelledby="modalListaDocsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #354647;">
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalListaDocsLabel">
                        <i class="bi bi-folder2-open me-2"></i>Documentos Digitales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive rounded border">
                        <table class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead style="background-color: #354647;">
                                <tr>
                                    <th class="text-white py-3 ps-3" style="width: 45%; color: #ffffff !important;">Nombre del Documento</th>
                                    <th class="text-white py-3" style="width: 35%; color: #ffffff !important;">Archivo</th>
                                    <th class="text-center text-white py-3 pe-3" style="width: 20%; color: #ffffff !important;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyListaDocs">
                                <!-- Inyección vía JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="nuevo_poder" style="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script>
        const pdfsUrlBase  = "{{ url('solicitud/pdfs') }}";
    </script>
    <script>
        // Elegir un día o un orden ya filtra; no hace falta el botón.
        ['auFecha', 'auOrden'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function () { el.form.submit(); });
        });
    </script>
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example').DataTable({
                // Sin colapso de columnas: se prefiere desplazamiento horizontal, que
                // lo da el .table-responsive de Bootstrap. No se usa scrollX porque
                // clona el <thead> y necesita la hoja de estilos de DataTables, que
                // este proyecto no carga.
                // Búsqueda y páginas van en el servidor (filtro de arriba y
                // paginación de abajo); DataTables sólo ordena las columnas.
                "responsive": false,
                "destroy": true,
                "paging": false,
                "searching": false,
                "ordering": true,
                "order": [],
                "info": false,
                "language": {
                    "zeroRecords": "No se encontraron coincidencias."
                }
            });

            // Los desplegables de la tabla quedan dentro del contenedor que hace
            // scroll y este los recortaria. Con la estrategia 'fixed' de Popper el
            // menu se posiciona contra el viewport y se escapa del recorte.
            document.querySelectorAll('#example [data-bs-toggle="dropdown"]').forEach(function (boton) {
                bootstrap.Dropdown.getOrCreateInstance(boton, {
                    popperConfig: function (config) {
                        return Object.assign({}, config, { strategy: 'fixed' });
                    }
                });
            });

            //$('#btnMostrarRegistros').on('click', function() {
            $(document).on('click', '.btn-mostrar-registros', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const id = $(this).data('id');
                const audienciaId = $(this).data('audienciaId');
                const pdfRouteBase = '{{ route("pdfCitatorioAudiencia", ["id" => "xxx"]) }}';

                listaRegistros.empty(); // Limpiar lista
                $.ajax({
                    url: `${pdfsUrlBase}/${id}`,
                    data: audienciaId ? { audiencia_id: audienciaId } : {},
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            // Iteramos sobre los datos recibidos (ya parseados por jQuery)
                            $.each(data, function(index, registro) {
                            const pdfUrl = pdfRouteBase.replace('xxx', registro.id);
                                const listItem = `
                                <tr>
                                    <td style="text-align: left;"> <strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido}</strong> </td>
                                    <td>
                                        <a href="${pdfUrl}" target="_blank" rel="noopener noreferrer">PDF</a>
                                    </td>
                                </tr>`;
                                listaRegistros.append(listItem);
                            });
                        } else {
                            listaRegistros.append('<li class="list-group-item">No se encontraron registros.</li>');
                        }

                        // Mostrar el modal
                        var myModal = new bootstrap.Modal(document.getElementById('modalListado'));
                        myModal.show();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener los datos:", error);
                        listaRegistros.append('<li class="list-group-item text-danger">Error de conexión con el servidor.</li>');

                        var myModal = new bootstrap.Modal(document.getElementById('modalListado'));
                        myModal.show();
                    }
                });
             });

            $(document).on('click', '.open-expediente-modal', function() {
                // 2. Capturar el 'data-id'
                var idRegistro = $(this).data('id');
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });
            // Limpiar backdrop y modal-open cuando modal se oculta
            $('#documentos').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });

            // Modal Documentos Digitales vía AJAX
            $(document).on('click', '.btn-cargar-lista-docs', function() {
                const solicitudId = $(this).data('id');
                const tbody = $('#tbodyListaDocs');
                const modalElement = document.getElementById('modalListaDocs');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                const finalUrl = $(this).data('docUrl');

                tbody.html(`
                    <tr>
                        <td colspan="3" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted mb-0">Obteniendo listado de documentos...</p>
                        </td>
                    </tr>
                `);

                modalInstance.show();

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        tbody.empty();
                        if (Array.isArray(data) && data.length > 0) {
                            $.each(data, function(index, item) {
                                const botonAccion = item.url
                                    ? `<a href="${item.url}" target="_blank" class="btn btn-primary btn-sm px-3 shadow-sm"><i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF</a>`
                                    : `<span class="badge bg-secondary rounded-pill px-3 py-2 fw-normal">No disponible</span>`;

                                const row = `
                                    <tr>
                                        <td class="fw-semibold ps-3 py-3">${item.nombre}</td>
                                        <td class="text-muted small py-3" style="max-width: 200px;">
                                            <div class="text-truncate" title="${item.archivo || ''}">
                                                ${item.archivo || 'N/A'}
                                            </div>
                                        </td>
                                        <td class="text-center pe-3 py-3">${botonAccion}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No hay documentos registrados para esta solicitud.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener documentos:', error);
                        tbody.html(`
                            <tr>
                                <td colspan="3" class="text-center text-danger py-4">Ocurrió un error al intentar consultar los documentos (${xhr.status}).</td>
                            </tr>
                        `);
                    }
                });
            });
        });
    </script>
    <script>//Constancias de no conciliación individuales en modal
        $(document).on('click', '[data-bs-target="#noConciliacion"]', function() {
            const idSolicitud = $(this).data('id');
            const tabla = $('#listaNoConciliacion');
            const urlData = "{{ url('ObtenerConstancias') }}/" + idSolicitud;

            // Ruta base para el PDF individual
            const routePdfBase = "{{ route('PDFnoConciliacionIndividual', ['id' => 'XXX']) }}";

            tabla.empty().append('<tr><td colspan="2">Cargando...</td></tr>');

            $.ajax({
                url: urlData,
                type: 'GET',
                success: function(data) {
                    tabla.empty();
                    if (data.length > 0) {
                        $.each(data, function(index, registro) {
                            // Reemplazamos el placeholder XXX por el ID del citado
                            const finalPdfUrl = routePdfBase.replace('XXX', registro.id);

                            const row = `
                                <tr>
                                    <td style="text-align: left;">
                                        <strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido || ''}</strong>
                                    </td>
                                    <td>
                                        <a href="${finalPdfUrl}" class="btn btn-danger btn-sm" target="_blank">
                                           Ver PDF
                                        </a>
                                    </td>
                                </tr>`;
                            tabla.append(row);
                        });
                    } else {
                        tabla.append('<tr><td colspan="2">No hay registros disponibles.</td></tr>');
                    }
                },
                error: function() {
                    tabla.empty().append('<tr><td colspan="2" class="text-danger">Error al cargar datos.</td></tr>');
                }
            });
        });
    </script>
@endsection
