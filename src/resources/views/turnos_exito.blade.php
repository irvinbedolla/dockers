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
        .fc-event-selected {
            border: 3px solid var(--color-oro) !important;
            box-shadow: 0 0 10px rgba(206, 168, 69, .8);
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
        }

        /* Títulos de modal */
        .modal-titulo {
            background-color: var(--color-guinda);
            color: #fff;
            padding: 10px;
            font-size: 20px;
        }

        /* Loader */
        .loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: url("{{ asset('assets/images/pageLoader.gif') }}")
                50% 50% no-repeat rgba(255, 255, 255, .85);
        }
        


    </style>
    {{-- Confirmación de cita: boleto, acciones, qué llevar e impresión. --}}
    <style>
        .cx { max-width: 820px; }
        .cx-hero { text-align: center; margin: 8px auto 22px; }
        .cx-hero__icono {
            width: 72px; height: 72px; margin: 0 auto 12px; border-radius: 50%;
            display: grid; place-items: center; background: #1E7B3C; color: #fff; font-size: 40px;
            box-shadow: 0 8px 22px rgba(30, 123, 60, .3);
        }
        .cx-hero__titulo { font-size: clamp(24px, 4vw, 32px); font-weight: 800; color: #2E3C3D; margin: 0 0 8px; }
        .cx-hero__texto { max-width: 560px; margin: 0 auto; color: #4A5859; font-size: 15.5px; }

        .cx-boleto {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(46, 60, 61, .1);
            overflow: hidden;
            margin-bottom: 14px;
        }
        .cx-boleto__folio {
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 20px 10px; background: var(--color-guinda); color: #fff;
            border-right: 3px dashed rgba(255, 255, 255, .45);
        }
        .cx-boleto__folio .cx-etiqueta { color: rgba(255, 255, 255, .8); }
        .cx-folio { font-size: 25px; font-weight: 800; letter-spacing: .03em; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .cx-boleto__datos {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px; padding: 20px 22px;
        }
        .cx-dato { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
        .cx-dato > i { font-size: 22px; color: var(--color-oro); line-height: 1.2; }
        .cx-dato--ancho { grid-column: 1 / -1; }
        .cx-etiqueta { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8A9899; }
        .cx-valor { display: block; font-size: 17px; font-weight: 700; color: #2E3C3D; word-break: break-word; }
        .cx-dato--grande .cx-valor { font-size: 20px; }
        .cx-valor--normal { font-weight: 500; font-size: 15px; }
        .cx-mapa { display: inline-flex; gap: 6px; align-items: center; margin-top: 4px; font-size: 14px; font-weight: 600; color: var(--color-guinda); }

        .cx-acciones { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin: 4px 0 20px; }
        .cx-btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 10px; font-weight: 600; }
        .cx-btn--oro { background: var(--color-oro); color: #fff; border: 0; }
        .cx-btn--oro:hover, .cx-btn--oro:focus { background: var(--color-oro-dark); color: #fff; }
        .cx-btn--linea { border: 2px solid var(--color-guinda); color: var(--color-guinda); background: #fff; }
        .cx-btn--linea:hover, .cx-btn--linea:focus { background: #F2F6F6; color: var(--color-guinda); }

        .cx-tarjeta { background: #fff; border: 1px solid #E3E8E8; border-radius: 14px; padding: 18px 20px; margin-bottom: 14px; color: #3A4849; }
        .cx-tarjeta--aviso { background: #FBF4E1; border-color: #F0DFAE; color: #5B4510; }
        .cx-subtitulo { display: flex; align-items: center; gap: 8px; font-size: 17px; font-weight: 700; margin: 0 0 10px; color: inherit; }
        .cx-tarjeta:not(.cx-tarjeta--aviso) .cx-subtitulo { color: var(--color-guinda); }
        .cx-lista { list-style: none; padding: 0; margin: 0 0 12px; }
        .cx-lista li { display: flex; gap: 10px; padding: 7px 0; border-top: 1px solid #F0F3F3; font-size: 15px; }
        .cx-lista li:first-child { border-top: 0; }
        .cx-lista i { color: #1E7B3C; font-size: 18px; line-height: 1.3; }
        .cx-detalle summary { cursor: pointer; font-weight: 600; color: var(--color-guinda); padding: 6px 0; }
        .cx-detalle__cuerpo { padding: 8px 0 2px; font-size: 14.5px; }

        .cx-cierre { text-align: center; color: #4A5859; margin: 18px 0 12px; }
        .cx-otra { text-align: center; }

        @media (max-width: 576px) {
            .cx-boleto { grid-template-columns: 1fr; }
            .cx-boleto__folio { flex-direction: row; gap: 12px; border-right: 0; border-bottom: 3px dashed rgba(255, 255, 255, .45); padding: 14px; }
            .cx-folio { font-size: 24px; }
            .cx-boleto__datos { grid-template-columns: 1fr; padding: 18px; }
            .cx-acciones .cx-btn { flex: 1 1 100%; justify-content: center; }
        }

        /* Impresión: sólo lo de la cita, sin barra ni botones. */
        @media print {
            body { padding-top: 0 !important; background: #fff !important; }
            .navbar, .cx-acciones, .cx-otra, .cx-detalle summary { display: none !important; }
            .cx-detalle__cuerpo { display: block !important; }
            .cx-boleto, .cx-tarjeta { box-shadow: none; break-inside: avoid; }
            .cx-boleto__folio { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
    {{--
        Confirmación de cita en línea. Los datos llegan en la sesión desde
        HomeController::turnos_publico (folio, fecha, hora, delegacion, modulo,
        direccion). Si se recarga la página, citas_exito regresa al formulario.
    --}}
    @php
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Str;

        $fechaCita = session('fecha') ? Carbon::parse(session('fecha')) : null;
        $diaSemana = $fechaCita ? Str::ucfirst($fechaCita->locale('es')->isoFormat('dddd')) : '';
        $fechaLarga = $fechaCita ? $fechaCita->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : session('fecha');
        $hora = session('hora');
        $folio = (string) session('folio');
        // Folio nuevo (MOR-S26-0142) tal cual; uno numérico viejo, con ceros.
        if (ctype_digit($folio)) {
            $folio = str_pad($folio, 5, '0', STR_PAD_LEFT);
        }
        $direccion = session('direccion');
        // La dirección viene en mayúsculas; se pasa a formato normal cuidando abreviaturas y preposiciones.
        $direccionLegible = $direccion
            ? strtr(Str::title(mb_strtolower($direccion)), [' De ' => ' de ', ' Del ' => ' del ', ' La ' => ' la ', 'C.p.' => 'C.P.', 'No. ' => 'No. '])
            : '';
        $modulo = str_replace('Modulo', 'Módulo', (string) session('modulo'));
        $mapa = $direccion ? 'https://www.google.com/maps/search/?api=1&query='.urlencode('Centro de Conciliación Laboral '.$direccion) : null;
        // "Agregar a Google Calendar": enlace de plantilla de Google (no usa API ni
        // credenciales). Abre Google Calendar con el evento lleno; la persona sólo guarda.
        $googleCalendar = null;
        if ($fechaCita && $hora) {
            $inicio = Carbon::parse(session('fecha').' '.substr($hora, 0, 5));
            $googleCalendar = 'https://calendar.google.com/calendar/render?'.http_build_query([
                'action'   => 'TEMPLATE',
                'text'     => 'Cita en el Centro de Conciliación Laboral (folio '.$folio.')',
                'dates'    => $inicio->format('Ymd\THis').'/'.$inicio->copy()->addMinutes(40)->format('Ymd\THis'),
                'ctz'      => 'America/Mexico_City',
                'location' => $direccionLegible,
                'details'  => $modulo.'. Lleva tu identificación oficial y tu acuse con código QR. Tolerancia: 5 minutos.',
            ], '', '&', PHP_QUERY_RFC3986);
        }
    @endphp

    <main class="container mb-5 cx">
        <div class="cx-hero">
            <span class="cx-hero__icono" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
            <h1 class="cx-hero__titulo">¡Tu cita quedó agendada!</h1>
            <p class="cx-hero__texto">
                Te enviamos a tu correo el <strong>acuse con código QR</strong>. Preséntalo el día de tu cita
                (impreso o en tu celular). Si no lo ves, revisa tu carpeta de correo no deseado.
            </p>
        </div>

        <section class="cx-boleto" aria-label="Datos de tu cita">
            <div class="cx-boleto__folio">
                <span class="cx-etiqueta">Folio</span>
                <span class="cx-folio">{{ $folio }}</span>
            </div>
            <div class="cx-boleto__datos">
                <div class="cx-dato cx-dato--grande">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    <div>
                        <span class="cx-etiqueta">Día</span>
                        <span class="cx-valor">{{ $diaSemana }} {{ $fechaLarga }}</span>
                    </div>
                </div>
                <div class="cx-dato cx-dato--grande">
                    <i class="bi bi-clock" aria-hidden="true"></i>
                    <div>
                        <span class="cx-etiqueta">Hora</span>
                        <span class="cx-valor">{{ $hora }} h</span>
                    </div>
                </div>
                <div class="cx-dato">
                    <i class="bi bi-building" aria-hidden="true"></i>
                    <div>
                        <span class="cx-etiqueta">Oficina</span>
                        <span class="cx-valor">{{ session('delegacion') }}</span>
                    </div>
                </div>
                <div class="cx-dato">
                    <i class="bi bi-door-open" aria-hidden="true"></i>
                    <div>
                        <span class="cx-etiqueta">Módulo</span>
                        <span class="cx-valor">{{ $modulo }}</span>
                    </div>
                </div>
                <div class="cx-dato cx-dato--ancho">
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <div>
                        <span class="cx-etiqueta">Dirección</span>
                        <span class="cx-valor cx-valor--normal">{{ $direccionLegible }}</span>
                        @if ($mapa)
                            <a class="cx-mapa" href="{{ $mapa }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-map" aria-hidden="true"></i> Ver en el mapa
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if ($googleCalendar)
            <div class="cx-acciones">
                <a class="btn cx-btn cx-btn--linea" href="{{ $googleCalendar }}" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-calendar-plus" aria-hidden="true"></i> Agregar a Google Calendar
                </a>
            </div>
        @endif

        <section class="cx-tarjeta cx-tarjeta--aviso">
            <h2 class="cx-subtitulo"><i class="bi bi-alarm" aria-hidden="true"></i> Llega con tiempo</h2>
            <p class="mb-0">
                Te recomendamos llegar <strong>10 minutos antes</strong>. Si llegas <strong>más de 5 minutos tarde</strong>,
                tu cita se cancela y tendrás que agendar otra.
            </p>
        </section>

        <section class="cx-tarjeta">
            <h2 class="cx-subtitulo"><i class="bi bi-card-checklist" aria-hidden="true"></i> Qué llevar</h2>
            <ul class="cx-lista">
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span><strong>Identificación oficial vigente</strong> (INE, pasaporte o cédula profesional).</span></li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Tu nombre, <strong>CURP</strong> y domicilio dentro de la región de esta oficina.</span></li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Nombre de la persona, empresa o sindicato al que se va a citar.</span></li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Su <strong>domicilio completo</strong> para poder notificarle.</span></li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span>El motivo de tu cita (qué pasó y qué solicitas).</span></li>
            </ul>

            <details class="cx-detalle">
                <summary>¿Vas en representación de otra persona?</summary>
                <div class="cx-detalle__cuerpo">
                    <p><strong>De una persona física:</strong> tu identificación oficial vigente y el original o copia certificada del poder notarial, o una carta poder firmada por quien te la otorga ante dos testigos, con copia de las identificaciones de todos.</p>
                    <p class="mb-0"><strong>De una empresa (persona moral):</strong> tu identificación oficial vigente y el original o copia certificada del instrumento notarial, o una carta poder firmada ante dos testigos junto con el instrumento notarial que demuestre que quien otorga el poder puede hacerlo.</p>
                </div>
            </details>
        </section>

        <p class="cx-cierre">
            Guarda tu folio <strong>{{ $folio }}</strong> para cualquier duda o aclaración. El trámite es gratuito. <strong>¡Te esperamos!</strong>
        </p>

        <div class="cx-otra">
            <a href="{{ route('citas') }}" class="btn btn-outline-secondary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agendar otra cita</a>
        </div>
    </main>



@section('scripts')
    <script src="public/assets/js/validaciones-ratificacion.js"></script> 
    <script src="public/assets/js/poderes/general.js"></script>
@endsection

    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')