<form class="form-inline me-auto mb-0" action="#">
    <ul class="navbar-nav me-3 mb-0 d-flex align-items-center">
        <li class="nav-item">
            {{-- Botón de menú: tres rayas dibujadas con <span>, no un glifo de
                 icono, para poder animarlas al abrir y cerrar. --}}
            <a href="#" data-toggle="sidebar" role="button" class="nav-link nav-link-lg boton-menu"
               aria-label="Mostrar u ocultar el menú" aria-expanded="true" aria-controls="sidebar-wrapper">
                <span class="boton-menu__cajon" aria-hidden="true">
                    <span class="boton-menu__raya"></span>
                    <span class="boton-menu__raya"></span>
                    <span class="boton-menu__raya"></span>
                </span>
            </a>
        </li>
    </ul>
</form>

<ul class="navbar-nav navbar-right ms-auto mb-0 d-flex align-items-center">
    @if(\Illuminate\Support\Facades\Auth::user())
        @php
            $usuarioBarra = \Illuminate\Support\Facades\Auth::user();
            // users.name viene en mayúsculas y con espacios de sobra.
            $nombreBarra  = \Illuminate\Support\Str::title(preg_replace('/\s+/', ' ', trim($usuarioBarra->name)));
            $rolBarra     = $usuarioBarra->getRoleNames()->first();
        @endphp

        <li class="dropdown nav-item">
            <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center text-white">
                {{-- La máscara es el <span>: la foto que el usuario suba, con
                     cualquier proporción, se recorta al círculo por object-fit. --}}
                <span class="avatar-usuario me-2">
                    <img src="{{ $usuarioBarra->avatar_url }}" alt="Foto de {{ $nombreBarra }}">
                </span>
                <span class="usuario-barra d-none d-lg-flex">
                    <span class="usuario-barra__nombre">{{ $nombreBarra }}</span>
                    @if ($rolBarra)
                        <span class="usuario-barra__rol">{{ $rolBarra }}</span>
                    @endif
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                <a href="{{ route('password_cambiar' ) }}" class="dropdown-item d-flex align-items-center">
                    <i class="bi bi-pass me-2 text-success"></i> Cambiar contraseña
                </a>
                <div class="dropdown-divider"></div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                        <i class="bi bi-door-open me-2"></i> Salir
                    </button>
                </form>
            </div>
        </li>
    @endif
</ul>