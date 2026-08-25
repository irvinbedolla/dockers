<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si Concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- 1. CSS de Bootstrap 5.3 e Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- CSS de la Aplicación -->
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    <style>
        /* Solución para que los dropdowns no se corten en tablas */
        .table-responsive {
            overflow: visible !important;
        }
    </style>

    <!-- 2. jQuery cargado en el HEAD -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @livewireStyles
    @yield('page_css')
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