<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Ionicons -->
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/iziToast.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Libraries CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet">
    
    <!-- jQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    

    <style>
       
        :root {
            --color-guinda: #496163;
            --color-guinda-dark: #530c3a;
            --color-oro: #CEA845;
            --color-oro-dark: #b59238;
            --color-gris-bg: #f8f9fa;
        }

        /* Página */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-gris-bg);
            color: #333;
            padding-top: 100px;
        }

        /* Navbar */
        .navbar-institucional {
            background-color: #fff;
            border-bottom: 3px solid var(--color-oro);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        /* Encabezados */
        .card-header-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            font-weight: 600;
        }

        .section-title-banner {
            background-color: #f1f3f5;
            border-left: 4px solid var(--color-guinda);
            padding: 8px 15px;
            margin-bottom: 20px;
            font-weight: 700;
            color: var(--color-guinda);
        }

        /* Botones */
        .btn-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            border: none;
        }

        .btn-guinda:hover,
        .btn-guinda:focus {
            background-color: var(--color-guinda-dark);
            color: #fff;
        }

        .btn-oro {
            background-color: var(--color-oro);
            color: #fff;
            border: none;
            font-weight: 500;
        }

        .btn-oro:hover,
        .btn-oro:focus {
            background-color: var(--color-oro-dark);
            color: #fff;
        }

        /* =========================
        FULLCALENDAR
        ========================= */

        #calendarTurno {
            width: 100%;
            min-height: 520px;
        }

        /* Eventos */
        .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
        }

        /* Disponible */
        .fc-event-disponible,
        .fc-event-turnos {
            background-color: #26c03a !important;
            border-color: #26c03a !important;
            color: #fff !important;
            cursor: pointer;
        }

        /* Expirado */
        .fc-event-expirado {
            background-color: #8a959e !important;
            border-color: #8a959e !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Día inhábil */
        .fc-event-inhabil {
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Ocupado */
        .fc-event-ocupado {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
            cursor: not-allowed;
        }

        /* Evento seleccionado */
        #calendarTurno .fc-event-selected {
            border: 3px solid var(--color-oro) !important;
            box-shadow: 0 0 10px rgba(206, 168, 69, .8) !important;
            opacity: 1 !important;
            filter: none !important;
        }

        /* Modal del calendario */
        .modal-xl.grande {
            max-width: 95% !important;
        }

        .modal-content.modal-grande {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
            font-size: 18px;
        }

        /* Títulos de modal */
        .modal-titulo {
            background-color: var(--color-guinda);
            color: #fff;
            padding: 10px;
            font-size: 20px;
        }

        /* Aviso de cobros indebidos */
        .aviso-engano {
            margin: 6px 0 10px;
            text-align: center;
            font-size: clamp(26px, 4vw, 36px);
            font-weight: 800;
            line-height: 1.15;
            color: var(--color-guinda);
        }
        .aviso-denuncia {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            margin: 22px auto 4px;
            text-align: center;
        }
        .aviso-denuncia__titulo {
            font-size: 18px;
            font-weight: 700;
            color: #2E3C3D;
        }
        .aviso-denuncia__numero {
            font-size: clamp(56px, 9vw, 80px);
            font-weight: 800;
            line-height: 1;
            letter-spacing: .04em;
            color: #C8102E;
            text-decoration: none;
        }
        .aviso-denuncia__numero:hover,
        .aviso-denuncia__numero:focus { color: #A00D25; text-decoration: none; }
        .aviso-denuncia__opcion {
            margin-top: 2px;
            font-size: 20px;
            font-weight: 800;
            color: #C8102E;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .aviso-denuncia__dependencia {
            font-size: 14px;
            color: #5E6E6F;
        }
        .aviso-buzon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            padding: 10px 20px;
            border-radius: 999px;
            background: #E8730C;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(232, 115, 12, .3);
        }
        .aviso-buzon:hover,
        .aviso-buzon:focus { background: #C95F05; color: #fff; text-decoration: none; }
        .aviso-buzon:focus-visible { outline: 3px solid #FFD3A8; outline-offset: 2px; }

        /* Loader */
        .loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: url("{{ asset('assets/images/pageLoader.gif') }}")
                50% 50% no-repeat rgba(255, 255, 255, .85);
        }
        @media (max-width: 768px) {

            #calendarTurno {
                width: 100% !important;
            }

            #calendarTurno .fc-list,
            #calendarTurno .fc-list-table {
                width: 100% !important;
            }

            #calendarTurno .fc-list-table {
                table-layout: fixed !important;
            }

            #calendarTurno .fc-list-event {
                width: 100% !important;
                box-sizing: border-box !important;
                opacity: 1 !important;
                filter: none !important;
                transform: translateZ(0);
            }

            #calendarTurno .fc-list-event td {
                box-sizing: border-box !important;
            }

            #calendarTurno .fc-list-event::before,
            #calendarTurno .fc-list-event::after,
            #calendarTurno .fc-list-event td::before,
            #calendarTurno .fc-list-event td::after {
                display: none !important;
                content: none !important;
            }

        }

    </style>
    {{-- Formulario de cita en línea: pasos, ayudas y validación en pantalla. --}}
    <style>
        .gc-intro { color: #4A5859; font-size: 15px; margin: 0 0 18px; }
        .gc-req { color: #C8102E; font-weight: 700; }
        .gc-opcional { color: #8A9899; font-weight: 400; font-size: 13px; }

        .gc-pasos {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0 0 22px;
            counter-reset: none;
        }
        .gc-pasos li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            background: #EEF2F2;
            color: #5E6E6F;
            font-size: 13.5px;
            font-weight: 600;
            min-width: 0;
        }
        .gc-pasos__num {
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #fff;
            color: var(--color-guinda);
            font-size: 13px;
            font-weight: 800;
        }
        .gc-pasos__texto { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gc-pasos li.is-listo { background: #E3F4E8; color: #1E7B3C; }
        .gc-pasos li.is-listo .gc-pasos__num { background: #1E7B3C; color: #fff; font-size: 0; }
        .gc-pasos li.is-listo .gc-pasos__num::before { content: "\2713"; font-size: 14px; }
        @media (max-width: 576px) {
            .gc-pasos { gap: 4px; }
            .gc-pasos li { flex-direction: column; gap: 4px; padding: 8px 4px; font-size: 11.5px; text-align: center; }
        }

        .gc-seccion {
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            padding: 18px 20px 20px;
            margin: 0 0 18px;
            background: #fff;
        }
        .gc-titulo {
            float: none;
            width: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 700;
            color: var(--color-guinda);
        }
        .gc-num {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--color-guinda);
            color: #fff;
            font-size: 15px;
        }
        .gc-form .form-label { font-weight: 600; color: #2E3C3D; font-size: 14.5px; }
        .gc-form .form-control,
        .gc-form .form-select { min-height: 46px; font-size: 16px; }   /* 16px: el celular no hace zoom */
        .gc-form .form-control:focus,
        .gc-form .form-select:focus { border-color: var(--color-oro); box-shadow: 0 0 0 .2rem rgba(206, 168, 69, .25); }
        .gc-mayus { text-transform: uppercase; }
        .gc-mayus::placeholder { text-transform: none; }

        .gc-ayuda-tramite.is-activa {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBF4E1;
            color: #6B5310;
            font-size: 13.5px;
        }

        .gc-oficina {
            height: 100%;
            min-height: 72px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px dashed #C9D3D3;
            color: #8A9899;
        }
        .gc-oficina i { font-size: 24px; }
        .gc-oficina__etiqueta { display: block; font-size: 12.5px; }
        .gc-oficina__nombre { display: block; font-size: 17px; font-weight: 700; }
        .gc-oficina.is-lista { border: 1px solid var(--color-guinda); background: #F2F6F6; color: var(--color-guinda); }

        .gc-horario { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
        .gc-horario__boton { padding: 12px 22px; font-size: 16px; font-weight: 600; border-radius: 10px; }
        .gc-horario__boton:disabled { opacity: .5; }
        .gc-turno {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #E3F4E8;
            color: #1E5E33;
            font-size: 15px;
        }
        .gc-turno i { font-size: 24px; }
        .gc-turno strong { display: block; font-size: 17px; }
        .gc-error {
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBE7EA;
            color: #A3162E;
            font-size: 14px;
            font-weight: 600;
        }
        .gc-aviso-cambio {
            padding: 8px 12px;
            border-radius: 8px;
            background: #FBF4E1;
            color: #6B5310;
            font-size: 13.5px;
        }

        .gc-enviar { text-align: center; padding-top: 6px; }
        .gc-enviar__boton { min-width: 260px; padding: 14px 28px; font-size: 18px; font-weight: 700; border-radius: 12px; }
        .gc-enviar__nota { margin: 10px auto 0; max-width: 520px; color: #6C7A7B; font-size: 13.5px; }
        @media (max-width: 576px) {
            .gc-seccion { padding: 14px 14px 16px; }
            .gc-enviar__boton { width: 100%; min-width: 0; }
        }
    </style>
    {{-- Selector de día y hora. --}}
    <style>
        .hc-contexto { display: block; margin-top: 2px; font-size: 13px; color: rgba(255, 255, 255, .8); }
        .hc-cuerpo { padding: 18px 20px; min-height: 380px; }

        .hc-semana { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
        .hc-semana__texto { font-size: 16px; font-weight: 700; color: #2E3C3D; text-align: center; }
        .hc-flecha {
            width: 42px; height: 42px; border-radius: 50%;
            border: 1px solid #DDE3E3; background: #fff; color: var(--color-guinda);
            display: grid; place-items: center; font-size: 18px;
        }
        .hc-flecha:hover:not(:disabled) { background: #F2F6F6; }
        .hc-flecha:disabled { opacity: .35; cursor: not-allowed; }

        .hc-dias {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 18px;
        }
        .hc-dia {
            display: flex; flex-direction: column; align-items: center; gap: 2px;
            padding: 10px 4px;
            border-radius: 12px;
            border: 2px solid #E3E8E8;
            background: #fff;
            color: #2E3C3D;
            line-height: 1.15;
            transition: border-color .12s, background-color .12s;
        }
        .hc-dia__semana { font-size: 12.5px; text-transform: capitalize; color: #6C7A7B; }
        .hc-dia__numero { font-size: 22px; font-weight: 800; }
        .hc-dia__mes { font-size: 12px; text-transform: capitalize; color: #6C7A7B; }
        .hc-dia__libres { margin-top: 4px; font-size: 11.5px; font-weight: 700; color: #1E7B3C; }
        .hc-dia:hover:not(:disabled) { border-color: #9FC3A9; }
        .hc-dia.is-activo { border-color: var(--color-guinda); background: var(--color-guinda); color: #fff; }
        .hc-dia.is-activo .hc-dia__semana,
        .hc-dia.is-activo .hc-dia__mes,
        .hc-dia.is-activo .hc-dia__libres { color: rgba(255, 255, 255, .9); }
        .hc-dia:disabled { background: #F5F7F7; color: #A6B1B2; cursor: not-allowed; }
        .hc-dia:disabled .hc-dia__libres { color: #A6B1B2; font-weight: 600; }

        .hc-grupo { margin-bottom: 14px; }
        .hc-grupo__titulo { font-size: 13px; font-weight: 700; color: #6C7A7B; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .05em; }
        .hc-grupo__horas { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: 8px; }
        .hc-hora {
            padding: 11px 6px;
            border-radius: 10px;
            border: 2px solid #CDE5D4;
            background: #F1FAF3;
            color: #1E5E33;
            font-size: 16px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .hc-hora:hover { border-color: #1E7B3C; }
        .hc-hora.is-elegida { border-color: var(--color-oro); background: var(--color-oro); color: #fff; box-shadow: 0 4px 12px rgba(206, 168, 69, .35); }
        .hc-dia:focus-visible, .hc-hora:focus-visible, .hc-flecha:focus-visible { outline: 3px solid var(--color-oro); outline-offset: 2px; }

        .hc-mensaje { padding: 36px 12px; text-align: center; color: #6C7A7B; }
        .hc-mensaje i { display: block; font-size: 34px; margin-bottom: 8px; color: #A6B1B2; }
        .hc-mensaje .btn { margin-top: 10px; }

        .hc-pie { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; background: #F8F9FA; }
        .hc-elegido { font-size: 14.5px; color: #6C7A7B; }
        .hc-elegido strong { color: #2E3C3D; }
        .hc-pie__botones { display: flex; gap: 8px; margin-left: auto; }

        /* Celular: la ventana ocupa la pantalla, los días se deslizan y los botones quedan abajo a lo ancho. */
        @media (max-width: 576px) {
            .hc-dialogo { margin: 0; max-width: none; height: 100%; }
            .hc-dialogo .modal-content { height: 100%; border-radius: 0; border: 0; }
            .hc-cuerpo { padding: 14px; }
            .hc-dias { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 4px; }
            .hc-dia { flex: 0 0 76px; scroll-snap-align: start; }
            .hc-grupo__horas { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hc-pie { flex-direction: column; align-items: stretch; }
            .hc-pie__botones { margin-left: 0; }
            .hc-pie__botones .btn { flex: 1; padding: 12px; }
            .hc-elegido { text-align: center; }
        }
    </style>
    @livewireStyles

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

</head>
<nav class="navbar navbar-expand-lg navbar-light navbar-institucional fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="65">
            </a>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">
                Centro de Conciliación Laboral del Estado de Michoacán
            </span>
        </div>
    </nav>
<body>
    <main class="container mb-5">
        <div id="app">
            <section class="section">
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-guinda py-3">
                        <h4 class="m-0 text-center fs-5"><i class="bi bi-calendar-check me-2"></i>Genera tu Cita</h4>
                    </div>
                    <div class="card-body p-4"> 

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{--
                                Formulario de cita en línea. Mismos campos y nombres que antes
                                (HomeController::turnos_publico no cambió); sólo se reorganizó en
                                cuatro pasos con ayudas, validación en pantalla y confirmación del
                                correo, que es a donde llega el acuse.
                            --}}
                            <form class="needs-validation gc-form" novalidate id="form_roles" method="POST" action="{{ route('turnos_publico') }}">
                                @csrf
                                <input type="hidden" name="orientacion" id="orientacion" value="No">
                                <input type="hidden" name="excepcion" id="excepcion" value="No">
                                <input type="hidden" name="delegacion" id="delegacion" value="">
                                <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                                <input type="hidden" name="hora_turno" id="hora_turno" value="">

                                <p class="gc-intro">
                                    Llena los cuatro pasos. Lo marcado con <span class="gc-req">*</span> es obligatorio.
                                    Al terminar te enviaremos a tu correo el <strong>acuse con código QR</strong> que debes presentar el día de tu cita.
                                </p>

                                <ol class="gc-pasos" id="gcPasos" aria-label="Avance">
                                    <li data-paso="1"><span class="gc-pasos__num">1</span><span class="gc-pasos__texto">Trámite</span></li>
                                    <li data-paso="2"><span class="gc-pasos__num">2</span><span class="gc-pasos__texto">Tus datos</span></li>
                                    <li data-paso="3"><span class="gc-pasos__num">3</span><span class="gc-pasos__texto">Tu caso</span></li>
                                    <li data-paso="4"><span class="gc-pasos__num">4</span><span class="gc-pasos__texto">Día y hora</span></li>
                                </ol>

                                {{-- PASO 1 --}}
                                <fieldset class="gc-seccion" data-paso="1">
                                    <legend class="gc-titulo"><span class="gc-num">1</span> ¿Qué trámite necesitas?</legend>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="tipo">Tipo de trámite <span class="gc-req">*</span></label>
                                            <select id="tipo" name="tipo" class="form-select" onchange="blockCalendar();" required>
                                                <option value="">Elige una opción</option>
                                                <option value="Asesoría">Asesoría</option>
                                                <option value="Solicitud">Solicitud de conciliación</option>
                                                <option value="Ratificación">Ratificación de convenio</option>
                                            </select>
                                            <div class="form-text gc-ayuda-tramite" id="gcAyudaTramite">Elige una opción y te explicamos de qué se trata.</div>
                                            <div class="invalid-feedback">Elige el tipo de trámite.</div>
                                        </div>
                                        <div class="col-md-6" style="display:none;" id="si_ratificacion">
                                            <label class="form-label" for="folio_patronal">¿Ya tienes un folio de ratificación que te dio tu patrón?</label>
                                            <select id="folio_patronal" class="form-select">
                                                <option value="">Elige una opción</option>
                                                <option value="si">Sí, ya tengo folio</option>
                                                <option value="no">No tengo folio</option>
                                            </select>
                                            <div class="form-text">Si ya tienes folio, te llevaremos al formulario para ratificar con él.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="municipio_citado">Municipio donde está tu trabajo (o la persona a la que vas a citar) <span class="gc-req">*</span></label>
                                            <select id="municipio_citado" class="form-select" name="municipio_citado" required>
                                                <option value="">Elige el municipio</option>
                                                @foreach($municipios as $mun)
                                                    <option value="{{ $mun['id'] }}" data-delegacion-id="{{ $mun['delegacion_id'] }}">{{ $mun['nombre'] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Elige el municipio.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="gc-oficina" id="gcOficina">
                                                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                                <div>
                                                    <span class="gc-oficina__etiqueta">Oficina del CCL que te corresponde</span>
                                                    <span id="modulo_delegacion" class="gc-oficina__nombre">Elige tu municipio</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- PASO 2 --}}
                                <fieldset class="gc-seccion" data-paso="2">
                                    <legend class="gc-titulo"><span class="gc-num">2</span> Tus datos</legend>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcNombre">Nombre completo <span class="gc-req">*</span></label>
                                            <input type="text" id="gcNombre" name="nombre" class="form-control gc-mayus" maxlength="100" autocomplete="name"
                                                   placeholder="Nombre(s) y apellidos" oninput="this.value = this.value.toUpperCase();" required>
                                            <div class="invalid-feedback">Escribe tu nombre completo.</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label" for="gcEdad">Edad <span class="gc-req">*</span></label>
                                            <input type="number" id="gcEdad" name="edad" class="form-control" inputmode="numeric" min="0" max="120" maxlength="3"
                                                   oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required>
                                            <div class="invalid-feedback">Escribe tu edad.</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label" for="gcSexo">Sexo <span class="gc-req">*</span></label>
                                            {{-- Los valores son los que acepta recepcion.sexo (H, M, NB, LGBTTTIQ).
                                                 Antes había "Otros", que la base rechazaba y la cita no se guardaba. --}}
                                            <select id="gcSexo" name="sexo" class="form-select" required>
                                                <option value="">Elige</option>
                                                <option value="H">Hombre</option>
                                                <option value="M">Mujer</option>
                                                <option value="NB">No binario</option>
                                                <option value="LGBTTTIQ">Otro (LGBTTTIQ+)</option>
                                            </select>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcCorreo">Correo electrónico <span class="gc-req">*</span></label>
                                            <input type="email" id="gcCorreo" name="email" class="form-control" maxlength="50" autocomplete="email"
                                                   placeholder="nombre@correo.com" required>
                                            <div class="form-text">Aquí te llegará tu acuse con el código QR.</div>
                                            <div class="invalid-feedback">Escribe un correo válido.</div>
                                        </div>
                                        <div class="col-md-6">
                                            {{-- Sin name: sólo sirve para evitar errores de dedo; no se envía. --}}
                                            <label class="form-label" for="gcCorreo2">Confirma tu correo <span class="gc-req">*</span></label>
                                            <input type="email" id="gcCorreo2" class="form-control" maxlength="50" autocomplete="off"
                                                   placeholder="Escríbelo otra vez" required>
                                            <div class="invalid-feedback" id="gcCorreo2Error">Los correos no coinciden.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcTelefono">Teléfono o celular <span class="gc-req">*</span></label>
                                            <input type="tel" id="gcTelefono" name="telefono" class="form-control" inputmode="numeric" autocomplete="tel-national"
                                                   maxlength="10" pattern="[0-9]{10}" placeholder="10 dígitos"
                                                   oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);" required>
                                            <div class="invalid-feedback">Escribe los 10 dígitos de tu teléfono.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="gcVulnerables">¿Perteneces a algún grupo de atención prioritaria? <span class="gc-req">*</span></label>
                                            <select id="gcVulnerables" name="vulnerables" class="form-select" required>
                                                <option value="">Elige una opción</option>
                                                <option value="No aplica">No, a ninguno</option>
                                                <option value="Menores de edad">Menores de edad</option>
                                                <option value="Adultos mayores">Adultos mayores</option>
                                                <option value="Personas con discapacidad">Personas con discapacidad</option>
                                                <option value="Población indígena">Población indígena</option>
                                                <option value="Personas Migrantes">Personas migrantes</option>
                                                <option value="LGBTTTIQ">LGBTTTIQ+</option>
                                            </select>
                                            <div class="form-text">Nos ayuda a darte la atención adecuada.</div>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- PASO 3 --}}
                                <fieldset class="gc-seccion" data-paso="3">
                                    <legend class="gc-titulo"><span class="gc-num">3</span> Tu caso</legend>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="gcTipoCaso">
                                                ¿Tu caso es por alguno de estos motivos? <span class="gc-req">*</span>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline" data-bs-toggle="modal" data-bs-target="#exampleModal">¿Por qué lo preguntamos?</button>
                                            </label>
                                            <select id="gcTipoCaso" name="tipo_caso" class="form-select" onchange="cambiaExcepcion(this); blockCalendar();" required>
                                                <option value="">Elige una opción</option>
                                                <option value="Ninguno" style="font-weight: bold;">No, ninguno de estos</option>
                                                <optgroup label="Discriminación en el empleo por:">
                                                    <option value="Acoso u hostigamiento sexual">Acoso u hostigamiento sexual</option>
                                                    <option value="Discriminación">Condición social</option>
                                                    <option value="Discriminación">Embarazo</option>
                                                    <option value="Discriminación">Raza</option>
                                                    <option value="Discriminación">Razones de sexo</option>
                                                    <option value="Discriminación">Religión</option>
                                                    <option value="Discriminación">Orientación sexual</option>
                                                    <option value="Discriminación">Origen étnico</option>
                                                </optgroup>
                                                <optgroup label="Designación de beneficiarios:">
                                                    <option value="Designacion">Designación de beneficiarios por muerte</option>
                                                </optgroup>
                                                <optgroup label="Prestaciones de seguridad social por:">
                                                    <option value="Prestaciones">Accidentes de trabajo</option>
                                                    <option value="Prestaciones">Enfermedades</option>
                                                    <option value="Prestaciones">Guarderías</option>
                                                    <option value="Prestaciones">Invalidez</option>
                                                    <option value="Prestaciones">Maternidad</option>
                                                    <option value="Prestaciones">Riesgos de trabajo</option>
                                                    <option value="Prestaciones">Prestaciones en especie</option>
                                                    <option value="Prestaciones">Vida</option>
                                                </optgroup>
                                                <optgroup label="Derechos fundamentales y libertades laborales:">
                                                    <option value="Libertades_publicas">Libertad de asociación</option>
                                                    <option value="Libertades_publicas">Libertad sindical</option>
                                                    <option value="Libertades_publicas">Reconocimiento efectivo de la negociación colectiva</option>
                                                    <option value="Libertades_publicas">Trabajo infantil</option>
                                                    <option value="Libertades_publicas">Trabajo forzoso u obligatorio</option>
                                                </optgroup>
                                                <optgroup label="Disputa de titularidad o estatutos sindicales:">
                                                    <option value="Titularidad">Contratos colectivos</option>
                                                    <option value="Titularidad">Contratos ley</option>
                                                    <option value="Titularidad">Impugnación de los estatutos de los sindicatos o su modificación</option>
                                                </optgroup>
                                            </select>
                                            <div class="form-text" id="gcAyudaCaso">Si no estás seguro, elige "No, ninguno de estos".</div>
                                            <div class="invalid-feedback">Elige una opción.</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="gcConflicto">Cuéntanos brevemente qué pasó <span class="gc-opcional">(opcional)</span></label>
                                            <textarea id="gcConflicto" name="conflicto" class="form-control gc-mayus" rows="3" maxlength="500"
                                                      placeholder="Por ejemplo: me despidieron sin pagarme el finiquito…" oninput="this.value = this.value.toUpperCase();"></textarea>
                                            <div class="form-text text-end"><span id="gcContador">0</span> / 500</div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- PASO 4 --}}
                                <fieldset class="gc-seccion" data-paso="4">
                                    <legend class="gc-titulo"><span class="gc-num">4</span> Elige día y hora</legend>
                                    <div class="gc-horario">
                                        <button type="button" id="botonCalendar" class="btn btn-guinda gc-horario__boton" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                            <i class="bi bi-calendar-week me-2" aria-hidden="true"></i><span id="gcTextoCalendario">Ver horarios disponibles</span>
                                        </button>
                                        <div class="form-text" id="gcAyudaCalendario">Primero elige el trámite y el municipio (paso 1).</div>
                                        <div id="resumenTurno" class="gc-turno" style="display:none;" aria-live="polite"></div>
                                        <div class="gc-error" id="gcErrorHorario" hidden>
                                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Elige el día y la hora de tu cita.
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="gc-enviar">
                                    <button id="btnGuardar" type="submit" class="btn btn-oro btn-lg gc-enviar__boton">
                                        <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Agendar mi cita
                                    </button>
                                    <p class="gc-enviar__nota">
                                        Revisa que tu correo esté bien escrito: ahí te llegará el acuse. El trámite es gratuito.
                                    </p>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">¿Por qué te preguntamos esto?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>En estos casos la Ley Federal del Trabajo (artículo 685 Ter) <strong>no te obliga a pasar por la conciliación</strong>, y te atenderá personal especializado:</p>
                            <ul class="mb-2">
                                <li>Discriminación en el empleo, acoso u hostigamiento sexual.</li>
                                <li>Designación de beneficiarios por muerte.</li>
                                <li>Prestaciones de seguridad social.</li>
                                <li>Libertad de asociación y sindical, negociación colectiva, trabajo infantil o forzoso.</li>
                                <li>Disputas por contratos colectivos o estatutos sindicales.</li>
                            </ul>
                            <p class="mb-0">Si tu caso no es ninguno de estos o no estás seguro, elige <strong>"No, ninguno de estos"</strong>.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            {{--
                Elegir día y hora. Antes era FullCalendar con vistas de semana/lista;
                ahora es una tira de días con cuántos horarios libres tiene cada uno
                y abajo sólo las horas libres de ese día. Mismos datos
                (/api/obtenerTurnosDisponibles) y el mismo botón #confirmarTurno.
            --}}
            <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="hcTitulo" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg hc-dialogo">
                    <div class="modal-content">
                        <div class="modal-header card-header-guinda py-3">
                            <div>
                                <h5 class="modal-title text-white mb-0" id="hcTitulo"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>Elige día y hora</h5>
                                <span class="hc-contexto" id="hcContexto"></span>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body hc-cuerpo">
                            <div class="hc-semana">
                                <button type="button" class="hc-flecha" id="hcAnterior" aria-label="Semana anterior"><i class="bi bi-chevron-left"></i></button>
                                <span class="hc-semana__texto" id="hcSemana" aria-live="polite"></span>
                                <button type="button" class="hc-flecha" id="hcSiguiente" aria-label="Semana siguiente"><i class="bi bi-chevron-right"></i></button>
                            </div>

                            <div class="hc-dias" id="hcDias" role="tablist" aria-label="Días de la semana"></div>

                            <div class="hc-horas" id="hcHoras" aria-live="polite"></div>
                        </div>

                        <div class="modal-footer hc-pie">
                            <div class="hc-elegido" id="hcElegido">Toca un horario para elegirlo.</div>
                            <div class="hc-pie__botones">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-oro" id="confirmarTurno" disabled>
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>Confirmar horario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Modal de Aviso -->
<div class="modal fade" id="avisoModal" tabindex="-1" aria-labelledby="avisoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            
            <div class="modal-body">
                    
                <div class="row">

                   <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <h5 class="text-center modal-titulo" >¡ATENCIÓN!</h5>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group" >
                          
                            <p class="text-center">Todos los servicios que brinda este Centro de Conciliación Laboral son completamente gratuitos<br><br>
                            Ningún servidor público, asociación, sindicato o gestor particular está autorizado para solicitar dinero o gratificaciones para agendar una cita, iniciar una solicitud o realizar tus trámites de conciliación.<br><br>
                            Si detectas o eres víctima de cualquier cobro indebido, ¡denúncialo inmediatamente por nuestros medios oficiales!<br><br>
                            </p>
                            <p class="aviso-engano">¡No te dejes engañar!</p>
                            <p class="text-center mb-0">Proteger tus derechos laborales es nuestra prioridad.</p>

                            {{-- Línea de denuncia. En celular el número se puede tocar para llamar. --}}
                            <div class="aviso-denuncia">
                                <span class="aviso-denuncia__titulo">Centro de Atención Telefónica</span>
                                <a class="aviso-denuncia__numero" href="tel:070" aria-label="Llamar al 070, opción 1">070</a>
                                <span class="aviso-denuncia__opcion">Opción 1</span>
                                <span class="aviso-denuncia__dependencia">Secretaría de Contraloría del Estado de Michoacán de Ocampo</span>
                                <a class="aviso-buzon" href="https://intra.secoem.michoacan.gob.mx/denuncias" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-mailbox2" aria-hidden="true"></i> Denuncia en el Buzón Naranja
                                </a>
                            </div>
                         
                    
                        </div>
                    </div>
                
                </div> 
                    
        
            </div> 
            
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            
        </div>
    </div>
</div>

   
    <div class="modal fade" id="seguridadModal" tabindex="-1" aria-labelledby="seguridadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

            
                <div class="modal-body">
                    
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <h5 class="text-center modal-titulo" > <i class="bi bi-shield-lock me-2"></i>Aviso de Privacidad</h5>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <p>Los Datos Personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, servirán únicamente para realizar el Procedimiento de Conciliación Individual Prejudicial, 
                            serán tratados conforme lo dispuesto por la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                            <p>Los datos considerados sensibles no serán recabados, ni tratados, ni se realizaran transferencias de datos personales, salvo aquellos que no requieran el consentimiento de los titulares y que 
                                sean necesarios para atender su solicitud o requerimientos de información realizados por autoridad competente, siempre y cuando se encuentren debidamente fundados y motivados; lo anterior, de 
                                conformidad con los artículos 22, 66 y 70 de la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.</p>
                            <p>Los datos personales que sean recabados serán empleados con los fines siguientes:</p>
                            <ul>
                                <li>Registrar al usuario en la plataforma digital denominada SICONCILIO (Sistema Integral para la Conciliación), dar seguimiento y trámite a su solicitud.</li><br>
                                <li>Administrar la información y datos del solicitante para efectuar el Procedimiento de Conciliación Prejudicial obligatorio y las notificaciones que deriven del mismo.</li><br>
                                <li>Generar información estadística y de control, en la que sus datos personales serán disociados de la información estadística para que no sea posible identificar a los titulares.</li><br>
                                <li>Establecer comunicación con los trabajadores y patrones por correo electrónico, por escrito, mediante correo ordinario o por teléfono, sobre aspectos relacionados con las 
                                    fases y etapas del procedimiento de conciliación individual.</li>
                            </ul>
                        </div>
                    </div>
                </div> 
                    
            
            </div> 
            
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aceptar</button>
            </div>

            </div>
        </div>
    </div>

        </section>
        
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const avisoModalEl = document.getElementById('avisoModal');
        const seguridadModalEl = document.getElementById('seguridadModal');

        const avisoModal = bootstrap.Modal.getOrCreateInstance(avisoModalEl);
        const seguridadModal = bootstrap.Modal.getOrCreateInstance(seguridadModalEl);

        // Mostrar el primer modal
        avisoModal.show();

        avisoModalEl.addEventListener('hidden.bs.modal', function () {
            seguridadModal.show();
        });

        seguridadModalEl.addEventListener('hidden.bs.modal', function () {

            document.body.classList.remove('modal-open');

            document.body.style.removeProperty('padding-right');

            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop){
                backdrop.remove();
            });

        });

    });
</script>
        

    <script>
        

        function cambiaExcepcion(elemento) {
            var valor = elemento.value;
            var inputExcepcion = document.getElementById('excepcion');
    
            if (inputExcepcion) {
                inputExcepcion.value = (valor === 'Ninguno') ? 'No' : 'Si';
            }
        }

        function blockCalendar() {
            const delegacion = document.getElementById('delegacion').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            const boton = document.getElementById("botonCalendar");

            if (delegacion !== "" && tipo !== "") {
                boton.removeAttribute("disabled");
            } else {
                boton.disabled = true;
            }

            limpiarTurnoSeleccionado();
        }

        function limpiarTurnoSeleccionado() {
            const habia = document.getElementById("fecha_turno").value;
            turnoSeleccionado = null;
            document.getElementById("fecha_turno").value = "";
            document.getElementById("hora_turno").value = "";
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");

            // Si ya había un horario, se avisa que hay que volver a elegirlo: cambió
            // un dato (trámite, municipio o caso) que define qué horarios hay.
            const resumen = document.getElementById("resumenTurno");
            if (habia) {
                resumen.className = 'gc-aviso-cambio';
                resumen.textContent = 'Cambiaste un dato que define los horarios. Vuelve a elegir día y hora.';
                resumen.style.display = "block";
            } else if (resumen.className !== 'gc-aviso-cambio') {
                resumen.style.display = "none";
            }
            const texto = document.getElementById('gcTextoCalendario');
            if (texto) texto.textContent = 'Ver horarios disponibles';
            if (window.gcActualizarPasos) window.gcActualizarPasos();
        }

        function pad(numero) {
            return String(numero).padStart(2, "0");
        }

        function formatoFechaLocal(fecha) {
            return fecha.getFullYear() + "-" + pad(fecha.getMonth() + 1) + "-" + pad(fecha.getDate());
        }

        function formatoHoraLocal(fecha) {
            return pad(fecha.getHours()) + ":" + pad(fecha.getMinutes()) + ":" + pad(fecha.getSeconds());
        }

        // ---------- Selector de día y hora ----------
        // turnoSeleccionado conserva la forma { start: Date } que usan
        // "Confirmar" y limpiarTurnoSeleccionado().
        let turnoSeleccionado = null;

        const hc = (function () {
            const URL_HORARIOS = "{{ url('/api/obtenerTurnosDisponibles') }}";
            const SEMANAS_A_BUSCAR = 8;        // al abrir, busca la primera semana con lugar
            const el = {
                semana: document.getElementById('hcSemana'),
                anterior: document.getElementById('hcAnterior'),
                siguiente: document.getElementById('hcSiguiente'),
                dias: document.getElementById('hcDias'),
                horas: document.getElementById('hcHoras'),
                elegido: document.getElementById('hcElegido'),
                contexto: document.getElementById('hcContexto'),
                confirmar: document.getElementById('confirmarTurno')
            };
            let lunes = null;           // lunes de la semana que se ve
            let lunesMinimo = null;     // no se puede ir antes de esta semana
            let porDia = {};            // 'YYYY-MM-DD' -> ['09:20', ...] (sólo libres)
            let diaActivo = null;
            let peticion = 0;           // descarta respuestas viejas si se navega rápido

            const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            const lunesDe = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); const w = (x.getDay() + 6) % 7; x.setDate(x.getDate() - w); return x; };
            const masDias = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
            const fmt = (d, o) => d.toLocaleDateString('es-MX', o);
            const nodo = (tag, clase, texto) => { const n = document.createElement(tag); if (clase) n.className = clase; if (texto != null) n.textContent = texto; return n; };

            function parametros() {
                return {
                    sede: document.getElementById('delegacion').value,
                    tipo: document.querySelector('select[name="tipo"]').value,
                    excepcion: document.getElementById('excepcion').value
                };
            }

            async function traerSemana(inicio) {
                const p = parametros();
                const q = new URLSearchParams({ sede: p.sede, tipo: p.tipo, excepcion: p.excepcion, externo: 1, start: ymd(inicio), end: ymd(masDias(inicio, 4)) });
                const r = await fetch(URL_HORARIOS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const eventos = await r.json();
                const dias = {};
                for (let i = 0; i < 5; i++) dias[ymd(masDias(inicio, i))] = [];
                eventos.forEach(function (e) {
                    const estado = e.extendedProps && e.extendedProps.estado;
                    if (estado !== 'disponible' && estado !== 'turnos') return;
                    const [f, h] = String(e.start).split('T');
                    if (dias[f]) dias[f].push(h.slice(0, 5));
                });
                Object.keys(dias).forEach(k => dias[k].sort());
                return dias;
            }

            function mensaje(icono, texto, accion) {
                el.horas.textContent = '';
                const m = nodo('div', 'hc-mensaje');
                m.appendChild(nodo('i', 'bi ' + icono));
                m.appendChild(nodo('div', null, texto));
                if (accion) {
                    const b = nodo('button', 'btn btn-outline-secondary btn-sm', accion.texto);
                    b.type = 'button';
                    b.addEventListener('click', accion.fn);
                    m.appendChild(b);
                }
                el.horas.appendChild(m);
            }

            function pintarSemana() {
                const viernes = masDias(lunes, 4);
                const mismoMes = lunes.getMonth() === viernes.getMonth();
                el.semana.textContent = mismoMes
                    ? lunes.getDate() + ' al ' + viernes.getDate() + ' de ' + fmt(viernes, { month: 'long', year: 'numeric' })
                    : fmt(lunes, { day: 'numeric', month: 'short' }) + ' al ' + fmt(viernes, { day: 'numeric', month: 'short', year: 'numeric' });
                el.anterior.disabled = lunes <= lunesMinimo;

                el.dias.textContent = '';
                Object.keys(porDia).forEach(function (f) {
                    const d = new Date(f + 'T00:00:00');
                    const libres = porDia[f].length;
                    const b = nodo('button', 'hc-dia' + (f === diaActivo ? ' is-activo' : ''));
                    b.type = 'button';
                    b.setAttribute('role', 'tab');
                    b.setAttribute('aria-selected', f === diaActivo ? 'true' : 'false');
                    b.disabled = libres === 0;
                    b.appendChild(nodo('span', 'hc-dia__semana', fmt(d, { weekday: 'short' }).replace('.', '')));
                    b.appendChild(nodo('span', 'hc-dia__numero', String(d.getDate())));
                    b.appendChild(nodo('span', 'hc-dia__mes', fmt(d, { month: 'short' }).replace('.', '')));
                    b.appendChild(nodo('span', 'hc-dia__libres', libres ? (libres === 1 ? '1 horario' : libres + ' horarios') : 'Sin lugar'));
                    b.setAttribute('aria-label', fmt(d, { weekday: 'long', day: 'numeric', month: 'long' }) + ', ' + (libres ? libres + ' horarios libres' : 'sin horarios'));
                    b.addEventListener('click', function () { diaActivo = f; pintarSemana(); pintarHoras(); });
                    el.dias.appendChild(b);
                });
                const activo = el.dias.querySelector('.is-activo');
                if (activo && activo.scrollIntoView) activo.scrollIntoView({ block: 'nearest', inline: 'center' });
            }

            function pintarHoras() {
                if (!diaActivo) {
                    mensaje('bi-calendar-x', 'No hay horarios libres esta semana.', { texto: 'Ver la semana siguiente', fn: () => irA(masDias(lunes, 7)) });
                    return;
                }
                el.horas.textContent = '';
                const horas = porDia[diaActivo];
                const grupos = [['Mañana', horas.filter(h => h < '13:00')], ['Tarde', horas.filter(h => h >= '13:00')]];
                grupos.forEach(function ([titulo, lista]) {
                    if (!lista.length) return;
                    const g = nodo('div', 'hc-grupo');
                    g.appendChild(nodo('div', 'hc-grupo__titulo', titulo));
                    const cont = nodo('div', 'hc-grupo__horas');
                    lista.forEach(function (h) {
                        const b = nodo('button', 'hc-hora', h);
                        b.type = 'button';
                        const elegido = turnoSeleccionado && ymd(turnoSeleccionado.start) === diaActivo && formatoHoraLocal(turnoSeleccionado.start).slice(0, 5) === h;
                        if (elegido) b.classList.add('is-elegida');
                        b.setAttribute('aria-pressed', elegido ? 'true' : 'false');
                        b.addEventListener('click', function () { elegir(diaActivo, h); });
                        cont.appendChild(b);
                    });
                    g.appendChild(cont);
                    el.horas.appendChild(g);
                });
            }

            function elegir(fecha, hora) {
                turnoSeleccionado = { start: new Date(fecha + 'T' + hora + ':00') };
                const d = turnoSeleccionado.start;
                const texto = fmt(d, { weekday: 'long', day: 'numeric', month: 'long' }).replace(',', '');
                el.elegido.textContent = '';
                el.elegido.appendChild(document.createTextNode('Elegiste: '));
                el.elegido.appendChild(nodo('strong', null, texto.charAt(0).toUpperCase() + texto.slice(1) + ' · ' + hora + ' h'));
                el.confirmar.removeAttribute('disabled');
                pintarHoras();
            }

            async function irA(inicio, buscarHaciaAdelante, diaPreferido) {
                const mia = ++peticion;
                lunes = inicio;
                diaActivo = null;
                el.dias.textContent = '';
                el.semana.textContent = 'Buscando horarios…';
                mensaje('bi-hourglass-split', 'Buscando horarios disponibles…');
                try {
                    let intentos = buscarHaciaAdelante ? SEMANAS_A_BUSCAR : 1;
                    let dias = await traerSemana(lunes);
                    while (--intentos > 0 && !Object.values(dias).some(h => h.length)) {
                        lunes = masDias(lunes, 7);
                        dias = await traerSemana(lunes);
                        if (mia !== peticion) return;
                    }
                    if (mia !== peticion) return;
                    porDia = dias;
                    diaActivo = (diaPreferido && porDia[diaPreferido] && porDia[diaPreferido].length)
                        ? diaPreferido
                        : (Object.keys(porDia).find(f => porDia[f].length) || null);
                    pintarSemana();
                    pintarHoras();
                } catch (e) {
                    if (mia !== peticion) return;
                    el.semana.textContent = '';
                    mensaje('bi-wifi-off', 'No pudimos cargar los horarios. Revisa tu conexión.', { texto: 'Intentar de nuevo', fn: () => irA(lunes) });
                }
            }

            el.anterior.addEventListener('click', () => irA(masDias(lunes, -7)));
            el.siguiente.addEventListener('click', () => irA(masDias(lunes, 7)));

            return {
                abrir: function () {
                    turnoSeleccionado = null;
                    el.confirmar.setAttribute('disabled', 'disabled');
                    el.elegido.textContent = 'Toca un horario para elegirlo.';
                    const p = parametros();
                    const tipoTexto = document.querySelector('select[name="tipo"]').selectedOptions[0];
                    el.contexto.textContent = 'Oficina ' + p.sede + ' · ' + (tipoTexto ? tipoTexto.textContent.trim() : p.tipo);
                    // Sábado o domingo: la primera semana útil es la siguiente.
                    const hoy = new Date();
                    lunesMinimo = lunesDe(hoy.getDay() === 0 || hoy.getDay() === 6 ? masDias(hoy, 2) : hoy);

                    // Si ya había un horario confirmado, se abre en ese día y con esa hora marcada.
                    const f = document.getElementById('fecha_turno').value;
                    const h = (document.getElementById('hora_turno').value || '').slice(0, 5);
                    if (f && h) {
                        elegir(f, h);
                        const semana = lunesDe(new Date(f + 'T00:00:00'));
                        irA(semana < lunesMinimo ? lunesMinimo : semana, false, f);
                    } else {
                        irA(lunesMinimo, true);
                    }
                }
            };
        })();

        document.getElementById("botonCalendar").addEventListener("click", function () {
            hc.abrir();
        });

        document.getElementById("confirmarTurno").addEventListener("click", function () {
            if (!turnoSeleccionado) {
                alert("Por favor selecciona un horario disponible.");
                return;
            }

            const inicio = turnoSeleccionado.start;
            document.getElementById("fecha_turno").value = formatoFechaLocal(inicio);
            document.getElementById("hora_turno").value = formatoHoraLocal(inicio);

            const resumen = document.getElementById("resumenTurno");
            const dia = inicio.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).replace(',', '');
            resumen.textContent = '';
            const icono = document.createElement('i');
            icono.className = 'bi bi-calendar-check-fill';
            const texto = document.createElement('div');
            const fuerte = document.createElement('strong');
            fuerte.textContent = dia.charAt(0).toUpperCase() + dia.slice(1) + ' · ' + pad(inicio.getHours()) + ':' + pad(inicio.getMinutes()) + ' h';
            texto.appendChild(fuerte);
            texto.appendChild(document.createTextNode('Se apartará cuando presiones "Agendar mi cita". Si quieres otro, vuelve a abrir los horarios.'));
            resumen.appendChild(icono);
            resumen.appendChild(texto);
            resumen.style.display = "flex";
            document.getElementById('gcTextoCalendario').textContent = 'Cambiar día u hora';
            document.getElementById('gcErrorHorario').hidden = true;
            if (window.gcActualizarPasos) window.gcActualizarPasos();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarModal')).hide();
        });

        document.getElementById("form_roles").addEventListener("submit", function (e) {
            const form = this;
            const btn = document.getElementById('btnGuardar');
            if (window.gcValidarCorreos) window.gcValidarCorreos();

            const sinHorario = !document.getElementById("fecha_turno").value || !document.getElementById("hora_turno").value;
            document.getElementById('gcErrorHorario').hidden = !sinHorario;

            if (!form.checkValidity() || sinHorario) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
                // Lleva a la persona al primer dato que falta.
                const primero = form.querySelector(':invalid:not(fieldset)') || (sinHorario ? document.getElementById('botonCalendar') : null);
                if (primero) {
                    primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(function () { try { primero.focus({ preventScroll: true }); } catch (err) {} }, 350);
                }
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Agendando tu cita…';
        });

        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('municipio_citado');
            const delegacionModulo = document.getElementById('modulo_delegacion');

            const delegaciones = {
                1: ['Morelia'],
                2: ['Zitácuaro'],
                3: ['Uruapan'],
                4: ['Lázaro Cárdenas'],
                5: ['Zamora'],
                6: ['Sahuayo']
            };

            municipioSelect.addEventListener('change', function () {
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                const delegacionId = selectedOption.getAttribute('data-delegacion-id');

                // Limpia el select de delegación
                delegacionSelect.innerHTML = '<option value="">Seleccione</option>';

                if (delegacionId && delegaciones[delegacionId]) {
                    delegaciones[delegacionId].forEach(delegacion => {
                        delegacionSelect.value = delegacion;
                        delegacionModulo.textContent = delegacion;
                    });
                }

                blockCalendar();
            });
        });

          
    </script>


    <script>
        $(document).ready(function() {
            

            $('#tipo').on('change', function() {
                var tipoSeleccionado = $(this).val();
                
                if (tipoSeleccionado === 'Ratificación') {

                    $('#si_ratificacion').fadeIn();
                } else {

                    $('#si_ratificacion').fadeOut();
                    $('#folio_patronal').val(''); 
                }
            });

            // 2. Detectar cuando cambia el select de "folio patronal"
            $('#folio_patronal').on('change', function() {
                var tieneFolio = $(this).val();
                
                if (tieneFolio === 'si') {
                    window.location.href = '{{ route('create_cita') }}'; 
                }
            });

        });
    </script>
    {{-- Ayudas del formulario: explicación del trámite, oficina, avance por pasos,
         confirmación del correo, contador del motivo y horario que caduca si
         cambia algo que lo determina. --}}
    <script>
    (function () {
        'use strict';
        var form = document.getElementById('form_roles');
        if (!form) return;

        var AYUDA_TRAMITE = {
            'Asesoría':     'Te orientamos sobre tus derechos laborales y qué puedes hacer en tu caso.',
            'Solicitud':    'Inicias la conciliación: citamos a tu patrón (o a la otra parte) para buscar un acuerdo sin ir a juicio.',
            'Ratificación': 'Ya tienen un acuerdo (por ejemplo, un finiquito) y vienen a firmarlo ante el Centro para que tenga validez.'
        };

        var tipo = document.getElementById('tipo');
        var ayudaTramite = document.getElementById('gcAyudaTramite');
        var municipio = document.getElementById('municipio_citado');
        var oficina = document.getElementById('gcOficina');
        var oficinaNombre = document.getElementById('modulo_delegacion');
        var delegacion = document.getElementById('delegacion');
        var tipoCaso = document.getElementById('gcTipoCaso');
        var ayudaCaso = document.getElementById('gcAyudaCaso');
        var correo = document.getElementById('gcCorreo');
        var correo2 = document.getElementById('gcCorreo2');
        var conflicto = document.getElementById('gcConflicto');
        var contador = document.getElementById('gcContador');
        var ayudaCal = document.getElementById('gcAyudaCalendario');
        var fecha = document.getElementById('fecha_turno');
        var hora = document.getElementById('hora_turno');
        var resumen = document.getElementById('resumenTurno');

        // Paso 1
        function pintarTramite() {
            var t = AYUDA_TRAMITE[tipo.value];
            ayudaTramite.textContent = t || 'Elige una opción y te explicamos de qué se trata.';
            ayudaTramite.classList.toggle('is-activa', !!t);
        }
        function pintarOficina() {
            // El listener original pone la sede en el campo oculto; aquí sólo se refleja.
            var lista = !!delegacion.value && !!municipio.value;
            oficina.classList.toggle('is-lista', lista);
            if (!lista) oficinaNombre.textContent = 'Elige tu municipio';
        }

        // Paso 3
        function pintarCaso() {
            if (!tipoCaso.value) {
                ayudaCaso.textContent = 'Si no estás seguro, elige "No, ninguno de estos".';
            } else if (tipoCaso.value === 'Ninguno') {
                ayudaCaso.textContent = 'Tu cita será con un auxiliar de conciliación.';
            } else {
                ayudaCaso.textContent = 'Por tu caso te atenderá personal especializado. Los horarios disponibles pueden ser distintos.';
            }
        }
        function contar() { contador.textContent = conflicto.value.length; }

        // Correo
        function validarCorreos() {
            var distinto = correo2.value && correo.value.trim().toLowerCase() !== correo2.value.trim().toLowerCase();
            correo2.setCustomValidity(distinto ? 'Los correos no coinciden.' : '');
        }
        window.gcValidarCorreos = validarCorreos;

        function pintarCalendario() {
            var listo = !!tipo.value && !!delegacion.value;
            ayudaCal.textContent = listo
                ? 'Los horarios en verde están libres. Toca uno y luego "Confirmar".'
                : 'Primero elige el trámite y el municipio (paso 1).';
        }

        // Avance por pasos
        function pasoCompleto(n) {
            if (n === 4) return !!fecha.value && !!hora.value;
            var campos = form.querySelectorAll('fieldset[data-paso="' + n + '"] [required]');
            return Array.prototype.every.call(campos, function (c) { return c.checkValidity(); });
        }
        function actualizarPasos() {
            document.querySelectorAll('#gcPasos li').forEach(function (li) {
                li.classList.toggle('is-listo', pasoCompleto(parseInt(li.getAttribute('data-paso'), 10)));
            });
        }
        window.gcActualizarPasos = actualizarPasos;

        // Restaura la clase del resumen cuando se confirma un horario nuevo.
        var confirmar = document.getElementById('confirmarTurno');
        if (confirmar) confirmar.addEventListener('click', function () { resumen.className = 'gc-turno'; }, true);

        tipo.addEventListener('change', function () { pintarTramite(); pintarCalendario(); });
        municipio.addEventListener('change', function () {
            // Si se regresa a "Elige el municipio", la sede anterior no debe quedarse.
            if (!municipio.value) { delegacion.value = ''; blockCalendar(); }
            setTimeout(function () { pintarOficina(); pintarCalendario(); }, 0);
        });
        tipoCaso.addEventListener('change', pintarCaso);
        correo.addEventListener('input', validarCorreos);
        correo2.addEventListener('input', validarCorreos);
        conflicto.addEventListener('input', contar);
        form.addEventListener('input', actualizarPasos);
        form.addEventListener('change', actualizarPasos);

        pintarTramite(); pintarOficina(); pintarCaso(); contar(); pintarCalendario(); actualizarPasos();
    })();
    </script>
@section('scripts')
    <script src="{{ asset('assets/js/validaciones-ratificacion.js') }}"></script> 
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
@endsection

    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nicescroll.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/profile.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')