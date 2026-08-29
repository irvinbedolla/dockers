{{--
    Estilos de la agenda. Lo comparten la pantalla /agenda y el Inicio, así que
    el calendario existe una sola vez: cualquier arreglo aquí llega a las dos.
--}}
    {{-- Mismos estilos de calendario que la pantalla de bloqueos por sede --}}
    <link href="{{ asset('assets/css/calendario.css') }}" rel="stylesheet">
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
        /* El display:block de este selector por id le ganaba a .cal-fc.is-oculto,
           así que el esqueleto se quedaba arriba y el calendario aparecía debajo
           al mismo tiempo. El ancho lo resuelve el contenedor. */
        #calendar {
            width: 100% !important;
        }

        /* Evita que las celdas se vean comprimidas */
        .fc-view-harness {
            background-color: #fff;
        }

        /* Ajuste para que la Card de Bootstrap no le ponga padding excesivo */
        .card-body {
            padding: 15px !important;
        }
        @media (max-width: 768px) {
            /* El contenedor padre deja de ser horizontal */
            .flex-column.flex-md-row {
                align-items: stretch !important;
            }


            /* Quitamos el justify-content-center para que no baile el contenido */
            .justify-content-center {
                justify-content: flex-start !important;
            }

            .fc .fc-toolbar {
                display: flex;
                flex-direction: column;
                gap: 10px; /* Espacio entre fecha y botones */
            }

            /* Centrar el título y reducir su tamaño */
            .fc .fc-toolbar-title {
                font-size: 1.2rem !important;
                text-align: center;
                width: 100%;
            }

            /* Asegurar que los botones ocupen el ancho necesario sin amontonarse */
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 5px;
            }

            /* Ajustar tamaño de botones para que quepan mejor */
            .fc .fc-button {
                padding: 0.4em 0.6em !important;
                font-size: 0.85em !important;
            }
            .fc-event {
                background-color: transparent !important;
                border: 1px solid transparent !important; /* Borde invisible para que no brinque */
                box-shadow: none !important;
                border-radius: 6px !important;
                transition: background-color 0.2s ease, border-color 0.2s ease; /* Efecto de transición suave */
                cursor: pointer;
            }
            .fc-event:hover, .fc-event:focus {
                background-color: #f0f0f0 !important; /* Gris claro */
                border-color: #e2e2e2 !important;     /* Borde gris para enmarcarlo */
            }

            /* Forzar que el texto no se salga del cuadro */
            .custom-event-content .text-truncate {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
                width: 100%;
            }

        }
    </style>
