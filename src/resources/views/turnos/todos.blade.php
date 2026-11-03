@extends('layouts.app')
@section('title', 'Todos los turnos')

@php
    use Illuminate\Support\Carbon;

    $multiSede   = count($sedesPermitidas) > 1;
    $fechaLarga  = fn ($f) => \Illuminate\Support\Str::ucfirst(Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    // Enlace a esta misma pantalla cambiando sólo algunos filtros. Cambiar
    // cualquier filtro regresa a la página 1.
    $con = fn (array $cambios) => route('turnos.todos', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $etiquetasEstatus = [
        'pendiente'  => 'Pendientes',
        'confirmada' => 'Confirmadas',
        'atendido'   => 'Atendidos',
        'expirada'   => 'Expiradas',
        'cancelada'  => 'Canceladas',
    ];
    $estatusActivo = $filtros['estatus'] ?? null;
    // El acuse vive en las rutas de quien crea turnos (CreaTurnos); Directivo sólo consulta.
    $puedeAcuse = auth()->user()->hasAnyRole(array_merge(\App\Http\Middleware\CreaTurnos::ROLES, \App\Support\Recepcion::ROLES))
        || \App\Support\CasosExcepcion::es(auth()->user());
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .tn-filtros {
            display: grid;
            grid-template-columns: auto repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }
        /* Sin selector de sede, la búsqueda ocupa su lugar en la fila. */
        @media (min-width: 1201px) { .tn-buscar--ancho { grid-column: span 2; } }
        @media (max-width: 1200px) { .tn-filtros { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 640px)  { .tn-filtros { grid-template-columns: 1fr; } }

        .tn-filtros label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--tn-tenue);
            margin-bottom: 4px;
        }

        /* Llegó tarde / cuánto lleva en sala sin que la llamen (Mi módulo usa lo mismo). */
        .tn-marca { display: inline-block; margin-top: 4px; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .tn-marca--tarde { background: #FBF4E1; color: #7A5D10; }
        .tn-marca--sala  { background: #EEF0F3; color: #5F6B7A; }
        .tn-marca--espera { background: #FBE3E3; color: #A61B1B; }
        .tn-tarde-error { display: block; font-size: 11.5px; color: #A61B1B; margin-top: 4px; white-space: normal; }

        .tn-dia { display: flex; align-items: stretch; gap: 6px; }
        .tn-dia .form-control { min-width: 150px; }
        .tn-dia .btn { padding-inline: 10px; }

        .tn-atajos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .tn-atajo {
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-suave);
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
        }
        .tn-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .tn-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .tn-pastillas { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 12px; }
        .tn-pastilla {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 10px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-tinta);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .tn-pastilla:hover { border-color: var(--tn-verde-claro); color: var(--tn-tinta); }
        .tn-pastilla.is-activa { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .tn-pastilla__num {
            min-width: 24px;
            padding: 1px 7px;
            border-radius: 999px;
            background: var(--tn-fondo);
            font-size: 12px;
            text-align: center;
        }

        .tn-encabezado-dia {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }
        .tn-encabezado-dia h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--tn-tinta);
        }
        .tn-encabezado-dia span { font-size: 13px; color: var(--tn-tenue); }

        .tn-tabla { margin: 0; }
        /* Con .tn delante para ganarle a .table:not(.table-sm) thead th de style.css,
           que pintaba la cabecera gris claro (el texto blanco no se veía) y metía
           padding de 25px por lado, lo que partía los nombres en muchos renglones. */
        .tn .table.tn-tabla > :not(caption) > * > th,
        .tn .table.tn-tabla > :not(caption) > * > td {
            padding: 10px 12px;
            height: auto;
        }
        .tn .table.tn-tabla thead th {
            background: #354647;
            --bs-table-bg: #354647;
            color: #fff !important;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .03em;
            white-space: nowrap;
            border: 0;
        }
        .tn-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
        .btn.tn-boton-verde { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; font-weight: 600; }
        .btn.tn-boton-verde:not(.btn-social):not(.btn-social-icon):hover,
        .btn.tn-boton-verde:not(.btn-social):not(.btn-social-icon):focus { background: #354647; border-color: #354647 !important; color: #fff; }
        .tn-tabla .tn-solicitante { min-width: 180px; }
        .tn-tabla .tn-contacto { overflow-wrap: anywhere; }
        .tn-tabla .tn-fila-dia td {
            background: var(--tn-fondo);
            font-size: 12.5px;
            font-weight: 700;
            color: var(--tn-verde);
            padding-block: 8px;
        }
        .tn-folio { font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .tn-hora { font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .tn-hora small { display: block; font-weight: 400; color: var(--tn-tenue); }
        .tn-solicitante { font-weight: 600; }
        .tn-contacto { display: block; font-size: 12px; color: var(--tn-tenue); word-break: break-all; }
        .tn-modulo { display: block; font-size: 12px; color: var(--tn-tenue); }

        .tn-vacio {
            padding: 48px 16px;
            text-align: center;
            color: var(--tn-suave);
        }
        .tn-vacio i { font-size: 34px; color: var(--tn-verde-claro); display: block; margin-bottom: 10px; }

        .tn-pie {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
            font-size: 13px;
            color: var(--tn-tenue);
        }
        .tn-pie .pagination { margin: 0; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                {{-- Directivo y Delegado no entran a la pantalla de Turnos de recepción. --}}
                @if (! auth()->user()->hasAnyRole(array_merge(\App\Http\Middleware\CreaTurnos::ROLES, \App\Support\Recepcion::ROLES)))
                    <a href="{{ route('inicio') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                        <i class="bi bi-arrow-left"></i> Inicio
                    </a>
                @else
                    <a href="{{ route('turnos') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                        <i class="bi bi-arrow-left"></i> Turnos
                    </a>
                @endif
                <h3 class="page__heading mb-0">Todos los turnos</h3>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if (\App\Support\AgendaTurnos::puedeDescargar(auth()->user()))
                    <button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#agModal">
                        <i class="bi bi-download me-1"></i> Descargar agenda
                    </button>
                    <button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#ocModal">
                        <i class="bi bi-calendar3 me-1"></i> Ocupación
                    </button>
                @endif
                @can('turnos_crear')
                    <a class="btn tn-boton-dorado shadow-sm" href="{{ route('nueva_cita') }}" onclick="crear_turnos();">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo turno
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $color)
                @if (session($clave))
                    <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Filtros. GET: la URL guarda la vista y se puede compartir. --}}
            <form method="GET" action="{{ route('turnos.todos') }}" class="tn-tarjeta" id="tnFiltros">
                @if ($estatusActivo)
                    <input type="hidden" name="estatus" value="{{ $estatusActivo }}">
                @endif

                <div class="tn-filtros">
                    <div>
                        <label for="tnFecha">Día</label>
                        <div class="tn-dia">
                            <a class="btn btn-outline-secondary" href="{{ $con(['fecha' => $diaAnterior]) }}" title="Día anterior" aria-label="Día anterior">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <input type="date" id="tnFecha" name="fecha" class="form-control" value="{{ $fecha }}">
                            <a class="btn btn-outline-secondary" href="{{ $con(['fecha' => $diaSiguiente]) }}" title="Día siguiente" aria-label="Día siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    @if ($multiSede)
                        <div>
                            <label for="tnSede">Sede</label>
                            <select id="tnSede" name="sede" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($sedesPermitidas as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label for="tnTipo">Trámite</label>
                        <select id="tnTipo" name="tipo" class="form-select">
                            <option value="">Todos</option>
                            @foreach (\App\Support\Recepcion::tramitesVisibles(auth()->user()) ?? \App\Http\Controllers\TurnosHistorialController::TIPOS as $t)
                                <option value="{{ $t }}" @selected(($filtros['tipo'] ?? '') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tnOrigen">Origen</label>
                        <select id="tnOrigen" name="origen" class="form-select">
                            <option value="">Todos</option>
                            <option value="linea" @selected(($filtros['origen'] ?? '') === 'linea')>Cita en línea</option>
                            <option value="ventanilla" @selected(($filtros['origen'] ?? '') === 'ventanilla')>Ventanilla</option>
                        </select>
                    </div>

                    <div class="{{ $multiSede ? '' : 'tn-buscar--ancho' }}">
                        <label for="tnBuscar">Buscar</label>
                        <input type="search" id="tnBuscar" name="q" class="form-control" maxlength="100"
                               placeholder="Nombre o folio" value="{{ $filtros['q'] ?? '' }}">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        @if (request()->except('page'))
                            <a href="{{ route('turnos.todos') }}" class="btn btn-outline-secondary" title="Quitar filtros">Limpiar</a>
                        @endif
                    </div>
                </div>

                <div class="tn-atajos">
                    <a class="tn-atajo {{ $fecha === null ? 'is-activo' : '' }}" href="{{ $con(['fecha' => null]) }}">Todos los días</a>
                    <a class="tn-atajo {{ $fecha === $hoy ? 'is-activo' : '' }}" href="{{ $con(['fecha' => $hoy]) }}">Hoy</a>
                    <a class="tn-atajo {{ $fecha === \Illuminate\Support\Carbon::parse($hoy)->addDay()->toDateString() ? 'is-activo' : '' }}"
                       href="{{ $con(['fecha' => \Illuminate\Support\Carbon::parse($hoy)->addDay()->toDateString()]) }}">Mañana</a>
                    <a class="tn-atajo {{ $fecha === \Illuminate\Support\Carbon::parse($hoy)->subDay()->toDateString() ? 'is-activo' : '' }}"
                       href="{{ $con(['fecha' => \Illuminate\Support\Carbon::parse($hoy)->subDay()->toDateString()]) }}">Ayer</a>
                </div>
            </form>

            {{-- Estatus: cuentan con los demás filtros aplicados. --}}
            <div class="tn-pastillas" role="tablist" aria-label="Estatus">
                <a class="tn-pastilla {{ $estatusActivo ? '' : 'is-activa' }}" href="{{ $con(['estatus' => null]) }}">
                    Todos <span class="tn-pastilla__num">{{ number_format($totalSinEstatus) }}</span>
                </a>
                @foreach ($etiquetasEstatus as $clave => $etiqueta)
                    <a class="tn-pastilla {{ $estatusActivo === $clave ? 'is-activa' : '' }}" href="{{ $con(['estatus' => $clave]) }}">
                        <span class="tn-estatus tn-estatus--{{ $clave }}" style="padding:0;background:none;">{{ $etiqueta }}</span>
                        <span class="tn-pastilla__num">{{ number_format($conteos[$clave] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                <div class="tn-encabezado-dia">
                    <h4>{{ $fecha ? $fechaLarga($fecha) : 'Todos los días' }}</h4>
                    <span>{{ number_format($turnos->total()) }} {{ $turnos->total() === 1 ? 'turno' : 'turnos' }}</span>
                </div>

                @if ($turnos->isEmpty())
                    <div class="tn-vacio">
                        <i class="bi bi-calendar-x" aria-hidden="true"></i>
                        No hay turnos con estos filtros.
                    </div>
                @else
                    <div class="table-responsive mt-2">
                        <table class="table table-hover tn-tabla">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th>Hora</th>
                                    <th>Solicitante</th>
                                    <th>Trámite</th>
                                    <th>Atiende</th>
                                    @if ($multiSede)<th>Sede</th>@endif
                                    <th class="text-center">Estatus</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $diaActual = null; @endphp
                                @foreach ($turnos as $turno)
                                    {{-- Sin día fijo, cada cambio de fecha abre su separador. --}}
                                    @if (! $fecha && $turno->fecha !== $diaActual)
                                        @php $diaActual = $turno->fecha; @endphp
                                        <tr class="tn-fila-dia">
                                            <td colspan="{{ $multiSede ? 8 : 7 }}">
                                                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i> {{ $fechaLarga($turno->fecha) }}
                                                @if ($turno->fecha === $hoy) · hoy @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @php
                                        $enLinea = $turno->origen === 'linea';
                                        $estatus = strtolower((string) $turno->estatus);
                                        $esHoy   = $turno->fecha === $hoy;
                                        // Minutos en sala sin que la llamen (sólo hoy); desde 30, en rojo.
                                        $enSala  = $esHoy ? \App\Support\SigueEnModulo::esperando((object) ['estatus' => $estatus, 'llamado_at' => $turno->llamado_at ?? null, 'confirmada_at' => $turno->confirmada_at ?? null, 'hora' => $turno->hora], $ahora) : null;
                                    @endphp
                                    <tr>
                                        <td class="text-center tn-folio">{{ \App\Support\FolioCita::mostrar($turno) }}</td>
                                        <td class="tn-hora">
                                            {{ substr($turno->hora, 0, 5) }}
                                            @if ($turno->hora_fin)<small>a {{ substr($turno->hora_fin, 0, 5) }}</small>@endif
                                        </td>
                                        <td>
                                            <span class="tn-solicitante">{{ $turno->solicitante }}</span>
                                            @if (filled($turno->correo))
                                                <span class="tn-contacto">{{ $turno->correo }}@if($turno->telefono) · {{ $turno->telefono }}@endif</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $turno->tipo }}<br>
                                            @if ($turno->exepcion === 'Si')
                                                <span class="tn-origen tn-origen--excepcion">Excepción</span>
                                            @elseif ($enLinea)
                                                <span class="tn-origen tn-origen--linea">En línea</span>
                                            @else
                                                <span class="tn-origen tn-origen--ventanilla">Ventanilla</span>
                                            @endif
                                            @if (! empty($turno->es_prueba))
                                                <span class="tn-origen" style="background:#FBF4E1;color:#7A5D10;">Prueba</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $turno->auxiliar ? \Illuminate\Support\Str::title(mb_strtolower($turno->auxiliar)) : 'Pendiente' }}
                                            @if ($turno->lugar_auxiliar)<span class="tn-modulo">{{ $turno->lugar_auxiliar }}</span>@endif
                                        </td>
                                        @if ($multiSede)<td>{{ $turno->delegacion }}</td>@endif
                                        <td class="text-center">
                                            <span class="tn-estatus tn-estatus--{{ $estatus }}">{{ $estatus }}</span>
                                            @if (! empty($turno->llego_tarde) && $estatus !== 'expirada')
                                                <br><span class="tn-marca tn-marca--tarde" title="Recepción la atendió aunque llegó tarde">Llegó tarde</span>
                                            @endif
                                            @if ($enSala !== null && $enSala > 0)
                                                <br><span class="tn-marca {{ $enSala >= \App\Support\SigueEnModulo::ESPERA_ALERTA ? 'tn-marca--espera' : 'tn-marca--sala' }}" title="En sala sin que la llamen">En sala {{ $enSala }} min</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if ($puedeTarde && $esHoy && $estatus === 'expirada')
                                                <button type="button" class="btn btn-warning btn-sm me-1 tn-tarde" data-url="{{ route('recepcion.escaner.tarde', $turno->id) }}"
                                                        data-resumen="{{ \App\Support\FolioCita::mostrar($turno) }} · {{ $turno->solicitante }}"
                                                        title="La persona sí está: vuelve a la fila de su módulo, después de quienes llegaron a tiempo">
                                                    <i class="bi bi-person-check-fill"></i> Atender de todos modos
                                                </button>
                                            @endif
                                            @if ($puedeAcuse)
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('citas.documento', $turno->id) }}" target="_blank" rel="noopener" title="Acuse de la cita">
                                                    <i class="bi bi-file-pdf"></i> Acuse
                                                </a>
                                            @endif
                                            {{-- Reasignar módulo: mismo diálogo y reglas que Administración → Módulos
                                                 (pendiente o confirmada, hoy o después, sin excepción). Las opciones
                                                 sólo vienen para las citas que se pueden mover y si hay permiso. --}}
                                            @if (isset($opciones[$turno->id]))
                                                <button type="button" class="btn btn-sm tn-boton-verde ms-1"
                                                        data-bs-toggle="modal" data-bs-target="#mdModalReasignar"
                                                        data-cita="{{ $turno->id }}" data-tipo="{{ $turno->tipo }}" data-linea="{{ $enLinea ? 1 : 0 }}"
                                                        data-resumen="{{ \App\Support\FolioCita::mostrar($turno) }} · {{ $turno->solicitante }} · {{ $turno->tipo }} a las {{ substr($turno->hora, 0, 5) }}">
                                                    <i class="bi bi-arrow-left-right"></i> {{ $turno->modulo_id || $turno->lugar_auxiliar ? 'Reasignar' : 'Asignar' }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tn-pie">
                        <span>Mostrando {{ $turnos->firstItem() }}–{{ $turnos->lastItem() }} de {{ number_format($turnos->total()) }}</span>
                        @include('turnos._paginacion', ['pagina' => $turnos])
                    </div>
                @endif
            </div>
        </div>
    </section>

    @include('turnos._modal_reasignar', [
        'opciones'     => $opciones,
        'urlReasignar' => route('turnos.reasignar', ['id' => '__ID__']),
    ])

    {{-- Calendario de ocupación de la agenda (App\Support\OcupacionTurnos). Mismos permisos que la descarga. --}}
    @if (\App\Support\AgendaTurnos::puedeDescargar(auth()->user()))
        @include('turnos._ocupacion')
    @endif

    {{-- Descargar la agenda de un día o una semana (App\Support\AgendaTurnos). --}}
    @if (\App\Support\AgendaTurnos::puedeDescargar(auth()->user()))
        @push('modals')
            <div class="modal fade tn" id="agModal" tabindex="-1" aria-labelledby="agModalTitulo" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="GET" action="{{ route('turnos.agenda.descargar') }}" id="agForm" data-sin-carga>
                        <div class="modal-header">
                            <h5 class="modal-title" id="agModalTitulo"><i class="bi bi-calendar-week me-1"></i> Descargar agenda de turnos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="agFecha" class="form-label small fw-semibold">Día</label>
                                    <input type="date" class="form-control" id="agFecha" name="fecha" value="{{ $fecha ?? $hoy }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold d-block">Periodo</label>
                                    <div class="btn-group w-100" role="group" aria-label="Periodo">
                                        <input type="radio" class="btn-check" name="periodo" id="agDia" value="dia" checked>
                                        <label class="btn btn-outline-secondary" for="agDia">Ese día</label>
                                        <input type="radio" class="btn-check" name="periodo" id="agSemana" value="semana">
                                        <label class="btn btn-outline-secondary" for="agSemana">Su semana</label>
                                    </div>
                                    <div class="form-text" id="agSemanaTexto"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="agSede" class="form-label small fw-semibold">Sede</label>
                                    <select class="form-select" id="agSede" name="sede">
                                        @if (count($sedesPermitidas) > 1)
                                            <option value="">{{ count($sedesPermitidas) >= count(\App\Support\Recepcion::TODAS_LAS_SEDES) ? 'Todas las sedes' : 'Mis sedes ('.implode(' y ', $sedesPermitidas).')' }}</option>
                                        @endif
                                        @foreach ($sedesPermitidas as $s)
                                            <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="agExcepcion" class="form-label small fw-semibold">Casos de excepción</label>
                                    <select class="form-select" id="agExcepcion" name="excepcion">
                                        @foreach (['incluir' => 'Incluirlos', 'excluir' => 'Excluirlos', 'solo' => 'Sólo esos casos'] as $valor => $texto)
                                            <option value="{{ $valor }}">{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @role('Super Usuario')
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="agPruebas" name="pruebas">
                                            <label class="form-check-label small" for="agPruebas">Incluir citas de prueba</label>
                                        </div>
                                    </div>
                                @endrole
                            </div>
                            <p class="small mt-3 mb-0" style="color: var(--tn-tenue);">
                                Incluye hora, módulo, folio, trámite, quién llega, quién le atiende, a qué hora llegó, la atención y en qué quedó.
                                Excel trae una hoja de resumen y una por sede; la imagen se abre en otra pestaña y se descarga sola.
                            </p>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" name="formato" value="xlsx" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                                <button type="submit" name="formato" value="pdf" class="btn btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                                <button type="submit" name="formato" value="png" formtarget="_blank" class="btn btn-outline-secondary"><i class="bi bi-image me-1"></i> Imagen</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endpush
    @endif
@endsection

@push('body_end')
<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/turnos/turnos.js') }}"></script>
    <script>
        // Descargar agenda: muestra qué semana se va a bajar.
        (function () {
            var fecha = document.getElementById('agFecha');
            var texto = document.getElementById('agSemanaTexto');
            if (!fecha || !texto) return;
            var meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            function pinta() {
                var semana = document.getElementById('agSemana').checked;
                if (!semana || !fecha.value) { texto.textContent = ''; return; }
                var d = new Date(fecha.value + 'T12:00:00');
                var lunes = new Date(d); lunes.setDate(d.getDate() - ((d.getDay() + 6) % 7));
                var domingo = new Date(lunes); domingo.setDate(lunes.getDate() + 6);
                texto.textContent = 'Del ' + lunes.getDate() + ' ' + meses[lunes.getMonth()] + ' al ' + domingo.getDate() + ' ' + meses[domingo.getMonth()];
            }
            fecha.addEventListener('change', pinta);
            document.querySelectorAll('input[name="periodo"]').forEach(function (r) { r.addEventListener('change', pinta); });
            // Bajar un archivo no cambia de página: se cierra el diálogo.
            document.getElementById('agForm').addEventListener('submit', function (e) {
                var formato = e.submitter ? e.submitter.value : '';
                if (formato !== 'png') {
                    setTimeout(function () { bootstrap.Modal.getInstance(document.getElementById('agModal'))?.hide(); }, 300);
                }
            });
        })();
    </script>
    <script>
        // "Atender de todos modos": la cita expiró hoy y la persona sí está (mismo
        // endpoint que el escáner). Al terminar se recarga la lista.
        document.querySelectorAll('.tn-tarde').forEach(function (b) {
            b.addEventListener('click', async function () {
                if (!confirm('¿Atender de todos modos ' + b.dataset.resumen + '?\nVuelve a la fila de su módulo, después de quienes llegaron a tiempo.')) return;
                b.disabled = true;
                var token = document.querySelector('meta[name="csrf-token"]');
                var aviso = function (t) { var s = document.createElement('span'); s.className = 'tn-tarde-error'; s.textContent = t; b.after(s); };
                try {
                    var r = await fetch(b.dataset.url, { method: 'POST', credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.content : '' } });
                    var d = r.ok ? await r.json() : null;
                    if (d && d.resultado === 'confirmada_tarde') { location.reload(); return; }
                    aviso(d ? d.mensaje : (r.status === 419 ? 'La sesión expiró; recarga la página.' : 'No se pudo (' + r.status + ').'));
                } catch (e) { aviso('Sin conexión.'); }
                b.disabled = false;
            });
        });
    </script>
    <script>
        // Elegir un día o un filtro de lista ya filtra; no hace falta el botón.
        (function () {
            var form = document.getElementById('tnFiltros');
            if (!form) return;
            ['tnFecha', 'tnSede', 'tnTipo', 'tnOrigen'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('change', function () { form.submit(); });
            });
        })();
    </script>
@endsection
