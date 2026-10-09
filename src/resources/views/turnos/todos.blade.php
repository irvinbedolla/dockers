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
    ];
    $estatusActivo = $filtros['estatus'] ?? null;
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
        .tn-tabla thead th {
            background: #354647;
            color: #fff !important;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .03em;
            white-space: nowrap;
            border: 0;
        }
        .tn-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }
        .tn-tabla .tn-fila-dia td {
            background: var(--tn-fondo);
            font-size: 12.5px;
            font-weight: 700;
            color: var(--tn-verde);
            padding-block: 8px;
        }
        .tn-folio { font-weight: 700; font-variant-numeric: tabular-nums; }
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
                <a href="{{ route('turnos') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Turnos
                </a>
                <h3 class="page__heading mb-0">Todos los turnos</h3>
            </div>
            @can('turnos_crear')
                <a class="btn tn-boton-dorado shadow-sm" href="{{ route('nueva_cita') }}" onclick="crear_turnos();">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo turno
                </a>
            @endcan
        </div>

        <div class="section-body">
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
                            @foreach (\App\Http\Controllers\TurnosHistorialController::TIPOS as $t)
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
                                        $enLinea = filled($turno->correo);
                                        $estatus = strtolower((string) $turno->estatus);
                                    @endphp
                                    <tr>
                                        <td class="text-center tn-folio">{{ str_pad($turno->consecutivo, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="tn-hora">
                                            {{ substr($turno->hora, 0, 5) }}
                                            @if ($turno->hora_fin)<small>a {{ substr($turno->hora_fin, 0, 5) }}</small>@endif
                                        </td>
                                        <td>
                                            <span class="tn-solicitante">{{ $turno->solicitante }}</span>
                                            @if ($enLinea)
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
                                        </td>
                                        <td>
                                            {{ $turno->auxiliar ? \Illuminate\Support\Str::title(mb_strtolower($turno->auxiliar)) : 'Pendiente' }}
                                            @if ($turno->lugar_auxiliar)<span class="tn-modulo">{{ $turno->lugar_auxiliar }}</span>@endif
                                        </td>
                                        @if ($multiSede)<td>{{ $turno->delegacion }}</td>@endif
                                        <td class="text-center">
                                            <span class="tn-estatus tn-estatus--{{ $estatus }}">{{ $estatus }}</span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('citas.documento', $turno->id) }}" target="_blank" rel="noopener" title="Acuse de la cita">
                                                <i class="bi bi-file-pdf"></i> Acuse
                                            </a>
                                            {{-- Asignar sólo el mismo día, igual que en "Turnos de hoy":
                                                 la asignación mira quién está disponible hoy. --}}
                                            @can('turnos_asignar')
                                                @if ($estatus === 'pendiente' && $turno->exepcion === 'No' && $turno->fecha === $hoy)
                                                    <a class="btn btn-sm text-white ms-1" style="background: var(--tn-verde);" href="{{ route('cambiar', $turno->id) }}" onclick="disponibles();">
                                                        <i class="bi bi-person-check"></i> Asignar
                                                    </a>
                                                @endif
                                            @endcan
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
