@extends('layouts.app')
@section('title', 'Mis notificaciones')

{{-- Notificaciones que el notificador ya diligenció (NotificacionesController::misNotificaciones). --}}

@php
    $url = fn (array $cambios) => route('Historial_Notificacador', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $estatusActivo = $filtros['estatus'] ?? null;
    $usuario = auth()->user();
    $verDocs = $usuario->can('notificaciones_ver_documento');
    $subir = $usuario->can('notificaciones_subir_documento');

    $documentos = function ($n) {
        $r = fn ($nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre, [$n->id_citado, $n->id_solicitud]) : null;
        $cita = $n->tipo_notificacion === 'Citatorio';
        $multa = $n->tipo_notificacion === 'Multa';
        $docs = match ($n->estatus) {
            'Finalizado exitosamente'                  => ['Notificación' => $cita ? $r('PDFRazonNoticacion') : null, 'Multa' => $multa ? $r('PDFmultaNotificacion') : null],
            'No notificada', 'Exitosa por Instructivo' => ['Notificación' => $cita ? $r('PDFInstructivo') : null, 'Multa' => $multa ? $r('VerPDFMultaInstructivo') : null],
            'No exitosa se constituye'                 => ['Notificación' => $cita ? $r('VerPDFNoExitConstituye') : null, 'Multa' => $multa ? $r('VerPDFMultaNoExitConstituye') : null],
            'No exitosa no se constituye'              => ['Notificación' => $cita ? $r('PDFnotificadoNoexitosaNS') : null],
            default                                    => null,
        };
        if ($docs === null) {
            return null;
        }
        return ['Identificaciones' => signedDocRoute('VerDocumentosAudiencia', $n->id_solicitud)] + array_filter($docs);
    };
@endphp

@section('page_css')
    @include('notificaciones._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('index_notificaciones') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Notificaciones
                </a>
                <h3 class="page__heading mb-0">Mis notificaciones</h3>
            </div>
            <a href="{{ route('seer') }}" class="btn tn-boton-dorado"><i class="bi bi-signpost-split me-1"></i> Ir a por notificar</a>
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

            <form method="GET" action="{{ route('Historial_Notificacador') }}" class="tn-tarjeta">
                @if ($estatusActivo)<input type="hidden" name="estatus" value="{{ $estatusActivo }}">@endif
                @if (! empty($filtros['por']))<input type="hidden" name="por" value="{{ $filtros['por'] }}">@endif
                <div class="nt-filtros" style="grid-template-columns: minmax(220px, 1fr) auto;">
                    <div>
                        <label for="ntBuscar">Buscar</label>
                        <input type="search" id="ntBuscar" name="buscar" class="form-control" maxlength="100"
                               placeholder="Expediente, solicitante, citado, colonia o calle" value="{{ $filtros['buscar'] ?? '' }}">
                    </div>
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-search me-1"></i> Buscar</button>
                        @if (array_filter(request()->except(['page', 'por'])))
                            <a href="{{ route('Historial_Notificacador') }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="nt-pestanas">
                <a class="nt-pestana {{ $estatusActivo ? '' : 'is-activa' }}" href="{{ $url(['estatus' => null]) }}">
                    Todas <span class="nt-pestana__num">{{ number_format($conteos->sum()) }}</span>
                </a>
                @foreach (\App\Http\Controllers\NotificacionesController::ESTATUS as $e)
                    @continue(! ($conteos[$e] ?? 0) && $estatusActivo !== $e)
                    <a class="nt-pestana {{ $estatusActivo === $e ? 'is-activa' : '' }}" href="{{ $url(['estatus' => $e]) }}">
                        {{ $e }} <span class="nt-pestana__num">{{ number_format($conteos[$e] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                @if ($mis_notificaciones->isEmpty())
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
                                    <th>Solicitante</th>
                                    <th>Citado</th>
                                    <th>Dirección</th>
                                    <th>Estatus</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mis_notificaciones as $n)
                                    @php
                                        $citado = trim(preg_replace('/\s+/', ' ', "{$n->nombre} {$n->primer_apellido} {$n->segundo_apellido}"));
                                        $docs = $verDocs ? $documentos($n) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="nt-nue">{{ $n->NUE ?: 'Sin NUE' }}</span>
                                            <span class="nt-sub"><span class="nt-tipo {{ $n->tipo_notificacion === 'Multa' ? 'nt-tipo--multa' : '' }}">{{ $n->tipo_notificacion ?: '—' }}</span></span>
                                        </td>
                                        <td>{{ $n->nombre_solicitado ?: '—' }}</td>
                                        <td><span class="nt-citado" title="{{ $citado }}">{{ $citado ?: 'Sin nombre' }}</span></td>
                                        <td>@include('notificaciones._direccion', ['n' => $n])</td>
                                        <td>
                                            @include('notificaciones._estatus', ['estatus' => $n->estatus])
                                            @if ($n->updated_at)<span class="nt-sub mt-1">{{ \Illuminate\Support\Carbon::parse($n->updated_at)->format('d/m/Y') }}</span>@endif
                                        </td>
                                        <td>
                                            <div class="nt-acciones">
                                                @if ($n->estatus === 'Sin asignar')
                                                    @can('notificaciones_ver_notificacion')
                                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('editar_citado', $n->id_citado) }}"><i class="bi bi-pencil-square"></i> Editar</a>
                                                    @endcan
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
                                                            @if ($subir)
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $n->id_solicitud }}" data-nue="{{ $n->NUE }}">
                                                                        <i class="bi bi-upload me-1"></i> Subir documento
                                                                    </button>
                                                                </li>
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

                    <div class="nt-pie">
                        <span class="nt-por">
                            Mostrando {{ number_format($mis_notificaciones->firstItem()) }}–{{ number_format($mis_notificaciones->lastItem()) }} de {{ number_format($mis_notificaciones->total()) }}
                            ·
                            <select class="form-select form-select-sm" id="ntPorPagina" aria-label="Filas por página">
                                @foreach ($porPagina as $p)
                                    <option value="{{ $p }}" @selected($mis_notificaciones->perPage() === $p)>{{ $p }} por página</option>
                                @endforeach
                            </select>
                        </span>
                        @include('turnos._paginacion', ['pagina' => $mis_notificaciones])
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($subir)
        <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="expedienteTitulo" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content needs-validation" novalidate method="POST" action="{{ route('subir_expediente') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="audiencia_id" id="expediente_audiencia_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="expedienteTitulo">Subir documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3" id="expediente_nue" style="color: var(--tn-suave);"></p>
                        <div class="mb-3">
                            <label class="form-label" for="expediente_archivo">Documento en PDF</label>
                            <input type="file" name="documentoExpediente" id="expediente_archivo" class="form-control" accept=".pdf" required>
                            <div class="invalid-feedback">El documento es obligatorio.</div>
                        </div>
                        <div>
                            <label class="form-label" for="expediente_nombre">Nombre del documento <span class="text-danger">*</span></label>
                            <input type="text" name="nombreExpediente" id="expediente_nombre" class="form-control" required>
                            <div class="invalid-feedback">El nombre es obligatorio.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn tn-boton-dorado">Subir</button>
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

    var modal = document.getElementById('expediente');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            document.getElementById('expediente_audiencia_id').value = e.relatedTarget.getAttribute('data-id');
            document.getElementById('expediente_nue').textContent = 'Expediente ' + (e.relatedTarget.getAttribute('data-nue') || '');
        });
        modal.querySelector('form').addEventListener('submit', function (ev) {
            if (!this.checkValidity()) { ev.preventDefault(); ev.stopPropagation(); }
            this.classList.add('was-validated');
        });
    }
})();
</script>
@endsection
