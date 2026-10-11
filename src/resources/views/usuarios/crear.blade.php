@extends('layouts.app')
@section('title', 'Nuevo usuario')

{{--
    Alta de personal. Guarda UsuarioController@store. La CURP va en la
    columna profile_photo_path (así la usan las cuentas de solicitantes).
    Los campos con error se marcan uno por uno y conservan lo escrito.
--}}

@section('page_css')
    @include('turnos._estilos')
    @include('usuarios._estilos_form')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <div>
                <a href="{{ route('usuarios') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Usuarios
                </a>
                <h3 class="page__heading mb-0">Nuevo usuario</h3>
            </div>
        </div>

        <div class="section-body uf">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Revisa los campos marcados.</strong>
                    <ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <form class="needs-validation" novalidate id="form_usuarios" method="POST"
                  action="{{ route('usuarios.store') }}" enctype="multipart/form-data" autocomplete="off">
                @csrf

                @include('usuarios._formulario')

                <div class="uf-pie">
                    <a href="{{ route('usuarios') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-person-plus me-1"></i> Crear cuenta</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_usuario" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}?v=2"></script>
@endsection
