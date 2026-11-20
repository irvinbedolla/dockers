{{-- Bitácora de cambios de agenda (historial_administracion, tipo agenda_conciliador). Espera $historial y, opcional, $nombres (id => nombre). --}}
<ul class="list-unstyled cn-historial mb-0">
    @foreach ($historial as $h)
        <li>
            <strong>{{ $h->created_at?->format('d/m/Y H:i') }}</strong>
            · {{ $h->user_nombre ?? 'Sistema' }}
            @isset($nombres)
                cambió a <strong>{{ $nombres[$h->registro_id] ?? 'conciliador #'.$h->registro_id }}</strong>
            @endisset
            <div class="cn-sub">
                @foreach ((array) $h->datos_despues as $clave => $valor)
                    <span class="d-block">{{ $clave }}: <span class="text-decoration-line-through">{{ ($h->datos_antes[$clave] ?? '—') }}</span> → {{ $valor }}</span>
                @endforeach
                @if ($h->motivo)<span class="d-block fst-italic">Motivo: {{ $h->motivo }}</span>@endif
            </div>
        </li>
    @endforeach
</ul>
