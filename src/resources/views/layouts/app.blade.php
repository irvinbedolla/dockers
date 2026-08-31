<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    {{-- Cada vista declara su @section('title'); el segundo argumento de
         @yield es el texto de respaldo si alguna no lo hiciera. --}}
    <title>@yield('title', 'Sistema Integral para la Conciliación') | SiConcilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover' name='viewport'>

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

        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            background-color: #f4f6f9 !important;
        }

        #app, .main-wrapper, .main-content {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }

        /* .table-responsive vuelve a su comportamiento de Bootstrap
           (`overflow-x: auto`): la tabla ancha se desplaza dentro de su tarjeta.
           Aquí estaba anulado con `overflow: visible !important` para que los
           desplegables dentro de tablas no se recortaran, pero eso quitaba la
           contención a las 94 vistas con tabla y cualquiera ancha empujaba la
           página entera, sacando barra horizontal en toda la vista.

           Las 15 pantallas que sí tienen desplegables dentro de la tabla marcan
           su contenedor con .menu-visible y conservan el comportamiento viejo. */
        .table-responsive.menu-visible {
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
                background-color: #496163 !important;
            }

            .main-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 250px !important;
                height: 100vh !important;
                height: 100dvh !important;
                z-index: 890 !important;
                background-color: #ffffff !important;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
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
                background-color: #496163 !important;
            }

            .main-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: -250px !important;
                width: 250px !important;
                height: 100vh !important;
                height: 100dvh !important;
                z-index: 999 !important;
                background-color: #ffffff !important;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                overscroll-behavior-y: contain !important;
            }

            body.sidebar-show .main-sidebar {
                left: 0 !important;
            }

            /* Evitar que iOS haga zoom automático al enfocar campos en iPhone */
            input:not([type="checkbox"]):not([type="radio"]),
            select,
            textarea,
            .form-control {
                font-size: 16px !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding-top: 85px !important;
                padding-left: 15px !important;
                padding-right: 15px !important;
                width: 100% !important;
            }

            /* style.css le da a .section-header `margin: 0 -30px` para que la
               franja blanca llegue de orilla a orilla, contando con los 30px de
               padding que .main-content tiene en escritorio. Aquí el padding es
               de 15, así que esos -30 se salían 15px por cada lado y empujaban
               la página: ésa era la barra horizontal que aparecía en todas las
               vistas, porque todas tienen encabezado de sección. */
            .main-wrapper-1 .section .section-header {
                margin-left: -15px !important;
                margin-right: -15px !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
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

        /* ------------------------------------------------------------- */
        /* [SICONCILIO] Botón de menú                                     */
        /* ------------------------------------------------------------- */

        .main-navbar .boton-menu {
            padding: 0 !important;
            justify-content: center !important;
        }

        .boton-menu__cajon {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            gap: 5px;
            width: 38px;
            height: 38px;
            padding: 0 9px;
            border-radius: 11px;
            background: rgba(255, 255, 255, .10);
            transition: background-color .2s ease;
        }

        .boton-menu:hover .boton-menu__cajon { background: rgba(255, 255, 255, .20); }

        .boton-menu:focus-visible .boton-menu__cajon {
            outline: 2px solid #CEA845;
            outline-offset: 2px;
        }

        .boton-menu__raya {
            display: block;
            width: 20px;
            height: 2px;
            border-radius: 2px;
            background: #fff;
            transition: width .22s ease, transform .28s ease, opacity .18s ease, background-color .2s ease;
        }

        /* La raya de en medio va más corta: es lo que lo saca del hamburger
           genérico. Al pasar el mouse se empareja y las tres toman el dorado. */
        .boton-menu__raya:nth-child(2) { width: 13px; }
        .boton-menu:hover .boton-menu__raya:nth-child(2) { width: 20px; }
        .boton-menu:hover .boton-menu__raya { background: #CEA845; }

        /* En escritorio con el menú colapsado se invierte el ritmo de las rayas:
           avisa del estado sin convertirse en una equis, que ahí significaría
           "cerrar" y el menú no está cerrado, está angosto. */
        body.sidebar-mini .boton-menu__raya:nth-child(1),
        body.sidebar-mini .boton-menu__raya:nth-child(3) { width: 13px; }
        body.sidebar-mini .boton-menu__raya:nth-child(2) { width: 20px; }

        /* En móvil el menú sí se abre encima, así que ahí las rayas se cruzan. */
        @media (max-width: 1024px) {
            body.sidebar-show .boton-menu__raya:nth-child(1) {
                width: 20px;
                transform: translateY(7px) rotate(45deg);
            }

            body.sidebar-show .boton-menu__raya:nth-child(2) { opacity: 0; }

            body.sidebar-show .boton-menu__raya:nth-child(3) {
                width: 20px;
                transform: translateY(-7px) rotate(-45deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .boton-menu__raya, .boton-menu__cajon { transition: none; }
        }

        /* Nombre y rol del usuario en la barra superior */
        .usuario-barra {
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
            text-align: left;
        }

        .usuario-barra__nombre {
            font-size: 13.5px;
            font-weight: 600;
            color: #fff;
        }

        .usuario-barra__rol {
            font-size: 11.5px;
            font-weight: 400;
            color: rgba(255, 255, 255, .68);
            margin-top: 2px;
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

        /* Avatar del header. El círculo lo hace el contenedor y no la imagen,
           para que al implementar la carga de foto cualquier archivo quede
           recortado al círculo en vez de deformarse. */
        .avatar-usuario {
            flex: 0 0 auto;
            display: block;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            /* Sin fondo propio: el marcador trae unos píxeles transparentes de
               orilla y un fondo claro ahí se veía como un halo entre la figura
               y el aro. */
            background: transparent;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .22);
        }

        /* El ancho va con !important porque style.css trae
           `.navbar .nav-link.nav-link-user img { width: 30px }`, que le gana por
           especificidad: la imagen quedaba de 30x36 dentro de un contenedor de
           36x36 y el recorte circular salía chico y cargado a la izquierda.

           El recorte se hace con clip-path además del overflow del contenedor:
           dentro de la navbar, que tiene transition: all, el recorte por
           border-radius se dibuja con las esquinas rectas. */
        .avatar-usuario img {
            width: 100% !important;
            height: 100% !important;
            display: block;
            object-fit: cover;
            object-position: center;
            border-radius: 50%;
            clip-path: circle(50% at 50% 50%);
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

        /* El primer ítem del menú queda pegado al logo sin este respiro, y en móvil
           se añade espacio al fondo para que la barra inferior de Chrome/iOS no tape
           los últimos ítems del menú. */
        .main-sidebar .sidebar-menu {
            padding-top: 6px !important;
            padding-bottom: 90px !important;
            padding-bottom: calc(90px + env(safe-area-inset-bottom, 0px)) !important;
        }

        /* ------------------------------------------------------------- */
        /* Ítem seleccionado del menú                                     */
        /* ------------------------------------------------------------- */
        /* Va en el color de apoyo (#CEA845) y no en el predominante, para que
           se distinga de la barra superior en lugar de fundirse con ella. */
        .main-sidebar .sidebar-menu li.active > a,
        body.sidebar-mini .main-sidebar .sidebar-menu > li.active > a {
            background-color: #CEA845 !important;
            color: #496163 !important;
            box-shadow: none !important;
            font-weight: 700 !important;
        }

        /* El <span> de la etiqueta trae la clase text-dark de Bootstrap, que ya
           viene con !important: hay que ganarle por especificidad. El peso se
           repite aquí porque el <span> hereda del tema, no del <a>. */
        .main-sidebar .sidebar-menu li.active > a i,
        .main-sidebar .sidebar-menu li.active > a span,
        body.sidebar-mini .main-sidebar .sidebar-menu > li.active > a i {
            color: #496163 !important;
        }

        .main-sidebar .sidebar-menu li.active > a span {
            font-weight: 700 !important;
        }

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
                        $('[data-toggle="sidebar"]').attr('aria-expanded', !$body.hasClass('sidebar-mini'));
                    } else {
                        // Modo Móvil: Mostrar / Ocultar lateral
                        $body.toggleClass('sidebar-show');
                        $('[data-toggle="sidebar"]').attr('aria-expanded', $body.hasClass('sidebar-show'));
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
    {{-- Bloques que las vistas hijas empujan al final del cuerpo (overlays de
         carga, modales sueltos). Antes se escribían fuera de @section y Blade
         los emitía ANTES del <!DOCTYPE>, lo que dejaba el documento en quirks
         mode y el <head> vacío. --}}
    @stack('body_end')

</body>
</html>