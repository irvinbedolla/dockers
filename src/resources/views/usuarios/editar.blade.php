@extends('layouts.app')
@section('title', 'Editar usuario')

{{--
    Edición de personal. Guarda UsuarioController@update. Comparte campos con
    el alta (usuarios._formulario). La contraseña es opcional aquí.
--}}

@php
    use App\Support\FechaLegible;
    use Illuminate\Support\Str;

    $bonito = Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $user->name))));
    $partes = preg_split('/\s+/', trim((string) $user->name));
    $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
@endphp

@section('page_css')
    @include('turnos._estilos')
    @include('usuarios._estilos_form')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <div class="w-100">
                <a href="{{ route('usuarios') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Usuarios
                </a>
                <div class="uf-cabeza mt-1">
                    @if ($user->tieneFotoDePerfil())
                        <img class="uf-cabeza__foto" src="{{ $user->avatar_url }}" alt="" width="56" height="56">
                    @else
                        <span class="uf-cabeza__foto uf-cabeza__letras" aria-hidden="true">{{ $iniciales }}</span>
                    @endif
                    <div>
                        <h3 class="page__heading mb-0">{{ $bonito }} @if ($soyYo)<small class="text-muted" style="font-size: 14px;">(tú)</small>@endif</h3>
                        <div class="uf-meta">
                            <span><strong>Estado:</strong> {{ $user->estatus }}</span>
                            <span><strong>Último acceso:</strong> {{ FechaLegible::conHora($user->last_login_at, 'Nunca ha entrado') }}</span>
                            @if ($user->created_at)
                                <span><strong>Alta:</strong> {{ $user->created_at->locale('es')->isoFormat('D MMM YYYY') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
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
                  action="{{ route('usuarios.update', $user->id) }}" enctype="multipart/form-data" autocomplete="off">
                @csrf
                @method('PATCH')

                @include('usuarios._formulario')

                <div class="uf-pie">
                    <a href="{{ route('usuarios') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-check2 me-1"></i> Guardar cambios</button>
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
