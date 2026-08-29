<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si Concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    
    <!-- Fonts & Icons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- CSS Local / Assets -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/realtime.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    @livewireStyles
    @yield('page_css')

    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat rgb(249,249,249);
            opacity: .8;
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            
            <!-- Navbar Superior -->
            <nav class="navbar navbar-expand-lg main-navbar" style="background-color: #496163">
                <form class="form-inline mr-auto" action="#">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                </form>
                <ul class="navbar-nav navbar-right">
                    @if(\Illuminate\Support\Facades\Auth::user())
                        <li class="dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <img alt="image" src="{{ asset('assets/images/ccl-r.png') }}" class="rounded-circle mr-1 thumbnail-rounded user-thumbnail">
                                <div class="d-sm-none d-lg-inline-block">Hola, {{ \Illuminate\Support\Facades\Auth::user()->name }}</div>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="{{ route('password_cambiar') }}" class="dropdown-item has-icon text-success">
                                    <i class="bi bi-pass"></i> Cambiar contraseña
                                </a>
                                <a href="{{ url('logout') }}" class="dropdown-item has-icon text-danger" onclick="event.preventDefault(); localStorage.clear(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-door-open"></i> Salir
                                </a>
                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endif
                </ul>
            </nav>
            
            <!-- Sidebar / Menú Lateral -->
            <div class="main-sidebar main-sidebar-postion">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <img class="navbar-brand-full app-header-logo" src="{{ asset('assets_seer/images/logos.png') }}" width="65" alt="Logo">
                        <a href="{{ url('/') }}"></a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ url('/') }}" class="small-sidebar-text">
                            <img class="navbar-brand-full" src="{{ asset('assets_seer/images/logos.png') }}" width="45px" alt="Logo Pequeño"/>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        @include('layouts.menu')
                    </ul>
                </aside>
            </div>

            <!-- Contenido Principal -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h3 class="page__heading">Sistema integral para la Conciliación</h3>
                    </div>
                    <div class="section-body">
                        @if(View::hasSection('content'))
                            @yield('content')
                        @else
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('assets_seer/images/ccl.png') }}" alt="Logo Bienvenida" style="max-width: 50%; height: auto; margin: 0 auto;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
            
            <footer class="main-footer">
                @include('layouts.footer')
            </footer>
        </div>
    </div>

    <div id="menu_carga" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Modales -->
    @stack('modals')

    <!-- Scripts Base -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

    <!-- Plugins -->
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nicescroll.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <!-- Template JS con Rutas Corregidas -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/general/menu.js') }}"></script>

    <!-- Livewire Scripts -->
    @livewireScripts

    @yield('page_js')
    @yield('scripts')
    @stack('scripts')

    <script>
        let loggedInUser = @json(\Illuminate\Support\Facades\Auth::user());
        let loginUrl = '{{ route('login') }}';
        const userUrl = '{{ url('users') }}';
        
        (function ($) {
            $.fn.button = function (action) {
                if (action === 'loading' && this.data('loading-text')) {
                    this.data('original-text', this.html()).html(this.data('loading-text')).prop('disabled', true);
                }
                if (action === 'reset' && this.data('original-text')) {
                    this.html(this.data('original-text')).prop('disabled', false);
                }
            };
        }(jQuery));
    </script>
</body>
</html>