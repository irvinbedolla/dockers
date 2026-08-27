            <aside id="sidebar-wrapper">
                <div class="sidebar-brand">
                    {{-- El <a> estaba vacío: el logo no era clickeable, y el alt venía
                         del boilerplate ("Infyom Logo"). --}}
                    <a href="{{ url('/') }}">
                        <img class="navbar-brand-full app-header-logo" src="{{ asset('assets/images/ccl-r.png') }}"
                            alt="SiConcilio - Centro de Conciliación Laboral">
                    </a>
                </div>
                <div class="sidebar-brand sidebar-brand-sm">
                    <a href="{{ url('/') }}" class="small-sidebar-text">
                        <img class="navbar-brand-full" src="{{ asset('assets/images/ccl-r.png') }}"
                            alt="SiConcilio"/>
                    </a>
                </div>
                <ul class="sidebar-menu">
                    @include('layouts.menu')
                </ul>
            </aside>
