{{--
    Tarjeta: convenios pagados por sede en el mes en curso.

    Una sola serie -magnitud por categoría-, así que va un solo tono del
    sistema, sin leyenda y sin paleta categórica: el nombre y las cifras se
    quedan en tinta y sólo la barra lleva color.

    Las tres métricas se pintan de una vez y las pastillas sólo cambian cuál
    se ve, así el cambio es instantáneo y el contenido está en el DOM desde el
    primer render.

    Espera $convenios, que arma App\Support\ConveniosPagados::porSede().
--}}

@php
    // Los montos son de siete y ocho cifras: se abrevian en la tarjeta y el
    // valor exacto se queda en el title para quien lo necesite.
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

<div class="inicio-tarjeta cv">
    <h3 class="inicio-tarjeta__titulo">
        Convenios pagados · <span class="cv-mes">{{ $convenios['mes'] }}</span>
    </h3>

    @if ($convenios['total'] === 0)
        <p class="cv-vacio">Todavía no se liquida ningún expediente este mes.</p>
    @else
    <div class="cv-totales">
        <div>
            <span class="cv-cifra">{{ number_format($convenios['total']) }}</span>
            <span class="cv-pie">expedientes liquidados</span>
        </div>
        <div>
            <span class="cv-cifra" title="{{ $exacto($convenios['monto']) }}">{{ $compacto($convenios['monto']) }}</span>
            <span class="cv-pie">entregados al trabajador</span>
        </div>
    </div>

    <div class="cv-pastillas" role="group" aria-label="Ordenar por">
        @foreach (\App\Support\ConveniosPagados::METRICAS as $clave => $etiqueta)
            <button type="button" class="cv-pastilla @if($loop->first) esta-activa @endif"
                    data-metrica="{{ $clave }}"
                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $etiqueta }}</button>
        @endforeach
    </div>

    @foreach (\App\Support\ConveniosPagados::METRICAS as $clave => $etiqueta)
        @php $bloque = $convenios['metricas'][$clave]; @endphp

        <ol class="cv-lista" data-metrica="{{ $clave }}" @unless($loop->first) hidden @endunless
            aria-label="Sedes ordenadas por {{ Str::lower($etiqueta) }}">
            @foreach ($bloque['sedes'] as $fila)
                @php
                    $valor = $fila[$clave];
                    $ancho = $bloque['maximo'] > 0 ? round($valor / $bloque['maximo'] * 100, 1) : 0;
                    $texto = $clave === 'expedientes' ? number_format($valor) : $compacto($valor);
                    $pista = $fila['sede'].': '.number_format($fila['expedientes']).' expedientes, '
                             .$exacto($fila['monto']).' entregados, '.$exacto($fila['promedio']).' en promedio';
                @endphp

                <li class="cv-fila" title="{{ $pista }}">
                    <span class="cv-sede">{{ $fila['sede'] }}</span>
                    <span class="cv-barra" aria-hidden="true">
                        <span class="cv-barra__relleno" style="width: {{ max($ancho, 1.5) }}%"></span>
                    </span>
                    <span class="cv-valor">{{ $texto }}</span>
                </li>
            @endforeach
        </ol>
    @endforeach

    <p class="cv-nota">
        Un expediente cuenta el mes en que se cubrió su última parcialidad.
    </p>
    @endif
</div>

<style>
    .cv-mes { text-transform: none; letter-spacing: 0; color: #5E6E6F; }

    .cv-vacio { font-size: 13px; color: #8A9899; margin: 0; }

    .cv-nota {
        font-size: 11.5px;
        line-height: 1.45;
        color: #A2AFAF;
        margin: 12px 0 0;
    }

    .cv-totales {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid #EDF1F1;
    }
    .cv-cifra {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #354647;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
    }
    .cv-pie { display: block; font-size: 11.5px; color: #8A9899; }

    .cv-pastillas { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }

    .cv-pastilla {
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
    .cv-pastilla:hover { border-color: #496163; color: #354647; }
    .cv-pastilla:focus-visible { outline: 2px solid #CEA845; outline-offset: 2px; }
    .cv-pastilla.esta-activa { background: #354647; border-color: #354647; color: #fff; }

    /* Sin esto el display de la lista le gana al atributo hidden. */
    .cv-lista[hidden] { display: none !important; }

    .cv-lista {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        /* 2px de superficie entre barras contiguas: se leen como marcas
           separadas y no como un bloque continuo. */
        gap: 2px;
    }

    .cv-fila {
        display: grid;
        grid-template-columns: 96px 1fr auto;
        align-items: center;
        gap: 10px;
        padding: 5px 4px;
        border-radius: 6px;
    }
    .cv-fila:hover { background: #F5F8F8; }

    .cv-sede {
        font-size: 12.5px;
        color: #3C4A4B;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cv-barra { display: block; height: 10px; background: #EDF1F1; border-radius: 3px; overflow: hidden; }
    .cv-barra__relleno {
        display: block;
        height: 100%;
        background: #496163;
        /* Extremo redondeado sólo del lado del dato; el otro queda anclado
           a la línea base. */
        border-radius: 0 4px 4px 0;
    }

    .cv-valor {
        font-size: 12.5px;
        font-weight: 600;
        color: #354647;
        font-variant-numeric: tabular-nums;
        min-width: 52px;
        text-align: right;
    }

    @media (prefers-reduced-motion: no-preference) {
        .cv-barra__relleno { transition: width .25s ease; }
    }
</style>

<script>
(function () {
    var tarjeta = document.currentScript.closest('.inicio-tarjeta') || document;
    var pastillas = tarjeta.querySelectorAll('.cv-pastilla');
    var listas    = tarjeta.querySelectorAll('.cv-lista');

    pastillas.forEach(function (pastilla) {
        pastilla.addEventListener('click', function () {
            var metrica = pastilla.dataset.metrica;

            pastillas.forEach(function (otra) {
                var activa = otra === pastilla;
                otra.classList.toggle('esta-activa', activa);
                otra.setAttribute('aria-pressed', activa ? 'true' : 'false');
            });

            listas.forEach(function (lista) {
                lista.hidden = lista.dataset.metrica !== metrica;
            });
        });
    });
})();
</script>
