<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ url('/') }}">
            <img class="navbar-brand-full app-header-logo" src="{{ asset('assets/images/ccl-r.png') }}"
                 alt="Centro de Conciliación Laboral">
        </a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="{{ url('/') }}" class="small-sidebar-text">
            <img class="navbar-brand-full" src="{{ asset('assets/images/ccl-r.png') }}"
                 alt="Centro de Conciliación Laboral"/>
        </a>
    </div>
    <nav aria-label="Menú principal">
        <ul class="sidebar-menu">
            @include('layouts.menu')
        </ul>
    </nav>
</aside>