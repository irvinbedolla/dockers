{{--
    Tarjeta: tabla de posiciones del mes, conciliadores, auxiliares y
    orientadores. Va debajo de las tarjetas de cifras, a todo lo ancho.

    Los tres grupos se ven a la vez, en columnas, no en pastillas: son competencias
    distintas y ninguna es "la otra vista" de la primera. Cada una abre con su
    primer lugar en grande -foto, sede y el dato que lo puso ahí- y sigue con
    los demás en renglones compactos.

    Aquí no hay barras. Son escalas que no se comparan entre sí -convenios
    contra solicitudes- y el número es el dato; una barra sólo invitaría a
    medir a un conciliador contra un auxiliar, que no significa nada.

    Quien todavía no tiene foto lleva un monograma con sus iniciales, no la
    silueta genérica: en una tabla de posiciones la silueta repetida borra la
    diferencia entre las personas, que es justo lo que la tarjeta muestra.

    Están todos los conciliadores, auxiliares y orientadores, aunque lleven 0
    en el mes. Se
    ven los primeros cinco; el resto se despliega con "Ver todos".

    Espera $posiciones, de App\Support\TablaPosiciones::resumen().
--}}

<div class="inicio-tarjeta tp">
    <h3 class="inicio-tarjeta__titulo">
        Tabla de posiciones · <span class="tp-mes">{{ $posiciones['mes'] }}</span>
    </h3>

    @if (! $posiciones['hayDatos'])
        <p class="tp-vacio">Todavía no hay movimientos este mes.</p>
    @else
        <div class="tp-grupos">
        @foreach ($posiciones['grupos'] as $grupo)
            <section class="tp-grupo">
                <h4 class="tp-grupo__titulo">{{ $grupo['titulo'] }}</h4>

                @if (empty($grupo['gente']))
                    <p class="tp-vacio">Sin registros este mes.</p>
                @else
                    @php
                        $lider = $grupo['gente'][0];
                        $resto = array_slice($grupo['gente'], 1);
                    @endphp

                    <div class="tp-lider">
                        <span class="tp-avatar tp-avatar--grande">
                            @if ($lider['foto'])
                                <img src="{{ asset('storage/'.$lider['foto']) }}" alt="">
                            @else
                                <span class="tp-iniciales">{{ $lider['iniciales'] }}</span>
                            @endif
                            <span class="tp-medalla">{{ $lider['puesto'] }}</span>
                        </span>

                        <span class="tp-lider__texto">
                            <span class="tp-lider__nombre">{{ $lider['nombre'] }}</span>
                            <span class="tp-lider__pie">
                                {{ $lider['sede'] }}
                                @if ($grupo['clave'] === 'conciliadores' && $lider['resueltas'] > 0)
                                    · {{ number_format($lider['valor']) }} de {{ number_format($lider['resueltas']) }} resueltas
                                @endif
                            </span>
                        </span>

                        <span class="tp-lider__valor">
                            {{ number_format($lider['valor']) }}
                            <small>{{ $grupo['unidad'] }}</small>
                        </span>
                    </div>

                    @if ($resto)
                        @php $visibles = \App\Support\TablaPosiciones::VISIBLES - 1; @endphp
                        <ol class="tp-resto" id="tp-resto-{{ $grupo['clave'] }}">
                            @foreach ($resto as $persona)
                                <li class="tp-fila {{ $loop->index >= $visibles ? 'tp-fila--extra' : '' }} {{ $persona['valor'] === 0 ? 'tp-fila--cero' : '' }}"
                                    @if ($loop->index >= $visibles) hidden @endif
                                    title="{{ $persona['nombre'] }} · {{ $persona['sede'] }} · {{ number_format($persona['valor']) }} {{ $grupo['unidad'] }}">
                                    <span class="tp-puesto">{{ $persona['puesto'] }}</span>
                                    <span class="tp-avatar">
                                        @if ($persona['foto'])
                                            <img src="{{ asset('storage/'.$persona['foto']) }}" alt="">
                                        @else
                                            <span class="tp-iniciales">{{ $persona['iniciales'] }}</span>
                                        @endif
                                    </span>
                                    <span class="tp-nombre">{{ $persona['nombre'] }}</span>
                                    <span class="tp-valor">{{ number_format($persona['valor']) }}</span>
                                </li>
                            @endforeach
                        </ol>
                        @if (count($resto) > $visibles)
                            <button type="button" class="tp-mas" aria-expanded="false" aria-controls="tp-resto-{{ $grupo['clave'] }}"
                                    data-mas="Ver todos ({{ count($grupo['gente']) }})" data-menos="Ver menos">
                                <span>Ver todos ({{ count($grupo['gente']) }})</span>
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </button>
                        @endif
                    @endif
                @endif
            </section>
        @endforeach
        </div>
    @endif
</div>

