<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') · SíConcilio</title>

    @include('partials.favicon')

    {{--
        Esta página se sirve justo cuando algo no funciona, así que no depende
        de la hoja de estilos compilada ni de Bootstrap: si eso fuera lo roto,
        el error se vería peor que el error. Todo va aquí adentro. La tipografía
        se pide a Google igual que el layout, pero con respaldo del sistema por
        si la red de la oficina la bloquea.
    --}}
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
            /* Los dos verdes de la casa en una banda diagonal muy tenue: da
               color institucional sin competir con el contenido. */
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
            margin: 0 auto 26px;
        }

        .marco__codigo {
            font-size: 66px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -.02em;
            color: var(--verde-claro);
            margin: 0;
        }

        /* Una regla corta en dorado bajo el número: es el único acento cálido
           del logotipo y ata la página a la marca sin repetirla. */
        .marco__regla {
            width: 46px;
            height: 3px;
            margin: 16px auto 20px;
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

        /* La dirección pedida, para que quien llame a soporte pueda decir
           exactamente qué abrió. Rompe por cualquier lado: hay URLs largas. */
        .marco__ruta {
            display: inline-block;
            max-width: 100%;
            margin-top: 18px;
            padding: 7px 12px;
            border-radius: 8px;
            background: #F2F6F6;
            color: var(--tinta-tenue);
            font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
            font-size: 12px;
            line-height: 1.4;
            word-break: break-all;
        }

        .marco__acciones {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
        }

        .boton {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
        }
        .boton:focus-visible { outline: 2px solid var(--dorado); outline-offset: 2px; }

        .boton--principal { background: var(--verde-obscuro); color: #fff; }
        .boton--principal:hover { background: #3C5254; }

        .boton--suave {
            background: #fff;
            border-color: #D7DEDE;
            color: var(--tinta-suave);
        }
        .boton--suave:hover { border-color: var(--verde-obscuro); color: var(--verde-obscuro); }

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
            .marco__codigo { font-size: 54px; }
            .marco__titulo { font-size: 19px; }
            .boton { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="marco">
        <img class="marco__logo" src="{{ asset('assets/images/ccl-r.png') }}"
             alt="SíConcilio — Sistema Integral para la Conciliación">

        <p class="marco__codigo">@yield('codigo')</p>
        <div class="marco__regla" aria-hidden="true"></div>

        <h1 class="marco__titulo">@yield('titulo')</h1>
        <p class="marco__texto">@yield('texto')</p>

        @hasSection('ruta')
            <p class="marco__ruta">@yield('ruta')</p>
        @endif

        <div class="marco__acciones">
            {{-- A quien no ha entrado no se le ofrece el inicio: lo rebotaría
                 al login y parecería un segundo error. --}}
            @auth
                <a class="boton boton--principal" href="{{ route('inicio') }}">Ir al inicio</a>
            @else
                <a class="boton boton--principal" href="{{ route('login') }}">Iniciar sesión</a>
            @endauth

            <button type="button" class="boton boton--suave" onclick="history.back()">Regresar</button>
        </div>

        <p class="marco__pie">
            Centro de Conciliación Laboral del Estado de Michoacán<br>
            Si el problema sigue, repórtalo con la dirección de arriba.
        </p>
    </main>
</body>
</html>
