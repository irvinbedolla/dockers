{{--
    "¿Está lista la audiencia?": lo que el conciliador revisa antes de
    iniciar, con semáforo (App\Support\RevisionAudiencia::armar). Cada punto
    lleva a la pestaña donde se ve o se corrige. Sólo lee. Abre cerrado: el
    conciliador lo despliega con "Ver detalle" si lo necesita.
--}}
@php
    $rv = $revision;
    $c = $rv['cuenta'];
    $niveles = [
        'falta' => ['Falta', 'bi-x-circle-fill'],
        'aviso' => ['Revisar', 'bi-exclamation-triangle-fill'],
        'ok'    => ['Bien', 'bi-check-circle-fill'],
        'info'  => ['Dato', 'bi-info-circle-fill'],
    ];
    $objeto = $isAudiencia == 'Si' ? 'la audiencia' : 'la solicitud';
@endphp
<details class="ra-lista {{ $rv['lista'] ? 'is-lista' : '' }}">
    <summary>
        <span class="ra-lista__titulo">
            @if ($rv['lista'])
                <i class="bi bi-check-circle-fill"></i> Todo en orden para {{ $objeto }}
            @else
                <i class="bi bi-clipboard-check"></i> ¿Está lista {{ $objeto }}?
            @endif
        </span>
        <span class="ra-lista__cuentas">
            @if ($c['falta'])<span class="ra-cuenta ra-cuenta--falta">{{ $c['falta'] }} por corregir</span>@endif
            @if ($c['aviso'])<span class="ra-cuenta ra-cuenta--aviso">{{ $c['aviso'] }} por revisar</span>@endif
            <span class="ra-cuenta ra-cuenta--ok">{{ $c['ok'] }} en orden</span>
            <span class="ra-lista__ver">Ver detalle</span>
        </span>
    </summary>

    <ul class="ra-puntos">
        @foreach ($rv['puntos'] as $p)
            <li class="ra-punto ra-punto--{{ $p['nivel'] }}">
                <i class="bi {{ $niveles[$p['nivel']][1] }}" aria-label="{{ $niveles[$p['nivel']][0] }}"></i>
                <div class="ra-punto__texto"><b>{{ $p['parte'] }}</b> {{ $p['texto'] }}</div>
                <a href="#{{ $p['pestana'] }}" class="ra-ir" data-pestana="{{ $p['pestana'] }}">{{ \App\Support\RevisionAudiencia::PESTANAS[$p['pestana']] ?? 'Ver' }} <i class="bi bi-chevron-right"></i></a>
            </li>
        @endforeach
    </ul>

    @if ($rv['llegadas'] !== null)
        <div class="ra-llegadas">
            <div class="ra-llegadas__titulo"><i class="bi bi-person-check"></i> Recepción (Llegadas hoy)</div>
            @if (empty($rv['llegadas']['llegaron']))
                <span class="ra-llegadas__vacio">Recepción todavía no registra que haya llegado alguien.</span>
            @else
                @foreach ($rv['llegadas']['llegaron'] as $l)
                    <span class="ra-llego"><i class="bi bi-check2"></i> {{ $l['nombre'] }} <small>{{ $l['parte'] }} · {{ $l['hora'] }}</small></span>
                @endforeach
            @endif
            @foreach ($rv['llegadas']['observaciones'] as $o)
                <span class="ra-obs"><i class="bi bi-chat-left-text"></i> {{ $o }}</span>
            @endforeach
        </div>
    @endif
</details>
