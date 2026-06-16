<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 4.1.1 -->
    <link href="public/assets_seer/assets/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ionicons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/@fortawesome/fontawesome-free/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
            opacity: .8;
        }
</style>

    @livewireStyles


    @yield('page_css')
    <!-- Template CSS -->
    <link rel="icon" href="public/assets_seer/images/icono.png" type="image/x-icon">
    <link rel="stylesheet" href="public/web/css/style.css">
    <link rel="stylesheet" href="public/web/css/components.css">
    @yield('page_css')

    @yield('css')
</head>

<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        <nav class="navbar navbar-expand-lg main-navbar" style="background-color: #6A0F49">
            <form class="form-inline mr-auto" action="#">
                <ul class="navbar-nav mr-3">
                    <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                </ul>
            </form>
            <ul class="navbar-nav navbar-right">
                @if(\Illuminate\Support\Facades\Auth::user())
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown"
                        class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="../public/assets/images/ccl-r.png"
                                class="rounded-circle mr-1 thumbnail-rounded user-thumbnail ">
                            <div class="d-sm-none d-lg-inline-block">
                                Hola, {{\Illuminate\Support\Facades\Auth::user()->name}}</div>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{ route('password_cambiar' ) }}" class="dropdown-item has-icon text-susess">
                                <i class="bi bi-pass"></i>Cambiar contraseña
                            </a>
                            <a href="{{ url('logout') }}" class="dropdown-item has-icon text-danger" onclick="event.preventDefault(); localStorage.clear();  document.getElementById('logout-form').submit();">
                                <i class="bi bi-door-open"></i>Salir
                            </a>
                            <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @else
                    <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                        <div class="d-sm-none d-lg-inline-block">{{ __('messages.common.hello') }}</div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-title">{{ __('messages.common.login') }}
                                / {{ __('messages.common.register') }}</div>
                            <a href="{{ route('login') }}" class="dropdown-item has-icon">
                                <i class="fas fa-sign-in-alt"></i> {{ __('messages.common.login') }}
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('register') }}" class="dropdown-item has-icon">
                                <i class="fas fa-user-plus"></i> {{ __('messages.common.register') }}
                            </a>
                        </div>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="main-sidebar main-sidebar-postion">
            <aside id="sidebar-wrapper">
                <div class="sidebar-brand">
                    <img class="navbar-brand-full app-header-logo" src="public/assets_seer/images/logos.png" width="65"
                        alt="Infyom Logo">
                    <a href="{{ url('/') }}"></a>
                </div>
                <div class="sidebar-brand sidebar-brand-sm">
                    <a href="{{ url('/') }}" class="small-sidebar-text">
                        <img class="navbar-brand-full" src="public/assets_seer/images/logos.png" width="45px" alt=""/>
                    </a>
                </div>
                <ul class="sidebar-menu">
                    @include('layouts.menu')
                </ul>
            </aside>
        </div>
        <div class="main-content">
            <section class="section">
                <div class="section-header">
                    <h3 class="page__heading">Sistema integral para la Conciliación</h3>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <ul class="navbar-nav flex-grow-1 justify-content-center">
                                            <li class="nav-item text-center">
                                                <img src="public/assets_seer/images/ccl.png" alt="" style="max-width: 50%; height: auto;">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <footer class="main-footer">
        </footer>
    </div>
</div>



<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="public/assets/js/general/menu.js"></script>
@endsection


</body>


    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>

    <!-- Template JS File -->
    <script src="public/web/js/stisla.js"></script>
    <script src="public/web/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom/custom.js"></script>
@yield('page_js')
@yield('scripts')
<script>
    let loggedInUser =@json(\Illuminate\Support\Facades\Auth::user());
    let loginUrl = '{{ route('login') }}';
    const userUrl = '{{url('users')}}';
    // Loading button plugin (removed from BS4)
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
</html>


