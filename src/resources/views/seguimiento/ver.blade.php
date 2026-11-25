@extends('layouts.app')
@section('title', 'Seguimiento · '.$caso['persona'])

{{--
    La historia de un caso con fecha y día, desde su cita hasta en qué quedó
    (App\Support\SeguimientoCaso::linea), con los documentos de cada paso,
    quién lo atendió y con quién le toca audiencia. Sólo lectura; la
    solicitud se ve en un modal (solicitudes/_vista).
--}}

@php
    $regreso = url()->previous();
    if (! str_contains($regreso, '/seguimiento') || str_contains($regreso, '/seguimiento/')) {
        $regreso = route('seguimiento');
    }
@endphp

@section('page_css')
    @include('turnos._estilos')
    @include('seguimiento._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ $regreso }}" class="text-decoration-none small sg-no-imprimir" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Todos los casos
                </a>
                <h3 class="page__heading mb-0">Seguimiento del caso</h3>
            </div>
            <div class="d-flex gap-2 sg-no-imprimir">
                @if ($caso['solicitud'])
                    <button type="button" class="btn tn-boton-dorado btn-sm" data-bs-toggle="modal" data-bs-target="#sgSolicitud">
                        <i class="bi bi-eye me-1"></i> Ver solicitud
                    </button>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
            </div>
        </div>

        <div class="section-body">
            <div class="tn-tarjeta">
                @include('seguimiento._caso', ['caso' => $caso])
            </div>
        </div>
    </section>

    @if ($caso['solicitud'])
        @push('modals')
            <div class="modal fade" id="sgSolicitud" tabindex="-1" aria-labelledby="sgSolicitudTitulo" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content tn" style="background: #F5F7F7;">
                        <div class="modal-header" style="background:#fff;">
                            <h5 class="modal-title" id="sgSolicitudTitulo">
                                Solicitud {{ $caso['nue'] ?: '' }}
                                <span class="sg-evento__etapa" style="font-size:11px;"><i class="bi bi-eye"></i> Sólo consulta</span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body" id="sgSolicitudCuerpo">
                            <div class="text-center py-5" style="color:#8A9899;"><div class="spinner-border spinner-border-sm me-2"></div> Cargando la solicitud…</div>
                        </div>
                    </div>
                </div>
            </div>
        @endpush
    @endif
@endsection

@section('scripts')
    @if ($caso['solicitud'])
        <script>
        (function () {
            var modal = document.getElementById('sgSolicitud');
            if (!modal) { return; }
            var cargada = false;
            modal.addEventListener('show.bs.modal', function () {
                if (cargada) { return; }
                fetch(@json(route('seguimiento.solicitud', $caso['solicitud'])), { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
                    .then(function (html) { document.getElementById('sgSolicitudCuerpo').innerHTML = html; cargada = true; })
                    .catch(function () {
                        document.getElementById('sgSolicitudCuerpo').innerHTML = '<div class="alert alert-warning mb-0">No se pudo cargar la solicitud. Intenta de nuevo.</div>';
                    });
            });
        })();
        </script>
    @endif
@endsection
