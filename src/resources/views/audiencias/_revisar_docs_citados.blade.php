{{--
    Pestaña Documentos: citatorio y notificación de cada citado en una sola
    tabla (antes, dos listas separadas). Lo mismo está en la tarjeta de cada
    citado. Espera $citados y $tarjetas.
--}}
<div class="col-12 mt-3">
    <h5 class="ra-subtitulo">Citatorios y notificaciones</h5>
    <div class="table-responsive">
        <table class="table table-sm ra-tabla-docs mb-0">
            <thead><tr><th>Citado</th><th>Notificación</th><th>Comparece</th><th class="text-end">Documentos</th></tr></thead>
            <tbody>
                @foreach ($citados as $citado)
                    @php $tj = $tarjetas[$loop->index] ?? null; @endphp
                    @continue(! $tj)
                    <tr>
                        <td>
                            <b>{{ $tj['nombre'] }}</b>
                            <div class="ra-parte__chips">@foreach ($tj['etiquetas'] as [$texto, $clase])<span class="ra-et ra-et--{{ $clase }}">{{ $texto }}</span>@endforeach</div>
                        </td>
                        <td><span class="ra-et ra-et--{{ $tj['notificacion']['nivel'] }}">{{ $tj['notificacion']['texto'] }}</span></td>
                        <td>{{ $tj['comparece'] ? 'Sí' : 'No' }}</td>
                        <td class="text-end" style="white-space: nowrap;">
                            @if ($tj['citatorio'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['citatorio'] }}" target="_blank">Citatorio</a>
                            @else<span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true" title="Guarda para generar el citatorio">Citatorio</span>@endif
                            @if ($tj['notificacion']['url'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['notificacion']['url'] }}" target="_blank">Notificación</a>@endif
                            @if ($tj['multa'])<a class="btn btn-sm btn-outline-secondary" href="{{ $tj['multa'] }}" target="_blank">Multa</a>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
