{{--
    Tarjeta: montos convenidos en el mes, por sede, y dentro de cada sede los
    conciliadores.

    La jerarquía se arma con <details>/<summary>: la sede es el resumen y sus
    conciliadores el contenido. Es plegado nativo del navegador, así que
    funciona con teclado y con lector de pantalla sin una línea de JavaScript.

    Una sola serie, un solo tono: la barra lleva el color y el texto se queda
    en tinta, igual que la tarjeta de convenios pagados.

    Espera $conveniosMes, que arma App\Support\ConveniosDelMes::resumen().
--}}

@php
    $compacto = function (float $monto): string {
        if ($monto >= 1000000) {
            return '$'.rtrim(rtrim(number_format($monto / 1000000, 1), '0'), '.').'M';
        }
        if ($monto >= 1000) {
            return '$'.number_format($monto / 1000, 0).'k';
        }
        return '$'.number_format($monto);
    };

    $exacto = fn (float $monto) => '$'.number_format($monto, 2);
@endphp

<div class="inicio-tarjeta ms">
    <h3 class="inicio-tarjeta__titulo">
        Montos convenidos · <span class="ms-mes">{{ $conveniosMes['mes'] }}</span>
    </h3>

    @if (empty($conveniosMes['sedes']))
        <p class="ms-vacio">Todavía no hay convenios registrados este mes.</p>
    @else
        <div class="ms-totales">
            <div>
                <span class="ms-cifra" title="{{ $exacto($conveniosMes['monto']) }}">{{ $compacto($conveniosMes['monto']) }}</span>
                <span class="ms-pie">convenidos en el mes</span>
            </div>
            <div>
                <span class="ms-cifra">{{ number_format($conveniosMes['total']) }}</span>
                <span class="ms-pie">convenios</span>
            </div>
        </div>

        <p class="ms-ayuda">Toca una sede para ver a sus conciliadores.</p>

        <div class="ms-sedes">
            @foreach ($conveniosMes['sedes'] as $sede)
                @php
                    $ancho = $conveniosMes['maximo'] > 0
                        ? round($sede['monto'] / $conveniosMes['maximo'] * 100, 1)
                        : 0;
                @endphp

                <details class="ms-sede">
                    <summary class="ms-fila">
                        <span class="ms-nombre">{{ $sede['sede'] }}</span>
                        <span class="ms-barra" aria-hidden="true">
                            <span class="ms-barra__relleno" style="width: {{ max($ancho, 1.5) }}%"></span>
                        </span>
                        <span class="ms-valor" title="{{ $exacto($sede['monto']) }} en {{ $sede['convenios'] }} convenios">{{ $compacto($sede['monto']) }}</span>
                    </summary>

                    <ul class="ms-gente">
                        @foreach ($sede['gente'] as $persona)
                            <li class="ms-persona @if($persona['anonimo']) es-anonima @endif">
                                <span class="ms-persona__nombre">{{ $persona['nombre'] }}</span>
                                <span class="ms-persona__n">{{ number_format($persona['convenios']) }}</span>
                                <span class="ms-persona__monto" title="{{ $exacto($persona['monto']) }}">{{ $compacto($persona['monto']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endforeach
        </div>
    @endif
</div>

<style>
    .ms-mes { text-transform: none; letter-spacing: 0; color: #5E6E6F; }

    .ms-totales {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        padding-bottom: 14px;
        margin-bottom: 12px;
        border-bottom: 1px solid #EDF1F1;
    }
    .ms-cifra {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #354647;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
    }
    .ms-pie { display: block; font-size: 11.5px; color: #8A9899; }

    .ms-ayuda { font-size: 11.5px; color: #A2AFAF; margin: 0 0 8px; }

    .ms-sedes { display: flex; flex-direction: column; gap: 2px; }

    .ms-fila {
        display: grid;
        grid-template-columns: 96px 1fr auto;
        align-items: center;
        gap: 10px;
        padding: 5px 4px;
        border-radius: 6px;
        cursor: pointer;
        list-style: none;
    }
    /* El triángulo por defecto rompe la rejilla; la señal de plegado la da
       el propio nombre con su cursor y el estado abierto. */
    .ms-fila::-webkit-details-marker { display: none; }
    .ms-fila::marker { content: ''; }

    .ms-fila:hover { background: #F5F8F8; }
    .ms-sede[open] > .ms-fila { background: #F0F5F5; }
    .ms-fila:focus-visible { outline: 2px solid #CEA845; outline-offset: 2px; }

    .ms-nombre {
        font-size: 12.5px;
        color: #3C4A4B;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ms-sede[open] > .ms-fila .ms-nombre { font-weight: 600; color: #354647; }

    .ms-barra { display: block; height: 10px; background: #EDF1F1; border-radius: 3px; overflow: hidden; }
    .ms-barra__relleno {
        display: block;
        height: 100%;
        background: #496163;
        border-radius: 0 4px 4px 0;
    }

    .ms-valor {
        font-size: 12.5px;
        font-weight: 600;
        color: #354647;
        font-variant-numeric: tabular-nums;
        min-width: 52px;
        text-align: right;
    }

    .ms-gente {
        list-style: none;
        margin: 2px 0 8px;
        padding: 6px 4px 2px 12px;
        border-left: 2px solid #E3EAEA;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .ms-persona {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 10px;
        align-items: baseline;
        font-size: 12px;
    }
    .ms-persona__nombre {
        color: #5E6E6F;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ms-persona__n {
        color: #A2AFAF;
        font-variant-numeric: tabular-nums;
        min-width: 28px;
        text-align: right;
    }
    .ms-persona__monto {
        color: #3C4A4B;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        min-width: 48px;
        text-align: right;
    }
    .ms-persona.es-anonima .ms-persona__nombre { font-style: italic; color: #A2AFAF; }

    .ms-vacio { font-size: 13px; color: #8A9899; margin: 0; }
</style>
