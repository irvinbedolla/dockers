<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Encuesta de atención') · CCL Michoacán</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --verde: #496163; --verde-osc: #3E5254; --verde-claro: #829A9C; --oro: #CEA845; --tinta: #2E3C3D; --suave: #5E6E6F; --borde: #DCE4E4; --fondo: #F4F7F7; }
        body { font-family: 'Roboto', sans-serif; background: #F1F4F4; color: var(--tinta); padding-top: 96px; }
        .at-nav { background: #fff; border-bottom: 3px solid var(--oro); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .at-nav img { height: 60px; }
        .at-nav__texto { font-weight: 700; color: var(--suave); font-size: 15px; }
        .at-tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 16px; box-shadow: 0 4px 18px rgba(46,60,61,.06); overflow: hidden; }
        .at-cabeza { background: var(--verde); color: #fff; padding: 22px 28px; }
        .at-cabeza h1 { font-size: 22px; font-weight: 700; margin: 0; }
        .at-cabeza p { margin: 6px 0 0; color: #DCE6E6; font-size: 14.5px; }
        .at-cuerpo { padding: 24px 28px; }
        @media (max-width: 576px) { .at-cuerpo, .at-cabeza { padding: 18px 16px; } body { padding-top: 84px; } .at-nav img { height: 48px; } }
        .btn-verde { background: var(--verde); border-color: var(--verde); color: #fff; font-weight: 700; border-radius: 10px; padding: 10px 22px; }
        .btn-verde:hover, .btn-verde:focus { background: var(--verde-osc); border-color: var(--verde-osc); color: #fff; }
        .at-aviso { border-radius: 12px; padding: 14px 16px; font-size: 14.5px; }
        .at-aviso--info { background: #EEF4F4; border: 1px solid #D5E2E2; }
        .at-aviso--alerta { background: #FBF4E1; border: 1px solid #F0E2B6; color: #5C4A10; }
        .at-aviso--error { background: #FBEDEA; border: 1px solid #F2D2CA; color: #7A2E1B; }
        .at-aviso--ok { background: #E9F5EE; border: 1px solid #CBE7D6; color: #1E5B32; }
        a { color: var(--verde); }
    </style>
    @yield('estilos')
</head>
<body>
    <nav class="navbar fixed-top at-nav px-3 px-md-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="https://cclmichoacan.gob.mx" target="_blank" rel="noopener">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="Centro de Conciliación Laboral del Estado de Michoacán">
            </a>
            <span class="at-nav__texto d-none d-md-block">Centro de Conciliación Laboral del Estado de Michoacán</span>
        </div>
    </nav>

    <main class="container mb-5" style="max-width: 860px;">
        @yield('contenido')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
