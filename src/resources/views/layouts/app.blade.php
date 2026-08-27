<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    {{-- Se abre la conexión con los CDN antes de necesitarlos: ahorra el DNS y el
         handshake TLS cuando el navegador llega a cada archivo. --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- 1. Bootstrap 5.3 CSS e Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- FullCalendar se movió al final del body: es el archivo más pesado del layout
         y en el <head> bloqueaba el pintado de TODAS las pantallas, también las que
         no tienen calendario. Las vistas lo usan desde su sección de scripts, que
         se renderiza después. --}}

    <!-- Ionicons y Estilos Globales -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    {{-- assets/css/all.css era Font Awesome 5.15.4 otra vez (75 KB), pero sin las
         declaraciones de fuente: los glifos los sirve el all.min.css del CDN. --}}
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/realtime.css') }}" rel="stylesheet">
    
    <!-- jQuery obligatorio en HEAD para componentes de Stisla -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    {{-- select2 4.0.13 se cargaba aquí y otra vez al final del body desde
         assets/js/select2.min.js. Se deja solo la copia local. --}}

    <!-- Template CSS (Stisla) -->
    <link rel="icon" href="{{ asset('assets/images/ccl-r.png') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">

    @livewireStyles
    @yield('page_css')
    @yield('css')

    <style>
        /* 1. Desactivar fondos decorativos rotos */
        .navbar-bg,
        .main-sidebar::before,
        .main-sidebar::after,
        body::before,
        body::after {
            display: none !important;
            content: none !important;
            background: none !important;
        }

        body {
            background-color: #f4f6f9 !important;
        }

        /* Fixes de Dropdowns en Tablas para BS5 */
        .table-responsive {
            overflow: visible !important;
        }

        /* Transiciones suaves para el menú */
        .main-sidebar,
        .main-navbar,
        .main-content {
            transition: all 0.3s ease-in-out !important;
        }

        /* ------------------------------------------------------------- */
        /* ESTILOS PARA ESCRITORIO (MÁS DE 1024px)                        */
        /* ------------------------------------------------------------- */
        @media (min-width: 1025px) {
            /* Estado Normal (Menú Expandido 250px) */
            .main-navbar {
                position: fixed !important;
                top: 0 !important;
                left: 250px !important;
                width: calc(100% - 250px) !important;
                height: 70px !important;
                z-index: 850 !important;
                background-color: #6A0F49 !important;
            }

            .main-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 250px !important;
                height: 100vh !important;
                z-index: 890 !important;
                background-color: #ffffff !important;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05) !important;
                overflow-y: auto !important;
            }

            .main-content {
                margin-left: 250px !important;
                padding-top: 90px !important;
                padding-left: 30px !important;
                padding-right: 30px !important;
                padding-bottom: 40px !important;
                width: calc(100% - 250px) !important;
                background-color: #f4f6f9 !important;
            }

            /* Estado Colapsado en PC (Clase sidebar-mini de Stisla) */
            body.sidebar-mini .main-sidebar {
                width: 78px !important;
            }

            body.sidebar-mini .main-navbar {
                left: 78px !important;
                width: calc(100% - 78px) !important;
            }

            body.sidebar-mini .main-content {
                margin-left: 78px !important;
                width: calc(100% - 78px) !important;
            }
        }

        /* ------------------------------------------------------------- */
        /* ESTILOS PARA DISPOSITIVOS MÓVILES (1024px O MENOS)            */
        /* ------------------------------------------------------------- */
        @media (max-width: 1024px) {
            .main-navbar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 70px !important;
                z-index: 850 !important;
                background-color: #6A0F49 !important;
            }

            .main-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: -250px !important;
                width: 250px !important;
                height: 100vh !important;
                z-index: 999 !important;
                background-color: #ffffff !important;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
                overflow-y: auto !important;
            }

            body.sidebar-show .main-sidebar {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding-top: 85px !important;
                padding-left: 15px !important;
                padding-right: 15px !important;
                width: 100% !important;
            }
        }
        /* Posicionamiento y superposición del Dropdown de Usuario */
        .main-navbar .dropdown-menu {
            top: 55px !important;
            z-index: 1060 !important;
            border: none !important;
            border-radius: 8px !important;
        }

        /* Alineación vertical dentro de la barra guinda */
        .main-navbar .navbar-nav {
            align-items: center !important;
            height: 100% !important;
        }
        /* 1. Centrado vertical exacto del logo en el Sidebar */
        .main-sidebar .sidebar-brand {
            height: 70px !important;
            line-height: 70px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 10px !important;
        }

        .main-sidebar .sidebar-brand img {
            max-height: 50px !important;
            width: auto !important;
        }

        .main-sidebar .sidebar-brand-sm {
            height: 70px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* 2. Alineación Flexbox para la barra superior (Navbar) */
        .main-navbar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 20px !important;
        }

        /* Icono de Bricks y botón lateral */
        .main-navbar .form-inline {
            display: flex !important;
            align-items: center !important;
            margin: 0 !important;
        }

        .main-navbar .nav-link-user,
        .main-navbar .nav-link-lg {
            display: flex !important;
            align-items: center !important;
            height: 70px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .main-navbar .nav-link-lg i {
            font-size: 1.4rem !important;
            line-height: 1 !important;
        }

        /* 3. Logo pequeño y avatar del usuario */
        .main-navbar .user-thumbnail {
            width: 35px !important;
            height: 35px !important;
            object-fit: contain !important;
        }
        /* Manejo de visibilidad de los logos según el estado del sidebar */
        /* Estado normal (expandido): Mostrar logo completo y ocultar logo pequeño */
        .main-sidebar .sidebar-brand {
            display: flex !important;
        }
        .main-sidebar .sidebar-brand-sm {
            display: none !important;
        }

        /* Estado mini (colapsado en PC): Ocultar logo completo y mostrar logo pequeño */
        body.sidebar-mini .main-sidebar .sidebar-brand {
            display: none !important;
        }
        body.sidebar-mini .main-sidebar .sidebar-brand-sm {
            display: flex !important;
        }

        /* ------------------------------------------------------------- */
        /* [SICONCILIO] Logo fijo arriba del sidebar                      */
        /* ------------------------------------------------------------- */

        /* El contenedor que hace scroll es .main-sidebar, así que el sticky se
           ancla a él: el logo se queda arriba y el menú corre por debajo, igual
           que el contenido corre por debajo de la barra superior. */
        .main-sidebar .sidebar-brand,
        .main-sidebar .sidebar-brand-sm {
            position: sticky !important;
            top: 0;
            z-index: 2;
            background-color: #fff !important;
            height: 62px !important;
            line-height: normal !important;
            /* Línea sutil que aparece cuando el menú empieza a pasar por abajo */
            box-shadow: 0 1px 0 rgba(0, 0, 0, .06);
        }

        .main-sidebar .sidebar-brand img {
            max-height: 40px !important;
            width: auto !important;
        }

        /* Colapsado: el isotipo baja de tamaño junto con el sidebar */
        body.sidebar-mini .main-sidebar .sidebar-brand-sm {
            height: 54px !important;
            padding: 0 !important;
        }

        body.sidebar-mini .main-sidebar .sidebar-brand-sm img {
            /* El logo es apaisado (2880x957), así que el que manda es el ancho:
               52px caben en los 65px del sidebar dejando margen a los lados. */
            max-width: 52px !important;
            max-height: none !important;
            width: auto !important;
            height: auto !important;
        }

        /* El primer ítem del menú queda pegado al logo sin este respiro */
        .main-sidebar .sidebar-menu { padding-top: 6px !important; }

        /* ------------------------------------------------------------- */
        /* [SICONCILIO] Menú en estado colapsado                          */
        /* ------------------------------------------------------------- */

        /* Colapsado sin barra de scroll visible: es lo único que deja usar los 78px
           completos. Con la barra a la derecha el contenido se recorre, y con
           scrollbar-gutter reservado a ambos lados aparece un hueco a la izquierda
           que corta el resaltado del ítem activo. El menú sigue desplazándose con
           la rueda y con el touchpad. */
        body.sidebar-mini .main-sidebar {
            scrollbar-width: none;
            scrollbar-gutter: auto;
        }

        body.sidebar-mini .main-sidebar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        /* Stisla mete padding:10px en cada <li>, y ese margen impedía que el
           resaltado del ítem activo llegara a los bordes. */
        body.sidebar-mini .main-sidebar .sidebar-menu > li {
            padding: 0 !important;
        }

        body.sidebar-mini .main-sidebar .sidebar-menu li a {
            justify-content: center !important;
            gap: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body.sidebar-mini .main-sidebar .sidebar-menu li a > i {
            flex: 0 0 auto !important;
            font-size: 18px !important;
        }

        /* El selector azul ocupa todo el ancho de la barra */
        body.sidebar-mini .main-sidebar .sidebar-menu > li.active > a {
            width: 100% !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        /* ------------------------------------------------------------- */
        /* [SICONCILIO] Una sola barra de scroll y footer pegado abajo    */
        /* ------------------------------------------------------------- */

        /* El documento mide exactamente el viewport: el contenido crece y el
           footer se queda abajo. Antes .main-content tenía min-height:100vh y
           con el footer debajo el documento SIEMPRE superaba la pantalla, así
           que la barra de la página aparecía incluso en vistas cortas, junto a
           la del sidebar. */
        .main-wrapper {
            display: flex !important;
            flex-direction: column !important;
            min-height: 100vh !important;
        }

        .main-content {
            flex: 1 0 auto !important;
            /* !important anula también el min-height en línea que ponía scripts.js */
            min-height: 0 !important;
        }

        .main-footer {
            flex-shrink: 0 !important;
            display: block !important;
            margin-top: 0 !important;
        }

        @media (min-width: 1025px) {
            .main-footer {
                margin-left: 250px !important;
                width: calc(100% - 250px) !important;
                padding-left: 30px !important;
            }

            body.sidebar-mini .main-footer {
                margin-left: 78px !important;
                width: calc(100% - 78px) !important;
                padding-left: 30px !important;
            }
        }

        @media (max-width: 1024px) {
            /* Stisla dejaba padding-left:280px y en móvil el texto salía de pantalla */
            .main-footer {
                margin-left: 0 !important;
                width: 100% !important;
                padding-left: 15px !important;
            }
        }

        /* En modo colapsado style.css forzaba overflow:initial y el menú quedaba
           cortado sin forma de hacer scroll. */
        body.sidebar-mini .main-sidebar {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        /* Barra del sidebar discreta: la que dibujaba niceScroll ya no existe. */
        .main-sidebar {
            scrollbar-width: thin;
            scrollbar-color: #c7c7c7 transparent;
        }

        .main-sidebar::-webkit-scrollbar { width: 6px; }
        .main-sidebar::-webkit-scrollbar-thumb { background-color: #c7c7c7; border-radius: 3px; }
        .main-sidebar::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <nav class="navbar navbar-expand-lg main-navbar">
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

    <!-- FullCalendar (antes estaba en el <head>, bloqueando el render) -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <!-- 5. Scripts de la Plantilla -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @livewireScripts
    
    @yield('page_js')
    @yield('scripts')
        <script>
            $(document).ready(function() {
                // Toggle para colapsar en PC y abrir/cerrar en Móvil
                $(document).on('click', '[data-toggle="sidebar"]', function(e) {
                    e.preventDefault();
                    var $body = $('body');
                    
                    if ($(window).width() > 1024) {
                        // Modo Escritorio: Alternar vista mini (65px) / expandida (250px)
                        $body.toggleClass('sidebar-mini');
                    } else {
                        // Modo Móvil: Mostrar / Ocultar lateral
                        $body.toggleClass('sidebar-show');
                    }
                });

                // Ocultar menú en móvil al hacer clic en cualquier opción del sidebar
                $('.main-sidebar .sidebar-menu a').on('click', function() {
                    if ($(window).width() <= 1024) {
                        $('body').removeClass('sidebar-show');
                    }
                });

                // Ocultar menú en móvil al hacer clic en el área de contenido principal
                $('.main-content').on('click', function() {
                    if ($(window).width() <= 1024 && $('body').hasClass('sidebar-show')) {
                        $('body').removeClass('sidebar-show');
                    }
                });
            });
        </script>
    @stack('scripts')

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorMsg = @json(session('error'));
                try {
                    if (typeof swal === 'function') {
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
</body>
</html>