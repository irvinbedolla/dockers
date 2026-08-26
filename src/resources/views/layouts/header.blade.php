<form class="form-inline me-auto mb-0" action="#">
    <ul class="navbar-nav me-3 mb-0 d-flex align-items-center">
        <li class="nav-item">
            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg d-flex align-items-center text-white">
                <i class="bi bi-bricks"></i>
            </a>
        </li>
    </ul>
</form>

<ul class="navbar-nav navbar-right ms-auto mb-0 d-flex align-items-center">
    @if(\Illuminate\Support\Facades\Auth::user())
        <li class="dropdown nav-item">
            <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center text-white">
                <img alt="image" src="{{ asset('assets/images/ccl-r.png') }}" class="rounded-circle me-2 user-thumbnail">
                <span class="d-none d-lg-inline-block">
                    Hola, {{\Illuminate\Support\Facades\Auth::user()->name}}
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