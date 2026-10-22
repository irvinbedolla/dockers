@extends('layouts.app')
@section('title', 'Ratificaciones')

@php
    $fechaActual = date('Y-m-d');
    $puedeEditarRat = auth()->user()->can('ratificaciones_editar')
        && in_array($userRole, ['Enlace', 'Super Usuario', 'Auxiliar'], true);
    // Mismos tonos de estatus que Turnos y Usuarios (turnos._estilos).
    $claseEstatus = fn (?string $e) => match ($e) {
        'Confirmado'                   => 'tn-estatus--confirmada',
        'Concluida', 'Concluida Pagos' => 'tn-estatus--atendido',
        'Incumplimiento'               => 'tn-estatus--incumplimiento',
        'Archivada'                    => 'tn-estatus--expirada',
        default                        => 'tn-estatus--pendiente',
    };
    // El día, los apartados y el orden se filtran en el servidor (SeerController::todas_ratificaciones)
    // porque la lista va paginada; cada liga conserva búsqueda, día, apartado y orden.
    $urlCon = fn (array $cambios) => url()->current().'?'.http_build_query(array_filter(
        array_merge(request()->only(['buscar', 'estatus', 'orden', 'fecha']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $apartadosVista = [
        'concluidas'     => ['Concluidas', 'bi-check2-all', 'rt-i-concluida'],
        'confirmadas'    => ['Confirmadas', 'bi-calendar-check', 'rt-i-confirmada'],
        'incumplimiento' => ['Incumplimiento', 'bi-exclamation-octagon', 'rt-i-incumplimiento'],
        'archivadas'     => ['Archivadas', 'bi-archive', 'rt-i-archivada'],
    ];
    $hoy = now()->toDateString();
    $ayer = now()->subDay()->toDateString();
    $manana = now()->addDay()->toDateString();
    $base = $fecha ? \Illuminate\Support\Carbon::parse($fecha) : now();
    $fechaLarga = fn ($f) => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $ordenesVista = [
        'recientes'      => 'Registradas más recientes',
        'antiguas'       => 'Registradas más antiguas',
        'fecha_reciente' => 'Fecha de cita más reciente',
        'fecha_antigua'  => 'Fecha de cita más antigua',
        'nue'            => 'NUE',
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .rt-cifras { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
        .rt-cifra {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px; text-decoration: none;
            background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px;
            transition: border-color .15s, box-shadow .15s;
        }
        .rt-cifra:hover { border-color: var(--tn-verde-claro); }
        .rt-cifra.is-activa { border-color: var(--tn-verde); box-shadow: 0 0 0 3px rgba(73, 97, 99, .12); }
        .rt-cifra__icono { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; font-size: 19px; flex: 0 0 auto; }
        .rt-cifra__num { display: block; font-size: 22px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .rt-cifra__txt { font-size: 12.5px; color: var(--tn-suave); }
        .rt-i-concluida { background: #E5F3EA; color: #1E7B3C; }
        .rt-i-confirmada { background: #E3EFF3; color: #1D5F73; }
        .rt-i-incumplimiento { background: #F6E4EA; color: #8C1D40; }
        .rt-i-archivada { background: #EEF0F0; color: #5B6667; }
        @media (max-width: 860px) { .rt-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* Filtros con la misma disposición que Todos los turnos. */
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

        /* Encabezado oscuro de Todos los turnos. Con .tn delante para ganarle a
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
        .rt-interno { font-weight: 700; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .rt-principal { display: block; font-weight: 700; color: var(--tn-tinta); line-height: 1.25; }
        .rt-secundario { display: block; font-size: 12.5px; color: var(--tn-tenue); overflow-wrap: anywhere; }
        
        .rt-acciones { display: flex; flex-direction: column; gap: 6px; align-items: stretch; min-width: 150px; }
        .tn-tabla .btn-sm { white-space: nowrap; font-weight: 600; }

        /* Controles de DataTables con los mismos tonos. */
        .tn .dt-container .dt-search input, .tn .dt-container .dt-length select { border-color: var(--tn-borde); }
        .tn .dt-container .dt-info { font-size: 12.5px; color: var(--tn-tenue); }
        .tn .dt-container .page-link { color: var(--tn-verde); }
        .tn .dt-container .page-item.active .page-link { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .tn-estatus--incumplimiento { background: #F6E4EA; color: #8C1D40; }
        .tn-estatus--atendido        { background: #E5F3EA; color: #1E7B3C; }
        .tn-estatus--confirmada      { background: #E3EFF3; color: #1D5F73; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Ratificaciones</h3>
                <span class="small" style="color: var(--tn-tenue);">Ratificaciones de convenio registradas en SICONCILIO</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('ratificaciones_editar')
                    <a class="btn btn-outline-secondary" href="{{ route('historial_edicion_ratificaciones') }}">
                        <i class="bi bi-clock-history me-1"></i> Historial Edición
                    </a>
                @endcan
                @can('ratificaciones_crear')
                    <a class="btn tn-boton-dorado" href="{{ route('create_cita', ['origen' => 'interno']) }}" target="_blank">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Ratificación
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="rt-cifras">
                @foreach ($apartadosVista as $clave => [$texto, $icono, $tono])
                    {{-- Un segundo clic sobre el apartado activo lo quita. --}}
                    <a class="rt-cifra {{ $estatus === $clave ? 'is-activa' : '' }}"
                       href="{{ $urlCon(['estatus' => $estatus === $clave ? null : $clave]) }}"
                       @if ($estatus === $clave) aria-current="true" @endif>
                        <span class="rt-cifra__icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span>
                        <span><span class="rt-cifra__num">{{ $conteo[$clave] }}</span><span class="rt-cifra__txt">{{ $texto }}</span></span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                {{-- Filtros en el servidor. GET: la URL guarda la vista y se puede compartir. --}}
                <form action="{{ url()->current() }}" method="GET" id="rtFiltros">
                    @if ($estatus)
                        <input type="hidden" name="estatus" value="{{ $estatus }}">
                    @endif

                    <div class="rt-filtros">
                        <div>
                            <label for="rtFecha">Día de la cita</label>
                            <div class="rt-dia">
                                <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->subDay()->toDateString()]) }}" title="Día anterior" aria-label="Día anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <input type="date" id="rtFecha" name="fecha" class="form-control" value="{{ $fecha }}">
                                <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->addDay()->toDateString()]) }}" title="Día siguiente" aria-label="Día siguiente">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div>
                            <label for="rtBuscar">Buscar</label>
                            <div class="rt-buscar">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input type="search" id="rtBuscar" name="buscar" class="form-control" placeholder="NUE o empresa" value="{{ request('buscar') }}">
                            </div>
                        </div>

                        <div>
                            <label for="rtOrden">Ordenar</label>
                            <select id="rtOrden" name="orden" class="form-select">
                                @foreach ($ordenesVista as $clave => $texto)
                                    <option value="{{ $clave }}" @selected($orden === $clave)>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                            @if (request('buscar') || $estatus || $fecha || $orden !== 'recientes')
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Quitar filtros">Limpiar</a>
                            @endif
                        </div>
                    </div>

                    <div class="rt-atajos">
                        <a class="rt-atajo {{ $fecha === null ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => null]) }}">Todos los días</a>
                        <a class="rt-atajo {{ $fecha === $hoy ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $hoy]) }}">Hoy</a>
                        <a class="rt-atajo {{ $fecha === $manana ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $manana]) }}">Mañana</a>
                        <a class="rt-atajo {{ $fecha === $ayer ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $ayer]) }}">Ayer</a>
                    </div>
                </form>
            </div>

            <div class="tn-tarjeta mt-3">
                <div class="rt-encabezado-dia" aria-live="polite">
                    <h4>{{ $fecha ? $fechaLarga($fecha) : 'Todos los días' }}</h4>
                    <span>
                        {{ number_format($solicitudes->total()) }} {{ $solicitudes->total() === 1 ? 'ratificación' : 'ratificaciones' }}@if($estatus) en {{ $apartadosVista[$estatus][0] }}@endif @if(request('buscar')) que coinciden con "{{ request('buscar') }}"@endif
                    </span>
                </div>

                {{-- Sin .menu-visible: esa clase pone overflow:visible y anula
                     el scroll. Se puede quitar porque el desplegable de
                     Documentos ya se posiciona con Popper en estrategia
                     'fixed' (mas abajo), asi que el recorte no lo afecta. --}}
                <div class="table-responsive mt-2">
                    <table id="example" class="table table-hover tn-tabla w-100">
                        <thead>
                            <tr>
                    
                                <th>NUE</th>
                                <th>Fecha</th>
                                <th>Empresa</th>
                                <th>Teléfono</th>
                                <th>Trabajador</th>
                                <th>Delegación</th>
                                <th class="text-center">Estatus</th>
                                @can('ratificaciones_consultar')
                                    <th class="text-center">Detalles</th>
                                @endcan
                                <th class="text-center">Acciones</th>
                                <th class="text-center">Documentos</th>
                            </tr>
                        </thead>
                        <tbody >
                            @foreach($solicitudes as $solicitud)
                                <tr>
                                    
                                    <td><span class="rt-nue">{{ $solicitud->NUE }}</span></td>
                                    <td data-order="{{ $solicitud->fecha }}" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d-m-Y') }}</td>
                                    <td>
                                        <span class="rt-principal">
                                            @if(is_null($solicitud->nombre_empresa) && is_null($solicitud->primero_empresa) && is_null($solicitud->segundo_empresa))
                                                {{ $solicitud->empresa }}
                                            @else
                                                {{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}
                                            @endif
                                        </span>
                                        <span class="rt-secundario">{{ $solicitud->email }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $solicitud->telefono }}</td>
                                    
                                    <td>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</td>
                                    <td>{{ $solicitud->delegacion }}</td>
                                    <td class="text-center">
                                        <span class="tn-estatus {{ $claseEstatus($solicitud->estatus) }}">{{ $solicitud->estatus }}</span>
                                    </td>
                                    @can('ratificaciones_consultar')
                                        <td class="text-center">
                                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('consultar_ratificacion', $solicitud->id) }}">
                                                <i class="bi bi-search me-1"></i><span class="visually-hidden">Consultar campos</span>
                                            </a>
                                            @if($puedeEditarRat)
                                                @if($solicitud->estatus !== "Concluida" && $solicitud->estatus !== "Concluida Pagos")
                                                    <a class="btn btn-outline-success btn-sm" href="{{ route('vista_previa_citas', $solicitud->id) }}">
                                                        <i class="bi bi-pencil me-1"></i><span class="visually-hidden">Editar</span>
                                                    </a>
                                                @endif
                                        
                                            @endif
                                        </td>
                                    @endcan
                                    <td class="text-center">
                                        @can('ratificaciones_concluir')
                                        
                                            @if($solicitud->estatus == "Confirmado")
                                                <a class="btn btn-outline-success btn-sm" href="{{ route('ratificacion_concluir', $solicitud->id) }}">
                                                    <i class="bi bi-check2-circle me-1"></i> Concluir
                                                </a>
                                            @endif
                                        
                                        @endcan
                                    
                                        @if($solicitud->estatus == "Concluida" || $solicitud->estatus == "Concluida Pagos")
                                            @can('cumplimientos_consultar')
                                                <a class="btn btn-outline-primary btn-sm" href="{{ route('ratificacion_cumplimientos', $solicitud->id) }}">
                                                    <i class="bi bi-cash-coin me-1"></i> Cumplimiento
                                                </a>
                                            @endcan
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="rt-acciones">
                                            @can('ratificaciones_subir_documentos')
                                                <button type="button" class="btn tn-boton-dorado btn-sm open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}">
                                                    <i class="bi bi-upload me-1"></i> Subir Documento
                                                </button>
                                            @endcan

                                            @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos', 'Confirmado', 'Incumplimiento', 'Archivada']))
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-folder2-open me-1"></i> Documentos
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-cargar-lista-docs" data-id="{{ $solicitud->id }}" data-doc-url="{{ signedDocRoute('VerDocumentosRatificacionModal', ['id' => $solicitud->id]) }}">
                                                                Documentos Digitales
                                                            </button>
                                                        </li>

                                                        @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos', 'Incumplimiento']))
                                                            @if(($solicitud->estatus == "Concluida" && $solicitud->motivo == "Pago de prestaciones" && $solicitud->PagoPTU == "1") || ($solicitud->estatus == "Concluida" && $solicitud->motivo == "PTU"))
                                                                <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_R', $solicitud->id) }}" target="_blank">Convenio PTU</a></li>
                                                            @else
                                                                <li><a class="dropdown-item" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                            @endif
                                                        @endif

                                                        @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos']))
                                                            <li><a class="dropdown-item" href="{{ route('PDFaudiencia', $solicitud->id) }}" target="_blank">Acta de audiencia</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'ratificacion', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilioR', ['id' => $solicitud->id]) }}" target="_blank">Carátula de Ratificaciones</a></li>
                                                            @if($solicitud->constancia == 0 && $solicitud->estatus == "Concluida")
                                                                <li><a class="dropdown-item" href="{{ route('PDFcumplimientoR', $solicitud->id) }}" target="_blank">Constancia de cumplimiento</a></li>
                                                            @endif
                                                        @elseif($solicitud->estatus == "Confirmado")
                                                            <li><a class="dropdown-item text-success fw-bold" href="{{ route('PDFratifi', $solicitud->id) }}" target="_blank">Acuse</a></li>
                                                        @elseif($solicitud->estatus == "Incumplimiento")
                                                            <li><a class="dropdown-item text-danger fw-bold" href="{{ route('PDFincumplimiento', $solicitud->id) }}" target="_blank">Incumplimiento</a></li>
                                                        @elseif($solicitud->estatus == "Archivada")
                                                            <li><a class="dropdown-item" href="{{ route('PDFinteres', $solicitud->id) }}" target="_blank">Acta de Archivo</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('PDFincomparecenciaT', $solicitud->id) }}" target="_blank">Certificado de incomparecencia</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Rechazar Turnos -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('rechazar_turnos') }}">
            @csrf
            <input type="hidden" id="modal-id" name="id" value="">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="observaciones" class="form-control" rows="4" required placeholder="Escriba el motivo de rechazo..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn tn-boton-dorado">Enviar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Subir Expediente -->
    <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('subir_expediente_ratificacion') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="audiencia_id" id="expediente_audiencia_id" value="">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel"><i class="bi bi-upload me-1" style="color:#CEA845;"></i> Subir expediente</h5>
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn tn-boton-dorado">Agregar</button> 
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Listado de Documentos Digitales -->
    <div class="modal fade" id="modalListaDocs" tabindex="-1" aria-labelledby="modalListaDocsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title mb-0" id="modalListaDocsLabel">
                        <i class="bi bi-folder2-open me-1" style="color:#CEA845;"></i> Documentos Digitales
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover tn-tabla mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Nombre del Documento</th>
                                    <th style="width: 35%;">Archivo</th>
                                    <th class="text-center" style="width: 20%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyListaDocs">
                                <!-- Inyección vía JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('body_end')
<div id="nuevo_poder" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script>
        // Elegir un día o un orden ya filtra; no hace falta el botón.
        ['rtFecha', 'rtOrden'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function () { el.form.submit(); });
        });

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }

            $('#example').DataTable({
                // Aqui NO se colapsan columnas: son 14 y todas se consultan de un
                // vistazo, asi que se prefiere desplazamiento horizontal.
                //
                // El scroll lo da el .table-responsive de Bootstrap, no scrollX.
                // scrollX clona el <thead> en un contenedor aparte y necesita la
                // hoja de estilos de DataTables para ocultar el original; ese CSS
                // no se carga en este proyecto, asi que el encabezado salia doble.
                "responsive": false,
                "destroy": true,
                "paging": true,
                "pageLength": 10,
                "ordering": true,
                "searching": false,  
                "lengthChange": false,
                "order": [],
                "info": true,
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias."
                }
            });

            // El desplegable de Documentos vive dentro del contenedor que ahora
            // hace scroll, y ese contenedor lo recortaria. Con la estrategia
            // 'fixed' de Popper el menu se posiciona contra el viewport y se
            // escapa del recorte, que es la unica forma de tener scroll y
            // desplegable a la vez.
            document.querySelectorAll('#example [data-bs-toggle="dropdown"]').forEach(function (boton) {
                bootstrap.Dropdown.getOrCreateInstance(boton, {
                    popperConfig: function (config) {
                        return Object.assign({}, config, { strategy: 'fixed' });
                    }
                });
            });

            // Asignación de ID para subida de expediente
            $(document).on('click', '.open-expediente-modal', function() {
                var idRegistro = $(this).data('id');            
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });

            // Lógica del Modal de Documentos Digitales vía AJAX
            $(document).on('click', '.btn-cargar-lista-docs', function() {
                const solicitudId = $(this).data('id');
                const tbody = $('#tbodyListaDocs');
                const modalElement = document.getElementById('modalListaDocs');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                // Utiliza la ruta nombrada para consultar documentos de la ratificación (versión JSON del modal)
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
                                    ? `<a href="${item.url}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF</a>`
                                    : `<span class="tn-estatus tn-estatus--pendiente">No disponible</span>`;

                                const row = `
                                    <tr>
                                        <td><span class="rt-principal">${item.nombre}</span></td>
                                        <td class="rt-secundario" style="max-width: 200px;">
                                            <div class="text-truncate" title="${item.archivo || ''}">
                                                ${item.archivo || 'N/A'}
                                            </div>
                                        </td>
                                        <td class="text-center">${botonAccion}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No hay documentos registrados para esta ratificación.</td>
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
@endsection