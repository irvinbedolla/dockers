{{--
    Cabecera de la tarjeta de un citado: quién es, cómo se le cita, cómo va
    su notificación, su representante y sus documentos, todo junto
    (App\Support\RevisionAudiencia::partes). Espera $tj y $n (número).
--}}
<div class="ra-parte__cab">
    <div class="ra-parte__quien">
        <span class="ra-parte__num">{{ $n }}</span>
        <div>
            <div class="ra-parte__nombre">{{ $tj['nombre'] ?? 'Citado' }}</div>
            <div class="ra-parte__chips">
                @foreach ($tj['etiquetas'] ?? [] as [$texto, $clase])
                    <span class="ra-et ra-et--{{ $clase }}">{{ $texto }}</span>
                @endforeach
                @if ($tj)
                    <span class="ra-et ra-et--{{ $tj['notificacion']['nivel'] }}"><i class="bi bi-envelope"></i> {{ $tj['notificacion']['texto'] }}</span>
                    <span class="ra-et ra-et--{{ $tj['comparece'] ? 'ok' : 'info' }}">{{ $tj['comparece'] ? 'Comparece' : 'Sin comparecencia registrada' }}</span>
                    @if ($tj['representante'])
                        <span class="ra-et ra-et--{{ $tj['representante']['nivel'] }}" title="Folio {{ $tj['representante']['folio'] }}"><i class="bi bi-person-badge"></i> {{ $tj['representante']['nombre'] }} · {{ $tj['representante']['texto'] }}</span>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <div class="ra-seccion__herr">
        @if ($tj)
            @if ($tj['citatorio'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['citatorio'] }}" target="_blank"><i class="bi bi-file-earmark-text"></i> Citatorio</a>@endif
            @if ($tj['notificacion']['url'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['notificacion']['url'] }}" target="_blank"><i class="bi bi-envelope-paper"></i> Notificación</a>@endif
            @if ($tj['multa'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['multa'] }}" target="_blank"><i class="bi bi-exclamation-triangle"></i> Multa</a>@endif
        @endif
        <span class="ra-seccion__cambios" hidden><i class="bi bi-dot"></i> Cambios sin guardar</span>
        @if ($puedeEditar)
            <button type="button" class="btn btn-sm ra-editar"><i class="bi bi-pencil"></i> <span>Editar</span></button>
        @endif
    </div>
</div>
