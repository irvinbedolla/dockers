@extends('layouts.app')
@section('title', 'Historial de notificaciones')

{{-- Historial de notificaciones. Paginado y filtrado en el servidor (NotificacionesController::historial). --}}

@php
    $url = fn (array $cambios) => route('notificaciones_consultar', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $estatusActivo = $filtros['estatus'] ?? null;
    $totalTodos = $conteos->sum();
    $puedeReasignar = auth()->user()->hasAnyRole(['Enlace', 'Super Usuario']);

    // Documentos por estatus: la misma tabla de antes, en un solo lugar.
    $documentos = function ($n) {
        $r = fn ($nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre, [$n->id, $n->id_solicitud]) : null;
        $notif = auth()->user()->can('notificaciones_ver_notificacion');
        $par = fn ($notificacion, $multa) => array_filter(['Notificación' => $notif ? $r($notificacion) : null, 'Multa' => $r($multa)]);
        return match ($n->estatus) {
            'Finalizado exitosamente', 'Recibe pero no firma'                  => $par('PDFRazonNoticacion', 'PDFmultaNotificacion'),
            'No notificada', 'Notificada en Audiencia', 'Exitosa por Instructivo' => $par('PDFInstructivo', 'VerPDFMultaInstructivo'),
            'No exitosa se constituye'                                         => $par('VerPDFNoExitConstituye', 'VerPDFMultaNoExitConstituye'),
            'No exitosa no se constituye'                                      => $par('PDFnotificadoNoexitosaNS', 'VerPDFMultaNoExitConstituye'),
            default                                                            => [],
        };
    };
    $verDocs = auth()->user()->can('notificaciones_ver_documento');

    // Periodos rápidos del filtro avanzado.
    $hoyC = \Illuminate\Support\Carbon::today();
    $periodos = [
        'Hoy'          => [$hoyC->toDateString(), $hoyC->toDateString()],
        'Esta semana'  => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->copy()->endOfWeek()->toDateString()],
        'Este mes'     => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->copy()->endOfMonth()->toDateString()],
        'Mes pasado'   => [$hoyC->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hoyC->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
    ];
    $desde = $filtros['desde'] ?? null;
    $hasta = $filtros['hasta'] ?? null;
    // Las cifras por notificador viven en /estadisticas/notificaciones.
    $verEstadisticas = auth()->user()->hasAnyRole(['Super Usuario', 'Estadistica', 'Directivo'])
        && \Illuminate\Support\Facades\Route::has('estadisticas.notificaciones');
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        /* Filtro por sede y periodo de la lista. */
        .nt-periodo { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--tn-borde); }
        .nt-periodo label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .nt-periodo .form-control, .nt-periodo .form-select { min-width: 150px; }
        .nt-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .nt-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; }
        .nt-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .nt-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('index_notificaciones') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Notificaciones
                </a>
                <h3 class="page__heading mb-0">Historial de notificaciones</h3>
            </div>
            @if ($verEstadisticas)
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.notificaciones', array_filter(['sede' => $filtros['sede'] ?? null, 'tipo' => $filtros['tipo'] ?? null, 'notificador' => $filtros['notificador'] ?? null, 'desde' => $filtros['desde'] ?? null, 'hasta' => $filtros['hasta'] ?? null])) }}">
                    <i class="bi bi-bar-chart-line"></i> Estadísticas
                </a>
            @endif
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $tipo)
                @if (session($clave))
                    <div class="alert alert-{{ $tipo }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach

            <form method="GET" action="{{ route('notificaciones_consultar') }}" class="tn-tarjeta" id="ntFiltros">
                @if ($estatusActivo)<input type="hidden" name="estatus" value="{{ $estatusActivo }}">@endif
                @if (! empty($filtros['por']))<input type="hidden" name="por" value="{{ $filtros['por'] }}">@endif
                <div class="nt-filtros">
                    <div>
                        <label for="ntBuscar">Buscar</label>
                        <input type="search" id="ntBuscar" name="buscar" class="form-control" maxlength="100"
                               placeholder="Expediente, citado, colonia o calle" value="{{ $filtros['buscar'] ?? '' }}">
                    </div>
                    <div>
                        <label for="ntNotificador">Notificador</label>
                        <select id="ntNotificador" name="notificador" class="form-select nt-auto">
                            <option value="">Todos</option>
                            <option value="sin" @selected(($filtros['notificador'] ?? '') === 'sin')>Sin notificador</option>
                            @foreach ($notificadoresPorSede as $sede => $gente)
                                <optgroup label="{{ $sede ?: 'Sin sede' }}">
                                    @foreach ($gente as $p)
                                        <option value="{{ $p->id }}" @selected((string) ($filtros['notificador'] ?? '') === (string) $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    @if (count($sedesPermitidas) > 1)
                        <div>
                            <label for="ntSede">Sede</label>
                            <select id="ntSede" name="sede" class="form-select nt-auto">
                                <option value="">Todas</option>
                                @foreach ($sedesPermitidas as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="ntTipo">Tipo</label>
                        <select id="ntTipo" name="tipo" class="form-select nt-auto">
                            <option value="">Todos</option>
                            <option value="Citatorio" @selected(($filtros['tipo'] ?? '') === 'Citatorio')>Citatorio</option>
                            <option value="Multa" @selected(($filtros['tipo'] ?? '') === 'Multa')>Multa</option>
                        </select>
                    </div>
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        @if (array_filter(request()->except(['page', 'por'])))
                            <a href="{{ route('notificaciones_consultar') }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>

                {{-- Periodo --}}
                <div class="nt-periodo">
                    <div>
                        <label>Periodo</label>
                        <div class="nt-atajos">
                            <a class="nt-atajo {{ ! $hayPeriodo ? 'is-activo' : '' }}" href="{{ $url(['desde' => null, 'hasta' => null]) }}">Todo</a>
                            @foreach ($periodos as $etiqueta => [$d, $h])
                                <a class="nt-atajo {{ $desde === $d && $hasta === $h ? 'is-activo' : '' }}" href="{{ $url(['desde' => $d, 'hasta' => $h]) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="ntDesde">Desde</label>
                        <input type="date" id="ntDesde" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div>
                        <label for="ntHasta">Hasta</label>
                        <input type="date" id="ntHasta" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <div>
                        <label for="ntFechaDe">La lista filtra por</label>
                        <select id="ntFechaDe" name="fecha_de" class="form-select">
                            <option value="notificacion" @selected(($filtros['fecha_de'] ?? 'notificacion') === 'notificacion')>Fecha de la diligencia</option>
                            <option value="asignacion" @selected(($filtros['fecha_de'] ?? '') === 'asignacion')>Fecha de asignación</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="nt-pestanas">
                <a class="nt-pestana {{ $estatusActivo ? '' : 'is-activa' }}" href="{{ $url(['estatus' => null]) }}">
                    Todas <span class="nt-pestana__num">{{ number_format($totalTodos) }}</span>
                </a>
                @foreach (\App\Http\Controllers\NotificacionesController::ESTATUS as $e)
                    @continue(! ($conteos[$e] ?? 0) && $estatusActivo !== $e)
                    <a class="nt-pestana {{ $estatusActivo === $e ? 'is-activa' : '' }}" href="{{ $url(['estatus' => $e]) }}">
                        {{ $e }} <span class="nt-pestana__num">{{ number_format($conteos[$e] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                @if ($notificaciones->isEmpty())
                    <div class="nt-vacio">
                        <i class="bi bi-inbox" aria-hidden="true"></i>
                        No hay notificaciones con estos filtros.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover nt-tabla" id="ntTabla">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Citado</th>
                                    <th>Dirección</th>
                                    <th>Estatus</th>
                                    <th>Notificador</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notificaciones as $n)
                                    @php
                                        $citado = trim(preg_replace('/\s+/', ' ', "{$n->nombre} {$n->primer_apellido} {$n->segundo_apellido}"));
                                        $docs = $verDocs ? $documentos($n) : [];
                                        $opciones = ($notificadoresPorSede->get($n->delegacion) ?? collect())
                                            ->map(fn ($o) => ['id' => $o->id, 'name' => $o->name])->values();
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="nt-nue">{{ $n->NUE ?: 'Sin NUE' }}</span>
                                            <span class="nt-sub">
                                                <span class="nt-tipo {{ $n->tipo_notificacion === 'Multa' ? 'nt-tipo--multa' : '' }}">{{ $n->tipo_notificacion ?: '—' }}</span>
                                                · {{ $n->delegacion }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nt-citado" title="{{ $citado }}">{{ $citado ?: 'Sin nombre' }}</span>
                                            @if ($n->notificacion)<span class="nt-sub">Medio: {{ $n->notificacion }}</span>@endif
                                        </td>
                                        <td>@include('notificaciones._direccion', ['n' => $n])</td>
                                        <td>
                                            @include('notificaciones._estatus', ['estatus' => $n->estatus])
                                            @if ($n->fecha && ! in_array($n->estatus, ['Sin asignar', 'Pendiente'], true))
                                                <span class="nt-sub mt-1" title="Fecha de la diligencia">{{ \Illuminate\Support\Carbon::parse($n->fecha)->format('d/m/Y H:i') }}</span>
                                            @elseif ($n->updated_at)
                                                <span class="nt-sub mt-1">{{ \Illuminate\Support\Carbon::parse($n->updated_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($n->estatus === 'Notificada en Audiencia')
                                                <span class="nt-sub">En audiencia</span>
                                            @else
                                                {{ $n->notificador_nombre ?? 'Sin asignar' }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="nt-acciones">
                                                <form method="POST" action="{{ route('editar_citado_historial') }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="id_solicitud" value="{{ $n->id_solicitud }}">
                                                    <input type="hidden" name="id" value="{{ $n->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i> Editar</button>
                                                </form>
                                                @if ($puedeReasignar && $n->estatus !== 'Notificada en Audiencia' && ! empty($n->id_notificador) && $opciones->isNotEmpty())
                                                    <button type="button" class="btn btn-sm btn-outline-secondary nt-reasignar"
                                                            data-bs-toggle="modal" data-bs-target="#asignarNotificadorModal"
                                                            data-id="{{ $n->id }}" data-nue="{{ $n->NUE }}"
                                                            data-notificador="{{ $n->id_notificador }}"
                                                            data-opciones="{{ json_encode($opciones, JSON_UNESCAPED_UNICODE) }}">
                                                        <i class="bi bi-arrow-left-right"></i> Reasignar
                                                    </button>
                                                @endif
                                                @if ($docs)
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-file-earmark-text"></i> Documentos
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @foreach ($docs as $etiqueta => $href)
                                                                <li><a class="dropdown-item" href="{{ $href }}" target="_blank" rel="noopener">{{ $etiqueta }}</a></li>
                                                            @endforeach
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

                    <div class="nt-pie">
                        <span class="nt-por">
                            Mostrando {{ number_format($notificaciones->firstItem()) }}–{{ number_format($notificaciones->lastItem()) }} de {{ number_format($notificaciones->total()) }}
                            ·
                            <select class="form-select form-select-sm" id="ntPorPagina" aria-label="Filas por página">
                                @foreach ($porPagina as $p)
                                    <option value="{{ $p }}" @selected($notificaciones->perPage() === $p)>{{ $p }} por página</option>
                                @endforeach
                            </select>
                        </span>
                        @include('turnos._paginacion', ['pagina' => $notificaciones])
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($puedeReasignar)
        <div class="modal fade" id="asignarNotificadorModal" tabindex="-1" aria-labelledby="asignarNotificadorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('asignar_notificador_busqueda') }}">
                    @csrf
                    <input type="hidden" name="id" id="asignarNotificador_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="asignarNotificadorModalLabel">Reasignar notificador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2" id="asignarNotificador_nue" style="color: var(--tn-suave);"></p>
                        <label class="form-label" for="asignarNotificador_select">Notificador</label>
                        <select class="form-select" name="id_notificador" id="asignarNotificador_select" required>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn tn-boton-dorado">Reasignar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
(function () {
    'use strict';
    var filtros = document.getElementById('ntFiltros');
    document.querySelectorAll('#ntFiltros .nt-auto').forEach(function (s) {
        s.addEventListener('change', function () { filtros.submit(); });
    });
    var por = document.getElementById('ntPorPagina');
    if (por) por.addEventListener('change', function () {
        var u = new URL(location.href);
        u.searchParams.set('por', this.value);
        u.searchParams.delete('page');
        location.href = u.toString();
    });

    document.querySelectorAll('#ntTabla [data-bs-toggle="dropdown"]').forEach(function (b) {
        bootstrap.Dropdown.getOrCreateInstance(b, {
            popperConfig: function (c) { return Object.assign({}, c, { strategy: 'fixed' }); }
        });
    });

    var modal = document.getElementById('asignarNotificadorModal');
    if (modal) modal.addEventListener('show.bs.modal', function (e) {
        var b = e.relatedTarget;
        var actual = b.getAttribute('data-notificador');
        var opciones = [];
        try { opciones = JSON.parse(b.getAttribute('data-opciones')) || []; } catch (err) {}
        document.getElementById('asignarNotificador_id').value = b.getAttribute('data-id');
        document.getElementById('asignarNotificador_nue').textContent = 'Expediente ' + (b.getAttribute('data-nue') || '');
        var sel = document.getElementById('asignarNotificador_select');
        while (sel.options.length > 1) sel.remove(1);
        opciones.forEach(function (o) {
            var op = new Option(o.name, o.id, false, String(o.id) === String(actual));
            sel.add(op);
        });
    });
})();
</script>
@endsection
