{{--
    Tarjeta: tasa de conciliación del mes, general y por sede.

    Es una razón, no una magnitud: las barras van sobre una escala fija de
    0 a 100 y no normalizadas contra la sede más alta. Normalizarlas haría que
    la mejor sede siempre llegara al tope y la tarjeta mentiría en cuanto
    todas bajaran a la vez.

    Sobre las barras corre una línea vertical en la tasa general, para que cada
    sede se lea contra el Centro y no sólo contra las demás. Una sola serie, un
    solo tono: el color lo lleva la barra y las cifras se quedan en tinta.

    Las dos métricas se pintan de una vez y las pastillas sólo cambian cuál se
    ve, igual que en la tarjeta de convenios pagados.

    Espera $tasaConciliacion, de App\Support\TasaConciliacion::resumen().
--}}

@php
    $pct = fn (?float $t) => $t === null ? '—' : number_format($t, 1).'%';
@endphp

<div class="inicio-tarjeta tc">
    <h3 class="inicio-tarjeta__titulo">
        Tasa de conciliación · <span class="tc-mes">{{ $tasaConciliacion['mes'] }}</span>
    </h3>

    @if (! $tasaConciliacion['hayDatos'])
        <p class="tc-vacio">Todavía no hay audiencias con resultado este mes.</p>
    @else
        @foreach (\App\Support\TasaConciliacion::METRICAS as $clave => $etiqueta)
            @php $bloque = $tasaConciliacion['metricas'][$clave]; @endphp

            <div class="tc-bloque" data-metrica="{{ $clave }}" @unless($loop->first) hidden @endunless>
                <div class="tc-totales">
                    <div>
                        <span class="tc-cifra">{{ $pct($bloque['tasa']) }}</span>
                        <span class="tc-pie">
                            @if ($clave === 'resueltas')
                                de las audiencias resueltas
                            @else
                                de todo lo concluido
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="tc-cifra">{{ number_format($bloque['conciliados']) }}</span>
                        <span class="tc-pie">de {{ number_format($bloque['base']) }} audiencias</span>
                    </div>
                    @if ($bloque['delta'] !== null)
                        @php
                            // La dirección va en palabras además de la flecha:
                            // la flecha sola no se oye en un lector de pantalla.
                            [$flecha, $palabra] = match (true) {
                                $bloque['delta'] > 0 => ['↑', 'más'],
                                $bloque['delta'] < 0 => ['↓', 'menos'],
                                default              => ['=', 'igual'],
                            };
                        @endphp
                        <div>
                            <span class="tc-cifra tc-delta">
                                <span class="tc-delta__flecha" aria-hidden="true">{{ $flecha }}</span>{{ number_format(abs($bloque['delta']), 1) }}
                            </span>
                            <span class="tc-pie">
                                puntos {{ $palabra }} que {{ $tasaConciliacion['mesPrevio'] }} ({{ $pct($bloque['previa']) }})
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="tc-pastillas" role="group" aria-label="Qué entra en el denominador">
            @foreach (\App\Support\TasaConciliacion::METRICAS as $clave => $etiqueta)
                <button type="button" class="tc-pastilla @if($loop->first) esta-activa @endif"
                        data-metrica="{{ $clave }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        title="{{ $clave === 'resueltas'
                            ? 'Conciliación contra no conciliación: audiencias en las que sí hubo resultado.'
                            : 'Suma incomparecencias, incompetencias, desistimientos y archivadas en audiencia.' }}">{{ $etiqueta }}</button>
            @endforeach
        </div>

        @foreach (\App\Support\TasaConciliacion::METRICAS as $clave => $etiqueta)
            @php $bloque = $tasaConciliacion['metricas'][$clave]; @endphp

            <ol class="tc-lista" data-metrica="{{ $clave }}" @unless($loop->first) hidden @endunless
                aria-label="Sedes por tasa de conciliación, {{ Str::lower($etiqueta) }}">
                @foreach ($bloque['sedes'] as $fila)
                    <li class="tc-fila"
                        title="{{ $fila['sede'] }}: {{ number_format($fila['conciliados']) }} convenios de {{ number_format($fila['base']) }} audiencias ({{ $pct($fila['tasa']) }})">
                        <span class="tc-sede">{{ $fila['sede'] }}</span>
                        <span class="tc-pista" aria-hidden="true">
                            <span class="tc-barra">
                                <span class="tc-barra__relleno" style="width: {{ $fila['tasa'] }}%"></span>
                            </span>
                            @if ($bloque['tasa'] !== null)
                                <span class="tc-ref" style="left: {{ $bloque['tasa'] }}%"></span>
                            @endif
                        </span>
                        <span class="tc-valor">{{ $pct($fila['tasa']) }}</span>
                    </li>
                @endforeach
            </ol>
        @endforeach

        <p class="tc-nota">
            <span class="tc-nota__marca" aria-hidden="true"></span>
            La línea marca la tasa general del Centro. No incluye ratificaciones:
            ésas llegan con el convenio ya acordado.
        </p>
    @endif
