<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Ratificación múltiple') | SíConcilio</title>
    {{-- Páginas públicas de la ratificación múltiple (RatificacionMultipleController):
         mismo aspecto que /generar-cita. --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --rm-verde: #496163; --rm-verde-osc: #3B5052; --rm-oro: #CEA845; --rm-tinta: #2E3C3D; --rm-suave: #5E6E6F; --rm-borde: #E3E8E8; }
        body { font-family: 'Roboto', sans-serif; background: #F5F7F7; color: #333; padding-top: 96px; }
        .rm-nav { background: #fff; border-bottom: 3px solid var(--rm-oro); box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .rm-card { background: #fff; border: 0; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
        .rm-card__cab { background: var(--rm-verde); color: #fff; border-radius: 12px 12px 0 0; padding: 16px 22px; }
        .rm-card__cab h1 { font-size: 19px; margin: 0; font-weight: 600; }
        .rm-card__cab p { margin: 4px 0 0; font-size: 13.5px; opacity: .9; }
        .rm-seccion { border: 1px solid var(--rm-borde); border-radius: 10px; padding: 18px 18px 8px; margin-bottom: 18px; }
        .rm-seccion > legend { float: none; width: auto; font-size: 15px; font-weight: 700; color: var(--rm-verde); padding: 0 8px; margin: 0 0 6px -8px; }
        .rm-seccion .form-label { font-weight: 600; font-size: 13.5px; color: var(--rm-tinta); }
        .rm-ayuda { font-size: 13px; color: var(--rm-suave); }
        .rm-req { color: #B42318; }
        .btn-rm { background: var(--rm-verde); color: #fff; border: 0; font-weight: 600; }
        .btn-rm:hover, .btn-rm:focus { background: var(--rm-verde-osc); color: #fff; }
        .btn-rm-oro { background: var(--rm-oro); color: #fff; border: 0; font-weight: 600; }
        .btn-rm-oro:hover { background: #B59238; color: #fff; }
        .rm-folio { border-radius: 8px; padding: 10px 12px; font-size: 13.5px; }
        .rm-folio--ok { background: #E9F5EE; color: #1E5E33; }
        .rm-folio--error { background: #FBEDEA; color: #8C2A16; }
        .rm-folio--espera { background: #F5F7F7; color: var(--rm-suave); }
        .rm-tabla th { font-size: 12px; color: var(--rm-suave); white-space: nowrap; }
        .rm-tabla td { font-size: 13px; vertical-align: top; }
        .rm-tabla tr.rm-error > td { background: #FFF5F3; }
        .rm-tabla tr.rm-aviso > td { background: #FFFBEF; }
        .rm-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .rm-chip { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .rm-chip--ok { background: #E3F4E8; color: #1E7B3C; }
        .rm-chip--error { background: #FBE4E6; color: #A3162E; }
        .rm-chip--aviso { background: #FBF4E1; color: #7A5D10; }
        .rm-dia { border: 1px solid var(--rm-borde); border-radius: 10px; padding: 12px 14px; height: 100%; }
        .rm-dia h3 { font-size: 14.5px; font-weight: 700; color: var(--rm-tinta); margin: 0 0 6px; }
        .rm-cargando { display: none; }
        form.is-enviando .rm-cargando { display: inline-block; }
        form.is-enviando .rm-boton-texto { display: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light rm-nav fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('citas') }}"><img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="60"></a>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">Centro de Conciliación Laboral del Estado de Michoacán</span>
        </div>
    </nav>

    <main class="container mb-5" style="max-width: 1100px;">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('contenido')
    </main>

    {{-- Aviso de privacidad (el mismo de las citas en línea, resumido). --}}
    <div class="modal fade" id="rmPrivacidad" tabindex="-1" aria-labelledby="rmPrivacidadTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--rm-verde); color: #fff;">
                    <h5 class="modal-title" id="rmPrivacidadTitulo"><i class="bi bi-shield-lock me-2"></i>Aviso de privacidad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="font-size: 14.5px;">
                    <p>Los datos personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo servirán únicamente para el
                        procedimiento de conciliación y la ratificación de convenios, y serán tratados conforme a la Ley General de Protección de Datos
                        Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                    <p>Se usan para registrar y dar seguimiento a la solicitud, comunicarnos con las partes por correo o teléfono y generar estadística
                        disociada. El sistema guarda además la dirección IP y el tipo de navegador desde los que se registra la solicitud, sólo para la
                        seguridad del sistema.</p>
                    <p class="mb-0">Puedes ejercer tus derechos de acceso, rectificación, cancelación y oposición (ARCO) ante la Unidad de Transparencia del Centro.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-rm" data-bs-dismiss="modal">Entendido</button></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>
