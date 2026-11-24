<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>En mantenimiento · SíConcilio</title>

    {{--
        Pantalla de mantenimiento. Se activa con:  sh mantenimiento.sh on
        y se quita con:                             sh mantenimiento.sh off

        Laravel la pre-renderiza al momento del "down" (opción --render) y la
        sirve sin arrancar el framework ni tocar la base de datos, así que se
        ve aunque el deploy esté a medias. Por eso todo va en línea, las rutas
        son relativas a la raíz (no dependen de APP_URL) y no hay @auth ni
        route(): se renderiza desde consola, sin sesión.
    --}}
    <link rel="icon" href="/favicon.ico?v=1" sizes="32x32">
    <link rel="icon" href="/favicon.svg?v=1" type="image/svg+xml">
    <meta name="theme-color" content="#496163">
    <link href="//fonts.googleapis.com/css?family=Lato:400,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --verde-obscuro: #496163;
            --verde-claro:   #829A9C;
            --dorado:        #CEA845;
            --tinta:         #2E3C3D;
            --tinta-suave:   #5E6E6F;
            --tinta-tenue:   #8A9899;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: 'Lato', -apple-system, 'Segoe UI', system-ui, sans-serif;
            color: var(--tinta);
            background:
                linear-gradient(135deg, rgba(130, 154, 156, .16), rgba(73, 97, 99, .07) 55%, transparent 80%),
                #F4F7F7;
        }

        .marco {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border: 1px solid #E3E8E8;
            border-radius: 16px;
            padding: 40px 38px 34px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(46, 60, 61, .04), 0 12px 32px rgba(46, 60, 61, .07);
        }

        .marco__logo {
            display: block;
            width: auto;
            max-width: 236px;
            max-height: 62px;
            margin: 0 auto 28px;
        }

        /* Engrane girando despacio: dice "estamos trabajando" sin texto. */
        .engrane {
            width: 72px;
            height: 72px;
            margin: 0 auto;
            color: var(--verde-claro);
            animation: girar 6s linear infinite;
        }
        @keyframes girar { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .engrane { animation: none; } }

        .marco__regla {
            width: 46px;
            height: 3px;
            margin: 18px auto 20px;
            border-radius: 2px;
            background: var(--dorado);
        }

        .marco__titulo {
            font-size: 22px;
            font-weight: 700;
            color: var(--verde-obscuro);
            margin: 0 0 10px;
        }

        .marco__texto {
            font-size: 14.5px;
            line-height: 1.6;
            color: var(--tinta-suave);
            margin: 0 auto;
            max-width: 42ch;
        }

        .marco__aviso {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 22px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #F2F6F6;
            color: var(--tinta-suave);
            font-size: 12.5px;
        }
        .marco__punto {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dorado);
            animation: latir 1.6s ease-in-out infinite;
        }
        @keyframes latir { 50% { opacity: .35; } }
        @media (prefers-reduced-motion: reduce) { .marco__punto { animation: none; } }

        .marco__pie {
            margin: 30px 0 0;
            padding-top: 18px;
            border-top: 1px solid #EDF1F1;
            font-size: 11.5px;
            line-height: 1.55;
            color: var(--tinta-tenue);
        }

        @media (max-width: 480px) {
            .marco { padding: 30px 22px 26px; border-radius: 14px; }
            .marco__titulo { font-size: 19px; }
        }
    </style>
</head>
<body>
    <main class="marco">
        <img class="marco__logo" src="/assets/images/ccl-r.png"
             alt="SíConcilio — Sistema Integral para la Conciliación">

        <svg class="engrane" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
        <div class="marco__regla" aria-hidden="true"></div>

        <h1 class="marco__titulo">Sistema en mantenimiento</h1>
        <p class="marco__texto">
            Estamos realizando mejoras en SíConcilio. El servicio volverá
            en unos minutos; tu información está segura.
        </p>

        <p class="marco__aviso">
            <span class="marco__punto" aria-hidden="true"></span>
            Esta página se actualizará sola cuando el sistema esté listo.
        </p>

        <p class="marco__pie">
            Centro de Conciliación Laboral del Estado de Michoacán<br>
            Lamentamos las molestias.
        </p>
    </main>
</body>
</html>
