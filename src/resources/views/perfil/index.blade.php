@extends('layouts.app')
@section('title', 'Mi perfil')

{{--
    Mi perfil. Dos tarjetas, dos formularios independientes: subir una foto y
    cambiar la contraseña son cosas distintas y no tienen por qué viajar
    juntas en el mismo envío.

    Nombre, correo y rol van de solo lectura. No es sólo que no haya campo: el
    controlador lee del request únicamente lo suyo, así que mandar 'email' a
    mano tampoco hace nada.
--}}

@php
    $nombre = \Illuminate\Support\Str::title(preg_replace('/\s+/', ' ', trim($usuario->name)));

    // Mismos nombres de rol que muestra la barra superior, para que la persona
    // no lea aquí una etiqueta distinta de la que ve arriba todo el día.
    $rolesMostrados = [
        'Enlace'      => 'Enlace Administrativo',
        'Estadistica' => 'Enlace Técnico',
        'Turnos'      => 'Recepcion',
    ];
    $rol = $usuario->getRoleNames()->first();
    $rol = $rolesMostrados[$rol] ?? $rol;

    $minimo = \App\Http\Controllers\PerfilController::MINIMO_CONTRASENA;
@endphp

@section('page_css')
    <style>
        .perfil-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 18px;
            align-items: start;
        }

        .perfil-tarjeta {
            background: #fff;
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            padding: 22px 24px;
        }

        .perfil-tarjeta__titulo {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #7B8A8B;
            margin: 0 0 16px;
        }

        /* Los datos que no se tocan: se ven como información, no como campos
           deshabilitados. Un input en gris invita a intentar escribir en él. */
        .perfil-dato + .perfil-dato { margin-top: 12px; }
        .perfil-dato__etiqueta {
            display: block;
            font-size: 11.5px;
            color: #8A9899;
            margin-bottom: 1px;
        }
        .perfil-dato__valor {
            display: block;
            font-size: 14px;
            color: #2E3C3D;
            font-weight: 600;
            word-break: break-word;
        }

        .perfil-foto {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            flex: 0 0 auto;
        }

        /* El botón del ojo se monta encima del input, así que el texto
           necesita espacio a la derecha para no quedar debajo. */
        .campo-clave { position: relative; }
        .campo-clave input { padding-right: 44px; }
        .campo-clave__ojo {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 42px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            color: #7B8A8B;
            cursor: pointer;
            padding: 0;
        }
        .campo-clave__ojo:hover { color: #354647; }
        .campo-clave__ojo:focus-visible { outline: 2px solid #CEA845; outline-offset: -2px; }

        .perfil-reglas { list-style: none; margin: 10px 0 0; padding: 0; font-size: 12px; }
        .perfil-regla {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #8A9899;
            padding: 2px 0;
        }
        .perfil-regla__marca {
            flex: 0 0 auto;
            width: 15px;
            text-align: center;
            font-weight: 700;
        }
        .perfil-regla.esta-ok { color: #1B5E3F; }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Mi perfil</h3>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="perfil-grid">

                {{-- ---------------------------------------------- Mi cuenta --}}
                <div class="perfil-tarjeta">
                    <h3 class="perfil-tarjeta__titulo">Mi cuenta</h3>

                    <form method="POST" action="{{ route('perfil.foto') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                            <img id="perfil_previa" class="perfil-foto"
                                 src="{{ $usuario->avatar_url }}"
                                 alt="{{ $usuario->tieneFotoDePerfil() ? 'Tu foto de perfil' : 'Todavía no tienes foto de perfil' }}">

                            <div class="flex-grow-1" style="min-width: 220px;">
                                <div class="perfil-dato">
                                    <span class="perfil-dato__etiqueta">Nombre</span>
                                    <span class="perfil-dato__valor">{{ $nombre }}</span>
                                </div>
                                <div class="perfil-dato">
                                    <span class="perfil-dato__etiqueta">Correo</span>
                                    <span class="perfil-dato__valor">{{ $usuario->email }}</span>
                                </div>
                                <div class="perfil-dato">
                                    <span class="perfil-dato__etiqueta">Rol</span>
                                    <span class="perfil-dato__valor">{{ $rol ?? 'Sin rol asignado' }}</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="font-size: 12px;">
                            Nombre, correo y rol los cambia un Super Usuario desde Administración.
                        </p>

                        <hr>

                        <div class="form-group">
                            <label for="foto_perfil" class="form-label">Cambiar mi foto</label>
                            <input type="file" name="foto_perfil" id="foto_perfil"
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">
                                JPG, PNG o WebP, mínimo 200&times;200 píxeles y hasta 8 MB.
                                Se recorta en cuadro y se reduce sola.
                            </small>
                            <div id="foto_aviso" class="text-danger small mt-1" hidden></div>

                            @if ($usuario->tieneFotoDePerfil())
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox"
                                           name="quitar_foto" value="1" id="quitar_foto">
                                    <label class="form-check-label" for="quitar_foto">
                                        Quitar mi foto actual
                                    </label>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary mt-2" style="background-color: #496163; border-color: #496163;">
                            Guardar foto
                        </button>
                    </form>
                </div>

                {{-- -------------------------------------------- Contraseña --}}
                <div class="perfil-tarjeta">
                    <h3 class="perfil-tarjeta__titulo">Cambiar mi contraseña</h3>

                    <form method="POST" action="{{ route('perfil.contrasena') }}" id="form_contrasena">
                        @csrf
                        @method('PATCH')

                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <div class="campo-clave">
                                <input type="password" name="password" id="password"
                                       class="form-control"
                                       placeholder="Al menos {{ $minimo }} caracteres"
                                       autocomplete="new-password"
                                       minlength="{{ $minimo }}" required>
                                <button type="button" class="campo-clave__ojo" data-ojo="password"
                                        aria-label="Mostrar la contraseña" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="password_confirmation" class="form-label">Confirma la contraseña</label>
                            <div class="campo-clave">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control"
                                       placeholder="Escríbela otra vez"
                                       autocomplete="new-password"
                                       minlength="{{ $minimo }}" required>
                                <button type="button" class="campo-clave__ojo" data-ojo="password_confirmation"
                                        aria-label="Mostrar la contraseña" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Las reglas se enseñan desde el principio y se van
                             palomeando: nadie tiene que adivinar qué falta ni
                             enterarse hasta que el servidor lo rechaza. --}}
                        <ul class="perfil-reglas" id="perfil_reglas" aria-live="polite">
                            <li class="perfil-regla" data-regla="largo">
                                <span class="perfil-regla__marca" aria-hidden="true">○</span>
                                Al menos {{ $minimo }} caracteres
                            </li>
                            <li class="perfil-regla" data-regla="igual">
                                <span class="perfil-regla__marca" aria-hidden="true">○</span>
                                Las dos contraseñas coinciden
                            </li>
                        </ul>

                        <button type="submit" class="btn btn-primary mt-3" style="background-color: #496163; border-color: #496163;">
                            Guardar contraseña
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
(function () {
    var MINIMO = {{ $minimo }};

    // ---- Ojitos: mostrar y ocultar ----
    document.querySelectorAll('.campo-clave__ojo').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var campo = document.getElementById(boton.dataset.ojo);
            if (!campo) { return; }

            var mostrando = campo.type === 'text';
            campo.type = mostrando ? 'password' : 'text';

            boton.setAttribute('aria-pressed', mostrando ? 'false' : 'true');
            boton.setAttribute('aria-label', mostrando ? 'Mostrar la contraseña' : 'Ocultar la contraseña');

            var icono = boton.querySelector('i');
            if (icono) { icono.className = mostrando ? 'bi bi-eye' : 'bi bi-eye-slash'; }

            // El foco se queda en el campo y el cursor al final, para poder
            // seguir escribiendo sin volver a hacer clic.
            campo.focus();
            var fin = campo.value.length;
            try { campo.setSelectionRange(fin, fin); } catch (e) {}
        });
    });

    // ---- Reglas en vivo ----
    var clave   = document.getElementById('password');
    var repite  = document.getElementById('password_confirmation');
    var reglas  = document.getElementById('perfil_reglas');
    var form    = document.getElementById('form_contrasena');

    if (!clave || !repite || !reglas || !form) { return; }

    function marcar(nombre, ok) {
        var li = reglas.querySelector('[data-regla="' + nombre + '"]');
        if (!li) { return; }
        li.classList.toggle('esta-ok', ok);
        li.querySelector('.perfil-regla__marca').textContent = ok ? '✓' : '○';
    }

    function revisar() {
        var largo = clave.value.length >= MINIMO;
        // Si la confirmación está vacía todavía no es un error: la persona
        // apenas va escribiendo la primera.
        var igual = repite.value.length > 0 && clave.value === repite.value;

        marcar('largo', largo);
        marcar('igual', igual);

        repite.setCustomValidity(
            repite.value.length > 0 && clave.value !== repite.value
                ? 'Las dos contraseñas no coinciden.'
                : ''
        );

        return largo && igual;
    }

    clave.addEventListener('input', revisar);
    repite.addEventListener('input', revisar);

    form.addEventListener('submit', function (evento) {
        if (!revisar()) {
            evento.preventDefault();
            // Se reporta con el propio navegador para no inventar un diálogo:
            // el mensaje sale pegado al campo que falta.
            form.reportValidity();
        }
    });

    revisar();
})();
</script>
@endsection