<style>
    .tp-mes { text-transform: none; letter-spacing: 0; color: #5E6E6F; }

    /* La tarjeta se mide a sí misma: el menú lateral cambia el ancho
       disponible sin que cambie la pantalla. */
    .inicio-tarjeta.tp {
        container: posiciones / inline-size;
        margin-bottom: 18px;
    }
    .tp-grupos {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
    }

    .tp-grupo + .tp-grupo {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #EDF1F1;
    }
    .tp-grupo__titulo {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #A2AFAF;
        margin: 0 0 10px;
    }

    /* --- avatares --- */
    .tp-avatar {
        position: relative;
        display: block;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        overflow: visible;
        flex: 0 0 auto;
    }
    .tp-avatar img,
    .tp-iniciales {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    .tp-iniciales {
        display: grid;
        place-items: center;
        background: #E3EAEA;
        color: #5E6E6F;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .tp-avatar--grande { width: 52px; height: 52px; }
    .tp-avatar--grande .tp-iniciales { font-size: 18px; }

    /* --- primer lugar --- */
    .tp-lider {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #F2F6F6;
    }

    .tp-medalla {
        position: absolute;
        right: -3px;
        bottom: -3px;
        min-width: 19px;
        height: 19px;
        padding: 0 4px;
        border-radius: 999px;
        background: #496163;
        /* Anillo del color del panel: despega la medalla de la foto, que
           puede ser oscura justo en esa esquina. */
        box-shadow: 0 0 0 2px #F2F6F6;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 19px;
        text-align: center;
    }

    .tp-lider__texto { min-width: 0; }
    .tp-lider__nombre {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #2E3C3D;
        line-height: 1.25;
    }
    .tp-lider__pie {
        display: block;
        font-size: 11px;
        color: #8A9899;
        margin-top: 2px;
    }
    .tp-lider__valor {
        font-size: 26px;
        font-weight: 700;
        color: #354647;
        line-height: 1;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .tp-lider__valor small {
        display: block;
        font-size: 10.5px;
        font-weight: 600;
        color: #8A9899;
        letter-spacing: .02em;
        margin-top: 3px;
    }

    /* --- del segundo en adelante --- */
    .tp-resto {
        list-style: none;
        margin: 6px 0 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    .tp-fila {
        display: grid;
        grid-template-columns: 16px auto 1fr auto;
        align-items: center;
        gap: 10px;
        padding: 5px 12px;
        border-radius: 6px;
    }
    .tp-fila:hover { background: #F5F8F8; }

    .tp-puesto {
        font-size: 11.5px;
        font-weight: 700;
        color: #B4BFBF;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .tp-nombre {
        font-size: 12.5px;
        color: #3C4A4B;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .tp-valor {
        font-size: 13px;
        font-weight: 700;
        color: #354647;
        font-variant-numeric: tabular-nums;
        min-width: 28px;
        text-align: right;
    }

    .tp-vacio { font-size: 12.5px; color: #8A9899; margin: 0; }

    .tp-fila[hidden] { display: none; }

    /* Quien va en 0 este mes: se lista, pero apagado. */
    .tp-fila--cero .tp-valor,
    .tp-fila--cero .tp-nombre { color: #A2AFAF; }

    .tp-mas {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        margin-top: 6px;
        padding: 6px 10px;
        border: 1px dashed #D5DEDE;
        border-radius: 8px;
        background: transparent;
        color: #496163;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .tp-mas:hover { background: #F5F8F8; border-style: solid; }
    .tp-mas i { transition: transform .2s; }
    .tp-mas[aria-expanded="true"] i { transform: rotate(180deg); }

    /* --- tres columnas ---
       Con espacio, los grupos van lado a lado separados por una línea
       vertical en vez de la horizontal de cuando se apilan. */
    @container posiciones (min-width: 760px) {
        .tp-grupos { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .tp-grupo { padding: 0 20px; }
        .tp-grupo:first-child { padding-left: 0; }
        .tp-grupo:last-child { padding-right: 0; }
        .tp-grupo + .tp-grupo {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
            border-left: 1px solid #EDF1F1;
        }
    }

    /* Columnas angostas (menos de ~300px): el primer lugar deja de ser una
       línea de tres columnas, porque el nombre se partía en cuatro renglones
       y la cifra se le encimaba. Avatar y nombre arriba, la cifra abajo. */
    @container posiciones (min-width: 760px) and (max-width: 1000px) {
        .tp-lider {
            grid-template-columns: auto 1fr;
            grid-template-areas: "foto texto" "valor valor";
            gap: 8px 9px;
            padding: 9px 10px;
        }
        .tp-lider > .tp-avatar--grande { grid-area: foto; }
        .tp-lider__texto { grid-area: texto; }
        .tp-lider__valor {
            grid-area: valor;
            display: flex;
            align-items: baseline;
            gap: 5px;
            font-size: 20px;
            text-align: left;
        }
        .tp-lider__valor small { margin-top: 0; }
        .tp-avatar--grande { width: 40px; height: 40px; }
        .tp-avatar--grande .tp-iniciales { font-size: 14px; }
        .tp-grupo { padding: 0 13px; }
        .tp-fila { gap: 7px; padding: 4px 8px; }
    }

    /* Sin soporte de @container se decide por la pantalla. */
    @supports not (container-type: inline-size) {
        @media (min-width: 900px) {
            .tp-grupos { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .tp-grupo { padding: 0 20px; }
            .tp-grupo:first-child { padding-left: 0; }
            .tp-grupo:last-child { padding-right: 0; }
            .tp-grupo + .tp-grupo { margin-top: 0; padding-top: 0; border-top: 0; border-left: 1px solid #EDF1F1; }
        }
    }
</style>

<script>
    // "Ver todos" / "Ver menos" de cada grupo de la tabla de posiciones.
    // La bandera evita registrarlo dos veces si la tarjeta se incluye más de una.
    if (!window.tpVerTodos) window.tpVerTodos = true, document.addEventListener('click', function (e) {
        var boton = e.target.closest('.tp-mas');
        if (!boton) { return; }
        var lista = document.getElementById(boton.getAttribute('aria-controls'));
        var abrir = boton.getAttribute('aria-expanded') !== 'true';
        lista.querySelectorAll('.tp-fila--extra').forEach(function (li) { li.hidden = !abrir; });
        boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        boton.querySelector('span').textContent = boton.getAttribute(abrir ? 'data-menos' : 'data-mas');
    });
</script>
