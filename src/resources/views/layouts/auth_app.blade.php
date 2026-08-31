<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title') | SiConcilio</title>

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --verde:  #496163;
            --dorado: #CEA845;
        }

        html, body { height: 100%; }

        body {
            margin: 0;
            background: #fff;
            color: #2B3839;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Dos columnas: la foto ocupa el espacio sobrante y el panel mide fijo,
           así el formulario no se estira en monitores anchos. */
        .acceso {
            display: flex;
            min-height: 100vh;
        }

        .acceso__foto {
            flex: 1 1 auto;
            position: relative;
            overflow: hidden;
            background-image: url('{{ asset("assets/images/login-background.webp") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Degradado del verde institucional, denso abajo y transparente arriba:
           asienta la foto sin taparla y amarra la imagen con la paleta. */
        .acceso__foto::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top,
                        rgba(73, 97, 99, .92) 0%,
                        rgba(73, 97, 99, .72) 25%,
                        rgba(73, 97, 99, .38) 55%,
                        rgba(73, 97, 99, .08) 85%,
                        rgba(73, 97, 99, 0) 100%);
        }

        .acceso__panel {
            flex: 0 0 420px;
            border-left: 4px solid var(--dorado);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 38px;
            background: #fff;
        }

        .acceso__caja { width: 100%; max-width: 320px; }

        .acceso__logo {
            display: block;
            width: 100%;
            max-width: 210px;
            height: auto;
            margin: 0 auto 26px;
        }

        .acceso__titulo {
            font-size: 19px;
            font-weight: 600;
            text-align: center;
            color: var(--verde);
            margin: 0 0 22px;
        }

        /* Campos con icono: el icono va absoluto y el input reserva el espacio
           con padding, no con un input-group, para que el borde sea uno solo. */
        .campo { position: relative; margin-bottom: 14px; }

        .campo > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9AA7A8;
            font-size: 15px;
            pointer-events: none;
        }

        .campo input {
            width: 100%;
            border: 1px solid #DDE3E3;
            border-radius: 10px;
            padding: 12px 14px 12px 40px;
            font-size: 16px;
            color: #2B3839;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .campo input::placeholder { color: #9AA7A8; }

        .campo input:focus {
            outline: 0;
            border-color: var(--verde);
            box-shadow: 0 0 0 3px rgba(73, 97, 99, .14);
        }

        .campo input[type="password"],
        .campo input.con-ojo { padding-right: 42px; }

        .ojo {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: none;
            color: #9AA7A8;
            padding: 6px 8px;
            line-height: 1;
            cursor: pointer;
            border-radius: 8px;
        }

        .ojo:hover  { color: var(--verde); }
        .ojo:focus-visible { outline: 2px solid var(--verde); outline-offset: 1px; }

        /* Botón dorado con texto blanco, a petición del área. */
        .acceso__btn {
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: var(--dorado);
            color: #fff;
            font-size: 14.5px;
            font-weight: 600;
            padding: 12px 16px;
            margin-top: 6px;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .acceso__btn:hover  { background: #BE9836; }
        .acceso__btn:active { background: #AE8B31; }
        .acceso__btn:focus-visible { outline: 2px solid var(--verde); outline-offset: 2px; }

        .acceso__aviso {
            display: flex;
            gap: 9px;
            align-items: flex-start;
            background: #FCF4F3;
            border: 1px solid #F0D2CD;
            border-radius: 10px;
            padding: 11px 13px;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.4;
            color: #8A3F35;
        }

        .acceso__aviso i { font-size: 15px; line-height: 1.25; flex: 0 0 auto; }
        .acceso__aviso b { display: block; font-weight: 600; margin-bottom: 1px; }

        .acceso__pie {
            margin-top: 26px;
            text-align: center;
            font-size: 11.5px;
            color: #9AA7A8;
        }

        /* En pantallas angostas la foto estorba: el formulario se queda solo. */
        @media (max-width: 900px) {
            .acceso__foto { display: none; }
            .acceso__panel {
                flex: 1 1 auto;
                border-left: 0;
                border-top: 4px solid var(--dorado);
            }
        }

        .loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: url('{{ asset("assets/images/pageLoader.gif") }}') 50% 50% no-repeat rgb(249, 249, 249);
            opacity: .8;
        }
    </style>
</head>

<body>

<main class="acceso">
    <div class="acceso__foto" role="img"
         aria-label="Atención al público en el Centro de Conciliación Laboral del Estado de Michoacán"></div>

    <div class="acceso__panel">
        <div class="acceso__caja">
            <img class="acceso__logo" src="{{ asset('assets/images/ccl-r.png') }}"
                 alt="SiConcilio — Sistema Integral para la Conciliación">

            @yield('content')

            <p class="acceso__pie">Centro de Conciliación Laboral del Estado de Michoacán</p>
        </div>
    </div>
</main>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

@stack('body_end')
@yield('scripts')

</body>
</html>