</div>

<style>
    .tc-mes { text-transform: none; letter-spacing: 0; color: #5E6E6F; }

    .tc-bloque[hidden], .tc-lista[hidden] { display: none !important; }

    .tc-totales {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid #EDF1F1;
    }
    .tc-cifra {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #354647;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
    }
    .tc-delta { font-size: 19px; color: #5E6E6F; }
    .tc-delta__flecha { margin-right: 3px; font-weight: 400; }
    .tc-pie { display: block; font-size: 11.5px; color: #8A9899; }

    .tc-pastillas { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }

    .tc-pastilla {
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        padding: 7px 12px;
        border: 1px solid #D7DEDE;
        border-radius: 999px;
        background: #fff;
        color: #5E6E6F;
        cursor: pointer;
    }
    .tc-pastilla:hover { border-color: #496163; color: #354647; }
    .tc-pastilla:focus-visible { outline: 2px solid #CEA845; outline-offset: 2px; }
    .tc-pastilla.esta-activa { background: #354647; border-color: #354647; color: #fff; }

    .tc-lista {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .tc-fila {
        display: grid;
        grid-template-columns: 96px 1fr auto;
        align-items: center;
        gap: 10px;
        padding: 5px 4px;
        border-radius: 6px;
    }
    .tc-fila:hover { background: #F5F8F8; }

    .tc-sede {
        font-size: 12.5px;
        color: #3C4A4B;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* La pista es el 100 %: la barra se lee como proporción aunque ninguna
       sede llegue al tope. */
    .tc-pista { position: relative; display: block; }
    .tc-barra {
        display: block;
        height: 10px;
        background: #EDF1F1;
        border-radius: 3px;
        overflow: hidden;
    }
    .tc-barra__relleno {
        display: block;
        height: 100%;
        background: #496163;
        border-radius: 0 4px 4px 0;
    }
    /* Referencia: cruza la pista clara y el relleno oscuro, así que ningún
       tono sólo le alcanza. Va en tinta con un anillo blanco de 1px, que es lo
       que la despega del relleno; y sobresale 3px para que se lea como una
       marca de la tarjeta y no como parte de la barra. */
    .tc-ref {
        position: absolute;
        top: -3px;
        bottom: -3px;
        width: 2px;
        margin-left: -1px;
        background: #354647;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, .85);
    }

    .tc-valor {
        font-size: 12.5px;
        font-weight: 600;
        color: #354647;
        font-variant-numeric: tabular-nums;
        min-width: 52px;
        text-align: right;
    }

    .tc-nota {
        display: flex;
        align-items: baseline;
        gap: 7px;
        font-size: 11.5px;
        line-height: 1.45;
        color: #A2AFAF;
        margin: 12px 0 0;
    }
    .tc-nota__marca {
        flex: 0 0 2px;
        align-self: stretch;
        min-height: 12px;
        background: #354647;
    }

    .tc-vacio { font-size: 13px; color: #8A9899; margin: 0; }

    @media (prefers-reduced-motion: no-preference) {
        .tc-barra__relleno { transition: width .25s ease; }
    }
</style>

<script>
(function () {
    var tarjeta = document.currentScript.closest('.inicio-tarjeta') || document;
    var pastillas = tarjeta.querySelectorAll('.tc-pastilla');
    var paneles   = tarjeta.querySelectorAll('.tc-bloque, .tc-lista');

    pastillas.forEach(function (pastilla) {
        pastilla.addEventListener('click', function () {
            var metrica = pastilla.dataset.metrica;

            pastillas.forEach(function (otra) {
                var activa = otra === pastilla;
                otra.classList.toggle('esta-activa', activa);
                otra.setAttribute('aria-pressed', activa ? 'true' : 'false');
            });

            paneles.forEach(function (panel) {
                panel.hidden = panel.dataset.metrica !== metrica;
            });
        });
    });
})();
</script>
