<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    // Bootstrap 5.3 CSS and Bootstrap Icons
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <!-- Ionicons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/realtime.css') }}" rel="stylesheet">
    
    <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        
        /* Fix de compatibilidad Stisla Layout para Bootstrap 5.3 */
        body {
            background-color: #f4f6f9;
        }

        #app .main-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Fijar el Sidebar a la izquierda */
        .main-sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            z-index: 890;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,.03);
        }

        /* Empujar el contenido principal hacia la derecha del Sidebar */
        .main-content {
            padding-left: 280px !important;
            padding-right: 30px !important;
            padding-top: 100px !important;
            width: 100%;
        }

        /* Ajuste del Navbar Superior */
        .navbar-bg {
            height: 70px;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 850;
        }

        .main-navbar {
            left: 250px !important;
            width: calc(100% - 250px) !important;
            z-index: 880;
            position: fixed !important;
            top: 0;
        }

        /* Ocultar elementos decorativos antiguos de Stisla que generan los globos de colores */
        .sidebar-brand::before,
        .main-sidebar::after {
            display: none !important;
        }
    </style>

    @livewireStyles


    @yield('page_css')
    <!-- Template CSS -->
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">
    @yield('page_css')

    @yield('css')
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            
            <nav class="navbar navbar-expand-lg main-navbar" style="background-color: #6A0F49">
                @include('layouts.header')
            </nav>
            
            <div class="main-sidebar main-sidebar-postion">
                @include('layouts.sidebar')
            </div>

            <!-- Contenido Principal -->
            <div class="main-content">
                @yield('content')
            </div>

            <footer class="main-footer">
                @include('layouts.footer')
            </footer>
        </div>
    </div>

    @stack('modals')

    <!-- 3. JS de Bootstrap 5.3 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 4. Plugins JQuery -->
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nicescroll.js') }}"></script>
    
    <!-- DataTables Bootstrap 5 -->
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>

    <!-- 5. Scripts de la Plantilla -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @livewireScripts
    
    <!-- 6. Inyección Limpia de Scripts Secundarios -->
    @yield('page_js')
    @yield('scripts')
    @stack('scripts')
</body>
</html>

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var errorMsg = @json(session('error'));
            try {
                if (typeof swal === 'function') {
                    // SweetAlert 1.x: la opción es "type", no "icon"; y el botón
                    // se controla con "confirmButtonText", no con "button".
                    swal({
                        title: 'Error',
                        text: errorMsg,
                        type: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Error: ' + errorMsg);
                }
            } catch (e) {
                console.error('Error showing flash message:', e);
            }
        });
    </script>
@endif
