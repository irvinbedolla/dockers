@extends('layouts.app')
@section('title', 'Nuevo turno')

{{--
    Turno de ventanilla. Usa el mismo formulario de 4 pasos que /generar-cita
    (turnos._cita_*), en modo "ventanilla": textos para quien captura y los
    campos que espera RecepcionController@turnos_guardar.
--}}

@section('page_css')
    @include('turnos._cita_estilos')
    <style>
        .nt-tarjeta { max-width: 1100px; }
        .nt-tarjeta .card-body { padding: 24px; }
        @media (max-width: 576px) { .nt-tarjeta .card-body { padding: 16px; } }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header mb-3">
            <div>
                <a href="{{ route('turnos') }}" class="text-decoration-none small" style="color: #829A9C;">
                    <i class="bi bi-arrow-left"></i> Turnos
                </a>
                <h3 class="page__heading mb-0">Nuevo turno</h3>
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <strong>Turno registrado.</strong> {{ session('success') }}
                    @if (session('cita_id') && Route::has('citas.documento'))
                        <a class="btn btn-sm btn-success ms-2" href="{{ route('citas.documento', session('cita_id')) }}" target="_blank" rel="noopener">
                            <i class="bi bi-printer me-1"></i> Imprimir acuse
                        </a>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 nt-tarjeta">
                <div class="card-header card-header-guinda py-3">
                    <h4 class="m-0 fs-5 text-white"><i class="bi bi-calendar-plus me-2"></i>Agendar turno en ventanilla</h4>
                </div>
                <div class="card-body">
                    @include('turnos._cita_formulario', ['modo' => 'ventanilla', 'accion' => route('turnos_guardar_nuevo')])
                </div>
            </div>
        </div>
    </section>
@endsection

{{-- Los modales van fuera de .main-content: dentro, el contenedor del layout
     crea su propio contexto de apilado y el fondo oscuro del modal quedaba
     ENCIMA del modal (se veía apagado y no se podía tocar) y el menú lateral
     lo tapaba por la izquierda. El stack "modals" se pinta antes de los
     scripts, que buscan estos elementos al cargar. --}}
@push('modals')
    @include('turnos._cita_modales')
@endpush

@section('scripts')
    @include('turnos._cita_scripts', ['modo' => 'ventanilla'])
@endsection
