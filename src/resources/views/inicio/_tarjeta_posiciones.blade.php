{{--
    Tarjeta: tabla de posiciones del mes, conciliadores y auxiliares.

    Los dos grupos se ven a la vez, no en pastillas: son competencias
    distintas y ninguna es "la otra vista" de la primera. Cada una abre con su
    primer lugar en grande -foto, sede y el dato que lo puso ahí- y sigue con
    los demás en renglones compactos.

    Aquí no hay barras. Son dos escalas que no se comparan entre sí -convenios
    contra solicitudes- y el número es el dato; una barra sólo invitaría a
    medir a un conciliador contra un auxiliar, que no significa nada.

    Quien todavía no tiene foto lleva un monograma con sus iniciales, no la
    silueta genérica: en una tabla de posiciones la silueta repetida borra la
    diferencia entre las personas, que es justo lo que la tarjeta muestra.

    Espera $posiciones, de App\Support\TablaPosiciones::resumen().
--}}

<div class="inicio-tarjeta tp">
    <h3 class="inicio-tarjeta__titulo">
        Tabla de posiciones · <span class="tp-mes">{{ $posiciones['mes'] }}</span>
    </h3>

    @if (! $posiciones['hayDatos'])
        <p class="tp-vacio">Todavía no hay movimientos este mes.</p>
    @else
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
                        <ol class="tp-resto">
                            @foreach ($resto as $persona)
                                <li class="tp-fila" title="{{ $persona['nombre'] }} · {{ $persona['sede'] }} · {{ number_format($persona['valor']) }} {{ $grupo['unidad'] }}">
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
                    @endif
                @endif
            </section>
        @endforeach
    @endif
</div>

<style>
    .tp-mes { text-transform: none; letter-spacing: 0; color: #5E6E6F; }

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
</style>
