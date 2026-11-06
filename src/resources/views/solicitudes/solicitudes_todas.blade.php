@extends('layouts.app')
@section('title', 'Solicitudes')

@php
    // El día, los apartados y el orden se filtran en el servidor (SeerController::todas_solicitudes)
    // porque la lista va paginada; cada liga conserva búsqueda, día, apartado y orden.
    $urlCon = fn (array $cambios) => url()->current().'?'.http_build_query(array_filter(
        array_merge(request()->only(['buscar', 'estatus', 'orden', 'fecha']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $apartadosVista = [
        'confirmadas'     => ['Confirmadas', 'bi-calendar-check', 'so-t-confirmadas'],
        'conciliadas'     => ['Conciliación / Concluidas', 'bi-check2-all', 'so-t-conciliadas'],
        'no_conciliacion' => ['No conciliación', 'bi-x-circle', 'so-t-no_conciliacion'],
        'incumplimiento'  => ['Incumplimiento', 'bi-exclamation-octagon', 'so-t-incumplimiento'],
        'archivadas'      => ['Archivadas', 'bi-archive', 'so-t-archivadas'],
        'otras'           => ['Otras', 'bi-three-dots', 'so-t-otras'],
    ];
    // Tono del estatus en la tabla: el de su apartado.
    $tonoEstatus = function (?string $e) use ($apartados, $apartadosVista) {
        foreach ($apartados as $clave => $valores) {
            if (in_array($e, $valores, true)) {
                return $apartadosVista[$clave][2];
            }
        }
        return 'so-t-otras';
    };
    $hoy = now()->toDateString();
    $ayer = now()->subDay()->toDateString();
    $base = $fecha ? \Illuminate\Support\Carbon::parse($fecha) : now();
    $fechaLarga = fn ($f) => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $ordenesVista = [
        'recientes'      => 'Registradas más recientes',
        'antiguas'       => 'Registradas más antiguas',
        'fecha_reciente' => 'Fecha de captura más reciente',
        'fecha_antigua'  => 'Fecha de captura más antigua',
        'nue'            => 'Expediente',
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .rt-cifras { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
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
        @media (max-width: 860px) { .rt-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* Un tono por apartado: el mismo en la cifra y en el estatus de la tabla. */
        .so-t-confirmadas     { background: #E3EFF3; color: #1D5F73; }
        .so-t-conciliadas     { background: #E5F3EA; color: #1E7B3C; }
        .so-t-no_conciliacion { background: #E7ECEC; color: #496163; }
        .so-t-incumplimiento  { background: #F6E4EA; color: #8C1D40; }
        .so-t-archivadas      { background: #EEF0F0; color: #5B6667; }
        .so-t-otras           { background: #EEEAF4; color: #5B4A7A; }
        .so-incidencia { display: inline-block; padding: 1px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #F6E4EA; color: #8C1D40; }

        /* Filtros con la misma disposición que Ratificaciones. */
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
        /* Hay citados muy largos ("QUIEN O QUIENES RESULTEN..."): dos líneas y el texto completo en el title. */
        .so-citados {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-width: 180px; max-width: 280px; line-height: 1.3;
        }
        .so-texto { max-width: 240px; line-height: 1.3; }

        .rt-acciones { display: flex; flex-direction: column; gap: 6px; align-items: stretch; min-width: 150px; }
        .tn-tabla .btn-sm { white-space: nowrap; font-weight: 600; }

        .rt-pie { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 14px; font-size: 13px; color: var(--tn-tenue); }
        .rt-vacio { padding: 48px 16px; text-align: center; color: var(--tn-suave); }
        .rt-vacio i { font-size: 34px; display: block; margin-bottom: 10px; color: var(--tn-verde-claro); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Solicitudes</h3>
                <span class="small" style="color: var(--tn-tenue);">Solicitudes de conciliación registradas en SICONCILIO</span>
            </div>
        </div>

        <div class="section-body">
            <div class="rt-cifras">
                @foreach ($apartadosVista as $clave => [$texto, $icono, $tono])
                    {{-- Un segundo clic sobre el apartado activo lo quita. --}}
                    <a class="rt-cifra {{ $estatus === $clave ? 'is-activa' : '' }}"
                       href="{{ $urlCon(['estatus' => $estatus === $clave ? null : $clave]) }}"
                       title="{{ implode(', ', $apartados[$clave]) }}{{ $clave === 'otras' ? ' e incidencias' : '' }}"
                       @if ($estatus === $clave) aria-current="true" @endif>
                        <span class="rt-cifra__icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span>
                        <span><span class="rt-cifra__num">{{ number_format($conteo[$clave]) }}</span><span class="rt-cifra__txt">{{ $texto }}</span></span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                {{-- Filtros en el servidor. GET: la URL guarda la vista y se puede compartir. --}}
                <form action="{{ url()->current() }}" method="GET" id="soFiltros">
                    @if ($estatus)
                        <input type="hidden" name="estatus" value="{{ $estatus }}">
                    @endif

                    <div class="rt-filtros">
                        <div>
                            <label for="soFecha">Fecha de captura</label>
                            <div class="rt-dia">
                                <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->subDay()->toDateString()]) }}" title="Día anterior" aria-label="Día anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <input type="date" id="soFecha" name="fecha" class="form-control" value="{{ $fecha }}">
                                <a class="btn btn-outline-secondary" href="{{ $urlCon(['fecha' => $base->copy()->addDay()->toDateString()]) }}" title="Día siguiente" aria-label="Día siguiente">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div>
                            <label for="soBuscar">Buscar</label>
                            <div class="rt-buscar">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input type="search" id="soBuscar" name="buscar" class="form-control" placeholder="NUE, solicitante o citado" value="{{ request('buscar') }}">
                            </div>
                        </div>

                        <div>
                            <label for="soOrden">Ordenar</label>
                            <select id="soOrden" name="orden" class="form-select">
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
                        <a class="rt-atajo {{ $fecha === $ayer ? 'is-activo' : '' }}" href="{{ $urlCon(['fecha' => $ayer]) }}">Ayer</a>
                    </div>
                </form>
            </div>

            @can('solicitudes_ver')
                <div class="tn-tarjeta mt-3">
                    <div class="rt-encabezado-dia" aria-live="polite">
                        <h4>{{ $fecha ? $fechaLarga($fecha) : 'Todos los días' }}</h4>
                        <span>
                            {{ number_format($solicitudes->total()) }} {{ $solicitudes->total() === 1 ? 'solicitud' : 'solicitudes' }}@if($estatus) en {{ $apartadosVista[$estatus][0] }}@endif @if(request('buscar')) que coinciden con "{{ request('buscar') }}"@endif
                        </span>
                    </div>

                    @if ($solicitudes->isEmpty())
                        <div class="rt-vacio">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            No hay solicitudes con estos filtros.
                        </div>
                    @else
                        {{-- Sin overflow:visible: los desplegables de Documentos se
                             posicionan con Popper en estrategia 'fixed' (más abajo),
                             así que el scroll horizontal no los recorta. --}}
                        <div class="table-responsive mt-2">
                            <table id="example" class="table table-hover tn-tabla w-100">
                                <thead>
                                    <tr>
                                        <th class="text-center">Folio</th>
                                        <th>Fecha Captura</th>
                                        <th>Expediente</th>
                                        <th>Solicitante</th>
                                        <th>Teléfono</th>
                                        <th>Citados</th>
                                        <th>Actividad Económica</th>
                                        <th>Tipo Solicitante</th>
                                        <th class="text-center">Estatus</th>
                                        @can('solicitudes_revisar')
                                            <th class="text-center">Revisar</th>
                                        @endcan
                                        <th class="text-center">Documentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($solicitudes as $solicitud)
                                        <tr>
                                            <td class="text-center"><span class="rt-interno">{{ $solicitud->consecutivo }}</span></td>
                                            <td data-order="{{ $solicitud->fecha }}" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d-m-Y') }}</td>
                                            <td><span class="rt-nue">{{ $solicitud->NUE }}</span></td>
                                            <td><span class="rt-principal">{{ $solicitud->nombre }}</span></td>
                                            <td style="white-space: nowrap;">{{ $solicitud->telefono }}</td>
                                            <td><span class="so-citados" title="{{ $solicitud->lista_citados }}">{{ $solicitud->lista_citados }}</span></td>
                                            <td><div class="so-texto">{{ $solicitud->actividad }}</div></td>
                                            <td>
                                                @if($solicitud->tipo_solicitud == 1) Trabajador
                                                @elseif($solicitud->tipo_solicitud == 2) Patronal
                                                @elseif($solicitud->tipo_solicitud == 3) Patronal Colectiva
                                                @elseif($solicitud->tipo_solicitud == 4) Sindical
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($solicitud->incidencia == 1)
                                                    <span class="tn-estatus so-t-otras">{{ $solicitud->estatus }}</span>
                                                    <span class="d-block mt-1"><span class="so-incidencia"><i class="bi bi-flag-fill"></i> Incidencia</span></span>
                                                @else
                                                    <span class="tn-estatus {{ $tonoEstatus($solicitud->estatus) }}">{{ $solicitud->estatus }}</span>
                                                @endif
                                            </td>
                                            @can('solicitudes_revisar')
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary btn-sm open-audiencias-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAudiencias"
                                                        data-id="{{ $solicitud->id }}">
                                                        <i class="bi bi-eye me-1"></i> Revisar
                                                    </button>
                                                </td>
                                            @endcan
                                            <td class="text-center">
                                                <div class="rt-acciones">
                                                    @can('solicitudes_subir_documentos')
                                                        <button type="button" class="btn tn-boton-dorado btn-sm open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}">
                                                            <i class="bi bi-upload me-1"></i> Subir Documento
                                                        </button>
                                                    @endcan

                                                    @if(in_array($solicitud->estatus, ['Archivada', 'Incompetencia', 'Comparecencia', 'Reagendada', 'No conciliacion', 'Incumplimiento', 'Conciliacion', 'Concluida', 'Reinstalacion', 'Confirmado', 'Desistimiento', 'Prevencion', 'Canalizada']))
                                                        <div class="dropdown">
                                                            @can('solicitudes_ver_documentos')
                                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bi bi-folder2-open me-1"></i> Documentos
                                                                </button>
                                                            @endcan
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                <li>
                                                                    <button type="button" class="dropdown-item btn-cargar-lista-docs" data-id="{{ $solicitud->id }}" data-doc-url="{{ signedDocRoute('VerDocumentosAudiencia', ['id' => $solicitud->id]) }}">
                                                                        Documentos Digitales
                                                                    </button>
                                                                </li>

                                                                @if($solicitud->estatus == "Archivada")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFfalltaInteres', $solicitud->id) }}" target="_blank">Acta de Archivo</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                @elseif($solicitud->estatus == "Incompetencia")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFincompetencia', $solicitud->id) }}" target="_blank">Incompetencia</a></li>
                                                                @elseif($solicitud->estatus == "Comparecencia")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFinteres', $solicitud->id) }}" target="_blank">Acta de incomparecencia</a></li>
                                                                @elseif($solicitud->estatus == "Reagendada" || $solicitud->estatus == "Confirmado" || $solicitud->estatus == "Desistimiento")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    @if(in_array($solicitud->estatus, ['Confirmado', 'Desistimiento']))
                                                                        <li><a class="dropdown-item" href="{{ route('PDFacuseConfirmada', $solicitud->id) }}" target="_blank">Acuse de solicitud confirmada</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                    @endif
                                                                @elseif($solicitud->estatus == "No conciliacion")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFno_conciliacion', $solicitud->id) }}" target="_blank">Constancias de no conciliación</a></li>
                                                                    <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#noConciliacion" data-id="{{ $solicitud->id }}">Constancia de no conciliación</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                @elseif(in_array($solicitud->estatus, ['Conciliacion', 'Concluida', 'Reinstalacion','Incumplimiento']))
                                                                    @if(isset($solicitud->mostrar_ptu) && $solicitud->mostrar_ptu)
                                                                        <li><a class="dropdown-item bg-success text-white fw-bold" href="{{ route('PDFconvenioPTU_NO_S', $solicitud->id) }}" target="_blank">Convenio PTU (No Labora)</a></li>
                                                                    @else
                                                                        <li>
                                                                            <a class="dropdown-item btn-convenio-audiencia" 
                                                                            data-id="{{ $solicitud->id }}"
                                                                            data-base="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                            href="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                            target="_blank">Convenio</a>
                                                                        </li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                    @endif
                                                                    @if(in_array($solicitud->estatus, ['Conciliacion', 'Concluida', 'Reinstalacion']))
                                                                        <li>
                                                                            <a class="dropdown-item btn-constancia-audiencia" 
                                                                            data-id="{{ $solicitud->id }}"
                                                                            data-base="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                            href="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                            target="_blank">Constancia de cumplimiento</a>
                                                                        </li>
                                                                    @endif
                                                                @elseif($solicitud->estatus == "Prevencion")
                                                                    <li><a class="dropdown-item" href="{{ route('PDFacuse_solicitud', $solicitud->id) }}" target="_blank">Acuse de solicitud</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                                @endif

                                                                <li><button type="button" class="dropdown-item btn-mostrar-registros" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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

                        <div class="rt-pie">
                            <span>Mostrando {{ number_format($solicitudes->firstItem()) }}–{{ number_format($solicitudes->lastItem()) }} de {{ number_format($solicitudes->total()) }}</span>
                            @include('turnos._paginacion', ['pagina' => $solicitudes])
                        </div>
                    @endif
                </div>
            @endcan
        </div>
    </section>

    <!-- Modales -->
    <div id="nuevo_usuario" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Modal Audiencias -->
    <div class="modal fade" id="modalAudiencias" tabindex="-1" aria-labelledby="modalAudienciasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAudienciasLabel">Audiencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle w-100 text-center">
                            <thead style="background-color: #D2D3D5;">
                                <tr>
                                    <th>ID</th>
                                    <th>Estatus</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaAudienciasSolicitud"></tbody>
                        </table>
                    </div>
                    <div id="audienciasSolicitudEmpty" class="text-center text-muted" style="display:none;">
                        No se encontraron audiencias para esta solicitud.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Documentos / Citatorios -->
    <div class="modal fade" id="documentos" tabindex="-1" aria-labelledby="modalLabelCitatorios" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelCitatorios">Citatorios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="citatoriosPasoAudiencias">
                        <p class="text-muted mb-2">Selecciona la audiencia de la que quieres ver los citatorios.</p>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle w-100 text-center">
                                <thead style="background-color: #D2D3D5;">
                                    <tr>
                                        <th>ID</th>
                                        <th>Estatus</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="listaAudienciasCitatorios"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paso 2: citatorios de la audiencia elegida -->
                    <div id="citatoriosPasoCitados" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" id="citatoriosAudienciaTitulo"></span>
                            <button type="button" class="btn btn-secondary btn-sm" id="citatoriosVolver">
                                <i class="bi bi-arrow-left me-1"></i> Volver a audiencias
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle w-100 text-center">
                                <thead style="background-color: #D2D3D5;">
                                    <tr>
                                        <th>Citatorios</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="listaRegistros"></tbody>
                            </table>
                        </div>
                    </div>
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
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845; color:#ffffff !important;">Agregar</button> 
                    </div>
                </div>
            </div>
        </form>
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
<!-- Modal Consatncias de no conciliacion separadas -->
    <div class="modal fade" id="noConciliacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Constancias de No Conciliación</h5>
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script>
        // Elegir un día o un orden ya filtra; no hace falta el botón.
        ['soFecha', 'soOrden'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function () { el.form.submit(); });
        });

        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }

            $('#example').DataTable({
                // Sin colapso de columnas: se prefiere desplazamiento horizontal, que
                // lo da el .table-responsive de Bootstrap. No se usa scrollX porque
                // clona el <thead> y necesita la hoja de estilos de DataTables, que
                // este proyecto no carga.
                // Búsqueda, apartados y páginas van en el servidor (filtros de arriba
                // y paginación de abajo); DataTables sólo ordena las columnas.
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


            // Modal Citatorios vía AJAX (Bootstrap 5.3 API)
            // Paso 1: al abrir el modal se listan las audiencias de la solicitud para elegir una
            let citatoriosSolicitudId = null;

            function mostrarPasoAudienciasCitatorios() {
                $('#citatoriosPasoCitados').hide();
                $('#citatoriosPasoAudiencias').show();
            }

            $(document).on('click', '.btn-mostrar-registros', function() {
                citatoriosSolicitudId = $(this).data('id');
                const lista = $('#listaAudienciasCitatorios');
                const endpoint = `{{ url('/api/audiencias-por-solicitud') }}/${citatoriosSolicitudId}`;

                mostrarPasoAudienciasCitatorios();
                lista.html('<tr><td colspan="5" class="text-muted">Cargando...</td></tr>');

                $.ajax({
                    url: endpoint,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        lista.empty();
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(a, index) {
                                const hora = a.hora ? (a.hora.substring(0, 5) + ' HRS') : '';
                                const titulo = `Audiencia ${index + 1} de ${data.length} (${a.fecha || 'sin fecha'})`;
                                lista.append(`
                                    <tr>
                                        <td>${a.id}</td>
                                        <td>${a.estatus || ''}</td>
                                        <td>${a.fecha || ''}</td>
                                        <td>${hora}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm btn-citatorios-audiencia"
                                                data-audiencia-id="${a.id}" data-titulo="${titulo}">
                                                Ver citatorios
                                            </button>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            lista.append('<tr><td colspan="5" class="text-muted">No se encontraron audiencias para esta solicitud.</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener audiencias:', error);
                        lista.html('<tr><td colspan="5" class="text-danger">Error de conexión con el servidor.</td></tr>');
                    }
                });
            });

            // Paso 2: citatorios de la audiencia elegida
            $(document).on('click', '.btn-citatorios-audiencia', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const pdfRouteBase = '{{ route("pdfCitatorioAudiencia", ["id" => "xxx"]) }}';

                $('#citatoriosAudienciaTitulo').text($(this).data('titulo'));
                $('#citatoriosPasoAudiencias').hide();
                $('#citatoriosPasoCitados').show();
                listaRegistros.html('<tr><td colspan="2" class="text-muted">Cargando...</td></tr>');

                $.ajax({
                    url: `${pdfsUrlBase}/${citatoriosSolicitudId}`,
                    data: { audiencia_id: $(this).data('audienciaId') },
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        listaRegistros.empty();
                        if (data.length > 0) {
                            $.each(data, function(index, registro) {
                                const pdfUrl = pdfRouteBase.replace('xxx', registro.id);
                                const listItem = `
                                <tr>
                                    <td class="text-start"><strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido || ''}</strong></td>
                                    <td>
                                        <a href="${pdfUrl}" target="_blank" class="btn btn-primary btn-sm">Ver PDF</a>
                                    </td>
                                </tr>`;
                                listaRegistros.append(listItem);
                            });
                        } else {
                            listaRegistros.append('<tr><td colspan="2" class="text-muted">No hay citatorios para esta audiencia.</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener los datos:", error);
                        listaRegistros.html('<tr><td colspan="2" class="text-danger">Error de conexión con el servidor.</td></tr>');
                    }
                });
            });

            $('#citatoriosVolver').on('click', mostrarPasoAudienciasCitatorios);

            $(document).on('click', '.open-expediente-modal', function() {
                var idRegistro = $(this).data('id');            
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });

            // Modal Audiencias para Revisar
            $(document).on('click', '.open-audiencias-modal', function() {
                const solicitudId = $(this).data('id');
                const lista = $('#listaAudienciasSolicitud');
                const empty = $('#audienciasSolicitudEmpty');
                lista.empty();
                empty.hide();

                const endpoint = `{{ url('/api/audiencias-por-solicitud') }}/${solicitudId}`;
                const revisarBase = `{{ route('solicitud_audiencia', 0) }}?isAudiencia=No&audiencia_id=AUDIENCIA_ID`;

                $.ajax({
                    url: endpoint,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(a) {
                                const revisarUrl = revisarBase
                                    .replace('/0?', `/${solicitudId}?`)
                                    .replace('AUDIENCIA_ID', a.id);

                                const fecha = a.fecha || '';
                                const hora = a.hora ? (a.hora.substring(0,5) + ' HRS') : '';
                                let estatus = a.estatus || '';
                                if (estatus === 'No conciliacion reagendada') {
                                    estatus = 'Reagendada (Solicitud de nueva fecha)';
                                } else if (estatus === 'Reagendada') {
                                    estatus = 'Reagendada (A notificar por el CCL)';
                                }

                                lista.append(`
                                    <tr>
                                        <td>${a.id}</td>
                                        <td>${estatus}</td>
                                        <td>${fecha}</td>
                                        <td>${hora}</td>
                                        <td>
                                            <a href="${revisarUrl}" class="btn btn-primary btn-sm">Revisar</a>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            empty.text('No se encontraron audiencias para esta solicitud.').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener audiencias:', error);
                        empty.text('Error de conexión con el servidor.').show();
                    }
                });
            });

            async function getUltimaAudienciaIdPorSolicitud(solicitudId) {
                try {
                    const endpoint = `{{ url('/api/audiencias-por-solicitud') }}/${solicitudId}`;
                    const resp = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                    if (!resp.ok) return null;
                    const data = await resp.json();
                    if (!Array.isArray(data) || data.length === 0) return null;
                    const last = data[data.length - 1];
                    return last?.id ?? null;
                } catch (e) {
                    console.error('Error al consultar última audiencia:', e);
                    return null;
                }
            }

            function buildUrlWithAudienciaId(baseUrl, audienciaId) {
                if (!audienciaId) return baseUrl;
                const sep = baseUrl.includes('?') ? '&' : '?';
                return `${baseUrl}${sep}audiencia_id=${audienciaId}`;
            }

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

            // Limpiar el iframe al cerrar el modal para no consumir memoria
            $('#modalDocumentosIframe').on('hidden.bs.modal', function () {
                $('#iframeDocumentos').attr('src', '');
            });
        });
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