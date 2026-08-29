@extends('layouts.auth_app')

@section('title', 'Inicio de Sesión')

@section('content')
    <h1 class="acceso__titulo">Inicio de Sesión</h1>

    {{-- Un solo aviso para todo lo que pueda salir mal. Antes se listaban los
         errores crudos de Laravel en una lista con viñetas. --}}
    @if ($errors->any())
        <div class="acceso__aviso" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <span>
                <b>No pudimos iniciar tu sesión</b>
                {{ $errors->first() }}
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" id="formLogin" novalidate>
        @csrf

        <div class="campo">
            <i class="bi bi-person"></i>
            <input type="email" id="email" name="email" placeholder="Usuario"
                   value="{{ old('email', Cookie::get('email')) }}"
                   autocomplete="username" inputmode="email"
                   tabindex="1" autofocus required>
        </div>

        <div class="campo">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" placeholder="Contraseña"
                   class="con-ojo" autocomplete="current-password" tabindex="2" required>
            <button type="button" class="ojo" id="verContrasena"
                    aria-label="Mostrar contraseña" aria-pressed="false" tabindex="3">
                <i class="bi bi-eye" id="iconoOjo"></i>
            </button>
        </div>

        <button type="submit" class="acceso__btn" id="botonLogin" tabindex="4">Ingresar</button>
    </form>
@endsection

@push('body_end')
    <div id="login_div" style="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
@endpush

@section('scripts')
    <script>
        (function () {
            // Ojito de ver la contraseña.
            var boton = document.getElementById('verContrasena');
            var campo = document.getElementById('password');
            var icono = document.getElementById('iconoOjo');

            boton.addEventListener('click', function () {
                var visible = campo.type === 'text';
                campo.type = visible ? 'password' : 'text';
                icono.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
                boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                boton.setAttribute('aria-pressed', visible ? 'false' : 'true');
                campo.focus();
            });

            // Overlay de carga al enviar. Antes esto buscaba un id que no existía
            // y reventaba en consola, así que nunca se mostraba.
            document.getElementById('formLogin').addEventListener('submit', function () {
                document.getElementById('botonLogin').disabled = true;
                document.getElementById('login_div').style.display = 'block';
            });
        })();
    </script>
@endsection
